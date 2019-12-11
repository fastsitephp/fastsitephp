#!/usr/bin/env bash

# -----------------------------------------------------------------------------
#
#  This is a Bash Script that runs on the production server [fastsitephp.com]
#  and is used to sync the latest changes from GitHub. It runs manually from
#  the author once published changes are confirmed.
#
#  To run:
#      bash /var/www/scripts/sync-server-from-github.sh
#
#  For testing with [rsync] use [-n = --dry-run]
#  Example:
#      rsync -nrcv --delete ~/fastsitephp-master/website/app/ /var/www/app
#
# -----------------------------------------------------------------------------

wget https://github.com/fastsitephp/fastsitephp/archive/master.zip -O ~/master.zip
unzip -q ~/master.zip
rm ~/master.zip
rsync -rcv --delete ~/fastsitephp-master/website/app/ /var/www/app
rsync -rcv --delete --exclude .env ~/fastsitephp-master/website/app_data/ /var/www/app_data
rsync -rcv --delete --exclude Web.config --exclude .htaccess --exclude index.php ~/fastsitephp-master/website/public/ /var/www/html
rsync -rcv --delete ~/fastsitephp-master/scripts/ /var/www/scripts
rsync -rcv --delete ~/fastsitephp-master/src/ /var/www/vendor/fastsitephp/src
rm -r ~/fastsitephp-master
