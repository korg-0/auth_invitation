<?php
// This file is part of Moodle - http://moodle.org/

namespace auth_invitation\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class password_form extends \moodleform {


    public function definition() {
        $mform = $this->_form;
        $invitation = $this->_customdata['invitation'];

        $mform->addElement('static', 'firstname_display', get_string('firstname'), s($invitation->firstname));
        $mform->addElement('static', 'lastname_display', get_string('lastname'), s($invitation->lastname));
        $mform->addElement('static', 'email_display', get_string('email'), s($invitation->email));

        $mform->addElement('hidden', 'token');
        $mform->setType('token', PARAM_ALPHANUM);

        $mform->addElement('passwordunmask', 'password', get_string('password'));
        $mform->setType('password', PARAM_RAW);
        $mform->addRule('password', get_string('required'), 'required', null, 'client');

        $mform->addElement('passwordunmask', 'password2', get_string('confirmpassword', 'auth_invitation'));
        $mform->setType('password2', PARAM_RAW);
        $mform->addRule('password2', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(false, get_string('completeregistration', 'auth_invitation'));
    }

    /**
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['password'] !== $data['password2']) {
            $errors['password2'] = get_string('passwordsdonotmatch', 'auth_invitation');
        } else {
            $errmsg = '';
            if (!check_password_policy($data['password'], $errmsg)) {
                $errors['password'] = $errmsg;
            }
        }

        return $errors;
    }
}
