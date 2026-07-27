<?php
// This file is part of Moodle - http://moodle.org/

namespace auth_invitation\table;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class invitations_table extends \table_sql {

    /**
     * @param string $uniqueid
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);

        $this->define_columns([
            'firstname', 'lastname', 'email', 'timecreated', 'expirytime', 'status', 'courses', 'actions',
        ]);

        $this->define_headers([
            get_string('firstname'),
            get_string('lastname'),
            get_string('email'),
            get_string('createddate', 'auth_invitation'),
            get_string('expirydate', 'auth_invitation'),
            get_string('status'),
            get_string('courses', 'auth_invitation'),
            get_string('actions'),
        ]);

        $this->set_sql('*', '{auth_invitation}', '1=1');
        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('courses');
        $this->no_sorting('actions');
        $this->collapsible(false);
        $this->set_attribute('class', 'generaltable auth-invitation-list');
    }

    /**
     * @param \stdClass $row
     * @return string
     */
    public function col_timecreated($row) {
        return userdate($row->timecreated, get_string('strftimedatetime', 'langconfig'));
    }

    /**
     * @param \stdClass $row
     * @return string
     */
    public function col_expirytime($row) {
        return userdate($row->expirytime, get_string('strftimedatetime', 'langconfig'));
    }

    /**
     * @param \stdClass $row
     * @return string
     */
    public function col_status($row) {
        $statuses = [
            \auth_invitation\invitation_manager::STATUS_PENDING    => get_string('statuspending', 'auth_invitation'),
            \auth_invitation\invitation_manager::STATUS_REGISTERED => get_string('statusregistered', 'auth_invitation'),
            \auth_invitation\invitation_manager::STATUS_EXPIRED    => get_string('statusexpired', 'auth_invitation'),
            \auth_invitation\invitation_manager::STATUS_CANCELLED  => get_string('statuscancelled', 'auth_invitation'),
        ];
        return $statuses[(int) $row->status] ?? '-';
    }

    /**
     * @param \stdClass $row
     * @return string
     */
    public function col_courses($row) {
        global $DB;

        $courseids = \auth_invitation\invitation_manager::get_invitation_courses((int) $row->id);
        if (empty($courseids)) {
            return '-';
        }

        [$insql, $params] = $DB->get_in_or_equal($courseids);
        $names = $DB->get_fieldset_select('course', 'fullname', "id $insql", $params);

        return implode(', ', array_map('format_string', $names));
    }

    /**
     * @param \stdClass $row
     * @return string
     */
    public function col_actions($row) {
        $actions = [];
        $status = (int) $row->status;

        $viewurl = new \moodle_url('/auth/invitation/manage.php', [
            'action' => 'view', 'id' => $row->id, 'sesskey' => sesskey(),
        ]);
        $actions[] = \html_writer::link($viewurl, get_string('view'));

        if (in_array($status, [
            \auth_invitation\invitation_manager::STATUS_PENDING,
            \auth_invitation\invitation_manager::STATUS_EXPIRED,
        ], true)) {
            $resendurl = new \moodle_url('/auth/invitation/manage.php', [
                'action' => 'resend', 'id' => $row->id, 'sesskey' => sesskey(),
            ]);
            $actions[] = \html_writer::link($resendurl, get_string('resend', 'auth_invitation'));
        }

        if ($status === \auth_invitation\invitation_manager::STATUS_PENDING) {
            $cancelurl = new \moodle_url('/auth/invitation/manage.php', [
                'action' => 'cancel', 'id' => $row->id, 'sesskey' => sesskey(),
            ]);
            $actions[] = \html_writer::link($cancelurl, get_string('cancel'), [
                'onclick' => 'return confirm(' . json_encode(get_string('confirmcancel', 'auth_invitation')) . ');',
            ]);
        }

        return implode(' | ', $actions);
    }
}
