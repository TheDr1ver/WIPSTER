<?php

###########################
/*
 * SET VARIABLES
 */
###########################

#Get MD5 Variable from URL
$idmd5=$_GET['idmd5'];

#Get PCAP Type from URL
$type=$_GET['type'];

###########################
/*
 * BUILD FUNCTIONS
 */
###########################

function getpcaptxt($idmd5)
{
	$myfile = './mastiff/'.$idmd5.'/strings.txt';
	$fileContent = file($myfile,FILE_SKIP_EMPTY_LINES);
		
	if($fileContent!=FALSE)
	{
		return $fileContent;
	}
}

function webStat($text)
{
	foreach ($text as $line)
	{
		#echo $line.'</br>';
		$pattern = array();
		$pattern['get']='/.*GET\s.*/';
		$pattern['post']='/.*POST\s.*/';
		$pattern['ref']='/Referer:\s.*/';
		$pattern['length']='/Content-Length:\s.*/';
		$pattern['type']='/Content-Type:\s.*/';
		$pattern['ok']='/200\sOK.*/';
		#$pattern['otherresp']='//';
		#$pattern['srcip']='//';
		#$pattern['dstip']='//';
		$pattern['host']='/Host:\s.*/';
		$pattern['date']='/Date:\s.*/';
		$pattern['missing']='/missing\sfrom\scapture.*/';
		$pattern['exe']='/This\sprogram\scannot\sbe\srun\sin\sDOS\smode.*/';
		
		foreach($pattern as $key=>$val)
		{
			if(preg_match_all($val,$line,$match[$key]))
			{
				$found[$key]=$match[$key];
			}
		}
		
	}
	return $found;
}

function emlStat($text)
{
	foreach ($text as $line)
	{
		#echo $line.'</br>';
		$pattern = array();
		$pattern['sender']='/MAIL\sFROM:.*/i';
		$pattern['reply']='/Reply-to:.*/i';
		$pattern['rcpt']='/RCPT\sTO:.*/i';
		$pattern['subject']='/Subject:\s.*/i';
		$pattern['date']='/Date:\s.*/i';
		$pattern['spoof']='/.*may\sbe\sforged.*/';
		$pattern['missing']='/missing\sfrom\scapture/';
		
		foreach($pattern as $key=>$val)
		{
			if (preg_match_all($val,$line,$match[$key]))
			{
				$found[$key]=$match[$key];
			}
			
		}
		
	}
	return $found;
}

###########################
/*
 * RUN FUNCTIONS
 */
###########################

$text = getpcaptxt($idmd5);

###########################
/*
 * BUILD HTML
 */
###########################

###########################
### HEADERS
###########################

