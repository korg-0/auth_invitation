<?php
// This file is part of Moodle - http://moodle.org/

/**
 * English language strings for auth_invitation.
 *
 * @package    auth_invitation
 * @copyright  2026 IDS Logic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Invitation-based registration';
$string['auth_invitationdescription'] = 'Users are created only by an administrator sending an invitation email; the invitee sets their own password via a secure, expiring link.';

$string['invitation:invite'] = 'Send and manage user invitations';

$string['invitepagetitle'] = 'Invite a user';
$string['managepagetitle'] = 'User invitations';
$string['invitenewuser'] = 'Invite new user';
$string['sendinvitation'] = 'Send invitation';
$string['completeregistration'] = 'Complete your registration';
$string['registrationintro'] = 'Please review your details and choose a password to finish setting up your account.';
$string['registrationcomplete'] = 'Your account has been created and you are now logged in.';

$string['expirytime'] = 'Expiry time';
$string['expirytime_help'] = 'How long the invitation link stays valid. Leave as the default unless this invitation needs a shorter or longer window.';
$string['courses'] = 'Courses';
$string['courses_help'] = 'Courses the user will be automatically enrolled into as soon as they complete registration.';
$string['confirmpassword'] = 'Confirm password';
$string['createddate'] = 'Created';
$string['expirydate'] = 'Expires';
$string['actions'] = 'Actions';
$string['resend'] = 'Resend';
$string['revoke'] = 'Revoke';
$string['confirmrevoke'] = 'Revoke this invitation? The invitee will no longer be able to use their registration link.';
$string['invitationrevoked'] = 'Invitation revoked.';
$string['statuspending']    = 'Pending';
$string['statusregistered'] = 'Registered';
$string['statusexpired']    = 'Expired';
$string['statuscancelled']  = 'Revoked';
$string['statusrevoked']    = 'Revoked';
$string['selectcourses'] = 'Select courses...';
$string['invitationsent'] = 'Invitation sent.';
$string['invitationresent'] = 'Invitation resent. The previous link is no longer valid.';

$string['invalidemail'] = 'Please enter a valid email address.';
$string['emailexists'] = 'A Moodle account already exists with this email address.';
$string['emailalreadyinvited'] = 'There is already a pending invitation for this email address.';
$string['invalidorexpiredtoken'] = 'This invitation link is invalid or has expired. Please contact your administrator.';
$string['passwordsdonotmatch'] = 'The passwords you entered do not match.';
$string['cannotresend'] = 'Only pending or expired invitations can be resent.';

$string['emailsubject'] = 'Invitation email subject';
$string['emailsubject_desc'] = 'The subject line of the invitation email sent to users. Placeholders like {{site_name}}, {{firstname}}, and {{lastname}} are supported.';
$string['defaultemailsubject'] = 'You have been invited to {{site_name}}';

$string['emailtemplate'] = 'Invitation email template';
$string['emailtemplate_desc'] = 'The body of the invitation email. Available placeholders: {{firstname}}, {{lastname}}, {{email}}, {{registration_link}}, {{temp_password}}, {{expiry_date}}, {{site_name}}.';
$string['defaultemailtemplate'] = '<p>Welcome <strong>{{firstname}}</strong>,</p>
<p>You have been invited to access <strong>{{site_name}}</strong>.</p>
<p>Your temporary password is: <code>{{temp_password}}</code></p>
<p>Click the link below to complete your registration:</p>
<p><a href="{{registration_link}}">Complete Your Registration</a></p>
<p><small>This invitation link expires on: {{expiry_date}}</small></p>
<p>If you were not expecting this invitation, please contact your site administrator.</p>
<p>Thank you.</p>';

$string['defaultexpiry'] = 'Default expiry time';
$string['defaultexpiry_desc'] = 'How long a new invitation link remains valid by default. Administrators can override this per invitation.';
$string['defaultcourses'] = 'Default courses';
$string['defaultcourses_desc'] = 'Courses that are pre-selected on the invitation form by default.';
$string['allowresend'] = 'Allow invitation resend';
$string['allowresend_desc'] = 'Reserved for a future release: whether administrators are allowed to resend invitations.';
$string['maxresend'] = 'Maximum resend attempts';
$string['maxresend_desc'] = 'Reserved for a future release: the maximum number of times a single invitation can be resent.';

$string['taskcleanupexpired'] = 'Expire past-due pending invitations';

$string['privacy:metadata:auth_invitation'] = 'Information about invitations sent to prospective users.';
$string['privacy:metadata:auth_invitation:firstname'] = 'The first name given for the invitation.';
$string['privacy:metadata:auth_invitation:lastname'] = 'The last name given for the invitation.';
$string['privacy:metadata:auth_invitation:email'] = 'The email address the invitation was sent to.';
$string['privacy:metadata:auth_invitation:userid'] = 'The user account created once the invitation was completed.';
$string['privacy:metadata:auth_invitation:timecreated'] = 'The time the invitation was created.';
$string['privacy:metadata:auth_invitation:completedtime'] = 'The time the invitee completed registration.';
$string['privacy:metadata:invitationemail'] = 'The invitation email is sent via the site\'s configured email server.';
$string['privacy:metadata:invitationemail:email'] = 'The invitee\'s email address, used to deliver the invitation.';
$string['created'] = 'Created';
$string['expires'] = 'Expires';
$string['status']  = 'Status';
$string['actions'] = 'Actions';
$string['userinvitations'] = 'User invitations';
$string['manageinvitations'] = 'Manage invitations';
$string['confirmresend'] = 'Are you sure you want to resend this invitation email?';
$string['eventinvitationsent'] = 'User invitation sent';
$string['eventinvitationresent'] = 'User invitation resent';
$string['eventinvitationrevoked'] = 'User invitation revoked';
$string['invitationlogs'] = 'Invitation logs';
