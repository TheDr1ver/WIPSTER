<?php

###########################
/*
 * SET VARIABLES
 */
###########################

###########################
/*
 * DEFINE FUNCTIONS
 */
###########################

require './func/urlFunctions.php';

###########################
/*
 * RUN FUNCTIONS
 */
###########################

#$apis = getAPIs();
require './func/config.php';

##########################
#For looking up a previously searched URL
##########################

if(isset($_GET['url'])){
	
	#CSS for submissino form
	$showsubmit = 'block';
	$hidesubmit = 'none';
	$formdisplay = 'none';
	
	#Set all checkboxes
	$_POST['vtChk'] = TRUE;
	$_POST['mldChk'] = TRUE;
	$_POST['googChk'] = TRUE;
	$_POST['wotChk'] = TRUE;
	$_POST['wepawetChk'] = TRUE;
	$_POST['sucChk'] = TRUE;
	$_POST['ipvoidChk'] = TRUE;
	$_POST['rexChk'] = TRUE;
	$_POST['urlqChk'] = TRUE;
	$_POST['qtraChk'] = TRUE;
	
	$_POST['duckChk'] = TRUE;
	$_POST['startChk'] = TRUE;
	
	#Get URL Lookup
	$_POST['url']=$_GET['url'];
	
	$db = new SQLite3('./urls/urls.db');
	$queryurl = $db->escapeString($_POST['url']);
	#echo $_POST['url'];
	#echo '<br/><br/>';
	#echo $queryurl;
	$result = $db->query('SELECT * FROM urls WHERE url = "'.$queryurl.'"');
	
	$arrayDump=$result->fetchArray();
	
	if (isset($arrayDump['id']))
	{
		$validOldURL=TRUE;	#The URL was found in the db
		
		$_POST['ticket']=$arrayDump['ticket'];
		
		if(isset($arrayDump['notes'])){
			$_POST['notes']=$arrayDump['notes'];
		}
		else{
			$_POST['notes']='';
		}
		
		$_POST['vtapi']=$apis['vt'];
		$_POST['wotapi']=$apis['wot'];
		$_POST['googapi']=$apis['goog'];
				
	}
	else{
		echo 'ERROR: Invalid URL.';
		$showsubmit = 'none';
		$hidesubmit = 'block';
		$formdisplay = 'block';
	}
}

##########################
#For submitting a new URL
##########################

if (($_SERVER['REQUEST_METHOD']=='POST')||(isset($validOldURL)))	#If submitting a new URL OR if looking for a valid URL that was previously submitted
{
	$showsubmit = 'block';
	$hidesubmit = 'none';
	$formdisplay = 'none';
	
	$url = $_POST['url'];
	
	##########################
	#Convert to HTTP:// if not already
	##########################
	
	$urlArray = parse_url($url);
	if(isset($urlArray['scheme'])){
		if ($urlArray['scheme']!='http'){
			$url=str_replace($urlArray['scheme'],"http",$url);
		}
	}
	else
	{
		$url = 'http://'.$url;
		$_POST['url'] = $url;
		$urlArray['host'] = $urlArray['path'];
	}
	
	##########################
	#In-page actions
	##########################
	$formSubmit=formSubmit($_POST);
	$ips = getIP($urlArray['host']);
	$notes = htmlentities($formSubmit['notes']);
	$ticket = htmlentities($formSubmit['ticket']);
	$old = $formSubmit['old'];
	$respHead = respHead($url);
	
	##########################
	#Open source actions
	##########################
	$vtScan = (!isset($_POST['vtChk'])||($_POST['vtapi']=='Your VirusTotal API')) ? FALSE : vtInteract($_POST);	#If virustotal checkbox is checked, runs vt function, otherwise sets $vtScan to FALSE
	$text = getMLD($url);
	$checkMLD = (!isset($_POST['mldChk'])) ? FALSE : checkMLD($text,$urlArray['host']);	#Return FALSE if no matches, Return Array of matches if found
	$googResults = (!isset($_POST['googChk'])||($_POST['googapi']=='Your Google SafeBrowsing API')) ? FALSE : checkGoog($urlArray['host'],$_POST['googapi']);
	$wotResults = (!isset($_POST['wotChk'])||($_POST['wotapi']=='Your MyWOT API')) ? FALSE : checkWot($urlArray['host'],$_POST['wotapi']);
	$wepawetResults = (!isset($_POST['wepawetChk'])) ? FALSE : wepawet($url);
	$sucuriResults = (!isset($_POST['sucChk'])) ? FALSE : sucuri($urlArray['host']);
	
	if(($ips!=FALSE) && isset($_POST['ipvoidChk'])){
		foreach($ips as $val){
			$ipvoidResults[] = ipvoid($val);
		}
	}
	else{
		$ipvoidResults = FALSE;
	}
		
}
else{
	#Set CSS for Submission Form on initial blank urlResearch page
	$showsubmit = 'none';
	$hidesubmit = 'block';
	$formdisplay = 'block';
}

