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
 * Bulk Delete for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/tablelib.php');
require_once(__DIR__ . '/classes/addtional_settings_helper.php');

$cmid = required_param('cmid', PARAM_INT);
$type = required_param('type', PARAM_TEXT);
$id = required_param('id', PARAM_INT);
$context = context_module::instance($cmid, MUST_EXIST);
require_capability('quizaccess/invigilator:deletescreenshot', $context);

$params = array('cmid' => $cmid, 'type' => $type, 'id' => $id);
$url = new moodle_url(
    '/mod/quiz/accessrule/invigilator/bulkdelete.php',
    $params
);

list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');

require_login($course, true, $cm);

$PAGE->set_url($url);
$PAGE->set_title(get_string('invigilator:bulkdelete', 'quizaccess_invigilator'));
$PAGE->set_heading(get_string('invigilator_bulkdelete', 'quizaccess_invigilator'));

$PAGE->navbar->add(get_string('invigilator:bulkdelete', 'quizaccess_invigilator'), $url);
$helper = new addtional_settings_helper();
echo $OUTPUT->header();

if ($type == 'course') {
    $screenshotdata = $helper->searchssbycourseid($id);
} else if ($type == 'quiz') {
    $screenshotdata = $helper->searchssbyquizid($id);
} else {
    echo get_string('invalidtype', 'quizaccess_invigilator');
}
$ssrowids = array();
foreach ($screenshotdata as $row) {
    array_push($ssrowids, $row->id);
}

$ssrowidstring = implode(',', $ssrowids);
$helper->deletesslogs($ssrowidstring);

$params = array(
    'cmid' => $cmid
);
$url = new moodle_url(
    '/mod/quiz/accessrule/invigilator/invigilatorsummary.php',
    $params
);
redirect($url, get_string('settings:deleteallsuccess', 'quizaccess_invigilator'), -11, get_string('success', 'quizaccess_invigilator'));
