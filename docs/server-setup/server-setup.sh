# ----------------------------------------------------------------------------------
#
# This file describes step by step how the Server is setup for the following sites:
#   https://www.fastsitephp.com/
#   https://playground.fastsitephp.com/
#   https://www.dataformsjs.com/
#   https://playground.dataformsjs.com/
#   https://ai-ml.dataformsjs.com/
#
# Originally all 5 sites used separate servers however they do not get enough
# traffic to justify the need for 5 servers so now only 1 server is used.
# The server is an AWS Lighsail instance with 1 GB RAM, 1 vCPU, 40 GB SSD,
# and the monthly cost for a server of this size is $5 USD.
#
# A varity of services are used on this server including the following:
#   - nginx with php-fpm for the main DataFormsJS and FastSitePHP sites
#   - Apache with a custom build of PHP for the FastSitePHP Playground
#   - Python Service built with Flask, TensorFlow, Keras, and scikit-learn running with Gunicorn
#   - nodejs service using express and GraphQL
#   - Both PHP and nodejs use SQLite for Database data
#
# These instructions apply specifically to Ubuntu 20.04 LTS; if using the commands
# for another build of Linux you will likely need to make changes to the commands
# for the custom build of PHP.
#
# This file is a shell script however the [*.sh] extension is so the commands
# can show up in code editor using syntax highlighting. This file does not run;
# rather the commands and instructions need to be followed for the server setup.
# Expect about 30 to 60 minutes for full server setup and likely a few hours for
# full testing, DNS changes, etc.
#
# For detail comments on each of the servers refer to one of the following documents:
#   https://github.com/fastsitephp/fastsitephp/blob/master/docs/Main%20Site%20Server%20Setup.txt
#   https://github.com/fastsitephp/playground
#   https://github.com/fastsitephp/playground/blob/master/docs/Playground%20Server%20Setup.txt
#   https://github.com/dataformsjs/website/blob/master/docs/Main%20Site%20Server%20Setup.txt
#   https://github.com/dataformsjs/website/blob/master/docs/Python%20Webserver%20Setup%20for%20AI_ML%20Functions.txt
#   https://github.com/dataformsjs/playground/blob/master/docs/Playground%20Server%20Setup.txt
#
# ----------------------------------------------------------------------------------

echo 'This script contains commands for running manually'
exit

# Update Server
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Optional (takes several minutes)
# sudo apt -y upgrade

# Install nginx and PHP
sudo apt install -y nginx
sudo ufw allow 'Nginx HTTP'
sudo apt install -y php8.1-fpm php8.1-sqlite3 php8.1-gd php8.1-bcmath php8.1-simplexml php8.1-zip

# Define nginx Settings
nano nginx-config.txt
# Copy file contents from [nginx-config.txt] and save
# Save using (this applies to all `nano` commands):
#    {control+s} -> {control+x}
sudo cp nginx-config.txt /etc/nginx/sites-available/multi-site
sudo ln -s /etc/nginx/sites-available/multi-site /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
rm nginx-config.txt

# Misc nginx Commands:
# sudo systemctl start nginx
# sudo systemctl reload nginx
# sudo systemctl status nginx.service

# Download FastSitePHP Starter Site
wget https://github.com/fastsitephp/starter-site/archive/master.zip
sudo apt install -y unzip
unzip master.zip

# Create Sites
sudo mkdir /var/www/fastsitephp-site
sudo mkdir /var/www/fastsitephp-playground
sudo mkdir /var/www/dataformsjs-site
sudo mkdir /var/www/dataformsjs-ai-ml
sudo mkdir /var/www/dataformsjs-playground
sudo mkdir /var/www/default-site

# Copy and install the Starter Site for each Website
sudo cp -r ./starter-site-master/. /var/www/fastsitephp-site
sudo php /var/www/fastsitephp-site/scripts/install.php

sudo cp -r ./starter-site-master/. /var/www/fastsitephp-playground
sudo php /var/www/fastsitephp-playground/scripts/install.php

