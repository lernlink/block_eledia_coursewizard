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
 * @author Matthias Schwabe <matthias.schwabe@eledia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package eledia_coursewizard
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('block_eledia_coursewizard_settings', '',
                   get_string('coursewizard_desc', 'block_eledia_coursewizard')));
    $settings->add(new admin_setting_configtext('block_eledia_coursewizard/mailsubject',
                   get_string('coursewizard_mailsubject_desc', 'block_eledia_coursewizard'), '',
                   get_string('coursewizard_mailsubject', 'block_eledia_coursewizard'), PARAM_TEXT, 50));
    $settings->add(new admin_setting_confightmleditor('block_eledia_coursewizard/mailcontent',
                   get_string('coursewizard_mailcontent_desc', 'block_eledia_coursewizard'), '',
                   get_string('coursewizard_mailcontent', 'block_eledia_coursewizard'), PARAM_RAW, 60, 10));
}
