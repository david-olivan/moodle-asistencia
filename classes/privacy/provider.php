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
 * Privacy API provider for mod_attendancecontrol.
 *
 * Declares what personal data is stored, and implements export and deletion
 * so that site administrators can comply with GDPR requests.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_attendancecontrol\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider implementation.
 *
 * Personal data stored: attendancecontrol_record (userid, status, remarks,
 * recorded_by, timecreated, timemodified).
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider
{
    /**
     * Describes the personal data stored by this plugin.
     *
     * @param  collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection
    {
        $collection->add_database_table(
            'attendancecontrol_record',
            [
                'userid' => 'privacy:metadata:attendancecontrol_record:userid',
                'status' => 'privacy:metadata:attendancecontrol_record:status',
                'remarks' => 'privacy:metadata:attendancecontrol_record:remarks',
                'recorded_by' => 'privacy:metadata:attendancecontrol_record:recorded_by',
                'timecreated' => 'privacy:metadata:attendancecontrol_record:timecreated',
                'timemodified' => 'privacy:metadata:attendancecontrol_record:timemodified',
            ],
            'privacy:metadata:attendancecontrol_record'
        );

        return $collection;
    }

    /**
     * Returns the contexts that contain personal data for the given user.
     *
     * @param  int          $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist
    {
        $contextlist = new contextlist();

        $sql = '
            SELECT DISTINCT ctx.id
              FROM {context}               ctx
              JOIN {course_modules}        cm  ON cm.id = ctx.instanceid
                                              AND ctx.contextlevel = :ctxlevel
              JOIN {attendancecontrol}     ac  ON ac.id = cm.instance
              JOIN {attendancecontrol_session} s ON s.attendancecontrolid = ac.id
              JOIN {attendancecontrol_record}  r ON r.sessionid = s.id
             WHERE r.userid = :userid
        ';

        $contextlist->add_from_sql($sql, [
            'ctxlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Returns the list of users in the given context who have personal data.
     *
     * @param  userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist): void
    {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $sql = '
            SELECT r.userid
              FROM {attendancecontrol_record}  r
              JOIN {attendancecontrol_session} s  ON s.id = r.sessionid
              JOIN {course_modules}            cm ON cm.instance = s.attendancecontrolid
             WHERE cm.id = :cmid
        ';

        $userlist->add_from_sql('userid', $sql, ['cmid' => $context->instanceid]);
    }

    /**
     * Exports all personal data for the given approved context list.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist): void
    {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('attendancecontrol', $context->instanceid, 0, false, MUST_EXIST);

            $sql = '
                SELECT r.id, s.session_date, s.start_time, s.end_time, s.duration_hours,
                       r.status, r.remarks, r.timecreated, r.timemodified
                  FROM {attendancecontrol_record}  r
                  JOIN {attendancecontrol_session} s ON s.id = r.sessionid
                 WHERE r.userid = :userid
                   AND s.attendancecontrolid = :instanceid
              ORDER BY s.session_date ASC, s.start_time ASC
            ';

            $records = $DB->get_records_sql($sql, [
                'userid' => $userid,
                'instanceid' => $cm->instance,
            ]);

            $data = [];
            foreach ($records as $rec) {
                $data[] = [
                    'session_date' => userdate($rec->session_date),
                    'start_time' => $rec->start_time,
                    'end_time' => $rec->end_time,
                    'duration_hours' => $rec->duration_hours,
                    'status' => $rec->status,
                    'remarks' => $rec->remarks,
                    'timecreated' => userdate($rec->timecreated),
                    'timemodified' => userdate($rec->timemodified),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('modulename', 'mod_attendancecontrol')],
                (object) ['records' => $data]
            );
        }
    }

    /**
     * Deletes all personal data for a specific context (all users).
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context): void
    {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('attendancecontrol', $context->instanceid, 0, false, MUST_EXIST);

        $sessionids = $DB->get_fieldset_select(
            'attendancecontrol_session',
            'id',
            'attendancecontrolid = :id',
            ['id' => $cm->instance]
        );

        if ($sessionids) {
            [$insql, $inparams] = $DB->get_in_or_equal($sessionids);
            $DB->delete_records_select('attendancecontrol_record', "sessionid $insql", $inparams);
        }
    }

    /**
     * Deletes all personal data for the given approved context list (one user).
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void
    {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('attendancecontrol', $context->instanceid, 0, false, MUST_EXIST);

            $sessionids = $DB->get_fieldset_select(
                'attendancecontrol_session',
                'id',
                'attendancecontrolid = :id',
                ['id' => $cm->instance]
            );

            if ($sessionids) {
                [$insql, $inparams] = $DB->get_in_or_equal($sessionids);
                $inparams['userid'] = $userid;
                $DB->delete_records_select(
                    'attendancecontrol_record',
                    "sessionid $insql AND userid = :userid",
                    $inparams
                );
            }
        }
    }

    /**
     * Deletes personal data for multiple users within a single context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist): void
    {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('attendancecontrol', $context->instanceid, 0, false, MUST_EXIST);

        $sessionids = $DB->get_fieldset_select(
            'attendancecontrol_session',
            'id',
            'attendancecontrolid = :id',
            ['id' => $cm->instance]
        );

        if (!$sessionids) {
            return;
        }

        [$sessql, $sesparams] = $DB->get_in_or_equal($sessionids, SQL_PARAMS_NAMED);
        [$usersql, $userparams] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);

        $DB->delete_records_select(
            'attendancecontrol_record',
            "sessionid $sessql AND userid $usersql",
            array_merge($sesparams, $userparams)
        );
    }
}
