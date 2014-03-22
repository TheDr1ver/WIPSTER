<?php

/*
 * GET VARS
 */

$ascii = '';
$base64 = '';
$urlenc = '';
$decimal = '';
$htmlent = '';
$binary = '';
$rot13 = '';
$hex = '';


/*
 * CONVERT
 */

#ASCII
if (isset($_POST['ascii'])){
	$ascii = $_POST['ascii'];
	
	$base64 = base64_encode($ascii);;
	
	$urlenc = urlencode($ascii);
	
	#$htmlent = htmlspecialchars($ascii, ENT_QUOTES);
	$htmlent = htmlentities($ascii, ENT_QUOTES);
	
	$decimal = array();
	$binary = array();
	$hex = array();	
	$asciisplit = str_split($ascii);
	foreach($asciisplit as $val){
		$decimal[]=ord($val);
	}
	foreach($decimal as $val){
		$binary[]=str_pad(base_convert($val, 10, 2), 8, '0', STR_PAD_LEFT);
	}
	foreach($decimal as $val){
		$hex[]=str_pad(base_convert($val, 10, 16), 2, '0', STR_PAD_LEFT);
	}
	
	$decimal = implode(' ',$decimal);
	
	$binary = implode(' ',$binary);
	
	$hex = implode(' ',$hex);
	
	$rot13 = str_rot13($ascii);
	
}
#BASE64
if (isset($_POST['base64'])){
	$ascii = base64_decode($_POST['base64']);
	
	$base64 = $_POST['base64'];
	
	$urlenc = urlencode($ascii);
	
	$htmlent = htmlspecialchars($ascii);
	
	$decimal = array();
	$binary = array();
	$hex = array();	
	$asciisplit = str_split($ascii);
	foreach($asciisplit as $val){
		$decimal[]=ord($val);
	}
	foreach($decimal as $val){
		$binary[]=str_pad(base_convert($val, 10, 2), 8, '0', STR_PAD_LEFT);
	}
	foreach($decimal as $val){
		$hex[]=str_pad(base_convert($val, 10, 16), 2, '0', STR_PAD_LEFT);
	}
	
	$decimal = implode(' ',$decimal);
	
	$binary = implode(' ',$binary);
	
	$hex = implode(' ',$hex);
	
	$rot13 = str_rot13($ascii);
}

#URL Encoded
if(isset($_POST['urlenc'])){
	$ascii = urldecode($_POST['urlenc']);
	
	$base64 = base64_encode($ascii);
	
	$urlenc = urlencode($ascii);
	
	$htmlent = htmlspecialchars($ascii);
	
	$decimal = array();
	$binary = array();
	$hex = array();	
	$asciisplit = str_split($ascii);
	foreach($asciisplit as $val){
		$decimal[]=ord($val);
	}
	foreach($decimal as $val){
		$binary[]=str_pad(base_convert($val, 10, 2), 8, '0', STR_PAD_LEFT);
	}
	foreach($decimal as $val){
		$hex[]=str_pad(base_convert($val, 10, 16), 2, '0', STR_PAD_LEFT);
	}
	
	$decimal = implode(' ',$decimal);
	
	$binary = implode(' ',$binary);
	
	$hex = implode(' ',$hex);
	
	$rot13 = str_rot13($ascii);
}

#HTML Entities
if(isset($_POST['htmlent'])){
	$ascii = html_entity_decode($_POST['htmlent']);
	
	$base64 = base64_encode($ascii);
	
	$urlenc = urlencode($ascii);
	
	$htmlent = htmlspecialchars($ascii);
	
	$decimal = array();
	$binary = array();
	$hex = array();	
	$asciisplit = str_split($ascii);
	foreach($asciisplit as $val){
		$decimal[]=ord($val);
	}
	foreach($decimal as $val){
		$binary[]=str_pad(base_convert($val, 10, 2), 8, '0', STR_PAD_LEFT);
	}
	foreach($decimal as $val){
		$hex[]=str_pad(base_convert($val, 10, 16), 2, '0', STR_PAD_LEFT);
	}
	
	$decimal = implode(' ',$decimal);
	
	$binary = implode(' ',$binary);
	
	$hex = implode(' ',$hex);
	
	$rot13 = str_rot13($ascii);
}

