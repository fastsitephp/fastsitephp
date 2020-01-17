<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * This is an optional install Script for FastSitePHP's [Starter Site] and
 * [Main Website]. Either Composer (Dependency Manager) or this file can be
 * used to setup the default projects needed for a site. This file allows
 * for quick and easy setup without having to install Composer or any
 * third-party tools. Composer handles already downloaded projects so you
 * can use this file first and then later switch to Composer if adding
 * additional dependencies to your site.
 *
 * All files downloaded including the FastSitePHP Framework are
 * relatively small in size so this script runs quickly.
 *
 * To run:
 *   (*) Make sure that you have write file permissions to the root directory
 *   (*) Run from command line using [php install.php]
 *   (*) Or open [install.php] in your browser
 *       Optionally updated constants to change behavior near the top of this file:
 *         INSTALL_PSR_LOG = true/false
 *         INSTALL_THIRD_PARTY = true/false
 *         ALWAYS_INSTALL_POLYFILLS = true/false
 *   (*) When this script runs it will download [cacert.pem] and
 *       save it to the temp directory (the file is always verified).
 *
 * Errors:
 *   (*) If you receive an file lock error while running and are running
 *       from a Code Editor or IDE then close and re-open the editor and
 *       run this script again. If it happens again make sure you have the
 *       related directories/folders closed in your file manager.
 *   (*) If you are on a very old version of PHP [5.3 or 5.4] you may have
 *       to manually download the files or upgrade PHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

// Show and report on all errors
set_time_limit(0);
error_reporting(-1);
ini_set('display_errors', 'on');

// Constants
const ERR_SCRIPT_FAILED = 1;
define('IS_CLI', (php_sapi_name() === 'cli'));
define('LINE_BREAK', (IS_CLI ? PHP_EOL : '<br>'));
define('VENDOR_DIR', __DIR__ . '/../vendor');

// Change as desired based on your project
const INSTALL_PSR_LOG = true; // [php-fig/log]
const INSTALL_MARKDOWN = true; // [erusev/parsedown]
const ALWAYS_INSTALL_POLYFILLS = false; // [ircmaxell/password_compat] and [paragonie/random_compat]

// CA certificates are download from [https://curl.haxx.se/docs/caextract.html]
// and saved at the following location:
//     http://fastsitephp.s3-us-west-1.amazonaws.com/cacert/2019-10-16/cacert.pem
//     http://fastsitephp.s3-us-west-1.amazonaws.com/cacert/2019-10-16/cacert.pem.sha256
// The file is over 220 kb so rather than including it with source the file is
// downloaded and verified from an HTTP link before any HTTPS requests are made.
const CACERT_URL = 'http://fastsitephp.s3-us-west-1.amazonaws.com/cacert/2019-10-16/cacert.pem';
const CACERT_SHA256 = '5cd8052fcf548ba7e08899d8458a32942bf70450c9af67a0850b4c711804a2e4';
define('CACERT_FILE', sys_get_temp_dir() . '/install-cacert.pem');

/**
 * Projects to download
 */
