<?php
###########################
/*
 * DEFINE FUNCTIONS
 */
###########################

###########################
### GET APIs
###########################

function getAPIs(){

	$apis=array();
	
	$db = new SQLite3('./urls/urls.db');
	
	#Grab most recent VirusTotal API
	
	$vtresult = $db->querySingle('SELECT key FROM apis WHERE site = "vt" ORDER BY id DESC');
	$apis['vt']=$vtresult;
		
	$wotresult = $db->querySingle('SELECT key FROM apis WHERE site = "wot" ORDER BY id DESC');
	$apis['wot']=$wotresult;
		
	$googresult = $db->querySingle('SELECT key FROM apis WHERE site = "goog" ORDER BY id DESC');
	$apis['goog']=$googresult;
	
	foreach($apis as $id=>$val){
		if ($val==''){
			exit("Error retrieving API key for ($id). Check the urls.db database.");
		}
	}
	
	#print_r($apis);
	return $apis;
}

###########################
### FORM SUBMISSION
###########################

function formSubmit($_POST)
{
	
	$formSubmit = array();
	
	$url = $_POST['url'];
	$ticket = $_POST['ticket'];
	$ticket = strtolower($ticket);
	$ticket = ltrim($ticket,'inc0');
	$notes = htmlentities($_POST['notes'],ENT_QUOTES);
	
	$db = new SQLite3('./urls/urls.db');
	$result = $db->query('SELECT * FROM urls WHERE url = "'.$url.'"');
	
	$arrayDump=$result->fetchArray();
	if (!isset($arrayDump['id']))
	{
		$formSubmit['old'] = FALSE;
		$formSubmit['url'] = $url;
		$formSubmit['notes'] = $notes;
		$formSubmit['ticket'] = $ticket;
		$time = time();
		
		$url = $db->escapeString($url);
		$notes = $db->escapeString($notes);
		$ticket = $db->escapeString($ticket);
		$time = $db->escapeString($time);
		$subip = $db->escapeString($_SERVER['REMOTE_ADDR']);
		
		$command = 'INSERT INTO urls (notes, ticket, url, time, ip) VALUES ("'.$notes.'","'.$ticket.'","'.$url.'","'.$time.'","'.$subip.'")';
		$query = $db->exec($command);
	}
	else
	{
		$formSubmit['ticket'] = $arrayDump['ticket'];
		$formSubmit['notes'] = $arrayDump['notes'];
		$formSubmit['url'] = $arrayDump['url'];
		$formSubmit['old'] = TRUE;
	}
		
	return $formSubmit;
}

###########################
### GET IP(s) of A records
###########################

function getIP($url){
	$urlcheck = 'http://api.statdns.com/'.$url.'/a';
	$ipResults = @file_get_contents($urlcheck);
	#var_dump($ipResults);
	
	if($ipResults!=FALSE){
			
		$jsonIterator = new RecursiveIteratorIterator(
			new RecursiveArrayIterator(json_decode($ipResults, TRUE)),
			RecursiveIteratorIterator::SELF_FIRST
		);
		$ipResults = array();
				
		foreach ($jsonIterator as $key=>$val) {
			
			if((is_array($val))&&(isset($val['rdata']))){
				#echo 'rdata = '.$val['rdata'];
				if ((isset($val['type']))&&$val['type']=="A"){
					$ipResults[] = $val['rdata'];
				}
			}
			#else{
				#echo '<pre>';
				#var_dump($val);
				#echo '</pre>';
			#}
			
		}
		
		#echo '<pre>';
		#var_dump($ipResults);
		#echo '</pre>';
	}
	
	return $ipResults;
		
}

###########################
### GET Response Headers
###########################

function respHead($url){
	
	$opts = array(
		'http'=>array(
			'method'=>"GET",
			'user_agent'=>"Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:19.0) Gecko/20100101 Firefox/19.0"
		)
	);
	
	$context = stream_context_set_default($opts);
	
	$headers = @get_headers($url,1);
	$response = FALSE;
	if($headers==FALSE){
		$response[0] = 'Server was unable to pull headers. Check for typo\'s, but the site is likely down.';
		return $response;
	}
	
	if(strpos($headers[0],'200')==FALSE){
		$response = array();
		$response[0] = $headers[0];

		if(isset($headers['Location'])){
			
			#echo $url;
			#echo '<pre>';
			#var_dump($headers);
			#echo '</pre>';
			if(is_array($headers['Location'])){
				$response['Location'] = $headers['Location'][0];
			}
			else{
				$response['Location'] = $headers['Location'];
			}
			
		}
	}
	
	return $response;
}

###########################
### GET/CHECK MALWAREDOMAINS LIST
###########################

