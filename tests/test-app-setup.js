/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode

    runHttpUnitTest("Application Object - Setup - Not Called", "test-app-setup.php/setup-not-called", {
        type: "text",
        response: "America/New_York"
    });

    runHttpUnitTest("Application Object - Setup - UTC Timezone", "test-app-setup.php/setup-utc", {
        type: "text",
        response: "UTC"
    });

    runHttpUnitTest("Application Object - Setup - Timezone using [php.ini]", "test-app-setup.php/setup-phpini", {
        type: "text",
        response: "America/Los_Angeles"
    });

    runHttpUnitTest("Application Object - Setup - Timezone missing from [php.ini]", "test-app-setup.php/setup-phpini-error", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">Exception</td>',
                '<td class="error-type">ErrorException</td>',
            ],
            [
                '<td class="error-message">The settings [date.timezone] is not setup in [php.ini], it must be defined when using calling setup([date.timezone]) or setup() must be called with a valid timezone instead.</td>',
                '<td class="error-message">ini_set(): Invalid date.timezone value &#039;&#039;, using &#039;UTC&#039; instead</td>',
            ],
            [
                "<td>{closure}</td>",
                "<td>shutdown</td>",
            ],
        ],
        responseExcludes: "[Should not show on unit test]"
    });

    runHttpUnitTest("Application Object - Setup - Invalid Timezone", "test-app-setup.php/setup-timezone-error", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">8 (E_NOTICE)</td></tr>',
            '<td class="error-message">date_default_timezone_set(): Timezone ID &#039;abc123&#039; is invalid</td>',
            "<td>errorHandler</td>",
            "<td>setup</td>"
        ],
        responseExcludes: "[Should not show on unit test]"
    });

    runHttpUnitTest("Application Object - Setup - Timezone set as null", "test-app-setup.php/setup-null", {
        type: "text",
        response: "America/New_York"
    });

    // In the [responseContains] nested array's the first line is
    // for PHP 5.* and PHP 7.0 and second line is for PHP 7.1
    runHttpUnitTest("Application Object - Setup - Timezone missing from [php.ini]", "test-app-setup.php/setup-missing", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">ErrorException</td>',
                '<td class="error-type">ArgumentCountError</td>',
            ],
            [
                '<td class="error-severity">8 (E_WARNING)</td>',
                '<td class="error-code">0</td></tr>',
            ],
            [
                '<td class="error-message">Missing argument 1 for FastSitePHP\\Application::setup(), called in ',
                '<td class="error-message">Too few arguments to function FastSitePHP\\Application::setup(), 0 passed in ',
            ],
            [
                "<td>shutdown</td>",
                "<td>setup</td>"
            ]
        ],
        responseExcludes: "[Should not show on unit test]"
    });

    runHttpUnitTest("Application Object - Setup - setup() called multiple times", "test-app-setup.php/setup-multiple", {
        type: "text",
        response: "America/Los_Angeles"
    });

    runHttpUnitTest("Application Object - Setup - setup() settings", "test-app-setup.php/setup-settings", {
        response: {
            error_reporting: -1,
            display_errors: "off"
        }
    });    
    
})();
