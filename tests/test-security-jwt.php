<?php
// ===========================================================
// Unit Testing Page
// *) This file uses only core Framework files
//     and required [Security\Crypto\JWT] Class
// ===========================================================

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
    require '../../vendor/fastsitephp/src/Security/Crypto.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/CryptoInterface.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/JWT.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/Random.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/PublicKey.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';
    require '../src/Encoding/Base64Url.php';
    require '../src/Encoding/Json.php';
    require '../src/Security/Crypto.php';
    require '../src/Security/Crypto/CryptoInterface.php';
    require '../src/Security/Crypto/JWT.php';
    require '../src/Security/Crypto/Random.php';
    require '../src/Security/Crypto/PublicKey.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Define Known Keys for Testing.
// IMPORANT - do not use these keys in your code, 
// use [$jwt->generateKey()] instead.
// -----------------------------------------------------------

$key_256 = 'fkeVxeElykoCBzRTIUjxwTD9MIg71nXxOEQl6HTrIvw=';
$key_384 = 'n1RNMjoLSLdbQ9vKjMFzkOgrFygg1tDFfQN5dXLdS6fVsAXzw6S4Zxmi4IYk9oyx';
$key_512 = '5K1fTsGPj05aw7I/+3CKj+aoH221GwpdxNfLemiz2UnsR2kHYw/gtzNXbGH0yVdCbzqyCAWMPVb4HohnLZJVQw==';

$key_256_hex = 'b3ddf95cd46d380801c5b2d8d90e14c7dd6d1ed2660079b87b14485c643543e6';

$private_key = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDTkUilvmMD28fC
+RvqnBCppx/t8GzSnRUmn5p42z+ZMDh8zFMyecv6+ElerLvKpkCdUg99XaG7BGLG
rv/gdBzFZp3xlZI70IlUZ/ctPr3e61Qf/v6Tjsmlsi+QtnJJ6OXW9nuXc7JTS7Ot
spkCCHpn7Nt90UmmXOia2G47B9vz6CO4IaazNaRC/5U4tcmVLmfLQruDnaS2izIz
YYRNj5q/KhWHILNHF71LOFth3aJMj7LGeOCB8a9v6W1YMQlMwKC6wVvNKTGI6PLx
g9ulVAZQ3YLeIMKuclhMPTvt47Wra6zgHkh6y+1PwJQAoqrbUpchBQnviT3Mhx9Y
3kPKoeYtAgMBAAECggEBANItGHCfjJn+spq9AsC1PdBsWMn1+QXaS3LNR5YcqTez
sco46cXRPZUbJzhIfV66fqJLLVwrskmp977NhyEh+JsacHnZTybg6izSA7oNBG76
dd47YgiQ5z4WoC4xMaS/G+XLg8hXhaY+JEfAj0R3y7KGmR6K1ZHR5ro9nHREQHwy
J5DF23tj6oh+BIGC4vKCoKSbKtdj4B+HtdgPLzrsX+YuUpfrnPpKHl4oovIIaOPJ
MRRqZlSCcUYWuNgOK21wgCrIR8hZ7Hxp3oPliysZ2M8e2Fw/J/rSetT2ze/LTvil
nU2shWGS2u0wymtHw149MV6sTkFUnMR/9dz05tiv4TkCgYEA/Sip58gfE+T9j2Qp
2MowixVhq8+cAOME91//VlUwChBeDOqIW7a2nEMErJl6uWawT1CaUcg8AlODbn3l
PaKG2yIrjbYTrblVzJtXYoh0cWnKtfoiV0w4BdaIDbdxynDmueCoZbPYFN5L8BC8
JBP2eP6ktdh4F3LDgu5etF0HwjcCgYEA1fEgW+F9CpLeQsNdsKXZ5ygxQveOYrjD
ckbA79fpb3BnAiV+FI3r53k+jfCNBLRiLkjxnf5yXEIPQjqwsajvrYji/SuIJ4g8
+KetUHXyLjunNrKf+oavGPOrHmlOGk2ieKKkJ3Av3sQHnzXj57ozdDkc186sr4EG
1PaQD1bdOLsCgYAw4lxErIkLv2kS+kV2XjyXbs6IbbNzHGNGHVxh4FtBZj2zAsrH
4vVKIUrSxWRETsb22dqitiaYUGYNvPO6PhsKT4PXfnQ4VJRzyP9LwIuzprkFAMMG
1mnu/qkQ9P5dGAYFmJML6HluNiq1tZWO5efvH0TQ9HbkGD4Wl07kRx6LawKBgAqv
PSd5jm4dwZ3h9ebhkY1a04L9rA3AHnqxj+cqiEz5dxuDVdWe9N4djwM1tUU74P9g
wzhZwcpbvjlXhwWIY1fuUy/DocwfoLPmY+B10399mS9BIn43hb0gRjaBmdX0dJ3D
uaECmFFuxtarsVMcWH+AbrFUcAlfz0CJg85QT0ILAoGAUghV0jA6bUuJyvooWdN3
+lWHxYgfCdPdx1PSFWmeEZqBhiYgM1yV+u1bL0rdDwsaVXB7SpB4ZHklJNau6NJQ
AdyUyYY9kifD1fOGxV60CzuPBfLU6Xzu/OtgH9rsk55yg7QwaEUV6F8Gxuxd4VUd
vlHZwaYATcdY0PmPm5fymTg=
-----END PRIVATE KEY-----
EOD;

