/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Lang Time Object", "test-lang-time.php/check-lang-time-class", {
        response: {
            get_class: "FastSitePHP\\Lang\\Time",
            get_parent_class: false
        }
    });
    
    runHttpUnitTest("Lang Time Object - Properties", "test-lang-time.php/check-lang-time-properties", {
        response: "All properties matched for [FastSitePHP\\Lang\\Time]: "
    });
    
    runHttpUnitTest("Lang Time Object - Functions", "test-lang-time.php/check-lang-time-methods", {
        response: "All methods matched for [FastSitePHP\\Lang\\Time]: english, secondsToText"
    });

    runHttpUnitTest("Lang Time - secondsToText()", "test-lang-time.php/time-seconds-to-text", {
        response: [
            "1 Day, 12 Hours, 1 Minute, and 20 Seconds",
            "10 Seconds",
            "0 Seconds",
            "1 Second",
            "59 Seconds",
            "1 Minute",
            "1 Minute and 59 Seconds",
            "2 Minutes",
            "1 Hour",
            "1 Day",
            "2 Days, 12 Hours, 59 Minutes, and 20 Seconds",
            "1 Year, 2 Days, 12 Hours, 59 Minutes, and 20 Seconds",
            "2 Years, 2 Days, 12 Hours, 59 Minutes, and 20 Seconds"
        ]
    });
})();
