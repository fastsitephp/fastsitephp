<?php
namespace App\Controllers;

use App\Models\ExampleCode;
use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;
use FastSitePHP\FileSystem\Security;

/**
 * API Controller
 * 
 * JSON files used by this Class are generated from the script:
 *     fastsitephp\scripts\create-api-json-files.php
 */
class API
{
    /**
     * Route function for URL '/:lang/api'
     * 
     * @param Application $app
     * @param string $lang
     * @return string
     */
    public function get(Application $app, $lang)
    {
        I18N::langFile('api', $lang);

        $file_path = $app->config['APP_DATA'] . 'api/Classes_and_Function.json';
        $classes = json_decode(file_get_contents($file_path));

        $templates = ['api.php', 'js-tabs.htm', 'js-filter.htm'];
        return $app->render($templates, [
            'nav_active_link' => 'api',
            'classes' => $classes,
        ]);
    }

    /**
     * Route function for URL '/:lang/api/:class'
     * 
     * @param Application $app
     * @param string $lang
     * @param string $class
     * @return string
     */
    public function getClass(Application $app, $lang, $class)
    {
        I18N::langFile('api', $lang);

        // Because [$class] comes directly from the user, first make 
        // sure the [en] folder contains the file name.
        $dir = $app->config['APP_DATA'] . 'api/en';
        if (!Security::dirContainsFile($dir, $class . '.json')) {
            return $app->pageNotFound();
        }

        // Get Class API JSON file based on the selected language.
        // This code is safe because of the above security check.
        $file_path = $app->config['APP_DATA'] . 'api/{lang}/' . $class . '.json';
        $json = I18N::textFile($file_path, $lang);
        $class = json_decode($json);
        
        // Get Example Code for the Class
        $example = new ExampleCode($app);
        $code = $example->getCode($class->short_name);

        // Get Class List for the Sidebar
        $file_path = $app->config['APP_DATA'] . 'api/Classes.json';
        $classes = json_decode(file_get_contents($file_path));        

        // Define a custom function for template rendering.
        // Convert new-lines to <br> and preserve white-space.
        $app->escapeDesc = function($content) use ($app) {
            $content = nl2br($app->escape($content), false);
            return str_replace('  ', '&nbsp;&nbsp;', $content);
        };

        // Update Page Title with Class Name
        $app->locals['i18n']['page_title'] .= ' | ' . $class->name;

        // Create a Github link to the source code for the clas
        if (strpos($class->name, 'App') === 0) {
            $class->github = 'https://github.com/fastsitephp/starter-site/blob/master/app/';
            $class->github .= str_replace('\\', '/', str_replace('App\\', '', $class->name));
        } else {
            $class->github = 'https://github.com/fastsitephp/fastsitephp/blob/master/src/';
            $class->github .= str_replace('\\', '/', str_replace('FastSitePHP\\', '', $class->name));    
        }
        $class->github .= '.php';

        // Render
        return $app->render('api-class.php', [
            'nav_active_link' => 'api',
            'classes' => $classes,
            'class' => $class,
            'example_code' => $code,
        ]);
    }
}