<?php
// ==========================================================================
// Unit Testing Page
// *) This file uses only core Framework files
//     and required [Security\Crypto\*] Classes
// *) Related to this file under the scripts folder on the main site are
//    other manual test files:
//      - crypto-test-many-files.php
//      - crypto-test-large-file.php
//      - shell/bash/encrypt.sh
//    The PHP files take a long time to run and [crypto-test-many-files.php]
//    is intended for custom parameters. The [encrypt.sh] is a Bash
//    Script that is compatible with the [FileEncryption] class.
//    If any changes are made to [Encryption] or [FileEncryption]
//    then it's important to test with the custom scripts.
// ==========================================================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Encoding/Base64Url.php';
    require '../../vendor/fastsitephp/src/Encoding/Json.php';
    require '../../vendor/fastsitephp/src/Encoding/Utf8.php';
    require '../../vendor/fastsitephp/src/Security/Crypto.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/AbstractCrypto.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/CryptoInterface.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/Encryption.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/SignedData.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/FileEncryption.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/Random.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';
    require '../src/Encoding/Base64Url.php';
    require '../src/Encoding/Json.php';
    require '../src/Encoding/Utf8.php';
    require '../src/Security/Crypto.php';
    require '../src/Security/Crypto/AbstractCrypto.php';
    require '../src/Security/Crypto/CryptoInterface.php';
    require '../src/Security/Crypto/Encryption.php';
    require '../src/Security/Crypto/SignedData.php';
    require '../src/Security/Crypto/FileEncryption.php';
    require '../src/Security/Crypto/Random.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// If running the full [pbkdf2] Test then uncomment the following line
// set_time_limit(0);

// -----------------------------------------------------------
// Shared Classes and Functions
// -----------------------------------------------------------

// Basic Class for Testing
class Test
{
    public $value = 'Test';
    public $prop1 = 123;
    public $prop2 = true;
}

// Shared function that gets called to verify compatability/polyfill functions
function runCompatabilityTests($tests, $error_tests) {
	// Keep Count and save all results to a single string
	$tests_count = 0;
	$error_count = 0;
	$all_results_string = '';

    // -------------------------
    // Run Tests
    foreach ($tests as $test) {
        // Call Function
        if (isset($test['parameters'])) {
            $value = call_user_func_array($test['function'], $test['parameters']);
        } else {
            $value = call_user_func($test['function'], $test['data']);
        }

        // Check Expected Result
        if ($value !== $test['expected']) {
            echo sprintf('Test %d did not return the expected value:', $tests_count);
            echo '<br>';
            echo json_encode($test);
            echo '<br>';
            echo '<strong>Returned:</strong> ' . $value;
            echo '<br>';
            echo '<strong>Expected:</strong> ' . $test['expected'];
            exit();
        }

        // keep count of passed tests
        // and add results to a string.
        $tests_count++;
        $all_results_string .= gettype($value) . (string)$value;
    }

    // -------------------------
    // Run Error Tests
    foreach ($error_tests as $test) {
       try {
           // Call Function
           if (isset($test['parameters'])) {
               call_user_func_array($test['function'], $test['parameters']);
           } else {
               call_user_func($test['function'], $test['data']);
           }

           // Handle Specific Versions of PHP
           if (isset($test['allow_false']) && PHP_VERSION_ID < $test['allow_false']) {
               $error_count++;
               $all_results_string .= $test['expected'];
               continue;
           }

           // Fatal Error, function call should have errored
           echo sprintf('Test %d should have failed:', $error_count);
           echo '<br>';
           echo json_encode($test);
           exit();
       } catch (\Exception $e) {
           // Check expected error message
           if (is_string($test['expected'])) {
               $matches = ($e->getMessage() === $test['expected']);
           } else {
                $matches = false;
                foreach ($test['expected'] as $expected) {
                    $matches = ($e->getMessage() === $expected);
                    if ($matches) {
                        break;
                    }
                }
           }
           if (!$matches) {
               echo sprintf('Test %d did not return the expected error message:', $error_count);
               echo '<br>';
               echo '<strong>Error:</strong> ' . $e->getMessage();
               echo '<br>';
               echo '<strong>Expected:</strong> ' . json_encode($test['expected']);
               exit();
           }

           // User defined functions cannot raise E_WARNING errors so
           // instead E_USER_WARNING errors are raised on polyfill functions.
           if ($e->getSeverity() !== E_WARNING && $e->getSeverity() !== E_USER_WARNING) {
               echo sprintf('Test %d did not return either E_WARNING or E_USER_WARNING:', $error_count);
               echo '<br>';
               echo json_encode($test);
               echo '<br>';
               echo json_encode($e->getSeverity());
               exit();
           }

           // Keep count of errors
		   // and add results to a string.
           $error_count++;
           $all_results_string .= $e->getMessage();
       }
    }

    // Return counts
    return array($tests_count, $error_count, $all_results_string);
}

// Test Encryption and Decryption with a specified Encryption Algorithms.
// Regardless of the Encryption Algorithm used everything should succeed and
// the Hash Result should be the same. A new key is randomly created each time.
function encryptAndDecrypt($encryption_algorithm, $app = null) {
    // Create Crypto Object and set
    // mode if not using the default.
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();
    if ($encryption_algorithm !== 'aes-256-cbc') {
        $crypto->encryptionAlgorithm($encryption_algorithm);
    }

    // Create a Random Key
    $key = $crypto->generateKey();
    if ($app !== null) {
        $app->config['ENCRYPTION_KEY'] = $key;
    }

    // By default nulls are not allowed for encryption,
    // however they are allowed here for testing.
    $crypto->allowNull(true);

    // Create an stdClass Object. When decrypted
    // it comes back as a Associative Array.
    $obj = new \stdClass;
    $obj->userId = 1;
    $obj->userName = 'Conrad';
    $obj->roles = array('admin', 'user');

    // Define Tests
    $tests = array(
        // Test different data types
        null,
        '',
        'This is a Test',
        123,
        123.456,
        true,
        false,
        array('value1', 'value2'),
        $obj,
        new Test(),
        // Test the English Word 'Hello' in different languages
        // See: https://en.wikipedia.org/wiki/List_of_languages_by_number_of_native_speakers
        '你好', // Mandarin Chinese
        'Hola', // Spanish
        'Hello', // English
        'नमस्ते', // Hindi
        'مرحبا', // Arabic
        'Olá', // Portuguese
        'হ্যালো', // Bengali
        'Здравствуйте', // Russian
        'こんにちは', // Japanese
        'ਸਤ ਸ੍ਰੀ ਅਕਾਲ', // Punjabi
        'Hallo', // German
    );

    // Run Tests
    $tests_count = 0;
    foreach ($tests as $test) {
        // Encrypt and Decrypt
        $encrypted = $crypto->encrypt($test, $key);
        $decrypted = $crypto->decrypt($encrypted, $key);

        // Make sure Encrypted Text (ciphertext) does not match the data to encrypt (plaintext)
        if ($test === $encrypted) {
            echo sprintf('Test %d did failed because ciphertext and plaintext matched:', $tests_count);
            echo '<br>';
            echo '<strong>Expected:</strong> ' . json_encode($test);
            exit();
        }
        $tests_count++;

        // If using default encryption method then verify the Crypto Class
        if ($encryption_algorithm === 'aes-256-cbc' && $test !== null) {
            $encrypted2 = \FastSitePHP\Security\Crypto::encrypt($test);
            $decrypted2 = \FastSitePHP\Security\Crypto::decrypt($encrypted);
            $decrypted3 = $crypto->decrypt($encrypted2, $key);
            if ($decrypted !== $decrypted2 || $decrypted !== $decrypted3) {
                echo sprintf('Test %d did failed because [Crypto::decrypt()] and [$crypto->decrypt()] are not matching:', $tests_count);
                echo '<br>';
                echo '<strong>Expected:</strong> ' . json_encode($test);
                exit();
            }
            $tests_count++;
        }

        // Make sure Encrypted Text (ciphertext) is returned as Base64URL
        if (\FastSitePHP\Encoding\Base64Url::decode($encrypted) === false) {
            echo sprintf('Test %d did failed because ciphertext was returned in a format other than base64:', $tests_count);
            echo '<br>';
            echo '<strong>Expected:</strong> ' . json_encode($test);
            exit();
        }
        $tests_count++;

        // Check Expected Result
        // Decrypted objects come back as an [ Associative Array / Dictionary / Ordered Map / etc ].
        // This terminology varies from programming language to programming language.
        if (is_object($test)) {
            $matches = ($decrypted === (array)$test);
        } else {
            $matches = ($decrypted === $test);
        }
        $tests_count++;

        if (!$matches) {
            echo sprintf('Test %d did not return the expected value:', $tests_count);
            echo '<br>';
            echo '<strong>is_object:</strong> ' . (is_object($test) ? 'true' : 'false');
            echo '<br>';
            echo '<strong>Returned:</strong> ' . json_encode($decrypted);
            echo '<br>';
            echo '<strong>Expected:</strong> ' . json_encode($test);
            exit();
        }
    }

    // Now Test with AAD (Additional Authorization Data)
    $aad = 'Additional Authorization Data';
    foreach ($tests as $test) {
        // Encrypt and Decrypt
        $encrypted = $crypto->encrypt($test, $key, $aad);
        $decrypted = $crypto->decrypt($encrypted, $key, $aad);

        // Check Expected Result
        // Decrypted objects come back as an [ Associative Array / Dictionary / Ordered Map / etc ].
        // This terminology varies from programming language to programming language.
        if (is_object($test)) {
            $matches = ($decrypted === (array)$test);
        } else {
            $matches = ($decrypted === $test);
        }
        $tests_count++;

        if (!$matches) {
            echo sprintf('Test %d did not return the expected value on AAD Tests:', $tests_count);
            echo '<br>';
            echo '<strong>is_object:</strong> ' . (is_object($test) ? 'true' : 'false');
            echo '<br>';
            echo '<strong>Returned:</strong> ' . json_encode($decrypted);
            echo '<br>';
            echo '<strong>Expected:</strong> ' . json_encode($test);
            exit();
        }

        // Make sure that if [decrypt()] is called without [$aad]
        // that decryption fails and null is returned.
        if ($crypto->decrypt($encrypted, $key) !== null) {
            echo sprintf('AAD Test %d did not return the null as expected:', $tests_count);
            echo '<br>';
            echo '<strong>is_object:</strong> ' . (is_object($test) ? 'true' : 'false');
            echo '<br>';
            echo '<strong>Data:</strong> ' . json_encode($test);
            exit();
        }
        $tests_count++;
    }

    // Return number of passed tests
    $json = json_encode($tests) . $aad;
    return sprintf('[%s], [Tests: %d], [Len: %d], [sha256: %s]', $crypto->encryptionAlgorithm(), $tests_count, len($json), hash('sha256', $json));
}

// Shared function that takes an array of known encrypted text and values
// with various settings and verifies that they decrypt correctly.
function decryptKnownValues($tests, $app = null) {
    $all_decrypted_text = '';
    $passed_tests = 0;
    $item = 0;
    $properties = array(
        'exceptionOnError',
        'encryptionAlgorithm',
    	'hashingAlgorithm',
    	'returnFormat',
    	'dataFormat',
    	'keySizeEnc',
    	'encryptThenAuthenticate',
    	'pbkdf2Algorithm',
    	'keyType',
    	'pbkdf2Iterations',
    );
    $isAEAD_Mode = '';

    foreach ($tests as $test) {
        // Create a new Crypto Object for each test
        $crypto = new \FastSitePHP\Security\Crypto\Encryption();
        //$crypto->exceptionOnError(true);
        $encrypted_text = $test['encrypted_text'];

        // Define Crypto Class Properties is specified for the current test
        $prop_count = 0;
        foreach ($properties as $prop) {
            if (isset($test[$prop])) {
                $crypto->{$prop}($test[$prop]);
                $prop_count++;
            }
        }
        // Keep track of last value set
        $isAEAD_Mode = json_encode($crypto->isAEAD_Mode());

        // Decrypt
        try
        {
            $decrypted_value = null;
	        if (isset($test['aad'])) {
	            $decrypted_value = $crypto->decrypt($encrypted_text, $test['key'], $test['aad']);
	        } else {
	            $decrypted_value = $crypto->decrypt($encrypted_text, $test['key']);
	        }
	        if ($decrypted_value !== $test['expected_value']) {
                echo sprintf('Failed at Test from $crypto: %d', $item);
                echo '<br>';
                echo '<br>';
                echo json_encode($test);
                echo '<br>';
                echo '<br>';
                echo json_encode($decrypted_value);
                exit();
	        }
            $passed_tests++;

            // Also use Crypto Class if all properties are default
            if ($prop_count === 0 && !isset($test['aad'])) {
                $app->config['ENCRYPTION_KEY'] = $test['key'];
                $decrypted_value2 = \FastSitePHP\Security\Crypto::decrypt($encrypted_text);
                if ($decrypted_value !== $decrypted_value2) {
                    echo sprintf('Failed at Test from [Crypto::decrypt]: %d', $item);
                    echo '<br>';
                    echo '<br>';
                    echo json_encode($test);
                    echo '<br>';
                    echo '<br>';
                    echo json_encode($decrypted_value);
                    exit();
                }
                $passed_tests++;
            }
       } catch (\Exception $e) {
            echo sprintf('Failed at Test: %d', $item);
            echo '<br>';
            echo '<br>';
            echo json_encode($e->getMessage());
            echo '<br>';
            echo '<br>';
            echo json_encode($encrypted_text);
            echo '<br>';
            echo '<br>';
            echo json_encode($test);
            echo '<br>';
            echo '<br>';
            echo json_encode($decrypted_value);
            exit();
       }

        // Build a string of all tested values (used for hashing after all tests complete)
        $item++;
        $all_decrypted_text .= (is_array($decrypted_value) ? json_encode($decrypted_value) : $decrypted_value);
    }

    return sprintf('[Tests: %s], [Len: %d], [isAEAD_Mode: %s], [sha256: %s]', $passed_tests, len($all_decrypted_text), $isAEAD_Mode, hash('sha256', $all_decrypted_text));
}

// Test Signing and Verifying Data with a specified Hashing Algorithms.
// Regardless of the Hashing Algorithm used everything should succeed and
// the Hash Result should be the same. A new key is randomly created each time.
// This test is baesd on [encryptAndDecrypt()].
function signAndVerify($hashing_algorithm, $app = null) {
    // Create SignedData Object and set mode if not using the default.
    $csd = new \FastSitePHP\Security\Crypto\SignedData();
    if ($hashing_algorithm !== 'sha256') {
        $csd->hashingAlgorithm($hashing_algorithm);
    }

    // Create a Random Key
    $key = $csd->generateKey();

    // By default nulls are not allowed for signing,
    // however they are allowed here for testing.
    $csd->allowNull(true);

    // Create an stdClass Object. When verified
    // it comes back as a Associative Array.
    $obj = new \stdClass;
    $obj->userId = 1;
    $obj->userName = 'Conrad';
    $obj->roles = array('admin', 'user');

    // Define Tests
    // These are the same values from [encryptAndDecrypt()]
    $tests = array(
        // Format:
        // array(VALUE, 'START_OF_SIGNED_TEXT'),
        array(null, 'AA.n.'),
        array('', '.s.'),
        array('This is a Test', 'VGhpcyBpcyBhIFRlc3Q.s.'),
        array(123, 'MTIz.i32.'),
        array(123.456, 'MTIzLjQ1Ng.f.'),
        array(true, 'MQ.b.'),
        array(false, 'MA.b.'),
        array(array('value1', 'value2'), 'WyJ2YWx1ZTEiLCJ2YWx1ZTIiXQ.j.'),
        array($obj, 'eyJ1c2VySWQiOjEsInVzZXJOYW1lIjoiQ29ucmFkIiwicm9sZXMiOlsiYWRtaW4iLCJ1c2VyIl19.j.'),
        array(new Test(), 'eyJ2YWx1ZSI6IlRlc3QiLCJwcm9wMSI6MTIzLCJwcm9wMiI6dHJ1ZX0.j.'),
        array('你好', '5L2g5aW9.s.'),
        array('Hola', 'SG9sYQ.s.'),
        array('Hello', 'SGVsbG8.s.'),
        array('नमस्ते', '4KSo4KSu4KS44KWN4KSk4KWH.s.'),
        array('مرحبا', '2YXYsdit2KjYpw.s.'),
        array('Olá', 'T2zDoQ.s.'),
        array('হ্যালো', '4Ka54KeN4Kav4Ka-4Kay4KeL.s.'),
        array('Здравствуйте', '0JfQtNGA0LDQstGB0YLQstGD0LnRgtC1.s.'),
        array('こんにちは', '44GT44KT44Gr44Gh44Gv.s.'),
        array('ਸਤ ਸ੍ਰੀ ਅਕਾਲ', '4Ki44KikIOCouOCpjeCosOCpgCDgqIXgqJXgqL7gqLI.s.'),
        array('Hallo', 'SGFsbG8.s.'),
    );

    // Run Tests
    $tested_values = array();
    $tests_count = 0;
    foreach ($tests as $test) {
        // Values to Test
        $value = $test[0];
        $expected = $test[1];
        $tested_values[] = $value;

        // Sign and Verify
        $signed = $csd->sign($value, $key);
        $verified = $csd->verify($signed, $key);

        // Make sure Signed Text does not match the data to Sign
        if ($value === $signed) {
            echo sprintf('Test %d did failed because ciphertext and plaintext matched:', $tests_count);
            echo '<br>';
            echo '<strong>Expected:</strong> ' . json_encode($test);
            exit();
        }
        $tests_count++;

        // If using default hasing method then verify the Crypto Facade class
        if ($hashing_algorithm === 'sha256' && $value !== null) {
            putenv("SIGNING_KEY=${key}");
            $signed2 = \FastSitePHP\Security\Crypto::sign($value, null); // 2nd Parameter defaults to a 1 hour timeout
            $verified2 = \FastSitePHP\Security\Crypto::verify($signed2);
            if ($signed !== $signed2 || $verified !== $verified2) {
                echo sprintf('Test %d did failed because [Crypto::sign()] and [$crypto->verify()] are not matching:', $tests_count);
                echo '<br>';
                echo '<strong>Test:</strong> ' . json_encode($test);
                exit();
            }
            $tests_count++;
        }

        // Make sure individual parts of the signed data parse to strings
        $data = explode('.', $signed);
        if (count($data) !== 3) {
            echo sprintf('Test %d did failed because signed text was not in the correct format:', $tests_count);
            echo '<br>';
            echo '<strong>Test:</strong> ' . json_encode($test);
            exit();
        }
        if (\FastSitePHP\Encoding\Base64Url::decode($data[0]) === false) {
            echo sprintf('Test %d did failed because signed text value was not in base64url format:', $tests_count);
            echo '<br>';
            echo '<strong>Test:</strong> ' . json_encode($test);
            exit();
        }
        if (\FastSitePHP\Encoding\Base64Url::decode($data[2]) === false) {
            echo sprintf('Test %d did failed because signed hash was not in base64url format:', $tests_count);
            echo '<br>';
            echo '<strong>Test:</strong> ' . json_encode($test);
            exit();
        }
        $tests_count++;

        // Match start of signed text to the expected value. The result
        // of the signed text will be the unique hash which changes every time.
        if (strpos($signed, $expected) !== 0) {
            echo sprintf('Test %d did not return the expected value:', $tests_count);
            echo '<br>';
            echo '<strong>verified:</strong> ' . $signed;
            echo '<br>';
            echo '<strong>expected:</strong> ' . $expected;
            exit();
        }
        $tests_count++;

        // Check Expected Result
        // Decrypted objects come back as an [ Associative Array / Dictionary / Ordered Map / etc ].
        // This terminology varies from programming language to programming language.
        if (is_object($value)) {
            $matches = ($verified === (array)$value);
        } else {
            $matches = ($verified === $value);
        }
        $tests_count++;

        if (!$matches) {
            echo sprintf('Test %d did not return the expected value:', $tests_count);
            echo '<br>';
            echo '<strong>is_object:</strong> ' . (is_object($value) ? 'true' : 'false');
            echo '<br>';
            echo '<strong>Returned:</strong> ' . json_encode($verified);
            echo '<br>';
            echo '<strong>Expected:</strong> ' . json_encode($value);
            exit();
        }
    }

    // Return number of passed tests
    // This format of adding 'Additional Authorization Data' is done
    // so that the resulting hash matches values from ['/encrypt-and-decrypt-*'] routes.
    $json = json_encode($tested_values) . 'Additional Authorization Data';
    return sprintf('[%s], [Key Size: %d], [Tests: %d], [Len: %d], [sha256: %s]', $csd->hashingAlgorithm(), $csd->keySizeHmac(), $tests_count, len($json), hash('sha256', $json));
}

// Shared Function in this file that gets called to verify known
// Standard Test Encryption Vectors. This function only decrypts data.
function runDecryptionTestVectors($tests) {
    // Create Crypto Object and Update Settings for the Test
    // *) Default return format is base64 so change to hex
    // *) Authenticated Encryption is used by default so turn it off
    // *) Objects, Numbers, etc are supported by default so change
    //    [dataFormat] to use strings only otherwise a random extra
    //    byte is added to the end of the plain text before encryption
    //    which would cause these test vectors to not work.
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();
    $crypto
        ->returnFormat('hex')
        ->encryptThenAuthenticate(false)
        ->dataFormat('string-only');

    // Run Tests
    $tests_count = 0;
    foreach ($tests as $test) {
        // Decypt
        // CBC Mode: [CT = Cipher Text] + [P = Padding] + [iv]
        // CTR Mode: [CT = Cipher Text] + [iv]
        // GCM Mode: [CT = Cipher Text] + [iv] + [tag]
        //      GCM may also include AAD (Additional Authorization Data)
        $cipher_text = $test['ct'] . (isset($test['p']) ? $test['p'] : '') . $test['iv'] . (isset($test['t']) ? $test['t'] : '');
        $key = $test['k'];
        $aad = (isset($test['aad']) ? hex2bin($test['aad']) : '');
        $crypto->encryptionAlgorithm($test['a']);
        $crypto->keySizeEnc(strlen($key) / 2 * 8); // Key size is specified in bits while key is specified in hex
        $value = $crypto->decrypt($cipher_text, $key, $aad);
        if ($value !== null) {
            $value = bin2hex($value);
        }

        // Check Expected Result
        if ($value !== $test['pt']) {
            echo sprintf('Test %d did not return the expected value using [%s]:', $tests_count, $test['a']);
            echo '<br>';
            echo json_encode($test);
            echo '<br>';
            echo '<strong>Returned:</strong> ' . $value;
            echo '<br>';
            echo '<strong>Expected:</strong> ' . $test['pt'];
            exit();
        }

        // Keep count of passed tests
        $tests_count++;
    }

    // Return number of passed tests
    return $tests_count;
}

// Get string length using [mb_strlen()] if the extension
// [mbstring] is loaded otherwise use [strlen()].
function len($str)
{
    if (extension_loaded('mbstring')) {
        return mb_strlen($str, '8bit');
    }
    return strlen($str);
}

// This function creates a compatible file for [Crypto->decryptFile()]
// and is based on [encryptFile()] however it uses a known IV
// rather than securely generating a random IV. This function also
// excludes some of the validation. This function is developed only
// for Unit Testing so that a known file can be created and verified.
// For sites and apps not related to testing the IV should always be
// securely generated on each encryption call.
function createFile($file_path, $process_files_with_cmd_line, $enc_file, $key_enc, $key_hmac, $iv)
{
	// Make sure any previous test files were deleted
	if (is_file($enc_file)) {
		throw new \Exception(sprintf('File encryption failed because the file for encryption [%s] already exists. Delete your previous test files and try again.', $enc_file));
    }

    // Create using Command Line (Linux, Unix, Mac)
    if ($process_files_with_cmd_line) {
        // Get path for the [xxd] command
        $xxd = xxdPath();

        // NOTE - IV would normally be generated with code similar to this:
		//$cmd = $xxd . ' -l 16 -p /dev/urandom';
        //$iv = runCmd($cmd, true);

        // Encrypt using openssl command line
        $cmd = 'openssl enc -aes-256-cbc -in "' . $file_path . '" -out "' . $enc_file . '" -iv ' . $iv . '  -K ' . $key_enc . ' 2>&1';
        runCmd($cmd);

        // Append IV to the end of the file
        $cmd = 'echo ' . $iv . ' | ' . $xxd . ' -r -p >> "' . $enc_file . '" 2>&1';
        runCmd($cmd);

        // HMAC the file using SHA-256 and append the result to end of the file
        if ($key_hmac !== null) {
            $cmd = 'cat "' . $enc_file . '" | openssl dgst -sha256 -mac hmac -macopt hexkey:' . $key_hmac . ' -binary >> "' . $enc_file . '" 2>&1';
            runCmd($cmd);
        }

        // Verify that the enrypted file can be read by PHP
        if (!is_file($enc_file)) {
            throw new \Exception(sprintf('File encryption failed because the encrypted file [%s] was not found after commands successfully ran. The error is unexpected so you may want to verify permissions for the web server or user running PHP and if the file was actually created.', $enc_file));
        }

        // Finishing creating file through command line
        return;
    }

    // Create file without Command Line (All OS's)
    // This function uses hex values for file commands so convert
    // from hex to binary strings on needed variables.
    $iv = hex2bin($iv);
    $key_enc = hex2bin($key_enc);
    if ($key_hmac !== null) {
        $key_hmac = hex2bin($key_hmac);
    }

    // Get file contents as a string (byte array)
    $plaintext = file_get_contents($file_path);

    // NOTE - IV would normally be generated with code similar to this:
    //$iv = \random_bytes(\openssl_cipher_iv_length('aes-256-cbc'));

    // If using PHP 5.3 then create add an OpenSSL Constant that wasn't defined until PHP 5.4.
    if (PHP_VERSION_ID < 50400 && !defined('OPENSSL_RAW_DATA')) {
        define('OPENSSL_RAW_DATA', 1);
    }

    // Encrypt using AES 256-Bit with CBC Mode and append IV to end of the string
    $ciphertext = \openssl_encrypt($plaintext, 'aes-256-cbc', $key_enc, OPENSSL_RAW_DATA, $iv);
    $ciphertext = $ciphertext . $iv;

    // HMAC the file using SHA-256 and append the result to end of cipher text
    if ($key_hmac !== null) {
        $hmac = \hash_hmac('sha256', $ciphertext, $key_hmac, true);
        $ciphertext = $ciphertext . $hmac;
    }

    // Output the encrypted file
    file_put_contents($enc_file, $ciphertext);
}

