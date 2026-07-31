<?php
// This file is part of Moodle - http://moodle.org/

/**
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_once($CFG->libdir . '/adminlib.php');
use auth_invitation\form\invite_form;
use auth_invitation\invitation_manager;

admin_externalpage_setup('auth_invitation_invite');

$returnurl = new moodle_url('/auth/invitation/manage.php');

$mform = new invite_form();

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    try {
        invitation_manager::create_invitation($data);
        redirect($returnurl, get_string('invitationsent', 'auth_invitation'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    } catch (\moodle_exception $e) {
        redirect($PAGE->url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('invitepagetitle', 'auth_invitation'));

echo html_writer::div(
    html_writer::link($returnurl, get_string('managepagetitle', 'auth_invitation')),
    'mb-3'
);

$mform->display();

echo $OUTPUT->footer();
