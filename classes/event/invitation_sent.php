<?php
namespace auth_invitation\event;

defined('MOODLE_INTERNAL') || die();

class invitation_sent extends \core\event\base {

    protected function init() {
        $this->data['objecttable'] = 'auth_invitation';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('eventinvitationsent', 'auth_invitation');
    }

    public function get_description() {
        return "The user with id '{$this->userid}' sent a registration invitation to email '{$this->other['email']}'.";
    }

    public function get_url() {
        return new \moodle_url('/auth/invitation/manage.php');
    }
}
