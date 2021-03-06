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
 * Report for the quizaccess_invigilator plugin.
 *
 * @package   quizaccess_invigilator
 * @copyright 2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */


require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/invigilator/lib.php');
require_once($CFG->libdir . '/tablelib.php');
// Get vars.
$courseid = required_param('courseid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$studentid = optional_param('studentid', '', PARAM_INT);
$searchkey = optional_param('searchKey', '', PARAM_TEXT);
$submittype = optional_param('submitType', '', PARAM_TEXT);
$reportid = optional_param('reportid', '', PARAM_INT);
$logaction = optional_param('logaction', '', PARAM_TEXT);

$context = context_module::instance($cmid, MUST_EXIST);

list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');

require_login($course, true, $cm);


$COURSE = $DB->get_record('course', array('id' => $courseid));
$quiz = $DB->get_record('quiz', array('id' => $cm->instance));

$params = array(
    'courseid' => $courseid,
    'userid' => $studentid,
    'cmid' => $cmid
);
if ($studentid) {
    $params['studentid'] = $studentid;
}
if ($reportid) {
    $params['reportid'] = $reportid;
}

$url = new moodle_url(
    '/mod/quiz/accessrule/invigilator/report.php',
    $params
);

$navparam = ["courseid" => $courseid, "cmid" => $cmid];
$navurl = new moodle_url(
    '/mod/quiz/accessrule/invigilator/report.php',
    $navparam
);

$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->set_title($COURSE->shortname . ': ' . get_string('pluginname', 'quizaccess_invigilator'));
$PAGE->set_heading($COURSE->fullname . ': ' . get_string('pluginname', 'quizaccess_invigilator'));

$PAGE->navbar->add(get_string('quizaccess_invigilator_label', 'quizaccess_invigilator'), $navurl);

$PAGE->requires->js_call_amd('quizaccess_invigilator/lightbox2');

$settingsbtn = "";
$logbtn = "";

if (has_capability('quizaccess/invigilator:deletescreenshot', $context, $USER->id)) {
    $settingspageurl = $CFG->wwwroot . '/mod/quiz/accessrule/invigilator/invigilatorsummary.php?cmid=' . $cmid;
    $settingsbtnlabel = "Invigilator Summary Report";
    $settingsbtn = '<a class="btn btn-primary" href="' . $settingspageurl . '">' . $settingsbtnlabel . '</a>';

    $logpageurl = $CFG->wwwroot . '/mod/quiz/accessrule/invigilator/additional_settings.php?cmid=' . $cmid;
    $logbtnlabel = "Invigilator Logs";
    $logbtn = '<a class="btn btn-primary" style="margin-left:5px" href="' . $logpageurl . '">' . $logbtnlabel . '</a>';
}

if ($submittype == 'Search' && $searchkey != null) {
    $searchform = '<form action="' . $CFG->wwwroot
        . '/mod/quiz/accessrule/invigilator/report.php"><input type="hidden" id="courseid" name="courseid" value="'
        . $courseid . '"><input type="hidden" id="cmid" name="cmid" value="' . $cmid
        . '"><input style="width:250px" type="text" id="searchKey" name="searchKey" placeholder="Search by email" value="'
        . $searchkey . '"><input type="submit" name="submitType" value="Search">'
        . '<input type="submit" name="submitType" value="clear"></form>';
} else if ($submittype == 'clear') {
    $searchform = '<form action="'
        . $CFG->wwwroot . '/mod/quiz/accessrule/invigilator/report.php"><input type="hidden" id="courseid" name="courseid" value="'
        . $courseid . '"><input type="hidden" id="cmid" name="cmid" value="'
        . $cmid . '"><input style="width:250px" type="text" id="searchKey" name="searchKey" placeholder="Search by email">'
        . '<input type="submit" name="submitType" value="Search"></form>';
} else {
    $searchform = '<form action="'
        . $CFG->wwwroot . '/mod/quiz/accessrule/invigilator/report.php"><input type="hidden" id="courseid" name="courseid" value="'
        . $courseid . '"><input type="hidden" id="cmid" name="cmid" value="'
        . $cmid . '">'
        . '<input style="width:250px" type="text" id="searchKey" name="searchKey" placeholder="Search by email">'
        . '<input type="submit" name="submitType" value="Search"></form>';
}

if (
    has_capability('quizaccess/invigilator:deletescreenshot', $context, $USER->id)
    && $studentid != null
    && $cmid != null
    && $courseid != null
    && $reportid != null
    && $logaction == "delete"
) {
    $DB->delete_records('quizaccess_invigilator_logs', array('courseid' => $courseid, 'cmid' => $cmid, 'userid' => $studentid));
    // Delete users file (webcam images).
    $filesql = 'SELECT * FROM {files} WHERE userid = :studentid  AND contextid = :contextid' .
        ' AND component = \'quizaccess_invigilator\' AND filearea = \'picture\'';

    $params = array();
    $params["studentid"] = $studentid;
    $params["contextid"] = $context->id;

    $usersfile = $DB->get_records_sql($filesql, $params);

    $fs = get_file_storage();
    foreach ($usersfile as $file) :
        // Prepare file record object.
        $fileinfo = array(
            'component' => 'quizaccess_invigilator',
            'filearea' => 'picture',     // Usually = table name.
            'itemid' => $file->itemid,               // Usually = ID of row in table.
            'contextid' => $context->id, // ID of context.
            'filepath' => '/',           // Any path beginning and ending in /.
            'filename' => $file->filename
        ); // Any filename.

        // Get file.
        $file = $fs->get_file(
            $fileinfo['contextid'],
            $fileinfo['component'],
            $fileinfo['filearea'],
            $fileinfo['itemid'],
            $fileinfo['filepath'],
            $fileinfo['filename']
        );

        // Delete it if it exists.
        if ($file) {
            $file->delete();
        }
    endforeach;
    $url2 = new moodle_url(
        '/mod/quiz/accessrule/invigilator/report.php',
        array(
            'courseid' => $courseid,
            'cmid' => $cmid
        )
    );
    redirect($url2, get_string('imgdlt', 'quizaccess_invigilator'), -11);
}

echo $OUTPUT->header();
echo '<div id="main"><h2>' . get_string('invigilatorreports', 'quizaccess_invigilator') . ''
    . $quiz->name . '</h2>' . '<br/><br/><div style="float: left">' . $searchform . '</div>' . '<div style="float: right">'
    . $settingsbtn . $logbtn . '</div><br/><br/><div class="box generalbox m-b-1 adminerror alert alert-info p-y-1">'
    . get_string('screenshot', 'quizaccess_invigilator') . '</div>';

// Report print.
if (
    has_capability('quizaccess/invigilator:viewreport', $context, $USER->id) &&
    $cmid != null &&
    $courseid != null
) {

    // Check if report if for some user.
    if ($studentid != null && $cmid != null && $courseid != null && $reportid != null) {
        // Report for this user.
        $sql = "SELECT e.id as reportid, e.userid as studentid, e.screenshot as screenshot, e.timecreated as timecreated, " .
            "u.firstname as firstname, u.lastname as lastname, u.email as email" .
            " FROM  {quizaccess_invigilator_logs} e INNER JOIN {user} u  ON u.id = e.userid"
            . " WHERE e.courseid = '$courseid' AND e.cmid = '$cmid' AND u.id = '$studentid' AND e.id = '$reportid'";
    }

    if ($studentid == null && $cmid != null && $courseid != null) {
        // Report for all users.
        $sql = "SELECT DISTINCT e.userid as studentid, u.firstname as firstname, u.lastname as lastname, u.email as email," .
            " max(e.screenshot) as screenshot, max(e.id) as reportid, max(e.timecreated) as timecreated" .
            " FROM  {quizaccess_invigilator_logs} e INNER JOIN {user} u ON u.id = e.userid" .
            " WHERE e.courseid = '$courseid' AND e.cmid = '$cmid'" .
            " GROUP BY e.userid, u.firstname, u.lastname, u.email";
    }

    if ($studentid == null && $cmid != null && $searchkey != null && $submittype == "clear") {
        // Report for searched users.
        $sql = "SELECT DISTINCT e.userid as studentid, u.firstname as firstname, u.lastname as lastname, u.email as email," .
            " max(e.screenshot) as screenshot, max(e.id) as reportid, max(e.timecreated) as timecreated" .
            " FROM  {quizaccess_invigilator_logs} e INNER JOIN {user} u ON u.id = e.userid" .
            " WHERE e.courseid = '$courseid' AND e.cmid = '$cmid'" .
            " GROUP BY e.userid, u.firstname, u.lastname, u.email";
    }

    if ($studentid == null && $cmid != null && $searchkey != null && $submittype == "Search") {
        // Report for searched users.
        $sql = "SELECT DISTINCT e.userid as studentid, u.firstname as firstname, u.lastname as lastname, u.email as email," .
            " max(e.screenshot) as screenshot, max(e.id) as reportid, max(e.timecreated) as timecreated" .
            " FROM  {quizaccess_invigilator_logs} e INNER JOIN {user} u ON u.id = e.userid" .
            " WHERE (e.courseid = '$courseid' AND e.cmid = '$cmid' AND "
            . $DB->sql_like('u.firstname', ':firstnamelike', false) . ") OR " . "(e.courseid = '$courseid' AND e.cmid = '$cmid' AND "
            . $DB->sql_like('u.email', ':emaillike', false) . ") OR " . "(e.courseid = '$courseid' AND e.cmid = '$cmid' AND "
            . $DB->sql_like('u.lastname', ':lastnamelike', false) . ")group by e.userid, u.firstname, u.lastname, u.email";
    }

    // Print report.
    $table = new flexible_table('invigilator-report-' . $COURSE->id . '-' . $cmid);

    $table->define_columns(array('fullname', 'email', 'dateverified', 'actions'));
    $table->define_headers(
        array(
            get_string('user'),
            get_string('email'),
            get_string('dateverified', 'quizaccess_invigilator'),
            get_string('actions', 'quizaccess_invigilator')
        )
    );
    $table->define_baseurl($url);

    $table->set_attribute('cellpadding', '5');
    $table->set_attribute('class', 'generaltable generalbox reporttable');
    $table->setup();

    // Prepare data.
    if ($studentid == null && $cmid != null && $searchkey != null && $submittype == "Search") {
        // Report for searched users.
        $params = ['firstnamelike' => "%$searchkey%", 'lastnamelike' => "%$searchkey%", 'emaillike' => "%$searchkey%"];
        $sqlexecuted = $DB->get_records_sql($sql, $params);
    } else {
        $sqlexecuted = $DB->get_records_sql($sql);
    }


    foreach ($sqlexecuted as $info) {
        $data = array();
        $data[] = '<a href="' . $CFG->wwwroot . '/user/view.php?id='
            . $info->studentid . '&course=' . $courseid . '" target="_blank">' . $info->firstname . ' ' . $info->lastname . '</a>';

        $data[] = $info->email;

        $data[] = date("Y/M/d H:m:s", $info->timecreated);

        $con = "return confirm('Are you sure want to delete the pictures?');";
        $btn = '<a onclick="' . $con . '" href="?courseid=' . $courseid . '&quizid=' . $cmid . '&cmid='
            . $cmid . '&studentid=' . $info->studentid . '&reportid='
            . $info->reportid . '&logaction=delete"><i class="icon fa fa-trash fa-fw "></i></a>';
        $data[] = '<a href="?courseid=' . $courseid . '&quizid=' . $quiz->id . '&cmid='
            . $cmid . '&studentid=' . $info->studentid . '&reportid=' . $info->reportid . '">'
            . '<i class="icon fa fa-folder-o fa-fw "></i>' . '</a>' . $btn;

        $table->add_data($data);
    }
    $table->finish_html();


    // Print image results.
    if ($studentid != null && $cmid != null && $courseid != null && $reportid != null) {
        $data = array();
        $sql = "SELECT e.id as reportid, e.userid as studentid, e.screenshot as screenshot," .
            " e.timecreated as timecreated, u.firstname as firstname," .
            " u.lastname as lastname, u.email as email" .
            " FROM {quizaccess_invigilator_logs} e INNER JOIN {user} u  ON u.id = e.userid" .
            " WHERE e.courseid = '$courseid' AND e.cmid = '$cmid' AND u.id = '$studentid'";

        $sqlexecuted = $DB->get_records_sql($sql);
        echo '<h3>' . get_string('picturesusedreport', 'quizaccess_invigilator') . '</h3>';

        $tablepictures = new flexible_table('invigilator-report-pictures' . $COURSE->id . '-' . $cmid);

        $tablepictures->define_columns(
            array(
                get_string('name', 'quizaccess_invigilator'),
                get_string('screenshot', 'quizaccess_invigilator')
            )
        );
        $tablepictures->define_headers(
            array(
                get_string('name', 'quizaccess_invigilator'),
                get_string('screenshot', 'quizaccess_invigilator')
            )
        );
        $tablepictures->define_baseurl($url);

        $tablepictures->set_attribute('cellpadding', '2');
        $tablepictures->set_attribute('class', 'generaltable generalbox reporttable');

        $tablepictures->setup();
        $pictures = '';

        $user = core_user::get_user($studentid);

        foreach ($sqlexecuted as $info) {
            $d = basename($info->screenshot, '.png');
            $imgid = "reportid-" . $info->reportid;

            $pictures .= $info->screenshot ? '<a href="' . $info->screenshot . '" data-lightbox="procImages"' . ' data-title ="'
                . $info->firstname . ' ' . $info->lastname . '">' . '<img id="'
                . $imgid . '" width="100" src="' . $info->screenshot . '" alt="'
                . $info->firstname . ' ' . $info->lastname . '" data-lightbox="'
                . basename($info->screenshot, '.png') . '"/></a>' : '';
        }

        $userinfo = '<table border="0" width="110" height="160px">'
            . '<tr height="120" style="background-color: transparent;"><td style="border: unset;">'
            . $OUTPUT->user_picture($user, array('size' => 100)) . '</td></tr><tr height="50"><td style="border: unset;"><b>'
            . $info->firstname . ' ' . $info->lastname . '</b></td></tr><tr height="50"><td style="border: unset;"><b>'
            . $info->email . '</b></td></tr></table>';

        $datapictures = array(
            $userinfo,
            $pictures
        );
        $tablepictures->add_data($datapictures);
        $tablepictures->finish_html();
    }
} else {
    // User has not permissions to view this page.
    echo '<div class="box generalbox m-b-1 adminerror alert alert-danger p-y-1">' .
        get_string('notpermissionreport', 'quizaccess_invigilator') . '</div>';
}
echo '</div>';
echo $OUTPUT->footer();
