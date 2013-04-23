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

require_once('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/blocks/eledia_coursewizard/createcourse_form.php');
require_once(dirname(__FILE__) . '/createcourse_step2_form.php');

error_reporting(E_ALL);

$id         = optional_param('id', 0, PARAM_INT);       // Course id.
$categoryid = optional_param('category', 0, PARAM_INT); // Course category.

if (empty($id)) {
    $pageparams = array('category'=>$categoryid);
}

$pageparams = array('id'=>$id);
$PAGE->set_url('/blocks/eledia_coursewizard/createcourse_step2.php', $pageparams);

global $CFG, $DB, $PAGE, $COURSE;

$categoryid = 1;
require_login();
$category = $DB->get_record('course_categories', array('id'=>$categoryid), '*', MUST_EXIST);
$catcontext = context_coursecat::instance($category->id);
require_capability('moodle/course:create', $catcontext);
$PAGE->set_context($catcontext);
$user = new StdClass();

$editform = new coursewizard_enrol_users_form(null, array('user'=>$user));

if ($data = $editform->get_data()) {
    $cid = $data->id;
    
    if($data->email != '') {
    
        $mailparams = $DB->get_records_sql("SELECT name, value
                                            FROM {config_plugins}
                                            WHERE plugin='block_eledia_coursewizard'");

        $usernames = $data->email;
        $usernames = str_replace("\n", " ", $usernames);
        $usernames = str_replace("\r", " ", $usernames);
        $usernames = str_replace(" ", "", $usernames);
        $usernames = explode(",", $usernames);

        foreach ($usernames as $username) {

            $passhash = md5($username.time());
            $password = substr($passhash, 0, 12);

// -------- Create new user.
            $newuser = create_user_record($username, $password, 'email');
            $newuser->email = $username;
            $newuser->emailstop = 0;
            $newuser->maildisplay = 2;
            $newuser->policyagreed = 0;
            // $newuser->confirm = 1;

            $DB->update_record('user', $newuser);
            set_user_preference('auth_forcepasswordchange', 1, $newuser->id);

            // Enrol into new course.
            enrol_try_internal_enrol($cid, $newuser->id, 5);

// -------- E-Mail to new user.
            $contact = get_admin();

            $mailuser = new stdClass(); // Dummy user because email_to_user needs a user.
            $mailuser->firstname = null;
            $mailuser->lastname = null;
            $mailuser->email = $username;
            $mailuser->id = 0; // To prevent anything annoying happening.

            $content = $mailparams['mailcontent']->value."\n\n";
            $content .= "Moodle-URL: ".$CFG->wwwroot."\n";
            $content .= "Your username: ".$username."\n";
            $content .= "Your password: ".$password;

            email_to_user($mailuser, $contact, $mailparams['mailsubject']->value, $content);
        }
    }
    $url = $CFG->wwwroot."/course/view.php?id=".$cid;
    redirect($url);
}

$PAGE->set_context($catcontext);

echo $OUTPUT->header();
$editform->display();
echo $OUTPUT->footer();