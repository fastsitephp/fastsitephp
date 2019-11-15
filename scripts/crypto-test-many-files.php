<?php
// This script is used to manually file encryption by encrypting and decrypting 
// a large number of files. It is primarily intended to confirm that encrypting
// using CLI (PHP or Bash) and decrypting in-memory (and vice-versa) works 100%
// of the time. This script uses random data, keys, passwords, etc to make sure
// that nothing breaks when special characters such as null values are used.
//
// If running on Windows this will run much faster since the FileEncryption class
// doesn't support Windows for shell commands.
//
// In Windows (or without shell commands) this will likely run at 500 files per  
// second or more when using a Key. On other OS's when using shell commands  
// expect around a minute per 500 files. Times will vary greatly based on OS 
// and Disk Performance. When using a Password expect 1 to 3 files per second only.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

// Browser or Command Line?
$is_cli = (php_sapi_name() === 'cli');
$line_break = ($is_cli ? "\n" : '<br>');

// Status
echo 'Starting Script at ' . date(DATE_RFC2822);
echo $line_break;
echo str_repeat('-', 80);
echo $line_break;

// Settings
// Change Settings here as needed to Test, key settings to change:
//     USE_PASSWORD, USE_SHELL_SCRIPT, DEFAULT_PBKDF2, TEST_UNICODE
const LOOP_COUNT = 10000; // How many loops to run, excluding the [TEST_UNICODE] test
const ECHO_EACH_FILE = false; // Use for development only with a low loop count (example 100)
const ECHO_FILE_CONTENT = false; // See above comment but this outputs much more
const DELETE_FILES = true; // If [false] loop will run once and then exit so files can be reviewed
const USE_CLI = (PHP_OS !== 'WINNT'); // Use Command Line Programs - Not supported on Windows
const USE_PASSWORD = false; // [false] = Key, [true] = Random Password
const USE_SHELL_SCRIPT = false; // Encrypt or Decrypt using [encrypt.sh] instead of PHP
const DEFAULT_PBKDF2 = true; // Make sure PBKDF2 changes work, doesn't run when using [USE_SHELL_SCRIPT=true]
const SCRIPT_PATH = __DIR__ . '/shell/bash/encrypt.sh';
$script_path = realpath(SCRIPT_PATH);

// Password Translated into a number of different languages (translations are from Google Translate).
// This has been tested and verified on Mac and recent versions of Ubuntu when using both PHP Code 
// and the Bash Script [encrypt.sh]. Not all OS's may support this.
// NOTE - Arabic and Hebrew Text may appear backwards on some editors.
const UNICODE_PASSWORDS = [
    'كلمه السر', // Arabic
    '密码', // Chinese Simplified
    '密碼', // Chinese Traditional
    'Κωδικός πρόσβασης', // Greek
    'סיסמה', // Hebrew
    'Lykilorð', // Icelandic
    'パスワード', // Japanese
    '암호', // Korean
];
const TEST_UNICODE = false;

// Create FileEncryption Object
$crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
if (TEST_UNICODE) {
    $crypto->keyType('password');
} elseif (USE_PASSWORD) {
    $crypto->keyType('password');
    if (!DEFAULT_PBKDF2 && !USE_SHELL_SCRIPT) {
        $crypto->pbkdf2Algorithm('sha256');
        $crypto->pbkdf2Iterations(10);    
    }
}

