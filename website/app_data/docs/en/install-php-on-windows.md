# Install IIS and PHP on a Windows Server or Desktop
<style>
    .logo-images { display:block; text-align:center; }
    .logo-images img { display:inline; height:150px; }
    .logo-images img[alt='Microsoft'] { display:block; margin:auto; }
    .logo-images img[alt='PHP'] { height:80px; margin-top:40px; margin-right:40px; }
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:700px) {
        .logo-images { display:inline-flex; text-align:left; }
    .logo-images img[alt='Microsoft'] { display:inline; }
    .logo-images img[alt='PHP'] { margin-right:40px; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/microsoft.png" alt="Microsoft">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Overview
This tutorial provides instructions with a step by step guide on how to setup a web server (IIS) and PHP on Windows and some alternative options. Installing PHP on Windows is relatively quick and simple because Microsoft provides easy to use installers.

<div class="quick-tip" style="margin-bottom:20px">
    <h3>IMPORTANT</h3>
    <p>This page has old content and will be updated in the future. In the meantime please see the main Windows Installation from the PHP Group:</p>
    <p><a href="https://www.php.net/manual/en/install.windows.php" target="_blank">https://www.php.net/manual/en/install.windows.php</a></p>
</div>

<div class="quick-tip">
    <h3>Quick Tip</h3>
    <p>If you only need PHP for local development you can skip the IIS install and jump to <a href="#install_php">installing PHP</a> or see additional links in this section of the page.</p>
</div>

### Web Platform Installer
Microsoft’s Web Platform Installer (WebPI) can be used to install multiple version of PHP both on regular desktops (Windows 10, etc) for development and on Windows Servers for production.
* https://www.microsoft.com/web/downloads/platform.aspx

### Alternative Development Environments for PHP with Windows
This tutorial shows how to use a Microsoft supported program for installing PHP, however many alternative options exist for local development. Here are a few:
* https://www.apachefriends.org/ - XAMPP Apache + MariaDB + PHP + Perl - Popular PHP development environment, also works with macOS and Linux.
* https://windows.php.net/download - Direct Download of PHP, once installed you can use PHP itself as a Development Server

### Additional PHP Install Resources
There are many ways that PHP can be installed. To find out more see additional links or search online.
* https://www.php.net/manual/en/install.windows.php
* https://docs.microsoft.com/en-us/iis/application-frameworks/scenario-build-a-php-website-on-iis/configuring-step-1-install-iis-and-php
* https://www.microsoft.com/en-us/sql-server/developer-get-started/php/windows
* https://docs.microsoft.com/en-us/sql/connect/php/loading-the-php-sql-driver?view=sql-server-2017


---
## Connect to the Windows Server
If installing PHP on a Windows Server you will likely use Remote Desktop Connection (RDC) to connect to the server.

&nbsp;

Open RDC by searching for “Remote” ou "mstsc" in the start menu; once opened you will see a login screen.

![Remote Desktop Connection (RDC)](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/00_RDC.png)

&nbsp;

Specifying “.\” before the user name will use the local network of the computer that you are connecting to rather than your domain. This may or may not be needed depending on where and how you are connecting.

![Remote Desktop Connection (RDC) Login](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/01_RDC_Auth.png)

&nbsp;

You may see a certificate warning when connecting. This is a common warning and it’s typically safe to click [Yes].

![Remote Desktop Connection (RDC) Warning](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/02_RDC_Warning.png)

---
## Install IIS on Windows Desktop

If you are using a Windows Desktop Computer such as Windows 10 you can install IIS from [Programs and Features] by turning it on as a Windows Feature. Using IIS for PHP development is not required for PHP development because PHP has a built-in Web Server.

![Install PHP on Windows Desktop](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/03_Win7_Install.png)

---
## Install IIS on Windows Server

This page shows how to install IIS and PHP on a recent version of Windows. If you have a very old server (example Windows 2003 with IIS 6) you can still install PHP however you will want to search for other links online as the steps will be different.

First open [Server Manager] from the Start Menu.

![Windows Server Manager Icon](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/04_Start_Menu_Server_Manager.png)

&nbsp;

Click [Add roles and features]

![Windows Server Manager - Add Roles and Features](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/05_Add_Roles_And_Features.png)

&nbsp;

You will go through a Wizard. Click the [Next] button.

![Windows Server Manager Wizard](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/06_Add_Roles_And_Features.png)

&nbsp;

You can leave the default options until you get to the [Server Roles] selection. Then select [Web Server (IIS)].

![Windows Server Manager - Select IIS](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/07_Selected_IIS.png)

&nbsp;

For this tutorial we are leaving the default options however you may want to change them based on your needs. Click [Next] and then finish the setup. Once complete IIS will be setup on your server.

![Windows Server Manager - IIS Options](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/08_IIS_Options.png)

---
## <a name="install_php">Install the Web Platform Installer and PHP</a>

Download Microsoft's Web Platform Installer. [https://www.microsoft.com/web/downloads/platform.aspx]

![Microsoft Web Platform Installer Website](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/09_Web_Platform_Installer.png)

&nbsp;

Windows Servers typically block most sites and downloads by default so you may see this warning if using IE. To work-around the issue, change IE Security Settings, download the Web Platform Installer from another browser (a portable version for example) if available, or download from another computer and copy the installer through RDC.

![IE Download Warning](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/10_Download_Warning.png)

&nbsp;

The Web Platform Installer is a simple setup wizard with one screen.

![Web Platform Installer Wizard](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/11_Install_Web_Platform_Installer.png)

&nbsp;

Once installed you’ll see it in the Start Menu.

![Web Platform Installer Icon](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/12_Start_Menu_Web_Platform_Installer.png)

&nbsp;

Search for “php” or a specific version such as “php 7.3”. The Web Platform Installer provides many different versions of PHP and various extensions.

![Web Platform Installer - PHP Search](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/13_Search_For_PHP.png)

&nbsp;

In this example we are going to install PHP 7.3.1 which is the latest version of PHP (at the time this tutorial was created), and we are going to install PHP SQL Server Drivers for IIS. You’ll notice that there is an option for each version of PHP to install for [IIS Express]. IIS Express is used for local development and not the full version of IIS so we do not select it here.

![Web Platform Installer - Install PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/14_Install_PHP.png)

&nbsp;

![Web Platform Installer - Install PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/15_Install_PHP.png)

&nbsp;

Depending on the speed of your computer and internet the installation may take around a minute to a few minutes.

![Web Platform Installer - PHP Installing](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/16_PHP_Installing.png)

&nbsp;

In this example an error came up during the install however it was for un-used extension that is not needed and the main PHP install worked.

![Web Platform Installer - PHP Installed](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/17_PHP_Installed.png)

---
## Create and View a PHP Page

&nbsp;

The default web root folder when using IIS is [C:\inetpub\wwwroot]. Here a file [phpinfo.php] is added using Notepad. This file will output PHP version, config info, etc.

~~~
<?php
phpinfo();
~~~

![IIS Create phpinfo File in wwwroot](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/18_wwwroot_Create_File.png)

&nbsp;

Viewing the page from localhost shows that PHP is installed and correctly working.

![View phpinfo File](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/19_View_phpinfo.png)

&nbsp;

The install location may vary for your server however here it is installed at [C:\Program Files\PHP\v7.3]. You can see that the Web Platform Installer sets up needed config options such as the timezone. The extension folder typically includes many addition extensions that are not enabled by default; if you need them view the related files to make sure they exist and then add them to the [ExtensionList] in the [php.ini] file.

![Windows PHP Config Folder and INI File](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/20_PHP_Config.png)

---
## Setup the FastSitePHP Starter Site

Download the FastSitePHP Starter Site from https://www.fastsitephp.com/downloads/starter-site or directly from GitHub https://github.com/fastsitephp/starter-site/archive/master.zip

Either link result in the file `starter-site-master.zip` being downloaded.

Unzip the file and copy the following folders:

~~~
Copy folders:
    starter-site-master\app
    starter-site-master\app_data
    starter-site-master\scripts

Copy under:
    C:\inetpub
~~~

These three folders will exist outside of the IIS public web root folder `wwwroot`.

![Copy Starter Site Folders](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/21_Copy_Starter_Site_Folders.png)

&nbsp;

Run the install script, this takes only a few seconds and installs the FastSitePHP Framework to `C:\inetpub\vendor`.

~~~
cd C:\inetpub\scripts
php install.php
~~~

![FastSitePHP Install Script](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/22_Run_Install.png)

&nbsp;

Copy public files and folders to the web root folder.

~~~
Copy from:
    starter-site-master\public\
        css
        img
        js
        index.php
        Web.config
        favicon.ico
        robots.txt

Copy under:
    C:\inetpub\wwwroot
~~~

![Copy Starter Site Public Files](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/23_Copy_Starter_Site_Files.png)

&nbsp;

View the site! Congratulations if you have followed all of these steps then you have setup a Windows Server with IIS for production usage.

![View Site](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/24_View_Starter_Site.png)
