/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Environment System Object", "test-environ-system.php/check-environ-system-class", {
        response: {
            get_class: "FastSitePHP\\Environment\\System",
            get_parent_class: false
        }
    });
    
    runHttpUnitTest("Environment System Object - Properties", "test-environ-system.php/check-environ-system-properties", {
        response: "All properties matched for [FastSitePHP\\Environment\\System]: "
    });
    
    runHttpUnitTest("Environment System Object - Functions", "test-environ-system.php/check-environ-system-methods", {
        response: "All methods matched for [FastSitePHP\\Environment\\System]: diskSpace, mappedDrives, osVersionInfo, systemInfo"
    });

    runHttpUnitTest("Environment System Object - Function [osVersionInfo()]", "test-environ-system.php/get-os-info", {
        response: "[OS Type:string][Version Info:string][Release Version:string][Host Name:string][CPU Type:string]"
    });
    
    runHttpUnitTest("Environment System Object - Function [systemInfo()]", "test-environ-system.php/get-sys-info", {
        response: "systemInfo(): string"
    });

    runHttpUnitTest("Environment System Object - Function [diskSpace()]", "test-environ-system.php/get-disk-space", {
        response: "[Drive:string][Free Space Bytes:double][Free Space MB:double][Free Space GB:double][Free Space Percent:double][Used Space Bytes:double][Used Space MB:double][Used Space GB:double][Used Space Percent:double][Total Space Bytes:double][Total Space MB:double][Total Space GB:double]"
    });

    runHttpUnitTest("Environment System Object - Function [mappedDrives()]", "test-environ-system.php/get-mapped-drives", {
        response: "mappedDrives(): array"
    });

})();
