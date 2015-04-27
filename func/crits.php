<?

###################################
##### Define Exclusions
###################################

$not_domain=array(
	'.*tools\.google\.com.*',
	'.*download\.windowsupdate\.com.*'
);

$not_ip=array(
	''
);

$not_uri=array(
	'.*tools\.google\.com.*',
	'.*download\.windowsupdate\.com.*'
);

$not_useragent = array(
	'.*Google Update.*',
	'.*Microsoft\-CryptoAPI.*'
);




###################################
##### Define CRITs Functions
###################################


#Set the constants - move this to the config file/db later
#$critsPage = "https://192.168.1.131/api/v1/";
#$critsLogin = "username=<username>&api_key=<api_key>";

#Search the CRITs API for a sample md5, domain, or IP
function searchCRITs($type, $search, $critsPage, $critsLogin){
	
	#Get response for sample
	if ($type=="sample"){
		$target=$critsPage."samples/?c-md5=".$search;
	}
	
	#Get response for domain
	if ($type=="domain"){
		$search=urlencode($search);
		$target=$critsPage."domains/?c-domain=$search";
	}
	
	#Get response for IP
	if ($type=="ip"){
		$target=$critsPage."ips/?c-ip=".$search;
	}
	
	if($type=="event"){
		$search=urlencode($search);
		$target=$critsPage."events/?c-title=$search";
	}
	
	#Send the request
	$target=$target."&".$critsLogin;
	echo '<p>URL sent: '.$target.'</p><br/>';
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $target);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$results=array($result,$httpCode);
	if($type=='sample'){
		echo "Response:";
		echo "<pre>";
		print_r($results);
		echo "</pre>";
	}
	return $results;
	
}

##### Upload to CRITs

function uploadCRITs($data, $critsPage, $critsLogin){
	
	/*
	echo "Running TYPE check:";	#DEBUG
	echo "<pre>";				#DEBUG
	print_r($data);				#DEBUG
	echo "</pre>";				#DEBUG
	*/
	
	if($data['type']=='sample'){
		$target=$critsPage."samples/";
	}
	
	elseif($data['type']=='domain'){
		$target=$critsPage."domains/";
	}
	
	elseif($data['type']=='ip'){
		$target=$critsPage."ips/";
	}
	
	elseif($data['type']=='pcap'){
		$target=$critsPage."pcaps/";
	}
	
	elseif($data['type']=='email'){
		$target=$critsPage."emails/";
	}
	
	elseif($data['type']=='raw_data'){
		$target=$critsPage."raw_data/";
	}
	
	elseif($data['type']=='event'){
		$target=$critsPage."events/";
	}
	
	elseif($data['type']=='relationship'){
		$target=$critsPage."relationships/";
	}
	else{
		echo "target type: $data[type] does not exist<br/>";
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		continue;
	}
	
	$target=$target."?".$critsLogin;	#Add API login to end of request
	#echo '<p>URL sent: '.$target.'</p><br/>';	#Debug
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $target);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
	unset($data['type']);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$results=array($result,$httpCode);
	
	return $results;
}



		
################################
#### Upload the files to CRITs
################################

