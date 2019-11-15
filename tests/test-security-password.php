<?php
// ===========================================================
// Unit Testing Page
// *) This file uses only core Framework files
//     and classes required for [Security\Crypto\Password]
// ===========================================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Security/Password.php';
    require '../../vendor/fastsitephp/src/Security/Crypto/Random.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';
    require '../src/Security/Password.php';
    require '../src/Security/Crypto/Random.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

$app->get('/hash-with-bcrypt', function() {
    $pw = new \FastSitePHP\Security\Password();

    $password = 'Password123';
    $hash = $pw->hash($password);
    $starting_cost = $pw->cost();

    $verified1 = $pw->verify($password, $hash);
    $password = 'Password1234';
    $verified2 = $pw->verify($password, $hash);

    $needs_rehash1 = $pw->needsRehash($hash);
    $needs_rehash2 = $pw->cost(11)->needsRehash($hash);
    $hash2 = $pw->hash($password);

    return array(
        'hash1' => substr($hash, 0, 7), // '$2y$10$'
        'hash2' => substr($hash2, 0, 7), // '$2y$11$'
        'verified1' => $verified1,
        'verified2' => $verified2,
        'needs_rehash1' => $needs_rehash1,
        'needs_rehash2' => $needs_rehash2,
        'starting_cost' => $starting_cost,
        'cost' => $pw->cost(),
    );
});

$app->get('/use-pepper-with-bcrypt', function() {
    $pw = new \FastSitePHP\Security\Password();

    $password = 'Password123';
    $pepper = $pw->generatePepper();
    $pepper2 = $pw->generatePepper(10);
    $hash = $pw->pepper($pepper)->hash($password);
    $matches = ($pepper === $pw->pepper());

    $verified1 = $pw->verify($password, $hash);

    $error = null;
    try {
        $pw->pepper('@');
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }

    // Pepper can't be cleared so create a new class instance
    $pw = new \FastSitePHP\Security\Password();
    $verified2 = $pw->verify($password, $hash);

    return array(
        'pepper_len' => strlen(hex2bin($pepper)),
        'pepper2_len' => strlen(hex2bin($pepper2)),
        'matches' => $matches,
        'error' => $error,
        'verified1' => $verified1,
        'verified2' => $verified2,
    );
});

// Argon2 works only with PHP 7.2+ otherwise an expected error will be returned
$app->get('/hash-with-argon2', function() {
    $result = '';
    try {
        $pw = new \FastSitePHP\Security\Password();

        $password = 'Password123';
        $hash = $pw->algo('Argon2')->hash($password);

        $result = $pw->algo();
        $result .= '[' . substr($hash, 0, 29) . ']'; // '$argon2i$v=19$m=1024,t=2,p=2$'
        $result .= '[' . json_encode($pw->options()) . ']';

        $result .= '[verify1=' . json_encode($pw->verify($password, $hash)) . ']';
        $password = 'Password1234';
        $result .= '[verify2=' . json_encode($pw->verify($password, $hash)) . ']';

        $options = array(
            'memory_cost' => 2048,
            'time_cost' => 10,
            'threads' => 4,
        );
        $result .= '[needsRehash1=' . json_encode($pw->needsRehash($hash)) . ']';
        $result .= '[needsRehash2=' . json_encode($pw->options($options)->needsRehash($hash)) . ']';

        $hash = $pw->hash($password);
        $result .= '[' . substr($hash, 0, 30) . ']'; // '$argon2i$v=19$m=2048,t=10,p=4$'
        $result .= '[' . json_encode($pw->options()) . ']';
    } catch (\Exception $e) {
        if (PHP_VERSION_ID < 70200) {
            $result = $e->getMessage();
        } else {
            $result = 'Unexpected Error: ' . $e->getMessage();
        }
    }
    return $result;
});

$app->get('/use-pepper-with-argon2', function() {
    $result = '';
    try {
        $pw = new \FastSitePHP\Security\Password();

        $password = 'Password123';
        $pepper = $pw->generatePepper();
        $hash = $pw->algo('Argon2')->pepper($pepper)->hash($password);

        $verified1 = $pw->verify($password, $hash);
        $pw = new \FastSitePHP\Security\Password();
        $verified2 = $pw->algo('Argon2')->verify($password, $hash);

        $result = json_encode(array($verified1, $verified2));
    } catch (\Exception $e) {
        if (PHP_VERSION_ID < 70200) {
            $result = $e->getMessage();
        } else {
            $result = 'Unexpected Error: ' . $e->getMessage();
        }
    }
    return $result;
});

$app->get('/misc', function() {
    $pw = new \FastSitePHP\Security\Password();

    $error = null;
    try {
        $pw->algo('Error');
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }

    // Generate 1000 passwords
    // They should be all unique, in reality a duplicate password is
    // unlikely to be generated. 1000 passwords is a quick test
    $count = 0;
    $passwords = array();
    for ($n = 0; $n < 1000; $n++) {
        $passwords[] = $pw->generate();
        $count++;
    }

    return array(
        'error' => $error,
        'findCost_type' => gettype($pw->findCost()),
        'generated' => $count,
        'unique' => count(array_unique($passwords)),
    );
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
