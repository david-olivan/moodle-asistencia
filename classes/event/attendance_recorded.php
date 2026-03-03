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
 * Event fired when a teacher records/updates attendance for a session.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\event;

/**
 * Attendance recorded event.
 *
 * Triggered after a teacher successfully saves attendance for a session.
 */
class attendance_recorded extends \core\event\base
{
    /**
     * Initialises the event properties.
     */
    protected function init(): void
    {
        $this->data['crud'] = 'u'; // Update (also covers create on first save).
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendancecontrol_session';
    }

    /**
     * Returns the event name shown in the log report.
     *
     * @return string
     */
    public static function get_name(): string
    {
        return get_string('eventattendancerecorded', 'mod_attendancecontrol');
    }

    /**
     * Returns a human-readable description of the event.
     *
     * @return string
     */
    public function get_description(): string
    {
        return "The user with id '{$this->userid}' recorded attendance for session id " .
            "'{$this->objectid}' in the attendancecontrol instance " .
            "with id '{$this->other['sessionid']}'.";
    }

    /**
     * Returns the URL of the relevant page.
     *
     * @return \moodle_url
     */
    public function get_url(): \moodle_url
    {
        return new \moodle_url('/mod/attendancecontrol/attendance.php', [
            'id' => $this->contextinstanceid,
            'sessionid' => $this->objectid,
        ]);
    }
}
