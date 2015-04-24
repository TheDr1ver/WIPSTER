<?
/*
session_start();


#######################
##### FUNCTIONS
#######################


*/

#Get Sandbox Name/Attributes Based on MAC Address
function macInfo($threatAPI,$threatPage,$threatArgs,$mac){
	$command='/sandboxes/'.$mac;
	$threatArgs='';
	$response=@callTA($command,$threatAPI,$threatPage,$threatArgs);
	$threatArgs='';	#Reset to blank
	$sandboxes = json_decode($response[0], true);
	#echo '<pre>';
	#var_dump($sandboxes);
	#echo '</pre>';
	$macRes = array();
	$macRes['name']=$sandboxes['sandbox']['name'];
	$macRes['attr']=$sandboxes['sandbox']['sandbox_attributes'][0];
	return $macRes;
}

############################
###### MD5 SUMMARY TABLE
############################

# Get All Analyses
$command='/analyses';
$threatArgs='md5='.$idmd5;
$response=@callTA($command,$threatAPI,$threatPage,$threatArgs);
$threatArgs='';
#echo '<p>HTTP Response: '.$response[1].'</p>';
$analyses = json_decode($response[0], true);
#Analysis IDs = $analyses[0]['analysis_id']
#echo '<pre>';
#var_dump($analyses);
#echo '</pre>';


#Build Analysis Table
$taTable=array();
#echo '$analyses[0]: ';
#print_r($analyses['analyses'][0]['analysis_id']);
$tai=0;
foreach($analyses as $innerArray){
	if(is_array($innerArray)){
		foreach($innerArray as $key=>$var){
			$taTable[$tai]['id']=$var['analysis_id'];	#Analysis ID
			
			$macInfo=macInfo($threatAPI,$threatPage,$threatArgs,$var['sandbox_mac_address']);
			$taTable[$tai]['sandbox_name']=$macInfo['name'];		#Sandbox Name
			$taTable[$tai]['sandbox_attr']=$macInfo['attr'];		#Attributes
			
			$taTable[$tai]['sandbox_mac']=$var['sandbox_mac_address'];		#Sandbox MAC
			
			$taTable[$tai]['status']=$var['status'];#Status
			$taTable[$tai]['filename']=$var['filename'][0];#Filename
			$taTable[$tai]['md5']=$var['md5'];#MD5
			$taTable[$tai]['time']=$var['created_at'];#Time
			if(isset($var['pcap_url'])){
				$taTable[$tai]['pcap']=$var['pcap_url'];#PCAP URL - /api/v1/analyses/40/downloads/pcap
			}
			else{
				$taTable[$tai]['pcap']='Unavailable';
			}
			$tai++;
		}
	}
}

#############
##### RISK
#############

$command='/samples/'.$idmd5.'/mdrs';
#$threatArgs='riskonly=true';
$threatArgs='';
$response=@callTA($command,$threatAPI,$threatPage,$threatArgs);
$threatArgs='';
#echo '<p>HTTP Response: '.$response[1].'</p>';
$risk[] = json_decode($response[0], true);

#echo "<pre>";
#var_dump($risk);
#echo "</pre>";

$riskDescKnown=array();
$riskDescHigh=array();
$riskDescMed=array();
$riskDescLow=array();
$riskDescUn=array();
$riskDescArr=array();

