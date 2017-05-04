<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

define('SMTP_INCLUDED', 1);

function server_parse($socket, $response, $line = __LINE__)
{
    $server_response = '';
    while (substr($server_response, 3, 1) != ' ') {
        if (!($server_response = fgets($socket, 256))) {
            bb_die('Could not get mail server response codes');
        }
    }

    if (!(substr($server_response, 0, 3) == $response)) {
        bb_die('Ran into problems sending mail. Response: ' . $server_response);
    }
}

// Replacement or substitute for PHP's mail command
function smtpmail($mail_to, $subject, $message, $headers = '')
{
    global $bb_cfg;

    // Fix any bare linefeeds in the message to make it RFC821 Compliant.
    $message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

    if ($headers != '') {
        if (is_array($headers)) {
            if (count($headers) > 1) {
                $headers = implode("\n", $headers);
            } else {
                $headers = $headers[0];
            }
        }
        $headers = rtrim($headers);

        // Make sure there are no bare linefeeds in the headers
        $headers = preg_replace('#(?<!\r)\n#si', "\r\n", $headers);

        // Ok this is rather confusing all things considered,
        // but we have to grab bcc and cc headers and treat them differently
        // Something we really didn't take into consideration originally
        $header_array = explode("\r\n", $headers);
        @reset($header_array);

        $headers = $cc = $bcc = '';
        while (list(, $header) = each($header_array)) {
            if (preg_match('#^cc:#si', $header)) {
                $cc = preg_replace('#^cc:(.*)#si', '\1', $header);
            } elseif (preg_match('#^bcc:#si', $header)) {
                $bcc = preg_replace('#^bcc:(.*)#si', '\1', $header);
                $header = '';
            }
            $headers .= ($header != '') ? $header . "\r\n" : '';
        }

        $headers = rtrim($headers);
        $cc = explode(', ', $cc);
        $bcc = explode(', ', $bcc);
    }

    if (trim($subject) == '') {
        bb_die('No email subject specified');
    }

    if (trim($message) == '') {
        bb_die('Email message was blank');
    }

    // Ok we have error checked as much as we can to this point let's get on it already
    $ssl = ($bb_cfg['smtp_ssl']) ? 'ssl://' : '';
    if (!$socket = @fsockopen($ssl . $bb_cfg['smtp_host'], $bb_cfg['smtp_port'], $errno, $errstr, 20)) {
        bb_die('Could not connect to smtp host : ' . $errno . ' : ' . $errstr);
    }

    // Wait for reply
    server_parse($socket, "220", __LINE__);

    // Do we want to use AUTH?, send RFC2554 EHLO, else send RFC821 HELO
    // This improved as provided by SirSir to accomodate
    if (!empty($bb_cfg['smtp_username']) && !empty($bb_cfg['smtp_password'])) {
        fwrite($socket, "EHLO " . $bb_cfg['smtp_host'] . "\r\n");
        server_parse($socket, "250", __LINE__);

        fwrite($socket, "AUTH LOGIN\r\n");
        server_parse($socket, "334", __LINE__);

        fwrite($socket, base64_encode($bb_cfg['smtp_username']) . "\r\n");
        server_parse($socket, "334", __LINE__);

        fwrite($socket, base64_encode($bb_cfg['smtp_password']) . "\r\n");
        server_parse($socket, "235", __LINE__);
    } else {
        fwrite($socket, "HELO " . $bb_cfg['smtp_host'] . "\r\n");
        server_parse($socket, "250", __LINE__);
    }

    // From this point onward most server response codes should be 250
    // Specify who the mail is from....
    fwrite($socket, "MAIL FROM: <" . $bb_cfg['board_email'] . ">\r\n");
    server_parse($socket, "250", __LINE__);

    // Add an additional bit of error checking to the To field.
    $mail_to = (trim($mail_to) == '') ? 'Undisclosed-recipients:;' : trim($mail_to);
    if (preg_match('#[^ ]+\@[^ ]+#', $mail_to)) {
        fwrite($socket, "RCPT TO: <$mail_to>\r\n");
        server_parse($socket, "250", __LINE__);
    }

    // Ok now do the CC and BCC fields...
    @reset($bcc);
    while (list(, $bcc_address) = each($bcc)) {
        // Add an additional bit of error checking to bcc header...
        $bcc_address = trim($bcc_address);
        if (preg_match('#[^ ]+\@[^ ]+#', $bcc_address)) {
            fwrite($socket, "RCPT TO: <$bcc_address>\r\n");
            server_parse($socket, "250", __LINE__);
        }
    }

    @reset($cc);
    while (list(, $cc_address) = each($cc)) {
        // Add an additional bit of error checking to cc header
        $cc_address = trim($cc_address);
        if (preg_match('#[^ ]+\@[^ ]+#', $cc_address)) {
            fwrite($socket, "RCPT TO: <$cc_address>\r\n");
            server_parse($socket, "250", __LINE__);
        }
    }

    // Ok now we tell the server we are ready to start sending data
    fwrite($socket, "DATA\r\n");

    // This is the last response code we look for until the end of the message.
    server_parse($socket, "354", __LINE__);

    // Send the Subject Line...
    fwrite($socket, "Subject: $subject\r\n");

    // Now the To Header.
    fwrite($socket, "To: $mail_to\r\n");

    // Now any custom headers....
    fwrite($socket, "$headers\r\n\r\n");

    // Ok now we are ready for the message...
    fwrite($socket, "$message\r\n");

    // Ok the all the ingredients are mixed in let's cook this puppy...
    fwrite($socket, ".\r\n");
    server_parse($socket, "250", __LINE__);

    // Now tell the server we are done and close the socket...
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    return true;
}