// Run a shell command and check the result
function runCmd($cmd, $expect_ouput = false)
{
    // Run the command saving the exit status and all output to an array.
    // If needed, uncomment debug lines.
    // echo $cmd;
    // echo '<br><br>';
    // exit();
	exec($cmd, $output, $exit_status);

	// If the return value/code from the program was 0 then
	// it ran successfully otherwise there was an error.
	// This applies to most Unix/Linux/Mac/Windows programs.

	$expected_count = ($expect_ouput ? 1 : 0);
	$error = null;

	if ($exit_status !== 0) {
		$error = sprintf('[%s] failed with an exit status other than 0.', $cmd);
	} elseif (count($output) !== $expected_count) {
		$error = sprintf('[%s] failed with unexpected output.', $cmd);
	}

	if ($error !== null) {
		switch ($exit_status) {
			case 127:
				$text = ' One of the command line executables used or a file path was not found. This can happen if the command doesn’t exist on the server or if the web server account user can’t see the command. To obtain info related to command paths and the web user call the function [%s->checkFileSetup()].';
				break;
			default:
				$error .= ' You may need to check read/write permissions on the directory or files being used.';
				break;
		}
		$error .= sprintf(' [exit status: %d] [output: %s]', $exit_status, implode(', ', $output));
		throw new \Exception($error);
	}

	// Return single output line or null
	return ($expect_ouput ? $output[0] : null);
}

// See comments in [Crypto->xxdPath()]
function xxdPath()
{
	if (PHP_OS === 'FreeBSD' &&
		strpos(getenv('PATH'), '/usr/local/bin') === false &&
		is_file('/usr/local/bin/xxd')
	) {
    	return '/usr/local/bin/xxd';
	} else {
    	return 'xxd';
	}
}

// Verify file encryption, this function gets called with different options and will
// create a plaintext file  encrypt it with either [encryptFile()] or [encrypt()] and
// decrypt it with either [decryptFile()] or [decrypt()]. This helps verify that
// [encryptFile() / decryptFile()] and [encrypt() / decrypt()] are compatible when
// using certain settings for [encrypt()] and [decrypt()]. Additionally the option
// [process_files_with_cmd_line] gets tested as both true and false in supported OS's.
function encryptAndDecryptFile($encrypt_with_file, $decrypt_with_file, $process_files_with_cmd_line = null) {
	// Create the Crypto Objects, since these Tests should succeed allow the client to see detailed
	// errors because it can help a developer determine why it is not working on the server.
    // In a secure production app end users should not see results of [displayCmdErrorDetail()].
    $file_crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
    $file_crypto->displayCmdErrorDetail(true);

    // Set cmd_line Option
    if ($process_files_with_cmd_line === null) {
        $file_crypto->processFilesWithCmdLine(true);
    } else {
        $file_crypto->processFilesWithCmdLine($process_files_with_cmd_line);
    }

    // Set required Encryption class properties  so that encrypt() is compatible
    // with decryptFile() and so encryptFile() is compatible with decrypt().
    if ($encrypt_with_file === false || $decrypt_with_file === false) {
        $crypto = new \FastSitePHP\Security\Crypto\Encryption();
        $crypto
            ->dataFormat('string-only')
            ->returnFormat('bytes');
    }

	// For Windows cmd line encryption is not supported
	$os = 'Other_OS';
	if (PHP_OS === 'WINNT') {
        $os = 'Windows';
        if (!($encrypt_with_file === false || $decrypt_with_file === false)) {
            $file_crypto->processFilesWithCmdLine(false);
        }
	}

	// Define file, contents, etc. A random file name is used
	// each time in case something fails.
    $rand = \bin2hex(\FastSitePHP\Security\Crypto\Random::bytes(6));
	$file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crypto_test_' . $rand;
	$enc_file = $file_path . '.enc';
	$output_file = $enc_file . '.decrypted';
	$contents = '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $content_size = strlen($contents);
	$expected_hash = '89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323';
	$functions_called = array();
    $error_msg = ' The error should never happen unless a code change breaks something.';

	// Delete files if they already exist (if so then likely the test previously failed)
	$files = array($file_path, $enc_file, $output_file);
	foreach ($files as $file) {
		if (is_file($file)) {
			unlink($file);
		}
	}

	// Two files will be created one using the default value of [encryptThenAuthenticate(true)]
	// and the other with it set to false. Define an array with the expected encrypted file size
	// based on the options.
	$padding_size = $content_size % 16;
	$iv_size = 16;
    $hash_size = 32;

	$file_sizes = array(
		$content_size + $padding_size + $iv_size + $hash_size,
		$content_size + $padding_size + $iv_size,
	);

	for ($test = 0; $test < 2; $test++) {
		// Set value to false fo 2nd file
		if ($test === 1) {
            $file_crypto->encryptThenAuthenticate(false);

            if ($encrypt_with_file === false || $decrypt_with_file === false) {
                $crypto->encryptThenAuthenticate(false);
            }
		}

		// Create a random key
        $key = $file_crypto->generateKey();

		// Create and verify plaintext file if using [encryptFile()].
		if ($encrypt_with_file) {
			file_put_contents($file_path, $contents);
			$hash_plain = \hash_file('sha256', $file_path);
			if (filesize($file_path) !== $content_size) {
				echo 'Failed to create plaintext file, file size is wrong.' . $error_msg;
				echo '<br>';
				var_dump($file_path);
				echo '<br>';
				var_dump(filesize($file_path));
				exit();
			} else if (!\hash_equals($expected_hash, $hash_plain)) {
				echo 'Failed to create plaintext file, hash did not match the expected value.' . $error_msg;
				echo '<br>';
				var_dump($file_path);
				echo '<br>';
				var_dump($expected_hash);
				echo '<br>';
				var_dump($hash_plain);
				exit();
			}
		}

		// Encrypt the file/string and verify the encrypted file
		if ($encrypt_with_file) {
            $file_crypto->encryptFile($file_path, $enc_file, $key);
            $class = new \ReflectionClass($file_crypto);
			$functions_called[] = $class->getShortName() . '->encryptFile()';
		} else {
            file_put_contents($enc_file, $crypto->encrypt($contents, $key));
            $class = new \ReflectionClass($crypto);
			$functions_called[] = $class->getShortName() . '->encrypt()';
		}
		$hash_enc = \hash_file('sha256', $enc_file);
		if (filesize($enc_file) !== $file_sizes[$test]) {
			echo 'Failed to encrypt file or data, file size is wrong.' . $error_msg;
			echo '<br>';
			var_dump($enc_file);
			echo '<br>';
			var_dump(filesize($enc_file));
			echo '<br>';
			var_dump($file_sizes[$test]);
			exit();
		} else if (\hash_equals($expected_hash, $hash_enc)) {
			echo 'Failed to encrypt file or data, hash matched the plaintext file or string.' . $error_msg;
			echo '<br>';
			var_dump($file_path);
			echo '<br>';
			var_dump($hash_plain);
			echo '<br>';
			var_dump($hash_enc);
			exit();
		}

		// Decrypt
		if ($decrypt_with_file) {
			$file_crypto->decryptFile($enc_file, $output_file, $key);
			$dec_size = filesize($output_file);
            $hash_dec = \hash_file('sha256', $output_file);
            $class = new \ReflectionClass($file_crypto);
			$functions_called[] = $class->getShortName() . '->decryptFile()';
		} else {
			$dec_content = $crypto->decrypt(file_get_contents($enc_file), $key);
			$dec_size = strlen($dec_content);
            $hash_dec = \hash('sha256', $dec_content);
            $class = new \ReflectionClass($crypto);
			$functions_called[] = $class->getShortName() . '->decrypt()';
		}
		if ($dec_size !== $content_size) {
			echo 'Failed to decrypt file or string, decrypted file/data size is wrong.' . $error_msg;
			echo '<br>';
			var_dump($hash_dec);
			echo '<br>';
			var_dump($dec_size);
			exit();
		} else if (!\hash_equals($expected_hash, $hash_dec)) {
			echo 'Failed to decrypt file, hash did not match the plaintext file or data.' . $error_msg;
			echo '<br>';
			var_dump($output_file);
			echo '<br>';
			var_dump($expected_hash);
			echo '<br>';
			var_dump($hash_dec);
			exit();
        }

		// Delete files created by this unit test
		$files = array($file_path, $enc_file, $output_file);
		foreach ($files as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}
	}

	// Return results as string with hash of decrypted data
	// and relevant file and function info
    $response = ($process_files_with_cmd_line === null ? '[' . $os . '], ' : '');
	$response .= '[' . $content_size . ']';
	$response .= ', [' . implode(',', $file_sizes) . ']';
	$response .= ', [' . ($file_crypto->processFilesWithCmdLine() ? 'true' : 'false') . ']';
	$response .= ', [' . implode(',', array_unique($functions_called)) . ']';
	$response .= ', [' . $hash_dec . ']';
	return $response;
}

// Decrypt a File using a known Key or Password and IV.
// Settings used here match Unit Tests from the Bash Script [encrypt.sh].
// However [encrypt.sh] only supports Encryption with Authentication (HMAC option).
function decryptKnownFile($use_password = false) {
	// Create the Crypto Object, since these Tests should succeed and
	// use known keys allow the client to see detailed errors because it
	// can help a developer determine why it is not working on the server.
	// In a secure production app end users should not see results of [displayCmdErrorDetail()].
	$crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
    $crypto->displayCmdErrorDetail(true);

    // If using PHP 5.3 or 5.4 then make one quick call to encrypt()
    // using a password key type which will result in the polyfill
    // function [hash_pbkdf2()] being created.
    if (PHP_VERSION_ID <= 50500) {
        $crypto2 = new \FastSitePHP\Security\Crypto\Encryption();
        $crypto2
            ->pbkdf2Iterations(1)
            ->keyType('password')
            ->pbkdf2Iterations(1)
            ->encrypt('test', 'password');
    }

	// Define file, contents, etc. A random file name is used
	// each time in case something fails.
	$rand = \bin2hex(\FastSitePHP\Security\Crypto\Random::bytes(6));
	$file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crypto_test_10mb_' . $rand;
	$enc_file = $file_path . '.enc';
	$output_file = $enc_file . '.decrypted';
	$expected_hash_plain = 'f1c9645dbc14efddc7d8a322685f26eb';

	// Define data sizes
	$one_megabyte = 1024 * 1024;
	$ten_megabytes = $one_megabyte * 10;
	$padding_size = 16;
	$iv_size = 16;
	$hash_size = 32;

	// Delete files if they already exist (if so then likely the test previously failed)
	$files = array($file_path, $enc_file, $output_file);
	foreach ($files as $file) {
		if (is_file($file)) {
			unlink($file);
		}
    }

    // Each of three random byte paramaters (Enc Key, HMAC Key, and IV)
    // are setup below to include a NULL Byte (char 0). Shell Scripts and
    // Bash do not allow Null Characters in strings so if the commands
    // were setup to use byte string variables then the test would fail.
    // Instead they are setup to pipe the output from command to command
    // which allows for null characters.
    //
    // For the Password 3 characters are included Space [ ], Single Quote ['],
    // and Double Quote ["]. The helps verify special characters that may
    // appear when running using Command Line.

	// Specify secret keys and IV used for encryption and decryption.
	// Since this is a published "secret key" DO NOT copy it and use it
	// in your applications/site. To create secret keys for your application
	// see the [generateKey()] function from the [Crypto] class.
    $key_enc  = 'b2e8ff4746c1006adafeb42235554363acf22391941b86b22a7b28c8a591ea4f';
    $key_hmac = '6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab';
    $password = "Password \"' 123";

	// This test uses a known hard-coded IV for encryption.
	// In real applications the IV should always be secure, random,
	// and generated each time data is encrypted.
	$iv = '0ee221ef9e00dfa69efb3b1112bfbb2f';

	// Two files will be created one using the default value of [encryptThenAuthenticate(true)]
	// and the other with it set to false. Define an array with the expected encrypted file size
	// based on the options.
	$enc_files = array(
		array(
			'size' => ($ten_megabytes + $padding_size + $iv_size + $hash_size),
			'expected_hash' => ($use_password ? '8908ec149e2ae3fa917e75c3f622a29f' : '371b4aad41c87bc27bb6cdd58c2c7c48'),
		),
		array(
			'size' => ($ten_megabytes + $padding_size + $iv_size),
			'expected_hash' => ($use_password ? '37afbf1cb5a459e45e4de30ef467fbc1' : 'd257ac3640eb35d82591facd8c7ddb25'),
		),
    );

	// For Windows cmd line encryption is not supported
	$os = 'Other_OS';
	if (PHP_OS === 'WINNT') {
        $os = 'Windows';
    } else {
		$crypto->processFilesWithCmdLine(true);
    }

    // Key (Default) or Password?
    if ($use_password) {
        $crypto->keyType('password');
    }

	$enc_hashes = array();
    $enc_file_sizes = array();

	for ($test = 0; $test < 2; $test++) {
		// Set value to false for 2nd file
		if ($test === 1) {
			$crypto->encryptThenAuthenticate(false);
        }

        // Generate Key from Password?
        if ($use_password) {
            if ($test === 0) {
                $keys = \hash_pbkdf2('sha512', $password, \hex2bin($iv), 200000, 512, true);
                $key_enc = bin2hex(substr($keys, 0, 32));
                $key_hmac = bin2hex(substr($keys, 32, 32));
            } else {
                $keys = \hash_pbkdf2('sha512', $password, \hex2bin($iv), 200000, 256, true);
                $key_enc = bin2hex(substr($keys, 0, 32));
                $key_hmac = null;
            }
        }

		// Create a 10 Megabyte Empty (null/0-ASCII) File
		$fp = fopen($file_path, 'w');
		for ($n = 0; $n < 10; $n++) {
			fwrite($fp, str_repeat(chr(0), $one_megabyte));
		}
		fclose($fp);

		// Check the file using [md5]; other file tests on this page use stronger hashing algorithms
		// and MD5 is easy to check from command line so it is acceptable to use here as this is a
		// known file and use of multiple hashing algorithms within the unit test functions is desired.
		$hash_plain = md5_file($file_path);
		if (filesize($file_path) !== $ten_megabytes) {
			echo 'Failed to create file, file size is wrong. The error should never happen unless a code change breaks something.';
			echo '<br>';
			var_dump($file_path);
			echo '<br>';
			var_dump(filesize($file_path));
			exit();
		} else if (!\hash_equals($expected_hash_plain, $hash_plain)) {
			echo 'Failed to create file, hash did not match the expected value. The error should never happen unless a code change breaks something.';
			echo '<br>';
			var_dump($file_path);
			echo '<br>';
			var_dump($expected_hash_plain);
			echo '<br>';
			var_dump($hash_plain);
			exit();
        }

		// Encrypt the File
		createFile($file_path, $crypto->processFilesWithCmdLine(), $enc_file, $key_enc, ($test === 0 ? $key_hmac : null), $iv);
		$hash_enc = md5_file($enc_file);
		$file_size = filesize($enc_file);
		$expected_hash = $enc_files[$test]['expected_hash'];
		$expected_size = $enc_files[$test]['size'];

		if ($file_size !== $expected_size) {
			echo 'Failed to encrypt file, file size is wrong. The error should never happen unless a code change breaks something.';
			echo '<br><strong>test:</strong> ';
			var_dump($test);
			echo '<br><strong>enc_file:</strong> ';
			var_dump($enc_file);
			echo '<br><strong>file_size:</strong> ';
			var_dump($file_size);
			echo '<br><strong>expected_size:</strong> ';
			var_dump($expected_size);
			exit();
		} else if (!\hash_equals($expected_hash, $hash_enc)) {
			echo 'Failed to encrypt file, hash did not match the expected value. The error should never happen unless a code change breaks something.';
			echo '<br><strong>test:</strong> ';
			var_dump($test);
			echo '<br><strong>enc_file:</strong> ';
			var_dump($enc_file);
			echo '<br><strong>hash_enc:</strong> ';
			var_dump($hash_enc);
			echo '<br><strong>expected_hash_enc:</strong> ';
			var_dump($expected_hash);
			exit();
		}

		$enc_hashes[] = $hash_enc;
        $enc_file_sizes[] = $file_size;

        // Decrypted
        if ($use_password) {
            $crypto->decryptFile($enc_file, $output_file, $password);
        } else {
            $key = $key_enc . ($test === 0 ? $key_hmac : '');
            $crypto->decryptFile($enc_file, $output_file, $key);
        }
		$hash_dec = md5_file($output_file);
		if (filesize($output_file) !== $ten_megabytes) {
			echo 'Failed to decrypt file, file size is wrong. The error should never happen unless a code change breaks something.';
			echo '<br>';
			var_dump($output_file);
			echo '<br>';
			var_dump(filesize($output_file));
			exit();
		} else if (!\hash_equals($expected_hash_plain, $hash_dec)) {
			echo 'Failed to decrypt file, hash did not match the expected value. The error should never happen unless a code change breaks something.';
			echo '<br>';
			var_dump($output_file);
			echo '<br>';
			var_dump($expected_hash_plain);
			echo '<br>';
			var_dump($hash_dec);
			exit();
		}

		// Delete files created by this unit test
		$files = array($file_path, $enc_file, $output_file);
		foreach ($files as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}
	}

	// Return Results
    $cmd_line = ($crypto->processFilesWithCmdLine() ? 'true' : 'false');
	return '[' . $os . '], [' . $cmd_line . '], [' . $hash_plain . '], [' . implode(',', $enc_file_sizes) . '], [' . implode(',', $enc_hashes) . '], [' . $hash_dec . ']';
}

// -----------------------------------------------------------
// Global Events
// -----------------------------------------------------------

// For all routes add text '[/{url}], ' to the start of the return value. This helps make
// it clearer which route is being returned and entered from  the client JS file.
$app->beforeSend(function($content) use ($app) {
	if ($app->header('Content-Type') !== 'application/json') {
		return '[' . $app->requestedPath() . '], ' . $content;
	} else {
		return $content;
	}
});

// -----------------------------------------------------------
// Define Test Routes
// -----------------------------------------------------------

$app->get('/default-settings-encryption', function() use ($app) {
    // Specify Content-Type for a custom [beforeSend] event on this page
    $app->header('Content-Type', 'application/json');
    if (PHP_VERSION_ID >= 50400) {
        $app->json_options = JSON_PRETTY_PRINT;
    }

    // Return default value of all getter/setter prop functions
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();
    return array(
        // General Class Info
        'get_class' => get_class($crypto),
        'get_parent_class' => get_parent_class($crypto),
        'class_implements' => array_keys(class_implements($crypto)),

        // Defined in [AbstractCrypto]
        'exceptionOnError' => $crypto->exceptionOnError(),
        'allowNull' => $crypto->allowNull(),
        'hashingAlgorithm' => $crypto->hashingAlgorithm(),
        'encryptThenAuthenticate' => $crypto->encryptThenAuthenticate(),
        'keyType' => $crypto->keyType(),
        'pbkdf2Algorithm' => $crypto->pbkdf2Algorithm(),
        'pbkdf2Iterations' => $crypto->pbkdf2Iterations(),
        'keySizeHmac' => $crypto->keySizeHmac(), // Getter only

        // Defined in [Encryption]
        'encryptionAlgorithm' => $crypto->encryptionAlgorithm(),
        'returnFormat' => $crypto->returnFormat(),
        'dataFormat' => $crypto->dataFormat(),
        'keySizeEnc' => $crypto->keySizeEnc(),
        'isAEAD_Mode' => $crypto->isAEAD_Mode(), // Getter only
    );
});

$app->get('/default-settings-file-encryption', function() use ($app) {
    // Specify Content-Type for a custom [beforeSend] event on this page
    $app->header('Content-Type', 'application/json');
    if (PHP_VERSION_ID >= 50400) {
        $app->json_options = JSON_PRETTY_PRINT;
    }

    // Return default value of all getter/setter prop functions
    $crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
    return array(
        // General Class Info
        'get_class' => get_class($crypto),
        'get_parent_class' => get_parent_class($crypto),
        'class_implements' => array_keys(class_implements($crypto)),

        // Defined in [AbstractCrypto]
        'encryptThenAuthenticate' => $crypto->encryptThenAuthenticate(),
        'keyType' => $crypto->keyType(),
        'pbkdf2Algorithm' => $crypto->pbkdf2Algorithm(),
        'pbkdf2Iterations' => $crypto->pbkdf2Iterations(),

        // Defined in [FileEncryption]
        'displayCmdErrorDetail' => $crypto->displayCmdErrorDetail(),
        'processFilesWithCmdLine' => $crypto->processFilesWithCmdLine(),
    );
});

$app->get('/default-settings-signed-data', function() use ($app) {
    // Specify Content-Type for a custom [beforeSend] event on this page
    $app->header('Content-Type', 'application/json');
    if (PHP_VERSION_ID >= 50400) {
        $app->json_options = JSON_PRETTY_PRINT;
    }

    // Return default value of all getter/setter prop functions
    $crypto = new \FastSitePHP\Security\Crypto\SignedData();
    return array(
        // General Class Info
        'get_class' => get_class($crypto),
        'get_parent_class' => get_parent_class($crypto),
        'class_implements' => array_keys(class_implements($crypto)),

        // Defined in [AbstractCrypto]
        'exceptionOnError' => $crypto->exceptionOnError(),
        'allowNull' => $crypto->allowNull(),
        'hashingAlgorithm' => $crypto->hashingAlgorithm(),
        'keySizeHmac' => $crypto->keySizeHmac(), // Getter only
    );
});

// Verify that any Compatibility functions defined by the Crypto
// class match functionality of the native PHP Versions. Newer
// versions of PHP such as PHP 7 will include all of these
// functions by default however older versions will not. Regardless
// of the  version of PHP used the functions calls should always
// have the same result and have the same error messages if called
// with invalid parameters. Created functions and constants:
//  OPENSSL_RAW_DATA
//  hex2bin()
//  bin2hex()
//  hash_equals()
//  random_bytes()
//	hash_pbkdf2() ** Verified in another route
$app->get('/compatibility-functions', function() {
    // Create a Crypto object as this will create functions
    // hex2bin() and bin2hex() if using PHP 5.3 and will
    // create hash_equals() when using PHP Versions below 5.6.
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();

    // Define tests that run without error
    $tests = array(
        // --------
        // bin2hex()
        // --------
        array(
            'function' => 'bin2hex',
            'data' => 'FastSitePHP',
            'expected' => '4661737453697465504850',
        ),
        array(
            'function' => 'bin2hex',
            'data' => 'abcABC',
            'expected' => '616263414243',
        ),
        array(
            'function' => 'bin2hex',
            'data' => 12345,
            'expected' => '3132333435',
        ),
        array(
            'function' => 'bin2hex',
            'data' => true,
            'expected' => '31',
        ),
        array(
            'function' => 'bin2hex',
            'data' => '1',
            'expected' => '31',
        ),
        array(
            'function' => 'bin2hex',
            'data' => '',
            'expected' => '',
        ),
        array(
            'function' => 'bin2hex',
            'data' => null,
            'expected' => '',
        ),
        // 'Hello' in Mandarin Chinese
        array(
            'function' => 'bin2hex',
            'data' => '你好',
            'expected' => 'e4bda0e5a5bd',
        ),
        // 'Hello' in Korean
        array(
            'function' => 'bin2hex',
            'data' => '여보세요',
            'expected' => 'ec97acebb3b4ec84b8ec9a94',
        ),
        // --------
        // hex2bin()
        // --------
        array(
            'function' => 'hex2bin',
            'data' => '4661737453697465504850',
            'expected' => 'FastSitePHP',
        ),
        array(
            'function' => 'hex2bin',
            'data' => '616263414243',
            'expected' => 'abcABC',
        ),
        array(
            'function' => 'hex2bin',
            'data' => 3132333435,
            'expected' => '12345',
        ),
        array(
            'function' => 'hex2bin',
            'data' => 31,
            'expected' => '1',
        ),
        array(
            'function' => 'hex2bin',
            'data' => '',
            'expected' => '',
        ),
        array(
            'function' => 'hex2bin',
            'data' => null,
            'expected' => '',
        ),
        // 'Hello' in Mandarin Chinese
        array(
            'function' => 'hex2bin',
            'data' => 'e4bda0e5a5bd',
            'expected' => '你好',
        ),
        // 'Hello' in Korean
        array(
            'function' => 'hex2bin',
            'data' => 'ec97acebb3b4ec84b8ec9a94',
            'expected' => '여보세요',
        ),
        // -----------
        // hash_equals
        // -----------
        array(
            'function' => 'hash_equals',
            'parameters' => array('12345', '12345'),
            'expected' => true,
        ),
        array(
            'function' => 'hash_equals',
            'parameters' => array('12345', '123456'),
            'expected' => false,
        ),
        array(
            'function' => 'hash_equals',
            'parameters' => array('12345', '12344'),
            'expected' => false,
        ),
        array(
            'function' => 'hash_equals',
            'parameters' => array('b4bf5ff6cf82ea1a362d1460616e9654864c87e757a660f1cdc0bdb9cb25ee54', 'b4bf5ff6cf82ea1a362d1460616e9654864c87e757a660f1cdc0bdb9cb25ee54'),
            'expected' => true,
        ),
        array(
            'function' => 'hash_equals',
            'parameters' => array('b4bf5ff6cf82ea1a362d1460616e9654864c87e757a660f1cdc0bdb9cb25ee54', 'b4bf5ff6cf82ea1a362d1460616e9654864c87e757a660f1cdc0bdb9cb25ee55'),
            'expected' => false,
        ),
        // 'Hello' in Mandarin Chinese
        array(
            'function' => 'hash_equals',
            'parameters' => array('你好', '你好'),
            'expected' => true,
        ),
        // 'Hello' in Mandarin Chinese with Korean
        array(
            'function' => 'hash_equals',
            'parameters' => array('你好', '여보세요'),
            'expected' => false,
        ),
    );

    // These tests verify that the error messages defined in
    // the compatibility functions match the same errors defined
    // from the native PHP functions.
    $error_tests = array(
        // --------
        // bin2hex()
        // --------
        array(
            'function' => 'bin2hex',
            'data' => array(),
            'expected' => 'bin2hex() expects parameter 1 to be string, array given',
        ),
        // --------
        // hex2bin()
        // --------
        array(
            'function' => 'hex2bin',
            'data' => array(),
            'expected' => 'hex2bin() expects parameter 1 to be string, array given',
        ),
        array(
            'function' => 'hex2bin',
            'data' => '123',
            'expected' => 'hex2bin(): Hexadecimal input string must have an even length',
        ),
        array(
            'function' => 'hex2bin',
            'data' => 'zz',
            'expected' => 'hex2bin(): Input string must be hexadecimal string',
            // This message wasn't defined till PHP 5.5 and in 5.4 it returns false
            'allow_false' => 50500,
        ),
        // -----------
        // hash_equals
        // -----------
        // NOTE - PHP 7.3 uses [int and bool] instead of [integer and boolean]
        array(
            'function' => 'hash_equals',
            'parameters' => array(12345, '12345'),
            'expected' => array(
                'hash_equals(): Expected known_string to be a string, integer given',
                'hash_equals(): Expected known_string to be a string, int given',
            ),
        ),
        array(
            'function' => 'hash_equals',
            'parameters' => array('12345', true),
            'expected' => array(
                'hash_equals(): Expected user_string to be a string, boolean given',
                'hash_equals(): Expected user_string to be a string, bool given',
            ),
        ),
    );

    // Run all Tests
    $results = runCompatabilityTests($tests, $error_tests);
    $tests_count = $results[0];
    $error_count = $results[1];
    $all_results_string = $results[2];

    // Call generateKey() once so that random_bytes() ends up
    // being defined if using any version of PHP 5. The
    // [random_compat] Code Project is used which is widely used 31
    // and well tested outside of PHP so only confirmation of
    // the function working is being tested and not error messages, etc.
    if (PHP_VERSION_ID < 70000) {
        $bytes = $crypto->generateKey();
    }
    $byte = ord(\random_bytes(1));
    if (!(is_int($byte) && $byte >= 0 && $byte <= 255)) {
        echo '<strong>Error:</strong> Failed to generated a random byte using random_bytes()';
        exit();
    }
    $tests_count++;
    $all_results_string .= '\random_bytes()';

    // OPENSSL_RAW_DATA will be defined by the Crypto Object if using PHP 5.3
    if (!defined('OPENSSL_RAW_DATA')) {
        echo '<strong>Error:</strong> OPENSSL_RAW_DATA is not defined';
        exit();
    }
    if (OPENSSL_RAW_DATA !== 1) {
        echo '<strong>Error:</strong> OPENSSL_RAW_DATA should be defined as 1';
        exit();
    }
    $tests_count++;
    $all_results_string .= 'OPENSSL_RAW_DATA';

    // Result
    return sprintf('[Tests: %d], [Error Tests: %d], [Len: %d], [sha256: %s]', $tests_count, $error_count, len($all_results_string), hash('sha256', $all_results_string));
});

