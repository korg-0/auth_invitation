<?php
// This file is part of Moodle - http://moodle.org/

namespace auth_invitation\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;

/**
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'auth_invitation',
            [
                'firstname'     => 'privacy:metadata:auth_invitation:firstname',
                'lastname'      => 'privacy:metadata:auth_invitation:lastname',
                'email'         => 'privacy:metadata:auth_invitation:email',
                'userid'        => 'privacy:metadata:auth_invitation:userid',
                'timecreated'   => 'privacy:metadata:auth_invitation:timecreated',
                'completedtime' => 'privacy:metadata:auth_invitation:completedtime',
            ],
            'privacy:metadata:auth_invitation'
        );

        $collection->add_external_location_link(
            'invitationemail',
            ['email' => 'privacy:metadata:invitationemail:email'],
            'privacy:metadata:invitationemail'
        );

        return $collection;
    }

    /**
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();
        if ($DB->record_exists('auth_invitation', ['userid' => $userid])) {
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (!$contextlist->count()) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $records = $DB->get_records('auth_invitation', ['userid' => $userid]);
        if (empty($records)) {
            return;
        }

        $data = [];
        foreach ($records as $record) {
            $data[] = (object) [
                'firstname'     => $record->firstname,
                'lastname'      => $record->lastname,
                'email'         => $record->email,
                'timecreated'   => transform::datetime($record->timecreated),
                'completedtime' => $record->completedtime ? transform::datetime($record->completedtime) : null,
            ];
        }

        writer::with_context(\context_system::instance())->export_data(
            [get_string('pluginname', 'auth_invitation')],
            (object) ['invitations' => $data]
        );
    }

    /**
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel === CONTEXT_SYSTEM) {
            $DB->execute('UPDATE {auth_invitation} SET userid = 0 WHERE userid <> 0');
        }
    }

    /**
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        $DB->set_field('auth_invitation', 'userid', 0, ['userid' => $userid]);
    }

    /**
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {auth_invitation} WHERE userid <> 0', []);
    }

    /**
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            $DB->set_field('auth_invitation', 'userid', 0, ['userid' => $userid]);
        }
    }
}
