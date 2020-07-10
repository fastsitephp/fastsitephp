/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest, rootDir */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
	runHttpUnitTest("Application Object - Error Testing - Exception Raised with before() function", "test-app-events.php/error-in-before", {
	    status: 500,
	    responseContains: [
	        "An error has occurred while processing your request.",
	        '<td class="error-message">Error in before() event</td>',
	        "[after1][after2]"
	    ]
	});
    
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content with the notFound() Event 1", "test-app-events.php/not-found-1", {
	    type: "text",
	    response: "[notFound1][beforeSend1][beforeSend2][after1][after2][After-Run]"
	});
	
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content with the notFound() Event 2", "test-app-events.php/not-found-2", {
	    type: "text",
	    response: "[notFound2][beforeSend1][beforeSend2][after1][after2][After-Run]"
	});
	
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content with the notFound() Event 3", "test-app-events.php/not-found-3", {
	    response: "[notFound3][after1][after2][After-Run]"
	});

	runHttpUnitTest("Application Object - Event Testing - Exception Raised with notFound() function", "test-app-events.php/error-in-not-found", {
	    status: 500,
	    responseContains: [
	        "An error has occurred while processing your request.",
	        '<td class="error-message">Error in notFound() event</td>',
	        "[after1][after2]"
	    ]
	});
    
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content", "test-app-events.php/event-test-1", {
	    type: "text",
	    response: "[before1][before2][before_prop_3 does not exist][updated-event-test-1][beforeSend1][beforeSend2][expected $content found][after1][after2][After-Run]"
	});
	
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content with Route echo called", "test-app-events.php/echo-test-1", {
	    response: "[before1][before2][before_prop_3 does not exist][echo-test-1][after1][after2][After-Run]"
	});
	
	// No route is defined for this test so beforeSend() is never called but after() is and no output is allowed after run()
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content for a Missing Page", "test-app-events.php/404", {
	    status: 404,
	    responseContains: [
	        "Page Not Found</h1>",
	        "The requested page could not be found.</div>",
	        "[after1][after2]"
	    ],
	    responseExcludes: [
	        "[beforeSend1][beforeSend2]",
	        "[After-Run]"
	    ]
	});
	
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content for an Error Page", "test-app-events.php/error", {
	    status: 500,
	    responseContains: [
	        "An error has occurred</h1>",
	        "An error has occurred while processing your request.</div>",
	        "[after1][after2]"
	    ],
	    responseExcludes: [
	        "[beforeSend1][beforeSend2]",
	        "[After-Run]"
	    ]
	});
	
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content with error() Events Handled", "test-app-events.php/error-with-events", {
	    status: 500,
	    responseContains: [
	        "An error has occurred</h1>",
	        "An error has occurred while processing your request.</div>",
	        '<td class="error-message">Error Event Test</td>',
	        "[after1][after2][error1:[500]:[Error Event Test]][error2]"
	    ],
	    responseExcludes: [
	        "[beforeSend1][beforeSend2]",
	        "[After-Run]"
	    ]
	});
	
	runHttpUnitTest("Application Object - Event Testing - Event Order and Event Content with the exit() called on error()", "test-app-events.php/error-with-exit", {
	    type: "text",
	    response: "Error Exit Test"
	});
	
	runHttpUnitTest("Application Object - Event Testing - Exception Raised with beforeSend() function", "test-app-events.php/error-in-before-send", {
	    status: 500,
	    responseContains: [
	        "An error has occurred while processing your request.",
	        '<td class="error-message">Error in beforeSend() event</td>',
	        "[after1][after2]"
	    ]
	});
	
	runHttpUnitTest("Application Object - Event Testing - Exception Raised with error() function", "test-app-events.php/error-in-error", {
	    status: 500,
	    responseContains: [
	        "An error has occurred while processing your request.",
	        '<td class="error-message">Error in Error - error()</td>',
	        "[after1][after2]"
	    ],
	    responseExcludes: "Error in Error - Route"
	});
	
	runHttpUnitTest("Application Object - Event Testing - Exception Raised with error() function", "test-app-events.php/error-in-after", {
	    status: 500,
	    responseContains: [
	        "An error has occurred while processing your request.",
	        '<td class="error-message">Error in after() event</td>'
	    ],
	    responseExcludes: [
	        "[Error in After]",
	        "[after1][after2]"
	    ]
	});
	
	runHttpUnitTest("Application Object - Event Testing - Checking that only Closure Functions can be added to Events", "test-app-events.php/invalid-event-test", {
		responseContains: [
			[
				"[before() Expected ErrorException Message][beforeSend() Expected ErrorException Message][after() Expected ErrorException Message][error() Expected ErrorException Message][notFound() Expected ErrorException Message][beforeSend1][beforeSend2][after1][after2][After-Run]",
				"[before() Expected TypeError Message][beforeSend() Expected TypeError Message][after() Expected TypeError Message][error() Expected TypeError Message][notFound() Expected TypeError Message][beforeSend1][beforeSend2][after1][after2][After-Run]",
			]
		]
	});
    
	runHttpUnitTest("Application Object - Event Testing - Mixed Response in Route 1", "test-app-events.php/mixed-response", {
	    type: "text",
	    response: "[Echo from Route][Return from Route][beforeSend1][beforeSend2][after1][after2][After-Run]"
	});
	
	runHttpUnitTest("Application Event Testing - Mixed Response in Route 2", "test-app-events.php/mixed-response-2", {
	    response: "[Echo from Route 2][Return from Route][beforeSend1][beforeSend2][after1][after2][After-Run]"
	});
	
	runHttpUnitTest("Application Object - Event Testing - Mixed Response in Route with Error", "test-app-events.php/error-after-output", {
	    responseContains: [
	        "[Test: error-after-output]",
	        "An error has occurred while processing your request.",
	        '<td class="error-message">Error after Output Test</td>',
	        "[after1][after2]"
	    ]
	});
	
	runHttpUnitTest("Application Object - Event Testing - Error Test - beforeSend() returns null", "test-app-events.php/before-send-return-null", {
	    status: 500,
	    responseContains: [
	        '<td class="error-message">Route [GET /before-send-return-null] was matched however the route&#039;s function or a beforeSend() callback function returned no response.</td>'
	    ]
	});
    
	runHttpUnitTest("Application Object - Event Testing - Manually calling runAfterEvents()", "test-app-events.php/after-called-manully", {
	    response: "[after1][after2]"
	});

    runHttpUnitTest("Application Object - Event Testing - Redirect Route with after() functions", "test-app-events.php/redirect-with-after", {
        responseUrl: rootDir + "test-app-events.php/redirected-with-after",
        response: "Called after() from: [/redirect-with-after]"
    });

	runHttpUnitTest("Application Object - Event Testing - Response Object", "test-app-events.php/response-object-events", {
	    response: "[Testing with a Response Object: CustomResponse][beforeSend(CustomResponse)][after(CustomResponse)]"
	});

    runHttpUnitTest("Application Object - Event Testing - OPTIONS Request with after() Event Triggered", "test-app-events.php/options-request-after-event", {
        method: "OPTIONS",
        response: "",
        responseHeaders: [
            { name: "Allow", value: "GET, HEAD, OPTIONS" },
            { name: "X-Content-Type", value: "NULL" },
            { name: "X-After-Event", value: "true" }
        ]
    });

    runHttpUnitTest("Application Object - Event Testing - Manually Calling send() without a Returned Response", "test-app-events.php/manual-send", {
        response: "<h1>Calling send()</h1>[after(NULL)][after1][after2][After-Run]"
    });

})();
