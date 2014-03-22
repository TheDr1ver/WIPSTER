<?php
###########################
/*
 * SET VARIABLES
 */
###########################
 
session_start();

#foreach($_SESSION as $key=>$val){
	#echo $key.' => '.$val;
#}

error_reporting(E_ALL); ini_set('display_errors',1);

#echo '<pre>';

#var_dump($_SESSION);

#$arr = get_defined_vars();

#foreach($arr as $key=>$val){
	#var_dump($val);
	#echo $key.' => '.$val;
#}

#echo '</pre>';
 
#Get Txt from md5page.php
#$txtdump = $_SESSION['txtdump'];
 
#Get MD5 Variable from URL
#$idmd5=$_GET['idmd5'];

###########################
/*
 * DEFINE FUNCTIONS
 */
###########################

###########################
/*
 * RUN FUNCTIONS
 */
###########################

###########################
/*
 * BUILD PAGE
 */
###########################
 

?>

<html>
	<head>
		<style>
			#container{
				max-width:100%;
				word-wrap:break-word;
				overflow:auto;
				font-family:monospace;
				white-space:pre;
				/*text-indent:-2em;*/
				/*padding-left:2em;*/
			}
		</style>
	</head>
	
	<body>
		<div id="container"><div id="subject"><?
				if (isset($_SESSION['ticket'])){
					$lenTicket = strlen($_SESSION['ticket']);
					$zeros = 12-$lenTicket;
					$ticket = 'TICKET'.str_repeat('0',$zeros).$_SESSION['ticket'];
				}
				else{
					$ticket = 'TICKETXXXXXXXXXXXX';
				}
				?>
<? echo $ticket;?>, Analysis Complete, Non Incident
<? echo $ticket;?>, Analysis Complete, SPAM
<? echo $ticket;?>, Analysis Complete, SEP Detected
<? echo $ticket;?>, Analysis Complete, Malcode Detected

Reason for Ticket/Alert			:	
			</div><div id="callouts"><?
					if ((isset($_SESSION['anubisDNS'])) && ($_SESSION['anubis']!=FALSE)){
						$dns=array();
						$ip=array();
						foreach($_SESSION['anubisDNS'] as $key=>$val){
							$dns[]=$val['@attributes']['name'];
							$ip[]=$val['@attributes']['result'];
						}
					}
					else{
						$dns[0]='';
						$ip[0]='';
					}
					
					foreach($dns as $key=>$val){
						if ($key===0){
							$dnsStr=$val;
						}
						else{
							$dnsStr.= ", ".$val;
						}
						
					}
					$dnsStr = str_replace('.','[.]',$dnsStr);
					
					foreach($ip as $key=>$val){
						if ($key===0){
							$ipStr = $val;
						}
						else{
							$ipStr.=", ".$val;
						}
						
					}
					
					
				?>
DNS Callout				:	<? echo $dnsStr; ?>

IP Callout				:	<? echo $ipStr; ?>

Block/Blackhole				:</div><? 
				if(isset($_SESSION['filename'])){
					$filename = $_SESSION['filename'];
				}
				else{
					$filename = '';
				}
				if(isset($_SESSION['md5'])){
					$md5 = $_SESSION['md5'];
				}
				else{
					$md5 = '';
				}
				if(isset($_SESSION['size'])){
					$size = $_SESSION['size'];
				}
				else{
					$size = '';
				}
				if(isset($_SESSION['type'])){
					$type = $_SESSION['type'];
				}
				else{
					$type = '';
				}
			?><div id="files">
