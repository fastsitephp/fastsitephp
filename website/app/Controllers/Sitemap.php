<?php
namespace App\Controllers;

use \SimpleXMLElement;
use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;
use FastSitePHP\FileSystem\Search;

/**
 * Controller for URL '/site/generate-sitemap'
 */
class Sitemap
{
    /**
     * Handle 'GET' requests and create the [sitemap.xml] file
     *
     * @param Application $app
     * @param string $lang
     */
    public function get(Application $app)
    {
        // Site Settings
        $host = 'https://www.fastsitephp.com';
        $urls = [];
        $langs = ['en'];
        $exclude_routes = [
            '/:lang/examples/response/:type',
            '/:lang/examples/database-demo/:page',
            '/:lang/examples/encryption/generate-key',
            '/:lang/translators-needed',
        ];
        $include_files = [
            __DIR__ . '/../routes-examples.php'
        ];

        // Generate lists of variables for specific routes

        // Docs
        I18N::langFile('documents', $langs[0]);
        $links = $app->locals['i18n']['links'];
        $docs = array_map(function($item) {
            return $item['page'];
        }, $links);

        // API Class Files
        $classes_dir = $app->config['APP_DATA'] . 'api/en';
        $search = new Search();
        $classes = $search
            ->dir($classes_dir)
            ->fileTypes(['json'])
            ->hideExtensions(true)
            ->files();

        // Include needed Route Files
        foreach ($include_files as $file) {
            include $file;
        }

        // Add or skip each route
        foreach ($app->routes() as $route) {
            // Skip POST-only and other web service routes
            if (!($route->method === 'GET' || $route->method === null)) {
                continue;
            }
            // Skip specific web service routes
            if (in_array($route->pattern, $exclude_routes, true)) {
                continue;
            }

            // Only include routes starting with a language
            if (strpos($route->pattern, '/:lang') === 0) {
                // Route URL/Pattern
                $url = $route->pattern;
                
                // Handle specific routes
                if ($url === '/:lang') {
                    $url = '/:lang/';
                } elseif ($url === '/:lang/documents/:page') {
                    foreach ($docs as $doc) {
                        foreach ($langs as $lang) {
                            $url = str_replace('/:lang', '/' . $lang, $route->pattern);
                            $url = str_replace(':page', $doc, $url);
                            $urls[] = $host . $url;
                        }
                    }
                    continue;
                } elseif ($url === '/:lang/api/:class') {
                    foreach ($classes as $class) {
                        foreach ($langs as $lang) {
                            $url = str_replace('/:lang', '/' . $lang, $route->pattern);
                            $url = str_replace(':class', $class, $url);
                            $urls[] = $host . $url;
                        }
                    }
                    continue;
                }

                // Add page for each language
                foreach ($langs as $lang) {
                    $urls[] = $host . str_replace('/:lang', '/' . $lang, $url);
                }
            }
        }

        // Uncomment to debug
        // header('Content-Type: text');
        // //var_dump($routes);
        // sort($urls);
        // var_dump($urls);
        // //var_dump($classes);
        // //var_dump($docs);
        // exit();

        // Generate [sitemap.xml]
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        foreach ($urls as $url) {
            $node = $xml->addChild('url');
            $node->addChild('loc', $url);
        }
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        $xml = $dom->saveXML();

        // Save to file
        $save_path = $app->config['APP_DATA'] . '../public/sitemap.xml';
        file_put_contents($save_path, $xml);

        // Output Save location
        $app->header('Content-Type: text/plain');
        return 'Sitemap saved to: ' . $save_path;
    }
}
