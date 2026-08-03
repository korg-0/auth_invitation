<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

admin_externalpage_setup('auth_invitation_logs');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new moodle_url('/auth/invitation/logs.php'));
$PAGE->set_title(get_string('invitationlogs', 'auth_invitation'));
$PAGE->set_heading(get_string('invitationlogs', 'auth_invitation'));

echo $OUTPUT->header();

$table = new flexible_table('auth_invitation_logs_table');
$table->define_columns(['timecreated', 'userid', 'eventname', 'targetemail']);
$table->define_headers([
    get_string('time', 'core'),
    get_string('user', 'core'),
    get_string('eventname', 'core'),
    get_string('email', 'core'),
]);

$table->define_baseurl(new moodle_url('/auth/invitation/logs.php'));
$table->set_attribute('class', 'generaltable width100');
$table->setup();

global $DB;
$sql = "SELECT l.id, l.timecreated, l.userid, l.eventname, l.other
          FROM {logstore_standard_log} l
         WHERE l.component = :component
      ORDER BY l.timecreated DESC";

$records = $DB->get_records_sql($sql, ['component' => 'auth_invitation']);

foreach ($records as $record) {
    if (!empty($record->userid)) {
        $user = \core_user::get_user($record->userid);
        $username = $user ? fullname($user) : 'System';
    } else {
        $username = 'System';
    }

    $other = [];
    if (!empty($record->other)) {
        $decoded = json_decode($record->other, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $other = $decoded;
        } else {
            $unserialized = @unserialize($record->other);
            if ($unserialized !== false && is_array($unserialized)) {
                $other = $unserialized;
            }
        }
    }

    $targetemail = $other['email'] ?? '-';

    $eventparts = explode('\\', $record->eventname);
    $eventlabel = end($eventparts);

    $table->add_data([
        userdate($record->timecreated, get_string('strftimedatetime', 'langconfig')),
        $username,
        s($eventlabel),
        s($targetemail),
    ]);
}

$table->print_html();

echo $OUTPUT->footer();
