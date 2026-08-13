<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| WorkNexus Email Configuration
|--------------------------------------------------------------------------
| SMTP credentials should be set via environment variables in production.
| Copy this file to email_local.php and set your actual credentials.
|
| IMPORTANT: from_email MUST match the authenticated SMTP account.
| Do not use a different address here than what you authenticate with.
*/
$config['email_type'] = 'smtp'; // 'phpmail' or 'smtp'

// SMTP Settings
$config['smtp_host'] = getenv('WN_SMTP_HOST') ?: 'smtp.gmail.com';
$config['smtp_port'] = getenv('WN_SMTP_PORT') ?: 587;
$config['smtp_user'] = getenv('WN_SMTP_USER') ?: '';
$config['smtp_pass'] = getenv('WN_SMTP_PASS') ?: '';
$config['smtp_crypto'] = 'tls'; // tls or ssl

// Sender — must match the authenticated SMTP account
$config['from_email'] = getenv('WN_FROM_EMAIL') ?: $config['smtp_user'];
$config['from_name']  = 'WorkNexus';

// Reply-To — same as sender for transactional emails
$config['reply_to'] = getenv('WN_FROM_EMAIL') ?: $config['smtp_user'];

$config['mailtype'] = 'html';
$config['charset']  = 'UTF-8';