sudo cp -r ./starter-site-master/. /var/www/dataformsjs-site
sudo php /var/www/dataformsjs-site/scripts/install.php

sudo cp -r ./starter-site-master/. /var/www/dataformsjs-playground
sudo php /var/www/dataformsjs-playground/scripts/install.php

# Remove the downloaded files
rm -r ./starter-site-master
rm ./master.zip

# Permissions
sudo adduser ubuntu www-data
sudo chown ubuntu:www-data -R /var/www
sudo chmod 0775 -R /var/www

# Install FastSitePHP Main Site
wget https://raw.githubusercontent.com/fastsitephp/fastsitephp/master/scripts/sync-server-from-github.sh
bash sync-server-from-github.sh
rm sync-server-from-github.sh

# --------------------------------------------------------------------------------
# Optional and no longer used as of 2022-01-08 due to security update because
# the FastSitePHP Playground (which exists on the same server) can be
# attacked by unkown PHP issues in the future. Because of this no senstive
# or secure info can be stored on the server. This file was deleted on the
# server after the upate but info is not removed from this file so that
# full setup (and example of using a [.env] file) can be described.
#
# Setup [.env] file which is used for the FastSitePHP [/en/security-issue] page
#     sudo nano /var/www/fastsitephp-site/app_data/.env
#
# Keys (copy from values local file):
#
# SMTP_HOST={host}
# SMTP_PORT={port}
# SMTP_USER={email}
# SMTP_PASSWORD={password}
# --------------------------------------------------------------------------------

# Install FastSitePHP Playground
wget https://raw.githubusercontent.com/fastsitephp/playground/master/scripts/sync-server-from-github.sh
bash sync-server-from-github.sh
rm sync-server-from-github.sh

# Additional Steps for FastSitePHP Playground
php /var/www/fastsitephp-playground/scripts/install.php
cp /var/www/fastsitephp-playground/scripts/fast_autoloader.php /var/www/fastsitephp-playground/vendor/autoload.php
mkdir /var/www/fastsitephp-playground/public/sites

# Install DataFormsJS Main Site
wget https://raw.githubusercontent.com/dataformsjs/website/master/scripts/sync-server-from-github.sh
bash sync-server-from-github.sh
rm sync-server-from-github.sh

# Install DataFormsJS Playground
wget https://raw.githubusercontent.com/dataformsjs/playground/master/scripts/sync-server-from-github.sh
bash sync-server-from-github.sh
rm sync-server-from-github.sh

# Additional Steps for DataFormsJS Playground
php /var/www/dataformsjs-playground/scripts/install.php
mkdir /var/www/dataformsjs-playground/public/sites