#Decimal
if(isset($_POST['decimal'])){
	$ascii = array();
	$decimal = explode(' ',$_POST['decimal']);
	foreach($decimal as $val){
		$ascii[]=chr($val);
	}
	$ascii = implode($ascii);
	
	$base64 = base64_encode($ascii);
	
	$urlenc = urlencode($ascii);
	
	$htmlent = htmlspecialchars($ascii);
	
	$decimal = array();
	$binary = array();
	$hex = array();	
	$asciisplit = str_split($ascii);
	foreach($asciisplit as $val){
		$decimal[]=ord($val);
	}
	foreach($decimal as $val){
		$binary[]=str_pad(base_convert($val, 10, 2), 8, '0', STR_PAD_LEFT);
	}
	foreach($decimal as $val){
		$hex[]=str_pad(base_convert($val, 10, 16), 2, '0', STR_PAD_LEFT);
	}
	
	$decimal = implode(' ',$decimal);
	
	$binary = implode(' ',$binary);
	
	$hex = implode(' ',$hex);
	
	$rot13 = str_rot13($ascii);
}

#ROT13
if(isset($_POST['rot13'])){
	$ascii = str_rot13($_POST['rot13']);
	
	$base64 = base64_encode($ascii);
	
	$urlenc = urlencode($ascii);
	
	$htmlent = htmlspecialchars($ascii);
	
	$decimal = array();
	$binary = array();
	$hex = array();	
	$asciisplit = str_split($ascii);
	foreach($asciisplit as $val){
		$decimal[]=ord($val);
	}
	foreach($decimal as $val){
		$binary[]=str_pad(base_convert($val, 10, 2), 8, '0', STR_PAD_LEFT);
	}
	foreach($decimal as $val){
		$hex[]=str_pad(base_convert($val, 10, 16), 2, '0', STR_PAD_LEFT);
	}
	
	$decimal = implode(' ',$decimal);
	
	$binary = implode(' ',$binary);
	
	$hex = implode(' ',$hex);
	
	$rot13 = str_rot13($ascii);
}
#Binary
if(isset($_POST['binary'])){
	$ascii = array();
	$binary = str_replace(" ", "", $_POST['binary']);
	$binary = str_split($binary, 8);
	foreach($binary as $val){
		$val = str_pad($val, 8, '0', STR_PAD_LEFT);
		$dec[]=bindec($val);
	}
	foreach($dec as $val){
		$ascii[]=chr($val);
	}
	$ascii = implode($ascii);
	
	$base64 = base64_encode($ascii);
	
	$urlenc = urlencode($ascii);
	
	$htmlent = htmlspecialchars($ascii);
	
	$decimal = array();
	$binary = array();
	$hex = array();	
	$asciisplit = str_split($ascii);
	foreach($asciisplit as $val){
		$decimal[]=ord($val);
	}
	foreach($decimal as $val){
		$binary[]=str_pad(base_convert($val, 10, 2), 8, '0', STR_PAD_LEFT);
	}
	foreach($decimal as $val){
		$hex[]=str_pad(base_convert($val, 10, 16), 2, '0', STR_PAD_LEFT);
	}
	
	$decimal = implode(' ',$decimal);
	
	$binary = implode(' ',$binary);
	
	$hex = implode(' ',$hex);
	
	$rot13 = str_rot13($ascii);
}

#Hexadecimal
if(isset($_POST['hex'])){
	$ascii = array();
	$hex = str_replace(" ", "", $_POST['hex']);
	$hex = str_split($hex, 2);
	#$hex = chunk_split($_POST['hex'], 2, ' ');
	#$hex = explode(' ',$hex);
	foreach($hex as $val){
		$dec[]=str_pad(base_convert($val, 16, 10), 2, '0', STR_PAD_LEFT);
	}
	foreach($dec as $val){
		$ascii[]=chr($val);
	}
	$ascii = implode($ascii);
	
	$base64 = base64_encode($ascii);
	
	$urlenc = urlencode($ascii);
	
	$htmlent = htmlspecialchars($ascii);
	
	$decimal = array();
	$binary = array();
	$hex = array();	
	$asciisplit = str_split($ascii);
	foreach($asciisplit as $val){
		$decimal[]=ord($val);
	}
	foreach($decimal as $val){
		$binary[]=str_pad(base_convert($val, 10, 2), 8, '0', STR_PAD_LEFT);
	}
	foreach($decimal as $val){
		$hex[]=str_pad(base_convert($val, 10, 16), 2, '0', STR_PAD_LEFT);
	}
	
	$decimal = implode(' ',$decimal);	
	#$decimal = hexdec($_POST['hex']);
	
	$binary = implode(' ',$binary);
	
	$hex = implode(' ',$hex);
	
	$rot13 = str_rot13($ascii);
}