// Verify the [hash_pbkdf2()] Compatibility functions defined by the Crypto Classes.
// See route '/compatibility-functions' for other functions.
$app->get('/compatibility-functions-pbkdf2', function() {
    // Create a Crypto object
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();

    // If using PHP 5.3 or 5.4 then make one quick call to encrypt()
    // using a password key type which will result in the polyfill
    // function [hash_pbkdf2()] being created.
    if (PHP_VERSION_ID <= 50500) {
        $crypto
            ->pbkdf2Iterations(1)
            ->keyType('password')
            ->pbkdf2Iterations(1)
            ->encrypt('test', 'password');
    }

    // Define tests that run without error
    $tests = array(
        // ------------------------------------------
        // hash_pbkdf2
        // All tests run with both [true/false]
        // for [$raw_output]
        //
        // Test Vectors are from RFC 6070:
        // https://www.ietf.org/rfc/rfc6070.txt
        // ------------------------------------------
        // 1 Iteration
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 1, 20, true),
            'expected' => hex2bin(str_replace(' ', '', '0c 60 c8 0f 96 1f 0e 71 f3 a9 b5 24 af 60 12 06 2f e0 37 a6')),
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 1, 40),
            'expected' => str_replace(' ', '', '0c 60 c8 0f 96 1f 0e 71 f3 a9 b5 24 af 60 12 06 2f e0 37 a6'),
        ),
        // 2 Iterations
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 2, 20, true),
            'expected' => hex2bin(str_replace(' ', '', 'ea 6c 01 4d c7 2d 6f 8c cd 1e d9 2a ce 1d 41 f0 d8 de 89 57')),
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 2, 40),
            'expected' => str_replace(' ', '', 'ea 6c 01 4d c7 2d 6f 8c cd 1e d9 2a ce 1d 41 f0 d8 de 89 57'),
        ),
        // 4096 Iterations
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 4096, 20, true),
            'expected' => hex2bin(str_replace(' ', '', '4b 00 79 01 b7 65 48 9a be ad 49 d9 26 f7 21 d0 65 a4 29 c1')),
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 4096, 40),
            'expected' => str_replace(' ', '', '4b 00 79 01 b7 65 48 9a be ad 49 d9 26 f7 21 d0 65 a4 29 c1'),
        ),
        // 16,777,216 Iterations
        // Uncomment to test if desired as this takes about 20-30 seconds for each call.
        // This was tested with PHP 5.3 and PHP 5.4 after the hash_pbkdf2() polyfill was developed.
        // If running also uncomment the line 'set_time_limit(0);' near the top of this file.
        /*
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 16777216, 20, true),
            'expected' => hex2bin(str_replace(' ', '', 'ee fe 3d 61 cd 4d a4 e4 e9 94 5b 3d 6b a2 15 8c 26 34 e9 84')),
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 16777216, 40),
            'expected' => str_replace(' ', '', 'ee fe 3d 61 cd 4d a4 e4 e9 94 5b 3d 6b a2 15 8c 26 34 e9 84'),
        ),
        */
        // 4096 Iterations with different password/salt and key size
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'passwordPASSWORDpassword', 'saltSALTsaltSALTsaltSALTsaltSALTsalt', 4096, 25, true),
            'expected' => hex2bin(str_replace(' ', '', '3d 2e ec 4f e4 1c 84 9b 80 c8 d8 36 62 c0 e4 4a 8b 29 1a 96 4c f2 f0 70 38')),
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'passwordPASSWORDpassword', 'saltSALTsaltSALTsaltSALTsaltSALTsalt', 4096, 50),
            'expected' => str_replace(' ', '', '3d 2e ec 4f e4 1c 84 9b 80 c8 d8 36 62 c0 e4 4a 8b 29 1a 96 4c f2 f0 70 38'),
        ),
        // 4096 Iterations with null characters
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', "pass\0word", "sa\0lt", 4096, 16, true),
            'expected' => hex2bin(str_replace(' ', '', '56 fa 6a a7 55 48 09 9d cc 37 d7 f0 34 25 e0 c3')),
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', "pass\0word", "sa\0lt", 4096, 32),
            'expected' => str_replace(' ', '', '56 fa 6a a7 55 48 09 9d cc 37 d7 f0 34 25 e0 c3'),
        ),
        // ----------------------------------
        // hash_pbkdf2
        // PHP allows for length parameter
        // to be 0 which returns all bytes
        // ----------------------------------
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha256', 'password', 'salt', 1, 0, true),
            'expected' => hex2bin('120fb6cffcf8b32c43e7225256c4f837a86548c92ccc35480805987cb70be17b'),
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha256', 'password', 'salt', 1),
            'expected' => '120fb6cffcf8b32c43e7225256c4f837a86548c92ccc35480805987cb70be17b',
        ),
    );

    // These tests verify that the error messages defined in
    // the compatibility functions match the same errors defined
    // from the native PHP functions.
    $error_tests = array(
        // -----------
        // hash_pbkdf2
        // -----------
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('test', 'password', 'salt', 1, 20, false),
            'expected' => 'hash_pbkdf2(): Unknown hashing algorithm: test',
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 0, 20, false),
            'expected' => 'hash_pbkdf2(): Iterations must be a positive integer: 0',
        ),
        array(
            'function' => 'hash_pbkdf2',
            'parameters' => array('sha1', 'password', 'salt', 1, -1, false),
            'expected' => 'hash_pbkdf2(): Length must be greater than or equal to 0: -1',
        ),
    );

    // Run all Tests
    $results = runCompatabilityTests($tests, $error_tests);
    $tests_count = $results[0];
    $error_count = $results[1];
    $all_results_string = $results[2];

    // Result
    return sprintf('[Tests: %d], [Error Tests: %d], [Len: %d], [sha256: %s]', $tests_count, $error_count, len($all_results_string), hash('sha256', $all_results_string));
});

// Check File Setup
// The function [checkFileSetup()] can be used to obtain relevant information for
// debugging errors with command line file encryption. This command checks all
// the properties but doesn't provide details to the client.
$app->get('/check-file-setup', function() {
	// Skip this Test if Windows
	if (PHP_OS === 'WINNT') {
		return 'Test Skipped, Running on Windows';
	}

	// Expected Keys and Command Paths
	$expected_keys = array('valid', 'whoami', 'path', 'getenforce', 'commands');
	$expected_cmds = array('openssl', 'echo', 'cat', 'cp', 'tail', 'rm');
	if (PHP_OS === 'Darwin') { // Mac
		$expected_cmds[] = 'ruby';
	} else {
		$expected_cmds[] = 'truncate';
		$expected_cmds[] = 'stat';
	}
	$expected_cmds[] = 'xxd';

    // Run [checkFileSetup()]
    $crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
    $file_setup = $crypto->checkFileSetup();
    
    // Uncomment for manual testing on the server to see the 
    // actual values for the server. The Test URL looks like this:
    //     http://localhost:3000/tests/test-security-crypto.php/check-file-setup
    //
    // header('Content-Type: text/plain');
    // var_dump($file_setup);
    // exit();    

	// Validate all properties
	$tests_count = 0;

	// Bascic check on properties
	if ($expected_keys !== array_keys($file_setup)) {
		return 'Failed at expected keys check [Expected: ' . implode(', ', $expected_keys) . '], [Found: ' . implode(', ', array_keys($file_setup)) . ']';
	} elseif ($expected_cmds !== array_keys($file_setup['commands'])) {
		return 'Failed at expected commands check [Expected: ' . implode(', ', $expected_cmds) . '], [Found: ' . implode(', ', array_keys($file_setup['commands'])) . ']';
	} elseif ($file_setup['valid'] !== true) {
		return 'Failed because setup is not valid';
	}
	$tests_count += 3;

	// Check properties for expected data types
	$properties = '{';
	$properties .= 'valid:' . gettype($file_setup['valid']);
	$properties .= ', whoami:' . gettype($file_setup['whoami']);
	$properties .= ', path:' . gettype($file_setup['path']);
	$type = gettype($file_setup['getenforce']); // Simply check if string or null
	$properties .= ', getenforce:' . ($type === 'string' || $type === 'NULL' ? 'string_or_null' : 'error');
	$properties .= ', commands:' . gettype($file_setup['commands']);
	$properties .= '[';
	foreach ($expected_cmds as $key) {
		$properties .= $key . ':' . gettype($file_setup['commands'][$key]) . ', ';
	}
	$properties .= ']}';

	// Results
	return sprintf('[Tests: %s], %s', $tests_count, $properties);
});

// Validate that [Base64Url::encode()] and [Base64Url::decode()] defined
// in the Crypto Object are properly working.
$app->get('/validate-base64url-encoding', function() {
    // Build a String of all ASCII Characters
    $data = '';
    for ($n = 0; $n <= 255; $n++) {
        $data .= chr($n);
    }

    $tests = array(
	    // Test with string of all ASCII Characters
        array(
            'data' => $data,
            'dataInHex' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f202122232425262728292a2b2c2d2e2f303132333435363738393a3b3c3d3e3f404142434445464748494a4b4c4d4e4f505152535455565758595a5b5c5d5e5f606162636465666768696a6b6c6d6e6f707172737475767778797a7b7c7d7e7f808182838485868788898a8b8c8d8e8f909192939495969798999a9b9c9d9e9fa0a1a2a3a4a5a6a7a8a9aaabacadaeafb0b1b2b3b4b5b6b7b8b9babbbcbdbebfc0c1c2c3c4c5c6c7c8c9cacbcccdcecfd0d1d2d3d4d5d6d7d8d9dadbdcdddedfe0e1e2e3e4e5e6e7e8e9eaebecedeeeff0f1f2f3f4f5f6f7f8f9fafbfcfdfeff',
            'base64'    => 'AAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8gISIjJCUmJygpKissLS4vMDEyMzQ1Njc4OTo7PD0+P0BBQkNERUZHSElKS0xNTk9QUVJTVFVWV1hZWltcXV5fYGFiY2RlZmdoaWprbG1ub3BxcnN0dXZ3eHl6e3x9fn+AgYKDhIWGh4iJiouMjY6PkJGSk5SVlpeYmZqbnJ2en6ChoqOkpaanqKmqq6ytrq+wsbKztLW2t7i5uru8vb6/wMHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2Nna29zd3t/g4eLj5OXm5+jp6uvs7e7v8PHy8/T19vf4+fr7/P3+/w==',
            'base64url' => 'AAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8gISIjJCUmJygpKissLS4vMDEyMzQ1Njc4OTo7PD0-P0BBQkNERUZHSElKS0xNTk9QUVJTVFVWV1hZWltcXV5fYGFiY2RlZmdoaWprbG1ub3BxcnN0dXZ3eHl6e3x9fn-AgYKDhIWGh4iJiouMjY6PkJGSk5SVlpeYmZqbnJ2en6ChoqOkpaanqKmqq6ytrq-wsbKztLW2t7i5uru8vb6_wMHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2Nna29zd3t_g4eLj5OXm5-jp6uvs7e7v8PHy8_T19vf4-fr7_P3-_w',
        ),
        // Padding with two '=' characters
        array(
            'data' => '0',
            'base64' => 'MA==',
            'base64url' => 'MA',
        ),
        // Padding with one char
        array(
            'data' => '01',
            'base64' => 'MDE=',
            'base64url' => 'MDE',
        ),
        // No Padding
        array(
            'data' => '012',
            'base64' => 'MDEy',
            'base64url' => 'MDEy',
        ),
        // Keep testing padding with '0...9'
        array(
            'data' => '0123',
            'base64' => 'MDEyMw==',
            'base64url' => 'MDEyMw',
        ),
        array(
            'data' => '01234',
            'base64' => 'MDEyMzQ=',
            'base64url' => 'MDEyMzQ',
        ),
        array(
            'data' => '012345',
            'base64' => 'MDEyMzQ1',
            'base64url' => 'MDEyMzQ1',
        ),
        array(
            'data' => '0123456',
            'base64' => 'MDEyMzQ1Ng==',
            'base64url' => 'MDEyMzQ1Ng',
        ),
        array(
            'data' => '01234567',
            'base64' => 'MDEyMzQ1Njc=',
            'base64url' => 'MDEyMzQ1Njc',
        ),
        array(
            'data' => '012345678',
            'base64' => 'MDEyMzQ1Njc4',
            'base64url' => 'MDEyMzQ1Njc4',
        ),
        array(
            'data' => '0123456789',
            'base64' => 'MDEyMzQ1Njc4OQ==',
            'base64url' => 'MDEyMzQ1Njc4OQ',
        ),
        // Null passed to both [base64_encode()] and [Base64Url::encode()]
        // returns an empty string.
        array(
            'data' => null,
            'base64' => '',
            'base64url' => '',
        ),
        // Empty String
        array(
            'data' => '',
            'base64' => '',
            'base64url' => '',
        ),
        // Test with alternative characters:
        //	Standard: ['+', '/']
        //	URL		: ['-', '_']
        array(
            'data' => chr(105) . chr(175) . chr(191),
            'base64' => 'aa+/',
            'base64url' => 'aa-_',
        ),
    );

    // Run Tests
    $tests_count = 0;
    $statement_count = 0;
    $all_encoded_values = '';
    foreach ($tests as $test) {
        // Encode
        $base64 = base64_encode($test['data']);
        $base64url = \FastSitePHP\Encoding\Base64Url::encode($test['data']);

        // Decode
        $decoded_base64 = base64_decode($base64, true);
        $decoded_base64url = \FastSitePHP\Encoding\Base64Url::decode($base64url);

        // Do the values match?
        $is_valid = true;
        $failed_reason = null;

        // Compare to Known Hex String
        if (isset($test['dataInHex'])) {
            $is_valid = (bin2hex($test['data']) === $test['dataInHex']);
            $all_encoded_values .= $test['dataInHex'];
            $statement_count++;
            if (!$is_valid) {
	            $failed_reason = 'dataInHex';
            }
        }

        // base64_encode
        if ($is_valid) {
            $is_valid = ($base64 === $test['base64']);
            $all_encoded_values .= $base64;
            $statement_count++;
            if (!$is_valid) {
	            $failed_reason = 'base64_encode';
            }
        }

        // base64_decode
        if ($is_valid) {
            $is_valid = ($decoded_base64 === $test['data'] || ($decoded_base64 === '' && $test['data'] === null));
            $all_encoded_values .= $decoded_base64;
            $statement_count++;
            if (!$is_valid) {
	            $failed_reason = 'base64_decode';
            }
        }

        // Base64Url::encode
        if ($is_valid) {
            $is_valid = ($base64url === $test['base64url']);
            $all_encoded_values .= $base64url;
            $statement_count++;
            if (!$is_valid) {
	            $failed_reason = 'Base64Url::encode';
            }
        }

        // Base64Url::decode
        if ($is_valid) {
            $is_valid = ($decoded_base64url === $test['data'] || ($decoded_base64 === '' && $test['data'] === null));
            $all_encoded_values .= $decoded_base64url;
            $statement_count++;
            if (!$is_valid) {
	            $failed_reason = 'Base64Url::decode';
            }
        }

        // Special case something to string for null or empty values
        if ($test['data'] === null) {
	        $all_encoded_values .= 'null';
        } elseif ($test['data'] === '') {
	        $all_encoded_values .= 'empty';
        }

        // All checks passed?
        if (!$is_valid) {
            echo sprintf('Test %d failed at Statement %d', $tests_count, $statement_count);
            echo '<br>';
            var_dump($test);
            echo '<br>';
            var_dump($decoded_base64url);
            echo '<br>';
            echo $failed_reason;
            exit();
        }

        // Keep count of passed tests
        $tests_count++;
    }

    // Error Test
    $tests_count++;

    // base64_decode() returns FALSE in strict mode.
    // Base64Url::decode() use the same behavior.
    $data = '!@#$%';
    if (base64_decode($data, true) !== false) {
        echo 'Error Test for base64_decode() should have returned false';
        exit();
    }
    $all_encoded_values .= $data;
    $statement_count++;

    if (\FastSitePHP\Encoding\Base64Url::decode($data) !== false) {
        echo 'Error Test for Base64Url::decode() should have returned false';
        exit();
    }
    $all_encoded_values .= $data;
    $statement_count++;

    // Exception Test using incorrect type with [Base64Url::decode]
    $exception = '';
    try {
	    $value = \FastSitePHP\Encoding\Base64Url::decode(array());
        echo 'Exception Test for Base64Url::decode() should have thrown an Exception';
        exit();
	} catch (\Exception $e) {
		$exception .= $e->getMessage();
	    $all_encoded_values .= $exception;
	    $statement_count++;
	}

    // Return Result
    return sprintf('[Tests: %s], [Statements: %s], [Len: %d], [sha256: %s], [Exception: %s]', $tests_count, $statement_count, len($all_encoded_values), hash('sha256', $all_encoded_values), $exception);
});

// Test Encryption and Decryption with Advanced Encryption Standard (AES)
// using CBC Mode (Cipher Block Chaining) with a 256 Bit Key Size.
// This is the default mode of encryption and expected to work on all computers.
$app->get('/encrypt-and-decrypt-aes-256-cbc', function() use ($app) {
    return encryptAndDecrypt('aes-256-cbc', $app);
});

// Test Encryption and Decryption with Advanced Encryption Standard (AES)
// using CTR Mode (Counter) with a 256 Bit Key Size.
$app->get('/encrypt-and-decrypt-aes-256-ctr', function() {
    // Skip this test unless from PHP 5.5 or greater. This does not impact whether
    // FastSitePHP and the Crypto Object will work on a server but rather that
    // this specific block mode will not be available.
    if (PHP_VERSION_ID < 50500) {
        return 'Test Skipped, PHP Version earlier than 5.5';
    }

    // Run Tests
    return encryptAndDecrypt('aes-256-ctr');
});

// Test Encryption and Decryption with Advanced Encryption Standard (AES)
// using GCM Mode (Galois/Counter Mode) with a 256 Bit Key Size.
$app->get('/encrypt-and-decrypt-aes-256-gcm', function() {
    // Skip this test unless from PHP 7.1 or greater.
    if (PHP_VERSION_ID < 70100) {
        return 'Test Skipped, PHP Version earlier than 7.1';
    }

    // Run Tests
    return encryptAndDecrypt('aes-256-gcm');
});