# Setup custom build of PHP with Apache for FastSitePHP Playground
#
# PHP [php-8.1.1] can be used instead of [php-7.4.27] with some changes
# documented in the comments however PHP 7 is being used because the `make`
# command runs in about 4 to 5 minutes on a low cost server. When using PHP 8
# the `make` command would typically crash low cost servers and take 10 minutes
# on more costly large servers. This site is being setup with a low cost
# ($5 USD per month) server so PHP 7 is being used.
#
# IMPORTANT - before new releases of PHP are added the change log for PHP must be
# reviewed to determine if new functions (or changes) support writing files.
# Functions that provide file write access are disabled using modified [*.c, *.h]
# files and the script [update-php-c-source-files.php] are used to update many more C files
# prior to PHP being build on the system. If you are interseted (or want to verify) the
# modified C files then a diff viewer such as WinMerge or VS Code can be used to compare files.
#
which php
# This is the active version of PHP prior to these commands (and needed if re-running)
# Output: /usr/bin/php
sudo apt install -y apache2 apache2-dev libxml2-dev
wget https://www.php.net/distributions/php-7.4.27.tar.bz2
tar xjf php-7.4.27.tar.bz2
wget https://fastsitephp.s3-us-west-1.amazonaws.com/playground/php-7.4.27/file.h
wget https://fastsitephp.s3-us-west-1.amazonaws.com/playground/php-7.4.27/file.c
wget https://fastsitephp.s3.us-west-1.amazonaws.com/playground/php-7.4.27/php.h
mv file.h ~/php-7.4.27/ext/standard/file.h
mv file.c ~/php-7.4.27/ext/standard/file.c
mv php.h ~/php-7.4.27/main/php.h
/usr/bin/php /var/www/fastsitephp-playground/scripts/update-php-c-source-files.php
#
# Build PHP 8
# cd php-8.1.1
# ./configure --with-apxs2=/usr/bin/apxs --disable-all --enable-filter --enable-ctype --enable-opcache
#
# Build PHP 7
cd php-7.4.27
./configure --with-apxs2=/usr/bin/apxs --disable-all --enable-json --enable-filter --enable-ctype --enable-opcache
#
# Run the `make` command. Simply using `make` works however it takes a long time so the
# below version outputs errors and info to a file [make.log] and runs the command in the
# background by using "&". The `wait` command prevents additional commands from being
# entered in terminal until the background task completes then it reports on the result.
# In addition to using `wait` the command `jobs` can be used instead to manually check
# the status of running background commands.
make > make.log 2>&1 &
wait
cat make.log
# At the end of the file look for:
#    Build complete.
#    Don't forget to run 'make test'.
#
sudo make install
which php
# Output: /usr/local/bin/php
# After PHP is built and installed the path is changed to this version
# which is very limited in functionality. Because of this if additional
# PHP commands are needed from terminal they should use the full path
# of `/usr/bin/php` which is the PHP 8 version installed for nginx.
#
# Configure PHP and Apache
cd ..
wget https://fastsitephp.s3-us-west-1.amazonaws.com/playground/php-8.1.1/php.ini-production
sudo mv php.ini-production /usr/local/lib/php.ini
# Change Apache to use prefork (required after PHP is enabled otherwise Apache won't start)
sudo a2dismod mpm_event
sudo a2enmod mpm_prefork
printf "<FilesMatch \\.php$>\n    SetHandler application/x-httpd-php\n</FilesMatch>\n" > php.conf
sudo mv php.conf /etc/apache2/mods-enabled/php.conf
cat /etc/apache2/mods-enabled/php.conf
cat /etc/apache2/mods-available/deflate.conf
sudo sed -i '/<IfModule mod_filter.c>/a \\t\tAddOutputFilterByType DEFLATE application\/json' /etc/apache2/mods-available/deflate.conf
cat /etc/apache2/mods-available/deflate.conf
# Edit Apache Config
sudo nano /etc/apache2/apache2.conf
#
# Under:
#     <Directory /var/www/>
# Add:
#     FallbackResource /index.php
# And Change:
#     AllowOverride None
# To:
#     AllowOverride All
#
sudo nano /etc/apache2/ports.conf
# Change:
#   Listen 80
# To:
#   Listen 8080
sudo nano /etc/apache2/sites-available/000-default.conf
# Change:
#   <VirtualHost *:80>
#       DocumentRoot /var/www/html
# To:
#   <VirtualHost 127.0.0.1:8080>
#       DocumentRoot /var/www/fastsitephp-playground/public
#
# Delete downloaded make files
rm php-7.4.27.tar.bz2
sudo rm -r php-7.4.27
# Restart Apache
sudo service apache2 restart

# Create Geonames Database
# About 350 MB to Download, Python Script takes about 3-5 minutes
# and will show "Success Database Created" once it completes.
# The generated SQLite database is around 2.6 GB in size
cd /var/www/dataformsjs-site/scripts
wget http://download.geonames.org/export/dump/countryInfo.txt
wget http://download.geonames.org/export/dump/allCountries.zip
unzip allCountries.zip
python3 geonames.py
# Remove downloaded Geonames files to clear up space on the server
sudo rm countryInfo.txt allCountries.zip allCountries.txt

