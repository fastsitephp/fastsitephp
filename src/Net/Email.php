<?php
/**
 * Copyright 2019 Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Net;

/**
 * Email Message API
 *
 * This classes is used for sending emails along with the [SmtpClient] class.
 * See docs in [SmtpClient] for more.
 *
 * In general this classes provides getter/setter functions for common email
 * fields and the format of email addresses and header fields are validated
 * when set. Unicode Email Addresses are supported when [allowUnicodeEmails(true)]
 * is called.
 *
 * @link https://en.wikipedia.org/wiki/MIME
 * @link https://en.wikipedia.org/wiki/Email_address
 */
class Email
{
    const CRLF = "\r\n";

    private $from = null;
    private $reply_to = null;
    private $to = array();
    private $cc = array();
    private $bcc = array();
    private $subject = '';
    private $body = '';
    private $is_html = true;
    private $priority = null;
    private $files = array();
    private $headers = array();
    private $allow_unicode_emails = false;
    private $encode_file_names = false;
    private $safe_header_names = true;

    /**
     * Class Constructor
     * Key email fields can be defined when an object is created
     *
     * @param string|null $from
     * @param string|null $to
     * @param string|null $subject
     * @param string|null $body
     */
    function __construct($from = null, $to = null, $subject = null, $body = null)
    {
        if ($from !== null) {
            $this->from($from);
        }
        if ($to !== null) {
            $this->to($to);
        }
        if ($subject !== null) {
            $this->subject($subject);
        }
        if ($body !== null) {
            $this->body($body);
        }
    }

    /**
     * Get or set the [From] Email Address, this function accepts a
     * string with an Email address or an array with ['Email', 'Name'].
     *
     * @param null|string|array $new_value
     * @return null|string|$this
     * @throws \Exception
     */
    public function from($new_value = null)
    {
        return $this->emailAddr('from', $new_value);
    }

    /**
     * Get or set the [Reply-To] Email Address, this function accepts a
     * string with an Email address or an array with ['Email', 'Name'].
     *
     * [Reply-To] does not show by default when viewing an Email, however
     * if a user clicks [Reply] then it appears. This is useful if you want
     * to send the email from a no-reply email but still allow a user
     * to reply.
     *
     * @param null|string|array $new_value
     * @return null|string|$this
     * @throws \Exception
     */
    public function replyTo($new_value = null)
    {
        return $this->emailAddr('reply_to', $new_value);
    }

    /**
     * Get or set [To] Email Addresses. This function can
     * set one or many email addresses at the same time.
     *
     * Accepted options:
     *     Null - Returns a list of Email Address Strings to Send to.
     *     Array of [Email Address Strings]
     *     'email address'
     *     Array with ['Email', 'Name']
     *
     * @param null|array|string $new_value
     * @return array|$this
     * @throws \Exception
     */
    public function to($new_value = null)
    {
        return $this->emailAddrList('to', $new_value);
    }

    /**
     * Get or set [CC] Email Addresses. This function
     * uses the same format as the [to()] function.
     *
     * @param null|array|string $new_value
     * @return array|$this
     * @throws \Exception
     */
    public function cc($new_value = null)
    {
        return $this->emailAddrList('cc', $new_value);
    }

    /**
     * Get or set [BCC] Email Addresses. This function
     * uses the same format as the [to()] function.
     *
     * @param null|array|string $new_value
     * @return array|$this
     * @throws \Exception
     */
    public function bcc($new_value = null)
    {
        return $this->emailAddrList('bcc', $new_value);
    }

    /**
     * Get or set the Subject of the email.
     *
     * @param null|string $new_value
     * @return null|string|$this
     */
    public function subject($new_value = null)
    {
        // When setting a new value replace any line breaks with spaces. This is done
        // because the calling code could include user or system suppied input with
        // line breaks. For example an automated error email might include part of an
        // error message in the subject.
        if ($new_value !== null) {
            $new_value = trim(str_replace(array("\r", "\n"), ' ', $new_value));
        }
        return $this->field('subject', 'string', $new_value);
    }

    /**
     * Get or set the Body of the email.
     *
     * @param null|string $new_value
     * @return null|string|$this
     * @throws \Exception
     */
    public function body($new_value = null)
    {
        return $this->field('body', 'string', $new_value);
    }

