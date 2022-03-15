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
 * Privacy Subsystem implementation for quizaccess_invigilator.
 * @package quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator\privacy;

use core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die();

class provider implements
    // This plugin does store personal user data.
    \core_privacy\local\metadata\provider
{

    public static function get_metadata(collection $collection): collection
    {

        $collection->add_subsystem_link(
            'core_files',
            [],
            'privacy:metadata:core_files'
        );

        // Stores all validations for reporting.
        $collection->add_database_table(
            'quizaccess_invigilator_logs',
            [
                'userid' => 'privacy:metadata:quizaccess_invigilator_logs:userid',
                'screenshot' => 'privacy:metadata:quizaccess_invigilator_logs:screenshot'
            ],
            'privacy:metadata:quizaccess_invigilator_logs'
        );

        return $collection;
    }
}
