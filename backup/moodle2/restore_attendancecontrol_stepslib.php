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
 * Restore step definitions for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the structure step for restoring attendancecontrol data.
 */
class restore_attendancecontrol_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the XML paths and DB targets.
     *
     * @return array
     */
    protected function define_structure(): array {
        $paths   = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('attendancecontrol', '/activity/attendancecontrol');
        $paths[] = new restore_path_element('attendancecontrol_schedule', '/activity/attendancecontrol/schedule');
        $paths[] = new restore_path_element('attendancecontrol_holiday', '/activity/attendancecontrol/holiday');
        $paths[] = new restore_path_element('attendancecontrol_session', '/activity/attendancecontrol/session');

        if ($userinfo) {
            $paths[] = new restore_path_element(
                'attendancecontrol_record',
                '/activity/attendancecontrol/session/record'
            );
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the main instance record.
     *
     * @param array|object $data
     */
    protected function process_attendancecontrol($data): void {
        global $DB;

        $data             = (object) $data;
        $data->course     = $this->get_courseid();
        $data->timecreated  = time();
        $data->timemodified = time();

        $newitemid = $DB->insert_record('attendancecontrol', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Processes a schedule slot.
     *
     * @param array|object $data
     */
    protected function process_attendancecontrol_schedule($data): void {
        global $DB;

        $data = (object) $data;
        $data->attendancecontrolid = $this->get_new_parentid('attendancecontrol');
        $DB->insert_record('attendancecontrol_schedule', $data);
    }

    /**
     * Processes a holiday record.
     *
     * @param array|object $data
     */
    protected function process_attendancecontrol_holiday($data): void {
        global $DB;

        $data = (object) $data;
        $data->attendancecontrolid = $this->get_new_parentid('attendancecontrol');
        $DB->insert_record('attendancecontrol_holiday', $data);
    }

    /**
     * Processes a session record.
     *
     * @param array|object $data
     */
    protected function process_attendancecontrol_session($data): void {
        global $DB;

        $data = (object) $data;
        $data->attendancecontrolid = $this->get_new_parentid('attendancecontrol');
        $data->timecreated         = isset($data->timecreated) ? $data->timecreated : time();
        $data->timemodified        = isset($data->timemodified) ? $data->timemodified : time();

        $newitemid = $DB->insert_record('attendancecontrol_session', $data);
        $this->set_mapping('attendancecontrol_session', $data->id, $newitemid);
    }

    /**
     * Processes an attendance record (userinfo only).
     *
     * @param array|object $data
     */
    protected function process_attendancecontrol_record($data): void {
        global $DB;

        $data             = (object) $data;
        $data->sessionid  = $this->get_new_parentid('attendancecontrol_session');
        $data->userid     = $this->get_mappingid('user', $data->userid);
        $data->recorded_by = $this->get_mappingid('user', $data->recorded_by);
        $data->timecreated  = isset($data->timecreated) ? $data->timecreated : time();
        $data->timemodified = isset($data->timemodified) ? $data->timemodified : time();

        $DB->insert_record('attendancecontrol_record', $data);
    }

    /**
     * Post-execution tasks after all data has been restored.
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_attendancecontrol', 'intro', null);
    }
}