    /**
     * Get or set the type of email to send:
     *     HTML = true (Default)
     *     Text = false
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function isHtml($new_value = null)
    {
        return $this->field('is_html', 'bool', $new_value);
    }

    /**
     * Sends both [X-Priority] and [Importance] headers. When setting
     * specify one of ['High', 'Normal', or 'Low'].
     *
     * @param null|string $new_value
     * @return null|string|$this
     * @throws \Exception
     */
    public function priority($new_value = null)
    {
        if ($new_value !== null && !in_array($new_value, array('High', 'Normal', 'Low'), true)) {
            $error = 'Error from [%s->%s()]. When setting email priority/importance you must use one of the following values: [High, Normal, Low]. Received: [%s]';
            if (!is_string($new_value)) {
                $new_value = 'Type: ' . gettype($new_value);
            }
            $error = sprintf($error, __CLASS__, __FUNCTION__, gettype($new_value));
            throw new \Exception($error);
        }
        return $this->field('priority', 'string', $new_value);
    }

    /**
     * Get or set a Custom Email Header
     *
     * @link https://www.iana.org/assignments/message-headers/message-headers.xhtml
     * @param string $name
     * @param null|string $new_value
     * @return $this|null|string
     * @throws \Exception
     */
    public function header($name, $new_value = null)
    {
        // Get
        if ($new_value === null) {
            return (isset($this->headers[$name]) ? $this->headers[$name] : null);
        }

        // Validate (New Lines and Null Characters)
        $this->validateLine($name);
        $this->validateLine($new_value);

        // By default only [A-Z], [a-z], [0-9], and [-]
        // are allowed for the Header Field Names.
        if ($this->safe_header_names) {
            $pattern = '/^[\-0-9A-Za-z]+$/i';
            if (preg_match($pattern, $name) !== 1) {
                $error = 'Error - Custom Email Header fields names can only contain the following characters [A-Z], [a-z], [0-9], and [-]. Please verify that your code is calling this function correctly. If you need to use additional characters then set [safeHeaderNames(false)] prior to calling this function and make sure your code does not allow user-entered field names.';
                throw new \Exception($error);
            }
        } else {
            if (strpos($name, ':') !== false) {
                $error = 'Error - Custom Email Header fields names cannot contain the [:] character even when using [safeHeaderNames(false)]. This is due to the fact that [:] is the "name: value" separator. Please review your code.';
                throw new \Exception($error);
            }
        }

        // Set
        $this->headers[$name] = (string)$new_value;
        return $this;
    }

    /**
     * By default only characters [A-Z], [a-z], [0-9], and [-] are allowed for
     * Custom Header Field Names when using the [header()] function. Setting
     * this value to false will allow any character other than
     * New-Lines (CR, LF), Null (char 0), and [:] to be used.
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function safeHeaderNames($new_value = null)
    {
        return $this->field('safe_header_names', 'bool', $new_value);
    }

    /**
     * Add a file attachment to the email.
     *
     * IMPORTANT - File paths should generally not be passed as user parameters
     * because a user could specify files other than the intended file. If
     * an App does need to allow the user to specify a file then the code
     * should be carefully reviewed and tested for security.
     *
     * Typical usage of this feature would be having a script generate a
     * report and then the report gets attached to an email sent to users
     * on an automated schedule.
     *
     * @param string $file_path - Full Path of a File that exists
     * @return $this
     * @throws \Exception
     */
    public function attachFile($file_path)
    {
        if (!is_file($file_path)) {
            $error = 'Unable to attach file [%s] to email. Verify that the file exists and that the web user has permissions to access it.';
            $error = sprintf($error, $file_path);
            throw new \Exception($error);
        }
        $this->files[] = $file_path;
        return $this;
    }