File					:	<? echo $filename."\n"; ?>
MD5					:	<? echo $md5."\n"; ?>
Size					:	<? echo $size."\n"; ?>
Path					:
Type					:	<? echo $type."\n"; ?>
CVE					:
</div><div id="links">
Link(s)					:
</div><div id="av">
<?
				if(isset($_SESSION['virusTotal']['results'])){
					$vtRes = $_SESSION['virusTotal']['results'];
				}
				else{
					$vtRes = "Not Found\n";
				}
				
				if(isset($_SESSION['virusTotal']['sep'])){
					$vtSep = $_SESSION['virusTotal']['sep'];
					$vtSep = preg_replace('/\s+/',' ',$vtSep);
				}
				else{
					$vtSep = '';
				}
				
				if(isset($_SESSION['virusTotal']['mse'])){
					$vtMse = $_SESSION['virusTotal']['mse'];
					$vtMse = preg_replace('/\s+/',' ',$vtMse);
				}
				else{
					$vtMse = '';
				}
				$caught = $vtRes != "Not Found\n" ? 'Yes' : "";			
				?>
Caught by AV (YES/NO)			:	<? echo $caught."\n";?>
	VT				:	<? echo $vtRes; ?>
	SEP				:	<? echo $vtSep."\n"; ?>
	MSE				:	<? echo $vtMse; ?>
</div><div id="email">
Email Info				:
</div><div id="summary">
--SUMMARY--
			
			
			
			</div>
			<div id="remediation">
--REMEDIATION STEPS--

- False Positive

- Block the following:
	Block:
	Justification:

- Search for successful callouts

			
			</div>
			
			<div id="notes">
			
--NOTES---

<?
$vttxt = @file('./mastiff/'.$md5.'/virustotal.txt');
if($vttxt){
	echo '##### VIRUSTOTAL #####'."\n";
	foreach($vttxt as $line_num=>$line){
		echo $line;
	}
}

$metadata = @file('./mastiff/'.$md5.'/metadata.txt');
if($metadata){
	echo "\n".'##### METADATA #####'."\n";
	foreach($metadata as $line_num=>$line){
		echo $line;
	}
}

if(isset($filename)){
	$exif = shell_exec('exiftool "/var/www/mastiff/'.$md5.'/'.$filename.'"');
	if (!is_null($exif)){
		echo "\n".'##### EXIFDATA #####'."\n";
		echo $exif;
	}
}


if(isset($filename)){
	$trid = shell_exec('trid -d:/usr/local/lib/triddefs.trd "/var/www/mastiff/'.$md5.'/'.$filename.'"');
	if (!is_null($trid)){
		echo "\n".'##### FILE TYPE INFO (from TrID) #####'."\n";
		echo $trid;
	}
}

$yara = @file('./mastiff/'.$md5.'/yara.txt');
if($yara){
	echo "\n".'##### YARA #####'."\n";
	foreach($yara as $line_num=>$line){
		echo $line;
	}
}

$pdfid = @file('./mastiff/'.$md5.'/pdfid.txt');
if($pdfid){
	echo "\n".'##### PDFID #####'."\n";
	foreach($pdfid as $line_num=>$line){
		echo $line;
	}
}

$pescan = @file('./mastiff/'.$md5.'/peinfo-quick.txt');
if($pescan){
	echo "\n".'##### PESCANNER RESULTS #####'."\n";
	foreach($pescan as $line_num=>$line){
		echo $line;
	}
}

$offscan = @file('./mastiff/'.$md5.'/office-analysis.txt');
if($offscan){
	echo "\n".'##### OLE SCANNER RESULTS #####'."\n";
	foreach($offscan as $line_num=>$line){
		echo $line;
	}
}

$dexor = @file('./mastiff/'.$md5.'/xor/dexorstrings.txt');
if($dexor){
	echo "\n".'##### DE-XOR\'D STRINGS #####'."\n";
	foreach($dexor as $line_num=>$line){
		echo htmlentities($line);
	}
}

$strings = @file('./mastiff/'.$md5.'/strings.txt');
if($strings){
	echo "\n".'##### STRINGS #####'."\n";
	foreach($strings as $line_num=>$line){
		echo htmlentities($line);
	}
}


##### GENERAL FILE INFO #####

##### SUSPICIOUS APIS #####

##### OFFICE MALSCANNER #####

##### INTERESTING STRINGS #####

##### XOR STRINGS FOUND #####

?>
			
			</div>
			
		</div>
	</body>
</html>


<?php

?>
