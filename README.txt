#########################################################################################
##### Web Interface Portal & Security Threat Engine for REMnux (WIPSTER) v0.1 		#####
##### (C) Nick Driver - 2014														#####
##### http://www.nickdriver.com - Twitter: @TheDr1ver								#####
#########################################################################################

#################
##### ABOUT #####
#################

WIPSTER is designed as an automated web portal for many of the tools included in
REMnux v4 (http://zeltser.com/remnux). 

NOTICE: This is a BETA version of WIPSTER that has NOT been fully tested for security.
		There is still a lot of sub-par coding and pointless comments left throughout the PHP files.
		As with most things in malware analysis, always be careful when dealing with malicious
		files, and make sure WIPSTER and REMnux are both installed on a network or 
		VM infrastructure far removed from any important data. USE AT YOUR OWN RISK!
		
########################		
##### REQUIREMENTS #####
########################

apache 2.0
php Version 5.3.6-13ubuntu3.10
PHP Modules:
	curl
	sqlite3
tcpick (tcpick.sourceforge.net) - for extracting
	TCP Streams from PCAPs
	

########################
##### INSTALLATION #####
########################

1. Install MASTIFF Upgrade http://zeltser.com/remnux/remnux4-installation-notes.html
	wget http://remnux.org/mastiff-upgrade.zip
	unzip mastiff-upgrade.zip
	cd mastiff-upgrade
	sudo ./upgrade_mastiff.sh
	cd ..
	rm -rf mastiff-upgrade mastiff-upgrade.zip

2. Install Apache
	sudo apt-get update
	sudo apt-get install apache2
	# Check install by browsing to server IP

3. Install MySQL (probably not necessary)
	sudo apt-get install mysql-server libapache2-mod-auth-mysql php5-mysql
	# Leave password blank (or don't) for all mysql setup prompts & hit enter

4. Install PHP
	sudo apt-get install php5 libapache2-mod-php5 php5-mcrypt
	sudo nano /etc/apache2/mods-enabled/dir.conf
	# Add index.php to the beginning of index files, like this:
	# <IfModule mod_dir.c>
	# 	DirectoryIndex index.php index.html index.cgi index.pl index.php index.xhtml index.htm
	# </IfModule>

5. Install PHP modules
	# curl
	sudo apt-get install php5-curl
	# sqlite
	sudo apt-get install php5-sqlite
	#sudo apt-get install php5-sqlite3 - Doesn't exist
	
6. Configure PHP max upload size
	sudo nano /etc/php5/apache2/php.ini
	# Set:
	# upload_max_filesize = 100M
	# post_max_size = 100M
	# Make sure file_uploads is set to ON, and tweak max_file_uploads as necessary
	# CTRL+X Y [Enter] To save and quit Nano
	
7. Install tcpick http://tcpick.sourceforge.net/?t=1&p=INSTALL
	sudo apt-get install tcpick
	
8. Install WIPSTER (assuming wipster-v0.1.zip is in home directory)
	sudo cp ~/wipster-v0.1.zip /var/www/
	cd /var/www/
	sudo unzip wipster-v0.1.zip
	sudo rm -f wipster-v0.1.zip

9. Set permissions
	sudo chown -R www-data:www-data /var/www/

10. Restart Apache
	sudo service apache2 restart

11. Modify /usr/local/etc/mastiff.conf so that the base directory is set to /var/www/mastiff
	# Input your VirusTotal API here too if you want
	sudo nano /usr/local/etc/mastiff.conf

12. Open /var/www/func/config.php and edit it to include your specific login info and/or API keys


13. Browse to the IP of your REMnux box to get the WIPSTER start page


Example MASTIFF.conf file (/usr/local/etc/mastiff.conf):

[Dir]
base_dir = /var/www/mastiff

[Misc]
verbose = off
copy = on

[Sqlite]
db_file = mastiff.db

[File ID]
trid = /usr/local/bin/trid
trid_db = /usr/local/lib/triddefs.trd

[Embedded Strings Plugin]
strcmd = /usr/bin/strings
str_opts = -a -t d
str_uni_opts = -e l

[VirusTotal]
api_key = YOUR VT API KEY HERE
submit = off

[pdfid]
pdfid_cmd = /usr/local/bin/pdfid.py
pdfid_opts = 

[pdf-parser]
pdf_cmd = /usr/local/bin/pdf-parser.py
feedback = on

[PDF Metadata]
exiftool = /usr/local/bin/exiftool

[yara]
yara_sigs = /usr/local/etc

[Digital Signatures]
disitool = /usr/local/bin/disitool.py
openssl = /usr/bin/openssl

[Office Metadata]
exiftool = /usr/local/bin/exiftool

[Single-Byte Strings]
length = 3
raw = False

[ZipExtract]
enabled = on
password = infected
feedback = on

[Office pyOLEScanner]
olecmd = /usr/local/bin/pyOLEScanner.py

#################
##### USAGE #####
#################

WIPSTER currently allows for:

- Batch-checking of URL's against open sources (/urlResearch.php)
- Submission of suspicious files for static analysis using MASTIFF (/upload2.html)
- Local conversion of various strings between several formats (/convert.php)

MASTIFF allows for the uploading of any filetype for analysis, including
password-protected .zip files that use the password "infected".

The URL checker currently only allows for http sites (not https).

###################
##### LICENSE #####
###################

Web Interface Portal & Security Threat Engine for REMnux (WIPSTER) is licensed 
under a  Creative Commons Attribution-NonCommercial-ShareAlike 3.0 United States License.
http://creativecommons.org/licenses/by-nc-sa/3.0/us/

Borrowed from JSON.org, but I found it fitting to include this as well:

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

(C) Nick Driver - 2014 - http://nickdriver.com - Twitter: @TheDr1ver