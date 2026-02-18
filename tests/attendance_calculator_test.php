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
 * PHPUnit tests for attendance_calculator.
 *
 * @package    mod_attendancecontrol
 * @category   test
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol;

use mod_attendancecontrol\local\attendance_calculator;

/**
 * Unit tests for \mod_attendancecontrol\local\attendance_calculator.
 *
 * @coversDefaultClass \mod_attendancecontrol\local\attendance_calculator
 */
class attendance_calculator_test extends \advanced_testcase {

    // -----------------------------------------------------------------------
    // Helpers.
    // -----------------------------------------------------------------------

    /**
     * Builds a minimal instance stdClass for the calculator.
     *
     * @param  int   $total     Total hours.
     * @param  float $delayr    Delay ratio.
     * @param  float $justr     Justified ratio.
     * @param  float $maxpct    Max unjustified %.
     * @return \stdClass
     */
    private function make_instance(
        int $total = 100,
        float $delayr = 0.50,
        float $justr  = 0.50,
        float $maxpct = 15.00
    ): \stdClass {
        return (object) [
            'id'                              => 1,
            'total_hours'                     => $total,
            'delay_to_unjustified_ratio'      => $delayr,
            'justified_to_unjustified_ratio'  => $justr,
            'max_unjustified_absence_pct'     => $maxpct,
        ];
    }

    // -----------------------------------------------------------------------
    // get_threshold.
    // -----------------------------------------------------------------------

    /**
     * @covers ::get_threshold
     */
    public function test_get_threshold(): void {
        $calc = new attendance_calculator($this->make_instance(100, 0.5, 0.5, 15.0));
        $this->assertEqualsWithDelta(85.0, $calc->get_threshold(), 0.001);
    }

    // -----------------------------------------------------------------------
    // compute_equivalent_absence_hours & compute_attendance_pct (via DB).
    // -----------------------------------------------------------------------

    /**
     * Verifies the PRD example calculation:
     *   2 lates (1h each)  → 2 × 1 × 0.5 = 1h equiv
     *   1 justified (4h)   → 1 × 4 × 0.5 = 2h equiv
     *   1 unjustified (2h) → 1 × 2 × 1.0 = 2h equiv
     *   Total equiv: 5h → 95% attendance.
     *
     * @covers ::compute_equivalent_absence_hours
     * @covers ::compute_attendance_pct
     */
    public function test_prd_example_calculation(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course   = $this->getDataGenerator()->create_course();
        $student  = $this->getDataGenerator()->create_user();

        // Build instance.
        $instance = $this->make_instance(100, 0.5, 0.5, 15.0);
        $instance->course       = $course->id;
        $instance->name         = 'PRD test';
        $instance->intro        = '';
        $instance->introformat  = FORMAT_HTML;
        $instance->groupid      = 0;
        $instance->course_start_date           = mktime(0, 0, 0, 9, 1, 2025);
        $instance->course_end_date             = mktime(0, 0, 0, 6, 30, 2026);
        $instance->timecreated  = time();
        $instance->timemodified = time();

        $instance->id = $DB->insert_record('attendancecontrol', $instance);

        // Create sessions.
        $now = time();
        $s1id = $DB->insert_record('attendancecontrol_session', (object) [
            'attendancecontrolid' => $instance->id,
            'session_date'        => mktime(0, 0, 0, 9, 15, 2025),
            'start_time'          => '09:00',
            'end_time'            => '10:00',
            'duration_hours'      => 1, // 2 lates in 1h sessions.
            'status'              => 1,
            'timecreated'         => $now,
            'timemodified'        => $now,
        ]);
        $s2id = $DB->insert_record('attendancecontrol_session', (object) [
            'attendancecontrolid' => $instance->id,
            'session_date'        => mktime(0, 0, 0, 9, 16, 2025),
            'start_time'          => '09:00',
            'end_time'            => '10:00',
            'duration_hours'      => 1, // Second late.
            'status'              => 1,
            'timecreated'         => $now,
            'timemodified'        => $now,
        ]);
        $s3id = $DB->insert_record('attendancecontrol_session', (object) [
            'attendancecontrolid' => $instance->id,
            'session_date'        => mktime(0, 0, 0, 9, 17, 2025),
            'start_time'          => '09:00',
            'end_time'            => '13:00',
            'duration_hours'      => 4, // Justified absence.
            'status'              => 1,
            'timecreated'         => $now,
            'timemodified'        => $now,
        ]);
        $s4id = $DB->insert_record('attendancecontrol_session', (object) [
            'attendancecontrolid' => $instance->id,
            'session_date'        => mktime(0, 0, 0, 9, 18, 2025),
            'start_time'          => '09:00',
            'end_time'            => '11:00',
            'duration_hours'      => 2, // Unjustified absence.
            'status'              => 1,
            'timecreated'         => $now,
            'timemodified'        => $now,
        ]);

        // Create attendance records.
        foreach ([
            [$s1id, 2], // late.
            [$s2id, 2], // late.
            [$s3id, 3], // justified.
            [$s4id, 4], // unjustified.
        ] as [$sid, $status]) {
            $DB->insert_record('attendancecontrol_record', (object) [
                'sessionid'    => $sid,
                'userid'       => $student->id,
                'status'       => $status,
                'remarks'      => '',
                'recorded_by'  => 2,
                'timecreated'  => $now,
                'timemodified' => $now,
            ]);
        }

        $calc = new attendance_calculator($instance);

        $this->assertEqualsWithDelta(5.0, $calc->compute_equivalent_absence_hours($student->id), 0.001);
        $this->assertEqualsWithDelta(95.0, $calc->compute_attendance_pct($student->id), 0.001);
        $this->assertFalse(
            $calc->compute_attendance_pct($student->id) < $calc->get_threshold(),
            '95% should be above the 85% threshold'
        );
    }
}
