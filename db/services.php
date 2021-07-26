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


