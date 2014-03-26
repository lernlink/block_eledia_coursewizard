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
 * @author Matthias Schwabe <support@eledia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package eledia_coursewizard
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class coursewizard_enrol_users_form extends moodleform {

    protected $user;

    function definition() {

        global $CFG, $DB;

        $cid  = optional_param('id', 0, PARAM_INT);  // Course id.
        $ocid = required_param('cid', PARAM_INT);  // Origin course id.

		$coursecontext = context_course::instance($ocid);
		$systemcontext = context_system::instance();

		$mform =& $this->_form;

		if (has_capability('block/eledia_coursewizard:create_user', $coursecontext) OR
			has_capability('moodle/user:create', $systemcontext)) {

			$user = $this->_customdata['user'];
			$this->user = $user;

			$mform->addElement('header', 'general', get_string('addusers', 'block_eledia_coursewizard'));
			$mform->addElement('static', 'description', '', get_string('createuser_desc', 'block_eledia_coursewizard'));
			$mform->addElement('textarea', 'email', get_string('emailuser', 'block_eledia_coursewizard'),
                           'wrap="virtual", rows="10" cols="100"');

			$mform->setType('email', PARAM_TEXT);

			$mform->addElement('hidden', 'id', $cid);
			$mform->setType('id', PARAM_INT);

			$mform->addElement('hidden', 'cid', null);
			$mform->setType('cid', PARAM_INT);
			$mform->setConstant('cid', $ocid);

			$this->add_action_buttons(false, get_string('createuser_button', 'block_eledia_coursewizard'));

			$mform->addElement('static', 'backbutton', '', '<br><a href='.$CFG->wwwroot.'/course/view.php?id='.$cid.'>'
			.get_string('backbutton_create2', 'block_eledia_coursewizard').'</a>');

			$this->set_data($user);

		} else {
			$url = $CFG->wwwroot."/course/view.php?id=".$cid;
			redirect($url);
		}
    }
}
