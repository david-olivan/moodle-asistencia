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
 * @copyright  2026 David Oliván Malagón
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
$session  = $DB->get_record('attendancecontrol_session',
    ['id' => $sessionid, 'attendancecontrolid' => $instance->id], '*', MUST_EXIST);

require_capability('mod/attendancecontrol:recordattendance', $context);

$PAGE->set_url('/mod/attendancecontrol/attendance.php', ['id' => $cmid, 'sessionid' => $sessionid]);
$PAGE->set_title(get_string('recordattendance', 'mod_attendancecontrol'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// ── POST handler ──────────────────────────────────────────────────────────────
if (data_submitted() && confirm_sesskey()) {
    $statuses = optional_param_array('student_status', [], PARAM_INT);
    $remarks  = optional_param_array('student_remarks', [], PARAM_TEXT);

    $data = (object) [
        'student_status'  => $statuses,
        'student_remarks' => $remarks,
    ];

    $manager = new \mod_attendancecontrol\local\session_manager($instance);
    $manager->save_attendance_records($session, $data);

    $event = \mod_attendancecontrol\event\attendance_recorded::create([
        'objectid' => $session->id,
        'context'  => $context,
        'other'    => ['sessionid' => $session->id],
    ]);
    $event->trigger();

    \core\session\manager::write_close();
    redirect(
        new moodle_url('/mod/attendancecontrol/view.php', ['id' => $cmid]),
        get_string('attendancesaved', 'mod_attendancecontrol'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// ── GET: build template context ───────────────────────────────────────────────

// Fetch students with all name fields required by fullname().
$students = groups_get_members(
    $instance->groupid,
    'u.id, u.firstname, u.lastname, u.middlename, u.firstnamephonetic, u.lastnamephonetic, u.alternatename'
);

// Sort by last name.
uasort($students, static function ($a, $b) {
    return strcmp($a->lastname, $b->lastname);
});

// Load existing records for this session, indexed by userid.
$existingrecords = $DB->get_records('attendancecontrol_record',
    ['sessionid' => $session->id], '', 'userid, status, remarks');

// Status definitions: value, lang string key, Bootstrap btn outline class.
$statusdefs = [
    1 => ['key' => 'statuspresent',     'btn' => 'btn-outline-success'],
    2 => ['key' => 'statuslate',        'btn' => 'btn-outline-warning'],
    3 => ['key' => 'statusjustified',   'btn' => 'btn-outline-info'],
    4 => ['key' => 'statusunjustified', 'btn' => 'btn-outline-danger'],
];

$studentrows = [];
foreach ($students as $student) {
    $uid            = (int) $student->id;
    $existingstatus = isset($existingrecords[$uid]) ? (int) $existingrecords[$uid]->status : 1;
    $existingremarks = $existingrecords[$uid]->remarks ?? '';

    $statuses = [];
    foreach ($statusdefs as $value => $def) {
        $statuses[] = [
            'uid'       => $uid,
            'value'     => $value,
            'label'     => get_string($def['key'], 'mod_attendancecontrol'),
            'btn_class' => $def['btn'],
            'checked'   => ($existingstatus === $value),
            'input_id'  => "status_{$uid}_{$value}",
        ];
    }

    $studentrows[] = [
        'uid'      => $uid,
        'fullname' => fullname($student),
        'remarks'  => $existingremarks,
        'statuses' => $statuses,
    ];
}

$bulkoptions = [];
foreach ($statusdefs as $value => $def) {
    $bulkoptions[] = [
        'value' => $value,
        'label' => get_string($def['key'], 'mod_attendancecontrol'),
    ];
}

$templatecontext = [
    'action_url'      => (new moodle_url('/mod/attendancecontrol/attendance.php',
        ['id' => $cmid, 'sessionid' => $sessionid]))->out(false),
    'sesskey'         => sesskey(),
    'id'              => $cmid,
    'sessionid'       => $sessionid,
    'cancel_url'      => (new moodle_url('/mod/attendancecontrol/view.php', ['id' => $cmid]))->out(false),
    'bulk_options'    => $bulkoptions,
    'students'        => $studentrows,
    'str_save'        => get_string('saveattendance',  'mod_attendancecontrol'),
    'str_cancel'      => get_string('cancel'),
    'str_markall'     => get_string('markallpresent',  'mod_attendancecontrol'),
];

$PAGE->requires->js_call_amd('mod_attendancecontrol/attendance_form', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading(
    get_string('sessionheading', 'mod_attendancecontrol',
        userdate($session->session_date) . ' — ' . $session->start_time . ' a ' . $session->end_time
    )
);
echo $OUTPUT->render_from_template('mod_attendancecontrol/attendance_table', $templatecontext);
echo $OUTPUT->footer();
