<?php
// ====================================================================================
// Unit Testing Page
// *) This page tests both [Request] and [Response] Objects for Client Side Cookies.
//    Secure Cookies (Signed and Encrypted) are also tested.
// ====================================================================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    // Key Classes
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Web/Request.php';
    require '../../vendor/fastsitephp/src/Web/Response.php';

    // Crypto Classes
    require '../../vendor/fastsitephp/src/Encoding/Base64Url.php';
    require '../../vendor/fastsitephp/src/Encoding/Json.php';
    require '../../vendor/fastsitephp/src/Security/Crypto.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/AbstractCrypto.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/CryptoInterface.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/Encryption.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/JWT.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/SignedData.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/Random.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';
    require '../src/Web/Request.php';
    require '../src/Web/Response.php';

    require '../src/Encoding/Base64Url.php';
    require '../src/Encoding/Json.php';
    require '../src/Security/Crypto.php';
    require '../src/Security/Crypto/AbstractCrypto.php';
    require '../src/Security/Crypto/CryptoInterface.php';
    require '../src/Security/Crypto/Encryption.php';
    require '../src/Security/Crypto/JWT.php';
    require '../src/Security/Crypto/SignedData.php';
    require '../src/Security/Crypto/Random.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// Keys for Encryption and Signing
// IMPORTANT - These are publish keys for testing only, do not use them in production
// Use [generateKey()] functions to create your own keys.
$app->config['ENCRYPTION_KEY'] = 'eada343fc415625494bfd1b065ba60c2a5c8508d353dbb872378c1356181c84f05c52ff60d1cc157957cbbf0101f9cb7d74b040b57192a6a820b5402132b9ab4';
$app->config['SIGNING_KEY'] = 'ab2403a36467b59b20cc314bb211e1812668b3bffb00358c161f26fe003073ed';
$app->config['JWT_KEY'] = 'fkeVxeElykoCBzRTIUjxwTD9MIg71nXxOEQl6HTrIvw=';

// -----------------------------------------------------------
// Routes
// -----------------------------------------------------------

// On a GET Request send back several cookies then POST route should contain cookies
$app->get('/cookie-plain', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->cookie('unit-test-plain', 'plain cookie')
        ->content('Plain Cookie');
});
$app->post('/cookie-plain', function() {
    $res = new \FastSitePHP\Web\Request();
    return array(
        'unit-test-plain' => $res->cookie('unit-test-plain'),
    );
});

// Encrypted Cookies
// The encrypted text will change everytime it is generated however due to
// fixed plaintext and padding size the length will be the same.
$app->get('/cookie-encrypted', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->cookie('unit-test-deleted', '')
        ->cookie('unit-test-plain', 'plain cookie')
        ->encryptedCookie('unit-test-encrypted-text', 'Secret Data')
        ->encryptedCookie('unit-test-encrypted-object', array('Secret' => 'Object'))
        ->content('Encrypted Cookies');
});
$app->post('/cookie-encrypted', function() {
    $req = new \FastSitePHP\Web\Request();
    return array(
        'unit-test-plain' => $req->cookie('unit-test-plain'),
        'unit-test-plain-decypted' => $req->decryptedCookie('unit-test-plain'),
        'unit-test-plain-deleted' => $req->decryptedCookie('unit-test-deleted'),
        'unit-test-encrypted-text' => $req->decryptedCookie('unit-test-encrypted-text'),
        'unit-test-encrypted-text-raw-len' => strlen($req->cookie('unit-test-encrypted-text')),
        'unit-test-encrypted-text-raw-base64url' => (\FastSitePHP\Encoding\Base64Url::decode($req->cookie('unit-test-encrypted-text')) !== false),
        'unit-test-encrypted-object' => $req->decryptedCookie('unit-test-encrypted-object'),
        'unit-test-encrypted-object-raw-len' => strlen($req->cookie('unit-test-encrypted-object')),
    );
});

// Signed Cookies
// Similar logic to the above encryption/decryption test
// NOTE - by default Signed Data has a '+1 hour' timeout.
// The third parameter for [signedCookie()] is set to null
// to avoid the timeout.
$app->get('/cookie-signed', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->cookie('unit-test-deleted', '')
        ->cookie('unit-test-plain', 'plain cookie')
        ->signedCookie('unit-test-signed-text', 'Signed by FastSitePHP', null)
        ->signedCookie('unit-test-signed-object', array('SignedBy' => 'FastSitePHP'), null)
        ->content('Signed Cookies');
});
$app->post('/cookie-signed', function() {
    $req = new \FastSitePHP\Web\Request();
    return array(
        'unit-test-plain' => $req->cookie('unit-test-plain'),
        'unit-test-plain-verified' => $req->verifiedCookie('unit-test-plain'),
        'unit-test-plain-deleted' => $req->verifiedCookie('unit-test-deleted'),
        'unit-test-signed-text' => $req->verifiedCookie('unit-test-signed-text'),
        'unit-test-signed-text-raw' => $req->cookie('unit-test-signed-text'),
        'unit-test-signed-object' => $req->verifiedCookie('unit-test-signed-object'),
        'unit-test-signed-object-raw' => $req->cookie('unit-test-signed-object'),
    );
});

// JWT Cookies
// Similar logic to the above signed/verified test
// NOTE - by default JWT Cookies have a '+1 hour' timeout.
// The third parameter for [jwtCookie()] is set to null
// to avoid the timeout.
$app->get('/cookie-jwt', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->cookie('unit-test-deleted', '')
        ->cookie('unit-test-plain', 'plain cookie')
        ->jwtCookie('unit-test-jwt', array('role' => 'Admin'), null)
        ->content('JWT Cookie');
});
$app->post('/cookie-jwt', function() {
    $req = new \FastSitePHP\Web\Request();
    return array(
        'unit-test-plain' => $req->cookie('unit-test-plain'),
        'unit-test-plain-verified' => $req->jwtCookie('unit-test-plain'),
        'unit-test-plain-deleted' => $req->jwtCookie('unit-test-deleted'),
        'unit-test-jwt' => $req->jwtCookie('unit-test-jwt'),
        'unit-test-jwt-raw' => $req->cookie('unit-test-jwt'),
    );
});

$app->get('/crypto-cookie-response-errors', function() use ($app) {
    $rep = new \FastSitePHP\Web\Response();
    $data = array();

    // Clear Keys before calling functions
    unset($app->config['SIGNING_KEY']);
    unset($app->config['ENCRYPTION_KEY']);

    try {
        $rep->signedCookie('error-test');
        $data[] = 'Error - signedCookie() should have thrown an Exception';
    } catch (\Exception $e) {
        $data[] = $e->getMessage();
    }

    try {
        $rep->encryptedCookie('error-test');
        $data[] = 'Error - encryptedCookie() should have thrown an Exception';
    } catch (\Exception $e) {
        $data[] = $e->getMessage();
    }

    return $data;
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
