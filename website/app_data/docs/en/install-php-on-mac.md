# Install Apache and PHP on a Mac
<style>
    .logo-images { display:inline-flex; flex-direction:column; }
    .logo-images img { display:inline; width:150px; height:150px; }
    .logo-images img[alt='Apple'] { height:80px; width:80px; margin-top:30px; margin-right:30px; }
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:500px) {
        .logo-images { flex-direction:row; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/apple.svg" alt="Apple">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Overview
Apple macOS comes with Apache and PHP already installed, however Apache and using PHP with it is not enabled by default. This tutorial provides an overview of some of the key tasks related to enabling Apache and PHP; however the needed steps for each version of macOS can vary greatly so you’ll likely want to visit many pages on this topic. 

<div class="quick-tip" style="margin-bottom:20px">
    <h3>IMPORTANT</h3>
    <p>This page has old content and will be updated in the future. In the meantime please see the main macOS Installation from the PHP Group:</p>
    <p><a href="https://www.php.net/manual/en/install.macosx.php" target="_blank">https://www.php.net/manual/en/install.macosx.php</a></p>
</div>

<div class="quick-tip">
    <h3>Quick Tip</h3>
    <p>Because PHP is already installed you can use the <a href="https://www.php.net/manual/en/features.commandline.webserver.php" target="_blank" rel="noopener">PHP Built-in Web Server</a> for local development and then there is no need for a complex Apache setup. For info on how to do this see <a href="edit-with-vs-code">Use Visual Studio Code for PHP Development</a> or <a href="edit-with-atom">Use GitHub Atom Editor for PHP Development</a>.</p>
</div>

### Recommended Apache, PHP, and MySQL Install Resources for macOS
* https://coolestguidesontheplanet.com/install-apache-mysql-php-on-macos-mojave-10-14/
* https://coolestguidesontheplanet.com/?s=mac+php
* https://websitebeaver.com/set-up-localhost-on-macos-high-sierra-apache-mysql-and-php-7-with-sslhttps
* http://osxdaily.com/2012/09/02/start-apache-web-server-mac-os-x/
* http://osxdaily.com/2012/09/10/enable-php-apache-mac-os-x/
* https://discussions.apple.com/docs/DOC-3083
* https://www.php.net/manual/en/install.macosx.bundled.php


---
## Opening Terminal

Terminal is commonly used for Mac Development. To open it search for “terminal” in Spotlight.

![Open Terminal](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/00_Open_Terminal.png)

&nbsp;

From terminal you can start Apache using the following command `sudo apachectl start`. Once entered you will be prompted for a password. Using this command and enabling PHP for Apache requires administrator rights. Once Apache is started you should be able to view `http://localhost/` from a browser.

![Start Apache](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/01_Start_Apache.png)

---
## Editing Apache Config to enable PHP

As stated earlier you can use the built-in PHP Server by default however to enable PHP for Apache you have to edit the Apache Config file. The most common method of doing this is using a terminal-based editor such as nano. To edit Apache config with nano enter the command `sudo 
nano /etc/apache2/httpd.conf`.

![Edit httpd.conf with nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/02_Edit_httpd_with_nano.png)

&nbsp;

You will then see the file in terminal along with a list of command options for editing the file.

![View httpd.conf with nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/03_httpd_in_nano.png)

&nbsp;

The file will likely be over 500 lines in length so finding the lines to edit without searching can take a while. To search type [control + w] and then enter “php” and press [enter].

![Search with nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/04_Search_Nano.png)

&nbsp;

Depending upon the version of macOS you have installed you will see a line starting with either [`#LoadModule php7_module`] or [`#LoadModule php5_module`]. Remove the [`#`] character from the start of the line.

![PHP Settings in httpd.conf](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/05_PHP_Config.png)

&nbsp;

To save type [`control + x`] then type [`y`].

![Save with nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/06_Save_with_Nano.png)

&nbsp;

If you don’t want to use a terminal based editor you can use Visual Studio Code to edit the file. Visual Studio Code also provides syntax highlighting of the file. Once you save you’ll be prompted to [Retry as Sudo] and then you’ll need to enter your password.

![VS Code Save-as Sudo](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/07_Edit_with_VS_Code.png)

&nbsp;

A file for each user has to be added/edited at [`/etc/apache2/users/{user-name}.conf`].

![Apache2 User Config File](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/08_User_Config.png)

&nbsp;

Depending on your environment you may also have to edit the [DocumentRoot] and [Directory] options in the [httpd.conf] file. 

![DocumentRoot in httpd.conf](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/09_DocRoot_Config.png)
