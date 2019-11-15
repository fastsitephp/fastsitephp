<?php
// ==================================================================
// Unit Testing Page
// *) This file is for Event Testing, it uses only core
//    Framework files and uses Framework Error Handling
// ==================================================================

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

// Create and Setup the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// General Classes and Functions used for Testing
// -----------------------------------------------------------

// Using a simple custom response class so that this page
// does not need to load the actual [Web\Response] class.
class CustomResponse
{
    public $content = null;

    public function send()
    {
        echo $this->content;
    }
}

// -----------------------------------------------------------
// Application Events
// -----------------------------------------------------------
// There are 5 Application callback events:
//   before(), beforeSend(), after(), notFound(), and error().
// Additional content can be sent to the client in most cases after run() is called. 
// Events are executed in the order called so two functions are added for each event
// and logic is used to output specific content that shows the event order. 
// The unit testing web page then verifies the content is correct.

// -----------------------------------------------------------------------------------------------------
// [before()] events are called from the [run()] function prior to any routes being matched. 
// These functions return now response so for unit testing they are creating dynamic app properties
// which are later checked in specific routes.

$app->before(function() use ($app) {
    $app->before_prop_1 = '[before1]';

    // This route is never defined but because the URL is handled in a before()
    // function it should return a 500 response rather than a 404 not found response.
    // This should show the standard error page with one error
    if ($app->requestedPath() === '/error-in-before') {
        throw new \Exception('Error in before() event');
    }
});

$app->before(function() use ($app) {
    $app->before_prop_2 = '[before2]';
});

// -----------------------------------------------------------------------------------------------------
// [notFound()] events if not initial routes are matched and they can return a response.
// There are three specific unit tests called from the client page for this:
// 1) '/not-found-1' - this tests returns content in the first function so beforeSend() and after() functions get called
// 2) '/not-found-2' - same as first call but the first function is skipped
// 3) '/not-found-3' - This echo's text so the response is valid but beforeSend() is never called

$app->notFound(function() use ($app) {
    switch ($app->requestedPath()) {
        case '/not-found-1':
            return '[notFound1]';
        case '/not-found-3':
            echo '[notFound3]';
            break;
        case '/error-in-not-found':
            throw new \Exception('Error in notFound() event');
            break;
    }
});

$app->notFound(function() use ($app) {
    if ($app->requestedPath() === '/not-found-2') {
        return '[notFound2]';
    }
});

// -----------------------------------------------------------------------------------------------------
// Define error events that only get called when a 500 error occurs. These events
// are used for logging errors as they cannot modify the existing response. However
// to handle a response without sending the error page the exit() function can be used
// as shown below. [error()] events do not trigger beforeSend() or notFound() but do 
// end up calling after() functions. Code cannot be called after run() when an error occurs.

$app->error(function($response_code, \Exception $e) use ($app) {
    switch ($app->requestedPath()) {
        case '/error-with-events':
            $app->error_data = sprintf('[error1:[%d]:[%s]]', $response_code, $e->getMessage());
            break;
        case '/error-with-exit':
            header('Content-Type: text/plain');
            echo $e->getMessage();
            exit();
            break;
        case '/error-in-error':
            // Raise Exception with specific URL
            throw new \Exception('Error in Error - error()');
    }
});

$app->error(function() use ($app) {
    if ($app->requestedPath() === '/error-with-events') {
        $app->error_data .= '[error2]';
    }
});

// -----------------------------------------------------------------------------------------------------
// [beforeSend()] get called only when a route is matched (either from a routing call or a [notFound()]
// function. [beforeSend()] can return a response.

$app->beforeSend(function($content) use ($app) {
    // Change Response Type
    if ($app->requestedPath() !== '/error-in-after') {
        $app->header('Content-Type', 'text/plain');
    }

    // Raise Exception with specific URL
    if ($app->requestedPath() === '/error-in-before-send') {
        throw new \Exception('Error in beforeSend() event');
    }

    // Modify and Return the Response Object
    if ($app->requestedPath() === '/response-object-events') {
        $content->content .= '[beforeSend(' . get_class($content) . ')]';
        return $content;
    }

    // Modify and return the response content
    $content = str_replace('event-test-', 'updated-event-test-', $content);
    return $content . '[beforeSend1]';
});

$app->beforeSend(function($content) use ($app) {
    // Return null with specific URL
    if ($app->requestedPath() === '/before-send-return-null') {
        return null;
    }

    // Modify and Return the Response Object
    if ($app->requestedPath() === '/response-object-events') {
        return $content;
    }

    // Return existing content with the text appended to it
    return $content . '[beforeSend2]';
});

// -----------------------------------------------------------------------------------------------------
// after() Events should always get called, this includes 404 and 500 pages

