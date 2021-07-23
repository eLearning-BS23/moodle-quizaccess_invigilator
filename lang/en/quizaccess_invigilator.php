<?php
/**
 * Strings for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Invigilator';
$string['setting:screenshotdelay'] = "The delay between screenshots in seconds.";
$string['setting:screenshotdelay_desc'] = "Given value will be the delay in seconds between each screenshot";
$string['setting:screenshotwidth'] = "The width of the screenshot image in pixel.";
$string['setting:screenshotwidth_desc'] = "Given value will be the width of the screenshot. The image height will be scaled to that";
$string['invigilatorlabel'] = 'I agree with the validation process.';
$string['youmustagree'] = 'You must agree to validate your identity before continue.';
$string['notrequired'] = 'not required';
$string['invigilatorrequiredoption'] = 'must be acknowledged before starting an attempt';
$string['invigilatorrequired'] = 'Screenshot capture validation';
$string['warning:allowscreenshare'] = 'Please allow screen share.';
$string['invigilatorheader'] = '<strong>To continue with this quiz attempt you must share your screen. You must choose entire monitor in screen sharing option.</strong>';
$string['picturesreport'] = 'View invigilator report';
$string['screensharemsg'] = '<strong>* Please allow screenshare for entire monitor.</strong><br/><strong>* Please dont close this window or your attempt will be closed</strong><br/>';
$string['screenhtml'] = '<span><video id="invigilator-video-screen" width="320" height="240" autoplay></video></span><canvas id="invigilator-canvas-screen" style="display:none;"></canvas><img id="invigilator-photo-screen" alt="The picture will appear in this box." style="display:none;"/><span class="invigilator-output-screen" style="display:none;"></span><span id="invigilator-log-screen" style="display:none;"></span>';
$string['sharescreen'] = 'Allow screen share to continue';
$string['sharescreenbtnlabel'] = 'Share screen';
$string['quizaccess_invigilator_label'] = 'Invigilator';
$string['invigilatorreports'] = 'Invigilator Reports';
$string['invigilatorreportsdesc'] = 'Invigilator Reports shows screenshots taken during quiz';
$string['dateverified'] = 'Date';
$string['actions'] = 'Action';
$string['name'] = 'Name';
$string['screenshot'] = 'Screenshot';
$string['notpermissionreport'] = 'You are not permitted to see this report';
$string['picturesusedreport'] = 'Screenshots';
$string['summarypagedesc'] = 'Summery report shows the number of screenshot each quiz and course have. You can delete all screenshots of a particulart quiz/course.';
$string['settings:deleteallsuccess'] = 'Screenshots deleted successfully';
$string['reportidheader'] = "Row ID";
$string['coursenameheader'] = "Course Name";
$string['quiznameheader'] = "Quiz Name";