# Install Node and NPM packages for GraphQL Services
cd /var/www/dataformsjs-site/app
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt-get install -y nodejs
# Use specific versions of npm packages because at the time of server setup the
# most recent server install [express-graphql] required older versions of graphql.
# In the future this will likely be fixed and the most recent package versions
# can be used again.
#
# npm i express graphql express-graphql better-sqlite3 cors
npm i express@4.17.2 graphql@15.3.0 express-graphql@0.12.0 better-sqlite3@7.4.5 cors@2.8.5
sudo npm install pm2 -g
pm2 start app.js

# Make sure PM2 starts the GraphQL service on reboot
pm2 startup
# ** Follow the copy and paste command from PM2 (looked like this on most recent server setup):
#   sudo env PATH=$PATH:/usr/bin /usr/lib/node_modules/pm2/bin/pm2 startup systemd -u ubuntu --hp /home/ubuntu
pm2 save

# DataFormsJS AI/ML Server
cd /var/www/dataformsjs-ai-ml
sudo apt install -y python3-pip python3-venv
python3 -m venv env
source env/bin/activate
# --------------------
# If using PyTorch
# Switched to PyTorch from TensorFlow on 4/20/2023. TensorFlow takes more memory
# and was the likely source of the server crashing occasionally in 2022 and 2023.
#
pip3 install numpy flask flask-cors Pillow scikit-learn Gunicorn
pip3 install --no-cache-dir torchvision
# --------------------
# If using TensorFlow
# The code currently requires specific versions of [keras, tensorflow, and scikit-learn].
# [scikit-learn] should generally be easy to handle version changes however both [Keras]
# and [tensorflow] can take a lot of research, testing, and updates to support new versions,
# so for this basic demo server specific versions are used.
#
# pip3 install numpy==1.21 keras==2.11.0 flask flask-cors Pillow scikit-learn Gunicorn
# pip3 install --no-cache-dir tensorflow-cpu

sudo apt install -y gunicorn
deactivate
cp /var/www/dataformsjs-site/app/app.py app.py
wget https://github.com/dataformsjs/static-files/raw/master/ai_ml/models/pima-indians-diabetes.json
mkdir public
cd public
cp /var/www/dataformsjs-site/app/Views/ai-ml-index.htm index.html
# To run as a stand-alone app use the following:
# cp /var/www/dataformsjs-site/app/Views/ai-ml-demo.htm index.html
cp /var/www/dataformsjs-site/public/favicon.ico favicon.ico
sudo nano /etc/systemd/system/gunicorn.service
# Copy file contents from [gunicorn.service.txt] and save
sudo systemctl enable gunicorn.service
sudo systemctl start gunicorn.service
# Other commands if needed
sudo systemctl status gunicorn.service
sudo systemctl stop gunicorn.service
sudo systemctl disable gunicorn.service
sudo systemctl restart gunicorn.service

# Default Site
cd /var/www/default-site
cp /var/www/dataformsjs-site/public/favicon.ico favicon.ico
nano index.html
# copy and paste file contents then save

# Reset permissions after all files and folders are set
sudo chown ubuntu:www-data -R /var/www
sudo chmod 0775 -R /var/www

# Setup a Cron Job using sudo to check for and delete expired playground sites.
# Runs once per minute, if not using [sudo] then sites will end up not being deleted.
sudo crontab -e
# Enter [1] for nano, and add the following after header comments:
* * * * * /usr/bin/php /var/www/dataformsjs-playground/scripts/delete-expired-sites.php > /var/www/dataformsjs-playground/app_data/last-cron-job.txt 2>&1
* * * * * /usr/bin/php /var/www/fastsitephp-playground/scripts/delete-expired-sites.php > /var/www/fastsitephp-playground/app_data/last-cron-job.txt 2>&1

# Reload nginx
sudo systemctl reload nginx

# Outside of the server
#   Created Static IP in AWS Lightsail for the Sever
#   GoDaddy - Used the IP for the Main A Record in Domain Settings
#   Update AWS to allow HTTPS in the Firewall on the Networking Tab

