<?php

###########################
/*
 * SET VARIABLES
 */
###########################
 
session_start();
 
#Get Txt from md5page.php
$txtdump = $_SESSION['txtdump'];
 
#Get MD5 Variable from URL
$idmd5=$_GET['idmd5'];

###########################
/*
 * DEFINE FUNCTIONS
 */
###########################

function buildTxt($txtdump, $idmd5)
{
	echo '<a id="top"></a>';
	
	echo '<ul>';
	
		$i=0;
		
		foreach($txtdump as $val)
		{
			echo '<li>';
			echo '<a href="#'.$i.'">'.$val.'</a>';
			echo '</li>';
			
			$i++;
		}
		
		
	
	echo '</ul>';
	
	$i = 0;
	foreach($txtdump as $val)
	{
		
		echo '<p>######################</p>';
		echo '<p><a id="'.$i.'">'.$val.'</a> - <a href="#top">TOP</a></p>';
		echo '<p>######################</p><br/>';
		
		$output = file('./mastiff/'.$idmd5.'/'.$val, FILE_SKIP_EMPTY_LINES);
		foreach($output as $line_num => $line){
			if($line!=''){
				#$line = preg_replace("/[^(\x0A\x20-\x7E)]*/",'',$line);
				$line = preg_replace("/[^(\x20-\x7E)]*/",'',$line);
				$line = preg_replace("/[\s^\n]+/",' ',$line);
				$line = preg_replace("/\h+/",' ',$line);
				$line = preg_replace("/\v+/",' ',$line);
				#$line = $line."\n";
				
				
				#$line = trim($line, " \t\0\x0B");
				#$line = ltrim($line);
				echo '<p>';
				echo htmlspecialchars($line, ENT_QUOTES);
				echo '</p>';
			}
		}
		#$output = htmlspecialchars($output, ENT_QUOTES);
		#echo $output;
		
		echo '<br/>';
		
		$i++;
	}
}

###########################
/*
 * RUN FUNCTIONS
 */
###########################
?>

<html>

	<head>
	
		<style>
			p{
				margin:0px;
			}
			
			ul{
				text-indent:0px;
			}
			
			#container{
				max-width:900px;
				word-wrap:break-word;
				overflow:auto;
				font-family:monospace;
				white-space:pre-line;
				text-indent:-2em;
				padding:2em;
			}
			
		</style>
	
	</head>

</html>

<div id="container">

<?
buildTxt($txtdump, $idmd5);
echo '</div>';

###########################
/*
 * BUILD PAGE
 */
###########################

?>
