/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest, rootDir */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Response Object", "test-web-response.php/check-response-class", {
        response: {
            get_class: "FastSitePHP\\Web\\Response",
            get_parent_class: false
        }
    });
    
    runHttpUnitTest("Response Object - Properties", "test-web-response.php/check-response-properties", {
        type: "text",
        response: "All properties matched for [FastSitePHP\\Web\\Response]: etag_type, header_fields, json_options, jsonp_query_string, response_content, response_cookies, response_file, status_code"
    });
    
    runHttpUnitTest("Response Object - Functions", "test-web-response.php/check-response-methods", {
        type: "text",
        response: "All methods matched for [FastSitePHP\\Web\\Response]: __construct, cacheControl, clearCookie, content, contentType, cookie, cookies, cors, dateHeader, encryptedCookie, etag, expires, file, fileTypeToMimeType, header, headers, json, jsonOptions, jsonpQueryString, jwtCookie, lastModified, noCache, redirect, reset, send, signedCookie, statusCode, vary"
    });

    runHttpUnitTest("Response Object - content() - HTML", "test-web-response.php/content-html", {
        response: "<h1>Content() Test</h1>"
    });

    runHttpUnitTest("Response Object - contentType() - HTML", "test-web-response.php/content-type-html", {
        type: null,
        response: "<h1>HTML Test using Response contentType()</h1>",
        responseHeaders: [
            { name: "Content-Type", value: "text/html; charset=UTF-8" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() - HTML with Charset ISO-8859-1", "test-web-response.php/content-type-html-charset", {
        type: null,
        response: "<h1>HTML Test using Response contentType() with Charset</h1>",
        responseHeaders: [
            { name: "Content-Type", value: "text/html; charset=ISO-8859-1" }
        ]
    });

    runHttpUnitTest("Response Object - content() - JSON from String", "test-web-response.php/content-json-string", {
        response: {
            Name: "FastSitePHP_Response",
            CreatedFrom: "String"
        }
    });

    runHttpUnitTest("Response Object - content() - JSON from Array", "test-web-response.php/content-json-array", {
        response: {
            Name: "FastSitePHP_Response",
            CreatedFrom: "Array"
        }
    });

    runHttpUnitTest("Response Object - content() - JSON from Object", "test-web-response.php/content-json-object", {
        response: {
            Name: "FastSitePHP_Response",
            CreatedFrom: "stdClass"
        }
    });

    runHttpUnitTest("Response Object - content() - JSON from User-Defined Class", "test-web-response.php/content-json-custom", {
        response: {
            Name: "FastSitePHP_Response",
            CreatedFrom: "CustomClass",
            IntValue: 123,
            BoolValue: true
        }
    });

    runHttpUnitTest("Response Object - json() - JSON Response with an Array", "test-web-response.php/content-json-func-with-array", {
        response: {
            test:123
        }
    });

    runHttpUnitTest("Response Object - json() - JSON Response with a User-Defined Class", "test-web-response.php/content-json-func-with-obj", {
        response: {
            Name: "Response_JSON",
            CreatedFrom: "CustomClass",
            IntValue: 123,
            BoolValue: true
        }
    });    

    runHttpUnitTest("Response Object - json() - JSON Response with a User-Defined Class", "test-web-response.php/content-json-func-with-stdclass", {
        response: {
            Name: "JSON_Response",
            CreatedFrom: "stdClass"
        }
    });

    runHttpUnitTest("Response Object - json() - Error", "test-web-response.php/content-json-func-error", {
        status: 500,
        responseContains: '<td class="error-message">Error - Invalid Parameter at [FastSitePHP\\Web\\Response-&gt;json()]. Expected and Array or Object but was passed a [string].</td>'
    });

    runHttpUnitTest("Response Object - contentType() - Text - UTF-8 Encoding", "test-web-response.php/text-charset", {
        type: null,
        response: "Plain Text Response Using UTF-8 Encoding",
        responseHeaders: [
            { name: "Content-Type", value: "text/plain; charset=UTF-8" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() - JavaScript - Test 1", "test-web-response.php/javascript1", {
        type: null,
        response: "alert('JavaScript_Response_1');",
        responseHeaders: [
            { name: "Content-Type", value: "application/javascript" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() - JavaScript - Test 2", "test-web-response.php/javascript2", {
        type: null,
        response: "alert('JavaScript_Response_2');",
        responseHeaders: [
            { name: "Content-Type", value: "application/javascript; charset=UTF-8" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() - CSS - Test 1", "test-web-response.php/css1", {
        type: null,
        response: "div { border:2px solid red; }",
        responseHeaders: [
            { name: "Content-Type", value: "text/css" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() - CSS - Test 2", "test-web-response.php/css2", {
        type: null,
        response: "div { border:2px solid blue; }",
        responseHeaders: [
            { name: "Content-Type", value: "text/css; charset=UTF-8" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() - XML - Return Type String", "test-web-response.php/xml-string", {
        type: "xml",
        response: "<test>XML Test using Response Object</test>"
    });

    runHttpUnitTest("Response Object - contentType() - XML - Return Type SimpleXMLElement", "test-web-response.php/xml-simplexml", {
        type: "xml",
        response: "<test>SimpleXML Test using Response Object</test>"
    });

    runHttpUnitTest("Response Object - contentType() - XML - Return Type XMLWriter", "test-web-response.php/xml-xmlwriter", {
        type: "xml",
        response: "<test>XMLWriter Test using Response Object</test>"
    });

    runHttpUnitTest("Response Object - contentType() - Encoding Parameter", "test-web-response.php/content-type-encoding", {
        type: "text",
        response: "Success, all 20 tests passed"
    });

    runHttpUnitTest("Response Object - contentType() - Invalid HTML Charset", "test-web-response.php/content-type-error-invalid-html-charset", {
        type: "text",
        response: "Invalid option for [FastSitePHP\\Web\\Response->contentType()]. Null which defaults to [UTF-8] or only widely used charsets are support as the option. The $type parameter was [html] and the invalid $option parameter was [CHARSET]. Valid Options for this function are [UTF-8], [ISO-8859-1], [GB2312], [Shift_JIS], [GBK]. Additional charsets can be defined if specifying the full [Content-Type] header as the first parameter when calling this function."
    });

    runHttpUnitTest("Response Object - contentType() - Invalid JSON Charset", "test-web-response.php/content-type-invalid-json-charset", {
        type: "text",
        response: "Invalid option for [FastSitePHP\\Web\\Response->contentType()]. The only content types that allow for an option to be specified are [html], [javascript], [css], [text], and [jsonp]. The $type parameter was [json] and the invalid $option parameter was [UTF-8]."
    });

    runHttpUnitTest("Response Object - contentType() - Invalid XML Charset", "test-web-response.php/content-type-invalid-xml-charset", {
        type: "text",
        response: "Invalid option for [FastSitePHP\\Web\\Response->contentType()]. The only content types that allow for an option to be specified are [html], [javascript], [css], [text], and [jsonp]. The $type parameter was [xml] and the invalid $option parameter was [UTF-8]."
    });

    runHttpUnitTest("Response Object - contentType() - Invalid Encoding with Full Header", "test-web-response.php/content-type-invalid-charset-with-full-header", {
        type: "text",
        response: "Invalid option for [FastSitePHP\\Web\\Response->contentType()]. The only content types that allow for an option to be specified are [html], [javascript], [css], [text], and [jsonp]. The $type parameter was [text/html] and the invalid $option parameter was [UTF-8]."
    });

    runHttpUnitTest("Response Object - contentType() - Error calling JSONP with an Int Option", "test-web-response.php/content-type-invalid-jsonp-option-int", {
        type: "text",
        response: "Unexpected parameter $value for [FastSitePHP\\Web\\Response->jsonpQueryString()], expected [string|array|null] but was passed [integer]"
    });

    runHttpUnitTest("Response Object - contentType() - Error calling JSONP with an Empty Array Option", "test-web-response.php/content-type-invalid-jsonp-option-empty-array", {
        type: "text",
        response: "Error with the parameter $value for [FastSitePHP\\Web\\Response->jsonpQueryString()], when passing an array as the parameter the array must have at least one or more values. The array passed to this function was empty."
    });

    runHttpUnitTest("Response Object - contentType() - Error calling JSONP with an Empty String Option", "test-web-response.php/content-type-invalid-jsonp-option-empty-string", {
        type: "text",
        response: "Error with the parameter $value for [FastSitePHP\\Web\\Response->jsonpQueryString()], when passing a string as the parameter the value cannot be empty."
    });

    runHttpUnitTest("Response Object - contentType() - Error changing from JSONP to JavaScript", "test-web-response.php/content-type-error-changing-jsonp-to-javascript", {
        status: 500,
        responseContains: '<td class="error-message">Unexpected Response Content Variable Type set when [FastSitePHP\\Web\\Response-&gt;content()] was called. If contentType() is [json] or [jsonp] then content() can be set with a string or any type that can be encoded to a JSON string such as an object or an array, however for all other response types that do not use a file response the content() must be a [string] type. At the time of the response the contentType() was set to [application/javascript] and the type of content set was a [array] type.</td>'
    });

    runHttpUnitTest("Response Object - contentType() - Check Content Types set with [contentType()] using [fileTypeToMimeType()]", "test-web-response.php/content-type-from-mime-type", {
        type: "text",
        response: "[htm=text/html][md=text/markdown][markdown=text/markdown][csv=text/csv][jsx=text/jsx][png=image/png][gif=image/gif][webp=image/webp][jpg=image/jpg][jpeg=image/jpg][svg=image/svg+xml][ico=image/x-icon][woff=application/font-woff][pdf=application/pdf][mp4=video/mp4][webm=video/webm][ogv=video/ogg][flv=video/x-flv][mp3=audio/mp3][weba=audio/weba][ogg=audio/ogg][m4a=audio/aac][aac=audio/aac]"
    });

    runHttpUnitTest("Response Object - contentType() - Custom Content type", "test-web-response.php/custom-content-type", {
        response: "text/template"
    });

    runHttpUnitTest("Response Object - contentType() - Error - Invalid Content Type", "test-web-response.php/invalid-content-type", {
        status: 500,
        responseContains: '<td class="error-message">Error - Invalid option [template] sepecified for [FastSitePHP\\Web\\Response-&gt;contentType()]. Valid values include [html, json, jsonp, text, css, javascript, xml], any type from function [Response-&gt;fileTypeToMimeType()], or the actual content type for example [video/mp4].</td>'
    });

    runHttpUnitTest("Response Object - contentType() = JSONP - Test 1a", "test-web-response.php/jsonp1?callback=test", {
        type: null,
        response: '/**/test({"data":"jsonp1"});',
        responseHeaders: [
            { name: "Content-Type", value: "application/javascript" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() = JSONP - Test 1b", "test-web-response.php/jsonp1?jsonp=testb", {
        type: null,
        response: '/**/testb({"data":"jsonp1"});',
        responseHeaders: [
            { name: "Content-Type", value: "application/javascript" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() = JSONP - Test 2", "test-web-response.php/jsonp2?fn=test2", {
        type: null,
        response: '/**/test2({"prop_name":"jsonp2"});',
        responseHeaders: [
            { name: "Content-Type", value: "application/javascript" }
        ]
    });

    runHttpUnitTest("Response Object - contentType() = JSONP - Test 3", "test-web-response.php/jsonp3?callback=test3", {
        type: null,
        response: '/**/test3({"data":"jsonp3"});',
        responseHeaders: [
            { name: "Content-Type", value: "application/javascript; charset=UTF-8" }
        ]
    });

    runHttpUnitTest("Response Object - JSONP Callback Test - Valid Result", "test-web-response.php/jsonp-callback-test?callback=jQuery123_456", {
        type: null,
        response: '/**/jQuery123_456({"data":"jsonp"});',
        responseHeaders: [
            { name: "Content-Type", value: "application/javascript" }
        ]
    });

    runHttpUnitTest("Response Object - JSONP Callback Test - Error Result - Missing Callback", "test-web-response.php/jsonp-callback-test", {
        status: 500,
        responseContains: '[jsonp] was specified as the content-type however a JavaScript function was not found in one of the query string parameters: callback, jsonp'
    });

    runHttpUnitTest("Response Object - JSONP Callback Test - Error Result - Empty String", "test-web-response.php/jsonp-callback-test?callback=", {
        status: 500,
        responseContains: 'The [jsonp] callback query string parameter [callback] was defined however it did not contain a function name and was instead an empty string.'
    });

    runHttpUnitTest("Response Object - JSONP Callback Test - Error Result - Invalid Function 1", "test-web-response.php/jsonp-callback-test?callback=123", {
        status: 500,
        responseContains: 'The [jsonp] callback function was not using a format supported. The function name must contain only letters, numbers, or the underscore character; it must be at least two characters in length and cannot start with a number. Query String Parameter [callback] and Value [123]'
    });

    runHttpUnitTest("Response Object - JSONP Callback Test - Error Result - Invalid Function 2", "test-web-response.php/jsonp-callback-test?callback=a", {
        status: 500,
        responseContains: 'The [jsonp] callback function was not using a format supported. The function name must contain only letters, numbers, or the underscore character; it must be at least two characters in length and cannot start with a number. Query String Parameter [callback] and Value [a]'
    });

    runHttpUnitTest("Response Object - JSONP Callback Test - Unicode Control Characters are Escaped", "test-web-response.php/jsonp-escape-characters?callback=callback", {
        type: null,
        response: '/**/callback({"string1":"Test1 \\u2028 Test2","string2":"Test1 \\u2029 Test2"});',
        responseHeaders: [
            { name: "Content-Type", value: "application/javascript" }
        ]
    });

    // Expires and Last-Modified need to be moved to seperate tests.
    // When running the test page multiple times web browsers will often
    // try to cache the page, the query string "?_={time}" prevents this from happening.
    // Some older versions of Edge Browser will show an error on this Test as it considers
    // these values to not be compatible (the older date). At the time of writing the latest
    // Version of Edge should accept all of these headers values.
    runHttpUnitTest("Response Object - Cache Headers Test - 1a - Setting all headers", "test-web-response.php/cache-headers-1?_=" + (new Date()).getTime(), {
        response: "cache-headers-1",
        responseHeaders: [
            { name: "Cache-Control", value: "public, max-age=86400" },
            { name: "ETag", value: 'W/"89da1dc9504f54ee76041b0f21e28b92"' },
            { name: "Expires", value: "Thu, 06 Aug 2015 00:00:00 GMT" },
            { name: "Last-Modified", value: "Wed, 05 Aug 2015 00:00:00 GMT" }
        ]
    });
    
    runHttpUnitTest("Response Object - Cache Headers Test - 1b - 304 Response for ETag", "test-web-response.php/cache-headers-1", {
        status: 304,
        type: null,
        response: "",
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"89da1dc9504f54ee76041b0f21e28b92"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 1c - 200 Response for different ETag", "test-web-response.php/cache-headers-1", {
        response: "cache-headers-1",
        requestHeaders: [
            { name: "If-None-Match", value: '"abc123"' }
        ],
        responseHeaders: [
            { name: "Cache-Control", value: "public, max-age=86400" },
            { name: "ETag", value: 'W/"89da1dc9504f54ee76041b0f21e28b92"' },
            { name: "Expires", value: "Thu, 06 Aug 2015 00:00:00 GMT" },
            { name: "Last-Modified", value: "Wed, 05 Aug 2015 00:00:00 GMT" }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 1d - 304 Response for ETag Array", "test-web-response.php/cache-headers-1", {
        status: 304,
        type: null,
        response: "",
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"89da1dc9504f54ee76041b0f21e28b92", "abc123"' }
        ]
    });

    // Per HTTP Protocal a special 'If-None-Match' value of '*' exists to match any
    // resource however it is intended only on being used for PUT requests. Many popular 
    // frameworks incorrectly send a 304 for '*' GET Requests. This tests verifies that
    // FastSitePHP does not return a 304 when using '*'
    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.26
    runHttpUnitTest("Response Object - Cache Headers Test - 1e - 200 Response for Etag *", "test-web-response.php/cache-headers-1", {
        response: "cache-headers-1",
        requestHeaders: [
            { name: "If-None-Match", value: '*' }
        ],
        responseHeaders: [
            { name: "ETag", value: 'W/"89da1dc9504f54ee76041b0f21e28b92"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 1f - HEAD Test", "test-web-response.php/cache-headers-1", {
        method: "HEAD",
        response: "",
        responseHeaders: [
            { name: "Cache-Control", value: "public, max-age=86400" },
            { name: "ETag", value: 'W/"89da1dc9504f54ee76041b0f21e28b92"' },
            { name: "Expires", value: "Thu, 06 Aug 2015 00:00:00 GMT" },
            { name: "Last-Modified", value: "Wed, 05 Aug 2015 00:00:00 GMT" }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 2a - 200 Response", "test-web-response.php/cache-headers-2?_=" + (new Date()).getTime() + "_" + Math.random(), {
        response: "cache-headers-2",
        responseHeaders: [
            { name: "Last-Modified", value: "Wed, 05 Aug 2015 00:00:00 GMT" }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 2b - 304 Response", "test-web-response.php/cache-headers-2?_=" + (new Date()).getTime() + "_" + Math.random(), {
        status: 304,
        type: null,
        response: "",
        requestHeaders: [
            { name: "If-Modified-Since", value: "Wed, 05 Aug 2015 00:00:00 GMT" }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 3a - ETag Function Test", "test-web-response.php/cache-headers-3?_=" + (new Date()).getTime(), {
        response: "cache-headers-3",
        responseHeaders: [
            { name: "ETag", value: 'W/"9691c62f7e3a1af96f955ca4a858d8c9"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 3b - 304 Response for ETag Function", "test-web-response.php/cache-headers-3", {
        status: 304,
        type: null,
        response: "",
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"9691c62f7e3a1af96f955ca4a858d8c9"' }
        ]
    });

    // NOTE - for info on the query string variable see comments above in "cache-headers-1?_="
    runHttpUnitTest("Response Object - Cache Headers Test - 3c - Strong ETag Function Test", "test-web-response.php/cache-headers-3-strong?_=" + (new Date()).getTime(), {
        response: "cache-headers-3-strong",
        responseHeaders: [
            { name: "ETag", value: '"90e4242da027108b4a79ae6b086d459a"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 3d - 304 Response for Strong ETag Function", "test-web-response.php/cache-headers-3-strong", {
        status: 304,
        type: null,
        response: "",
        requestHeaders: [
            { name: "If-None-Match", value: '"90e4242da027108b4a79ae6b086d459a"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 3e - ETag Function Test using a hash string 'hash:md5'", "test-web-response.php/cache-headers-3-v2", {
        response: "cache-headers-3",
        responseHeaders: [
            { name: "ETag", value: 'W/"9691c62f7e3a1af96f955ca4a858d8c9"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 3f - ETag Function Error Test using a hash string 'hash:error'", "test-web-response.php/cache-headers-3-v2-error", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">Error calling [FastSitePHP\\Web\\Response-&gt;etag()] using the parameter [hash:error]. Invalid hash, the required format for this function when specifying a hash is &quot;hash:{algorithim}&quot;, for example &quot;hash:md5&quot;. Any algorithm registered in the PHP function hash_algos() can be used.</td>',
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 4 - ETag Quote", "test-web-response.php/cache-headers-4", {
        response: "cache-headers-4",
        responseHeaders: [
            { name: "ETag", value: 'W/"8b20e97e6181fee408af74a3a186195f"' }
        ]
    });

    // For info on the query string variable see comments above in "cache-headers-1?_="
    runHttpUnitTest("Response Object - Cache Headers Test - 5a - Strong ETag", "test-web-response.php/cache-headers-5?_=" + (new Date()).getTime(), {
        response: "cache-headers-5",
        responseHeaders: [
            { name: "ETag", value: '"f7c00370398923313f4b36c4b35ddc8b"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 5b - 304 Response for Strong ETag", "test-web-response.php/cache-headers-5", {
        status: 304,
        type: null,
        response: "",
        requestHeaders: [
            { name: "If-None-Match", value: '"f7c00370398923313f4b36c4b35ddc8b"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 6 - Error Validation", "test-web-response.php/cache-headers-6", {
        type: "text",
        response: "Invalid parameter $last_modified_time for [FastSitePHP\\Web\\Response->lastModified('abc')]. The parameter must be a valid value for the php function strtotime()"
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 7 - Error Validation", "test-web-response.php/cache-headers-7", {
        type: "text",
        response: "Unexpected parameter $last_modified_time for [FastSitePHP\\Web\\Response->lastModified()], expected [string|int|null] but was passed [boolean]"
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 8 - Error Validation", "test-web-response.php/cache-headers-8", {
        type: "text",
        response: "Invalid parameter $expires_time for [FastSitePHP\\Web\\Response->expires('abc')]. The parameter must be a valid value for the php function strtotime()"
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 9 - Error Validation", "test-web-response.php/cache-headers-9", {
        type: "text",
        response: "Unexpected parameter $expires_time for [FastSitePHP\\Web\\Response->expires()], expected [string|int|null] but was passed [object]"
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 10 - Error Validation", "test-web-response.php/cache-headers-10", {
        type: "text",
        response: "Unexpected parameter $value for [FastSitePHP\\Web\\Response->etag()], expected [string|Closure|null] but was passed [object]"
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 11 - Error Validation", "test-web-response.php/cache-headers-11", {
        type: "text",
        response: "Incorrect parameter $type for [FastSitePHP\\Web\\Response->etag()]; $type must be specified as either 'strong' or 'weak'"
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 12 - Error Validation", "test-web-response.php/cache-headers-12", {
        status: 500,
        responseContains: "The ETag function defined by the app should return a string but instead returned a [integer]"
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 13a - NoCache with ETag", "test-web-response.php/cache-headers-13", {
        response: "cache-headers-13",
        responseHeaders: [
            { name: "Cache-Control", value: "no-cache, no-store, must-revalidate" },
            { name: "Pragma", value: "no-cache" },
            { name: "Expires", value: "-1" },
            { name: "ETag", value: 'W/"5bb2a00515ea7956b15d4d041331b28c"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 13b - 200 Response for NoCache with ETag", "test-web-response.php/cache-headers-13", {
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"5bb2a00515ea7956b15d4d041331b28c"' }
        ],
        response: "cache-headers-13",
        responseHeaders: [
            { name: "Cache-Control", value: "no-cache, no-store, must-revalidate" },
            { name: "Pragma", value: "no-cache" },
            { name: "Expires", value: "-1" },
            { name: "ETag", value: 'W/"5bb2a00515ea7956b15d4d041331b28c"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 14 - 200 Response for [Cache-Control:no-store] with ETag", "test-web-response.php/cache-headers-14", {
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"dec7d325ab643cb83c7cd904ccfe8a72"' }
        ],
        response: "cache-headers-14",
        responseHeaders: [
            { name: "ETag", value: 'W/"dec7d325ab643cb83c7cd904ccfe8a72"' }
        ],
        // NOTE - the code sends only "no-store" however the server may modify it
        callback: function (xhr, assert) {
            var value = xhr.getResponseHeader("Cache-Control");
            var isValid = (value === "no-store" || value === "no-store, max-age=0");
            assert.ok(isValid, "Header Value for [Cache-Control]: " + value);
        },
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 15 - 200 Response for [Expires:0] with ETag", "test-web-response.php/cache-headers-15", {
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"7788a57625fd27bd321d87452699ef0b"' }
        ],
        response: "cache-headers-15",
        responseHeaders: [
            { name: "Expires", value: "0" },
            { name: "ETag", value: 'W/"7788a57625fd27bd321d87452699ef0b"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 16 - 200 Response for [Pragma:no-cache] with ETag", "test-web-response.php/cache-headers-16", {
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"b7e4648f45cf2445ed142321f95f9083"' }
        ],
        response: "cache-headers-16",
        responseHeaders: [
            { name: "Pragma", value: "no-cache" },
            { name: "ETag", value: 'W/"b7e4648f45cf2445ed142321f95f9083"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 17 - 500 Response not cached with ETag", "test-web-response.php/cache-headers-17", {
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"902b0d55fddef6f8d651fe1035b7d4bd"' }
        ],
        status: 500,
        response: "Error",
        responseHeaders: [
            { name: "ETag", value: 'W/"902b0d55fddef6f8d651fe1035b7d4bd"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 18 - 200 Response for POST with ETag", "test-web-response.php/cache-headers-18", {
        postData: "site=FastSitePHP",
        postType: 'application/x-www-form-urlencoded; charset=UTF-8',
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"d8dcae11324a659707f90373e69f5c1b"' }
        ],
        response: "cache-headers-18",
        responseHeaders: [
            { name: "ETag", value: 'W/"d8dcae11324a659707f90373e69f5c1b"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 19 - Error Validation", "test-web-response.php/cache-headers-19", {
        status: 500,
        responseContains: "Invalid value for the header [Last-Modified] which was likely set by calling the header() function. If using a string value then the parameter must be a valid value for the php function strtotime(), the value specified was: [abc]"
    });

    // This unit tests confirms that sending bad data for the 'If-Modified-Since' header
    // does not cause an error on the server
    runHttpUnitTest("Response Object - Cache Headers Test - 20 - 200 Response for an Invalid 'If-Modified-Since' header", "test-web-response.php/cache-headers-2", {
        status: 200,
        response: "cache-headers-2",
        requestHeaders: [
            { name: "If-Modified-Since", value: "abc" }
        ],
        responseHeaders: [
            { name: "Last-Modified", value: "Wed, 05 Aug 2015 00:00:00 GMT" }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 20 - 'Expires' header with '0'", "test-web-response.php/cache-headers-20", {
        response: "cache-headers-20",
        responseHeaders: [
            { name: "Expires", value: '0' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 21 - 'Expires' header with '-1'", "test-web-response.php/cache-headers-21", {
        response: "cache-headers-21",
        responseHeaders: [
            { name: "Expires", value: '-1' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 22 - ETag Quote at Start of String", "test-web-response.php/cache-headers-22", {
        response: "cache-headers-22",
        responseHeaders: [
            { name: "ETag", value: 'W/""04c4053c6a84aa2ee96ccf7b1f1a7a20"' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 23 - ETag Quote at End of String", "test-web-response.php/cache-headers-23", {
        response: "cache-headers-23",
        responseHeaders: [
            { name: "ETag", value: 'W/"35ba15140b5837808203b221df847673""' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 24 - cacheControl() function", "test-web-response.php/cache-headers-24", {
        response: [
            { option: "public", isError: false },
            { option: "private", isError: false },
            { option: "private, max-age=60", isError: false },
            { option: "no-cache, no-store, must-revalidate", isError: false },
            { option: "private=Server", isError: false },
            { option: "no-cache=Server", isError: false },
            { option: 'private="Server"', isError: false },
            { option: 'private="public, max-age", max-age=60', isError: false },
            { option: "unknown", isError: true, errorMessage: "Cache Control Extensions are not supported the cacheControl() function, please check that the value is not a typo and if user defined fields are needed then use the header() function instead. The cacheControl() parameter was [unknown] and the invalid field name is [unknown]." },
            { option: "private, public", isError: true, errorMessage: "A Cache-Control header value cannot have both [public] and [private] specified. Please check the value and if needed use the header() function instead. The cacheControl() parameter was [private, public]." },
            { option: "private, max-age=abc", isError: true, errorMessage: "The cacheControl() function was called with an invalid option for [max-age], the value must be specified as a time in seconds using integer format. The cacheControl() parameter was [private, max-age=abc]." },
            { option: "private, s-maxage=-1", isError: true, errorMessage: "The cacheControl() function was called with a negative number for [s-maxage], the value must be specified as a time in seconds using integer format and must be 0 or greater. The cacheControl() parameter was [private, s-maxage=-1]." },
            { option: "public, no-store", isError: true, errorMessage: "A Cache-Control header value cannot have [no-store] set with either [public] or [private] specified. Please check the value and if needed use the header() function instead. The cacheControl() parameter was [public, no-store]." },
            { option: "private, no-store", isError: true, errorMessage: "A Cache-Control header value cannot have [no-store] set with either [public] or [private] specified. Please check the value and if needed use the header() function instead. The cacheControl() parameter was [private, no-store]." },
            { option: "public=Server", isError: true, errorMessage: "A Cache Control Option was set with a field value for an option that does not support field values. Please check the options specified and if needed use the header() function instead. The cacheControl() parameter was [public=Server] and the option name with a field value is [public]." },
            { option: "no-store=Server", isError: true, errorMessage: "A Cache Control Option was set with a field value for an option that does not support field values. Please check the options specified and if needed use the header() function instead. The cacheControl() parameter was [no-store=Server] and the option name with a field value is [no-store]." },
            { option: "no-transform=Server", isError: true, errorMessage: "A Cache Control Option was set with a field value for an option that does not support field values. Please check the options specified and if needed use the header() function instead. The cacheControl() parameter was [no-transform=Server] and the option name with a field value is [no-transform]." },
            { option: "must-revalidate=Server", isError: true, errorMessage: "A Cache Control Option was set with a field value for an option that does not support field values. Please check the options specified and if needed use the header() function instead. The cacheControl() parameter was [must-revalidate=Server] and the option name with a field value is [must-revalidate]." },
            { option: "proxy-revalidate=Server", isError: true, errorMessage: "A Cache Control Option was set with a field value for an option that does not support field values. Please check the options specified and if needed use the header() function instead. The cacheControl() parameter was [proxy-revalidate=Server] and the option name with a field value is [proxy-revalidate]." },
            { option: 'private="Server', isError: true, errorMessage: 'The cacheControl() function was called with an invalid format. A quoted-string string was started by using the ["] character however an ending ["] was not added to the string. All quoted-strings must have both the starting and ending quote characters. The cacheControl() parameter was [private="Server].' }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 25 - vary() function", "test-web-response.php/cache-headers-25", {
        response: [
            { option: "User-Agent", isError: false },
            { option: "USER-AGENT", isError: false },
            { option: "Accept, Accept-Charset", isError: false },
            { option: "Accept-Encoding, Accept-Language", isError: false },
            { option: "Origin", isError: false },
            { option: "Cookie, Referer", isError: false },
            { option: "*", isError: false },
            { option: "User-Agent, *", isError: true, errorMessage: "The [Vary] Response Header Option [*] cannot be combined with other options, the vary() value specified was [User-Agent, *]." },
            { option: "UserAgent", isError: true, errorMessage: "An unknown option was specified for the [Vary] Response Header. The vary() function only supports options used with server driven content negotiation and several commonly used request headers. If you have confirmed that the header value is valid then use the header() function instead. The vary() parameter was [UserAgent] and the invalid option is [UserAgent]. Valid Options are [Accept], [Accept-Charset], [Accept-Encoding], [Accept-Language], [User-Agent], [Origin], [Cookie], [Referer]." },
            { option: "Accept-Encoding, User-Agent, Accept-Language", isError: true, errorMessage: "The [Vary] Response Header was specified with more than 2 options. The vary() function supports a maximum of two options because if more than 2 are used the content would likely never be cached. Please double-check the need for your site or application to use these options together and if you have confirmed that the header value is valid then use the header() function instead. The vary() parameter was [Accept-Encoding, User-Agent, Accept-Language]." }
        ]
    });

    runHttpUnitTest("Response Object - Cache Headers Test - 26 - expires() and header() date validation test and noCache()", "test-web-response.php/cache-headers-26", {
        type: "text",
        response: "Success passed all tests for the expires() max time validation",
        responseHeaders: [
            { name: "Cache-Control", value: "no-cache, no-store, must-revalidate" },
            { name: "Pragma", value: "no-cache" },
            { name: "Expires", value: "-1" },
        ]
    });
    
    runHttpUnitTest("Response Object - Header Test - 1 - Error Validation", "test-web-response.php/header-1", {
        type: "text",
        response: "The function [FastSitePHP\\Web\\Response->header()] was called with an invalid parameter. The $name parameter must be defined a string but instead was defined as type [integer]."
    });

    runHttpUnitTest("Response Object - Header Test - 2 - Error Validation", "test-web-response.php/header-2", {
        type: "text",
        response: "The function [FastSitePHP\\Web\\Response->header()] was called with invalid parameters. The $name parameter defined as an empty string. It must instead be set to a valid header field."
    });

    runHttpUnitTest("Response Object - Header Test - 3 - Error Validation", "test-web-response.php/header-3", {
        type: "text",
        response: "The function [FastSitePHP\\Web\\Response->header()] was called with an invalid parameter. The parameter $value must be either [string|null] for most headers, [string|int|null] for [Expires], [Last-Modified], or [Content-Length] Response Headers, and [string|int|Closure|null] for the [ETag] Response Header. The function was called with the following parameters: $name = [X-Custom], type of $value = [integer]"
    });

    runHttpUnitTest("Response Object - Header Test - 4 - Error Validation", "test-web-response.php/header-4", {
        type: "text",
        response: "The function [FastSitePHP\\Web\\Response->header()] was called with an invalid parameter. The parameter $value must be either [string|null] for most headers, [string|int|null] for [Expires], [Last-Modified], or [Content-Length] Response Headers, and [string|int|Closure|null] for the [ETag] Response Header. The function was called with the following parameters: $name = [X-Custom], type of $value = [object]"
    });

    runHttpUnitTest("Response Object - Header Test - 5 - Testing Header Functionality with the header() and headers() functions", "test-web-response.php/header-5", {
        type: "text",
        responseHeaders: [
            { name: "X-Custom-Header", value: "FastSitePHP" }
        ],
        response: "header-5 Test"
    });

    runHttpUnitTest("Response Object - Header Test - 6 - Testing 'Content-Length' Validation", "test-web-response.php/header-6", {
        response: "Header Count: 0, Defined Length (integer): 10"
    });
    
    runHttpUnitTest("Response Object - Cookie Test 1 - Adding and Updating Cookies", "test-web-response.php/cookie-1", {
        response: "[Cookie Test 1][2 Headers were found]"
    });

    runHttpUnitTest("Response Object - Cookie Test 2 - Deleting Cookies", "test-web-response.php/cookie-2", {
        response: "[Cookie Test 2][2 Headers were found]"
    });

    runHttpUnitTest("Response Object - Cookie Test 3 - Error Setting Cookie", "test-web-response.php/cookie-3", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">ErrorException</td>',
                '<td class="error-type">TypeError</td>'
            ],
            [
                '<td class="error-severity">2 (E_WARNING)</td>',
                '<td class="error-code">0</td>',
            ],
            [
                '<td class="error-message">setcookie() expects parameter 2 to be string, array given</td>',
                '<td class="error-message">setcookie(): Argument #2 ($value) must be of type string, array given</td>',
            ],
            "<td>setcookie</td>",
            "<td>send</td>"
        ]
    });

    runHttpUnitTest("Response Object - Cookie Test 4 - Exception Setting Cookie", "test-web-response.php/cookie-4", {
        status: 500,
        responseContains: [
            [
                '<td class="error-type">Exception</td>',
                '<td class="error-type">TypeError</td>'
            ],
            [
                '<td class="error-message">Error: setcookie() returned false for cookie named [unit-test]</td>',
                '<td class="error-message">setcookie(): Argument #2 ($value) must be of type string, array given</td>',
            ],
            "<td>send</td>"
        ]
    });
    
    runHttpUnitTest("Response Object - Cookie Test 5 - Exception Setting Cookie", "test-web-response.php/cookie-5", {
        status: 500,
        responseContains: [
        [
                '<td class="error-type">Exception</td>',
                '<td class="error-type">TypeError</td>'
            ],
            [
                '<td class="error-message">Error: setcookie() returned false for cookie named [Name was not a string, gettype=array]</td>',
                '<td class="error-message">setcookie(): Argument #1 ($name) must be of type string, array given</td>',
            ],
            "<td>send</td>"
        ]
    });
    
    runHttpUnitTest("Response Object - File Type to Mime Type", "test-web-response.php/check-mime-types", {
        type: "json",
        response: {
            "htm": "text/html",
            "html": "text/html",
            "txt": "text/plain",
            "md": "text/markdown",
            "markdown": "text/markdown",
            "csv": "text/csv",
            "css": "text/css",
            "png": "image/png",
            "gif": "image/gif",
            "webp": "image/webp",
            "jpg": "image/jpg",
            "jpeg": "image/jpg",
            "svg": "image/svg+xml",
            "ico": "image/x-icon",
            "js": "application/javascript",
            "woff": "application/font-woff",
            "json": "application/json",
            "jsx": "text/jsx",
            "xml": "application/xml",
            "mp4": "video/mp4",
            "webm": "video/webm",
            "ogv": "video/ogg",
            "flv": "video/x-flv",
            "mp3": "audio/mp3",
            "weba": "audio/weba",
            "ogg": "audio/ogg",
            "m4a": "audio/aac",
            "aac": "audio/aac",
            "data": "application/octet-stream",
            "doc": "application/octet-stream",
            "docx": "application/octet-stream",
            "xls": "application/octet-stream",
            "xlsx": "application/octet-stream",
            "zip": "application/octet-stream"
        }
    });

    runHttpUnitTest("Response Object - File Response - Download File using 'download'", "test-web-response.php/download-file", {
        type: "download",
        response: "This is a simple text file.",
        responseHeaders: [
            { name: "Content-Description", value: "File Transfer" },
            { name: "Content-Type", value: "application/octet-stream" },
            { name: "Content-Disposition", value: 'attachment; filename="text-file.txt"' },
            { name: "Cache-Control", value: "must-revalidate" },
            { name: "Pragma", value: "no-cache" },
            { name: "Expires", value: "-1" },
            { name: "Content-Length", value: "27" },
            { name: "ETag", value: null },
            { name: "Last-Modified", value: null },
            { name: "X-Hash-md5", value: 'W/"7c4ad4aae741e4664e0bd3fa05a960fc"' },
            { name: "X-Hash-sha1", value: 'W/"2cb94cfe53c7b87a6d38e3444641f06246d88b0c"' },
            { name: "X-Last-Modified", value: "valid" }
        ]
    });

    runHttpUnitTest("Response Object - File Response - Download File using 'application/octet-stream'", "test-web-response.php/download-file2", {
        type: "download",
        response: "This is a simple text file.",
        responseHeaders: [
            { name: "Content-Description", value: "File Transfer" },
            { name: "Content-Type", value: "application/octet-stream" },
            { name: "Content-Disposition", value: 'attachment; filename="text-file.txt"' },
            { name: "Cache-Control", value: "must-revalidate" },
            { name: "Pragma", value: "no-cache" },
            { name: "Expires", value: "-1" },
            { name: "Content-Length", value: "27" },
            { name: "ETag", value: null },
            { name: "Last-Modified", value: null }
        ]
    });

    runHttpUnitTest("Response Object - File Response - Download File using default Mime-type", "test-web-response.php/download-file3", {
        type: "text",
        response: "This is a simple text file."
    });

    // For info on the query string variable see comments above in "cache-headers-1?_="
    runHttpUnitTest("Response Object - File Response - Download File using Text Mime-type with ETag", "test-web-response.php/download-file4?_=" + (new Date()).getTime(), {
        type: "text",
        response: "This is a simple text file.",
        responseHeaders: [
            { name: "ETag", value: 'W/"7c4ad4aae741e4664e0bd3fa05a960fc"' }
        ]
    });

    runHttpUnitTest("Response Object - File Response - 304 Response for ETag", "test-web-response.php/download-file4", {
        status: 304,
        type: null,
        response: "",
        requestHeaders: [
            { name: "If-None-Match", value: 'W/"7c4ad4aae741e4664e0bd3fa05a960fc"' }
        ]
    });

    runHttpUnitTest("Response Object - File Response - Missing File", "test-web-response.php/download-missing-file-error", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">[FastSitePHP\\Web\\Response-&gt;file()] was called for a file that does not exist: ',
            "missing-file.txt</td></tr>"
        ]
    });

    runHttpUnitTest("Response Object - File Response - Clearing Output Buffer", "test-web-response.php/download-file-buffer", {
        type: "text",
        response: "This is a simple text file."
    });

    runHttpUnitTest("Response Object - File Response - Invalid Cache Type Parameter", "test-web-response.php/download-file-invalid-param", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">Invalid parameter for option $cache_type: cache_type_error</td>',
        ]
    });

    runHttpUnitTest("Response Object - File Response - Error by specifying only a Directory", "test-web-response.php/file-response-with-directory", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">[FastSitePHP\\Web\\Response-&gt;file()] was called for a file that does not exist: ',
            "../app_data/unit-testing/files/</td></tr>"
        ]
    });

    runHttpUnitTest("Response Object - File Response - Error - Invalid ETag", "test-web-response.php/file-response-invalid-etag", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">Etag must not be defined as a closure function for file responses when calling the function file(). To specify an etag for file responses use the $cache_type parameter of the file() function.</td>'
        ]
    });
    
    runHttpUnitTest("Response Object - reset()", "test-web-response.php/reset", {
        response: "reset() function has been tested"
    });

    runHttpUnitTest("Response Object - send() - Validation Error 1", "test-web-response.php/send-error-1", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">The [FastSitePHP\\Web\\Response] Object for the current Route had content set through both [content()] and [file()] functions. When returning the response object from a route or when sending the response only one of these functions can be called.</td>',
            "<td>send</td>",
            "<td>run</td>"
        ]
    });

    runHttpUnitTest("Response Object - send() - Validation Error 2", "test-web-response.php/send-error-2", {
        status: 500,
        responseContains: [
            '<td class="error-type">Exception</td>',
            '<td class="error-message">The [FastSitePHP\\Web\\Response] Object for the current Route had no content set from either [content()], [file()], or [redirect()] functions. Before returning the response object from a route or before sending the response content must be set unless the status code is [204 - No Content], [205 - Reset Content], or [304 - Not Modified].</td>',
            "<td>send</td>",
            "<td>run</td>"
        ]
    });

    runHttpUnitTest("Response Object - Manually Calling send()", "test-web-response.php/manual-send", {
        response: "<h1>Calling send()</h1>"
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
            runHttpUnitTest("Response Object - Redirect for Status Code - " + prop + " " + redirectStatusCodes[prop], "test-web-response.php/redirect-" + prop, {
                responseUrl: rootDir + "test-web-response.php/redirected-" + prop,
                response: prop + " Redirect from Response"
            });
        }
    }

    runHttpUnitTest("Response Object - Redirect with Parameters", "test-web-response.php/redirect-with-params", {
        responseUrl: rootDir + "test-web-response.php/redirected-with-params?param1=abc&param2=123",
        response: {
            param1: "abc",
            param2: "123"
        }
    });

    runHttpUnitTest("Response Object - Redirect Errors", "test-web-response.php/redirect-errors", {
        response: "[redirect-errors-from-response-object][Tested Errors: 5]"
    });

    runHttpUnitTest("Response Object - Cross-Origin Resource Sharing cors() Function Validation", "test-web-response.php/cors-validation", {
        response: "Success checked Response-&gt;cors() with 7 passed tests"
    });

    runHttpUnitTest("Response Object - Cross-Origin Resource Sharing cors() Function Test 1 - OPTIONS", "test-web-response.php/cors-1", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Methods", value: "GET, HEAD, OPTIONS" }
        ]
    });

    runHttpUnitTest("Response Object - Cross-Origin Resource Sharing cors() Function Test 1 - GET", "test-web-response.php/cors-1", {
        response: "Testing cors() [Access-Control-Allow-Origin] from Response Object with a String Value",
        responseHeaders: [
            { name: "Access-Control-Allow-Origin", value: "*" }
        ]
    });

    runHttpUnitTest("Response Object - Cross-Origin Resource Sharing cors() Function Test 2 - OPTIONS", "test-web-response.php/cors-2", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Headers", value: "Content-Type, X-Requested-With" },
            { name: "Access-Control-Allow-Methods", value: "GET, HEAD, OPTIONS" }
        ]
    });

    runHttpUnitTest("Response Object - Cross-Origin Resource Sharing cors() Function Test 2 - GET", "test-web-response.php/cors-2", {
        response: "Testing cors() [Access-Control-Allow-Origin] from Response Object with an Array",
        responseHeaders: [
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Headers", value: "Content-Type, X-Requested-With" }
        ]
    });

    runHttpUnitTest("Response Object - Response Created with Status Code and Headers from the Application Object", "test-web-response.php/headers-from-app", {
        status: 202,
        response: "Test with Response->__construct()",
        responseHeaders: [
            { name: "Access-Control-Allow-Origin", value: "*" },
            { name: "Access-Control-Allow-Headers", value: "Content-Type, X-Requested-With" },
            { name: "Cache-Control", value: "no-cache, no-store, must-revalidate" },
            { name: "Pragma", value: "no-cache" },
            { name: "Expires", value: "-1" },
            { name: "X-Custom-Header", value: "Unit-Test" },
            { name: "X-API-Key", value: "password123" },
        ]
    });

})();
