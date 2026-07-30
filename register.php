<?php
// This file is part of Moodle - http://moodle.org/

/**
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/user/lib.php');

$PAGE->set_url(new moodle_url('/auth/invitation/register.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');

$firstname  = optional_param('firstname', '', PARAM_TEXT);
$lastname   = optional_param('lastname', '', PARAM_TEXT);
$email      = optional_param('email', '', PARAM_RAW_TRIMMED);
$expirytime = optional_param('expirytime', 0, PARAM_INT);
$token      = optional_param('token', '', PARAM_ALPHANUM);

$invitation = \auth_invitation\invitation_manager::validate_token(
    $firstname,
    $lastname,
    $email,
    $expirytime,
    $token
);

if (!$invitation) {
    throw new \moodle_exception('invalidorexpiredtoken', 'auth_invitation');
}

$existinguser = $DB->get_record('user', [
    'email' => $invitation->email,
    'mnethostid' => $CFG->mnet_localhost_id,
    'deleted' => 0
]);

if ($existinguser) {
    \auth_invitation\invitation_manager::complete_invitation($invitation->id, $existinguser->id);

    $courseids = \auth_invitation\invitation_manager::get_invitation_courses($invitation->id);
    if (!empty($courseids)) {
        \auth_invitation\invitation_manager::enrol_courses($existinguser->id, $courseids);
    }

    complete_user_login($existinguser);
    redirect(new moodle_url('/my/'), get_string('registrationcomplete', 'auth_invitation'));
}

$user = new stdClass();
$user->auth          = 'invitation';
$user->confirmed     = 1;
$user->mnethostid    = $CFG->mnet_localhost_id;
$user->username      = trim(core_text::strtolower($invitation->email));
$user->email         = $invitation->email;
$user->firstname     = $invitation->firstname;
$user->lastname      = $invitation->lastname;
$user->lang          = current_language();
$user->timecreated   = time();
$user->timemodified  = time();

$password = !empty($invitation->temppassword) ? $invitation->temppassword : generate_password(10);
$user->password = hash_internal_user_password($password);

$userid = user_create_user($user, false, false);
$createduser = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

\auth_invitation\invitation_manager::complete_invitation($invitation->id, $userid);

$courseids = \auth_invitation\invitation_manager::get_invitation_courses($invitation->id);
if (!empty($courseids)) {
    \auth_invitation\invitation_manager::enrol_courses($userid, $courseids);
}

complete_user_login($createduser);

redirect(new moodle_url('/my/'), get_string('registrationcomplete', 'auth_invitation'));
