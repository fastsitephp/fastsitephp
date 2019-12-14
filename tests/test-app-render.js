/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
	runHttpUnitTest("Application Object - Template Rendering - 404 Not Found - PHP", "test-app-render.php/php-404", {
	    status: 404,
	    response: "[HeaderPhp1][header&amp;data][NotFoundPhp1][404 - Page Not Found][The requested page could not be found.][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - 404 Not Found - HTML", "test-app-render.php/file-404", {
	    status: 404,
	    response: "[HeaderHtml1][NotFoundHtml1][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - 404 Not Found - Text", "test-app-render.php/custom-404", {
	    status: 404,
	    response: "[HeaderText1][header&amp;data][NotFoundText1][404 - Page Not Found][The requested page could not be found.][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing 404 Not Found - PHP", "test-app-render.php/php-missing-not-found", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Template file was not found: ',
	        "/views/php/missing-not-found.php</td></tr>",
	        "<td>render</td>",
	        "<td>sendErrorPage</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing 404 Not Found - HTML", "test-app-render.php/file-missing-not-found", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Template file was not found: ',
	        "/views/file/missing-not-found.htm</td></tr>",
	        "<td>render</td>",
	        "<td>sendErrorPage</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing 404 Not Found - Text", "test-app-render.php/custom-missing-not-found", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Template file was not found: ',
	        "/views/custom/missing-not-found.txt</td></tr>",
	        "<td>render</td>",
	        "<td>sendErrorPage</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple 404 Not Found - PHP", "test-app-render.php/php-multiple-not-found", {
	    status: 404,
	    response: "[HeaderPhp1][header&amp;data][NotFoundPhp1][404 - Page Not Found][The requested page could not be found.][NotFoundPhp2][404 - Page Not Found][The requested page could not be found.][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple 404 Not Found - HTML", "test-app-render.php/file-multiple-not-found", {
	    status: 404,
	    response: "[HeaderHtml1][NotFoundHtml1][NotFoundHtml2][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple 404 Not Found - Text", "test-app-render.php/custom-multiple-not-found", {
	    status: 404,
	    response: "[HeaderText1][header&amp;data][NotFoundText1][404 - Page Not Found][The requested page could not be found.][NotFoundText2][404 - Page Not Found][The requested page could not be found.][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error with Not Found - PHP", "test-app-render.php/php-error-with-not-found", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">ErrorException</td>',
	        '<td class="error-severity">8 (E_NOTICE)</td>',
	        '<td class="error-message">Undefined variable: footer_data</td>',
	        "footer-1.php</td></tr>",
			"<td>include</td>",
			"<td>render</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error with Not Found - Text", "test-app-render.php/custom-error-with-not-found", {
	    status: 500,
	    response: "[HeaderText1][header&amp;data][ErrorText1][An error has occurred][An error has occurred while processing your request.][getMessage():Class &#039;UnknownObject&#039; not found][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Exception with Not Found - Text", "test-app-render.php/custom-exception-with-not-found", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Exception Test from engine([search_replace])</td>',
	        "<td>preg_replace_callback</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - 404 Page with a Custom Message - PHP", "test-app-render.php/php-404-custom-text", {
	    status: 404,
	    response: "[HeaderPhp1][header&amp;data][NotFoundPhp1][404 Page Not Found Custom][The page you requested does not exist.][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - 404 Page with a Custom Message - Text", "test-app-render.php/custom-404-custom-text", {
	    status: 404,
	    response: "[HeaderText1][header&amp;data][NotFoundText1][404 Page Not Found Custom][The page you requested does not exist.][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - One Page/Header/Footer - PHP", "test-app-render.php/php-template-one-page-header-footer", {
	    response: "[HeaderPhp1][header&amp;data][PagePhp1][page-1][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - One Page/Header/Footer - HTML", "test-app-render.php/file-template-one-page-header-footer", {
	    response: "[HeaderHtml1][HtmlPage1][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - One Page/Header/Footer - Text", "test-app-render.php/custom-template-one-page-header-footer", {
	    response: "[HeaderText1][header&amp;data][PageText1][page-1][{{not_defined}}][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple Pages/Headers/Footers - PHP", "test-app-render.php/php-template-multiple-pages", {
	    response: "[HeaderPhp1][header&amp;data][HeaderPhp2][header&amp;data][PagePhp1][page-1][PagePhp2][page-2][FooterPhp1][footer&amp;data][FooterPhp2][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple Pages/Headers/Footers - HTML", "test-app-render.php/file-template-multiple-pages", {
	    response: "[HeaderHtml1][HeaderHtml2][HtmlPage1][HtmlPage2][FooterHtml1][FooterHtml2]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple Pages/Headers/Footers - Text", "test-app-render.php/custom-template-multiple-pages", {
	    response: "[HeaderText1][header&amp;data][HeaderText2][header&amp;data][PageText1][page-1][{{not_defined}}][PageText2][page-2][FooterText1][footer&amp;data][FooterText2][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - One Page with No Header/Footer - PHP", "test-app-render.php/php-template-one-page", {
	    response: "[PagePhp1][page-1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - One Page with No Header/Footer - HTML", "test-app-render.php/file-template-one-page", {
	    response: "[HtmlPage1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - One Page with No Header/Footer - Text", "test-app-render.php/custom-template-one-page", {
	    response: "[PageText1][page-1][{{not_defined}}]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Exception - PHP", "test-app-render.php/php-exception", {
	    status: 500,
	    response: "[HeaderPhp1][header&amp;data][ErrorPhp1][An error has occurred][An error has occurred while processing your request.][getMessage():PHP Exception Test][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Exception - HTML", "test-app-render.php/file-exception", {
	    status: 500,
	    response: "[HeaderHtml1][ErrorHtml1][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Exception - Text", "test-app-render.php/custom-exception", {
	    status: 500,
	    response: "[HeaderText1][header&amp;data][ErrorText1][An error has occurred][An error has occurred while processing your request.][getMessage():Text Error Test][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error - PHP", "test-app-render.php/php-error", {
	    status: 500,
	    response: "[HeaderPhp1][header&amp;data][ErrorPhp1][An error has occurred][An error has occurred while processing your request.][getMessage():Class &#039;UnknownObject&#039; not found][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error - HTML", "test-app-render.php/file-error", {
	    status: 500,
	    response: "[HeaderHtml1][ErrorHtml1][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error - Text", "test-app-render.php/custom-error", {
	    status: 500,
	    response: "[HeaderText1][header&amp;data][ErrorText1][An error has occurred][An error has occurred while processing your request.][getMessage():Class &#039;UnknownObject&#039; not found][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error using Custom Message - PHP", "test-app-render.php/php-error-custom-message", {
	    status: 500,
	    response: "[HeaderPhp1][header&amp;data][ErrorPhp1][500 Error Page][Error Page Custom Message][getMessage():Error Test with Custom Message][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error using Custom Message - Text", "test-app-render.php/custom-error-custom-message", {
	    status: 500,
	    response: "[HeaderText1][header&amp;data][ErrorText1][500 Error Page][Error Page Custom Message][getMessage():Error Test with Custom Message][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple Error Templates - PHP", "test-app-render.php/php-multiple-error-templates", {
	    status: 500,
	    response: "[HeaderPhp1][header&amp;data][ErrorPhp1][An error has occurred][An error has occurred while processing your request.][getMessage():PHP Multiple Error Pages Test][ErrorPhp2][An error has occurred][An error has occurred while processing your request.][getMessage():PHP Multiple Error Pages Test][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple Error Templates - HTML", "test-app-render.php/file-multiple-error-templates", {
	    status: 500,
	    response: "[HeaderHtml1][ErrorHtml1][ErrorHtml2][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Multiple Error Templates - Text", "test-app-render.php/custom-multiple-error-templates", {
	    status: 500,
	    response: "[HeaderText1][header&amp;data][ErrorText1][An error has occurred][An error has occurred while processing your request.][getMessage():Text Multiple Error Pages Test][ErrorText2][An error has occurred][An error has occurred while processing your request.][getMessage():Text Multiple Error Pages Test][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing Page - PHP", "test-app-render.php/php-missing-page", {
	    status: 500,
	    responseContains: [
	        "[HeaderPhp1][header&amp;data][ErrorPhp1][An error has occurred][An error has occurred while processing your request.][getMessage():Template file was not found: ",
	        "/views/php/missing-page.php][FooterPhp1][footer&amp;data]"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing Page - HTML", "test-app-render.php/file-missing-page", {
	    status: 500,
	    response: "[HeaderHtml1][ErrorHtml1][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing Page - Text", "test-app-render.php/custom-missing-page", {
	    status: 500,
	    responseContains: [
	        "[HeaderText1][header&amp;data][ErrorText1][An error has occurred][An error has occurred while processing your request.][getMessage():Template file was not found: ",
	        "/views/custom/missing-page.txt][FooterText1][footer&amp;data]"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing Error Page - PHP", "test-app-render.php/php-missing-error-page", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Template file was not found: ',
	        "/views/php/missing-error-page.php</td></tr>",
	        "<td>render</td>",
	        "<td>sendErrorPage</td>",
	        "<td>exceptionHandler</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing Error Page - HTML", "test-app-render.php/file-missing-error-page", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Template file was not found: ',
	        "/views/file/missing-error-page.htm</td></tr>",
	        "<td>render</td>",
	        "<td>sendErrorPage</td>",
	        "<td>exceptionHandler</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Missing Error Page - Text", "test-app-render.php/custom-missing-error-page", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Template file was not found: ',
	        "/views/custom/missing-error-page.txt</td></tr>",
	        "<td>render</td>",
	        "<td>sendErrorPage</td>",
	        "<td>exceptionHandler</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error on Render 1 - PHP", "test-app-render.php/php-error-on-render-1", {
	    status: 500,
	    response: "[HeaderPhp1][header&amp;data][ErrorPhp1][An error has occurred][An error has occurred while processing your request.][getMessage():Undefined variable: page1_data][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error on Render 2 - Text", "test-app-render.php/php-error-on-render-2", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">ErrorException</td>',
	        '<td class="error-severity">8 (E_NOTICE)</td>',
	        '<td class="error-message">Undefined variable: header_data</td>',
	        "header-1.php</td></tr>",
	        "<td>errorHandler</td>",
	        "<td>include</td>",
	        "<td>render</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Exception on Render - PHP", "test-app-render.php/php-exception-on-render", {
	    status: 500,
	    response: "[HeaderPhp1][header&amp;data][ErrorPhp1][An error has occurred][An error has occurred while processing your request.][getMessage():Exception from PHP Page 3][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error on Render - Text", "test-app-render.php/custom-error-on-render", {
	    status: 500,
	    response: "[HeaderText1][header&amp;data][ErrorText1][An error has occurred][An error has occurred while processing your request.][getMessage():Class &#039;UnknownObject&#039; not found][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Exception on Render - Text", "test-app-render.php/custom-exception-on-render", {
	    status: 500,
	    response: "[HeaderText1][header&amp;data][ErrorText1][An error has occurred][An error has occurred while processing your request.][getMessage():Exception Test from engine([search_replace])][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error on Error Page - PHP", "test-app-render.php/php-error-on-error-page", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">ErrorException</td>',
	        '<td class="error-severity">8 (E_NOTICE)</td>',
	        '<td class="error-message">Undefined variable: header_data</td>',
	        "header-1.php</td></tr>",
	        "<td>include</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Exception on Error Page - PHP", "test-app-render.php/php-exception-on-error-page", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Exception from error-exception.php</td>',
	        "<td>include</td>",
	        "<td>render</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Error on Error Page - Text", "test-app-render.php/custom-error-on-error-page", {
	    status: 500,
	    response: ""
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Exception on Error Page - Text", "test-app-render.php/custom-exception-on-error-page", {
	    status: 500,
	    responseContains: [
	        '<td class="error-type">Exception</td>',
	        '<td class="error-message">Exception Test from engine([search_replace])</td>',
	        "<td>preg_replace_callback</td>",
	        "<td>call_user_func</td>",
	        "<td>render</td>"
	    ]
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Full File Path - PHP", "test-app-render.php/php-template-full-path", {
	    response: "[HeaderPhp1][header&amp;data][PagePhp1][page-1][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - [template_dir] Format Test - PHP", "test-app-render.php/php-dir-format", {
	    response: "[HeaderPhp1][header&amp;data][PagePhp1][page-1][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Testing Errors in the render() function", "test-app-render.php/error-test-render-function", {
	    response: "Success, Tested for 1 Exceptions in the function render()"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Testing Errors in the engine() function", "test-app-render.php/error-test-engine-function", {
	    response: "Success, Tested for 4 Exceptions in the function engine() and added 2 rendering engines"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - Testing Errors with Properties and the render() function", "test-app-render.php/error-test-render-properties", {
	    response: "Success, Tested for 3 Exceptions for Properties when calling render()"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - pageNotFound() function - PHP", "test-app-render.php/php-page-not-found", {
	    status: 404,
	    response: "[HeaderPhp1][header&amp;data][NotFoundPhp1][404 - Page Not Found][The requested page could not be found.][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - pageNotFound() function - HTML", "test-app-render.php/file-page-not-found", {
	    status: 404,
	    response: "[HeaderHtml1][NotFoundHtml1][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - pageNotFound() function - Text", "test-app-render.php/custom-page-not-found", {
	    status: 404,
	    response: "[HeaderText1][header&amp;data][NotFoundText1][404 - Page Not Found][The requested page could not be found.][FooterText1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - pageNotFound() function with Custom Message - PHP", "test-app-render.php/php-page-not-found-custom", {
	    status: 404,
	    response: "[HeaderPhp1][header&amp;data][NotFoundPhp1][404][Page missing][FooterPhp1][footer&amp;data]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - pageNotFound() function with Custom Message - Text", "test-app-render.php/custom-page-not-found-custom", {
	    status: 404,
	    response: "[HeaderText1][header&amp;data][NotFoundText1][404][Page missing][FooterText1][footer&amp;data]"
	});

    runHttpUnitTest("Application Object - Template Rendering - pageNotFound() with Default Template - PHP", "test-app-render.php/php-default-page-not-found", {
        status: 404,
        responseContains: [
            "Page Not Found</h1>",
            "The requested page could not be found.</div>"
        ]
    });

    runHttpUnitTest("Application Object - Template Rendering - pageNotFound() with Default Template - HTML", "test-app-render.php/file-default-page-not-found", {
        status: 404,
        responseContains: [
            "Page Not Found</h1>",
            "The requested page could not be found.</div>"
        ]
    });

    runHttpUnitTest("Application Object - Template Rendering - pageNotFound() with Default Template - Text", "test-app-render.php/custom-default-page-not-found", {
        status: 404,
        responseContains: [
            "Page Not Found</h1>",
            "The requested page could not be found.</div>"
        ]
    });

	runHttpUnitTest("Application Object - Template Rendering - 405 Method Not Allowed - PHP", "test-app-render.php/php-405", {
	    status: 405,
	    response: "[HeaderPhp1][header&amp;data][NotFoundPhp1][Error - Method Not Allowed][A [GET] request was submitted however this route only allows for [POST, OPTIONS] methods.][FooterPhp1][footer&amp;data]"
	});

	runHttpUnitTest("Application Object - Template Rendering - 405 Method Not Allowed - HTML", "test-app-render.php/file-405", {
	    status: 405,
	    response: "[HeaderHtml1][NotFoundHtml1][FooterHtml1]"
	});
	
	runHttpUnitTest("Application Object - Template Rendering - 405 Method Not Allowed - Text", "test-app-render.php/custom-405", {
	    status: 405,
	    response: "[HeaderText1][header&amp;data][NotFoundText1][Error - Method Not Allowed][A [GET] request was submitted however this route only allows for [POST, OPTIONS] methods.][FooterText1][footer&amp;data]"
	});

	runHttpUnitTest("Application Object - Template Rendering - 405 Method Not Allowed with a Custom Message - PHP", "test-app-render.php/php-405-custom-text", {
	    status: 405,
	    response: "[HeaderPhp1][header&amp;data][NotFoundPhp1][405 Method Not Allowed Custom][[Request: GET] [Allowed: POST, OPTIONS]][FooterPhp1][footer&amp;data]"
	});

	runHttpUnitTest("Application Object - Template Rendering - 405 Method Not Allowed with a Custom Message - Text", "test-app-render.php/custom-405-custom-text", {
	    status: 405,
	    response: "[HeaderText1][header&amp;data][NotFoundText1][405 Method Not Allowed Custom][[Request: GET] [Allowed: POST, OPTIONS]][FooterText1][footer&amp;data]"
	});

})();
