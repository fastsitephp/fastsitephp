<?php
// -----------------------------------------------------------
// Unit Testing Page
// *) This file uses only core Framework files
//     and classes required for sending emails
// -----------------------------------------------------------

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under 
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    $smtp_file = '../../vendor/fastsitephp/src/Net/SmtpClient.php';
    $email_file = '../../vendor/fastsitephp/src/Net/Email.php'; 
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require $smtp_file;
    require $email_file;
} else {
    $smtp_file = '../src/Net/SmtpClient.php';
    $email_file = '../src/Net/Email.php'; 
    require '../src/Application.php';
    require '../src/Route.php';
    require $smtp_file;
    require $email_file;
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Functions
// -----------------------------------------------------------

// The [Date] Header in an Email changes each time 
// so remove it from the result and return with only LF.
function removeDateLine($message) {
    $CRLF = "\r\n";
    $lines = explode($CRLF, $message);
    if (strpos($lines[0], 'Date: ') === 0) {
        $lines[0] = 'Date: <Removed>';
    }
    return implode("\n", $lines);
}

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// This Test connects to Gmail’s SMTP Server and runs several SMTP Commands.
// Gmail is expected to be rarely down so this test verifies that a server can 
// connect to an SMTP Server on Secure Port 587.
//
// Sending an actual email is not Unit Tested because that would require 
// publishing an email address and credentials to the public. To test with 
// your environment or email search for the file [testing-smtp.php] or 
// refer to docs and examples.
$app->get('/connect-to-gmail', function() {
    // Log Communication with Server
    // NOTE - the [&$reply_lines] means the variable is being
    // passed by reference so it can be modified.
    $reply_lines = array();
    $debug_callback = function($message) use (&$reply_lines) {
        $reply_lines[] = trim($message); 
    };

    // Connect to Gmail and run some SMTP Commands
    $host = 'smtp.gmail.com';
    $port = 587;    
    $timeout = 5;
    $smtp = new \FastSitePHP\Net\SmtpClient($host, $port, $timeout, $debug_callback);
    $smtp->noop();
    $smtp->help();
    $smtp = null;

    // Check Logged Lines
    $line_count = count($reply_lines);
    if ($line_count === 0) {
        throw new \Exception('Expected Reply Lines');
    }

    $open_reply = substr($reply_lines[1], 7, 3);
    $ehlo_replys = array();
    $tls_reply = null;
    $noop_reply = null;
    $help_reply = null;
    $quit_reply = null;
    $send_lines = array();

    for ($n = 0; $n < $line_count; $n++) {
        $line = $reply_lines[$n];
        if (strpos($line, 'send: EHLO') === 0) {
            $ehlo_replys[] = substr($reply_lines[$n+1], 7, 3);
        } elseif ($line === 'send: STARTTLS') {
            $tls_reply = substr($reply_lines[$n+1], 7, 3);
        } elseif ($line === 'send: NOOP') {
            $noop_reply = substr($reply_lines[$n+1], 7, 3);
        } elseif ($line === 'send: HELP') {
            $help_reply = substr($reply_lines[$n+1], 7, 3);
        } elseif ($line === 'send: QUIT') {
            $quit_reply = substr($reply_lines[$n+1], 7, 3);
        }

        if (strpos($line, 'send: ') === 0) {
            $data = explode(' ', $line);
            $send_lines[] = $data[1];
        }
    }

    // Uncomment to Debug and see all Lines
    // header('Content-Type: text/plain');
    // print_r($reply_lines);
    // exit();

    // Expected: '[open: smtp.gmail.com 587][EHLO,STARTTLS,EHLO,NOOP,HELP,QUIT][open:220][ehlo:2:250:250][tls:220][noop:250][help:214][quit:221][close]'
    $first = $reply_lines[0];
    $last = $reply_lines[count($reply_lines) - 1];
    $ehlo_count = count($ehlo_replys);
    $ehlo = implode(':', $ehlo_replys);
    $send = implode(',', $send_lines);
    return "[{$first}][{$send}][open:{$open_reply}][ehlo:{$ehlo_count}:{$ehlo}][tls:{$tls_reply}][noop:{$noop_reply}][help:{$help_reply}][quit:{$quit_reply}][{$last}]";
});

// Build a Basic Email and Return as Text using [message()] which is
// what gets called by the SMTP Client
$app->get('/create-basic-email', function() use ($app) {
    $from = 'noreply@example.com';
    $to = array('name1@example.com', 'name2@example.com');
    $subject = 'This is a Test Email from FastSitePHP with a long subject that wraps and used base64 encoding';
    $body = '<h1>Email Title</h1><p style="color:blue">This is a test.</p>';
    $email = new \FastSitePHP\Net\Email($from, $to, $subject, $body);

    $app->header('Content-Type', 'text/plain');
    return removeDateLine($email->message());
});

// Build an Advanced Email with most properties set and add a file attachment
$app->get('/create-advanced-email', function() use ($app) {
    $from = '无回复@example.com';
    $reply_to = array('测试@example.com', '测试');
    $to = 'name1@example.com';
    $cc = array('name2@example.com', '"User" Name');
    $bcc = 'name3@example.com';
    $subject = 'This is a Test Email with Attached Files';
    $body = 'Test Plain Text Email';
    $file = __DIR__ . '/files/Test.txt';

    // This also verifies that all setter functions are chainable.
    // This tests all setter functions except [encodeFileNames()].
    // Unlike the Basic Email Test no propertes are passed to the Constructor.
    $email = new \FastSitePHP\Net\Email();
    $email
        ->allowUnicodeEmails(true)
        ->from($from)
        ->replyTo($reply_to)
        ->to($to)
        ->cc($cc)
        ->bcc($bcc)
        ->subject($subject)
        ->body($body)
        ->isHtml(false)
        ->priority('High')
        ->header('X-Transaction-ID', '123abc')
        ->header('X-Email-Type', 'Unit-Test')
        ->header('X-Emoji', '😊')
        ->header('X-Encode-Backslash', '\\')
        ->header('X-Encode-Quote', '"')
        ->header('X-Encode-URL', '%')
        ->header('X-Encode-HTML1', '&')
        ->header('X-Encode-HTML2', 'U+')
        ->safeHeaderNames(false)
        ->header('Strange Header 😊', '👍')
        ->attachFile($file);

    $app->header('Content-Type', 'text/plain');
    return removeDateLine($email->message());
});

// Similar to the above but setting [encodeFileNames(true)] and not using 
// Unicode Address so [allowUnicodeEmails(true)] is not needed even
// though the email contains Unicode Content. Adds 2 file attachments.
$app->get('/create-email-with-encoded-file-names', function() use ($app) {
    $from = 'noreply@example.com';
    $to = 'name1@example.com';
    $subject = 'Test Email with Encoded File Names';
    $body = '<h1>File Name Test</h1>';
    $file1 = __DIR__ . '/files/Test.txt';

    // Create a Temp File using Chinese Characters in the file name.
    // This file is created each time it's needed rather than saved
    // with the project because Windows built-in Zip program won't
    // zip the root folder unless the locale is setup. Third-Party 
    // Zip programs such as 7-Zip don't have this issue.
    $file2 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . '测试.txt';
    if (is_file($file2)) {
        unlink($file2);
    }
    $contents = 'This is a Test using Simplified Chinese' . "\r\n" . '测试' . "\r\n";
    $contents .= $contents;
    file_put_contents($file2, $contents);

    // Create Email
    $email = new \FastSitePHP\Net\Email($from, $to, $subject, $body);
    $email
        ->header('X-Transaction-ID', '测试-123')
        ->encodeFileNames(true)
        ->attachFile($file1)
        ->attachFile($file2);

    // Build Message
    $app->header('Content-Type', 'text/plain');
    $message =  removeDateLine($email->message());

    // Delete Temp file and return message
    unlink($file2);
    return $message;
});

// Because not all SMTP commands are Unit Tested and emails are not sent the 
// files themselves are hashed in order to show if something changed. 
// Sending Emails to common email providers using the SMTP Class 
// and seeing how they look with widely used Email Clients
// must be manually verified after any changes.
$app->get('/class-hashes', function() use ($smtp_file, $email_file) {
    // In case files are saved using [CRLF] normalize the line endings [CRLF -> LF].
    // Also trim any white space.
    return array(
        'Email' => hash('sha384', trim(str_replace("\r\n", "\n", file_get_contents($email_file)))),
        'SmtpClient' => hash('sha384', trim(str_replace("\r\n", "\n", file_get_contents($smtp_file)))),
    );
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
