#!/usr/bin/env bash

# =============================================================================
#
#  ------------------------------------------------------------------
#  Install Apache, PHP, and FastSitePHP with a Starter Site
#  ------------------------------------------------------------------
#
#  https://www.fastsitephp.com/
#  https://github.com/fastsitephp/starter-site
#
#  Author:   Conrad Sollitt
#  Created:  2019
#  License:  MIT
#
#  Supported Operating Systems:
#      Ubuntu 18.04 LTS
#
#  Download and running this script (requires root/sudo).
#  This script works on a default OS when nothing is installed.
#      wget https://github.com/fastsitephp/fastsitephp/blob/master/scripts/shell/bash/create-fastsitephp-app.sh
#      sudo bash create-fastsitephp-app.sh
#
#  This script is generally safe to run multiple times because it checks
#  for if programs such as php are already installed, and prompts before
#  overwriting and existing site.
#
#  This script is linted using:
#  https://www.shellcheck.net/
#
# =============================================================================

# Set Bash Options for this Script
#   e - Exit if a command fails
#   u - Exit if variable is unset
#   o pipefail - Exit if any command in a pipe fails
set -eou pipefail

# Error Codes
# Output for errors is sent to STDERR by using the
# redirection command [>&2] before calling "echo".
ERR_NOT_ROOT=2
ERR_MISSING_APT=3
ERR_MISSING_USER=4

# Font Formatting for Output
FONT_RESET="\x1B[0m"
FONT_BOLD="\x1B[1m"
FONT_UNDERLINE="\x1B[4m"
FONT_WHITE="\x1B[97m"
FONT_BG_RED="\x1B[41m"
FONT_BG_GREEN="\x1B[42m"
FONT_SUCCESS="${FONT_BG_GREEN}${FONT_WHITE}"
FONT_ERROR="${FONT_BG_RED}${FONT_WHITE}"

# Get Path and Name of the Script
SCRIPT_PATH="${BASH_SOURCE[0]}"
SCRIPT_NAME=$(basename "${SCRIPT_PATH}")

# ---------------------------------------------------------
# Main function, this gets called from bottom of the file
# ---------------------------------------------------------
main ()
{
    # Declare local variables
    local user php_ver file time_taken ip

    # Environment Validation
    # These function will terminate the script if there is an error
    check_root
    check_apt
    user=$(get_user)

    # Install Apache and PHP
    apt_install 'apache2'
    apt_install 'php'

    # Enable PHP for Apache
    apt_install 'libapache2-mod-php'

    # Get the installed PHP major and minor version (example: 7.2)
    php_ver=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")

    # Add PHP Extensions. A large number of extensions exist and the
    # installed PHP version number needs to be included. The extensions
    # below are needed for all FastSitePHP common features to work and
    # for all Unit Tests to succeed, however they are not required
    # in order to use FastSitePHP.
    apt_install "php${php_ver}-sqlite"
    apt_install "php${php_ver}-gd"
    apt_install "php${php_ver}-bc"
    apt_install "php${php_ver}-simplexml"

    # The zip extension is required in order for the FastSitePHP
    # install script to run.
    apt_install "php${php_ver}-zip"

    # Enable a Fallback page so that [index.php] does not show in the URL.
    #
    # Instructions for manually performing this step:
    #     sudo nano /etc/apache2/apache2.conf
    # Scroll through the file and look for line:
    #     <Directory /var/www/>
    # Under it add the line:
    #     FallbackResource /index.php
    # Save using:
    #     {control+s} -> {control+x}
    #     or {control+x} -> {y} -> {enter}
    file='/etc/apache2/apache2.conf'
    echo -e "Checking file ${FONT_BOLD}${FONT_UNDERLINE}${file}${FONT_RESET}"
    if grep "FallbackResource \/index.php" $file; then
        echo -e "${FONT_BOLD}${FONT_UNDERLINE}${file}${FONT_RESET} already contains FallbackResource"
    else
        # Note, the "\\t" is used to insert 1 tab and will not work with sed on all OS's
        # Only OS's at listed at the top of this file are known to work.
        echo -e "Updating ${FONT_BOLD}${FONT_UNDERLINE}${file}${FONT_RESET} for FallbackResource"
        sed -i '/<Directory \/var\/www\/>/a \\tFallbackResource \/index.php' $file
    fi

    # Enable Gzip Compression for JSON Responses.
    # This is not enabled by default on Apache.
    #
    # Instructions for manually performing this step:
    #     sudo nano /etc/apache2/mods-available/deflate.conf
    # Add the following under similar commands:
    #     AddOutputFilterByType DEFLATE application/json
    file='/etc/apache2/mods-available/deflate.conf'
    echo -e "Checking file ${FONT_BOLD}${FONT_UNDERLINE}${file}${FONT_RESET}"
    if grep "AddOutputFilterByType DEFLATE application\/json" $file; then
        echo -e "${FONT_BOLD}${FONT_UNDERLINE}${file}${FONT_RESET} already contains DEFLATE with json"
    else
        # Note, the "\\t\t" is used to insert 2 tabs, see related comments in above code block
        echo -e "Updating ${FONT_BOLD}${FONT_UNDERLINE}${file}${FONT_RESET} for DEFLATE with json"
        sed -i '/<IfModule mod_filter.c>/a \\t\tAddOutputFilterByType DEFLATE application\/json' $file
    fi

    # Restart Apache
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Restarting Apache${FONT_RESET}"
    service apache2 restart

    # Set Permissions so that the main OS account expected to be used by a developer
    # exists and is granted access to create and update files on the site.
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Setting user permissions for ${user}${FONT_RESET}"
    adduser "${user}" www-data
    chown "${user}:www-data" -R /var/www
    chmod 0775 -R /var/www

    # Install the FastSitePHP Starter Site

    # Navigate to your home directory and download the Starter Site
    # This is a small download (~32 kb)
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Downloading FastSitePHP Stater Site${FONT_RESET}"
    wget https://github.com/fastsitephp/starter-site/archive/master.zip
    apt_install 'unzip'
    unzip master.zip

    # Copy Files
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Copying Files${FONT_RESET}"
    copy_dir ./starter-site-master/app /var/www/app
    copy_dir ./starter-site-master/app_data /var/www/app_data
    copy_dir ./starter-site-master/scripts /var/www/scripts
    cp -r ./starter-site-master/public/. /var/www/html

    # Install FastSitePHP (~470 kb) and Dependencies (~20 - 40 kb)
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Installing FastSitePHP${FONT_RESET}"
    php /var/www/scripts/install.php

    # Delete files that are not needed including the Apache default page
    # The [.htaccess] file being deleted is a version for local development
    # that is copied from the starter site (it's not needed for production).
    rm /var/www/html/.htaccess
    rm /var/www/html/Web.config
    if [[ -f /var/www/html/index.html ]]; then
        # If this script runs twice the file will already be deleted
        rm /var/www/html/index.html
    fi

    # Remove the downloaded files
    rm -r ./starter-site-master
    rm ./master.zip

    # Success! print summary
    echo ""
    echo ""
    echo -e "${FONT_SUCCESS}Success!${FONT_RESET}"
    echo "The FastSitePHP Starter Site is setup and ready to use."
    time_taken=$(format_time $SECONDS)
    echo "Time Taken to Install: [${time_taken}]"

    # Get public IP for the server from Google and show to the user
    # From: https://www.cyberciti.biz/faq/how-to-find-my-public-ip-address-from-command-line-on-a-linux/
    ip=$(dig TXT +short o-o.myaddr.l.google.com @ns1.google.com | awk -F'"' '{ print $2}')
    echo -e "View your site at: ${FONT_BOLD}${FONT_UNDERLINE} http://${ip}/ ${FONT_RESET}"
}

