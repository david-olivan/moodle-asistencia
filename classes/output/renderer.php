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
 * Plugin renderer – delegates HTML generation to Mustache templates.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\output;

use mod_attendancecontrol\local\attendance_calculator;
use moodle_url;
use plugin_renderer_base;

/**
 * Renderer for mod_attendancecontrol.
 *
 * All public methods return HTML strings produced via Mustache templates.
 */
class renderer extends plugin_renderer_base {

    // -----------------------------------------------------------------------
    // Teacher views.
    // -----------------------------------------------------------------------

    /**
     * Renders the teacher main view (session list + action buttons).
     *
     * @param  \stdClass          $instance
     * @param  \cm_info|\stdClass $cm
     * @param  \context_module    $context
     * @return string  HTML
     */
    public function render_teacher_view(\stdClass $instance, $cm, \context_module $context): string {
        global $DB;

        // Week navigation parameter.
        $weekoffset = optional_param('week', 0, PARAM_INT);
        $monday     = strtotime('monday this week', strtotime("+{$weekoffset} weeks"));
        $sunday     = strtotime('+6 days', $monday);

        $sessions = $DB->get_records_select(
            'attendancecontrol_session',
            'attendancecontrolid = :id AND session_date >= :start AND session_date <= :end',
            ['id' => $instance->id, 'start' => $monday, 'end' => $sunday],
            'session_date ASC, start_time ASC'
        );

        $today = mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y'));

        // Find today's first session (if any) for the direct "Register today" shortcut.
        $todaysessions = $DB->get_records_select(
            'attendancecontrol_session',
            'attendancecontrolid = :id AND session_date = :today',
            ['id' => $instance->id, 'today' => $today],
            'start_time ASC',
            'id',
            0,
            1
        );
        $todaysession = !empty($todaysessions) ? reset($todaysessions) : null;

        $sessiondata = [];
        foreach ($sessions as $s) {
            $sessiondata[] = [
                'id'           => $s->id,
                'cmid'         => $cm->id,
                'date_label'   => userdate($s->session_date, get_string('strftimedaydate')),
                'schedule'     => $s->start_time . '–' . $s->end_time,
                'is_today'     => ((int) $s->session_date === $today),
                'is_recorded'  => ((int) $s->status === 1),
                'status_label' => ((int) $s->status === 1)
                    ? get_string('statusrecorded', 'mod_attendancecontrol')
                    : get_string('statuspending', 'mod_attendancecontrol'),
                'url_record'   => (new moodle_url('/mod/attendancecontrol/attendance.php',
                    ['id' => $cm->id, 'sessionid' => $s->id]))->out(false),
            ];
        }

        $data = [
            'cmid'         => $cm->id,
            'url_today'    => $todaysession
                ? (new moodle_url('/mod/attendancecontrol/attendance.php',
                    ['id' => $cm->id, 'sessionid' => $todaysession->id]))->out(false)
                : '',
            'url_report'   => (new moodle_url('/mod/attendancecontrol/report.php',    ['id' => $cm->id]))->out(false),
            'url_prevweek' => (new moodle_url('/mod/attendancecontrol/view.php',
                ['id' => $cm->id, 'week' => $weekoffset - 1]))->out(false),
            'url_nextweek' => (new moodle_url('/mod/attendancecontrol/view.php',
                ['id' => $cm->id, 'week' => $weekoffset + 1]))->out(false),
            'week_label'   => userdate($monday, get_string('strftimedatefullshort')) . ' – ' .
                              userdate($sunday, get_string('strftimedatefullshort')),
            'sessions'     => array_values($sessiondata),
            'hassessions'  => !empty($sessiondata),
            'str_register' => get_string('registerattendancetoday', 'mod_attendancecontrol'),
            'str_report'   => get_string('viewfulldata', 'mod_attendancecontrol'),
            'str_prev'     => get_string('previousweek', 'mod_attendancecontrol'),
            'str_next'     => get_string('nextweek', 'mod_attendancecontrol'),
        ];

        return $this->render_from_template('mod_attendancecontrol/session_list', $data);
    }

