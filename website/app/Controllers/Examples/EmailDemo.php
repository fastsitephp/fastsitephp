<?php

namespace App\Controllers\Examples;

use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;
use FastSitePHP\Web\Request;
use FastSitePHP\Net\Email;
use FastSitePHP\Net\SmtpClient;

class EmailDemo
{
    /**
     * Return HTML for the Web Page
     */
    public function get(Application $app, $lang)
    {
        // Load Language File
        I18N::langFile('email-demo', $lang);

        // Add Code Examples from text files
        $file_path = $app->config['I18N_DIR'] . '/code/email-demo-smtp.{lang}.txt';
        $app->locals['i18n']['smtp_code'] = I18N::textFile($file_path, $app->lang);

        $file_path = $app->config['I18N_DIR'] . '/code/email-demo-code.{lang}.txt';
        $app->locals['i18n']['code'] = I18N::textFile($file_path, $app->lang);

        // Sending emails is only allowed if the user is local.
        // A variable is sent to JavaScript/Template to disable sending
        // emails from the UI, however the main validation happens on the server
        // in [sendEmail()] below in case a user were to manipulate the UI
        // with Dev Tools or call the service manually.
        $req = new Request();
        $is_local = $req->isLocal();

        // Render the View
        $templates = [
            'js-tabs.htm',
            'old-browser-warning.htm',
            'loading.htm',
            'examples/email-demo.php',
            'examples/email-demo.htm',
        ];
        return $app->render($templates, [
            'nav_active_link' => 'examples',
            'is_local' => $is_local,
        ]);
    }

    /**
     * Sent an email using SMTP and return the result.
     * This service only runs on a local computer.
     * Download FastSitePHP to run it.
     */
    public function sendEmail(Application $app)
    {
        // Log Communication with Server
        // NOTE - the [&$reply_lines] means the variable is being
        // passed by reference so it can be modified.
        $reply_lines = [];
        $debug_callback = function($message) use (&$reply_lines) {
            $reply_lines[] = '[' . date('H:i:s') . '] ' . trim($message);
        };

        // For Security this service is only allowed to run on localhost.
        // Both user and server have to be [127.0.0.1 / ::1] which means
        // an admin user with control over the computer is running it.
        $req = new Request();
        if (!$req->isLocal()) {
            throw new \Exception('Error - This service only runs on a local computer. Download the source code and run from your computer or modify for your needs.');
        }

        // Read values from the JSON Post
        $data = $req->content();
        $host = $data['host'];
        $port = (int)$data['port'];
        $timeout = (int)$data['timeout'];
        $user = $data['user'];
        $password = $data['password'];
        $from = $data['from'];
        $to = $data['to'];
        $subject = $data['subject'];
        $body = $data['body'];

        // Build and Send the Email
        $smtp = null;
        $success = false;
        $error = null;
        try {
            $email = new Email($from, $to, $subject, $body);
            $smtp = new SmtpClient($host, $port, $timeout, $debug_callback);
            if ($user) {
                $smtp->auth($user, $password);
            }
            $smtp->send($email);
            $smtp = null; // Automatically calls [$smtp->quit()] and [$smtp->close()]
            $success = true;
        } catch (\Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
        $smtp = null; // In case of error

        // Return the Result and Reply Log
        return [
            'success' => $success,
            'error' => $error,
            'replyLines' => $reply_lines,
        ];
    }

    /**
     * Connect to and run basic commands against an SMTP Server.
     * No email is sent. This service is allowed by public users.
     */
    public function smtpServer(Application $app)
    {
        // See comments in above [sendEmail()] function.
        // This default to 5 second timeout.
        $timeout = 5;
        $reply_lines = [];
        $debug_callback = function($message) use (&$reply_lines) {
            $reply_lines[] = '[' . date('H:i:s') . '] ' . trim($message);
        };

        // Read Host and Port from user selection.
        // Only several well-known and widely uses SMTP Services are allowed.
        $req = new Request();
        $server = $req->queryString('server');
        switch ($server) {
            case 'gmail':
                $host = 'smtp.gmail.com';
                $port = 587;
                break;
            case 'live':
                $host = 'smtp.live.com';
                $port = 587;
                // Increase the Timeout because the [HELP] command takes a long time (~5 seconds).
                // This is likely a SMTP Transaction Delays to prevent spam, see info at:
                // https://www.tldp.org/HOWTO/Spam-Filtering-for-MX/smtpdelays.html
                $timeout = 10;
                break;
            case 'aws':
                $host = 'email-smtp.us-east-1.amazonaws.com';
                $port = 587;
                break;
            default:
                throw new \Exception('Error - Invalid Server Specified. To test with a different server, download and modify this code.');
        }

        // Connect with an SMTP Server and submit several verbs (commands)
        // and then close the connection (No email sent).
        $smtp = null;
        $success = false;
        $error = null;
        try {
            $smtp = new SmtpClient($host, $port, $timeout, $debug_callback);
            $smtp->help();
            $smtp->noop();
            $smtp->quit();
            $smtp->close();
            $success = true;
        } catch (\Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
        $smtp = null;

        // Return the Result and Reply Log
        return [
            'success' => $success,
            'error' => $error,
            'replyLines' => $reply_lines,
        ];
    }
}
