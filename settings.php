<?php
// This file is part of Moodle - http://moodle.org/

/**
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configduration(
        'auth_invitation/defaultexpiry',
        get_string('defaultexpiry', 'auth_invitation'),
        get_string('defaultexpiry_desc', 'auth_invitation'),
        DAYSECS
    ));

    $settings->add(new admin_setting_configtextarea(
        'auth_invitation/emailtemplate',
        get_string('emailtemplate', 'auth_invitation'),
        get_string('emailtemplate_desc', 'auth_invitation'),
        get_string('defaultemailtemplate', 'auth_invitation')
    ));

    $coursechoices = $DB->get_records_menu('course', ['visible' => 1], 'fullname ASC', 'id, fullname');
    if (!empty($coursechoices)) {
        unset($coursechoices[SITEID]);
        foreach ($coursechoices as $id => $fullname) {
            $coursechoices[$id] = format_string($fullname);
        }
    } else {
        $coursechoices = [];
    }

    $settings->add(new admin_setting_configmulticheckbox(
        'auth_invitation/defaultcourses',
        get_string('defaultcourses', 'auth_invitation'),
        get_string('defaultcourses_desc', 'auth_invitation'),
        [],
        $coursechoices
    ));

    $settings->add(new admin_setting_configcheckbox(
        'auth_invitation/allowresend',
        get_string('allowresend', 'auth_invitation'),
        get_string('allowresend_desc', 'auth_invitation'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'auth_invitation/maxresend',
        get_string('maxresend', 'auth_invitation'),
        get_string('maxresend_desc', 'auth_invitation'),
        5,
        PARAM_INT
    ));
}

$ADMIN->add('authsettings', new admin_externalpage(
    'authinvitationinvite',
    get_string('invitepagetitle', 'auth_invitation'),
    new moodle_url('/auth/invitation/invite.php'),
    'auth/invitation:invite'
));

$ADMIN->add('authsettings', new admin_externalpage(
    'authinvitationmanage',
    get_string('managepagetitle', 'auth_invitation'),
    new moodle_url('/auth/invitation/manage.php'),
    'auth/invitation:invite'
));
