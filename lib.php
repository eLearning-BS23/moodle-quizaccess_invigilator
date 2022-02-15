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
 * Lib for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */


defined('MOODLE_INTERNAL') || die();
/**
 * Serve the files.
 *
 * @param stdClass $course the course object.
 * @param stdClass $cm the course module object.
 * @param context $context the context.
 * @param string $filearea the name of the file area.
 * @param array $args extra arguments (itemid, path).
 * @param bool $forcedownload whether or not force download.
 * @param array $options additional options affecting the file serving.
 * @return bool false if the file not found, just send the file otherwise and do not return anything.
 */
function quizaccess_invigilator_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    $itemid = array_shift($args);
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' .implode('/', $args) . '/';
    }
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'quizaccess_invigilator', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Get invigilator settings values.
 *
 * @param String $settingtype the settingstype.
 * @return String.
 */
function quizaccess_invigilator_get_invigilator_settings($settingtype) {
    $value = "";
    global $DB;
    $settingssql = "SELECT * FROM {config_plugins} WHERE plugin = 'quizaccess_invigilator' AND name = '$settingtype'";
    $settingsdata = $DB->get_records_sql($settingssql);
    if (count($settingsdata) > 0) {
        foreach ($settingsdata as $row) {
            $value = $row->value;
        }
    }
    return $value;
}
