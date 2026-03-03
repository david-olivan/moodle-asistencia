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
 * PHPUnit tests for session_manager.
 *
 * @package    mod_attendancecontrol
 * @category   test
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol;

use mod_attendancecontrol\local\session_manager;

/**
 * Unit tests for \mod_attendancecontrol\local\session_manager.
 *
 * @coversDefaultClass \mod_attendancecontrol\local\session_manager
 */
final class session_manager_test extends \advanced_testcase
{
    /**
     * Verifies duration calculation rounds up to the nearest whole hour.
     *
     * @param string $start    Start time in HH:MM format.
     * @param string $end      End time in HH:MM format.
     * @param int    $expected Expected duration in hours (ceiling).
     * @covers ::compute_duration_hours
     * @dataProvider duration_provider
     */
    public function test_compute_duration_hours(string $start, string $end, int $expected): void {
        $result = session_manager::compute_duration_hours($start, $end);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_compute_duration_hours.
     *
     * @return array[]
     */
    public static function duration_provider(): array {
        return [
            'exactly 1 hour' => ['09:00', '10:00', 1],
            '55 minutes rounds to 1' => ['09:00', '09:55', 1],
            '1 hour 40 min → 2' => ['09:00', '10:40', 2],
            '2 hours exactly' => ['09:00', '11:00', 2],
            '30 minutes → 1' => ['14:30', '15:00', 1],
            '2 hours 59 min → 3' => ['09:00', '11:59', 3],
        ];
    }

    /**
     * Verifies that sessions on holidays are skipped during generation.
     *
     * @covers ::generate_sessions
     */
    public function test_generate_sessions_excludes_holidays(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        // Create a simple instance record directly (no real Moodle install needed).
        $instance = (object) [
            'course' => $course->id,
            'name' => 'Test',
            'intro' => '',
            'introformat' => FORMAT_HTML,
            'groupid' => 0,
            'total_hours' => 100,
            'course_start_date' => mktime(0, 0, 0, 9, 1, 2025), // Mon 2025-09-01.
            'course_end_date' => mktime(0, 0, 0, 9, 7, 2025), // Sun 2025-09-07.
            'max_unjustified_absence_pct' => 15.00,
            'delay_to_unjustified_ratio' => 0.50,
            'justified_to_unjustified_ratio' => 0.50,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        $instance->id = $DB->insert_record('attendancecontrol', $instance);

        // Add a Monday slot (day_of_week = 1).
        $DB->insert_record('attendancecontrol_schedule', (object) [
            'attendancecontrolid' => $instance->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        // Mark 2025-09-01 as a holiday.
        $DB->insert_record('attendancecontrol_holiday', (object) [
            'attendancecontrolid' => $instance->id,
            'holiday_date' => mktime(0, 0, 0, 9, 1, 2025),
            'description' => 'Festivo test',
        ]);

        $manager = new session_manager($instance);
        $manager->generate_sessions();

        // Week 2025-09-01..07 has one Monday (Sep 1) – but it is a holiday.
        // The next Monday would be Sep 8, which is outside the range.
        $count = $DB->count_records('attendancecontrol_session', ['attendancecontrolid' => $instance->id]);
        $this->assertSame(0, $count, 'Holiday on Monday should result in zero sessions for this week.');
    }

    /**
     * Verifies that the correct number of sessions is created for a date range.
     *
     * @covers ::generate_sessions
     */
    public function test_generate_sessions_creates_correct_count(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        // Range: Mon 2025-09-01 to Fri 2025-09-05.
        $instance = (object) [
            'course' => $course->id,
            'name' => 'Test2',
            'intro' => '',
            'introformat' => FORMAT_HTML,
            'groupid' => 0,
            'total_hours' => 50,
            'course_start_date' => mktime(0, 0, 0, 9, 1, 2025),
            'course_end_date' => mktime(0, 0, 0, 9, 5, 2025),
            'max_unjustified_absence_pct' => 15.00,
            'delay_to_unjustified_ratio' => 0.50,
            'justified_to_unjustified_ratio' => 0.50,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        $instance->id = $DB->insert_record('attendancecontrol', $instance);

        // Two slots: Mon + Wed.
        $DB->insert_record('attendancecontrol_schedule', (object) [
            'attendancecontrolid' => $instance->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);
        $DB->insert_record('attendancecontrol_schedule', (object) [
            'attendancecontrolid' => $instance->id,
            'day_of_week' => 3,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $manager = new session_manager($instance);
        $manager->generate_sessions();

        // Sep 1 = Mon, Sep 3 = Wed → 2 sessions.
        $count = $DB->count_records('attendancecontrol_session', ['attendancecontrolid' => $instance->id]);
        $this->assertSame(2, $count);
    }

    /**
     * Builds and inserts a minimal instance record; returns it with ->id set.
     *
     * @param  int $courseid
     * @return \stdClass
     */
    private function make_instance_record(int $courseid): \stdClass {
        global $DB;

        $instance = (object) [
            'course' => $courseid,
            'name' => 'Test',
            'intro' => '',
            'introformat' => FORMAT_HTML,
            'groupid' => 0,
            'total_hours' => 100,
            'course_start_date' => mktime(0, 0, 0, 9, 1, 2025),
            'course_end_date' => mktime(0, 0, 0, 6, 30, 2030),
            'max_unjustified_absence_pct' => 15.00,
            'delay_to_unjustified_ratio' => 0.50,
            'justified_to_unjustified_ratio' => 0.50,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        $instance->id = $DB->insert_record('attendancecontrol', $instance);

        return $instance;
    }

    /**
     * Inserts a session row and returns the full record.
     *
     * @param  int $instanceid
     * @param  int $timestamp  Midnight Unix timestamp for the session date.
     * @return \stdClass
     */
    private function make_session_record(int $instanceid, int $timestamp): \stdClass {
        global $DB;

        $now = time();
        $id = $DB->insert_record('attendancecontrol_session', (object) [
            'attendancecontrolid' => $instanceid,
            'session_date' => $timestamp,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'duration_hours' => 2,
            'status' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        return $DB->get_record('attendancecontrol_session', ['id' => $id]);
    }

    /**
     * AC 11.2.5 — Saving attendance creates a persisted record.
     *
     * @covers ::save_attendance_records
     */
    public function test_save_attendance_records_inserts_new(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $instance = $this->make_instance_record($course->id);
        $session = $this->make_session_record($instance->id, mktime(0, 0, 0, 9, 15, 2025));

        $data = (object) [
            'student_status' => [$student->id => 1],
            'student_remarks' => [$student->id => 'On time'],
        ];

        $manager = new session_manager($instance);
        $manager->save_attendance_records($session, $data);

        $record = $DB->get_record(
            'attendancecontrol_record',
            ['sessionid' => $session->id, 'userid' => $student->id]
        );

        $this->assertNotFalse($record, 'Attendance record should have been created.');
        $this->assertSame(1, (int) $record->status, 'Status should be present (1).');
        $this->assertSame('On time', $record->remarks);

        // Session should also be marked as recorded (status = 1).
        $updated = $DB->get_field('attendancecontrol_session', 'status', ['id' => $session->id]);
        $this->assertSame(1, (int) $updated, 'Session status should be set to recorded (1).');
    }

    /**
     * AC 11.2.6 — Saving attendance a second time updates, not duplicates.
     *
     * @covers ::save_attendance_records
     */
    public function test_save_attendance_records_updates_existing(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $instance = $this->make_instance_record($course->id);
        $session = $this->make_session_record($instance->id, mktime(0, 0, 0, 9, 15, 2025));

        $manager = new session_manager($instance);

        // First save: present.
        $manager->save_attendance_records($session, (object) [
            'student_status' => [$student->id => 1],
            'student_remarks' => [$student->id => 'Initial save'],
        ]);

        // Second save: correct to unjustified.
        $manager->save_attendance_records($session, (object) [
            'student_status' => [$student->id => 4],
            'student_remarks' => [$student->id => 'Corrected to unjustified'],
        ]);

        $records = $DB->get_records(
            'attendancecontrol_record',
            ['sessionid' => $session->id, 'userid' => $student->id]
        );

        $this->assertCount(1, $records, 'Only one record should exist after two saves.');
        $rec = reset($records);
        $this->assertSame(4, (int) $rec->status, 'Status should reflect the second save (unjustified = 4).');
        $this->assertSame('Corrected to unjustified', $rec->remarks);
    }

    /**
     * AC 11.2.7 — Retroactive recording: records can be saved for past sessions.
     *
     * @covers ::save_attendance_records
     */
    public function test_save_attendance_records_retroactive(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $instance = $this->make_instance_record($course->id);

        // Session firmly in the past (2025-01-10).
        $session = $this->make_session_record($instance->id, mktime(0, 0, 0, 1, 10, 2025));

        $manager = new session_manager($instance);
        $manager->save_attendance_records($session, (object) [
            'student_status' => [$student->id => 3],
            'student_remarks' => [$student->id => 'Retroactive justified'],
        ]);

        $record = $DB->get_record(
            'attendancecontrol_record',
            ['sessionid' => $session->id, 'userid' => $student->id]
        );

        $this->assertNotFalse($record, 'Retroactive record should have been created.');
        $this->assertSame(3, (int) $record->status, 'Status should be justified (3).');
    }

    /**
     * AC 11.6.3 — regenerate_future_sessions preserves future sessions
     * that already have attendance records attached.
     *
     * @covers ::regenerate_future_sessions
     */
    public function test_regenerate_future_sessions_preserves_sessions_with_records(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $instance = $this->make_instance_record($course->id);

        // A session well in the future (2028-06-05, a Monday) with an attendance record.
        $future_sess_id = $DB->insert_record('attendancecontrol_session', (object) [
            'attendancecontrolid' => $instance->id,
            'session_date' => mktime(0, 0, 0, 6, 5, 2028),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'duration_hours' => 2,
            'status' => 1,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $now = time();
        $DB->insert_record('attendancecontrol_record', (object) [
            'sessionid' => $future_sess_id,
            'userid' => $student->id,
            'status' => 1,
            'remarks' => '',
            'recorded_by' => 2,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        // No schedule slots → regeneration adds nothing new but must not delete the recorded session.
        $manager = new session_manager($instance);
        $manager->regenerate_future_sessions();

        $this->assertTrue(
            $DB->record_exists('attendancecontrol_session', ['id' => $future_sess_id]),
            'Future session that has attendance records must not be deleted during regeneration.'
        );
    }
}