// Decrypt known encrypted values using 'aes-256-cbc' and 'aes-128-cbc' encryption modes.
// This route is expected to work on all versions of PHP 5.3+ and helps verify that encryption
// and decryption of different data types is working with the current server and framework install.
// The default encryption algorithm used is AES (Rigndeal) with a 256 bit key using CBC mode 'aes-256-cbc'.
$app->get('/decrypt-known-values-aes-cbc', function() use ($app) {
    // Define Keys
    $key1_512 = 'b6bbd7940d59e3b35a4227ef3d8d28f30d3cf1e4f69b8d768bc93790b651cea9f2e2da69be619bb26ba946d56a1625c0dc27a7e58e8c350c5e5ff18c9d8a3f22';
    $key2_640 = 'dd8daccb9e2b66321a9fc149276270586024a3cba3fb1c74cdf72a7e91f86ea1296c7ff5521781998a3f808602f548861973d33fe4b1b374c9a6f22cdc6fbe7336b968c9da8883068436374c129fa87d';
    $key3_256 = 'a98be275630409fe4632ed02a910d7e23f88e5dedb76d59a233772d3e792d121';
    $key4_384 = '03dbe0735142fc5f286e4c87711f3401ed554c272a2c6fd937c91c6777fac44dc6cee703a5ed84bef43e54109db18edc';

    // If running PHP 5.3 then make sure to create on instance of the Encryption
    // class before running Tests so that [hex2bin()] is defined.
    if (PHP_VERSION_ID <= 50400) {
       $crypto = new \FastSitePHP\Security\Crypto\Encryption();
    }

    // Create an stdClass Object. When decrypted
    // it comes back as a Associative Array.
    $obj = new \stdClass;
    $obj->userId = 1;
    $obj->userName = 'Conrad';
    $obj->roles = array('admin', 'user');

    // Define Tests
    $tests = array(
        // Decrypt a Null Value
        // NOTE - the only way a null can be encrypted is
        // by setting [$crypto->allowNull(true);]
        array(
            'key' => $key1_512,
            'exceptionOnError' => true,
            'encrypted_text' => '3TmsaOSE5m34Gk_3hQ9xWFP_iEFQed1bLIK85wGqjNF6twXnksVO2W_-doMztHpzW1BMtnr7UzfBYxXdJhJ_VA',
            'expected_value' => null,
        ),
        // Decrypt a String
        array(
            'key' => $key1_512,
            'encrypted_text' => 'odjv-FkSWWLWbl6MkiS1kZFiaK140cGfvZTpM8zzbwpwa_uDQGFWcxWp1a-kvYbUPnZLXh3CMwCwI78ihpLYLrQS8tLk3AnZpzGrIOTO_slTYcR6iOVEjVId-k3-b_finxRQIiFKDI67nZrYgNX6wIHDr7fHgCIyt_AQCalXd7FACwCLuOBxc1qJdG5agb0M',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ),
        // Verify a UTF-8 String - 'Hello' in Korean
        array(
            'key' => $key1_512,
            'encrypted_text' => 'SvoZG5-Jt_ly7TYCbSjbL9gnehM9lVqeQez3PcpoabZ7L6CcSkVGa1MT0AS2npOg0allv4hnJoqCeCAEpshjZQ',
            'expected_value' => '여보세요',
        ),
        // Decrypt an Empty String
        array(
            'key' => $key1_512,
            'encrypted_text' => 'mouUw5o5vaoWI1dRD5kJRnGfdRUvXEFhN5cAeQDvJqsGN9UR08nGA-EA-KUFsYc0ERHba2dTvreweYKHId_pCw',
            'expected_value' => '',
        ),
        // Decrypt an Int
        array(
            'key' => $key1_512,
            'encrypted_text' => 'mwDJcfU90cJKkewahNjGu1gdGa-KxxJkTL-AUNP2_szegD_f0Cor-rFGo77GaySs0Gd0F8kMc84roOwdAtfC5Q',
            'expected_value' => 12345,
        ),
        // Decrypt an Int with Additional Authorization Data (AAD)
        array(
            'key' => $key1_512,
            'encrypted_text' => 'ATXHKLJaGI-Xk5UNTOQ50tFxh2tPFPsq14JPHbxgdvaXoR0Vtt1bh9Z99eexH2L6NaWb1Mz48elaVr3ekCXqnA',
            'aad' => 'aad',
            'expected_value' => 12345,
        ),
        // Try to decrypt the same int as above but excluding AAD
        // so data cannot be verified and the function returns null
        array(
            'key' => $key1_512,
            'encrypted_text' => 'ATXHKLJaGI-Xk5UNTOQ50tFxh2tPFPsq14JPHbxgdvaXoR0Vtt1bh9Z99eexH2L6NaWb1Mz48elaVr3ekCXqnA',
            'expected_value' => null,
        ),
        // Decrypt the max size of 64-Bit Integer
        // On a 32-Bit OS and PHP Install this will be returned as a string.
        // On a 64-Bit OS running 64-Bit PHP this will return as an int.
        array(
            'key' => $key1_512,
            'encrypted_text' => 'afDktEiK0RWb-I-ftjpewzM3PGaISXZTCDgasFk3CHbusgumH016wAf3bqHk7gFvFYUAXTFIRWji3JDHI5gcTciKkVbnilobMADDRT_Cdu0',
            'expected_value' => (PHP_INT_SIZE === 4 ? '9223372036854775807' : (int)'9223372036854775807'),
        ),
        // Decrypt a Float (Decimal Number)
        array(
            'key' => $key1_512,
            'encrypted_text' => '8KzirCXA6XH8QkFkQU4slS2H5my3zvbYJ8l4VB7fYaIt1xJrvFD4BeYR8XwsOSJXQI-8g_Spkkw-l1bEOcuczA',
            'expected_value' => 12345.6789,
        ),
        // Decrypt a Large Float
        array(
            'key' => $key1_512,
            'encrypted_text' => 'sPswszPJszWyfCL7lUrO4hdPEj14MR1EdFtBteUtbPhvZtS8pOZdJPR9-Emhf-O5Z2keME3Cnl3eTFemv854PS1U_Cr6fSnZ0QQ-VcgldiM',
            'expected_value' => 4.6116860141324E+18,
        ),
        // Decrypt Boolean Values
        array(
            'key' => $key1_512,
            'encrypted_text' => '_jaht0OCwyaVIqPW7JAdtQaOmpcHqWuJ1FOIRwkfOaSaJ6YotAa5wPLYxC-PS8HppA_8TWZuvmK8PxXFSZ77dA',
            'expected_value' => true,
        ),
        array(
            'key' => $key1_512,
            'encrypted_text' => 'bBOkW2ASit4rTycaERazqqiZmj0ggmF01yKLyTKPFBHQRmdoXug-bifIL-ziO4YltGDtuUI39m2Uny_tP5m2cw',
            'expected_value' => false,
        ),
        // Decrypt Objects
        // Returned as an Associative Array in PHP. Similar
        // concept to a Dictionary or Hash in other languages.
        array(
            'key' => $key1_512,
            'encrypted_text' => 'i1KcVGwUu03w-lbP81BKN0-BojEwwUSAlnGrfaMtvh2WE2yB6qnFTw8EnXiQcQ7vXyzJ3xypo_wIHLLFDOaCebyBdteHvzEpXSSvp4qgIW5KNufYhbVuXHL1UjggJcz9TesI5qJBaz5WQS96BoyFLg',
            'expected_value' => array(
                'user' => 'Conrad',
                'id' => 1,
                'roles' => array('Admin', 'User')
            ),
        ),
        array(
            'key' => $key1_512,
            'encrypted_text' => 'UDKYmqDYCdvdR6d2VAjQlLfMOl5VJM-FMfQexmNOpvoBfMqqgptEUKu4ntPg7Tqj1dYGP4QXtxU8rETdsSpiW2yn-rEc5Zbh47J7kyxgW-mfAHCzqWIpZXw7yLaIYtoHq1qheyBW03Q4adgVljEEpA',
            'expected_value' => (array)$obj,
        ),
        array(
            'key' => $key1_512,
            'encrypted_text' => 'ruJoX1VbYd5tsBe5Fy-BXkvqB5o3yAZbC1pd6WQTJGGatZJqyaqY3XXNTMcizAbkR0budIyAVwehPjKoTJQG87-uEBjm99P4GFbYFCjrmeDoAsOpIZI_p5cGZouqvZNz',
            'expected_value' => (array)(new Test()),
        ),
        // Decrypt an Array of Ints
        array(
            'key' => $key1_512,
            'encrypted_text' => 't8Su4Kn4nKfQ3nWKp01DSs3HmVWfed26D-jUBw5SaiVUN6mBSutG86fft7KRYXs5mDm6I1RXrsTlhNZJf1NA5Q',
            'expected_value' => array(1, 2, 3, 4, 5),
        ),
        // Decrypt an Array of Mixed Data Types
        array(
            'key' => $key1_512,
            'encrypted_text' => '0hnYtyhCn6aY_MXVwnnmkqAEUW5k5ljvL_qBe_F1BtDcYlS6qkH6gBELZ3hCetw_LdET0gjOovMoKK0aZfYQXIofqcSTBoWXcFBFxxJrh1LAJwE18wDuTUwAhTsD7xBd',
            'expected_value' => array(1, 2, 3, 4.5, 'a', 'b', 'c', true, false, null, array(0, 1)),
        ),
        // Decrypt a String that was signed with sha384
        array(
            'key' => $key2_640,
            'hashingAlgorithm' => 'sha384',
            'encrypted_text' => 'n9SBmEr3_UzmNIv3FRRxE4gmX613k8xyyrcktqkuf8WRYo79_Q10bNlOm9iJoCHb5RhBM-RiEVaGDCZQGI0p17KexBQIFc_tO8IyAlUue447efzEvOxSzp7FO65Ix0lO',
            'expected_value' => 'This is a Test.',
        ),
        // Decrypt an Int that was signed with sha384
        array(
            'key' => $key2_640,
            'hashingAlgorithm' => 'sha384',
            'encrypted_text' => 'EYT2MVSJVc_rTZWHg2WiJtRM6_epe2Ik2-qonKiByGA83fO4XbIH_IING7MvnxM7Cz6Z40KHNRJ-Zc-kVl-_8CzsH04doyJJ1UE1N7IIbzo',
            'expected_value' => 12345,
        ),
        // Return Format - Default 'base64url'
        array(
            'key' => $key1_512,
            'encrypted_text' => 'iKLA6yRGkd3pqk_1dhYEM53KincdOJsWc6V4co39Nlw7ihXheJwiJxiBPArdq0ekLIU0pRgZBiKI31AeZoNnavPaUaefvyEcbs6AlwhPwFIuWRmkDxnhNEfNNsYTzGAFMH7_fd28CrgDR9UiZdl_5s-ABZmhCRGMCsMLXymBvA9-2lKtXP0R9Ir4kB8i5CcX',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'base64url',
        ),
        // Return Format - 'base64'
        array(
            'key' => $key1_512,
            'encrypted_text' => 'sR/1TYJYNyt4UeHnDVwme5ENrTeneSVq7FbguewuJCAntC28oeGGrZgCEzcSRtDIe4lSwQekMU1c2joKDUGe8guGbsiUuPz6M3cFqsyLksNbb60tMjw8yZtPmYOJqyIxJLXlKns/2bDwroFdO584rRgLJOrsUw3gbLa6Zz1TLgccGWNPECEcjOiQyP/1qHOt',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'base64',
        ),
        // Return Format - Hex
        array(
            'key' => $key1_512,
            'encrypted_text' => '818b07c7980e799dd425397d48787fd2b04cc42d0342e61fc0a42e933f201400338394d5facd62514fca672edf25471e8d19df43a373b7108bb02e20ab9b0a265de4a6e601eb778c7a72e1e22cd04820e1385bdfe43f032422455f91dd7d35579a0d352485ba056e208af12a7357bfcc7655c372dd6cced55aa295e22a93c340ec09c46dd1138fa7e15b14f6ddc67457',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'hex',
        ),
        // Return Format - 'bytes' = String/Binary
        array(
            'key' => $key1_512,
            'encrypted_text' => hex2bin('5412f4a7cf7f4a5498430047460888009ab2ff5ec449541137c0e95531c9e5012047a501d8da54554b35a6238732a4a75198ca7753bf25c2156dcbb338c2154f8ad9aaad1e579d2807e66ccbd7cde5774dfbfd74432a2323bf53c3cc20b0f119f3c99860436a5ad5a68b290b5c0f92695f6e08d5a058757b63b309f42809d1afb377d0414caca83a52e3c40ccb770682'),
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'bytes',
        ),
        // Date Format - Default 'type-byte' - this allows for data types other than strings to be encrypted.
        // When used an extra byte is appended to the plain text before encryption indicating what the data type is.
        array(
            'key' => $key1_512,
            'encrypted_text' => 'yBtP9-KUAfwA2tn9X-lbnEbGYzvWokU40pulBK9PWzP1TjfAr0VzLdmrRYYqhFi7DTL2PoID7yNJ1el_J5pKS3UxRSjSouuC1xIhTcipGQs7etPvYUPZr5qBQNN5xFaeMbVux5IBvj6CNlvh3J6gdqkoJKMbqXPQMSuEHMgH2oesmLEO05pNj14otokNCdUf',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'dataFormat' => 'type-byte',
        ),
        // Date Format - 'string-only' - only strings/bytes can be encrypted when used.
        // This format also allows for compatability with file encryption.
        array(
            'key' => $key1_512,
            'encrypted_text' => '0KDSxi-JlHnrEvZ4mOIa12lA98a-0NsKpTBFgowAPW7yqImyhx__XO2W8rFiMKMdhpCIFYFe17_IHsOyKjkzMfzhbsXuXwZJ4BLmLIYEbOzbCGvQH55lFtlWUNXC51sHiedQyKxZ3JKy8IqAvsusNgrvl2w4qzBnUOP4pKjkyOdkro7yRLBHwAAJxg3GAn2T',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'dataFormat' => 'string-only',
        ),
        // Encryption with Authentication (default) and without (not recommended)
        array(
            'key' => $key1_512,
            'encrypted_text' => 'wR3AiuRSmvgPdCxZxFfG0YWfD5SYg77DWrlr37yLANa4mqBMgQF6-jJpT4fZwOoZEAh2Q9FxjwslxJthLkpq_SeKESwAp_zxMa5bXi-d2QbyUsWw_X2kL2jC77L_j2Wyh7vSJjev-IMF7zJLmbaAMrjQn1C8bXtZon7Uqme9V4J2RWF2h-Upe02Rn9B1Sv0X',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'encryptThenAuthenticate' => true,
        ),
        array(
            'key' => $key3_256,
            'encrypted_text' => 'FTKEgecgG86ze1eFLGHc6t9juZP2s3oBqkuPIDk7yBHmheyjTLvcO2G7oG7oVU_mPWsuaJ2pL02bT5uPWcys3AtSr0VP5ZKeGSfDC_Aafi7mRipD4HlmU1xZ-iz5Qb33ZWUcpm7uNwop5b63zR88wA',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'encryptThenAuthenticate' => false,
        ),
        // Test with default encryption ('aes-256-cbc') and hashing ('sha256') algorithms
        array(
            'key' => $key1_512,
            'encrypted_text' => 'i3ewXNU72wnaorVuR7kiUGUnMMxoSTgcrafHVduN-Y5WcC1r_olNOqQRGC4jores5V0Wu0psA_BloY41tRLGLV9g403U6XDMmIqE5AokzlOVQp6EmB4HDq2ebSaz1BwJKJHram6FnemovbnNp55HJRjMdKZEuf5sodRb96lmyUnx34l2YXZQLo5Idd7B-2F8',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'encryptionAlgorithm' => 'aes-256-cbc',
            'hashingAlgorithm' => 'sha256',
            'keySizeEnc' => 256,
        ),
        // Testing with non-default encryption ('aes-128-cbc') and hashing ('sha256') algorithms
        array(
            'key' => $key4_384,
            'encrypted_text' => '_f64lovm7IB7d7vW85XpqJxnXoo-T87ZLFqDZ9fHAEfVHx0A4FDCuNvZxXlI4n98dROJYsCQR38xIi2l9o3amPPsAUOAa9fcUABgG1rRHq3m-Muc511hZ0YU1Uhq6LpDSH_CHNMZHFM1bjCpCFzE2OSI2fIzY1VL-lcdK6B8vHw5UnlRklX6GIbEWZ5vUMu7',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'encryptionAlgorithm' => 'aes-128-cbc',
            'hashingAlgorithm' => 'sha256',
            'keySizeEnc' => 128,
        ),
    );

    // Run Tests
    return decryptKnownValues($tests, $app);
});

// Decrypt known encrypted values using algorithm 'aes-256-ctr'.
$app->get('/decrypt-known-values-aes-ctr', function() {
    // Skip this test unless from PHP 5.5 or greater. This does not impact whether
    // FastSitePHP and the Crypto Object will work on a server but rather that
    // this specific block mode will not be available.
    if (PHP_VERSION_ID < 50500) {
        return 'Test Skipped, PHP Version earlier than 5.5';
    }

    // Define Keys
    $key1_512 = '443df47eaaac4328e72f9d4984d98d8e7858cf2e245bb6b33aad6c4ab1e0ee2feb44eb0973c690fdf3863eb972711f7d439e383796e4a98deaa6da934ec8814d';
    $key2_640 = 'fe0bc5c492de4d538f03aabb7bdd1e624f601d72730502a50071c4de6af18e4840fb7d16ff083d8f23c614932a149dbe9acbf0b7206abac033ae046fd0abedcd25febbf0e6364c95e5e8e7d008178164';
    $key3_256 = '265339046602a45d6b3100cd8b9603cb1763f01f991e300259155e1ef7010c4a';

    // Create an stdClass Object. When decrypted
    // it comes back as a Associative Array.
    $obj = new \stdClass;
    $obj->userId = 1;
    $obj->userName = 'Conrad';
    $obj->roles = array('admin', 'user');

    // Define Tests
    $tests = array(
        // Decrypt a Null Value
        // NOTE - the only way a null can be encrypted is
        // by setting [$crypto->allowNull(true);]
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'exceptionOnError' => true,
            'encrypted_text' => 'g1-JeNmo40x3HFnqQBu8wbZLskAowUSH0qhHgInuiNE0c1ekadl4IDRlO2Vo9sU706w',
            'expected_value' => null,
        ),
        // Decrypt a String
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => '1h2LVeq5QzSbAL_gAE5oH1u4uIozrBnQiFwOkqukoA5N339HXHNio4KQEvL0dqFbdNbGE0-KjVJrFnAHZv7GfXB12LvcbqFHLbcT7x3ZDI4iesDHbzozZIxcDbrI-LBIv01DItLZ4KfO_9B6S0791uoOpEVeqoLQlvlswRCeEO6TvW_xTPnqCUk',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ),
        // Verify a UTF-8 String - 'Hello' in Korean
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'c-MTrmZi5KJNRMIWebxUObt61oVqGwRRxqt4Pv77o11iR5jdL9qj1MvvkpPB0Xx-4nsZObq_KSCfJr9McA',
            'expected_value' => '여보세요',
        ),
        // Decrypt an Empty String
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'CzNkkSSiXGh1fXFqZAXIFEHIAIXQzUtVPyWJkMFrk7rOrEGgYpjUWVDJe00zh7Kx2Q',
            'expected_value' => '',
        ),
        // Decrypt an Int
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'PTcGBxPjwGVaLpXfvIm4XSMH4f1aXGMnpuHqIwQby2_cllE_WgcJekXf7PWqAKaNSvlgNngO',
            'expected_value' => 12345,
        ),
        // Decrypt an Int with Additional Authorization Data (AAD)
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'k9e6txWycaabPFAKAgMHOxq3Ngp25wsrq6eVLMXMFSDfHGvN82IP2iDUvH6jFaIkemq1XO_c',
            'aad' => 'aad',
            'expected_value' => 12345,
        ),
        // Try to decrypt the same int as above but excluding AAD
        // so data cannot be verified and the function returns null
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'k9e6txWycaabPFAKAgMHOxq3Ngp25wsrq6eVLMXMFSDfHGvN82IP2iDUvH6jFaIkemq1XO_c',
            'expected_value' => null,
        ),
        // Decrypt the max size of 64-Bit Integer
        // On a 32-Bit OS and PHP Install this will be returned as a string.
        // On a 64-Bit OS running 64-Bit PHP this will return as an int.
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => '7XKbcKWQmUm0iqRFJ5M9k3nZtxbmRI9LXHfG8GYN575zNajro3FLdtnELUc4TIZUuCz5lkxA43IWs-2TAr7r7XExkXs',
            'expected_value' => (PHP_INT_SIZE === 4 ? '9223372036854775807' : (int)'9223372036854775807'),
        ),
        // Decrypt a Float (Decimal Number)
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'eO6-IsFe_yM6NUphxmambXbDmU2cJrVahVfx5Io6i0c8-RToCPE7XgqQVga3XgegQse2Pa4xo7DtvZA',
            'expected_value' => 12345.6789,
        ),
        // Decrypt a Large Float
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'f2GSGUer3s-ka08OzEPdQvQqNH12f1dk-blKbb3rE1CxVJYAmdoO4p6jdr9yocUIin6I69pmhlc-T2rm8kVlUY5FsCM',
            'expected_value' => 4.6116860141324E+18,
        ),
        // Decrypt Boolean Values
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'sX6qpJwGiOD3YTMM9qcTCCWx_FoCIVFDHSEk9zJ3eeM7579YCoKUf5fcj-TA4TbmcMU',
            'expected_value' => true,
        ),
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'Ex4xCDz0mB9OpFqoE2KX_YkxZhtoFsSpYUUJ3GuENcXh7b7b0IdZcQEnqXn9M88_5MM',
            'expected_value' => false,
        ),
        // Decrypt Objects
        // Returned as an Associative Array in PHP. Similar
        // concept to a Dictionary or Hash in other languages.
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'xD-5zYpGV502Yo0pVbgzEeql-9qvnII_TEgSAsUkoREFAu6qPrHI7N97F5pYC6Dvjb59yr4MvdtoEVXy_2wz1nT1D-3ksMXUqZBlqLf3KAsP1emic1ObRSKfQmBGxCPLgMg',
            'expected_value' => array(
                'user' => 'Conrad',
                'id' => 1,
                'roles' => array('Admin', 'User')
            ),
        ),
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'KaNTdQ8gKtFpoIk__rK60Ph9TQ3jJmMPstol1BX3scM34dfxF0OD_77lQscU7NDiGjerIsVtOnHj0y0PeVfDnqygP841ED218fEBVPJkQKS_hfyqoJ0_EHW34kr17tEacNdJuoaicUJAlQ',
            'expected_value' => (array)$obj,
        ),
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'oIltcGUTjDWE7IbRt5PATwpp2i_rKkl-n1eRxidcs4H59Gq0TI8sBIszSmzm6RIJJqNVvS7IG9lWoCdZAYHDuykJlEAXYnpAg1mwKdahh9t2s7sIHiWqD8Lq',
            'expected_value' => (array)(new Test()),
        ),
        // Decrypt an Array of Ints
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'BmxYpMpC8aVXPYb7SPsQntcryuli5i6Efdy0TAdEjtVTtRGGb6NLkFgXmUwFqjnCVj2hszIY7SlccqV8',
            'expected_value' => array(1, 2, 3, 4, 5),
        ),
        // Decrypt an Array of Mixed Data Types
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'EjzUGfxsNfkQKlELVGRjSTr-2PwGxGRO42PdNSrfmcqRz8n91crrvaG4-jrCUWAHabD4VySf3PnA8w-dFKPBAJ9Ira8N9wNgILnk2Qauw6-AQ1ic4Cy3G7Lw4E3pjw',
            'expected_value' => array(1, 2, 3, 4.5, 'a', 'b', 'c', true, false, null, array(0, 1)),
        ),
        // Decrypt a String that was signed with sha384
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key2_640,
            'hashingAlgorithm' => 'sha384',
            'encrypted_text' => 'PA6TeTAMojUs8A1kvEalFFfuqsMIL6fcXSHikAgFp6bGix0lEMWRBcmMWSZghenIqG_7Zx_orfdQX7kyuJ7DXUS6_wV1sZY6JNeZm6cAF3c',
            'expected_value' => 'This is a Test.',
        ),
        // Decrypt an Int that was signed with sha384
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key2_640,
            'hashingAlgorithm' => 'sha384',
            'encrypted_text' => 'Zjc5-IARd6BJ64TNymsKe7sjLnxHQCW0dn-Uo34_0xc3H3wBL1WLN_vVFwOFa_umWfp4Wc_gh9MGxnsWGl4iy_kTwhuf6w',
            'expected_value' => 12345,
        ),
        // Return Format - base64
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'obbhSDoH+daNo7g03ezMYmJrd/EJRZus8vVvd/tBXjQ6AU3W9npi49XymS0WuSkFFGDlsYPINk+PtDofdIRKfIEuuA9WMmZpym1eaJrFCZN2JFkfmqh3LqA7lC2iO8rVeqOmvJmFJxt0yRfdgefPtX+5q81o3e8sYW50RNsluukm+OnoKZbkDRw=',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'base64',
        ),
        // Return Format - Hex
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'ab47bf98d8d6a8789950fb292327fd2e958333868b939f3b99ff198b436d518486380453c82190bca4f920aaf350333342b2e7731c30b3e5cb98e8367ca518f343d621aeab18a0c8ad4e39bd5bd4b3f7224379da5ab1b96210828f98a0f4345691f79f3a52c0315095094fa558e0a0add0cd576d0fc47a2fe47bebcfcdd00c49929dcb7777051a8f0c',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'hex',
        ),
        // Return Format - 'bytes' = String/Binary
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => hex2bin('eefd8130bc120377ffda64034525737bffb2df5a8d8afc40ce10a2a289dcf84bd86e070d2ef5ef2057e55faa64dbe85c2390e1dc3692ddb03c0a3bf4cd1289d19066d9b470b89fdba68d994a25cc8d19f56117474912aa7896e89be25c3113804e57dafe6dc6344acd5d458af749afe997fbf4ffd6e8621c92d8c6289c8d88473e895f1c464577b911'),
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'bytes',
        ),
        // Date Format - 'string-only' - only strings/bytes can be encrypted when used.
        // This format also allows for compatability with file encryption.
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key1_512,
            'encrypted_text' => 'uwsSjtIWLpqF4zWZjB2Dl6OPI9Fv7ui6zLUcC-3H9DlKazaiEtgIkIQlDOCQfpS0J3ZbH-eZ8t1dRvx0SW4bn-wzZINJiZkdgMlAkPZ6Jwo6Dayvz-mK-3na92mHhJII6NWgaPGY5DmlEP4Ka7USzgaMkQTzEfJHT1l1t122-pJPoKijc3GPfQ',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'dataFormat' => 'string-only',
        ),
        // Encryption without Authentication (not recommended)
        array(
            'encryptionAlgorithm' => 'aes-256-ctr',
            'key' => $key3_256,
            'encrypted_text' => 'lKoeCe5L0rI-4x6ywzK98H3IovnlO0YTV_4c79jvz3IIB3HajRinSSsHwLPQ8PnYuaqLjzyi5nCii-HIyg1l5dMFT5uCiNwhY2-gUJ8aLpwdpATy-NWbkiLGNthYP-ERxSB0txKu69m-',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'encryptThenAuthenticate' => false,
        ),
    );

    // Run Tests
    return decryptKnownValues($tests);
});

