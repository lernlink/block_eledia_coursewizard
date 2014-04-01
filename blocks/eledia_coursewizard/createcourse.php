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

require_once('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/blocks/eledia_coursewizard/createcourse_form.php');
require_once($CFG->libdir . '/blocklib.php');

defined('MOODLE_INTERNAL') || die();
error_reporting(E_ALL);

$id         = optional_param('id', 0, PARAM_INT);  // Course id.
$cid        = required_param('cid', PARAM_INT);  // Origin course id.
$categoryid = optional_param('category', 0, PARAM_INT);  // Course category - can be changed in edit form.
$returnto   = optional_param('returnto', 0, PARAM_ALPHANUM);  // Generic navigation return page switch.

$pageparams = array('id' => $id);

if (empty($id)) {
    $pageparams = array('category' => $categoryid);
}

$PAGE->set_url('/blocks/eledia_coursewizard/eledia_coursewizard.php', $pageparams);

$course = null;
require_login();

if ($categoryid != 0) {
	$catcontext = context_coursecat::instance($categoryid);
} else {
	$catcontext = context_system::instance();
}

$PAGE->set_context($catcontext);

// Prepare course and the editor.
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

// Editor should respect category context if course context is not set.
$editoroptions['context'] = $catcontext;
$course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);

// First create the form.
$editform = new eledia_course_edit_form(null, array('course'=>$course, 'category' => $categoryid, 'editoroptions' => $editoroptions,
													'returnto' => $returnto, 'cid' => $cid));
if ($editform->is_cancelled()) {

    $url = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $cid));
    redirect($url);

} else if ($data = $editform->get_data()) {

    // In creating the course.
    $course = create_course($data, $editoroptions);

    // Get the context of the newly created course.
    $context = context_course::instance($course->id, MUST_EXIST);

    if (!empty($CFG->creatornewroleid) and !is_viewing($context, null, 'moodle/role:assign')
        and !is_enrolled($context, null, 'moodle/role:assign')) {
        // Deal with course creators - enrol them internally with default role.
        enrol_try_internal_enrol($course->id, $USER->id, $CFG->creatornewroleid);
    }

    if (!is_enrolled($context)) {
        // Redirect to manual enrolment page if possible.
        $instances = enrol_get_instances($course->id, true);
        foreach ($instances as $instance) {
            if ($plugin = enrol_get_plugin($instance->enrol)) {
                if ($plugin->get_manual_enrol_link($instance)) {
                    // We know that the ajax enrol UI will have an option to enrol.
                    redirect(new moodle_url('/blocks/eledia_coursewizard/createcourse_step2.php',
											array('id' => $course->id, 'cid' => $cid)));
                }
            }
        }
    }

    switch ($returnto) {
        case 'category':
        case 'topcat': // Redirecting to where the new course was created by default.
            $url = new moodle_url($CFG->wwwroot.'/course/category.php', array('id' => $categoryid));
            break;
        default:
            $url = new moodle_url('/blocks/eledia_coursewizard/createcourse_step2.php', array('id' => $course->id, 'cid' => $cid));
            break;
    }
    redirect($url);
}

$site = get_site();

$streditcoursesettings = get_string("editcoursesettings");
$straddnewcourse = get_string("addnewcourse");
$stradministration = get_string("administration");
$strcategories = get_string("categories");

$PAGE->navbar->add($stradministration, new moodle_url('/admin/index.php'));
$PAGE->navbar->add($strcategories, new moodle_url('/course/index.php'));
$PAGE->navbar->add($straddnewcourse);

$title = "$site->shortname: $straddnewcourse";
$fullname = $site->fullname;
$PAGE->set_title($title);
$PAGE->set_heading($fullname);

echo $OUTPUT->header();
$editform->display();
echo $OUTPUT->footer();
