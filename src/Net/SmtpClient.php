<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (https://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Net;

/**
 * SMTP Client
 *
 * This class provides a simple API for communicating with SMTP Servers
 * to send emails. Emails are created from the [FastSitePHP\Net\Email] class
 * and this class has no dependencies other than the [Email] class.
 *
 * This class implements Simple Mail Transfer Protocol as defined in RFC 5321
 * and various extensions including:
 *     AUTH        RFC 2554
 *     STARTTLS    RFC 3207
 *     SMTPUTF8    RFC 6531
 *
 * Supported Verbs:
 *     EHLO    HELO    STARTTLS    AUTH    MAIL    RCPT
 *     VRFY    RSET    NOOP        DATA    HELP    QUIT
 *
 * Supported Auth Methods:
 *     LOGIN    PLAIN
 *
 * @link https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol
 * @link http://www.samlogic.net/articles/smtp-commands-reference.htm
 * @link https://aws.amazon.com/blogs/messaging-and-targeting/debugging-smtp-conversations-part-1-how-to-speak-smtp/
 * @link https://docs.microsoft.com/en-us/exchange/mail-flow/test-smtp-with-telnet?view=exchserver-2019
 * @link https://tools.ietf.org/html/rfc5321
 */
class SmtpClient
{
    const CRLF = "\r\n";

    private $debug_callback = null;
    private $socket = null;
    private $timeout = null;
    private $ehlo_reply = null;
    private $sent_quit = false;

    /**
     * Class Constructor
     * Optionally connect to SMTP Server and define a Debug Callback.
     * This is the recommended method for connecting to an SMTP Server
     * because it automatically handles SMTP EHLO and STARTTLS commands.
     *
     * Defaults to a 5 second timeout. Generally, communication with
     * SMTP Servers is very fast and if a page were to freeze for many
     * seconds a User may be likely to try and refresh it so a quick
     * timeout of 5 seconds is used. The timeout applies both to the
     * initial connection to the server and when reading of each reply.
     *
     * [debug_callback] includes all send and reply text which can include
     * the authentication password and private emails. When used with
     * Authentication or emails it should only be used for debugging purposes.
     *
     * @param string|null $host
     * @param int|null $port
     * @param int $timeout
     * @param \Closure $debug_callback
     * @return void
     * @throws \Exception
     */
    public function __construct($host = null, $port = null, $timeout = 5, \Closure $debug_callback = null)
    {
        $this->debug_callback = $debug_callback;
        if ($host !== null) {
            $this->connect($host, $port, $timeout);
            $this->ehlo();
            if ($port === 587) {
                $this->startTls();
            }
        }
    }

    /**
     * Class Destructor
     * Automatically send a QUIT command to the server
     * and close the socket connection.
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->socket) {
            if (!$this->sent_quit) {
                try {
                    $this->quit();
                } catch (\Exception $e) {
                    // Do nothing
                }
            }
            $this->close();
        }
    }

    /**
     * Static helper function that sends emails using config values defined in
     * either the [app->config] array or as environment variables.
     *
     * For sites that store sensitive information in the environment or special
     * config files using this function prevents the need to hard-code SMTP and
     * Auth values in the source. At a minimum values for [SMTP_HOST] and
     * [SMTP_PORT] are required. [SMTP_TIMEOUT] defaults to 5 seconds if not set;
     * [SMTP_USER] and [SMTP_PASSWORD] are only needed if you SMTP Server
     * requires an Auth User.
     *
     * @param array $emails - Array of [\FastSitePHP\Net\Email] objects
     * @return void
     * @throws \Exception
     */
    public static function sendEmails(array $emails)
    {
        global $app;

        // Get Config Settings
        $config = array(
            'SMTP_HOST' => null,
            'SMTP_PORT' => null,
            'SMTP_TIMEOUT' => 5,
            'SMTP_USER' => null,
            'SMTP_PASSWORD' => null,
        );
        foreach (array_keys($config) as $key) {
            // Get from [app->config] array
            if (isset($app) && isset($app->config[$key])) {
                $config[$key] = $app->config[$key];
                continue;
            }
            // Get from the System's Enviroment Variable. If a project
            // is set to use a [.env] file and a related class then the
            // value would come from here.
            $value = getenv($key);
            if ($value !== false) {
                $config[$key] = $value;
            }
        }

        // Validate that Host and Port are set
        if ($config['SMTP_HOST'] === null || $config['SMTP_PORT'] === null) {
            $error = 'Missing Application Config Values or Environment Variables for [SMTP_HOST] and [SMTP_PORT]. If this error is not clear then please review FastSitePHP documentation and examples on how to use [%s::%s()].';
            $error = sprintf($error, __CLASS__, __METHOD__);
            throw new \Exception($error);
        }

        // Connect and Send Emails
        $smtp = new SmtpClient($config['SMTP_HOST'], (int)$config['SMTP_PORT'], (int)$config['SMTP_TIMEOUT']);
        if ((string)$config['SMTP_USER'] !== '') {
            $smtp->auth($config['SMTP_USER'], $config['SMTP_PASSWORD']);
        }
        foreach ($emails as $email) {
            $smtp->send($email);
        }
        $smtp = null;
    }

    /**
     * Send an email
     *
     * @param \FastSitePHP\Net\Email $email
     * @return void
     * @throws \Exception
     */
    public function send(Email $email)
    {
        // From Address
        $utf8 = $email->allowUnicodeEmails();
        $this->mailFrom($email->from(), $utf8);

        // To, CC, BCC Addresses
        $functions = array('to', 'cc', 'bcc');
        $unqiue_addr = array();
        foreach ($functions as $func) {
            foreach ((array)$email->{$func}() as $addr) {
                if (!in_array($addr, $unqiue_addr, true)) {
                    $unqiue_addr[] = $addr;
                }
            }
        }
        if (count($unqiue_addr) === 0) {
            throw new \Exception('No recipients to send to. Before sending an email make sure to set at least one email address in [to(), cc(), or bcc()] fields.');
        }
        foreach ($unqiue_addr as $addr) {
            $this->rcptTo($addr);
        }

        // Send Email
        // If the message size is larger than the server allows
        // then reset and throw exception.
        $message = $email->message();
        $max_size = $this->size();
        if ($max_size !== -1 && strlen($message) > $max_size) {
            $this->rset();
            $error = 'Error - The email to send is larger than allowed by the server. Message size [%d], allowed max size [%d].';
            $error = sprintf($error, strlen($message), $max_size);
            throw new \Exception($error);
        }
        $this->data($message);
    }

    /**
     * Connect to the SMTP Server, see comments in the Class Constructor
     * because that is the main method of connecting to an SMTP Server.
     *
     * @param string|null $host
     * @param int|null $port
     * @param int $timeout
     * @return void
     * @throws \Exception
     */
    public function connect($host, $port, $timeout = 5)
    {
        $this->close();
        $this->validateLine($host);
        if (!is_int($port)) {
            $error = 'Invalid Port, must be an [int] but received a [%s].';
            $error = sprintf($error, gettype($port));
            throw new \Exception($error);
        }
        $this->timeout = $timeout;
        $this->log('open', $host . ' ' . $port);
        $this->socket = stream_socket_client($host . ':' . $port, $errno, $errstr, $timeout);
        if (!$this->socket) {
            $error = sprintf('Failed to connect to SMTP Host at stream_socket_client(%s, %s)', $errno, $errstr);
            $this->log('error', $error);
            throw new \Exception($error);
        }
        $this->sendCommand(220, null);
    }

    /**
     * Close SMTP Server Connection. If calling this manually
     * then [quit()] should be called first.
     *
     * @return void
     */
    public function close()
    {
        if ($this->socket) {
            $this->log('close', null);
            fclose($this->socket);
            $this->socket = null;
            $this->ehlo_reply = null;
            $this->sent_quit = false;
        }
    }

    /**
     * Send an EHLO (Extended Hello) Command to the SMTP Server
     * and read the reply lines which define supported extensions.
     * This must be called after connecting to the server and also
     * after sending STARTTLS.
     *
     * @param null|string $client Optionally pass a client name to send to the server, by default the FQDN of the current server is used. See the function [fqdn()].
     * @return array Reply lines from the server
     * @throws \Exception
     */
    public function ehlo($client = null)
    {
        if ($client === null) {
            $client = $this->fqdn();
        }
        $this->validateLine($client);
        $this->ehlo_reply = $this->sendCommand(250, 'EHLO ' . $client);
        return $this->ehlo_reply;
    }

    /**
     * Send a HELO (Hello) Command to the SMTP Server.
     * This is typically used by very old servers that
     * do not support EHLO.
     *
     * @param null|string $client See comments in [ehlo()]
     * @return array Reply lines from the server
     * @throws \Exception
     */
    public function helo($client = null)
    {
        if ($client === null) {
            $client = $this->fqdn();
        }
        $this->validateLine($client);
        return $this->sendCommand(250, 'HELO ' . $client);
    }

    /**
     * Return the default FQDN 'fully-qualified domain name' used
     * with Extended HELLO (EHLO) or HELLO (HELO) commands.
     *
     * If the server name can be determined and it's part of a domain
     * then the return value will be in the fromat of [server.example.com].
     * Other return values include the Web Server Host, or Server IP Address.
     *
     * A send/reply example if this function returns [server.example.com]
     * and connects to [smtp.gmail.com] from public IP [54.231.17.108]:
     *   send: EHLO server.example.com
     *   reply: 250-smtp.gmail.com at your service, [54.231.17.108]
     *
     * Per RFC 5321 and 2821, Section [4.1.1.1] a FQDN is sent if
     * available and if not then an IP Address is sent.
     *
     * Per RFC 5321 and 2821, Section [4.1.4] the domain element is
     * used by the server for logging purposes only and it does not
     * decide to route an email based on this value.
     *
     * @link https://www.ietf.org/rfc/rfc2821.txt
     * @link https://tools.ietf.org/html/rfc5321
     * @return string
     */
    public function fqdn()
    {
        // First try to get fqdn from the hostname and a dns lookup.
        // If it works it returns value in format of [server.example.com]
        $host = gethostname();
        $fqdn = '';
        if ($host !== false) {
            $record = dns_get_record($host);
            $fqdn = (isset($record[0]) && isset($record[0]['host']) ? $record[0]['host'] : $host);
        }

        // Next, try the URL Host [www.example.com]
        if (strpos($fqdn, '.') === false && isset($_SERVER['HTTP_HOST'])) {
            $fqdn = $_SERVER['HTTP_HOST'];
        }

        // If still not found then get an IP address for the host machine
        if (strpos($fqdn, '.') === false && $host !== false) {
            $ip_list = gethostbynamel($host);
            if (count($ip_list) > 0) {
                $fqdn = $ip_list[0];
            }
        }

        // Fallback to local IP in case nothing is found. When this happens
        // SMTP will still work as client will send it's local IP and the
        // server will reply the the public IP that it was connected to.
        // Simply sending 'localhost' is a common fallback as well which
        // is what Outlook does.
        if (strpos($fqdn, '.') === false) {
            $fqdn = '127.0.0.1';
        }
        return $fqdn;
    }

    /**
     * Send a STARTTLS (Start Transport Layer Security) Command
     * to the SMTP Server and once returned send a new EHLO Command.
     *
     * This gets called by default when using Port 587.
     *
     * When using PHP 5.6 or greater [TLS 1.2], [TLS 1.1], and [TLS 1.0]
     * are supported and when using PHP 5.5 or below only [TLS 1.0]
     * is supported.
     *
     * @param null|string $client - See comments in [ehlo()]
     * @return array - Reply lines from the server from the EHLO command
     * @return array
     * @throws \Exception
     */
    public function startTls($client = null)
    {
        $this->sendCommand(220, 'STARTTLS');

        $method = '[TLS 1.0]';
        $type = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (PHP_VERSION_ID >= 50600) {
            $method = '[TLS 1.2] or [TLS 1.1] or [TLS 1.0]';
            $type |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $type |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }
        $this->log('crypto', $method);
        $result = stream_socket_enable_crypto($this->socket, true, $type);
        if ($result !== true) {
            $error = 'Failed STARTTLS at stream_socket_enable_crypto()';
            $this->log('error', $error);
            throw new \Exception($error);
        }

        return $this->ehlo($client);
    }

    /**
     * Authenticate using the AUTH Command an a supported Auth method
     * of either [AUTH LOGIN] or [AUTH PLAIN]. The method to call
     * is determined based on the server's response.
     *
     * Supported Auth Methods will come back from the EHLO command, examples:
     *   Gmail:
     *     250-AUTH LOGIN PLAIN XOAUTH2 PLAIN-CLIENTTOKEN OAUTHBEARER XOAUTH
     *   AWS:
     *     reply: 250-AUTH PLAIN LOGIN
     *
     * @param string $user
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function auth($user, $password)
    {
        // Parse the Reply
        $methods = array();
        foreach ($this->ehlo_reply as $line) {
            if (strpos($line, '250-AUTH ') === 0) {
                $methods = explode(' ', trim(str_replace('250-AUTH ', '', $line)));
                break;
            }
        }

        // Use the first matching method
        foreach ($methods as $method) {
            switch ($method) {
                case 'LOGIN':
                    $this->authLogin($user, $password);
                    return;
                case 'PLAIN':
                    $this->authPlain($user, $password);
                    return;
            }
        }

        // Fallback to AUTH LOGIN
        $this->authLogin($user, $password);
    }

    /**
     * Submit AUTH LOGIN credentials to the SMTP Server.
     * This will typically be done over Secure Port 587.
     *
     * @param string $user
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function authLogin($user, $password)
    {
        $this->validateLine($user);
        $this->validateLine($password);
        $this->sendCommand(334, 'AUTH LOGIN');
        $this->sendCommand(334, base64_encode($user));
        $this->sendCommand(235, base64_encode($password));
    }

    /**
     * Submit AUTH PLAIN credentials to the SMTP Server.
     * This will typically be done over Secure Port 587.
     *
     * @param string $user
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function authPlain($user, $password)
    {
        $this->validateLine($user);
        $this->validateLine($password);
        $this->sendCommand(334, 'AUTH PLAIN');
        $this->sendCommand(235, base64_encode(chr(0) . $user . chr(0) . $password));
    }

    /**
     * Send the MAIL FROM Command to the SMTP Server and optionally
     * include SMTPUTF8 if Unicode Emails Addresses are needed.
     * This gets called once for the [From] Email Address.
     * This is handled automatically from the [send()] function.
     *
     * @param string $email
     * @param bool $utf8 - Defaults to false
     * @return void
     * @throws \Exception
     */
    public function mailFrom($email, $utf8 = false)
    {
        $this->validateLine($email);
        $cmd = 'MAIL FROM: <' . $email . '>';
        if ($utf8 && $this->supports('SMTPUTF8')) {
            $cmd .= ' SMTPUTF8';
        }
        $this->sendCommand(250, $cmd);
    }

    /**
     * Send the RCPT TO Command to the SMTP Server. This gets
     * called for each person the email is being sent to.
     * This is handled automatically from the [send()] function.
     *
     * @param string $email
     * @return void
     * @throws \Exception
     */
    public function rcptTo($email)
    {
        $this->validateLine($email);
        $accepted_codes = array(250, 251);
        $this->sendCommand($accepted_codes, 'RCPT TO: <' . $email . '>');
    }

    /**
     * Send a VRFY (Verify) Command with an Email Address to the Server.
     * This is an older SMTP Command that is usually ignored by servers
     * since spammers could use it to check for existance of an email.
     *
     * @param string $email
     * @return array - Reply lines from the server
     * @throws \Exception
     */
    public function vrfy($email)
    {
        $this->validateLine($email);
        $accepted_codes = array(250, 251, 252);
        return $this->sendCommand($accepted_codes, 'VRFY ' . $email);
    }

    /**
     * Send a RSET (Reset) Command to the SMTP Server.
     * This would be used to cancel an message.
     *
     * @return void
     * @throws \Exception
     */
    public function rset()
    {
        $this->sendCommand(250, 'RSET');
    }

    /**
     * Send a NOOP (No operation) Command to the SMTP Server.
     * This can be used to verify if the connection is ok.
     *
     * @return void
     * @throws \Exception
     */
    public function noop()
    {
        $this->sendCommand(250, 'NOOP');
    }

    /**
     * Send email message using the DATA command.
     * This is handled automatically from the [send()] function.
     *
     * @param string $data
     * @return void
     * @throws \Exception
     */
    public function data($data)
    {
        $this->sendCommand(354, 'DATA');
        $this->sendCommand(250, $data . self::CRLF. '.');
    }

    /**
     * Send the HELP Command to the SMTP Server and return the rely lines.
     *
     * Example return values:
     *   From Exchange:
     *     214-This server supports the following commands:
     *     214 HELO EHLO STARTTLS RCPT DATA RSET MAIL QUIT HELP AUTH BDAT
     *   From Gmail:
     *     214 2.0.0  https://www.google.com/search?btnI&q=RFC+5321 {{random_id}} - gsmtp
     *
     * @return array
     * @throws \Exception
     */
    public function help()
    {
        return $this->sendCommand(214, 'HELP');
    }

    /**
     * Send a QUIT Command to the SMTP Server.
     * This gets call automatically when the object instance is destroyed.
     *
     * @return void
     * @throws \Exception
     */
    public function quit()
    {
        $this->sendCommand(221, 'QUIT');
        $this->sent_quit = true;
    }

    /**
     * Return true/false if an extension is supported based on the
     * Server's response from the EHLO command.
     *
     * This does not handle the size attribute, instead use the [size()]
     * function.
     *
     * Example:
     *   $smtp->supports('SMTPUTF8')
     * Returns [true]
     *   if the EHLO response contains either '250-SMTPUTF8' or '250 SMTPUTF8'
     *
     * @param string $extension
     * @return bool
     */
    public function supports($extension)
    {
        foreach ($this->ehlo_reply as $line) {
            $line = trim($line);
            if ($line === '250-' . $extension || $line === '250 ' . $extension) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the maximum allowed message size in bytes based on the
     * Server's response from the EHLO command.
     *
     * Returns -1 if [SIZE] was not specified from the server
     *
     * @return int
     */
    public function size()
    {
        foreach ($this->ehlo_reply as $line) {
            if (strpos($line, '250-SIZE ') === 0) {
                $values = explode(' ', trim($line));
                return (int)$values[1];
            }
        }
        return -1;
    }

    /**
     * Log to callback if one was defined when the class was created
     *
     * @param string $type
     * @param string|null $message
     * @return void
     */
    private function log($type, $message)
    {
        if ($this->debug_callback !== null) {
            $message = ($message === null ? $type : $type . ': ' . $message);
            call_user_func($this->debug_callback, $message);
        }
    }

    /**
     * Used to validate that data send with SMTP Commands does not contain line breaks.
     * Per RFC 5321 SMTP client implementations MUST NOT transmit line breaks except
     * when they are they are intended as line terminators using a <CRLF> sequence.
     * Null (character 0) is also checked.
     *
     * @param string $line
     * @return void
     * @throws \Exception
     */
    private function validateLine($line)
    {
        if (strpos($line, "\r") !== false
            || strpos($line, "\n") !== false
            || strpos($line, chr(0)) !== false
        ) {
            throw new \Exception('Error - Data to send to SMTP Server cannot contain new lines or null characters.');
        }
    }

    /**
     * Send a Command to the Server and read the rely lines
     *
     * @param int|array $expect_code - Required Matching Reply Codes
     * @param string|null $cmd - Command to Send to Server, [null] used after the initial connection.
     * @return array
     * @throws \Exception
     */
    private function sendCommand($expect_code, $cmd)
    {
        // Make sure Socket is valid
        if (!is_resource($this->socket)) {
            $error = sprintf('SMTP Host is not connected to. Make sure [%s->connect()] is successful', __CLASS__);
            $this->log('error', $error);
            throw new \Exception($error);
        }

        // Send Command
        if ($cmd !== null) {
            $this->log('send', $cmd);
            fwrite($this->socket, $cmd . self::CRLF);
        }

        // Timeout has to be set and checked on each call in order to work properly.
        // First call [stream_set_timeout()] which applies to [stream_get_meta_data()].
        // [stream_select()] is also used otherwise it's possible for client code to
        // hang in the event of unexpected errors. See docs:
        //   http://php.net/manual/en/function.stream-set-timeout.php
        //     A comment on the above page shows similar code for SMTP to what is used here.
        //   http://php.net/manual/en/function.stream-select.php
        //     A comment in the above page says using [stream_select()] should not be
        //     used with blocking streams as is done here however this conflicts with
        //     other comments for [stream_set_time()] and a review of the php source
        //     code appears to that [stream_select()] is valid for blocking streams.
        //   http://php.net/manual/en/function.stream-get-meta-data.php
        // Related PHP Source Code at:
        //    https://github.com/php/php-src/blob/master/ext/standard/streamsfuncs.c
        //    https://github.com/php/php-src/blob/master/main/streams/streams.c
        //    https://github.com/php/php-src/blob/master/ext/standard/file.c
        // Internally PHP uses a [select()] system call when using [stream_select()]
        // for Unix and Linux Systems:
        //    https://en.wikipedia.org/wiki/Select_(Unix)
        //    http://man7.org/linux/man-pages/man2/select.2.html
        // And for Windows [stream_select()] uses this code:
        //    https://github.com/php/php-src/blob/master/win32/select.c
        stream_set_timeout($this->socket, $this->timeout);
        $read = array($this->socket);
        $write = $except = null;

        // Read the Reply
        // This will loop and read all reply lines for the command.
        $reply_lines = array();
        $reply = '';
        $reply_code = 0;
        while (is_resource($this->socket) && !feof($this->socket)) {
            // Read the full response line which ends with CRLF. Check for a timeout
            // before and after each call to [fgets()]. It's likely lines will always
            // be less than 256 chars in which case this loop will only run once.
            $reply = '';
            while (substr($reply, -2) !== self::CRLF) {
                // Wait for activity from the socket while using a timeout
                $result = stream_select($read, $write, $except, $this->timeout);
                if ($result === false || $result === 0) {
                    if ($result === false) {
                        $error = 'Failed to read data from SMTP Server. Read failed at [stream_select()]. The connection may have been dropped.';
                    } else {
                        $error = 'Failed with Timeout before reading data from STMP Server at [stream_select()].';
                    }
                    $this->log('error', $error);
                    throw new \Exception($error);
                }

                // Read from Socket
                $data = fgets($this->socket, 256);
                if ($data === false) {
                    $error = 'Failed reading socket value at [fgets()]';
                    $this->log('error', $error);
                    throw new \Exception($error);
                }

                // Check for timeout after each read
                $info = stream_get_meta_data($this->socket);
                if (isset($info['timed_out']) && $info['timed_out'] === true) {
                    $error = 'Failed with Timeout after reading data from STMP Server at [stream_get_meta_data()].';
                    $this->log('error', $error);
                    throw new \Exception($error);
                }

                // Append
                $reply .= $data;
            }
            $reply_lines[] = $reply;

            // Log Response Line
            $this->log('reply', $reply);

            // Once the reply code is found then break the loop as it will
            // the last line in the reply. Reply lines uses a minus '-' at
            // the 4th character to continue and a space ' ' to end.
            // See Page 36 of RFC 959 (https://www.ietf.org/rfc/rfc959.txt).
            //
            //   Example:
            //     '250-SIZE 157286400' - Keep reading
            //     '250 SMTPUTF8'       - Reply Code found
            if (substr($reply, 3, 1) === ' ') {
                $reply_code = (int)substr($reply, 0, 3);
                break;
            }
        }

        // Did the reply match the expected code?
        // Note - reply responses are acceptable to show in the error message however
        // the command sent to the server is not as it might contain auth info.
        if (is_int($expect_code)) {
            $valid = ($reply_code === $expect_code);
        } else {
            $valid = in_array($reply_code, $expect_code, true);
        }
        if (!$valid) {
            $error = 'Reply faild with wrong reply code.';
            $error .= self::CRLF . 'Expected: [' . implode(', ', (array)$expect_code) . '] but received [' . $reply_code . '].';
            $error .= self::CRLF . 'See documentation on [debug_callback] to obtain detailed debug info.';
            $error .= self::CRLF . 'Last reply from Server: ' . $reply;
            $this->log('error', $error);
            throw new \Exception($error);
        }
        return $reply_lines;
    }
}
