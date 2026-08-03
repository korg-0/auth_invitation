<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if (!class_exists('admin_setting_course_autocomplete')) {
    class admin_setting_course_autocomplete extends \admin_setting_configmultiselect {
        public function output_html($data, $query = '') {
            global $PAGE;
            $PAGE->requires->js_call_amd('core/form-autocomplete', 'enhance', [
                '#' . $this->get_id(),
                true,
                null,
                get_string('searchcourses', 'core')
            ]);
            return parent::output_html($data, $query);
        }
    }
}

$ADMIN->add('users', new admin_category(
    'auth_invitation_category',
    get_string('userinvitations', 'auth_invitation')
));

$ADMIN->add('auth_invitation_category', new admin_externalpage(
    'auth_invitation_manage',
    get_string('manageinvitations', 'auth_invitation'),
    new moodle_url('/auth/invitation/manage.php'),
    'moodle/site:config'
));

$ADMIN->add('auth_invitation_category', new admin_externalpage(
    'auth_invitation_invite',
    get_string('invitepagetitle', 'auth_invitation'),
    new moodle_url('/auth/invitation/invite.php'),
    'moodle/site:config'
));

$ADMIN->add('auth_invitation_category', new admin_externalpage(
    'auth_invitation_logs',
    get_string('invitationlogs', 'auth_invitation'),
    new moodle_url('/auth/invitation/logs.php'),
    'moodle/site:config'
));

if ($ADMIN->fulltree) {
    global $DB;

    $courseoptions = $DB->get_records_menu('course', ['visible' => 1], 'fullname ASC', 'id, fullname');
    unset($courseoptions[SITEID]);
    foreach ($courseoptions as $id => $fullname) {
        $courseoptions[$id] = format_string($fullname);
    }

    $settings->add(new admin_setting_course_autocomplete(
        'auth_invitation/defaultcourses',
        get_string('defaultcourses', 'auth_invitation'),
        get_string('defaultcourses_desc', 'auth_invitation'),
        [],
        $courseoptions
    ));

    $settings->add(new admin_setting_configduration(
        'auth_invitation/defaultexpiry',
        get_string('defaultexpiry', 'auth_invitation'),
        get_string('defaultexpiry_desc', 'auth_invitation'),
        DAYSECS
    ));

    $settings->add(new admin_setting_configtext(
        'auth_invitation/emailsubject',
        get_string('emailsubject', 'auth_invitation'),
        get_string('emailsubject_desc', 'auth_invitation'),
        get_string('defaultemailsubject', 'auth_invitation'),
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_confightmleditor(
        'auth_invitation/emailtemplate',
        get_string('emailtemplate', 'auth_invitation'),
        get_string('emailtemplate_desc', 'auth_invitation'),
        get_string('defaultemailtemplate', 'auth_invitation')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'auth_invitation/allowresend',
        get_string('allowresend', 'auth_invitation'),
        get_string('allowresend_desc', 'auth_invitation'),
        1
    ));
}
