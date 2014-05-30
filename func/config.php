<?php
#Testing update Comment
############################
##### Get Variables from DB
#############################
if(!function_exists('getSettings')){
	function getSettings(){
		$db = new SQLite3('/var/www/admin/admin.db');
		$result = $db->query('SELECT * FROM admin WHERE id = 1');
		
		if(isset($result))
		{
			while ($res=$result->fetchArray()){
				#$_SESSION['size']=$res['size'];
				#$malwrRes['uuid']=$res['uuid'];
				$settingRes=array();
				$settingRes=$res;
				/*	remver
				 * 	mastiffconf
				 * 	mastiffpy
				 * 	tridloc
				 * 	malwrPlugin
				 * 	malwrAPI
				 * 	threatanalyzerplugin
				 * 	threatapi
				 * 	threatbase
				 * 	threatpage
				 * 	threatargs
				 * 	tasubpriority
				 * 	tasubsandbox
				 * 	tasubreanalyze
				 * 	anubisuser
				 * 	anubispass
				 * 	wotapi
				 * 	vtapi
				 * 	googapi
				 * 	gcsekey
				 * 	gcsesig
				 * 	gcsecx
				 * 	gcsequery
				 * 	autopbua
				 * 	twitterapi
				 * 	twittertoken
				 * 	twitterquery
				 * 	twitterconsec
				 * 	twitteroauthsec
				 */
				
			}
		}
		if(!$result){
			$settingRes['db']=$db->lastErrorMsg();
		}
		else{
			$settingRes['db']=$db->changes().' Record updated successfully.';
		}
		$db->close();
		return $settingRes;
	}#end getsettings function
	$settingRes=getSettings();
}#end getsettings exist check




#############################################################################
##### Set the below variables to your specific credentials and API keys #####
#############################################################################

#REMnux version
$remver=$settingRes['remver'];	#REMnux version - set to 4 or 5

###################
##### MASTIFF #####
###################

# Location of the Mastiff Config file
	#REMnux v5 Beta
		#$mastiffConf = '/usr/local/mastiff/mastiff.conf';
	#REMnux v4
		#$mastiffConf = '/usr/local/etc/mastiff.conf';
	$mastiffConf = $settingRes['mastiffconf'];
# Location of the Mastiff script
	#REMnux v 5 Beta
		#$mastiffPy = '/usr/local/mastiff/mas.py';
	#REMnux v4
		#$mastiffPy = '/usr/local/bin/mas.py';
	$mastiffPy = $settingRes['mastiffpy'];

#######################
##### TRid Location
#######################

#REMnux v5 BETA
#$tridLoc = '/usr/local/TrID/triddefs.trd';

#REMnux v4
#$tridLoc = '/usr/local/lib/triddefs.trd';

$tridLoc = $settingRes['tridloc'];

#################################
##### Malwr.com Integration
#################################

#Sign up at https://malwr.com/account/signup/

if($settingRes['malwrPlugin']===1){
	$malwrPlugin=True;	#Set True if you wish to use the malwr.com plugin
}
else{
	$malwrPlugin=False;	
}

$malwrAPI=$settingRes['malwrAPI'];

#################################
##### ThreatAnalyzer Integration
#################################

if($settingRes['threatanalyzerplugin']===1){
	$threatAnalyzerPlugin=True;	#Set True if you wish to use a network-accessible version of ThreatAnalyzer
}
else{
	$threatAnalyzerPlugin=False;
}

#Vars
$threatAPI = $settingRes['threatapi'];
$threatBase = $settingRes['threatbase'];
$threatPage = $settingRes['threatpage'];
$threatArgs = $settingRes['threatargs'];

#Submission Variables

$taSubPriority = $settingRes['tasubpriority'];	# Submission priority
$taSubSandbox = $settingRes['tasubsandbox'];	#Sandbox for submission
$taSubReanalyze = $settingRes['tasubreanalyze'];	# Reanalyze files that have previously been submitted

#New

$taSubGroupOpt = $settingRes['tasubgroupopt'];	#custom | for_any_group | for_all_group
$taSubGroup = $settingRes['tasubgroup'];		# Group ID#

#Comment the following out if you don't want to run a custom action
$taSubCustomName = $settingRes['tasubcustomname'];	#Custom Action name
$taSubCustomVal = $settingRes['tasubcustomval'];	#Custom Action value
#Additional Custom actions can be added manually in $postArray on md5page.php and accept-file.php

##################
##### ANUBIS #####
##################

# Request account at https://anubis.iseclab.org/?action=register

$anubisUser = $settingRes['anubisuser'];
$anubisPass = $settingRes['anubispass'];

$apis=array();
#################
##### MyWOT #####
#################

# Request API Key at https://www.mywot.com/en/signup?destination=profile/api

$apis['wot']=$settingRes['wotapi'];

######################
##### VirusTotal #####
######################

# Request API Key at https://www.virustotal.com/en/#dlg-join

$apis['vt']=$settingRes['vtapi'];

###############################
##### Google SafeBrowsing #####
###############################

# Request API Key at http://www.google.com/safebrowsing/key_signup.html

$apis['goog']=$settingRes['googapi'];

#######################################
##### Google Custom Search Engine #####
#######################################
#CSE API Key
$gcseKey = $settingRes['gcsekey'];
#CSE Signature
$gcseSig = $settingRes['gcsesig'];
#Custom Search Engine ID
$gcseCx = $settingRes['gcsecx']; 

##### AutoPastebin
$gcseQuery = $settingRes['gcsequery'];
$autopbUA = $settingRes['autopbua'];

##########################
##### Twitter Widget #####
##########################


$twitterAPI = $settingRes['twitterapi'];
$twitterToken = $settingRes['twittertoken'];
$twitterQuery = $settingRes['twitterquery'];
$twitterConSec = $settingRes['twitterconsec'];
$twitterOAuthSec = $settingRes['twitteroauthsec'];

##### Dump to page for debugging
#echo "<pre>";
#print_r(get_defined_vars());
#echo "</pre>";

?>
