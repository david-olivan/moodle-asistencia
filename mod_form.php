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
class mod_attendancecontrol_mod_form extends moodleform_mod {

    /**
     * Defines the form fields.
     */
    public function definition(): void {
        global $COURSE;

        $mform = $this->_form;

        // ----------------------------------------------------------------
        // General section (name + intro provided by moodleform_mod).
        // ----------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $this->standard_intro_elements();
        $mform->setDefault('name', get_string('pluginname', 'mod_attendancecontrol'));

        // ----------------------------------------------------------------
        // Group selection.
        // ----------------------------------------------------------------
        $groups = groups_get_all_groups($COURSE->id);
        $groupoptions = [];
        foreach ($groups as $g) {
            $groupoptions[$g->id] = format_string($g->name);
        }

        $mform->addElement('select', 'groupid', get_string('group', 'mod_attendancecontrol'), $groupoptions);
        $mform->addRule('groupid', null, 'required', null, 'client');
        $mform->addHelpButton('groupid', 'group', 'mod_attendancecontrol');

        // ----------------------------------------------------------------
        // Course date range.
        // ----------------------------------------------------------------
        $mform->addElement('header', 'daterangehdr', get_string('daterange', 'mod_attendancecontrol'));

        $mform->addElement('date_selector', 'course_start_date', get_string('coursestartdate', 'mod_attendancecontrol'));
        $mform->addRule('course_start_date', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'course_end_date', get_string('courseenddate', 'mod_attendancecontrol'));
        $mform->addRule('course_end_date', null, 'required', null, 'client');

        // ----------------------------------------------------------------
        // Total hours.
        // ----------------------------------------------------------------
        $mform->addElement('text', 'total_hours', get_string('totalhours', 'mod_attendancecontrol'), ['size' => 5]);
        $mform->setType('total_hours', PARAM_INT);
        $mform->addRule('total_hours', null, 'required', null, 'client');
        $mform->addRule('total_hours', null, 'numeric', null, 'client');
        $mform->addRule('total_hours', null, 'nonzero', null, 'client');

        // ----------------------------------------------------------------
        // Weekly schedule (repeatable).
        // ----------------------------------------------------------------
        $mform->addElement('header', 'schedulehdr', get_string('schedule', 'mod_attendancecontrol'));

        $repeatarray = [];
        $repeatarray[] = $mform->createElement(
            'select',
            'schedule_day',
            get_string('dayofweek', 'mod_attendancecontrol'),
            attendancecontrol_get_day_options()
        );
        $repeatarray[] = $mform->createElement(
            'text',
            'schedule_start',
            get_string('starttime', 'mod_attendancecontrol'),
            ['placeholder' => 'HH:MM', 'size' => 6]
        );
        $repeatarray[] = $mform->createElement(
            'text',
            'schedule_end',
            get_string('endtime', 'mod_attendancecontrol'),
            ['placeholder' => 'HH:MM', 'size' => 6]
        );

        $repeatoptions = [];
        $repeatoptions['schedule_start']['type'] = PARAM_TEXT;
        $repeatoptions['schedule_end']['type']   = PARAM_TEXT;

        $this->repeat_elements(
            $repeatarray,
            1,
            $repeatoptions,
            'schedule_count',
            'schedule_add',
            1,
            get_string('addslot', 'mod_attendancecontrol')
        );

        // ----------------------------------------------------------------
        // Holidays (repeatable).
        // ----------------------------------------------------------------
        $mform->addElement('header', 'holidayshdr', get_string('holidays', 'mod_attendancecontrol'));

        $holidayrepeat = [];
        $holidayrepeat[] = $mform->createElement('date_selector', 'holiday_date', get_string('holidaydate', 'mod_attendancecontrol'));
        $holidayrepeat[] = $mform->createElement(
            'text',
            'holiday_description',
            get_string('holidaydescription', 'mod_attendancecontrol'),
            ['size' => 30]
        );

        $holidayoptions = [];
        $holidayoptions['holiday_description']['type'] = PARAM_TEXT;

        $this->repeat_elements(
            $holidayrepeat,
            0,
            $holidayoptions,
            'holiday_count',
            'holiday_add',
            1,
            get_string('addholiday', 'mod_attendancecontrol')
        );

        // ----------------------------------------------------------------
        // Penalty configuration.
        // ----------------------------------------------------------------
        $mform->addElement('header', 'penaltyhdr', get_string('penaltyconfig', 'mod_attendancecontrol'));

        $mform->addElement(
            'text',
            'max_unjustified_absence_pct',
            get_string('maxunjustifiedpct', 'mod_attendancecontrol'),
            ['size' => 5]
        );
        $mform->setType('max_unjustified_absence_pct', PARAM_FLOAT);
        $mform->setDefault('max_unjustified_absence_pct', 15.00);
        $mform->addRule('max_unjustified_absence_pct', null, 'required', null, 'client');
        $mform->addHelpButton('max_unjustified_absence_pct', 'maxunjustifiedpct', 'mod_attendancecontrol');

        $mform->addElement(
            'text',
            'delay_to_unjustified_ratio',
            get_string('delayratio', 'mod_attendancecontrol'),
            ['size' => 5]
        );
        $mform->setType('delay_to_unjustified_ratio', PARAM_FLOAT);
        $mform->setDefault('delay_to_unjustified_ratio', 0.50);
        $mform->addRule('delay_to_unjustified_ratio', null, 'required', null, 'client');
        $mform->addHelpButton('delay_to_unjustified_ratio', 'delayratio', 'mod_attendancecontrol');

        $mform->addElement(
            'text',
            'justified_to_unjustified_ratio',
            get_string('justifiedratio', 'mod_attendancecontrol'),
            ['size' => 5]
        );
        $mform->setType('justified_to_unjustified_ratio', PARAM_FLOAT);
        $mform->setDefault('justified_to_unjustified_ratio', 0.50);
        $mform->addRule('justified_to_unjustified_ratio', null, 'required', null, 'client');
        $mform->addHelpButton('justified_to_unjustified_ratio', 'justifiedratio', 'mod_attendancecontrol');

        // Standard grade/completion elements.
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Extra server-side validation.
     *
     * @param  array  $data   Submitted form data.
     * @param  array  $files  Uploaded files.
     * @return array          Associative array of errors (fieldname => message).
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

        foreach (['max_unjustified_absence_pct', 'delay_to_unjustified_ratio', 'justified_to_unjustified_ratio'] as $field) {
            if (isset($data[$field]) && ($data[$field] < 0 || $data[$field] > 100)) {
                $errors[$field] = get_string('err_invalidratio', 'mod_attendancecontrol');
            }
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
        1 => get_string('monday',    'calendar'),
        2 => get_string('tuesday',   'calendar'),
        3 => get_string('wednesday', 'calendar'),
        4 => get_string('thursday',  'calendar'),
        5 => get_string('friday',    'calendar'),
    ];
}
