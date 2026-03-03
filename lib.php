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
 * Standard Moodle module callbacks for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds a new instance of attendancecontrol.
 *
 * Called by the course module form when a new instance is saved for the
 * first time. Generates all sessions automatically.
 *
 * @param  stdClass $data  Form data from mod_form.php.
 * @return int             New instance ID.
 */
function attendancecontrol_add_instance(stdClass $data): int {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    // Convert integer N (from select) → stored float ratio 1/N.
    attendancecontrol_convert_ratios($data);

    // Persist base record.
    $instanceid = $DB->insert_record('attendancecontrol', $data);
    $data->id = $instanceid;

    // Persist schedule slots submitted via the repeatable element.
    attendancecontrol_save_schedule($data);

    // Persist holiday dates.
    attendancecontrol_save_holidays($data);

    // Auto-generate sessions.
    $manager = new \mod_attendancecontrol\local\session_manager($data);
    $manager->generate_sessions();

    return $instanceid;
}

/**
 * Updates an existing instance of attendancecontrol.
 *
 * Regenerates future sessions (without attendance records) when the
 * schedule or date range changes.
 *
 * @param  stdClass $data  Form data from mod_form.php.
 * @return bool
 */
function attendancecontrol_update_instance(stdClass $data): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();

    // Convert integer N (from select) → stored float ratio 1/N.
    attendancecontrol_convert_ratios($data);

    $DB->update_record('attendancecontrol', $data);

    // Re-save schedule (delete old, insert new).
    $DB->delete_records('attendancecontrol_schedule', ['attendancecontrolid' => $data->id]);
    attendancecontrol_save_schedule($data);

    // Re-save holidays (delete old, insert new).
    $DB->delete_records('attendancecontrol_holiday', ['attendancecontrolid' => $data->id]);
    attendancecontrol_save_holidays($data);

    // Regenerate future sessions without attendance records.
    $manager = new \mod_attendancecontrol\local\session_manager($data);
    $manager->regenerate_future_sessions();

    return true;
}

/**
 * Deletes an instance of attendancecontrol and all associated data.
 *
 * @param  int $id  Module instance ID.
 * @return bool
 */
function attendancecontrol_delete_instance(int $id): bool {
    global $DB;

    if (!$DB->get_record('attendancecontrol', ['id' => $id])) {
        return false;
    }

    // Delete records, sessions, schedule, holidays, then the instance.
    $sessionids = $DB->get_fieldset_select(
        'attendancecontrol_session',
        'id',
        'attendancecontrolid = :id',
        ['id' => $id]
    );

    if ($sessionids) {
        [$insql, $inparams] = $DB->get_in_or_equal($sessionids);
        $DB->delete_records_select('attendancecontrol_record', "sessionid $insql", $inparams);
    }

    $DB->delete_records('attendancecontrol_session', ['attendancecontrolid' => $id]);
    $DB->delete_records('attendancecontrol_schedule', ['attendancecontrolid' => $id]);
    $DB->delete_records('attendancecontrol_holiday', ['attendancecontrolid' => $id]);
    $DB->delete_records('attendancecontrol', ['id' => $id]);

    return true;
}

/**
 * Returns the features this module supports.
 *
 * @param  string $feature  FEATURE_* constant.
 * @return bool|null        True/false or null when unknown.
 */
function attendancecontrol_supports(string $feature): ?bool {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COLLABORATION;
        default:
            return null;
    }
}

/**
 * Persists schedule slots submitted by the dynamic JS table.
 *
 * Reads directly from $_POST using optional_param_array() because the form
 * inputs are injected by AMD JavaScript and are not registered with the
 * Moodle form engine (so they are absent from the $data object).
 *
 * @param stdClass $data  Instance data (only $data->id is used).
 */
function attendancecontrol_save_schedule(stdClass $data): void {
    global $DB;

    $days = optional_param_array('schedule_day', [], PARAM_INT);
    $starts = optional_param_array('schedule_start', [], PARAM_TEXT);
    $ends = optional_param_array('schedule_end', [], PARAM_TEXT);

    foreach ($days as $i => $day) {
        if (empty($day)) {
            continue;
        }
        $DB->insert_record('attendancecontrol_schedule', (object) [
            'attendancecontrolid' => $data->id,
            'day_of_week' => (int) $day,
            'start_time' => clean_param($starts[$i] ?? '', PARAM_TEXT),
            'end_time' => clean_param($ends[$i] ?? '', PARAM_TEXT),
        ]);
    }
}

/**
 * Persists holiday dates submitted by the dynamic JS table.
 *
 * Dates arrive as YYYY-MM-DD strings from <input type="date"> and are
 * converted to midnight Unix timestamps before storage.
 *
 * @param stdClass $data  Instance data (only $data->id is used).
 */
function attendancecontrol_save_holidays(stdClass $data): void {
    global $DB;

    $dates = optional_param_array('holiday_date', [], PARAM_TEXT);
    $descs = optional_param_array('holiday_description', [], PARAM_TEXT);

    foreach ($dates as $i => $date_str) {
        if (empty($date_str)) {
            continue;
        }
        // Convert YYYY-MM-DD to midnight Unix timestamp.
        $timestamp = strtotime($date_str . ' 00:00:00');
        if ($timestamp === false || $timestamp <= 0) {
            continue;
        }
        $DB->insert_record('attendancecontrol_holiday', (object) [
            'attendancecontrolid' => $data->id,
            'holiday_date' => $timestamp,
            'description' => clean_param($descs[$i] ?? '', PARAM_TEXT),
        ]);
    }
}

/**
 * Converts the integer N values selected in the form to the float ratios
 * stored in the database.
 *
 * The form presents "how many X equal 1 unjustified absence?" as an integer
 * (N = 1…10).  The calculator expects a multiplier stored as 1/N.
 * max_unjustified_absence_pct is already an integer (1–50) and needs no
 * conversion – the NUMBER column accepts integers directly.
 *
 * @param stdClass $data  Form data modified in place.
 */
function attendancecontrol_convert_ratios(stdClass $data): void {
    foreach (['delay_to_unjustified_ratio', 'justified_to_unjustified_ratio'] as $field) {
        $n = (int) ($data->$field ?? 2);
        if ($n < 1) {
            $n = 1;
        }
        $data->$field = round(1.0 / $n, 6);
    }
}
