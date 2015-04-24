<html>
	<head>
		<div id="header">
			<title>Submit Files to WIPSTER and CRITs</title>
			<LINK href="./css/upload2.css" rel="stylesheet" type="text/css">
			<h1>Submit Files to WIPSTER and CRITs</h1>
			<a href="./mastiffResults.php">WIPSTER Results Dashboard</a> | <a href="./search.php">Search</a>
		</div>
	</head>
	<body>
		<div id="container">
			<div id="forms">
				<div id="mastiff">

					<form action="crits-upload.php" method="post" enctype="multipart/form-data">

						<div id="required">
							<div id="reqHead">
								REQUIRED Fields
							</div>

							<br/>
							Ticket #: 
							<input id="ticket" type="text" name="ticket" pattern="\d{1,12}" size="25" required />
							<br/><br/>
							<b>Sample for WIPSTER analysis: </b><input type="file" name="malware" size="25" />
							<br/><br/>
							<b>Note:</b> .xlsx and .docx files may take several minutes to complete,<br/>
							as they are treated by MASTIFF as .zip files.
							<br/><br/>
							
							
							
							<input id="taSubChk" type="checkbox" name="taSubChk" value="true" /><b>Submit to ThreatAnalyzer</b>
							<br/>
							<input id="malwrSubChk" type="checkbox" name="malwrSubChk" value="true" /><b>Submit to Malwr.com</b>
							<br/>
						</div>
						<br/>
						<center><b>The following will be related to this ticket in <a href="https://192.168.1.131/" target="_blank">CRITs</a></b></center><br/>
						<b>PCAP: </b><input type="file" name="pcap" size="25" />
						<br/><br/>
						<b>Email: </b><input type="file" name="email" size="25" />
						<br/><br/>
						<b>Domain: </b><input type="text" name="domain" size="25" />
						<br/><br/>
						<b>IP: </b><input type="text" name="ip" size="25" />
						<br/><br/>
						<!--<b>Campaign: </b><input type="text" name="campaign" size="25" />
						<br/>-->
						Notes:
						<br/>
						<textarea name="notes" cols="25" rows="5" style="width:700px; height:150px;"></textarea>
						<br/>
						<input type="submit" name="submit" value="Submit" />

					</form>
				
				</div>
			
			</div>
			
		</div>


	</body>

