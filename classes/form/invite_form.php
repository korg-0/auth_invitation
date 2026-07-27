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
class invite_form extends \moodleform {


    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'firstname', get_string('firstname'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastname'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email'));
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', get_string('required'), 'required', null, 'client');

        $mform->addElement('duration', 'expirytime', get_string('expirytime', 'auth_invitation'));
        $mform->setDefault('expirytime', (int) get_config('auth_invitation', 'defaultexpiry'));
        $mform->addHelpButton('expirytime', 'expirytime', 'auth_invitation');

        $courses = self::get_course_choices();
        $select = $mform->addElement(
            'autocomplete',
            'courses',
            get_string('courses', 'auth_invitation'),
            $courses,
            ['multiple' => true]
        );
        $mform->addHelpButton('courses', 'courses', 'auth_invitation');

        $defaultcourses = (string) get_config('auth_invitation', 'defaultcourses');
        if ($defaultcourses !== '') {
            $mform->setDefault('courses', array_filter(array_map('trim', explode(',', $defaultcourses))));
        }

        $this->add_action_buttons(true, get_string('sendinvitation', 'auth_invitation'));
    }

    /**
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);

        $email = trim(\core_text::strtolower($data['email']));

        if (!validate_email($email)) {
            $errors['email'] = get_string('invalidemail', 'auth_invitation');
        } else if ($DB->record_exists('user', ['email' => $email, 'deleted' => 0, 'mnethostid' => $CFG->mnet_localhost_id])) {
            $errors['email'] = get_string('emailexists', 'auth_invitation');
        } else if (\auth_invitation\invitation_manager::has_pending_invitation($email)) {
            $errors['email'] = get_string('emailalreadyinvited', 'auth_invitation');
        }

        return $errors;
    }

    /**
     *
     * @return array
     */
    protected static function get_course_choices(): array {
        global $DB;

        $courses = $DB->get_records('course', ['visible' => 1], 'fullname ASC', 'id, fullname');
        $choices = [];
        foreach ($courses as $course) {
            if ((int) $course->id === SITEID) {
                continue;
            }
            $choices[$course->id] = format_string($course->fullname);
        }
        return $choices;
    }
}
