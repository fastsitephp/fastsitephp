/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode

    runHttpUnitTest("Security JWT - Default Settings for Getter/Setter Functions", "test-security-jwt.php/default-settings", {
        response: {
            exceptionOnError: false,
            algo: "HS256",
            allowedAlgos: ["HS256"],
            useInsecureKey: false,
            validateDefinedClaims: true,
            allowedIssuers: null,
            requireSubject: null,
            allowedAudiences: null,
            requireExpireTime: [false, 0],
            requireNotBefore: [false, 0],
            requireIssuedAt: false,
            requireJwtId: null
        }
    });

    runHttpUnitTest("Security JWT - Set Value for Setter Functions and return [$this]", "test-security-jwt.php/getter-and-setter-functions", {
        response: {
            exceptionOnError: true,
            algo: "HS256",
            allowedAlgos: ["HS256"],
            useInsecureKey: true,
            validateDefinedClaims: false,
            allowedIssuers: ["iss"],
            requireSubject: "sub",
            allowedAudiences: ["aud"],
            requireExpireTime: [true, 10],
            requireNotBefore: [true, 10],
            requireIssuedAt: true,
            requireJwtId: "jti"
        }
    });
    
    runHttpUnitTest("Security JWT - Use Default Settings to Encode and Decode an Array", "test-security-jwt.php/encode-decode-array", {
        response: {
            id: 1,
            user: "John Doe",
            roles: ["Admin", "SQL User"],
        }
    });

    runHttpUnitTest("Security JWT - Use Default Settings to Encode and Decode an Array", "test-security-jwt.php/encode-decode-object", {
        response: {
            id: 2,
            user: "Jane Doe",
            roles: ["Viewer"],
        }
    });

    runHttpUnitTest("Security JWT - Use Default Settings to Encode and Decode an Custom Object", "test-security-jwt.php/encode-decode-class", {
        response: {
            id: 3,
            user: "Guest",
            roles: ["Guest"],
        }
    });

    runHttpUnitTest("Security JWT - Verify the Crypto Helper Class for Encoding and Decoding JWT", "test-security-jwt.php/encode-decode-with-crypto", {
        response: {
            token_match: true,
            decoded_match: true,
            decoded: {
                user: "Admin",
                roles: ["Admin"]
            }
        }
    });

    runHttpUnitTest("Security JWT - Generate an use a Hex Key for Encoding and Decoding", "test-security-jwt.php/encode-decode-hex-key", {
        response: {
            data: {
                id: 3,
                user: "Guest",
                roles: ["Guest"],    
            },
            keyIsHex: true,
            keyLen: 64
        }
    });

    runHttpUnitTest("Security JWT - Encode and Decode using HMAC ['HS256', 'HS384', 'HS512']", "test-security-jwt.php/encode-decode-hmac", {
        response: "[HS256,true,false,32][HS384,true,false,48][HS512,true,false,64]"
    });

    if (window.runTestsWithRSA && window.runTestsThatCreateRSAKey) {
        runHttpUnitTest("Security JWT - Server can create an RSA Key for ['RS256', 'RS384', 'RS512']", "test-security-jwt.php/create-rsa-key", {
            response: [0, 0]
        });
    }

    if (window.runTestsWithRSA && window.runTimeConsumingTasks && window.runTestsThatCreateRSAKey) {
        runHttpUnitTest("Security JWT - Encode and Decode using RSA ['RS256', 'RS384', 'RS512'] with default 2048-bit Key", "test-security-jwt.php/encode-decode-rsa", {
            response: "[RS256,true,false,451][RS384,true,false,451][RS512,true,false,451]"
        });

        runHttpUnitTest("Security JWT - Encode and Decode using RSA ['RS256', 'RS384', 'RS512'] and a larger 3072-bit Key", "test-security-jwt.php/encode-decode-rsa-large-key", {
            response: "[RS256,true,false,625][RS384,true,false,625][RS512,true,false,625]"
        });
    }

    runHttpUnitTest("Security JWT - Encode and Decode with a Known Key using 'HS256'", "test-security-jwt.php/encode-decode-hs256", {
        response: {
            jwt: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoiSFMyNTYifQ.rhL-dgqr8CKH2m2Vy8Dxvmof-AD8fWt_e1wBpdL1dvc",
            payload: {test:"HS256"}
        }
    });

    runHttpUnitTest("Security JWT - Encode and Decode with a Known Key using 'HS384'", "test-security-jwt.php/encode-decode-hs384", {
        response: {
            jwt: "eyJhbGciOiJIUzM4NCIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoiSFMzODQifQ.v7gIne33e9cuwDepiqU0FqKagPri9Mk_hrXyiZPKAaC8J-OZCI48ZSTUVKjw0768",
            payload: {test:"HS384"}
        }
    });

    runHttpUnitTest("Security JWT - Encode and Decode with a Known Key using 'HS512'", "test-security-jwt.php/encode-decode-hs512", {
        response: {
            jwt: "eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoiSFM1MTIifQ.NwOXnP48ereGbKfaKmT2qffzTHGazXhGW3e12u1aPMU8TVmovI4Y6fTYjGGbse_Mvy-T5Nr0nJtvxogwSF0AIw",
            payload: {test:"HS512"}
        }
    });

    runHttpUnitTest("Security JWT - Encode and Decode with a Known Hex Key", "test-security-jwt.php/encode-decode-known-hex", {
        response: {
            jwt: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoiYjNkZGY5NWNkNDZkMzgwODAxYzViMmQ4ZDkwZTE0YzdkZDZkMWVkMjY2MDA3OWI4N2IxNDQ4NWM2NDM1NDNlNiJ9.Hmq1VrzIQ0NceknTXjgI_kMhIFTnoeA9heEjhvgNJ5U",
            payload: {test:"b3ddf95cd46d380801c5b2d8d90e14c7dd6d1ed2660079b87b14485c643543e6"}
        }
    });

    if (window.runTestsWithRSA) {
        runHttpUnitTest("Security JWT - Encode and Decode with a Known Key using 'RS256'", "test-security-jwt.php/encode-decode-rs256", {
            response: {
                jwt: "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoiUlMyNTYifQ.mSBiAeZHy2MTuJZ56B3RTPsdDWbRb9NPofadIJ8AOsHXyt8aDWpyNy3GHI16RgutXLmohdrnc3TFKRjTVrMxkZ6E0KO2A9gZiE1ZjIiP-kfdNs0--V5PjzKWe4GcJ6zIuDgcCKp7eURKty89jiMAtSP7woMk61oDYGSN0JNvA7UJmJYRKcU2FhBndqFGBQyTbjWdyUGDDpdVpWbKoiIWbabX6di6144zOKVEuKgx_ckxZNq6mw9KZJ5d9uU7K-BU5mvJVsvz-yQqGmYZIf-bNSG1WiX12l84kWCL-Y_cgP3wTvbdJVirn_7zKOw4En5jNmAi71kUKrVnpPi23CI_zA",
                payload: {test:"RS256"}
            }
        });
    
        runHttpUnitTest("Security JWT - Encode and Decode with a Known Key using 'RS384'", "test-security-jwt.php/encode-decode-rs384", {
            response: {
                jwt: "eyJhbGciOiJSUzM4NCIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoiUlMzODQifQ.PFQ_bgZjnqtPcuFOpSFuBYRskzP7AM2Jvx04fUCJdQ5mMOX_5lDcDiW9Elfe-5ta-8KgmdGHsJsKHtS5lJ6WSkZrfuNa_gBkquy39MbSh9FfXACeSgGmwmdjlLD157hFS3rTjjxpricD4E29LqqXjyeZR7e0mc13KqmB_RsbbXJzoRKvP4kBRoZvAGDSzk-IUw9kYtwxDpuKlIMjunW9X2hBeNlhfru0AQU6ucWa8N4URG82EpT2YjLmPLwtqOm9N1-wpkJj7SiogXX3xCDhG1OUVmk95Tic0yfnKbN_ITaYktJapS9uN9b9vnauKURbZ0MHcqLOzw9FTX-Kow6F1Q",
                payload: {test:"RS384"}
            }
        });
    
        runHttpUnitTest("Security JWT - Encode and Decode with a Known Key using 'RS512'", "test-security-jwt.php/encode-decode-rs512", {
            response: {
                jwt: "eyJhbGciOiJSUzUxMiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoiUlM1MTIifQ.XUeVFsD_lOmaP-A3ipLJ3myLNpqppKKXCdTLMXENwsdab4iGPeYIk64odAqJwAYomud3EvnPP157Q7M1fDKYYzwEodxrDM-lGbwiCnAiwWrnw5q0-hlIUDbsTkvUObhhqOIKoxcMx_VdfU0IQmioB4eIxcFEESE22-rHSY1joE6n9Iii2KsqyJ6Y6ufw5CJmGutvGLCe4qNVHLKSVhHAPwrEjqmdytawlona2z04zIBI68cYxj-i_aeNY7fk_HUD6lYAaKP8XZhfZf_bVo5GBr-5WTYjUTjbIUYq2mBpWwYXO_anERIDn7ZaZ3lSSEY7x1v5rdlTpWUDJWwbhcfFIg",
                payload: {test:"RS512"}
            }
        });
    }

    runHttpUnitTest("Security JWT - Encode and Decode with an Insecure Key", "test-security-jwt.php/encode-decode-with-insecure-key", {
        response: {
            jwt: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoiSW5zZWN1cmVLZXkifQ.Nk8SVK3WZtjmeCNDlSKBBXdPaxh2LkhWBS77VcZo8mg",
            payload: {test:"InsecureKey"}
        }
    });

    runHttpUnitTest("Security JWT - Verify that Claims are validated by Default", "test-security-jwt.php/claims-validated-by-default", {
        response: {
            payload_fields: ["test", "exp"],
            returned: null
        }
    });

    runHttpUnitTest("Security JWT - Use Exception on Error with an Expired Token", "test-security-jwt.php/exception-on-error", {
        response: "Error - JWT Validation failed. Token is expired."
    });
    
    runHttpUnitTest("Security JWT - Errors for all JWT Claims", "test-security-jwt.php/claim-errors", {
        response: [
            "Error - JWT Validation failed. Submitted [iss] field however no issuers are defined in [allowedIssuers()].",
            "Error - JWT Validation failed. Submitted [sub] field however a subject is not defined in [requireSubject()].",
            "Error - JWT Validation failed. Submitted [aud] field however no audiences are defined in [allowedAudiences()].",
            "Error - JWT Validation failed. Token is expired.",
            "Error - JWT Validation failed. The token is valid but it cannot be used before [Tue, 19 Jan 2038 03:14:07 +0000] which is the value specified by the [nbf] field.",
            "Error - JWT Validation failed. Field [iat] should be a [integer] but received a [string].",
            "Error - JWT Validation failed. Submitted [jti] field however a JWT ID is not defined from [requireJwtId()].",
            "Success Count [7] with validateDefinedClaims(false)",

            "Error - JWT Validation failed. Submitted [iss] field however no issuers are defined in [allowedIssuers()].",
            "Error - JWT Validation failed. Submitted [sub] field however a subject is not defined in [requireSubject()].",
            "Error - JWT Validation failed. Submitted [aud] field however no audiences are defined in [allowedAudiences()].",
            "Error - JWT Validation failed. Submitted [jti] field however a JWT ID is not defined from [requireJwtId()].",

            "Error - JWT Validation failed. Submitted [iss] field [issue] does not match one of the required issuers [example.com].",
            "Error - JWT Validation failed. Submitted [sub] field [subject] does not match the subject value [TestSubject].",
            "Error - JWT Validation failed. Submitted [aud] field [audience] does not match one of the required audience values [UnitTesters].",
            "Error - JWT Validation failed. Submitted [jti] field [jwt_id] does not match the required JWT ID [12345]."
        ]
    });

    runHttpUnitTest("Security JWT - Verify that leeway-time works correctly for 'exp'", "test-security-jwt.php/claim-exp-leeway", {
        response: [
            "success",
            "Error - JWT Validation failed. Token is expired.",
        ]
    });    

    runHttpUnitTest("Security JWT - Verify that leeway-time works correctly for 'nbf'", "test-security-jwt.php/claim-nbf-leeway", {
        response: [
            "success",
            "ErrorFound",
        ]
    });

    runHttpUnitTest("Security JWT - Token with all Claims Validated", "test-security-jwt.php/validate-all-claims", {
        response: {
            "iss": "issue",
            "sub": "subject",
            "aud": "audience",
            "exp": 2147483647,
            "nbf": 1514764800,
            "iat": 1514764800,
            "jti": "jwt_id"
        }
    });

})();