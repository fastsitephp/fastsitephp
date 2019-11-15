<?php
// =============================================================================
//
// This script is used to manually test that the computer/server can create,
// encrypt, and decrypt a 3 gb file. Some file systems have a 2 gb file limit
// (2^31 in bytes) so if this script successfully runs then functions [encryptFile()]
// and [decryptFile] from the class [FastSitePHP\Security\Crypto\FileEncryption]
// can be used on the computer with large files. To verify this file this 
// script encrypts the file using a known IV/Key and also verifies the file 
// hash as each file is created.
//
// Run time will vary greatly depending upon how fast the server is however
// this script is generally expected to run in about 5 minutes on most servers.
// On some servers this can run in as little as 2 minutes and currently the 
// longest runtime was seen at 25 minutes.
//
// In addition to this script to test the PHP code you can manually test
// the shell commands using a bash script at [shell/bash/encrypt.sh].
// The bash script doesn't require PHP and should run on most Linux OS's 
// wihtout having to install anything.
//
// Instructions
// 1) Copy this file to the [html] folder or a location that can be
//    viewed from your brower. If copying to a different location then
//    modify the statement that loads the autoloader [autoload.php] so
//    that it loads the correct files.
// 2) Run the script from a browser and wait till it completes. If there is a 
//    permissions error then correct the permissions and run again.
// 3) You will see either a success or error message depending upon the result.
// 4) Manually delete the files when done as they will take a lot of disk space.
//
// Designed and Developed by Conrad Sollitt, 2017
//
// =============================================================================

// Setup the autoloader and set FastSitePHP to handle errors/exceptions
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// Create the Crypto Object and specify secret keys and IV used 
// for encryption and decryption. 
//
// IMPORTANT - Since this is a published "secret key" DO NOT copy it
// and use it in your applications/site. To create secret keys for your 
// application see the [generateKey()] function from the [Crypto] class.
$crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
$crypto->displayCmdErrorDetail(true);
$crypto->processFilesWithCmdLine(true);

$key_enc  = 'b2e8ff4746c1006adafeb42235554363acf22391941b86b22a7b28c8a591ea4f';
$key_hmac = '6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab';

// This test uses a known hard-coded IV for encryption.
// In real applications the IV should always be secure, random, 
// and generated each time data is encrypted.
$iv = '0ee221ef9e00dfa69efb3b1112bfbb2f';

// Make sure there is no timeout because this script 
// will likely take about 5 mintues or more to run.
set_time_limit(0);

// Uncomment if needed to see permissions and command paths
// header('Content-type: text/plain');
// var_dump($crypto->checkFileSetup());
// exit();

// -----------------------------------------------------------
// Functions
// -----------------------------------------------------------

function createNullFile($file_path, $file_size)
{
	// Choose commands to look up based on Mac or Linux/Unix
	if (PHP_OS === 'Darwin') {
		$cmds = array('mkfile', 'dd');
	} else {
		$cmds = array('xfs_mkfile', 'fallocate', 'truncate', 'dd');
	}
	
	// Check to see if the command exists using the [which] command.
	// If running manually from the command line [type] can be used
	// to provide additional info.
	$file_cmd = null;
	foreach ($cmds as $cmd) {
		$file_cmd = exec('which ' . $cmd);
		if (!($file_cmd === null || $file_cmd === '')) {
			$file_cmd = $cmd;
			break;
		}
	}
	
	// Build the command
	switch ($file_cmd) {
		case 'mkfile':
			$cmd = 'mkfile -n ' . $file_size . ' "' . $file_path . '"';
			break;
		case 'xfs_mkfile':
			$cmd = 'xfs_mkfile ' . $file_size . ' "' . $file_path . '"';
			break;
		case 'fallocate':
			$cmd = 'fallocate -l ' . $file_size . ' "' . $file_path . '"';
			break;
		case 'truncate':
			$cmd = 'truncate -s ' . $file_size . ' "' . $file_path . '"';
			break;
		case 'dd':
			$cmd = 'dd if=/dev/zero of="' . $file_path . '" bs=' . $file_size . ' count=1';
			break;
		default:
			throw new \Exception('Unexpected Error - Unable to find a commnad on this OS for creating empty files.');
			break;
	}
	$cmd .= ' 2>&1'; // Make sure errors are displayed
	runCmd($cmd);
	
	// Make sure that the file was created
	if (is_file($file_path)) {
		echo '<p>File created: [' . $file_path . ']</p>';
	} else {
		echo 'Command [' . $file_cmd . '] ran successfully but file [' . $file_path . '] was not found. Check to see if it was created and if permissions are correct.';
		exit();
	}
}

