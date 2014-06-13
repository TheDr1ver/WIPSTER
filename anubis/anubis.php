<?php

###########################
/*
 * SET VARIABLES
 */
###########################

#Anubis-specific Variables - set these to your Anubis Account
#If you do not have an Anubis account, request one at https://anubis.iseclab.org/?action=register
include ('../func/config.php');

 
#Get Variables from URL
if(isset($_GET['idmd5'])){
	$idmd5=$_GET['idmd5'];
	$fileName=$_GET['fileName'];
}

if(isset($_GET['url'])){
	$url=$_GET['url'];
}

###########################
/*
 * DEFINE FUNCTIONS
 */
###########################

function anubisFILE($idmd5,$fileName){
	
	#Execute the Python Script
	#python /var/www/anubis/submit_to_anubis.py /var/www/mastiff/MD5/filename.VIR
	$command='python /var/www/anubis/submit_to_anubis.py -u '.$anubisUser.' -p '.$anubisPass.' "/var/www/mastiff/'.$idmd5.'/'.$fileName.'"';
	$output = shell_exec($command);
	$anubisRes['out']=$output;
	
	$pattern='/https?\:\/\/[^\" ]+/i';
	preg_match($pattern, $output, $matches);
	#echo '<pre>';
	#	echo '$matches: ';
	#	var_dump($matches);
	#echo '</pre>';
	$anubisLink = $matches[0];
	$anubisLink = strstr($anubisLink, "\n", true);
	$anubisRes['link']=$anubisLink;
		
	#Update the Database
	$db = new SQLite3('../mastiff/mastiff.db');
	
	$result = $db->exec('UPDATE mastiff SET anubis = "'.$anubisLink.'" WHERE md5 = "'.$idmd5.'"');
	
	if(!$result){
		$anubisRes['db']=$db->lastErrorMsg();
	}
	else{
		$anubisRes['db']=$db->changes().' Record updated successfully.';
	}
	
	return $anubisRes;
}

function anubisURL($url){
	
	#Execute the Python Script
	
	#Update the Database
	
}

###########################
/*
 * RUN FUNCTIONS
 */
###########################
if(isset($url)){
	$anubisRes=anubisURL($url);
}

if(isset($idmd5)){
	$anubisRes=anubisFILE($idmd5,$fileName);
	#echo '<pre>';
	#var_dump($anubisRes);
	#echo '</pre>';
}


###########################
/*
 * BUILD PAGE
 */
###########################

?>

<html>

	<head>
	<title>Anubis Submission Results for <? echo $fileName;?></title>
	</head>
	
	<body>

		<div id="container">

			<div id="header">
				<h1>Anubis Submission Results for <? echo $fileName;?></h1>
				<a href="../md5page.php?idmd5=<? echo $idmd5;?>">Back to MD5 Analysis</a>
			</div>
			
			<div id="summary">
				<p><b>Server Output:</b> <? echo $anubisRes['out']?></p>
				<p><b>Permalink:</b> <a href="<?echo $anubisRes['link']?>" target="_blank"><?echo $anubisRes['link']?></a></p>
				<p><b>Database Output:</b> <? echo $anubisRes['db']?></p>
			</div>
			

		</div>
	<? include '../footer.php';?>
	</body>
<script>
setTimeout(function(){
	window.location.assign("../md5page.php?idmd5=<? echo $idmd5;?>");
}, 5000);
</script>
</html>
