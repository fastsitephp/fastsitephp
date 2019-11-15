<?php
namespace App\Controllers;

use FastSitePHP\Application;
use FastSitePHP\Data\Validator;
use FastSitePHP\Environment\DotEnv;
use FastSitePHP\Lang\I18N;
use FastSitePHP\Net\Email;
use FastSitePHP\Net\SmtpClient;

class TranslatorsNeeded
{
    /**
     * Route function for URL '/:lang/translators-needed'.
     * 
     * This page is linked from the [README.md] file and provides a form
     * for developers to submit private messages for translation services.
     * 
     * Testing this page requires setup of a [.env] file with the needed
     * settings. The code in this page provides a good example of handling
     * a <form> POST with PHP and sending the result via SMTP.
     * 
     * @param Application $app
     * @param string $lang
     * @return string
     */
    public function route(Application $app, $lang)
    {
        // Load JSON Language File
        I18N::langFile('translators-needed', $lang);

        // This page is not translated
        if ($app->lang !== 'en') {
            return $app->pageNotFound();
        }

        // Variables used for email logic and with template rendering
        $email_from = null;
        $message = null;
        $message_sent = false;
        $errors = null;
        $sent_info = null;

        // Handle Form Posts
        if ($_POST) {
            $email_from = $_POST['email'];
            $message = $_POST['message'];

            // Only send if valid (this is fallback validation as the form
            // will be validated from the browser using HTML5 attributes.
            $v = new Validator();
            $v->addRules([
                ['email',   'Email',   'required type="email"'],
                ['message', 'Message', 'required'],
            ]);
            list($errors, $fields) = $v->validate($_POST);
            if (!$errors) {
                // Load the [.env] file
                // This file only exists on FastSitePHP's production webserver.
                $dir = $app->config['APP_DATA'];
                $required_vars = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASSWORD'];
                DotEnv::load($dir, $required_vars);

                // Send Email based on [.env] settings
                $from = getenv('SMTP_USER');
                $to = $from;
                $subject = 'FastSitePHP and DataFormsJS Translator Inquiry';
                $body = $email_from . "\n" . $_SERVER['REMOTE_ADDR'] . "\n" . str_repeat('-', 80) . "\n" . $message;
                $email = new Email($from, $to, $subject, $body);
                $email->isHtml(false);
                SmtpClient::sendEmails([$email]);

                // Mark email as sent
                $message_sent = true;
                $sent_info = 'Your message has been sent. Thank you for your interest! Look for an email from [%s]; you may need to add this to your email whitelist.';
                $sent_info = sprintf($sent_info, $to);
            }
        }

        // Render Template
        return $app->render('translators-needed.php', [
            'email_from' => $email_from,
            'message' => $message,
            'message_sent' => $message_sent,
            'sent_info' => $sent_info,
            'errors' => $errors,
        ]);
    }
}
