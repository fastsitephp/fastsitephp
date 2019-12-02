<?php

namespace App\Controllers\Examples;

use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;
use FastSitePHP\Web\Request;

class RequestDemo
{
    /**
     * Return HTML for the Web Page
     */
    public function get(Application $app, $lang)
    {
        // Load Language File
        I18N::langFile('request-demo', $lang);

        // Read Request Info
        $req = new Request();
        $headers = $req->headers();

        foreach ($headers as $key => $value) {
            if (strlen($value) > 150) {
                $label = $app->locals['i18n']['length'];
                $headers[$key] = substr($value, 0, 150) . "\n" . '... (' . $label . ': ' . strlen($value) . ')';
            }
        }

        $string = $app->locals['i18n']['string'];
        $array_bool = $app->locals['i18n']['array_bool'];
        $str_null = $app->locals['i18n']['string_null'];
        $boolean = $app->locals['i18n']['boolean'];
        $array_string = $app->locals['i18n']['array_string'];
        $mixed = $app->locals['i18n']['mixed'];

        $app_props = [
            ['$app->rootUrl()',         $string,   $app->rootUrl()],
            ['$app->rootDir()',         $string,   $app->rootDir()],
            ['$app->requestedPath()',   $string,   $app->requestedPath()],
        ];

        $req_props = [
            ['$req->method()',          $str_null,      0,  $req->method()],
            ['$req->serverIp()',        $str_null,      0,  $req->serverIp()],
            ['$req->accept()',          $array_bool,    0,  str_replace('\\/', '/', json_encode($req->accept(), JSON_PRETTY_PRINT))],
            ['$req->acceptCharset()',   $array_bool,    0,  json_encode($req->acceptCharset(), JSON_PRETTY_PRINT)],
            ['$req->acceptEncoding()',  $array_bool,    0,  json_encode($req->acceptEncoding(), JSON_PRETTY_PRINT)],
            ['$req->acceptLanguage()',  $array_bool,    0,  json_encode($req->acceptLanguage(), JSON_PRETTY_PRINT)],
            ['$req->origin()',          $str_null,      0,  $req->origin()],
            ['$req->userAgent()',       $str_null,      0,  $req->userAgent()],
            ['$req->referrer()',        $str_null,      0,  $req->referrer()],
            ['$req->clientIp()',        $str_null,      1,  $req->clientIp()],
            ['$req->protocol()',        $str_null,      1,  $req->protocol()],
            ['$req->host()',            $str_null,      1,  $req->host()],
            ['$req->port()',            $str_null,      1,  $req->port()],
            ['$req->isLocal()',         $boolean,       0,  json_encode($req->isLocal())],
            ['$req->isXhr()',           $boolean,       0,  json_encode($req->isXhr())],
        ];

        $req_content = [
            ['$req->contentType()',                             $str_null],
            ['$req->content()',                                 $array_string],
            ['$req->contentText()',                             $string],
            ['$req->queryString($name, $format = \'value?\')',  $mixed],
            ['$req->form($name, $format = \'value?\')',         $mixed],
            ['$req->cookie($name, $format = \'value?\')',       $mixed],
            ['$req->verifiedCookie($name)',                     $mixed],
            ['$req->jwtCookie($name)',                          $mixed],
            ['$req->decryptedCookie($name)',                    $mixed],
            ['$req->value($name, $format = \'value?\', $max_length = null)',       $mixed],
        ];

        // Find/Replace in text
        $app->locals['i18n']['request_headers_desc'] = str_replace('{ip}', $req->clientIp(), $app->locals['i18n']['request_headers_desc']);

        // Add Code Examples from text files
        $file_path = $app->config['I18N_DIR'] . '/code/request-demo-header.{lang}.txt';
        $app->locals['i18n']['header_code'] = \FastSitePHP\Lang\I18N::textFile($file_path, $app->lang);

        $file_path = $app->config['I18N_DIR'] . '/code/request-demo-code.{lang}.txt';
        $app->locals['i18n']['req_code'] = \FastSitePHP\Lang\I18N::textFile($file_path, $app->lang);

        // Render the View
        $templates = [
            'js-tabs.htm',
            'examples/request-demo.php',
        ];
        return $app->render($templates, [
            'nav_active_link' => 'examples',
            'app_props' => $app_props,
            'req_props' => $req_props,
            'headers' => $headers,
            'req_content' => $req_content,
        ]);        
    }
}