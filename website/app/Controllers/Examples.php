<?php

namespace App\Controllers;

use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;

class Examples
{
    /**
     * Route function for URL '/:lang/examples'
     * 
     * @param Application $app
     * @param string $lang
     * @return string
     */
    public function get(Application $app, $lang)
    {
        // Read JSON Language File
        I18N::langFile('examples', $lang);

        // Get Examples from JSON file and Update for i18n Keys
        $i18n = $app->locals['i18n'];
        $examples = json_decode(file_get_contents(__DIR__ . '/../Models/Examples.json'));
        foreach ($examples as $example) {
            $example->title = $i18n[$example->page];
            $example->category = $i18n[$example->category];
            $example->img_alt = $i18n[$example->img_alt];
        }
        
        // Render Page
        return $app->render('card-list.php', [
            'nav_active_link' => 'examples',
            'cards' => $examples,
        ]);
    }
}
