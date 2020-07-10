/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode

    runHttpUnitTest("Security Crypto - Class [Encryption] - Default Settings for Getter/Setter Functions", "test-security-crypto.php/default-settings-encryption", {
        response: {
            "get_class": "FastSitePHP\\Security\\Crypto\\Encryption",
            "get_parent_class": "FastSitePHP\\Security\\Crypto\\AbstractCrypto",
            "class_implements": [
                "FastSitePHP\\Security\\Crypto\\CryptoInterface"
            ],
            "exceptionOnError": false,
            "allowNull": false,
            "hashingAlgorithm": "sha256",
            "encryptThenAuthenticate": true,
            "keyType": "key",
            "pbkdf2Algorithm": "sha512",
            "pbkdf2Iterations": 200000,
            "keySizeHmac": 256,
            "encryptionAlgorithm": "aes-256-cbc",
            "returnFormat": "base64url",
            "dataFormat": "type-byte",
            "keySizeEnc": 256,
            "isAEAD_Mode": false
        }
    });

    runHttpUnitTest("Security Crypto - Class [FileEncryption] - Default Settings for Getter/Setter Functions", "test-security-crypto.php/default-settings-file-encryption", {
        response: {
            "get_class": "FastSitePHP\\Security\\Crypto\\FileEncryption",
            "get_parent_class": "FastSitePHP\\Security\\Crypto\\AbstractCrypto",
            "class_implements": [
                "FastSitePHP\\Security\\Crypto\\CryptoInterface"
            ],
            "encryptThenAuthenticate": true,
            "keyType": "key",
            "pbkdf2Algorithm": "sha512",
            "pbkdf2Iterations": 200000,
            "displayCmdErrorDetail": false,
            "processFilesWithCmdLine": false
        }
    });

    runHttpUnitTest("Security Crypto - Class [SignedData] - Default Settings for Getter/Setter Functions", "test-security-crypto.php/default-settings-signed-data", {
        response: {
            "get_class": "FastSitePHP\\Security\\Crypto\\SignedData",
            "get_parent_class": "FastSitePHP\\Security\\Crypto\\AbstractCrypto",
            "class_implements": [
                "FastSitePHP\\Security\\Crypto\\CryptoInterface"
            ],
            "exceptionOnError": false,
            "allowNull": false,
            "hashingAlgorithm": "sha256",
            "keySizeHmac": 256
        }
    });
    
    runHttpUnitTest("Security Crypto - Compatibility Functions", "test-security-crypto.php/compatibility-functions", {
        responseContains: [[
            "[/compatibility-functions], [Tests: 26], [Error Tests: 6], [Len: 661], [sha256: 5fa42c28767343636ed2ce0b1c70b548415a23bb7dffc6d7b103294ce7922205]",
            "[/compatibility-functions], [Tests: 26], [Error Tests: 6], [Len: 654], [sha256: 45589eff61e712d8959f0a4b5a70bd1cc330b6ce08e7bf8dc53040f4ee5614cd]",
            "[/compatibility-functions], [Tests: 26], [Error Tests: 6], [Len: 704], [sha256: 4ad29ee9cdcad1ce645bb179f700e20cb7cc6c859d3ff1e9a18b9ac2ad19ff0c]",
        ]]
    });
    
    runHttpUnitTest("Security Crypto - Compatibility Functions for PBKDF2 - RFC 6070 Test Vectors", "test-security-crypto.php/compatibility-functions-pbkdf2", {
        responseContains: [[
            // PHP 5 and 7
            "[/compatibility-functions-pbkdf2], [Tests: 12], [Error Tests: 3], [Len: 632], [sha256: 880e6e8f4331679ac682d0a4da9d5303e481752505f4df7cb4d8bb543925528d]",
            // PHP 8
            "[/compatibility-functions-pbkdf2], [Tests: 12], [Error Tests: 3], [Len: 687], [sha256: 16b7c5df588e72f2dbd734ed663a3e894de4284413faf3bb802f5ff707725a85]",
            // This version only runs if uncommenting long running tests for pbkdf2
            "[/compatibility-functions-pbkdf2], [Tests: 14], [Error Tests: 3], [Len: 704], [sha256: 40ccf3534a26bc302bf07450ba3f1d351a810cbe8e0b3a21a4c75b4ce763c8f3]",
        ]]
    });
    
    runHttpUnitTest("Security Crypto - Check File Setup", "test-security-crypto.php/check-file-setup", {
        responseContains: [[
            "[/check-file-setup], Test Skipped, Running on Windows",
            "[/check-file-setup], [Tests: 3], {valid:boolean, whoami:string, path:string, getenforce:string_or_null, commands:array[openssl:string, echo:string, cat:string, cp:string, tail:string, rm:string, ruby:string, xxd:string, ]}",
            "[/check-file-setup], [Tests: 3], {valid:boolean, whoami:string, path:string, getenforce:string_or_null, commands:array[openssl:string, echo:string, cat:string, cp:string, tail:string, rm:string, truncate:string, stat:string, xxd:string, ]}",
            // Nginx will return [path:boolean] using a standard setup:
            "[/check-file-setup], [Tests: 3], {valid:boolean, whoami:string, path:boolean, getenforce:string_or_null, commands:array[openssl:string, echo:string, cat:string, cp:string, tail:string, rm:string, truncate:string, stat:string, xxd:string, ]}"
        ]]
    });

    runHttpUnitTest("Security Crypto - Base64 URL Safe Encoding and Decoding", "test-security-crypto.php/validate-base64url-encoding", {
        response: "[/validate-base64url-encoding], [Tests: 15], [Statements: 60], [Len: 2136], [sha256: fcae9e5b92009d9d5229b0345aabce57cc612b0fdfbed05d79f78be526c36efe], [Exception: Invalid parameter of type [array] for [FastSitePHP\\Encoding\\Base64Url::decode()], only strings or null can be decoded.]"
    });

    runHttpUnitTest("Security Crypto - Encrypt and Decrypt using Encryption Algorithm AES-256-CBC", "test-security-crypto.php/encrypt-and-decrypt-aes-256-cbc", {
        response: "[/encrypt-and-decrypt-aes-256-cbc], [aes-256-cbc], [Tests: 125], [Len: 531], [sha256: 74ef15f56f1702f5c83c652ef4ad40b132947d59334678a61731229b97b12772]"
    });

    runHttpUnitTest("Security Crypto - Encrypt and Decrypt using Encryption Algorithm AES-256-CTR", "test-security-crypto.php/encrypt-and-decrypt-aes-256-ctr", {
        responseContains: [[
            "[/encrypt-and-decrypt-aes-256-ctr], Test Skipped, PHP Version earlier than 5.5",
            "[/encrypt-and-decrypt-aes-256-ctr], [aes-256-ctr], [Tests: 105], [Len: 531], [sha256: 74ef15f56f1702f5c83c652ef4ad40b132947d59334678a61731229b97b12772]"
        ]]
    });

    runHttpUnitTest("Security Crypto - Encrypt and Decrypt using Encryption Algorithm AES-256-GCM", "test-security-crypto.php/encrypt-and-decrypt-aes-256-gcm", {
        responseContains: [[
            "[/encrypt-and-decrypt-aes-256-gcm], Test Skipped, PHP Version earlier than 7.1",
            "[/encrypt-and-decrypt-aes-256-gcm], [aes-256-gcm], [Tests: 105], [Len: 531], [sha256: 74ef15f56f1702f5c83c652ef4ad40b132947d59334678a61731229b97b12772]"
        ]]
    });

    runHttpUnitTest("Security Crypto - Decrypt Known Values with Advanced Encryption Standard (AES) using CBC Mode", "test-security-crypto.php/decrypt-known-values-aes-cbc", {
        response: "[/decrypt-known-values-aes-cbc], [Tests: 44], [Len: 1262], [isAEAD_Mode: false], [sha256: 88008b3d09d504163eb219f75d3c131a15deb4fbc874215c653f67200b9ebbdf]"
    });

    runHttpUnitTest("Security Crypto - Decrypt Known Values with Advanced Encryption Standard (AES) using CTR Mode", "test-security-crypto.php/decrypt-known-values-aes-ctr", {
        responseContains: [[
            "[/decrypt-known-values-aes-ctr], Test Skipped, PHP Version earlier than 5.5",
            "[/decrypt-known-values-aes-ctr], [Tests: 24], [Len: 822], [isAEAD_Mode: false], [sha256: 6176383eb6d722d9dfaa9d6de5f5565e37a541733a6ac812cd70acbd2f457443]"
        ]]
    });

    runHttpUnitTest("Security Crypto - Decrypt Known Values with Advanced Encryption Standard (AES) using GCM Mode", "test-security-crypto.php/decrypt-known-values-aes-gcm", {
        responseContains: [[
            "[/decrypt-known-values-aes-gcm], Test Skipped, PHP Version earlier than 7.1",
            "[/decrypt-known-values-aes-gcm], [Tests: 22], [Len: 802], [isAEAD_Mode: true], [sha256: 67cb05e7aceb5b7ab6acad7e4d3c86f6719bd0714b4e634b3cd054d23bd9580a]"
        ]]
    });

    // This function often takes 5-10 seconds so it can be turned off from the main test page
    if (window.runTimeConsumingTasks) {
        runHttpUnitTest("Security Crypto - Decrypt With Password using PBKDF2", "test-security-crypto.php/decrypt-with-password", {
            response: "[/decrypt-with-password], [Tests: 13], [Len: 621], [isAEAD_Mode: false], [sha256: ad4c18f3c1e21a6fa59e41ea058144ec272d7bc477f07d97e1d151cf963e5a6b]"
        });
    }

    runHttpUnitTest("Security Crypto - Sign and Verify using HMAC with SHA1", "test-security-crypto.php/sign-and-verify-sha1", {
        response: "[/sign-and-verify-sha1], [sha1], [Key Size: 160], [Tests: 84], [Len: 531], [sha256: 74ef15f56f1702f5c83c652ef4ad40b132947d59334678a61731229b97b12772]"
    });

    runHttpUnitTest("Security Crypto - Sign and Verify using HMAC with SHA2-256", "test-security-crypto.php/sign-and-verify-sha2-256", {
        response: "[/sign-and-verify-sha2-256], [sha256], [Key Size: 256], [Tests: 104], [Len: 531], [sha256: 74ef15f56f1702f5c83c652ef4ad40b132947d59334678a61731229b97b12772]"
    });

    runHttpUnitTest("Security Crypto - Sign and Verify using HMAC with SHA2-384", "test-security-crypto.php/sign-and-verify-sha2-384", {
        response: "[/sign-and-verify-sha2-384], [sha384], [Key Size: 384], [Tests: 84], [Len: 531], [sha256: 74ef15f56f1702f5c83c652ef4ad40b132947d59334678a61731229b97b12772]"
    });

   runHttpUnitTest("Security Crypto - Sign and Verify using HMAC with SHA3-512", "test-security-crypto.php/sign-and-verify-sha3-512", {
        responseContains: [[
            "[/sign-and-verify-sha3-512], Test Skipped, PHP Version earlier than 7.2",
            "[/sign-and-verify-sha3-512], [sha3-512], [Key Size: 512], [Tests: 84], [Len: 531], [sha256: 74ef15f56f1702f5c83c652ef4ad40b132947d59334678a61731229b97b12772]"
        ]]
    });

    runHttpUnitTest("Security Crypto - Verify Known Values", "test-security-crypto.php/verify-known-values", {
        response: "[/verify-known-values], [Tests: 31], [Len: 377], [sha256: b52f8824c715e4fcf25f6ed4ffd86a5bfa5692ad89e75a9c4c6890dffedf7b17]"
    });

    runHttpUnitTest("Security Crypto - Decrypt Large Integer Values", "test-security-crypto.php/decrypt-large-ints", {
        responseContains: [[
            "[/decrypt-large-ints], [Int Size: 4], [Len: 196], [sha256: 0f09758112ddd6d27dc5290e62017716b43e4799fc95ee95b2b623f405dbb382]",
            "[/decrypt-large-ints], [Int Size: 8], [Len: 214], [sha256: 469d6425d9e787cecbf77750424c330220001d585f46bce3e63132be16870064]"
        ]]
    });

    runHttpUnitTest("Security Crypto - Verify Large Integer Values", "test-security-crypto.php/verify-large-ints", {
        responseContains: [[
            "[/verify-large-ints], [Int Size: 4], [Len: 196], [sha256: 0f09758112ddd6d27dc5290e62017716b43e4799fc95ee95b2b623f405dbb382]",
            "[/verify-large-ints], [Int Size: 8], [Len: 214], [sha256: 469d6425d9e787cecbf77750424c330220001d585f46bce3e63132be16870064]"
        ]]
    });

    if (window.runTestsRequiringFileWrite) {
        runHttpUnitTest("Security Crypto - Create and Decrypt a 10 MB Empty File with a known Crypto Key and IV", "test-security-crypto.php/decrypt-known-file-with-key", {
            responseContains: [[
                "[/decrypt-known-file-with-key], [Other_OS], [true], [f1c9645dbc14efddc7d8a322685f26eb], [10485824,10485792], [371b4aad41c87bc27bb6cdd58c2c7c48,d257ac3640eb35d82591facd8c7ddb25], [f1c9645dbc14efddc7d8a322685f26eb]",
                "[/decrypt-known-file-with-key], [Windows], [false], [f1c9645dbc14efddc7d8a322685f26eb], [10485824,10485792], [371b4aad41c87bc27bb6cdd58c2c7c48,d257ac3640eb35d82591facd8c7ddb25], [f1c9645dbc14efddc7d8a322685f26eb]",
            ]]
        });

        if (window.runTimeConsumingTasks) {
            runHttpUnitTest("Security Crypto - Create and Decrypt a 10 MB Empty File with a known Crypto Password and IV", "test-security-crypto.php/decrypt-known-file-with-password", {
                responseContains: [[
                    "[/decrypt-known-file-with-password], [Other_OS], [true], [f1c9645dbc14efddc7d8a322685f26eb], [10485824,10485792], [8908ec149e2ae3fa917e75c3f622a29f,37afbf1cb5a459e45e4de30ef467fbc1], [f1c9645dbc14efddc7d8a322685f26eb]",
                    "[/decrypt-known-file-with-password], [Windows], [false], [f1c9645dbc14efddc7d8a322685f26eb], [10485824,10485792], [8908ec149e2ae3fa917e75c3f622a29f,37afbf1cb5a459e45e4de30ef467fbc1], [f1c9645dbc14efddc7d8a322685f26eb]",
                ]]
            });
        }

        runHttpUnitTest("Security Crypto - Encrypt a file using [encryptFile()] and decrypt using [decryptFile()]", "test-security-crypto.php/encrypt-and-decrypt-file", {
            responseContains: [[
                "[/encrypt-and-decrypt-file], [Other_OS], [88], [144,112], [true], [FileEncryption->encryptFile(),FileEncryption->decryptFile()], [89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323]",
                "[/encrypt-and-decrypt-file], [Windows], [88], [144,112], [false], [FileEncryption->encryptFile(),FileEncryption->decryptFile()], [89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323]",
            ]]
        });
        
        runHttpUnitTest("Security Crypto - Encrypt a file using [encryptFile()] and decrypt using [decryptFile()] with [process_files_with_cmd_line = false]", "test-security-crypto.php/encrypt-and-decrypt-file-no-cmd", {
            response: "[/encrypt-and-decrypt-file-no-cmd], [88], [144,112], [false], [FileEncryption->encryptFile(),FileEncryption->decryptFile()], [89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323]"
        });
        
        runHttpUnitTest("Security Crypto - Encrypt a file using [encrypt()] and decrypt using [decryptFile()]", "test-security-crypto.php/encrypt-text-and-decrypt-file", {
            response: "[/encrypt-text-and-decrypt-file], [88], [144,112], [false], [Encryption->encrypt(),FileEncryption->decryptFile()], [89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323]"
        });

        runHttpUnitTest("Security Crypto - Encrypt a file using [encryptFile()] and decrypt using [decrypt()]", "test-security-crypto.php/encrypt-file-and-decrypt-text", {
            response: "[/encrypt-file-and-decrypt-text], [88], [144,112], [false], [FileEncryption->encryptFile(),Encryption->decrypt()], [89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323]"
        });
    }

    runHttpUnitTest("Security Crypto - RFC 4231 HMAC Test Vectors", "test-security-crypto.php/rfc-4231-hmac-test-vectors", {
        response: "[/rfc-4231-hmac-test-vectors], [Tests: 28], [Len: 4318], [sha256: fdb8c1001711631ebe66f315985d66e27c11cd8bf8515d9eff0d8eac1e421d55]"
    });

    runHttpUnitTest("Security Crypto - RFC 4868 HMAC Test Vectors", "test-security-crypto.php/rfc-4868-hmac-test-vectors", {
        response: "[/rfc-4868-hmac-test-vectors], [Tests: 30], [Len: 8345], [sha256: da149e39c44e9333af561a91ce179e034067f680db28d0aa454a5b60acd68648]"
    });
    
    runHttpUnitTest("Security Crypto - NIST 800 38A - AES CBC Test Vectors", "test-security-crypto.php/nist-800-38a-aes-cbc-test-vectors", {
        response: "[/nist-800-38a-aes-cbc-test-vectors], [Tests: 3], [Len: 1279], [sha256: 7a307539290268c754a0a7fc89ee4dd5d0e2e899241d555e9135db789a16a238]"
    });
    
    runHttpUnitTest("Security Crypto - NIST 800 38A - AES CTR Test Vectors", "test-security-crypto.php/nist-800-38a-aes-ctr-test-vectors", {
        responseContains: [[
            "[/nist-800-38a-aes-ctr-test-vectors], Test Skipped, PHP Version earlier than 5.5",
            "[/nist-800-38a-aes-ctr-test-vectors], [Tests: 3], [Len: 1162], [sha256: d822a772e42172e39d5c40d6caeee4e83fae82eb58cda5db90feb24880a6a992]",
        ]]
    });

    runHttpUnitTest("Security Crypto - NIST 800 38D - AES GCM Test Vectors", "test-security-crypto.php/nist-800-38d-aes-gcm-test-vectors", {
        responseContains: [[
            "[/nist-800-38d-aes-gcm-test-vectors], Test Skipped, PHP Version earlier than 7.1",
            "[/nist-800-38d-aes-gcm-test-vectors], [Tests: 30], [Len: 8077], [sha256: 48ac1db2cec321b9bebba4eace6ea5b235eabd16eb2bc4a44d0512586fc811ea]",
        ]]
    });

    runHttpUnitTest("Security Cryto - Error Messages", "test-security-crypto.php/encryption-class-errors", {
        // Lines:
        //   [Tests: 34] = PHP 7.1 or later
        //   [Tests: 35] = Below 7.1
        //   [Tests: 36] = Below 5.5
        responseContains: [[
            "[/encryption-class-errors], [Tests: 34], [Len: 6309], [sha256: 36d00efc93eea86a574efea93094eff8d662e4f552aa6336ffb1ed68cc00ad2e]",
            "[/encryption-class-errors], [Tests: 35], [Len: 177], [sha256: 30fe6c4a6fde871ab45851a20ffe844910b849797207f1e3c5fe7389f174ae5e]",
            "[/encryption-class-errors], [Tests: 36], [Len: 456], [sha256: 1934b18dbd0d3fcf6321ef84386bcb17d566382ab1f59321b5c18a0b583b3e02]",
        ]]
    });
    
    runHttpUnitTest("Security Cryto - Error Messages", "test-security-crypto.php/signed-data-class-errors", {
        response: "[/signed-data-class-errors], [Tests: 22], [Len: 4078], [sha256: d3768f0d0b839f38481a4daa04282e382bc801d26aa175a206256b37f9b1a545]"
    });

})();
