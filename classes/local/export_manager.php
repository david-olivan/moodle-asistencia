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
 * Excel export logic for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\local;

/**
 * Builds and streams a three-sheet Excel workbook to the browser.
 *
 * Requires Moodle's built-in MoodleExcelWorkbook (lib/excellib.class.php).
 */
class export_manager {
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

    /**
     * Streams the Excel file to the browser and exits.
     */
    public function send_excel(): void {
        global $CFG;

        require_once($CFG->libdir . '/excellib.class.php');

        $calculator = new attendance_calculator($this->instance);
        $summary    = $calculator->get_group_summary();

        $filename = clean_filename(
            get_string('modulename', 'mod_attendancecontrol') . '_' .
            userdate($this->instance->course_start_date, '%Y%m%d') . '.xls'
        );

        $workbook = new \MoodleExcelWorkbook('-');
        $workbook->send($filename);

        $this->build_summary_sheet($workbook, $summary);
        $this->build_detail_sheet($workbook, $summary);
        $this->build_config_sheet($workbook);

        $workbook->close();
        exit;
    }

    /**
     * Sheet 1 – One row per student with aggregated counts.
     *
     * @param \MoodleExcelWorkbook $wb
     * @param array                $summary
     */
    protected function build_summary_sheet(\MoodleExcelWorkbook $wb, array $summary): void {
        $sheet = $wb->add_worksheet(get_string('excel_sheet_summary', 'mod_attendancecontrol'));

        $headers = [
            get_string('excel_col_student', 'mod_attendancecontrol'),
            get_string('presences', 'mod_attendancecontrol'),
            get_string('lates', 'mod_attendancecontrol'),
            get_string('justifiedabsences', 'mod_attendancecontrol'),
            get_string('unjustifiedabsences', 'mod_attendancecontrol'),
            get_string('excel_col_equivhours', 'mod_attendancecontrol'),
            get_string('excel_col_pct', 'mod_attendancecontrol'),
        ];

        foreach ($headers as $col => $h) {
            $sheet->write_string(0, $col, $h);
        }

        foreach ($summary as $row => $data) {
            $r = $row + 1;
            $sheet->write_string($r, 0, fullname($data['student']));
            $sheet->write_number($r, 1, $data['presences']);
            $sheet->write_number($r, 2, $data['lates']);
            $sheet->write_number($r, 3, $data['justified']);
            $sheet->write_number($r, 4, $data['unjustified']);
            $sheet->write_number($r, 5, $data['equiv_hours']);
            $sheet->write_number($r, 6, $data['pct']);
        }
    }

    /**
     * Sheet 2 – One row per student × session.
     *
     * @param \MoodleExcelWorkbook $wb
     * @param array                $summary
     */
    protected function build_detail_sheet(\MoodleExcelWorkbook $wb, array $summary): void {
        global $DB;

        $sheet = $wb->add_worksheet(get_string('excel_sheet_detail', 'mod_attendancecontrol'));

        $headers = [
            get_string('excel_col_student', 'mod_attendancecontrol'),
            get_string('excel_col_date', 'mod_attendancecontrol'),
            get_string('excel_col_schedule', 'mod_attendancecontrol'),
            get_string('excel_col_duration', 'mod_attendancecontrol'),
            get_string('excel_col_status', 'mod_attendancecontrol'),
            get_string('excel_col_remarks', 'mod_attendancecontrol'),
        ];

        foreach ($headers as $col => $h) {
            $sheet->write_string(0, $col, $h);
        }

        $row = 1;
        $calculator = new attendance_calculator($this->instance);

        foreach ($summary as $data) {
            $detail = $calculator->get_student_detail((int) $data['student']->id);
            foreach ($detail as $item) {
                $statuslabel = $this->status_label((int) ($item['record']->status ?? 0));
                $sheet->write_string($row, 0, fullname($data['student']));
                $sheet->write_string($row, 1, userdate($item['session']->session_date, get_string('strftimedatefullshort')));
                $sheet->write_string($row, 2, $item['session']->start_time . '-' . $item['session']->end_time);
                $sheet->write_number($row, 3, $item['session']->duration_hours);
                $sheet->write_string($row, 4, $statuslabel);
                $sheet->write_string($row, 5, $item['record']->remarks ?? '');
                $row++;
            }
        }
    }

    /**
     * Sheet 3 – Configuration parameters.
     *
     * @param \MoodleExcelWorkbook $wb
     */
    protected function build_config_sheet(\MoodleExcelWorkbook $wb): void {
        global $DB;

        $sheet = $wb->add_worksheet(get_string('excel_sheet_config', 'mod_attendancecontrol'));
        $sheet->write_string(0, 0, get_string('excel_param_subject', 'mod_attendancecontrol'));
        $sheet->write_string(0, 1, format_string($this->instance->name));

        $group = $DB->get_record('groups', ['id' => $this->instance->groupid], 'name');
        $sheet->write_string(1, 0, get_string('excel_param_group', 'mod_attendancecontrol'));
        $sheet->write_string(1, 1, $group ? format_string($group->name) : '');

        $sheet->write_string(2, 0, get_string('excel_param_totalhours', 'mod_attendancecontrol'));
        $sheet->write_number(2, 1, $this->instance->total_hours);

        $sheet->write_string(3, 0, get_string('excel_param_delayratio', 'mod_attendancecontrol'));
        $sheet->write_number(3, 1, $this->instance->delay_to_unjustified_ratio);

        $sheet->write_string(4, 0, get_string('excel_param_justifiedratio', 'mod_attendancecontrol'));
        $sheet->write_number(4, 1, $this->instance->justified_to_unjustified_ratio);

        $sheet->write_string(5, 0, get_string('excel_param_maxpct', 'mod_attendancecontrol'));
        $sheet->write_number(5, 1, $this->instance->max_unjustified_absence_pct);
    }

    /**
     * Returns a localized label for an attendance status code.
     *
     * @param  int    $status
     * @return string
     */
    protected function status_label(int $status): string {
        return match ($status) {
            1 => get_string('statuspresent', 'mod_attendancecontrol'),
            2 => get_string('statuslate', 'mod_attendancecontrol'),
            3 => get_string('statusjustified', 'mod_attendancecontrol'),
            4 => get_string('statusunjustified', 'mod_attendancecontrol'),
            default => get_string('statuspending', 'mod_attendancecontrol'),
        };
    }
}
