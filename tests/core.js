/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (https://conradsollitt.com)
 * @license  MIT License
 */

/* Validates with [jshint] */
/* global QUnit */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode

    // ---------------------------------
    // Polyfills
    // ---------------------------------
    if (window.location.origin === undefined) {
        window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
    }

    // ---------------------------------------------------------------
    // Page Settings
    // ---------------------------------------------------------------

    // Determine the root directory URL, this is based on the known name 
    // of this page. Example:
    //
    // Before:
    //   http://localhost:3000/vendor/fastsitephp/tests/index.htm
    // After:
    //   http://localhost:3000/vendor/fastsitephp/tests/
    var rootDir = location.href;
    if (rootDir.indexOf("index.htm") > -1) {
        rootDir = rootDir.replace("index.htm", "");
    }

    // Also create as a global variable
    window.rootDir = rootDir;

    // ------------------------------------------------------
    // Http object defined for unit testing, functions in
    // this object allow for options to define a request
    // and an expected response. This object is built for
    // asynchronous testing with QUnit.
    // ------------------------------------------------------
    var http = {
        // Number of URL's submitted, this is defined a public property
        // however it is only intended for use in this class
        urlCount: 0,

        // -------------------------------------------------------------------------------
        // Make a request for a Web-Page/Web-Service and check that the
        // response contains the expected result.
        //
        // @param options object
        //  "options has optional properties that if defined are checked,
        //   url, type and response are required for checking the result"
        //      url string "The webpage to fetch"
        //      status int "Expected Response Status Code"
        //      type string "text|html|json" "Expected Response Content Type"
        //      checkType bool
        //      response mixed "Text or JavaScript Object with the expected response"
        //      requestHeaders array of {name:string,value:string} objects
        //      responseHeaders array of {name:string,value:string} objects
        // @param assert "QUnit assert object"
        // @param callback function(result, errorText) "errorText is optional"
        // -------------------------------------------------------------------------------
        request: function (options, assert, callback) {
            // Create a new Xhr Object and define variables
            var xhr = new XMLHttpRequest(),
                n,
                m,
                name,
                value,
                textFound,
                matchedText,
                method,
                x,
                y,
                contentType,
                responseValue,
                checkValues;

            // Private function to set request headers
            var setRequestHeaders = function () {
                // Check for Request Headers to set, this is optional and needs
                // to be defined as an array of {name:string,value:string} objects
                if (options.requestHeaders !== undefined) {
                    for (n = 0, m = options.requestHeaders.length; n < m; n++) {
                        name = options.requestHeaders[n].name;
                        value = options.requestHeaders[n].value;
                        xhr.setRequestHeader(name, value);
                    }
                }
            };

            // Check xhr status and results on when readyState changes
            xhr.onload = function () {
                // Was there a callback function defined for the request?
                // If so then call it prior to checking the response.
                // This is used by the first unit test for FastSitePHP
                // which is [check-server-config] and can be used by any Unit Test
                // that needs to handle data in a specific manner.
                if (options.callback !== undefined) {
                    options.callback(xhr, assert);
                }

                // Check Status Code - 200, 400, etc
                if (options.status !== null) {
	            	assert.equal(xhr.status, options.status, "Response Status Code");    
                }

                // Check the final URL, this is for redirected responses
                if (options.responseUrl !== undefined) {
                    // Not all browsers support [responseURL] as it is relatively new
                    // (added by Chrome and Firefox in 2014). At the time of writing
                    // the latest versions of Safari and IE do not support it so if the
                    // property doesn't exist then do not run this check.
                    if (xhr.responseURL !== undefined) {
                        assert.equal(xhr.responseURL, options.responseUrl, "Response URL");
                    }
                }

                // Check for Expected Response Headers, this is optional and needs
                // to be defined as an array of {name:string,value:string} objects
                if (options.responseHeaders !== undefined) {
                    for (n = 0, m = options.responseHeaders.length; n < m; n++) {
                        name = options.responseHeaders[n].name;
                        value = options.responseHeaders[n].value;
                        responseValue = xhr.getResponseHeader(name);
                        checkValues = true;

                        // Depending on the web service [text/*] responses will come back with or without chartset 
                        // and also have spacing variations so handle known differences here. For example if running
                        // directly using PHP's Development Web Server the Response then Text Response Content Types 
                        // such as "text/html" will be returned with "text/plain;charset=UTF-8".
                        if (name === "Content-Type") {
                            if ((value === "text/html" && (responseValue === "text/html; charset=UTF-8" || responseValue === "text/html;charset=UTF-8")) ||
                                (value === "text/plain" && (responseValue === "text/plain; charset=UTF-8" || responseValue === "text/plain;charset=UTF-8")) ||
                                (value === "text/css" && (responseValue === "text/css; charset=UTF-8" || responseValue === "text/css;charset=UTF-8"))) {
                                    assert.ok(true, "Expected Response Header for " + name + ": [" + value + "] returned an accepted response of [" + responseValue + "]");
                                    checkValues = false;
                            }
                        }
                        if (checkValues) {
                            assert.equal(responseValue, value, "Expected Response Header for " + name + ": " + value);
                        }
                    }
                }

                // Check Content Type and Response
                if (options.type === 'text') {
                    // Check content-type
                    contentType = xhr.getResponseHeader("content-type");

                    // If running directly using PHP's Development Web Server the Response
                    // Content Type will be "text/plain;charset=UTF-8" instead of "text/plain"
                    // so handle both.
                    if (contentType === "text/plain" || contentType === "text/plain; charset=UTF-8" || contentType === "text/plain;charset=UTF-8") {
                        assert.equal(contentType, contentType, "Expected Response Content Type of [text/plain|UTF-8] matches [" + contentType + "]");
                    } else {
                        assert.equal(contentType, "text/plain", "Expected Response Content Type of [text/plain] matches [" + contentType + "]");
                    }

                    // Check if the responseText matches the text parameter
                    assert.equal(xhr.responseText, options.response, "Response text matches");
                    callback(true);
                } else if (options.type === 'html') {
                    // Check content-type
                    // Assume "text/html" and "text/html; charset=UTF-8" as different web servers
                    // will handle the value differently. Also running directly using PHP's
                    // Development Web Server will return "text/html;charset=UTF-8".
                    // Some versions of Apache (starting late 2019) will also exclude 'Content-Type' when
                    // the response is empty.
                    contentType = xhr.getResponseHeader("content-type");
                    if (contentType === "text/html" || contentType === "text/html; charset=UTF-8" || contentType === "text/html;charset=UTF-8") {
                        assert.equal(contentType, contentType, "Expected Response Content Type of [text/html|UTF-8] matches [" + contentType + "]");
                    } else if (contentType === null && xhr.responseText === '') {
                        assert.ok(true, 'Both Response Content Type and Response are empty.');
                    } else {
                        assert.equal(contentType, "text/html", "Expected Response Content Type of [text/html] matches [" + contentType + "]");
                    }

                    // Check if the responseText matches the text parameter
                    if (options.responseContains !== undefined) {
                        // Search text or search each item from an array
                        if (Object.prototype.toString.call(options.responseContains) === "[object Array]") {
                            for (n = 0, m = options.responseContains.length; n < m; n++) {
                                // If the item is an array then match any item from the array
                                if (Object.prototype.toString.call(options.responseContains[n]) === "[object Array]") {
                                    matchedText = "";
                                    for (x = 0, y = options.responseContains[n].length; x < y; x++) {
                                        matchedText = options.responseContains[n][x];
                                        textFound = (xhr.responseText.indexOf(matchedText) !== -1);
                                        if (textFound) {
                                            break;
                                        }
                                    }
                                    assert.ok(textFound, "Response html contains search text item [" + x + "] from the array [" + n + "]: " + matchedText);
                                    // If the item is a string then it must be found in the response to pass the unit test
                                } else {
                                    textFound = (xhr.responseText.indexOf(options.responseContains[n]) !== -1);
                                    assert.ok(textFound, "Response html contains search text [" + n + "]: " + options.responseContains[n]);
                                }
                            }
                        } else {
                            textFound = (xhr.responseText.indexOf(options.responseContains) !== -1);
                            assert.ok(textFound, "Response html contains search text: " + options.responseContains);
                        }

                        // responseContains tests may also contain responseExcludes tests
                        if (options.responseExcludes !== undefined) {
                            if (Object.prototype.toString.call(options.responseExcludes) === "[object Array]") {
                                for (n = 0, m = options.responseExcludes.length; n < m; n++) {
                                    textFound = (xhr.responseText.indexOf(options.responseExcludes[n]) !== -1);
                                    assert.ok(!textFound, "Response html contains exclude search text [" + n + "]");
                                }
                            } else {
                                textFound = (xhr.responseText.indexOf(options.responseExcludes) !== -1);
                                assert.ok(!textFound, "Response html contains exclude search text");
                            }
                        }
                    } else {
                        assert.equal(xhr.responseText, options.response, "Response html matches");
                    }
                    callback(true);
                } else if (options.type === 'json') {
                    // Check content-type
                    assert.equal(xhr.getResponseHeader("content-type"), "application/json", "Expected Response Content Type");

                    // Check if the JSON Response is the same as the expected response
                    try {
                        assert.deepEqual(JSON.parse(xhr.responseText), options.response, "Expected JSON Response");
                    } catch (e) {
                        assert.ok(false, "Error checking for Expected JSON Response, [xhr.responseText] = " + xhr.responseText);
                    }
                    callback(true);
                } else if (options.type === 'xml') {
                    // Check content-type
                    assert.equal(xhr.getResponseHeader("content-type"), "application/xml", "Expected Response Content Type");

                    // Check if the XML Response Text is the same as the expected response.
                    // In many cases when working with XML it's a good idea to use the
                    // XMLSerializer Object, for example:
                    //   (new XMLSerializer()).serializeToString(xhr.responseXML);
                    //
                    // However for this code it was not ideal as different browsers
                    // treated the XML header differently. For example at the time of
                    // development if the header [<?xml version="1.0"?>]
                    // was include with the response the these browser would include
                    // or modify it.
                    //   Firefox added encoding during serializeToString():
                    //      <?xml version="1.0" encoding="UTF-8"?>
                    //   Edge/IE returned XML without the header
                    //      <?xml version="1.0"?>
                    //
                    // Rather than handle different browsers are now only checking
                    // based on the documentElement of the Response XML so this code
                    // works with all browsers for the basic xml tests used here.
                    var responseXml = xhr.responseXML;
                    if (responseXml !== null) {
                        responseXml = responseXml.documentElement.outerHTML;
                        if (responseXml === undefined && responseXml.documentElement !== undefined) {
                            // IE 11
                            responseXml = (new XMLSerializer()).serializeToString(responseXml.documentElement);
                        }
                    }
                    assert.deepEqual(responseXml, options.response, "Expected XML Response");
                    callback(true);
                } else if (options.type === null) {
                    // No content type to check, this would be expected for a 304 response
                    // Or when specifying a specific content-type in the response header array.

                    // Check if the responseText matches the exepected value
                    assert.equal(xhr.responseText, options.response, "Response data matches");
                    callback(true);
                } else if (options.type === 'download') {
                    contentType = xhr.getResponseHeader("content-type");
                    assert.equal(contentType, "application/octet-stream", "Expected Response Content Type: " + contentType);
                    assert.equal(xhr.responseText, options.response, "Response text matches");
                    callback(true);
                } else {
                    callback(false, "Unhandled Content Type in function fetch()");
                }
            };

            // Make the Request
            if (options.postData !== undefined) {
                // If there is data to submit then set the default method to POST if not defined
                method = (options.method === undefined ? "POST" : options.method);
                xhr.open(method, options.url, true);
                // options.postType =
                //   "application/json; charset=utf-8"
                //   "application/json;"
                //   "application/x-www-form-urlencoded"
                // Submitting as "Content-type" instead of "Content-Type" to confirm
                // that the header is read as "Content-Type" rather than "Content-type"
                // from the function [\FastSitePHP\Application::headers()].
                if (options.postType !== undefined) {
                    xhr.setRequestHeader("Content-type", options.postType);
                }
                setRequestHeaders();
                xhr.send(options.postData);
            } else {
                method = (options.method === undefined ? "GET" : options.method);
                xhr.open(method, options.url, true);
                setRequestHeaders();
                xhr.send();
            }

            // Debug Status, checking for console is done to prevent errors with IE8/9
            this.urlCount++;
            if (window.console !== undefined) {
                console.log("Request Url #" + this.urlCount + ", " + options.url);
            }
        }
    };

    // ------------------------------------------------------
    // Unit Testing Functions to Call.
    // Assigned to the global Windows Object.
    // ------------------------------------------------------

    /**
     * Make a request for a Web-Page/Web-Service and check that the
     * response contains the expected result.
     * 
     * @param {string} description 
     * @param {string} page 
     * @param {object} options 
     */
    window.runHttpUnitTest = function (description, page, options) {
        // Run a Test with QUnit
        QUnit.test(description + " - [" + page + "]", function (assert) {
            // Http Tests are Asynchronous
            var done = assert.async();

            // Add url property to the expected options object
            options.url = rootDir + page;

            // If status code is not specified as an option then expect success
            // and use status code 200
            if (options.status === undefined) {
                options.status = 200;
            }

            // If type is not specified then expect json is the response is an object
            // and html if it is a string
            if (options.type === undefined) {
                options.type = ((typeof options.response === "object") ? "json" : "html");
            }

            // Make the request
            http.request(options, assert, function (result, errorText) {
                assert.ok(result, "Finished Checking Result" + (errorText !== undefined ? " " + errorText : ""));
                done();
            });
        });
    };

    /**
     * Run a tests which require both a GET followed by a POST request.
     * This is useful for check Requests that can only be handled by
     * data sent to and read from the client (example: HTTP-Cookies).
     * 
     * @param {string} description 
     * @param {string} page 
     * @param {object} options 
     */
    window.runGetAndPostUnitTest = function (description, page, options) {
        // Define new QUnit Test
        QUnit.test(description + " - [" + page + "]", function (assert) {
            var done = assert.async();
            var url = window.rootDir + page;

            // GET Request Runs First then calls a POST
            function getRequest() {
                var xhr = new XMLHttpRequest();
                xhr.onload = function() {
                    assert.equal(xhr.status, 200, "GET HTTP Response Status Code");
                    postRequest();
                };
                xhr.open("GET", url, true);
                xhr.send();
            }

            // Handle POST, currently only a JSON Response is accepted
            function postRequest() {
                var xhr = new XMLHttpRequest();
                xhr.onload = function() {
                    assert.equal(xhr.status, 200, "POST HTTP Response Status Code");
                    try {
                        assert.deepEqual(JSON.parse(xhr.responseText), options.response, "Expected JSON Response");
                    } catch (e) {
                        assert.ok(false, "Error checking for Expected JSON Response, [xhr.responseText] = " + xhr.responseText);
                    }                    
                    done();
                };
                xhr.open("POST", url, true);
                xhr.send();
            }
            
            // Run the GET Request
            getRequest();
        });
    };

    // ---------------------------------------------------------------------------------
    // This function is intended to show Server Configuration Errors clearly
    // in a section above the QUnit Tests. In the default installation of FastSitePHP
    // this function can be called by the first two Unit Testing Routes.
    //
    // @param {array|null} errors	List of errors to show or if null a general message
    // ---------------------------------------------------------------------------------
    window.showServerConfigError = function (errors) {
        // Declare Variables
        var div,
            ul,
            li,
            n,
            m,
            p,
			errorMessage = "";

	    // Main error message that appears above the list
	    if (errors === null) {
			errorMessage = "WARNING - There was an unexpected error while checking response of unit test [Check Server Configuration]. Review the error in the Developer Console for more information.";
	    } else {
		    errorMessage = "WARNING - Server Settings are configured in a manner that will likely make some of the Unit Tests fail. FastSitePHP uses common and recommended settings so this message should not be seen on a Production Server. If you need help with specific errors review code comments in the file [php-test/test-app.php] and route [check-server-config].";
	    }

        // Get Element and Define Initial HTML the first time this function is called
        div = document.getElementById("server-config-error");
        p = div.querySelector("p");
        if (p === null) {
	       div.innerHTML = "<p></p><ul></ul>";
	       p = div.querySelector("p");
        }
        p.textContent = errorMessage;

        // Add each error to a Unordered List
        if (errors !== null) {
	        ul = div.querySelector("ul");
	        for (n = 0, m = errors.length; n < m; n++) {
	            li = document.createElement("LI");
	            li.textContent = errors[n];
	            ul.appendChild(li);
	        }
        }
        div.style.display = "";
    };
})();
