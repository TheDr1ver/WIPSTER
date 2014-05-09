<?php

#############################################################################
##### Set the below variables to your specific credentials and API keys #####
#############################################################################

###################
##### MASTIFF #####
###################

# Location of the Mastiff Config file
	#REMnux v5 Beta
		#$mastiffConf = '/usr/local/mastiff/mastiff.conf';
	#REMnux v4
		$mastiffConf = '/usr/local/etc/mastiff.conf';
# Location of the Mastiff script
	#REMnux v 5 Beta
		#$mastiffPy = '/usr/local/mastiff/mas.py';
	#REMnux v4
		$mastiffPy = '/usr/local/mastiff/mas.py';

##################
##### ANUBIS #####
##################

# Request account at https://anubis.iseclab.org/?action=register
$anubisUser = 'username';
$anubisPass = 'password';

$apis=array();

#################
##### MyWOT #####
#################

# Request API Key at https://www.mywot.com/en/signup?destination=profile/api

$apis['wot']='Your MyWOT API';

######################
##### VirusTotal #####
######################

# Request API Key at https://www.virustotal.com/en/#dlg-join

$apis['vt']='Your VirusTotal API';

###############################
##### Google SafeBrowsing #####
###############################

# Request API Key at http://www.google.com/safebrowsing/key_signup.html

$apis['goog']='Your Google SafeBrowsing API';

#######################################
##### Google Custom Search Engine #####
#######################################
# Set up your custom search at https://www.google.com/cse/all

# CSE API Key
$gcseKey = 'Your Custom Search Engine API';

# CSE Signature
$gcseSig = 'Your Custom Search Engine Signature Key';

# Custom Search Engine ID
$gcseCx = 'Your Custom Search Engine ID'; #Tied to a dummy acct

	########################
	##### AutoPastebin #####
	########################

	$gcseQuery = '%22TheDr1ver%22';	#Whatever default search term you want to search pastebin for
	$autopbUA = 'AutoPastebinSearch';	#Set to whatever useragent you want to use

##########################
##### Twitter Widget #####
##########################
# Set up your twitter application to get API keys at https://dev.twitter.com/apps/new

$twitterAPI = 'Twitter API Key';
$twitterToken = 'Twitter Access Token';
$twitterQuery = '#0Day -RT filter:links';	#Searches for #0Day, ignoring Re-Tweets, and showing only posts with links
$twitterConSec = 'Twitter Consumer Secret';
$twitterOAuthSec = 'Twitter Access Token Secret';

#######################
##### TRid Location
#######################

#REMnux v5 BETA
#$tridLoc = '/usr/local/TrID/triddefs.trd';

#REMnux v4
$tridLoc = '/usr/local/lib/triddefs.trd';

?>
