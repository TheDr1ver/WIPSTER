<?


//Save changes
$dbUpdate=array();

if(isset($_POST['remver'])){
	#Update the database
	$newSettings=$_POST;
	$db = new SQLite3('./admin/admin.db');
	#$insert="'".$malwrDBArray['status']."', '".$malwrDBArray['sha256']."', '".$malwrDBArray['md5']."', '".$malwrDBArray['uuid']."'";
	#$result = $db->exec('INSERT INTO malwr (status, sha256, md5, uuid) VALUES ('.$insert.') ');
	#$dbKeys = '';
	#$insert = '';
	$update='';
	
	#Set FALSE for checkboxes not present
	
	if(!isset($newSettings['malwrPlugin'])){
		$newSettings['malwrPlugin']='false';
	}
	
	if(!isset($newSettings['threatanalyzerplugin'])){
		$newSettings['threatanalyzerplugin']='false';
	}
	
	if(!isset($newSettings['tasubreanalyze'])){
		$newSettings['tasubreanalyze']='false';
	}
	
	if(isset($newSettings['threatbase'])){
		$newSettings['threatpage']="http://".$newSettings['threatbase']."/api/v1";
	}
	
	
	foreach($newSettings as $key=>$val){
		if($key!='submit'){
			
			if($key!='tasubreanalyze'){
				if($val=='true'){
					$val=1;
				}
				elseif($val=='false'){
					$val=0;
				}
			}
			
			#$dbKeys = $db->escapeString($dbKeys);#Sanitize for Sqlite
			#$insert = $db->escapeString($insert);#Sanitize for Sqlite
			$key = $db->escapeString($key);
			$val = $db->escapeString($val);
			
			#$dbKeys=$dbKeys.$key.', ';
			#$insert=$insert."'".$val."', ";
			$update=$update.$key."='".$val."', ";
			
		}
	}
	#$dbKeys = rtrim($dbKeys, ', '); #Trim last , from $dbKeys
	#$insert = rtrim($insert, ', '); #Trim , ' from $insert
	$update = rtrim($update, ', '); #Trim , ' from $insert
	
	#$update = $db->escapeString($update);#Sanitize for Sqlite
	#$result = $db->exec('INSERT INTO admin ('.$dbKeys.') VALUES ('.$insert.') ');
	$result = $db->exec('UPDATE admin SET '.$update.' WHERE id=1');
	#echo '$dbKeys: '.$dbKeys;
	#echo '$insert: '.$insert;
	#echo '$update: <br/>'.$update;
	
	if(!$result){
		$adminRes['db']=$db->lastErrorMsg();
		echo "<script>alert('ERROR".$adminRes['db']."');</script>";
	}
	else{
		$adminRes['db']=$db->changes().' Record updated successfully.';
		echo "<script>alert('Settings Saved Successfully');</script>";
	}

	#echo "<b>RESULT:</b><br/>";
	#echo '<pre>';
	#var_dump($adminRes);
	#echo '</pre>';
	
	$db->close();
}#End isset $_POST

//Get existing values in Database
function getSettings(){
	$db = new SQLite3('./admin/admin.db');
	$result = $db->query('SELECT * FROM admin WHERE id = 1');
	
	if(isset($result))
	{
		while ($res=$result->fetchArray()){
			#$_SESSION['size']=$res['size'];
			#$malwrRes['uuid']=$res['uuid'];
			$settingRes=array();
			$settingRes=$res;
			/*	remver
			 * 	mastiffconf
			 * 	mastiffpy
			 * 	tridloc
			 * 	malwrplugin
			 * 	malwrapi
			 * 	threatanalyzerplugin
			 * 	threatapi
			 * 	threatbase
			 * 	threatpage
			 * 	threatargs
			 * 	tasubpriority
			 * 	tasubsandbox
			 * 	tasubreanalyze
			 * 	anubisuser
			 * 	anubispass
			 * 	wotapi
			 * 	vtapi
			 * 	googapi
			 * 	gcsekey
			 * 	gcsesig
			 * 	gcsecx
			 * 	gcsequery
			 * 	autopbua
			 * 	twitterapi
			 * 	twittertoken
			 * 	twitterquery
			 * 	twitterconsec
			 * 	twitteroauthsec
			 */
			
		}
	}
	if(!$result){
		$settingRes['db']=$db->lastErrorMsg();
	}
	else{
		$settingRes['db']=$db->changes().' Record updated successfully.';
	}
	$db->close();
	return $settingRes;
}

