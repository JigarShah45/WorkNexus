<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Google extends CI_Controller
{
    private $client;

    public function __construct()
    {
        parent::__construct();

        // Load Composer autoloader
        require_once FCPATH . 'vendor/autoload.php';

        // Create Google client
        $this->client = new Google\Client();

        // Google OAuth credentials
        $credentialsPath = APPPATH . 'config/google/credentials.json';

        if (!file_exists($credentialsPath)) {
            show_error('Google credentials.json not found.');
            return;
        }

        $this->client->setAuthConfig($credentialsPath);

        // OAuth callback URL
        $this->client->setRedirectUri(
            site_url('google/callback')
        );

        /*
         * Only request the permission we actually need.
         *
         * This allows WorkNexus to create and modify
         * Google Calendar events.
         */
        $this->client->setScopes([
            Google\Service\Calendar::CALENDAR
        ]);

        // Request refresh token
        $this->client->setAccessType('offline');

        // Show consent screen when connecting
        $this->client->setPrompt('consent');
    }


    /**
     * ---------------------------------------------------------
     * CONNECT GOOGLE CALENDAR
     * ---------------------------------------------------------
     *
     * URL:
     * /index.php/google/connect
     */
    public function connect()
    {
        /*
         * Optional return target.
         *
         * WorkNexus sets this automatically before redirecting here so that
         * after a successful OAuth the user is returned to the page they
         * were working on instead of being stranded on this URL.
         *
         * Example: /index.php/google/connect?redirect_to=meetings/add
         */
        $redirectTo = $this->input->get('redirect_to');

        if ($redirectTo) {
            $this->session->set_userdata('google_after_auth', $redirectTo);
        }

        $authUrl = $this->client->createAuthUrl();

        redirect($authUrl);
    }


    /**
     * ---------------------------------------------------------
     * GOOGLE OAUTH CALLBACK
     * ---------------------------------------------------------
     *
     * URL:
     * /index.php/google/callback
     */
    public function callback()
    {
        // Google did not return authorization code
        if (!$this->input->get('code')) {

            $error = $this->input->get('error');

            if ($error) {
                show_error(
                    'Google authorization failed: ' .
                    htmlspecialchars($error)
                );
            }

            show_error(
                'Google authorization code was not received.'
            );

            return;
        }

        $code = $this->input->get('code');

        // Exchange authorization code for access token
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        // Check for Google OAuth errors
        if (isset($token['error'])) {

            show_error(
                'Google authentication failed: ' .
                (
                    $token['error_description']
                    ?? $token['error']
                )
            );

            return;
        }

        /*
         * Store token securely.
         *
         * IMPORTANT:
         * token.json must be included in .gitignore.
         */
        $tokenPath = APPPATH . 'config/google/token.json';

        /*
         * Preserve the previously stored refresh_token.
         *
         * Google does not always return a new refresh_token on
         * re-authorization. Overwriting a working refresh_token with a
         * missing/empty one would force the user through OAuth again as
         * soon as the access token expires. This is a common reason
         * applications suddenly require reconnecting.
         */
        $existing = array();

        if (file_exists($tokenPath)) {
            $existing = json_decode(file_get_contents($tokenPath), TRUE);

            if (!is_array($existing)) {
                $existing = array();
            }
        }

        if (empty($token['refresh_token']) && !empty($existing['refresh_token'])) {
            $token['refresh_token'] = $existing['refresh_token'];
        }

        file_put_contents(
            $tokenPath,
            json_encode(
                $token,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        /*
         * Return to the page the user was on before authorization
         * (set automatically by the meeting flow via google/connect).
         */
        $returnUrl = $this->session->userdata('google_after_auth');

        $this->session->unset_userdata('google_after_auth');

        if (empty($returnUrl)) {
            $returnUrl = 'dashboard';
        }

        $this->session->set_flashdata('success', 'Google Calendar connected successfully. Please submit your meeting again.');

        redirect($returnUrl);
    }


    /**
     * ---------------------------------------------------------
     * TEST GOOGLE CALENDAR + GOOGLE MEET
     * ---------------------------------------------------------
     *
     * URL:
     * /index.php/google/test_meeting
     *
     * This creates a test Calendar event approximately
     * one hour from the current time and requests a
     * Google Meet conference.
     */
    // public function test_meeting()
    // {
    //     $tokenPath = APPPATH . 'config/google/token.json';

    //     /*
    //      * Make sure Google has been connected.
    //      */
    //     if (!file_exists($tokenPath)) {

    //         show_error(
    //             'Google Calendar is not connected. ' .
    //             'Please connect Google first.'
    //         );

    //         return;
    //     }


    //     /*
    //      * Read saved OAuth token.
    //      */
    //     $tokenJson = file_get_contents($tokenPath);

    //     $token = json_decode($tokenJson, true);

    //     if (!is_array($token)) {

    //         show_error(
    //             'Google token.json is invalid.'
    //         );

    //         return;
    //     }


    //     /*
    //      * Give token to Google client.
    //      */
    //     $this->client->setAccessToken($token);


    //     /*
    //      * Refresh access token if necessary.
    //      */
    //     if ($this->client->isAccessTokenExpired()) {

    //         $refreshToken = $this->client->getRefreshToken();

    //         /*
    //          * Sometimes the refresh token is stored
    //          * only inside token.json.
    //          */
    //         if (!$refreshToken && isset($token['refresh_token'])) {
    //             $refreshToken = $token['refresh_token'];
    //         }


    //         if (!$refreshToken) {

    //             show_error(
    //                 'Google access token expired and no refresh token ' .
    //                 'was found. Please reconnect Google Calendar.'
    //             );

    //             return;
    //         }


    //         /*
    //          * Request a new access token.
    //          */
    //         $newToken =
    //             $this->client->fetchAccessTokenWithRefreshToken(
    //                 $refreshToken
    //             );


    //         if (isset($newToken['error'])) {

    //             show_error(
    //                 'Unable to refresh Google token: ' .
    //                 (
    //                     $newToken['error_description']
    //                     ?? $newToken['error']
    //                 )
    //             );

    //             return;
    //         }


    //         /*
    //          * Preserve the refresh token.
    //          */
    //         $token = array_merge(
    //             $token,
    //             $newToken
    //         );

    //         if (!isset($token['refresh_token'])) {
    //             $token['refresh_token'] = $refreshToken;
    //         }


    //         /*
    //          * Save updated token.
    //          */
    //         file_put_contents(
    //             $tokenPath,
    //             json_encode(
    //                 $token,
    //                 JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    //             )
    //         );


    //         /*
    //          * Update Google client.
    //          */
    //         $this->client->setAccessToken($token);
    //     }


    //     /*
    //      * Create Google Calendar service.
    //      */
    //     $calendarService =
    //         new Google\Service\Calendar(
    //             $this->client
    //         );


    //     /*
    //      * -----------------------------------------------------
    //      * CREATE GOOGLE MEET CONFERENCE
    //      * -----------------------------------------------------
    //      */

    //     $conferenceSolutionKey =
    //         new Google\Service\Calendar\ConferenceSolutionKey();

    //     $conferenceSolutionKey->setType(
    //         'hangoutsMeet'
    //     );


    //     $createConferenceRequest =
    //         new Google\Service\Calendar\CreateConferenceRequest();

    //     /*
    //      * Google requires a unique request ID.
    //      */
    //     $createConferenceRequest->setRequestId(
    //         'worknexus-' . bin2hex(random_bytes(16))
    //     );


    //     $createConferenceRequest->setConferenceSolutionKey(
    //         $conferenceSolutionKey
    //     );


    //     $conferenceData =
    //         new Google\Service\Calendar\ConferenceData();

    //     $conferenceData->setCreateRequest(
    //         $createConferenceRequest
    //     );


    //     /*
    //      * -----------------------------------------------------
    //      * MEETING START / END TIME
    //      * -----------------------------------------------------
    //      */

    //     $timezone = new DateTimeZone(
    //         'Asia/Kolkata'
    //     );


    //     $startTime = new DateTime(
    //         'now',
    //         $timezone
    //     );

    //     /*
    //      * Test meeting starts one hour from now.
    //      */
    //     $startTime->modify('+1 hour');


    //     $endTime = clone $startTime;

    //     /*
    //      * Meeting duration = 1 hour.
    //      */
    //     $endTime->modify('+1 hour');


    //     /*
    //      * -----------------------------------------------------
    //      * EVENT START
    //      * -----------------------------------------------------
    //      */

    //     $eventStart =
    //         new Google\Service\Calendar\EventDateTime();

    //     $eventStart->setDateTime(
    //         $startTime->format(DateTime::RFC3339)
    //     );

    //     $eventStart->setTimeZone(
    //         'Asia/Kolkata'
    //     );


    //     /*
    //      * -----------------------------------------------------
    //      * EVENT END
    //      * -----------------------------------------------------
    //      */

    //     $eventEnd =
    //         new Google\Service\Calendar\EventDateTime();

    //     $eventEnd->setDateTime(
    //         $endTime->format(DateTime::RFC3339)
    //     );

    //     $eventEnd->setTimeZone(
    //         'Asia/Kolkata'
    //     );


    //     /*
    //      * -----------------------------------------------------
    //      * CREATE CALENDAR EVENT
    //      * -----------------------------------------------------
    //      */

    //     $event =
    //         new Google\Service\Calendar\Event();

    //     $event->setSummary(
    //         'WorkNexus Google Meet Test'
    //     );

    //     $event->setDescription(
    //         'Test Google Meet meeting created from WorkNexus.'
    //     );

    //     $event->setStart(
    //         $eventStart
    //     );

    //     $event->setEnd(
    //         $eventEnd
    //     );

    //     $event->setConferenceData(
    //         $conferenceData
    //     );


    //     /*
    //      * -----------------------------------------------------
    //      * INSERT EVENT
    //      * -----------------------------------------------------
    //      *
    //      * conferenceDataVersion = 1 tells Google Calendar
    //      * to process the conference creation request.
    //      */
    //     $createdEvent =
    //         $calendarService->events->insert(
    //             'primary',
    //             $event,
    //             [
    //                 'conferenceDataVersion' => 1
    //             ]
    //         );


    //     /*
    //      * -----------------------------------------------------
    //      * GET GOOGLE MEET LINK
    //      * -----------------------------------------------------
    //      */

    //     $meetLink = null;

    //     $createdConferenceData =
    //         $createdEvent->getConferenceData();


    //     if ($createdConferenceData) {

    //         $entryPoints =
    //             $createdConferenceData->getEntryPoints();


    //         if ($entryPoints) {

    //             foreach ($entryPoints as $entryPoint) {

    //                 if (
    //                     $entryPoint->getEntryPointType()
    //                     === 'video'
    //                 ) {

    //                     $meetLink =
    //                         $entryPoint->getUri();

    //                     break;
    //                 }
    //             }
    //         }
    //     }


    //     /*
    //      * -----------------------------------------------------
    //      * DISPLAY RESULT
    //      * -----------------------------------------------------
    //      */

    //     echo '<!DOCTYPE html>';

    //     echo '<html>';

    //     echo '<head>';

    //     echo '<title>WorkNexus Google Meet Test</title>';

    //     echo '</head>';

    //     echo '<body style="
    //         font-family: Arial, sans-serif;
    //         padding: 40px;
    //         background: #f5f7fb;
    //     ">';


    //     echo '<div style="
    //         max-width: 700px;
    //         margin: auto;
    //         background: white;
    //         padding: 30px;
    //         border-radius: 12px;
    //         box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    //     ">';


    //     echo '<h2>Google Calendar Event Created</h2>';


    //     echo '<p>';

    //     echo '<strong>Event ID:</strong><br>';

    //     echo htmlspecialchars(
    //         $createdEvent->getId()
    //     );

    //     echo '</p>';


    //     /*
    //      * Conference status
    //      */
    //     $conferenceStatus = 'pending';


    //     if ($createdConferenceData) {

    //         $createRequest =
    //             $createdConferenceData
    //                 ->getCreateRequest();


    //         if ($createRequest) {

    //             $status =
    //                 $createRequest->getStatus();


    //             if ($status) {

    //                 $conferenceStatus =
    //                     $status->getStatusCode()
    //                     ?? 'pending';
    //             }
    //         }
    //     }


    //     echo '<p>';

    //     echo '<strong>Conference Status:</strong><br>';

    //     echo htmlspecialchars(
    //         $conferenceStatus
    //     );

    //     echo '</p>';


    //     /*
    //      * Meet link
    //      */
    //     if ($meetLink) {

    //         echo '<p>';

    //         echo '<strong>Google Meet:</strong><br>';

    //         echo '<a href="'
    //             . htmlspecialchars($meetLink)
    //             . '" target="_blank">'
    //             . htmlspecialchars($meetLink)
    //             . '</a>';

    //         echo '</p>';

    //     } else {

    //         echo '<p>';

    //         echo '<strong>Google Meet:</strong><br>';

    //         echo 'The conference is still being generated. ';

    //         echo 'Open Google Calendar and check the event.';

    //         echo '</p>';
    //     }


    //     echo '<p>';

    //     echo '<a href="'
    //         . site_url('meetings')
    //         . '">';

    //     echo 'Return to WorkNexus Meetings';

    //     echo '</a>';

    //     echo '</p>';


    //     echo '</div>';

    //     echo '</body>';

    //     echo '</html>';
    // }

    public function test_meeting()
{
    $tokenPath = APPPATH . 'config/google/token.json';

    if (!file_exists($tokenPath)) {
        show_error(
            'Google Calendar is not connected. Please connect Google first.'
        );
        return;
    }

    $token = json_decode(
        file_get_contents($tokenPath),
        true
    );

    if (!is_array($token)) {
        show_error('Invalid Google token.json file.');
        return;
    }

    $this->client->setAccessToken($token);

    /*
     * Refresh access token if necessary
     */
    if ($this->client->isAccessTokenExpired()) {

        $refreshToken = $this->client->getRefreshToken();

        if (!$refreshToken && isset($token['refresh_token'])) {
            $refreshToken = $token['refresh_token'];
        }

        if (!$refreshToken) {
            show_error(
                'Google access token expired and no refresh token was found. ' .
                'Please reconnect Google Calendar.'
            );
            return;
        }

        $newToken =
            $this->client->fetchAccessTokenWithRefreshToken(
                $refreshToken
            );

        if (isset($newToken['error'])) {
            show_error(
                'Unable to refresh Google token: ' .
                ($newToken['error_description'] ?? $newToken['error'])
            );
            return;
        }

        $token = array_merge($token, $newToken);

        if (!isset($token['refresh_token'])) {
            $token['refresh_token'] = $refreshToken;
        }

        file_put_contents(
            $tokenPath,
            json_encode(
                $token,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        $this->client->setAccessToken($token);
    }

    /*
     * Create Calendar service
     */
    $calendarService =
        new Google\Service\Calendar($this->client);

    /*
     * ---------------------------------------------------------
     * MEETING TIME
     * ---------------------------------------------------------
     */

    $timezone = new DateTimeZone('Asia/Kolkata');

    $startTime = new DateTime(
        'now',
        $timezone
    );

    // Start one hour from now
    $startTime->modify('+1 hour');

    $endTime = clone $startTime;

    // One hour duration
    $endTime->modify('+1 hour');

    /*
     * ---------------------------------------------------------
     * CREATE EVENT
     * ---------------------------------------------------------
     *
     * We use the array constructor here.
     * This creates the exact JSON structure expected
     * by Google Calendar.
     */

    $event = new Google\Service\Calendar\Event([
        'summary' => 'WorkNexus Google Meet Test',

        'description' =>
            'Test Google Meet meeting created from WorkNexus.',

        'start' => [
            'dateTime' =>
                $startTime->format(DateTime::RFC3339),

            'timeZone' =>
                'Asia/Kolkata'
        ],

        'end' => [
            'dateTime' =>
                $endTime->format(DateTime::RFC3339),

            'timeZone' =>
                'Asia/Kolkata'
        ],

        /*
         * Google Meet configuration
         */
        'conferenceData' => [
            'createRequest' => [
                'requestId' =>
                    'worknexus-' .
                    bin2hex(random_bytes(16)),

                'conferenceSolutionKey' => [
                    'type' => 'hangoutsMeet'
                ]
            ]
        ]
    ]);

    try {

        /*
         * Create Calendar event + Google Meet
         */
        $createdEvent =
            $calendarService->events->insert(
                'primary',
                $event,
                [
                    'conferenceDataVersion' => 1,
                    'sendUpdates' => 'all'
                ]
            );

    } catch (Google\Service\Exception $e) {

        echo '<h2>Google Calendar API Error</h2>';

        echo '<pre>';

        echo htmlspecialchars(
            $e->getMessage()
        );

        echo '</pre>';

        return;
    }

    /*
     * ---------------------------------------------------------
     * READ CONFERENCE DATA
     * ---------------------------------------------------------
     */

    $meetLink = null;

    $conferenceData =
        $createdEvent->getConferenceData();

    $conferenceStatus = 'unknown';

    if ($conferenceData) {

        /*
         * Conference creation status
         */
        $createRequest =
            $conferenceData->getCreateRequest();

        if ($createRequest) {

            $status =
                $createRequest->getStatus();

            if ($status) {

                $conferenceStatus =
                    $status->getStatusCode()
                    ?? 'unknown';
            }
        }

        /*
         * Get Meet URL
         */
        $entryPoints =
            $conferenceData->getEntryPoints();

        if ($entryPoints) {

            foreach ($entryPoints as $entryPoint) {

                if (
                    $entryPoint->getEntryPointType()
                    === 'video'
                ) {

                    $meetLink =
                        $entryPoint->getUri();

                    break;
                }
            }
        }
    }

    /*
     * ---------------------------------------------------------
     * DISPLAY RESULT
     * ---------------------------------------------------------
     */

    echo '<!DOCTYPE html>';

    echo '<html>';

    echo '<head>';

    echo '<title>WorkNexus Google Meet Test</title>';

    echo '</head>';

    echo '<body style="
        font-family: Arial, sans-serif;
        padding: 40px;
        background: #f5f7fb;
    ">';

    echo '<div style="
        max-width: 750px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    ">';

    echo '<h2>Google Calendar Event Created</h2>';

    echo '<p>';

    echo '<strong>Event ID:</strong><br>';

    echo htmlspecialchars(
        $createdEvent->getId()
    );

    echo '</p>';

    echo '<p>';

    echo '<strong>Conference Status:</strong><br>';

    echo htmlspecialchars(
        $conferenceStatus
    );

    echo '</p>';

    if ($meetLink) {

        echo '<div style="
            margin-top: 25px;
            padding: 20px;
            background: #e8f5e9;
            border-radius: 8px;
        ">';

        echo '<h3>Google Meet Created Successfully 🎉</h3>';

        echo '<p>';

        echo '<a href="'
            . htmlspecialchars($meetLink)
            . '"
            target="_blank"
            style="
                font-size: 18px;
                font-weight: bold;
            ">';

        echo htmlspecialchars($meetLink);

        echo '</a>';

        echo '</p>';

        echo '</div>';

    } else {

        echo '<div style="
            margin-top: 25px;
            padding: 20px;
            background: #fff3cd;
            border-radius: 8px;
        ">';

        echo '<h3>Meet conference is still being generated</h3>';

        echo '<p>';

        echo 'Conference status: ';

        echo htmlspecialchars(
            $conferenceStatus
        );

        echo '</p>';

        echo '<p>';

        echo 'Open Google Calendar and check the newly created event.';

        echo '</p>';

        echo '</div>';
    }

    echo '<p style="margin-top: 30px;">';

    echo '<a href="' .
        site_url('meetings') .
        '">';

    echo '← Return to WorkNexus Meetings';

    echo '</a>';

    echo '</p>';

    echo '</div>';

    echo '</body>';

    echo '</html>';
}

    public function diagnose_calendar()
{
    $tokenPath = APPPATH . 'config/google/token.json';

    if (!file_exists($tokenPath)) {
        show_error('Google Calendar is not connected. Please connect Google first.');
        return;
    }

    $token = json_decode(
        file_get_contents($tokenPath),
        true
    );

    if (!is_array($token)) {
        show_error('Invalid Google token.json file.');
        return;
    }

    $this->client->setAccessToken($token);

    // Refresh token if expired
    if ($this->client->isAccessTokenExpired()) {

        $refreshToken = $this->client->getRefreshToken();

        if (!$refreshToken && isset($token['refresh_token'])) {
            $refreshToken = $token['refresh_token'];
        }

        if (!$refreshToken) {
            show_error(
                'Access token expired and no refresh token was found. ' .
                'Please reconnect Google Calendar.'
            );
            return;
        }

        $newToken =
            $this->client->fetchAccessTokenWithRefreshToken(
                $refreshToken
            );

        if (isset($newToken['error'])) {
            show_error(
                'Unable to refresh Google token: ' .
                ($newToken['error_description'] ?? $newToken['error'])
            );
            return;
        }

        $token = array_merge($token, $newToken);

        if (!isset($token['refresh_token'])) {
            $token['refresh_token'] = $refreshToken;
        }

        file_put_contents(
            $tokenPath,
            json_encode(
                $token,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        $this->client->setAccessToken($token);
    }

    // Create Calendar service
    $calendarService =
        new Google\Service\Calendar($this->client);

    try {

        // Get the primary calendar
        $calendar =
            $calendarService->calendars->get('primary');

        $conferenceProperties =
            $calendar->getConferenceProperties();

        $allowedTypes = [];

        if ($conferenceProperties) {
            $allowedTypes =
                $conferenceProperties
                    ->getAllowedConferenceSolutionTypes();
        }

        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<title>WorkNexus Calendar Diagnostic</title>';
        echo '</head>';

        echo '<body style="
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f5f7fb;
        ">';

        echo '<div style="
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        ">';

        echo '<h2>WorkNexus Google Calendar Diagnostic</h2>';

        echo '<hr>';

        echo '<p>';
        echo '<strong>Calendar:</strong><br>';
        echo htmlspecialchars(
            $calendar->getSummary() ?? 'Unknown'
        );
        echo '</p>';

        echo '<p>';
        echo '<strong>Calendar ID:</strong><br>';
        echo htmlspecialchars(
            $calendar->getId() ?? 'Unknown'
        );
        echo '</p>';

        echo '<h3>Allowed Conference Types</h3>';

        if (!empty($allowedTypes)) {

            echo '<ul>';

            foreach ($allowedTypes as $type) {

                echo '<li>';
                echo '<strong>';
                echo htmlspecialchars($type);
                echo '</strong>';
                echo '</li>';
            }

            echo '</ul>';

        } else {

            echo '<p style="color: red;">';
            echo 'Google did not report any allowed conference types.';
            echo '</p>';
        }

        echo '<h3>Raw Response</h3>';

        echo '<pre style="
            background: #f1f3f5;
            padding: 20px;
            overflow-x: auto;
        ">';

        echo htmlspecialchars(
            json_encode(
                $conferenceProperties,
                JSON_PRETTY_PRINT
            )
        );

        echo '</pre>';

        echo '</div>';

        echo '</body>';
        echo '</html>';

    } catch (Google\Service\Exception $e) {

        echo '<h2>Google Calendar API Error</h2>';

        echo '<pre>';
        echo htmlspecialchars(
            $e->getMessage()
        );
        echo '</pre>';
    }
}
}