// Decrypt known encrypted values using algorithm 'aes-256-gcm'.
$app->get('/decrypt-known-values-aes-gcm', function() {
    // Skip this test unless from PHP 7.1 or greater. This does not impact whether
    // FastSitePHP and the Crypto Object will work on a server but rather that
    // this specific block mode will not be available.
    if (PHP_VERSION_ID < 70100) {
        return 'Test Skipped, PHP Version earlier than 7.1';
    }

    // Define Keys
    $key = '3b5d07fb0d03ad1bdf7587498fb73648f07a4cfd196e9b58696d9ee558a7a79d';

    // Create an stdClass Object. When decrypted
    // it comes back as a Associative Array.
    $obj = new \stdClass;
    $obj->userId = 1;
    $obj->userName = 'Conrad';
    $obj->roles = array('admin', 'user');

    // Define Tests
    $tests = array(
        // Decrypt a Null Value
        // NOTE - the only way a null can be encrypted is
        // by setting [$crypto->allowNull(true);]
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'exceptionOnError' => true,
            'encrypted_text' => 'hfFn6xiRXx_ctRHKbpwWzd14OZMcd946CVFPWZJ7',
            'expected_value' => null,
        ),
        // Decrypt a String
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 't0U-MTDK6Ue_odBbgCrOKSUM03Jd7LnhXJ10_XtgPmZaOfCm6aDE4g4Bm07APMz7ck8ZUtavtT2ZOo9f-CzIdsCWTMZhiJeXQng1LPWPTaQX_E5O9TQHcCYHTYR495QJMJkk4cXh5xTrkJjBFdtCZ_7BI3I_',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ),
        // Same as above but using [encryptThenAuthenticate(false)]. In Non-AEAD Modes such
        // as CBC and CTR setting this property is not recommended however when
        // using GCM it has no effect because authentication is built in to GCM mode.
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 't0U-MTDK6Ue_odBbgCrOKSUM03Jd7LnhXJ10_XtgPmZaOfCm6aDE4g4Bm07APMz7ck8ZUtavtT2ZOo9f-CzIdsCWTMZhiJeXQng1LPWPTaQX_E5O9TQHcCYHTYR495QJMJkk4cXh5xTrkJjBFdtCZ_7BI3I_',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'encryptThenAuthenticate' => false,
        ),
        // Verify a UTF-8 String - 'Hello' in Korean
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'jDENLu9UNDIaUCEsnzr96zGmiICRDczYImMFlNqP0EexdcfLesZ_idw',
            'expected_value' => '여보세요',
        ),
        // Decrypt an Empty String
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'cKurBbLCuwxnoxvGGQ0zAfSmJnBZWv1kX4cslxI',
            'expected_value' => '',
        ),
        // Decrypt an Int
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => '_m7LDzHzK6yDNrL3__N3SJu_q0nbONsguOb5yLapq1oefw',
            'expected_value' => 12345,
        ),
        // Decrypt an Int with Additional Authorization Data (AAD)
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'KcU0nDvrB2bHHIAVgrPVuDuUWQl2eaLDVEr7Oi2eAB9wyA',
            'aad' => 'aad',
            'expected_value' => 12345,
        ),
        // Try to decrypt the same int as above but excluding AAD
        // so data cannot be verified and the function returns null
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'KcU0nDvrB2bHHIAVgrPVuDuUWQl2eaLDVEr7Oi2eAB9wyA',
            'expected_value' => null,
        ),
        // Decrypt the max size of 64-Bit Integer
        // On a 32-Bit OS and PHP Install this will be returned as a string.
        // On a 64-Bit OS running 64-Bit PHP this will return as an int.
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'wxOo4D1tHanhzcqIwErZ9GmI7x2oHLXau6b-ny2sLqEPJUjzD_2bm0TYssgAoVxS',
            'expected_value' => (PHP_INT_SIZE === 4 ? '9223372036854775807' : (int)'9223372036854775807'),
        ),
        // Decrypt a Float (Decimal Number)
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'V3ndn-VjavbHgVQKCIGlHIw-Yym52zWHhEHXH9P4tcGBOnNMG7FM',
            'expected_value' => 12345.6789,
        ),
        // Decrypt a Large Float
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'fzJ73A_35Bx7Rq9sGfIX3hO_FKTPgQ_AnQv_V2B2rECH18LBKxslgx0BqK8x9JLY',
            'expected_value' => 4.6116860141324E+18,
        ),
        // Decrypt Boolean Values
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 's1B6LYCuXaNEiHfVpLqC0WYB57PCD-B8n0EIph4a',
            'expected_value' => true,
        ),
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'hbdS0hOiI9_bIuqD-hOyg4hMKmfQ4UjXudpa-ifr',
            'expected_value' => false,
        ),
        // Decrypt Objects
        // Returned as an Associative Array in PHP. Similar
        // concept to a Dictionary or Hash in other languages.
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'Y29gQKhz7FT5SjVj_6K1N6mOdVAeV9UelYFePkW_FOUUzzPbsKEQxxyEcRXe_7_U11I6Txbi6X8zEL7HzKaFDeIUy03ipJd-HQsp_F61',
            'expected_value' => array(
                'user' => 'Conrad',
                'id' => 1,
                'roles' => array('Admin', 'User')
            ),
        ),
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'MFpnIQniBMhZICNa9s4HICWmF5X1S5oDymTRg7FKCjKFhCJxYbkFV1xi-ToMOqGitcrkkUEGM5CeD0KPYGH7YhabCBzMybw7Lsc1TEfFqpa0FkVbJjI',
            'expected_value' => (array)$obj,
        ),
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'tr0WphR7zD6jwRwziB1CmmMK3jjhiQOstIymnaiWdp-HHXu2KEXN-wh-wiDmIXGmZD4FaefssyYxnJ-lXTjxAC7aLwfuPg',
            'expected_value' => (array)(new Test()),
        ),
        // Decrypt an Array of Ints
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'miz4-L0JaKedZLQkvTBlFaJ1vhtOu9WJ8Txd8kzxLaWlTs3jlZe1og',
            'expected_value' => array(1, 2, 3, 4, 5),
        ),
        // Decrypt an Array of Mixed Data Types
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'RX7e1KAQyN3EQA-LlU0ThKLaKAkWoWEpUYuKNfufuRBtrA8BE8Fq4o5fwhDA6wXzXkOpGjPbJVFA5lEJs0L557TgPqo_0ArWjNk',
            'expected_value' => array(1, 2, 3, 4.5, 'a', 'b', 'c', true, false, null, array(0, 1)),
        ),
        // Return Format - URL Safe base64
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'sy75qu2usMevpnxoXZh8HYGTJ3D_fBIMXu_ssMZbAxsU15NOl87tw0ZYKU-SvWyOqPMYATbbcavQQ0TqNAHL64M6dnSmzVhv9a2_sBPenoQ3bA_OlKxN-XY5Ey3gCo8-6l5M_z_k-L01g7L77iCLGXOtW6al',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'base64url',
        ),
        // Return Format - Hex
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => '0e1ea0fa1231dec4cc3ea07d5397ca989406c27e71d47a042d76947fccd5cead323c53686fba3d2847d93338870030ac044565f9eaa1d4aa4aa2ed48ef78adb483b4d151a9259edf60941e304dd9c0cea20b61fbb29a4efaef330a902dfde7e4ed70d52b7c8e21876ca9f87b7d1c4da0db0c3f6c6f',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'hex',
        ),
        // Return Format - 'bytes' = String/Binary
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => hex2bin('723ba0d51088f68cd109674a26d7c101e8b0ca2dd5d2c41f8095c4e0d1ee86a8964a030a60c0f33473823f567c5b1f0b150f7b1e4afc058e1f963a049a1f178f065dc542bfaeddaa25f43e16eaca921a258b5707d8a35df4e4ad7ba2d4b2e35e749c979687dc1f558796d512e48c35c36e6dbeeec5'),
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'returnFormat' => 'bytes',
        ),
        // Date Format - 'string-only' - only strings/bytes can be encrypted when used.
        // This format also allows for compatability with file encryption.
        array(
            'encryptionAlgorithm' => 'aes-256-gcm',
            'key' => $key,
            'encrypted_text' => 'E98RIeNHSyv0HFALW6jHQYVldjg7-aE93ZlDBgnUjHer8xi-shI7BIaP_2XTXMhK2owPRVwyRrG9BpsyT6gb3TgSNDRokDB91Jfx2LuCa1r6-pSF0RIX4MxEJm0dIyyasbGsvVKAIVn-je1ZlAD9HzusJMs',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'dataFormat' => 'string-only',
        ),
    );

    // Run Tests
    return decryptKnownValues($tests);
});

// This route is similar to [/decrypt-known-values] but uses password based encryption and decryption
// using PBKDF2 (Password-Based Key Derivation Function 2) rather than standard keys.
// PBKDF2 can be very slow which is why it is seperated to it's own route.
$app->get('/decrypt-with-password', function() {
    // Define Passwords
    $key1_512 = 'de5f31a2aa949afc16fdab86880b4bea0d07bd38aa8c44c9afbd51a8021e460e39cbe73b25f1b74417b1bc412aaa0bd6375cff2973d760f6542722412b3d5e90';
    $password1 = 'password123';
    $password2 = 'password';

    // Define Tests
    $tests = array(
        // Use Standard Encryption Key while setting key type
        array(
            'keyType' => 'key',
            'key' => $key1_512,
            'encrypted_text' => '9qahJQNVumJktcrB7KsbLVeVMsUQQVdFZ9RcMvRo96c4r_X9UDDnZFospW_LWqGDJEudzcJfT3GB_udk_GFdUcHqBz8KFIT8uLziq3W8d1AyWd2wfWHRU-XQm6uQ0tl8ZbgVe_t0wl5-svBLkuRistpkJajGr9MqvcDSaX3QEAL3FN2Ypg49scKHUMWTMxW-',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ),
        // Use Password
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => 'p6tONJ6Xkv2mq5tIQpEBmoZIqng6ewcnrFwJ89BPnMhHO6k8V2BRlpaIJAughy9K7Rh1wcPFQBdBV2lS_ixIVuFU5ipIPHwN9IadfIJ9fw84fM9-kfdfC1Mzze3buEJSsX4HNiOZN6r6Q0IEkRMhZMw1irBJSnS0manjp-kd5FFrjwyrLOHms2mBhlY1TGbV',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ),
        // Use a small number of iterations (10,000) for additional tests, these run much
        // faster but would not be considered very secure at this number of iterations.
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => '41NFl1hxfBzggY-WgyYDhg_ZsWQiQHamQ1iYsesU80uWeEpv4saWJIcCq2dp8kemfHvDZCa_C3P-2xNToYTSNSPHLwx6Bjxi35VjFhs5ACGPIEKMfI6eJ3d0TUpau3J6HRBBiCa2sqMFyP5M3R6hHmsAKSw11VnkM_AKABvmOtbrn5VsZrhwQ3lPOWYQjft0',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'pbkdf2Iterations' => 10000,
        ),
        // Same as above but using a different number of iteration
        // which will cause decryption to fail and return null.
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => '41NFl1hxfBzggY-WgyYDhg_ZsWQiQHamQ1iYsesU80uWeEpv4saWJIcCq2dp8kemfHvDZCa_C3P-2xNToYTSNSPHLwx6Bjxi35VjFhs5ACGPIEKMfI6eJ3d0TUpau3J6HRBBiCa2sqMFyP5M3R6hHmsAKSw11VnkM_AKABvmOtbrn5VsZrhwQ3lPOWYQjft0',
            'expected_value' => null,
            'pbkdf2Iterations' => 10001,
        ),
        // Use a different algorithm (sha1), using low iterations for speed
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => 'rtzDZew222xqheI1tmDvuoySaSHCs8g91b45cwwclFrFADA9LBoFxyLI2KubnD8RrgfncgVX8QCL91W3mZbnzU9T4C7Z4anH3gNok1eaARxVyUlhFpYfQTiTfBwDf-ZufmhnepNtVf1CahCk1SbZ1k0YywAYBtppLtq55QkpT-3Fm1iPWbHV2Lt2WtPdD7bA',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'pbkdf2Iterations' => 10000,
            'pbkdf2Algorithm' => 'sha1',
        ),
        // Same as above but without 'sha1' which causes decryption to fail and returns null
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => 'rtzDZew222xqheI1tmDvuoySaSHCs8g91b45cwwclFrFADA9LBoFxyLI2KubnD8RrgfncgVX8QCL91W3mZbnzU9T4C7Z4anH3gNok1eaARxVyUlhFpYfQTiTfBwDf-ZufmhnepNtVf1CahCk1SbZ1k0YywAYBtppLtq55QkpT-3Fm1iPWbHV2Lt2WtPdD7bA',
            'expected_value' => null,
            'pbkdf2Iterations' => 10000,
        ),
        // Encrypt with Password and No Authentication
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => 'Rvyks6hBBO8PnL7u8PwOagwFA7RLo1uFDB-NXObS_LGXbj8sGFQnZhCINvMCV6hB38Y-Nc5jBt67mhLvUfSWaR3lj2FSwXdWGcFGpsFm7aZdcZNfHxZAFO_u2dUgCa3lFINHnc9Fj5rrgVhsaiJEGg',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'pbkdf2Iterations' => 10000,
            'encryptThenAuthenticate' => false,
        ),
        // Encrypt with Password and No Authentication (different key size defined)
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => 'Vjb160mI_gS620ILo8JwfTFMERTNW8YY92BRQZlhQ4_eJo2sjPi6KcDE3MhF-qPTYZ9q3HART_yVNGX4UMsmX4_zOJUUohcRUxea-besInGqUqfwR4p-oncYEOpw4KTjrLlfZv9tTgVSG2X1rCDilQ',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'pbkdf2Iterations' => 10000,
            'encryptThenAuthenticate' => false,
            'keySizeEnc' => 128,
        ),
        // Same as above but using standard key size causing the decryption to fail and return null
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => 'Vjb160mI_gS620ILo8JwfTFMERTNW8YY92BRQZlhQ4_eJo2sjPi6KcDE3MhF-qPTYZ9q3HART_yVNGX4UMsmX4_zOJUUohcRUxea-besInGqUqfwR4p-oncYEOpw4KTjrLlfZv9tTgVSG2X1rCDilQ',
            'expected_value' => null,
            'pbkdf2Iterations' => 10000,
            'encryptThenAuthenticate' => false,
        ),
        // Encrypt with Password using non-default Key Sizes for Encryption
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => '3FpQLm5NBff0hprr8PTKlXLck0JhXLpwKc5AHrRjXXWWw0BRuSO9zPhxPvQU4ZVaEUoQtX0XLUQlWoOp8t6pghiIqSx3bzeIQV-TDXft-kcIWKnpX4dhpW-42I_EoYaG8863uiJIOa4xaljH9n980o-93O4lIxmB-qiS-B18yU5dMKxTBLYNfP6uHZ5xaDAl',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'pbkdf2Iterations' => 10000,
            'keySizeEnc' => 128,
        ),
        // Same as above but using standard key sizes causing the decryption to fail and return null
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => '3FpQLm5NBff0hprr8PTKlXLck0JhXLpwKc5AHrRjXXWWw0BRuSO9zPhxPvQU4ZVaEUoQtX0XLUQlWoOp8t6pghiIqSx3bzeIQV-TDXft-kcIWKnpX4dhpW-42I_EoYaG8863uiJIOa4xaljH9n980o-93O4lIxmB-qiS-B18yU5dMKxTBLYNfP6uHZ5xaDAl',
            'expected_value' => null,
            'pbkdf2Iterations' => 10000,
        ),
        // Decrypt an Int using a Password
        array(
	        'keyType' => 'password',
            'key' => $password1,
            'encrypted_text' => 'vI5_xSHTxRGcqL-Rydm8SquGvOrqNIUUn1BMng85st75Z_443NUzDgWNnOjkL1-HFWVgEOrqWuPw18VwKk8EgQ',
            'expected_value' => 12345,
            'pbkdf2Iterations' => 10000,
        ),
        // Same as above test but using a different password which causes decryption to fail and return null
        array(
	        'keyType' => 'password',
            'key' => $password2,
            'encrypted_text' => 'vI5_xSHTxRGcqL-Rydm8SquGvOrqNIUUn1BMng85st75Z_443NUzDgWNnOjkL1-HFWVgEOrqWuPw18VwKk8EgQ',
            'expected_value' => null,
            'pbkdf2Iterations' => 10000,
        ),
    );

    // Run Tests
    return decryptKnownValues($tests);
});

// Test Data Signing and Verifying with:
//     SHA-1 (Secure Hash Algorithm 1)
//     SHA-2 (Secure Hash Algorithm 1) with 256 and 384 bits
//     SHA-3 (Secure Hash Algorithm 3) with 512 bits for PHP 7.2+
$app->get('/sign-and-verify-sha1', function() {
    return signAndVerify('sha1');
});
$app->get('/sign-and-verify-sha2-256', function() use ($app) {
    return signAndVerify('sha256', $app);
});
$app->get('/sign-and-verify-sha2-384', function() {
    return signAndVerify('sha384');
});
$app->get('/sign-and-verify-sha3-512', function() {
    if (PHP_VERSION_ID < 70200) {
        return 'Test Skipped, PHP Version earlier than 7.2';
    }
    return signAndVerify('sha3-512');
});

