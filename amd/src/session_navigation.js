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
 * AMD module: session_navigation
 *
 * Enhances the teacher's main view (view.php) with client-side week
 * navigation highlighting. Marks the current-day session row and
 * provides keyboard accessibility for the navigation buttons.
 *
 * @module     mod_attendancecontrol/session_navigation
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialises the session navigation enhancements.
 *
 * @param {string} region CSS data-region attribute value of the view container.
 */
export const init = (region = "attendancecontrol-view") => {
	const container = document.querySelector(`[data-region="${region}"]`);
	if (!container) {
		return;
	}

	// Ensure today's rows receive the highlight class if not already set
	// server-side (belt-and-suspenders approach).
	const todayRows = container.querySelectorAll("tr.table-info");
	todayRows.forEach((row) => {
		row.setAttribute("aria-current", "date");
	});

	// Add keyboard navigation between weeks (left/right arrow keys).
	const prevBtn = container.querySelector('a[href*="week="]');
	const nextBtn = container.querySelectorAll('a[href*="week="]')[1];

	document.addEventListener("keydown", (e) => {
		if (e.key === "ArrowLeft" && prevBtn) {
			prevBtn.click();
		} else if (e.key === "ArrowRight" && nextBtn) {
			nextBtn.click();
		}
	});
};
