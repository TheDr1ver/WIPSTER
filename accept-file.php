<?php

/*
 * 
 * Process Uploaded File
 * 
 */
 
 
//if ticket# provided, set $ticket variable
if(isset($_POST['ticket']))
{
	$ticket = $_POST['ticket'];
	$ticket = strtolower($ticket);
	$ticket = ltrim($ticket,'inc0');
}
else
{
	$ticket = 'ERROR';
}


//if notes provided, set $notes variable
if(isset($_POST['notes']))
{
	$notes = $_POST['notes'];
	$notes = htmlspecialchars($notes,ENT_QUOTES);
}
else
{
	$notes = 'No Notes Entered';
}

//set values of file uploaded
if($_FILES['malware']['name'])
{
    //if no errors...
    if(!$_FILES['malware']['error'])
    {
        //now is the time to modify the future file name and validate the file
        $new_file_name = strtolower($_FILES['malware']['name']); //rename file to lowercase
        $new_file_name = preg_replace('/[^A-Za-z0-9-.]/','',$new_file_name); //Strip special characters
        if($_FILES['malware']['size'] > (102400000)) //can't be larger than 100 MB
        {
            $valid_file = false;
            $message = 'Oops!  Your file\'s size is to large. Max 100MB.';
        }
        else
        {
			$valid_file=true;
		}
       
        //if the file has passed the test
        if($valid_file)
        {
            //move it to where we want it to be
            move_uploaded_file($_FILES['malware']['tmp_name'], './upload/malware/'.$new_file_name);
            $message = 'Congratulations!  Your file was accepted.';
        }
    }
    //if there is an error...
    else
    {
        //set that to be the returned message
        $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['malware']['error'];
    }
}
//you get the following information for each file:
echo $message;
echo '<br/>';
echo '<br/>';

/*
 * 
 * Define Vars
 * 
 */
$md5hash = md5_file('./upload/malware/'.$new_file_name);


/*
 * 
 * HTML Headers
 * 
 */

echo '<html>';

	echo '<head>';
		echo '<title>'.$md5hash.' Upload Status</title>';
		echo '<LINK href="./css/accept-file.css" rel="stylesheet" type="text/css">';
	echo '</head>';

	echo '<body>';



# Probably shouldn't print this to the page in case there are special chars that weren't stripped
#echo 'Original name: ';
#echo $_FILES['malware']['name'];
#echo '<br/>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'New Filename: ';
echo '</div><div class="atrdata">';
echo $new_file_name;
echo '</div></div>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'Size: ';
echo '</div><div class="atrdata">';
echo $_FILES['malware']['size'];
echo '</div></div>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'Type: ';
echo '</div><div class="atrdata">';
echo $_FILES['malware']['type'];
echo '</div></div>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'tmp_name: ';
echo '</div><div class="atrdata">';
echo $_FILES['malware']['tmp_name'];
echo '</div></div>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'MD5: ';
echo '</div><div class="atrdata">';
echo $md5hash;
echo '</div></div>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'Uploading Remnux User: ';
echo '</div><div class="atrdata">';
echo exec('whoami');
echo '</div></div>';

echo '<br/>';

echo '<div class="datapoint">';
echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'Ticket: ';
echo '</div><div class="atrdata">';
echo $ticket;
echo '</div></div>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'Notes: ';
echo '</div><div class="atrdata">';
echo $notes;
echo '</div></div>';

echo '<br/>';


/*
 * 
 * Execute commands on Remnux Server
 * 
 */


#Run uploaded file through Remnux and delete original upload after run
$command = '/usr/local/bin/mas.py -c /usr/local/etc/mastiff.conf "/var/www/upload/malware/'.$new_file_name.'" && rm "/var/www/upload/malware/'.$new_file_name.'"';
$masResult = shell_exec($command);

#Get the largest row # in the files table of the mastiff.db
$getMaxRow = 'sqlite3 /var/www/mastiff/mastiff.db "SELECT id FROM files WHERE id = (SELECT MAX(id) FROM files);"';
#Ensure row # is an integer (probably not necessary)
$getMax = (int)(shell_exec($getMaxRow));

#Define Database
$db = new SQLite3('./mastiff/mastiff.db');

#Get all records from DB for this file with no ticket#
$result = $db->query('SELECT * FROM files WHERE filename LIKE "%'.$md5hash.'%" OR ticket = "" OR filename = "/var/www/upload/malware/'.$new_file_name.'"');

while ($res=$result->fetchArray()){
		#echo '$res["ticket"]: '.$res['ticket'];
		if ($res['ticket']=='')
		{
			#echo '<pre>'.var_dump($res).'</pre>';
			
			$notes=$db->escapeString($notes);
			$subip=$db->escapeString($_SERVER['REMOTE_ADDR']);
			$command2 = 'UPDATE files SET ticket = "'.$ticket.'", notes = "'.$notes.'", ip = "'.$subip.'" WHERE id = "'.$res['id'].'"';
			$query = $db->exec($command2);
			
			if (!$query)
			{
				exit("Error in query.");
			}
			else
			{
				$updateTable='Number of rows modified: '.$db->changes();
			}
		}
		else
		{
			$command2 = '<b><u>NOTICE</u></b> - This file was previously uploaded: <a href="./md5page.php?idmd5='.$md5hash.'">'.$md5hash.'</a>';
			$updateTable = 'If you wish to update the notes/ticket#, please re-name the file and upload it again.';
		}
		
}


#Print any results

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'Mastiff Run Result: ';
echo '</div><div class="atrdata">';
var_dump($masResult);	#Should return 'NULL'
echo '</div></div>';


echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'getMax: ';	
echo '</div><div class="atrdata">';
echo $getMax; #Returns the largest row in the table
echo '</div></div>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'Update Command Executed: ';
echo '</div><div class="atrdata">';
echo $command2; #Prints the command run to update the sqlite table
echo '</div></div>';

echo '<div class="datapoint">';
echo '<div class="attribute">';
echo 'Update Command Result: ';
echo '</div><div class="atrdata">';
echo $updateTable;	#Should return blank if successful
echo '</div></div>';


echo '<br/><br/>';
echo '<h1><a href="./md5page.php?idmd5='.$md5hash.'">View MASTIFF Analysis for '.$new_file_name.'</a></h1><br/>';
echo '<a href="./mastiffResults.php">MASTIFF Results Dashboard</a> | <a href="./search.php">Search</a> | <a href="./upload2.html">Upload another file</a></br>';
echo '<br/>';
echo '<a href="./mastiffResults.php">View List of All MASTIFF Results</a>';

?>

<script>
setTimeout(function(){
	window.location.assign("./md5page.php?idmd5=<? echo $md5hash;?>");
}, 5000);
</script>

<?php

echo '</body></html>';

?>
