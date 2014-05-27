<?php

###########################
/*
 * SET VARIABLES
 */
###########################
 
session_start();

#error_reporting(E_ALL); ini_set('display_errors',1);
require('./func/config.php');

 
#Get MD5 Variable from URL
if(isset($_GET['idmd5'])){
	$idmd5=$_GET['idmd5'];
	
	$db = new SQLite3('./mastiff/mastiff.db');
	
	$result = $db->query('SELECT * FROM mastiff WHERE md5 = "'.$idmd5.'"');
	if(isset($result))
	{
		while ($res=$result->fetchArray()){
			#$_SESSION['size']=$res['size'];
			$_SESSION['type']=$res['type'];
			$_SESSION['sha1']=$res['sha1'];
			$_SESSION['sha256']=$res['sha256'];
			$_SESSION['fuzzy']=$res['fuzzy'];
		}
	}
}




###########################
/*
 * DEFINE FUNCTIONS
 */
###########################

require './func/mastiffFunctions.php';

###########################
/*
 * RUN FUNCTIONS
 */
###########################

$anubis = anubisSubmit($idmd5);
$fileArrays = getFiles($idmd5);
$relatedTix = relatedTix($idmd5);
$virusTotal = virusTotal($idmd5);
$zipContents = zipContents($idmd5);
$pcapFormat = pcapFormat($idmd5, $fileArrays);

$_SESSION['txtdump'] = isset($fileArrays['txt']) ? $fileArrays['txt'] : FALSE;
$_SESSION['virusTotal'] = (($virusTotal!=FALSE) && (!isset($virusTotal['submit']))) ? $virusTotal : FALSE;

#echo 'relatedTix: '.$relatedTix[-1]['ticket'];
$lastTicket=end($relatedTix);
$_SESSION['ticket'] = isset($lastTicket['ticket']) ? $lastTicket['ticket'] : FALSE;

$_SESSION['md5'] = isset($idmd5) ? $idmd5 : FALSE;
$_SESSION['filename'] = isset($fileArrays['vir'][0]) ? $fileArrays['vir'][0] : FALSE;
$_SESSION['link'] = isset($url) ? $url : FALSE;

#$_SESSION['path'] = isset($idmd5) ? $idmd5 : FALSE;
#$_SESSION['cve'] = isset($idmd5) ? $idmd5 : FALSE;

$_SESSION['anubis'] = isset($anubis) ? $anubis : FALSE;

#Submit to ThreatAnalyzer

#cURL wrapper
function callTA($command,$threatAPI,$threatPage,$threatArgs,$tapost,$postArray){
	#echo '<p>Command: '.$command.'</p>';
	$target=$threatPage.$command."?api_token=".$threatAPI."&".$threatArgs;
	#echo '<p>URL sent: '.$target.'</p>';
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $target);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);

	if(isset($tapost)){
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postArray);
	}
	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$results=array($result,$httpCode);
	return $results;
}

if($threatAnalyzerPlugin===True){
	
	#Check for submission argument
	if(isset($_GET['tasub'])){
		
		# Copy file to temporary directory
		$taCommand[]='cp /var/www/mastiff/'.$idmd5.'/'.$fileArrays['vir'][0].' /var/www/upload/malware/';
		#Rename the file
		$taFile=substr($fileArrays['vir'][0],0,-4);	#Strip .VIR (4 chars)
		$taCommand[]='mv /var/www/upload/malware/'.$fileArrays['vir'][0].' /var/www/upload/malware/'.$taFile;
		#Run the above commands
		foreach($taCommand as $key=>$val){
			shell_exec($val);
		}
		# Set full path of file upload
		$taFile='@/var/www/upload/malware/'.$taFile;
		
		
		$command='/submissions';
		$threatArgs='';
		
		
		#most vars set in config.php
		$postArray=array(
			'submission[file]'=>$taFile,
			'submission[priority]'=>$taSubPriority,
			'submission[sandbox][group_option]'=>'custom',
			'submission[sandbox][custom_sandbox][]'=>$taSubSandbox,
			'submission[submission_type]'=>'file',
			'submission[reanalyze]'=>$taSubReanalyze
		);
		
		if (isset($taSubCustomName)){
			$custNamePost = 'custom_param['.$taSubCustomName.']';
			$postArray[$custNamePost]=$taSubCustomVal;
			}
		
		$submission=@callTA($command,$threatAPI,$threatPage,$threatArgs,1,$postArray);	#1 means POST
		$threatArgs='';
		
		shell_exec('rm -r /var/www/upload/malware/*');	#Clean up  uploaded files
		#echo '<p>HTTP Response: '.$response[1].'</p>';
		$submissionResp = json_decode($submission[0], true);
		#echo '<pre>';
		#var_dump($submission);
		#echo '</pre>';
	}
	
}


