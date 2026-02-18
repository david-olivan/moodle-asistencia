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
 * Event fired when a session is automatically generated.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\event;

/**
 * Session created event.
 *
 * Logged whenever the session_manager generates a new session record.
 */
class session_created extends \core\event\base {

    /**
     * Initialises the event properties.
     */
    protected function init(): void {
        $this->data['crud']        = 'c';
        $this->data['edulevel']    = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendancecontrol_session';
    }

    /**
     * Returns the event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventsessioncreated', 'mod_attendancecontrol');
    }

    /**
     * Returns a human-readable event description.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' created a session with id '{$this->objectid}'.";
    }

    /**
     * Returns the URL of the activity.
     *
     * @return \moodle_url
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/attendancecontrol/view.php', [
            'id' => $this->contextinstanceid,
        ]);
    }
}
