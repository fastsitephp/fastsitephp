/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Security Password - Hash with bcrypt", "test-security-password.php/hash-with-bcrypt", {
        response: {
            hash1: "$2y$10$",
            hash2: "$2y$11$",
            verified1: true,
            verified2: false,
            needs_rehash1: false,
            needs_rehash2: true,
            starting_cost: 10,
            cost: 11
        }
    });

    runHttpUnitTest("Security Password - Use Pepper with bcrypt", "test-security-password.php/use-pepper-with-bcrypt", {
        response: {
            pepper_len: 8,
            pepper2_len: 10,
            matches: true,
            error: "Invalid Pepper. The pepper value must be a hexadecimal encoded string and the function was called with a non-hex value.",
            verified1: true,
            verified2: false
        }
    });

    runHttpUnitTest("Security Password - Hash with Argon2", "test-security-password.php/hash-with-argon2", {
        responseContains: [[
            // < PHP 7.2
            'Using [Argon2] is not supported on this server. [Argon2] requires PHP 7.2 or later.',
            // PHP 7.2+
            // NOTE - the output varies depending on the server (memory, threads, etc).
            // This test will likely be revised in the future to only verify that it works.
            'Argon2[$argon2i$v=19$m=1024,t=2,p=2$][{"memory_cost":1024,"time_cost":2,"threads":2}][verify1=true][verify2=false][needsRehash1=false][needsRehash2=true][$argon2i$v=19$m=2048,t=10,p=4$][{"memory_cost":2048,"time_cost":10,"threads":4}]',
            'Argon2[$argon2i$v=19$m=65536,t=4,p=1][{"memory_cost":65536,"time_cost":4,"threads":1}][verify1=true][verify2=false][needsRehash1=false][needsRehash2=true][$argon2i$v=19$m=2048,t=10,p=4$][{"memory_cost":2048,"time_cost":10,"threads":4}]',
        ]]
    });

    runHttpUnitTest("Security Password - Use Pepper with Argon2", "test-security-password.php/use-pepper-with-argon2", {
        responseContains: [[
            'Using [Argon2] is not supported on this server. [Argon2] requires PHP 7.2 or later.',
            '[true,false]',
        ]]
    });

    runHttpUnitTest("Security Password - Misc Checks", "test-security-password.php/misc", {
        response: {
            error: "Unsupported Algorithm. The only valid options for this class are [bcrypt] and [Argon2].",
            findCost_type: "integer", 
            generated: 1000,
            unique: 1000
        }
    });

})();