echo '<html>';

	echo '<head>';
	
		echo '<title>'.$idmd5.'</title>';
		echo '<LINK href="./css/pcap.css" rel="stylesheet" type="text/css">';
		
	echo '</head>';
	
	echo '<body>';

	echo '<div id="container">';

		echo '<div id="mainHead">';

			echo '<h1>PCAP Analysis for MD5: <a href="./md5page.php?idmd5='.$idmd5.'">'.$idmd5.'</a></h1>';
			echo '<br/>';
			echo '<a href="./mastiffResults.php">MASTIFF Results Dashboard</a> | ';
			echo '<a href="./search.php">Search</a> | ';
			echo '<a href="./upload2.html">Submit Files</a>';
			echo '<br/>';
		
		echo '</div>';
		
		###########################
		### BODY
		###########################
		
		echo '<div id="mainContent">';
		
			echo '<div id="pcap">';
			
				echo '<h1>PCAP Summary</h1>';
				echo '<a href="./mastiff/'.$idmd5.'/strings.txt">View Full Strings</a><br/><br/>';
				
				if($type=='email')
				{
					$matches = emlStat($text);
					
					#echo'<pre>';
					#var_dump($matches);
					#echo '</pre>';
					
					foreach($matches as $key=>$val)
					{
						
						if($key=='sender')
						{
							foreach($val as $senderKey=>$senderVal)
							{
								echo '<b>Sender: </b>';
								echo htmlspecialchars($senderVal[0]);
								echo '<br/>';
							}
						}
						
						if($key=='reply')
						{
							foreach($val as $replyKey=>$replyVal)
							{
								echo '<b>ReplyTo: </b>';
								echo htmlspecialchars($replyVal[0]);
								echo '<br/>';
							}
						}
						
						if($key=='rcpt')
						{
							foreach($val as $rcptKey=>$rcptVal)
							{
								echo '<b>Recipient: </b>';
								echo htmlspecialchars($rcptVal[0]);
								echo '<br/>';
							}
						}
						
						if($key=='subject')
						{
							foreach($val as $subjectKey=>$subjectVal)
							{
								echo '<b>Subject: </b>';
								echo htmlspecialchars($subjectVal[0]);
								echo '<br/>';
							}
						}
						
						if($key=='date')
						{
							foreach($val as $dateKey=>$dateVal)
							{
								echo '<b>Date: </b>';
								echo $dateVal[0];
								echo '<br/>';
							}
						}
						
						
						if($key=='spoof')
						{
							echo '<span class="notice"><b>NOTICE: Email Possiblly Spoofed!</b></span><br/>';
						}
						
						if($key=='missing')
						{
							echo '<span class="notice"><b>NOTICE: Bytes Missing from Capture File!</b></span><br/>';
						}
						
					}
				}
				
				if($type=='web')
				{
					$matches = webStat($text);
					
					#echo'<pre>';
					#var_dump($matches);
					#echo '</pre>';
					
					foreach($matches as $key=>$val)
					{
					
						if($key=='get')	#If a GET string is found
						{
							
							foreach($val as $getKey=>$getVal)	#Go through each string found
							{								
								echo '<b>GET Request: </b>';
								echo $getVal[0];
								echo '<br/>';
								/*
								if(isset($matches['ok']))	#If an OK response is found
								{
									if(isset($matches['ok'][$getKey][$getKey]))	#If an OK response matches the GET request
									#NOTE: Go back and make sure an empty array is added to POST results if GET found
									#		And vice-versa.
									{
										
										echo '<b>Response: </b>';
										echo $matches['ok'][$getKey][$getKey];		#Print the OK response
										echo '<br/><br/>';
									}
									
								}
								*/
							}
							
						}
						
						if($key=='post')
						{
							
							foreach($val as $postKey=>$postVal)
							{
								echo '<b>POST Request: </b>';
								echo $postVal[0];
								echo '<br/>';
								/*
								if(isset($matches['ok']))	#If an OK response is found
								{
									if(isset($matches['ok'][$postKey][$postKey]))	#If an OK response matches the POST request
									{
										
										echo '<b>Response: </b>';
										echo $matches['ok'][$postKey][$postKey];	#Print the OK Response
										echo '<br/><br/>';
									}
									
								}
								*/
							}
							
						}
						
						if($key=='ref')
						{
							echo '<b>REFERERS: </b>';
							foreach($val as $refKey=>$refVal)
							{
								echo $refVal[0];
								echo '<br/>';
							}
						}
						
						/*
						if($key=='length')
						{
						}
						
						if($key=='type')
						{
						}
						*/
						
						if($key=='ok')
						{
							echo '<b>200 OK Responses witnessed.</b>';	#Add counter?
							echo '<br/>';
						}
						
						if($key=='host')
						{
							echo '<b>HOSTS: </b>';
							foreach($val as $hostKey=>$hostVal)
							{
								echo $hostVal[0];
								echo '<br/>';
							}
							
						}
						
						/*
						if($key=='date')
						{
						}
						*/
						
						if($key=='missing')
						{
							echo '<span class="notice"><b>NOTICE: Bytes found missing from PCAP!</b></span>';
							echo '<br/>';
						}
						
						if($key=='exe')
						{
							echo '<span class="notice"><b>NOTICE: Executable File Detected!</b></span>';
							echo '<br/>';
						}
					
						
					}
				}
			
			echo '</div>';
		
		echo '</div>';
		
	echo '</div>';
	
	echo '</body>';
	
echo '</html>';

?>