/*
echo '<pre>';
var_dump($fileArrays);
echo '</pre>';
*/

###########################
/*
 * BUILD PAGE
 */
###########################


###########################
### HEADERS
###########################

echo '<html>';

	echo '<head>';
	
		echo '<title>'.$idmd5.'</title>';
		echo '<LINK href="./css/md5style.css" rel="stylesheet" type="text/css">';
		echo '<script src="./scripts/jquery-1.11.0.min.js"></script>';
		
	echo '</head>';
	
	echo '<body>';

	echo '<div id="container">';

	echo '<div id="mainHead">';

		echo '<h1>MASTIFF results for MD5: '.$idmd5.'</h1>';
		echo '<br/>';
		echo '<a href="./mastiffResults.php">MASTIFF Results Dashboard</a> | ';
		echo '<a href="./search.php">Search</a> | ';
		echo '<a href="./upload2.html">Submit Files</a>';
		echo '<br/>';
		echo '<br/>';
		echo '<a href="./mastiff/'.$idmd5.'/">View Directory for '.$idmd5.'</a> ';
		echo '<div id="topbuttons">';
		if((isset($fileArrays['xor'])) && (empty($fileArrays['xor']))){
			echo '<br/>';
			echo '<div id="xorbutton"><a href="./xor.php">Run NoMoreXOR</a></div>';
		}
		
		##### ThreatAnalyzer Check
		
		
		
		if($threatAnalyzerPlugin==True){
			#Get all analyses
			$command='/analyses';
			$threatArgs='md5='.$idmd5;
			$response=@callTA($command,$threatAPI,$threatPage,$threatArgs);
			$threatArgs='';
			#echo '<p>HTTP Response: '.$response[1].'</p>';
			#$analyses = json_decode($response[0], true);
			#echo '<pre>';
			#var_dump($analyses);
			#echo '</pre>';
		}
		
		
		if( ($threatAnalyzerPlugin==True)&&($response[1]!='404') || (isset($_GET['tasub'])) ){
			#echo '<br/>';
			echo '<div id="taButton">ThreatAnalyzer Details</div>';
			echo '<div id="remButton">REMnux Details</div>';
		}
		elseif(($threatAnalyzerPlugin==True)&&($response[1]=='404')){
			#echo '<br/>';
			echo '<div id="taButtonSubmit"><a href="http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'&tasub=1">Send to ThreatAnalyzer</a></div>';
		}
		