    /**
     * Set to true to allow Unicode Emails to be sent. When the [SmtpClient]
     * Class sends the email it checks this and if set to [true] sends the
     * option SMTPUTF8 if supported by the SMTP Server.
     *
     * Setting this value is only required if using Unicode Email Addresses
     * and it is not required for sending content with Unicode characters
     * (Subject, Body, Attachement File Names, Headers, etc).
     *
     * This function defaults to false so that email address validation
     * uses strict rules.
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function allowUnicodeEmails($new_value = null)
    {
        return $this->field('allow_unicode_emails', 'bool', $new_value);
    }

    /**
     * Get or set whether attached file names should be UTF-8 encoded.
     * Defaults to false.
     *
     * If set to true then the following MIME Header:
     *   "Content-Disposition: attachment; filename*=UTF-8''{encoded_name}"
     * is included in the email message. For modern SMTP Servers and
     * widely used email providers this is generally not needed even
     * when the file name includes Unicode Characters.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
     * @link https://tools.ietf.org/html/rfc5987
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function encodeFileNames($new_value = null)
    {
        return $this->field('encode_file_names', 'bool', $new_value);
    }

    /**
     * Return the Email message as a String encoded as UTF-8 using Base64
     * for the [SmtpClient] to send with the DATA command. This function
     * is public but generally only needs to be called internally by the
     * [SmtpClient] Class.
     *
     * @return string
     */
    public function message()
    {
        // Start building message with Date and Subject Headers
        $message = 'Date: ' . date(DATE_RFC2822) . self::CRLF;
        $message .= 'Subject: ' . $this->encodeHeader($this->subject) . self::CRLF;

        // Add Email Addresses
        // Note - Bcc Addresses are not included as an email header
        // but rather sent using [RCPT TO] commands via SMTP.
        $message .= 'To: ' . $this->buildAddressList($this->to) . self::CRLF;
        if (count($this->cc) > 0) {
            $message .= 'Cc: ' . $this->buildAddressList($this->cc) . self::CRLF;
        }
        $message .= 'From: ' . $this->buildAddressList(array($this->from)) . self::CRLF;
        if ($this->reply_to !== null) {
            $message .= 'Reply-To: ' . $this->buildAddressList(array($this->reply_to)) . self::CRLF;
        }

        // Add other fields
        $message .= 'MIME-Version: 1.0' . self::CRLF;
        if ($this->priority !== null) {
            $message .= 'Importance: ' . $this->priority . self::CRLF;
            $priorities = array(
                'High' => '5',
                'Normal' => '3',
                'Low' => '1',
            );
            $message .= 'X-Priority: ' . $priorities[$this->priority] . self::CRLF;
        }

        // Custom Headers
        foreach ($this->headers as $key => $value) {
            $message .= $key . ': ' . $this->encodeHeader($value) . self::CRLF;
        }

        // Define multipart and boundary if files are attached
        if (count($this->files) > 0) {
            // Content is always base64 encoded here so using a simple boundary of [----NEXT_PART----] always works
            $message .= 'Content-Type: multipart/alternative; boundary=--NEXT_PART----' . self::CRLF . self::CRLF;
            $message .= 'This is a message with multiple parts in MIME format.' . self::CRLF;
            $message .= '----NEXT_PART----' . self::CRLF;
        }

        // Attach body as either HTML or Text. SMTP Servers and clients typically allow many
        // different encoding rules, however it leads to a lot of complexity and complex regex.
        // For simplicity, security, and reliability this class always uses base64 encoding for the body.
        $message .= sprintf('Content-Type: %s; charset=utf-8', ($this->is_html ? 'text/html' : 'text/plain')) . self::CRLF;
        $message .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
        $message .= chunk_split(base64_encode($this->body));

        // Attach files
        // Note - By default the name of the file is used and the encoded [filename*]
        // parameter is optional. Simply using the file name has been confirmed to
        // work with widely used providers (Gmail, Office365, etc) for both long file
        // names (100+ characters) and when using unicode characters.
        if (count($this->files) > 0) {
            foreach ($this->files as $file) {
                // Get the file name and replace any double-quotes.
                // Note - [basename()] is not used because it doesn't always
                // work in some environments (often Linux or Unix) for Unicode
                // Characters unless calling [setlocale()]. Since the Locale
                // is not known this method is more reliable.
                //   $file_name = str_replace('"', '', basename($file));
                $data = explode(DIRECTORY_SEPARATOR, realpath($file));
                $file_name = $data[count($data)-1];

                // Add to File Contents to the Message
                $message .= '----NEXT_PART----' . self::CRLF;
                $message .= 'Content-Type: application/octet-stream;' . self::CRLF;
                $message .= 'Content-Disposition: attachment; filename="' . $file_name . '"' . self::CRLF;
                if ($this->encode_file_names) {
                    $message .= "Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($file_name) . self::CRLF;
                }
                $message .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
                $message .= chunk_split(base64_encode(file_get_contents($file)));
            }
            $message .= '----NEXT_PART----';
        }
        return $message;
    }

