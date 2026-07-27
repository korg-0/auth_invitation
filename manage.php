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
use auth_invitation\invitation_manager;
use auth_invitation\table\invitations_table;

admin_externalpage_setup('authinvitationmanage');

$action = optional_param('action', '', PARAM_ALPHA);
$id     = optional_param('id', 0, PARAM_INT);

if ($id && in_array($action, ['resend', 'cancel'], true)) {
    require_sesskey();

    try {
        if ($action === 'resend') {
            invitation_manager::resend_invitation($id);
            $message = get_string('invitationresent', 'auth_invitation');
        } else {
            invitation_manager::cancel_invitation($id);
            $message = get_string('invitationcancelled', 'auth_invitation');
        }
        redirect($PAGE->url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
    } catch (\moodle_exception $e) {
        redirect($PAGE->url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managepagetitle', 'auth_invitation'));

echo html_writer::div(
    html_writer::link(
        new moodle_url('/auth/invitation/invite.php'),
        get_string('invitenewuser', 'auth_invitation'),
        ['class' => 'btn btn-primary']
    ),
    'mb-3'
);

if ($action === 'view' && $id) {
    require_sesskey();
    $invitation = $DB->get_record('auth_invitation', ['id' => $id], '*', MUST_EXIST);

    echo $OUTPUT->box_start('generalbox mb-3');
    echo html_writer::tag('p', html_writer::tag('strong', get_string('firstname') . ': ') . s($invitation->firstname));
    echo html_writer::tag('p', html_writer::tag('strong', get_string('lastname') . ': ') . s($invitation->lastname));
    echo html_writer::tag('p', html_writer::tag('strong', get_string('email') . ': ') . s($invitation->email));
    echo html_writer::tag('p', html_writer::tag('strong', get_string('createddate', 'auth_invitation') . ': ')
        . userdate($invitation->timecreated, get_string('strftimedatetime', 'langconfig')));
    echo html_writer::tag('p', html_writer::tag('strong', get_string('expirydate', 'auth_invitation') . ': ')
        . userdate($invitation->expirytime, get_string('strftimedatetime', 'langconfig')));
    echo $OUTPUT->box_end();
}

$table = new invitations_table('auth-invitation-list');
$table->define_baseurl($PAGE->url);
$table->out(25, true);

echo $OUTPUT->footer();