$app->get('/verify-known-values', function() use ($app) {
    // Define Keys
    $key1_256 = 'd5a955f7545882e0985583417528c7376afb71a87fbb90649922d679ba6d6b6c';
    $key2_384 = 'a3cd2b9232f8e07d3b85d5441d73bd68d47dc5cd82c5d31cbb07282635b5d47b847328bcbd11068c67545e5a30d469ee';

    // Create an stdClass Object. When verified
    // it comes back as a Associative Array.
    $obj = new \stdClass;
    $obj->userId = 1;
    $obj->userName = 'Conrad';
    $obj->roles = array('admin', 'user');

    // Define Tests
    // Tests are are mostly identical to the route '/decrypt-known-values'
    // however '/decrypt-known-values' contains some extra tests for AAD
    // which is part of encryption and not signing.
    $tests = array(
        // Verify a Null Value
        // NOTE - the only way a null can be signed is
        // by setting [$crypto->allowNull(true);]
        array(
            'key' => $key1_256,
            'exceptionOnError' => true,
            'signed_text' => 'AA.n.6yLaLUPknft35QSpb0pObnNH9ZAnhyz4WZM5L79C9Ts',
            'expected_value' => null,
        ),
        // Verify a String
        array(
            'key' => $key1_256,
            'signed_text' => 'MDEyMzQ1Njc4OSBgfiFAIyQlXiYqKClbXXt9Ozo8PiwuPyBhYmNkZWZnaGlqa2xtbm9wcXJzdHV2d3h5eiBBQkNERUZHSElKS0xNTk9QUVJTVFVWV1hZWg.s.c7x8wQ9OM0DuLRfdItgB5H0hTarERQzKaFzIHFTCyxo',
            'expected_value' => '0123456789 `~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ),
        // Verify a UTF-8 String - 'Hello' in Korean
        array(
            'key' => $key1_256,
            'signed_text' => '7Jes67O07IS47JqU.s.da7JERkgQoHR0-iROsqRIT4Fy_SFewmu2e8MTnRIaZo',
            'expected_value' => '여보세요',
        ),
        // Verify an Empty String
        array(
            'key' => $key1_256,
            'signed_text' => '.s.zKEGWK4bsipGtRoLAPNHAPCQUnhQMqOnIakDsxaBgUg',
            'expected_value' => '',
        ),
        // Verify an Int
        array(
            'key' => $key1_256,
            'signed_text' => 'MTIzNDU.i32.dG5mFMQEdQxuOQGpUv4gFYMRbqU9qF0D03z9fhRADws',
            'expected_value' => 12345,
        ),
        // Verify the max size of 64-Bit Integer
        // On a 32-Bit OS and PHP Install this will be returned as a string.
        // On a 64-Bit OS running 64-Bit PHP this will return as an int.
        array(
            'key' => $key1_256,
            'signed_text' => 'OTIyMzM3MjAzNjg1NDc3NTgwNw.i64.FSlRJooKIfwfCzilZ-3lp3IXjghDQwjbYfBoARfI5vI',
            'expected_value' => (PHP_INT_SIZE === 4 ? '9223372036854775807' : (int)'9223372036854775807'),
        ),
        // Verify a Float (Decimal Number)
        array(
            'key' => $key1_256,
            'signed_text' => 'MTIzNDUuNjc4OQ.f.OtehJCmgIKfL9byK9FV5Ytb8DJk2hlt1ZLTmYerEQ5Q',
            'expected_value' => 12345.6789,
        ),
        // Verify a Large Float
        array(
            'key' => $key1_256,
            'signed_text' => 'NC42MTE2ODYwMTQxMzI0RSsxOA.f.cOoZx4O5bI0E5wBPcjGt6sAXJd-euOLv8woJFe4pvvI',
            'expected_value' => 4.6116860141324E+18,
        ),
        // Verify Boolean Values
        array(
            'key' => $key1_256,
            'signed_text' => 'MQ.b.Ica7AEdXgKPWycAKrR7NXZYwYZUKfPu1-8GjxO1Y5P4',
            'expected_value' => true,
        ),
        array(
            'key' => $key1_256,
            'signed_text' => 'MA.b.66iwJm1p4MMiPC6fkP-TGF4PGIGx3hCJEpjaZUIpBIo',
            'expected_value' => false,
        ),
        // Verify Objects
        // Returned as an Associative Array in PHP. Similar
        // concept to a Dictionary or Hash in other languages.
        array(
            'key' => $key1_256,
            'signed_text' => 'eyJ1c2VyIjoiQ29ucmFkIiwiaWQiOjEsInJvbGVzIjpbIkFkbWluIiwiVXNlciJdfQ.j.9mOVOj6s-loRPjp230AttQu4nsQ4PyFSTPn9ab-0SyY',
            'expected_value' => array(
                'user' => 'Conrad',
                'id' => 1,
                'roles' => array('Admin', 'User')
            ),
        ),
        array(
            'key' => $key1_256,
            'signed_text' => 'eyJ1c2VySWQiOjEsInVzZXJOYW1lIjoiQ29ucmFkIiwicm9sZXMiOlsiYWRtaW4iLCJ1c2VyIl19.j.Mh5bPbShmA3QFzJBkeKooxMJmI6Alu-FQDh6Z2ddO1Y',
            'expected_value' => (array)$obj,
        ),
        array(
            'key' => $key1_256,
            'signed_text' => 'eyJ2YWx1ZSI6IlRlc3QiLCJwcm9wMSI6MTIzLCJwcm9wMiI6dHJ1ZX0.j.xHmK4hTO5S8p-PmmMSPe_c_9gFoObYDMVI2_HEI-T2U',
            'expected_value' => (array)(new Test()),
        ),
        // Verify an Array of Ints
        array(
            'key' => $key1_256,
            'signed_text' => 'WzEsMiwzLDQsNV0.j.CCGXMvKU2GDdxlXqhSGxJwQ3IElBhl4au_3feY8gCsg',
            'expected_value' => array(1, 2, 3, 4, 5),
        ),
        // Verify an Array of Mixed Data Types
        array(
            'key' => $key1_256,
            'signed_text' => 'WzEsMiwzLDQuNSwiYSIsImIiLCJjIix0cnVlLGZhbHNlLG51bGwsWzAsMV1d.j.rDCZQKm-bZBH8GmT8fYoG_T-yGOFGNvP5hOpP2tWHBo',
            'expected_value' => array(1, 2, 3, 4.5, 'a', 'b', 'c', true, false, null, array(0, 1)),
        ),
        // Verify a String that was signed with sha384
        array(
            'key' => $key2_384,
            'hashingAlgorithm' => 'sha384',
            'signed_text' => 'VGhpcyBpcyBhIFRlc3Qu.s.o2eSqLEDcW0IhbI4q8SElQpkdeieITBfDKpktkSbpNzRjvhQDOrX0r5tfbUj7ekp',
            'expected_value' => 'This is a Test.',
        ),
        // Verify an Int that was signed with sha384
        array(
            'key' => $key2_384,
            'hashingAlgorithm' => 'sha384',
            'signed_text' => 'MTIzNDU.i32.b1rlABWGZLfz3ab_fcKPFtqvrw49bC2n6H2XfEcWd0MrOUYQpB5oB4IOb3tFtIw8',
            'expected_value' => 12345,
        ),
    );

    $all_signed_text = '';
    $passed_tests = 0;
    $properties = array(
        'exceptionOnError',
        'hashingAlgorithm',
    );
    foreach ($tests as $test) {
        $csd = new \FastSitePHP\Security\Crypto\SignedData();
        $signed_text = $test['signed_text'];

        // Set properties as setter functions
        $prop_count = 0;
        foreach ($properties as $prop) {
            if (isset($test[$prop])) {
                $csd->{$prop}($test[$prop]);
                $prop_count++;
            }
        }

        // Internally verify() uses json_decode() which returns both arrays and objects
        // as stdClass so if an object is returned convert to an array for comparing.
        $verified_value = $csd->verify($signed_text, $test['key']);
        $match = ($verified_value === $test['expected_value']);
        if (!$match) {
            echo 'Vefification Failed from $csd';
            echo '<br>';
            var_dump(json_encode($verified_value));
            echo '<br>';
            var_dump(json_encode($test['expected_value']));
            echo '<br>';
            var_dump(json_encode($test));
            exit();
        }
        $passed_tests++;

        // Also use Crypto Class if all properties are default
        if ($prop_count === 0) {
            $app->config['SIGNING_KEY'] = $test['key'];
            $verified_value2 = \FastSitePHP\Security\Crypto::verify($signed_text);
            if ($verified_value !== $verified_value2) {
                echo sprintf('Failed at Test from [Crypto::verify]: %d', $item);
                echo '<br>';
                echo '<br>';
                echo json_encode($test);
                echo '<br>';
                echo '<br>';
                echo json_encode($verified_value);
                exit();
            }
            $passed_tests++;
        }

        $all_signed_text .= (is_array($verified_value) ? json_encode($verified_value) : $verified_value);
    }

    // Show PHP Version while Developing if needed
    // and hide it for the actual Unit Test
    // return sprintf('[PHP Version: %s], [Tests: %s], [sha256: %s]', PHP_VERSION_ID, $passed_tests, hash('sha256', $all_signed_text));
    return sprintf('[Tests: %s], [Len: %d], [sha256: %s]', $passed_tests, len($all_signed_text), hash('sha256', $all_signed_text));
});

// Verify that Large Integers with Decryption
$app->get('/decrypt-large-ints', function() {
    // Create a Random Key
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();
    $crypto->exceptionOnError(true);
    $key = $crypto->generateKey();

    // JSON Data Strings
    $invalid_large_ints = '{"bigInt":19223372036854775807,"array":[19223372036854775807,29223372036854775807,-19223372036854775807,92233720368547758079223372036854775807]}';
    $valid_large_ints = '{"maxInt":' . (string)PHP_INT_MAX . ',"minInt":' . (string)~PHP_INT_MAX . '}';

    // Manually create the encrypted JSON data with Ints
    // that are too large for any valid 64-bit int size.
    function encrypt_large_int_json($text, $key) {
        // Convert Single Hex Key to Seperate Encryption and HMAC Keys
        $key = hex2bin($key);
        $keys = array(
            'enc' => substr($key, 0, 32),
            'hmac' => substr($key, 32, 48),
        );

        // Add the Type Byte to end of JSON Text
        $text .= chr(6);

        // Generate Random Initialization Vector (IV)
        $iv_size = \openssl_cipher_iv_length('aes-256-cbc');
        $iv = \random_bytes($iv_size);

        // Encrypt the Text String and then add the IV after
        // the encrypted bytes/string.
        $cipher_text = \openssl_encrypt($text, 'aes-256-cbc', $keys['enc'], OPENSSL_RAW_DATA, $iv);
        $cipher_text .= $iv;

        // Calculate HMAC from the encrypted bytes and add it after the encrypted bytes
        $hmac = \hash_hmac('sha256', $cipher_text, $keys['hmac'], true);
        $cipher_text .= $hmac;

        // Return the Encrypted Text encoded as Base64
        return base64_encode($cipher_text);
    }

    // Encrypt and Decrypt
    $invalid_large_ints = $crypto->decrypt(encrypt_large_int_json($invalid_large_ints, $key), $key);
    $valid_large_ints = $crypto->decrypt(encrypt_large_int_json($valid_large_ints, $key), $key);
    $all_decrypted_text = json_encode($invalid_large_ints) . json_encode($valid_large_ints);

    // Compare on BigInts converted to Strings
    // NOTE - max size of a 64-bit Int is [9223372036854775807]
    // and min size of 64-Bit Int is [-9223372036854775808]
    // so these numbers will always be converted to a string.
    $data = array(
        'bigInt' => '19223372036854775807',
        'array' => array(
            '19223372036854775807',
            '29223372036854775807',
            '-19223372036854775807',
            '92233720368547758079223372036854775807'
        )
    );
    if ($invalid_large_ints !== $data) {
        return 'Failed on Big Int String Check';
    }

    // Compare on BigInts with max size for
    // the installed version of PHP.
    $data = array(
        'maxInt' => PHP_INT_MAX,
        'minInt' => ~PHP_INT_MAX,
    );
    if ($valid_large_ints !== $data) {
        return 'Failed on Big Int Value Check';
    }

    // All Tests Passed
    return sprintf('[Int Size: %d], [Len: %d], [sha256: %s]', PHP_INT_SIZE, len($all_decrypted_text), hash('sha256', $all_decrypted_text));
});

// Verify that Large Integers with Verification of Signed Data
$app->get('/verify-large-ints', function() {
    // Create a Random Key
    $csd = new \FastSitePHP\Security\Crypto\SignedData();
    $csd->exceptionOnError(true);
    $key = $csd->generateKey();

    // JSON Data Strings
    $invalid_large_ints = '{"bigInt":19223372036854775807,"array":[19223372036854775807,29223372036854775807,-19223372036854775807,92233720368547758079223372036854775807]}';
    $valid_large_ints = '{"maxInt":' . (string)PHP_INT_MAX . ',"minInt":' . (string)~PHP_INT_MAX . '}';

    // Manually create the signed JSON data with Ints
    // that are too large for any valid 64-bit int size.
    function sign_large_int_json($text, $key) {
        // Convert Hex Key to Bytes
        $key = hex2bin($key);

        // Add the Type to end of Based-64-URL Text
        $hash_text = \FastSitePHP\Encoding\Base64Url::encode($text) . '.j';

        // Sign with HMAC, Append Result, and Return
        $hmac = \hash_hmac('sha256', $hash_text, $key, true);
        return $hash_text . '.' . \FastSitePHP\Encoding\Base64Url::encode($hmac);
    }

    // Sign and Verify
    $invalid_large_ints = $csd->verify(sign_large_int_json($invalid_large_ints, $key), $key);
    $valid_large_ints = $csd->verify(sign_large_int_json($valid_large_ints, $key), $key);
    $all_verified_text = json_encode($invalid_large_ints) . json_encode($valid_large_ints);

    // Compare on BigInts converted to Strings
    $data = array(
        'bigInt' => '19223372036854775807',
        'array' => array(
            '19223372036854775807',
            '29223372036854775807',
            '-19223372036854775807',
            '92233720368547758079223372036854775807'
        )
    );
    if ($invalid_large_ints !== $data) {
        return 'Failed on Big Int String Check';
    }

    // Compare on BigInts with max size for
    // the installed version of PHP.
    $data = array(
        'maxInt' => PHP_INT_MAX,
        'minInt' => ~PHP_INT_MAX,
    );
    if ($valid_large_ints !== $data) {
        return 'Failed on Big Int Value Check';
    }

    // All Tests Passed
    return sprintf('[Int Size: %d], [Len: %d], [sha256: %s]', PHP_INT_SIZE, len($all_verified_text), hash('sha256', $all_verified_text));
});

// Create an empty (null byte) 10 MB file using a known encryption key and IV then
// decrypt the file. All files are verified. This function requires write access
// to the directory [data/unit-testing/temp]. Windows creates the file without
// using Command Line while all other OS's (Linux, Unix, Mac) use shell commands
// to encrypt and decrypt the file.
$app->get('/decrypt-known-file-with-key', function() {
    return decryptKnownFile();
});
$app->get('/decrypt-known-file-with-password', function() {
    return decryptKnownFile(true);
});

// Create a plaintext file, encrypt it using [encryptFile()] with a
// random key, then decrypt the file using [decryptFile()]. This will
// run with [processFilesWithCmdLine(true)] if not on Windows.
$app->get('/encrypt-and-decrypt-file', function() {
	return encryptAndDecryptFile(true, true);
});

// Same as above but with the [processFilesWithCmdLine(false)]
$app->get('/encrypt-and-decrypt-file-no-cmd', function() {
	return encryptAndDecryptFile(true, true, false);
});

// Create an encrypted file using contents from [encrypt()] then decrypt the file using [decryptFile()].
// In most cases [encrypt()] would be used with [decrypt()] and [encryptFile()] with [decryptFile()],
// however this function exists to confirm that [encrypt()] and [decryptFile()] are compatible.
$app->get('/encrypt-text-and-decrypt-file', function() {
	return encryptAndDecryptFile(false, true, false);
});

// Reverse of the above route, create an encrypted file using [encryptFile()] then read
// and decrypt the contents using [decrypt()].
$app->get('/encrypt-file-and-decrypt-text', function() {
	return encryptAndDecryptFile(true, false, false);
});

// This test does not test FastSitePHP itself but rather confirms that the
// critical function [hash_hmac()] works as expected. The HMAC function is
// used by the Crypto object both for digital signing using sign()/verify()
// and with encryption using encrypt()/decrypt(). This tests [sha224, sha256,
// sha384, and sha512]. This test would only fail if the version of PHP on
// the server being uses was compromised.
//
// Some of these tests are also similar to the tests from RFC 4868.
//
// @link: https://tools.ietf.org/html/rfc4231
// @link: http://nvlpubs.nist.gov/nistpubs/FIPS/NIST.FIPS.198-1.pdf
$app->get('/rfc-4231-hmac-test-vectors', function() {
    // Create a Encryption object as this will create functions
    // hex2bin() and bin2hex() if using PHP 5.3 and will
    // create hash_equals() when using PHP Versions below 5.6.
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();

    // Define Tests
    $tests = array(
        // Test Case 1:
        array(
            'key' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
            'data' => 'Hi There',
            'algo' => array(
                'sha224' => implode(array(
                    '896fb1128abbdf196832107cd49df33f',
                    '47b4b1169912ba4f53684b22',
                )),
                'sha256' => implode(array(
                    'b0344c61d8db38535ca8afceaf0bf12b',
                    '881dc200c9833da726e9376c2e32cff7',
                )),
                'sha384' => implode(array(
                    'afd03944d84895626b0825f4ab46907f',
                    '15f9dadbe4101ec682aa034c7cebc59c',
                    'faea9ea9076ede7f4af152e8b2fa9cb6',
                )),
                'sha512' => implode(array(
                    '87aa7cdea5ef619d4ff0b4241a1d6cb0',
                    '2379f4e2ce4ec2787ad0b30545e17cde',
                    'daa833b7d6b8a702038b274eaea3f4e4',
                    'be9d914eeb61f1702e696c203a126854',
                )),
            ),
        ),
        // Test Case 2:
        array(
            'key' => bin2hex('Jefe'),
            'data' => 'what do ya want for nothing?',
            'algo' => array(
                'sha224' => implode(array(
                    'a30e01098bc6dbbf45690f3a7e9e6d0f',
                    '8bbea2a39e6148008fd05e44',
                )),
                'sha256' => implode(array(
                    '5bdcc146bf60754e6a042426089575c7',
                    '5a003f089d2739839dec58b964ec3843',
                )),
                'sha384' => implode(array(
                    'af45d2e376484031617f78d2b58a6b1b',
                    '9c7ef464f5a01b47e42ec3736322445e',
                    '8e2240ca5e69e2c78b3239ecfab21649',
                )),
                'sha512' => implode(array(
                    '164b7a7bfcf819e2e395fbe73b56e0a3',
                    '87bd64222e831fd610270cd7ea250554',
                    '9758bf75c05a994a6d034f65f8f0e6fd',
                    'caeab1a34d4a6b4b636e070a38bce737',
                )),
            ),
        ),
        // Test Case 3:
        array(
            'key' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'data' => hex2bin('dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd'),
            'algo' => array(
                'sha224' => implode(array(
                    '7fb3cb3588c6c1f6ffa9694d7d6ad264',
                    '9365b0c1f65d69d1ec8333ea',
                )),
                'sha256' => implode(array(
                    '773ea91e36800e46854db8ebd09181a7',
                    '2959098b3ef8c122d9635514ced565fe',
                )),
                'sha384' => implode(array(
                    '88062608d3e6ad8a0aa2ace014c8a86f',
                    '0aa635d947ac9febe83ef4e55966144b',
                    '2a5ab39dc13814b94e3ab6e101a34f27',
                )),
                'sha512' => implode(array(
                    'fa73b0089d56a284efb0f0756c890be9',
                    'b1b5dbdd8ee81a3655f83e33b2279d39',
                    'bf3e848279a722c806b485a47e67c807',
                    'b946a337bee8942674278859e13292fb',
                )),
            ),
        ),
        // Test Case 4:
        array(
            'key' => '0102030405060708090a0b0c0d0e0f10111213141516171819',
            'data' => hex2bin('cdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcd'),
            'algo' => array(
                'sha224' => implode(array(
                    '6c11506874013cac6a2abc1bb382627c',
                    'ec6a90d86efc012de7afec5a',
                )),
                'sha256' => implode(array(
                    '82558a389a443c0ea4cc819899f2083a',
                    '85f0faa3e578f8077a2e3ff46729665b',
                )),
                'sha384' => implode(array(
                    '3e8a69b7783c25851933ab6290af6ca7',
                    '7a9981480850009cc5577c6e1f573b4e',
                    '6801dd23c4a7d679ccf8a386c674cffb',
                )),
                'sha512' => implode(array(
                    'b0ba465637458c6990e5a8c5f61d4af7',
                    'e576d97ff94b872de76f8050361ee3db',
                    'a91ca5c11aa25eb4d679275cc5788063',
                    'a5f19741120c4f2de2adebeb10a298dd',
                )),
            ),
        ),
        // Test Case 5:
        array(
            'key' => implode(array(
                '0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c',
                '0c0c0c0c',
            )),
            'data' => 'Test With Truncation',
            'truncate' => 128,
            'algo' => array(
                'sha224' => '0e2aea68a90c8d37c988bcdb9fca6fa8',
                'sha256' => 'a3b6167473100ee06e0c796c2955552b',
                'sha384' => '3abf34c3503b2a23a46efc619baef897',
                'sha512' => '415fad6271580a531d4179bc891d87a6',
            ),
        ),
        // Test Case 6:
        array(
            'key' => implode(array(
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaa',
            )),
            'data' => 'Test Using Larger Than Block-Size Key - Hash Key First',
            'algo' => array(
                'sha224' => implode(array(
                    '95e9a0db962095adaebe9b2d6f0dbce2',
                    'd499f112f2d2b7273fa6870e',
                )),
                'sha256' => implode(array(
                    '60e431591ee0b67f0d8a26aacbf5b77f',
                    '8e0bc6213728c5140546040f0ee37f54',
                )),
                'sha384' => implode(array(
                    '4ece084485813e9088d2c63a041bc5b4',
                    '4f9ef1012a2b588f3cd11f05033ac4c6',
                    '0c2ef6ab4030fe8296248df163f44952',
                )),
                'sha512' => implode(array(
                    '80b24263c7c1a3ebb71493c1dd7be8b4',
                    '9b46d1f41b4aeec1121b013783f8f352',
                    '6b56d037e05f2598bd0fd2215d6a1e52',
                    '95e64f73f63f0aec8b915a985d786598',
                )),
            ),
        ),
        // Test Case 7:
        array(
            'key' => implode(array(
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaa',
            )),
            'data' => 'This is a test using a larger than block-size key and a larger than block-size data. The key needs to be hashed before being used by the HMAC algorithm.',
            'algo' => array(
                'sha224' => implode(array(
                    '3a854166ac5d9f023f54d517d0b39dbd',
                    '946770db9c2b95c9f6f565d1',
                )),
                'sha256' => implode(array(
                    '9b09ffa71b942fcb27635fbcd5b0e944',
                    'bfdc63644f0713938a7f51535c3a35e2',
                )),
                'sha384' => implode(array(
                    '6617178e941f020d351e2f254e8fd32c',
                    '602420feb0b8fb9adccebb82461e99c5',
                    'a678cc31e799176d3860e6110c46523e',
                )),
                'sha512' => implode(array(
                    'e37b6a775dc87dbaa4dfa9f96e5e3ffd',
                    'debd71f8867289865df5a32d20cdc944',
                    'b6022cac3c4982b10d5eeb55c3e4de15',
                    '134676fb6de0446065c97440fa8c6a58',
                )),
            ),
        ),
    );

    // Run Tests
    $tests_count = 0;
    foreach ($tests as $test) {
        // Each test will have values for multiple hash functions
        foreach ($test['algo'] as $algo => $expected) {
            // Call Function
            $value = bin2hex(\hash_hmac($algo, $test['data'], hex2bin($test['key']), true));
            if (isset($test['truncate'])) {
                $value = substr($value, 0, ((int)$test['truncate'] / 4));
            }

            // Check Expected Result
            if ($value !== $expected) {
                echo sprintf('Test %d did not return the expected value:', $tests_count);
                echo '<br>';
                echo json_encode($test);
                echo '<br>';
                echo '<strong>Returned:</strong> ' . $value;
                echo '<br>';
                echo '<strong>Expected:</strong> ' . $expected;
                exit();
            }

            // Keep count of passed tests
            $tests_count++;
        }
    }

    // Result
    $json = json_encode(\FastSitePHP\Encoding\Utf8::encode($tests));
    return sprintf('[Tests: %d], [Len: %d], [sha256: %s]', $tests_count, len($json), hash('sha256', $json));
});

// This test does not test FastSitePHP itself but rather confirms that the
// critical function [hash_hmac()] works as expected. The HMAC function is
// used by the Crypto object both for digital signing using sign()/verify()
// and with encryption using encrypt()/decrypt(). This tests [sha256, sha384,
// and sha512]. This test would only fail if the version of PHP on the server
// being uses was compromised.
//
// Some of these tests are similar to the tests from RFC 4231.
//
// @link: https://tools.ietf.org/html/rfc4868
// @link: http://nvlpubs.nist.gov/nistpubs/FIPS/NIST.FIPS.198-1.pdf
$app->get('/rfc-4868-hmac-test-vectors', function() {
    // Create a Encryption object as this will create functions
    // hex2bin() and bin2hex() if using PHP 5.3 and will
    // create hash_equals() when using PHP Versions below 5.6.
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();

    // Define Tests
    $tests = array(
        // Test Case PRF-1:
        array(
            'key' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
            'data' => 'Hi There',
            'algo' => array(
                'sha256' => implode(array(
                    'b0344c61d8db38535ca8afceaf0bf12b',
                    '881dc200c9833da726e9376c2e32cff7',
                )),
                'sha384' => implode(array(
                    'afd03944d84895626b0825f4ab46907f',
                    '15f9dadbe4101ec682aa034c7cebc59c',
                    'faea9ea9076ede7f4af152e8b2fa9cb6',
                )),
                'sha512' => implode(array(
                    '87aa7cdea5ef619d4ff0b4241a1d6cb0',
                    '2379f4e2ce4ec2787ad0b30545e17cde',
                    'daa833b7d6b8a702038b274eaea3f4e4',
                    'be9d914eeb61f1702e696c203a126854',
                )),
            ),
        ),
        // Test Case PRF-2:
        array(
            'key' => bin2hex('Jefe'),
            'data' => 'what do ya want for nothing?',
            'algo' => array(
                'sha256' => implode(array(
                    '5bdcc146bf60754e6a042426089575c7',
                    '5a003f089d2739839dec58b964ec3843',
                )),
                'sha384' => implode(array(
                    'af45d2e376484031617f78d2b58a6b1b',
                    '9c7ef464f5a01b47e42ec3736322445e',
                    '8e2240ca5e69e2c78b3239ecfab21649',
                )),
                'sha512' => implode(array(
                    '164b7a7bfcf819e2e395fbe73b56e0a3',
                    '87bd64222e831fd610270cd7ea250554',
                    '9758bf75c05a994a6d034f65f8f0e6fd',
                    'caeab1a34d4a6b4b636e070a38bce737',
                )),
            ),
        ),
        // Test Case PRF-3:
        array(
            'key' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'data' => hex2bin('dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd'),
            'algo' => array(
                'sha256' => implode(array(
                    '773ea91e36800e46854db8ebd09181a7',
                    '2959098b3ef8c122d9635514ced565fe',
                )),
                'sha384' => implode(array(
                    '88062608d3e6ad8a0aa2ace014c8a86f',
                    '0aa635d947ac9febe83ef4e55966144b',
                    '2a5ab39dc13814b94e3ab6e101a34f27',
                )),
                'sha512' => implode(array(
                    'fa73b0089d56a284efb0f0756c890be9',
                    'b1b5dbdd8ee81a3655f83e33b2279d39',
                    'bf3e848279a722c806b485a47e67c807',
                    'b946a337bee8942674278859e13292fb',
                )),
            ),
        ),
        // Test Case PRF-4:
        array(
            'key' => '0102030405060708090a0b0c0d0e0f10111213141516171819',
            'data' => hex2bin('cdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcd'),
            'algo' => array(
                'sha256' => implode(array(
                    '82558a389a443c0ea4cc819899f2083a',
                    '85f0faa3e578f8077a2e3ff46729665b',
                )),
                'sha384' => implode(array(
                    '3e8a69b7783c25851933ab6290af6ca7',
                    '7a9981480850009cc5577c6e1f573b4e',
                    '6801dd23c4a7d679ccf8a386c674cffb',
                )),
                'sha512' => implode(array(
                    'b0ba465637458c6990e5a8c5f61d4af7',
                    'e576d97ff94b872de76f8050361ee3db',
                    'a91ca5c11aa25eb4d679275cc5788063',
                    'a5f19741120c4f2de2adebeb10a298dd',
                )),
            ),
        ),
        // Test Case PRF-5:
        array(
            'key' => implode(array(
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaa',
            )),
            'data' => 'Test Using Larger Than Block-Size Key - Hash Key First',
            'algo' => array(
                'sha256' => implode(array(
                    '60e431591ee0b67f0d8a26aacbf5b77f',
                    '8e0bc6213728c5140546040f0ee37f54',
                )),
                'sha384' => implode(array(
                    '4ece084485813e9088d2c63a041bc5b4',
                    '4f9ef1012a2b588f3cd11f05033ac4c6',
                    '0c2ef6ab4030fe8296248df163f44952',
                )),
                'sha512' => implode(array(
                    '80b24263c7c1a3ebb71493c1dd7be8b4',
                    '9b46d1f41b4aeec1121b013783f8f352',
                    '6b56d037e05f2598bd0fd2215d6a1e52',
                    '95e64f73f63f0aec8b915a985d786598',
                )),
            ),
        ),
        // Test Case PRF-6:
        array(
            'key' => implode(array(
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'aaaaaa',
            )),
            'data' => 'This is a test using a larger than block-size key and a larger than block-size data. The key needs to be hashed before being used by the HMAC algorithm.',
            'algo' => array(
                'sha256' => implode(array(
                    '9b09ffa71b942fcb27635fbcd5b0e944',
                    'bfdc63644f0713938a7f51535c3a35e2',
                )),
                'sha384' => implode(array(
                    '6617178e941f020d351e2f254e8fd32c',
                    '602420feb0b8fb9adccebb82461e99c5',
                    'a678cc31e799176d3860e6110c46523e',
                )),
                'sha512' => implode(array(
                    'e37b6a775dc87dbaa4dfa9f96e5e3ffd',
                    'debd71f8867289865df5a32d20cdc944',
                    'b6022cac3c4982b10d5eeb55c3e4de15',
                    '134676fb6de0446065c97440fa8c6a58',
                )),
            ),
        ),
        // Test Case AUTH256-1:
        array(
            'key' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
            'data' => 'Hi There',
            'algo' => array(
                'sha256' => implode(array(
                    '198a607eb44bfbc69903a0f1cf2bbdc5',
                    'ba0aa3f3d9ae3c1c7a3b1696a0b68cf7',
                )),
            ),
        ),
        // Test Case AUTH256-2:
        array(
            'key' => bin2hex('JefeJefeJefeJefeJefeJefeJefeJefe'),
            'data' => 'what do ya want for nothing?',
            'algo' => array(
                'sha256' => implode(array(
                    '167f928588c5cc2eef8e3093caa0e87c',
                    '9ff566a14794aa61648d81621a2a40c6',
                )),
            ),
        ),
        // Test Case AUTH256-3:
        array(
            'key' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'data' => hex2bin('dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd'),
            'algo' => array(
                'sha256' => implode(array(
                    'cdcb1220d1ecccea91e53aba3092f962',
                    'e549fe6ce9ed7fdc43191fbde45c30b0',
                )),
            ),
        ),
        // Test Case AUTH256-4:
        array(
            'key' => '0102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f20',
            'data' => hex2bin('cdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcd'),
            'algo' => array(
                'sha256' => implode(array(
                    '372efcf9b40b35c2115b1346903d2ef4',
                    '2fced46f0846e7257bb156d3d7b30d3f',
                )),
            ),
        ),
        // Test Case AUTH384-1:
        array(
            'key' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
            'data' => 'Hi There',
            'algo' => array(
                'sha384' => implode(array(
                    'b6a8d5636f5c6a7224f9977dcf7ee6c7',
                    'fb6d0c48cbdee9737a959796489bddbc',
                    '4c5df61d5b3297b4fb68dab9f1b582c2',
                )),
            ),
        ),
        // Test Case AUTH384-2:
        array(
            'key' => bin2hex('JefeJefeJefeJefeJefeJefeJefeJefeJefeJefeJefeJefe'),
            'data' => 'what do ya want for nothing?',
            'algo' => array(
                'sha384' => implode(array(
                    '2c7353974f1842fd66d53c452ca42122',
                    'b28c0b594cfb184da86a368e9b8e16f5',
                    '349524ca4e82400cbde0686d403371c9',
                )),
            ),
        ),
        // Test Case AUTH384-3:
        array(
            'key' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'data' => hex2bin('dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd'),
            'algo' => array(
                'sha384' => implode(array(
                    '809f439be00274321d4a538652164b53',
                    '554a508184a0c3160353e3428597003d',
                    '35914a18770f9443987054944b7c4b4a',
                )),
            ),
        ),
        // Test Case AUTH384-4:
        array(
            'key' => '0102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f200a0b0c0d0e0f10111213141516171819',
            'data' => hex2bin('cdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcd'),
            'algo' => array(
                'sha384' => implode(array(
                    '5b540085c6e6358096532b2493609ed1',
                    'cb298f774f87bb5c2ebf182c83cc7428',
                    '707fb92eab2536a5812258228bc96687',
                )),
            ),
        ),
        // Test Case AUTH512-1:
        array(
            'key' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
            'data' => 'Hi There',
            'algo' => array(
                'sha512' => implode(array(
                    '637edc6e01dce7e6742a99451aae82df',
                    '23da3e92439e590e43e761b33e910fb8',
                    'ac2878ebd5803f6f0b61dbce5e251ff8',
                    '789a4722c1be65aea45fd464e89f8f5b',
                )),
            ),
        ),
        // Test Case AUTH512-2:
        array(
            'key' => bin2hex('JefeJefeJefeJefeJefeJefeJefeJefeJefeJefeJefeJefeJefeJefeJefeJefe'),
            'data' => 'what do ya want for nothing?',
            'algo' => array(
                'sha512' => implode(array(
                    'cb370917ae8a7ce28cfd1d8f4705d614',
                    '1c173b2a9362c15df235dfb251b15454',
                    '6aa334ae9fb9afc2184932d8695e397b',
                    'fa0ffb93466cfcceaae38c833b7dba38',
                )),
            ),
        ),
        // Test Case AUTH512-3:
        array(
            'key' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'data' => hex2bin('dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd'),
            'algo' => array(
                'sha512' => implode(array(
                    '2ee7acd783624ca9398710f3ee05ae41',
                    'b9f9b0510c87e49e586cc9bf961733d8',
                    '623c7b55cebefccf02d5581acc1c9d5f',
                    'b1ff68a1de45509fbe4da9a433922655',
                )),
            ),
        ),
        // Test Case AUTH512-4:
        array(
            // The first item in the key array (first 16 bytes) are commented out
            // and only the last 64 bytes are used as the key. Then first 16 bytes
            // displayed in the Test Case are not used.
            'key' => implode(array(
                //'0a0b0c0d0e0f10111213141516171819',
                '0102030405060708090a0b0c0d0e0f10',
                '1112131415161718191a1b1c1d1e1f20',
                '2122232425262728292a2b2c2d2e2f30',
                '3132333435363738393a3b3c3d3e3f40',
            )),
            'data' => hex2bin('cdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcdcd'),
            'algo' => array(
                'sha512' => implode(array(
                    '5e6688e5a3daec826ca32eaea224eff5',
                    'e700628947470e13ad01302561bab108',
                    'b8c48cbc6b807dcfbd850521a685babc',
                    '7eae4a2a2e660dc0e86b931d65503fd2',
                )),
            ),
        ),
    );

    // Run Tests
    $tests_count = 0;
    foreach ($tests as $test) {
        // Each test will have values for multiple hash functions
        foreach ($test['algo'] as $algo => $expected) {
            // Call Function
            $value = bin2hex(\hash_hmac($algo, $test['data'], hex2bin($test['key']), true));

            // Check Expected Result
            if ($value !== $expected) {
                echo sprintf('Test %d did not return the expected value:', $tests_count);
                echo '<br>';
                echo json_encode($test);
                echo '<br>';
                echo '<strong>Returned:</strong> ' . $value;
                echo '<br>';
                echo '<strong>Expected:</strong> ' . $expected;
                exit();
            }

            // Keep count of passed tests
            $tests_count++;
        }
    }

    // Result
    $json = json_encode(\FastSitePHP\Encoding\Utf8::encode($tests));
    return sprintf('[Tests: %d], [Len: %d], [sha256: %s]', $tests_count, len($json), hash('sha256', $json));
});

// Test decrypting AES using (128, 192, and 256 bit keys) in CBC Mode
// (Cipher Block Chaining) against Standard Test Vectors from NIST SP 800-38A.
// Encryption is not tested here because FastSitePHP doesn't allow for the IV
// or NONCE to be specified as it is always generated randomly. Rather this
// function confirms that that the version of PHP running on the server is
// correctly built. This test is always expected to succeed as long as openssl
// is included with PHP.
//
// @link: http://nvlpubs.nist.gov/nistpubs/Legacy/SP/nistspecialpublication800-38a.pdf
$app->get('/nist-800-38a-aes-cbc-test-vectors', function() {
    // Variables:
    //   a:  Encryption Algorithm
    //   k:  Key
    //   iv: Initialization Vector
    //   ct: Cipher Text
    //   p:  Padding
    //   pt: Plain Text

    // Define Tests
    $tests = array(
        // F.2.2 CBC-AES128.Decrypt
        array(
            'a' => 'aes-128-cbc',
            'k' => '2b7e151628aed2a6abf7158809cf4f3c',
            'iv' => '000102030405060708090a0b0c0d0e0f',
            'ct' => implode(array(
                '7649abac8119b246cee98e9b12e9197d',
                '5086cb9b507219ee95db113a917678b2',
                '73bed6b8e3c1743b7116e69e22229516',
                '3ff1caa1681fac09120eca307586e1a7',
            )),
            'p' => '8cb82807230e1321d3fae00d18cc2012',
            'pt' => implode(array(
                '6bc1bee22e409f96e93d7e117393172a',
                'ae2d8a571e03ac9c9eb76fac45af8e51',
                '30c81c46a35ce411e5fbc1191a0a52ef',
                'f69f2445df4f9b17ad2b417be66c3710',
            )),
        ),
        // F.2.4 CBC-AES192.Decrypt
        array(
            'a' => 'aes-192-cbc',
            'k' => '8e73b0f7da0e6452c810f32b809079e562f8ead2522c6b7b',
            'iv' => '000102030405060708090a0b0c0d0e0f',
            'ct' => implode(array(
                '4f021db243bc633d7178183a9fa071e8',
                'b4d9ada9ad7dedf4e5e738763f69145a',
                '571b242012fb7ae07fa9baac3df102e0',
                '08b0e27988598881d920a9e64f5615cd',
            )),
            'p' => '612ccd79224b350935d45dd6a98f8176',
            'pt' => implode(array(
                '6bc1bee22e409f96e93d7e117393172a',
                'ae2d8a571e03ac9c9eb76fac45af8e51',
                '30c81c46a35ce411e5fbc1191a0a52ef',
                'f69f2445df4f9b17ad2b417be66c3710',
            )),
        ),
        // F.2.6 CBC-AES256.Decrypt
        array(
            'a' => 'aes-256-cbc',
            'k' => '603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4',
            'iv' => '000102030405060708090a0b0c0d0e0f',
            'ct' => implode(array(
                'f58c4c04d6e5f1ba779eabfb5f7bfbd6',
                '9cfc4e967edb808d679f777bc6702c7d',
                '39f23369a9d9bacfa530e26304231461',
                'b2eb05e2c39be9fcda6c19078c6a9d1b',
            )),
            'p' => '3f461796d6b0d6b2e0c2a72b4d80e644',
            'pt' => implode(array(
                '6bc1bee22e409f96e93d7e117393172a',
                'ae2d8a571e03ac9c9eb76fac45af8e51',
                '30c81c46a35ce411e5fbc1191a0a52ef',
                'f69f2445df4f9b17ad2b417be66c3710',
            )),
        ),
    );

    // Find Padding Values when adding new Test Vectors
    // FastSitePHP uses PKCS #7 padding for CBC Mode however
    // NIST test vectors do not list padding values as they
    // are defined using full blocks. Because of this padding
    // values need to be deteremine during development and added
    // manually to the above tests.
    foreach ($tests as $test) {
        if ($test['p'] === '') {
            $ct = bin2hex(openssl_encrypt(hex2bin($test['pt']), $test['a'], hex2bin($test['k']), OPENSSL_RAW_DATA, hex2bin($test['iv'])));
            // 16 bytes or (32 in hex)
            var_dump(substr($ct, 0, strlen($ct) - 32));
            echo '<br>';
            var_dump(substr($ct, -32));
            exit();
        }
    }

    // Test and return results
    $tests_count = runDecryptionTestVectors($tests);
    $json = json_encode($tests);
    return sprintf('[Tests: %d], [Len: %d], [sha256: %s]', $tests_count, len($json), hash('sha256', $json));
});

// Test decrypting AES using (128, 192, and 256 bit keys) in CTR Mode
// (Counter Mode) against Standard Test Vectors from NIST SP 800-38A.
// Encryption is not tested here because FastSitePHP doesn't allow for the IV
// or NONCE to be specified as it is always generated randomly. Rather this
// function confirms that that the version of PHP running on the server is
// correctly built. This test is always expected to succeed as long as openssl
// is included with PHP and the version of PHP iss 5.5 or higher. If using PHP
// 5.3 or 5.4 then this test is simply skipped.
//
// @link: http://nvlpubs.nist.gov/nistpubs/Legacy/SP/nistspecialpublication800-38a.pdf
$app->get('/nist-800-38a-aes-ctr-test-vectors', function() {
    // Variables:
    //   a:  Encryption Algorithm
    //   k:  Key
    //   iv: Initialization Vector or nonce (number once)
    //   ct: Cipher Text
    //   pt: Plain Text

    // Skip this test unless from PHP 5.5 or greater. This does not impact whether
    // FastSitePHP and the Crypto Object will work on a server but rather that
    // this specific block mode will not be available.
    if (PHP_VERSION_ID < 50500) {
        return 'Test Skipped, PHP Version earlier than 5.5';
    }

    // Define Tests
    $tests = array(
        // F.5.2 CTR-AES128.Decrypt
        array(
            'a' => 'aes-128-ctr',
            'k' => '2b7e151628aed2a6abf7158809cf4f3c',
            'iv' => 'f0f1f2f3f4f5f6f7f8f9fafbfcfdfeff',
            'ct' => implode(array(
                '874d6191b620e3261bef6864990db6ce',
                '9806f66b7970fdff8617187bb9fffdff',
                '5ae4df3edbd5d35e5b4f09020db03eab',
                '1e031dda2fbe03d1792170a0f3009cee',
            )),
            'pt' => implode(array(
                '6bc1bee22e409f96e93d7e117393172a',
                'ae2d8a571e03ac9c9eb76fac45af8e51',
                '30c81c46a35ce411e5fbc1191a0a52ef',
                'f69f2445df4f9b17ad2b417be66c3710',
            )),
        ),
        // F.5.4 CTR-AES192.Decrypt
        array(
            'a' => 'aes-192-ctr',
            'k' => '8e73b0f7da0e6452c810f32b809079e562f8ead2522c6b7b',
            'iv' => 'f0f1f2f3f4f5f6f7f8f9fafbfcfdfeff',
            'ct' => implode(array(
                '1abc932417521ca24f2b0459fe7e6e0b',
                '090339ec0aa6faefd5ccc2c6f4ce8e94',
                '1e36b26bd1ebc670d1bd1d665620abf7',
                '4f78a7f6d29809585a97daec58c6b050',
            )),
            'pt' => implode(array(
                '6bc1bee22e409f96e93d7e117393172a',
                'ae2d8a571e03ac9c9eb76fac45af8e51',
                '30c81c46a35ce411e5fbc1191a0a52ef',
                'f69f2445df4f9b17ad2b417be66c3710',
            )),
        ),
        // F.5.6 CTR-AES256.Decrypt
        array(
            'a' => 'aes-256-ctr',
            'k' => '603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4',
            'iv' => 'f0f1f2f3f4f5f6f7f8f9fafbfcfdfeff',
            'ct' => implode(array(
                '601ec313775789a5b7a7f504bbf3d228',
                'f443e3ca4d62b59aca84e990cacaf5c5',
                '2b0930daa23de94ce87017ba2d84988d',
                'dfc9c58db67aada613c2dd08457941a6',
            )),
            'pt' => implode(array(
                '6bc1bee22e409f96e93d7e117393172a',
                'ae2d8a571e03ac9c9eb76fac45af8e51',
                '30c81c46a35ce411e5fbc1191a0a52ef',
                'f69f2445df4f9b17ad2b417be66c3710',
            )),
        ),
    );

    // Test and return results
    $tests_count = runDecryptionTestVectors($tests);
    $json = json_encode($tests);
    return sprintf('[Tests: %d], [Len: %d], [sha256: %s]', $tests_count, len($json), hash('sha256', $json));
});


// Test decrypting AES using a 256 bit key in GCM Mode (Galois/Counter Mode)
// against standard validation tests from NIST SP 800-38D. The full test suite
// is very large and contains options not supported by FastSitePHP such as small
// Auth Tags (FastSitePHP only supports 128 bit Auth Tags), so rather than testing
// the full file a handful of tests are run using plain text sizes using several
// settings - block size of AES and using a larger block size, plus using AAD and
// not using AAD. Unless the build of PHP was custom and compromised for encryption
// these tests should always pass.
//
// @Link: http://csrc.nist.gov/groups/STM/cavp/block-cipher-modes.html
// All test vectors used here are from Zip Download [gcmtestvectors.zip]
// and file [gcmDecrypt256.rsp]
$app->get('/nist-800-38d-aes-gcm-test-vectors', function() {
    // Variables:
    //   a:   Encryption Algorithm
    //   k:   Key
    //   iv:  Initialization Vector
    //   ct:  Cipher Text
    //   pt:  Plain Text
    //   t:   Auth Tag
    //   aad: Additional Authenticated Data

    // Skip this test unless from PHP 7.1 or greater. This does not impact whether
    // FastSitePHP and the Crypto Object will work on a server but rather that
    // this specific block mode will not be available.
    if (PHP_VERSION_ID < 70100) {
        return 'Test Skipped, PHP Version earlier than 7.1';
    }

    // Define Tests
    $tests = array(
        /*
        Testing Section (starts at line 4417 in [gcmDecrypt256.rsp]):

        [Keylen = 256]
        [IVlen = 96]
        [PTlen = 128]
        [AADlen = 0]
        [Taglen = 128]
        */
        // Count = 0
        array(
            'a' => 'aes-256-gcm',
            'k' => '4c8ebfe1444ec1b2d503c6986659af2c94fafe945f72c1e8486a5acfedb8a0f8',
            'iv' => '473360e0ad24889959858995',
            'ct' => 'd2c78110ac7e8f107c0df0570bd7c90c',
            'aad' => '',
            't' => 'c26a379b6d98ef2852ead8ce83a833a7',
            'pt' => '7789b41cb3ee548814ca0b388c10b343',
        ),
        // Count = 1
        array(
            'a' => 'aes-256-gcm',
            'k' => '3934f363fd9f771352c4c7a060682ed03c2864223a1573b3af997e2ababd60ab',
            'iv' => 'efe2656d878c586e41c539c4',
            'ct' => 'e0de64302ac2d04048d65a87d2ad09fe',
            'aad' => '',
            't' => '33cbd8d2fb8a3a03e30c1eb1b53c1d99',
            'pt' => '697aff2d6b77e5ed6232770e400c1ead',
        ),
        // Count = 2
        array(
            'a' => 'aes-256-gcm',
            'k' => 'c997768e2d14e3d38259667a6649079de77beb4543589771e5068e6cd7cd0b14',
            'iv' => '835090aed9552dbdd45277e2',
            'ct' => '9f6607d68e22ccf21928db0986be126e',
            'aad' => '',
            't' => 'f32617f67c574fd9f44ef76ff880ab9f',
            'pt' => null,
        ),
        // Count = 3
        array(
            'a' => 'aes-256-gcm',
            'k' => 'f05871fa6fced6d88fb68b0f2cd8b3ff6298901c38799be6be33e7d6193a18e6',
            'iv' => '1424ef6d15967c05509e50f2',
            'ct' => '8492fe9e53510d96d9c2aa00e4967112',
            'aad' => '',
            't' => '33656dd6b89763313b4fd0105f506310',
            'pt' => null,
        ),
        // Count = 4
        array(
            'a' => 'aes-256-gcm',
            'k' => '0f8900d95592c2079c447204321d8bf9e0ddb08bd568d51bd503fd7813db193f',
            'iv' => '5daeb9365de9c3274c73a3c7',
            'ct' => '8cd3a91f164565dd58b36a5044918115',
            'aad' => '',
            't' => '41ec4b3638f6cf66efd46add73d14498',
            'pt' => 'c0a49675d098728a38831008bddc64a3',
        ),
        // Count = 5
        array(
            'a' => 'aes-256-gcm',
            'k' => '7fc66fdb3cdda946a3775f001268e35e53143d31bc5bf8b95a00791aa59a272c',
            'iv' => 'e88105f9e7c35efbe2f589a8',
            'ct' => '84253f31cb8d2f97b85f83d346d07f47',
            'aad' => '',
            't' => '2788640ba7ebe6977bc84ba516c47e67',
            'pt' => '25b310e144db4f4d874ba77668902c3e',
        ),
        // Count = 6
        array(
            'a' => 'aes-256-gcm',
            'k' => '1759cac2024a3ddd5e561ca5a9b91c3c4e64c722381bd30f3f26851faf16c7e8',
            'iv' => '656ca7bd2cb82ab7a3d6b268',
            'ct' => '8d9530d3ac659240ddd8b77155cfc2f7',
            'aad' => '',
            't' => '6000924fb29f7d2588866371b131ef5d',
            'pt' => null,
        ),
        // Count = 7
        array(
            'a' => 'aes-256-gcm',
            'k' => 'a33a97cf788c10b8bfab5825cc4d49e7dd586efa0539b5ccc0bf0b005ec59284',
            'iv' => '812beff898f7850bcdd774f9',
            'ct' => 'd89aec5115cec627b8fe48e29e9d1c4b',
            'aad' => '',
            't' => 'cfdf364d4e131cbe1975a904995b4814',
            'pt' => '4bfdebcafe92b09dfec4805234eb272a',
        ),
        // Count = 8
        array(
            'a' => 'aes-256-gcm',
            'k' => '99e96497f227e1e99f7a30f3b17e622265c15575f7c075833142fa89d72d3e77',
            'iv' => 'e06b9202379d8bb374ae39c2',
            'ct' => 'bc3abf931b28146cf438eee55b491760',
            'aad' => '',
            't' => '14ca7e834e7f461bd3f41d8adb3255ce',
            'pt' => null,
        ),
        // Count = 9
        array(
            'a' => 'aes-256-gcm',
            'k' => 'd75554d59778242bcdf14b0ced142d1a530a3b4daee1c6f37a44c2af994d537b',
            'iv' => 'b9e3f8cc4617f111af038cd5',
            'ct' => 'aec5ecf970b8b99231932931562718c4',
            'aad' => '',
            't' => 'e5b3cfc3cafbd449fc2b0bd99bbe7dc8',
            'pt' => 'd4cf089074aa82383155630d471f1c6c',
        ),
        // Count = 10
        array(
            'a' => 'aes-256-gcm',
            'k' => '1327a2b4a3d2a6b54a78e55ebb213f0819233ac139c63f26e0eee887237add65',
            'iv' => '666c33d9a64ca627d5cb3106',
            'ct' => '658023c008e40bf84d85619e1d86975b',
            'aad' => '',
            't' => '59304bc134c808e342c13b84f7593603',
            'pt' => 'c71c78eeb11d3a5f270706b9b7ebfbd0',
        ),
        // Count = 11
        array(
            'a' => 'aes-256-gcm',
            'k' => '84d212aa45110ed3e81f6c04a80c7ea2b38f3e66db5fe61a088411cc777b0aab',
            'iv' => '69baab39ccd13ecb62a0036c',
            'ct' => '8703d3d4fbdca78f51e451f13b7662f9',
            'aad' => '',
            't' => '05b15c2f041baae61bc4a99a3c7460dd',
            'pt' => null,
        ),
        // Count = 12
        array(
            'a' => 'aes-256-gcm',
            'k' => '5dfa8574b70c79d39fa30badb80955ca0aa80c451e960a64b7baec71105277d2',
            'iv' => '147ea967202a0ff648ef45fb',
            'ct' => '3186d08897e925665d29010a61c71d67',
            'aad' => '',
            't' => 'a724f1ad84b0637349e591f5538aadf8',
            'pt' => 'ea13b8fd94c3d55f38e40bacb7367eb7',
        ),
        // Count = 13
        array(
            'a' => 'aes-256-gcm',
            'k' => '0e2803b03ed22b6449cb2761a0fed8316329f948d6644903bca55d4e8cae796b',
            'iv' => '94949f64e2112c24a5153b07',
            'ct' => '2c03b20355e7895cd8ec6130789be051',
            'aad' => '',
            't' => '7ccfd0b1b14183aa6594a8fb9b74889d',
            'pt' => '6246af8c35814215cc63e8d772573987',
        ),
        // Count = 14
        array(
            'a' => 'aes-256-gcm',
            'k' => '5152f92330de18e816c836b638602ed3d5abcac821673c76b4eba4c574fecbca',
            'iv' => '36b2ba93c0a15255c64e77d6',
            'ct' => '39320f651d7c27ff7d1916b9bc28026b',
            'aad' => '',
            't' => '9d84ad08e303fec9295c94305e416beb',
            'pt' => '737fceddbf726b7ff7fbf3e6922a701f',
        ),
        /*
        Testing Section (starts at line 14119 in [gcmDecrypt256.rsp]):

        [Keylen = 256]
        [IVlen = 96]
        [PTlen = 256]
        [AADlen = 128]
        [Taglen = 128]
        */
        // Count = 0
        array(
            'a' => 'aes-256-gcm',
            'k' => '22ab50af434ac76377118aaa014c008d25e766b23e2d0488c1b2a3b720a9e89e',
            'iv' => '123f018807b3f5368c38b1dd',
            'ct' => 'b05cca6533e1f81c9751b42cd32158ac37841afae09eafde4cf51458ed6d234f',
            'aad' => '1c56bf07fdbddc8eebcd0712ab2c16ad',
            't' => 'f082c6743dca40f7b98ed44de872d46c',
            'pt' => null,
        ),
        // Count = 1
        array(
            'a' => 'aes-256-gcm',
            'k' => 'f0eaf7b41b42f4500635bc05d9cede11a5363d59a6288870f527bcffeb4d6e04',
            'iv' => '18f316781077a595c72d4c07',
            'ct' => '7a1b61009dce6b7cd4d1ea0203b179f1219dd5ce7407e12ea0a4c56c71bb791b',
            'aad' => '42cade3a19204b7d4843628c425c2375',
            't' => '4419180b0b963b7289a4fa3f45c535a3',
            'pt' => '400fb5ef32083b3abea957c4f068abad50c8d86bbf9351fa72e7da5171df38f9',
        ),
        // Count = 2
        array(
            'a' => 'aes-256-gcm',
            'k' => '046a2e5ef707f319e86aea115bc4c9ac4803ef17afb74ba13238e11213da981a',
            'iv' => 'c00967f52771b66a252ea978',
            'ct' => 'ecf55ee6ff85cac359767edebed91f61a3615a8058325ad08e8f8c4b6b08bddc',
            'aad' => 'd4152360ddf17d836ff0c5ac6d5bcf62',
            't' => 'bd55502939041b32224998318d39a2d5',
            'pt' => null,
        ),
        // Count = 3
        array(
            'a' => 'aes-256-gcm',
            'k' => 'b8b524f0bf5770242b703f64be5d6a57ef0457f15900fc4bac061fe5b615fcea',
            'iv' => 'de5a425aeb0aa1f71bbddcea',
            'ct' => 'f25cfad9f871263a26bf3f518fdd17ff4a386f0beeea84b7ad02e8c9e93a86c6',
            'aad' => '061c9a235237d87e8f750b2239f23e67',
            't' => '9d198f6bd17d0bb87767107973342f1f',
            'pt' => null,
        ),
        // Count = 4
        array(
            'a' => 'aes-256-gcm',
            'k' => 'cd24256f5a5e7f509736620803f03fa3b1cc06abc668a2c63d4cfc1482cc03b4',
            'iv' => 'eb221042533d8275797e9ce9',
            'ct' => '7b1d9fbac580c6bb7f9f87d658311e4116902e122465edd7f63729d4767ab66c',
            'aad' => '15f2343e28c375d938e20a19a282baa1',
            't' => '7b9a640eb024124b3e7bd5b15c279c9a',
            'pt' => null,
        ),
        // Count = 5
        array(
            'a' => 'aes-256-gcm',
            'k' => 'a68f043e1336dfa26625d18e40bdc595b54a3e458ac01d8f3c0f859c47a2df3f',
            'iv' => 'ff29fff9a2abcbd1ea4951d7',
            'ct' => 'd7a8e9ec7860fb7e04bba31281e7feb33bc996fd695347ddf2e49f699760e68b',
            'aad' => 'f96e3e30f9f0de510f0164d4c7637b05',
            't' => '3f3a0eee090d684a61a16950d0b88379',
            'pt' => '82d64a95b3a4b5ae5746312139d21f440d96611d92fb7ae4ab0d690857071e9a',
        ),
        // Count = 6
        array(
            'a' => 'aes-256-gcm',
            'k' => '7ade912c6ee958abeca8e675ba24c9a64ddad6e17635ea0bf1b1daaf429da095',
            'iv' => 'a01bf04026af5b1afa172273',
            'ct' => '622058631c51da95ba7a7681e90e4b815c7bb5611488397deb3e91a3e3802d93',
            'aad' => '6e846274b483e7e79796bfdf0b957400',
            't' => '9d8132267966e3a4af82c570fa2eb39c',
            'pt' => null,
        ),
        // Count = 7
        array(
            'a' => 'aes-256-gcm',
            'k' => '1d1c9437fe5bf33b570f9695cd4abc8d32620c9f9a64e594288df64f123c4a10',
            'iv' => 'b32781b3a7fc18871f50d954',
            'ct' => 'b9332c5bf1e09339532e0020b28335b02b99d78f51f4b0f6a51e58baee24d319',
            'aad' => 'c1a43164c5f773593e01b09ccd9b347c',
            't' => '53a5ea9a2abe5aa0a7df15c0d492d7f8',
            'pt' => '7e873d0c41dfe32e80c8d9d62895b8b0787e575f7f718928f6113aae41290592',
        ),
        // Count = 8
        array(
            'a' => 'aes-256-gcm',
            'k' => '232f6108d4e50982d1694a6f0d72fa781b0edd642fcdf3fa7dc253608f029af4',
            'iv' => '0a4ddec0fdea208bc5b19f41',
            'ct' => '77f2ae2d9710f302f0051651a354d156daf7cb35c33919ead0091ad92611f126',
            'aad' => '701e1721ba2cbb74d4d5db6a058251ef',
            't' => '6be0ef2adf1620af8858c3d2b84e15e2',
            'pt' => null,
        ),
        // Count = 9
        array(
            'a' => 'aes-256-gcm',
            'k' => '166ea427bc90dbdc318a56d61480f9dd0552337207754bea6a3cd107ce2b560f',
            'iv' => '6f14958b2d69fea5b357446d',
            'ct' => '344bfeee0a19c73816481921365dd2df0f512561425451a0a2cea062786b34ce',
            'aad' => '19a53f575ee61934fbb75f31d406c23e',
            't' => '6b55ee046423c87bfd9a9da137d0bb18',
            'pt' => null,
        ),
        // Count = 10
        array(
            'a' => 'aes-256-gcm',
            'k' => 'cdfb9f40864cd75ef1786d61f3081b7d59cb4076e21db557853f39bb8653e251',
            'iv' => 'ea9a4e22be53c75d6e6d1c40',
            'ct' => '87f867251f9f0ab6973f036a7f8fa118aabbd5c0d861dcd6e5035db156715ab6',
            'aad' => 'c535dd1486015346136aca2257ff174d',
            't' => 'e18acd7a6321c738550ade80a0c4f5e7',
            'pt' => null,
        ),
        // Count = 11
        array(
            'a' => 'aes-256-gcm',
            'k' => 'd1a0553d07a1df213f0cd858d620c0db72d59eefa784d07c396a26dd7bc8eb29',
            'iv' => '8f3987dc1fbdc338bdc82f75',
            'ct' => '65a91a30efee1db4091fafe4d3f38ba7b36d3da4653748037d53e7c70e15aa81',
            'aad' => '487f37ab5630ccc52145782f81d84feb',
            't' => '065209cd87969eb1417e04b76fa0d892',
            'pt' => null,
        ),
        // Count = 12
        array(
            'a' => 'aes-256-gcm',
            'k' => 'ad422d8e8ca2ee13f58c781c551d29d34b11a1995550f54fb49caedc46723009',
            'iv' => '6863f6cbe4fbfc6e95daf4b6',
            'ct' => '08aaebf4a062e7ca31d9394a5c0d1d4e99f8bfdbef15566ab45ef1e4438ab835',
            'aad' => '62ae15a47d594810e74c514e9e472401',
            't' => 'c4c00182a44c29be46c95bc88f037f20',
            'pt' => null,
        ),
        // Count = 13
        array(
            'a' => 'aes-256-gcm',
            'k' => 'ed885d466003e4649b01245b64095492d45948670198cfaeac4d53674ed1e1df',
            'iv' => '5ca399e862ca014c6d87c73e',
            'ct' => '09ff289764179ec8032b5346398bf99515fc770d82f8e7e6242c621bdcc14c30',
            'aad' => 'bfe13d23eaed370a7e32e5298a3f0cd0',
            't' => '9f7fb6f09a6d93545a76b3aeac1b5d22',
            'pt' => null,
        ),
        // Count = 14
        array(
            'a' => 'aes-256-gcm',
            'k' => 'fc00d8caabdbed37fb5f10d27d280c86aee9493ef8add8bd341810a8ef9a1f78',
            'iv' => '068cc31a45eacf98a9d703ff',
            'ct' => '7da9102a8a84be76595233be27b7a7343d2b8b2d918708ff90f7b67504df4f97',
            'aad' => '0ab5cea9689f176ecd956b337c35c90a',
            't' => '0db97bbd6342c02425c499a9a3124e15',
            'pt' => null,
        ),
    );

    // Test and return results
    $tests_count = runDecryptionTestVectors($tests);
    $json = json_encode($tests);
    return sprintf('[Tests: %d], [Len: %d], [sha256: %s]', $tests_count, len($json), hash('sha256', $json));
});

$app->get('/encryption-class-errors', function() use ($app) {
    // Setup Crypto Objects and Create Random Keys
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();
    $key = $crypto
        ->exceptionOnError(true)
        ->generateKey();
    $key_alt = $crypto->generateKey();

    $crypto2 = new \FastSitePHP\Security\Crypto\Encryption();
    $key2 = $crypto2
        ->encryptThenAuthenticate(false)
        ->exceptionOnError(true)
        ->generateKey();
    $key2_alt = $crypto2->generateKey();

    $crypto3 = new \FastSitePHP\Security\Crypto\Encryption();
    $crypto3
        ->dataFormat('string-only')
        ->exceptionOnError(true)
        ->returnFormat('base64');

    $crypto4 = new \FastSitePHP\Security\Crypto\Encryption();
    $crypto4
        ->exceptionOnError(true)
        ->returnFormat('hex');

    $crypto5 = new \FastSitePHP\Security\Crypto\Encryption();
    $crypto5->keyType('password');

    // Ciphertext for Testing
    $ciphertext = $crypto->encrypt('Test', $key);
    $ciphertext2 = $crypto2->encrypt('Test', $key2);

    // Define a function used to generate encrypted text for specific errors
    // and define the text to use (these tests fail because of errors
    // related to [dataFormat = 'type-byte'])
    function generateInvalidEncryptedText($plaintext, $key) {
        $iv_size = \openssl_cipher_iv_length('aes-256-cbc');
        $iv = \FastSitePHP\Security\Crypto\Random::bytes($iv_size);
        $encrypted_bytes = \openssl_encrypt($plaintext, 'aes-256-cbc', \hex2bin($key), OPENSSL_RAW_DATA, $iv);
        $encrypted_bytes = $encrypted_bytes . $iv;
        return base64_encode($encrypted_bytes);
    }
    $ciphertext_null_error = generateInvalidEncryptedText('test' . chr(0), $key2);
    $ciphertext_bool_error = generateInvalidEncryptedText('yes' . chr(5), $key2);
    $ciphertext_json_error = generateInvalidEncryptedText('}{' . chr(6), $key2);
    $ciphertext_unknown_error = generateInvalidEncryptedText('test' . chr(7), $key2);

    // Define Error Tests
    $tests_count = 0;
    $error_text = array();
    $error_text_old_php = array();
    $tests = array(
        array(
            'func' => 'hashingAlgorithm',
            'param' => 123,
            'expected' => 'The parameter for [FastSitePHP\Security\Crypto\Encryption->hashingAlgorithm()] must be a string or null but was instead a [integer].',
        ),
        array(
            'func' => 'hashingAlgorithm',
            'param' => 'unknown',
            'expected' => 'The hashing algorithm [FastSitePHP\Security\Crypto\Encryption->hashingAlgorithm(\'unknown\')] is not available on this computer.',
        ),
        array(
            'func' => 'keyType',
            'param' => 123,
            'expected' => 'The specified value [FastSitePHP\Security\Crypto\Encryption->keyType()] is not the correct type. Expected a string with one of the valid options: [key, password], but received at [integer].',
        ),
        array(
            'func' => 'keyType',
            'param' => 'unknown',
            'expected' => 'The specified value for [FastSitePHP\Security\Crypto\Encryption->keyType(\'unknown\')] is not valid. Valid options are [key, password].',
        ),
        array(
            'func' => 'returnFormat',
            'param' => 123,
            'expected' => 'The specified value [FastSitePHP\Security\Crypto\Encryption->returnFormat()] is not the correct type. Expected a string with one of the valid options: [base64, base64url, hex, bytes], but received at [integer].',
        ),
        array(
            'func' => 'returnFormat',
            'param' => 'unknown',
            'expected' => 'The specified value for [FastSitePHP\Security\Crypto\Encryption->returnFormat(\'unknown\')] is not valid. Valid options are [base64, base64url, hex, bytes].',
        ),
        array(
            'func' => 'dataFormat',
            'param' => 123,
            'expected' => 'The specified value [FastSitePHP\Security\Crypto\Encryption->dataFormat()] is not the correct type. Expected a string with one of the valid options: [type-byte, string-only], but received at [integer].',
        ),
        array(
            'func' => 'dataFormat',
            'param' => 'unknown',
            'expected' => 'The specified value for [FastSitePHP\Security\Crypto\Encryption->dataFormat(\'unknown\')] is not valid. Valid options are [type-byte, string-only].',
        ),
        array(
            'func' => 'keySizeEnc',
            'param' => 'test',
            'expected' => 'When setting a key length from [FastSitePHP\Security\Crypto\Encryption->keySizeEnc()] the value must be an integer but was instead a [string].',
        ),
        array(
            'func' => 'keySizeEnc',
            'param' => 500,
            'expected' => 'When setting a key length from [FastSitePHP\Security\Crypto\Encryption->keySizeEnc()] the value must be divisible by 8, for example 256 or 512. This function was called with [500].',
        ),
        array(
            'func' => 'keySizeEnc',
            'param' => 0,
            'expected' => 'When setting a key length from [FastSitePHP\Security\Crypto\Encryption->keySizeEnc()] value for must be key size of at least 8 bits.',
        ),
        array(
            'object' => 'Crypto_Static',
            'func' => 'encrypt',
            'param' => 'test',
            'expected' => 'Missing Application Config Value or Environment Variable for [ENCRYPTION_KEY]. If this error is not clear then please review FastSitePHP documentation and examples on how to use [FastSitePHP\Security\Crypto::encrypt()].',
        ),
        array(
            'object' => 'Crypto_Static',
            'func' => 'decrypt',
            'param' => 'test',
            'expected' => 'Missing Application Config Value or Environment Variable for [ENCRYPTION_KEY]. If this error is not clear then please review FastSitePHP documentation and examples on how to use [FastSitePHP\Security\Crypto::decrypt()].',
        ),
        array(
            'func' => 'encrypt',
            'param' => 'test',
            'param2' => 'test',
            'expected' => 'Invalid Key. The key must be a hexadecimal encoded string value and the function was called with a non-hex key.',
        ),
        array(
            'func' => 'encrypt',
            'param' => 'test',
            'param2' => 'ffff',
            'expected' => 'Invalid Key for encryption. The key required using the current settings must be a hex encoded string that is 128 characters in length (64 bytes, 512 bits) but was instead 4 hex characters. Required key size is determined from the [FastSitePHP\Security\Crypto\Encryption] class using properties [encryptThenAuthenticate, encryptionAlgorithm, hashingAlgorithm, and keySizeEnc].',
        ),
        array(
            'func' => 'decrypt',
            'param' => $ciphertext,
            'param2' => 'test',
            'expected' => 'Invalid Key. The key must be a hexadecimal encoded string value and the function was called with a non-hex key.',
        ),
        array(
            'func' => 'decrypt',
            'param' => $ciphertext,
            'param2' => 'ffff',
            'expected' => 'Invalid Key for encryption. The key required using the current settings must be a hex encoded string that is 128 characters in length (64 bytes, 512 bits) but was instead 4 hex characters. Required key size is determined from the [FastSitePHP\Security\Crypto\Encryption] class using properties [encryptThenAuthenticate, encryptionAlgorithm, hashingAlgorithm, and keySizeEnc].',
        ),
        array(
            'object' => 'crypto2',
            'func' => 'decrypt',
            'param' => $ciphertext_null_error,
            'param2' => $key2,
            'expected' => 'Decryption was successful however the decrypted data did not match a null value. It\'s likely that the data was encrypted with another program or a software library which is not compatible with this class.',
        ),
        array(
            'object' => 'crypto2',
            'func' => 'decrypt',
            'param' => $ciphertext_bool_error,
            'param2' => $key2,
            'expected' => 'Decryption was successful however the decrypted data did not match a boolean value of either 0 or 1. It\'s likely that the data was encrypted with another program or a software library which is not compatible with this class.',
        ),
        array(
            'object' => 'crypto2',
            'func' => 'decrypt',
            'param' => $ciphertext_json_error,
            'param2' => $key2,
            'expected' => 'Decryption was successful however the decrypted data could not be parsed as valid JSON. It\'s likely that the data was encrypted with another program or a software library which is not compatible with this class. Json Decode Error: Error decoding JSON Data: Syntax error',
        ),
        array(
            'object' => 'crypto2',
            'func' => 'decrypt',
            'param' => $ciphertext_unknown_error,
            'param2' => $key2,
            'expected' => 'Decryption was successful however the decrypted data has an unknown type of [Byte 7]. It\'s likely that the data was encrypted with another program or a software library which is not compatible with this class.',
        ),
        array(
            'func' => 'encryptionAlgorithm',
            'param' => 0,
            'expected' => 'The parameter [FastSitePHP\Security\Crypto\Encryption->encryptionAlgorithm($encryption_algorithm)] must be a string but was instead a [integer].',
        ),
        array(
            'run-test' => (PHP_VERSION_ID < 70100),
            'object' => 'crypto3',
            'func' => 'encryptionAlgorithm',
            'param' => 'aes-256-gcm',
            'expected' => 'The encryption algorithm [FastSitePHP\Security\Crypto\Encryption->encryptionAlgorithm(\'aes-256-gcm\')] uses AEAD Block Cipher Mode (GCM or CCM) which requires PHP 7.1 or greater.',
        ),
        array(
            'run-test' => (PHP_VERSION_ID < 50500),
            'object' => 'crypto3',
            'func' => 'encryptionAlgorithm',
            'param' => 'aes-256-ctr',
            'expected' => 'The encryption algorithm [FastSitePHP\Security\Crypto\Encryption->encryptionAlgorithm(\'aes-256-ctr\')] is not available on this computer. CTR Mode often requires PHP 5.5 or greater. If possible upgrade your PHP version or use the default CBC Mode which is also known to be secure.',
        ),
        array(
            'object' => 'crypto3',
            'func' => 'encrypt',
            'param' => 0,
            'param2' => $key,
            'expected' => 'Error when calling encrypt(), if [dataFormat()] is set to [string-only] then only strings can be encrypted. Data of type [integer] was passed to the function.',
        ),
        array(
            'func' => 'decrypt',
            'param' => 123,
            'param2' => $key,
            'expected' => 'Error when decrypting encrypted text using the decrypt() function. The [$encrypted_text] parameter was not a string and was instead a [integer]. This is a programming error because the function was not called correctly.',
        ),
        array(
            'func' => 'decrypt',
            'param' => '',
            'param2' => $key,
            'expected' => 'Error when decrypting encrypted text using the decrypt() function. The [$encrypted_text] parameter was a blank string. This is a programming error because the function was not called correctly.',
        ),
        array(
            'object' => 'crypto3',
            'func' => 'decrypt',
            'param' => '#$%',
            'param2' => $key,
            'expected' => 'Error when decrypting encrypted text using the decrypt() function. Either the encrypted text has been modified and is not a valid base-64 string or the data was encrypted in another format. This error can also happen if another function encoded or decoded the original value.',
        ),
        array(
            'func' => 'decrypt',
            'param' => '#$%',
            'param2' => $key,
            'expected' => 'Error when decrypting encrypted text using the decrypt() function. Either the encrypted text has been modified and is not a valid base-64 url safe string or the data was encrypted in another format. This error can also happen if another function encoded or decoded the original value.',
        ),
        array(
            'object' => 'crypto4',
            'func' => 'decrypt',
            'param' => '#$%',
            'param2' => $key,
            'expected' => 'Error when decrypting encrypted text using the decrypt() function. Either the encrypted text has been modified and is not a valid hex string or the data was encrypted in another format. This error can also happen if another function encoded or decoded the original value.',
        ),
        array(
            'func' => 'decrypt',
            'param' => 'dGVzdA',
            'param2' => $key,
            'expected' => 'The text to decrypt is smaller than the minimum expected text size. The text was either tampered with, encrypted using different settings, or accidently truncated.',
        ),
        array(
            'func' => 'decrypt',
            'param' => $ciphertext,
            'param2' => $key_alt,
            'expected' => 'Decryption failed. The text was encrypted using different settings or has been tampered with.',
        ),
        array(
            'object' => 'crypto2',
            'func' => 'decrypt',
            'param' => $ciphertext2,
            'param2' => $key2_alt,
            // NOTE - since this error comes from [openssl] is possible that the error
            // message can vary. This is the expected starting text of the error.
            'expectedStart' => 'Decryption Failed, Error from openssl: [error:06065064:digital envelope routines:EVP_DecryptFinal_ex:bad decrypt]',
        ),
        array(
            'func' => 'encrypt',
            'param' => null,
            'param2' => $key,
            'expected' => 'Unable to encrypt a null value unless [FastSitePHP\Security\Crypto\Encryption->allowNull(true)] is set.',
        ),
        array(
            'func' => 'encrypt',
            'param' => fopen(__FILE__, 'r'),
            'param2' => $key,
            'expected' => 'Invalid data type for encryption, data passed to encrypt() must be one of the following types: [null, string, int, float, array, object]. Instead [encrypt()] was called with a [resource] data type.',
        ),
        array(
            'object' => 'crypto5',
            'func' => 'encrypt',
            'param' => 'Test',
            'param2' => '',
            'expected' => 'Error, the password cannot be empty.',
        ),
    );

    // Run Tests
    foreach ($tests as $test) {
        $static_class = false;
        if (isset($test['object'])) {
            switch ($test['object']) {
                case 'crypto2':
                    $object = $crypto2;
                    break;
                case 'crypto3':
                    $object = $crypto3;
                    break;
                case 'crypto4':
                    $object = $crypto4;
                    break;
                case 'crypto5':
                    $object = $crypto5;
                    break;
                case 'Crypto_Static':
                    $object = null;
                    $static_class = true;
                    break;
                default:
                    $object = null;
            }
        } else {
            $object = $crypto;
        }
        $run_test_isset = isset($test['run-test']);
        $run_test = ($run_test_isset ? $test['run-test'] : true);
        $func = $test['func'];
        $param = $test['param'];
        $expected_start = (isset($test['expectedStart']) ? $test['expectedStart'] : null);
        $expected = (isset($test['expected']) ? $test['expected'] : null);
        try {
            if ($run_test) {
                if ($static_class) {
                    // NOTE - if using PHP 5.4+ the following line could be used instead:
                    //     $value = \FastSitePHP\Security\Crypto::{$func}($param);
                    // However the current version of FastSitePHP supports 5.3 so
                    // [call_user_func()] is used instead.
                    $value = call_user_func("\FastSitePHP\Security\Crypto::{$func}", $param);
                } elseif (isset($test['param2'])) {
                    $value = $object->{$func}($param, $test['param2']);
                } else {
                    $value = $object->{$func}($param);
                }
                echo 'Test should have thrown an Exception:';
                echo '<br>';
                var_dump($test);
                echo '<br>';
                var_dump($value);
                exit();
            }
        } catch (\Exception $e) {
            $tests_count++;
            $error = $e->getMessage();
            if ($expected_start !== null) {
                $error_text[] = $expected_start;
                if (strpos($error, $expected_start) !== 0) {
                    echo 'Failed with function: ' . $func;
                    echo '<br>';
                    var_dump($param);
                    echo '<br>';
                    var_dump($error);
                    echo '<br><b>Expected Start:</b> ';
                    var_dump($expected);
                    exit();
                }
            } else {
                $error_text[] = $error;
                if ($error !== $expected) {
                    echo 'Failed with function: ' . $func;
                    echo '<br>';
                    var_dump($param);
                    echo '<br>';
                    var_dump($error);
                    echo '<br>';
                    var_dump($expected);
                    exit();
                }
            }

            if ($run_test_isset) {
                $error_text_old_php[] = $error;
            }
        }
    }

    // Result
    // [$error_text_old_php] is used on older PHP versions to hash only the old error text.
    // This prevents having to re-test on many PHP versions when updating a single error message.
    $error_text = implode('', ($error_text_old_php ? $error_text_old_php : $error_text));
    return sprintf('[Tests: %d], [Len: %d], [sha256: %s]', $tests_count, len($error_text), hash('sha256', $error_text));
});

$app->get('/signed-data-class-errors', function() use ($app) {
    // Setup Object and Create Random Keys
    $crypto = new \FastSitePHP\Security\Crypto\SignedData($app);
    $key = $crypto
        ->exceptionOnError(true)
        ->generateKey();
    $key_alt = $crypto->generateKey();

    // Signed Data for Testing
    $signed_data = $crypto->sign('Test', $key);

    // Define a function used to generate signed data for specific errors
    // and define the text to use. These tests fail because of errors
    // related to invalid type specified
    function generateInvalidSignedData($text, $type, $expire_time, $key) {
        $expire_time = ($expire_time === null ? '' : '.' . (string)$expire_time);
        $hash_text = \FastSitePHP\Encoding\Base64Url::encode($text) . '.' . $type . $expire_time;
        $hmac = \hash_hmac('sha256', $hash_text, \hex2bin($key), true);
        return $hash_text . '.' . \FastSitePHP\Encoding\Base64Url::encode($hmac);
    }
    $signed_null_error = generateInvalidSignedData('test', 'n', null, $key);
    $signed_bool_error = generateInvalidSignedData('yes', 'b', null, $key);
    $signed_json_error = generateInvalidSignedData('}{', 'j', null, $key);
    $signed_unknown_error = generateInvalidSignedData('test', 'u', null, $key);

    $expired_time = (strtotime('-30 minutes') * 1000);
    $signed_time_error = generateInvalidSignedData('test', 's', $expired_time, $key);

    // Define Error Tests
    $tests_count = 0;
    $error_text = array();
    $tests = array(
        array(
            'func' => 'hashingAlgorithm',
            'param' => 123,
            'expected' => 'The parameter for [FastSitePHP\Security\Crypto\SignedData->hashingAlgorithm()] must be a string or null but was instead a [integer].',
        ),
        array(
            'func' => 'hashingAlgorithm',
            'param' => 'unknown',
            'expected' => 'The hashing algorithm [FastSitePHP\Security\Crypto\SignedData->hashingAlgorithm(\'unknown\')] is not available on this computer.',
        ),
        array(
            'object' => 'Crypto_Static',
            'func' => 'sign',
            'param' => 'test',
            'expected' => 'Missing Application Config Value or Environment Variable for [SIGNING_KEY]. If this error is not clear then please review FastSitePHP documentation and examples on how to use [FastSitePHP\Security\Crypto::sign()].',
        ),
        array(
            'object' => 'Crypto_Static',
            'func' => 'verify',
            'param' => 'test',
            'expected' => 'Missing Application Config Value or Environment Variable for [SIGNING_KEY]. If this error is not clear then please review FastSitePHP documentation and examples on how to use [FastSitePHP\Security\Crypto::verify()].',
        ),
        array(
            'func' => 'sign',
            'param' => 'test',
            'param2' => 'test',
            'expected' => 'Invalid Key. The key must be a hexadecimal encoded string value and the function was called with a non-hex key.',
        ),
        array(
            'func' => 'sign',
            'param' => 'test',
            'param2' => 'ffff',
            'expected' => 'Invalid Key for signing. The key required for [sign()] using the current settings must be a hex encoded string that is 64 characters in length (32 bytes, 256 bits). Required key size is determined from [hashingAlgorithm()].',
        ),
        array(
            'func' => 'verify',
            'param' => $signed_data,
            'param2' => 'test',
            'expected' => 'Invalid Key. The key must be a hexadecimal encoded string value and the function was called with a non-hex key.',
        ),
        array(
            'func' => 'verify',
            'param' => $signed_data,
            'param2' => 'ffff',
            'expected' => 'Invalid Key for signing. The key required for [verify()] using the current settings must be a hex encoded string that is 64 characters in length (32 bytes, 256 bits). Required key size is determined from [hashingAlgorithm()].',
        ),
        array(
            'func' => 'verify',
            'param' => $signed_null_error,
            'param2' => $key,
            'expected' => 'Verification was successful however the verified data did not match a null value. It\'s likely that the data was signed with another program or a software library which is not compatible with this class.',
        ),
        array(
            'func' => 'verify',
            'param' => $signed_bool_error,
            'param2' => $key,
            'expected' => 'Verification was successful however the verified data did not match a boolean value of either 0 or 1. It\'s likely that the data was signed with another program or a software library which is not compatible with this class.',
        ),
        array(
            'func' => 'verify',
            'param' => $signed_json_error,
            'param2' => $key,
            'expected' => 'Verification was successful however the verified data could not be parsed as valid JSON. It\'s likely that the data was signed with another program or a software library which is not compatible with this class. Json Decode Error: Error decoding JSON Data: Syntax error',
        ),
        array(
            'func' => 'verify',
            'param' => $signed_unknown_error,
            'param2' => $key,
            'expected' => 'Verification was successful however the verified data has an unknown type of [u]. It\'s likely that the data was signed with another program or a software library which is not compatible with this class.',
        ),
        array(
            'func' => 'verify',
            'param' => $signed_time_error,
            'param2' => $key,
            'expected' => 'Error when verifying signed text using the verify() function. The text is valid however it has expired based on the [expire_time] value.',
        ),
        array(
            'func' => 'sign',
            'param' => null,
            'param2' => $key,
            'expected' => 'Unable to sign a null value unless [FastSitePHP\Security\Crypto\SignedData->allowNull(true)] is set.',
        ),
        array(
            'func' => 'sign',
            'param' => fopen(__FILE__, 'r'),
            'param2' => $key,
            'expected' => 'Invalid type [resource] for signing. Only null, string, int, float, bool, object, and array types can be signed.',
        ),
        array(
            'func' => 'sign',
            'param' => 'test',
            'param2' => $key,
            'param3' => 'invalid_time',
            'expected' => 'Invalid [expire_time] parameter for signing when the function sign() was called. A string was passed however it could not be converted to a valid timestamp. If specified the parameter [expire_time] must be either a float representing a Unix Timestamp in Milliseconds or a valid string for the PHP function [strtotime()], examples include \'+1 day\' and \'+30 minutes\'.',
        ),
        array(
            'func' => 'sign',
            'param' => 'test',
            'param2' => $key,
            'param3' => true,
            'expected' => 'Unexpected [expire_time] parameter for signing when the function sign() was called, expected [string|float|null] but was passed [boolean].',
        ),
        array(
            'func' => 'verify',
            'param' => 123,
            'param2' => $key,
            'expected' => 'Error when verifying signed text using the verify() function. The [$signed_text] parameter was not a string but instead was passed a [integer].',
        ),
        array(
            'func' => 'verify',
            'param' => 'test.test',
            'param2' => $key,
            'expected' => 'Error when verifying signed text using the verify() function. Unexpected format of signed text. The expected format is [base64(data).type.base64(hmac)] or [base64(data).type.expireTime.base64(hmac)].',
        ),
        array(
            'func' => 'verify',
            'param' => '$%^.s.dGVzdA',
            'param2' => $key,
            'expected' => 'Error when verifying signed text using the verify() function. Either text or hmac values have been modified and are not valid base-64-url strings. This error can happen if another function has encoded or decoded and modified the original value.',
        ),
        // NOTE - same error as before, this is done for the following line:
        //   if (!($count === 3 || $count === 4)) {
        array(
            'func' => 'verify',
            'param' => 'dGVzdA.s.$%^',
            'param2' => $key,
            'expected' => 'Error when verifying signed text using the verify() function. Either text or hmac values have been modified and are not valid base-64-url strings. This error can happen if another function has encoded or decoded and modified the original value.',
        ),
        array(
            'func' => 'verify',
            'param' => $signed_data,
            'param2' => $key_alt,
            'expected' => 'Error when verifying signed text using the verify() function. The signed text has either been modified or a different key was used to sign the data.',
        ),
    );

    // Run Tests
    foreach ($tests as $test) {
        $static_class = false;
        if (isset($test['object'])) {
            switch ($test['object']) {
                case 'Crypto_Static':
                    $object = null;
                    $static_class = true;
                    break;
                default:
                    $object = null;
            }
        } else {
            $object = $crypto;
        }
        $run_test = (isset($test['run-test']) ? $test['run-test'] : true);
        $func = $test['func'];
        $param = $test['param'];
        $expected = $test['expected'];
        try {
            if ($run_test) {
                if ($static_class) {
                    // NOTE - if using PHP 5.4+ the following line could be used instead:
                    //     $value = \FastSitePHP\Security\Crypto::{$func}($param);
                    // However the current version of FastSitePHP supports 5.3 so
                    // [call_user_func()] is used instead.
                    $value = call_user_func("\FastSitePHP\Security\Crypto::{$func}", $param);
                } elseif (isset($test['param3'])) {
                    $value = $object->{$func}($param, $test['param2'], $test['param3']);
                } elseif (isset($test['param2'])) {
                    $value = $object->{$func}($param, $test['param2']);
                } else {
                    $value = $object->{$func}($param);
                }
                echo 'Test should have thrown an Exception:';
                echo '<br>';
                var_dump($test);
                echo '<br>';
                var_dump($value);
                exit();
            }
        } catch (\Exception $e) {
            $tests_count++;
            $error = $e->getMessage();
            $error_text[] = $error;
            if ($error !== $expected) {
                echo 'Failed with function: ' . $func;
                echo '<br>';
                var_dump($param);
                echo '<br>';
                var_dump($error);
                echo '<br>';
                var_dump($expected);
                exit();
            }
        }
    }

    // Result
    $error_text = implode('', $error_text);
    return sprintf('[Tests: %d], [Len: %d], [sha256: %s]', $tests_count, len($error_text), hash('sha256', $error_text));
});

$app->run();