?>

<html>
	<head>
		<title>Research URLs</title>
		<LINK href="./css/urlResearch.css" rel="stylesheet" type="text/css">
		<script src="./scripts/jquery-1.11.0.min.js"></script>
	</head>

<body>
	
<div id="container">

	<div id="header">
	
		<h1>Research URLs</h1>
	
		<a href="./urlResults.php">URL Results Dashboard</a> <!--| <a href="./urlSearch.php">Search</a>-->
		
		<div id="shcontainer">
			<div id="showsubmit" class="showhide" style="display:<? echo $showsubmit;?>;">
				Show submission box
			</div>
			
			<div id="hidesubmit" class="showhide" style="display:<? echo $hidesubmit;?>;">
				Hide submission box
			</div>
		</div>
	
	</div>
	
	<div id="forms" style="display:<? echo $formdisplay;?>;">
	
		<div id="urls">
		
			<form action="urlResearch.php" method="post" enctype="multipart/form-data">

				<div id="required">
					<div id="reqHead">
						Required Fields
					</div>
					<br/>
					<div class="attribute">URL: </div>
					<input id="url" type="text" name="url" value="http://" size="25" required />
					<br/>
					<br/>
					<div class="attribute">Ticket #: </div>
					<input id="ticket" type="text" name="ticket" pattern="\d{7,12}" size="25" required />
					<br/>
				</div>
				
				<div id="resources">
					<div id="opensource">
						<div class="attribute">OpenSource Resources</div>
						<div class="osCheckbox"><input type="checkbox" name="vtChk" id="vtChk" value="true" checked>VirusTotal</div>
						<div class="osCheckbox"><input type="checkbox" name="mldChk" id="mldChk" value="true" checked>MalwareDomains.com</div>
						<div class="osCheckbox"><input type="checkbox" name="googChk" id="googChk" value="true" checked>Google SafeBrowsing</div>
						<div class="osCheckbox"><input type="checkbox" name="wotChk" id="wotChk" value="true" checked>Web of Trust (MyWOT)</div>
						<div class="osCheckbox"><input type="checkbox" name="wepawetChk" id="wepawetChk" value="true" checked>Wepawet</div>
						<div class="osCheckbox"><input type="checkbox" name="sucChk" id="sucChk" value="true" checked>Sucuri</div>
						<div class="osCheckbox"><input type="checkbox" name="ipvoidChk" id="ipvoidChk" value="true" checked>IPVoid</div>
						<div class="osCheckbox"><input type="checkbox" name="rexChk" id="rexChk" value="true" checked>RexSwain</div>
						<div class="osCheckbox"><input type="checkbox" name="urlqChk" id="urlqChk" value="true" checked>URLQuery</div>
						<div class="osCheckbox"><input type="checkbox" name="qtraChk" id="qtraChk" value="true" checked>Quttera</div>
					</div>
					
					<div id="browsers">
						<div class="attribute">Browsers</div>
						<div class="browseCheckbox"><input type="checkbox" name="duckChk" id="duckChk" value="true" checked>DuckDuckGo</div>
						<div class="browseCheckbox"><input type="checkbox" name="startChk" id="startChk" value="true" checked>StartPage</div>
					</div>
				</div>
				
				<div id="optional">
					<div class="hidden">
					<div class="attribute">VirusTotal API:</div> <input id="vtapi" type="text" name="vtapi" size="25" value="<?php echo $apis['vt'];?>" required />
					<br/>
					<div class="attribute">WOT API:</div> <input id="wotapi" type="text" name="wotapi" size="25" value="<?php echo $apis['wot'];?>" required />
					<br/>
					<div class="attribute">Google SafeBrowsing API:</div> <input id="googapi" type="text" name="googapi" size="25" value="<?php echo $apis['goog'];?>" required />
					</div><!--end hidden class-->
					<br/>
					Notes:
					<br/>
					<textarea name="notes" cols="25" rows="5" style="width:700px; height:150px;"></textarea>
					<br/>
					<input type="submit" name="submit" value="Submit" />
				</div>

			</form>
		
		</div>
	
	</div>
	
	<div id="results">
	
