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
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ---------------------------------------------------------------------------
// CRUD callbacks required by Moodle.
// ---------------------------------------------------------------------------

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

    $data->timecreated  = time();
    $data->timemodified = time();

    // Persist base record.
    $instanceid = $DB->insert_record('attendancecontrol', $data);
    $data->id   = $instanceid;

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

    $data->id           = $data->instance;
    $data->timemodified = time();

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

    $DB->delete_records('attendancecontrol_session',  ['attendancecontrolid' => $id]);
    $DB->delete_records('attendancecontrol_schedule', ['attendancecontrolid' => $id]);
    $DB->delete_records('attendancecontrol_holiday',  ['attendancecontrolid' => $id]);
    $DB->delete_records('attendancecontrol',          ['id'                  => $id]);

    return true;
}

// ---------------------------------------------------------------------------
// Feature-support flags.
// ---------------------------------------------------------------------------

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

// ---------------------------------------------------------------------------
// Helper functions used internally by the callbacks above.
// ---------------------------------------------------------------------------

/**
 * Persists schedule slots from the repeatable form element.
 *
 * @param stdClass $data  Form data containing schedule_day, schedule_start,
 *                        schedule_end arrays from the repeating group.
 */
function attendancecontrol_save_schedule(stdClass $data): void {
    global $DB;

    if (empty($data->schedule_day)) {
        return;
    }

    foreach ($data->schedule_day as $i => $day) {
        if (empty($day)) {
            continue;
        }
        $slot = (object) [
            'attendancecontrolid' => $data->id,
            'day_of_week'         => (int) $day,
            'start_time'          => $data->schedule_start[$i],
            'end_time'            => $data->schedule_end[$i],
        ];
        $DB->insert_record('attendancecontrol_schedule', $slot);
    }
}

/**
 * Persists holiday dates from the repeatable form element.
 *
 * @param stdClass $data  Form data containing holiday_date and
 *                        holiday_description arrays.
 */
function attendancecontrol_save_holidays(stdClass $data): void {
    global $DB;

    if (empty($data->holiday_date)) {
        return;
    }

    foreach ($data->holiday_date as $i => $date) {
        if (empty($date)) {
            continue;
        }
        $holiday = (object) [
            'attendancecontrolid' => $data->id,
            'holiday_date'        => $date,
            'description'         => $data->holiday_description[$i] ?? '',
        ];
        $DB->insert_record('attendancecontrol_holiday', $holiday);
    }
}
