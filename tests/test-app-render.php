<?php
// =====================================================================
// Unit Testing Page
// *) This file is for testing template rendering
// =====================================================================

// A major goal of FastSitePHP is to always provide an error screen for
// errors or exceptions when setup() is called. So if a User-Defined
// PHP Template has an error or a User-Defined Rendering Engine has an
// error then FastSitePHP can fallback to it's default error template.
// Currently all tests in this file are expected to show content except 
// for one URL which causes a White-Screen-of-Death (WSOD):
//   '/custom-error-on-error-page'
// This condition would be rare as it requires a fatal error from
// a custom error template using a custom rendering engine.

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under 
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';    
}

// Make sure display_errors is set to 'on' prior to
// calling app->setup() because the setup() function
// will turn it off. If the line to turn it off is commented
// out then it will cause serveral tests in this file to fail.
ini_set('display_errors', 'on');

// Create and Setup the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// Set defaults for PHP Template Rendering using one header, one footer, one error, and one not-found page.
// Note - [template_dir] needs to use a full path which is set from [__DIR__]. This is because if an error
// happens the current directory would change and the user-defined error template would not be found.
$app->template_dir = __DIR__ . '/views/php/';
$app->header_templates = 'header-1.php';
$app->footer_templates = 'footer-1.php';
$app->error_template = 'error-1.php';
$app->not_found_template = 'not-found-1.php';

// Define local variables used in the header/footer templates.
// The [&] character will end up being escaped for HTML as [&amp;] 
// when used in these templates.
$app->locals['header_data'] = 'header&data';
$app->locals['footer_data'] = 'footer&data';

// -----------------------------------------------------------
// Define a Simple Template Engine to Find/Replace Text
// -----------------------------------------------------------

// This View Engine is using settings passed from the App, if using a real view engine
// such as Mustache or Twig the config settings would likely be defined in engine.
$search_replace_view_engine = function($files, array $data = null) use ($app) {
    // Build File List
    $templates = array_merge((array)$app->header_templates, (array)$files, (array)$app->footer_templates);
    $template_files = array();

    // Validate each template file and build a new array of full file paths
    foreach ($templates as $template_file) {
        // Cast template_dir to '' if null, this allows
        // for full file paths to be specified
        $file_path = (string)$app->template_dir . $template_file;

        // Make sure the file exists
        if (!is_file($file_path)) {
            throw new \Exception('Template file was not found: ' . $file_path);
        }

        // Add file to the new array
        $template_files[] = $file_path;
    }

    // First build a string with the contents of all files
    $response = '';
    foreach ($template_files as $template_file) {
        $response .= file_get_contents($template_file);
    }

    // Use a Regular Expression Pattern to search for data in the format of '{{text}}' or 
    // '{{text()}}' (mustache/handlebars format) and if a function [ending with ()] then handle
    // the function call otherwise if a variable is defined then replace the string with the
    // value of the variable or leave the text as-is if no variable is defined.
    return preg_replace_callback('/\{\{(\w+)\}\}|\{\{(\w+)\(\)\}\}/', function (array $matches) use ($data) {
        // Make sure that FastSitePHP\Application is passed in the $data property
        $app = $data['app'];
        if (!(is_object($app) && $app instanceof \FastSitePHP\Application)) {
            throw new \Exception('Variable [app] is not defined as FastSitePHP\Application');
        }

        // First look for defined functions and handle them if found
        switch ($matches[0]) {
            case '{{errorMessage()}}':
                return (isset($data['e']) ? $app->escape($data['e']->getMessage()) : $matches[0]);
                break;
            case '{{exception()}}':
                throw new \Exception('Exception Test from engine([search_replace])');
                break;
            case '{{error()}}':
                $test = new UnknownObject();
                break;
        }

        // If the variable is defined then replace the text with the escaped variable value
        // otherwise return the text as-is.
        return (isset($data[$matches[1]]) ? $app->escape($data[$matches[1]]) : $matches[0]);
    }, $response);
};

// -----------------------------------------------------------
// Application Events
// -----------------------------------------------------------

