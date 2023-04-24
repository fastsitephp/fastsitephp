<?php
// This script allows for testing of the [SmtpClient] to send
// emails. It can be ran from either CLI (Command Line Interface)
// or from a Web Browser.
//
// ** IMPORTANT - If you modify this file and add Auth Credentials
// to this file you should remove them before saving the file
// to any sort of public source control. You should also remove
// any real email addresses so bots don't find them.
//
// This script provides many options and test examples. To use
// it you will need an SMTP Server and one or more emails to send
// from and to.


// If the [Application] does not get created then uncomment
// the code below:
//
// error_reporting(-1);
// ini_set('display_errors', 'on');
// date_default_timezone_set('UTC');

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// Plain-Text Response
header('Content-Type: text/plain');

// -----------------------------------------------------------
// Define empty values
$auth_user = null;
$auth_pass = null;
$to = null;
$from = null;
$cc = null;
$bcc = null;
$priority = null;

// -----------------------------------------------------------
// Office 365 - On Company VPN using Port 25:
// NOTE - If you use Office365 replace replace [YOUR_COMPANY] 
// with the value for your company or change the full host.
// This only works based on specific rules if a company
// allows internal SMTP on port 25 when on VPN.
$host = 'YOUR_COMPANY-com.mail.protection.outlook.com';
$port = 25;
$to = 'YOUR_EMAIL@example.com';
$from = 'noreply@example.com';

// -----------------------------------------------------------
// Gmail using Port 587:
// $host = 'smtp.gmail.com';
// $port = 587;
// $to = 'YOUR_EMAIL@gmail.com';
// $from = 'YOUR_EMAIL@gmail.com';
// $auth_user = 'YOUR_EMAIL@gmail.com';
// $auth_pass = 'YOUR_PASSWORD';

// -----------------------------------------------------------
// AWS
// $host = 'email-smtp.us-east-1.amazonaws.com';
// $port = 587;

// -------------------------------------------
// Define other options or chagne
// $to = array('YOUR_EMAIL@example.com', 'YOUR_EMAIL@hotmail.com', 'YOUR_EMAIL@gmail.com');
// $priority = 'Low'; // 'High'
// $cc = 'YOUR_EMAIL@gmail.com';
// $bcc = 'YOUR_EMAIL@hotmail.com';

// -----------------------------------------------------------
// Define Email Subject and Body
$subject = 'Email Test from PHP at ' . date(DATE_RFC2822);
// $subject = '😊';
$body = '<h1>Email Title</h1><p style="color:blue">This is a test.</p>';

// --------------------------------------------------------------
// Big Email Test (100+ characters in Subject and 1000+ in body)
//
// $subject = 'Big Email Test: ';
// $body = '';
// for ($n = 0; $n < 10; $n++) {
// 	$subject .= str_repeat((string)$n, 10) . ' ';
// 	$body .= '<p>' . str_repeat((string)$n, 100) . ' </p>';
// }

// ------------------------------------------------------------------------
// Define Debug Callback to show all communication with the SMTP Server
$timeout = 10;
$debug_callback = function($message) { 
	echo '[' . date('H:i:s') . '] ' . trim($message) . "\n"; 
};

// $timeout = null;
// $debug_callback = null;

// -----------------------------------------------------------------
// Create Email
$email = new \FastSitePHP\Net\Email($from, $to, $subject, $body);
if ($cc !== null) {
	$email->cc($cc);
}
if ($bcc !== null) {
	$email->bcc($bcc);
}
if ($priority !== null) {
	$email->priority($priority);
}

// ---------------------------------------------------------------------------------
// Test sending without a [From] Address, this will work for some SMTP Servers
//$email = new \FastSitePHP\Net\Email(null, $to);

// ---------------------------------------------------------------------------------
// 'No Reply' translated to [Chinese (Simplified)] from Google Translate
// $email->allowUnicodeEmails(true)->from('无回复@example.com');

// ---------------------------------------------------------------------------------
// Set multiple emails using both email and name
// $email
// 	->to(array('YOUR_EMAIL@example.com', 'Name Work'))
// 	->to(array('YOUR_EMAIL@hotmail.com', 'Name Hotmail'))
// 	->to(array('YOUR_EMAIL@gmail.com', 'Name Gmail'));

// ---------------------------------------------------------------------------------
// Set other Header Options
// $email->replyTo('YOUR_EMAIL@example.com');
// $email->header('X-Transaction-ID', '123abc');
// $email->header('X-Transaction-ID', '测试-123');
// $email->header('X-Transaction-ID2', 'test\\123');

