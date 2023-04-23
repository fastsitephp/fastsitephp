/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest, rootDir, showServerConfigError */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode

    // This is the first test that runs, is calls a web service which checks
    // the Server settings and configuration for issues that might affect
    // some of the unit tests. If the Server is configured in a manner that
    // is known to trigger errors then the callback function will call
    // [showServerConfigError] which clearly shows a list of errors above
    // the section where QUnit is displayed. There are two Server
    // configuration tests, this Unit Test and the one below.
    runHttpUnitTest("Check Server Configuration", "test-app.php/check-server-config", {
        type: "json",
        response: {
            settingsAreValid: true,
            errors: []
        },
        callback: function (xhr) {
	        try {
                var result = JSON.parse(xhr.responseText);
                if (result.settingsAreValid !== true) {
	                showServerConfigError(result.errors);
	            }
	        } catch (e) {
		        console.log("Error on [Check Server Configuration]:");
		        console.log(e);
		        showServerConfigError(null);
	        }
        }
    });

    // Send an OPTIONS request to see what the Web Server allows as Request Methods. If the Web Server
    // doesn't return the expected result then assume that the options will not work and clearly
    // show an error above all QUnit Tests as this error will cause many Unit Tests to fail.
    // This is the 2nd and final Server Configuration Unit Test.
    runHttpUnitTest("Check Server Allowed Request Options", "test-app.php/check-server-options", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "DELETE, OPTIONS, PATCH, PUT" }
        ],
        callback: function (xhr) {
	        try {
                var allowedOptions = xhr.getResponseHeader("Allow");
                if (xhr.status !== 200 || allowedOptions !== "DELETE, OPTIONS, PATCH, PUT") {
	                showServerConfigError([
	                	"This web server does not allow on or more of the following request methods [DELETE, OPTIONS, PATCH, PUT]. If your site only uses GET and POST methods then this error will likely not impact it however if you require additional methods or Cross-Origin Resource Sharing (CORS) support then your server settings will likely need to be updated. This error will cause many unit tests to fail. To enable these features you may want to consider researching your specific web server (e.g.: Apache, IIS, nginx) and how to enable PUT or DELETE request."
	                ]);
	            }
	        } catch (e) {
		        console.log("Error on [Check Server Allowed Request Options]:");
		        console.log(e);
		        showServerConfigError(null);
	        }
        }
    });

    runHttpUnitTest("Application Object", "test-app.php/check-app-class", {
        response: {
            get_class: "FastSitePHP\\Application",
            get_parent_class: "stdClass"
        }
    });

    runHttpUnitTest("Application Object - Properties", "test-app.php/check-app-properties", {
        response: "All properties matched for [FastSitePHP\\Application]: after_callbacks, allow_methods_override, allow_options_requests, before_callbacks, before_send_callbacks, case_sensitive_urls, config, controller_root, cors_headers, error_callbacks, error_page_message, error_page_title, error_template, footer_templates, header_fields, header_templates, json_options, lang, last_error, lazy_load_props, locals, method_not_allowed_message, method_not_allowed_title, middleware_root, no_cache, not_found_callbacks, not_found_page_message, not_found_page_title, not_found_template, params, render_callbacks, response_cookies, show_detailed_errors, site_routes, status_code, strict_url_mode, template_dir, view_engine"
    });

    runHttpUnitTest("Application Object - Functions", "test-app.php/check-app-methods", {
        response: "All methods matched for [FastSitePHP\\Application]: __call, __get, after, before, beforeSend, callMiddleware, checkParam, clearCookie, cookie, cookies, cors, delete, engine, error, errorHandler, errorPage, escape, exceptionHandler, get, header, headers, lazyLoad, methodExists, mount, noCache, notFound, onRender, pageNotFound, param, patch, post, put, redirect, render, requestedPath, rootDir, rootUrl, route, routeMatches, routes, run, runAfterEvents, sendErrorPage, sendOptionsResponse, sendPageNotFound, sendResponse, setup, shutdown, skipRoute, statusCode"
    });

    runHttpUnitTest("Application Object - Basic Text Response - Default Route with No Slash", "test-app.php", {
        response: "test-app.php"
    });

    runHttpUnitTest("Application Object - Basic Text Response - Default Route with Slash", "test-app.php/", {
        response: "test-app.php"
    });

    runHttpUnitTest("Application Object - URL Info - Version 1a", "test-app.php/get-url", {
        response: {
            rootUrl: rootDir + "test-app.php/",
            rootDir: rootDir,
            requestedPath: "/get-url"
        }
    });

    runHttpUnitTest("Application Object - URL Info - Version 1b - Upper-case URL with [case_sensitive_urls=true]", "test-app.php/GET-URL", {
        status: 404,
        responseContains: "Page Not Found"
    });

    runHttpUnitTest("Application Object - URL Info - Version 2", "test-app.php/get/url", {
        response: {
            rootUrl: rootDir + "test-app.php/",
            rootDir: rootDir,
            requestedPath: "/get/url"
        }
    });

    // Making a request to "/get/url/3/" which by default matches up to route "/get/url/3"
    runHttpUnitTest("Application Object - URL Info - Version 3", "test-app.php/get/url/3/", {
        response: {
            rootUrl: rootDir + "test-app.php/",
            rootDir: rootDir,
            requestedPath: "/get/url/3"
        }
    });

    runHttpUnitTest("Application Object - URL Info - strict_url_mode - Test 1", "test-app.php/get-url2", {
        response: "/get-url2"
    });

    runHttpUnitTest("Application Object - URL Info - strict_url_mode - Test 2", "test-app.php/get-url2/", {
        response: "/get-url2"
    });

    runHttpUnitTest("Application Object - URL Info - strict_url_mode - Test 3", "test-app.php/get-url2/?strict_url_mode=0", {
        response: "/get-url2"
    });

    runHttpUnitTest("Application Object - URL Info - strict_url_mode - Test 3", "test-app.php/get-url2/?strict_url_mode=1", {
        response: "/get-url2/"
    });

    if (window.runTestsWithUnicodeCodeUrl) {
        runHttpUnitTest("Application Object - URL Info - Unicode Characters in URL", "test-app.php/test/测试?test=" + encodeURIComponent("test/测试"), {
            response: {
                rootUrl: rootDir + "test-app.php/",
                rootDir: rootDir,
                requestedPath: "/test/测试",
                queryString: {
                    test: "test\/测试"
                }
            }
        });
    }

    runHttpUnitTest("Application Object - Basic HTML - Version 1", "test-app.php/html", {
        response: "<h1>HTML</h1>"
    });

    runHttpUnitTest("Application Object - Basic HTML - Version 2", "test-app.php/html2", {
        response: "<h1>HTML2</h1>",
    });

    runHttpUnitTest("Application Object - Basic HTML - Version 3", "test-app.php/html3", {
        response: "<h1>HTML3</h1>"
    });

    runHttpUnitTest("Application Object - Basic HTML - Version 4", "test-app.php/html4", {
        response: "<h1>HTML4</h1>"
    });

    runHttpUnitTest("Application Object - Basic HTML - Version 5", "test-app.php/html5", {
        type: null,
        response: "<h1>HTML5</h1>",
        responseHeaders: [
            { name: "Content-Type", value: "text/html; charset=ISO-8859-1" }
        ]
    });

    runHttpUnitTest("Application Object - contentType() - JSON - Return Type String", "test-app.php/json-string", {
        response: {
            Name: "FastSitePHP_App",
            ReturnType: "String"
        }
    });

    runHttpUnitTest("Application Object - contentType() - JSON - Return Type Array", "test-app.php/json-array", {
        response: {
            Name: "FastSitePHP_App",
            CreatedFrom: "Array"
        }
    });

    runHttpUnitTest("Application Object - contentType() - JSON - Return Type Object:stdClass", "test-app.php/json-object", {
        response: {
            Name: "FastSitePHP_App",
            CreatedFrom: "stdClass"
        }
    });

    runHttpUnitTest("Application Object - contentType() - JSON - Return Type User-Defined Class", "test-app.php/json-custom", {
        response: {
            Name: "FastSitePHP_App",
            CreatedFrom: "CustomClass",
            IntValue: 123,
            BoolValue: true
        }
    });

    runHttpUnitTest("Application Object - Custom Response Object", "test-app.php/custom-response-object", {
        response: "Testing with a Custom Response Class: custom_response"
    });

    runHttpUnitTest("Application Object - Error Check - Invalid Response Object", "test-app.php/invalid-response-object", {
        status: 500,
        responseContains: '<td class="error-message">Unexpected route return type of [object:CustomSend]. Expected a string, mixed data for a JSON Response, or an object that includes &quot;Response&quot; in the name with a [send()] method.</td>'
    });

    runHttpUnitTest("Application Object - Error Check - Invalid Response Type", "test-app.php/invalid-response-type", {
        status: 500,
        responseContains: '<td class="error-message">Unexpected route return type of [integer]. Expected a string, mixed data for a JSON Response, or an object that includes &quot;Response&quot; in the name with a [send()] method.</td>'
    });

    // Define Unit Tests for each of the Supported Redirect Status Codes
    var redirectStatusCodes = {
        "301": "Moved Permanently",
        "302": "Found",
        "303": "See Other",
        "307": "Temporary Redirect",
        "308": "Permanent Redirect"
    };
    for (var prop in redirectStatusCodes) {
        if (Object.prototype.hasOwnProperty.call(redirectStatusCodes, prop)) {
            runHttpUnitTest("Application Object - Redirect for Status Code - " + prop + " " + redirectStatusCodes[prop], "test-app.php/redirect-" + prop, {
                responseUrl: rootDir + "test-app.php/redirected-" + prop,
                response: prop + " Redirect"
            });
        }
    }

    runHttpUnitTest("Application Object - Redirect with Parameters", "test-app.php/redirect-with-params", {
        responseUrl: rootDir + "test-app.php/redirected-with-params?param1=abc&param2=123",
        response: {
            param1: "abc",
            param2: "123"
        }
    });

    runHttpUnitTest("Application Object - Redirect Errors", "test-app.php/redirect-errors", {
        response: "[redirect-errors][Tested Errors: 5]"
    });

    runHttpUnitTest("Application Object - Route Filter with Redirect", "test-app.php/redirect-filter", {
        responseUrl: rootDir + "test-app.php/redirected",
        response: "Route was redirected"
    });

    runHttpUnitTest("Application Object - Filter Test - Version 4", "test-app.php/filter-test-1", {
        response: "filter-test-1"
    });

    runHttpUnitTest("Application Object - Skip Route Test", "test-app.php/skip-route-test", {
        status: 404,
        responseContains: [
            "Page Not Found",
            "The requested page could not be found."
        ]
    });

    runHttpUnitTest("Application Object - Route Defined Twice - Version 1", "test-app.php/route-defined-twice-1", {
        response: "route-defined-twice-1 - function 2"
    });

    runHttpUnitTest("Application Object - Route Defined Twice - Version 2", "test-app.php/route-defined-twice-2", {
        response: "route-defined-twice-2 - function 1"
    });

    runHttpUnitTest("Application Object - Filter Test with Filter Modifying the App Object", "test-app.php/update-app-filter", {
        response: "updateAppFilter()"
    });

    runHttpUnitTest("Application Object - Invalid Filter Test", "test-app.php/invalid-filter-test", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<tr><td><b>Message</b></td><td class="error-message">An item from [Route-&gt;filter()] for URL [GET /invalid-filter-test] was defined as a [integer] but it should be defined as either a Closure function or a string in the format of &#039;Class.method&#039;.</td></tr>',
            "<td>skipRoute</td>",
            "<td>run</td>",
        ]
    });

    runHttpUnitTest("Application Object - Invalid Controller Test", "test-app.php/invalid-controller-test", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            "<td class=\"error-message\">A [route-&gt;controller] for URL [GET /invalid-controller-test] was defined as a [integer] but it should be defined as either a Closure function or a string in the format of &#039;Class&#039; or &#039;Class.method&#039;",
            "<td>run</td>",
        ]
    });

    runHttpUnitTest("Application Object - Route Parameter Test - One Parameter", "test-app.php/hello/World", {
        response: "Hello World"
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Three Parameters", "test-app.php/record/order/get/123", {
        response: {
            controller: 'order',
            action: 'get',
            id: '123'
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - File Name", "test-app.php/get-file/image.jpg", {
        response: "image.jpg"
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Testing Encoding", "test-app.php/" + encodeURIComponent("param test") + "/" + encodeURIComponent("page title with spaces"), {
        response: "page title with spaces"
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Multiple Values 1", "test-app.php/param-test-5/abc/123", {
        response: {
            value1: "abc",
            value2: "123"
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Multiple Values 1", "test-app.php/param-test-6/abc/456", {
        response: {
            value1: "456",
            value2: "abc"
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Validation and Converting - Int and Callback", "test-app.php/param-validation-test-1/123456/6/7/8", {
        response: {
            product_id: 123456,
            range1: "6",
            range2: 7,
            range3: 8
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Validation and Converting - Float", "test-app.php/param-validation-test-2/123456.789/5.25/abc", {
        response: {
            float1: 123456.789,
            float2: 5.25,
            float3: 0
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Validation and Converting - Boolean", "test-app.php/param-validation-test-3/true/false/yes/no/on/off/1/0", {
        response: {
            bool1: true,
            bool2: false,
            bool3: true,
            bool4: false,
            bool5: true,
            bool6: false,
            bool7: true,
            bool8: false
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Single Character for Variables in Route Defintion", "test-app.php/param-test-10/xyz/987", {
        response: {
            value1: "xyz",
            value2: "987"
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Optional Parameters with all defined", "test-app.php/param-test-11/2016/01", {
        response: {
            year: "2016",
            month: "01"
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Optional Parameters with one missing", "test-app.php/param-test-11/2016", {
        response: {
            year: "2016",
            month: 12
        }
    });

    runHttpUnitTest("Application Object - Route Parameter Test - Optional Parameters with all missing", "test-app.php/param-test-11", {
        response: {
            year: 2015,
            month: 12
        }
    });

    runHttpUnitTest("Application Object - escape()", "test-app.php/escape", {
        response: "&lt;script&gt;&amp;&quot;&#039;[]"
    });

    runHttpUnitTest("Application Object - Parameter - Error Message 1", "test-app.php/param-error-1", {
        response: "Unexpected $name variable type specified for [FastSitePHP\\Application->param()]"
    });

    runHttpUnitTest("Application Object - Parameter - Error Message 2", "test-app.php/param-error-2", {
        response: "$name must be longer than 2 characters when [FastSitePHP\\Application->param()] is called"
    });

    runHttpUnitTest("Application Object - Parameter - Error Message 3", "test-app.php/param-error-3", {
        response: "$name must start with [:] when [FastSitePHP\\Application->param()] is called"
    });

    runHttpUnitTest("Application Object - Parameter - Error Message 4", "test-app.php/param-error-4", {
        response: "The $name [:duplicate] is a duplicate and was already defined when [FastSitePHP\\Application->param()] was called"
    });

    runHttpUnitTest("Application Object - Parameter - Error Message 5", "test-app.php/param-error-5", {
        response: "Error with param([:error]): $validation cannot be a zero length string"
    });

    runHttpUnitTest("Application Object - Parameter - Error Message 6", "test-app.php/param-error-6", {
        response: "Error with param([:error]): $validation must be either a closure, a string with [any|int|float|bool], or a regular expression"
    });

    runHttpUnitTest("Application Object - Parameter - Error Message 7", "test-app.php/param-error-7", {
        response: "Error with param([:error]): $converter string is not correct and must be either a closure or a string with [int|float|bool]"
    });

    runHttpUnitTest("Application Object - Parameter - Error Message 8", "test-app.php/param-error-8", {
        response: "Error with param([:error]): $converter is of the wrong type and must be either a closure or a string with [int|float|bool]"
    });

    runHttpUnitTest("Application Object - Error Test - Exception Raised with Error Handling Set", "test-app.php/exception", {
        status: 500,
        responseContains: [
            'An error has occurred</h1>',
            'An error has occurred while processing your request.',
            '<tr><th colspan="2">Error Source</th></tr>',
            '<tr><td><b>Type</b></td><td class="error-type">Exception</td></tr>',
            '<tr><td><b>Code</b></td><td class="error-code">0</td></tr>',
            '<tr><td><b>Message</b></td><td class="error-message">Exception Test</td></tr>',
            '<tr><td><b>File</b></td><td class="error-file">',
            'test-app.php</td></tr>',
            '<tr><td><b>Line</b></td><td class="error-line">',
            '<tr><td><b>Time</b></td><td class="error-time">',
            '<h2>Stack Trace</h2>',
            '<th>#</th>',
            '<th>Function</th>',
            '<th>File</th>',
            '<th>Line</th>',
            "document.addEventListener('DOMContentLoaded', function () {",
            "var errorTime = document.querySelector('.error-time');",
            'if (errorTime !== null) {',
            'var d = new Date(errorTime.textContent);',
            'if (!isNaN(d.getTime())) {',
            "var time = (typeof d.toLocaleString === 'function' ? d.toLocaleString() : d.toString());",
            'errorTime.textContent = time;'
        ]
    });

    runHttpUnitTest("Application Object - Request Method - OPTIONS - Exception", "test-app.php/exception-in-filter", {
        method: "OPTIONS",
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">Exception in Filter</td>',
            "<td>{closure}</td>",
            "<td>skipRoute</td>"
        ],
        responseExcludes: "Should never get called"
    });

    runHttpUnitTest("Application Object - Error Test - Exception Raised from filter()", "test-app.php/exception-in-filter", {
        status: 500,
        responseContains: '<td class="error-message">Exception in Filter</td>',
        responseExcludes: "Should never get called"
    });

    // In the [responseContains] nested array's the first line is
    // for PHP 5 and second line is for PHP 7
    runHttpUnitTest("Application Object - Error Test - Error Type E_ERROR or Throwable Error", "test-app.php/error-fatal", {
        status: 500,
        responseContains: [
            "An error has occurred</h1>",
            [
                '<td class="error-type">ErrorException</td>',
                '<td class="error-type">Error</td>'
            ],
            [
                '<td class="error-severity">1 (E_ERROR)</td>',
                "<td>call_user_func_array</td>"
            ],
            [
                // PHP 5 and 7
                '<td class="error-message">Class &#039;UnknownObject&#039; not found</td>',
                // PHP 8
                '<td class="error-message">Class &quot;UnknownObject&quot; not found</td>',
            ],
            [
                "<td>shutdown</td>",
                "<td>run</td>"
            ]
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_WARNING", "test-app.php/error-warning", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">2 (E_WARNING)</td>',
            [
                '<td class="error-message">Division by zero</td>',
                '<td class="error-message">session_destroy(): Trying to destroy uninitialized session</td>',
            ],
            "<td>errorHandler</td>",
            "<td>{closure}</td>",
            "<td>call_user_func_array</td>",
            "<td>run</td>"
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_WARNING with the setting [track_errors] turned on", "test-app.php/error-track-errors", {
        responseContains: [
            [
                "[$php_errormsg: Division by zero]",
                "Skipping Test, PHP version is 8 or above",
            ]
        ]
    });

    // For the [responseContains] array's with 2 items the first line is for PHP 5
    // and the 2nd line is for PHP 7
    runHttpUnitTest("Application Object - Error Test - Error Type E_PARSE or Throwable ParseError", "test-app.php/error-parse", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">ErrorException</td>',
                '<td class="error-type">ParseError</td>'
            ],
            [
                '<td class="error-severity">4 (E_PARSE)</td>',
                "<td>call_user_func_array</td>"
            ],
            // Match any one of items as the text varies based on the version of PHP used
            [
                // Windows PHP 5.4 and 5.5
                '<td class="error-message">syntax error, unexpected &#039;echo&#039; (T_ECHO), expecting &#039;,&#039; or &#039;;&#039;</td>',
                // Windows PHP 5.3
                '<td class="error-message">syntax error, unexpected T_ECHO, expecting &#039;,&#039; or &#039;;&#039;</td>',
                // Mac with PHP 5.4
                '<td class="error-message">parse error, expecting `&#039;,&#039;&#039; or `&#039;;&#039;&#039;</td>',
                // PHP 7.4
                '<td class="error-message">syntax error, unexpected &#039;echo&#039; (T_ECHO), expecting &#039;;&#039; or &#039;,&#039;</td>',
                // PHP 8
                '<td class="error-message">syntax error, unexpected token &quot;echo&quot;, expecting &quot;;&quot; or &quot;,&quot;</td>',
                '<td class="error-message">syntax error, unexpected token &quot;echo&quot;, expecting &quot;,&quot; or &quot;;&quot;</td>',
            ],
            "test-app-parse-error.php",
            [
                "<td>shutdown</td>",
                "<td>run</td>"
            ]
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_NOTICE", "test-app.php/error-notice", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">8 (E_NOTICE)</td>',
            [
                '<td class="error-message">Undefined variable: undefined_variable</td>',
                '</td><td class="error-message">date_default_timezone_set(): Timezone ID &#039;test&#039; is invalid</td>'
            ],
            "<td>errorHandler</td>",
            "<td>{closure}</td>"
        ]
    });


    runHttpUnitTest("Application Object - Error Test - Error Type E_RECOVERABLE_ERROR or Throwable TypeError", "test-app.php/error-recoverable", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">ErrorException</td>', // PHP 5
                '<td class="error-type">TypeError</td>' // PHP 7+
            ],
            [
                '<td class="error-severity">4096 (E_RECOVERABLE_ERROR)</td>', // PHP 5
                '<td class="error-code">0</td>' // PHP 7+
            ],
            [
                '<td class="error-message">Argument 1 passed to showObject() must be an instance of stdClass, string given, called in', // PHP 5 and 7
                '<td class="error-message">showObject(): Argument #1 ($obj) must be of type stdClass, string given, called in', // PHP 8
            ],
            "<td>showObject</td>"
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_DEPRECATED", "test-app.php/error-deprecated", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">8192 (E_DEPRECATED)</td>',
            [
                // PHP 5
                '<td class="error-message">Function split() is deprecated</td>',
                // PHP 7
                '<td class="error-message">Non-static method DeprecatedTest::nonStaticFunction() should not be called statically</td>',
                // PHP 8
                '<td class="error-message">Required parameter $b follows optional parameter $a</td>',
                // PHP 8.1
                '<td class="error-message">Optional parameter $a declared before required parameter $b is implicitly treated as a required parameter</td>',
            ],
            "<td>errorHandler</td>",
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_USER_ERROR", "test-app.php/error-user-error", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">256 (E_USER_ERROR)</td>',
            '<td class="error-message">User Error Test</td>',
            "<td>errorHandler</td>",
            "<td>trigger_error</td>"
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_USER_WARNING", "test-app.php/error-user-warning", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">512 (E_USER_WARNING)</td>',
            '<td class="error-message">User Warning Test</td>',
            "<td>errorHandler</td>",
            "<td>trigger_error</td>"
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_USER_NOTICE", "test-app.php/error-user-notice", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">1024 (E_USER_NOTICE)</td>',
            '<td class="error-message">User Notice Test</td>',
            "<td>errorHandler</td>",
            "<td>trigger_error</td>"
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_USER_DEPRECATED", "test-app.php/error-user-deprecated", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">16384 (E_USER_DEPRECATED)</td>',
            '<td class="error-message">User Deprecated Test</td>',
            "<td>errorHandler</td>",
            "<td>trigger_error</td>"
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Type E_COMPILE_ERROR", "test-app.php/error-compile-error", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            '<td class="error-severity">64 (E_COMPILE_ERROR)</td>',
            [
                '<td class="error-message">{closure}(): Failed opening required &#039;missing-file.php&#039;',
                '<td class="error-message">Cannot use &#039;string&#039; as class name as it is reserved</td>',
            ],
            "<td>shutdown</td>"
        ]
    });

    // Check for E_STRICT in PHP 7.
    // In PHP 7 all of the E_STRICT notices have been reclassified to other levels.
    runHttpUnitTest("Application Object - Error Test - Error Type E_STRICT", "test-app.php/error-strict", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">Exception</td>',
                '<td class="error-type">ErrorException</td>',
            ],
            [
                '<td class="error-severity">2048 (E_STRICT)</td>', // PHP 5
                '<td class="error-severity">8 (E_NOTICE)</td>', // PHP 7
                '<td class="error-code">0</td>', // PHP 8
            ],
            [
                'Non-static method SimpleClass::getValue() should not be called statically', // PHP 5
                '<td class="error-message">Accessing static property SimpleClass::$prop as non static</td>', // PHP 7
                '<td class="error-message">Skipping E_STRICT for PHP 8+</td>', // PHP 8+
            ]
        ]
    });

    // In the [responseContains] nested array's the first line is
    // for PHP 5 and second line is for PHP 7
    runHttpUnitTest("Application Object - Error Test - Throwable ArithmeticError", "test-app.php/error-arithmetic-error", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">Exception</td>',
                '<td class="error-type">ArithmeticError</td>'
            ],
            [
                '<td class="error-message">ArithmeticError are not in PHP 5</td>',
                '<td class="error-message">Bit shift by negative number</td>'
            ]
        ]
    });

    // In the [responseContains] nested array's the first line is
    // for PHP 5.* and PHP 7.0 and second line is for PHP 7.1
    runHttpUnitTest("Application Object - Error Test - Throwable DivisionByZeroError", "test-app.php/error-division-by-zero-error", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">ErrorException</td>',
                '<td class="error-type">DivisionByZeroError</td>'
            ],
            [
                '<td class="error-message">Division by zero</td>',
                '<td class="error-message">Modulo by zero</td>'
            ]
        ]
    });

    // In the [responseContains] nested array's the first line is
    // for PHP 5.* and PHP 7.0 and second line is for PHP 7.1
    runHttpUnitTest("Application Object - Error Test - Throwable ArgumentCountError", "test-app.php/error-argument-count-error", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">ErrorException</td>',
                '<td class="error-type">ArgumentCountError</td>'
            ],
            [
                '<td class="error-severity">8 (E_WARNING)</td>',
                '<td class="error-code">0</td>',
            ],
            [
                '<td class="error-message">Missing argument 1 for argument_error_test(), called in ',
                '<td class="error-message">Too few arguments to function argument_error_test(), 0 passed in '
            ]
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Control Operator", "test-app.php/error-control-operator", {
        responseContains: [
            [
                // PHP 5 and 7
                "@file(null) === false",
                // PHP 8                
                "@date_default_timezone_set(test) = false",
            ]
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Reporting Disabled", "test-app.php/error-reporting-disabled", {
        responseContains: [
            [
                // PHP 5 and 7
                "file(null) === false",
                // PHP 8                
                "date_default_timezone_set(test) = false",
            ]
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Control Operator Not Used", "test-app.php/error-control-operator-not-used", {
        status: 500,
        responseContains: [
            '<td class="error-type">ErrorException</td>',
            [
                // PHP 5 and 7
                '<td class="error-severity">2 (E_WARNING)</td>',
                // PHP 8
                '<td class="error-severity">8 (E_NOTICE)</td>',
            ],
            [
                // PHP 5 and 7
                '<td class="error-message">file(): Filename cannot be empty</td>',
                // PHP 8
                '<td class="error-message">date_default_timezone_set(): Timezone ID &#039;test&#039; is invalid</td>',
            ],
            "<td>errorHandler</td>",
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Error Control Operator", "test-app.php/error-try-catch-instead-of-control-operator", {
        responseContains: [
            [
                // PHP 5 and 7
                "[ErrorException]: file(): Filename cannot be empty",
                // PHP 8
                "[ErrorException]: date_default_timezone_set(): Timezone ID 'test' is invalid",
            ]
        ]
    });

    runHttpUnitTest("Application Object - Error Test - Changing Response Type", "test-app.php/error-change-content-type", {
        status: 500,
        responseContains: [
            [
                // PHP 5, 7, 8.1
                '<td class="error-type">ErrorException</td>',
                // PHP 8.0
                '<td class="error-type">ValueError</td>',
            ],
            '<td class="error-code">0</td>',
            [
                // PHP 5 and 7
                '<td class="error-message">readfile(): Filename cannot be empty</td>',
                // PHP 8
                '<td class="error-message">Path cannot be empty</td>',
                // PHP 8.1
                '<td class="error-message">readfile(): Passing null to parameter #1 ($filename) of type string is deprecated</td>',
            ]
        ]
    });

    runHttpUnitTest("Application Object - Error Test - 304 Response Type", "test-app.php/error-status-code-304", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">[304] is an invalid option for [FastSitePHP\\Application-&gt;statusCode()]. Support for 304 [Not Modified] Responses are only available when calling [FastSitePHP\\Web\\Response()-&gt;statusCode] and using the Response Object as the Route&#039;s Return Value.</td>',
            "<td>statusCode</td>",
        ]
    });

    runHttpUnitTest("Application Object - Dynamic Functions assigned to the Application Object", "test-app.php/dynamic-functions", {
        response: "[called from test()][called from test2(abc)][called from test2(123)][called from test2(&lt;&amp;&gt;)][BadMethodCallException][Call to undefined method FastSitePHP\\Application::test4()][BadMethodCallException][Call to undefined method FastSitePHP\\Application::test3(), a property exists of the same name however to be called as a dynamic function from FastSitePHP it must be defined as a Closure. The current type of [test3] is [string].]"
    });

    runHttpUnitTest("Application Object - [methodExists()] Function with built-in and dynamic methods", "test-app.php/method-exists", {
        response: "true,false,true,false"
    });

    runHttpUnitTest("Application Object - POST Data - Send and Receive JSON - Using inputText()", "test-app.php/post-data", {
        postData: JSON.stringify({ site: 'FastSitePHP', page: 'UnitTest' }),
        postType: 'application/json; charset=UTF-8',
        response: { site: 'FastSitePHP', page: 'UnitTest' }
    });

    runHttpUnitTest("Application Object - Request Method - PUT Test with 201 Response", "test-app.php/put-test-1", {
        method: "PUT",
        postType: 'application/json; charset=UTF-8',
        postData: JSON.stringify({ data: 'PUT Test' }),
        status: 201,
        response: {
            result: "success"
        }
    });

    runHttpUnitTest("Application Object - Request Method - PUT Test with 204 Response", "test-app.php/put-test-2", {
        method: "PUT",
        status: 204,
        response: ""
    });

    runHttpUnitTest("Application Object - Request Method - PUT Test with 205 Response", "test-app.php/put-test-3", {
        method: "PUT",
        status: 205,
        response: ""
    });

    runHttpUnitTest("Application Object - Request Method - DELETE Test with 202 Response", "test-app.php/delete-test-1", {
        method: "DELETE",
        status: 202,
        response: {
            result: "success"
        }
    });

    runHttpUnitTest("Application Object - Request Method - PATCH Test with 204 Response", "test-app.php/patch-test-1", {
        method: "PATCH",
        status: 204,
        response: ""
    });

    runHttpUnitTest("Application Object - Request Method - HEAD Test 1", "test-app.php/", {
        method: "HEAD",
        response: "",
    });

    runHttpUnitTest("Application Object - Request Method - OPTIONS Test 1 - get() route", "test-app.php/", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "GET, HEAD, OPTIONS" }
        ]
    });

    runHttpUnitTest("Application Object - Request Method - OPTIONS Test 2 - Asterisk (*)", "test-app.php/*", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT" }
        ]
    });

    runHttpUnitTest("Application Object - Request Method - OPTIONS Test 3 - put() route", "test-app.php/put-test-1", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "OPTIONS, PUT" }
        ]
    });

    runHttpUnitTest("Application Object - Request Method - OPTIONS Test 4 - post() route", "test-app.php/post-data", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "OPTIONS, POST" }
        ]
    });

    runHttpUnitTest("Application Object - Request Method - OPTIONS Test 5 - delete() route", "test-app.php/delete-test-1", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "DELETE, OPTIONS" }
        ]
    });

    runHttpUnitTest("Application Object - Request Method - [/method] route - OPTIONS", "test-app.php/method", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT" }
        ]
    });

    runHttpUnitTest("Application Object - Request Method - OPTIONS - 404", "test-app.php/404", {
        method: "OPTIONS",
        status: 404,
        responseContains: "Page Not Found"
    });

    runHttpUnitTest("Application Object - Request Method - [/method] route - HEAD", "test-app.php/method", {
        method: "HEAD",
        response: "",
        responseHeaders: [
            { name: "Allow", value: null }
        ]
    });

    runHttpUnitTest("Application Object - Request Method - [/method] route - GET", "test-app.php/method", {
        response: "get()",
        responseHeaders: [
            { name: "Allow", value: null }
        ]
    });

    runHttpUnitTest("Application Object - Request Method - [/method] route - POST", "test-app.php/method", {
        method: "POST",
        response: "post()"
    });

    runHttpUnitTest("Application Object - Request Method - [/method] route - PUT", "test-app.php/method", {
        method: "PUT",
        response: "put()"
    });

    runHttpUnitTest("Application Object - Request Method - [/method] route - PATCH", "test-app.php/method", {
        method: "PATCH",
        response: "patch()"
    });

    runHttpUnitTest("Application Object - Request Method - [/method] route - DELETE", "test-app.php/method", {
        method: "DELETE",
        response: "delete()"
    });

    runHttpUnitTest("Application Object - Testing [Application->allow_options_requests = true]", "test-app.php/toggle-options?options=true", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "GET, HEAD, OPTIONS, POST" }
        ]
    });

    runHttpUnitTest("Application Object - Testing [Application->allow_options_requests = false]", "test-app.php/toggle-options?options=false", {
        response: "Called [toggle-options]",
        responseHeaders: [
            { name: "Allow", value: null }
        ]
    });

    // The next 3 tests verify that direct calls to the files
    // called from mount() exist and return the expected value.
    // These routes for testing mount() are also tested with a different
    // option from the file [test-app-url-case.js]
    runHttpUnitTest("Application Object - Mount Testing - Checking for File [app-mount]", "test-app-mount.php", {
        response: "test-app-mount.php"
    });

    runHttpUnitTest("Application Object - Mount Testing - Checking for File [mount1]", "mount-testing/test-mount1.php", {
        response: "test-mount1.php"
    });

    runHttpUnitTest("Application Object - Mount Testing - Checking for File [mount1]", "mount-testing/test-mount2.php", {
        response: "test-mount2.php"
    });

    runHttpUnitTest("Application Object - Mount Testing - Testing Route defined from mount() - File Name in Test File 1", "test-app.php/mount/test", {
        response: "/mount/test"
    });

    runHttpUnitTest("Application Object - Mount Testing - Testing that Route is not loaded when case_sensitive_urls = true", "test-app.php/MOUNT/TEST", {
        status: 404,
        responseContains: "Page Not Found"
    });

    runHttpUnitTest("Application Object - Mount Testing - Error Test for File Name", "test-app.php/mount-file-not-found/test", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">Error calling [FastSitePHP\\mount()]: File [file-not-found.php] specified for mount path [/mount-file-not-found/] was not found in the directory [',
            "tests] or permissions are set so the file is not visible to PHP.</td></tr>",
            "<td>mount</td>"
        ]
    });

    runHttpUnitTest("Application Object - Mount Testing - Error Test for Full File Path", "test-app.php/mount-path-not-found/test", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">Error calling [FastSitePHP\\mount()]: File Path [',
            "file-not-found.php] specified for mount path [/mount-path-not-found] does not exists or permissions are set so the file is not visible to PHP.</td></tr>",
            "<td>mount</td>"
        ]
    });

    runHttpUnitTest("Application Object - Mount Testing - Testing Route defined from mount() using Full File Path", "test-app.php/mount2/", {
        response: "/mount2"
    });

    runHttpUnitTest("Application Object - Mount Testing - Checking that mount() route is not defined if route is not matched", "test-app.php/check-for-mount-route", {
        response: "Route [/mount/test] was not found"
    });

    runHttpUnitTest("Application Object - Testing routes() function", "test-app.php/get-routes", {
        response: "Success routes() returned expected data"
    });

    runHttpUnitTest("Application Object - Routing - Testing routeMatches()", "test-app.php/route-matches", {
        response: "Success for routeMatches() function, Completed 30 Unit Tests and 6 Exception Tests"
    });

    runHttpUnitTest("Application Object - Routing - Testing routeMatches() Error with Error Handling Set", "test-app.php/route-matches-param-error", {
        responseContains: [[
            "Error with param([:regex_invalid]), the regular expression [ABC] is not valid for the PHP function preg_match(). Error message from PHP: preg_match(): Delimiter must not be alphanumeric or backslash",
            "Error with param([:regex_invalid]), the regular expression [ABC] is not valid for the PHP function preg_match(). Error message from PHP: preg_match(): Delimiter must not be alphanumeric, backslash, or NUL",
        ]]
    });

    runHttpUnitTest("Application Object - Routing - Testing routeMatches() Custom Error with Error Handling Set", "test-app.php/route-matches-custom-error", {
        response: "Error with param([:regex_invalid]), the regular expression [ABC] is not valid for the PHP function preg_match(). Specific error message from [preg_match()] cannot be obtained because a function defined by this site for [set_error_handler()] did not return false."
    });

    runHttpUnitTest("Application Object - noCache() Function", "test-app.php/no-cache", {
        response: "noCache() Tests Passed: 2",
        responseHeaders: [
            { name: "Cache-Control", value: "no-cache, no-store, must-revalidate" },
            { name: "Pragma", value: "no-cache" },
            { name: "Expires", value: "-1" },
        ]
    });

    runHttpUnitTest("Application Object - 405 Response - Test 1", "test-app.php/405", {
        status: 405,
        responseContains: [
            '<h1 class="alert alert-danger">Error - Method Not Allowed</h1>',
            '<div class="alert alert-info">A [GET] request was submitted however this route only allows for [POST, OPTIONS] methods.</div>'
        ]
    });

    runHttpUnitTest("Application Object - 405 Response - Test 2", "test-app.php/", {
        method: "POST",
        status: 405,
        responseContains: [
            '<h1 class="alert alert-danger">Error - Method Not Allowed</h1>',
            '<div class="alert alert-info">A [POST] request was submitted however this route only allows for [GET, HEAD, OPTIONS] methods.</div>'
        ]
    });

    runHttpUnitTest("Application Object - 405 Response - Test 3", "test-app.php/405-options", {
        status: 405,
        responseContains: [
            '<h1 class="alert alert-danger">Error - Method Not Allowed</h1>',
            '<div class="alert alert-info">A [GET] request was submitted however this route only allows for [POST] methods.</div>'
        ]
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing cors() Function Validation", "test-app.php/cors-validation", {
        response: "Success checked cors() with 5 passed tests and 19 exception tests"
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing cors() Function Test 1 - OPTIONS", "test-app.php/cors-1", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Methods", value: "GET, HEAD, OPTIONS" }
        ]
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing cors() Function Test 1 - GET", "test-app.php/cors-1", {
        response: "Testing cors() [Access-Control-Allow-Origin] with a String Value",
        responseHeaders: [
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Methods", value: null }
        ]
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing cors() Function Test 2 - OPTIONS", "test-app.php/cors-2", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "GET, HEAD, OPTIONS" },
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Headers", value: "Content-Type, X-Requested-With" },
            { name: "Access-Control-Allow-Methods", value: "GET, HEAD, OPTIONS" }
        ]
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing cors() Function Test 2 - GET", "test-app.php/cors-2", {
        response: "Testing cors() [Access-Control-Allow-Origin, Access-Control-Allow-Headers] with an Array",
        responseHeaders: [
            { name: "Allow", value: null },
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Headers", value: "Content-Type, X-Requested-With" },
            { name: "Access-Control-Allow-Methods", value: null }
        ]
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing (CORS) from route() - OPTIONS", "test-app.php/cors-3", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "GET, HEAD, OPTIONS, POST" },
            { name: "Access-Control-Allow-Origin", value: '*' },
            { name: "Access-Control-Allow-Methods", value: "GET, HEAD, OPTIONS, POST" }
        ]
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing (CORS) from route() - GET", "test-app.php/cors-3", {
        response: "Testing cors() from route()",
        responseHeaders: [
            { name: "Allow", value: null },
            { name: "Access-Control-Allow-Origin", value: '*' },
            { name: "Access-Control-Allow-Methods", value: null }
        ]
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing (CORS) from route() with custom 'Allow' Headers - OPTIONS", "test-app.php/cors-4", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "HEAD, GET, PUT, OPTIONS" },
            { name: "Access-Control-Allow-Origin", value: '*' },
            { name: "Access-Control-Allow-Methods", value: "HEAD, GET, PUT, OPTIONS" }
        ]
    });

    runHttpUnitTest("Application Object - Cross-Origin Resource Sharing (CORS) from route() with custom 'Allow' Headers - GET", "test-app.php/cors-4", {
        response: "Testing cors() from route() with custom allow headers",
        responseHeaders: [
            { name: "Allow", value: null },
            { name: "Access-Control-Allow-Origin", value: '*' },
            { name: "Access-Control-Allow-Methods", value: 'HEAD, GET, PUT, OPTIONS' }
        ]
    });

    runHttpUnitTest("Application Object - Header Error Validation - Test 1", "test-app.php/header-error-1", {
        response: "The function [FastSitePHP\\Application->header()] was called with an invalid parameter. The $name parameter must be defined a string but instead was defined as type [integer]."
    });

    runHttpUnitTest("Response Object - Header Error Validation - Test 2", "test-app.php/header-error-2", {
        response: "The function [FastSitePHP\\Application->header()] was called with invalid parameters. The $name parameter defined as an empty string. It must instead be set to a valid header field."
    });

    runHttpUnitTest("Response Object - Testing Header Functionality with the header() and headers() functions", "test-app.php/headers", {
        type: "text",
        responseHeaders: [
            { name: "X-API-Key", value: "test123" }
        ],
        response: "Testing of $app.headers() and $app.header()"
    });

})();
