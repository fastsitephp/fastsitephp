<?php
namespace App\Controllers;

use \SimpleXMLElement;
use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;
use FastSitePHP\FileSystem\Search;

/**
 * Controller for URL '/site/generate-sitemap'
 * 
 * Typically this will run from localhost at the following URL:
 * http://localhost:3000/fastsitephp/website/public/site/generate-sitemap
 * 
 * It will overwite the existing site [sitemap.xml] so it only needs 
 * to run when new pages or translations are added.
 * 
 * To add a new language add it under the [$langs] array at line 34.
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
        // *** Add new lanauges here once the content is ready
        $langs = [
            'en',
            'pt-BR',
            'es',
            'zh-CN',
            'fr',
            'ar',
        ];
        // Only change [docs] and [api] languages if content has been created
        $docs_langs = ['en'];
        $api_langs = ['en'];
        $exclude_routes = [
            '/:lang/examples/response/:type',
            '/:lang/examples/database-demo/:page',
            '/:lang/examples/encryption/generate-key',
            '/:lang/security-issue',
        ];
        $include_files = [
            __DIR__ . '/../routes-examples.php'
        ];

        // Generate lists of variables for specific routes

        // Docs
        $search = new Search();
        $docs_dir = $app->config['APP_DATA'] . 'docs/en';
        $docs = $search
            ->reset()
            ->dir($docs_dir)
            ->fileTypes(['md'])
            ->hideExtensions(true)
            ->files();

        // API Class Files
        $classes_dir = $app->config['APP_DATA'] . 'api/en';
        $classes = $search
            ->reset()
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
                        foreach ($docs_langs as $lang) {
                            $url = str_replace('/:lang', '/' . $lang, $route->pattern);
                            $url = str_replace(':page', $doc, $url);
                            $urls[] = $host . $url;
                        }
                    }
                    continue;
                } elseif ($url === '/:lang/api/:class') {
                    foreach ($classes as $class) {
                        foreach ($api_langs as $lang) {
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
