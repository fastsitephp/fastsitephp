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
        // Read JSON Language File
        I18N::langFile('documents', $lang);

        // Get Documents from JSON file and Update for i18n Keys
        $i18n = $app->locals['i18n'];
        $documents = json_decode(file_get_contents(__DIR__ . '/../Models/Documents.json'));
        foreach ($documents as $document) {
            $document->title = $i18n[$document->page];
            $document->category = $i18n[$document->category];
            if (isset($document->img_alt_i18n)) {
                $document->img_alt = $i18n[$document->img_alt_i18n];
            }
        }
        
        // Render Page
        return $app->render('card-list.php', [
            'nav_active_link' => 'documents',
            'cards' => $documents,
        ]);
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