# Verify that the HTTP Sites work
# When testing with new servers [www] and other subdomains can be replaced with
# something for testing. For example use [www2, www3, etc] testing a new server
# setup before migration to the new server.
#
#   http://35.155.22.255/
#   http://fastsitephp.com/
#   http://www.fastsitephp.com/
#   http://playground.fastsitephp.com/
#   http://dataformsjs.com/
#   http://www.dataformsjs.com/
#   http://playground.dataformsjs.com/
#   http://ai-ml.dataformsjs.com/
#   http://www.dataformsjs.com/data/geonames/countries
#   http://www.dataformsjs.com/graphql
#   http://www.dataformsjs.com/graphql?query={countries{iso,country}}
#   http://www.dataformsjs.com/graphql?query=query($country:String!){regions(country:$country){name}}&variables={%22country%22:%22US%22}
#   http://www.dataformsjs.com/examples/image-classification-vue.htm#/en/
#       app.activeModel.predictUrl = "http://ai-ml.dataformsjs.com/predict/resnet50"
#   http://www.dataformsjs.com/examples/binary-classification-vue.htm#/en/
#       app.activeModel.saveUrl = "http://ai-ml.dataformsjs.com/predict/pima-indians-diabetes"
#   http://www.dataformsjs.com/en/playground
#       Use DevTools to set a breakpoint after `urlRoot` is defined then run:
#           s.urlRoot = 'http://playground.dataformsjs.com/'
#   http://www.fastsitephp.com/en/playground
#       Use DevTools to set breakpoint at `var state` then after it's defined run:
#           state.urlRoot = 'http://playground.fastsitephp.com/'
#       Test the default tempalte.
#       Delete and create a new site and manually test the following:
#           https://github.com/fastsitephp/playground/blob/master/scripts/app-error-testing.php
#           https://github.com/fastsitephp/playground/blob/master/scripts/app-error-testing-2.php
#   http://www.dataformsjs.com/unit-testing/
#   http://fastsitephp.com/en/security-issue
#       Submit form to confirm email works
#   Test many of the example pages

# Installed HTTPS Certificate using certbot:
#    https://certbot.eff.org/instructions?ws=nginx&os=ubuntufocal
#    For each main site make sure both non-www and www are entered, example:
#        [dataformsjs.com] and [www.dataformsjs.com]
#        The new certbot is doing this by default while previous versions did not.
#    After update the above HTTP links should redirect automatically to HTTPS.
#        Excluding the URL with IP in it.
#        Confirm DataFormsJS examples work from localhost once HTTPS takes effect.
#        - Examples include: Entry Form, Image Classification, GraphQL, Places Demo.
#        - This confirms that CORS and other functions works with the various services.

# Make sure playground sites are deleted as expected. The full test requires
# leaving sites open for over an hour to make sure they are deleted (both
# for DataFormsJS and FastSitePHP)
#
# To view cron history:
grep CRON /var/log/syslog

# View last result and sites directory:
cat /var/www/dataformsjs-playground/app_data/delete-sites-last-result.txt
cat /var/www/dataformsjs-playground/app_data/last-cron-job.txt
ls -la /var/www/dataformsjs-playground/public/sites
cat /var/www/fastsitephp-playground/app_data/delete-sites-last-result.txt
cat /var/www/fastsitephp-playground/app_data/last-cron-job.txt
ls -la /var/www/fastsitephp-playground/public/sites

# Restart the server and make sure everything works after a reboot
sudo reboot

# As new security updates are found and patched for the FastSitePHP Playground
# it can be updated on the main server wihtout having to setup a new server by
# running commands between the following lines in this file:
#   wget https://www.php.net/distributions/php-7.4.27.tar.bz2
#   ....
#   sudo make install
#
# For example in January 2022 updates for were made to address advanced
# exploits related to modifying [php.ini] disabled functions.
#
# IMPORANT - the main server only has 1 GB of memory so before running
# the `make` command stop node/express and gunicorn/python otherwise
# the server will freeze and require a reboot from the AWS console.
# It's possible that only gunicorn needs to be stopped due to TensorFlow.
#   pm2 stop app
#   sudo systemctl stop gunicorn.service
#
# After the updates are made reboot the server.
