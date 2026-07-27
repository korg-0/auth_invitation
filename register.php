<?php
// This file is part of Moodle - http://moodle.org/

/**
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
use auth_invitation\form\password_form;
use auth_invitation\invitation_manager;

$token = required_param('token', PARAM_ALPHANUM);

$PAGE->set_url('/auth/invitation/register.php', ['token' => $token]);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('completeregistration', 'auth_invitation'));
$PAGE->set_heading(format_string(get_site()->fullname));

if (isloggedin() && !isguestuser()) {
    redirect(new moodle_url('/my/'));
}

$invitation = invitation_manager::validate_token($token);

if (!$invitation) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('invalidorexpiredtoken', 'auth_invitation'), 'error');
    echo $OUTPUT->footer();
    exit;
}

$mform = new password_form(null, ['invitation' => $invitation]);
$mform->set_data(['token' => $token]);

if ($data = $mform->get_data()) {

    $invitation = invitation_manager::validate_token($data->token);
    if (!$invitation) {
        redirect($PAGE->url, get_string('invalidorexpiredtoken', 'auth_invitation'), null,
            \core\output\notification::NOTIFY_ERROR);
    }

    $newuser = new stdClass();
    $newuser->auth        = 'invitation';
    $newuser->firstname   = $invitation->firstname;
    $newuser->lastname    = $invitation->lastname;
    $newuser->email       = $invitation->email;
    $newuser->username    = core_text::strtolower($invitation->email);
    $newuser->password    = $data->password;
    $newuser->confirmed   = 1;
    $newuser->mnethostid  = $CFG->mnet_localhost_id;
    $newuser->lang        = current_language();

    $userid = user_create_user($newuser, true, true);

    invitation_manager::complete_invitation($invitation->id, $userid);

    $courseids = invitation_manager::get_invitation_courses($invitation->id);
    if (!empty($courseids)) {
        invitation_manager::enrol_courses($userid, $courseids);
    }

    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

    complete_user_login($user);

    redirect(new moodle_url('/my/'), get_string('registrationcomplete', 'auth_invitation'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('completeregistration', 'auth_invitation'));
echo html_writer::tag('p', get_string('registrationintro', 'auth_invitation'));
$mform->display();
echo $OUTPUT->footer();
