<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Security\Crypto;

use FastSitePHP\Security\Crypto\AbstractCrypto;
use FastSitePHP\Security\Crypto\CryptoInterface;
use FastSitePHP\Security\Crypto\Encryption;
use FastSitePHP\Security\Crypto\Random;

/**
 * File Encryption
 * 
 * This class is designed to provide secure and easy to use file encryption.
 * This class uses encryption that is compatible with most Linux, Unix, and Mac
 * computers using command line calls on the OS with [openssl]. This class
 * and has the ability to encrypt both large on-disk files and smaller 
 * in-memory files. Very large files of any size can be encrypted on when  
 * using the default command line settings.
 * 
 * As of 2019 file encryption has been tested and confirmed on recent versions
 * the following operating systems using the default or minimal OS setup and PHP:
 *   macOS, Windows, Ubuntu, Debian, Amazon Linux, CentOS, openSUSE, Fedora,
 *   Red Hat Enterprise Linux, Windows using Windows Subsystem for Linux
 * 
 * Large file encryption with FreeBSD may or may not work depending on which shell
 * commands are avaiable and which version of FreeBSD is used. If you intended on
 * using this class with FreeBSD for large file encryption make sure your server
 * passes all crypto unit tests.
 * 
 * A compatible Bash Script available on the main site at
 * [scripts/shell/bash/encrypt.sh].
 */
class FileEncryption extends AbstractCrypto implements CryptoInterface
{
    // Member Variables with Default Settings
    private $display_cmd_error_detail = false;
    private $process_files_with_cmd_line = false;

    /**
     * Return a new key in hex format based on the size of the needed key.
     * Keys are generated using random bytes from the System's CSPRNG
     * (Cryptographically secure pseudorandom number generator).
     *
     * The same key must be used for both encryption and decryption
     * and the key should be kept secret in a secure manner and should
     * not be shared publicly.
     *
     * @return string
     */
    public function generateKey()
    {
        $bit_length = 256;
        if ($this->encrypt_then_authenticate) {
            $bit_length += 256;
        }
        return \bin2hex(Random::bytes($bit_length / 8));
    }
    