// ---------------------------------------------------------------------------------------------
// File Testing. Change paths or create these files as needed.
//
// In this tested email providers accept both versions of [Desert.jpg] with the same name
// Tested on Gmail, Hotmail, and Office365.
//
// $email->attachFile('C:\Users\Public\Pictures\Thumbnails\Desert.jpg');
// $email->attachFile('C:\Users\Public\Pictures\Sample Pictures\Desert.jpg');
// $email->attachFile(__DIR__ . '\Test.txt');
// $email->attachFile(__DIR__ . '\Long File Name 0000000000 1111111111 2222222222 3333333333 4444444444 5555555555 6666666666 7777777777 8888888888 9999999999.txt');
// $email->attachFile(__DIR__ . '\测试.txt');

// Optional param when using [attachFile()] 
// $email->encodeFileNames(true);

// ---------------------------------------------------------------------------------------------
// Test using App Config or Environment Variables

// $app->config['SMTP_HOST'] = $host;
// $app->config['SMTP_PORT'] = $port;
// $app->config['SMTP_TIMEOUT'] = $timeout;
// $app->config['SMTP_USER'] = $auth_user;
// $app->config['SMTP_PASSWORD'] = $auth_pass;

// // putenv("SMTP_HOST=${host}");
// // putenv("SMTP_PORT=${port}");
// // putenv("SMTP_TIMEOUT=${timeout}");
// // putenv("SMTP_USER=${auth_user}");
// // putenv("SMTP_PASSWORD=${auth_pass}");

// \FastSitePHP\Net\SmtpClient::sendEmails(array($email));
// exit();

// ---------------------------------------------------------------------------------
// Create SMTP Client and Send Email or Run Commands
$sent_email = false;
$has_error = false;
try {
	// Create SMTP Client
	if ($timeout === null) {
		$smtp = new \FastSitePHP\Net\SmtpClient($host, $port);	
	} elseif ($debug_callback === null) {
		$smtp = new \FastSitePHP\Net\SmtpClient($host, $port, $timeout);
	} else {
		$smtp = new \FastSitePHP\Net\SmtpClient($host, $port, $timeout, $debug_callback);
	}

	// An additional option would be to not use host/port when creating object.
	// If using that method then EHLO and STARTTLS would need to be manually called
	//
	// $smtp = new \FastSitePHP\Net\SmtpClient(null, null, null, $debug_callback);
	// $smtp->connect($host, $port, $timeout);
	// $smtp->ehlo();
	// if ($port === 587) {
	// 	$smtp->startTls();
	// }

	// Optionally authenticate before sending email
	if ($auth_user !== null) {
		// Determined automatically from EHLO:
		$smtp->auth($auth_user, $auth_pass);
		
		// Or use specific method:
		// $smtp->authPlain($auth_user, $auth_pass);
		// $smtp->authLogin($auth_user, $auth_pass);
	}

	// Send Email
	$smtp->send($email);
	$sent_email = true;

	// Or instead of sending email, simply try other commands
	// $smtp->ehlo('server.example.com');
	// $smtp->helo();
	// $smtp->vrfy('test@example.com');
	// $smtp->startTls();
	// $smtp->rset();
	// $smtp->noop();
	// $smtp->help();
	// $smtp->quit();
	// var_dump('SMTPUTF8: ' . json_encode($smtp->supports('SMTPUTF8')));
	// var_dump('SIZE: ' . $smtp->size());
} catch (\Exception $e) {
	echo "\n";
	echo 'Exception:';
	echo "\n";
	echo $e;
	// echo $e->getMessage();
	echo "\n";
	$has_error  = true;
}

// ----------------------------------------------------------------
// Send a 2nd Email with the same SMTP Connection
// $email->subject('Email 2 from PHP');
// $smtp->send($email);

// ----------------------------------------------------------------
// This will automatically call [quit()] and [close()]
// You do not need to call it yourself unless you want
// to obtain logging info as soon as you finish sending email.
$smtp = null;

// ----------------------------------------------------------
// Result
echo "\n";
echo "\n";
if ($sent_email) {
	echo 'SUCCESS - No error, email should be sent';
} elseif ($has_error) {
	echo 'ERROR - No Email Sent';
} else {
	echo 'No Email Sent';
}
echo "\n";
