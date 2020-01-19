<?php
namespace App\Controllers;

use FastSitePHP\Application;
use FastSitePHP\Net\HttpClient;
use FastSitePHP\Web\Response;

class Downloads
{
    /**
     * Route function for URL '/downloads/file'
     * 
     * @param Application $app
     * @param string $file
     */
    public function get(Application $app, $file)
    {
        $url = null;
        switch ($file) {
            case 'encrypt-bash':
                $file_path = __DIR__ . '/../../scripts/shell/bash/encrypt.sh';
                if (!is_file($file_path)) {
                    // Path when running site for local development
                    $file_path = __DIR__ . '/../../../scripts/shell/bash/encrypt.sh';
                }
                return (new Response())->file($file_path, 'download');
            case 'create-fast-site.sh':
            case 'create-fastsitephp-app.sh':
                // When first published [create-fast-site.sh] was named [create-fastsitephp-app.sh]
                $file_path = __DIR__ . '/../../scripts/shell/bash/create-fast-site.sh';
                if (!is_file($file_path)) {
                    $file_path = __DIR__ . '/../../../scripts/shell/bash/create-fast-site.sh';
                }
                return (new Response())->file($file_path, 'download');
            case 'fastsitephp':
                $url = 'https://github.com/fastsitephp/fastsitephp/archive/master.zip';
                break;
            case 'starter-site':
                $url = 'https://github.com/fastsitephp/starter-site/archive/master.zip';
                break;
            case 'framework':
                // Determine latest release verison from GitHub, example 1.1.2
                $api_url = 'https://api.github.com/repos/fastsitephp/fastsitephp/releases/latest';
                $res = HttpClient::get($api_url);
                if ($res->error || !isset($res->json['tag_name'])) {
                    throw new \Exception('Call to GitHub failed, unable to get release number.');
                }
                $version = $res->json['tag_name'];
                $url = 'https://github.com/fastsitephp/fastsitephp/archive/' . $version . '.zip';
                break;
        }
        if (isset($url)) {
            $app->redirect($url);
        }
        return $app->pageNotFoud();
    }
}