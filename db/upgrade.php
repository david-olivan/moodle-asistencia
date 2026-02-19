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
 * Database upgrade steps for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Executes pending upgrade steps.
 *
 * @param  int  $oldversion  Previously installed plugin version.
 * @return bool
 */
function xmldb_attendancecontrol_upgrade(int $oldversion): bool {
    // No upgrade steps required for v1.0.0.
    // Add future migration blocks here as:
    //   if ($oldversion < YYYYMMDDXX) { … upgrade_mod_savepoint(…); }

    return true;
}
