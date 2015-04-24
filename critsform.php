<!--<LINK href="./css/critsform.css" rel="stylesheet" type="text/css">-->
<div id="critsContent">

	<form action="md5page2.php<?php echo "?idmd5=$_GET[idmd5]";?>" method="post" enctype="multipart/form-data">
		<div id="critsContainer">
		<h2><center>The following information has been extracted from this analysis and may be uploaded to CRITs</center></h2>
		<?php
			
			require('./func/crits.php');
			
			######################################################################################
			#### Get all the info from this analysis and build the form based on that info
			######################################################################################
			
			
			if(isset($server) && !empty($server)){
				echo "<div id='txCritsInfo'>";
				echo "<input type='checkbox' name='txdomainchk$key' checked/><label>ThreatExpert Domain Callout: </label><input type='text' name='txdomain' size='25' value='$server' class='text'/><br/>";
				echo "</div>";
			}
			
			if($threatAnalyzerPlugin===True){
				echo "<div id='taCritsInfo'>";
				# TLO - Domain (From ThreatAnalyzer (if TA enabled) and ThreatExpert)
				if(isset($dnsCallouts[0])){
					foreach($dnsCallouts as $key=>$val){
						if(!empty($val) && $val!='wpad'){
							$pattern = '/'.implode("|",$not_domain).'/';
							unset($matches);
							preg_match($pattern, $val, $matches);
							if(!empty($matches[0])){
								echo "<input type='checkbox' name='domainchk$key' unchecked/><label>ThreatAnalyzer Domain Callout: </label><input type='text' name='domain$key' size='25' value='$val' class='text'/><br/>";
								
							}
							else{
								echo "<input type='checkbox' name='domainchk$key' checked/><label>ThreatAnalyzer Domain Callout: </label><input type='text' name='domain$key' size='25' value='$val' class='text'/><br/>";
								
							}
							
							
						}
						
					}
				}
				
				
				
				# TLO - IP (From ThreatAnalyzer (if TA enabled) and ThreatExpert)
				if(isset($ipCallouts[0])){
					foreach($ipCallouts as $key=>$val){
						if(!empty($val)){
							$pattern = '/'.implode("|",$not_ip).'/';
							unset($matches);
							preg_match($pattern, $val, $matches);
							if(!empty($matches[0])){
								echo "<input type='checkbox' name='ipchk$key' unchecked/><label>ThreatAnalyzer IP Callout: </label><input type='text' name='ip$key' size='25' value='$val' class='text'/><br/>";
								
							}
							else{
								echo "<input type='checkbox' name='ipchk$key' checked/><label>ThreatAnalyzer IP Callout: </label><input type='text' name='ip$key' size='25' value='$val' class='text'/><br/>";
								
							}
							
						}
					}
				}
				
				
				
				# TLO - Event - "Indicators - Network Activity" - Full GET/POST request (From ThreatAnalyzer)
				if(isset($critsCon['uri'][0])){
					foreach($critsCon['uri'] as $key=>$val){
						if(!empty($val)){
							$val=str_replace('Host: ','',$val);
							$pattern = '/'.implode("|",$not_uri).'/';
							unset($matches);
							preg_match($pattern, $val, $matches);
							if(!empty($matches[0])){
								echo "<input type='checkbox' name='urichk$key' unchecked/><label>ThreatAnalyzer URI Callouts: </label><input type='text' name='uri$key' size='25' value='$val' class='text'/><br/>";
								
							}
							else{
								echo "<input type='checkbox' name='urichk$key' checked/><label>ThreatAnalyzer URI Callouts: </label><input type='text' name='uri$key' size='25' value='$val' class='text'/><br/>";
								
							}
							
						}
						
					}
				}
				
				
				/*
				echo "<pre>";
				print_r($critsCon);
				echo "</pre>";
				*/
				
				# TLO - Event - "Indicators - Network Activity" - User-Agent (From ThreatAnalyzer)
				if(isset($critsCon['useragent'][0])){
					foreach($critsCon['useragent'] as $key=>$val){
						if(!empty($val)){
							$val=str_replace('User-Agent: ','',$val);
							$pattern = '/'.implode("|",$not_useragent).'/';
							#echo "Pattern: $pattern <br/>";
							unset($matches);
							preg_match_all($pattern, $val, $matches);
							#echo "<b><i>MATCHES:</i></b>";
							#echo "<pre>";
							#print_r($matches);
							#echo "</pre>";
							if(!empty($matches[0])){
								echo "<input type='checkbox' name='useragentchk$key' unchecked/><label>ThreatAnalyzer User-Agent: </label><input type='text' name='useragent$key' size='25' value='$val' class='text'/><br/>";
							}
							else{
								echo "<input type='checkbox' name='useragentchk$key' checked/><label>ThreatAnalyzer User-Agent: </label><input type='text' name='useragent$key' size='25' value='$val' class='text'/><br/>";
							}
							
						}
						
					}
				}
				
				
				echo "</div><!--end taCritsInfo-->";
			}
			
			echo "<div id='genCritsInfo'>";
			
				echo "<input type='checkbox' name='domainchk' unchecked/><label>Domain Callout: </label><input type='text' name='domain' size='25' value='' class='text'/><br/>";
				echo "<input type='checkbox' name='ipchk' unchecked/><label>IP Callout: </label><input type='text' name='ip' size='25' value='' class='text'/><br/>";
				echo "<input type='checkbox' name='urichk' unchecked/><label>URI Callout: </label><input type='text' name='uri' size='25' value='' class='text'/><br/>";
				echo "<input type='checkbox' name='useragentchk' unchecked/><label>User-Agent: </label><input type='text' name='useragent' size='25' value='' class='text'/><br/>";
			
			echo "</div><!--end genCritsInfo-->";
			
			
			echo "<div id='vtCritsInfo'>";
			# TLO - Event - "Malware Samples" - (if VT results) simplified SEP and MSE definitions
			if(isset($virusTotal['sep'])){
				if(!empty($virusTotal['sep'])){
					$virusTotal['sep']=rtrim($virusTotal['sep']);
					#echo "strlen ";
					#echo strlen($virusTotal['sep']);
					#echo "strrpos of space ";
					#echo strrpos($virusTotal['sep'], ' ');
					#echo -(strlen($virusTotal['sep'])-strrpos($virusTotal['sep'], ' '));
					$val=substr($virusTotal['sep'], (-(strlen($virusTotal['sep'])-strrpos($virusTotal['sep'], ' '))+1));
							echo "<input type='checkbox' name='vtsepchk' checked/><label>SEP Detected: </label><input type='text' name='vtsep' size='25' value='SEP: $val' class='text'/><br/>";
				}
			}
			
			if(isset($virusTotal['mse'])){
				if(!empty($virusTotal['mse'])){
					$virusTotal['mse']=rtrim($virusTotal['mse']);
					#echo -(strlen($virusTotal['mse'])-strrpos($virusTotal['mse'], ' '));
					$val=substr($virusTotal['mse'], (-(strlen($virusTotal['mse'])-strrpos($virusTotal['mse'], ' '))+1));
							echo "<input type='checkbox' name='vtmsechk' checked/><label>MSE Detected: </label><input type='text' name='vtmse' size='25' value='MSE: $val' class='text'/><br/>";
				}
			}
			echo "</div>";
			# TLO - PCAP - (from ThreatAnalyzer if enabled) - optional - try to only get one
			
			# Section for adding additional inputs for Domain, IP, User-Agent, GET/POST requests, Malware Type, PCAP, EML
			
			# Get Ticket # (pulled from existing variables)
			$latestTicket = $relatedTix[0]['ticket'];
			echo "<div id='ticketCritsInfo'>";
				echo "<input type='checkbox' name='ticketchk' checked/><label>Ticket#: </label><input type='text' name='ticket' size='25' value='$latestTicket' class='text'/><br/>";
			echo "</div><!--end ticketCritsInfo-->";
			
			# Search CRITs for the MD5 being examined to get the ObjectID If no ObjectID is found, upload sample w/ other data
			

			
		?>
		<input id="submit" type="submit" name="submit" value="Submit" />
	</div><!--end critsContainer-->
	</form>
	
	