if(array_key_exists('analyses',$risk[0]['sample'])){
	foreach($risk[0]['sample']['analyses'] as $key=>$val){
		if($val['risk']!=='Unknown'){
			foreach($val['risks'] as $risKey=>$risVal){
				#echo '<pre>';
				#var_dump($risVal);
				if($risVal['maliciousness']=='Known'){
					$risVal['maliciousness']='<span style="font-weight:bold;color:red;" class="blink">'.$risVal['maliciousness'].'</span>';
				}
				elseif($risVal['maliciousness']=='Unknown'){
				}
				elseif($risVal['maliciousness']=='Low'){
					$risVal['maliciousness']='<span style="font-weight:bold;color:green;">'.$risVal['maliciousness'].'</span>';
				}
				elseif($risVal['maliciousness']=='Medium'){
					$risVal['maliciousness']='<span style="font-weight:bold;color:orange;">'.$risVal['maliciousness'].'</span>';
				}
				elseif($risVal['maliciousness']=='High'){
					$risVal['maliciousness']='<span style="font-weight:bold;color:red;">'.$risVal['maliciousness'].'</span>';
				}
				$riskName = $risVal['maliciousness'].': '.$risVal['name'];
				
				#echo '$riskName = '.$riskName;
				#echo '</pre>';

				if($risVal['maliciousness']=='<span style="font-weight:bold;color:red;" class="blink">Known</span>'){
					if(!in_array($riskName,$riskDescKnown)){
						$riskDescKnown[]=$riskName;
					}
				}
				elseif($risVal['maliciousness']=='<span style="font-weight:bold;color:red;">High</span>'){
					if(!in_array($riskName,$riskDescHigh)){
						$riskDescHigh[]=$riskName;
					}
				}
				elseif($risVal['maliciousness']=='<span style="font-weight:bold;color:orange;">Medium</span>'){
					if(!in_array($riskName,$riskDescMed)){
						$riskDescMed[]=$riskName;
					}
				}
				elseif($risVal['maliciousness']=='<span style="font-weight:bold;color:green;">Low</span>'){
					if(!in_array($riskName,$riskDescLow)){
						$riskDescLow[]=$riskName;
					}
				}
				elseif($risVal['maliciousness']=='Unknown'){
					if(!in_array($riskName,$riskDescUn)){
						$riskDescUn[]=$riskName;
					}
				}
				
			}
		}
	}
}

$riskDescArr=array_merge($riskDescKnown,$riskDescHigh,$riskDescMed,$riskDescLow,$riskDescUn);

#echo '<pre>';
#var_dump($riskDescArr);
#echo '</pre>';


############################
##### NETWORK CONNECTIONS
############################

# GET ANALYSES IDS
$analysesIDs=array();
foreach($taTable as $key=>$val){
	$analysesIDs[]=$val['id'];
}

# GET FULL JSON DUMPS
#$jsonDumps=array();
$critsCon=array();
$critsCon['uri']=array();
$critsCon['useragent']=array();

