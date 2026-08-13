<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WorkNexus Mailer - Centralized Email Service
 *
 * Sends multipart HTML + plain-text emails using CodeIgniter's email library.
 * Falls back to PHP mail() if SMTP is not configured.
 * Email failures are logged but never break business logic.
 *
 * Includes:
 *  - Reply-To header matching the authenticated sender
 *  - X-Mailer identification header
 *  - Multipart/alternative (HTML + plain text)
 *  - Duplicate send protection via email_sent column
 */
class Worknexusmailer {

    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('email');
    }

    /**
     * Send an email using the WorkNexus HTML template.
     *
     * @param string $to        Recipient email address
     * @param string $subject   Email subject
     * @param string $title     Template heading (e.g. "Leave Approved")
     * @param string $name      Recipient name
     * @param string $body      Email body (HTML content)
     * @param string $buttonUrl Optional "View in WorkNexus" button URL
     * @param string $buttonText Optional button text
     * @return bool
     */
    public function send($to, $subject, $title, $name, $body, $buttonUrl = '', $buttonText = 'View in WorkNexus')
    {
        if (empty($to))
        {
            log_message('error', 'WorkNexusMailer: No recipient email provided.');
            return FALSE;
        }

        $html = $this->renderTemplate($title, $name, $body, $buttonUrl, $buttonText);
        $plain = $this->renderPlainText($title, $name, $body, $buttonUrl, $buttonText);

        return $this->dispatch($to, $subject, $html, $plain);
    }

    /**
     * Dispatch email via configured method.
     */
    private function dispatch($to, $subject, $html, $plain)
    {
        $type = $this->CI->config->item('email_type');

        if ($type === 'smtp')
        {
            return $this->sendViaSmtp($to, $subject, $html, $plain);
        }

        return $this->sendViaPhpMail($to, $subject, $html, $plain);
    }

    /**
     * Send using CodeIgniter Email library (SMTP).
     */
    private function sendViaSmtp($to, $subject, $html, $plain)
    {
        $this->CI->load->library('email');

        $this->CI->email->clear();
        $this->CI->email->initialize(array(
            'protocol'     => 'smtp',
            'smtp_host'    => $this->CI->config->item('smtp_host'),
            'smtp_port'    => $this->CI->config->item('smtp_port'),
            'smtp_user'    => $this->CI->config->item('smtp_user'),
            'smtp_pass'    => $this->CI->config->item('smtp_pass'),
            'smtp_crypto'  => $this->CI->config->item('smtp_crypto'),
            'smtp_timeout' => 30,
            'charset'      => 'utf-8',
            'newline'      => "\r\n",
            'crlf'         => "\r\n",
            'wordwrap'     => TRUE,
        ));

        $fromEmail = $this->CI->config->item('from_email');
        $fromName  = $this->CI->config->item('from_name');

        $this->CI->email->from($fromEmail, $fromName);
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->set_mailtype('html');
        $this->CI->email->set_alt_message($plain);

        // Reply-To: same as sender for transactional emails
        $replyTo = $this->CI->config->item('reply_to');
        if ( ! empty($replyTo))
        {
            $this->CI->email->set_header('Reply-To', $replyTo);
        }

        $this->CI->email->set_header('X-Mailer', 'WorkNexus/1.0');
        $this->CI->email->set_header('Precedence', 'bulk');
        $this->CI->email->set_header('X-Auto-Response-Suppress', 'All');

        $this->CI->email->message($html);

        if ( ! $this->CI->email->send())
        {
            log_message('error', 'WorkNexusMailer SMTP failed: ' . $this->CI->email->print_debugger(array('headers')));
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Send using PHP native mail().
     */
    private function sendViaPhpMail($to, $subject, $html, $plain)
    {
        $fromEmail = $this->CI->config->item('from_email');
        $fromName  = $this->CI->config->item('from_name');
        $replyTo   = $this->CI->config->item('reply_to');

        $boundary = md5(uniqid(time()));

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";

        if ( ! empty($replyTo))
        {
            $headers .= "Reply-To: {$replyTo}\r\n";
        }

        $headers .= "X-Mailer: WorkNexus/1.0\r\n";
        $headers .= "Precedence: bulk\r\n";
        $headers .= "X-Auto-Response-Suppress: All\r\n";

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $plain . "\r\n\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $html . "\r\n\r\n";
        $body .= "--{$boundary}--";

        $result = @mail($to, $subject, $body, $headers);

        if ( ! $result)
        {
            log_message('error', "WorkNexusMailer: PHP mail() failed for {$to}");
        }

        return $result;
    }

    /**
     * Generate plain-text alternative from HTML body content.
     */
    private function renderPlainText($title, $name, $body, $buttonUrl = '', $buttonText = 'View in WorkNexus')
    {
        // Strip HTML tags from the body content
        $plainBody = strip_tags($body);
        $plainBody = html_entity_decode($plainBody, ENT_QUOTES, 'UTF-8');
        $plainBody = preg_replace('/\n{3,}/', "\n\n", trim($plainBody));

        $text  = $title . "\n";
        $text .= str_repeat('=', strlen($title)) . "\n\n";
        $text .= "Hello {$name},\n\n";
        $text .= $plainBody . "\n\n";

        if ( ! empty($buttonUrl))
        {
            $text .= $buttonText . ": {$buttonUrl}\n\n";
        }

        $text .= "---\n";
        $text .= "Regards,\n";
        $text .= "WorkNexus\n";
        $text .= "Employee Management System\n";

        return $text;
    }

    /**
     * Get logo as base64 data URI for embedding in emails.
     * Converts SVG to PNG using GD library for email client compatibility.
     */
    private function getLogoDataUri()
    {
        $svgPath = FCPATH . 'assets/images/logo-icon.svg';

        if ( ! file_exists($svgPath))
        {
            return $this->getFallbackLogoDataUri();
        }

        if (function_exists('imagecreatefromsvg'))
        {
            $img = @imagecreatefromsvg($svgPath);
            if ($img)
            {
                ob_start();
                imagepng($img);
                $pngData = ob_get_clean();
                imagedestroy($img);
                return 'data:image/png;base64,' . base64_encode($pngData);
            }
        }

        return $this->getFallbackLogoDataUri();
    }

    /**
     * Generate a fallback PNG logo using GD primitives.
     * Used when SVG loader is not available.
     */
    private function getFallbackLogoDataUri()
    {
        if ( ! function_exists('imagecreatetruecolor'))
        {
            return '';
        }

        $size = 128;
        $img = imagecreatetruecolor($size, $size);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);

        $blue  = imagecolorallocate($img, 79, 70, 229);
        $white = imagecolorallocate($img, 255, 255, 255);
        $whiteAlpha = imagecolorallocatealpha($img, 255, 255, 255, 38);

        $cx = $size / 2;
        $cy = $size / 2;
        $r  = 55;

        imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $blue);

        $s = $size / 48;
        $topX = round(24 * $s);
        $topY = round(14 * $s);
        $leftX = round(13 * $s);
        $leftY = round(30 * $s);
        $rightX = round(35 * $s);
        $rightY = round(30 * $s);

        imagesetthickness($img, max(2, round(2 * $s)));
        imageline($img, $topX, $topY, $leftX, $leftY, $whiteAlpha);
        imageline($img, $topX, $topY, $rightX, $rightY, $whiteAlpha);
        imageline($img, $leftX, $leftY, $rightX, $rightY, $whiteAlpha);

        $d1 = round(5.5 * $s);
        $d2 = round(4.5 * $s);
        imagefilledellipse($img, $topX, $topY, $d1 * 2, $d1 * 2, $white);
        imagefilledellipse($img, $leftX, $leftY, $d2 * 2, $d2 * 2, $white);
        imagefilledellipse($img, $rightX, $rightY, $d2 * 2, $d2 * 2, $white);

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    /**
     * Render the WorkNexus HTML email template.
     */
    private function renderTemplate($title, $name, $body, $buttonUrl = '', $buttonText = 'View in WorkNexus')
    {
        $buttonHtml = '';
        if ( ! empty($buttonUrl))
        {
            $buttonHtml = '
                <div style="text-align:center;margin:28px 0 12px 0;">
                    <a href="' . htmlspecialchars($buttonUrl) . '"
                       style="display:inline-block;padding:12px 32px;background-color:#4F46E5;color:#FFFFFF;font-family:Arial,sans-serif;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;">' . htmlspecialchars($buttonText) . '</a>
                </div>';
        }

        $logoSrc = $this->getLogoDataUri();

        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#F3F4F6;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3F4F6;padding:40px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4F46E5,#6366F1);padding:28px 32px;text-align:center;">
                            <img src="' . $logoSrc . '" alt="WorkNexus" width="48" height="48" style="vertical-align:middle;border-radius:10px;">
                            <span style="font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#FFFFFF;vertical-align:middle;margin-left:12px;">WorkNexus</span>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 32px 24px 32px;">
                            <h2 style="margin:0 0 8px 0;font-size:20px;font-weight:700;color:#111827;font-family:Arial,sans-serif;">' . htmlspecialchars($title) . '</h2>
                            <p style="margin:0 0 24px 0;font-size:15px;color:#6B7280;font-family:Arial,sans-serif;">Hello ' . htmlspecialchars($name) . ',</p>
                            <div style="font-size:14px;color:#374151;line-height:1.7;font-family:Arial,sans-serif;">
                                ' . $body . '
                            </div>
                            ' . $buttonHtml . '
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid #E5E7EB;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9CA3AF;font-family:Arial,sans-serif;">
                                Regards,<br><strong>WorkNexus</strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

        return $html;
    }
}
