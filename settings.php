<?php
/**
 * Implementaton for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


global $ADMIN;

if ($hassiteconfig) {
    $settings->add(new admin_setting_configtext('quizaccess_invigilator/screenshotdelay',
        get_string('setting:screenshotdelay', 'quizaccess_invigilator'),
        get_string('setting:screenshotdelay_desc', 'quizaccess_invigilator'), 30, PARAM_INT));

    $settings->add(new admin_setting_configtext('quizaccess_invigilator/screenshotwidth',
        get_string('setting:screenshotwidth', 'quizaccess_invigilator'),
        get_string('setting:screenshotwidth_desc', 'quizaccess_invigilator'), 720, PARAM_INT));

}