    /**
     * Encode a header value (Subject or Custom Header) when building the email.
     *
     * @param string $value
     * @return string
     */
    private function encodeHeader($value)
    {
        // There are many different encoding header options allowed and defined standards,
        // this makes supporting a wide range of values complex. Traditionally header fields
        // have been a source of security issues such as [Email Header Injection] due to the
        // complexity. Often the security issues of most concern (Remote Code Execution - RCE)
        // have been related to email headers with php [mail()] function or [sendmail]
        // program calls; those issues are avoided entirely by this class and the SmtpClient
        // because only SMTP with sockets is used.
        //
        // This class takes the approach to leave the header alone if it appears safe and short
        // otherwise safely encode it as UTF8/Base-64. Because data is validated for line breaks
        // and null characters prior to this call it's not possible to inject additional headers,
        // however a malicious user may try to send escape sequences which are handled by this code.
        //
        // All modern email SMTP servers can handle Unicode UTF8 values in header fields which
        // is why encoding is not needed for most characters. This makes viewing the header from
        // email clients much easier for admins or end users.
        //
        // If performed the value will be encode to chunked base64 with a 'folding white space'.
        // Based on RFC 2822, Section 2.2.3 'Long Header Fields':
        // http://www.faqs.org/rfcs/rfc2822.html
        //
        // A 'folding white space' starts any line after the first
        // line break with a space:
        //   |Subject: This
        //   | is a test
        $encode = strlen($value) > 76;
        $encode |= (strpos($value, '\\') !== false); // Escape Character for Mime fields
        $encode |= (strpos($value, '"') !== false);  // Quotes have to be escaped for Mime
        $encode |= (strpos($value, '%') !== false);  // Possible URL Encoding
        $encode |= (strpos($value, '&') !== false);  // Possible HTML Encoding
        $encode |= (strpos($value, 'U+') !== false); // Possible HTML Encoding
        if ($encode) {
            $value = trim(chunk_split(base64_encode($value), 76, "\r\n "));
            return '=?utf-8?b?' . $value . '?=';
        }
        return $value;
    }

    /**
     * Internal function used to return a list of email addresss
     * formatted for an Email Message
     *
     * @param string|array $addresses
     * @return string
     */
    private function buildAddressList($addresses)
    {
        $list = array();
        foreach ($addresses as $addr) {
            if (is_string($addr)) {
                $list[] = sprintf('<%s>', $addr);
            } else {
                // Normalize the ["] character to [\"] for escape sequence.
                // If [\] is used to escape any other characters then simply
                // remove it and let the SMTP Server handle the name. If it's
                // used for anything other that quotes then it's may be a
                // user input and an attempted attack.
                $name = str_replace('\\', '', $addr[1]); // First remove [\] characters
                $name = str_replace('"', '\\"', $name); // Then replace ["] with [\"]
                $list[] = sprintf('"%s" <%s>', $name, $addr[0]);
            }
        }
        return implode(', ', $list);
    }

    /**
     * Internal function used to get or set a single Email address field.
     * Used for [From] and [Reply-To] headers.
     *
     * @param string $prop
     * @param null|string|array $new_value
     * @return null|string|$this
     * @throws \Exception
     */
    private function emailAddr($prop, $new_value)
    {
        // Get
        if ($new_value === null) {
            if (is_string($this->{$prop})) {
                return $this->{$prop};
            } elseif (is_array($this->{$prop})) {
                return $this->{$prop}[0];
            }
            return null;
        }

        // Set using one of the accepted formats:
        //   array('email@example.com', 'Name')
        //   'email@example.com'
        $is_valid = (is_array($new_value) && $this->isEmailAndName($new_value));
        $is_valid |= (is_string($new_value) && $this->isEmailAddr($new_value));
        if (!$is_valid) {
            throw new \Exception('Invalid From Email, expected an Email Address as a string, or array in the format of ["Email", "Name"].');
        }
        $this->{$prop} = $new_value;
        return $this;
    }

