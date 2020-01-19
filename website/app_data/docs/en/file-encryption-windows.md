# File Encryption using the Bash Script [encrypt.sh] on Windows
<style>
    img.header-image { margin-top:20px; height:150px; }
</style>
<img src="../../img/icons/Security-Lock.svg" alt="Encryption" class="header-image">

---
## Overview
FastSitePHP includes the ability to encrypt files using both a PHP Class and a compatible Bash Script. Bash is the default shell on many versions of Linux and on macOS; it is also widely available on other Unix Like OS’s such as the BSD's (FreeBSD, NetBSD, OpenBSD, DragonFlyBSD etc).

Windows 10 and Windows Server 1709 and later provide support for the Windows Subsystem for Linux which allows for Linux Programs, Bash Scripts, and more to run on Windows.

This document shows up to run the script [encrypt.sh] on the Windows Subsystem for Linux and can be used as a general reference for running Bash Scripts on Windows.

### File Encryption API and Bash Script
* [📄 Class [Security\Crypto\FileEncryption]](../api/Security_Crypto_FileEncryption)
* <a href="https://github.com/fastsitephp/fastsitephp/blob/master/scripts/shell/bash/encrypt.sh">📜 View Source Code of [encrypt.sh]</a>
* [📥 Bash Script File Download [encrypt.sh]](../../downloads/encrypt-bash)
* [📑 More info on using the Bash Script File [encrypt.sh]](file-encryption-bash)

### Windows Subsystem for Linux Links
* [Windows Subsystem for Linux Documentation](https://docs.microsoft.com/en-us/windows/wsl/about)
* [Windows Subsystem for Linux Installation Guide for Windows 10](https://docs.microsoft.com/en-us/windows/wsl/install-win10)
* [Windows Server Installation Guide](https://docs.microsoft.com/en-us/windows/wsl/install-on-server)
* [How to copy files to and from Nano Server using PowerShell](https://msdn.microsoft.com/en-us/library/windows/desktop/mt708806(v=vs.85).aspx)

---
## Copying Files or Uploading the Script
These print screens were created on an Amazon Web Services (AWS) EC2 Instance of Windows Server 1709 (Nano Server).

To copy files to Windows Nano Server you can use Powershell, however if Powershell is not an option and you need to copy a text file you can open an instance of Notepad by typing `notepad.exe` into the Windows Command Line before logging into Linux.

In this example the file [encrypt.sh] was saved to the folder [C:\Users\Administrator\Documents]; and in the Linux you can access the C Drive (or other drives) from [/mnt/], being [/mnt/c] for the C drive.

To run a bash script navigate to the directory of the script and then run either `bash {script}` or `./{script}`.

You can see in this example there was an error when first running the script. This happened because Windows Notepad saved the Bash Script with Windows Line Breaks (CR/LF) rather than Unix Line Breaks (LF); to fix this run the command `sed -i 's/\r$//' encrypt.sh`. The script will then run as expected.

![Windows Nano Server - Open Notepad and Create Script](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/01_Create_Encrypt_SH_File.png)

---
## Running the Script
When you run the script without any options using either `bash encrypt.sh` or `./encrypt.sh` or if you use the option `-h` then you will see the help screen which provides an overview of the script, usage options, and examples of how to use it.

![Encrypt Bash Script Help](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/02_Encrypt_Shell_Help.png)

---
## Running Unit Tests
The `-t` option will run Unit Tests to confirm that the script works in your environment.

![Encrypt Bash Script Unit Tests](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/03_Encrypt_Shell_Unit_Tests.png)

![Encrypt Bash Script Unit Tests](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/04_Encrypt_Shell_Unit_Tests_Result.png)

---
## Encrypting Large Files
A large file test using the `-l` option will create, encrypt, and decrypt 1 GB and 3 GB files and confirms that your system can handle files of any size. This is ideal for encrypting backups because you can zip or compress many files to one large compressed file and then encrypt it using the script.

![Encrypt Bash Script Large File Tests](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/05_Encrypt_Shell_Large_File_Tests.png)