$app->after(function($content) use ($app) {
    // Get the Requested URL
    $requested_path = $app->requestedPath();

    // Output the Class of the Response Object and Exit
    if ($requested_path === '/response-object-events') {
        echo '[after(' . get_class($content) . ')]';
        exit();
    }

    // Output the variable type of the $content parameter
    if ($requested_path === '/manual-send') {
        echo '[after(' . gettype($content) . ')]';
    }

    // Make sure this gets called from an OPTIONS request.
    // Specifiy additional headers as OPTIONS request do
    // not include content.
    if ($requested_path === '/options-request-after-event') {
        header('X-Content-Type: ' . gettype($content));
        header('X-After-Event: true');
        exit();
    }

    // Check for data in route [event-test-1], if found then add text to the response
    if (strpos($content, '[updated-event-test-') !== false) {
        echo '[expected $content found]';
    }

    // Add Content
    echo '[after1]';

    // Raise Exception with specific URL
    if ($requested_path === '/error-in-after') {
        throw new \Exception('Error in after() event');
    }

    // Start a Session and Define a Session Variable that
    // will be checked once the redirected route is called.
    if ($requested_path === '/redirect-with-after') {
        session_start();
        $_SESSION['Redirect-Event'] = 'Called after() from: [/redirect-with-after]';
    }
});

$app->after(function() use ($app) {
    // Add Content
    echo '[after2]';

    // Add Content if a specific property is defined for an error response
    if (isset($app->error_data) && $app->statusCode() === 500) {
        echo $app->error_data;
    }
});

// -----------------------------------------------------------
// Define Routes
// -----------------------------------------------------------

// Standard route with no errors that should handle all before(), beforeSend(), and after() events and allow for
// additional output after run() is called
$app->get('/event-test-1', function() use ($app) {
    // If defined add properties created by the before() functions.
    // The first two should exist and the third item should not.
    $content = (property_exists($app, 'before_prop_1') ? $app->before_prop_1 : '[before_prop_1 does not exist]');
    $content .= (property_exists($app, 'before_prop_2') ? $app->before_prop_2 : '[before_prop_2 does not exist]');
    $content .= (property_exists($app, 'before_prop_3') ? $app->before_prop_3 : '[before_prop_3 does not exist]');
    $content .= '[event-test-1]';

    // Return the content (the default 'Content-Type' at this point will be 'text/html'
    return $content;
});

// Output the response with an echo statement, beforeSend() events should not get called for this route
$app->get('/echo-test-1', function() use ($app) {
    // If defined add properties created by the before() functions.
    // The first two should exist and the third item should not.
    $content = (property_exists($app, 'before_prop_1') ? $app->before_prop_1 : '[before_prop_1 does not exist]');
    $content .= (property_exists($app, 'before_prop_2') ? $app->before_prop_2 : '[before_prop_2 does not exist]');
    $content .= (property_exists($app, 'before_prop_3') ? $app->before_prop_3 : '[before_prop_3 does not exist]');
    $content .= '[echo-test-1]';

    // Output the response
    echo $content;
});

// Error Test using the default error template. This test should not call beforeSend() functions 
// but should call after() functions. It also does not allow for output after run() is called.
$app->get('/error', function() use ($app) {
   throw new \Exception('Error Test');
});

// Error Test using with error() events looking for this specific URL and handling it
// This test should not call beforeSend() functions however it does get handled with a
// error() function which sets data to be handled in an after() event.
$app->get('/error-with-events', function() use ($app) {
    throw new \Exception('Error Event Test');
});

// Error Test using with error() events looking for this specific URL and handling it.
// This route should get caught in an error() function and sent a plain text message
// with no data after.
$app->get('/error-with-exit', function() use ($app) {
    throw new \Exception('Error Exit Test');
});

// This route should throw an Exception in a beforeSend() event
$app->get('/error-in-before-send', function() use ($app) {
    return 'error-in-before-send';
});

// This route should throw an Exception in an error() event and
// the Exception here will not show to the client because another
// Exception will be raised after it.
$app->get('/error-in-error', function() use ($app) {
    throw new \Exception('Error in Error - Route');
});

// This route should throw an Exception in an after() event.
// The after() events will get called once so the contents from
// this route and the after() functions should not be included
// in the response to the client.
$app->get('/error-in-after', function() use ($app) {
    return '[Error in After]';
});

