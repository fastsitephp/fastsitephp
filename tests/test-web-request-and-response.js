/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest, runGetAndPostUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode

    runGetAndPostUnitTest("Request and Response Objects - Plain Cookie Test with [cookie()]", "test-web-request-and-response.php/cookie-plain", {
        response: {
            "unit-test-plain": "plain cookie",
        }
    });

    runGetAndPostUnitTest("Request and Response Objects - Encrypted Cookie Test with [Response.encryptedCookie()] and [Request.decryptedCookie()]", "test-web-request-and-response.php/cookie-encrypted", {
        response: {
            "unit-test-encrypted-object": {
                "Secret": "Object"
            },
            "unit-test-encrypted-object-raw-len": 107,
            "unit-test-encrypted-text": "Secret Data",
            "unit-test-encrypted-text-raw-base64url": true,
            "unit-test-encrypted-text-raw-len": 86,
            "unit-test-plain": "plain cookie",
            "unit-test-plain-decypted": null,
            "unit-test-plain-deleted": null,
        }
    });

    runGetAndPostUnitTest("Request and Response Objects - Signed Cookie Test with [Response.signedCookie()] and [Request.verifiedCookie()]", "test-web-request-and-response.php/cookie-signed", {
        response: {
            "unit-test-signed-object": {
                "SignedBy": "FastSitePHP"
            },
            "unit-test-signed-object-raw": "eyJTaWduZWRCeSI6IkZhc3RTaXRlUEhQIn0.j.7tcV74pn8n4I3i5D4fXx5ZNJK_UuWoF1ap8Os5Aroik",
            "unit-test-signed-text": "Signed by FastSitePHP",
            "unit-test-signed-text-raw": "U2lnbmVkIGJ5IEZhc3RTaXRlUEhQ.s.knNtjH4Sat3bevuGouyRvrB_m5Cv27i26pV3vqx87dI",
            "unit-test-plain": "plain cookie",
            "unit-test-plain-verified": null,
            "unit-test-plain-deleted": null,
          }
    });

    runGetAndPostUnitTest("Request and Response Objects - JWT Cookie Test with [Response.jwtCookie()] and [Request.jwtCookie()]", "test-web-request-and-response.php/cookie-jwt", {
        response: {
            "unit-test-jwt": {
                "role": "Admin"
            },
            "unit-test-jwt-raw": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJyb2xlIjoiQWRtaW4ifQ.PCL-6ashG7Xv6GXGRKZEwafntTC5hc8o8Xyn22mavJQ",
            "unit-test-plain": "plain cookie",
            "unit-test-plain-verified": null,
            "unit-test-plain-deleted": null,
          }
    });

    runHttpUnitTest("Request and Response Objects - Exceptions for Cookie Crypto Functions", "test-web-request-and-response.php/crypto-cookie-response-errors", {
        response: [
            "Missing Application Config Value or Environment Variable for [SIGNING_KEY]. If this error is not clear then please review FastSitePHP documentation and examples on how to use [FastSitePHP\\Security\\Crypto::sign()].",
            "Missing Application Config Value or Environment Variable for [ENCRYPTION_KEY]. If this error is not clear then please review FastSitePHP documentation and examples on how to use [FastSitePHP\\Security\\Crypto::encrypt()].",
        ]
    });

})();