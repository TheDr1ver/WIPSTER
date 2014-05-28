<?php

#############################################################################
##### Set the below variables to your specific credentials and API keys #####
#############################################################################

#REMnux version
$remver='4';

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
		$mastiffPy = '/usr/local/bin/mas.py';

#######################
##### TRid Location
#######################

#REMnux v5 BETA
#$tridLoc = '/usr/local/TrID/triddefs.trd';

#REMnux v4
$tridLoc = '/usr/local/lib/triddefs.trd';

#################################
##### Malwr.com Integration
#################################
#Sign up at https://malwr.com/account/signup/
$malwrPlugin=False;	#Set to True to use the plugin
$malwrAPI = 'Your Malwr.com API';

#################################
##### ThreatAnalyzer Integration
#################################

$threatAnalyzerPlugin=False;	#False by default - set TRUE to upload & run files with ThreatAnalyzer using WIPSTER

#Vars
$threatAPI = '';	#ThreatAnalyzer API grabbed from ThreatAnalyzer Admin console
$threatBase = '';	#IP address of your ThreatAnalyzer Server (ex. 192.168.1.100)
$threatPage = 'http://'.$threatBase.'/api/v1';	#Leave this alone
$threatArgs = '';	#Leave blank unless specifically adding additional arguments to the API call (ex. 'md5=[md5]&setting=whatever')

#Submission Variables

$taSubPriority = 'high';	# Submission priority (ex. high, medium, low)
$taSubSandbox = '';	#MAC address of Sandbox to submit file to (ex: 00:11:22:33:44:55)
$taSubReanalyze = 'false';	# Reanalyze files that have previously been submitted

#UnComment the following lines if you want to run a custom action:
#$taSubCustomName = 'ActionAfterAnalysis';	#Custom Action name
#$taSubCustomVal = 'revert';	#Custom Action value
	#Additional Custom actions can be added manually in $postArray on md5page.php and accept-file.php

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
#Set up your custom search at https://www.google.com/cse/all

#CSE API Key
$gcseKey = 'Your Custom Search Engine API';

#CSE Signature
$gcseSig = 'Your Custom Search Engine Signature Key';

#Custom Search Engine ID
$gcseCx = 'Your Custom Search Engine ID'; 

	##### AutoPastebin
	$gcseQuery = 'SearchTerm';	#Whatever default search term you want to search pastebin for automatically
	$autopbUA = 'AutoPastebinSearch';	#Set to whatever useragent you want to use for the pastebin searching

##########################
##### Twitter Widget #####
##########################
# Set up your twitter application to get API keys at https://dev.twitter.com/apps/new

$twitterAPI = 'Twitter API Key';
$twitterToken = 'Twitter Access Token';
$twitterQuery = '#0Day -RT filter:links';	#Searches for #0Day, ignoring Re-Tweets, and showing only posts with links
$twitterConSec = 'Twitter Consumer Secret';
$twitterOAuthSec = 'Twitter Access Token Secret';

?>
