/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Networking Config Object", "test-net-config.php/check-net-config-class", {
        response: {
            get_class: "FastSitePHP\\Net\\Config",
            get_parent_class: false
        }
    });
    
    runHttpUnitTest("Networking Config Object - Properties", "test-net-config.php/check-net-config-properties", {
        response: "All properties matched for [FastSitePHP\\Net\\Config]: "
    });
    
    runHttpUnitTest("Networking Config Object - Functions", "test-net-config.php/check-net-config-methods", {
        response: "All methods matched for [FastSitePHP\\Net\\Config]: fqdn, networkInfo, networkIp, networkIpList, parseIpAddr, parseIpConfig, parseNetworkInfo"
    });

    runHttpUnitTest("Networking Config Object - Fully-Qualified Domain Name - fqdn()", "test-net-config.php/fqdn", {
        responseContains: [[
            'type: string',
            'type: null',
        ]]
    });

    runHttpUnitTest("Networking Config Object - networkIp()", "test-net-config.php/network-ip", {
        response: "[type:string][is_ip:true]"
    });

})();
