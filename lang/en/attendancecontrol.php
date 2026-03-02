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
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addholiday'] = 'Add holiday';
$string['addslot'] = 'Add another time slot';
$string['alertthresholdreached'] = 'Warning! You have exceeded the maximum allowed absence threshold.';
$string['attendancecontrol:addinstance'] = 'Add an Attendance Control instance';
$string['attendancecontrol:export'] = 'Export attendance data to Excel';
$string['attendancecontrol:managesessions'] = 'Manage future sessions';
$string['attendancecontrol:recordattendance'] = 'Record and edit attendance';
$string['attendancecontrol:viewownattendance'] = 'View own attendance';
$string['attendancecontrol:viewsummary'] = 'View group attendance summary';
$string['attendancepct'] = 'Attendance %';
$string['attendancesaved'] = 'Attendance saved successfully.';
$string['courseenddate'] = 'End date';
$string['coursestartdate'] = 'Start date';
$string['daterange'] = 'Course date range';
$string['dayofweek'] = 'Day of the week';
$string['delayratio'] = 'How many late arrivals equal 1 unjustified absence?';
$string['delayratio_help'] = 'Number of one-hour late arrivals needed to accumulate 1 unjustified absence hour. Example: 2 means it takes 2 late arrivals to count as 1 unjustified absence hour.';
$string['endtime'] = 'End time';
$string['equivalenthours'] = 'Equiv. absence hours';
$string['err_enddatebeforestart'] = 'The end date must be after the start date.';
$string['err_hourspositive'] = 'Total hours must be a positive number.';
$string['err_invalidratio'] = 'The value must be between 0 and 100.';
$string['eventattendancerecorded'] = 'Attendance recorded';
$string['eventcoursemodudeviewed'] = 'Attendance control viewed';
$string['eventsessioncreated'] = 'Session created';
$string['excel_col_date'] = 'Date';
$string['excel_col_duration'] = 'Duration (h)';
$string['excel_col_equivhours'] = 'Equiv. absence hours';
$string['excel_col_pct'] = 'Attendance %';
$string['excel_col_remarks'] = 'Remarks';
$string['excel_col_schedule'] = 'Schedule';
$string['excel_col_status'] = 'Status';
$string['excel_col_student'] = 'Student';
$string['excel_param_delayratio'] = 'Late arrival ratio';
$string['excel_param_group'] = 'Group';
$string['excel_param_justifiedratio'] = 'Justified absence ratio';
$string['excel_param_maxpct'] = 'Maximum allowed absence %';
$string['excel_param_subject'] = 'Subject';
$string['excel_param_totalhours'] = 'Total hours';
$string['excel_sheet_config'] = 'Configuration';
$string['excel_sheet_detail'] = 'Session detail';
$string['excel_sheet_summary'] = 'Summary';
$string['exportexcel'] = 'Export to Excel';
$string['group'] = 'Student group';
$string['group_help'] = 'Select the Moodle group whose members will form part of the attendance list.';
$string['holidaydate'] = 'Holiday date';
$string['holidaydescription'] = 'Description (optional)';
$string['holidays'] = 'Holidays';
$string['justifiedabsences'] = 'Justified absences';
$string['justifiedratio'] = 'How many justified absences equal 1 unjustified absence?';
$string['justifiedratio_help'] = 'Number of justified absence hours needed to accumulate 1 unjustified absence hour. Example: 2 means it takes 2 justified hours to count as 1 unjustified absence hour.';
$string['lates'] = 'Late arrivals';
$string['markallpresent'] = 'Mark all';
$string['maxunjustifiedpct'] = 'Maximum allowed absence percentage';
$string['maxunjustifiedpct_help'] = 'Maximum percentage of equivalent unjustified absence hours over total subject hours (1%–50%). Students above this threshold are highlighted in red.';
$string['modulename'] = 'Mister Asistencia';
$string['modulename_help'] = 'Mister Asistencia allows you to record daily student attendance, view summaries with percentages, and export data to Excel.';
$string['modulenameplural'] = 'Mister Asistencia';
$string['myattendance'] = 'My attendance';
$string['nextweek'] = 'Next week ►';
$string['nosessionsthisweek'] = 'No sessions scheduled for this week.';
$string['nostudentview'] = 'You do not have permission to view this activity.';
$string['penaltyconfig'] = 'Penalty configuration';
$string['pluginadministration'] = 'Mister Asistencia Administration';
$string['pluginname'] = 'Mister Asistencia';
$string['presences'] = 'Presences';
$string['previousweek'] = '◄ Previous week';
$string['privacy:metadata:attendancecontrol_record'] = 'Stores the attendance record for each student per session.';
$string['privacy:metadata:attendancecontrol_record:recorded_by'] = 'ID of the teacher who recorded the attendance.';
$string['privacy:metadata:attendancecontrol_record:remarks'] = 'Remarks entered by the teacher.';
$string['privacy:metadata:attendancecontrol_record:status'] = 'Attendance status (present, late, justified, unjustified).';
$string['privacy:metadata:attendancecontrol_record:timecreated'] = 'Timestamp of when the record was created.';
$string['privacy:metadata:attendancecontrol_record:timemodified'] = 'Timestamp of the last modification.';
$string['privacy:metadata:attendancecontrol_record:userid'] = 'The ID of the user (student) to whom the record belongs.';
$string['recordattendance'] = 'Record attendance';
$string['registerattendancetoday'] = 'Record today\'s attendance';
$string['remarks'] = 'Remarks';
$string['reporttitle'] = 'Attendance summary';
$string['saveattendance'] = 'Save attendance';
$string['schedule'] = 'Weekly time slots';
$string['sessionbreakdown'] = 'Session breakdown';
$string['sessiondate'] = 'Date';
$string['sessionheading'] = 'Session: {$a}';
$string['sessionschedule'] = 'Schedule';
$string['sessionstatus'] = 'Status';
$string['sessionsthisweek'] = 'Sessions this week';
$string['starttime'] = 'Start time';
$string['statusjustified'] = 'Justified absence';
$string['statuslate'] = 'Late';
$string['statuspending'] = 'Pending';
$string['statuspresent'] = 'Present';
$string['statusrecorded'] = 'Recorded';
$string['statusunjustified'] = 'Unjustified absence';
$string['studentdetailtitle'] = 'Attendance detail';
$string['studentname'] = 'Student';
$string['thresholdnotice'] = 'Configured threshold: {$a->threshold}% ({$a->pct}% maximum absences out of {$a->hours}h)';
$string['totalhours'] = 'Total subject hours';
$string['unjustifiedabsences'] = 'Unjustified absences';
$string['viewbreakdown'] = 'View session breakdown';
$string['viewfulldata'] = 'View full data';
