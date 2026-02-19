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
 * English language strings for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ── Plugin identity ──────────────────────────────────────────────────────────
$string['modulename']        = 'Attendance Control';
$string['modulenameplural']  = 'Attendance Controls';
$string['modulename_help']   = 'The Attendance Control module allows you to record daily student attendance, view summaries with percentages, and export data to Excel.';
$string['pluginname']        = 'Attendance Control';
$string['pluginadministration'] = 'Attendance Control Administration';

// ── mod_form: sections and fields ────────────────────────────────────────────
$string['group']                   = 'Student group';
$string['group_help']              = 'Select the Moodle group whose members will form part of the attendance list.';
$string['daterange']               = 'Course date range';
$string['coursestartdate']         = 'Start date';
$string['courseenddate']           = 'End date';
$string['totalhours']              = 'Total subject hours';
$string['schedule']                = 'Weekly time slots';
$string['dayofweek']               = 'Day of the week';
$string['starttime']               = 'Start time (HH:MM)';
$string['endtime']                 = 'End time (HH:MM)';
$string['addslot']                 = 'Add another time slot';
$string['holidays']                = 'Holidays';
$string['holidaydate']             = 'Holiday date';
$string['holidaydescription']      = 'Description (optional)';
$string['addholiday']              = 'Add holiday';
$string['penaltyconfig']           = 'Penalty configuration';
$string['maxunjustifiedpct']       = 'Maximum allowed absence percentage';
$string['maxunjustifiedpct_help']  = 'Maximum percentage of equivalent unjustified absence hours over the total hours. Students above this threshold are highlighted in red.';
$string['delayratio']              = 'Late → unjustified absence ratio';
$string['delayratio_help']         = 'A late arrival counts as this fraction of one unjustified absence hour. Example: 0.5 means a 1-hour late arrival counts as 0.5 unjustified absence hours.';
$string['justifiedratio']          = 'Justified absence → unjustified absence ratio';
$string['justifiedratio_help']     = 'A justified absence counts as this fraction of one unjustified absence hour. Example: 0.5 means 1 justified hour counts as 0.5 unjustified absence hours.';

// ── Validation errors ────────────────────────────────────────────────────────
$string['err_enddatebeforestart'] = 'The end date must be after the start date.';
$string['err_hourspositive']      = 'Total hours must be a positive number.';
$string['err_invalidratio']       = 'The value must be between 0 and 100.';

// ── view.php ─────────────────────────────────────────────────────────────────
$string['registerattendancetoday'] = 'Record today\'s attendance';
$string['viewfulldata']            = 'View full data';
$string['sessionsthisweek']        = 'Sessions this week';
$string['previousweek']           = '◄ Previous week';
$string['nextweek']               = 'Next week ►';
$string['nostudentview']          = 'You do not have permission to view this activity.';

// ── Session list ─────────────────────────────────────────────────────────────
$string['sessiondate']      = 'Date';
$string['sessionschedule']  = 'Schedule';
$string['sessionstatus']    = 'Status';
$string['statuspending']       = 'Pending';
$string['statusrecorded']      = 'Recorded';
$string['nosessionsthisweek']  = 'No sessions scheduled for this week.';

// ── attendance.php ───────────────────────────────────────────────────────────
$string['recordattendance']  = 'Record attendance';
$string['sessionheading']    = 'Session: {$a}';
$string['markallpresent']    = 'Mark all as:';
$string['studentname']       = 'Student';
$string['statuspresent']     = 'Present';
$string['statuslate']        = 'Late';
$string['statusjustified']   = 'Justified absence';
$string['statusunjustified'] = 'Unjustified absence';
$string['remarks']           = 'Remarks';
$string['saveattendance']    = 'Save attendance';
$string['attendancesaved']   = 'Attendance saved successfully.';

// ── report.php ───────────────────────────────────────────────────────────────
$string['reporttitle']          = 'Attendance summary';
$string['presences']            = 'Presences';
$string['lates']                = 'Late arrivals';
$string['justifiedabsences']    = 'Justified absences';
$string['unjustifiedabsences']  = 'Unjustified absences';
$string['equivalenthours']      = 'Equiv. absence hours';
$string['attendancepct']        = 'Attendance %';
$string['exportexcel']          = 'Export to Excel';
$string['thresholdnotice']      = 'Configured threshold: {$a->threshold}% ({$a->pct}% maximum absences out of {$a->hours}h)';

// ── student_detail.php ───────────────────────────────────────────────────────
$string['studentdetailtitle']    = 'Attendance detail';
$string['myattendance']          = 'My attendance';
$string['viewbreakdown']         = 'View session breakdown';
$string['alertthresholdreached'] = 'Warning! You have exceeded the maximum allowed absence threshold.';
$string['sessionbreakdown']      = 'Session breakdown';

// ── export.php ───────────────────────────────────────────────────────────────
$string['excel_sheet_summary']       = 'Summary';
$string['excel_sheet_detail']        = 'Session detail';
$string['excel_sheet_config']        = 'Configuration';
$string['excel_col_student']         = 'Student';
$string['excel_col_date']            = 'Date';
$string['excel_col_schedule']        = 'Schedule';
$string['excel_col_duration']        = 'Duration (h)';
$string['excel_col_status']          = 'Status';
$string['excel_col_remarks']         = 'Remarks';
$string['excel_col_equivhours']      = 'Equiv. absence hours';
$string['excel_col_pct']             = 'Attendance %';
$string['excel_param_subject']       = 'Subject';
$string['excel_param_group']         = 'Group';
$string['excel_param_totalhours']    = 'Total hours';
$string['excel_param_delayratio']    = 'Late arrival ratio';
$string['excel_param_justifiedratio']= 'Justified absence ratio';
$string['excel_param_maxpct']        = 'Maximum allowed absence %';

// ── Capabilities (shown in role administration) ──────────────────────────────
$string['attendancecontrol:addinstance']       = 'Add an Attendance Control instance';
$string['attendancecontrol:viewsummary']       = 'View group attendance summary';
$string['attendancecontrol:recordattendance']  = 'Record and edit attendance';
$string['attendancecontrol:viewownattendance'] = 'View own attendance';
$string['attendancecontrol:export']            = 'Export attendance data to Excel';
$string['attendancecontrol:managesessions']    = 'Manage future sessions';

// ── Events ───────────────────────────────────────────────────────────────────
$string['eventattendancerecorded']   = 'Attendance recorded';
$string['eventcoursemodudeviewed']   = 'Attendance control viewed';
$string['eventsessioncreated']       = 'Session created';

// ── Privacy ──────────────────────────────────────────────────────────────────
$string['privacy:metadata:attendancecontrol_record']              = 'Stores the attendance record for each student per session.';
$string['privacy:metadata:attendancecontrol_record:userid']       = 'The ID of the user (student) to whom the record belongs.';
$string['privacy:metadata:attendancecontrol_record:status']       = 'Attendance status (present, late, justified, unjustified).';
$string['privacy:metadata:attendancecontrol_record:remarks']      = 'Remarks entered by the teacher.';
$string['privacy:metadata:attendancecontrol_record:recorded_by']  = 'ID of the teacher who recorded the attendance.';
$string['privacy:metadata:attendancecontrol_record:timecreated']  = 'Timestamp of when the record was created.';
$string['privacy:metadata:attendancecontrol_record:timemodified'] = 'Timestamp of the last modification.';
