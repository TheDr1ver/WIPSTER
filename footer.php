<?php
	include '/var/www/func/config.php';
	echo '<LINK href="./css/footer.css" rel="stylesheet" type="text/css">';

	echo '<div id="footer">';
	#Testing Update comment
	
		echo '<div id="footcontent">';
				echo "<div id='collapseBtn'>Hide/Show Footer</div>";
				echo '<div id="left">';
				echo '<p class="one"><a href="./">WIPSTER v0.2 Beta</a> (C) Nick Driver (<a href="https://www.twitter.com/TheDr1ver">@TheDr1ver</a>) - '.date("Y").'</p>';
				echo '<p class="two">Operating on <a href="http://zeltser.com/remnux/">REMNUX 5</a>, running Apache with PHP version '.phpversion().'.</p>';
				echo '</div>';
				
				echo '<div id="center">';
				include './twitter.php';
				echo '</div>';
				
				echo '<div id="right">';
				#Check for autoPasteBin checker results from today
				
				$today = getdate();
				$today = $today['year'].'-'.$today['mon'].'-'.$today['mday'];
				
				$db = new SQLite3('./autopb/autopb.db');
				
				$result = $db->querySingle('SELECT date FROM results ORDER BY id DESC');
				if ($result == $today){
					echo '<p><a href="./autoPastebin.php"><span style="color:red;font-weight:bold;">NOTICE:</span> New '.$gcseQuery.' data found today on a PasteBin site.</a></p>';
				}
				
				echo '<p><a href="./mastiffResults.php">MASTIFF Analysis</a> | <a href="./urlResearch.php">URL Research</a> | <a href="./convert.php">Text Conversion</a> | <a href="./pastebinsearch.php">Pastebin Search</a></p>';
				echo '</div>';
				
		echo '</div>';
	
	echo '</div>';


?>

<script src="./scripts/jquery-1.11.0.min.js"></script>
<script>
$(document).ready(function(){
	if($(window).width()<1080){
		$("#footcontent").animate({
			height:'30px'
			},600);
		$toggle = 'show';
	}
	else{
		$toggle = 'hide';
	}
	
	$("#collapseBtn").click(function(){
		if($toggle=='hide'){
			$("#footcontent").animate({
			height:'30px'
			},600);
			$toggle = 'show';
		}
		
		else{
			$("#footcontent").animate({
			height:'auto'
			},600);
			$toggle = 'hide';
		}
		
		
	});
});
</script>
