#!/bin/bash

# Intro
echo "This script will install WIPSTER on your version of REMnux."
# Press any key to continue
read -p "Press [ENTER] to continue..."

ROOT_UID=0 	# Only users with $UID 0 have root privileges

# Run as root
if [ "$UID" -ne "$ROOT_UID" ]
then
	echo "Must be root to run this script."
	exit #E_NOTROOT
fi

echo "Installing dependencies..."
apt-get update
apt-get install php5-fpm php5-mcrypt php5-curl php5-sqlite -y

#Modify nginx and MASTIFFconfigs
echo "Modifying nginx and mastiff configs..."
sed -i -e 's/index index\.html.*/index index.php index.html index.htm/g' /etc/nginx/sites-enabled/default

sed -i "55s/#//" /etc/nginx/sites-enabled/default
sed -i "56s/#//" /etc/nginx/sites-enabled/default
sed -i "62s/#//" /etc/nginx/sites-enabled/default
sed -i "63s/#//" /etc/nginx/sites-enabled/default
sed -i "64s/#//" /etc/nginx/sites-enabled/default
sed -i "65s/#//" /etc/nginx/sites-enabled/default
sed -i "56s/^/\t\ttry_files \$uri =404;\r\n/" /etc/nginx/sites-enabled/default

sed -i -e 's/short_open_tag = Off/short_open_tag = On/g' /etc/php5/fpm/php.ini
sed -i -e 's/max_execution_time = 30/max_execution_time = 300/g' /etc/php5/fpm/php.ini

sed -i -e 's/^log_dir.*/log_dir = \/var\/www\/mastiff/g' /usr/config/mastiff.conf

#Downloading and installing WIPSTER

mkdir /opt/remnux-wipster/
cd /opt/remnux-wipster/
git clone -b remnux-v6 --single-branch https://github.com/TheDr1ver/WIPSTER.git
cp -rf /opt/remnux-wipster/WIPSTER/.* /var/www/
chown -R www-data:www-data /var/www/
service nginx start
service php5-fpm restart


#Launching config page
firefox -new-window http://127.0.0.1/admin.php