    /**
     * Encrypt a file
     * 
     * This function takes an input plaintext file and an output file of
     * where to save the encrypted file. The file path for the encrypted
     * file must refer to a file that does not yet exist. This function 
     * returns nothing and raises an exception in the event of an error.
     * 
     * The key is a string value in hexadecimal format that can be securely
     * generated using [generateKey()].
     * 
     * If encrypting large files call [processFilesWithCmdLine(true)]
     * or use the helper [Crypto] class instead. See additional comments below.
     * 
     * If using this Red Hat Linux you will likely first have to install
     * the [xxd] command using [sudo yum install vim-common]. By default 
     * [xxd] is expected to exist on most Linux and Unix OS's.
     * 
     * IMPORTANT - File paths should generally not be passed user parameters
     * because a user could specify files other than the intended file. If
     * an App does need to allow the user to specify a file then the code 
     * should be carefully reviewed and tested for security.
     * 
     * Function Properties used with [encryptFile()] and [decryptFile()]:
     *   Create a secure key
     *     [generateKey()]
     *
     *   [displayCmdErrorDetail(false)]
     *     Set to [true] to show error detail on command line errors. 
     *     IMPORTANT - this should only be used for debugging as it can show 
     *     security info such as file paths and the key in the error message.
     * 
     *   [processFilesWithCmdLine(false)]
     *     By default files are processed in memory. If this is set to [true]
     *     then files are encrypted using shell commands. [true] will work with
     *     most *nix OS's (Unix, Linux, macOS, etc); [false] is the only option that 
     *     will work with Windows. Small files can be processed in memory much 
     *     faster than using shell commands, however when using shell commands 
     *     large files (Gigs in size) can be processed.
     *
     *   Functions that change how Crypto Works:
     *     [keyType()] - 'key' or 'password' - Default to 'key'
     *     [pbkdf2Algorithm()] - For use with 'password' Key type
     *     [pbkdf2Iterations()] - For use with 'password' Key type
     *     [encryptThenAuthenticate()] - Set to [false] to disable Authentication
     * 
     * @param string $file_path - Input file to encrypt, this file will not be modified
     * @param string $enc_file - Path to save the encrypted (output) file
     * @param string $key - Key in hexadecimal format
     * @return void
     * @throws \Exception
     */
    public function encryptFile($file_path, $enc_file, $key)
    {
        // Validate Environment
        $this->validateFileSetup();

        // Validate Files:
        // *) Input file must exist
        // *) Output file cannot exist
        // *) Safe output file name
        if (!is_file($file_path)) {
            $error = 'File encryption failed because the file to encrypt [%s] was not found. If it exists then you should check directory or file permissions to verify that the user or account running has access.';
            $error = sprintf($error, $file_path);
            throw new \Exception($error);
        } elseif (is_file($enc_file)) {
            $error = 'File encryption failed because the file for encryption [%s] already exists. The [%s()] function will not overwrite existing files.';
            $error = sprintf($error, $enc_file, __FUNCTION__);
            throw new \Exception($error);
        }
        $this->validateFileName($enc_file);
        
        // Process files using either command line (default and *nix systems only)
        // or by loading the entire file in memory and processing it using
        // the standard [encrypt()] function.
        if ($this->process_files_with_cmd_line) {
            // Get path for the [xxd] command (used for hex/bytes conversion)
            $xxd = $this->xxdPath();

            // Generate a 16 Byte IV which is the size for 'aes-256-cbc'. The IV is
            // generated from the pseudo-device file [/dev/urandom] which exists on
            // Linux/Mac/Unix computers and is a source of secure random bytes.
            $cmd = $xxd . ' -l 16 -p /dev/urandom';
            $iv = $this->runCmd($cmd, __FUNCTION__, 'create iv', 32);

            // Get Encryption and HMAC Keys from a Single Key or Password.
            // If using a Password the IV is used as Salt with PBKDF2.
            list($key_enc, $key_hmac) = $this->encryptionKeys($key, \hex2bin($iv), 256, 256, false);
            $key_enc = \bin2hex($key_enc); // Convert back to hex for CLI processing
            if ($this->encrypt_then_authenticate) {
                $key_hmac = \bin2hex($key_hmac);
            }

            // Encrypt using openssl command line.
            // The [2>&1] redirects stderr to stdout,  
            // this allows PHP to get the error details.
            $cmd = 'openssl enc -aes-256-cbc -in "' . $file_path . '" -out "' . $enc_file . '" -iv ' . $iv . '  -K ' . $key_enc . ' 2>&1';
            $this->runCmd($cmd, __FUNCTION__, 'openssl enc');
            
            // Append IV to the end of the file
            $cmd = 'echo ' . $iv . ' | ' . $xxd . ' -r -p >> "' . $enc_file . '" 2>&1';
            $this->runCmd($cmd, __FUNCTION__, 'append iv');

            // HMAC the file using SHA-256 and append the result to end of the file.
            if ($this->encrypt_then_authenticate) {
                $cmd = 'cat "' . $enc_file . '" | openssl dgst -sha256 -mac hmac -macopt hexkey:' . $key_hmac . ' -binary >> "' . $enc_file . '" 2>&1';
                $this->runCmd($cmd, __FUNCTION__, 'openssl dgst');
            }
        } else {
            // Encrypt file in memory with the Encryption Class 
            // using compatible settings.
            $crypto = new Encryption();
            $crypto
                ->dataFormat('string-only')
                ->returnFormat('bytes')
                ->keyType($this->key_type)
                ->pbkdf2Algorithm($this->pbkdf2_algorithm)
                ->pbkdf2Iterations($this->pbkdf2_iterations)
                ->encryptThenAuthenticate($this->encrypt_then_authenticate);
            
            // Read Input File, Encrypt Contents, and Write back to Encrypted File
            $plaintext = file_get_contents($file_path);
            $ciphertext = $crypto->encrypt($plaintext, $key);
            file_put_contents($enc_file, $ciphertext);
        }

        // Verify that the encrypted file exists and can be read by PHP
        if (!is_file($enc_file)) {
            $error = 'File encryption failed because the encrypted file [%s] was not found after commands successfully ran. The error is unexpected so you may want to verify permissions for the web server or user running PHP and if the file was actually created.';
            $error = sprintf($error, $enc_file);
            throw new \Exception($error);
        }
    }