// This function creates a compatible file for [Crypto->decryptFile()]
// and is based on [encryptFile()] however it uses a known IV
// rather than securely generating a random IV and this function
// excludes some of the validation.
function createFile($file_path, $enc_file, $key_enc, $key_hmac, $iv) 
{
	// Make sure any previous test files were deleted
	if (is_file($enc_file)) {
		throw new \Exception(sprintf('File encryption failed because the file for encryption [%s] already exists. Delete your previous test files and try again.', $enc_file));
    }
    
	// Validate disk space (Not on 32-Bit PHP though)
	if (PHP_INT_SIZE !== 4) {
	    $file_size = filesize($file_path);
	    $disk_space = disk_free_space(dirname($enc_file));
	    $ten_megabytes = (1024 * 1024 * 10);
	    if (($file_size + $ten_megabytes) > $disk_space) {
	        throw new \Exception(sprintf('File encryption failed because there is not enough disk space available on [%s] for file [%s]. The function [%s] requires the disk to have least the size of the file to encrypt plus an additional 10 megabytes.', dirname($enc_file), $file_path, __FUNCTION__));
	    }
    }
    
    // Get path for the [xxd] command
    $xxd = xxdPath();
    
    // Encrypt using openssl command line
    $cmd = 'openssl enc -aes-256-cbc -in "' . $file_path . '" -out "' . $enc_file . '" -iv ' . $iv . '  -K ' . $key_enc . ' 2>&1';
    runCmd($cmd);
    
    // Append IV to the end of the file
    $cmd = 'echo ' . $iv . ' | ' . $xxd . ' -r -p >> "' . $enc_file  . '" 2>&1';
    runCmd($cmd);
    
    // HMAC the file using SHA-256 and append the result to end of the file
	$cmd = 'cat "' . $enc_file . '" | openssl dgst -sha256 -mac hmac -macopt hexkey:' . $key_hmac . ' -binary >> "' . $enc_file . '" 2>&1';
    runCmd($cmd);
    
    // Verify that the enrypted file can be read by PHP
    if (!is_file($enc_file)) {
        throw new \Exception(sprintf('File encryption failed because the encrypted file [%s] was not found after commands successfully ran. The error is unexpected so you may want to verify permissions for the web server or user running PHP and if the file was actually created.', $enc_file));
    }	
}

// Compare an md5 hash of the created file with the valid known value.
function checkHash($file, $expected_hash)
{
	// Calculate md5 using openssl as openssl is required for file encryption
	// and will be installed on all systems by default. This can also be 
	// calculated using the following commands:
	// macOS/FreeBSD: [md5 -q {{file}}]
	// Linux:         [md5sum {{file}} | cut -d ' ' -f 1]
	$cmd = 'openssl dgst -binary -md5 ' . $file . ' | ' . xxdPath() . ' -p';
	
	// Calculate md5 from shell command
	$file_hash = runCmd($cmd, true);
	echo '<p><strong>md5:</strong> [' . $file_hash . ']</p>';

	// NOTE - This is a manual test so time safe compare is not needed
	// however to compare hashes in a secure manner use [hash_equals()].
	if ($file_hash !== $expected_hash) {
		echo '<p><strong style="color:red; border:2px solid red; padding:10px; margin:10px auto; display:inline-block;">';
		echo 'Fatal error when working with large files on this computer or server because the md5 value of the created file does not match the known valid value.';
		echo '</strong></p>';
		echo '<strong>Expected:</strong> ' . $expected_hash;
		exit();
	}
}

// Run a shell command and check the result
function runCmd($cmd, $expect_ouput = false)
{
    // Run the command saving the exit status and all output to an array
    // var_dump($cmd);
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
				$error .= ' One of the command line executables used was not found. This can happen if the command doesn’t exist on the server or if the web server account user can’t see the command.';
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

// ---------------------------------------------------------
// Start of Script
// ---------------------------------------------------------

// Define options to test.
// To see how this function would error simply change '1g' to '1m'.
$tests = array(
	array(
		'size' => '1g',
		'hash_plain' => 'cd573cfaace07e7949bc0c46028904ff',
		'hash_enc' => '6caa8477d12b6cafb47a2ddc2969bcbd',
	),
	array(
		'size' => '3g',
		'hash_plain' => 'c698c87fb53058d493492b61f4c74189',
		'hash_enc' => 'f5aeeb7d2cd73d358783f39a3aaa5821',
	),
);

// Uncomment to skip the 3gb test
// array_pop($tests);

// Run each test (1gb file and a 3gb file).
// Files will be saved the same directory as this PHP script file.
foreach ($tests as $test) {
	$file_path = dirname(__FILE__) . '/temp_large_file_' . $test['size'] . 'b';
	$enc_file = $file_path . '.enc';
	$output_file = $enc_file . '.decrypted';
	
	// Print Script Start Time
	echo '<div style="margin:20px; padding:10px 20px; border:2px solid #4F5B93;"><p><strong>Start Time</strong><br>';
	echo date('H:i:s', time());
	echo '</p>';
	
	// Create the file (the file created will be filled with null-bytes ASCII 0)
	if (!is_file($file_path)) {
		createNullFile($file_path, $test['size']);
	}
	
	// Check md5 of the created file
	checkHash($file_path, $test['hash_plain']);
	
	// Encrypt the file
	createFile($file_path, $enc_file, $key_enc, $key_hmac, $iv);
	echo '<p>File encrypted: [' . $enc_file . ']</p>';
	checkHash($enc_file, $test['hash_enc']);
	
	// Decrypt the file
	$key = $key_enc . $key_hmac;
	$crypto->decryptFile($enc_file, $output_file, $key);
	echo '<p>File decrypted: [' . $output_file . ']</p>';
	checkHash($output_file, $test['hash_plain']);
	
	// Show Success/Result Message if code execution makes it here
	$file_info = ($test['size'] === '1g' ? 'a 1 GB file' : 'files larger than 2 GB');
	echo <<<HTML
<p><strong style="color:green; border:2px solid green; padding:10px; margin:10px auto; display:inline-block;">
	Success, file was encrypted then decrypted correctly so this computer/server can correctly encrypt and decrypt {$file_info}.
</strong></p>
<p>
	This script does not delete the test files so after the result is verified you should delete the created files to get back disk space.
</p>
HTML;
	
	// Print Script End Time
	echo '<p><strong>End Time</strong><br>';
	echo date('H:i:s', time());
	echo '</p></div>';
}
