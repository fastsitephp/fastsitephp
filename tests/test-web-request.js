/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Request Object", "test-web-request.php/check-request-class", {
        response: {
            get_class: "FastSitePHP\\Web\\Request",
            get_parent_class: false
        }
    });
    
    runHttpUnitTest("Request Object - Properties", "test-web-request.php/check-request-properties", {
        response: "All properties matched for [FastSitePHP\\Web\\Request]: saved_input_stream"
    });
    
    runHttpUnitTest("Request Object - Functions", "test-web-request.php/check-request-methods", {
        response: "All methods matched for [FastSitePHP\\Web\\Request]: accept, acceptCharset, acceptEncoding, acceptHeader, acceptLanguage, clientIp, content, contentText, contentType, cookie, decryptedCookie, fixIp, form, header, headers, host, isLocal, isXhr, jwtCookie, method, origin, port, protocol, proxyHeader, queryString, referrer, serverIp, userAgent, value, verifiedCookie"
    });

    runHttpUnitTest("Request Object - method() - GET", "test-web-request.php/method", {
        method: "GET",
        response: "GET"
    });

    runHttpUnitTest("Request Object - method() - POST", "test-web-request.php/method", {
        method: "POST",
        response: "POST"
    });
    
    runHttpUnitTest("Request Object - POST Data 1 - Send and Receive JSON - Using contentText()", "test-web-request.php/post-data-1", {
        postData: JSON.stringify({ site: 'FastSitePHP', page: 'UnitTest' }),
        postType: 'application/json; charset=utf-8',
        response: { site: 'FastSitePHP', page: 'UnitTest' }
    });

    runHttpUnitTest("Request Object - POST Data 2 - Send and Receive JSON - Using content()", "test-web-request.php/post-data-2", {
        postData: JSON.stringify({ site: 'FastSitePHP', page: 'UnitTest2' }),
        postType: 'application/json; charset=utf-8',
        response: { site: 'FastSitePHP', page: 'UnitTest2' }
    });

    runHttpUnitTest("Request Object - POST Data 3 - Send and Receive JSON - Using route()", "test-web-request.php/post-data-3", {
        postData: JSON.stringify({ site: 'FastSitePHP', page: 'UnitTest3' }),
        postType: 'application/json; charset=utf-8',
        response: { site: 'FastSitePHP', page: 'UnitTest3' }
    });

    runHttpUnitTest("Request Object - POST Data 4 - Form POST and Return JSON from [form()]", "test-web-request.php/post-data-4", {
        postData: "site=FastSitePHP&page=UnitTest4",
        postType: 'application/x-www-form-urlencoded; charset=UTF-8',
        response: { notSet: null, site: 'FastSitePHP', page: 'UnitTest4' }
    });

    runHttpUnitTest("Request Object - POST Data 5 - Read Form Post with content()", "test-web-request.php/post-data-5", {
        postData: "site=FastSitePHP&page=UnitTest5",
        postType: 'application/x-www-form-urlencoded; charset=UTF-8',
        response: { site: 'FastSitePHP', page: 'UnitTest5' }
    });

    // Sending JSON data specified as a Form (This is an Invalid Request)
    runHttpUnitTest("Request Object - POST Data 6 - Invalid Type - Sending JSON as an HTML Form", "test-web-request.php/post-data-6", {
        postData: JSON.stringify({ site: 'FastSitePHP', page: 'UnitTest6' }),
        postType: 'application/x-www-form-urlencoded; charset=UTF-8',
        response: {
            "{\"site\":\"FastSitePHP\",\"page\":\"UnitTest6\"}": ""
        }
    });

    // Sending Form data specified as JSON (This is an Invalid Request)
    runHttpUnitTest("Request Object - POST Data 7 - Invalid Type - Sending an HTML Form as JSON", "test-web-request.php/post-data-7", {
        postData: "site=FastSitePHP&page=UnitTest7",
        postType: 'application/json; charset=utf-8',
        type: "text",
        response: "<null>"
    });

    // Send XML using Request Content-Type 'application/xml' and Receive back as text
    runHttpUnitTest("Request Object - POST Data 8 - Post XML and Receive Text", "test-web-request.php/post-data-8", {
        postData: "<test><site>FastSitePHP</site><page>UnitTest8</page></test>",
        postType: 'application/xml',
        type: "text",
        response: "<test><site>FastSitePHP</site><page>UnitTest8</page></test>"
    });

    // Send XML using Request Content-Type 'text/xml' and Receive back as XML
    runHttpUnitTest("Request Object - POST Data 9 - Post XML and Receive XML", "test-web-request.php/post-data-9", {
        postData: "<test><site>FastSitePHP</site><page>UnitTest9</page></test>",
        postType: 'text/xml',
        type: "xml",
        response: "<test><site>FastSitePHP</site><page>UnitTest9</page></test>"
    });

    // Send Text using Request Content-Type 'text/plain' and Receive back as plain text
    runHttpUnitTest("Request Object - POST Data 10 - Send and Receive Text", "test-web-request.php/post-data-10", {
        postData: "Test with plain text",
        postType: 'text/plain',
        type: "text",
        response: "Test with plain text"
    });

    // Send null data using Request Content-Type 'text/plain' and Receive back a zero length string
    runHttpUnitTest("Request Object - POST Data 11 - Send and Receive Null as Text", "test-web-request.php/post-data-11", {
        postData: null,
        postType: 'text/plain',
        type: "text",
        response: ""
    });

    // POST Data 12 uses an Immediately-invoked anonymous function
    // so variables related to the test are kept in scope only for the
    // test. This specific test also has a related C# program and a
    // Unix Shell Script for testing it because it cannot be fully
    // tested from a web-browser.
    (function () {
        var options,
            resultType;

        // The FormData API is not available in all browsers
        // (for example IE9 and below). If not avaiable just
        // post the data as a standard form. When the C# program
        // and Unix Shell Script post FormData the Request Header
        // 'Expect' will be set to '100-continue', however no
        // browsers at the time of writing send the header.
        if (window.FormData === undefined) {
            resultType = "form";

            options = {
                postType: "application/x-www-form-urlencoded",
                postData: "site=FastSitePHP&page=UnitTest12",
                type: "text"
            };
        } else {
            resultType = "form-data";
            var data = new FormData();
            data.append("site", "FastSitePHP");
            data.append("page", "UnitTest12");

            options = {
                postData: data,
                type: "text"
            };
        }

        // Copy values from the options object to a 2nd options object
        var options2 = {};
        for (var prop in options) {
            if (options.hasOwnProperty(prop)) {
                options2[prop] = options[prop];
            }
        }

        // Run the test for "Has Expect:100-continue"
        options.response = "Has Expect:100-continue: false\n";
        runHttpUnitTest("Request Object - POST Data 12 - Form Data", "test-web-request.php/post-data-12?data=Expect100", options);

        // Check the Form Data
        options2.response = "(" + resultType + "): [site=FastSitePHP] [page=UnitTest12]\n";
        runHttpUnitTest("Request Object - POST Data 12 - Form Data (" + resultType + ")", "test-web-request.php/post-data-12", options2);
    })();

    // Form Post - Using Quotes
    // This Test should only fail if using a version of PHP 5.3 with Magic Quotes turned on.
    runHttpUnitTest("Request Object - POST Data 13 - Form POST and Return JSON", "test-web-request.php/post-data-13", {
        postData: "site=FastSitePHP&page='UnitTest_With_Quotes'",
        postType: 'application/x-www-form-urlencoded; charset=UTF-8',
        response: { site: 'FastSitePHP', page: "'UnitTest_With_Quotes'" }
    });

    runHttpUnitTest("Request Object - Query String Parmaeters using [Request->queryString()]", "test-web-request.php/query-string?param1=123", {
        response: {
            param1: "123",
            param1AsInt: 123,
            missing: null,
        }
    });

    runHttpUnitTest("Request Object - Cross-Origin Resource Sharing (CORS) with origin() - GET", "test-web-request.php/cors-origin", {
        response: "Cross-Origin Resource Sharing (CORS) Test with origin()",
        responseHeaders: [
            { name: "Allow", value: null },
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Methods", value: null }
        ]
    });

    runHttpUnitTest("Request Object - Cross-Origin Resource Sharing (CORS) with origin() - OPTIONS", "test-web-request.php/cors-origin", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "GET, HEAD, OPTIONS, POST" },
            { name: "Access-Control-Allow-Origin", value: window.location.origin },
            { name: "Access-Control-Allow-Methods", value: "GET, HEAD, OPTIONS, POST" }
        ]
    });
    
    runHttpUnitTest("Request Object - Checking origin() function", "test-web-request.php/check-default-origin", {
        response: "Function origin() returned null"
    });

    //By default jQuery and most major JavaScript Frameworks will set this header
    runHttpUnitTest("Request Object - HTTP_X_REQUESTED_WITH - Test 1 - Expected Header", "test-web-request.php/xhr?_=" + (new Date()).getTime() + "_" + Math.random(), {
        requestHeaders: [
            { name: "X-Requested-With", value: "XMLHttpRequest" }
        ],
        response: {
            isXhr: true,
            HTTP_X_REQUESTED_WITH: "XMLHttpRequest",
            header: "XMLHttpRequest"
        }
    });

    runHttpUnitTest("Request Object - HTTP_X_REQUESTED_WITH - Test 2 - Changing Header Value", "test-web-request.php/xhr?_=" + (new Date()).getTime() + "_" + Math.random(), {
        requestHeaders: [
            { name: "X-Requested-With", value: "Test" }
        ],
        response: {
            isXhr: false,
            HTTP_X_REQUESTED_WITH: "Test",
            header: "Test"
        }
    });

    runHttpUnitTest("Request Object - HTTP_X_REQUESTED_WITH - Test 3 - Missing Request Header", "test-web-request.php/xhr?_=" + (new Date()).getTime() + "_" + Math.random(), {
        response: {
            isXhr: false,
            HTTP_X_REQUESTED_WITH: "<Null>",
            header: null
        }
    });
    
    runHttpUnitTest("Request Object - Header Test - Comparing headers() to header() functon for a GET Request", "test-web-request.php/compare-headers-to-header", {
        type: "text",
        response: "All Request Header Values Matched between headers() and header(), Request Type = GET"
    });

    runHttpUnitTest("Request Object - Header Test - Comparing headers() to header() functon for a POST Request", "test-web-request.php/compare-headers-to-header", {
        postData: "site=FastSitePHP",
        postType: 'application/x-www-form-urlencoded; charset=UTF-8',
        type: "text",
        response: "All Request Header Values Matched between headers() and header(), Request Type = POST"
    });
    
	runHttpUnitTest("Request Object - value() - Running 104 Total Tests on the Server", "test-web-request.php/value", {
	    response: "Success for value() function, Completed 101 Unit Tests and 4 Exception Tests"
	});
	
	runHttpUnitTest("Request Object - value() - Testing Data", "test-web-request.php/value2", {
	    response: {
	        "input1": "test",
	        "input2": 123.456,
	        "missing": "",
	        "checkbox1": 1,
	        "missing-checkbox": 0,
	        "checkbox1-bool": true,
	        "app": "FastSitePHP",
	        "string-string?": "abc",
	        "string-int": 0,
	        "string-int?": null,
	        "number-int": 123,
	        "items-0-name": "item1",
	        "items-1-name": "item2",
	        "items-2-name": null
	    }
	});
	
    // Check that the submitted [User-Agent] Header obtained from Request Object 
    // function [userAgent()] matches the Brower's [navigator.userAgent] property.
    //
    // This test uses an Immediately-invoked anonymous function so 
    // variables related to the test are kept in scope only for the test. 
    //
    // In IE 11 the Submitted Request Header will likely be different then what
    // is available to the Browser so check userAgent if IE 11 and then replicate
    // what is likely being sent to the server:
    //   https://blogs.msdn.microsoft.com/ieinternals/2013/09/21/internet-explorer-11s-many-user-agent-strings/
    (function () {
        // Get userAgent from Browser
        var userAgent = navigator.userAgent,
            checkString,
            pos1,
            pos2;

        // IE 11 with Touch Support?
        checkString = " Trident/7.0; Touch;";
        pos1 = userAgent.indexOf(checkString);

        // IE 11 without Touch Support?
        if (pos1 === -1) {
            checkString = " Trident/7.0;";
            pos1 = userAgent.indexOf(checkString);
        }

        // If IE 11 then update the string value to check for the response
        if (pos1 !== -1) {
            pos2 = userAgent.indexOf(" rv:11");
        }
        if (pos1 !== -1 && pos2 !== -1) {
            userAgent = userAgent.substr(0, pos1 + checkString.length) + userAgent.substr(pos2);
        }

        // Submit Request and Check Result
        runHttpUnitTest("Request Object - [User-Agent] from Browser", "test-web-request.php/user-agent?_=" + (new Date()).getTime(), {
            type: "text",
            response: userAgent
        });
    })();
	
	runHttpUnitTest("Request Object - [Referer] from Browser", "test-web-request.php/referrer", {
	    type: "text",
	    response: document.URL
	});
	
	runHttpUnitTest("Request Object - Headers Cleared on Server", "test-web-request.php/missing-headers", {
	    type: "text",
	    response: "[userAgent():null][referrer():null]"
	});
	
	runHttpUnitTest("Request Object - Header 'Accept' using the function [accept()]", "test-web-request.php/accept", {
	    response: {
	        header: "text\/html, application\/xhtml+xml, application\/xml;q=0.9,image\/webp,*\/*;q=0.8",
	        value: [
	            { value: "text\/html", quality: null },
	            { value: "application\/xhtml+xml", quality: null },
	            { value: "application\/xml", quality: 0.9 },
	            { value: "image\/webp", quality: null },
	            { value: "*\/*", quality: 0.8 }
	        ],
	        search_true: true,
	        search_false: false,
	        empty_value: [],
	        empty_search: false
	    }
	});
	
	runHttpUnitTest("Request Object - Header 'Accept-Charset' using the function [acceptCharset()]", "test-web-request.php/accept-charset", {
	    response: {
	        header: "ISO-8859-1,utf-8;q=0.7,*;q=0.7",
	        value: [
	            { value: "ISO-8859-1", quality: null },
	            { value: "utf-8", quality: 0.7 },
	            { value: "*", quality: 0.7 }
	        ],
	        search_true: true,
	        search_false: false,
	        empty_value: [],
	        empty_search: false
	    }
	});
	
	runHttpUnitTest("Request Object - Header 'Accept-Encoding' using the function [acceptEncoding()]", "test-web-request.php/accept-encoding", {
	    response: {
	        header: "gzip, deflate, sdch",
	        value: [
	            { value: "gzip", quality: null },
	            { value: "deflate", quality: null },
	            { value: "sdch", quality: null }
	        ],
	        search_true: true,
	        search_false: false,
	        empty_value: [],
	        empty_search: false
	    }
	});
	
	runHttpUnitTest("Request Object - Header 'Accept-Language' using the function [acceptLanguage()]", "test-web-request.php/accept-language", {
	    response: {
	        header: "ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4",
	        value: [
	            { value: "ru-RU", quality: null },
	            { value: "ru", quality: 0.8 },
	            { value: "en-US", quality: 0.6 },
	            { value: "en", quality: 0.4 }
	        ],
	        search_true: true,
	        search_false: false,
	        empty_value: [],
	        empty_search: false
	    }
	});

	runHttpUnitTest("Request Object - clientIp()", "test-web-request.php/client-ip", {
	    type: "text",
	    response: "[type:string][is_ip:true][null_check:true]"
	});
	
	runHttpUnitTest("Request Object - serverIp()", "test-web-request.php/server-ip", {
	    type: "text",
	    response: "[type:string][is_ip:true][LOCAL_ADDR:true][SERVER_ADDR:true]"
	});
	
	runHttpUnitTest("Request Object - isLocal()", "test-web-request.php/is-local", {
	    response: {
	        test_ipv4: true,
	        test_ipv6: true,
	        test_mixed_local: true,
	        test_server: false,
	        test_client: false,
	        test_no_local: false
	    }
	});

    runHttpUnitTest("Request Object - Compare rootDir() with protocol(), host(), and port() for the Current Server", "test-web-request.php/compare-rootdir-protocol-host-port", {
        response: "Test is valid when comparing protocol(), host(), and port() with rootDir()"
    });

})();
