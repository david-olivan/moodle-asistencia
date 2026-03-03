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
 * Attendance percentage calculation logic.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\local;

/**
 * Computes attendance percentages and produces summary/detail data structures.
 *
 * Status codes:
 *   0 = unregistered (no penalty)
 *   1 = present      (no penalty)
 *   2 = late         (duration_hours × delay_to_unjustified_ratio)
 *   3 = justified    (duration_hours × justified_to_unjustified_ratio)
 *   4 = unjustified  (duration_hours × 1.0)
 */
class attendance_calculator
{
    /** @var \stdClass Plugin instance record. */
    protected \stdClass $instance;

    /**
     * Constructor.
     *
     * @param \stdClass $instance  Row from the attendancecontrol table.
     */
    public function __construct(\stdClass $instance) {
        $this->instance = $instance;
    }

    // Public API.

    /**
     * Returns one summary row per student in the configured group.
     *
     * Each element has:
     *   - student       \stdClass  Moodle user object (id, firstname, lastname).
     *   - presences     int
     *   - lates         int
     *   - justified     int
     *   - unjustified   int
     *   - equiv_hours   float      Equivalent absence hours.
     *   - pct           float      Attendance percentage (0–100).
     *   - below_threshold bool     True if pct < threshold.
     *
     * @return array
     */
    public function get_group_summary(): array {
        global $DB;

        $students = groups_get_members(
            $this->instance->groupid,
            'u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename'
        );

        $result = [];
        foreach ($students as $student) {
            $result[] = $this->build_student_summary($student);
        }

        // Sort alphabetically by lastname, firstname.
        usort(
            $result,
            static fn($a, $b) => strcmp(
                $a['student']->lastname . $a['student']->firstname,
                $b['student']->lastname . $b['student']->firstname
            )
        );

        return $result;
    }

    /**
     * Returns per-session detail for a single student.
     *
     * Each element has:
     *   - session   \stdClass  Session record (id, session_date, start_time, end_time, duration_hours, status).
     *   - record    \stdClass|null  Attendance record (status, remarks) or null.
     *
     * @param  int   $userid
     * @return array
     */
    public function get_student_detail(int $userid): array {
        global $DB;

        // Single LEFT JOIN query to avoid one get_record() call per session.
        $sql = 'SELECT s.id,
                       s.attendancecontrolid,
                       s.session_date,
                       s.start_time,
                       s.end_time,
                       s.duration_hours,
                       s.status        AS session_status,
                       s.timecreated   AS session_timecreated,
                       s.timemodified  AS session_timemodified,
                       r.id            AS record_id,
                       r.status        AS record_status,
                       r.remarks,
                       r.recorded_by,
                       r.timecreated   AS record_timecreated,
                       r.timemodified  AS record_timemodified
                  FROM {attendancecontrol_session} s
             LEFT JOIN {attendancecontrol_record} r
                    ON r.sessionid = s.id AND r.userid = :userid
                 WHERE s.attendancecontrolid = :instanceid
              ORDER BY s.session_date ASC, s.start_time ASC';

        $rows = $DB->get_records_sql($sql, ['userid' => $userid, 'instanceid' => $this->instance->id]);

        $result = [];
        foreach ($rows as $row) {
            $session = (object) [
                'id'                  => $row->id,
                'attendancecontrolid' => $row->attendancecontrolid,
                'session_date'        => $row->session_date,
                'start_time'          => $row->start_time,
                'end_time'            => $row->end_time,
                'duration_hours'      => $row->duration_hours,
                'status'              => $row->session_status,
                'timecreated'         => $row->session_timecreated,
                'timemodified'        => $row->session_timemodified,
            ];

            $record = null;
            if ($row->record_id !== null) {
                $record = (object) [
                    'id'           => $row->record_id,
                    'sessionid'    => $row->id,
                    'userid'       => $userid,
                    'status'       => $row->record_status,
                    'remarks'      => $row->remarks,
                    'recorded_by'  => $row->recorded_by,
                    'timecreated'  => $row->record_timecreated,
                    'timemodified' => $row->record_timemodified,
                ];
            }

            $result[] = ['session' => $session, 'record' => $record];
        }

        return $result;
    }

    // Core calculation.

    /**
     * Computes the equivalent absence hours for a student.
     *
     * @param  int    $userid
     * @return float
     */
    public function compute_equivalent_absence_hours(int $userid): float {
        global $DB;

        $sql = '
            SELECT r.id, r.status, s.duration_hours
              FROM {attendancecontrol_record} r
              JOIN {attendancecontrol_session} s ON s.id = r.sessionid
             WHERE r.userid = :userid
               AND s.attendancecontrolid = :instanceid
        ';

        $records = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'instanceid' => $this->instance->id,
        ]);

        $equiv = 0.0;
        foreach ($records as $rec) {
            $equiv += $this->status_to_equiv_hours((int) $rec->status, (int) $rec->duration_hours);
        }

        return $equiv;
    }

    /**
     * Computes the attendance percentage for a student.
     *
     *   pct = 100 − (equiv_hours / total_hours × 100)
     *
     * @param  int    $userid
     * @return float  Clamped to [0, 100].
     */
    public function compute_attendance_pct(int $userid): float {
        $total = (int) $this->instance->total_hours;

        if ($total <= 0) {
            return 100.0;
        }

        $equiv = $this->compute_equivalent_absence_hours($userid);
        $pct = 100.0 - ($equiv / $total * 100.0);

        return max(0.0, min(100.0, $pct));
    }

    /**
     * Returns the alert threshold (minimum required attendance percentage).
     *
     *   threshold = 100 − max_unjustified_absence_pct
     *
     * @return float
     */
    public function get_threshold(): float {
        return 100.0 - (float) $this->instance->max_unjustified_absence_pct;
    }

    // Private helpers.

    /**
     * Maps an attendance status to its equivalent absence hours.
     *
     * @param  int   $status
     * @param  int   $duration  Session duration in hours.
     * @return float
     */
    protected function status_to_equiv_hours(int $status, int $duration): float {
        return match ($status) {
            2 => $duration * (float) $this->instance->delay_to_unjustified_ratio,
            3 => $duration * (float) $this->instance->justified_to_unjustified_ratio,
            4 => (float) $duration,
            default => 0.0,
        };
    }

    /**
     * Builds a summary row for a single student.
     *
     * @param  \stdClass $student
     * @return array
     */
    protected function build_student_summary(\stdClass $student): array {
        global $DB;

        $sql = '
            SELECT r.status, COUNT(*) AS cnt, SUM(s.duration_hours) AS total_dur
              FROM {attendancecontrol_record} r
              JOIN {attendancecontrol_session} s ON s.id = r.sessionid
             WHERE r.userid = :userid
               AND s.attendancecontrolid = :instanceid
          GROUP BY r.status
        ';

        $rows = $DB->get_records_sql($sql, [
            'userid' => $student->id,
            'instanceid' => $this->instance->id,
        ]);

        $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        foreach ($rows as $row) {
            $counts[(int) $row->status] = (int) $row->cnt;
        }

        $equiv = $this->compute_equivalent_absence_hours($student->id);
        $pct = $this->compute_attendance_pct($student->id);
        $threshold = $this->get_threshold();

        return [
            'student' => $student,
            'presences' => $counts[1],
            'lates' => $counts[2],
            'justified' => $counts[3],
            'unjustified' => $counts[4],
            'equiv_hours' => round($equiv, 2),
            'pct' => round($pct, 2),
            'below_threshold' => $pct < $threshold,
        ];
    }
}
