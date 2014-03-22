<?php

/*
 * 
 * DEFINE VARIABLES
 * 
 */

$db = new SQLite3('./mastiff/mastiff.db');
$row = array();
$ticket=$_GET['ticket'];

/*
 * 
 * DEFINE FUNCTIONS
 * 
 */
 
 function getFiles($db,$ticket,$row)
 {
	$result = $db->query('SELECT * FROM files WHERE ticket = '.$ticket);

	$i = 0;

	while ($res=$result->fetchArray()){
		if(!isset($res['filename'])) continue;
			$row[$i]['filename'] = $res['filename'];
			$row[$i]['size'] = $res['size'];
			$row[$i]['sid'] = $res['sid'];
			$row[$i]['ticket'] = $res['ticket'];
			$row[$i]['notes'] = $res['notes'];
			$i++;
	}
	
	$i = 0;
	
	foreach($row as $key=>$val)
	{
		$result = $db->query('SELECT md5, type FROM mastiff WHERE id = '.$val['sid']);

		

		while ($res=$result->fetchArray()){
			if(!isset($res['md5'])) continue;
				$row[$i]['md5'] = $res['md5'];
				$row[$i]['type'] = $res['type'];
				$i++;
		}
	}
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
			echo '<title>Files Analyzed for '.$ticket.'</title>';
			echo '<LINK href="./css/ticket.css" rel="stylesheet" type="text/css">';
			echo '<h1>Files Analyzed for Ticket# '.$ticket.'</h1>';
			echo '<a href="./mastiffResults.php">MASTIFF Results Dashboard</a> | <a href="./search.php">Search</a> | <a href="./upload2.html">Submit Files</a>';
		echo '</header>';

	echo '</div>';	#header
	
	echo '<div id="content">';
	echo '<body>';

		echo '<div id="table">';
		
			echo '<table id="ticket-table-main">';
				echo '<tbody>';
					echo '<tr><th>md5</th><th>filename</th><th>type</th><th>size</th></tr>';

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
							echo '<td><a href="./md5page.php?idmd5='.$val['md5'].'">'.$val['md5'].'</a></td>';
							
							echo '<td>';
							
							$filename = preg_split('/\/mastiff/',$val['filename']);
							if(isset($filename[1]))
							{
								echo $filename[1];
							}
							else
							{
								echo $val['filename'];
							}
							
							echo '</td>';
							echo '<td>'.$val['type'].'</td>';
							echo '<td>'.$val['size'].'</td>';
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
