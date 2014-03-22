<?php

/*
 * 
 * DEFINE VARIABLES
 * 
 */

$db = new SQLite3('./urls/urls.db');
$row = array();
$ticket=$_GET['ticket'];

/*
 * 
 * DEFINE FUNCTIONS
 * 
 */
 
 function getFiles($db,$ticket,$row)
 {
	$result = $db->query('SELECT * FROM urls WHERE ticket = '.$ticket);

	$i = 0;

	while ($res=$result->fetchArray()){
		if(!isset($res['url'])) continue;
			$row[$i]['url'] = $res['url'];
			$row[$i]['notes'] = $res['notes'];
			$i++;
	}
	
	$i = 0;
	
	return $row;
 }
 
/*
 * 
 * RUN FUNCTIONS
 * 
 */

$row=getFiles($db,$ticket,$row);

/*
 * 
 * BUILD HTML PAGE
 * 
 */

echo '</html>';

echo '<div id="container">';

	echo '<div id="header">';

		echo '<header>';
			echo '<title>URLS Analyzed for '.$ticket.'</title>';
			echo '<LINK href="./css/ticket.css" rel="stylesheet" type="text/css">';
			echo '<h1>URLS Analyzed for Ticket# '.$ticket.'</h1>';
			echo '<a href="./urlResearch.php">Submit a URL</a> | <a href="./urlResults.php">URL Results Dashboard</a> <!--| <a href="./urlSearch.php">Search</a>-->';
		echo '</header>';

	echo '</div>';	#header
	
	echo '<div id="content">';
	echo '<body>';

		echo '<div id="table">';
		
			echo '<table id="ticket-table-main">';
				echo '<tbody>';
					echo '<tr><th>url</th><th>notes</th>';

					$c=0;
					
					foreach($row as $key=>$val)
					{
						if($c %2 == 0)
						{
							$oddEven='even';
						}
						else
						{
							$oddEven='odd';
						}
						echo '<tr class="'.$oddEven.'">';
							echo '<td><a href="./urlResearch.php?url='.$val['url'].'">'.$val['url'].'</a></td>';
							
							
							echo '<td>'.$val['notes'].'</td>';
						echo '</tr>';
						
						$c++;
					}
				echo '</tbody>';
			echo '</table>';
		
		echo '</div>'; #table
	
	echo '</body>';
	echo '</div>';	#content
	

echo '</div>';	#container
include './footer.php';
echo '</html>';

?>

