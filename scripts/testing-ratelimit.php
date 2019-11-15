<?php

// Test Script for manually testing the RateLimit class.
// In the future this code can be used as a starting point 
// when creating the full unit tests and demos. In the meantime 
// this manually helps confirm the class works as expected.
// 
// Demos are not yet decided on but likely will include something
// similar to the copied/echo function versions at the bottom
// of this file.

// In case autoloader is not found:
error_reporting(-1);
ini_set('display_errors', 'on');

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

// Storage
$file_path = sys_get_temp_dir() . '/ratelimit-cache.sqlite';
$storage = new \FastSitePHP\Data\KeyValue\SqliteStorage($file_path);

// Data Example

// 10 requests in 20 seconds
// Using a large max allowed and large duration allows for bursts when
// using 'token-bucket' but also allows for the max to exceed 10 in 20 seconds
// because Token Bucket is based on rate.
//
// $max_allowed = 10;
// $duration = 20;

// 1 per 2 seconds
// Using a smaller max_allowed allows for better control when using 'token-bucket'
// $max_allowed = 1;
// $duration = 2;

// 2 per minute
// $max_allowed = 2;
// $duration = 60;

// 2 per day
// $max_allowed = 2;
// $duration = 60 * 60 * 24;

// 5 Requests per 8 seconds
$max_allowed = 5;
$duration = 8;

// 2 Requests per second
// $max_allowed = 2;
// $duration = 1;

// 10 per hour
// $max_allowed = 10;
// $duration = (60 * 60);

// 1 Request per second (default)
// $max_allowed = 1;
// $duration = 1;

// Basic Client IP
$id = $_SERVER['REMOTE_ADDR'];
//
// Client IP if behind a Firewall or Load Balancer
// $req = new \FastSitePHP\Web\Request();
// $id = $req->clientIp('from proxy');
//
// User ID
// $id = 'abc123';

// If all values are empty it defaults to 1 request per 1 second
$options = [
    'max_allowed' => $max_allowed,
    'duration' => $duration,
    'storage' => $storage,
    'id' => $id,

    // Optional if using RateLimiter for multiple site features
    // 'key' => 'messages-sent',

    // Defaults to [fixed-window-counter]
    // 'algo' => 'fixed-window-counter',
    // 'algo' => 'token-bucket',

    // Only applies to [filterRequest()]:
    //'headers' => false,
];

// --------------------------------------------------------
// Test Request Filter Function
// --------------------------------------------------------

// Uncommenting this confirms that [filterRequest()] keeps
// headers from the Application Object and overwrites 'Content-Type'.
// $app->header('X-Test', 'Test');
// $app->header('Content-Type', 'application/json');

// $rate_limit = new \FastSitePHP\Security\Web\RateLimit();
// $rate_limit->filterRequest($app, $options);
// echo 'Passed Filter';
// exit();

// --------------------------------------------------------
// Check [allow()] function and Debug Code
// --------------------------------------------------------

// $result = json_encode(fixedWindowCounter($max_allowed, $duration), JSON_PRETTY_PRINT);
// $result = json_encode(tokenBucket($max_allowed, $duration), JSON_PRETTY_PRINT);

echo "Allowing ${max_allowed} requests per ${duration} seconds<br>";
$rate_limit = new \FastSitePHP\Security\Web\RateLimit();
$result = json_encode($rate_limit->allow($options), JSON_PRETTY_PRINT);

echo "<br><br><strong><pre>${result}</pre></strong>";

$script = <<<'EOD'
    <br><br><div><strong>Time since page was refreshed: </strong><span id="timer">0</span>
    <script type="module">
        let counter = 0;
        let label = document.getElementById('timer');
        window.setInterval(() => { counter++; label.textContent = counter; }, 1000);
    </script>
EOD;
echo $script;

/**
 * Debug function, this is a copy of the [RateLimit->fixedWindowCounter()],
 * however it contains 'echo' statements to see the value of variables.
 * 
 * Saves data to session. 
 */
