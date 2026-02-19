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
 * AMD module: attendance_form
 *
 * Handles the "Mark all as ..." bulk action on the attendance registration
 * form (attendance.php). When the teacher selects a status from the bulk
 * selector, all radio buttons in each student row are updated to match.
 *
 * @module     mod_attendancecontrol/attendance_form
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialises the bulk-status behaviour.
 *
 * Called by PHP via $PAGE->requires->js_call_amd(...).
 *
 * @param {string} formSelector CSS selector for the attendance form.
 */
export const init = (formSelector = 'form[data-region="attendance-form"]') => {
    const form = document.querySelector(formSelector);
    if (!form) {
        return;
    }

    const bulkSelect = form.querySelector('[name="bulk_status"]');
    if (!bulkSelect) {
        return;
    }

    // Apply bulk status: check the radio with matching value in every student row.
    const applyBulk = () => {
        const value = bulkSelect.value;
        form.querySelectorAll('input[type="radio"][name^="student_status"]').forEach((radio) => {
            radio.checked = (radio.value === value);
        });
    };

    bulkSelect.addEventListener('change', applyBulk);
};
