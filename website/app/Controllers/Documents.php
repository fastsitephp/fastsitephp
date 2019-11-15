<?php
namespace App\Controllers;

use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;
use FastSitePHP\FileSystem\Security;
use Parsedown;

class Documents
{
    /**
     * Route function for URL '/:lang/documents'
     * 
     * @param Application $app
     * @param string $lang
     * @return string
     */
    public function get(Application $app, $lang)
    {
        I18N::langFile('documents', $lang);
        return $app->render('card-list.php', ['nav_active_link' => 'documents']);
    }
    
    /**
     * Route function for URL '/:lang/document/:page'
     * 
     * @param Application $app
     * @param string $lang
     * @param string $page
     * @return string
     */
    public function getDoc(Application $app, $lang, $page)
    {
        I18N::langFile('document', $lang);

        // Because [$page] comes directly from the user, first make 
        // sure the [en] folder contains the file name.
        $dir = $app->config['APP_DATA'] . 'docs/en';
        if (!Security::dirContainsFile($dir, $page . '.md')) {
            return $app->pageNotFound();
        }

        // Get Markdown Content based on the selected language.
        // This code is safe because of the above security check.
        $file_path = $app->config['APP_DATA'] . 'docs/{lang}/' . $page . '.md';
        $md = I18N::textFile($file_path, $lang);

        // Get <h1> Title from Markdown Content
        if (preg_match('/^# (.+)/', $md, $matches)) {
            $app->locals['i18n']['page_title'] = $matches[1];
        }

        // Render Markdown to HTML and swap Line Breaks with Sections
        $html = (new Parsedown())->text($md);
        $html = str_replace('<hr />', '</section><section class="content">', $html);

        // Render PHP Template
        return $app->render('document.php', [
            'nav_active_link' => 'documents',
            'html' => $html,
        ]);
    }
}