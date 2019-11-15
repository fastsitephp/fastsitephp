<?php
// Manual Test file to verify helper functions [Crypto::encryptFile] and [Crypto::decryptFile].
// The FileEncryption class is well tested but not the two helper functions. Once Unit Tests
// are added then this file can be deleted.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

// File Paths
$file_path = __DIR__ . '/crypto_test_20mb.txt';
$enc_file = __DIR__ . '/crypto_test_20mb.enc.txt';
$output_file = __DIR__ . '/crypto_test_20mb.dec.txt';

// Delete files if they already exist from previous test
$files = array($file_path, $enc_file, $output_file);
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

// Create a 20 Megabyte File with all 'a' characters
$one_megabyte = 1024 * 1024;
$fp = fopen($file_path, 'w');
for ($n = 0; $n < 20; $n++) {
    fwrite($fp, str_repeat('a', $one_megabyte));
}
fclose($fp);

// Create FileEncryption Class
$crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
if (PHP_OS === 'WINNT') {
    $crypto->processFilesWithCmdLine(false);
}
$key = $crypto->generateKey();
$app->config['ENCRYPTION_KEY'] = $key;

// Encrypt with FileEncryption then Decrypt with Crypto
// $crypto->encryptFile($file_path, $enc_file, $key);
// \FastSitePHP\Security\Crypto::decryptFile($enc_file, $output_file);
//
// Or Encrypt with Crypto then Decrypt with FileEncryption
\FastSitePHP\Security\Crypto::encryptFile($file_path, $enc_file);
$crypto->decryptFile($enc_file, $output_file, $key);

echo 'Finished, check files';
