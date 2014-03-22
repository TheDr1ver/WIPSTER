<?php

session_start();

$md5 = $_SESSION['md5'];
$filename = $_SESSION['filename'];

$cmd[0] = 'mkdir /var/www/mastiff/'.$md5.'/xor';
$cmd[1] = 'cp "/var/www/mastiff/'.$md5.'/'.$filename.'" /var/www/mastiff/'.$md5.'/xor/';
#$cmd[2] = 'sudo chown -R www-data:www-data /var/www/mastiff/'.$md5.'/xor';
$cmd[2] = '/usr/local/bin/NoMoreXOR.py -a -o "/var/www/mastiff/'.$md5.'/xor/'.$filename.'.hex" "/var/www/mastiff/'.$md5.'/xor/'.$filename.'"';
#$cmd[4] = 'sudo chmod -R 777 /var/www/mastiff/'.$md5.'/xor';
$cmd[3] = 'strings /var/www/mastiff/'.$md5.'/xor/*.unxored > "/var/www/mastiff/'.$md5.'/xor/dexorstrings.txt"';
$cmd[4] = 'rm -f /var/www/mastiff/'.$md5.'/xor/*.unxored';
$cmd[5] = 'rm -f /var/www/mastiff/'.$md5.'/xor/*.hex';
$cmd[6] = 'rm -f /var/www/mastiff/'.$md5.'/xor/*.VIR';

foreach($cmd as $val){
	#$output=shell_exec($val);
	echo '<pre>';
	#exec($val, $output, $return);
	$output = system($val, $return);
	if ($return!=0){
		echo 'CMD: ';
		echo $val;
		echo '<br/>';
		echo 'Error: ';
		var_dump($return);
		echo '<br/>Output: ';
		var_dump($output);
		echo '<br/>';
		echo '---------------------------';
		echo '<br/>';
	}
	else{
		echo 'CMD: ';
		echo $val;
		echo '<br/>';
		echo 'Output: ';
		var_dump($output);
		echo '<br/>';
		echo '---------------------------';
		echo '<br/>';
	}
	
	echo '</pre>';
}

?>

<script>
setTimeout(function(){
	window.history.back();
}, 5000);
</script>