$downloads = array(
    array(
        'url' => 'https://github.com/fastsitephp/fastsitephp/archive/{tag_name}.zip',
        'save_file' => __DIR__ . '/FastSitePHP.zip',
        'check_file' => VENDOR_DIR . '/fastsitephp/src/Application.php',
        'composer_file' => VENDOR_DIR . '/fastsitephp/fastsitephp/src/Application.php', // Composer uses an extra nested directory
        'rename_from' => VENDOR_DIR . '/fastsitephp-{tag_name}',
        'rename_to' => VENDOR_DIR . '/fastsitephp',
        'skip_check' => __DIR__ . '/../src/Application.php', // Skip download if running within Framework
    ),
    array(
        'url' => 'https://github.com/php-fig/log/archive/1.1.2.zip',
        'save_file' => __DIR__ . '/psr-log.zip',
        'check_file' => VENDOR_DIR . '/psr/log/Psr/Log/AbstractLogger.php',
        'mkdir' => VENDOR_DIR . '/psr',
        'rename_from' => VENDOR_DIR . '/log-1.1.2',
        'rename_to' => VENDOR_DIR . '/psr/log',
        'install' => INSTALL_PSR_LOG,
    ),
    array(
        'url' => 'https://github.com/erusev/parsedown/archive/1.7.3.zip',
        'save_file' => __DIR__ . '/parsedown.zip',
        'check_file' => VENDOR_DIR . '/erusev/parsedown/Parsedown.php',
        'mkdir' => VENDOR_DIR . '/erusev',
        'rename_from' => VENDOR_DIR . '/parsedown-1.7.3',
        'rename_to' => VENDOR_DIR . '/erusev/parsedown',
        'install' => INSTALL_MARKDOWN,
    ),
    array(
        'url' => 'https://github.com/ircmaxell/password_compat/archive/v1.0.4.zip',
        'save_file' => __DIR__ . '/password_compat.zip',
        'check_file' => VENDOR_DIR . '/ircmaxell/password-compat/lib/password.php',
        'mkdir' => VENDOR_DIR . '/ircmaxell',
        'rename_from' => VENDOR_DIR . '/password_compat-1.0.4',
        'rename_to' => VENDOR_DIR . '/ircmaxell/password-compat',
        'install' => (PHP_VERSION_ID < 50500) || ALWAYS_INSTALL_POLYFILLS,
    ),
    array(
        'url' => 'https://github.com/paragonie/random_compat/archive/v2.0.18.zip',
        'save_file' => __DIR__ . '/random_compat.zip',
        'check_file' => VENDOR_DIR . '/paragonie/random_compat/lib/random.php',
        'mkdir' => VENDOR_DIR . '/paragonie',
        'rename_from' => VENDOR_DIR . '/random_compat-2.0.18',
        'rename_to' => VENDOR_DIR . '/paragonie/random_compat',
        'install' => (PHP_VERSION_ID < 70000) || ALWAYS_INSTALL_POLYFILLS,
    ),
);

/**
 * Create the root [vendor] directory where
 * PHP projects are downloaded and extracted to.
 */
function createVendorDir() {
    if (!is_dir(VENDOR_DIR)) {
        mkdir(VENDOR_DIR);
    }
}

/**
 * Download and verify a [cacert.pem] file for HTTPS requests.
 * The file will downloaded via an HTTP request and then saved
 * to the system's temp directory. Everytime the file is used
 * the contents are verified using a SHA-256 hash.
 */
function downloadCACert() {
    // File already downloaded and verified
    if (is_file(CACERT_FILE) && hash_file('sha256', CACERT_FILE) === CACERT_SHA256) {
        return;
    }

    // Status
    echo str_repeat('-', 80) . LINE_BREAK;
    echo 'Downloading: ' . CACERT_URL . LINE_BREAK;

    // Download and verify contents
    $contents = file_get_contents(CACERT_URL);
    if (hash('sha256', $contents) !== CACERT_SHA256) {
        echo 'ERROR - Downloaded CA Cert Contents does not match the known SHA-256 Hash:' . LINE_BREAK;
        echo 'Expected: ' . CACERT_SHA256 . LINE_BREAK;
        echo 'Content Hash: ' . hash('sha256', $contents) . LINE_BREAK;
        echo 'Response Headers:' . LINE_BREAK;
        foreach ($http_response_header as $header) {
            echo $header . LINE_BREAK;
        }
        echo 'Response:' . LINE_BREAK;
        var_dump($contents);
        exit(ERR_SCRIPT_FAILED);
    }

    // Write to file and verify written file
    file_put_contents(CACERT_FILE, $contents);
    if (hash_file('sha256', CACERT_FILE) !== CACERT_SHA256) {
        echo 'ERROR - Downloaded CA Cert File does not match the known SHA-256 Hash:' . LINE_BREAK;
        echo 'Expected: ' . CACERT_SHA256 . LINE_BREAK;
        echo 'File Hash: ' . hash_file('sha256', CACERT_FILE) . LINE_BREAK;
        exit(ERR_SCRIPT_FAILED);
    }

    // File is valid if code execution makes it here
    echo 'CA Cert File Saved: ' . CACERT_FILE . LINE_BREAK;
    echo 'CA Cert File Hash Verified: ' . CACERT_SHA256 . LINE_BREAK;
}

