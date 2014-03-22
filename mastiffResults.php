<?php

$db = new SQLite3('./mastiff/mastiff.db');
$row = array();

###########################
/*
 * FUNCTIONS
 */
###########################

#Array Search Function
function searchForID($id, $array){
	foreach ($array as $key => $val){
		if ($val['sid'] === $id){
			return $key;
		}
	}
	return null;
}


###########################
/*
 * DB QUERIES
 */
###########################

#GET last 25 Filenames and Sizes that don't have MASTIFF in the filename (ignores files pulled from zips)

$result = $db->query('SELECT * FROM files WHERE filename NOT LIKE "%mastiff%" ORDER BY id DESC LIMIT 25');

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

#Match MD5's and Filetypes

$i=0;

foreach ($row as $val)
{
	$idKey = $val['sid'];
	$result = $db->query('SELECT md5, type, id FROM mastiff WHERE id = '.$idKey);
	
	while ($res=$result->fetchArray()){
    if(!isset($res['md5'])) continue;
		
		$row[$i]['md5'] = $res['md5'];
		$row[$i]['type'] = $res['type'];
		$row[$i]['id'] = $res['id'];
		
	}
	$i++;
}

###########################
/*
 * BUILD PAGE
 */
###########################

echo '<html>';
echo '<LINK href="./css/mastiffResults.css" rel="stylesheet" type="text/css">';
echo '<div id="container">';

	echo '<div id="content">';
	
		echo '<div id="header">';

			echo '<header>';
			echo '<title>MASTIFF Results Dashboard</title>';
			echo '<h1>MASTIFF Results Dashboard</h1>';
			echo '<a href="./upload2.html">Submit Files</a> | <a href="./search.php">Search</a>';
			echo '</header>';
		
		echo '</div>';
		
		echo '<div id="table">';
			
			echo '<table id="mastiff-table-main">';
			echo '<tbody>';
			echo '<tr>';
			echo '<th>md5</th><th>filename</th><th>type</th><th>size</th><th>ticket #</th>';
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
				if (isset($row[$i]['md5']))
				{
					$md5=$row[$i]['md5'];
					$row[$i]['md5']='<a href="./md5page.php?idmd5='.$md5.'">'.$md5.'</a>';
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
					echo $row[$i]['md5'];
					echo '</td>';
					
					echo '<td>';
					
					$filename = preg_split('/\/mastiff/',$row[$i]['filename']);
					if(isset($filename[1]))
					{
						echo $filename[1];
					}
					else
					{
						echo $row[$i]['filename'];
					}
					echo '</td>';
					
					echo '<td>';
					echo $row[$i]['type'];
					echo '</td>';
					
					echo '<td>';
					echo $row[$i]['size'];
					echo '</td>';
					
					echo '<td>';
					echo '<a href="./ticket.php?ticket='.$row[$i]['ticket'].'">'.$row[$i]['ticket'].'</a>';
					echo '</td>';
					
					/*
					echo '<td>';
					echo $row[$i]['notes'];
					echo '</td>';
					*/
					
				
				echo '</tr>';
				
			}
		
		echo '</div>'; #Table
		
	echo '</div>'; #Content

echo '</div>'; #Container
//include './footer.php';
echo '</body></html>';
?>