$public_key = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA05FIpb5jA9vHwvkb6pwQ
qacf7fBs0p0VJp+aeNs/mTA4fMxTMnnL+vhJXqy7yqZAnVIPfV2huwRixq7/4HQc
xWad8ZWSO9CJVGf3LT693utUH/7+k47JpbIvkLZySejl1vZ7l3OyU0uzrbKZAgh6
Z+zbfdFJplzomthuOwfb8+gjuCGmszWkQv+VOLXJlS5ny0K7g52ktosyM2GETY+a
vyoVhyCzRxe9SzhbYd2iTI+yxnjggfGvb+ltWDEJTMCgusFbzSkxiOjy8YPbpVQG
UN2C3iDCrnJYTD077eO1q2us4B5IesvtT8CUAKKq21KXIQUJ74k9zIcfWN5DyqHm
LQIDAQAB
-----END PUBLIC KEY-----
EOD;

// -----------------------------------------------------------
// Define Custom Objects
// -----------------------------------------------------------

class User
{
    public $id;
    public $user;
    public $roles;
}

// -----------------------------------------------------------
// Define Test Routes
// -----------------------------------------------------------

$app->get('/default-settings', function() {
    // Return default value of all getter/setter props
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    return array(
        'exceptionOnError' => $jwt->exceptionOnError(),
        'algo' => $jwt->algo(),
        'allowedAlgos' => $jwt->allowedAlgos(),
        'useInsecureKey' => $jwt->useInsecureKey(),
        'validateDefinedClaims' => $jwt->validateDefinedClaims(),
        'allowedIssuers' => $jwt->allowedIssuers(),
        'requireSubject' => $jwt->requireSubject(),
        'allowedAudiences' => $jwt->allowedAudiences(),
        'requireExpireTime' => $jwt->requireExpireTime(),
        'requireNotBefore' => $jwt->requireNotBefore(),
        'requireIssuedAt' => $jwt->requireIssuedAt(),
        'requireJwtId' => $jwt->requireJwtId(),
    );
});

$app->get('/getter-and-setter-functions', function() {
    // This verifies that the functions are chainable
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    return array(
        'exceptionOnError' => $jwt->exceptionOnError(true)->exceptionOnError(),
        'algo' => $jwt->algo('HS256')->algo(),
        'allowedAlgos' => $jwt->allowedAlgos(array('HS256'))->allowedAlgos(),
        'useInsecureKey' => $jwt->useInsecureKey(true)->useInsecureKey(),
        'validateDefinedClaims' => $jwt->validateDefinedClaims(false)->validateDefinedClaims(),
        'allowedIssuers' => $jwt->allowedIssuers(array('iss'))->allowedIssuers(),
        'requireSubject' => $jwt->requireSubject('sub')->requireSubject(),
        'allowedAudiences' => $jwt->allowedAudiences(array('aud'))->allowedAudiences(),
        'requireExpireTime' => $jwt->requireExpireTime(true, 10)->requireExpireTime(),
        'requireNotBefore' => $jwt->requireNotBefore(true, 10)->requireNotBefore(),
        'requireIssuedAt' => $jwt->requireIssuedAt(true)->requireIssuedAt(),
        'requireJwtId' => $jwt->requireJwtId('jti')->requireJwtId(),
    );
});

