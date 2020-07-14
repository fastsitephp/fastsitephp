<?php
// This script is the main entry point for the app. Routes are defined here and
// other PHP files are also loaded from here. This script and gets loaded from
// the file [public\index.php].

// ------------------------------------------------------------------
// Classes used in this file. Classes are not loaded unless used.
// ------------------------------------------------------------------

use FastSitePHP\Lang\I18N;
use FastSitePHP\Web\Response;

// --------------------------------------------------------------------------------------
// Site Configuration
// By default FastSitePHP does not require any site configuration in order to run.
// Config is used for this site to allow template rendering and language translations.
// When using the default error template detailed errors are only displayed when
// running from localhost or when [show_detailed_errors] is set to [true].
// For the main FastSitePHP website detailed errors are always displayed.
// --------------------------------------------------------------------------------------

// General Application Settings
$app->controller_root = 'App\Controllers';
$app->middleware_root = 'App\Middleware';
$app->template_dir = __DIR__ . '/Views/';
$app->header_templates = '_header.php';
$app->footer_templates = '_footer.php';
$app->error_template = 'error.php';
$app->not_found_template = 'error.php';
$app->show_detailed_errors = true;

// Translation Settings
$app->config['I18N_DIR'] = __DIR__ . '/../app_data/i18n';
$app->config['I18N_FALLBACK_LANG'] = 'en';
I18N::setup($app);

// Misc settings for this site
$app->config['APP_DATA'] = __DIR__ . '/../app_data/';

// The site css file is loaded and embedded directly in the page.
// This reduces render-blocking resources on page load and prevents
// the file from being cached allowing for immediate updates.
// This technique is only recommended if you have small CSS files
// that change regularly.
$app->onRender(function() use ($app) {
    // [public] dir is for dev, and [DOCUMENT_ROOT] for production.
    // This is a basic check but for the Requested URL to be 'localhost';
    // for actual IP validation see the class [App\Middleware\Env].
    // [App\Middleware\Env] is used on router filters in this file;
    // to see how it's used search this file for 'Env.isLocalhost'.
    $is_localhost = (
        isset($_SERVER['HTTP_HOST'])
        && ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0)
    );
    if ($is_localhost) {
        $path = __DIR__ . '/../public/css/site.css';
    } else {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/css/site.css';
    }
    $app->locals['site_css'] = '<style>' . file_get_contents($path) . '</style>';
});

// If the development environment Laravel Valet (Mac-only) is being used
// then check if the requested URL is looking for a file and return it if found.
// This is needed because Valet uses the root [index.php] file as a router
// and only looks under [~/public] for files by default. In all other development
// environments and on production servers this function will be called for a 404
// error but ignored because nothing is returned.
$app->notFound(function() use ($app) {
    if (isset($_SERVER['DOCUMENT_URI']) && strpos($_SERVER['DOCUMENT_URI'], '/laravel/valet/server.php') !== false) {
        $root = __DIR__ . '/../public';
        $path = $app->requestedPath();
        if (Security::dirContainsPath($root, $path)) {
            $res = new Response();
            return $res->file($root . $path);
        }
    }
});

// ----------------------------------------------------------------------------
// Routes
// FastSitePHP provides a number of different methods of defining routes.
// The code below provides several different examples.
// ----------------------------------------------------------------------------

// Root URL, redirect to the user's default language based the 'Accept-Language'
// request header. Defaults to 'en = English' if no language is matched.
// For example if the user's default language is Spanish then they will be
// redirected to '/es/'.
//
// This route is defined as a callback function (Closure in PHP).
// Defining routes with callback functions allows for fast prototyping
// and works well when minimal logic is used. As code grows in size it
// can be organized into controller classes.
//
// The response header [Vary: Accept-Language] is used for Content
// negotiation to let bots know that the content will change based
// on language. For example this applies to Googlebot and Bingbot.
//
$app->get('/', function() use ($app) {
    $res = new Response();
    return $res
        ->vary('Accept-Language')
        ->redirect($app->rootUrl() . I18N::getUserDefaultLang() . '/');
});

// Home Page
$app->get('/:lang', function($lang) use ($app) {
    // Load JSON Language File
    I18N::langFile('home-page', $lang);

    // Load Sample Code based on the selected language
    $file_path = $app->config['APP_DATA'] . 'sample-code/home-page-{lang}.php';
    $sample_code = I18N::textFile($file_path, $lang);

    // Update sample code to show common [vendor] dir
    // instead of working path for development.
    $sample_code = str_replace('../../../', '../vendor/', $sample_code);

    // Render a PHP Template and return the results
    return $app->render('home-page.php', [
        'nav_active_link' => 'home',
        'sample_code' => $sample_code,
    ]);
});

// Define Several Routes in a loop
//
// These routes simply load a language translation JSON file and template
// based on the page name so they can be created dynamically. Creating only
// a few routes in a loop may not be practical, however it is shown here as
// an example. If a site has many pages that use the same logic to create
// the page then this option can be useful.
//
$pages = ['playground', 'getting-started'];
foreach ($pages as $page) {
    $pattern = '/:lang/' . $page;
    $app->get($pattern, function($lang) use ($app, $page) {
        I18N::langFile($page, $lang);
        $template = $page . '.php';
        $data = ['nav_active_link' => $page];
        return $app->render($template, $data);
    });
}

// Define routes that point to specific Controllers and Methods. The optional
// config option 'controller_root' is used to specify the root class path.
// The two format options are 'class' and 'class.method'. When using only
// class name then the route function [route(), get(), post(), put(), etc]
// will be used for the method name of the matching controller.
//
$app->get('/:lang/quick-reference', 'QuickReference');
$app->get('/:lang/examples', 'Examples');
$app->get('/:lang/documents', 'Documents');
$app->get('/:lang/documents/:page', 'Documents.getDoc');
$app->get('/:lang/api', 'API');
$app->get('/:lang/api/:class', 'API.getClass');
$app->route('/:lang/security-issue', 'SecurityIssue'); // Using [route()] to allow for both GET and POST
$app->get('/downloads/:file', 'Downloads');
$app->get('/site/generate-sitemap', 'Sitemap')->filter('Env.isLocalhost');

// Example of an 500 error page
$app->get('/site/example-error', function() {
    throw new \Exception('Example Error');
});

// Load additional route files if the requested URL matches.
//
// This feature can be used to limit the number of routes that are loaded
// for each request on a site with many pages and allows for code to be
// organized into smaller related files.
//
// When specifying an optional condition (3rd parameter) the file will only
// be loaded if the condition returns [true]. In this example with [sysinfo] routes
// when using the [Env.isLocalhost] middleware function the routes will only be loaded
// if the user is requesting the page from localhost. If the request is coming
// from someone on the internet then a 404 Response 'Page not found' would be returned.
//
$app
    ->mount('/:lang/examples/', 'routes-examples.php')
    ->mount('/sysinfo/', 'routes-sysinfo.php', 'Env.isLocalhost');