function getMLD($url)
{
	$today = getdate();
	$date = $today['year'].'-'.$today['mon'].'-'.$today['mday'];
	
	#Check if the MLD list has been downloaded already in the past 24 hrs
	$db = new SQLite3('./urls/urls.db');
	$result = $db->query('SELECT * FROM mld WHERE date = "'.$date.'"');
	$arrayDump=$result->fetchArray();
	
	if(!isset($arrayDump['id']))
	{
		$myfile = 'http://mirror1.malwaredomains.com/files/domains.txt';
		$myfile2 = 'http://mirror2.malwaredomains.com/files/domains.txt';
		
		$fileContent = file($myfile,FILE_SKIP_EMPTY_LINES);
			
		if($fileContent==FALSE)
		{
			$fileContent = file($myfile2,FILE_SKIP_EMPTY_LINES);
		}
		if($fileContent!=FALSE)
		{
			$fileContent = implode("\n", $fileContent);
			$command = 'INSERT INTO mld (domains, date) VALUES ("'.$fileContent.'","'.$date.'")';
			$query = $db->exec($command);
		}
	}
	else
	{
		$fileContent = $arrayDump['domains'];
		#echo '<pre>';
		#var_dump($fileContent);
		#echo '</pre>';
	}
		
	return $fileContent;
}

function checkMLD($text,$url)
{
	$text = explode("\n", $text);
	$results = array();
	
	$i=0;
	
	$pattern='~.*'.$url.'.*~';
	
	foreach($text as $line)
	{
		#echo $line;
		#echo '<br/>';
		
		preg_match($pattern,$line,$match);
		if ($match!=FALSE)
		{
			$results[$i]=$match;
		}
		
		$i++;
	}
	return $results;
}

###########################
### VIRUSTOTAL
###########################

/**
 * Custom VirusTotal URL scan 
 *
 * @param string $url the url we want to scan
 * @param int $autoScan queue a new scan if no record is found or if it's outdated -- 1 on
 *
 * @return
 * 0 = record exists and is up-to-date and the page is clean
 * 1 = record exists and is up-to-date and the page has malware
 * 2 = no record exists (request scan)
 * 3 = record exists but was not up-to-date and page is clean (request new scan)
 * 4 = record exists but was not up-to-date and page has malware (request new scan)
 * 5 = anything else (errors, etc.)
 *   
 */

require './func/vtinteract.php';

###########################
### GOOGLE SAFE BROWSING
###########################

function checkGoog($url,$api)
{
	$urlcheck = 'https://sb-ssl.google.com/safebrowsing/api/lookup?client=wipsterresearch&apikey='.$api.'&appver=0.1&pver=3.0&url='.$url;
	$googResults = file_get_contents($urlcheck);
	return $googResults;
}

###########################
### WEB OF TRUST
###########################

