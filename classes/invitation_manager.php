<?php
// This file is part of Moodle - http://moodle.org/

namespace auth_invitation;

defined('MOODLE_INTERNAL') || die();

/**
 * Invitation manager class.
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class invitation_manager {

    /** @var int Invitation created, link not yet used. */
    const STATUS_PENDING = 0;

    /** @var int Invited user completed registration. */
    const STATUS_REGISTERED = 1;

    /** @var int Invitation link expired before use. */
    const STATUS_EXPIRED = 2;

    /** @var int Invitation cancelled by an administrator. */
    const STATUS_CANCELLED = 3;

    /**
     *
     * @param \stdClass $formdata Data submitted from \auth_invitation\form\invite_form.
     * @return int ID of the new auth_invitation record.
     */
    public static function create_invitation(\stdClass $formdata): int {
        global $DB, $CFG;

        $email = trim(\core_text::strtolower($formdata->email));

        if (!validate_email($email)) {
            throw new \moodle_exception('invalidemail', 'auth_invitation');
        }

        if ($DB->record_exists('user', ['email' => $email, 'deleted' => 0, 'mnethostid' => $CFG->mnet_localhost_id])) {
            throw new \moodle_exception('emailexists', 'auth_invitation');
        }

        if (self::has_pending_invitation($email)) {
            throw new \moodle_exception('emailalreadyinvited', 'auth_invitation');
        }

        $record = new \stdClass();
        $record->firstname = trim($formdata->firstname);
        $record->lastname  = trim($formdata->lastname);
        $record->email     = $email;

        $timecreated = time();
        $record->timecreated  = $timecreated;
        $record->timemodified = $timecreated;

        $expiryseconds = !empty($formdata->expirytime) ? (int) $formdata->expirytime
            : (int) get_config('auth_invitation', 'defaultexpiry');
        $record->expirytime = $timecreated + max($expiryseconds, MINSECS);

        $temppassword = generate_password(10);

        $token = self::generate_token(
            $record->firstname,
            $record->lastname,
            $record->email,
            $record->expirytime,
            $temppassword
        );
        $record->token = self::hash_token($token);

        $record->status        = self::STATUS_PENDING;
        $record->userid        = 0;
        $record->completedtime = 0;

        $invitationid = $DB->insert_record('auth_invitation', $record);

        if (!empty($formdata->courses)) {
            foreach ($formdata->courses as $courseid) {
                $courseid = (int) $courseid;
                if ($courseid <= 0) {
                    continue;
                }
                $link = new \stdClass();
                $link->invitationid = $invitationid;
                $link->courseid     = $courseid;
                $DB->insert_record('auth_invitation_courses', $link);
            }
        }

        self::send_invitation_email($invitationid, $token, $temppassword);

        \auth_invitation\event\invitation_sent::create([
            'objectid' => $invitationid,
            'context'  => \context_system::instance(),
            'other'    => ['email' => $email],
        ])->trigger();

        return $invitationid;
    }

    /**
     *
     * @param int $invitationid
     */
    public static function resend_invitation(int $invitationid): void {
        global $DB;

        $invitation = $DB->get_record('auth_invitation', ['id' => $invitationid], '*', MUST_EXIST);

        if (!in_array((int) $invitation->status, [self::STATUS_PENDING, self::STATUS_EXPIRED], true)) {
            throw new \moodle_exception('cannotresend', 'auth_invitation');
        }

        $now = time();
        $invitation->expirytime   = $now + (int) get_config('auth_invitation', 'defaultexpiry');
        $invitation->status       = self::STATUS_PENDING;
        $invitation->timemodified = $now;

        $temppassword = generate_password(10);

        $token = self::generate_token(
            $invitation->firstname,
            $invitation->lastname,
            $invitation->email,
            $invitation->expirytime,
            $temppassword
        );

        $invitation->token = self::hash_token($token);
        $DB->update_record('auth_invitation', $invitation);

        self::send_invitation_email($invitationid, $token, $temppassword);

        \auth_invitation\event\invitation_resent::create([
            'objectid' => $invitationid,
            'context'  => \context_system::instance(),
            'other'    => ['email' => $invitation->email],
        ])->trigger();
    }

    /**
     *
     * @param int $invitationid
     */
    public static function cancel_invitation(int $invitationid): void {
        global $DB;

        $invitation = $DB->get_record('auth_invitation', ['id' => $invitationid], '*', MUST_EXIST);

        $DB->set_field('auth_invitation', 'status', self::STATUS_CANCELLED, ['id' => $invitationid]);
        $DB->set_field('auth_invitation', 'timemodified', time(), ['id' => $invitationid]);

        \auth_invitation\event\invitation_revoked::create([
            'objectid' => $invitationid,
            'context'  => \context_system::instance(),
            'other'    => ['email' => $invitation->email],
        ])->trigger();
    }

    /**
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param int $expirytime
     * @param string $temppassword
     * @param string $token
     * @return \stdClass|false
     */
    public static function validate_token(
        string $firstname,
        string $lastname,
        string $email,
        int $expirytime,
        string $temppassword,
        string $token
    ) {
        global $DB;

        $expectedtoken = self::generate_token($firstname, $lastname, $email, $expirytime, $temppassword);
        if (!hash_equals($expectedtoken, $token)) {
            return false;
        }

        $invitation = $DB->get_record('auth_invitation', ['token' => self::hash_token($token)]);
        if (!$invitation) {
            return false;
        }

        if ((int) $invitation->status !== self::STATUS_PENDING) {
            return false;
        }

        if ($invitation->expirytime < time()) {
            $invitation->status       = self::STATUS_EXPIRED;
            $invitation->timemodified = time();
            $DB->update_record('auth_invitation', $invitation);
            return false;
        }

        return $invitation;
    }

    /**
     *
     * @param int $invitationid
     * @param int $userid
     */
    public static function complete_invitation(int $invitationid, int $userid): void {
        global $DB;

        $invitation = $DB->get_record('auth_invitation', ['id' => $invitationid], '*', MUST_EXIST);
        $invitation->status        = self::STATUS_REGISTERED;
        $invitation->userid        = $userid;
        $invitation->completedtime = time();
        $invitation->timemodified  = time();

        $DB->update_record('auth_invitation', $invitation);

        if (class_exists('\auth_invitation\event\invitation_registered')) {
            \auth_invitation\event\invitation_registered::create([
                'objectid'      => $invitationid,
                'relateduserid' => $userid,
                'context'       => \context_system::instance(),
                'other'         => ['email' => $invitation->email],
            ])->trigger();
        }
    }

    /**
     *
     * @param int $invitationid
     * @return int[]
     */
    public static function get_invitation_courses(int $invitationid): array {
        global $DB;
        $ids = $DB->get_fieldset_select('auth_invitation_courses', 'courseid', 'invitationid = ?', [$invitationid]);
        return array_map('intval', $ids);
    }

    /**
     *
     * @param int $userid
     * @param int[] $courseids
     */
    public static function enrol_courses(int $userid, array $courseids): void {
        foreach ($courseids as $courseid) {
            if (self::is_user_enrolled($userid, $courseid)) {
                continue;
            }
            try {
                enrol_try_internal_enrol($courseid, $userid);
            } catch (\Exception $e) {
                error_log('auth_invitation: Enrolment notice for course ' . $courseid . ': ' . $e->getMessage());
            }
        }
    }

    /**
     *
     * @param int $userid
     * @param int $courseid
     * @return bool
     */
    protected static function is_user_enrolled(int $userid, int $courseid): bool {
        $context = \context_course::instance($courseid, IGNORE_MISSING);
        if (!$context) {
            return false;
        }
        return is_enrolled($context, $userid);
    }

    /**
     *
     * @param string $email
     * @return bool
     */
    public static function has_pending_invitation(string $email): bool {
        global $DB;
        return $DB->record_exists('auth_invitation', [
            'email'  => trim(\core_text::strtolower($email)),
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param int $expirytime
     * @param string $temppassword
     * @return string
     */
    protected static function generate_token(
        string $firstname,
        string $lastname,
        string $email,
        int $expirytime,
        string $temppassword
    ): string {
        global $CFG;
        $secret = $CFG->passwordsaltmain ?? 'auth_invitation_default_salt';

        $payload = $firstname . '|' . $lastname . '|' . $email . '|' . $expirytime . '|' . $temppassword;

        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     *
     * @param string $token
     * @return string
     */
    protected static function hash_token(string $token): string {
        return hash('sha256', $token);
    }

    /**
     *
     * @param int $invitationid
     * @param string $token
     * @param string $temppassword
     */
    protected static function send_invitation_email(int $invitationid, string $token, string $temppassword): void {
        global $DB;

        $invitation = $DB->get_record('auth_invitation', ['id' => $invitationid], '*', MUST_EXIST);

        $registrationurl = new \moodle_url('/auth/invitation/register.php', [
            'firstname'    => $invitation->firstname,
            'lastname'     => $invitation->lastname,
            'email'        => $invitation->email,
            'expirytime'   => $invitation->expirytime,
            'temppassword' => $temppassword,
            'token'        => $token,
        ]);

        $expirydate = userdate($invitation->expirytime, get_string('strftimedatetime', 'langconfig'));
        $sitename = format_string(get_site()->fullname);

        $placeholders = [
            '{{firstname}}'         => $invitation->firstname,
            '{{lastname}}'          => $invitation->lastname,
            '{{email}}'             => $invitation->email,
            '{{registration_link}}' => $registrationurl->out(false),
            '{{temp_password}}'     => $temppassword,
            '{{expiry_date}}'       => $expirydate,
            '{{site_name}}'         => $sitename,
        ];

        $rawtemplate = (string) get_config('auth_invitation', 'emailtemplate');
        $htmlbody = strtr($rawtemplate, $placeholders);
        $textbody = html_to_text($htmlbody);

        $rawsubject = (string) get_config('auth_invitation', 'emailsubject');
        if (empty($rawsubject)) {
            $rawsubject = get_string('defaultemailsubject', 'auth_invitation');
        }
        $subject = strtr($rawsubject, $placeholders);

        $touser = self::get_temp_recipient($invitation);
        $fromuser = \core_user::get_support_user();

        email_to_user($touser, $fromuser, $subject, $textbody, $htmlbody);
    }

    /**
     *
     * @param \stdClass $invitation
     * @return \stdClass
     */
    protected static function get_temp_recipient(\stdClass $invitation): \stdClass {
        $touser = new \stdClass();
        $touser->id                = -1;
        $touser->email             = $invitation->email;
        $touser->firstname         = $invitation->firstname;
        $touser->lastname          = $invitation->lastname;
        $touser->firstnamephonetic = '';
        $touser->lastnamephonetic  = '';
        $touser->middlename        = '';
        $touser->alternatename     = '';
        $touser->maildisplay       = true;
        $touser->mailformat        = 1;
        $touser->auth               = 'invitation';
        $touser->deleted           = 0;
        $touser->suspended         = 0;
        return $touser;
    }
}