#Build the data array
function dataBuild($type, $new_file_name, $_POST, $_FILES=NULL, $critsPage=NULL, $critsLogin=NULL, $relationArray=NULL, $desc=NULL, $md5=NULL){

	$data=array(
		'type'=>$type,
		'source'=>'WIPSTER',	#Should be added to dynamic configs instead of hardcoded
		'ticket'=>$_POST['ticket']
	);
	
	#Set parameters unique to Samples
	if($type=='sample'){
		$filedata="@/var/www/upload/malware/".$new_file_name;
		$data['upload_type']='file';
		$data['file_format']='raw';
		$data['filedata']=$filedata;
	}
	
	if($type=='sampleRetro'){
		$data['type']='sample';
		$filedata="@/var/www/upload/malware/$new_file_name";
		$data['upload_type']='file';
		$data['file_format']='raw';
		$data['filedata']=$filedata;
	}
	
	#Set parameters unique to PCAPs
	if($type=='pcap'){
		$filedata="@/var/www/upload/malware/".$new_file_name;
		$data['filedata']=$filedata;
	}
	
	#Set parameters unique to Domains
	if($type=='domain'){
		$data['domain']=$_POST['domain'];
		if(!empty($new_file_name)){
			$data['domain']=$new_file_name;
		}
	}
	
	#Set parameters unique to IPs
	if($type=='ip'){
		$data['ip']=$_POST['ip'];
		$data['ip_type']="Address - ipv4-addr";
		if(!empty($new_file_name)){
			$data['ip']=$new_file_name;
		}
	}
	
	#Set parameters unique to Event (used for relating tickets)
	if($type=='event'){
		$data['event_type']='Incident';
		$data['title']=$_POST['ticket'];
		$data['description']="This event is for ticket# $_POST[ticket].";
		$dt = new DateTime();
		$data['date']=$dt->format('Y-m-d H:i:s.u');
	}
	
	#Set parameters unique to emails
	if($type=='email'){
		$filedata="@/var/www/upload/malware/".$new_file_name;
		$data['filedata']=$filedata;
		#Get the file extension of the uploaded email
		$fileExt = pathinfo($new_file_name, PATHINFO_EXTENSION);
		$fileExt = strtolower($fileExt);
		#if eml, set the eml upload_type
		echo "<br/>Detected Extension: $fileExt<br/>";
		if($fileExt=='eml'){
			$data['upload_type']='eml';
		}
		#if msg, set the msg upload_type
		elseif($fileExt=='msg'){
			$data['upload_type']='msg';
		}
		#if other, set the raw upload_type
		else{
			$data['upload_type']='raw';
		}	
	}
	
	#Set parameters unique to Event - UserAgent
	if($type=='useragent'){
		$data['type']='event';
		$data['event_type']='Indicators - Network Activity';
		$data['title']=$new_file_name;
		if($desc!=NULL){
			$data['description']="This User-Agent is from a ThreatAnalyzer callout in ticket# $_POST[ticket].";
		}
		else{
			$data['description']="This User-Agent was added manually for ticket# $_POST[ticket].";
		}
		
		$dt = new DateTime();
		$data['date']=$dt->format('Y-m-d H:i:s.u');
	}
	
	#Set parameters unique to Event - URI
	if($type=='uri'){
		$data['type']='event';
		$data['event_type']='Indicators - Network Activity';
		$data['title']=$new_file_name;
		if($desc!=NULL){
			$data['description']="This URI is from a ThreatAnalyzer callout in ticket# $_POST[ticket].";
		}else{
			$data['description']="This URI was added manually for ticket# $_POST[ticket].";
		}
		
		$dt = new DateTime();
		$data['date']=$dt->format('Y-m-d H:i:s.u');
	}
	
	#Set parameters unique to Event - VT Definitions
	if($type=='vt'){
		$data['type']='event';
		$data['event_type']='Malware Samples';
		
		if($desc=='sep'){
			$data['title']=$new_file_name;
			$data['description']="This is a VT SEP result pulled from ticket# $_POST[ticket].";
		}
		elseif($desc=='mse'){
			$data['title']=$new_file_name;
			$data['description']="This is a VT MSE result pulled from ticket# $_POST[ticket].";
		}
		else{
			$data['title']=$new_file_name;
			$data['description']="This VT result was added manually for ticket# $_POST[ticket].";
		}
		$dt = new DateTime();
		$data['date']=$dt->format('Y-m-d H:i:s.u');
	}
	
	
	
	#Set parameters unique to campaign - Add this later if we want to be able to make new campaigns from this form
	#if($type=='campaign'){
	#}
	
	if($type=='notes'){
		$type='raw_data';
		$data['upload_type']='metadata';
		$data['data']=$_POST['notes'];
		if(!empty($_FILES['malware']['name'])){
			$data['title']="Notes related to $_FILES[malware][name] in ticket $_POST[ticket]";
		}
		else{
			$data['title']="Notes related to ticket $_POST[ticket] (no sample uploaded)";
		}
	}
	
	if($type=='relationship'){
		$data['left_type']=$relationArray['TLO1type'];
		$data['left_id']=$relationArray['TLO1id'];
		$data['right_type']=$relationArray['TLO2type'];
		$data['right_id']=$relationArray['TLO2id'];
		$data['rel_type']='Related_To';
		$data['rel_reason']="Related via ticket $data[ticket]";
	}
	
	#DEBUG
	#echo "<pre>";
	#print_r($data);
	#echo "</pre>";
	
	$critsResults = uploadCRITs($data, $critsPage, $critsLogin);
	$critsJSON = json_decode($critsResults[0],true);
	
	if($critsResults[1]!=200 && $type!="relationship" && $type!="email"){	#Error checking
		echo "<h2>ERROR uploading $type to CRITs: $critsResults[1] - $critsJSON[message]</h2><pre>";
		print_r($critsResults);
		echo "</pre><br/>";
	}
	elseif($type=="email" && $critsResults[1]==500){
		echo "<h2>Stupid email error: $critsResults[1] - $critsJSON[message]</h2><pre>";
		print_r($critsResults);
		echo "</pre><br/>";
	}
	else{
		echo "<h3>Success uploading to CRITS: $critsResults[1] - $critsJSON[message]</h3><pre>";
		print_r($critsResults);
		echo "</pre><br/>";
	}
	
	if($type=='sample' || $type=='pcap' || $type=='email'){
		unlink('/var/www/upload/malware/'.$new_file_name);
	}
		
	return $critsJSON;
}

