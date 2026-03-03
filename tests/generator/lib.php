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
 * Test data generator for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Data generator for mod_attendancecontrol activity instances.
 */
class mod_attendancecontrol_generator extends testing_module_generator {
    /**
     * Creates an attendancecontrol instance with sensible defaults.
     *
     * Required DB fields that are not passed in the Behat table are filled
     * with safe defaults so the record can be inserted without error.
     *
     * @param array|stdClass $record  Field values; any field may be omitted.
     * @param array|null     $options Optional extra options passed to parent.
     * @return stdClass               The created course-module record.
     */
    public function create_instance($record = null, array $options = null): stdClass {
        $record = (object) (array) $record;

        if (!isset($record->groupid)) {
            $record->groupid = 0;
        }
        if (!isset($record->total_hours)) {
            $record->total_hours = 100;
        }
        if (!isset($record->course_start_date)) {
            $record->course_start_date = strtotime('-6 months');
        }
        if (!isset($record->course_end_date)) {
            $record->course_end_date = strtotime('+6 months');
        }
        // Passed as integer N; attendancecontrol_add_instance() converts to 1/N.
        if (!isset($record->delay_to_unjustified_ratio)) {
            $record->delay_to_unjustified_ratio = 2;
        }
        if (!isset($record->justified_to_unjustified_ratio)) {
            $record->justified_to_unjustified_ratio = 2;
        }
        if (!isset($record->max_unjustified_absence_pct)) {
            $record->max_unjustified_absence_pct = 15;
        }

        return parent::create_instance($record, $options);
    }
}