$settingRes=getSettings();

if(isset($_GET['update'])){
	$command[]="cd /var/www/";
	$command[]="mkdir tmpupdatebackup";
	$command[]="cd /var/www/tmpupdatebackup/";
	#Backup DB's
	$command[]="cp /var/www/admin/admin.db /var/www/tmpupdatebackup";
	$command[]="cp /var/www/urls/urls.db /var/www/tmpupdatebackup";
	$command[]="cp /var/www/autopb/autopb.db /var/www/tmpupdatebackup";
	$command[]="cp /var/www/malwr/malwr.db /var/www/tmpupdatebackup";
	$command[]="cp /var/www/mastiff/mastiff.db /var/www/tmpupdatebackup";
	$command[]="cp /var/www/twitter/twitter.db /var/www/tmpupdatebackup";
	#Get new WIPSTER files
	$command[]="wget https://github.com/TheDr1ver/WIPSTER/archive/master.zip";
	$command[]="unzip ./master.zip";
	$command[]="/bin/cp -rf ./WIPSTER-master/* /var/www/";
	#Restore DB's
	$command[]="mv -f /var/www/tmpupdatebackup/admin.db /var/www/admin/admin.db";
	$command[]="mv -f /var/www/tmpupdatebackup/urls.db /var/www/urls/urls.db";
	$command[]="mv -f /var/www/tmpupdatebackup/autopb.db /var/www/autopb/autopb.db";
	$command[]="mv -f /var/www/tmpupdatebackup/malwr.db /var/www/malwr/malwr.db";
	$command[]="mv -f /var/www/tmpupdatebackup/mastiff.db /var/www/mastiff/mastiff.db";
	$command[]="mv -f /var/www/tmpupdatebackup/twitter.db /var/www/twitter/twitter.db";
	#Set permissions
	$command[]="chown -R www-data:www-data /var/www/";
	$command[]="cd /var/www/";
	#Remove temp folder
	$command[]="rm -rf /var/www/tmpupdatebackup/";
	foreach($command as $key=>$val){
		$runcmd = shell_exec($val);
	}
	echo "<script>alert('Update complete!');</script>";
}
if(isset($_GET['backup'])){
	$command[]="cd /var/www/admin/ && zip -r WIPSTER-backup /var/www/*";
	foreach($command as $key=>$val){
		$runcmd = shell_exec($val);
	}
	header('Location: ./admin/WIPSTER-backup.zip');
	die();
}
if(isset($_GET['configs'])){
	header('Location: ./admin/admin.db');
	die();
}

?>