    /**
     * Renders the summary table (report.php).
     *
     * @param  array              $summary   Output of attendance_calculator::get_group_summary().
     * @param  \stdClass          $instance
     * @param  \cm_info|\stdClass $cm
     * @return string  HTML
     */
    public function render_summary_table(array $summary, \stdClass $instance, $cm): string {
        $calculator = new attendance_calculator($instance);
        $threshold  = $calculator->get_threshold();

        $rows = [];
        foreach ($summary as $data) {
            $rows[] = [
                'student_name'     => fullname($data['student']),
                'userid'           => $data['student']->id,
                'url_detail'       => (new moodle_url('/mod/attendancecontrol/student_detail.php',
                    ['id' => $cm->id, 'userid' => $data['student']->id]))->out(false),
                'presences'        => $data['presences'],
                'lates'            => $data['lates'],
                'justified'        => $data['justified'],
                'unjustified'      => $data['unjustified'],
                'equiv_hours'      => number_format($data['equiv_hours'], 1),
                'pct'              => number_format($data['pct'], 1),
                'below_threshold'  => $data['below_threshold'],
            ];
        }

        $data = [
            'rows'           => $rows,
            'url_export'     => (new moodle_url('/mod/attendancecontrol/export.php', ['id' => $cm->id]))->out(false),
            'threshold_notice' => get_string('thresholdnotice', 'mod_attendancecontrol', (object) [
                'threshold' => number_format($threshold, 1),
                'pct'       => number_format((float) $instance->max_unjustified_absence_pct, 1),
                'hours'     => $instance->total_hours,
            ]),
            'str_export'     => get_string('exportexcel', 'mod_attendancecontrol'),
        ];

        return $this->render_from_template('mod_attendancecontrol/summary_table', $data);
    }

    /**
     * Renders the per-student session-by-session detail view.
     *
     * @param  array     $detail   Output of attendance_calculator::get_student_detail().
     * @param  \stdClass $instance
     * @param  \stdClass $student  Moodle user object.
     * @return string  HTML
     */
    public function render_student_detail(array $detail, \stdClass $instance, \stdClass $student): string {
        $calculator = new attendance_calculator($instance);
        $pct        = $calculator->compute_attendance_pct((int) $student->id);
        $threshold  = $calculator->get_threshold();

        $rows = [];
        foreach ($detail as $item) {
            $statuskey = match ((int) ($item['record']->status ?? 0)) {
                1 => 'statuspresent',
                2 => 'statuslate',
                3 => 'statusjustified',
                4 => 'statusunjustified',
                default => 'statuspending',
            };
            $rows[] = [
                'date'         => userdate($item['session']->session_date, get_string('strftimedaydate')),
                'schedule'     => $item['session']->start_time . '–' . $item['session']->end_time,
                'duration'     => $item['session']->duration_hours,
                'status_label' => get_string($statuskey, 'mod_attendancecontrol'),
                'status_code'  => (int) ($item['record']->status ?? 0),
                'remarks'      => $item['record']->remarks ?? '',
            ];
        }

        $data = [
            'student_name'    => fullname($student),
            'pct'             => number_format($pct, 1),
            'below_threshold' => $pct < $threshold,
            'rows'            => $rows,
        ];

        return $this->render_from_template('mod_attendancecontrol/student_detail', $data);
    }

    // -----------------------------------------------------------------------
    // Student view.
    // -----------------------------------------------------------------------

    /**
     * Renders the student's personal attendance summary.
     *
     * @param  \stdClass          $instance
     * @param  \cm_info|\stdClass $cm
     * @param  \context_module    $context
     * @return string  HTML
     */
    public function render_student_view(\stdClass $instance, $cm, \context_module $context): string {
        global $USER;

        $calculator = new attendance_calculator($instance);
        $summary    = null;

        // Find this student's summary row.
        foreach ($calculator->get_group_summary() as $row) {
            if ((int) $row['student']->id === (int) $USER->id) {
                $summary = $row;
                break;
            }
        }

        if ($summary === null) {
            return $this->output->notification(get_string('nostudentview', 'mod_attendancecontrol'));
        }

        $threshold = $calculator->get_threshold();

        $data = [
            'presences'       => $summary['presences'],
            'lates'           => $summary['lates'],
            'justified'       => $summary['justified'],
            'unjustified'     => $summary['unjustified'],
            'pct'             => number_format($summary['pct'], 1),
            'below_threshold' => $summary['below_threshold'],
            'url_breakdown'   => (new moodle_url('/mod/attendancecontrol/student_detail.php',
                ['id' => $cm->id, 'userid' => $USER->id]))->out(false),
            'str_breakdown'   => get_string('viewbreakdown', 'mod_attendancecontrol'),
        ];

        return $this->render_from_template('mod_attendancecontrol/student_summary', $data);
    }
}