$app->get('/encode-decode-array', function() {
    $payload = array(
        'id' => 1,
        'user' => 'John Doe',
        'roles' => array('Admin', 'SQL User'),
    );

    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt->generateKey();
    $token = $jwt->encode($payload, $key);
    return $jwt->decode($token, $key);
});

$app->get('/encode-decode-object', function() {
    $payload = new \stdClass();
    $payload->id = 2;
    $payload->user = 'Jane Doe';
    $payload->roles = array('Viewer');

    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt->generateKey();
    $token = $jwt->encode($payload, $key);
    return $jwt->decode($token, $key);
});

$app->get('/encode-decode-class', function() {
    $payload = new User();
    $payload->id = 3;
    $payload->user = 'Guest';
    $payload->roles = array('Guest');

    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt->generateKey();
    $token = $jwt->encode($payload, $key);
    return $jwt->decode($token, $key);
});

$app->get('/encode-decode-with-crypto', function() use ($app) {
    $payload = array(
        'user' => 'Admin',
        'roles' => array('Admin'),
    );

    // Create a Random Key
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt->generateKey();
    $app->config['JWT_KEY'] = $key;

    // Encode with the JWT Class
    $token = $jwt->encode($payload, $key);
    $decoded = $jwt->decode($token, $key);

    // Encode with the Crypto Class
    // 2nd Parameter is set to null as it default to '+1 hour' expire time
    $token2 = \FastSitePHP\Security\Crypto::encodeJWT($payload, null);
    $decoded2 = \FastSitePHP\Security\Crypto::decodeJWT($token2);

    // Results should be an exact match
    return array(
        'token_match' => ($token === $token2),
        'decoded_match' => ($decoded === $decoded2),
        'decoded' => $decoded2, 
    );
});

$app->get('/encode-decode-hex-key', function() {
    $payload = new User();
    $payload->id = 3;
    $payload->user = 'Guest';
    $payload->roles = array('Guest');

    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt->generateKey('hex');
    $token = $jwt->encode($payload, $key);
    return array(
        'data' => $jwt->decode($token, $key),
        'keyLen' => strlen($key),
        'keyIsHex' => ctype_xdigit($key),
    );
});

$app->get('/encode-decode-hmac', function() {
    $payload = array('test' => 'test');
    $algos = array('HS256', 'HS384', 'HS512');
    $result = '';
    foreach ($algos as $algo) {
        $jwt = new \FastSitePHP\Security\Crypto\JWT();
        $key = $jwt->algo($algo)->allowedAlgos(array($algo))->generateKey();
        $key2 = $jwt->generateKey();
        $token = $jwt->encode($payload, $key);
        $decoded = $jwt->decode($token, $key); // Works and returns payload
        $decoded2 = $jwt->decode($token, $key2); // Fails because a different key is used
        $success = ($payload === $decoded ? 'true' : 'false'); 
        $success2 = ($payload === $decoded2 ? 'true' : 'false'); 
        $key_size = (string)strlen(base64_decode($key, true));
        $result .= '[' . $jwt->algo() . ',' . $success . ',' . $success2 . ',' . $key_size . ']';
    }
    return $result;
});

$app->get('/create-rsa-key', function() {
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    list($private_key, $public_key) = $jwt->algo('RS256')->generateKey();

    $is_private = strpos($private_key, '-----BEGIN PRIVATE KEY-----');
    $is_public = strpos($public_key, '-----BEGIN PUBLIC KEY-----');

    return array($is_private, $is_public);
});

