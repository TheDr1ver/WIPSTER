<?
session_start();

$block64 = $_SESSION['block64'];
$md5 = $_SESSION['md5'];

foreach($block64 as $key=>$val){
	$output_file='./mastiff/'.$md5.'/block-'.($key+1).'.b64';
	$ifp = fopen($output_file,'wb');
	#echo $key.' => '.$val;
	#echo '<br/>';
	fwrite($ifp, base64_decode($val));
	fclose($ifp);
	echo $output_file.'was created.<br/>';
}


?>

<script>
setTimeout(function(){
	window.history.back();
}, 3000);
</script>

