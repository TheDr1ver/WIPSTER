#!/bin/bash

# Intro
echo "This script will install WIPSTER on your version of REMnux."
# Press any key to continue
read -p "Press any key to continue..."

ROOT_UID=0 	# Only users with $UID 0 have root privileges

# Run as root
if [ "$UID" -ne "$ROOT_UID" ]
then
	echo "Must be root to run this script."
	exit #E_NOTROOT
fi

# Prompt for version fo REMnux
echo "Please enter your version of REMnux [4 or 5]:"
read remv
if (( $remv != 4 )) && (( $remv !=5 )); then
	echo "$remv is not a valid version. Please enter 4 or 5:"
	read remv
	if (( $remv != 4 )) && (( $remv !=5 )); then
		echo "$remv is not 4 or 5. Exiting."
		exit 1
	else
		echo "REMnux v $remv selected."
	fi
else
	echo "REMnux v $remv selected."
fi

# Install apache/mysql/php
echo "Updating repositories..."
apt-get update 				# Make sure repo's are updated
echo "Installing apache..."
apt-get install apache2 -y	# Install apache
# echo "Installing MySQL..."
#apt-get install mysql-server libapache2-mod-auth-mysql php5-mysql -y 	# Install MySQL - I don't think this is necessary
echo "Installing PHP and dependencies..."
apt-get install php5 libapache2-mod-php5 php5-mcrypt php5-curl php5-sqlite -y	# Install php and dependencies

# Update apache2 dir.conf
# /etc/apache2/mods-enabled/dir.conf
echo "Updating apache2 dir.conf..."


# Configure PHP
# /etc/php5/apache2/php.ini

echo "Creating backup of /etc/php5/apache2/php.ini as php.bak..."
cp /etc/php5/apache2/php.ini /etc/php5/apache2/php.bak

echo "Modifying /etc/php5/apache2/php.ini..."
# upload_max_filesize = 100M
sed -i -e 's/upload_max_filesize =.*/upload_max_filesize = 100M/g' /etc/php5/apache2/php.ini
# post_max_size = 100M
sed -i -e 's/post_max_size =.*/post_max_size = 100M/g' /etc/php5/apache2/php.ini

# Install WIPSTER
echo "Downloading WIPSTER from github..."
wget https://github.com/TheDr1ver/WIPSTER/archive/master.zip
echo "Unzipping package..."
unzip ./master.zip
echo "Copying WIPSTER directory to /var/www/..."
cp -r ./WIPSTER-master/* /var/www/
echo "Making empty directories..."
mkdir /var/www/upload
mkdir /var/www/upload/malware
mv /var/www/index.html /var/www/indexhtml.bak

# Set permissions
echo "Setting /var/www/ permissions to chown -R www-data:www-data..."
chown -R www-data:www-data /var/www/

# Mastiff fix for REMnux v4
if (( "$remv"==4 )); then
	wget http://remnux.org/mastiff-upgrade.zip
	unzip mastiff-upgrade.zip
	cd mastiff-upgrade
	./upgrade_mastiff.sh
	cd ..
	rm -rf mastiff-upgrade mastiff-upgrade.zip
fi

# Restart Apache
echo "Restarting Apache..."
service apache2 restart

# Modify mastiff.conf
echo "Modifying mastiff.conf"
# if REMnux v4 - edit /usr/local/etc/mastiff.conf
if (( "$remv"==4 )); then
	echo "Backing up mastiff.conf as mastiff.bak..."
	cp /usr/local/etc/mastiff.conf /usr/local/etc/mastiff.bak
	echo "Setting log_dir variable in /usr/local/etc/mastiff.conf"
	sed -i -e 's/log_dir =.*/log_dir = \/var\/www\/mastiff/g' /usr/local/etc/mastiff.conf
# if REMnux v5 - edit /usr/local/mastiff/mastiff.conf
elif (( "$remv"==5 )); then
	echo "Backing up mastiff.conf as mastiff.bak..."
	cp /usr/local/mastiff/mastiff.conf /usr/local/mastiff/mastiff.bak
	echo "Setting log_dir variable in /usr/local/mastiff/mastiff.conf"
	sed -i -e 's/log_dir =.*/log_dir = \/var\/www\/mastiff/g' /usr/local/mastiff/mastiff.conf
else
	echo "REMnux version not detected as 4 or 5. Edit mastiff.conf manually"
fi

# Prompt user for autorun pastebin searches
echo "Would you like to set up the auto-Pastebin checker to run every hour? [Y/N]:"
read autopb
autopb=${autopb,,}	#Set to lowercase
#echo "autopb = $autopb"
if [ "$autopb" == "y" ];then
	# Create /etc/cron.d/autopb
	# #<timing>		<user>		<command>
	# 00 * * * *	www-data	/usr/bin/php5 /var/www/autoPastebinRand.php
	echo "#<timing>		<user>		<command>" > /etc/cron.d/autopb
	echo "00 * * * *	www-data	/usr/bin/php5 /var/www/autoPastebinRand.php" > /etc/cron.d/autopb
	echo "/etc/cron.d/autopb created. Pastebin will be checked every hour for the keyword(s) in your config.php file."
else
	echo "auto-Pastebin checker not set up. Add the following to /etc/cron.d/autopb if you wish to set it up manually:"
	echo "#<timing>		<user>		<command>"
	echo "# 00 * * * *	www-data	/usr/bin/php5 /var/www/autoPastebinRand.php"
fi

# Set vars based on REM version
echo "Putting the finishing touches on config.php..."

if (( "$remv"==5 )); then
	# Change Mastiff Config Lines
	sed -i -e "s/\$mastiffConf = '\/usr\/local\/etc\/mastiff\.conf';.*/#\$mastiffConf = '\/usr\/local\/etc\/mastiff\.conf';/g" /var/www/func/config.php
	sed -i -e "s/#\$mastiffConf = '\/usr\/local\/mastiff\/mastiff\.conf';.*/\$mastiffConf = '\/usr\/local\/mastiff\/mastiff\.conf';/g" /var/www/func/config.php
	sed -i -e "s/\$mastiffPy = '\/usr\/local\/bin\/mas\.py';.*/#\$mastiffPy = '\/usr\/local\/bin\/mas\.py';/g" /var/www/func/config.php
	sed -i -e "s/#\$mastiffPy = '\/usr\/local\/mastiff\/mas\.py';.*/\$mastiffPy = '\/usr\/local\/mastiff\/mas\.py';/g" /var/www/func/config.php
	# Change trID line
	sed -i -e "s/\$tridLoc = '\/usr\/local\/lib\/triddefs.trd';.*/#\$tridLoc = '\/usr\/local\/lib\/triddefs.trd';/g" /var/www/func/config.php
	sed -i -e "s/#\$tridLoc = '\/usr\/local\/TrID\/triddefs.trd';.*/\$tridLoc = '\/usr\/local\/TrID\/triddefs.trd';/g" /var/www/func/config.php
	# Change footer
	sed -i -e "s/REMNUX 4/REMNUX 5/g" /var/www/footer.php
else
	apt-get install tcpick	# Install tcpick for REMnux v4
fi

echo "Installation finished!"
echo "Setup your config.php file and then browse to your local IP to access WIPSTER."


# Prompt - Press any key to edit config.php with SciTE
read -p "Press any key to edit /var/www/func/config.php..."

gksu scite /var/www/func/config.php

# Launch Firefox
su - remnux -c "firefox localhost" &
