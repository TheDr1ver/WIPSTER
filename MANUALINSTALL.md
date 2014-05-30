For manual installation, follow these steps:

1. **REMnux v4 ONLY:** Install [MASTIFF Upgrade](http://zeltser.com/remnux/remnux4-installation-notes.html)

	`wget http://remnux.org/mastiff-upgrade.zip`

	`unzip mastiff-upgrade.zip`

	`cd mastiff-upgrade`

	`sudo ./upgrade_mastiff.sh`

	`cd ..`

	`rm -rf mastiff-upgrade mastiff-upgrade.zip`

2. Install Apache

	`sudo apt-get update`

	`sudo apt-get install apache2`

	Check the install by browsing to server's IP, and look for **It Worked!**

3. Install PHP

	`sudo apt-get install php5 libapache2-mod-php5 php5-mcrypt`

	`sudo nano /etc/apache2/mods-enabled/dir.conf`

	Add index.php to the beginning of index files, like this:

    	<IfModule mod_dir.c>
    		DirectoryIndex index.php index.html index.cgi index.pl index.php index.xhtml index.htm
    	</IfModule>

4. Install PHP modules

	curl and sqlite:

	`sudo apt-get install php5-curl php5-sqlite`

5. Configure PHP max upload size

	`sudo nano /etc/php5/apache2/php.ini`

	Set:

		upload_max_filesize = 100M
		post_max_size = 100M
	Make sure file_uploads is set to ON, and tweak `max_file_uploads` as necessary

	CTRL+X Y [Enter] To save and quit Nano

6. **REMnux v4 ONLY:** Install [tcpick](http://tcpick.sourceforge.net/?t=1&p=INSTALL)

	`sudo apt-get install tcpick`

7. Install WIPSTER (assuming WIPSTER-master.zip is in your home directory)

	`sudo cp ~/WIPSTER-master.zip /var/www/`

	`cd /var/www/`

	`sudo unzip WIPSTER-master.zip`
	
	`sudo mv ./WIPSTER-master/* ./`

	`sudo rm -f WIPSTER-master.zip`
	
	`sudo rm -rf ./WIPSTER-master/`

8. Set permissions

	`sudo chown -R www-data:www-data /var/www/`

9. Restart Apache

	`sudo service apache2 restart`

10. Modify mastiff.conf 

    *REMnux v4:* /usr/local/etc/mastiff.conf

    *REMnux v5:* /usr/local/mastiff/mastiff.conf

    `sudo nano /usr/local/etc/mastiff.conf`

    Set **log_dir** to /var/www/mastiff and save the file

    **NOTE:** You can input your VirusTotal API here too if you want to auto-check VirusTotal

11. Open /var/www/func/config.php and edit it to include your specific login info and/or API keys

12. Browse to the IP of your REMnux box to get the WIPSTER start page

13. If you wish to run pastebin searches automatically every hour:

    `cd /etc/cron.d/ `

    `nano autopb`

    In nano, add the following lines:

        #<timing>    <user>        <command>
        00 * * * *    www-data     /usr/bin/php5 /var/www/autoPastebinRand.php