$app->get('/encode-decode-rsa', function() {
    $payload = array('test' => 'test');
    $algos = array('RS256', 'RS384', 'RS512');
    $result = '';
    foreach ($algos as $algo) {
        $jwt = new \FastSitePHP\Security\Crypto\JWT();
        list($private_key, $public_key) = $jwt->algo($algo)->allowedAlgos(array($algo))->generateKey();
        list($private_key2, $public_key2) = $jwt->generateKey();
        $token = $jwt->encode($payload, $private_key);
        $decoded = $jwt->decode($token, $public_key);
        $decoded2 = $jwt->decode($token, $public_key2);
        $success = ($payload === $decoded ? 'true' : 'false'); 
        $success2 = ($payload === $decoded2 ? 'true' : 'false'); 
        $key_size = (string)strlen($public_key);
        $result .= '[' . $jwt->algo() . ',' . $success . ',' . $success2 . ',' . $key_size . ']';
    }
    return $result;
});

$app->get('/encode-decode-rsa-large-key', function() {
    set_time_limit(0);
    
    $config = \FastSitePHP\Security\Crypto\PublicKey::defaultConfig();
    $config['private_key_bits'] = 3072;
    
    $payload = array('test' => 'test');
    $algos = array('RS256', 'RS384', 'RS512');
    $result = '';
    foreach ($algos as $algo) {
        list($private_key, $public_key) = \FastSitePHP\Security\Crypto\PublicKey::generateKeyPair($config);
        list($private_key2, $public_key2) = \FastSitePHP\Security\Crypto\PublicKey::generateKeyPair($config);
        $jwt = new \FastSitePHP\Security\Crypto\JWT();
        $jwt->algo($algo)->allowedAlgos(array($algo));
        $token = $jwt->encode($payload, $private_key);
        $decoded = $jwt->decode($token, $public_key);
        $decoded2 = $jwt->decode($token, $public_key2);
        $success = ($payload === $decoded ? 'true' : 'false');
        $success2 = ($payload === $decoded2 ? 'true' : 'false'); 
        $key_size = (string)strlen($public_key);
        $result .= '[' . $jwt->algo() . ',' . $success . ',' . $success2 . ',' . $key_size . ']';
    }
    return $result;
});

$app->get('/encode-decode-hs256', function() use ($key_256) {
    $algo = 'HS256';
    $payload = array('test' => $algo);

    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $jwt->algo($algo)->allowedAlgos(array($algo));
    $token = $jwt->encode($payload, $key_256);
    $decoded = $jwt->decode($token, $key_256);
    return array(
        'jwt' => $token,
        'payload' => $decoded,
    );
});

$app->get('/encode-decode-hs384', function() use ($key_384) {
    $algo = 'HS384';
    $payload = array('test' => $algo);

    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $jwt->algo($algo)->allowedAlgos(array($algo));
    $token = $jwt->encode($payload, $key_384);
    $decoded = $jwt->decode($token, $key_384);
    return array(
        'jwt' => $token,
        'payload' => $decoded,
    );
});

$app->get('/encode-decode-hs512', function() use ($key_512) {
    $algo = 'HS512';
    $payload = array('test' => $algo);

    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $jwt->algo($algo)->allowedAlgos(array($algo));
    $token = $jwt->encode($payload, $key_512);
    $decoded = $jwt->decode($token, $key_512);
    return array(
        'jwt' => $token,
        'payload' => $decoded,
    );
});

$app->get('/encode-decode-known-hex', function() use ($key_256_hex) {
    $payload = array('test' => $key_256_hex);
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $token = $jwt->encode($payload, $key_256_hex);
    $decoded = $jwt->decode($token, $key_256_hex);
    return array(
        'jwt' => $token,
        'payload' => $decoded,
    );
});

// RSA Tokens can all use the same RSA Key Pair
foreach (array('RS256', 'RS384', 'RS512') as $algo) {
    $url = '/encode-decode-' . strtolower($algo);
    $app->get($url, function() use ($algo, $private_key, $public_key) {
        $payload = array('test' => $algo);    
        $jwt = new \FastSitePHP\Security\Crypto\JWT();
        $jwt->algo($algo)->allowedAlgos(array($algo));
        $token = $jwt->encode($payload, $private_key);
        $decoded = $jwt->decode($token, $public_key);
        return array(
            'jwt' => $token,
            'payload' => $decoded,
        );
    });
}

