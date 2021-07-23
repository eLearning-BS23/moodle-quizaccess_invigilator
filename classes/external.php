<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Extrarnal for the quizaccess_invigilator plugin.
 *
 * @package   quizaccess_invigilator
 * @copyright 2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/externallib.php');

/**
 * External class.
 *
 * @package quizaccess_invigilator
 * @copyright 2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_invigilator_external extends external_api
{

    /**
     * Store parameters.
     *
     * @return external_function_parameters
     */
    public static function send_screenshot_parameters () {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'cmid' => new external_value(PARAM_INT, 'screenshot id'),
                'quizid' => new external_value(PARAM_INT, 'screenshot quiz id'),
                'screenshot' => new external_value(PARAM_RAW, 'webcam photo')
            )
        );
    }

    /**
     * Store the screenshots in Moodle subsystems and insert in log table
     *
     * @param mixed $courseid
     * @param mixed $cmid
     * @param mixed $quizid Quizid OR cmid
     * @param mixed $screenshot
     *
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws stored_file_creation_exception
     */
    public static function send_screenshot($courseid, $cmid, $quizid, $screenshot) {
        global $DB, $USER;

        // Validate the params.
        self::validate_parameters(
            self::send_screenshot_parameters(),
            array(
                'courseid' => $courseid,
                'cmid' => $cmid,
                'quizid' => $quizid,
                'screenshot' => $screenshot
            )
        );
        $filepath = "/";

        // Save file.
        $warnings = array();

        // Insert log with blank path.
        $record = new stdClass();
        $record->courseid = $courseid;
        $record->cmid = $cmid;
        $record->quizid = $quizid;
        $record->userid = $USER->id;
        $record->screenshot = $filepath;
        $record->timecreated = time();
        $screenshotid = $DB->insert_record('quizaccess_invigilator_logs', $record, true);

        $record = new stdClass();
        $record->filearea = 'picture';
        $record->component = 'quizaccess_invigilator';
        $record->filepath = '';
        $record->itemid = $screenshotid;
        $record->license = '';
        $record->author = '';

        $context = context_module::instance($cmid);
        $fs = get_file_storage();
        $record->filepath = file_correct_filepath($record->filepath);

        // For base64 to file.
        $data = $screenshot;
        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        $filename = 'screenshot-' . $screenshotid . '-' . $USER->id . '-' . $courseid . '-' . time() . rand(1, 1000) . '.png';

        $data = self::add_timecode_to_image($data);

        $record->courseid = $courseid;
        $record->filename = $filename;
        $record->contextid = $context->id;
        $record->userid = $USER->id;

        $fs->create_file_from_string($record, $data);

        $url = moodle_url::make_pluginfile_url(
            $context->id,
            $record->component,
            $record->filearea,
            $record->itemid,
            $record->filepath,
            $record->filename,
            false
        );

        // Update filepath in log.
        $updateddata = new stdClass();
        $updateddata->id = $screenshotid;
        $updateddata->courseid = $courseid;
        $updateddata->cmid = $cmid;
        $updateddata->quizid = $quizid;
        $updateddata->userid = $USER->id;
        $updateddata->screenshot = "{$url}";
        $updateddata->timecreated = time();
        $DB->update_record('quizaccess_invigilator_logs', $updateddata);

        $result = array();
        $result['screenshotid'] = $screenshotid;
        $result['warnings'] = $warnings;

        return $result;
    }


    /**
     * Cam shots return parameters.
     *
     * @return external_single_structure
     */
    public static function send_screenshot_returns() {
        return new external_single_structure(
            array(
                'screenshotid' => new external_value(PARAM_INT, 'screenshot sent id'),
                'warnings' => new external_warnings()
            )
        );
    }



    /**
     * Check user capability
     * @param array $params
     * @param context $context
     * @param $USER
     * @return void
     * @throws dml_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */
    protected static function request_user_require_capability(array $params, context $context, $USER) {
        $user = core_user::get_user($params['userid'], '*', MUST_EXIST);
        core_user::require_active_user($user);

        // Extra checks so only users with permissions can view other users reports.
        if ($USER->id != $user->id) {
            require_capability('quizaccess/invigilator:viewreport', $context);
        }
    }

    /**
     * Adds timestamp information to captured image.
     * @param $data
     * @return string
     */
    private static function add_timecode_to_image ($data) {
        global $CFG;

        $image = imagecreatefromstring($data);
        imagefilledrectangle($image, 0, 0, 120, 22, imagecolorallocatealpha($image, 255, 255, 255, 60));
        imagefttext($image, 9, 0, 4, 16, imagecolorallocate($image, 0, 0, 0),
            $CFG->dirroot . '/mod/quiz/accessrule/invigilator/assets/Roboto-Light.ttf', date('d-m-Y H:i:s') );
        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        ob_end_clean();
        imagedestroy($image);
        return $data;
    }
}
