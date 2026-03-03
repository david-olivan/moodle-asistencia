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
 * Activity instance configuration form.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Form for adding/editing an attendancecontrol activity instance.
 */
class mod_attendancecontrol_mod_form extends moodleform_mod
{
    /**
     * Defines the form fields.
     */
    public function definition(): void {
        global $COURSE;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $this->standard_intro_elements();

        $groups = groups_get_all_groups($COURSE->id);
        $groupoptions = [];
        foreach ($groups as $g) {
            $groupoptions[$g->id] = format_string($g->name);
        }

        $mform->addElement('select', 'groupid', get_string('group', 'mod_attendancecontrol'), $groupoptions);
        $mform->addRule('groupid', null, 'required', null, 'client');
        $mform->addHelpButton('groupid', 'group', 'mod_attendancecontrol');

        $mform->addElement('header', 'daterangehdr', get_string('daterange', 'mod_attendancecontrol'));

        $mform->addElement('date_selector', 'course_start_date', get_string('coursestartdate', 'mod_attendancecontrol'));
        $mform->addRule('course_start_date', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'course_end_date', get_string('courseenddate', 'mod_attendancecontrol'));
        $mform->addRule('course_end_date', null, 'required', null, 'client');

        $mform->addElement('text', 'total_hours', get_string('totalhours', 'mod_attendancecontrol'), ['size' => 5]);
        $mform->setType('total_hours', PARAM_INT);
        $mform->addRule('total_hours', null, 'required', null, 'client');
        $mform->addRule('total_hours', null, 'numeric', null, 'client');
        $mform->addRule('total_hours', null, 'nonzero', null, 'client');

        $mform->addElement('header', 'schedulehdr', get_string('schedule', 'mod_attendancecontrol'));

        $mform->addElement(
            'html',
            '<div class="fitem"><div class="w-100">' .
            '<table class="table table-sm table-bordered mb-2" id="ac-schedule-table">' .
            '<thead class="table-light"><tr>' .
            '<th>' . get_string('dayofweek', 'mod_attendancecontrol') . '</th>' .
            '<th>' . get_string('starttime', 'mod_attendancecontrol') . '</th>' .
            '<th>' . get_string('endtime', 'mod_attendancecontrol') . '</th>' .
            '<th></th>' .
            '</tr></thead>' .
            '<tbody id="ac-schedule-tbody"></tbody>' .
            '</table>' .
            '<button type="button" id="btn-add-schedule" class="btn btn-sm btn-secondary">' .
            '+ ' . get_string('addslot', 'mod_attendancecontrol') .
            '</button>' .
            '</div></div>'
        );

        // Holidays: dynamic JS table (no repeat_elements).
        $mform->addElement('header', 'holidayshdr', get_string('holidays', 'mod_attendancecontrol'));

        $mform->addElement(
            'html',
            '<div class="fitem"><div class="w-100">' .
            '<table class="table table-sm table-bordered mb-2" id="ac-holiday-table">' .
            '<thead class="table-light"><tr>' .
            '<th>' . get_string('holidaydate', 'mod_attendancecontrol') . '</th>' .
            '<th>' . get_string('holidaydescription', 'mod_attendancecontrol') . '</th>' .
            '<th></th>' .
            '</tr></thead>' .
            '<tbody id="ac-holiday-tbody"></tbody>' .
            '</table>' .
            '<button type="button" id="btn-add-holiday" class="btn btn-sm btn-secondary">' .
            '+ ' . get_string('addholiday', 'mod_attendancecontrol') .
            '</button>' .
            '</div></div>'
        );

        // Penalty configuration: integer selectors.
        $mform->addElement('header', 'penaltyhdr', get_string('penaltyconfig', 'mod_attendancecontrol'));

        // Max percentage of unjustified absences allowed (selector 1%...50%).
        $pct_options = [];
        for ($i = 1; $i <= 50; $i++) {
            $pct_options[$i] = "{$i}%";
        }
        $mform->addElement(
            'select',
            'max_unjustified_absence_pct',
            get_string('maxunjustifiedpct', 'mod_attendancecontrol'),
            $pct_options
        );
        $mform->setType('max_unjustified_absence_pct', PARAM_INT);
        $mform->setDefault('max_unjustified_absence_pct', 15);
        $mform->addHelpButton('max_unjustified_absence_pct', 'maxunjustifiedpct', 'mod_attendancecontrol');

        // How many lates equal 1 unjustified absence (selector 1...10).
        $n_options = [];
        for ($i = 1; $i <= 10; $i++) {
            $n_options[$i] = $i;
        }
        $mform->addElement(
            'select',
            'delay_to_unjustified_ratio',
            get_string('delayratio', 'mod_attendancecontrol'),
            $n_options
        );
        $mform->setType('delay_to_unjustified_ratio', PARAM_INT);
        $mform->setDefault('delay_to_unjustified_ratio', 2);
        $mform->addHelpButton('delay_to_unjustified_ratio', 'delayratio', 'mod_attendancecontrol');

        // How many justified absences equal 1 unjustified absence (selector 1...10).
        $mform->addElement(
            'select',
            'justified_to_unjustified_ratio',
            get_string('justifiedratio', 'mod_attendancecontrol'),
            $n_options
        );
        $mform->setType('justified_to_unjustified_ratio', PARAM_INT);
        $mform->setDefault('justified_to_unjustified_ratio', 2);
        $mform->addHelpButton('justified_to_unjustified_ratio', 'justifiedratio', 'mod_attendancecontrol');

        // Standard grade/completion elements.
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Converts stored float ratios → integer N for the select display,
     * and loads existing schedule/holiday data into the AMD module.
     *
     * Called automatically by Moodle after set_data().
     */
    public function definition_after_data(): void {
        global $DB, $PAGE;

        parent::definition_after_data();

        $schedule_data = [];
        $holiday_data = [];

        // If there is POST schedule data (re-display after a server-side validation
        // error), restore from the submitted values so the user does not lose them.
        $submitted_days = optional_param_array('schedule_day', [], PARAM_INT);
        if (!empty($submitted_days)) {
            $starts = optional_param_array('schedule_start', [], PARAM_TEXT);
            $ends = optional_param_array('schedule_end', [], PARAM_TEXT);
            foreach ($submitted_days as $i => $day) {
                if (!empty($day)) {
                    $schedule_data[] = [
                        'day' => (int) $day,
                        'start' => clean_param($starts[$i] ?? '', PARAM_TEXT),
                        'end' => clean_param($ends[$i] ?? '', PARAM_TEXT),
                    ];
                }
            }
            $dates = optional_param_array('holiday_date', [], PARAM_TEXT);
            $descs = optional_param_array('holiday_description', [], PARAM_TEXT);
            foreach ($dates as $i => $date) {
                if (!empty($date)) {
                    $holiday_data[] = [
                        'date' => clean_param($date, PARAM_TEXT),
                        'description' => clean_param($descs[$i] ?? '', PARAM_TEXT),
                    ];
                }
            }
        } else if ($this->_instance) {
            // Editing an existing instance: load from DB.
            $slots = $DB->get_records(
                'attendancecontrol_schedule',
                ['attendancecontrolid' => $this->_instance],
                'day_of_week ASC, start_time ASC'
            );
            foreach ($slots as $slot) {
                $schedule_data[] = [
                    'day' => (int) $slot->day_of_week,
                    'start' => $slot->start_time,
                    'end' => $slot->end_time,
                ];
            }

            $holidays = $DB->get_records(
                'attendancecontrol_holiday',
                ['attendancecontrolid' => $this->_instance],
                'holiday_date ASC'
            );
            foreach ($holidays as $h) {
                $holiday_data[] = [
                    'date' => date('Y-m-d', (int) $h->holiday_date),
                    'description' => $h->description ?? '',
                ];
            }
        }

        // Localised weekday names passed to JS so the select renders correctly.
        $day_names = [
            1 => get_string('monday', 'calendar'),
            2 => get_string('tuesday', 'calendar'),
            3 => get_string('wednesday', 'calendar'),
            4 => get_string('thursday', 'calendar'),
            5 => get_string('friday', 'calendar'),
        ];

        $PAGE->requires->js_call_amd('mod_attendancecontrol/mod_form', 'init', [
            array_values($schedule_data),
            array_values($holiday_data),
            $day_names,
        ]);
    }

    /**
     * Converts stored float ratios to the integer N shown in the select
     * when the form is opened for editing.
     *
     * @param  array $defaultvalues  Data loaded from DB (passed by reference).
     */
    public function data_preprocessing(&$defaultvalues): void {
        parent::data_preprocessing($defaultvalues);

        // Stored ratio = 1/N  →  display integer N = round(1/ratio).
        foreach (['delay_to_unjustified_ratio', 'justified_to_unjustified_ratio'] as $field) {
            if (!empty($defaultvalues[$field]) && (float) $defaultvalues[$field] > 0) {
                $n = (int) round(1.0 / (float) $defaultvalues[$field]);
                $defaultvalues[$field] = max(1, min(10, $n));
            }
        }

        // Float % → integer (e.g. 15.00 → 15).
        if (isset($defaultvalues['max_unjustified_absence_pct'])) {
            $v = (int) round((float) $defaultvalues['max_unjustified_absence_pct']);
            $defaultvalues['max_unjustified_absence_pct'] = max(1, min(50, $v));
        }
    }

    /**
     * Extra server-side validation.
     *
     * @param  array $data   Submitted form data.
     * @param  array $files  Uploaded files.
     * @return array         Associative array of errors (fieldname => message).
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (!empty($data['course_start_date']) && !empty($data['course_end_date'])) {
            if ($data['course_end_date'] <= $data['course_start_date']) {
                $errors['course_end_date'] = get_string('err_enddatebeforestart', 'mod_attendancecontrol');
            }
        }

        if (!empty($data['total_hours']) && $data['total_hours'] <= 0) {
            $errors['total_hours'] = get_string('err_hourspositive', 'mod_attendancecontrol');
        }

        return $errors;
    }
}

/**
 * Returns localized weekday options (Monday–Friday).
 *
 * @return array Indexed by ISO day number (1 = Monday … 5 = Friday).
 */
function attendancecontrol_get_day_options(): array {
    return [
        1 => get_string('monday', 'calendar'),
        2 => get_string('tuesday', 'calendar'),
        3 => get_string('wednesday', 'calendar'),
        4 => get_string('thursday', 'calendar'),
        5 => get_string('friday', 'calendar'),
    ];
}