<html>
	<head>
		<title>WIPSTER Administration Console</title>
		<style>
			p{
				/*display:inline-block;*/
			}
			input{
				/*display:inline-block;*/
			}
			#header{
				margin-left:auto;
				margin-right:auto;
				text-align:center;
			}
			#maincontent{
				width:100%;
				margin-left:auto;
				margin-right:auto;
				align-content:center;
			}
			#remver{
				margin-left:auto;
				margin-right:auto;
				width:400px;
				text-align:center;
			}
			#subcontent{
				width:75%;
				margin-left:auto;
				margin-right:auto;
			}
			.box{
				width:400px;
				min-height:300px;
				margin-left:auto;
				margin-right:auto;
				display:inline-block;
				vertical-align:top;
				float:left;
			}
			#submit{
				text-align:center;
				align-content:center;
				width:100px;
				margin-left:auto;
				margin-right:auto;
			}
		</style>
	</head>
	<body>
		<div id="container">
			<div id="header">
					<h1>WIPSTER Administration Console</h1>
					<p><a href="./admin.php?backup=1">Backup WIPSTER (This may take a while)</a> | <a href="./admin.php?configs=1">Download Configs</a> | <a href="./admin.php?update=1" onclick="return confirm('Are you sure you want to update all files in the WIPSTER framework? Your configurations and existing files will be saved.')">Update WIPSTER (Saves configs and downloads most recent files from GitHub)</a></p>
			</div><!--end header-->
			<div id="maincontent">
				
				<form action="./admin.php" method="post">
				
				<div id="remver">
					<h2>REMnux Version</h2>
				<?
				if ($settingRes['remver']=='4'){
					echo '<input type="radio" name="remver" value="4" checked/>REMnux v4';
					echo '<input type="radio" name="remver" value="5" />REMnux v5';
				}
				elseif($settingRes['remver']=='5'){
					echo '<input type="radio" name="remver" value="4" />REMnux v4';
					echo '<input type="radio" name="remver" value="5" checked/>REMnux v5';
				}
				else{
					echo '<input type="radio" name="remver" value="4"/>REMnux v4';
					echo '<input type="radio" name="remver" value="5"/>REMnux v5';
				}
				?>
				</div><!--end remver-->
				<div id="subcontent">
				<div id="mastiff" class="box">
					<h2>MASTIFF Settings</h2>
					<p>Location of the MASTIFF config file: </p>
					<input type="text" name="mastiffconf" size="25" value="<?echo $settingRes['mastiffconf'];?>" />
					<p>Location of the MASTIFF python script: </p>
					<input type="text" name="mastiffpy" size="25" value="<?echo $settingRes['mastiffpy'];?>" />
				</div><!--end mastiff-->
				<div id="trid" class="box">
					<h2>TRiD Settings</h2>
					<p>Location of TRid: </p>
					<input type="text" name="tridloc" size="25" value="<?echo $settingRes['tridloc'];?>" />
				</div><!--end trid-->
				<div id="malwr" class="box">
					<h2>Malwr.com Settings</h2>
					<?
					if($settingRes['malwrPlugin']===1){
						echo '<input type="checkbox" name="malwrPlugin" id="malwrPlugin" value="true" checked/>Malwr';
					}
					else{
						echo '<input type="checkbox" name="malwrPlugin" id="malwrPlugin" value="true"/>Malwr';
					}
					
					?>
					<p>Malwr.com API: </p>
					<input type="text" name="malwrAPI" size="25" value="<?echo $settingRes['malwrAPI'];?>" />
					
				</div><!--end malwr-->
				<div id="threatanalyzer" class="box">
					<h2>ThreatAnalyzer Settings</h2>
					<?
					if($settingRes['threatanalyzerplugin']===1){
						echo '<input type="checkbox" name="threatanalyzerplugin" id="threatanalyzerplugin" value="true" checked/>Use ThreatAnalyzer';
					}
					else{
						echo '<input type="checkbox" name="threatanalyzerplugin" id="threatanalyzerplugin" value="true"/>Use ThreatAnalyzer';
					}
					?>
					<p>ThreatAnalyzer API: </p>
					<input type="text" name="threatapi" size="25" value="<?echo $settingRes['threatapi'];?>" />
					<p>ThreatAnalyzer Base IP: </p>
					<input type="text" name="threatbase" size="25" value="<?echo $settingRes['threatbase'];?>" />
					<p>ThreatAnalyzer Submission Priority: </p>
					<input type="text" name="tasubpriority" size="25" value="<?echo $settingRes['tasubpriority'];?>" />
					<p>ThreatAnalyzer Sandbox for Submission: </p>
					<input type="text" name="tasubsandbox" size="25" value="<?echo $settingRes['tasubsandbox'];?>" />
					<p>ThreatAnalyzer Custom Action Name: </p>
					<input type="text" name="tasubcustomname" size="25" value="<?echo $settingRes['tasubcustomname'];?>" />
					<p>ThreatAnalyzer Custom Action Value: </p>
					<input type="text" name="tasubcustomval" size="25" value="<?echo $settingRes['tasubcustomval'];?>" />
					<p>
					<?
					if($settingRes['tasubreanalyze']=='false'){
						echo '<input type="checkbox" name="tasubreanalyze" id="tasubreanalyze" value="true"/>';
					}
					else{
						echo '<input type="checkbox" name="tasubreanalyze" id="tasubreanalyze" value="true" checked/>';
					}
					?>
					Reanalyze previously submitted files</p>
					
				</div><!--end threatanalyzer-->
				<div id="anubis" class="box">
					<h2>Anubis Settings</h2>
					<p>Anubis Username: </p>
					<input type="text" name="anubisuser" size="25" value="<?echo $settingRes['anubisuser'];?>" />
					<p>Anubis Password: </p>
					<input type="password" name="anubispass" size="25" value="<?echo $settingRes['anubispass'];?>" />
				</div><!--end anubis-->
				
				<div id="urls" class="box">
					<h2>Open Source URL Checkers</h2>
					<p>MyWOT API: </p>
					<input type="text" name="wotapi" size="25" value="<?echo $settingRes['wotapi'];?>" />
					<p>VirusTotal API: </p>
					<input type="text" name="vtapi" size="25" value="<?echo $settingRes['vtapi'];?>" />
					<p>MyWOT API: </p>
					<input type="text" name="googapi" size="25" value="<?echo $settingRes['googapi'];?>" />
				</div><!--end urls-->
				
				<div id="pastebin" class="box">
					<h2>Pastebin Checker Settings</h2>
					<p>Google Custom Search Engine API Key: </p>
					<input type="text" name="gcsekey" size="25" value="<?echo $settingRes['gcsekey'];?>" />
					<p>Google Custom Search Engine Signature Key: </p>
					<input type="text" name="gcsesig" size="25" value="<?echo $settingRes['gcsesig'];?>" />
					<p>Google Custom Search Engine ID: </p>
					<input type="text" name="gcsecx" size="25" value="<?echo $settingRes['gcsecx'];?>" />
					<p>Google Custom Search Engine Query: </p>
					<input type="text" name="gcsequery" size="25" value="<?echo $settingRes['gcsequery'];?>" />
					<p>User Agent for automated Pastebin Checker: </p>
					<input type="text" name="autopbua" size="25" value="<?echo $settingRes['autopbua'];?>" />
				</div><!--end pastebin-->
				
				<div id="twitter" class="box">
					<h2>Twitter Settings</h2>
					<p>Twitter API Key: </p>
					<input type="text" name="twitterapi" size="25" value="<?echo $settingRes['twitterapi'];?>" />
					<p>Twitter Access Token: </p>
					<input type="text" name="twittertoken" size="25" value="<?echo $settingRes['twittertoken'];?>" />
					<p>Twitter Query: </p>
					<input type="text" name="twitterquery" size="25" value="<?echo $settingRes['twitterquery'];?>" />
					<p>Twitter Consumer Secret: </p>
					<input type="text" name="twitterconsec" size="25" value="<?echo $settingRes['twitterconsec'];?>" />
					<p>Twitter Access Token Secret: </p>
					<input type="text" name="twitteroauthsec" size="25" value="<?echo $settingRes['twitteroauthsec'];?>" />
				</div><!--end twitter-->
				</div><!--End subcontent-->
				<div id="submit"><input type="submit" name="submit" value="Save" style="width:100px;height:50px;font-weight:bold;"/></div>
				</form>
				<br/><br/><br/><br/><br/><br/>
				
			</div><!--End maincontent-->
		</div><!--End Container-->
		<?include('./footer.php');?>
	</body>
</html>
