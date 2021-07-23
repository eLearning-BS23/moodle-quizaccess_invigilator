<?php
/**
 * Link Generator for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * link_generator class.
 *
 * @copyright  2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class link_generator {

    /**
     * Get a link to force the download of the file over https or invigilator protocols.
     *
     * @param string $courseid Course ID.
     * @param string $cmid Course module ID.
     * @param bool $invigilator Whether to use a proctoring:// scheme or fall back to http:// scheme.
     * @param bool $secure Whether to use HTTPS or HTTP protocol.
     * @return string A URL.
     */
    public static function get_link(string $courseid, string $cmid, $proctoring = false, $secure = true) : string {
        // Check if course module exists.
        get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);

        $url = new moodle_url('/mod/quiz/accessrule/invigilator/report.php?courseid=' . $courseid.'&cmid=' . $cmid);
        if ($proctoring) {
            $secure ? $url->set_scheme('invigilators') : $url->set_scheme('invigilator');
        } else {
            $secure ? $url->set_scheme('https') : $url->set_scheme('http');
        }
        return $url->out();
    }
}
