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
 * Full group attendance summary report.
 *
 * Shows one row per student with counts, equivalent hours and the
 * attendance percentage. Rows below the threshold are highlighted in red.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course-module ID.

[$course, $cm] = get_course_and_cm_from_cmid($id, 'attendancecontrol');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$instance = $DB->get_record('attendancecontrol', ['id' => $cm->instance], '*', MUST_EXIST);

require_capability('mod/attendancecontrol:viewsummary', $context);

$PAGE->set_url('/mod/attendancecontrol/report.php', ['id' => $id]);
$PAGE->set_title(get_string('reporttitle', 'mod_attendancecontrol'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$calculator = new \mod_attendancecontrol\local\attendance_calculator($instance);
$summary = $calculator->get_group_summary();

$renderer = $PAGE->get_renderer('mod_attendancecontrol');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reporttitle', 'mod_attendancecontrol'));
echo $renderer->render_summary_table($summary, $instance, $cm);
echo $OUTPUT->footer();