#		echo '<br/>';
		echo '<div id="emailformat"><a href="./ticketgen.php" target="_blank">View Results in Plain Text</a></div>';
		echo '</div>';#End topbuttons div
		
		#Show status of file submission
		if( (isset($_GET['tasub'])) && ($threatAnalyzerPlugin==True) ){
			
			echo '<div id="subStatus">';
			if($submission[1]=='200'){
				
				$command='/submissions/'.$submission[1]['id'];
				$threatArgs='';
				$subResp=@callTA($command,$threatAPI,$threatPage,$threatArgs);
				$threatArgs='';
				$subDetails = json_decode($subResp[0], true);
				/*
				 * Example JSON Response (Not used right now, but could be useful later):
				 * submission
				 * 		id:31,
				 * 		state: "hash_worker_started",
				 * 		created_at:"time",
				 * 		updated_at:"time",
				 * 		samples:
				 * 			id
				 * 			md5
				 * 			sha1
				 * 			file_location
				 * 			sha256
				 * 			known_filenames:
				 * 				pic.jpg
				 * 			ssdeep
				 * 			last_analyzed_at
				 * 			is_file
				 * 			is_url
				 * 			analyses:
				 * 				analysis_id
				 * 				sandbox_mac_address
				 * 				etc.... Same as Analyses call from earlier
				 */
				
				echo '<div id="subSent">';
					echo '<b>File submission successful! Status: </b>'.$submissionResp['state'].' <b>Please wait for analysis to complete, then refresh this page.</b>';
				echo '</div>';
				
				#Consider JQuery to check status of analysis every 5 seconds, then replace subSent DIV with
				# this DIV, and refresh page when complete.
				echo '<div id="subDone">';	#Style:display=none
					#echo '<b>ANALYSIS COMPLETE! Refreshing Page.</b>';
				echo '</div>';
				
			}
			else{
				echo '<div id="subError"><b>ERROR: </b>'.$submission[1].'<br/>';
				foreach($submission[0] as $key=>$val){
					echo $key.': '.$val.'<br/>';
				}
				echo '</div>';
			}
				
			
			echo '</div>';#End subStatus
		}
		
		
		#echo ' | <a href="./xor.php">De-XOR</a>';
	
	echo '</div>';
	
	###########################
	### BODY
	###########################
	
	echo '<div id="mainContent">';
	
	if(($threatAnalyzerPlugin===True)&&($response[1]!='404')){
		echo '<div id="taPage">';
		include('./threatanalyzer.php');
		echo "</div><!--END taPage-->\r\n";
	}
	
	echo '<div id="topcontent">';
		
	
	echo '<div id="summary">';
	echo'<span style="text-align:center;"><h1>Summary</h1></span>';
		###########################
		### SAMPLES ANALYZED
		###########################
	
	
	
		echo '<div id="samples">';
			
				echo '<h2>Samples Analyzed</h2>';
						
					foreach($fileArrays['vir'] as $val)
					{
						echo '<a href="./mastiff/'.$idmd5.'/'.$val.'">'.$val.'</a>';
						echo '<br/>';
					}
				
		echo '</div>';
		
		###########################
		### RELATED TICKETS
		###########################
		
		echo '<div id="related">';
		
			echo '<h2>Related Tickets</h2>';
			echo '<div id="tickets">';

			foreach($relatedTix as $key=>$val)
			{
				#echo '<pre>'.var_dump($val).'</pre>';
				
				echo '<div class="attribute">';
				echo 'Filename: ';
				echo '</div><div class="datapoint">';
				echo $val['filename'];
				echo '</div>';
				
				echo '<div class="attribute">';
				echo 'Ticket#: ';
				echo '</div><div class="datapoint">';
				echo '<a href="./ticket.php?ticket='.$val['ticket'].'">'.$val['ticket'].'</a>';
				echo '</div>';
				
				echo '<div class="attribute">';
				echo 'Notes: ';
				echo '</div><div class="datapoint">';
				if ($val['notes']!='')
				{
					echo $val['notes'];
				}
				else
				{
					echo 'No notes listed.';
				}
				echo '</div>';
				
				echo '<div class="attribute">';
				echo 'Last Seen: ';
				echo '</div><div class="datapoint">';
				$lastseen = date('D M j Y G:i:s T',$val['lastseen']);
				echo $lastseen;
				echo '</div>';
				
				echo '</br>';
			}
			
			echo '</div>';
		
		echo '</div>';
		
		###########################
		### EXPORT ALL TEXT
		###########################
		
		/*
		echo '<div id="txtall">';
			
				echo '<h2>Results from MASTIFF Analysis</h2>';
				echo '<a href="./txtdump.php?idmd5='.$idmd5.'" target="_blank">Open in new Tab</a></h2>';
			
		echo '</div>';
		*/
		
		
		###########################
		### PCAP ANALYSIS
		###########################
		if ($pcapFormat['ispcap']==TRUE)
		{
			
			echo '<div id="pcap">';
			
				echo '<h2>PCAP Analysis</h2>';
				
				echo '<b>PCAP Type: </b>';
				echo $pcapFormat['type'];

				if (($pcapFormat['type']=='email') || ($pcapFormat['type']=='web'))
				{
					echo ' - <a href="./pcap.php?idmd5='.$idmd5.'&type='.$pcapFormat['type'].'">Analyze PCAP (Alpha Stages)</a>';
				}
				echo '<br/>';
				#echo count($pcapFormat['block']).'<br/>';
				if((isset($pcapFormat['block'])) && (count($pcapFormat['block'])>0)){
					$_SESSION['block64']=$pcapFormat['block'];
					if(count($fileArrays['b64'])<1){
						#echo $pcapFormat['block'];
						echo '<a href="./base64.php">Extract '.count($pcapFormat['block']).' file(s)</a>';
					}
					else{
						foreach($fileArrays['b64'] as $key=>$val){
							
							#echo "<div id='base64block>";
							#echo $pcapFormat['block'][0];
							#echo "</div>";
							
							echo '<a href="./mastiff/'.$idmd5.'/'.$val.'">Download '.$val.'</a><br/>';
							
							$cmd = 'file "./mastiff/'.$idmd5.'/'.$val.'"';
							$fileout = shell_exec($cmd);
							$fileout=explode(':',$fileout);
							echo $fileout[1].'<br/>';
							
							$cmd = 'md5sum "./mastiff/'.$idmd5.'/'.$val.'"';
							$fileout = shell_exec($cmd);
							$fileout=explode(' ',$fileout);
							echo $fileout[0].'<br/>';
						}
					}
					
					#foreach($pcapFormat['block'] as $key=>$val){
						#echo 'Block '.($key+1).'<br/>';
						#echo $val.'<br/>';
					#}
					
				}
				
			
			echo '</div>';
			
		}
		
		
		###########################
		### ZIP CONTENTS
		###########################
		
		if ($zipContents!=FALSE)
		{
			echo '<div id="zip">';
			
				echo '<h2>Zip Contents</h2>';
				
				echo '<pre>';
				#var_dump($zipContents);
				foreach($zipContents as $val)
				{
					echo '<a href="./md5page.php?idmd5='.$val['md5'].'">'.$val['md5'].'</a>     ';
					$zipFile = preg_split('/\/zip_contents/',$val['filename']);
					echo $zipFile[1].'</br>';
					#echo $zipContents['size'];
				}
				
				echo '</pre>';
			
			echo '</div>';
		}
		else
		{
		}
		
		###########################
		### ANALYZE FUZZY HASH
		###########################
		
		echo '<div id="fuzzy">';
			
				$fuzzyOut = shell_exec('grep -e "[0-9a-f]\{32\}" ./mastiff/'.$idmd5.'/fuzzy.txt');
								 
				echo '<h2>Fuzzy Hash (SSDeep) Results</h2>';
				
				if (isset($fuzzyOut))
				{
					$regPattern = '/[0-9a-f]{32}/i';
												
					echo '<pre>';
					
						echo 'MD5                                      Percentage Match';
						echo '<br/><br/>';
						
						$fuzzySplit = preg_split("/((\r?\n)|(\r\n?))/", $fuzzyOut);
						array_pop($fuzzySplit);	#Removes last entry, which is blank
														
						foreach($fuzzySplit as $line)
						{
							preg_match($regPattern,$line,$match);
							$replacement = '<a href="./md5page.php?idmd5='.$match[0].'">'.$match[0].'</a>';
							$output = preg_replace($regPattern, $replacement, $line);
							$output.='<br/>';
							echo $output;

						}
					
					echo '</pre>';
				}
				
				else
				{
					echo 'No similar files were previously analyzed by MASTIFF';
				}
			
		echo '</div>';
		
		###########################
		### STRINGS
		###########################
		
		echo '<div id ="strings">';
			echo '<h2>Strings</h2>';
			echo '<a href="./mastiff/'.$idmd5.'/strings.txt" target="_blank">View all strings</a><br/>';
				
				$stringsfile=file('./mastiff/'.$idmd5.'/strings.txt');
				
				echo '<div id="xorhttp">';
				
					echo '<h3>Strings with "HTTP":</h3>';
					foreach($stringsfile as $line_no=>$line){
						$search = strstr($line, 'http');
						if($search!=FALSE){
							echo htmlentities($line);
							echo '<br/>';
						}
					}
					
				echo '</div>';
				
				echo '<div id="xorprog">';	
				
					echo '<h3>Strings with "Program":</h3>';
					foreach($stringsfile as $line_no=>$line){
						$search = strstr($line, 'program');
						if($search!=FALSE){
							echo htmlentities($line);
							echo '<br/>';
						}
					}
				
				echo '</div>';
							
		echo '</div>';
		
		###########################
		### DE-XOR
		###########################
		
		echo '<div id ="dexor">';
			echo '<h2>De-XOR</h2>';
			if((isset($fileArrays['xor'][0])) && ($fileArrays['xor'][0]!='')){
				$nomorexor = false;
				foreach($fileArrays['xor'] as $xval){
					echo '<a href="./mastiff/'.$idmd5.'/xor/'.$xval.'" target="_blank">View all De-Xor\'d strings</a><br/>';
				}
				
				
				$xorfile=file('./mastiff/'.$idmd5.'/xor/'.$xval);
				
				echo '<div id="xorhttp">';
				
					echo '<h3>Strings with "HTTP":</h3>';
					foreach($xorfile as $line_no=>$line){
						$search = strstr($line, 'http');
						if($search!=FALSE){
							echo htmlentities($line);
							echo '<br/>';
						}
					}
					
				echo '</div>';
				
				echo '<div id="xorprog">';	
				
					echo '<h3>Strings with "Program":</h3>';
					foreach($xorfile as $line_no=>$line){
						$search = strstr($line, 'program');
						if($search!=FALSE){
							echo htmlentities($line);
							echo '<br/>';
						}
					}
				
				echo '</div>';
				
				echo '<br/><a href="./xor.php">Run again using: <br/>'.$_SESSION['filename'].'</a>';
			}
			else{
				$nomorexor = true;
				echo '<p>NOTICE: NoMoreXOR has not yet been run!</p>';
				echo '<a href="./xor.php">Click to run NoMoreXOR on '.$_SESSION['filename'].'</a>';
			}
		echo '</div>';
		
		
		
	echo '</div>'; #End Summary Section
	
	echo '<div id="opensource">';
	
	echo'<span style="text-align:center;"><h1>Open Source Checks</h1></span>';
		
		###########################
		### VIRUSTOTAL
		###########################
		
		if ($virusTotal!=FALSE && !isset($virusTotal['submit']))
		{
			echo '<div id="virustotal">';
		
				echo '<h2>VirusTotal Results</h2>';
				
				echo '<pre>';
				
					echo $virusTotal['date'];
					echo $virusTotal['results'];
					echo '<a href="'.$virusTotal['link'].'" target="_blank">View VirusTotal results</a><br/><br/>';
					if(isset($virusTotal['sep']))
					{
						echo $virusTotal['sep'].'<br/>';
					}
					else
					{
						echo '<span style="color:red;">NOT DETECTED BY SEP</span><br/>';
					}
					if(isset($virusTotal['mse']))
					{
						echo $virusTotal['mse'].'<br/>';
					}
					else
					{
						echo '<span style="color:red;">NOT DETECTED BY MSE</span><br/>';
					}
				
				echo '</pre>';
		
			echo '</div>';
		}
		elseif($virusTotal!=FALSE && $virusTotal['submit']==FALSE)
		{
			echo '<div id="virustotal">';
		
				echo '<h2>VirusTotal Results</h2>';
				echo 'File not checked on VirusTotal.';
				
			echo '</div>';
		}
		else
		{
			echo '<div id="virustotal">';
		
				echo '<h2>VirusTotal Results</h2>';
				echo 'MD5 not found on VirusTotal.';
				
			echo '</div>';
		}
		
		###########################
		### ANUBIS
		###########################
		
		$fileName = $fileArrays['vir'][0];
		
		echo '<div id="anubis">';
			echo '<h2>Anubis</h2>';
			
			if($anubis==''){
				echo '<p>'.$idmd5.' has not yet been submitted to Anubis. <a href="./anubis/anubis.php?idmd5='.$idmd5.'&fileName='.urlencode($fileName).'" onclick="return confirm(\'Are you sure you want to submit to Anubis?\')"><br/>Click here to submit '.$fileName.'.</a></p>';
			}
			else{
				echo '<p>'.$idmd5.' has previously been submitted to Anubis. <a href="'.$anubis.'" target="_blank"><br/>Click here to view the results.</a></p>';
				echo '<p>Or <a href="./anubis/anubis.php?idmd5='.$idmd5.'&fileName='.urlencode($fileName).'">Click Here</a> to attempt to resubmit '.$fileName.'.</p>';
				
				$anubXML = @simplexml_load_file($anubis.'&format=xml');
				#$anubXML = new SimpleXMLElement($getAnub);
				
				if($anubXML!=FALSE){
										
					echo '<div id="anubisNet">';					
										
					foreach($anubXML->analysis_subject as $id=>$val){
						$anubTcp = $val->activities->network_activities->tcp_traffic;
						$anubDns = $val->activities->network_activities->dns_queries;
						
						
						if(!empty($anubTcp)){
							
							echo '<div id="anubTCP"><h3>TCP Connections: </h3>';
							
							if(isset($anubTcp->http_traffic->http_conversation[0])){
								$ai=0;
								foreach($anubTcp->http_traffic->http_conversation as $tcpid=>$tcpval){
									
									echo '<div class="anubConnect">';
									
									if( (isset($tcpval['hostname'])) && (isset($tcpval['dest_ip'])) ){
										$dest = '(Host: '.$tcpval['hostname'].') '.$tcpval['dest_ip'].':'.$tcpval['dest_port'];
									}
									elseif( isset($tcpval['hostname'])  ){
										$dest=$tcpval['hostname'].':'.$tcpval['dest_port'];
									}
									elseif(isset($tcpval['dest_ip'])){
										$dest=$tcpval['dest_ip'].':'.$tcpval['dest_port'];
									}
									else{
										$dest=='No Destination found.';
									}
									
									$_SESSION['anubisIP'][$ai]=$dest;
									$ai++;
									
									echo '<div class="anubRow httpconvo">'.$tcpval['src_ip'].':'.$tcpval['src_port'].' => '.$dest.'</div>';
									if(isset($tcpval['et_classification'])){
										echo '<div class="anubRow et"><b>EmergingThreat Classification: </b>'.$tcpval['et_classification'].'</div>';
									}
									if(isset($tcpval['et_rule_name'])){
										echo '<div class="anubRow et"><b>EmergingThreat Rule Name: </b>'.$tcpval['et_rule_name'].'</div>';
									}
									
									#echo '<div class="anub">';
									#foreach($tcpval->attributes() as $a=>$b){
									#	echo '<div class="anubRow"><div class="attribute">'.$a.' : </div><div class="data">'.$b.'</div></div>';
									#}
									#echo '</div>';
									
									
									if(isset($tcpval->http_request[0])){
										$ai2=0;
										foreach($tcpval->http_request as $reqid=>$reqval){
											echo '<div class="anubRow httpreq"><b>Request: </b>'.htmlentities($reqval['request']).'</div>';
											$_SESSION['anubisReq'][$ai2]=htmlentities($reqval['request']);
											$ai2;
										}
									
									
										
									}
									echo '</div>';
								}
							}
							#else{
								#foreach($anubTcp->http_traffic->http_conversation->attributes() as $tcpid => $tcpval){
									
									#echo '<div class="anubRow">'.$dnsval['src_ip'].':'.$dnsval['src_port'].' => '.$dnsval['name'].':'.$dnsval['dest_port'].'</div>';
									
									#echo '<div class="anub">';
									#echo '<div class="anubRow"><div class="attribute">'.$a.' : </div><div class="data">'.$b.'</div></div>';
									#echo '</div>';
								#}
							#}
							
							echo '</div>';
						}
						
						if(!empty($anubDns)){
							
							echo '<div id="anubDNS"><h3>DNS Requests: </h3>';
							
							if(isset($anubDns->dns_query[0])){
								$ai=0;
								foreach($anubDns->dns_query as $dnsid=>$dnsval){
									
									echo '<div class="anubRow">'.$dnsval['src_ip'].':'.$dnsval['src_port'].' => '.$dnsval['name'].':'.$dnsval['dest_port'].'</div>';
									$_SESSION['anubisDNS'][$ai]=(array)$dnsval;
									$ai++;
									#echo '<div class="anub">';
									#foreach($dnsval->attributes() as $a=>$b){
										#echo '<div class="anubRow"><div class="attribute">'.$a.' : </div><div class="data">'.$b.'</div></div>';
									#}
									#echo '</div>';
								}
							}
							else{
								#foreach($anubDns->dns_query->attributes() as $dnsid => $dnsval){
									#echo '<div class="anub">';
									#echo '<div class="anubRow">'.$dnsval['src_ip'].':'.$dnsval['src_port'].' => '.$dnsval['name'].':'.$dnsval['dest_port'].'</div>';
									#echo '<div class="anubRow"><div class="attribute">'.$a.' : </div><div class="data">'.$b.'</div></div>';
									#echo '</div>';
									#}
								}
								echo '</div>';
						}
							
							/*
							echo '<pre>';
							echo '<b>anubDns: </b>';
							var_dump($anubDns);
							echo '</pre>';
							echo '<br/><br/>';
							*/
							
							
						}
						
						
							/*
							echo '<pre>';
								echo '<b>anubTcp: </b>';
								var_dump($anubTcp);
								echo '<b>anubDns: </b>';
								var_dump($anubDns);
							echo '</pre>';
							*/
							
							
						
					
					
					echo '</div>';
					
					#echo '<br/><br/>';
					#echo '<pre>';
					#echo '<b>anubXML: </b>';
					#var_dump($anubXML);
					#echo '</pre>';
					
					
					
				}else{
					echo '<p>No Network Traffic Found.</p>';
				}
				
				
			}
			echo '</div>';
		
		
		###########################
		### THREATEXPERT CALLOUTS
		###########################
					
		$teXML = new SimpleXMLElement(file_get_contents('http://www.threatexpert.com/report.aspx?md5='.$idmd5.'&xml=1'));
		#echo '<pre>';
		#echo var_dump($teXML);
		#echo '</pre>';
		#echo'<pre>String: '.(string)($teXML).'</pre>';
		
		if ((string)$teXML!='not_found')
		{
			echo '<div id="texpertcall">';
			
			if(isset($teXML->subreports->subreport->technical_details->internetconnect_api->internetconnect->server)){
				$server = $teXML->subreports->subreport->technical_details->internetconnect_api->internetconnect->server;
				$server = (string)$server;
			}
			else{
				$server = '';
			}
			if(isset($teXML->subreports->subreport->technical_details->urldownloadtofile_api->urldownloadtofile_collection->urldownloadtofile->url)){
				$download = $teXML->subreports->subreport->technical_details->urldownloadtofile_api->urldownloadtofile_collection->urldownloadtofile->url;
				$download = (string)$download;
			}
			else{
				$download = '';
			}
			
			if(isset($teXML->subreports->subreport->technical_details->getrequests->request)){
				$dlget = $teXML->subreports->subreport->technical_details->getrequests->request;
				$dlget = (string)$dlget;
			}else{
				$dlget='';
			}
						
			if(($download) && ($dlget) && ($dlget!='')){
				$fulldownload = $download.'/'.$dlget;
			}else{
				$fulldownload='';
			}
						
			if($server!='' || $fulldownload!='')
			{
				
		
				echo '<h2>ThreatExpert Callouts</h2>';
				
				if($server!='')
				{
					echo 'File attempts to connect to: <b>'.$server.'</b><br/>';
					$_SESSION['threatX']['server']=$server;
					
				}
				else
				{
					echo 'No connections witnessed.<br/>';
				}
				
				if($fulldownload!='')
				{
					echo 'File attempts to download: <b>'.$fulldownload.'</b><br/>';
					$_SESSION['threatX']['fulldownload']=$fulldownload;
				}
				else
				{
					echo 'No downloads witnessed.<br/>';
				}
				
			}
			
			echo '</div>';
			
		}
		
		
	
	echo '</div>'; #END OPENSOURCE
	
	echo '</div>'; #END TOP CONTENT
	
	###########################
	### FILES SECTION
	###########################
	
	echo '<div id="bottomcontent">';
	echo '<div id="files">';
	
		echo '<span style="text-align:center;"><h1>Files Produced by MASTIFF</h1></span>';
	
		###########################
		### TXT RESULTS
		###########################
	
		echo '<div id="txt">';
	
			echo '<h2>TXT Results</h2>';
			
			echo '<ul>';
			
				foreach($fileArrays['txt'] as $val)
				{
					echo '<li>';
					echo '<a href="./mastiff/'.$idmd5.'/'.$val.'">'.$val.'</a>';
					echo '</li>';
				}
				
			echo '</ul>';
		
		echo '</div>';
		
		###########################
		### MASTIFF LOG
		###########################
		
		echo '<div id="log">';
		
			echo '<h2>Log Files</h2>';
			
			echo '<ul>';
				
					foreach($fileArrays['log'] as $val)
					{
						echo '<li>';
						echo '<a href="./mastiff/'.$idmd5.'/'.$val.'">'.$val.'</a>';
						echo '</li>';
					}
					
				echo '</ul>';
		
		echo '</div>';
		
		###########################
		### EXTRACTED FILES
		###########################
		
		if (isset($fileArrays['extract'][0]))
		{
			echo '<div id="extract">';
			
				echo '<h2>Files Extracted</h2>';
				
				echo '<ul>';
				
					foreach($fileArrays['extract'] as $val)
					{
						echo '<li>';
						echo '<a href="./mastiff/'.$idmd5.'/resources/'.$val.'">'.$val.'</a>';
						echo '</li>';
					}
					
				echo '</ul>';
			
			echo '</div>';
		}
		
		
	
	echo '</div>';
	
	###########################
	### BROWSER SECTION
	###########################
	
	echo '<div id="browser">';
	
		echo '<span style="text-align:center;"><h1>Web Searches</h1></span>';
		
		###########################
		### GOOGLE LINK
		###########################
		
		echo '<h2><a href="http://www.google.com/#q=%22'.$idmd5.'%22" target="_blank">Click for Google</a></h2>';
		
		###########################
		### DUCKDUCKGO FRAME
		###########################
		
		echo '<h2>DuckDuckGo</h2>';
		
		echo '<iframe width="100%" height="500px" src="http://www.duckduckgo.com/?q=%22'.$idmd5.'%22"></iframe>';

		echo '<br/>';
		echo '<br/>';
		
		###########################
		### STARTPAGE FRAME
		###########################
		
		echo '<h2>StartPage</h2>';
		
		echo '<iframe width="100%" height="500px" src="https://www.startpage.com/do/metasearch.pl?q=%22'.$idmd5.'%22"></iframe>';

		echo '<br/>';
		echo '<br/>';
		
		###########################
		### THREATEXPERT FRAME
		###########################
		
		echo '<h2>ThreatExpert</h2>';
		
		echo '<iframe name="threatexpert" width="100%" height="500px" src="http://www.threatexpert.com/report.aspx?md5='.$idmd5.'"></iframe>';

		echo '<br/>';
		
	echo '</div>'; #END BOTTOMCONTENT
	
	echo '</div>'; #END MAINCONTENT
	
	/*
	echo '<pre>';

	#$arr = get_defined_vars();

	foreach($_SESSION as $key=>$val){
		#var_dump($val);
		echo $key.' => '.$val;
		echo '<br/>';
	}
	
	var_dump($_SESSION);

	echo '</pre>';
	*/
	
	echo '</div>'; #END CONTAINER
	
	###########################
	### FOOTER
	###########################
	
	include './footer.php';
?>

<script>
	$(document).ready(function(){

		// Hide/Unhide ThreatAnalyzer Data
		$("#remButton").toggle();
		
		$("#taButton").click(function(){
			$("#taPage").toggle();
			$("#topcontent").toggle();
			$("#bottomcontent").toggle();
			$("#taButton").toggle();
			$("#remButton").toggle();
		});
		
		$("#remButton").click(function(){
			$("#taPage").toggle();
			$("#topcontent").toggle();
			$("#bottomcontent").toggle();
			$("#taButton").toggle();
			$("#remButton").toggle();
		});
		
	});
</script>
	
</body>
</html>

