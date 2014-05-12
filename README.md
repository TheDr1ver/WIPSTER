
<center> 
# Web Interface Portal & Security Threat Engine for REMnux (WIPSTER) v0.2
(C) Nick Driver - 2014 | http://www.nickdriver.com | [@TheDr1ver](https://www.twitter.com/TheDr1ver)</center>


## ABOUT

WIPSTER is designed as an automated web portal for many of the tools included in
REMnux (http://zeltser.com/remnux). 

**NOTICE:** _This is a BETA version of WIPSTER that has NOT been fully tested for security.
		There is still a lot of sub-par coding and pointless comments left throughout the PHP files.
		As with most things in malware analysis, always be careful when dealing with malicious
		files, and make sure WIPSTER and REMnux are both installed on a network or 
		VM infrastructure far removed from any important data. USE AT YOUR OWN RISK!_
		
## REQUIREMENTS

* apache 2.0
* php Version 5.3.6-13ubuntu3.10
* PHP Modules:
	- curl
	- sqlite3
* tcpick (http://tcpick.sourceforge.net) - for extracting
	- TCP Streams from PCAPs
 
### OPTIONAL
* Anubis login credentials (Request account at https://anubis.iseclab.org/?action=register)
* MyWOT API Key (https://www.mywot.com/en/signup?destination=profile/api)
* VirusTotal API Key (https://www.virustotal.com/en/#dlg-join)
* Google SafeBrowsing API Key (http://www.google.com/safebrowsing/key_signup.html)
* Google Custom Search Engine for searching PasteBin-like sites (https://www.google.com/cse/all)
* Twitter App API Keys for streaming feed of #0Day (https://dev.twitter.com/apps/new)


## INSTALLATION

**NOTE:** For automatic installation, simply run the following commands from the REMnux command prompt:
	
`cd ~`

`wget https://raw.githubusercontent.com/TheDr1ver/WIPSTER/master/install.sh --no-check-certificate`

`chmod +x ./install.sh`

`sudo ./install.sh`
	
(Remember the default password for REMnux is **malware**) 

This will install all the necessary software for WIPSTER to work properly. For manual installation, follow these steps:

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
		
## USAGE

WIPSTER currently allows for:

- Batch-checking of URL's against open sources (/urlResearch.php)
- Submission of suspicious files for static analysis using MASTIFF (/upload2.html)
- Local conversion of various strings between multiple formats (/convert.php)
- Searching various PasteBin-like sites for content, manually or automatically
- Streaming a Twitter feed in the footer of most pages based on a keyword search

MASTIFF allows for the uploading of any filetype for analysis, including
password-protected .zip files that use the password "infected".

The URL checker currently only allows for http sites (not https).

## LICENSE

Web Interface Portal & Security Threat Engine for REMnux (WIPSTER) is licensed 
under a  [Creative Commons Attribution-NonCommercial-ShareAlike 3.0 United States License](http://creativecommons.org/licenses/by-nc-sa/3.0/us/).

I borrowed from JSON.org, but I found it fitting to include this as well:

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

(C) Nick Driver - 2014 | [nickdriver.com](http://nickdriver.com) | [@TheDr1ver](https://twitter.com/TheDr1ver)
