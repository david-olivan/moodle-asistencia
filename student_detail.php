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
 * Per-student session-by-session detail view.
 *
 * Accessible by teachers/managers viewing any student, and by students
 * viewing their own data (viewownattendance capability).
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$cmid = required_param('id', PARAM_INT); // Course-module ID.
$userid = required_param('userid', PARAM_INT); // Target student user ID.

[$course, $cm] = get_course_and_cm_from_cmid($cmid, 'attendancecontrol');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$instance = $DB->get_record('attendancecontrol', ['id' => $cm->instance], '*', MUST_EXIST);

// Students may only view their own data.
if (!has_capability('mod/attendancecontrol:viewsummary', $context)) {
    require_capability('mod/attendancecontrol:viewownattendance', $context);
    if ($userid !== (int) $USER->id) {
        throw new \moodle_exception('accessdenied', 'admin');
    }
}

$student = core_user::get_user($userid, '*', MUST_EXIST);

$PAGE->set_url('/mod/attendancecontrol/student_detail.php', ['id' => $cmid, 'userid' => $userid]);
$PAGE->set_title(fullname($student));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$calculator = new \mod_attendancecontrol\local\attendance_calculator($instance);
$detail = $calculator->get_student_detail($userid);

$renderer = $PAGE->get_renderer('mod_attendancecontrol');

echo $OUTPUT->header();
echo $OUTPUT->heading(fullname($student));
echo $renderer->render_student_detail($detail, $instance, $student);
echo $OUTPUT->footer();