foreach($analysesIDs as $anKey=>$anVal){
	$jsonDumps = array();
	$connectionDumps=array();
	$netOpDumps=array();
	$ipCallouts=array();
	$dnsCallouts=array();
	$calloutCommands=array();
	#$conHeaders=array();
	
	
	$command='/analyses/'.$anVal.'/archive_browser/get_file';
	$threatArgs='archive_file=Analysis/analysis.json';
	$response=@callTA($command,$threatAPI,$threatPage,$threatArgs);
	$threatArgs='';
	#echo '<p>HTTP Response: '.$response[1].'</p>';
	$jsonDumps[] = json_decode($response[0], true);
	
	
	
	# GET CONNECTION SECTIONS

	foreach($jsonDumps as $key=>$val){
		#if( (array_key_exists('analysis',$val)) && (array_key_exists('processes',$val['analysis'])) && (array_key_exists('process',$val['analysis']['processes'])) && (array_key_exists('connection_section',$val['analysis']['processes']['process']))){
		if($val!=NULL){
			if(array_key_exists('analysis',$val)){
				#echo 'Analysis '.$key.': $val["analysis"] EXISTS!';
				if(array_key_exists('processes',$val['analysis'])){
					#echo 'Analysis '.$key.': $val["analysis"]["processes"] EXISTS!';
					if(array_key_exists('process',$val['analysis']['processes'])){
						#echo 'Analysis '.$key.': $val["analysis"]["processes"]["process"] EXISTS!';
						foreach($val['analysis']['processes']['process'] as $prokey=>$proval){
							#echo "<br/>looking at ".$key."['analysis']['processes']['process']['".$prokey."']<br/>";
							#echo '$proval '.$prokey.':<br/><br/>';
							#echo '<pre>';
							#var_dump($proval);
							#echo '</pre>';
							if(array_key_exists('connection_section',$proval)){
								#echo 'Analysis '.$key.': $val["analysis"]["processes"]["process"]["'.$prokey.'"]["connection_section"] EXISTS!<br/>';
								$connectionDumps[]=$proval['connection_section'];
								
								# GET CALLOUTS
																
								foreach($connectionDumps as $condumpkey=>$condumpval){
									if(array_key_exists('connection',$condumpval)){
										foreach($condumpval['connection'] as $conKey=>$conVal){
											$critsConTemp=array();
											#Get IP Direct Connection
											if($conVal['@sandbox_action']=='FAIL'){
												if(!in_array($conVal['@remote_ip'],$ipCallouts)){
													#$ipCallouts[$conKey]=$conVal['@remote_ip'];
													$ipCallouts[]=$conVal['@remote_ip'];
												}
												
											}
											
											#if($conVal['@remote_hostname']!=''){
											#	$dnsCallouts[$conKey]=$conVal['@remote_hostname'];
											#}
											
											#Get HTTP Headers
											if(array_key_exists('http_command',$conVal)){
												foreach($conVal['http_command'] as $httpComKey=>$httpComVal){
													$calloutCommands[$conKey]=$httpComVal['@method'].": ".$httpComVal['@url'];
													$critsConTemp['method']=$httpComVal['@method'];
													$critsConTemp['uri']=$httpComVal['@url'];
												}
											}
											
											if(array_key_exists('http_header',$conVal)){
												foreach($conVal['http_header'] as $headKey=>$headVal){
													#echo '#@header: '.$headVal['@header'].'<br/>';
													if(strpos($headVal['@header'],'User-Agent')!==false){
														$calloutCommands[$conKey]=$calloutCommands[$conKey]."\r\n".$headVal['@header'];
														$critsConTemp['useragent']=$headVal['@header'];
													}
													if(strpos($headVal['@header'],'Host')!==false){
														$calloutCommands[$conKey]=$calloutCommands[$conKey]."\r\n".$headVal['@header'];
														$critsConTemp['host']=$headVal['@header'];
													}
												}
											}
											#Build the full request for crits
											if(!empty($critsConTemp['method']) || !empty($critsConTemp['host']) || !empty($critsConTemp['uri'])){
												$critsConTemp['request']=$critsConTemp['method']." ".$critsConTemp['host'].$critsConTemp['uri'];
											}
											
											#If not already in the crits array, add it
											if(!in_array($critsConTemp['request'],$critsCon['uri']) && !empty($critsConTemp['request'])){
												$critsCon['uri'][]=$critsConTemp['request'];
											}
											if(!in_array($critsConTemp['useragent'],$critsCon['useragent']) && !empty($critsConTemp['useragent'])){
												$critsCon['useragent'][]=$critsConTemp['useragent'];
												/*
												echo "$critsConTemp[useragent] Added.";
												echo "<pre>";
												print_r($critsCon);
												echo "</pre>";
												*/
											}
											$critsConTemp=array();
										}
									}
								}
							}
							if(array_key_exists('networkoperation_section',$proval)){
								$netOpDumps[]=$proval['networkoperation_section'];
								# GET CALLOUTS
								foreach($netOpDumps as $netKey=>$netVal){
									if(array_key_exists('dns_request_by_name',$netVal)){
										foreach($netVal['dns_request_by_name'] as $dnsKey=>$dnsVal){
											if(array_key_exists('@request_name',$dnsVal)){
												if(!in_array($dnsVal['@request_name'],$dnsCallouts)){
													$dnsCallouts[]=$dnsVal['@request_name'];
												}
											}
											
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
	}
}

#echo '<pre>';
#var_dump($jsonDumps);
#echo '</pre>';



#echo '<pre>';
#echo '##### $connectionDumps:';
#var_dump($connectionDumps);
#echo '</pre>';

#echo '<pre>';
#echo '##### $netOpDumps:';
#var_dump($netOpDumps);
#echo '</pre>';



/*
echo '<pre>';
echo '##### $ipCallouts, $dnsCallouts, $calloutCommands:';
var_dump($ipCallouts);
echo '<br/>';
var_dump($dnsCallouts);
echo '<br/>';
var_dump($calloutCommands);
echo '<br/>';
echo '</pre>';
*/


?>
<!--BEGIN TEMPORARY HEADERS-->
<!--
<html>
<head>
<style>
	#taContent{
		width:100%;
		height:100%;
	}
	#taSummary{
		width:1000px;
		margin-left:auto;
		margin-right:auto;
	}
	#analysisTable{
		/*content-align:center;*/
		display:table;
	}
	.taHead{
		display:table-header-group;
		font-weight:bold;
		padding:5px;
	}
	.tahCell{
		display:table-cell;
		padding-right:5px;
	}
	.taRowEv{
		background-color:aliceblue;
	}
	.taRowOd{
		background-color:white;
	}
	.taCell{
		display:table-cell;
		padding:5px;
	}
	.taRow{
		display:table-row;
	}
	.blink{
		animation: blink 1s steps(5, start) infinite;
		-webkit-animation: blink 1s steps(5, start) infinite;
	}
	@keyframes blink{
		to {visibility:hidden;}
	}
	@-webkit-keyframes blink{
		to {visibility:hidden;}
	}
	.calloutVal{
		margin:5px;
	}
	.callData{
		display:inline-block;
		height:250px;
		width:300px;
		overflow-x:auto;
		overflow-y:auto;
		vertical-align:top;
		margin-right:15px;
		border-style:solid;
		border-color:black;
	}
	#ipCallouts{
		display:inline-block;
		height:350px;
		width:300px;
		vertical-align:top;
		margin-right:5px;
	}
	#dnsCallouts{
		display:inline-block;
		height:350px;
		width:300px;
		vertical-align:top;
		margin-right:5px;
	}
	#calloutCmd{
		display:inline-block;
		height:350px;
		width:300px;
		vertical-align:top;
	}
	#calloutCmd .callData{
		white-space:pre;
	}
	#calloutCmd .calloutVal{
		margin-bottom:15px;
	}
	
</style>
</head>
-->
<!--END TEMP HEADERS-->

<div id="taContent">
	<div id="taSummary">
		<h2>Summary</h2>
		
		<h3>ThreatAnalyzer Analyses of <? echo $idmd5; ?></h3>
		
		<div id="analysisTable">
			<div id="analysisTableHeader" class="taHead">
				<div id="taAnalysis" class="tahCell">Analysis</div>
				<div id="taSandName" class="tahCell">Name</div>
				<!--<div id="taSandAttr" class="tahCell">Attributes</div>-->
				<div id="taStatus" class="tahCell">Status</div>
				<div id="taFilename" class="tahCell">Filename</div>
				<div id="taMD5" class="tahCell">MD5</div>
				<div id="taTime" class="tahCell">Time</div>
				<div id="taPCAP" class="tahCell">PCAP</div>
			</div>
			<?
				
				#Build rows
				foreach($taTable as $key=>$val){
					if($key %2==0){
						echo "<div id='taRow".$key."' class='taRow taRowEv ".$key."'>";
					}
					else{
						echo "<div class='taRow taRowOd ".$key."'>";
					}
					echo "<div id='analysis".$key."' class='taCell analysis ".$key."'><a href='http://".$threatBase."/samples/".$val['md5']."/analyses/".$val['id']."' target='_blank'>".$val['id']."</a></div>";
					echo "<div id='sandbox_name".$key."' class='taCell sandbox_name ".$key."' title='".$val['sandbox_attr']."'>".$val['sandbox_name']."</div>";
					#echo "<div id='sandbox_attr".$key."' class='taCell sandbox_attr ".$key."'>".$val['sandbox_attr']."</div>";
					echo "<div id='status".$key."' class='taCell status ".$key."'>".$val['status']."</div>";
					echo "<div id='filename".$key."' class='taCell filename ".$key."'>".$val['filename']."</div>";
					echo "<div id='md5".$key."' class='taCell md5 ".$key."'><a href='http://".$threatBase."/samples/".$val['md5']."' target='_blank'>".$val['md5']."</a></div>";
					echo "<div id='time".$key."' class='taCell time ".$key."'>".$val['time']."</div>";
					if($val['pcap']!='Unavailable'){
						echo "<div id='pcap".$key."' class='taCell pcap ".$key."'><a href='".$val['pcap']."?api_token=".$threatAPI."'>Download</a></div>";
					}
					else{
						echo "<div id='pcap".$key."' class='taCell pcap ".$key."'>".$val['pcap']."</div>";
					}
					
					
					echo "</div>";	#End Row Div
				}
			?>
			
		</div><!--End Analysis Table-->
		
		<div id="taRisk">
		<?
			if($risk[0]['sample']['risk']=='High'){
				echo "<a href='http://".$threatBase."/samples/".$idmd5."/mdr_details' target='_blank'><h3>Risk: <span class='blink' style='color:red;'>HIGH</span></h3></a>";
			}
			elseif($risk[0]['sample']['risk']=='Medium'){
				echo "<a href='http://".$threatBase."/samples/".$idmd5."/mdr_details' target='_blank'><h3>Risk: <span style='color:orange;'>MEDIUM</span></h3></a>";
			}
			elseif($risk[0]['sample']['risk']=='Low'){
				echo "<a href='http://".$threatBase."/samples/".$idmd5."/mdr_details' target='_blank'><h3>Risk: <span style='color:green;'>LOW</span></h3></a>";
			}
			elseif($risk[0]['sample']['risk']=='Known'){
				echo "<a href='http://".$threatBase."/samples/".$idmd5."/mdr_details' target='_blank'><h3>Risk: <span class='blink' style='color:red;'>KNOWN</span></h3></a>";
			}
			elseif($risk[0]['sample']['risk']=='Unknown'){
				echo "<a href='http://".$threatBase."/samples/".$idmd5."/mdr_details' target='_blank'><h3>Risk: UNKNOWN</h3></a>";
			}
			
			?>
			<div id='risklist'>
				<?
				foreach($riskDescArr as $key=>$val){
					echo "<div class='riskname'>".$val."</div>";
				}
				?>
			</div><!--END RISKLIST-->
			
			
		</div><!--END taRisk-->
			
		<div id="taNetwork">
			<h3>Network Connections</h3>
			<?
			if(!empty($ipCallouts)){
				echo "<div id='ipCallouts'>";
					echo "<h3>IPs</h3>";
					echo "<div class='callData'>";
					$_SESSION['taIP']='';
					foreach($ipCallouts as $key=>$val){
						echo "<div class='calloutVal'>";
						echo $val;
						echo "</div>";
						$_SESSION['taIP']=$val.' '.$_SESSION['taIP'];
					}
					$_SESSION['taIP']=rtrim($_SESSION['taIP'], ' ');
					echo "</div>";
				echo "</div>";
			}
			if(!empty($dnsCallouts)){
				echo "<div id='dnsCallouts'>";
					echo "<h3>Domains</h3>";
					echo "<div class='callData'>";
						$_SESSION['taDNS']='';
						foreach($dnsCallouts as $key=>$val){
								echo "<div class='calloutVal'>";
								echo $val;
								echo "</div>";
								$valPattern = '/^([a-zA-Z0-9\.\-]*)\.([a-z]*\/*.*)$/';
								$valReplace = '$1[.]$2';
								$val = preg_replace($valPattern, $valReplace, $val);
								$_SESSION['taDNS']=$val.' '.$_SESSION['taDNS'];
							}
						$_SESSION['taDNS']=rtrim($_SESSION['taDNS'], ' ');
						#echo $_SESSION['taDNS'];
					echo "</div>";
				echo "</div>";
			}
			if(!empty($calloutCommands)){
				echo "<div id='calloutCmd'>";
					echo "<h3>Commands</h3>";
					echo "<div class='callData'>";
						foreach($calloutCommands as $key=>$val){
								echo "<div class='calloutVal'>";
								echo $val;
								echo "</div>";
							}
					echo "</div>";
				echo "</div>";
			}
			if( (empty($ipCallouts)) && (empty($dnsCallouts)) && (empty($calloutCommands)) ){
				echo "<b>No Callouts Detected</b>";
			}
			else{
				echo "<div id='networklink'>";
					echo "<a href='http://".$threatBase."/samples/".$idmd5."/network_activity' target='_blank'>View Network Activity</a>";
				echo "</div>";
			}
			
			?>
		</div><!--End taNetwork-->
		
	</div><!--End taSummary-->
</div><!--End taContent-->
