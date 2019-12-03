<?php

namespace App\Controllers\Examples;

use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;
use FastSitePHP\Web\Response;

class ResponseDemo
{
    /**
     * Return HTML for the Web Page
     */
    public function get(Application $app, $lang)
    {
        // Load Language File
        I18N::langFile('response-demo', $lang);

        // Add Code Examples from text files
        $templates = ['html', 'json', 'text'];
        foreach ($templates as $tmpl) {
            $file_path = $app->config['I18N_DIR'] . '/code/response-demo-' . $tmpl . '.{lang}.txt';
            $app->locals['i18n']['tmpl_' . $tmpl] = I18N::textFile($file_path, $app->lang);    
        }

        // Render the View
        $templates = [
            'old-browser-warning.htm',
            'examples/response-demo.php',
            'examples/response-demo.htm',
        ];
        return $app->render($templates, [
            'nav_active_link' => 'examples',
        ]);
    }

    /**
     * This route gets called by drop-down changes on the page
     */
    public function byType(Application $app, $lang, $type)
    {
        I18N::langFile('response-demo', $lang);
        $i18n = $app->locals['i18n'];
        $hello_world = $i18n['hello_world'];
        $page_requested = str_replace('{time}', date(DATE_RFC2822), $i18n['page_requested']);

        switch ($type) {
            case 'html':
                $html = '<h1>' . $hello_world . '</h1>';
                $html .= "\n<p>" . $page_requested . '</p>';
                return $html;
            case 'text':
                $app->header('Content-Type', 'text/plain');
                return "${hello_world}\n${page_requested}";
            case 'json':
                return [
                    'greeting' => $hello_world,
                    'valueInt' => 123,
                    'valueBool' => true,
                    'requestTime' => date(DATE_RFC2822),
                ];
            default:
                throw new \Exception('Unknown Response Type: ' . (string)$type);
        }
    }
}