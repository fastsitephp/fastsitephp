/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode

    // These routes for testing mount() are also tested with a different
    // option from the file [test-app.js]

    runHttpUnitTest("Application Object - URL Info - File 2 - Lower-case URL with [case_sensitive_urls=false]", "test-app-url-case.php/get-url", {
        response: "/get-url"
    });

    runHttpUnitTest("Application Object - URL Info - File 2 - Upper-case URL with [case_sensitive_urls=false]", "test-app-url-case.php/GET-URL", {
        response: "/GET-URL"
    });

    runHttpUnitTest("Application Object - Mount Testing 1 - mount() with [case_sensitive_urls=false]", "test-app-url-case.php/mount/test", {
        response: "/mount/test"
    });

    runHttpUnitTest("Application Object - Mount Testing 2 - mount() with [case_sensitive_urls=false]", "test-app-url-case.php/MOUNT/TEST", {
        response: "/MOUNT/TEST"
    });
})();