    /**
     * Decrypt a file
     * 
     * This function returns nothing and if the file cannot be decrypted an 
     * exception is thrown.
     * 
     * See comments in [encryptFile()] because the same properties and settings
     * are used here.
     * 
     * @param string $enc_file - Encrypted file, this file will not be modified
     * @param string $output_file - Path to save the decrypted file
     * @param string $key - Key in hexadecimal format
     * @return void
     * @throws \Exception
     */
    public function decryptFile($enc_file, $output_file, $key)
    {
        // Validate Environment
        $this->validateFileSetup();

        // Validate Files:
        // *) Encrypted file must exist
        // *) Output file cannot exist
        // *) Safe output file name
        // *) File Size for specific environments		
        if (!is_file($enc_file)) {
            $error = 'File decryption failed because the file to decrypt [%s] was not found. If it exists then you should check directory or file permissions to verify that the user or account running has access.';
            $error = sprintf($error, $enc_file);
            throw new \Exception($error);
        } elseif (is_file($output_file)) {
            $error = 'File decryption failed because the output file [%s] already exists. The [%s()] function will not overwrite existing files.';
            $error = sprintf($error, $output_file, __FUNCTION__);
            throw new \Exception($error);
        }
        $this->validateFileName($output_file);
        $this->validateFileSizeDec($enc_file);

        // Decrypt file in memory with the Encryption Class.
        // This option is based on [processFilesWithCmdLine(false)].		
        if (!$this->process_files_with_cmd_line) {
            $crypto = new Encryption();
            $crypto
                ->dataFormat('string-only')
                ->returnFormat('bytes')
                ->exceptionOnError(true)
                ->keyType($this->key_type)
                ->pbkdf2Algorithm($this->pbkdf2_algorithm)
                ->pbkdf2Iterations($this->pbkdf2_iterations)
                ->encryptThenAuthenticate($this->encrypt_then_authenticate);

            // Read Encrypted File, Decrypt Contents, and Write back to Decrypted File
            $ciphertext = file_get_contents($enc_file);
            $plaintext = $crypto->decrypt($ciphertext, $key);
            file_put_contents($output_file, $plaintext);

            // Verify that the decrypted file can be read by PHP
            if (!is_file($output_file)) {
                $error = 'File decryption failed because the decrypted file [%s] was not found after commands successfully ran. The error is unexpected so you may want to verify permissions for the web server or user running PHP and if the file was actually created.';
                $error = sprintf($error, $output_file);
                throw new \Exception($error);
            }

            // Success
            return;
        }
        
        // Use Command Line - Default Decryption Method.
        // This option is not available on Windows.

        // Define which command to use to trucate/remove bytes from the end of a file.
        // Truncating bytes from the end of a file happens almost instantly with the
        // correct commands while removing bytes from the beginning of a file would
        // require the entire file to be copied which is why the IV and HMAC are appended
        // to the end of the file rather than the beginning of the file. On Linux and most
        // Unix computers the [truncate] command will exist while on macOS it will not
        // exist unless manually installed so a one-line Ruby command line script is used.
        // Examples:
        //   ruby -e 'File.truncate("file.enc.tmp", File.size("file.enc.tmp")-32)'
        //   truncate -s $(( $(stat -c%s file.enc.tmp 2>/dev/null || stat -f%z file.enc.tmp) - 32 )) file.enc.tmp 2>&1		
        if (PHP_OS === 'Darwin') {
            $truncate_cmd = 'ruby -e \'File.truncate("{{file}}", File.size("{{file}}")-{{bytes}})\' 2>&1';
        } else {
            $truncate_cmd = 'truncate -s $(( $(stat -c%s "{{file}}" 2>/dev/null || stat -f%z "{{file}}") - {{bytes}} )) "{{file}}" 2>&1';
        }

        // Get path for the [xxd] command
        $xxd = $this->xxdPath();

        // Get IV as a hex string from the end of the file
        // File Format if using Authenticated Encryption:
        //   [Encrypted Bytes][IV 16-Bytes][HMAC 32-Bytes]
        // File Format when not using Authenticated Encryption:
        //   [Encrypted Bytes][IV 16-Bytes]
        $count = ($this->encrypt_then_authenticate ? 48 : 16);
        $cmd = 'tail -c ' . $count . ' "' . $enc_file . '" | ' . $xxd . ' -l 16 -c 16 -p';
        $iv = $this->runCmd($cmd, __FUNCTION__, 'read file iv', 32);

        // Get Encryption and HMAC Keys from a Single Key or Password.
        // If using a Password the IV is used as Salt with PBKDF2.
        list($key_enc, $key_hmac) = $this->encryptionKeys($key, \hex2bin($iv), 256, 256, false);
        $key_enc = \bin2hex($key_enc);
        if ($this->encrypt_then_authenticate) {
            $key_hmac = \bin2hex($key_hmac);
        }

        // Get temp file name and make sure that it does not already exist
        $tmp_file = $output_file . '.tmp';
        if (is_file($tmp_file)) {
            $error = 'File decryption failed because the temp file [%s] already exists. The [%s()] function will not overwrite existing files. This is the temp file and since it exists it can also mean that this function is already running for the current file or was previously ran and failed.';
            $error = sprintf($error, $tmp_file, __FUNCTION__);
            throw new \Exception($error);
        } elseif ($tmp_file === $output_file) {
            $error = 'File decryption failed because the output file [%s] contains the same name as the temp file used for decryption. The [%s()] function will not overwrite existing files. To successfully run this command you will need to specify an output file with a different file name.';
            $error = sprintf($error, $tmp_file, __FUNCTION__);
            throw new \Exception($error);
        }

        // Update truncate command as it will only run on the temp file
        $truncate_cmd = str_replace('{{file}}', $tmp_file, $truncate_cmd);

        // The next block of code will create and process the temporary
        // file. In case it fails handle the exception and delete the
        // temporary file before re-throwing the exception.
        $ex = null;
        try {
            // Copy encrypted file to the temp file
            $cmd = 'cp "' . $enc_file . '" "' . $tmp_file . '" 2>&1';
            $this->runCmd($cmd, __FUNCTION__, 'copy file');

            // Read and Verify HMAC if using Authenticated Encryption (this will run by default)
            if ($this->encrypt_then_authenticate) {
                // Get the HMAC (last 32 bytes)
                $cmd = 'tail -c 32 "' . $tmp_file . '" | ' . $xxd . ' -l 32 -c 32 -p';
                $saved_hmac = $this->runCmd($cmd, __FUNCTION__, 'read file hmac', 64);

                // Truncate the HMAC Bytes from the end of the file
                $cmd = str_replace('{{bytes}}', 32, $truncate_cmd);
                $this->runCmd($cmd, __FUNCTION__, 'truncate file hmac');

                // Calculate the File HMAC
                $cmd = 'cat "' . $tmp_file . '" | openssl dgst -sha256 -mac hmac -macopt hexkey:' . $key_hmac . '  -binary | xxd -l 32 -c 32 -p';
                $file_hmac = $this->runCmd($cmd, __FUNCTION__, 'openssl dgst', 64);

                // Verify that the Saved HMAC and Caculated HMAC are Equal.
                // If running manually from shell you could assign the HMAC values to variables
                // and then run a command similar to this '[ "$FILE_HMAC" = "$CALC_HMAC"  ]; echo $?'
                // which returns 0 if the values are equal. This would not use a time-safe compare method
                // however if manually typing on a command line time-safe compare would be irrelevant.
                if (!\hash_equals($file_hmac, $saved_hmac)) {
                    throw new \Exception('File decryption failed because the HMAC hash values are different. This means the file was either tampered with or encrypted using a different key');
                }
            }

            // Truncate the IV (last 16 bytes of the file)
            $cmd = str_replace('{{bytes}}', 16, $truncate_cmd);
            $this->runCmd($cmd, __FUNCTION__, 'truncate file iv');

            // Decrypt using openssl command line
            $cmd = 'openssl enc -d -aes-256-cbc -in "' . $tmp_file . '" -out "' . $output_file . '" -iv ' . $iv . '  -K ' . $key_enc . ' 2>&1';
            $this->runCmd($cmd, __FUNCTION__, 'openssl enc -d');

            // Delete the temp file
            $cmd = 'rm "' . $tmp_file . '" 2>&1';
            $this->runCmd($cmd, __FUNCTION__, 'delete temp file');

            // Verify that the decrypted file can be read by PHP
            if (!is_file($output_file)) {
                $error = 'File decryption failed because the decrypted file [%s] was not found after commands successfully ran. The error is unexpected so you may want to verify permissions for the web server or user running PHP and if the file was actually created.';
                $error = sprintf($error, $output_file);
                throw new \Exception($error);
            }

            // Success, file was decrypted so exit function
            return;
        } catch (\Exception $e) {
            // Save exception to a variable so it can
            // be re-thrown later in this function
            $ex = $e;
        }

        // If code execution made it here then there was an exception while
        // the temporary file might have been created so try to delete it.

        // If using command line they first try to delete it using command line.
        // This command first checks if the file exists and only calls [rm] if it does.
        if ($this->process_files_with_cmd_line) {
            try {
                $cmd = '[ -f "' . $tmp_file . '" ] && rm "' . $tmp_file . '"';
                $this->runCmd($cmd, __FUNCTION__, 'delete temp file after error');
            } catch (\Exception $e) {
                // Do Nothing
            }
        }

        // In case command [rm] failed and the file still exists
        // try to delete the file using native PHP functions.
        if (is_file($tmp_file)) {
            try {
                unlink($tmp_file);
            } catch (\Exception $e) {
                // Do Nothing
            }
        }

        // Re-throw the original exception with an added message if the file still exists.
        // The [is_file()] check is not unit tested and has to be manually tested by
        // throwing an exception and commenting out the above code for removing the file.
        // If it happens in production then the web user likely doesn't have access to
        // delete files in the output directory.
        $message = $ex->getMessage();
        if (is_file($tmp_file)) {
            $message .= sprintf('. The temporary file [%s] was created and could not be deleted. This file will need to be manually deleted before this function can be called again and if the file is very large it should be deleted to clear up disk space.', $tmp_file);
        }
        throw new \Exception($message);
    }
    