/**
 * Use GitHub's API to get the latest release of FastSitePHP
 *
 * @return string
 */
function getLatestRelease() {
    $url = 'https://api.github.com/repos/fastsitephp/fastsitephp/releases/latest';
    $http_options = array(
        'ssl' => array(
            'ciphers' => 'HIGH',
            'cafile' => CACERT_FILE,
        ),
        'http' => array(
            'method' => 'GET',
            'header' => array(
                'User-Agent: php/' . phpversion(),
            ),
        ),
    );
    // Status
    echo str_repeat('-', 80) . LINE_BREAK;
    echo 'Getting latest release of FastSitePHP: ' . $url . LINE_BREAK;
    $context = stream_context_create($http_options);
    $response = file_get_contents($url, null, $context);
    $json = json_decode($response);
    if ($json && isset($json->tag_name)) {
        echo 'Version: ' . $json->tag_name . LINE_BREAK;
        return $json->tag_name;
    } else {
        echo 'ERROR - Unable to determine latest FastSitePHP Release using URL:' . LINE_BREAK;
        echo $url . LINE_BREAK;
        exit(ERR_SCRIPT_FAILED);
    }
}

/**
 * Download the Zip File if not already downloaded.
 *
 * @param string $url
 * @param string $path
 */
function downloadZip($url, $path) {
    // Does the file already exist?
    if (is_file($path)) {
        echo 'Skipping Download, File already exists: ' . $path . LINE_BREAK;
        return;
    }

    // Status
    echo 'Downloading: ' . $url . LINE_BREAK;
    echo 'Save Location: ' . $path . LINE_BREAK;

    // Build HTTP Request
    $http_options = array(
        'ssl' => array(
            'ciphers' => 'HIGH',
            'cafile' => CACERT_FILE,
        ),
    );
    $context = stream_context_create($http_options);
    $response = file_get_contents($url, null, $context);

    // Look for a 200 Response Code, example:
    //     'HTTP/1.0 200 OK'
    //     'HTTP/1.1 200 OK'
    // Headers are returned in order so all redirect headers will appear first.
    $is_ok = false;
    foreach ($http_response_header as $header) {
        preg_match('/HTTP\/[1|2].[0|1] ([0-9]{3})/', $header, $matches);
        if ($matches) {
            $status_code = (int)$matches[1];
            if ($status_code === 200) {
                $is_ok = true;
                break;
            }
        }
    }

    // Save Zip file if response is ok
    if ($is_ok) {
        file_put_contents($path, $response);
        echo 'Download Ok' . LINE_BREAK;
    } else {
        echo 'Error Downloading File, Response Headers:' . LINE_BREAK;
        foreach ($http_response_header as $header) {
            echo $header . LINE_BREAK;
        }
        exit(ERR_SCRIPT_FAILED);
    }
}

/**
 * Extract the downloaded Zip File
 *
 * @param array $download
 */
function extractZip($download) {
    // Status
    $path = $download['save_file'];
    echo 'Extracting: ' . $path . LINE_BREAK;

    // Extract from Zip
    $zip = new \ZipArchive;
    $zip->open($path);
    $success = $zip->extractTo(VENDOR_DIR);
    $zip->close();

    // Check Result
    if ($success) {
        echo 'File extracted successfully' . LINE_BREAK;
    } else {
        echo 'Error extracting Zip' . LINE_BREAK;
        exit(ERR_SCRIPT_FAILED);
    }

    // Create the [vendor/{owner}] Directory if needed
    if (isset($download['mkdir'])) {
        $dir = $download['mkdir'];
        if (!is_dir($dir)) {
            echo 'Making Directory: ' . $dir . LINE_BREAK;
            mkdir($dir);
        }
    }

    // Rename from version specific directory to the
    // directory that is compatible for composer.
    if (isset($download['rename_from'])) {
        echo 'Rename From: ' . $download['rename_from'] . LINE_BREAK;
        echo 'Rename To: ' . $download['rename_to'] . LINE_BREAK;
        rename($download['rename_from'], $download['rename_to']);
    }

    // Make sure an expected file is found to verify the zip extraction
    $path = $download['check_file'];
    if (is_file($path)) {
        echo 'Confirmed File: ' . $path . LINE_BREAK;
    } else {
        echo 'ERROR - the following file was not found after unzipping the Zip file. Check your file permissions and any warning or error messages' . LINE_BREAK;
        echo $path . LINE_BREAK;
        exit(ERR_SCRIPT_FAILED);
    }

    // Status
    echo 'Success project extracted' . LINE_BREAK;
}

