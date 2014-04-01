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
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/coursecatlib.php');

class eledia_course_edit_form extends moodleform {
    protected $course;
    protected $context;

    function definition() {
        global $CFG, $DB, $PAGE, $COURSE;

        $mform = $this->_form;
        $PAGE->requires->yui_module('moodle-course-formatchooser', 'M.course.init_formatchooser',
               array(array('formid' => $mform->getAttribute('id'))));

        $editoroptions = $this->_customdata['editoroptions'];
        $returnto      = $this->_customdata['returnto'];
        $cid           = $this->_customdata['cid'];

		$course = $DB->get_record('course', array('id' => $cid), 'id, category');
		if ($course) { // Should always exist, but just in case.
			$categoryid = $course->category;
		}

        $systemcontext = context_system::instance();
		if ($COURSE->category != 0) {
			$categorycontext = context_coursecat::instance($categoryid);
		} else {
			$categorycontext = $systemcontext;
		}

        $coursecontext = context_course::instance($cid);

		if (has_capability('block/eledia_coursewizard:create_course', $coursecontext) OR
			has_capability('moodle/course:create', $categorycontext)) {

			$this->course = $course;
			$this->context = $coursecontext;

			$mform->addElement('header', 'general', get_string('general', 'form'));

			$mform->addElement('hidden', 'returnto', null);
			$mform->setType('returnto', PARAM_ALPHANUM);
			$mform->setConstant('returnto', $returnto);

			$mform->addElement('hidden', 'cid', null);
			$mform->setType('cid', PARAM_INT);
			$mform->setConstant('cid', $cid);

			// Verify permissions to change course category or keep current.
            if (has_capability('block/eledia_coursewizard:change_category', $coursecontext)) {
                $displaylist = coursecat::make_categories_list();
                $mform->addElement('select', 'category', get_string('coursecategory'), $displaylist);
                $mform->addHelpButton('category', 'coursecategory');
                $mform->setDefault('category', $categoryid);
            } else {
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                $mform->setConstant('category', $categoryid);
            }

			$mform->addElement('text', 'fullname', get_string('fullnamecourse'), 'maxlength="254" size="50"');
			$mform->addHelpButton('fullname', 'fullnamecourse');
			$mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
			$mform->setType('fullname', PARAM_TEXT);

			$mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="20"');
			$mform->addHelpButton('shortname', 'shortnamecourse');
			$mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
			$mform->setType('shortname', PARAM_TEXT);

			$mform->addElement('editor', 'summary_editor', get_string('coursesummary'), null, $editoroptions);
			$mform->addHelpButton('summary_editor', 'coursesummary');
			$mform->setType('summary_editor', PARAM_RAW);

			$courseformats = get_sorted_course_formats(true);
			$formcourseformats = array();
			foreach ($courseformats as $courseformat) {
				$formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
			}

			if (isset($course->format)) {
				$course->format = course_get_format($course)->get_format(); // Replace with default if not found.
				if (!in_array($course->format, $courseformats)) {
					// This format is disabled. Still display it in the dropdown.
					$formcourseformats[$course->format] = get_string('withdisablednote', 'moodle',
                        get_string('pluginname', 'format_'.$course->format));
				}
			}

			$this->add_action_buttons();

			$mform->addElement('hidden', 'id', null);
			$mform->setType('id', PARAM_INT);

			// Finally set the current form data.
			$this->set_data($course);

		} else {
			$mform->addElement('static', 'norights', '', get_string('norights', 'block_eledia_coursewizard'));
			$mform->addElement('static', 'backbutton', '', '<br><a href='.$CFG->wwwroot.'/course/view.php?id='.$cid.'>'
					.get_string('backbutton_cancel', 'block_eledia_coursewizard').'</a>');
		}
	}

    function definition_after_data() {
        global $DB;

        $mform = $this->_form;

        // Add available groupings.
        if ($courseid = $mform->getElementValue('id') and $mform->elementExists('defaultgroupingid')) {
            $options = array();
            if ($groupings = $DB->get_records('groupings', array('courseid' => $courseid))) {
                foreach ($groupings as $grouping) {
                    $options[$grouping->id] = format_string($grouping->name);
                }
            }
            $gr_el =& $mform->getElement('defaultgroupingid');
            $gr_el->load($options);
        }
    }

    // Perform some extra moodle validation.
    function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        if ($foundcourses = $DB->get_records('course', array('shortname' => $data['shortname']))) {
            if (!empty($data['id'])) {
                unset($foundcourses[$data['id']]);
            }
            if (!empty($foundcourses)) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }
                $foundcoursenamestring = implode(',', $foundcoursenames);
                $errors['shortname'] = get_string('shortnametaken', '', $foundcoursenamestring);
            }
        }
        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));
        return $errors;
    }
}
