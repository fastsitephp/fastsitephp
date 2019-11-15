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
        I18N::langFile('examples', $lang);
        return $app->render('card-list.php', ['nav_active_link' => 'examples']);
    }
}
