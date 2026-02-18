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
 * Event fired when the attendance activity main page is viewed.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\event;

/**
 * Course module viewed event.
 *
 * Extends the Moodle core base event for "course module viewed" so that
 * completion tracking and the standard log report work out of the box.
 */
class course_module_viewed extends \core\event\course_module_viewed {

    /**
     * Initialises the event properties.
     */
    protected function init(): void {
        $this->data['objecttable'] = 'attendancecontrol';
        parent::init();
    }
}
