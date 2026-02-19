<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Lists all mod_attendancecontrol instances in a course.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course ID.

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_course_login($course);

$PAGE->set_url('/mod/attendancecontrol/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_course::instance($course->id));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_attendancecontrol'));

$instances = get_all_instances_in_course('attendancecontrol', $course);

if (empty($instances)) {
    notice(get_string('noneactiveyet'), new moodle_url('/course/view.php', ['id' => $course->id]));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable';
$table->head  = [
    get_string('name'),
    get_string('section'),
];
$table->align = ['left', 'left'];

foreach ($instances as $instance) {
    $link = html_writer::link(
        new moodle_url('/mod/attendancecontrol/view.php', ['id' => $instance->coursemodule]),
        format_string($instance->name, true, ['context' => context_module::instance($instance->coursemodule)])
    );

    $table->data[] = [$link, $instance->section];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
