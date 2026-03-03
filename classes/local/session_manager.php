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
 * Session generation and management logic.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\local;

/**
 * Handles session generation, regeneration and attendance record persistence.
 */
class session_manager
{
    /** @var \stdClass Plugin instance record. */
    protected \stdClass $instance;

    /**
     * Constructor.
     *
     * @param \stdClass $instance  Row from the attendancecontrol table.
     */
    public function __construct(\stdClass $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Generates all sessions for the full date range.
     *
     * Called when a new instance is created.
     */
    public function generate_sessions(): void
    {
        global $DB;

        $slots = $DB->get_records('attendancecontrol_schedule', ['attendancecontrolid' => $this->instance->id]);
        $holidays = $this->get_holiday_timestamps();

        $start = (int) $this->instance->course_start_date;
        $end = (int) $this->instance->course_end_date;

        $now = time();

        for ($ts = $start; $ts <= $end; $ts = strtotime('+1 day', $ts)) {
            // ISO day of week (1=Mon … 7=Sun).
            $dow = (int) date('N', $ts);

            // Skip holidays.
            if (in_array($ts, $holidays, true)) {
                continue;
            }

            foreach ($slots as $slot) {
                if ((int) $slot->day_of_week !== $dow) {
                    continue;
                }

                $duration = self::compute_duration_hours($slot->start_time, $slot->end_time);

                $session = (object) [
                    'attendancecontrolid' => $this->instance->id,
                    'session_date' => $ts,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'duration_hours' => $duration,
                    'status' => 0,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ];

                $DB->insert_record('attendancecontrol_session', $session);
            }
        }
    }

    /**
     * Regenerates future sessions that have no attendance records yet.
     *
     * Called when an instance is updated. Preserves past sessions and any
     * future session that already has records attached.
     */
    public function regenerate_future_sessions(): void
    {
        global $DB;

        $today = mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y'));

        // Collect future session IDs with records.
        $futuresessions = $DB->get_records_select(
            'attendancecontrol_session',
            'attendancecontrolid = :id AND session_date >= :today',
            ['id' => $this->instance->id, 'today' => $today],
            '',
            'id'
        );

        $sessionswithrec = [];
        foreach ($futuresessions as $s) {
            if ($DB->record_exists('attendancecontrol_record', ['sessionid' => $s->id])) {
                $sessionswithrec[] = $s->id;
            }
        }

        // Delete future sessions without records.
        $allids = array_keys($futuresessions);
        $deletable = array_diff($allids, $sessionswithrec);

        if ($deletable) {
            [$insql, $inparams] = $DB->get_in_or_equal($deletable);
            $DB->delete_records_select('attendancecontrol_session', "id $insql", $inparams);
        }

        // Regenerate from today.
        $slots = $DB->get_records('attendancecontrol_schedule', ['attendancecontrolid' => $this->instance->id]);
        $holidays = $this->get_holiday_timestamps();
        $end = (int) $this->instance->course_end_date;
        $now = time();

        for ($ts = $today; $ts <= $end; $ts = strtotime('+1 day', $ts)) {
            $dow = (int) date('N', $ts);

            if (in_array($ts, $holidays, true)) {
                continue;
            }

            foreach ($slots as $slot) {
                if ((int) $slot->day_of_week !== $dow) {
                    continue;
                }

                // Skip if a session already exists for this date+time (kept because it has records).
                if (
                    $DB->record_exists('attendancecontrol_session', [
                        'attendancecontrolid' => $this->instance->id,
                        'session_date' => $ts,
                        'start_time' => $slot->start_time,
                    ])
                ) {
                    continue;
                }

                $duration = self::compute_duration_hours($slot->start_time, $slot->end_time);

                $session = (object) [
                    'attendancecontrolid' => $this->instance->id,
                    'session_date' => $ts,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'duration_hours' => $duration,
                    'status' => 0,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ];

                $DB->insert_record('attendancecontrol_session', $session);
            }
        }
    }

    /**
     * Saves (upserts) attendance records submitted from attendance_form.
     *
     * @param \stdClass $session  Session record.
     * @param \stdClass $data     Form submission data.
     */
    public function save_attendance_records(\stdClass $session, \stdClass $data): void
    {
        global $DB, $USER;

        if (empty($data->student_status)) {
            return;
        }

        $now = time();

        foreach ($data->student_status as $userid => $status) {
            $remarks = $data->student_remarks[$userid] ?? '';

            $existing = $DB->get_record('attendancecontrol_record', [
                'sessionid' => $session->id,
                'userid' => $userid,
            ]);

            if ($existing) {
                $existing->status = (int) $status;
                $existing->remarks = $remarks;
                $existing->recorded_by = (int) $USER->id;
                $existing->timemodified = $now;
                $DB->update_record('attendancecontrol_record', $existing);
            } else {
                $record = (object) [
                    'sessionid' => $session->id,
                    'userid' => (int) $userid,
                    'status' => (int) $status,
                    'remarks' => $remarks,
                    'recorded_by' => (int) $USER->id,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ];
                $DB->insert_record('attendancecontrol_record', $record);
            }
        }

        // Mark session as recorded.
        $DB->set_field('attendancecontrol_session', 'status', 1, ['id' => $session->id]);
        $DB->set_field('attendancecontrol_session', 'timemodified', $now, ['id' => $session->id]);
    }

    /**
     * Returns an array of holiday timestamps (midnight) for this instance.
     *
     * @return int[]
     */
    protected function get_holiday_timestamps(): array
    {
        global $DB;

        $rows = $DB->get_records(
            'attendancecontrol_holiday',
            ['attendancecontrolid' => $this->instance->id],
            '',
            'holiday_date'
        );

        return array_column($rows, 'holiday_date');
    }

    /**
     * Computes the session duration in whole hours (ceiling).
     *
     * @param  string $start  'HH:MM'
     * @param  string $end    'HH:MM'
     * @return int
     */
    public static function compute_duration_hours(string $start, string $end): int
    {
        [$sh, $sm] = array_map('intval', explode(':', $start));
        [$eh, $em] = array_map('intval', explode(':', $end));

        $totalminutes = ($eh * 60 + $em) - ($sh * 60 + $sm);

        if ($totalminutes <= 0) {
            return 0;
        }

        return (int) ceil($totalminutes / 60);
    }
}
