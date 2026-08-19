<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Google Calendar settings for WorkNexus meetings.
 *
 * google_calendar_id:
 *   'primary' = the primary calendar of the connected Google OAuth account.
 *   This is what the standalone /google/test_meeting endpoint uses and it is
 *   the ONLY value proven to work with the currently connected account.
 *
 *   IMPORTANT: A literal calendar ID (e.g. someone@gmail.com) only works if the
 *   connected OAuth account has access to that calendar. If it does not, Google
 *   returns HTTP 404 "Not Found" when inserting the event.
 */
$config['google_calendar_id'] = 'primary';
