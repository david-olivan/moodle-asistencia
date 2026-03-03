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
 * Attendance registration form.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

/**
 * Form for recording attendance for a single session.
 *
 * Custom data expected:
 *   - instance  \stdClass  Plugin instance record.
 *   - session   \stdClass  Session record.
 *   - cm        \cm_info   Course module info.
 *   - context   \context_module
 */
class attendance_form extends \moodleform
{
    /**
     * Defines form elements – one row per group member.
     */
    public function definition(): void {
        $mform = $this->_form;
        $instance = $this->_customdata['instance'];
        $session = $this->_customdata['session'];
        $cm = $this->_customdata['cm'];

        // Allow the attendance_form AMD module to locate this form element.
        $mform->updateAttributes(['data-region' => 'attendance-form']);

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->addElement('hidden', 'sessionid', $session->id);
        $mform->setType('id', PARAM_INT);
        $mform->setType('sessionid', PARAM_INT);

        // Bulk action: mark all as.
        $bulkoptions = [
            1 => get_string('statuspresent', 'mod_attendancecontrol'),
            2 => get_string('statuslate', 'mod_attendancecontrol'),
            3 => get_string('statusjustified', 'mod_attendancecontrol'),
            4 => get_string('statusunjustified', 'mod_attendancecontrol'),
        ];
        $mform->addElement('select', 'bulk_status', get_string('markallpresent', 'mod_attendancecontrol'), $bulkoptions);
        $mform->setDefault('bulk_status', 1);

        // Per-student rows.
        $students = groups_get_members($instance->groupid, 'u.id, u.firstname, u.lastname, u.middlename');
        uasort($students, static fn($a, $b) => strcmp($a->lastname, $b->lastname));

        $statusoptions = [
            1 => get_string('statuspresent', 'mod_attendancecontrol'),
            2 => get_string('statuslate', 'mod_attendancecontrol'),
            3 => get_string('statusjustified', 'mod_attendancecontrol'),
            4 => get_string('statusunjustified', 'mod_attendancecontrol'),
        ];

        foreach ($students as $student) {
            $uid = $student->id;

            $mform->addElement('header', "student_hdr_{$uid}", fullname($student));

            $mform->addElement(
                'select',
                "student_status[{$uid}]",
                get_string('sessionstatus', 'mod_attendancecontrol'),
                $statusoptions
            );
            $mform->setType("student_status[{$uid}]", PARAM_INT);
            $mform->setDefault("student_status[{$uid}]", 1);

            $mform->addElement(
                'textarea',
                "student_remarks[{$uid}]",
                get_string('remarks', 'mod_attendancecontrol'),
                ['rows' => 2, 'cols' => 40]
            );
            $mform->setType("student_remarks[{$uid}]", PARAM_TEXT);
        }

        // Load existing records if already registered.
        $this->load_existing_records($session);

        $this->add_action_buttons(true, get_string('saveattendance', 'mod_attendancecontrol'));
    }

    /**
     * Pre-fills status/remarks from previously saved records.
     *
     * @param \stdClass $session
     */
    protected function load_existing_records(\stdClass $session): void {
        global $DB;

        $records = $DB->get_records('attendancecontrol_record', ['sessionid' => $session->id]);

        $currentdata = [];
        foreach ($records as $rec) {
            $currentdata["student_status[{$rec->userid}]"] = $rec->status;
            $currentdata["student_remarks[{$rec->userid}]"] = $rec->remarks;
        }

        if ($currentdata) {
            $this->set_data($currentdata);
        }
    }
}
