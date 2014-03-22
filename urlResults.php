<?php

$db = new SQLite3('./urls/urls.db');
$result = $db->query('SELECT * FROM urls ORDER BY id DESC LIMIT 25');
$row = array();

$i = 0;

while ($res=$result->fetchArray()){
    if(!isset($res['url'])) continue;
		$row[$i]['url'] = $res['url'];
		$row[$i]['ticket'] = $res['ticket'];
		$row[$i]['notes'] = $res['notes'];
		$i++;
}



###########################
/*
 * BUILD PAGE
 */
###########################

echo '<html>';
echo '<LINK href="./css/urlResults.css" rel="stylesheet" type="text/css">';
echo '<div id="container">';

	echo '<div id="content">';
	
		echo '<div id="header">';

			echo '<header>';
			echo '<title>URL Results Dashboard</title>';
			echo '<h1>URL Results Dashboard</h1>';
			#echo $_SERVER['REMOTE_ADDR'];
			echo '<a href="./urlResearch.php">Submit URL</a> <!--| <a href="./urlSearch.php">Search</a>-->';
			echo '</header>';
		
		echo '</div>';
		
		echo '<div id="table">';
			
			echo '<table id="url-table-main">';
			echo '<tbody>';
			echo '<tr>';
			echo '<th>url</th><th>ticket</th><th>notes</th>';
			echo '</tr>';

			/* DEBUGGING */

			/*
			echo '<pre>';
			var_dump($row);
			echo '</pre>';
			*/

			$length = count($row);
			for ($i=0; $i < $length; $i++)
			{

				#Change MD5 Data to Hyperlink
				if (isset($row[$i]['url']))
				{
					$url=$row[$i]['url'];
					$row[$i]['url']='<a href="./urlResearch.php?url='.rawurlencode($url).'">'.$url.'</a>';
				}
				
				if ($i %2 == 0)
				{
					$eveOdd = 'even';
				}
				else
				{
					$eveOdd = 'odd';
				}
				
				echo '<tr class="'.$eveOdd.'">';

					echo '<td>';
					echo $row[$i]['url'];
					echo '</td>';
					
										
					echo '<td>';
					echo '<a href="./urlTicket.php?ticket='.$row[$i]['ticket'].'">'.$row[$i]['ticket'].'</a>';
					echo '</td>';
					
					
					echo '<td>';
					echo $row[$i]['notes'];
					echo '</td>';
					
					
				
				echo '</tr>';
				
			}
		
		echo '</div>'; #Table
		
	echo '</div>'; #Content

echo '</div>'; #Container
//include './footer.php';
echo '</body></html>';
?>




