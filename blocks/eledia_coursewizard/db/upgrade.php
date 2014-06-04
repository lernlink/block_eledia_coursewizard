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
 * @author Andreas Grabs <support@eledia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package eledia_coursewizard
 */

/**
 * Handles upgrading instances of this block.
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_eledia_coursewizard_upgrade($oldversion, $block) {
    global $DB;


    if ($oldversion < 2014060402) {
        // Change value of config setting userfield.
        // If the field is not a field from user table so it is a user_profile_field.
        // Those values has to be prefixed with "user_profile_".
        $config = get_config('block_eledia_coursewizard');
        $userfield = $config->userfield;

        // Load a dummy user to check the profile fields.
        if ($testuser = $DB->get_record('user', array('username' => 'guest'))) {
            if (!isset($testuser->{$userfield})) {
                // The userfield name is a user_profile_field
                $userfield = 'profile_field_'.$userfield;
                set_config('userfield', $userfield, 'block_eledia_coursewizard');
            }
        }

        // Savepoint reached.
        upgrade_block_savepoint(true, 2014060402, 'eledia_coursewizard');
    }

    return true;
}