<?php
#Import configs
require './func/config.php';
#If posting data to the page, make sure there's actual data posted
if ($_SERVER['REQUEST_METHOD']=='POST'){
	#Check to make sure at least one of the required fields is populated
	if (empty($_FILES['malware']['name']) && empty($_FILES['pcap']['name']) && empty($_FILES['email']['name']) && empty($_POST['domain']) && empty($_POST['ip']) && empty($_POST['campaign'])){
		echo "<center><h2>At least one field is required!</h2></center>";
	}
	else{
		
		
		require './func/crits.php';
		
		
		/*
		echo "<pre>";		#DEBUG
		print_r($_FILES);	#DEBUG
		print_r($_POST);	#DEBUG
		echo "</pre>";		#DEBUG
		*/
		
		########################
		#### FORM VALIDATION
		########################

		foreach ($_FILES as $key => $val){
			#echo "<b>KEY: $key</b><br/>";	#DEBUG
			#echo "<b>VAL: $val</b><br/><br/>";	#DEBUG
			if(!empty($val['name']) && !$val['error']){
				
				#Clean up the filename
				$new_file_name = strtolower($val['name']);
				$new_file_name = preg_replace('/[^A-Za-z0-9-.]/','',$new_file_name);
				
				#Get the file extension
				$fileExt = pathinfo($new_file_name, PATHINFO_EXTENSION);
				$fileExt = strtolower($fileExt);
				
				#Form validation
				
				
				if($key=="malware"){
					if(in_array($fileExt, array("eml", "msg", "pcap"))){
						echo "<h2>ERROR: Emails and PCAPs cannot be submitted through the Sample field.</h2>";
						die();
					}
				}
				
				if($key=="pcap"){
					if(in_array($fileExt, array("pcap"))){
						#do nothing
					}
					else{
						echo "<h2>ERROR: Invalid pcap extension: $fileExt</h2>";
						die();
					}
				}
					
				if($key=="email"){
					if(in_array($fileExt, array("eml", "msg"))){
						#do nothing
					}
					else{
						echo "<h2>ERROR: Invalid email extension: $fileExt</h2>";
						die();
					}
				}
				
			}
		}
		
		/*
		echo "<pre>";		#DEBUG
		print_r($_FILES);	#DEBUG
		print_r($_POST);	#DEBUG
		echo "</pre>";		#DEBUG
		*/
		
		
		####################################
		#### Process each file uploaded
		####################################
		
		#echo "Going through _FILES<br/>"; #DEBUG
		foreach ($_FILES as $key => $val){
			#echo "<b>KEY: $key</b><br/>";	#DEBUG
			#echo "<b>VAL: $val</b><br/><br/>";	#DEBUG
			if(!empty($val['name']) && !$val['error']){
				
				#Clean up the filename
				$new_file_name = strtolower($val['name']);
				$new_file_name = preg_replace('/[^A-Za-z0-9-.]/','',$new_file_name);
				
				if($val['size'] < 25600000){	#File cannot be larger than 25 MB
					if(!copy($val['tmp_name'], '/var/www/upload/malware/'.$new_file_name)){	#Copy the temp file to a location with a proper name
						echo "<h2>ERROR: File $key[tmp_name] failed to copy to /var/www/upload/malware/</h2><br/><br/>";
					}
					else{
						#Set the proper file types for each field
						if($key=="malware"){
							$type='sample';
						}
						elseif($key=="pcap"){
							$type='pcap';
						}
						elseif($key=="email"){
							$type='email';
						}
						
						if(!isset($critsJSON)){
							$critsJSON=array();
						}
						#Build Data for Sample
						if(!empty($type)&&!empty($new_file_name)){
							$critsJSON[$type]=dataBuild($type, $new_file_name, $_POST, $_FILES, $critsPage, $critsLogin);
							unset($type);
						}
						elseif(!empty($type)){
							echo "<h2>ERROR: No new_file_name set for type: $type</h2>";
						}
						else{
							echo "<h2>ERROR: No TLO type set!</h2>";
						}
					}
				}
				else{
					echo "<h2>ERROR: File too large. Cannot be larger than 25 MB. $new_file_name is $val[size] bytes.</h2><br/><br/>";
				}
				
				
				
				if(isset($new_file_name)){
					unset($new_file_name);
				}
			}
		}
		
		#Process & upload text data
		#echo "Going through POST<br/>"; #DEBUG
		foreach($_POST as $key=>$val){
			/*
			echo "<b>KEY: $key</b><br/>";	#DEBUG
			echo "<b>VAL: $val</b><br/><br/>";	#DEBUG
			*/
			
			if(!empty($val)){
				
				if($key=='submit' || $key=='ticket'){
				continue;
				}
				
				if($key=='domain' && !empty($val)){
					$type='domain';
				}
				if($key=='ip' && !empty($val)){
					$type='ip';
				}
				#Work out the campaign stuff later
				#if($key=='campaign' && !empty($val)){
				#	$type='campaign';
				#}
				if($key=='notes' && !empty($val)){
					$type='notes';
				}
				#Check if the main JSON array already exists
				if(!isset($critsJSON)){
					$critsJSON=array();
				}
				#Build Data for Sample
				if(!empty($type) && !empty($val)){
					$critsJSON[$type]=dataBuild($type, $new_file_name=NULL, $_POST, $_FILES, $critsPage, $critsLogin);
				}
				elseif(!empty($type)){
					echo "<h2>ERROR: No TLO type set</h2>";#DEBUG
				}
				
			}
			
			
		}
		
		##### Create new Event if one does not already exist
		#Check if an event already exists matching the ticket number
		#if the ticket number matches, add that id/data to $critsJSON['event']['id'] and $critsJSON['event']['type']
		#if the ticket number does not match, build the data to create a new event and POST to the events API
		
		if(isset($_POST['ticket'])){
			#Search crits for an existing event matching this ticket #
			$eventSearch = searchCRITs($type='event', $_POST['ticket'], $critsPage, $critsLogin);
			$eventJSON = json_decode($eventSearch[0],true);
			#If the event already exists, just set the critsJSON tags and move on. Otherwise, add the event.
			if(isset($eventJSON['objects'][0]['_id'])){
				$critsJSON['event']['id']=$eventJSON['objects'][0]['_id'];
				$critsJSON['event']['type']='Event';
			}
			else{
				#build a new event
				$type='event';
				$critsJSON[$type]=dataBuild($type, $new_file_name=NULL, $_POST, $_FILES, $critsPage, $critsLogin);
			}
		}
		
		
		
		
		
		
		
		
		##### RELATE ALL UPLOADED CONTENT TO EACH OTHER
		
		$relationArray=array();
		$type='relationship';
		
		#handling the fact that email uploading doesn't work properly
		if(isset($critsJSON['email']['error_message'])){
			#Look for a particular error message, and grab the TLO's id# if it's there
			$pattern="/^ObjectId\(\'(\w*)\'\)/";
			preg_match($pattern, $critsJSON['email']['error_message'], $matches);
			/*
			echo "<pre>";	#DEBUG
			print_r($matches);	#DEBUG
			echo "</pre>";	#DEBUG
			#die();	#DEBUG
			*/
			if(isset($matches[1])){
				#if a match is found, properly set the id and type properties
				$critsJSON['email']['id']=$matches[1];
				$critsJSON['email']['type']='Email';
			}
			#if($critsJSON['email']['error_message']){
			#}
		}
		
		foreach($critsJSON as $key=>$val){
			if(isset($val['id'])){
				/*
				echo "<br/>critsJSON:</br>"; #DEBUG
				echo "<pre>";	#DEBUG
				print_r($critsJSON);	#DEBUG
				echo "</pre>";	#DEBUG
				*/
				$relationArray['TLO1type']=$val['type'];
				$relationArray['TLO1id']=$val['id'];
				foreach($critsJSON as $key2=>$val2){
					if($key2!=$key){
						$relationArray['TLO2type']=$val2['type'];
						$relationArray['TLO2id']=$val2['id'];
						if(isset($val2['id'])){
							$relationJSON[$key][$key2]=dataBuild($type,$new_file_name=NULL,$_POST,$_FILES,$critsPage,$critsLogin,$relationArray);
						}
						
					}
					
				}
				unset($critsJSON[$key]);
				
			}
			
		}
		
				
		
		
		#set all the CRITs variables to prep them for relation ($sampleCRITs, $ipCRITs, $domainCRITs, $pcapCRITs, etc...)
			#if $sampleCRITs is not already set, that means it wasn't found in the db -- 
				#upload it to CRITs, and save the response (id, type, msg, url, etc) as an array in $sampleCRITs
				#likewise for all the other fields submitted
		
		#Once all the $xxxCRITs variables are populated above, and the ticket #'s have all been updated, relate all the objects to eachother
			# relationships.relationship:"Related_to"
			# relationships.rel_reason:"Related via ticket XXXX"
		
		#Upload files that aren't in CRITs to the CRITs DB
		
		#Relate all files (new or old)
			
		
		
		
		#######################################################
		##### Handle the WIPSTER/MASTIFF analysis of the sample
		#######################################################
		
		include './accept-file.php';
	}

}

###############	
##### DEBUG
###############

/*echo "<pre>";
print_r(get_defined_vars());
echo "</pre>";*/


?>
</html>