    /**
     * If either [encryptFile()] or [decryptFile()] fails this function
     * can be used to check the setup on the current computer or server.
     * It returns relevant information for debugging such as the file
     * path of all used commands, the current user, and the path variable.
     * 
     * Example of Data Returned:
     *   {
     *       "valid": true,
     *       "whoami": "www-data",
     *       "path": "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin",
     *       "getenforce": null,
     *       "commands": {
     *           "openssl": "/usr/bin/openssl",
     *           "echo": "/bin/echo",
     *           ...
     *           ...
     *       }
     *   }
     * 
     * The return property [getenforce] checks if Security-Enhanced Linux 
     * (SELinux) is installed. SELinux is included with various Linux OS's 
     * including Red Hat Enterprise Edition and CoreOS. SELinux provides 
     * additional security features that can prevent Apache and PHP from 
     * writing files unless configured to allow writing. If the shell command 
     * [getenforce] runs then SELinux is installed. Permissions may vary  
     * system to system however one example to allow writing by Apache/PHP 
     * to the directory [/var/www/data] and subdirectories is this:
     * 
     *     sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/app_data(/.*)?"
     *     sudo restorecon -Rv /var/www/app_data
     *     sudo chown apache:apache -R /var/www/app_data/*
     * 
     * @return void
     * @link https://opensource.com/article/18/7/sysadmin-guide-selinux
     * @link https://wiki.centos.org/HowTos/SELinux
     * @link https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/5/html/deployment_guide/ch-selinux
     */
    public function checkFileSetup()
    {
        // Build an array of commands used
        $cmds = array('openssl', 'echo', 'cat', 'cp', 'tail', 'rm');
        if (PHP_OS === 'Darwin') { // Mac
            $cmds[] = 'ruby';
        } else {
            $cmds[] = 'truncate';
            $cmds[] = 'stat';
        }

        // Find the file path of each command and validate that all
        // commands are found. To run the [which] command as a specific
        // user run the following:
        //   [sudo -u {user} which {cmd}]
        // example:
        //   [sudo -u _www which xxd]
        //
        // Also if looking up command info manually using the [type]
        // command will provide additional info and version can be checked
        // on many commands by using the [-v] parameter. Examples:
        //   type xxd
        //   xxd -v
        $cmdInfo = array();
        $valid = true;
        foreach ($cmds as $cmd) {
            $cmdInfo[$cmd] = exec('which ' . $cmd);
            if ($cmdInfo[$cmd] === null || $cmdInfo[$cmd] === '') {
                $valid = false;
            }
        }

        // Add the [xxd] command - in Linux and Mac this will likely
        // be [xxd] and for FreeBSD it will likely be the full path.
        // Most OS's will have this by default but some will not.
        // By default it's not included on Red Hat and can be installed
        // by running [sudo yum install vim-common].
        $cmdInfo['xxd'] = $this->xxdPath();
        if ($cmdInfo['xxd'] === 'xxd') {
            $cmdInfo['xxd'] = exec('which xxd');
            if ($cmdInfo['xxd'] === null || $cmdInfo['xxd'] === '') {
                $valid = false;
            }
        }
        
        // Check if Security-Enhanced Linux (SELinux) is installed.
        $getenforce = null;
        if (stripos(PHP_OS, 'Linux') !== false) {
            $getenforce = exec('which getenforce');
            if ($getenforce !== null && $getenforce !== '') {
                $getenforce = exec('getenforce');
            }
        }

        // Return overall status, current user, enviroment path,
        // and command full paths. These values are all relevant
        // to helping a developer solve problems in the event that
        // file encryption doesn't work on the server.
        return array(
            'valid' => $valid,
            'whoami' => exec('whoami'),
            'path' => getenv('PATH'),
            'getenforce' => $getenforce,
            'commands' => $cmdInfo,
        );
    }

