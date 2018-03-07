#!/bin/bash

# Additional configuration and packages that our Vagrantbox requires
# We will need php7.0, so install it
sudo apt-get -y update
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get -y install php7.0
sudo apt-get -y update
# This includes the base php7.0 packages, plus a couple mbstring and dom that
# some of the composer dependencies require
sudo apt-get -y install php7.0-mysql pdo pdo_mysql pdo_pgsql pgsql soap libapache2-mod-php7.0 php7.0-mbstring php7.0-dom php7.0-curl php7.0-zip
sudo a2dismod php5
sudo a2enmod php7.0
sudo apachectl restart

# Make sure the composer has a recent version. This probably
# only suppress the yellow banner
sudo composer self-update
