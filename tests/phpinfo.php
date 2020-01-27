<?php

// The [tests] folder should not be left in a public location on a live server but
// just in case make sure this file doesn't run on a live server unless the user
// is using localhost. This logic is based on [FastSitePHP\Web\Request->isLocal()].
// See comments in the source function for full details.

// Get Client IP
$client_ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);

// Get Server IP
$server_ip = null;
if (isset($_SERVER['SERVER_ADDR'])) {
    $server_ip = $_SERVER['SERVER_ADDR'];
} elseif (isset($_SERVER['LOCAL_ADDR'])) {
    $server_ip = $_SERVER['LOCAL_ADDR'];
} elseif (php_sapi_name() === 'cli-server' && isset($_SERVER['REMOTE_ADDR'])) {
    $server_ip = $_SERVER['REMOTE_ADDR'];
}

// Normalize IP's if needed
$client_ip = ($client_ip === '[::1]' ? '::1' : $client_ip);
$server_ip = ($server_ip === '[::1]' ? '::1' : $server_ip);

// Check IP's
$show_info = (
    ($client_ip === '127.0.0.1' || $client_ip === '::1')
    && ($server_ip === '127.0.0.1' || $server_ip === '::1')
);

// Show PHP Info (this can include environment variables)
if ($show_info) {
    phpinfo();
} else {
    echo 'This only runs when using localhost. You can change this by modifying the file on the server.';
}