</div><!--end critsContent-->

<?

#############################################
#### If POST, submit the data
#############################################

# if the upload checkboxes are checked for each field, and the fields have content, submit

if ($_SERVER['REQUEST_METHOD']=='POST'){

	$allPostKeys = implode(',',array_keys($_POST));
	$critsUpdateData = array();
	
	
	#### Cycle through ThreatAnalyzer Inputs
	
	#check the useragents
		#$critsChkbox = 'useragentchk';
		#$critsInput = 'useragent';
		#$critsInfoSource = 'ta';
	$critsUpdateData = critsChkFields('useragentchk','useragent',$critsUpdateData,$allPostKeys);
	
	#check the domains
	$critsUpdateData = critsChkFields('domainchk','domain',$critsUpdateData,$allPostKeys);
	
	#check the IPs
	$critsUpdateData = critsChkFields('ipchk','ip',$critsUpdateData,$allPostKeys);
	
	#check the URIs
	$critsUpdateData = critsChkFields('urichk','uri',$critsUpdateData,$allPostKeys);
	
	#### Cycle through VT
	$critsUpdateData = critsChkFields('vtsepchk','vtsep',$critsUpdateData,$allPostKeys);
	$critsUpdateData = critsChkFields('vtmsechk','vtmse',$critsUpdateData,$allPostKeys);
	
	
	
	#########################################
	#### Build POST data and Upload to CRITs
	#########################################
	
	if(!empty($_POST['ticket']) && !empty($critsUpdateData)){
		
		
		$critsJSON = metaDataBuild('tauseragent', 'useragent', 'event', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON, $desc='tauseragent');
		$critsJSON = metaDataBuild('tadomain', 'domain', 'domain', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON, $desc='tadomain');
		$critsJSON = metaDataBuild('taip', 'ip', 'ip', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON, $desc='taip');
		$critsJSON = metaDataBuild('tauri', 'uri', 'event', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON, $desc='tauri');
		$critsJSON = metaDataBuild('genuseragent', 'useragent', 'event', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON);
		$critsJSON = metaDataBuild('gendomain', 'domain', 'domain', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON);
		$critsJSON = metaDataBuild('genip', 'ip', 'ip', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON);
		$critsJSON = metaDataBuild('genuri', 'uri', 'event', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON);
		$critsJSON = metaDataBuild('genvtsep', 'vt', 'event', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON, $desc='sep');
		$critsJSON = metaDataBuild('genvtmse', 'vt', 'event', $critsUpdateData, $critsPage, $critsLogin, $_POST, $critsJSON, $desc='mse');
		

		
		##########################################################
		#### Check if the sample and ticket are already in CRITs. 
		#### If not, upload/create them.
		##########################################################
		
		#Search crits for an existing event matching this ticket #
		$eventSearch = searchCRITs($type='event', $_POST['ticket'], $critsPage, $critsLogin);
		$eventJSON = json_decode($eventSearch[0],true);
		#If the event (ticket) already exists, just set the critsJSON tags and move on. Otherwise, add the event.
		if(isset($eventJSON['objects'][0]['_id'])){
			$critsJSON['event'][0]['id']=$eventJSON['objects'][0]['_id'];
			$critsJSON['event'][0]['type']='Event';
		}
		else{
			#build a new event (ticket)
			$type='event';
			$critsJSON[$type][0]=dataBuild($type, $new_file_name=NULL, $_POST, $_FILES=NULL, $critsPage, $critsLogin);
		}
		
		
		
		#### Work in Progress - Adding Samples if not found
		
		#Search crits for an existing sample matching this MD5
		$eventSearch = searchCRITs($type='sample', $_GET['idmd5'], $critsPage, $critsLogin);
		$eventJSON = json_decode($eventSearch[0],true);
		#If the sample already exists, just set the critsJSON tags and move on. Otherwise, add it.
		if(isset($eventJSON['objects'][0]['_id'])){
			$critsJSON['sample'][0]['id']=$eventJSON['objects'][0]['_id'];
			$critsJSON['sample'][0]['type']='Sample';
		}
		else{
			#build a new event
			echo "<pre>";
			print_r($fileArrays['vir'][0]);
			echo "</pre>";
			$old_file_name=$fileArrays['vir'][0];
			
			$type='sampleRetro';
			$new_file_name=substr($fileArrays['vir'][0], 0, -4);
			$upload_path='/var/www/upload/malware/'.$new_file_name;
			if(!copy("/var/www/mastiff/$_GET[idmd5]/$old_file_name", $upload_path)){	#Copy the temp file to a location with a proper name
				echo "<h2>ERROR: File /var/www/mastiff/$_GET[idmd5]/$old_file_name failed to copy to $upload_path</h2><br/><br/>";
			}
			else{
				#Set the sample 
				$critsJSON[$type][0]=dataBuild($type, $new_file_name, $_POST, $_FILES=NULL, $critsPage, $critsLogin, $md5=$_GET['idmd5']);
				unlink($upload_path);
			}
			
			
			
			
		}
		
		
		
		
		
	}
	
	#####################################################
	##### Relate New Uploads to same ticket in CRITs
	#####################################################
	
	$relationArray=array();
	$type='relationship';
	
	
	foreach($critsJSON as $formField=>$arrayVal){
		foreach($arrayVal as $key=>$val){
			$compare1=$formField.$key;
			$relationArray['TLO1type']=$val['type'];
			$relationArray['TLO1id']=$val['id'];
			foreach($critsJSON as $formField2=>$arrayVal2){
				foreach($arrayVal2 as $key2=>$val2){
					$compare2=$formField2.$key2;
					if($compare2!=$compare1){
						$relationArray['TLO2type']=$val2['type'];
						$relationArray['TLO2id']=$val2['id'];
						if(isset($val2['id'])){
							echo "Setting relationship: ";
							echo "<pre>";
							print_r($relationArray);
							echo "</pre>";
							$relationJSON[$formField][$key][$formField2][$key2]=dataBuild($type,$new_file_name=NULL,$_POST,$_FILES,$critsPage,$critsLogin,$relationArray);
						}
						else{
							echo "<h2>val2[id] is not set for $formField2 -> $key2</h2><pre>";
							print_r($key2);
							echo "</pre>";
							
						}
					}
					else{
						echo "<h2>apparently, $compare2==$compare1</h2>";
					}
				}
			}
			/*
			echo "before unsetting critsJSON[formField][key]: ";
			echo "<pre>";
			print_r($critsJSON);
			echo "</pre>";
			
			unset($critsJSON[$formField][$key]);
			
			echo "after unsetting critsJSON[formField][key]: ";
			echo "<pre>";
			print_r($critsJSON);
			echo "</pre>";
			*/
		}
		echo "before unsetting critsJSON[formField]: ";
		echo "<pre>";
		print_r($critsJSON);
		echo "</pre>";
		
		unset($critsJSON[$formField]);
		
		echo "after unsetting critsJSON[formField]: ";
		echo "<pre>";
		print_r($critsJSON);
		echo "</pre>";
	}
	
	
	
}#end if POST

#DEBUG

echo "relationJSON: ";
echo "<pre>";
print_r($relationJSON);
echo "</pre>";


echo "<pre>";
print_r($_POST);
echo "critsUpdateData: ";
print_r($critsUpdateData);
print_r($relatedTix);
echo $relatedTix[0]['ticket'];
#print_r($critsKeys);
#print_r($critsVals);
echo "$allPostKeys";
echo "</pre>";

?>