    /**
     * Get or set error detail level.
     * 
     * When preforming command line file encryption and decryption this 
     * property can be set to true to provide detailed information 
     * including the command called with the error message.
     * 
     * IMPORTANT - This property is intended only for developers during
     * development because the encryption key can be displayed with the
     * error message if the command that has an error includes it.
     * 
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function displayCmdErrorDetail($new_value = null)
    {
        if ($new_value === null) {
            return $this->display_cmd_error_detail;
        }
        $this->display_cmd_error_detail = (bool)$new_value;
        return $this;
    }
    
    /**
     * Get or set option on how to process files. Defaults to [false].
     * 
     * When set to [true] file encryption with will be done through command 
     * line calls on the OS using [openssl]. This allows for efficient 
     * streaming of large files during encryption and description and on most 
     * systems allows for files of any size (limited by the OS) to be 
     * encrypted. Command line file encryption can be done on most Linux, Unix, 
     * and Mac computers.
     *
     * When set to [false] then file encryption will be handled in-memory 
     * using settings that are compatible with [openssl] command line.
     * 
     * When set to [false] file encryption will be faster for small files 
     * however the entire file is loaded in memory so it should only be used 
     * on small files or based on the needs of your app and server.
     *
     * To perform file encryption on Windows this must be set to [false] because
     * command line encryption is currently not supported on Windows.
     * 
     * WARNING - setting [processFilesWithCmdLine(true)] is currently not 
     * recommended when using FreeBSD unless your server passes all crypto
     * unit tests.
     * 
     * @param bool|null $new_value
     * @return bool|$this
     */
    public function processFilesWithCmdLine($new_value = null)
    {
        if ($new_value === null) {
            return $this->process_files_with_cmd_line;
        }
        $this->process_files_with_cmd_line = (bool)$new_value;
        return $this;
    }