// Test each event to see if a parameter other than a Closure can be added. If the Application
// Event System is changed to support both Closure and callable types then this Unit Test must 
// be updated. This test confirms that it will function as expected. The type hint callable is
// supported for PHP 5.4 and above so if support for 5.3 is dropped then this feature can be changed.
// http://php.net/manual/en/functions.anonymous.php
// http://php.net/manual/en/language.types.callable.php
$app->get('/invalid-event-test', function() use ($app) {
    // Define Variables
    $content = '';
    $events = array('before', 'beforeSend', 'after', 'error', 'notFound');

    // Check each event
    foreach ($events as $event) {
        try {
            // Dynamically call the event using a string as the parameter.
            // With regular PHP code this would look like [$app->before('');]
            call_user_func(array($app, $event), '');
            $content .= '[' . $event . ' Event should not have been added]';
        } catch (\ErrorException $e) {
            // PHP 5
            // The error message comes from PHP and should be in the contain text similar to the following:
            //   "Argument 1 passed to FastSitePHP\Application::before() must be an instance of Closure, string given, called in ..."
            // If it doesn't then the result is not expected.
            if (strpos($e->getMessage(), 'Argument 1 passed to FastSitePHP\Application::' . $event . '() must be an instance of Closure, string given') === 0) {
                $content .= '[' . $event . '() Expected ErrorException Message]';
            } else {
                $content .= '[' . $event . '() Unexpected ErrorException: ' . $e->getMessage() . ']';
            }
        } catch (\TypeError $e) {
            // PHP 7
            if (strpos($e->getMessage(), 'Argument 1 passed to FastSitePHP\Application::' . $event . '() must be an instance of Closure, string given') === 0) {
                $content .= '[' . $event . '() Expected ErrorException Message]';
            } else {
                $content .= '[' . $event . '() Unexpected ErrorException: ' . $e->getMessage() . ']';
            }
        } catch (\Exception $e) {
            $content .= '[' . $event . '() Unexpected Exception: ' . $e->getMessage() . ']';
        }
    }

    // Return the content
    return $content;
});

// Echo output then return output.
$app->get('/mixed-response', function() use ($app) {
    
    // If the [php.ini] setting [output_buffering] is turned off then
    // ob_get_length() will return false. If so then for this unit
    // test to succeed on the client page then header() function needs
    // to be called before echo as the client page expects plain text
    // which is defined in a [$app->before()] callback function near
    // the top of this page. If [output_buffering] is turned on then
    // [ob_get_length()] will return 0 and even though output is sent
    // before the header it will result in the correct type because
    // it is saved in the buffer before being sent so the header ends
    // up being sent first.
    if (ob_get_length() === false) {
		header('Content-Type: text/plain');
    }

    echo '[Echo from Route]';
    return '[Return from Route]';
});

// Output a header, content and then flush the output buffer.
// Then return a response. Because headers will have already have
// been sent the content type will not be changed to text. This
// verifies the line [if (!headers_sent()) {] in the sendResponse()
// function.
$app->get('/mixed-response-2', function() use ($app) {
    header('Content-Type: text/html');
    echo '[Echo from Route 2]';

    // If the [php.ini] setting [output_buffering] is turned off then
    // ob_get_length() will return false. If so that means the header
    // and the content have already been sent and there is no need to 
    // send the output buffer. If [output_buffering] is turned on as 
    // would be expected in most environments then call [ob_flush()].
    if (ob_get_length() !== false) {
		ob_flush();
    }
    
    return '[Return from Route]';
});

// Similar to the above statement but with an error. Because headers
// will have already been sent the response status code will be 200
// rather than 500 for an error, content from this route will be
// included, and the standard error page will show after the content.
$app->get('/error-after-output', function() use ($app) {
    header('Content-Type: text/html');
    echo '[Test: error-after-output]';
    
    // See comments above in [/mixed-response-2]
    if (ob_get_length() !== false) {
        ob_flush();
    }
    
    throw new \Exception('Error after Output Test');
});

// The original route returns data but beforeSend() returns null for this
// so it will throw the exception: 'Route [%s %s] was matched however ...'
$app->get('/before-send-return-null', function() use ($app) {
    return '[before-send-return-null]';
});

// Manually call runAfterEvents() then stop the PHP Script
$app->get('/after-called-manully', function() use ($app) {
    $app->runAfterEvents(null);
    exit();
});

// Verify that redirect() triggers after() functions.
// This works by setting a session variable in the after()
$app->get('/redirect-with-after', function() use ($app) {
    $app->redirect('redirected-with-after');
});

// Send Data defined from the after() function for the above
// route '/redirect-with-after'.
$app->get('/redirected-with-after', function() use ($app) {
    // This works by first starting the session so that [$_SESSION]
    // is populated, then the sesssion is immediately cleared, however
    // any values in [$_SESSION] can still be read for the current request.
    session_start();
    session_destroy();
    echo $_SESSION['Redirect-Event'];
    exit();
});

// Make sure that when an Object is used as the Response (in this case a CustomResponse defined 
// near the top of this page) that beforeSend() and after() accept and return the Response Object
// in the event functions.
$app->get('/response-object-events', function() use ($app) {
    $res = new CustomResponse();
    $res->content = '[Testing with a Response Object: ' . get_class($res) . ']';
    return $res;
});

// Make sure that an OPTIONS request triggers after() functions
$app->get('/options-request-after-event', function() use ($app) {
    return 'options-request-after-event';
});

// Manually call the send function rather than returning the Custom Response Object.
// This is to make sure that a valid route without a return value still triggers
// the after() functions
$app->get('/manual-send', function() {
    $res = new CustomResponse();
    $res->content = '<h1>Calling send()</h1>';
    $res->send();
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();

// This called for most responses. However if a response terminates output
// by calling the exit() statement then this won't get called. Currently both
// 404 and 500 responses call exit() so this should not be included on those responses.
echo '[After-Run]';