$app->get('/encode-decode-with-insecure-key', function() {
    $algo = 'HS256';
    $payload = array('test' => 'InsecureKey');
    $key = 'password';

    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $jwt
        ->useInsecureKey(true)
        ->algo($algo)
        ->allowedAlgos(array($algo));

    $token = $jwt->encode($payload, $key);
    $decoded = $jwt->decode($token, $key);
    return array(
        'jwt' => $token,
        'payload' => $decoded,
    );
});

$app->get('/claims-validated-by-default', function() {
    $jwt = new \FastSitePHP\Security\Crypto\JWT();

    $payload = array('test' => 'Expired');
    $payload = $jwt->addClaim($payload, 'exp', '-10 seconds');

    $key = $jwt->generateKey();
    $token = $jwt->encode($payload, $key);
    $decoded = $jwt->decode($token, $key); // Returns null

    return array(
        'payload_fields' => array_keys($payload),
        'returned' => $decoded,
    );
});

// Same as above but uses [exceptionOnError(true)]
$app->get('/exception-on-error', function() {
    $jwt = new \FastSitePHP\Security\Crypto\JWT();

    $payload = array('test' => 'Expired');
    $payload = $jwt->addClaim($payload, 'exp', '-10 seconds');

    $key = $jwt
        ->exceptionOnError(true)
        ->generateKey();
    $token = $jwt->encode($payload, $key);

    try {
        return $jwt->decode($token, $key);
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// This test validates all errors from [private function validateClaims()]
// It also verifies that [addClaim()] works correctly for each field
$app->get('/claim-errors', function() {
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt
        ->exceptionOnError(true)
        ->generateKey();

    // Make sure [addClaim()] works with a valid 'iat'
    $ita_payload = $jwt->addClaim(array(), 'iat', time());
    $ita_payload['iat'] = 'test';

    // This test will fail after 2038-01-19 based on 'nbf' 
    // which is the largest 32-bit type for PHP Unix Timestamp.
    $tests = array(
        $jwt->addClaim(array(), 'iss', 'issue'),
        $jwt->addClaim(array(), 'sub', 'subject'),
        $jwt->addClaim(array(), 'aud', 'audience'),
        $jwt->addClaim(array(), 'exp', '-10 seconds'),
        $jwt->addClaim(array(), 'nbf', '2038-01-19 03:14:07'),
        $ita_payload,
        $jwt->addClaim(array(), 'jti', 'jwt_id'),
    );

    $results = array();
    foreach ($tests as $payload) {
        $token = $jwt->encode($payload, $key);
        try {
            $results[] = $jwt->decode($token, $key);
        } catch (\Exception $e) {
            $results[] =  $e->getMessage();
        }
    }

    // Now run the same tests with validation turned off unless
    // defined per field, then reset
    $jwt->validateDefinedClaims(false);
    $success_count = 0;
    foreach ($tests as $payload) {
        $token = $jwt->encode($payload, $key);
        $jwt->decode($token, $key);
        $success_count++;
    }
    $results[] = "Success Count [${success_count}] with validateDefinedClaims(false)";
    $jwt->validateDefinedClaims(true);

    // Set emtpy arrays and string and run tests
    // This verifies lines such as:
    //     if ($this->issuers === null || count($this->issuers) === 0) {
    $jwt
        ->allowedIssuers(array())
        ->requireSubject('')
        ->allowedAudiences(array())
        ->requireJwtId('');

    $tests = array(
        $jwt->addClaim(array(), 'iss', 'issue'),
        $jwt->addClaim(array(), 'sub', 'subject'),
        $jwt->addClaim(array(), 'aud', 'audience'),
        $jwt->addClaim(array(), 'jti', 'jwt_id'),
    );

    foreach ($tests as $payload) {
        $token = $jwt->encode($payload, $key);
        try {
            $results[] = $jwt->decode($token, $key);
        } catch (\Exception $e) {
            $results[] =  $e->getMessage();
        }
    }

    // Test with Fields that 
    $tests = array(
        $jwt->addClaim(array(), 'iss', 'issue'),
        $jwt->addClaim(array(), 'sub', 'subject'),
        $jwt->addClaim(array(), 'aud', 'audience'),
        $jwt->addClaim(array(), 'jti', 'jwt_id'),
    );

    foreach ($tests as $payload) {

        $jwt = new \FastSitePHP\Security\Crypto\JWT();
        $jwt->exceptionOnError(true);
        $token = $jwt->encode($payload, $key);

        if (isset($payload['iss'])) {
            $jwt->allowedIssuers(array('example.com'));
        } elseif (isset($payload['sub'])) {
            $jwt->requireSubject('TestSubject');
        } elseif (isset($payload['aud'])) {
            $jwt->allowedAudiences(array('UnitTesters'));
        } elseif (isset($payload['jti'])) {
            $jwt->requireJwtId('12345');
        }

        try {
            $results[] = $jwt->decode($token, $key);
        } catch (\Exception $e) {
            $results[] =  $e->getMessage();
        }
    }

    return $results;
});

$app->get('/claim-exp-leeway', function() {
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt
        ->exceptionOnError(true)
        ->generateKey();
    
    // Add 10 seconds leeway time for 'exp'
    $jwt->requireExpireTime(true, 10);

    // 1st Test - Create a Token with a time that expired 10 seconds ago
    // This test will succeed because of the leeway
    $payload = $jwt->addClaim(array(), 'exp', '-10 seconds');
    $token = $jwt->encode($payload, $key);
    $jwt->decode($token, $key);
    $results = array('success');

    // 2nd Test - - Create a Token with a time that expired 11 seconds ago
    // This test will fail because only 10 seconds is allowed
    $payload = $jwt->addClaim(array(), 'exp', '-11 seconds');
    $token = $jwt->encode($payload, $key);
    try {
        $results[] = $jwt->decode($token, $key);
    } catch (\Exception $e) {
        $results[] =  $e->getMessage();
    }
    
    return $results;
});

$app->get('/claim-nbf-leeway', function() {
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt
        ->exceptionOnError(true)
        ->generateKey();
    
    // Add 10 seconds of leeway time for 'nbf'
    $jwt->requireNotBefore(true, 10);

    // 1st Test - Create a Token with a time that cannot be used until 10 seconds in the future
    // This test will succeed because of the leeway
    $payload = $jwt->addClaim(array(), 'nbf', '+10 seconds');
    $token = $jwt->encode($payload, $key);
    $jwt->decode($token, $key);
    $results = array('success');

    // 2nd Test - - Create a Token with a time that cannot be used until 11 seconds in the future
    // This test will fail because only 10 seconds is allowed
    $payload = $jwt->addClaim(array(), 'nbf', '+11 seconds');
    $token = $jwt->encode($payload, $key);
    try {
        $results[] = $jwt->decode($token, $key);
    } catch (\Exception $e) {
        // Error will always be different because it lists the time
        $search = 'Error - JWT Validation failed. The token is valid but it cannot be used before [';
        $results[] = (strpos($e->getMessage(), $search) === 0 ? 'ErrorFound' : 'NotFound');
    }
    
    return $results;
});


$app->get('/validate-all-claims', function() {
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt->generateKey();
    
    // Build Payload with all Claim Fields defined
    $payload = $jwt->addClaim(array(), 'iss', 'issue');
    $payload = $jwt->addClaim($payload, 'sub', 'subject');
    $payload = $jwt->addClaim($payload, 'aud', 'audience');
    $payload = $jwt->addClaim($payload, 'exp', '2038-01-19 03:14:07');
    $payload = $jwt->addClaim($payload, 'nbf', '2018-01-01');
    $payload = $jwt->addClaim($payload, 'iat', '2018-01-01');
    $payload = $jwt->addClaim($payload, 'jti', 'jwt_id');

    // Set rules; time rules [exp, nbf, iat] are automatically handled 
    // as confirmed in other tests and do not have to be set unless 
    // [validateDefinedClaims(false)] is called or leeway is used.
    $jwt->allowedIssuers(array('issue'));
    $jwt->requireSubject('subject');
    $jwt->allowedAudiences(array('audience'));
    $jwt->requireJwtId('jwt_id');

    $token = $jwt->encode($payload, $key);
    return $jwt->decode($token, $key);
});

$app->run();
