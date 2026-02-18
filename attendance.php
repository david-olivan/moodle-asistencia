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
 * Attendance registration page for a single session.
 *
 * Teachers/managers use this page to mark each student as present, late,
 * justified absence, or unjustified absence for a given session.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$cmid      = required_param('id',        PARAM_INT); // Course-module ID.
$sessionid = required_param('sessionid', PARAM_INT); // Session ID.

[$course, $cm] = get_course_and_cm_from_cmid($cmid, 'attendancecontrol');

require_login($course, true, $cm);

$context  = context_module::instance($cm->id);
$instance = $DB->get_record('attendancecontrol', ['id' => $cm->instance], '*', MUST_EXIST);
$session  = $DB->get_record('attendancecontrol_session', ['id' => $sessionid, 'attendancecontrolid' => $instance->id], '*', MUST_EXIST);

require_capability('mod/attendancecontrol:recordattendance', $context);

$PAGE->set_url('/mod/attendancecontrol/attendance.php', ['id' => $cmid, 'sessionid' => $sessionid]);
$PAGE->set_title(get_string('recordattendance', 'mod_attendancecontrol'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$form = new \mod_attendancecontrol\form\attendance_form(null, [
    'instance' => $instance,
    'session'  => $session,
    'cm'       => $cm,
    'context'  => $context,
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/attendancecontrol/view.php', ['id' => $cmid]));
}

if ($data = $form->get_data()) {
    // Persist records for each student in the group.
    $manager = new \mod_attendancecontrol\local\session_manager($instance);
    $manager->save_attendance_records($session, $data);

    // Fire event.
    $event = \mod_attendancecontrol\event\attendance_recorded::create([
        'objectid' => $session->id,
        'context'  => $context,
        'other'    => ['sessionid' => $session->id],
    ]);
    $event->trigger();

    redirect(
        new moodle_url('/mod/attendancecontrol/view.php', ['id' => $cmid]),
        get_string('attendancesaved', 'mod_attendancecontrol'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(
    get_string('sessionheading', 'mod_attendancecontrol',
        userdate($session->session_date) . ' — ' . $session->start_time . ' a ' . $session->end_time
    )
);

$form->display();

echo $OUTPUT->footer();
