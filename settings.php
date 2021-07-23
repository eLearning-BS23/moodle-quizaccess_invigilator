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


global $ADMIN;

if ($hassiteconfig) {
    $settings->add(new admin_setting_configtext('quizaccess_invigilator/screenshotdelay',
        get_string('setting:screenshotdelay', 'quizaccess_invigilator'),
        get_string('setting:screenshotdelay_desc', 'quizaccess_invigilator'), 30, PARAM_INT));

    $settings->add(new admin_setting_configtext('quizaccess_invigilator/screenshotwidth',
        get_string('setting:screenshotwidth', 'quizaccess_invigilator'),
        get_string('setting:screenshotwidth_desc', 'quizaccess_invigilator'), 720, PARAM_INT));

}


