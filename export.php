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
 * Excel export handler.
 *
 * Streams a three-sheet Excel workbook directly to the browser:
 *   Sheet 1 – Summary (one row per student).
 *   Sheet 2 – Session detail (one row per student × session).
 *   Sheet 3 – Configuration parameters.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course-module ID.

[$course, $cm] = get_course_and_cm_from_cmid($id, 'attendancecontrol');

require_login($course, true, $cm);

$context  = context_module::instance($cm->id);
$instance = $DB->get_record('attendancecontrol', ['id' => $cm->instance], '*', MUST_EXIST);

require_capability('mod/attendancecontrol:export', $context);

$manager = new \mod_attendancecontrol\local\export_manager($instance);
$manager->send_excel();
// send_excel() calls exit() after streaming the file.
