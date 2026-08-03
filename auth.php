<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Invitation authentication plugin.
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

class auth_plugin_invitation extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'invitation';
        $this->config = get_config('auth_invitation');
    }

    /**
     * Authenticate user using internal Moodle password hash.
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
     * Pre-login hook executed before login processing begins.
     * Useful for performing early redirects or checks before credentials are submitted.
     */
    public function prelogin_hook() {
    }

    /**
     * Post-authentication hook executed immediately after successful login.
     * Enforces the 'auth_forcepasswordchange' preference on session launch.
     *
     * @param \stdClass $user User record.
     * @param string $username Username submitted.
     * @param string $password Password submitted.
     */
    public function user_authenticated_hook(&$user, $username, $password) {
        global $DB;

        if (get_user_preferences('auth_forcepasswordchange', false, $user)) {
            set_user_preference('auth_forcepasswordchange', 1, $user);
        }
    }

    /**
     * Declares internal authentication mechanism.
     *
     * @return bool
     */
    public function is_internal() {
        return true;
    }

    /**
     * Allows local passwords.
     *
     * @return bool
     */
    public function prevent_local_passwords() {
        return false;
    }

    /**
     * Allows users to change their password.
     *
     * @return bool
     */
    public function can_change_password() {
        return true;
    }

    /**
     * Allows password resets.
     *
     * @return bool
     */
    public function can_reset_password() {
        return true;
    }

    /**
     * Disallows manual account creation via UI settings if meant solely for invites.
     *
     * @return bool
     */
    public function can_be_manually_set() {
        return false;
    }

    /**
     * Returns custom change password URL if overridden, null uses core.
     *
     * @return string|null
     */
    public function change_password_url() {
        return null;
    }

    /**
     * Identity provider list for login page.
     *
     * @param string $wantsurl
     * @return array
     */
    public function loginpage_idp_list($wantsurl) {
        return [];
    }
}