    /**
     * Internal function used to get or set Email address(es) for
     * a multiple email address field [To, CC, BCC].
     *
     * @param string $prop
     * @param null|array|string $new_value
     * @return array|$this
     * @throws \Exception
     */
    private function emailAddrList($prop, $new_value)
    {
        // Get
        if ($new_value === null) {
            // Return only the email addresses excluding the names
            $list = array();
            foreach ($this->{$prop} as $addr) {
                if (is_string($addr)) {
                    $list[] = $addr;
                } else {
                    $list[] = $addr[0];
                }
            }
            return $list;
        }

        // Set using one of the accepted formats:
        //   array('email1@example.com', 'email2,example.com', ...)
        //   array('email@example.com', 'Name')
        //   'email@example.com'
        $is_array = is_array($new_value);
        if ($is_array && $this->isArrayOfEmails($new_value)) {
            foreach ($new_value as $item) {
                $this->{$prop}[] = $item;
            }
            return $this;
        }
        $is_valid = ($is_array && $this->isEmailAndName($new_value));
        $is_valid |= (is_string($new_value) && $this->isEmailAddr($new_value));
        if (!$is_valid) {
            throw new \Exception('Invalid Email, expected an Email Address as a string, an array of Emails Addresses, or array in the format of ["Email", "Name"].');
        }
        $this->{$prop}[] = $new_value;
        return $this;
    }

    /**
     * Return true if an email if valid. By default this uses PHP built-in
     * [filter_var()] function which is based on RFC 5321 (PHP docs state
     * that emails are validated for RFC 822) however they are out-dated
     * based on PHP Source (link below).
     *
     * https://github.com/php/php-src/blob/master/ext/filter/logical_filters.c
     *   php_filter_validate_email()
     *
     * When [allowUnicodeEmails(true)] is set then this function does minimal
     * string and regex email validation to check if the value looks like an
     * email. When this is set it's up to the SMTP Server to validate and
     * accept the email.
     *
     * @param string $email
     * @return bool
     * @throws \Exception
     */
    private function isEmailAddr($email)
    {
        if ($this->allow_unicode_emails) {
            $pattern = '/.+@.+\..+/';
            return (
                strpos($email, ' ') === false
                && strpos($email, '<') === false
                && strpos($email, '>') === false
                && strpos($email, "\r") === false
                && strpos($email, "\n") === false
                && strpos($email, "'") === false
                && strpos($email, '"') === false
                && strpos($email, chr(0)) === false
                && preg_match($pattern, $email) === 1 ? true : false
            );
        } else {
            return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? true : false);
        }
    }

    /**
     * Return true if an array contains only email addresses.
     *
     * @param array $list
     * @return bool
     */
    private function isArrayOfEmails(array $list)
    {
        foreach ($list as $item) {
            if (!(is_string($item) && $this->isEmailAddr($item))) {
                return false;
            }
        }
        return (count($list) > 0 ? true : false);
    }

    /**
     * Return true if an array looks like it's in the format of [email, name].
     *
     * @param array $data
     * @return bool
     */
    private function isEmailAndName(array $data)
    {
        if (count($data) === 2
            && $this->isEmailAddr($data[0])
            && is_string($data[1])
            && !$this->isEmailAddr($data[1])
        ) {
            $this->validateLine($data[1]);
            return true;
        }
        return false;
    }

    /**
     * Get or set a field (object property)
     *
     * @param string $prop
     * @param string $type
     * @param string|null $new_value
     * @return string|null|$this
     */
    private function field($prop, $type, $new_value)
    {
        if ($new_value === null) {
            return $this->{$prop};
        }
        $this->{$prop} = ($type === 'string' ? (string)$new_value : (bool)$new_value);
        return $this;
    }

    /**
     * Make sure that a text line does not contain any line breaks or null characters.
     *
     * @param string $line
     * @throws \Exception
     */
    private function validateLine($line)
    {
        if (strpos($line, "\r") !== false
            || strpos($line, "\n") !== false
            || strpos($line, chr(0)) !== false
        ) {
            throw new \Exception('Error - Data for Email field cannot contain new lines or null characters.');
        }
    }
}