<?php
	
	if(isset($url))
	{
	
	###########################
	### ALERT SECTION
	###########################
	
	echo '<div id = "alert">';
	
		#Check if URL has been previously submitted
		if($old)
		{
			echo '<p>This URL has been previously submitted in ticket# <a href="./urlTicket.php?ticket='.$ticket.'">'.$ticket.'</a>.</p>';
		}
		
		#Check response headers
		if($respHead!=FALSE){
			echo '<p>RESPONSE Header: '.$respHead[0].'</p>';
			if(isset($respHead['Location'])){
				echo '<div id="redirNotice">';
				
					echo '<p>The response header contains a location redirect to: '.$respHead['Location'].'</p>';
					?>
					
					<script>
					$(document).ready(function(){
						$('#forms').show();
						$('#showsubmit').hide();
						$('#hidesubmit').show();
						$('#url').val('<? echo $respHead['Location']; ?>');
						$('#ticket').val('<? echo $ticket; ?>');
					});
					</script>
					
					<?
					echo '<p><br/>The submission form has been pre-populated with the redirect link.</p>';
					echo '<p>BE SURE TO DOUBLE-CHECK REXSWAIN TO VERIFY THE LOCATION!</p>';
				
				echo '</div>';
			}
		}
		
		#Check VT Response
		if(isset($vtScan)){
			foreach($vtScan as $key=>$val){
				if(isset($val['positives'])){
					
					if($val['positives']>0)
					{
						echo '<p>VirusTotal found '.$val['positives'].' / '.$val['total'].' postive matches for malware.</p>';
					}	
					
				}
				
			}
		}
		
		#Check WOT Responses
		if(isset($wotResults['categories'])){
			foreach($wotResults['categories'] as $key=>$val){
				if($key!='Good site'){
					echo '<p>MyWOT categorizes this site as '.$key.' with '.$val.'% certainty.</p>';
				}
				
			}
		}
				
		if(isset($wotResults['blacklists'])){
			foreach($wotResults['blacklists'] as $key=>$val){
				echo '<p>MyWOT found this url on '.$key.' blacklists, last seen '.date("Y-m-d",$val).'.</p>';
			}
		}
		
		#Check Sucuri Responses
		if($sucuriResults!=FALSE){
			echo '<p>Sucuri did not find this domain to be benign.</p>';
		}
		
		#Check MalwareDomains.com Response
		if($checkMLD)
		{
			echo '<p>'.count($checkMLD).' matches on MalwareDomains.com found.</p>';
		}
		
		#Check Wepawet Response
		if($wepawetResults){
			foreach($wepawetResults as $id=>$val){
				if ($id===0){
					echo '<p>Wepawet found this link suspicious.</p>';
				}
				if ($id===1){
					echo '<p>Wepawet found this link malicious.</p>';
				}
			}
				
		}
		
		#Check Google SafeBrowsing Response
		if($googResults){
			if($googResults!=''){
				echo '<p>Google SafeBrowsing classifies this site as '.$googResults.'.</p>';
			}
		}
		
	
	echo '</div>';

	###########################
	### SUMMARY SECTION
	###########################
	
	echo '<div id="summary">';

		###########################
		### SUMMARY HEADING
		###########################

		echo '<div id="heading">';
			echo '<h1>Research Summary For:</h1>';
			echo '<h2>'.$url.'</h2>';
			echo '<h2>IPs per statdns.com: </h2>';
			if($ips!=FALSE){
				echo '<div class="ipblock">';
				foreach ($ips as $val){
					echo '<div class="ip">'.$val.'</div>';
				}
				echo '</div>';
			}
			else{
				echo '<div class="ip">No IPs resolved.</div>';
			}
			
		echo '</div>';
		
		###########################
		### VIRUSTOTAL SECTION
		###########################
		
		echo '<div id="virustotal">';
			echo '<h2>VirusTotal</h2>';
			
			if((isset($vtScan)) && ($vtScan!=FALSE)){
				foreach($vtScan as $key=>$val){
					if($val['response_code']==-1){	#Check for Error Response
						echo '<div id="vtNone">'.$val['verbose_msg'].'</div>';
						break;
					}
					else{
						echo '<div class="attribute">permalink: </div><div class="data"><a href="'.$val['permalink'].'" target="_blank">Open in new Tab</a></div>';
						echo '<div class="attribute">scan date: </div><div class="data">'.$val['scan_date'].'</div>';
						if(isset($val['positives'])){
							#GREEN Response
							if($val['positives']<5){
								echo '<div class="attribute">results: </div><div class="data" style="color:green;font-weight:900;">'.$val['positives'].' / '.$val['total'].'</div>';
							}
							#YELLOW Response
							elseif($val['positives']<10){
								echo '<div class="attribute">results: </div><div class="data" style="color:goldenrod;font-weight:900;">'.$val['positives'].' / '.$val['total'].'</div>';
							}
							#RED Response
							else{
								echo '<div class="attribute">results: </div><div class="data" style="color:red;font-weight:900;">'.$val['positives'].' / '.$val['total'].'</div>';
							}
						}
						#STILL SCANNING Response
						else{
							echo '<div class="attribute">results: </div><div class="data" style="color:blue;font-weight:900;">Scanning... Refresh Page (Hit F5) for results.</div>';
						}
					}
					
				}
			}
			elseif($vtScan===FALSE){
				echo '<div class="attribute">results: </div><div class="data" style="color:goldenrod;font-weight:900;">VirusTotal not checked.</div>';
			}
			else{	#No Results Found
				echo '<div class="attribute">results: </div><div class="data" style="color:goldenrod;font-weight:900;">No results found.</div>';
			}
		
		echo '</div>';
		
		###########################
		### WOT SECTION
		###########################
		
		echo '<div id="wot">';		
			echo '<h2>Web of Trust</h2>';
			
			#PROCESS CATEGORY			
			if(isset($wotResults['categories'])){
				#Category Header
				echo '<div class="attribute" style="font-style:italic;">Category</div><div class="data" style="font-style:italic;margin-bottom:10px;">pct certainty</div>';
				
				foreach($wotResults['categories'] as $key=>$val){
					echo '<div class="attribute">'.$key.'</div><div class="data">'.$val.'</div>';
				}
				
				echo '<br/>';
			}
			elseif($wotResults === FALSE){
				echo '<div id="wotNone">MyWOT not checked.</div>';
			}
			else{
				echo '<div id="wotNone">No category found.</div>';
			}
			
			#PROCESS BLACKLISTS
			if(isset($wotResults['blacklists'])){
				#Blacklist Header
				echo '<br/>';
				echo '<div class="attribute" style="font-style:italic;">Blacklist Type</div><div class="data" style="font-style:italic;">Last Seen</div>';
				
				foreach($wotResults['blacklists'] as $key=>$val){
					echo '<div class="attribute">'.$key.'</div><div class="data">'.date('Y-m-d',$val).'</div>';
				}
				
				echo '<br/>';
			}
		
		echo '</div>';
		
		###########################
		### Sucuri SiteCheck
		###########################
		
		echo '<div id="sucuri">';
			echo '<h2>Sucuri SiteCheck</h2>';
					echo '<div id="sucLink"><a href="http://sitecheck2.sucuri.net/results/'.$urlArray['host'].'" target="_blank">Open Results in new Tab</a>.</div>';
			if($sucuriResults!=FALSE){
				foreach($sucuriResults as $key=>$val){
					if(is_array($val)){
						if(count($val)>1){
							echo '<div id="sucList">';
						}
						
						foreach($val as $key2=>$val2){
							$clean = strstr($val2,'clean');
							$atag = strstr($val2,'</a>');
							if($clean!=FALSE){
								echo '</div><div class="sucGreen">'.$val2;
							}
							elseif($atag!=FALSE){
								echo '</div><div class="sucRed">'.$val2;
							}
							else{
								echo $val2;
							}
						}
						
						if(count($val)>1){
							echo '</div>';
						}
					}
					else{
						echo $val;
					}
				}
			}
			elseif(!isset($_POST['sucChk'])){
				echo '<div id="sucNone">Sucuri not checked. <a href="http://sitecheck2.sucuri.net/results/'.$urlArray['host'].'" target="_blank">Check in a new tab</a>.</div>';
			}
			else{
				echo '<div id="sucNone">No malicious results found.</div>';
			}
			

						
		echo '</div>';
		
		###########################
		### GOOGLE SAFEBROWSING SECTION
		###########################
		
		echo '<div id="goog">';
			echo '<h2>Google Safebrowsing</h2>';
			
			if ($googResults!=''){
				echo '<div class="attribute">Result: </div><div class="data">'.$googResults.'</div>';
			}
			elseif($googResults===FALSE){
				echo '<div id="googNone">Google SafeBrowsing not checked.</div>';
			}
			else{
				echo '<div id="googNone">No results found.</div>';
			}
		
		echo '</div>';
		
		###########################
		### MALWAREDOMAINS SECTION
		###########################
		
		echo '<div id="mld">';
		
			echo '<h2>MalwareDomains.com</h2>';
			
			if(!isset($_POST['mldChk'])){
				echo '<div id="mldNONE">MLD Not checked.</div>';
			}
			else{
				$pattern = '/(?<!\d)\d{8}(?!\d)/';
				
				echo '<pre>';
				$tmp=0;
				foreach($checkMLD as $val) if ($tmp++ < 9){	#Only Get first 10 results
					
					echo '<div class="mldres">';
					
					$mldRes = explode("\t",current($val));	#Create array from each piece of the line, tab-delimited
					$mldLine = array();
					$max = 0;
					
					foreach($mldRes as $bit){						#For each section of every line
						$bit = trim($bit,"\t");						#Remove any extraneous tabs
						$bit = preg_replace('/\s+/','',$bit);		#Remove al spaces					
						preg_match($pattern,$bit,$match);			#Search for all dates (to remove them)

						if ($match==FALSE && $bit!=''&& $bit!='#')	#Going through each piece of the line, if the piece is not a date
						{
							$mldLine[] = $bit;						#Add it to the final output
						}
					}
					
					foreach($mldLine as $key2=>$val2)				#Go through the final output, line by line
					{
						if ($key2===0){						
							echo '<div class="mldsite">'.$val2.'</div>';		
						}
						elseif ($key2===1){						
							echo '<div class="mldreason">'.$val2.'</div>';		
						}
						elseif ($key2===2){						
							echo '<div class="mldsource">'.$val2.'</div>';		
						}
						elseif ($key2===3 && $val2 == 'relisted'){						
							#echo '<div class="mldrelist">'.$val2.'</div>';		
						}
						else{
							echo '<div class="mldextra">'.$val2.'</div>';
						}
					}
					
					echo '</div>';
				}
				
				echo '</pre><br/>';
				
				if($checkMLD==FALSE){
					echo '<div id="mldNONE">No results found.</div>';
				}
			}
		
		echo '</div>';
		
		###########################
		### IPVOID SECTION
		###########################
		
		if(!isset($_POST['ipvoidChk'])){
			echo '<div id="ipvoid">';
				echo '<h2>IPVoid</h2>';
				
				echo '<div class="ipvoidRes">';
					echo 'IPVoid not checked.';
				echo '</div>';
				
			echo '</div>';
		}
		else{
			if($ipvoidResults[0]!=FALSE){
				echo '<div id="ipvoid">';
					echo '<h2>IPVoid</h2>';
					
					foreach($ipvoidResults as $key=>$val){
						echo '<div class="ipvoidRes">';
							echo '<a href="http://ipvoid.com/scan/'.key($val).'" target="_blank">'.key($val).'</a>'.' - '.current($val);
						echo '</div>';
					}
					
				echo '</div>';
			}
			else{
				echo '<div id="ipvoid">';
					echo '<h2>IPVoid</h2>';
					
					echo '<div class="ipvoidRes">';
						echo 'This IP has not been scanned by IPVoid.';
					echo '</div>';
					
				echo '</div>';
			}
		}
		
		
		###########################
		### WEPAWET SECTION
		###########################
		
		echo '<div id="wepawet">';
			echo '<h2>Wepawet</h2>';
			
			if(!isset($_POST['wepawetChk'])){
				echo 'Wepawet not checked.';
			}
			else{
				if($wepawetResults){
					foreach($wepawetResults as $val){
						echo $val;
						echo '<br/>';
					}
				}
				else
				{
					echo 'No Wepawet Results found.';
				}
			}
			
			
		echo '</div>';
		
		###########################
		### REXSWAIN SECTION
		###########################
		
		if(isset($_POST['rexChk'])){
			echo '<div id="rexswain">';
				echo '<h2>RexSwain</h2>';
				
				echo '<div id="rexshowhide" class="shframe">Show/Hide Frame</div>';
			
				echo '<iframe id="rexframe" style="display:none;" width="90%" height="500px" src="http://www.rexswain.com/cgi-bin/httpview.cgi?url='.$url.'&uag=Mozilla/5.0+(X11%3B+Ubuntu%3B+Linux+i686%3B+rv:19.0)+Gecko/20100101+Firefox/19.0&ref=http://www.rexswain.com/httpview.html&aen=&req=GET&ver=1.1&fmt=AUTO"></iframe>';
			echo '</div>';
		}
		
		
		###########################
		### URLQUERY SECTION
		###########################
		
		if(isset($_POST['urlqChk'])){
			echo '<div id="urlquery">';
				echo '<h2>URLQuery</h2>';
				
				echo '<div id="urlshowhide" class="shframe">Show/Hide Frame</div>';
				
				echo '<iframe id="urlframe" style="display:none;" width="90%" height="500px" src="http://urlquery.net/search.php?q='.$urlArray['host'].'&type=string"></iframe>';
			echo '</div>';
		}
		
		###########################
		### QUTTERA SECTION
		###########################
		
		if(isset($_POST['qtraChk'])){
			echo '<div id="quttera">';
				echo '<h2>Quttera</h2>';
				
				echo '<div id="qtrashowhide" class="shframe">Show/Hide Frame</div>';
				
				echo '<iframe id="qtraframe" style="display:none;" width="90%" height="500px" src="http://quttera.com/detailed_report/'.$urlArray['host'].'"></iframe>';
			echo '</div>';
		}
		

	echo '</div>';		#END SUMMARY SECTION


	###########################
	### BROWSER SECTION
	###########################
	if( (isset($_POST['duckChk'])) || (isset($_POST['startChk'])) ){
	
	echo '<div id="browser">';
	
		echo '<span style="text-align:center;"><h1>Web Searches</h1></span>';
		
		###########################
		### GOOGLE LINK
		###########################
		
		echo '<h2><a href="http://www.google.com/#q=%22'.$url.'%22" target="_blank">Click for Google</a></h2>';
		
		###########################
		### DUCKDUCKGO FRAME
		###########################

		if(isset($_POST['duckChk'])){
			echo '<h2>DuckDuckGo</h2>';
			
			echo '<div id="duckshowhide" class="shframe">Show/Hide Frame</div>';
			
			echo '<iframe id="duckframe" width="90%" height="500px" src="http://www.duckduckgo.com/?q=%22'.$url.'%22"></iframe>';

			echo '<br/>';
			echo '<br/>';
		}
		
		###########################
		### STARTPAGE FRAME
		###########################
		
		if(isset($_POST['startChk'])){
			echo '<h2>StartPage</h2>';
		
			echo '<div id="startshowhide" class="shframe">Show/Hide Frame</div>';
			
			echo '<iframe id="startframe" width="90%" height="500px" src="https://www.startpage.com/do/metasearch.pl?q=%22'.$url.'%22"></iframe>';

			echo '<br/>';
			echo '<br/>';
		}
		
		
		
	echo '</div>';	#End Browser Section
	}
}

