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
 * Restore task for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/attendancecontrol/backup/moodle2/restore_attendancecontrol_stepslib.php');

/**
 * Provides the steps required to restore one attendancecontrol activity.
 */
class restore_attendancecontrol_activity_task extends restore_activity_task {

    /**
     * No specific settings.
     */
    protected function define_my_settings(): void {
        // Nothing to do.
    }

    /**
     * Registers the structure restore step.
     */
    protected function define_my_steps(): void {
        $this->add_step(new restore_attendancecontrol_activity_structure_step(
            'attendancecontrol_structure',
            'attendancecontrol.xml'
        ));
    }

    /**
     * Defines the contents_conditions for file restoration.
     *
     * @return array
     */
    public static function define_decode_contents(): array {
        return [
            new restore_decode_content('attendancecontrol', ['intro'], 'attendancecontrol'),
        ];
    }

    /**
     * Defines the link rewrites performed during restore.
     *
     * @return array
     */
    public static function define_decode_rules(): array {
        return [
            new restore_decode_rule(
                'ATTENDANCECONTROLVIEWBYID',
                '/mod/attendancecontrol/view.php?id=$1',
                'course_module'
            ),
        ];
    }

    /**
     * No log entries to restore.
     *
     * @return array
     */
    public static function define_restore_log_rules(): array {
        return [];
    }

    /**
     * No course-level log entries.
     *
     * @return array
     */
    public static function define_restore_log_rules_for_course(): array {
        return [];
    }
}