// URL's to test based on code defined in before() callback function
//  /php-404
//  /file-404
//  /custom-404
//
//  /php-missing-not-found
//  /file-missing-not-found
//  /custom-missing-not-found
//
//  /php-multiple-not-found
//  /file-multiple-not-found
//  /custom-multiple-not-found
//
//  /php-error-with-not-found
//  /custom-error-with-not-found
//  /custom-exception-with-not-found
//
//  /php-404-custom-text
//  /custom-404-custom-text
//
//  /php-405-custom-text
//  /custom-405-custom-text

// This function gets called prior to routes being matched or called
$app->before(function() use ($app, $search_replace_view_engine) {
    // Set options for specific routes beginning with '/php'
    if (strpos($app->requestedPath(), '/php') === 0) {
        switch ($app->requestedPath()) {
            // Not-found Template is missing so this will show the default error template
            case '/php-missing-not-found':
                $app->not_found_template = 'missing-not-found.php';
                break;
            // Multiple not-found templates
            case '/php-multiple-not-found':
                $app->not_found_template = array('not-found-1.php', 'not-found-2.php');
                break;
            // The URL '/php-error-with-not-found' is not defined so it will trigger a 
            // 404 Response however just like '/php-error-on-render-2' a required local 
            // variable is being cleared so there will be an error with the template 
            // and instead of a 404 response this should send a 500 response.
            case '/php-error-with-not-found':
                unset($app->locals['footer_data']);
                break;
            // Verfiy custom 404 page title and message
            case '/php-404-custom-text':
                $app->not_found_page_title = '404 Page Not Found Custom';
                $app->not_found_page_message = 'The page you requested does not exist.';
                break;
            // Verfiy custom 405 page title and message
            case '/php-405-custom-text':
                $app->method_not_allowed_title = '405 Method Not Allowed Custom';
                $app->method_not_allowed_message = '[Request: {method}] [Allowed: {allowed_methods}]';
                break;
        }
    // Change templates and type to plain HTML files if the requested URL begins with '/file'
    } elseif  (strpos($app->requestedPath(), '/file') === 0) {
        $app->template_dir = __DIR__ . '/views/file/';
        $app->header_templates = 'header-1.htm';
        $app->footer_templates = 'footer-1.htm';
        $app->error_template = 'error-1.htm';
        $app->not_found_template = 'not-found-1.htm';

        switch ($app->requestedPath()) {
            // Not-found Template is missing so this will show the default error template
            case '/file-missing-not-found':
                $app->not_found_template = 'missing-not-found.htm';
                break;
            // Multiple not-found templates
            case '/file-multiple-not-found':
                $app->not_found_template = array('not-found-1.htm', 'not-found-2.htm');
                break;
        }
    // Change to the Custom engine() template if the 
    // requested URL begins with '/custom'
    } elseif (strpos($app->requestedPath(), '/custom') === 0) {
        $app->engine($search_replace_view_engine);
        $app->template_dir = __DIR__ . '/views/custom/';
        $app->header_templates = 'header-1.txt';
        $app->footer_templates = 'footer-1.txt';
        $app->error_template = 'error-1.txt';
        $app->not_found_template = 'not-found-1.txt';
        
        switch ($app->requestedPath()) {
            // Not-found Template is missing so this will show the default error template
            case '/custom-missing-not-found':
                $app->not_found_template = 'missing-not-found.txt';
                break;
            // Multiple not-found templates
            case '/custom-multiple-not-found':
                $app->not_found_template = array('not-found-1.txt', 'not-found-2.txt');
                break;
            // This template will trigger an error in the not found template
            // and result in the text error template showing
            case '/custom-error-with-not-found':
                $app->not_found_template = 'not-found-error.txt';
                break;
            // This should trigger the default error page with the reason being
            // that if an exception is caught from a not-missing or error template
            // it will show the default error page. It would be unlikely for an
            // one of these templates to throw an exception, rather raising an error
            // would be more common in PHP 5.
            case '/custom-exception-with-not-found':
                $app->not_found_template = 'not-found-exception.txt';
                break;
            // Verfiy custom 404 page title and message
            case '/custom-404-custom-text':
                $app->not_found_page_title = '404 Page Not Found Custom';
                $app->not_found_page_message = 'The page you requested does not exist.';
                break;
            // Verfiy custom 405 page title and message
            case '/custom-405-custom-text':
                $app->method_not_allowed_title = '405 Method Not Allowed Custom';
                $app->method_not_allowed_message = '[Request: {method}] [Allowed: {allowed_methods}]';
                break;
        }
    }
});

