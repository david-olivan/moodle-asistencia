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
 * Backup step definitions for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the structure step for backing up attendancecontrol data.
 */
class backup_attendancecontrol_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the backup structure tree.
     *
     * @return backup_nested_element
     */
    protected function define_structure(): backup_nested_element {
        $userinfo = $this->get_setting_value('userinfo');

        // Root element (instance).
        $attendancecontrol = new backup_nested_element(
            'attendancecontrol',
            ['id'],
            [
                'name', 'intro', 'introformat', 'groupid',
                'total_hours', 'course_start_date', 'course_end_date',
                'max_unjustified_absence_pct',
                'delay_to_unjustified_ratio',
                'justified_to_unjustified_ratio',
                'timecreated', 'timemodified',
            ]
        );

        // Schedule slots.
        $schedule = new backup_nested_element(
            'schedule',
            ['id'],
            ['day_of_week', 'start_time', 'end_time']
        );

        // Holidays.
        $holidays = new backup_nested_element(
            'holiday',
            ['id'],
            ['holiday_date', 'description']
        );

        // Sessions.
        $sessions = new backup_nested_element(
            'session',
            ['id'],
            [
                'session_date', 'start_time', 'end_time',
                'duration_hours', 'status', 'timecreated', 'timemodified',
            ]
        );

        // Attendance records (user data – only if userinfo is enabled).
        $records = new backup_nested_element(
            'record',
            ['id'],
            ['userid', 'status', 'remarks', 'recorded_by', 'timecreated', 'timemodified']
        );

        // Build tree.
        $attendancecontrol->add_child($schedule);
        $attendancecontrol->add_child($holidays);
        $attendancecontrol->add_child($sessions);
        $sessions->add_child($records);

        // Set data sources.
        $attendancecontrol->set_source_table('attendancecontrol', ['id' => backup::VAR_ACTIVITYID]);
        $schedule->set_source_table('attendancecontrol_schedule', ['attendancecontrolid' => backup::VAR_PARENTID]);
        $holidays->set_source_table('attendancecontrol_holiday', ['attendancecontrolid' => backup::VAR_PARENTID]);
        $sessions->set_source_table('attendancecontrol_session', ['attendancecontrolid' => backup::VAR_PARENTID]);

        if ($userinfo) {
            $records->set_source_table('attendancecontrol_record', ['sessionid' => backup::VAR_PARENTID]);
        }

        // Annotate IDs that reference other tables.
        $attendancecontrol->annotate_ids('group', 'groupid');
        $records->annotate_ids('user', 'userid');
        $records->annotate_ids('user', 'recorded_by');

        // Return the root.
        return $this->prepare_activity_structure($attendancecontrol);
    }
}
