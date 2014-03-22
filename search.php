<?php


/*
 * 
 * BUILD FUNCTIONS
 * 
 */
 
function formSubmit($_POST)
{
	$db = new SQLite3('./mastiff/mastiff.db');
	$row = array();
	$error = '';
	
	$md5tix = $_POST['md5tix'];
	$search = $_POST['search'];
	
	$search = trim($search);
	$search = stripslashes($search);
	$search = htmlspecialchars($search);
	
	if ($md5tix=='md5')
	{
		#Query DB to see if MD5 Exists
		$result = $db->query('SELECT * FROM mastiff WHERE md5 = "'.$search.'"');
	
		$arrayDump=$result->fetchArray();
		if (!isset($arrayDump['id']))
		{
			$error='MD5 "'.$search.'" Not Found.';
			#echo $error;
			return $error;
		}
		else
		{
			$newURL = './md5page.php?idmd5='.$search;
			header('Location: '.$newURL);
			die();
		}

	}
	else
	{
		#Query DB to see if ticket # exists
		$result = $db->query('SELECT * FROM files WHERE ticket = '.$search);

		$arrayDump=$result->fetchArray();
		if (!isset($arrayDump['id']))
		{
			$error='Ticket "'.$search.'" Not Found.';
			#echo $error;
			return $error;
		}
		else
		{
			$newURL = './ticket.php?ticket='.$search;
			header('Location: '.$newURL);
			die();
		}
	
	}
}
 
/*
 * 
 * RUN FUNCTIONS
 * 
 */

if ($_SERVER['REQUEST_METHOD']=='POST')
{
	$error=formSubmit($_POST);
	#echo '<pre>'.var_dump($error).'</pre>';
}

/*
 * 
 * Build HTML Form
 * 
 */

echo '<html>';

echo '<div id="container">';

	echo '<header>';

	echo '<div id="header">';
	
	echo '<LINK href="./css/search.css" rel="stylesheet" type="text/css">';	
	echo '<title>Search for Files</title>';
	
	echo '<h1>Search for Files</h1>';
	echo '<a href="./mastiffResults.php">MASTIFF Results Dashboard</a> | <a href="./upload2.html">Submit Files</a>';
	
	if(isset($error))
	{
		echo '<p style="font-weight:bold;color:red;">'.$error.'</p>';
	}
	
	echo '</div>';
	
	echo '</header>';
	
	echo '<body><div id="content">';
	
		echo '<div id="form">';
		
			echo '<form action="./search.php" method="post">';
			
				echo 'Search for:<br/>';
				echo '<input type="radio" name="md5tix" value="md5" checked />MD5 ';
				echo '<input type="radio" name="md5tix" value="ticket" />Ticket';
				echo '</br>';
				echo '<div id="text">';
					echo 'MD5/Ticket#: ';
					echo '<input type="text" name="search" size="25" required />';
				echo '</div>';
				echo '<br/>';
				
				echo '<input type="submit" name="submit" value="Search" />';
			
			echo '</form>';
		
		echo '</div>';
	
	echo '</body></div>';

echo '</div>';

include './footer.php';

echo '</html>';

?>
