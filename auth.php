<?php
// This file is part of Moodle - http://moodle.org/

/**
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

class auth_plugin_invitation extends auth_plugin_base {


    public function __construct() {
        $this->authtype = 'invitation';
        $this->config = get_config('auth_invitation');
    }

    /**
     *
     * @param string $username
     * @param string $password plain text password.
     * @return bool
     */
    public function user_login($username, $password) {
        global $CFG, $DB;

        if (!$user = $DB->get_record('user', [
            'username'   => $username,
            'mnethostid' => $CFG->mnet_localhost_id,
        ])) {
            return false;
        }

        return validate_internal_user_password($user, $password);
    }

    /**
     *
     * @return bool
     */
    public function is_internal() {
        return true;
    }

    /**
     *
     * @return bool
     */
    public function prevent_local_passwords() {
        return false;
    }

    /**
     *
     * @return bool
     */
    public function can_change_password() {
        return true;
    }

    /**
     *
     * @return bool
     */
    public function can_reset_password() {
        return true;
    }

    /**
     *
     * @return bool
     */
    public function can_be_manually_set() {
        return false;
    }

    /**
     *
     * @return string|null
     */
    public function change_password_url() {
        return null;
    }

    /**
     *
     * @param string $wantsurl
     * @return array
     */
    public function loginpage_idp_list($wantsurl) {
        return [];
    }
}
