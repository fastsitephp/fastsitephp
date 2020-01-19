# Using the Db2Database Class with an IBM iSeries Server
<style>
    .logo-images { display:inline-flex; flex-direction:column; }
    .logo-images img { display:inline; width:150px; height:150px; }
    .logo-images img[alt='IBM'] { height:70px; margin-top:40px; margin-right:30px; }
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:500px) {
        .logo-images { flex-direction:row; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/IBM_logo.svg" alt="IBM">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Overview
This page provides a brief overview of database development using FastSitePHP with an IBM Database on an IBM Server. FastSitePHP provides several database classes which reduces the amount of code needed to query databases using PHP. One of the classes [[Data\Db2Database](../api/Data_Db2Database)] is specifically for IBM DB2 and AS/400 Databases.

PHP is supported for IBM Servers and typically the most recent version of PHP can even be installed on old IBM AIX Servers. This allows for modern scripting and much faster development when working on older IBM Servers. This document doesn’t cover how to install PHP on an IBM iSeries Server and assumes you have access to an IBM Server and that PHP is already setup; this would typically be done by an administrator of the IBM Server and not a developer.

### Links
* https://www.ibm.com/it-infrastructure/power/os/ibm-i
* https://www.ibm.com/it-infrastructure/power/os/aix
* http://www.zend.com/en/solutions/modernize-ibm-i
* http://files.zend.com/help/Zend-Server/content/i5_installation_guide.htm
* https://en.wikipedia.org/wiki/IBM_AIX

### API and Test Script
* [📄 Class [Data\Db2Database]](../api/Data_Db2Database)
* <a href="https://github.com/fastsitephp/fastsitephp/blob/master/scripts/ibm-db2-test.php">📜 IBM Test Script File [scripts/ibm-db2-test.php]</a>

---
## Copying Files
A script that comes with FastSitePHP [scripts\ibm-db2-test.php] provides details on what files to copy. Many FTP programs will not work with IBM Servers however when using Windows the Command Line built-in FTP works with IBM Servers.

![Copy Files to IBM Server](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/00_Upload_Using_FTP.png)

---
## Starting an IBM Server Session
If you are in an IBM Environment then you may this setup or a similar setup from the Windows Start Menu.

![Start an IBM Session](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/01_IBM_Start_Session.png)

&nbsp;

Login with your account once you start the program.

![IBM iSeries Login](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/02_IBM_Login.png)

---
## Running a Terminal or Command Line Program from IBM iSeries
The default menu will likely be customized by a software vendor such as an ERP system so the command may be different on your server. In this example the command [AZ] is used to bring up the default [IBM i Main Menu].

![Run Menu Command on IBM Server](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/03_AZ_Command.png)

&nbsp;

From the Main Menu type [call qp2term] and press [enter] to bring up a command line interface.

![IBM Call QP2TERM](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/04_QP2TERM.png)

&nbsp;

From the command line you can run the uploaded script [ibm-db2-test.php] to verify that the FastSitePHP [[Db2Database](../api/Data_Db2Database)] Class works on your server. Once you have it working you can use it as a starting point for custom scripts and apps.

![IBM Run PHP Script](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/05_Running_Commands.png)