function checkWot($url,$api)
{
	$urlcheck = 'http://api.mywot.com/0.4/public_link_json2?hosts='.$url.'/&key='.$api;
	$wotResults = file_get_contents($urlcheck);
	#var_dump($wotResults);
	$jsonIterator = new RecursiveIteratorIterator(
		new RecursiveArrayIterator(json_decode($wotResults, TRUE)),
		RecursiveIteratorIterator::SELF_FIRST
	);
	$wotResults = array();
	
	$tmp=0;
	
	foreach ($jsonIterator as $key=>$val) if ($tmp++ < 1){
		if(is_array($val)){
			$wotResults = $val;
		}
	}
	
	/*
	echo '<pre>';
	echo 'wotresults: ';
	echo '<br/>';
	var_dump($wotResults);
	echo '</pre>';
	*/
	
		
	if(isset($wotResults['categories'])){
		
		foreach($wotResults['categories'] as $key=>$val){
		
			if($key==101){
				$wotResults['categories']['Malware or viruses'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==102){
				$wotResults['categories']['Poor customer experience'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==103){
				$wotResults['categories']['Phishing'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==104){
				$wotResults['categories']['Scam'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==105){
				$wotResults['categories']['Potentially illegal'] = $val;
				unset($wotResults['categories'][$key]);
			}
			
			if($key==201){
				$wotResults['categories']['Misleading claims or unethical'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==202){
				$wotResults['categories']['Privacy risks'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==203){
				$wotResults['categories']['Suspicious'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==204){
				$wotResults['categories']['Hate, discrimination'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==205){
				$wotResults['categories']['SPAM'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==206){
				$wotResults['categories']['Potentially unwanted programs'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==207){
				$wotResults['categories']['Ads / pop-ups'] = $val;
				unset($wotResults['categories'][$key]);
			}
			
			if($key==301){
				$wotResults['categories']['Online tracking'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==302){
				$wotResults['categories']['Alternative or controversial medicine'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==303){
				$wotResults['categories']['Opinions, religion, politics'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==304){
				$wotResults['categories']['Other'] = $val;
				unset($wotResults['categories'][$key]);
			}
			
			if($key==401){
				$wotResults['categories']['Adult content'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==402){
				$wotResults['categories']['Incidental nudity'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==403){
				$wotResults['categories']['Gruesome or shocking'] = $val;
				unset($wotResults['categories'][$key]);
			}
			if($key==404){
				$wotResults['categories']['Site for kids'] = $val;
				unset($wotResults['categories'][$key]);
			}
			
			if($key==501){
				$wotResults['categories']['Good site'] = $val;
				unset($wotResults['categories'][$key]);
			}
		
		}
	}
	
	#if(isset($jsonIterator[0]['blacklists'])){
	#	$wotResults['blacklists'] = $jsonIterator[0]['blacklists'];
	#}
		
	return $wotResults;
}

###########################
### SUCURI
###########################

function sucuri($url){
	
	$urlcheck = 'http://sitecheck2.sucuri.net/results/'.$url;
	
	$opts = array(
		'http'=>array(
			'method'=>"GET",
			'user_agent'=>"Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:19.0) Gecko/20100101 Firefox/19.0"
		)
	);
	
	$context = stream_context_create($opts);
	
	$sucRes = file($urlcheck, FILE_SKIP_EMPTY_LINES,$context);
	$result = FALSE;
	$split = FALSE;
	#var_dump($sucRes);
	$pattern[0]='/class\=\"scan\-red/';
	#$pattern[1]='/class\=\"scan\-yellow/';
	
	$i=0;
	#Search HTML for scan-red and scan-yellow class
	foreach($sucRes as $line_num=>$line){
		foreach($pattern as $patid=>$pat){
			preg_match($pat,$line,$match);
			if($match!=FALSE){
				#echo $line;
				#echo 'patid: '.$patid;
				#echo '<br/>';
				#$result = array();
				if($patid===0){
					$result[$i]='<div class="sucRed">'.$line.'</div>';
					$i++;
				}
				else{
					$result[$i]='<div class="sucYellow">'.$line.'</div>';
					$i++;
				}
			}
		}
	}
	
	
	#Split at References
	$splitPat='/(Domain)/';
	
	if($result!=FALSE){
		foreach($result as $key=>$val){
			$split[$key] = preg_split($splitPat, $val, -1);
		}
	}
	else{
		$split=FALSE;
	}
	
	
	return $split;
}

###########################
### IPVOID
###########################

function ipvoid($ip){
	
	
	$urlcheck = 'http://ipvoid.com/scan/'.$ip;
	$ipvoidRes = file($urlcheck);
	$result=array();
	
	$pattern[0]='/class\=\"label\slabel-warning/';
	$pattern[1]='/class\=\"label\slabel-danger/';
	$pattern[2]='/class\=\"label\slabel-success/';
	$fail='/\<h1\>Report\snot\sfound\<\/h1\>/';
	
	
	foreach($ipvoidRes as $line_num=>$line){
		$i=0;
		foreach($pattern as $pat){
			
			
			preg_match($pat,$line,$match);
			if($match!=FALSE){
				#$line=str_replace('href="','href="http://wepawet.cs.ucsb.edu/',$line);
				#$line=str_replace('">','" target="_blank">',$line);
				#$result[$i]=$line;
				$result[$ip]=$line;
			}
			$i++;
		}
		
		preg_match($fail,$line,$failmatch);
		if($failmatch!=FALSE){
			$result[$ip]='IP not analyzed by IPVoid.';
			$ipVoidResults = $result;
			return $ipVoidResults;
		}
		
	}
	#print_r($result);
	if($result!=''){
		$ipvoidResults = $result;
	}
	else{
		$ipvoidResults = FALSE;
	}
	
	return $ipvoidResults;
}

###########################
### WEPAWET
###########################

function wepawet($url){
	
	$urlArray = parse_url($url);
	if(isset($urlArray['scheme'])){
		if ($urlArray['scheme']!='http'){
			$url=str_replace($urlArray['scheme'],"http");
		}
	}
	else
	{
		$url = 'http://'.$url;
	}

	$md5Url = md5($url);
	
	$urlcheck = 'http://wepawet.cs.ucsb.edu/view.php?hash='.$md5Url.'&type=js';
	$wepawetRes1 = file($urlcheck);
	$result=array();
	
	$pattern[0]='/class\=\"suspicious/';
	$pattern[1]='/class\=\"malicious/';
	$pattern[2]='/class\=\"benign/';
	
	
	foreach($wepawetRes1 as $line_num=>$line){
		$i=0;
		foreach($pattern as $pat){
			
			
			preg_match($pat,$line,$match);
			if($match!=FALSE){
				$line=str_replace('href="','href="http://wepawet.cs.ucsb.edu/',$line);
				$line=str_replace('">','" target="_blank">',$line);
				$result[$i]=$line;
			}
			$i++;
		}
		
	}
	#print_r($result);
	if($result!=''){
		$wepawetResults = $result;
	}
	else{
		$wepawetResults = FALSE;
	}
	
	return $wepawetResults;
}

?>
