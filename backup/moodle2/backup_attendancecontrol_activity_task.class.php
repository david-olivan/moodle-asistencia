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
 * Backup task for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/attendancecontrol/backup/moodle2/backup_attendancecontrol_stepslib.php');

/**
 * Provides the steps required to backup one attendancecontrol activity.
 */
class backup_attendancecontrol_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity.
     */
    protected function define_my_settings(): void {
        // Nothing to do.
    }

    /**
     * Registers the structure backup step.
     */
    protected function define_my_steps(): void {
        $this->add_step(new backup_attendancecontrol_activity_structure_step(
            'attendancecontrol_structure',
            'attendancecontrol.xml'
        ));
    }

    /**
     * Encodes content links for rewriting on restore.
     *
     * @param  string $content
     * @return string
     */
    public static function encode_content_links(string $content): string {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // view.php.
        $search  = "/({$base}\/mod\/attendancecontrol\/view\.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@ATTENDANCECONTROLVIEWBYID*$2@$', $content);

        return $content;
    }
}
