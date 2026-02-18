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
 * selector, all individual student selects are updated to match.
 *
 * @module     mod_attendancecontrol/attendance_form
 * @copyright  2026 Kings Corner Formación Profesional
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

    // Apply bulk status to all student selects.
    const applyBulk = () => {
        const value = bulkSelect.value;
        form.querySelectorAll('select[name^="student_status"]').forEach((sel) => {
            sel.value = value;
        });
    };

    bulkSelect.addEventListener('change', applyBulk);

    // Collapse/expand remarks textareas by default.
    form.querySelectorAll('textarea[name^="student_remarks"]').forEach((ta) => {
        // Wrap in a collapsible element if not already done by Moodle.
        const wrapper = ta.closest('.fitem');
        if (wrapper && !wrapper.dataset.collapsible) {
            wrapper.dataset.collapsible = 'true';
            ta.style.display = 'none';

            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'btn btn-link btn-sm p-0 ms-1';
            toggle.textContent = '[+]';
            toggle.setAttribute('aria-expanded', 'false');

            toggle.addEventListener('click', () => {
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                ta.style.display = expanded ? 'none' : '';
                toggle.setAttribute('aria-expanded', String(!expanded));
                toggle.textContent = expanded ? '[+]' : '[−]';
            });

            wrapper.querySelector('.fitemtitle')?.appendChild(toggle);
        }
    });
};
