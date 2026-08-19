<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WorkNexus Google - Google Calendar + Google Meet Service
 *
 * Encapsulates the shared Google OAuth token handling and Calendar API
 * event creation so the meeting module can reuse the credentials already
 * connected via the Google controller (google/connect, google/callback).
 *
 * Creates a Calendar event with a Google Meet conference, adds assigned
 * employees as attendees, and returns the event ID plus Meet link.
 */
class Worknexusgoogle {

    private $CI;
    private $client;
    private $calendarId = 'primary';
    private $timezone = 'Asia/Kolkata';

    public function __construct()
    {
        $this->CI =& get_instance();

        // Calendar target from config (defaults to the connected account's primary calendar)
        $this->CI->config->load('google_calendar');
        $configuredCalendar = $this->CI->config->item('google_calendar_id');
        if (!empty($configuredCalendar)) {
            $this->calendarId = $configuredCalendar;
        }

        // Load Composer autoloader
        require_once FCPATH . 'vendor/autoload.php';

        // Create Google client
        $this->client = new Google\Client();

        $credentialsPath = APPPATH . 'config/google/credentials.json';

        if (!file_exists($credentialsPath)) {
            log_message('error', 'WorkNexusGoogle: credentials.json not found.');
            return;
        }

        $this->client->setAuthConfig($credentialsPath);

        // OAuth callback URL (kept in sync with the Google controller)
        $this->client->setRedirectUri(site_url('google/callback'));

        $this->client->setScopes(array(Google\Service\Calendar::CALENDAR));

        // Request refresh token
        $this->client->setAccessType('offline');

        // Show consent screen when connecting
        $this->client->setPrompt('consent');
    }

    /**
     * Load the saved OAuth token and refresh it when expired.
     *
     * @return string  'ok'            token ready to use
     *                 'missing'       no saved token (first-time / never connected)
     *                 'invalid'       saved token file is unreadable
     *                 'auth_failed'   refresh token rejected/revoked - must reconnect
     *                 'refresh_error' temporary failure refreshing - retry, no reconnect
     */
    private function loadToken()
    {
        $tokenPath = APPPATH . 'config/google/token.json';

        if (!file_exists($tokenPath)) {
            log_message('error', 'WorkNexusGoogle: token.json not found. Google Calendar is not connected.');
            return 'missing';
        }

        $token = json_decode(file_get_contents($tokenPath), TRUE);

        if (!is_array($token)) {
            log_message('error', 'WorkNexusGoogle: token.json is invalid.');
            return 'invalid';
        }

        $this->client->setAccessToken($token);

        if ($this->client->isAccessTokenExpired()) {

            $refreshToken = $this->client->getRefreshToken();

            if (!$refreshToken && isset($token['refresh_token'])) {
                $refreshToken = $token['refresh_token'];
            }

            if (!$refreshToken) {
                log_message('error', 'WorkNexusGoogle: access token expired and no refresh token was found. Please reconnect Google Calendar.');
                return 'auth_failed';
            }

            $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (isset($newToken['error'])) {

                // Authentication/authorization failures mean the refresh token is
                // revoked or invalid - clear it and require a fresh OAuth flow.
                $authErrors = array('invalid_grant', 'invalid_client', 'unauthorized_client', 'access_denied');

                if (in_array($newToken['error'], $authErrors)) {
                    log_message('error', 'WorkNexusGoogle: refresh token rejected (' . $newToken['error'] . '). Clearing stored token; reconnect required.');

                    if (file_exists($tokenPath)) {
                        @unlink($tokenPath);
                    }

                    return 'auth_failed';
                }

                // Temporary failure (network, quota, etc.) - retry later, do NOT
                // force the user through OAuth for this.
                log_message('error', 'WorkNexusGoogle: unable to refresh token: ' . ($newToken['error_description'] ?? $newToken['error']));
                return 'refresh_error';
            }

            $token = array_merge($token, $newToken);

            if (!isset($token['refresh_token'])) {
                $token['refresh_token'] = $refreshToken;
            }

            file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $this->client->setAccessToken($token);
        }

        return 'ok';
    }