// ---------------------------------------------------------------------------------------------
// Define Routes
// *) All routes starting with [php-] render PHP Templates, routes starting with [file-]
//    render HTML Templates, and routes starting with [custom-] use the custom rendering
//    engine defined from the engine() function.
// *) Routes are grouped together when a similar test exists for multiple template types.
// *) HTML Templates do not use variables so the render() function is
//    called without the $data parameter for tests using HTML Templates.
// ---------------------------------------------------------------------------------------------

// ----------------------------------------------------------------
// Return a Rendered Template including Header/Footer Templates
// ----------------------------------------------------------------

$app->get('/php-template-one-page-header-footer', function() use ($app) {
    return $app->render('page-1.php', array(
        'page1_data' => 'page-1',
    ));
});

$app->get('/file-template-one-page-header-footer', function() use ($app) {
    return $app->render('page-1.htm');
});

$app->get('/custom-template-one-page-header-footer', function() use ($app) {
    return $app->render('page-1.txt', array(
        'page1_data' => 'page-1',
    ));
});

// ----------------------------------------------------------------
// Return a Multiple Rendered Templates including Multiple
// Header/Footer Templates
// ----------------------------------------------------------------

$app->get('/php-template-multiple-pages', function() use ($app) {
    // Set multiple header and footer files
    $app->header_templates = array('header-1.php', 'header-2.php');
    $app->footer_templates = array('footer-1.php', 'footer-2.php');

    // Return the rendered template using an array of multiple pages
    return $app->render(array('page-1.php', 'page-2.php'), array(
        'page1_data' => 'page-1',
        'page2_data' => 'page-2',
    ));
});

$app->get('/file-template-multiple-pages', function() use ($app) {
    $app->header_templates = array('header-1.htm', 'header-2.htm');
    $app->footer_templates = array('footer-1.htm', 'footer-2.htm');

    return $app->render(array('page-1.htm', 'page-2.htm'));
});

$app->get('/custom-template-multiple-pages', function() use ($app) {
    $app->header_templates = array('header-1.txt', 'header-2.txt');
    $app->footer_templates = array('footer-1.txt', 'footer-2.txt');

    return $app->render(array('page-1.txt', 'page-2.txt'), array(
        'page1_data' => 'page-1',
        'page2_data' => 'page-2',
    ));
});

// ----------------------------------------------------------------
// Return a single rendered Template File
// ----------------------------------------------------------------

$app->get('/php-template-one-page', function() use ($app) {
    // Clear header/footer template
    $app->header_templates = null;
    $app->footer_templates = null;

    // Render the page
    return $app->render('page-1.php', array(
        'page1_data' => 'page-1',
    ));
});

$app->get('/file-template-one-page', function() use ($app) {
    $app->header_templates = null;
    $app->footer_templates = null;

    return $app->render('page-1.htm');
});

$app->get('/custom-template-one-page', function() use ($app) {
    $app->header_templates = null;
    $app->footer_templates = null;

    return $app->render('page-1.txt', array(
        'page1_data' => 'page-1',
    ));
});

// ----------------------------------------------------------------
// Throw an Exception to view the User-Defined Error Page
// ----------------------------------------------------------------

$app->get('/php-exception', function() use ($app) {
    throw new \Exception('PHP Exception Test');
});

$app->get('/file-exception', function() use ($app) {
    throw new \Exception('HTML Error Test');
});

$app->get('/custom-exception', function() use ($app) {
    throw new \Exception('Text Error Test');
});

// ----------------------------------------------------------------
// Raise an Error to view the PHP User-Defined Error Page
// ----------------------------------------------------------------

$app->get('/php-error', function() use ($app) {
    $test = new UnknownObject();
});

$app->get('/file-error', function() use ($app) {
    $test = new UnknownObject();
});

$app->get('/custom-error', function() use ($app) {
    $test = new UnknownObject();
});

// ----------------------------------------------------------------
// Test Custom Error Messages.
// ----------------------------------------------------------------

$app->get('/php-error-custom-message', function() use ($app) {
    $app->error_page_title = '500 Error Page';
    $app->error_page_message = 'Error Page Custom Message';
    throw new \Exception('Error Test with Custom Message');
});

