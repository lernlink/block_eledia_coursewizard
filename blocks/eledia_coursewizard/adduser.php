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
require_once(dirname(__FILE__) . '/adduser_form.php');

error_reporting(E_ALL);

$id  = optional_param('id', 0, PARAM_INT);  // Course id.
$pageparams = array('id' => $id);
$PAGE->set_url('/blocks/eledia_coursewizard/adduser.php', $pageparams);

global $CFG, $DB, $PAGE, $USER;

require_login();
$context = context_course::instance($id);
$PAGE->set_context($context);
$user = new StdClass();
$config = get_config('block_eledia_coursewizard');

$editform = new coursewizard_add_users_form(null, array('user' => $user));

if ($data = $editform->get_data()) {
    $cid = $data->id;

    if ($data->email != '') {

        $mailparams = $DB->get_records_sql("SELECT name, value
                                            FROM {config_plugins}
                                            WHERE plugin='block_eledia_coursewizard'");

        $usernames = $data->email;
        $usernames = str_replace("\n", " ", $usernames);
        $usernames = str_replace("\r", " ", $usernames);
        $usernames = str_replace(" ", "", $usernames);
        $usernames = explode(",", $usernames);

        $invalidemails = Array();  // Array for invalid e-mail addresses.

        foreach ($usernames as $username) {

            if (validate_email($username)) {
                $contact = get_admin();
                $uname = $DB->get_record('user', array('email' => $username));

                if (empty($uname)) {  // New user => create, enrol and mail.

                    $passhash = md5($username.time());
                    $password = substr($passhash, 0, 12);

				 // Create new user.
                    $newuser = create_user_record($username, $password, 'email');
                    $newuser->email = $username;
                    $newuser->emailstop = 0;
                    $newuser->maildisplay = 2;
                    $newuser->policyagreed = 0;
                 // $newuser->confirm = 1;
                    $newuser->theme = $USER->theme;

                    if (!empty($config->userfield)) {
                        if (!empty($USER->profile[$config->userfield])) {
                            $field = $DB->get_record('user_info_field', array('shortname' => $config->userfield));
                            $user_data = new stdClass();
                            $user_data->userid = $newuser->id;
                            $user_data->fieldid = $field->id;
                            $user_data->data = $USER->profile[$config->userfield];
                            $DB->insert_record('user_info_data', $user_data);
                        }
                    }

                    $DB->update_record('user', $newuser);
                    set_user_preference('auth_forcepasswordchange', 1, $newuser->id);

                    enrol_try_internal_enrol($cid, $newuser->id, 5);  // Enrol into new course.

				 // E-Mail to new user.
                    $mailuser = new stdClass();  // Dummy user because email_to_user needs a user.
                    $mailuser->email = $username;
                    $mailuser->id = $newuser->id;

                    $mailuser->firstname = ' ';
                    $mailuser->lastname = ' ';
                    $mailuser->firstnamephonetic = ' ';
                    $mailuser->lastnamephonetic = ' ';
                    $mailuser->middlename = ' ';
                    $mailuser->alternatename = ' ';

                    $content = $mailparams['mailcontent']->value."\n\n";
                    $content .= "Moodle-URL: ".$CFG->wwwroot."\n";
                    $content .= "Your username: ".$username."\n";
                    $content .= "Your password: ".$password;

                    email_to_user($mailuser, $contact, $mailparams['mailsubject']->value, strip_tags($content), $content);

                } else {  // Existing user => enrol and mail.

                    enrol_try_internal_enrol($cid, $uname->id, 5);  // Enrol into new course.

                    $content = $mailparams['mailcontent_notnew']->value."\n\n";
                    $content .= "Moodle-URL: ".$CFG->wwwroot."\n";
                    $content .= "Moodle-Kurs: ".$CFG->wwwroot."/course/view.php?id=".$cid."\n";
                    email_to_user($uname, $contact, $mailparams['mailsubject_notnew']->value,
								  strip_tags($content), $content);
                }
            } else {
                $invalidemails[] = $username;
            }
        }
    }
    if (!empty($invalidemails)) {
        foreach ($invalidemails as $invalidemail) {
            echo get_string('invalidemail', 'block_eledia_coursewizard', $invalidemail);
        }
    } else {
        $url = $CFG->wwwroot."/enrol/users.php?id=".$cid;
        redirect($url);
    }
}

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$straddnewuser = get_string('addusers_button', 'block_eledia_coursewizard');
$stradministration = get_string("administration");
$strcategories = get_string("categories");

$PAGE->navbar->add($stradministration, new moodle_url('/admin/index.php'));
$PAGE->navbar->add($strcategories, new moodle_url('/course/index.php'));
$PAGE->navbar->add($course->shortname, $CFG->wwwroot.'/course/view.php?id='.$id);
$PAGE->navbar->add($straddnewuser);

$title = "$course->shortname: $straddnewuser";
$fullname = $course->fullname;
$PAGE->set_title($title);
$PAGE->set_heading($fullname);

echo $OUTPUT->header();
$editform->display();
echo $OUTPUT->footer();