    /**
     * Create a Google Calendar event with a Google Meet conference.
     *
     * @param array $data Expected keys:
     *                    summary      (string)  Event title
     *                    description  (string)  Event description
     *                    location     (string)  Event location
     *                    start        (string)  Start datetime (Asia/Kolkata), e.g. 2026-08-20T14:30
     *                    attendees    (array)   Employee email addresses
     * @return array  ['success' => bool, 'event_id' => string|null,
     *                 'meet_link' => string|null, 'error' => string|null]
     */
    public function createMeetingEvent($data)
    {
        $tokenStatus = $this->loadToken();

        if ($tokenStatus !== 'ok') {

            $needsAuth = in_array($tokenStatus, array('missing', 'invalid', 'auth_failed'));

            $errorMessage = ($tokenStatus === 'auth_failed')
                ? 'Your Google Calendar authorization has expired. Please reconnect your Google account.'
                : 'Google Calendar is not connected. Redirecting you to connect your Google account.';

            return array(
                'success'    => FALSE,
                'needs_auth' => $needsAuth,
                'event_id'   => NULL,
                'meet_link'  => NULL,
                'error'      => $errorMessage
            );
        }

        $calendarService = new Google\Service\Calendar($this->client);

        // Parse the submitted datetime as Asia/Kolkata (the value users pick in the form)
        $timezone = new DateTimeZone($this->timezone);

        try {
            $startTime = new DateTime($data['start'], $timezone);
        } catch (Exception $e) {
            log_message('error', 'WorkNexusGoogle: invalid start datetime: ' . $data['start']);
            return array(
                'success'    => FALSE,
                'needs_auth' => FALSE,
                'event_id'   => NULL,
                'meet_link'  => NULL,
                'error'      => 'The meeting date/time is invalid. Please try again.'
            );
        }

        $endTime = clone $startTime;
        $endTime->modify('+60 minutes');

        // -----------------------------------------------------------------
        // Build the event using explicit Google API objects.
        // This is the exact pattern proven by the standalone test for
        // Google Meet conference creation (object setters, not nested
        // constructor arrays) and avoids client-side hydration quirks.
        // -----------------------------------------------------------------
        $event = new Google\Service\Calendar\Event();
        $event->setSummary($data['summary']);
        $event->setDescription($data['description']);
        $event->setLocation($data['location']);

        $event->setStart(new Google\Service\Calendar\EventDateTime(array(
            'dateTime' => $startTime->format(DateTime::RFC3339),
            'timeZone' => $this->timezone
        )));

        $event->setEnd(new Google\Service\Calendar\EventDateTime(array(
            'dateTime' => $endTime->format(DateTime::RFC3339),
            'timeZone' => $this->timezone
        )));

        // Attendees (skip invalid / duplicate emails)
        $attendees = array();
        if (!empty($data['attendees'])) {
            $seen = array();

            foreach ($data['attendees'] as $email) {
                $email = trim($email);

                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                if (isset($seen[strtolower($email)])) {
                    continue;
                }

                $seen[strtolower($email)] = TRUE;
                $attendees[] = $email;
            }
        }

        if (!empty($attendees)) {
            $eventAttendees = array();

            foreach ($attendees as $email) {
                $eventAttendees[] = new Google\Service\Calendar\EventAttendee(array('email' => $email));
            }

            $event->setAttendees($eventAttendees);
        }

        // Google Meet conference - explicit object construction
        $conferenceSolutionKey = new Google\Service\Calendar\ConferenceSolutionKey();
        $conferenceSolutionKey->setType('hangoutsMeet');

        $createConferenceRequest = new Google\Service\Calendar\CreateConferenceRequest();
        // Google requires a unique request ID for each conference
        $createConferenceRequest->setRequestId('worknexus-' . bin2hex(random_bytes(16)));
        $createConferenceRequest->setConferenceSolutionKey($conferenceSolutionKey);

        $conferenceData = new Google\Service\Calendar\ConferenceData();
        $conferenceData->setCreateRequest($createConferenceRequest);

        $event->setConferenceData($conferenceData);

        // Debug: record exactly what is being sent (no secrets)
        log_message('debug', 'WorkNexusGoogle: Creating event on calendar "' . $this->calendarId . '" start=' . $startTime->format(DateTime::RFC3339) . ' end=' . $endTime->format(DateTime::RFC3339) . ' timezone=' . $this->timezone . ' attendee_count=' . count($attendees));

        try {
            // conferenceDataVersion = 1 tells Google Calendar to process the conference request
            $createdEvent = $calendarService->events->insert(
                $this->calendarId,
                $event,
                array(
                    'conferenceDataVersion' => 1,
                    'sendUpdates'           => 'all'
                )
            );
        } catch (Google\Service\Exception $e) {
            $this->logGoogleServiceException($e);
            return array(
                'success'    => FALSE,
                'needs_auth' => FALSE,
                'event_id'   => NULL,
                'meet_link'  => NULL,
                'error'      => 'Google Calendar could not create the meeting. Please try again.'
            );
        } catch (Google\Exception $e) {
            log_message('error', 'WorkNexusGoogle: Google client exception (class=' . get_class($e) . '): ' . $e->getMessage());
            return array(
                'success'    => FALSE,
                'needs_auth' => FALSE,
                'event_id'   => NULL,
                'meet_link'  => NULL,
                'error'      => 'Google Calendar could not create the meeting. Please try again.'
            );
        }

        // Extract the Google Meet join URL from the conference entry points
        $meetLink = NULL;

        $conferenceData = $createdEvent->getConferenceData();

        if ($conferenceData) {
            $entryPoints = $conferenceData->getEntryPoints();

            if ($entryPoints) {
                foreach ($entryPoints as $entryPoint) {
                    if ($entryPoint->getEntryPointType() === 'video') {
                        $meetLink = $entryPoint->getUri();
                        break;
                    }
                }
            }
        }

        return array(
            'success'    => TRUE,
            'needs_auth' => FALSE,
            'event_id'   => $createdEvent->getId(),
            'meet_link'  => $meetLink,
            'error'      => NULL
        );
    }

    /**
     * Log a Google Calendar API service exception with maximum useful detail.
     * Never logs tokens, client secrets, credentials.json or authorization codes.
     */
    private function logGoogleServiceException(Google\Service\Exception $e)
    {
        log_message('error', 'WorkNexusGoogle: Calendar API exception (class=' . get_class($e) . '): ' . $e->getMessage());

        if (method_exists($e, 'getCode')) {
            $httpCode = $e->getCode();
            if ($httpCode) {
                log_message('error', 'WorkNexusGoogle: HTTP status: ' . $httpCode);
            }
        }

        $errors = $e->getErrors();

        if (is_array($errors) && !empty($errors)) {
            $first = $errors[0];

            if (isset($first['reason'])) {
                log_message('error', 'WorkNexusGoogle: API reason: ' . $first['reason']);
            }

            if (isset($first['domain'])) {
                log_message('error', 'WorkNexusGoogle: API domain: ' . $first['domain']);
            }

            if (isset($first['message'])) {
                log_message('error', 'WorkNexusGoogle: API message: ' . $first['message']);
            }
        }
    }
}
