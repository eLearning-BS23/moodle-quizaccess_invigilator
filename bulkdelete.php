<?php
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

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');

require_login($course, true, $cm);

$PAGE->set_url($url);
$PAGE->set_title('Invigilator:Bulk Delete');
$PAGE->set_heading('Invigilator Bulk Delete');

$PAGE->navbar->add('Invigilator: Bulk Delete', $url);
$helper = new addtional_settings_helper();
echo $OUTPUT->header();

if ($type == 'course') {
    $screenshotdata = $helper->searchssbycourseid($id);

} else if ($type == 'quiz') {
    $screenshotdata = $helper->searchssbyquizid($id);
} else {
    echo "invalid type";
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
redirect($url, get_string('settings:deleteallsuccess', 'quizaccess_invigilator'), -11, 'success');
