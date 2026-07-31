<?php
// This file is part of Moodle - http://moodle.org/

namespace auth_invitation\table;

use html_writer;
use moodle_url;

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

        global $DB;

        $this->define_columns([
            'firstname',
            'lastname',
            'email',
            'timecreated',
            'expirytime',
            'status',
            'courses',
            'actions',
        ]);

        $this->define_headers([
            get_string('firstname'),
            get_string('lastname'),
            get_string('email'),
            get_string('created', 'auth_invitation'),
            get_string('expires', 'auth_invitation'),
            get_string('status', 'auth_invitation'),
            get_string('courses', 'auth_invitation'),
            get_string('actions', 'auth_invitation'),
        ]);

        $fields = "i.id, i.firstname, i.lastname, i.email, i.timecreated, i.expirytime, i.status, " .
                  "GROUP_CONCAT(DISTINCT c.fullname SEPARATOR ', ') AS courses";

        $from = "{auth_invitation} i " .
                "LEFT JOIN {auth_invitation_courses} ic ON ic.invitationid = i.id " .
                "LEFT JOIN {course} c ON c.id = ic.courseid";

        $where = "1=1 GROUP BY i.id, i.firstname, i.lastname, i.email, i.timecreated, i.expirytime, i.status";

        $this->set_sql($fields, $from, $where);

        $this->set_count_sql("SELECT COUNT(DISTINCT i.id) FROM {auth_invitation} i");
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
     *
     * @param \stdClass $values
     * @return string
     */
    public function col_status($values) {
        switch ((int)$values->status) {
            case \auth_invitation\invitation_manager::STATUS_PENDING:
                return html_writer::span(get_string('statuspending', 'auth_invitation'), 'badge badge-warning');
            case \auth_invitation\invitation_manager::STATUS_REGISTERED:
                return html_writer::span(get_string('statusregistered', 'auth_invitation'), 'badge badge-success');
            case \auth_invitation\invitation_manager::STATUS_EXPIRED:
                return html_writer::span(get_string('statusexpired', 'auth_invitation'), 'badge badge-secondary');
            case \auth_invitation\invitation_manager::STATUS_CANCELLED:
                return html_writer::span(get_string('statuscancelled', 'auth_invitation'), 'badge badge-danger');
            default:
                return '-';
        }
    }

    /**
     *
     * @param \stdClass $values
     * @return string
     */
    public function col_courses($values) {
        if (empty($values->courses)) {
            return '-';
        }

        $courselist = explode(',', $values->courses);
        $items = [];
        foreach ($courselist as $coursename) {
            $name = trim($coursename);
            if (!empty($name)) {
                $items[] = \html_writer::tag('li', s($name));
            }
        }

        if (empty($items)) {
            return '-';
        }

        return \html_writer::tag('ul', implode('', $items), ['class' => 'list-unstyled mb-0']);
    }

    /**
     * @param \stdClass $row
     * @return string
     */
    public function col_actions($values) {
        global $OUTPUT;

        $actions = [];

        if (in_array((int)$values->status, [\auth_invitation\invitation_manager::STATUS_PENDING, \auth_invitation\invitation_manager::STATUS_EXPIRED])) {
            $resendurl = new moodle_url('/auth/invitation/manage.php', [
                'action' => 'resend',
                'id' => $values->id,
                'sesskey' => sesskey(),
            ]);
            $actions[] = html_writer::link($resendurl, get_string('resend', 'auth_invitation'), ['class' => 'btn btn-sm btn-outline-secondary mr-1']);

            $revokeurl = new moodle_url('/auth/invitation/manage.php', [
                'action' => 'revoke',
                'id' => $values->id,
                'sesskey' => sesskey(),
            ]);
            $actions[] = html_writer::link($revokeurl, get_string('revoke', 'auth_invitation'), ['class' => 'btn btn-sm btn-outline-danger']);
        }

        return implode(' ', $actions);
    }
}
