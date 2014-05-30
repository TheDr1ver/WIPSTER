
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
		
### Screenshots

http://i.imgur.com/ViPpjS1.png

http://i.imgur.com/2Qv2Ijq.png

http://i.imgur.com/VUQVldw.png

http://i.imgur.com/AWHnJET.png

http://i.imgur.com/Nh343bn.png

http://i.imgur.com/N1r9jOe.png

http://i.imgur.com/jkKQohb.png

http://i.imgur.com/CtilD3N.png

http://i.imgur.com/q7OBTsE.png

http://i.imgur.com/fOOajGt.png
		
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

**UPDATE:** WIPSTER installation is now included in the latest release of REMnux v5. Make sure your REMnux box can connect to the Internet, then simply run the following from your REMnux v5 console and WIPSTER should automatically install:

`/usr/local/sbin/install-wipster`

**REMnux v4 Installation Steps:**



For automatic installation, simply run the following commands from the REMnux command prompt:
	
`cd ~`

`wget https://raw.githubusercontent.com/TheDr1ver/WIPSTER/master/install.sh --no-check-certificate`

`chmod +x ./install.sh`

`sudo ./install.sh`
	
(Remember the default password for REMnux is **malware**) 

This will install all the necessary software for WIPSTER to work properly.

For manual installation, see [MANUALINSTALL.md](https://github.com/TheDr1ver/WIPSTER/blob/master/MANUALINSTALL.md).
		
## USAGE

WIPSTER currently allows for:

- Batch-checking of URL's against open sources (/urlResearch.php)
- Submission of suspicious files for static analysis using MASTIFF (/upload2.html)
- Local conversion of various strings between multiple formats (/convert.php)
- Searching various PasteBin-like sites for content, manually or automatically
- Streaming a Twitter feed in the footer of most pages based on a keyword search
- Submit files directly to ThreatAnalyzer via the API if you have a network-accessible copy installed
- View ThreatAnalyzer analysis summary for each uploaded file

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
