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
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol;

use mod_attendancecontrol\local\session_manager;

/**
 * Unit tests for \mod_attendancecontrol\local\session_manager.
 *
 * @coversDefaultClass \mod_attendancecontrol\local\session_manager
 */
class session_manager_test extends \advanced_testcase {

    // -----------------------------------------------------------------------
    // compute_duration_hours – pure function, no DB needed.
    // -----------------------------------------------------------------------

    /**
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
            'exactly 1 hour'         => ['09:00', '10:00', 1],
            '55 minutes rounds to 1' => ['09:00', '09:55', 1],
            '1 hour 40 min → 2'      => ['09:00', '10:40', 2],
            '2 hours exactly'        => ['09:00', '11:00', 2],
            '30 minutes → 1'         => ['14:30', '15:00', 1],
            '2 hours 59 min → 3'     => ['09:00', '11:59', 3],
        ];
    }

    // -----------------------------------------------------------------------
    // generate_sessions – requires DB.
    // -----------------------------------------------------------------------

    /**
     * @covers ::generate_sessions
     */
    public function test_generate_sessions_excludes_holidays(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        // Create a simple instance record directly (no real Moodle install needed).
        $instance = (object) [
            'course'                       => $course->id,
            'name'                         => 'Test',
            'intro'                        => '',
            'introformat'                  => FORMAT_HTML,
            'groupid'                      => 0,
            'total_hours'                  => 100,
            'course_start_date'            => mktime(0, 0, 0, 9, 1, 2025),  // Mon 2025-09-01.
            'course_end_date'              => mktime(0, 0, 0, 9, 7, 2025),  // Sun 2025-09-07.
            'max_unjustified_absence_pct'  => 15.00,
            'delay_to_unjustified_ratio'   => 0.50,
            'justified_to_unjustified_ratio' => 0.50,
            'timecreated'                  => time(),
            'timemodified'                 => time(),
        ];

        $instance->id = $DB->insert_record('attendancecontrol', $instance);

        // Add a Monday slot (day_of_week = 1).
        $DB->insert_record('attendancecontrol_schedule', (object) [
            'attendancecontrolid' => $instance->id,
            'day_of_week'         => 1,
            'start_time'          => '09:00',
            'end_time'            => '11:00',
        ]);

        // Mark 2025-09-01 as a holiday.
        $DB->insert_record('attendancecontrol_holiday', (object) [
            'attendancecontrolid' => $instance->id,
            'holiday_date'        => mktime(0, 0, 0, 9, 1, 2025),
            'description'         => 'Festivo test',
        ]);

        $manager = new session_manager($instance);
        $manager->generate_sessions();

        // Week 2025-09-01..07 has one Monday (Sep 1) – but it is a holiday.
        // The next Monday would be Sep 8, which is outside the range.
        $count = $DB->count_records('attendancecontrol_session', ['attendancecontrolid' => $instance->id]);
        $this->assertSame(0, $count, 'Holiday on Monday should result in zero sessions for this week.');
    }

    /**
     * @covers ::generate_sessions
     */
    public function test_generate_sessions_creates_correct_count(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        // Range: Mon 2025-09-01 to Fri 2025-09-05.
        $instance = (object) [
            'course'                          => $course->id,
            'name'                            => 'Test2',
            'intro'                           => '',
            'introformat'                     => FORMAT_HTML,
            'groupid'                         => 0,
            'total_hours'                     => 50,
            'course_start_date'               => mktime(0, 0, 0, 9, 1, 2025),
            'course_end_date'                 => mktime(0, 0, 0, 9, 5, 2025),
            'max_unjustified_absence_pct'     => 15.00,
            'delay_to_unjustified_ratio'      => 0.50,
            'justified_to_unjustified_ratio'  => 0.50,
            'timecreated'                     => time(),
            'timemodified'                    => time(),
        ];

        $instance->id = $DB->insert_record('attendancecontrol', $instance);

        // Two slots: Mon + Wed.
        $DB->insert_record('attendancecontrol_schedule', (object) [
            'attendancecontrolid' => $instance->id,
            'day_of_week'         => 1,
            'start_time'          => '09:00',
            'end_time'            => '11:00',
        ]);
        $DB->insert_record('attendancecontrol_schedule', (object) [
            'attendancecontrolid' => $instance->id,
            'day_of_week'         => 3,
            'start_time'          => '09:00',
            'end_time'            => '11:00',
        ]);

        $manager = new session_manager($instance);
        $manager->generate_sessions();

        // Sep 1 = Mon, Sep 3 = Wed → 2 sessions.
        $count = $DB->count_records('attendancecontrol_session', ['attendancecontrolid' => $instance->id]);
        $this->assertSame(2, $count);
    }
}
