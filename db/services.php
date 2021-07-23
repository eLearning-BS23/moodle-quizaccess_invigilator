<?php
/**
 * Services for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */


defined('MOODLE_INTERNAL') || die;

$functions = array(
    'quizaccess_invigilator_send_screenshot' => array(
        'classname' => 'quizaccess_invigilator_external',
        'methodname' => 'send_screenshot',
        'description' => 'Send screenshot on the given session.',
        'type' => 'write',
        'ajax'        => true,
        'capabilities' => 'quizaccess/invigilator:sendscreenshot',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )
);


