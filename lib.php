<?php
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
function get_invigilator_settings($settingtype) {
    $value = "";
    global $DB;
    $settingssql = "SELECT * FROM {config_plugins} 
WHERE plugin = 'quizaccess_invigilator' AND name = '$settingtype'";
    $settingsdata = $DB->get_records_sql($settingssql);
    if (count($settingsdata) > 0) {
        foreach ($settingsdata as $row) {
            $value = $row->value;
        }
    }
    return $value;
}