?>
	</div>
	
</div>



<!-- JQuery Script -->

<script>
	$(document).ready(function(){

		// Hide/Unhide URL Submission Form

		$("#showsubmit").click(function(){
			$("#showsubmit").toggle();
			$("#hidesubmit").toggle();
			$("#forms").toggle();
		});
		
		$("#hidesubmit").click(function(){
			$("#showsubmit").toggle();
			$("#hidesubmit").toggle();
			$("#forms").toggle();
		});
		
		$("#rexshowhide").click(function(){
			$("#rexframe").toggle();
		});
		
		$("#urlshowhide").click(function(){
			$("#urlframe").toggle();
		});
		
		$("#qtrashowhide").click(function(){
			$("#qtraframe").toggle();
		});
		
		$("#duckshowhide").click(function(){
			$("#duckframe").toggle();
		});
		
		$("#startshowhide").click(function(){
			$("#startframe").toggle();
		});
		
		//Show or hide specific divs
		
		var checkChkBox = function(check,div){
			if($(check).prop('checked')){
				$(div).show();
				//alert('show - checkbox: '+check+' div: '+div);
			}
			else{
				$(div).hide();
				//alert('hide - checkbox: '+check+' div: '+div);
			}
		};
		
		$('#vtChk').change(function(){
			checkChkBox('#vtChk','#virustotal');
		});
		$('#mldChk').change(function(){
			checkChkBox('#mldChk','#mld');
		});
		$('#googChk').change(function(){
			checkChkBox('#googChk','#goog');
		});
		$('#wotChk').change(function(){
			checkChkBox('#wotChk','#wot');
		});
		$('#wepawetChk').change(function(){
			checkChkBox('#wepawetChk','#wepawet');
		});
		$('#sucChk').change(function(){
			checkChkBox('#sucChk','#sucuri');
		});
		$('#ipvoidChk').change(function(){
			checkChkBox('#ipvoidChk','#ipvoid');
		});
		$('#rexChk').change(function(){
			checkChkBox('#rexChk','#rexswain');
		});
		$('#urlqChk').change(function(){
			checkChkBox('#urlqChk','#urlquery');
		});
		$('#qtraChk').change(function(){
			checkChkBox('#qtraChk','#quttera');
		});
		
		$('#duckChk').change(function(){
			checkChkBox('#duckChk','#duckframe');
		});
		$('#startChk').change(function(){
			checkChkBox('#startChk','#startframe');
		});
		
	});

</script>

</body>
<? include './footer.php';?>
</html>
