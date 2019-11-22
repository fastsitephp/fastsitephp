# Install Apache and PHP on a Linux or Unix Server
<style>
    .logo-images { display:inline-flex; flex-direction:column; }
    .logo-images img { display:inline; width:150px; height:150px; }
    .logo-images img[alt='Linux'] { height:120px; width:120px; margin-top:20px; margin-right:20px;}
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:500px) {
        .logo-images { flex-direction:row; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/linux.svg" alt="Linux">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Overview
This tutorial provides instructions including shell commands, a step-by-step guide and additional resources at the bottom of this page. There are many different versions of Linux (and Unix) and PHP installation will vary from OS to OS. If you have never worked with Linux or command line programs before it may feel challenging at first, however there are a lot of great resources and tutorials online so if you get stuck simply search the internet for additional resources.

---
## Connecting to Linux
If you are connecting to a Linux Could Server from either Windows or macOS there are many programs that you can use. You may want to start with guides from your web host on how to get connected. If you have a Mac [ssh] is built-in to terminal so you can get connected without having to install anything. Here are some resources for connecting to Linux.

### Connecting from Windows
* https://docs.microsoft.com/en-us/azure/virtual-machines/linux/ssh-from-windows
* https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/putty.html
* https://winscp.net/eng/index.php *File Transfer*
* https://www.putty.org/
* https://docs.microsoft.com/en-us/windows/wsl/install-win10

### Connecting from Mac
* http://osxdaily.com/2017/04/28/howto-ssh-client-mac/
* https://panic.com/transmit/ *File Transfer*
* https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/AccessingInstancesLinux.html


---
## Quick Reference Code
This section shows several different installation options for Linux using shell (terminal / command-prompt). If you are familiar with using shell and are installing on one of the listed OS’s you can use this quick reference. These instructions currently provide an overview of the initial installation. Depending on your OS you may want install additional Apache extensions to update various config files.

### Installation on Ubuntu
~~~
# Update [apt] Package Manager
# The [upgrade] is not required but recommend (however, it takes many minutes)
sudo apt update
sudo apt upgrade

# Install Apache and PHP
sudo apt install apache2
sudo apt install php

# Enable PHP for Apache
sudo apt install libapache2-mod-php

# An alternative command version exists to install Apache, MySQL and PHP.
# Depending on the version of the command you will be prompted to
# either provide a password or set one up later for MySQL.
#
# sudo apt install lamp-server^

# Add PHP Extensions. A large number of extensions exist and the
# installed PHP version number needs to be included. The extensions
# below are needed for all FastSitePHP common features to work and
# for all Unit Tests to succeed, however they are not required
# in order to use FastSitePHP.
sudo apt install php7.2-sqlite php7.2-gd php7.2-bc php7.2-simplexml

# The zip extension is required in order for the FastSitePHP
# install script to run.
sudo apt install php7.2-zip

# Optional - Enable a Fallback page so that [index.php] 
# does not show in the URL.
sudo nano /etc/apache2/apache2.conf
# Scroll through the file and look for line:
#    <Directory /var/www/>
# Under it add the line:
#    FallbackResource /index.php
# Save using:
#    {control+s} -> {control+x}
#    or {control+x} -> {y} -> {enter}

# Optional - Enable Gzip Compression for JSON Responses
#   (This is not enabled by default on Apache)
sudo nano /etc/apache2/mods-available/deflate.conf
# Add the following under similar commands:
#       AddOutputFilterByType DEFLATE application/json

# Restart Apache
sudo service apache2 restart

# Set Permissions
# This assumes that the user [ubuntu] exists and
# is used to create and update files on the site.
sudo adduser ubuntu www-data
sudo chown ubuntu:www-data -R /var/www
sudo chmod 0775 -R /var/www

# Create and view a PHP test file
cd /var/www/html
echo "<?php phpinfo(); ?>" | sudo tee phpinfo.php
# http://your-server.example.com/phpinfo.php

# After you view the [phpinfo.php] link it's a good idea to delete it:
rm phpinfo.php

# Bonus! - Install the FastSitePHP Starter Site

# Navigate to your home directory and download the Starter Site
# This is a small download (~32 kb)
cd ~
wget https://github.com/fastsitephp/starter-site/archive/master.zip
sudo apt install unzip
unzip master.zip

# Copy Files
cp -r ~/starter-site-master/app /var/www/app
cp -r ~/starter-site-master/app_data /var/www/app_data
cp -r ~/starter-site-master/scripts /var/www/scripts
cp -r ~/starter-site-master/public/. /var/www/html
ls /var/www
ls -la /var/www/html

# Install FastSitePHP (~470 kb) and Dependencies (~20 - 40 kb)
php /var/www/scripts/install.php

# Delete files that are not needed including the Apache default page
# The [.htaccess] file being deleted is a version for local development
# that is copied from the starter site (it's not needed for production).
sudo rm /var/www/html/.htaccess
sudo rm /var/www/html/Web.config
sudo rm /var/www/html/index.html

# Remove the downloaded files
rm -r ~/starter-site-master
rm master.zip
ls ~

# View your site
# http://your-server.example.com/
~~~

### Installation on Red Hat, CentoOS, Fedora
~~~
# Update Package Manager
sudo yum update –y

# Optional install [vim-common] for hex command [xxd] support.
# This is required is using FastSitePHP or the included shell
# script for file encryption.
sudo yum install vim-common

# Install Apache and PHP
sudo su
yum install httpd
yum install php
apachectl start

# Various Linux OS's including Red Hat Enterprise Edition and CoreOS
# use Security-Enhanced Linux (SELinux) by default. If used then Apache
# and PHP will be blocked from writing files. To grant file write access
# on a folder to Apache run the following (modify the dir path
# '/var/www/app_data' as needed for your environment.
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/app_data(/.*)?"
sudo restorecon -Rv /var/www/app_data
sudo chown apache:apache -R /var/www/app_data/*
~~~

### Installation on FreeBSD
~~~
# [sudo] may not always be available so you can use [su -]
su -

# Install and start Apache
pkg install apache24
sysrc apache24_enable=yes
service apache24 start

# View Site
# http://your-server.example.com/

# Install PHP (use PHP version number and install optional packages)
pkg install mod_php73
pkg install php73-json php73-filter php73-hash php73-ctype
pkg install php73-openssl php73-mbstring

# Create new file:
vi /usr/local/etc/apache24/Includes/php.conf
# enter [i] for Insert then copy/paste or type the following:
~~~
~~~
<IfModule dir_module>
    DirectoryIndex index.php index.html
    <FilesMatch "\.php$">
        SetHandler application/x-httpd-php
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
    </FilesMatch>
</IfModule>
~~~
~~~
#Save using:
# {esc} :wq

# Make sure the file looks correct
cat /usr/local/etc/apache24/Includes/php.conf

# Copy [php.ini] and create Test [phpinfo.php] file
cp /usr/local/etc/php.ini-production /usr/local/etc/php.ini
cd /usr/local/www/apache24/data
echo "<?php echo phpinfo(); ?>" | tee phpinfo.php
service apache24 restart
# http://your-server.example.com/phpinfo.php

# Optional Set file permissions so you can copy files
chown ec2-user /usr/local/www/apache24/*
chown ec2-user /usr/local/www/*
~~~

---
## Step by Step Example
This example was performed on Ubuntu using an Amazon AWS Lightsail Server and commands were typed into the supplied browser based terminal.

&nbsp;

Update Advanced Package Tool (APT) `sudo apt update`

![Update Advanced Package Tool (APT)](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/00_apt_get_update.png)

&nbsp;

The update outputs log info as it runs and should complete quickly. Once it is updated you’ll be able to type on the terminal again.

![APT Updated](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/01_apt_get_update_complete.png)

&nbsp;

Install Apache, PHP, and then enable PHP for Apache and PHP. Detailed log info will be displayed when each command runs.
* `sudo apt install apache2`
* `sudo apt install php`
* `sudo apt install libapache2-mod-php`

![Install Apache2](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/02_install_apache2.png)

&nbsp;

Determine the installed PHP Version; it will be clearly available from the installer log. This can be used to optionally install various extensions. Example: `sudo apt install php7.2-sqlite php7.2-gd php7.2-bc php7.2-simplexml`

![Get PHP Version](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/03_install_php.png)

&nbsp;

Optional - Enable a Fallback page so that [index.php] does not show in the URL. Edit the Apache Config file using: `sudo nano /etc/apache2/apache2.conf`. The file will then show in the nano editor.

![Nano Editor Command](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/04_nano_edit_config.png)

![View Apache Config with Nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/05_apache_config_in_nano.png)

&nbsp;

Scroll through the file and look for line: `<Directory /var/www/>`. Add the line `FallbackResource /index.php` under it. In this example `CGIPassAuth On` is also added so that the HTTP Request Header [Authorization] is made avaialble to PHP using `$_SERVER['HTTP_AUTHORIZATION']`; however it is not required when using the FastSitePHP Request Object. The nano exit/save/etc menus will show at the bottom of the screen.

![Edit Apache Config with Nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/06_edit_apache_config.png)

&nbsp;

Save the file using `{control}+x` -> `y` -> `{enter}`

![Nano Save Prompt](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/07_nano_save.png)

![Nano Confirm Prompt](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/08_nano_confirm.png)

&nbsp;

Restart Apache using `sudo service apache2 restart`

![Restart Apache](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/09_apache_restart.png)

&nbsp;

Set User Permissions so that web files can be modified by the user you login with.
* `sudo adduser ubuntu www-data`
* `sudo chown ubuntu:www-data -R /var/www`
* `sudo chmod 0775 -R /var/www`

&nbsp;

![Set User Permissions](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/10_user_permissions.png)

&nbsp;

Create a [phpinfo.php] file to confirm that PHP works.
* `cd /var/www/html`
* `echo "<?php phpinfo(); ?>" | sudo tee phpinfo.php`

&nbsp;

![Create PHP Info File](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/11_create_phpinfo_file.png)

&nbsp;

Use the Public IP of the Server to view the default Apache page in a web browser.

![View Default Apache Page](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/12_view_apache_default_page.png)

&nbsp;

Confirm that PHP works and see detailed installation and config info using the page [phpinfo.php].

![View PHP Info Page](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/13_view_phpinfo_page.png)

---
## Additional Resources

* https://www.linode.com/docs/web-servers/lamp/
* https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-ubuntu-18-04
* https://www.digitalocean.com/community/tutorials?q=php
* https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/ec2-lamp-amazon-linux-2.html
* https://www.vultr.com/docs/how-to-install-apache-mysql-and-php-on-ubuntu-16-04
* https://linuxize.com/post/how-to-install-php-on-ubuntu-18-04/
* https://www.tecmint.com/install-apache-mariadb-and-php-famp-stack-on-freebsd/
* https://www.cyberciti.biz/faq/how-to-install-apache-mysql-php-stack-on-freebsd-unix-server/
* http://wiki.hawkguide.com/wiki/AWS_Redhat_RHEL_Install_LAMP