    /**
     * Validation that runs from both [encryptFile()] and [decryptFile()]
     * 
     * @return void
     * @throws \Exception
     */
    private function validateFileSetup()
    {
        // Check to make sure that [exec()] has not been disabled and if it has,0
        // provide a helpful message so that it's easy for the developer to solve.
        // If you see this error then [exec()] is likely disabled in the [php.ini]
        // file so see the link: https://php.net/disable-functions
        // For info on editing [php.ini] refer to file [test-app.php] 
        // route '/check-server-config'. This error is not Unit Tested but must 
        // be manually tested by disabling [exec()].
        if (PHP_OS === 'WINNT') {
            if ($this->process_files_with_cmd_line) {
                $error = 'File encryption with [%s] is not yet available on Windows however files can be encrypted and decrypted in memory by setting [processFilesWithCmdLine(false)].';
                $error = sprintf($error, __CLASS__);
                throw new \Exception($error);
            }
        } elseif (!function_exists('exec')) {
            $error = 'File encryption with [%s] depends on [exec()] which has likely been disabled for security reasons on this computer or server. Please refer to online documentation or source code comments on how to enable this feature.';
            $error = sprintf($error, __CLASS__);
            throw new \Exception($error);			
        }
    }
    
    /**
     * When using FreeBSD the default Apache configuration may not
     * include the path '/usr/local/bin' so the command [xxd] might
     * not be visible to PHP. If so then use the full command path
     * instead. Without this code block [exec()] will likely return
     * a 127 return code when running on FreeBSD. Most OS's will have
     * [xxd] installed however some may not (e.g.: Red Hat); to install
     * on Red Hat or similar run [sudo yum install vim-common].
     * 
     * FreeBSD does not included [xxd] by default and can be installed
     * by running the following commands:
     *     su -
     *     pkg install vim-console
     * 
     * @return string
     */
    private function xxdPath()
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

