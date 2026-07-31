<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('auth_invitation_manage');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$action  = optional_param('action', '', PARAM_ALPHA);
$id      = optional_param('id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$manageurl = new moodle_url('/auth/invitation/manage.php');

if (!empty($action) && !empty($id)) {
    require_sesskey();

    if ($action === 'revoke') {
        if (!$confirm) {
            echo $OUTPUT->header();
            $continueurl = new moodle_url('/auth/invitation/manage.php', [
                'action'  => 'revoke',
                'id'      => $id,
                'confirm' => 1,
                'sesskey' => sesskey(),
            ]);
            echo $OUTPUT->confirm(
                get_string('confirmrevoke', 'auth_invitation'),
                $continueurl,
                $manageurl
            );
            echo $OUTPUT->footer();
            exit;
        }

        \auth_invitation\invitation_manager::cancel_invitation($id);
        redirect($manageurl, get_string('invitationrevoked', 'auth_invitation'));
    }

    if ($action === 'resend') {
        if (!$confirm) {
            echo $OUTPUT->header();
            $continueurl = new moodle_url('/auth/invitation/manage.php', [
                'action'  => 'resend',
                'id'      => $id,
                'confirm' => 1,
                'sesskey' => sesskey(),
            ]);
            echo $OUTPUT->confirm(
                get_string('confirmresend', 'auth_invitation', 'Resend this invitation email?'),
                $continueurl,
                $manageurl
            );
            echo $OUTPUT->footer();
            exit;
        }

        \auth_invitation\invitation_manager::resend_invitation($id);
        redirect($manageurl, get_string('invitationresent', 'auth_invitation'));
    }
}

echo $OUTPUT->header();

echo html_writer::div(
    $OUTPUT->single_button(
        new moodle_url('/auth/invitation/invite.php'),
        get_string('invitenewuser', 'auth_invitation'),
        'get',
        ['class' => 'btn-primary']
    ),
    'd-flex justify-content-end mb-3'
);

$table = new \auth_invitation\table\invitations_table('auth-invitation-list');
$table->baseurl = $manageurl;
$table->out(30, true);

echo $OUTPUT->footer();