##############################################
##### Process data from critsform.php
##############################################

#If checkbox checked, then add value to critsUpdateData
function critsChkFields($critsChkbox, $critsInput, $critsUpdateData, $allPostKeys){
	$critsVals = array();
	$critsKeys = array();
	
	$pattern='/,?('.$critsChkbox.'\d*),?/';
	#if (preg_match_all('/,?(useragentchk\d+),?/',$allPostKeys,$matches)){
	if (preg_match_all($pattern,$allPostKeys,$matches)){
		
		echo "<pre>";
		print_r($matches);
		echo "</pre>";
		
		$critsKeys[$critsChkbox] = $matches[1];
		while($key = array_shift($critsKeys[$critsChkbox])){
			$critsVals[$critsChkbox][$key]=$_POST[$key];
		}
		$genIndex=0;
		foreach($critsVals[$critsChkbox] as $key=>$val){
			#echo "key: $key val: $val<br/>";
			if($val=='on'){
				#echo "$key is checked";
				$pattern='/'.$critsChkbox.'(\d*)/';
				#preg_match_all('/useragentchk(\d+)/',$key,$matches);
				preg_match_all($pattern,$key,$matches);
				
				echo "<pre>";
				echo $key;
				print_r($matches);
				echo "</pre>";
				
				if($matches[1][0]!=''){
					$chkindex=$matches[1][0];
					$critsInfoSource = 'ta'.$critsInput;
				}
				else{
					$chkindex='';
					$critsInfoSource = 'gen'.$critsInput;
				}
				
				#print_r($matches);
				#echo "chkindex: $chkindex";
				#$critsUpdateData['taUseragent'][]=$_POST["useragent$chkindex"];
				if(!empty($_POST[$critsInput.$chkindex])){
					if($chkindex!=''){
						$critsUpdateData[$critsInfoSource][$chkindex]=$_POST[$critsInput.$chkindex];
					}
					else{
						$critsUpdateData[$critsInfoSource][$genIndex]=$_POST[$critsInput];
						$genIndex=$genIndex+1;
					}
					
				}
				
				

			}
		}
	}
	return $critsUpdateData;
}

#### Build and Upload the Data sent to the critsform.php

function metaDataBuild($formField, $formType, $type, $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON, $desc=NULL){
	if(isset($critsUpdateData[$formField])){
		#echo "critsUpdateData[$formField] is set<br/><br/>";
		foreach($critsUpdateData[$formField] as $key=>$val){
			
			#Search crits for an existing event matching this ticket #
			$eventSearch = searchCRITs($type, $val, $critsPage, $critsLogin);
			echo "eventSearch:";
			echo "<pre>";
			print_r($eventSearch);
			echo "</pre>";
			$eventJSON = json_decode($eventSearch[0],true);				
			#If the event already exists, just set the critsJSON tags and move on. Otherwise, add the event.
			if(isset($eventJSON['objects'][0]['_id'])){
				$critsJSON[$formField][$key]['id']=$eventJSON['objects'][0]['_id'];
				if($type=='event'){
					$critsJSON[$formField][$key]['type']='Event';
				}
				elseif($type=='ip'){
					$critsJSON[$formField][$key]['type']='IP';
				}
				elseif($type=='domain'){
					$critsJSON[$formField][$key]['type']='Domain';
				}
				
			}
			else{
				#build a new event
				#$type='event';
				#$desc='tauseragent';
				#$critsJSON[$type]=dataBuild($type, $new_file_name=NULL, $_POST, $_FILES, $critsPage, $critsLogin);
				
				$critsJSON[$formField][$key]=dataBuild($formType, $val, $_POST, $_FILES=NULL, $critsPage, $critsLogin, $relationArray=NULL, $desc);
				
				/*
				echo "<pre>";
				echo "critsJSON at $key: <br/>";
				print_r($critsJSON);
				echo "formField: $formField <br/>";
				echo "val: $val <br/>";
				echo "desc: $desc <br/>";
				echo "</pre>";
				*/
				
			}
		
			
		}#end foreach loop
		
	}
	else{
		#echo "critsUpdateData[$formField] is NOT set: ";
		#print_r($critsUpdateData);
		#echo "<br/><br/>";
	}
	return $critsJSON;
}



?>