    /**
     * Run a shell command and validate the output
     * 
     * @param string $cmd - Command to run
     * @param string $type - Calling function
     * @param string $line - Debug info
     * @param bool $output_size - If not null the command must output data of the specified size. Since output is in hex it will be 2x the byte size.
     * @return string|null
     * @throws \Exception
     */
    private function runCmd($cmd, $type, $line, $output_size = null)
    {
        // Run the command saving the exit status and all output to an array
        exec($cmd, $output, $exit_status);

        // If the return value/code from the program was 0 then
        // it ran successfully otherwise there was an error.
        // This applies to most Unix/Linux/Mac/Windows programs.

        $expected_count = ($output_size === null ? 0 : 1);
        $error = null;

        if ($exit_status !== 0) {
            $error = sprintf('[%s] failed at [%s] with an exit status other than 0.', $type, $line);
            switch ($exit_status) {
                case 127:
                    $text = ' One of the command line executables used was not found. This can happen if the command doesn’t exist on the server or if the web server account user can’t see the command. To obtain info related to command paths and the web user call the function [%s->checkFileSetup()].';
                    $error .= sprintf($text, __CLASS__);
                    break;
                default:
                    $error .= ' You may need to check read/write permissions on the directory or files being used.';
                    break;
            }
        } elseif (count($output) !== $expected_count) {
            // This error is currently not Unit Tested however it is defined in case something
            // unexpected happens or if a code change breaks a calling function. Any code changes
            // made here would require manual testing of this by forcing it to execute.
            $error = '[%s] failed at [%s] with unexpected output. You may need to check read/write permissions on the directory or files being used.';
            $error = sprintf($error, $type, $line);
        } elseif ($expected_count === 1 && $this->strlen($output[0]) !== $output_size) {
            // This error is also not unit tested and would have to 
            // be manually triggered by modifying code to be invalid.
            $error = '[%s] failed at [%s] with an unexpected output size. You may want to check available disk space on the drive. A serious error occurred or the code in this class was modified and is no longer working.';
            $error = sprintf($error, $type, $line);
        }

        if ($error !== null) {
            if ($this->display_cmd_error_detail) {
                $text = ' [cmd: %s][exit status: %d] [output: %s]';
                $error .= sprintf($text, $cmd, $exit_status, implode(', ', $output));
            } else {
                $text = ' To see additional info for this error set [%s->displayCmdErrorDetail(true)].';
                $error .= sprintf($text, __CLASS__);
            }
            throw new \Exception($error);
        }

        // Return single output line or null
        return ($expected_count === 1 ? $output[0] : null);
    }

    /**
     * The output path is not intended to be defined by user parameters but 
     * just in case Validate the output file path. This is a basic check for 
     * key characters that can pose a security risk; if other invalid 
     * characters specific to the OS are used then it will cause other 
     * parts of the calling functions to fail.
     * 
     * @param string $file_path
     * @return void
     * @throws \Exception
     */
    private function validateFileName($file_path)
    {
        // Characters Validated:
        //   chr(0) = NULL Character
        //   "      = Double-quotes is used on the calling shell
        //            commands for path and not valid for many OS's
        if (is_string($file_path) === false
            || strpos($file_path, chr(0)) !== false 
            || strpos($file_path, '"') !== false
        ) {
            throw new \Exception('Error - Invalid path for output file. The output file path cannot contain null character or characters [:"].');
        }
    }

    /**
     * Validate the file size for decryption. This is an early check
     * to provide a helpfull message. It only runs if PHP is using
     * a 64-Bit Build. On a 32-Bit build other commands will fail instead
     * on an invalid or small file.
     * 
     * @param string $enc_file
     * @return void
     * @throws \Exception
     */
    private function validateFileSizeDec($enc_file)
    {
        if (PHP_INT_SIZE !== 4) {
            // Validate for a Minimum File Size:
            //   AES/CBC Block Size = 16 Bytes
            //   IV = 16 Bytes
            //   HMAC/SHA256 = 32 Bytes
            $file_size = filesize($enc_file);
            $min_length = 16 + ($this->encrypt_then_authenticate ? 32 : 16);
            if ($file_size < $min_length) {
                throw new \Exception('The file to decrypt is smaller than the minimum expected file size. The file was either tampered with or encrypted using different settings or a different program.');
            }
        }
    }
}