$app->get('/custom-error-custom-message', function() use ($app) {
    $app->error_page_title = '500 Error Page';
    $app->error_page_message = 'Error Page Custom Message';
    throw new \Exception('Error Test with Custom Message');
});

// ----------------------------------------------------------------
// Define multiple Error Template Files then Throw an Exception 
// to view the User-Defined Error Page with the defined templates
// ----------------------------------------------------------------

$app->get('/php-multiple-error-templates', function() use ($app) {
    $app->error_template = array('error-1.php', 'error-2.php');
    throw new \Exception('PHP Multiple Error Pages Test');
});

$app->get('/file-multiple-error-templates', function() use ($app) {
    $app->error_template = array('error-1.htm', 'error-2.htm');
    throw new \Exception('HTML Multiple Error Pages Test');
});

$app->get('/custom-multiple-error-templates', function() use ($app) {
    $app->error_template = array('error-1.txt', 'error-2.txt');
    throw new \Exception('Text Multiple Error Pages Test');
});

// ------------------------------------------------------------------------------
// Test for a missing template, this will show the User-Defined Error Page
// ------------------------------------------------------------------------------

$app->get('/php-missing-page', function() use ($app) {
    return $app->render('missing-page.php');
});

$app->get('/file-missing-page', function() use ($app) {
    return $app->render('missing-page.htm');
});

$app->get('/custom-missing-page', function() use ($app) {
    return $app->render('missing-page.txt');
});

// ------------------------------------------------------------------------------
// Test for a missing error template and a missing page template. 
// These tests first assign a missing error template so when actual render()
// call fails and would normally show the user-defined error file it won't be
// found so the default PHP Error Template will be displayed
// ------------------------------------------------------------------------------

$app->get('/php-missing-error-page', function() use ($app) {
    $app->error_template = 'missing-error-page.php';
    return $app->render('missing-page.php');
});

$app->get('/file-missing-error-page', function() use ($app) {
    $app->error_template = 'missing-error-page.htm';
    return $app->render('missing-page.htm');
});

$app->get('/custom-missing-error-page', function() use ($app) {
    $app->error_template = 'missing-error-page.txt';
    return $app->render('missing-page.txt');
});

// ----------------------------------------------------------------
// Calling these pages will trigger an error or throw an exception
// from the render() function.
// ----------------------------------------------------------------

// Return the error template because required variables
// are not defined when the page is rendered.
$app->get('/php-error-on-render-1', function() use ($app) {
    return $app->render('page-1.php');
});

// Similar to above statement but this will return an error with
// the default error template because the locals are cleared which 
// has required variables used in the header and footer templates
$app->get('/php-error-on-render-2', function() use ($app) {
    $app->locals = array();
    return $app->render('page-1.php');
});

// This page will throw an exception
$app->get('/php-exception-on-render', function() use ($app) {
    return $app->render('page-exception.php');
});

// This page will raise an error from the engine() callback
$app->get('/custom-error-on-render', function() use ($app) {
    return $app->render('page-error.txt');
});

// This page will throw an exception from the engine() callback
$app->get('/custom-exception-on-render', function() use ($app) {
    return $app->render('page-exception.txt');
});

// ----------------------------------------------------------------
// Throw Exceptions or Trigger Errors from the Error Page, this
// will result in the Default PHP Error Template being rendered.
// ----------------------------------------------------------------

$app->get('/php-error-on-error-page', function() use ($app) {
    $app->locals = array();
    return $app->render('page-1.php');
});

$app->get('/php-exception-on-error-page', function() use ($app) {
    $app->error_template = 'error-exception.php';
    return $app->render('page-1.php');
});

// This is currently the only test that causes a White-Screen-of-Death (WSOD)
$app->get('/custom-error-on-error-page', function() use ($app) {
    $app->error_template = 'error-error.txt';
    throw new \Exception('Test from [custom-error-on-error-page]');
});

$app->get('/custom-exception-on-error-page', function() use ($app) {
    $app->error_template = 'error-exception.txt';
    throw new \Exception('Test from [custom-exception-on-error-page]');
});

// ----------------------------------------------------------------
// Misc Testing - These tests are only for PHP because the logic
// applies to finding template files before specific rendering
// functions are called.
// ----------------------------------------------------------------