# ---------------------------------------------------------
# Make sure this script is running as root
# ---------------------------------------------------------
check_root ()
{
    if (( EUID != 0 )); then
        >&2 echo -e "${FONT_ERROR}Error${FONT_RESET}, unable to install site. This script requires the root user."
        >&2 echo "Install using the command below:"
        >&2 echo "    sudo bash ${SCRIPT_NAME}"
        exit $ERR_NOT_ROOT
    fi
}

# ---------------------------------------------------------
# Make sure APT is installed and then run an update
# ---------------------------------------------------------
check_apt ()
{
    if hash apt 2>/dev/null; then
        # Update [apt] Package Manager
        echo -e "Updating APT using ${FONT_BOLD}${FONT_UNDERLINE}apt update${FONT_RESET}"
        apt update
        # The [upgrade] is not required but recommend.
        # However, it takes many minutes so it is commented out by default.
        # apt upgrade
    else
        >&2 echo -e "${FONT_ERROR}Error${FONT_RESET}, This script requires Advanced Package Tool (APT) and currently only runs on"
        >&2 echo "Ubuntu, Debian, and related Linux distributions"
        exit $ERR_MISSING_APT
    fi
}

# -----------------------------------------------------------------------------
# Check for a Command and install using APT if missing
#   @param  $1  Command
# -----------------------------------------------------------------------------
apt_install ()
{
    if hash "$1" 2>/dev/null; then
        echo -e "${FONT_BOLD}${FONT_UNDERLINE}${1}${FONT_RESET} is already installed"
    else
        echo -e "Installing ${FONT_BOLD}${FONT_UNDERLINE}${1}${FONT_RESET}"
        apt install -y "$1"
        echo -e "${FONT_BOLD}${FONT_UNDERLINE}${1}${FONT_RESET} has been installed"
    fi
}

# -----------------------------------------------------------------------------
# Copy a Directory and prompt the user if it already exists
#   @param  $1  src
#   @param  $2  dest
# -----------------------------------------------------------------------------
copy_dir ()
{
    local input
    if [[ -d "$2" ]]; then
        echo "Directory [$1] already exists, overwrite existing files? [y, n]"
        read -r input
        if [[ "${input}" == 'y' || ${input} == 'Y' ]]; then
            cp -r "$1" "$2"
        fi
    else
        cp -r "$1" "$2"
    fi
}

# ---------------------------------------------------------
# Return a known user that will be granted access to the
# Web Root Directory, Currently supported:
#   /var/www
#     ubuntu
# ---------------------------------------------------------
get_user ()
{
    if id -u ubuntu >/dev/null 2>&1; then
        printf 'ubuntu'
    else
        >&2 echo -e "${FONT_ERROR}Error${FONT_RESET}, This script currently only runs on Ubuntu."
        >&2 echo "To run modify the function [get_user ()] for your OS."
        exit $ERR_MISSING_USER
    fi
}

# -----------------------------------------------------------------------------
# Format time in "mm:ss" or "hh:mm:ss" from the first parameter in seconds
# -----------------------------------------------------------------------------
format_time ()
{
    local h m s
    ((h=$1/3600))
    ((m=$1/60))
    ((s=$1%60))
    if (( h > 0 )); then
        printf "%02d:%02d:%02d" "$h" "$m" "$s"
    else
        printf "%02d:%02d" "$m" "$s"
    fi
}

# ---------------------------------------------------------
# Run the main() function and exit with the result
# ---------------------------------------------------------
main
exit $?