function fixedWindowCounter($max_allowed, $duration)
{
    $now = time();
    $allowed = true;
    $retry_after = null;
    $key = 'rate-limit-fwc';

    session_start();
    // session_unset();

    // Get saved value
    $expires = null;
    $remaining = null;
    $value = (isset($_SESSION[$key]) ? $_SESSION[$key] : null);
    if ($value !== null) {
        $pattern = '/^(\d+):(\d+)$/';
        if (preg_match($pattern, $value, $matches)) {
            $expires = (int)$matches[1];
            $remaining = (float)$matches[2];
        }
    }

    // Check Request
    echo "Allowing ${max_allowed} requests per ${duration} seconds<br>";
    if ($expires === null || $expires < $now) {
        // Reset to (max - 1) because the counter just started or has expired
        $expires = $now + $duration;
        $remaining = $max_allowed - 1;
        echo '<span style="color:blue;">Reset to Rate</span><br>';
    } elseif ($remaining <= 0) {
        // Limit reached
        $allowed = false;
        $retry_after = max(1, $expires - $now);
        echo '<span style="color:red;">Invalid</span><br>';            
    } else {
        // Decrease counter
        $remaining--;
        echo '<span style="color:white; background-color:green;">Allowed</span><br>';
    }
    echo "Remaining: ${remaining} <br>";
    
    // Build Headers
    $headers = array();
    if ($retry_after !== null) {
        $headers['Retry-After'] = $retry_after;
    }
    $plural1 = ($max_allowed === 1 ? '' : 's');
    $plural2 = ($duration === 1 ? '' : 's');
    $headers['X-RateLimit-Limit'] = "${max_allowed} Request${plural1} per ${duration} Second${plural2}";
    $headers['X-RateLimit-Remaining'] = $remaining;
    $headers['X-RateLimit-Reset'] = $expires;

    // Save status and return result
    $_SESSION[$key] = $expires . ':' . $remaining;
    echo 'Saved: ' . $_SESSION[$key] . '<br>';
    return array($allowed, $headers);
}

/**
 * Debug function, this is a copy of the [RateLimit->tokenBucket()],
 * however it contains 'echo' statements to see the value of variables.
 * 
 * Saves value to session. 
 */
function tokenBucket($max_allowed, $duration)
{
    $now = time();
    $allowed = true;
    $retry_after = null;
    $key = 'rate-limit-tb';

    session_start();
    // session_unset();

    // Get saved value
    $prev_request = null;
    $requests_allowed = null;
    $value = (isset($_SESSION[$key]) ? $_SESSION[$key] : null);
    if ($value !== null) {
        $pattern = '/^(\d+):([0-9.]+)$/';
        if (preg_match($pattern, $value, $matches)) {
            $prev_request = (int)$matches[1];
            $requests_allowed = (float)$matches[2];
        }
    }

    // Check Request
    echo "Allowing a rate of ${max_allowed} requests per ${duration} seconds<br>";
    if ($prev_request === null) {
        $requests_allowed = $max_allowed - 1;
    } else {
        $time_passed = $now - $prev_request;
        echo "Time Passed: ${time_passed} <br>";
        $allowed_per_sec = $max_allowed / $duration;
        echo "Allowed Per Second: ${allowed_per_sec} <br>";
        echo "Starting Allowed: ${requests_allowed} <br>";
        $requests_allowed += $time_passed * $allowed_per_sec;
        echo "New Allowed: ${requests_allowed} <br>";
        if ($requests_allowed > $max_allowed) {
            // Reset to (max - 1) because the bucket has expired
            $requests_allowed = $max_allowed - 1;
            echo '<span style="color:blue;">Reset to Rate</span><br>';
        } elseif ($requests_allowed < 1.0) {
            // Limit reached
            $allowed = false;
            $requests_allowed = 0;
            // Round-up to next int so 1.2 results with 2
            $retry_after = ceil((1 - $requests_allowed) * ($duration / $max_allowed));
            echo '<span style="color:red;">Invalid</span><br>';
        } else {
            // Decrease counter
            $requests_allowed--;
            echo '<span style="color:white; background-color:green;">Allowed</span><br>';
        }
    }
    echo "Allowed: ${requests_allowed} <br>";

    // Build Headers
    $headers = array();
    if ($retry_after !== null) {
        $headers['Retry-After'] = $retry_after;
    }

    // Save status and return result
    $_SESSION[$key] = $now . ':' . $requests_allowed;
    echo 'Saved: ' . $_SESSION[$key] . '<br>'; 
    return array($allowed, $headers);
}
