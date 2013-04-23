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

class block_eledia_coursewizard extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_eledia_coursewizard');
    }

    public function get_content() {
        global $CFG, $DB, $COURSE;

        if (has_capability('moodle/course:create', $this->context)) {

            $this->content = new stdClass;
            $this->content->footer = '';
            $this->content->text  = '<div class="eledia_coursewizard">';
            $this->content->text .= "<a href=\"".$CFG->wwwroot."/blocks/eledia_coursewizard/createcourse.php\">Create a course</a>";
            $this->content->text .= '</div>';

            return $this->content;
        }
    }

    public function applicable_formats() {
        return array('site' => true, 'course' => true);
    }

    function has_config() {
        return true;
    }

    /**
     * Returns the role that best describes the eledia_coursewizard block.
     *
     * @return string
     */
    public function get_aria_role() {
        return 'navigation';
    }
}
