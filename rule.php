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
 * Implementaton for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/invigilator/lib.php');


/**
 * quizaccess_invigilator
 */
class quizaccess_invigilator extends quiz_access_rule_base
{

    /**
     * Check is preflight check is required.
     *
     * @param mixed $attemptid
     * @return bool
     */
    public function is_preflight_check_required($attemptid) {
        $script = $this->get_topmost_script();
        $base = basename($script);
        if ($base == "view.php"){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get topmost script path
     *
     * @return String
     * @throws coding_exception
     */
    public function get_topmost_script() {
        $backtrace = debug_backtrace(
            defined("DEBUG_BACKTRACE_IGNORE_ARGS")
                ? DEBUG_BACKTRACE_IGNORE_ARGS
                : false);
        $topframe = array_pop($backtrace);
        return $topframe['file'];
    }

    /**
     * Get modal content
     *
     * @return String
     * @throws coding_exception
     */
    public function make_modal_content() {
        global $USER, $OUTPUT;
        $headercontent = get_string('sharescreen', 'quizaccess_invigilator');
        $header = "<h3>$headercontent</h3>";

        $screenhtml = get_string('screenhtml', 'quizaccess_invigilator');
        $screensharemsg = get_string('screensharemsg', 'quizaccess_invigilator');
        $html = "<div style='margin: auto !important;padding: 30px !important;'>
                 <table>
                    <tr>
                        <td colspan='2'>$header</td>
                    </tr>
                    <tr>
                        <td colspan='2'>$screensharemsg</td>
                    </tr>
                    <tr>
                        <td colspan='2'>$screenhtml</td>
                    </tr>
                </table></div>";

        return $html;
    }

    /**
     * add_preflight_check_form_fields
     *
     * @param mod_quiz_preflight_check_form $quizform
     * @param MoodleQuickForm $mform
     * @param mixed $attemptid
     * @return void
     * @throws coding_exception
     */
    public function add_preflight_check_form_fields(mod_quiz_preflight_check_form $quizform, MoodleQuickForm $mform, $attemptid) {
        global $PAGE;
        $coursedata = $this->get_courseid_cmid_from_preflight_form();
        $screenshotdelay = get_invigilator_settings('screenshotdelay');
        $screenshotwidth = get_invigilator_settings('screenshotwidth');

        $record = array();
        $record["courseid"] = (int)$coursedata['courseid'];
        $record["cmid"] = (int)$coursedata['cmid'];
        $record["quizid"] = (int)$coursedata['quizid'];
        $record["screenshotdelay"] = (int)$screenshotdelay;
        $record["screenshotwidth"] = (int)$screenshotwidth;

        $PAGE->requires->js_call_amd('quizaccess_invigilator/startattempt', 'setup', array($record));
        $attributesarray = $mform->_attributes;
        $attributesarray['target'] = '_blank';
        $mform->_attributes = $attributesarray;

        $screensharebtnlabel = get_string('sharescreenbtnlabel', 'quizaccess_invigilator');
        $modalcontent = $this->make_modal_content();
        $actionbtns = "<button id='invigilator-share-screen-btn' style='margin: 5px'>".$screensharebtnlabel."</button>";
        $hiddenvalue = "<input id='invigilator_window_surface' value='' type='hidden'/>
                        <input id='invigilator_share_state' value='' type='hidden'/>
                        <input id='invigilator_screen_off_flag' value='0' type='hidden'/>";

        $mform->addElement('static', 'modalcontent', '', $modalcontent);
        $mform->addElement('static', 'actionbtns', '', $actionbtns);
        $mform->addElement('checkbox', 'invigilator', get_string('invigilatorlabel', 'quizaccess_invigilator'));
        $mform->addElement('html', $hiddenvalue);
    }

    /**
     * Get_courseid_cmid_from_preflight_form
     *
     * @return array
     * @throws coding_exception
     */
    public function get_courseid_cmid_from_preflight_form() {
        $response = array();
        $response['courseid'] = $this->quiz->course;
        $response['quizid'] = $this->quiz->id;
        $response['cmid'] = $this->quiz->cmid;
        return $response;
    }

    /**
     * Validate the preflight check
     *
     * @param mixed $data
     * @param mixed $files
     * @param mixed $errors
     * @param mixed $attemptid
     * @return mixed $errors
     * @throws coding_exception
     */
    public function validate_preflight_check($data, $files, $errors, $attemptid) {
        if (empty($data['invigilator'])) {
            $errors['invigilator'] = get_string('youmustagree', 'quizaccess_invigilator');
        }

        return $errors;
    }

    /**
     * * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     *
     * @param quiz $quizobj
     * @param int $timenow
     * @param bool $canignoretimelimits
     * @return quiz_access_rule_base|quizaccess_invigilator|null
     */
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        if (empty($quizobj->get_quiz()->invigilatorrequired)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    /**
     * Add any fields that this rule requires to the quiz settings form. This
     * method is called from mod_quiz_mod_form::definition(), while the
     * security section is being built.
     *
     * @param mod_quiz_mod_form $quizform the quiz settings form that is being built.
     * @param MoodleQuickForm $mform the wrapped MoodleQuickForm.
     * @throws coding_exception
     */
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        $mform->addElement('select', 'invigilatorrequired',
            get_string('invigilatorrequired', 'quizaccess_invigilator'),
            array(
                0 => get_string('notrequired', 'quizaccess_invigilator'),
                1 => get_string('invigilatorrequiredoption', 'quizaccess_invigilator'),
            ));
        $mform->addHelpButton('invigilatorrequired', 'invigilatorrequired', 'quizaccess_invigilator');
    }

    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from quiz_after_add_or_update() in lib.php.
     *
     * @param object $quiz the data from the quiz form, including $quiz->id
     *      which is the id of the quiz being saved.
     * @throws dml_exception
     */
    public static function save_settings($quiz) {
        global $DB;
        if (empty($quiz->invigilatorrequired)) {
            $DB->delete_records('quizaccess_invigilator', array('quizid' => $quiz->id));
        } else {
            if (!$DB->record_exists('quizaccess_invigilator', array('quizid' => $quiz->id))) {
                $record = new stdClass();
                $record->quizid = $quiz->id;
                $record->invigilatorrequired = 1;
                $DB->insert_record('quizaccess_invigilator', $record);
            }
        }
    }

    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from quiz_delete_instance() in lib.php.
     *
     * @param object $quiz the data from the database, including $quiz->id
     *      which is the id of the quiz being deleted.
     * @throws dml_exception
     */
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_invigilator', array('quizid' => $quiz->id));
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probalby to read the code of quiz_access_manager::load_settings().
     *
     * If you have some settings that cannot be loaded in this way, then you can
     * use the get_extra_settings() method instead, but that has
     * performance implications.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *     can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be alised
     *        if neccessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid) {
        return array(
            'invigilatorrequired',
            'LEFT JOIN {quizaccess_invigilator} invigilator ON invigilator.quizid = quiz.id',
            array());
    }

    /**
     * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     *
     * @return mixed a message, or array of messages, explaining the restriction
     *         (may be '' if no message is appropriate).
     * @throws coding_exception
     */
    public function description() {
        global $PAGE;
        $record = new stdClass();
        $record->allowscreenshare = get_string('warning:allowscreenshare', 'quizaccess_invigilator');
        $PAGE->requires->js_call_amd('quizaccess_invigilator/startattempt', 'init', array($record));
        $messages = [get_string('invigilatorheader', 'quizaccess_invigilator')];

        $messages[] = $this->get_download_config_button();

        return $messages;
    }

    /**
     * Sets up the attempt (review or summary) page with any special extra
     * properties required by this rule.
     *
     * @param moodle_page $page the page object to initialise.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function setup_attempt_page($page) {
        $cmid = optional_param('cmid', '', PARAM_INT);
        $attempt = optional_param('attempt', '', PARAM_INT);

        $page->set_title($this->quizobj->get_course()->shortname . ': ' . $page->title);
        $page->set_popup_notification_allowed(false); // Prevent message notifications.
        $page->set_heading($page->title);

        global $DB, $COURSE, $USER;
        if ($cmid) {
            // Get Screenshot Delay and Image Width.
            $screenshotdelay = get_invigilator_settings('screenshotdelay');
            $screenshotwidth = get_invigilator_settings('screenshotwidth');
            $quizurl = new moodle_url("/mod/quiz/view.php", array("id" => $this->quiz->cmid));

            $record = new stdClass();
            $record->screenshotdelay = $screenshotdelay;
            $record->screenshotwidth = $screenshotwidth;
            $record->quizurl = $quizurl->__toString();
            $page->requires->js_call_amd('quizaccess_invigilator/attemptpage', 'setup', array($record));
        }
    }

    /**
     * Get a button to view the Invigilator report.
     *
     * @return string A link to view report
     * @throws coding_exception
     */
    private function get_download_config_button() : string {
        global $OUTPUT, $USER;

        $context = context_module::instance($this->quiz->cmid, MUST_EXIST);
        if (has_capability('quizaccess/invigilator:viewreport', $context, $USER->id)) {
            $httplink = \quizaccess_invigilator\link_generator::get_link($this->quiz->course, $this->quiz->cmid, false, is_https());
            return $OUTPUT->single_button($httplink, get_string('picturesreport', 'quizaccess_invigilator'), 'get');
        } else {
            return '';
        }
    }

}
