<?php
namespace App\Controllers;

use App\Models\ExampleCode;
use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;

/**
 * Controller for URL '/:lang/quick-reference'
 */
class QuickReference
{
    /**
     * Handle 'GET' requests
     *
     * @param Application $app
     * @param string $lang
     */
    public function get(Application $app, $lang)
    {
        // Load JSON Language File
        I18N::langFile('quick-reference', $lang);

        // Get Example Code based on Selected Language
        $example = new ExampleCode($app);
        $example_code = $example->getCode();

        // Render Template
        // The JavaScript filter code is small so it is included inline in the
        // page so it runs immediately, and being in a separate file it can be
        // shared with other pages.
        $templates = ['quick-reference.php', 'js-filter.htm'];
        return $app->render($templates, array(
            'nav_active_link' => 'quick-reference',
            'example_code' => $example_code,
        ));
    }
}