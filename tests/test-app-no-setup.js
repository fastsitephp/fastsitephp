/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Application Object - Error Test - Exception Raised with No Error Handling Set", "test-app-no-setup.php/exception", {
        status: 500,
        response: ""
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_ERROR with No Error Handling Set", "test-app-no-setup.php/error-fatal", {
        status: 500,
        response: ""
    });

    runHttpUnitTest("Application Object - Routing - Testing routeMatches() Error with No Error Handling Set", "test-app-no-setup.php/route-matches-param-error", {
        response: "Error with param([:regex_invalid]), the regular expression [ABC] is not valid for the PHP function preg_match(). Error message from PHP: preg_match(): Delimiter must not be alphanumeric or backslash"
    });

    runHttpUnitTest("Application Object - Routing - Testing routeMatches() Custom Error with No Error Handling Set", "test-app-no-setup.php/route-matches-custom-error", {
        response: "Error with param([:regex_invalid]), the regular expression [ABC] is not valid for the PHP function preg_match(). Specific error message from [preg_match()] cannot be obtained because a function defined by this site for [set_error_handler()] did not return false."
    });

    // Even though the default 500 page won't show the application should still handle 404 pages
    // using the default template when setup() is not called.
    runHttpUnitTest("Application Object - Missing Page with No Error Handling Set", "test-app-no-setup.php/404", {
        status: 404,
        responseContains: [
            "Page Not Found</h1>",
            "The requested page could not be found.</div>"
        ]
    });

})();
