<?php
###########################
### GET FILES
###########################

function getFiles($idmd5){

	#Get files listed in ./mastiff/$idmd5
	
	$lsFiles=array();
	$lsExtract=array();

	$txtFiles = array();
	$virFiles = array();
	$logFiles = array();
	$xorFiles = array();
	$b64Files = array();
	
	$results = array();
	
	$output = shell_exec('ls -t ./mastiff/'.$idmd5);
	$lsFiles = preg_split('/[\r\n]+/',$output, -1, PREG_SPLIT_NO_EMPTY);
	
	$extOutput = shell_exec('ls ./mastiff/'.$idmd5.'/resources');
	$lsExtract = preg_split('/[\r\n]+/',$extOutput, -1, PREG_SPLIT_NO_EMPTY);
			
	#Break into separate arrays
	
	foreach ($lsFiles as $key => $val)
	{
		$fileExt = substr($val, -4);
		$fileExt = strtolower($fileExt);
		
		if ($fileExt === '.txt')
		{
			array_push($txtFiles, $val);
		}
		if ($fileExt === '.vir')
		{
			array_push($virFiles, $val);
		}
		if ($fileExt === '.log')
		{
			array_push($logFiles, $val);
		}
		#echo '$val: ';
		#echo $val;
		if ($fileExt === '.b64'){
			array_push($b64Files, $val);
		}
		
		if ($val == 'xor'){
			$xorOutput = shell_exec('ls ./mastiff/'.$idmd5.'/xor');
			$lsXor = preg_split('/[\r\n]+/',$xorOutput, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($lsXor as $xval){
				#array_push($xorFiles, $xval);
				#echo '$xval: ';
				#echo $xval;
				$fileExt = substr($xval, -4);
				$fileExt = strtolower($fileExt);
				if ($fileExt === '.txt'){
					array_push($xorFiles, $xval);
				}
			}
		}
	} 
	
	

	$results['txt']=$txtFiles;
	$results['vir']=$virFiles;
	$results['log']=$logFiles;
	$results['xor']=$xorFiles;
	$results['b64']=$b64Files;
	if(isset($lsExtract))
	{
		$results['extract']=$lsExtract;
	}
	return $results;
}

###########################
### RELATED TICKETS
###########################

function relatedTix($idmd5)
{
	$mastiff=array();
	$related=array();
	$db = new SQLite3('./mastiff/mastiff.db');
	
	$result = $db->query('SELECT id, md5 FROM mastiff WHERE md5 = "'.$idmd5.'"');
	if(isset($result))
	{
		while ($res=$result->fetchArray()){
			$mastiff['id']=$res['id'];
		}
	}
	
	if(isset($mastiff['id']))
	{
	
		$c=0;
		
		$result = $db->query('SELECT * FROM files WHERE sid = '.$mastiff['id']);
		while ($res=$result->fetchArray()){
			$related[$c]['id']=$res['id'];
			$related[$c]['sid']=$res['sid'];
			$related[$c]['ticket']=$res['ticket'];
			$related[$c]['notes']=$res['notes'];
			$related[$c]['lastseen']=$res['lastseen'];
			$related[$c]['filename']=$res['filename'];
			$_SESSION['size']=$res['size'];
			$c++;
		}
	}
	
	return $related;
}

###########################
### VIRUSTOTAL
###########################

function virusTotal($idmd5)
{
	$virusTotal = array();
	$myfile = './mastiff/'.$idmd5.'/virustotal.txt';
	$file_handle = @fopen($myfile, 'r'); #@ hides Warning output
	
	
	if($file_handle!=FALSE)
	{
		while (!feof($file_handle))	#Go line-by-line in virustotal.txt
		{
			$line = fgets($file_handle);
			
			if ($line=='File not submitted because submission disabled.')
			{

				fclose($file_handle);
				return FALSE;
			}
			
			else
			{
				if(preg_match("/Last scan/i",$line))
				{
					$virusTotal['date']=$line;
				}
				if(preg_match("/Total positive/i",$line))
				{
					$virusTotal['results']=$line;
				}
				if(preg_match("/https/i",$line))
				{
					$virusTotal['link']=$line;
				}
				if(preg_match("/Symantec/i",$line))
				{
					$virusTotal['sep']=$line;
				}
				if(preg_match("/Microsoft/i",$line))
				{
					$virusTotal['mse']=$line;
				}
			}
			
			
			
		}
	}
	else
	{
		$virusTotal['submit']=FALSE;
		
	}
	if ($file_handle)
	{
		fclose($file_handle);
	}
	return $virusTotal;
}

###########################
### ANUBIS
###########################

function anubisSubmit($idmd5){
	
	$anubis='';
	
	$db = new SQLite3('./mastiff/mastiff.db');
	
	$result = $db->query('SELECT anubis FROM mastiff WHERE md5 = "'.$idmd5.'"');
	if(isset($result))
	{
		while ($res=$result->fetchArray()){
			$anubis=$res['anubis'];
		}
	}
	
	return $anubis;
	
}

###########################
### ZIP CONTENTS
###########################

function zipContents($idmd5)
{

	$zipContents = array();
	$myfile = './mastiff/'.$idmd5.'/zipinfo.txt';
	$file_handle = @fopen($myfile, 'r'); #@ hides Warning output
	
	
	if($file_handle!=FALSE)
	{
		#Define Database
		$db = new SQLite3('./mastiff/mastiff.db');

		#Get all filenames of items extracted from this .zip
		$result = $db->query('SELECT * FROM files WHERE filename LIKE "%'.$idmd5.'%"');

		$i=0;
		
		while ($res=$result->fetchArray()){
			$zipContents[$i]['sid'] = $res['sid'];
			$zipContents[$i]['filename'] = $res['filename'];
			$zipContents[$i]['size'] = $res['size'];
			
			$i++;
		}
		
		$i=0;
		#echo '<pre>'.var_dump($zipContents).'</pre>';
		foreach($zipContents as $val)
		{
			$result = ($db->querySingle('SELECT md5 FROM mastiff WHERE id = '.$val['sid']));
			#echo 'Result: '.$result['md5'].'<br/>';
			#echo '<pre>'.var_dump($result).'</pre>';
			$zipContents[$i]['md5'] = $result;
			$i++;
		}
		
	}	
	else
	{
		$zipContents=FALSE;
	}
	if ($file_handle)
	{
		fclose($file_handle);
	}
	return $zipContents;
	
}

###########################
### PCAP FORMATTING
###########################

function pcapFormat($idmd5, $fileArrays)
{
	$pcapFormat=array();
	#$filename = $fileArrays['vir'][0];
	
	$ext = strtolower($fileArrays['vir'][0]);
	$ext = substr($ext, -8, -4);
	
	if ($ext=='pcap')
	{
		$pcapFormat['ispcap']=TRUE;
	}
	else 
	{
		$pcapFormat['ispcap']=FALSE;
	}
	
	if ($pcapFormat['ispcap']==TRUE)
	{
		
		#$myfile = './mastiff/'.$idmd5.'/strings.txt';
		$myfile = './mastiff/'.$idmd5.'/tcpflow.txt';
		$fileContent = @file_get_contents($myfile);
		
		if($fileContent!=FALSE)
		{
			$mailPattern = array();
			$mailPattern[0] = '/.*@.*\..*/i';
			$mailPattern[1] = '/MAIL\s/i';
			#$mailPattern[2] = '/.*QUIT/';
			$mailPattern[2] = '/.*Received.+/';
			
			$isMail = TRUE;
			
			foreach($mailPattern as $mailVal)	#Check if the PCAP is mail traffic
			{
				preg_match($mailVal,$fileContent,$match);
				if(!isset($match[0]))
				{
					$isMail = FALSE;
				}
				
			}
			
			if ($isMail == FALSE)	#If not mail traffic, check if it's web traffic
			{
				$trafficPattern = array();
				$trafficPattern[0] = '/Content-Type/i';
				$trafficPattern[1] = '/Content-Length/i';
				$trafficPattern[2] = '/Host/i';
				
				$isWeb = TRUE;
				
				foreach($trafficPattern as $trafficVal)
				{
					preg_match($trafficVal,$fileContent,$match);
					if(!isset($match[0]))
					{
						$isWeb = FALSE;
					}
					
				}
			}
			
			if ($isMail == TRUE)
			{
				$pcapFormat['type']='email (<a href="./mastiff/'.$idmd5.'/tcpflow.txt" target="_blank">View TCP Stream</a>)';
			}
			
			elseif ($isWeb == TRUE)
			{
				$pcapFormat['type']='web (<a href="./mastiff/'.$idmd5.'/tcpflow.txt" target="_blank">View TCP Stream</a>)';
			}
			
			else
			{
				$pcapFormat['type']='unknown (<a href="./mastiff/'.$idmd5.'/tcpflow.txt" target="_blank">View TCP Stream</a>)';
			}
			
			#Search for Base64 Content
			$base64Content = file($myfile, FILE_SKIP_EMPTY_LINES);
			
			#$base64Chk = '/base64/';
			$base64Chk = '/Content\-Transfer\-Encoding\:\sbase64/';
			$base64firstPat = '/\b(?:[A-Za-z0-9+\/]{4}){14,}(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})/m';
			#$base64Pattern = '/A\s?\b((?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))/m';
			#$base64Pattern = '/\b((?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))/m';
			#$base64Pattern = '/\b((?:[A-Za-z0-9+\/])*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4}))/m';
			#$base64Pattern = '/\b(?:[A-Za-z0-9+\/])*/m';
			$base64Pattern = '/[^A-Za-z0-9+\/]/';
			$end64 = '/^\-\-/';
			
			
			#echo '<pre>';
			
			
			#Look for all occurences of exact match for 'Content-Transfer-Encoding: base64'
			$i=0;
			$baseheadcheck=array();
			foreach($base64Content as $head_line_num=>$head_line){
				$baseheadmatch = preg_match($base64Chk,$head_line,$matchhead, PREG_OFFSET_CAPTURE);
				if($baseheadmatch==1){
					$baseheadcheck[$i] = $head_line_num;
					$i++;
					#echo $baseheadcheck; #Return line numbers of each instance of 'Content-Transfer-Encoding: base64'
				}
			}
			
			if(!empty($baseheadcheck)){	#If 'base64' was found in the header
				$block64=array();
				
				#Avoid any extraneous base64 strings in the headers
				foreach($baseheadcheck as $begline_num=>$begline){	#Go through each instance where 'base64' was found
					$firstcodeline=-1;
					foreach($base64Content as $line_num=>$line){	#Go through each line in the stream
						#if ($line_num==count($base64Content)-1) break;	#Leave loop if comparing the last line of the file
						if ($line_num<$begline) continue; # Don't start looking for base64 code until content-type: base64 is seen
						$firstmatch = preg_match($base64firstPat,$line,$match1, PREG_OFFSET_CAPTURE);
						if($firstmatch==1){
							$firstcodeline=$line_num;
						}
						if($firstcodeline===-1) continue;	#If it hasn't found the first line of base64 code, go to the next line
						#See if the line has double dashes in it, signifying the end of the base64 block
						$endmatch = preg_match($end64, $base64Content[$line_num],$matchend, PREG_OFFSET_CAPTURE);
						if($endmatch==1){
							break 1; #if double-dashes exist, end the base64 block
						}
						#$line = preg_replace($base64Pattern, '', $line);
						$line = trim(preg_replace('/\s+/', '', $line));
						$block64[$begline_num][]=$line;	#Otherwise, add the line to the block of code
						
						
						
					}#end cycling through content
				}#end base64 lines
				#echo '<pre>';
				#print_r($block64);
				#echo '</pre>';
				foreach($block64 as $key=>$val){
					#echo $block64[$key][0];
					#echo '<br/>';
					#echo end($block64[$key]);
					$block64[$key]=implode($val);
					$block64[$key]=str_replace("\s", "", $block64[$key]);
				}
				$pcapFormat['block']=$block64;
				
			}
			
			
									
		}
		
		else
		{
			$pcapFormat['type']='Processing PCAP... Hit Refresh (F5) to review.<br/>';
			#$cmd1= 'sudo chown -R www-data:www-data "./mastiff/'.$idmd5.'/" ';
			#$output=shell_exec($cmd1);
			#echo $output;
			
			$cmd = '/usr/sbin/tcpick -r "./mastiff/'.$idmd5.'/'.$fileArrays['vir'][0].'" -bR > "./mastiff/'.$idmd5.'/tcpflow.txt"';
			#$cmd = 'tcpflow -r "./mastiff/'.$idmd5.'/'.$fileArrays['vir'][0].'" -C > "./mastiff/'.$idmd5.'/tcpflow.txt"';
			#echo 'Command: '.$cmd;
			$output=shell_exec($cmd);
			#echo var_dump($output);
		}
		
	}
	
	else
	{
		$pcapFormat['ispcap']=FALSE;
	}
	
	
	return $pcapFormat;
}

?>
