<?

##### VARS

#Set filename if submitted from accept-file.php upload page
if(isset($new_file_name)){
	$malwrFile='@/var/www/upload/malware/'.$new_file_name;
}

#Options set for POSTing
$callMalwrOpts = array(
	'command'=>'add/',	#Other option: status/
	'api'=>$malwrAPI,
	'page'=>'https://malwr.com/api/analysis/',
	'post'=>'1',	#Comment out this line for GET requests
	'postArgs'=>array(
		'shared'=>'no',
		'api_key'=>$malwrAPI
	)
);

##### FUNCTIONS

#cURL wrapper - upload
function callMalwr($callMalwrOpts,$malwrFile){
	#echo '<p>Command: '.$command.'</p>';
	$target=$callMalwrOpts['page'].$callMalwrOpts['command']."?api_key=".$callMalwrOpts['api'];#."&".$callMalwrOpts[];
	#echo '<p>URL sent: '.$target.'</p>';
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $target);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);

	if(isset($callMalwrOpts['post'])){
		$callMalwrOpts['postArgs']['file']=$malwrFile;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $callMalwrOpts['postArgs']);
	}
	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$results=array($result,$httpCode);
	return $results;
}

#Add results to DB
function malwrDBAdd($malwrDBArray){
	/*
	$db = new SQLite3('./mastiff/mastiff.db');

	#Get all records from DB for this file with no ticket#
	$result = $db->query('SELECT * FROM files WHERE filename LIKE "%'.$md5hash.'%" OR ticket = "" OR filename = "/var/www/upload/malware/'.$new_file_name.'"');
	
	#insert (from autopb.php)
	$insert = "'".$val['date']."', '".$val['link']."', '".$val['content']."'";
	$db->exec('INSERT INTO results (date,link,content) VALUES ('.$insert.')');
	*/
	
	$db = new SQLite3('./malwr/malwr.db');
	$insert="'".$malwrDBArray['status']."', '".$malwrDBArray['sha256']."', '".$malwrDBArray['md5']."', '".$malwrDBArray['uuid']."'";
	$result = $db->exec('INSERT INTO malwr (status, sha256, md5, uuid) VALUES ('.$insert.') ');
	if(!$result){
		$malwrRes['db']=$db->lastErrorMsg();
	}
	else{
		$malwrRes['db']=$db->changes().' Record updated successfully.';
	}
	$db->close();
	return $malwrRes;
	
}

#Check DB for Existing Results
function malwrDBCheck($idmd5){
	$db = new SQLite3('./malwr/malwr.db');
	$result = $db->query('SELECT * FROM malwr WHERE md5 = "'.$idmd5.'"');
	
	if(isset($result))
	{
		while ($res=$result->fetchArray()){
			#$_SESSION['size']=$res['size'];
			$malwrRes['uuid']=$res['uuid'];			
		}
	}
	if(!$result){
		$malwrRes['db']=$db->lastErrorMsg();
	}
	else{
		$malwrRes['db']=$db->changes().' Record updated successfully.';
	}
	$db->close();
	return $malwrRes;
}

#cURL wrapper - check status
function chkMalwr($callMalwrOpts){
	#echo '<p>Command: '.$command.'</p>';
	$target=$callMalwrOpts['page'].$callMalwrOpts['command']."?api_key=".$callMalwrOpts['api'];#."&".$callMalwrOpts[];
	#echo '<p>URL sent: '.$target.'</p>';
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $target);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);

	if(isset($callMalwrOpts['post'])){
		$callMalwrOpts['postArgs']['file']=$malwrFile;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $callMalwrOpts['postArgs']);
	}
	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$results=array($result,$httpCode);
	return $results;
}

#Check Malwr Analysis Status
function malwrStatus($malwrAPI,$malwrUUID){
	#Options set for POSTing
	$callMalwrOpts = array(
		'command'=>'status/',	#Other option: status/
		'api'=>$malwrAPI,
		'page'=>'https://malwr.com/api/analysis/',
		'post'=>'1',	#Comment out this line for GET requests
		'postArgs'=>array(
			'shared'=>'no',
			'api_key'=>$malwrAPI,
			'uuid'=>$malwrUUID
		)
	);
	
	#malwrChk
	$chkMalwrStatus = chkMalwr($callMalwrOpts);
	return $chkMalwrStatus;	#[0]=results [1]=code
}

#############################################
//Process Malwr Upload
//if Malwr Checkbox ticked on upload page
#############################################
if(isset($_POST['malwrSubChk'])){

	$response=callMalwr($callMalwrOpts,$malwrFile);
	$malwrSubResp = json_decode($response[0], true);
	if($response[1]=='200'){
		$message = 'Congratulations!  Your file was accepted and successfully submitted to Malwr.com.<br/><br/><b>RESPONSE: </b>'.$response[0];
		$malwrDBArray=array(
			'status'=>$malwrSubResp['status'],
			'sha256'=>$malwrSubResp['sha256'],
			'md5'=>$idmd5,
			'uuid'=>$malwrSubResp['uuid']
		);
		#Log results in DB
		$malDBResp = malwrDBAdd($malwrDBArray);
		#echo "<pre>";
		#echo "malDBResp:";
		#var_dump($malDBResp);
		#echo "</pre>";
	}
	else{
		$message = 'Your file was accepted, but we were unable to successfully submit it to Malwr.com.<br/><br/><b>ERROR: </b>'.$response[0];
	}
	
}


#############################################
//Process Malwr Upload
//if initiated from MD5 Page
#############################################

#Prep file if submitted from md5 page
if(isset($_GET['malsub'])){
		
		# Copy file to temporary directory
		$malwrCommand[]='cp /var/www/mastiff/'.$idmd5.'/'.$fileArrays['vir'][0].' /var/www/upload/malware/';
		#Rename the file
		$malwrFile=substr($fileArrays['vir'][0],0,-4);	#Strip .VIR (4 chars)
		$malwrCommand[]='mv /var/www/upload/malware/'.$fileArrays['vir'][0].' /var/www/upload/malware/'.$malwrFile;
		#Run the above commands
		foreach($malwrCommand as $key=>$val){
			shell_exec($val);
		}
		# Set full path of file upload
		$malwrFile='@/var/www/upload/malware/'.$malwrFile;
		
		$malwrSubmission=callMalwr($callMalwrOpts,$malwrFile);
		$malwrSubResp = json_decode($malwrSubmission[0], true);
		#echo '<pre>';
		#echo '$malwrSubmission: ';
		#var_dump($malwrSubmission);
		#echo '$malwrSubResp: ';
		#var_dump($malwrSubResp);
		#echo '</pre>';
		
		if($malwrSubmission[1]=='200'){
			#Log results in DB
			$malwrDBArray=array(
				'status'=>$malwrSubResp['status'],
				'sha256'=>$malwrSubResp['sha256'],
				'md5'=>$idmd5,
				'uuid'=>$malwrSubResp['uuid']
			);
			
			$malDBResp = malwrDBAdd($malwrDBArray);
			#echo "<pre>";
			#echo "malDBResp:";
			#var_dump($malDBResp);
			#echo "</pre>";
		}
		elseif($malwrSubmission[1]=='400'){
			
		}
				
		shell_exec('rm -r /var/www/upload/malware/*');	#Clean up  uploaded files
		
		
		
}

?>