/**
 * Return true if project already exists in the correct location.
 *
 * @param array $download
 * @return bool
 */
function projectIsDownloaded($download) {
    if (isset($download['skip_check']) && is_file($download['skip_check'])) {
        return true;
    }
    $file_exists = is_file($download['check_file']);
    if (!$file_exists && isset($download['composer_file'])) {
        $file_exists = is_file($download['composer_file']);
    }
    return $file_exists;
}

/**
 * Return true if the request is running from localhost '127.0.0.1' (IPv4)
 * or '::1' (IPv6) and if the web server software is also running on localhost.
 *
 * This function is based on [FastSitePHP\Web\Request->isLocal()].
 * See comments in the source function for full details.
 *
 * @return bool
 */
function isLocal() {
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
    return (
        ($client_ip === '127.0.0.1' || $client_ip === '::1')
        && ($server_ip === '127.0.0.1' || $server_ip === '::1')
    );
}

/**
 * Main function
 *
 * @param array $downloads
 */
function main($downloads) {
    // Running from command line or localhost? (both client and server are required)
    if (!(IS_CLI || isLocal())) {
        echo 'No Action Taken - Exiting Script. Running this file requires server access. You need to run from either Command Line or directly on the server/computer.' . LINE_BREAK;
        exit(ERR_SCRIPT_FAILED);
    }

    // Make sure [cacert.pem] is downloaded and valid
    downloadCACert();

    // Get the latest release tag/number of FastSitePHP
    $tag_name = getLatestRelease();

    // Download and Extract Zip Files
    createVendorDir();
    foreach ($downloads as $download) {
        if (strpos($download['url'], '{tag_name}') !== false) {
            // For FastSitePHP lastest release
            $download['url'] = str_replace('{tag_name}', $tag_name, $download['url']);
            $download['rename_from'] = str_replace('{tag_name}', $tag_name, $download['rename_from']);
        }
        // Download or Skip
        echo str_repeat('-', 80) . LINE_BREAK;
        $install = (!isset($download['install']) || $download['install'] === true);
        if (!$install) {
            echo 'Skipping download of project [' . $download['url'] . ']' . LINE_BREAK;
        } else {
            $is_downloaded = projectIsDownloaded($download);
            if ($is_downloaded) {
                echo 'Project [' . $download['url'] . '] is already downloaded' . LINE_BREAK;
            } else {
                downloadZip($download['url'], $download['save_file']);
                extractZip($download);
                unlink($download['save_file']); // Delete the Zip file
            }
        }
    }

    // Create a [vendor/autoload.php] file unless one already exists
    echo str_repeat('-', 80) . LINE_BREAK;
    $autoload_path = VENDOR_DIR . '/autoload.php';
    if (is_file($autoload_path)) {
        echo 'Using existing autoloader file: ' . $autoload_path . LINE_BREAK;
    } else {
        $source = __DIR__ . '/fast_autoloader.php';
        echo 'Creating autoloader file' . LINE_BREAK;
        echo 'Copying from: ' . $source . LINE_BREAK;
        echo 'Copying to: ' . $autoload_path . LINE_BREAK;
        copy($source, $autoload_path);
    }

    // PHP continues code execution by default when there is
    // an error so make sure there were no errors.
    echo str_repeat('=', 80) . LINE_BREAK;
    $err = error_get_last();
    if ($err !== null) {
        echo 'WARNING - an error has occurred. This site may or may not work. Review the full output and take any action needed (for example setting file permissions).' . LINE_BREAK;
        exit(ERR_SCRIPT_FAILED);
    } else {
        echo 'SUCCESS - All files are downloaded and extracted to the correct folder' . LINE_BREAK;
    }
}

/**
 * Start of Script
 */
main($downloads);
