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
 * Main entry-point view for the attendancecontrol activity.
 *
 * Renders either the teacher session-list view or the student summary view
 * depending on the current user's capabilities.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course-module ID.

[$course, $cm] = get_course_and_cm_from_cmid($id, 'attendancecontrol');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$instance = $DB->get_record('attendancecontrol', ['id' => $cm->instance], '*', MUST_EXIST);

$PAGE->set_url('/mod/attendancecontrol/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Trigger course-module viewed event.
$event = \mod_attendancecontrol\event\course_module_viewed::create([
    'objectid' => $instance->id,
    'context' => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('attendancecontrol', $instance);
$event->trigger();

// Completion tracking.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->requires->js_call_amd('mod_attendancecontrol/session_navigation', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($instance->name));

$renderer = $PAGE->get_renderer('mod_attendancecontrol');

if (has_capability('mod/attendancecontrol:viewsummary', $context)) {
    // Teacher / manager / non-editing teacher view.
    echo $renderer->render_teacher_view($instance, $cm, $context);
} else if (has_capability('mod/attendancecontrol:viewownattendance', $context)) {
    // Student view.
    echo $renderer->render_student_view($instance, $cm, $context);
} else {
    throw new \moodle_exception('accessdenied', 'admin');
}

echo $OUTPUT->footer();