// Encrypt and Decrypt a Temp file on each loop
$loop_count = (TEST_UNICODE ? count(UNICODE_PASSWORDS) : LOOP_COUNT);
for ($n = 0; $n < $loop_count; $n++) {
    // Generate a new Key each time
    if (USE_PASSWORD || TEST_UNICODE) {
        $key = generate_password();
    } else {
        $key = $crypto->generateKey();
    }
    if (ECHO_EACH_FILE) {
        echo str_repeat('-', 80);
        echo $line_break;
        if (TEST_UNICODE) {
            echo $key;
        } elseif (USE_PASSWORD && !USE_SHELL_SCRIPT) {
            echo bin2hex($key);
        } else {
            echo $key;
        }
        echo $line_break;
    }

    // Create a random file path
    $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-' . microtime(true) . '.tmp';
    if (ECHO_EACH_FILE) {
        echo $file_path;
        echo $line_break;
    }

    // Generate Random Bytes for Content using the basic Random function.
    // This doesn't have to use cryptographically secure values.
    // When using a large loop every possible byte value will be tested many times.
    $size = rand(0, 1000);
    $content = '';
    for ($x = 0; $x < $size; $x++) {
        $content .= chr(rand(0, 256));
    }
    if (ECHO_EACH_FILE && ECHO_FILE_CONTENT) {
        echo bin2hex($content);
        echo $line_break;
    }

    // Create File
    file_put_contents($file_path, $content);

    // Encrypt
    $enc_file = $file_path . '.enc';
    if (USE_SHELL_SCRIPT) {
        $use_script = ($n % 2 === 0);
        if ($use_script) {
            if (USE_PASSWORD || TEST_UNICODE) {
                $cmd = 'bash ' . $script_path . ' -e -i "' . $file_path . '" -o "' . $enc_file . '" -p "' . escape_password($key) . '"';
            } else {
                $cmd = 'bash ' . $script_path . ' -e -i "' . $file_path . '" -o "' . $enc_file . '" -k ' . $key;
            }            
            if (ECHO_EACH_FILE) {
                echo $cmd;
                echo $line_break;
            }
            runCmd($cmd);
        } else {
            if (ECHO_EACH_FILE) {
                echo 'Encrypting File';
                echo $line_break;
            }
            $crypto->encryptFile($file_path, $enc_file, $key);
        }
    } else {
        if (USE_CLI) {
            $use_cli = ($n % 2 === 0);
            $crypto->processFilesWithCmdLine($use_cli);
        }
        if (ECHO_EACH_FILE) {
            echo 'Encrypting with CLI = ' . json_encode($use_cli);
            echo $line_break;
        }    
        $crypto->encryptFile($file_path, $enc_file, $key);
    }

    // Decrypt
    $output_file = $file_path . '.dec';
    if (USE_SHELL_SCRIPT) {
        $use_script = !$use_script;
        if ($use_script) {
            if (USE_PASSWORD || TEST_UNICODE) {
                $cmd = 'bash ' . $script_path . ' -d -i "' . $enc_file . '" -o "' . $output_file . '" -p "' . escape_password($key) . '"';
            } else {
                $cmd = 'bash ' . $script_path . ' -d -i "' . $enc_file . '" -o "' . $output_file . '" -k ' . $key;
            }
            if (ECHO_EACH_FILE) {
                echo $cmd;
                echo $line_break;
            }
            runCmd($cmd);
        } else {
            if (ECHO_EACH_FILE) {
                echo 'Decrypting File';
                echo $line_break;
            }
            $crypto->decryptFile($enc_file, $output_file, $key);
        }
    } else {
        if (USE_CLI) {
            // Do the reverse
            $crypto->processFilesWithCmdLine(!$crypto->processFilesWithCmdLine());
        }
        if (ECHO_EACH_FILE) {
            echo 'Decrypting with CLI = ' . json_encode($crypto->processFilesWithCmdLine());
            echo $line_break;
        }    
        $crypto->decryptFile($enc_file, $output_file, $key);
    }

    // Compare files
    $content_enc = file_get_contents($enc_file);
    $content_dec = file_get_contents($output_file);

    // Since these will never error with valid code - verify error by un-commenting
    // $content_enc = $content;
    // $content = substr($content, 10);

    if ($content === $content_enc) {
        echo $line_break;
        echo 'ERROR - Files Match:'; 
        echo $line_break;
        echo $file_path;
        echo $line_break;
        echo $enc_file;
        exit();
    } elseif ($content !== $content_dec) {
        echo $line_break;
        echo 'ERROR - Files do not match:'; 
        echo $line_break;
        echo $file_path;
        echo $line_break;
        echo $output_file;
        exit();
    }

    // Delete files or exit for debug
    if (DELETE_FILES) {
        unlink($file_path);
        unlink($enc_file);
        unlink($output_file);
    } else {
        exit();
    }

    // Show Status every 50 or 500 files depending on settings
    $m = (USE_PASSWORD || TEST_UNICODE ? 50 : 500);
    if ($n % $m === 0) {
        echo 'Created ' . $n . ' of ' . $loop_count . ' files at ' . date(DATE_RFC2822);
        echo $line_break;
        if (!$is_cli) {
            ob_flush();
        }
    }
}

// Status
echo str_repeat('-', 80);
echo $line_break;
echo 'Created ' . $n . ' of ' . $loop_count . ' files at ' . date(DATE_RFC2822);
echo $line_break;
echo 'Finished Script at ' . date(DATE_RFC2822);
echo $line_break;

// Escape Password for Shell.
// PHP has a built-in function [escapeshellarg()] 
// but it didn't work for this code.
function escape_password($password) {
    $password = str_replace('\\', '\\\\', $password);
    $password = str_replace('"', '\"', $password);
    return $password;
}

// Generate a Random Password
function generate_password() {
    $size = rand(1, 256);

    // Test with any random bytes as compatability with [encrypt.sh] is not needed
    if (!USE_SHELL_SCRIPT) {
        return \FastSitePHP\Security\Crypto\Random::bytes($size);
    }

    // Generate a Shell Safe Password that can include a few special characters
    // including space and quotes. This function runs over and over and secure
    // passwords are not needed so [rand()] is ok to use here.
    $password = '';
    $special_chars = array(
        ' ', '"', "'", '\\'
    );
    while (strlen($password) < $size) {
        $ascii = rand(0, 255);
        if ($ascii >= ord('A') && $ascii <= ord('Z')) {
            $password .= chr($ascii);	
        } elseif ($ascii >= ord('a') && $ascii <= ord('z')) {
            $password .= chr($ascii);
        } elseif ($ascii >= ord('0') && $ascii <= ord('9')) {
            $password .= chr($ascii);
        } elseif (in_array(chr($ascii), $special_chars, true)) {
            $password .= chr($ascii);
        }
    }
    return $password;
}

// Used to run the Shell Script [encrypt.sh]
function runCmd($cmd) {
    exec($cmd, $output, $exit_status);
    if ($exit_status !== 0) {
        echo 'Failed at Bash Script';
        echo "\n";
        var_dump($cmd);
        echo "\n";
        var_dump($exit_status);
        echo "\n";
        var_dump($output);
        exit();
    }
}