?>

<html>
<head>
<title>Text Converting Tool</title>
<style>
h1{
	text-align:center;
}
.box{
	margin:10px;
	padding:5px;
	float:left;
}
textarea{
	width:330px;
	height:150px;
}
</style>

</head>
<body>
<div id="container">
	<div id="header">
		<h1>Convert Various Strings to Other Formats</h1>
	</div>

	<div id="ascii" class="box">
		<form method="post" action="convert.php">
			<h3>Plain Text (ASCII)</h3>
			<textarea id="ascii" name="ascii"><? echo $ascii; ?></textarea>
			<br/>
			<input type="submit" value="Convert"/>
		</form>
	</div>
	
	<div id="base64" class="box">
		<form method="post" action="convert.php">
			<h3>Base64</h3>
			<textarea id="base64" name="base64"><? echo $base64; ?></textarea>
			<br/>
			<input type="submit" value="Convert"/>
		</form>
	</div>
	
	<div id="urlenc" class="box">
		<form method="post" action="convert.php">
			<h3>URL Encoded</h3>
			<textarea id="urlenc" name="urlenc"><? echo $urlenc; ?></textarea>
			<br/>
			<input type="submit" value="Convert"/>
		</form>
	</div>
	
	<div id="decimal" class="box">
		<form method="post" action="convert.php">
			<h3>Decimal</h3>
			<textarea id="decimal" name="decimal"><? echo $decimal; ?></textarea>
			<br/>
			<input type="submit" value="Convert"/>
		</form>
	</div>
	
	<div id="htmlent" class="box">
		<form method="post" action="convert.php">
			<h3>HTML Entity</h3>
			<textarea id="htmlent" name="htmlent"><? echo htmlentities($htmlent, ENT_QUOTES); ?></textarea>
			<br/>
			<input type="submit" value="Convert"/>
		</form>
	</div>
	
	<div id="binary" class="box">
		<form method="post" action="convert.php">
			<h3>Binary</h3>
			<textarea id="binary" name="binary"><? echo $binary ?></textarea>
			<br/>
			<input type="submit" value="Convert"/>
		</form>
	</div>
	
	<div id="rot13" class="box">
		<form method="post" action="convert.php">
			<h3>ROT 13</h3>
			<textarea id="rot13" name="rot13"><? echo $rot13 ?></textarea>
			<br/>
			<input type="submit" value="Convert"/>
		</form>
	</div>
	
	<div id="hex" class="box">
		<form method="post" action="convert.php">
			<h3>Hexidecmial</h3>
			<textarea id="hex" name="hex"><? echo $hex ?></textarea>
			<br/>
			<input type="submit" value="Convert"/>
		</form>
	</div>
	
	<div id="md5" class="box">
		<form method="post" action="convert.php">
			<h3>MD5</h3>
			<textarea id="md5" name="md5"><? if ($ascii!=''){echo md5($ascii);} ?></textarea>
			<br/>
			<!--<input type="submit" value="Convert"/>-->
		</form>
	</div>
	
	<div id="sha1" class="box">
		<form method="post" action="convert.php">
			<h3>SHA1</h3>
			<textarea id="sha1" name="sha1"><? if ($ascii!=''){echo sha1($ascii);} ?></textarea>
			<br/>
			<!--<input type="submit" value="Convert"/>-->
		</form>
	</div>
	
	<div id="sha256" class="box">
		<form method="post" action="convert.php">
			<h3>SHA256</h3>
			<textarea id="sha256" name="sha256"><? if ($ascii!=''){echo hash('sha256',$ascii);} ?></textarea>
			<br/>
			<!--<input type="submit" value="Convert"/>-->
		</form>
	</div>
	
	<div id="sha512" class="box">
		<form method="post" action="convert.php">
			<h3>SHA512</h3>
			<textarea id="sha512" name="sha512"><? if ($ascii!=''){echo hash('sha512',$ascii);} ?></textarea>
			<br/>
			<!--<input type="submit" value="Convert"/>-->
		</form>
	</div>
	
	
</div><!--Container-->

</body>
</html>
