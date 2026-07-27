<?php
// This file is part of Moodle - http://moodle.org/

namespace auth_invitation\task;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_expired_task extends \core\task\scheduled_task {

    /**
     * @return string task name shown in Site administration > Server > Scheduled tasks.
     */
    public function get_name() {
        return get_string('taskcleanupexpired', 'auth_invitation');
    }


    public function execute() {
        global $DB;

        $now = time();

        $count = $DB->count_records_select(
            'auth_invitation',
            'status = :pendingstatus AND expirytime < :now',
            ['pendingstatus' => \auth_invitation\invitation_manager::STATUS_PENDING, 'now' => $now]
        );

        if ($count > 0) {
            $DB->execute(
                'UPDATE {auth_invitation}
                    SET status = :expiredstatus, timemodified = :now
                  WHERE status = :pendingstatus
                    AND expirytime < :now2',
                [
                    'expiredstatus' => \auth_invitation\invitation_manager::STATUS_EXPIRED,
                    'pendingstatus' => \auth_invitation\invitation_manager::STATUS_PENDING,
                    'now'           => $now,
                    'now2'          => $now,
                ]
            );
        }

        mtrace("auth_invitation: marked {$count} pending invitation(s) as expired.");
    }
}