$app->get('/php-template-full-path', function() use ($app) {
    // Set [template_dir] to null and change files to full paths
    $app->template_dir = null;
    $app->header_templates = __DIR__ . '/views/php/header-1.php';
    $app->footer_templates = __DIR__ . '/views/php/footer-1.php';
    $template_file = __DIR__ . '/views/php/page-1.php';

    // Render the page
    return $app->render($template_file, array(
        'page1_data' => 'page-1',
    ));
});

$app->get('/php-dir-format', function() use ($app) {
    // The default template specifies a '/' at the end however this function
    // is testing without it as the render() function handles it.
    $app->template_dir = __DIR__ . '/views/php';

    return $app->render('page-1.php', array(
        'page1_data' => 'page-1',
    ));
});


// ----------------------------------------------------------------
// Misc Testing - These tests are only for PHP because the logic
// applies to finding template files before specific rendering
// functions are called.
// ----------------------------------------------------------------

// This is testing all Exceptions in the render() function other than [Template file was not found:]
// which gets tested many times in other unit tests
$app->get('/error-test-render-function', function() use ($app) {
    // Keep count of errors
    $error_count = 0;

    // Set Templates to null
    $app->header_templates = null;
    $app->footer_templates = null;

    // Check for an error when there are no templates to render
    try {
        $app->render(null);
    } catch (\Exception $e) {
        if ($e->getMessage() === 'The function [FastSitePHP\Application->render()] was called without template file specified to render.') {
            $error_count++;
        } else {
            return 'Failed checking for no templates, message returned: ' . $e->getMessage();
        }
    }

    // Return Result
    return sprintf('Success, Tested for %s Exceptions in the function render()', $error_count);
});

// This is testing all Exceptions in the engine() function
$app->get('/error-test-engine-function', function() use ($app) {
    // Keep count of errors
    $error_count = 0;
    
    // Invalid Closure - Wrong Parameters
    try {
        $app->engine(function() {});
    } catch (\Exception $e) {
        if ($e->getMessage() === 'Wrong number of parameters for the $callback closure definition defined from [FastSitePHP\Application->engine()]. The closure should be defined as [function($file, array $data = null)]') {
            $error_count++;
        } else {
            return 'Failed test 1, message returned: ' . $e->getMessage();
        }
    }

    // Invalid Closure - 1st Parameter - Optional Value
    try {
        $app->engine(function($file = null, $vars = null) {});
    } catch (\Exception $e) {
        if ($e->getMessage() === 'Invalid parameters for the $callback closure definition defined from [FastSitePHP\Application->engine()]. The first parameter was defined as an optional value. The closure should be defined as [function($file, array $data = null)]') {
            $error_count++;
        } else {
            return 'Failed test 2, message returned: ' . $e->getMessage();
        }
    }
    
    // Invalid Closure - 2nd Parameter - Missing Typehint
    try {
        $app->engine(function($file, $data) {});
    } catch (\Exception $e) {
        if ($e->getMessage() === 'Invalid parameters for the $callback closure definition defined from [FastSitePHP\Application->engine()]. The second parameter was not defined with an array typehint. The closure should be defined as [function($file, array $data = null)]') {
            $error_count++;
        } else {
            return 'Failed test 3, message returned: ' . $e->getMessage();
        }
    }

    // Invalid Closure - 2nd Parameter - Missing Optional
    try {
        $app->engine(function($file, array $data) {});
    } catch (\Exception $e) {
        if ($e->getMessage() === 'Invalid parameters for the $callback closure definition defined from [FastSitePHP\Application->engine()]. The second parameter was not defined as an optional value. The closure should be defined as [function($file, array $data = null)]') {
            $error_count++;
        } else {
            return 'Failed test 4, message returned: ' . $e->getMessage();
        }
    }

    // These should work
    $engines_added = 0;

    $app->engine(function(array $files, array $data = null) {});
    $engines_added++;

    $app->engine(function($file, array $vars = null) {});
    $engines_added++;

    // Return Result
    return sprintf('Success, Tested for %d Exceptions in the function engine() and added %d rendering engines', $error_count, $engines_added);
});

