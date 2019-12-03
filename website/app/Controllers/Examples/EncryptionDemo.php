<?php

namespace App\Controllers\Examples;

use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;
use FastSitePHP\Security\Crypto\Encryption;
use FastSitePHP\Web\Request;

class EncryptionDemo
{
    /**
     * Return HTML for the Web Page UI
     */
    public function get(Application $app, $lang)
    {
        // Load Language File
        I18N::langFile('encryption-demo', $lang);

        // Add Code Example from text file
        $file_path = $app->config['I18N_DIR'] . '/code/encryption-demo.{lang}.txt';
        $app->locals['i18n']['code'] = I18N::textFile($file_path, $app->lang);

        // Generate a new Key each time the page is loaded
        $crypto = new Encryption();
        $key = $crypto->generateKey();

        // Render the View
        $templates = [
            'js-tabs.htm',
            'old-browser-warning.htm',
            'examples/encryption-demo.php',
            'examples/encryption-demo.htm',
        ];
        return $app->render($templates, [
            'nav_active_link' => 'examples',
            'key' => $key,
        ]);
    }

    /**
     * JSON Web Service to Generate a New Key
     */
    public function generateKey()
    {
        $crypto = new Encryption();
        $key = $crypto->generateKey();
        return ['key' => $key];
    }

    /**
     * Read JSON Data from Request
     */
    private function getRequest() {
        $req = new Request();
        $data = $req->content();
        return [$data['key'], $data['text']];
    }

    /**
     * JSON Web Service to Encrypt Text
     */
    public function encrypt()
    {
        $crypto = new Encryption();
        try {
            list($key, $plaintext) = $this->getRequest();
            $ciphertext = $crypto->encrypt($plaintext, $key);
            return ['text' => $ciphertext];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * JSON Web Service to Decrypt Text
     */
    public function decrypt()
    {
        $crypto = new Encryption();
        $crypto->exceptionOnError(true); // By default NULL is returned on errors
        try {
            list($key, $ciphertext) = $this->getRequest();
            $plaintext = $crypto->decrypt($ciphertext, $key);
            return ['text' => $plaintext];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}