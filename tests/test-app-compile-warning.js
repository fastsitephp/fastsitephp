/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Application Object - Error Test - Error Type E_COMPILE_WARNING", "test-app-compile-warning.php/error-compile-warning", {
	    status: null,
        responseContains: [[
            '<td class="error-severity">128 (E_COMPILE_WARNING)</td></tr>',
            'php7',
        ]]
    });
                
})();