// This is testing errors related to setting invalid properties prior to calling render()
$app->get('/error-test-render-properties', function() use ($app) {
    // Keep count of errors
    $error_count = 0;

    // Save a copy of the original locals value then set it to null
    $locals = $app->locals;
    $app->locals = null;

    // Changed Locals to null from Array
    try {
        $app->render('page-1.php', array(
            'page1_data' => 'page-1',
        ));
    } catch (\Exception $e) {
        if ($e->getMessage() === 'extract() expects parameter 1 to be array, null given') {
            $error_count++;
        } else {
            return 'Failed Test 1, message returned: ' . $e->getMessage();
        }
    }

    // Change the locals back
    $app->locals = $locals;

    // Call render with a string instead of array for $data
    try {
        $app->render('page-1.php', 'abc');
    } catch (\Exception $e) {
        // PHP 5
        // Look for 'array' in the error message, the actual message 
        // will vary depending upon the version of PHP
        if (strpos($e->getMessage(), 'array') !== false) {
            $error_count++;
        } else {
            return 'Failed Test 2, message returned: ' . $e->getMessage();
        }
    } catch (\Throwable $e) {
        // PHP 7
        if (strpos($e->getMessage(), 'array') !== false) {
            $error_count++;
        } else {
            return 'Failed Test 2, message returned: ' . $e->getMessage();
        }
    }

    // Set [header_templates] to a non-string value
    try {
        $app->header_templates = 0;
        $app->render('page-1.php', array(
            'page1_data' => 'page-1',
        ));
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Template file was not found: ') === 0 && strpos($e->getMessage(), '/views/php/0') !== false) {
            $error_count++;
        } else {
            return 'Failed Test 3, message returned: ' . $e->getMessage();
        }
    }

    // Return Result
    return sprintf('Success, Tested for %s Exceptions for Properties when calling render()', $error_count);
});


// ------------------------------------------------------------------------------
// Test the pageNotFound() Method
// pageNotFound() sets statusCode = 404 and content-type so first
// make sure contentType() is set to another value.
// ------------------------------------------------------------------------------

$app->get('/php-page-not-found', function() use ($app) {
    $app->header('Content-Type', 'text/plain');
    return $app->pageNotFound();
});

$app->get('/file-page-not-found', function() use ($app) {
    $app->header('Content-Type', 'text/plain');
    return $app->pageNotFound();
});

$app->get('/custom-page-not-found', function() use ($app) {
    $app->header('Content-Type', 'text/plain');
    return $app->pageNotFound();
});

// ------------------------------------------------------------------------------
// Test the pageNotFound() Method with custom title/message
// ------------------------------------------------------------------------------

$app->get('/php-page-not-found-custom', function() use ($app) {
    $app->not_found_page_title = '404';
    $app->not_found_page_message = 'Page missing';
    return $app->pageNotFound();
});

$app->get('/custom-page-not-found-custom', function() use ($app) {
    $app->not_found_page_title = '404';
    $app->not_found_page_message = 'Page missing';
    return $app->pageNotFound();
});

// ------------------------------------------------------------------------------
// Test the pageNotFound() Method without a [not_found_template]
// The default not found PHP template should be returned for these routes.
// ------------------------------------------------------------------------------

$app->get('/php-default-page-not-found', function() use ($app) {
    $app->not_found_template = null;
    return $app->pageNotFound();
});

$app->get('/file-default-page-not-found', function() use ($app) {
    $app->not_found_template = null;
    return $app->pageNotFound();
});

$app->get('/custom-default-page-not-found', function() use ($app) {
    $app->not_found_template = null;
    return $app->pageNotFound();
});

// ------------------------------------------------------------------------------
// Testing of 405 'Method Not Allowed' Responses
// For these routes the return value will be the [not_found_template]
// Template with text from [method_not_allowed_title]
// and [method_not_allowed_message]
// ------------------------------------------------------------------------------

$app->post('/php-405', function() {
    return 'php-405';
});

$app->post('/file-405', function() {
    return 'file-405';
});

$app->post('/custom-405', function() {
    return 'custom-405';
});

// ------------------------------------------------------------------------------
// Testing of 405 'Method Not Allowed' Responses with a custom
// title and message which is defined near the top of this file
// in a before() function.
// ------------------------------------------------------------------------------

$app->post('/php-405-custom-text', function() {
    return 'php-405-custom-text';
});

$app->post('/custom-405-custom-text', function() {
    return 'custom-405-custom-text';
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
