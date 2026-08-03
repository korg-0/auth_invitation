<?php
namespace auth_invitation\event;

defined('MOODLE_INTERNAL') || die();

class invitation_revoked extends \core\event\base {

    protected function init() {
        $this->data['objecttable'] = 'auth_invitation';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('eventinvitationrevoked', 'auth_invitation');
    }

    public function get_description() {
        return "The user with id '{$this->userid}' revoked the invitation (id '{$this->objectid}') for '{$this->other['email']}'.";
    }

    public function get_url() {
        return new \moodle_url('/auth/invitation/manage.php');
    }
}
