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
 * Dynamic schedule and holidays table management for the mod_attendancecontrol form.
 *
 * Renders rows into the #ac-schedule-tbody and #ac-holiday-tbody containers
 * already present in the Moodle form HTML.  Each row contains real <input>
 * elements with indexed array names (schedule_day[N], etc.) that are submitted
 * with the form without any JavaScript serialisation step.
 *
 * @module     mod_attendancecontrol/mod_form
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

import Templates from 'core/templates';

/** Running counter used to generate unique input name indices. */
let scheduleCounter = 0;
let holidayCounter = 0;

/** Localised weekday map { 1: 'Lunes', 2: 'Martes', … } passed from PHP. */
let dayNames = {};

// ---------------------------------------------------------------------------
// Private helpers.
// ---------------------------------------------------------------------------

/**
 * Build day options array for a weekday select template context.
 *
 * @param {number|string} selectedDay  ISO weekday number to pre-select.
 * @returns {Array<{value: string, label: string, selected: boolean}>}
 */
const buildDayOptionsArray = (selectedDay) =>
    Object.entries(dayNames).map(([val, label]) => ({
        value: val,
        label,
        selected: String(val) === String(selectedDay),
    }));

// ---------------------------------------------------------------------------
// Schedule table.
// ---------------------------------------------------------------------------

/**
 * Append a new row to the weekly schedule table.
 *
 * @param {number|string} day   ISO weekday (default 1 = Monday).
 * @param {string}        start Start time in HH:MM format (default '').
 * @param {string}        end   End time in HH:MM format (default '').
 * @returns {Promise<void>}
 */
const addScheduleRow = async(day = 1, start = '', end = '') => {
    const tbody = document.getElementById('ac-schedule-tbody');
    if (!tbody) {
        return;
    }

    const idx = scheduleCounter++;
    const context = {
        idx,
        start,
        end,
        days: buildDayOptionsArray(day),
        strDelete: 'Eliminar franja',
    };
    const {html} = await Templates.render('mod_attendancecontrol/schedule_row', context);
    new DOMParser().parseFromString(html, 'text/html')
        .querySelectorAll('tr')
        .forEach((row) => tbody.appendChild(row));
};

// ---------------------------------------------------------------------------
// Holidays table.
// ---------------------------------------------------------------------------

/**
 * Append a new row to the holidays table.
 *
 * @param {string} date        Date string in YYYY-MM-DD format (default '').
 * @param {string} description Optional label for the holiday (default '').
 * @returns {Promise<void>}
 */
const addHolidayRow = async(date = '', description = '') => {
    const tbody = document.getElementById('ac-holiday-tbody');
    if (!tbody) {
        return;
    }

    const idx = holidayCounter++;
    const context = {
        idx,
        date,
        description,
        strDelete: 'Eliminar festivo',
    };
    const {html} = await Templates.render('mod_attendancecontrol/holiday_row', context);
    new DOMParser().parseFromString(html, 'text/html')
        .querySelectorAll('tr')
        .forEach((row) => tbody.appendChild(row));
};

// ---------------------------------------------------------------------------
// Public API.
// ---------------------------------------------------------------------------

/**
 * Initialise the dynamic schedule and holiday tables.
 *
 * Called by Moodle's AMD loader via $PAGE->requires->js_call_amd().
 *
 * @param {Array<{day: number, start: string, end: string}>} existingSchedule
 *   Schedule rows already stored in the DB (empty array for new instances).
 * @param {Array<{date: string, description: string}>} existingHolidays
 *   Holiday rows already stored in the DB (empty array for new instances).
 * @param {Object.<string, string>} days
 *   Localised weekday map, e.g. { "1": "Lunes", "2": "Martes", … }.
 */
export const init = (existingSchedule, existingHolidays, days) => {
    dayNames = days || {};

    // Populate existing schedule rows (edit mode or validation re-display).
    if (Array.isArray(existingSchedule)) {
        existingSchedule.forEach((s) => addScheduleRow(s.day, s.start, s.end));
    }

    // Populate existing holiday rows.
    if (Array.isArray(existingHolidays)) {
        existingHolidays.forEach((h) => addHolidayRow(h.date, h.description));
    }

    // Wire up the "Add slot" button.
    const btnSchedule = document.getElementById('btn-add-schedule');
    if (btnSchedule) {
        btnSchedule.addEventListener('click', () => addScheduleRow());
    }

    // Wire up the "Add holiday" button.
    const btnHoliday = document.getElementById('btn-add-holiday');
    if (btnHoliday) {
        btnHoliday.addEventListener('click', () => addHolidayRow());
    }

    // Delete rows via event delegation (handles async-rendered rows).
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.ac-del-schedule, .ac-del-holiday');
        if (btn) {
            btn.closest('tr').remove();
        }
    });
};
