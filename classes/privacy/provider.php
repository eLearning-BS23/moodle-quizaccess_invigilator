<?php
// This file is part of Moodle invigilator for Moodle - http://moodle.org/
//
// Moodle invigilator is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle invigilator is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with MailTest.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Privacy Subsystem implementation for quizaccess_invigilator.
 * @package quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator\privacy;

use coding_exception;
use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;
use dml_exception;

defined('MOODLE_INTERNAL') || die();

class provider implements
    \core_privacy\local\metadata\provider,
    core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    public static function get_metadata(collection $collection): collection {

        $collection->add_subsystem_link(
            'core_files',
            [],
            'privacy:metadata:core_files'
        );

        // Stores all validations for reporting.
        $collection->add_database_table(
            'quizaccess_invigilator_logs',
            [
                'userid' => 'privacy:metadata:quizaccess_invigilator_logs:userid',
                'screenshot' => 'privacy:metadata:quizaccess_invigilator_logs:screenshot'
            ],
            'privacy:metadata:quizaccess_invigilator_logs'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $params = ['context' => CONTEXT_MODULE, 'userid' => $userid];

        $sql = "SELECT DISTINCT c.id
                  FROM {quizaccess_invigilator_logs} qil
                  JOIN {context} c ON c.instanceid = qil.quizid AND c.contextlevel = :context
                  WHERE qil.userid = :userid
              GROUP BY c.id";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        $fileparams = ['component' => 'quizaccess_invigilator', 'userid' => $userid];
        $sqlfile = "SELECT DISTINCT contextid as id
                    FROM {files}
                    WHERE component = :component
                    AND userid= :userid";
        $contextlist->add_from_sql($sqlfile, $fileparams);
        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        // The data is associated at the quiz module context level, so retrieve the user's context id.
        $sql = "SELECT DISTINCT qil.userid AS userid
                  FROM {quizaccess_invigilator_logs} qil
                  JOIN {course_modules} cm ON cm.id = qil.quizid
                 WHERE cm.id = ?";
        $params = [$context->instanceid];
        $userlist->add_from_sql('userid', $sql, $params);

        $fileparams = ['component' => 'quizaccess_invigilator', 'contextid' => $context->id];
        $sqlfile = "SELECT DISTINCT userid
                    FROM {files}
                    WHERE component = :component
                    AND contextid= :contextid";
        $userlist->add_from_sql('userid', $sqlfile, $fileparams);
    }

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     * @param approved_contextlist $contextlist
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        // Get all cmids that correspond to the contexts for a user.
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel === CONTEXT_MODULE) {
                if ($context->instanceid) {
                    list($insql, $params) = $DB->get_in_or_equal($context->instanceid, SQL_PARAMS_NAMED);
                    $params['userid'] = $contextlist->get_user()->id;

                    $sql = "SELECT qil.id as id,
                    qil.userid as userid,
                    qil.courseid as courseid,
                    qil.quizid as quizid,
                    qil.screenshot as screenshot
                    FROM moodlebackup.mdl_quizaccess_invigilator_logs qil
                    WHERE qpl.quizid {$insql} WHERE qil.userid =2
                    ORDER BY qil.id ASC";

                    $qailogs = $DB->get_records_sql($sql, $params);
                    $index = 0;
                    foreach ($qailogs as $qailog) {
                        $index++;
                        $subcontext = [
                            get_string('quizaccess_invigilator', 'quizaccess_invigilator'),
                            'invigilator_logs',
                            $index
                        ];

                        $data = (object)[
                            'id' => $qailog->id,
                            'userid' => $qailog->userid,
                            'courseid' => $qailog->courseid,
                            'quizid' => $qailog->quizid,
                            'screenshot' => $qailog->screenshot
                        ];

                        $screenshotimage = explode("/", "$qailog->screenshot");
                        $screenshotimagelast = end($screenshotimage);

                        $paramfile["userid"] = $qailog->userid;
                        $paramfile["filename"] = $screenshotimagelast;

                        if (!empty($screenshotimagelast)) {
                            $userfiles = $DB->get_record('files', $paramfile);
                            writer::with_context($context)->export_area_files(
                                [get_string('privacy:core_files', 'quizaccess_invigilator')],
                                'quizaccess_invigilator',
                                'picture',
                                $userfiles->itemid
                            )->export_data($subcontext, $data);
                        } else {
                            writer::with_context($context)->export_data($subcontext, $data);
                        }
                    }
                }
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context
     * @throws dml_exception
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        // Sanity check that context is at the module context level, then get the quizid.
        if ($context->contextlevel === CONTEXT_MODULE) {
            $cmid = $context->instanceid;
            $quizid = $DB->get_field('course_modules', 'instance', ['id' => $cmid]);

            $params['quizid'] = $quizid;
            $DB->set_field_select('quizaccess_invigilator_logs', 'userid', 0, "quizid = :quizid", $params);
        }
        // Delete all of the webcam images for this user.
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'quizaccess_invigilator', 'picture');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_userlist $userlist
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();

        // Sanity check that context is at the Module context level.
        if ($context->contextlevel !== CONTEXT_MODULE) {
            $userids = $userlist->get_userids();
            list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

            $DB->set_field_select('quizaccess_invigilator_logs', 'userid', 0, "userid {$insql}", $inparams);

            // Delete users file (webcam images).
            $filesql = "SELECT * FROM {files} WHERE userid {$insql}";
            $usersfile = $DB->get_records_sql($filesql, $inparams);
            $fs = get_file_storage();
            foreach ($usersfile as $file) :
                $fs->delete_area_files($context->id, 'quizaccess_invigilator', 'picture', $file->id);
            endforeach;
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param approved_contextlist $contextlist
     * @throws dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        // If the user has data, then only the User context should be present so get the first context.
        $contexts = $contextlist->get_contexts();
        if (count($contexts) == 0) {
            return;
        }

        $params['userid'] = $contextlist->get_user()->id;
        $DB->set_field_select('quizaccess_invigilator', 'userid', 0, "userid = :userid", $params);

        foreach ($contextlist as $context) {
            // Delete user file (webcam images).
            $userfiles = $DB->get_records('files', $params);
            $fs = get_file_storage();
            foreach ($userfiles as $file) :
                $fs->delete_area_files($context->id, 'quizaccess_invigilator', 'picture', $file->itemid);
            endforeach;
        }
    }
}
