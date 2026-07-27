# auth_invitation

Invitation-based user registration plugin for Moodle 4.5.

## Overview

`auth_invitation` is a custom Moodle authentication plugin that allows administrators to invite users via email. Invited users set their own password through a secure, single-use, expiring link, after which they are automatically logged in and enrolled in designated courses.

## Features

- Administrator form to issue user invitations with customizable expiry and course selection.
- Invitation management dashboard to view, resend, or cancel invitations.
- Secure, cryptographically generated single-use token links.
- Automatic account creation, course enrolment, and seamless post-registration login.
- Scheduled task to clean up expired pending invitations.

## Installation & Setup

1. Copy the `invitation` directory into `<moodleroot>/auth/` so the path is `<moodleroot>/auth/invitation/`.
2. Navigate to **Site administration > Notifications** in Moodle to complete database installation.
3. Go to **Site administration > Plugins > Authentication > Manage authentication** and **Enable** "Invitation-based registration".
4. Configure default plugin settings under **Site administration > Plugins > Authentication > Invitation-based registration**.
5. Assign the **Manager** system role (or a role with `auth/invitation:invite` capability) to administrators managing invitations.

## Usage

- **Invite Users:** Navigate to **Site administration > Users > Authentication > Invite a user**.
- **Manage Invitations:** Navigate to **Site administration > Users > Authentication > User invitations**.