<?

##### Define Vars

require '/var/www/func/config.php';

##### Run Search
$opts=array(
	'http'=>array(
		'method'=>"GET",
		'header'=>"Accept-language: en\r\n".
				"Referer: https://www.google.com/\r\n",
		'user_agent'=>$autopbUA		
	)
);
$context = stream_context_create($opts);

$search = file_get_contents('https://www.googleapis.com/customsearch/v1element?key='.$gcseKey.'&rsz=filtered_cse&num=10&hl=en&prettyPrint=false&source=gcsc&gss=.com&sig='.$gcseSig.'&cx='.$gcseCx.'&q='.$gcseQuery.'&sort=date&googlehost=www.google.com&callback=google.search.Search.apiary13655&nocache=1395785766092', false, $context);
$search = strstr($search, '(');
$search = substr($search, 1, -2);
$results = json_decode($search, true);

#####
##### PARSE RESULTS
#####

#Content Layout
/*
["cursor"]=>
array(6) {
	["currentPageIndex"]=>int(0)
	["estimatedResultCount"]=>string(5)"26100"
	["moreResultsUrl"]=>"http://www.google.com/cse?oe=utf8&ie=utf8&source=uds&q=string&start=0&sort=data&cx=searchengineID"
	["resultCount"]=>string(6)"26,100"
	["searchResultTime"]=>string(4)"0.26"
	["pages"]=>
	array(10) {
		[0]=>
		array(2){
			["label"]=>int(1)
			["start"]=>string(1)"0"
		}
		[1]=>
		array(2){
			["label"]=>int(2)
			["start"]=>string(2)"10"
		}
		.....SNIP....
	}
	
}
["context"]=>
array(3){
	["title"]=>string(14)"Custom Search Engine"
	["total_results"]=>string(1)"0"
	["facets"]=>
	array(0){
	}
}
["results"]=>
array(10){
	[0]=>
	array(12){
		["GsearchResultClass"]=>string(10)"GwebSearch"
		["cacheUrl"]=>string(62)"http://google.com/search?q=cache....[cache link]"
		["clicktrackUrl"]=>string(163)"http://google.com/....tracking URL"
		["content"]=>string(202)"Formatted Snippet from Page - bolds search terms"
		["contentNoFormatting"]=>string(177)"Snippet of page without Formatting... Duh."
		["formattedUrl"]=>string(21)"example.com/page"
		["title"]=>string(59)"Formatted Title from page - bolds search terms"
		["titleNoFormatting"]=>string(21)"Non-formatted Title... Duh again."
		["unescapedUrl"]=>string(28)"http://example.com/page 1 - not URL-escaped"
		["url"]=>string(28)"http://example.com/page%201 - URL-escaped"
		["visibleUrl"]=>"example.com"
		["richSnippet"]=>
		array(2){
			["cseImage"]=>
			array(1){
				["src"]=>string(29)"http://URL to site's icon"
			}
			["metatags"]=>
			array(6){
				["fbAppId"]=>string(15)"Facebook App Numerical ID"
				["ogTitle"]=>string(95)"Original title of page"
				["ogType"]=>string(7)"article"
				["ogUrl"]=>string(29)"http://example.com/page"
				["ogImage"]=>string(28)"URL to Page icon"
				["ogSiteName"]=>string(8)"Example Dot Com"
			}
		}
		
	}
}
*/

$htmlRes=array();
$today = getdate();
$today = $today['year'].'-'.$today['mon'].'-'.$today['mday'];
if (isset($results)){
	#Get Titles and Content		
	
	foreach($results['results'] as $key=>$var){

		$htmlRes[$key]['link']=$var['url'];
		$htmlRes[$key]['date']=$today;

		#$htmlRes[$key]='<div class="res"><div class="title"><a href="'.$var['url'].'" target="_blank">'.$var['title'].'</a></div><div class="snip">'.$var['content'].'</div><div class="url">'.$var['formattedUrl'].'</div></div>';
		
		if($var['visibleUrl']=='pastebin.com'){
			$pasteId = (parse_url($var['url']));
			$pasteId = $pasteId['path'];
			$pasteId = ltrim($pasteId, '/');
			if(strpos($pasteId, 'u/')===false){
				$htmlRes[$key]['content']=@file_get_contents('http://pastebin.com/raw.php?i='.$pasteId, false, $context);
			}
			
		}
		elseif($var['visibleUrl']=='pastie.org'){
			if(strpos($var['url'], 'pastes')=== false){
				$htmlRes[$key]['content']=@file_get_contents($var['url'].'/text', false, $context);
			}
			else{
				$pasteId = (parse_url($var['url']));
				$pasteId = $pasteId['path'];
				$pasteId = ltrim($pasteId, '/');
				$htmlRes[$key]['content']=@file_get_contents('http://pastie.org/pastes/'.$pasteId, false, $context);
			}
			
		}
	}
		
	
}


#echo '<pre>';
#var_dump($htmlRes);
#echo '</pre>';

#####
##### Access the DB
#####

$db = new SQLite3('./autopb/autopb.db');

$linkArray=array();
$links = $db->query('SELECT link FROM results');
while($lres=$links->fetchArray()){
	$linkArray[]=$lres['link'];
}

/*
echo '<pre>';
echo 'htmlRes:';
foreach ($htmlRes as $key=>$val){
	echo $val['link'].'<br/>';
	}
#var_dump($htmlRes);
echo '';
echo 'linkArray:';
var_dump($linkArray);
echo 'in_array results:';
foreach ($htmlRes as $key=>$val){
	if(!in_array($val['link'],$linkArray)){
		echo $val['link'].' is not in the following:';
		var_dump($linkArray);
	}
	else{
		echo $val['link'].' is in the linkArray';
		#var_dump($linkArray);
	}
}
echo '</pre>';
*/


foreach ($htmlRes as $key=>$val){
	if(!in_array($val['link'],$linkArray)){
		#echo $val['link'].' is NOT in the linkArray.</br>';
		if(isset($val['content'])){
			$val['link']=$db->escapeString($val['link']);
			$val['content']=$db->escapeString($val['content']);
			$insert = "'".$val['date']."', '".$val['link']."', '".$val['content']."'";
			$db->exec('INSERT INTO results (date,link,content) VALUES ('.$insert.')');
		}
		else{
			$insert = "'".$val['date']."', '".$val['link']."'";
			$db->exec('INSERT INTO results (date,link) VALUES ('.$insert.')');
		}
	}
	else{
		#echo $val['link'].' IS in the linkArray.</br>';
	}
	
	
}


$db->exec('DELETE FROM results WHERE id NOT IN (SELECT id FROM results ORDER BY id DESC LIMIT 25)');

$result = $db->query('SELECT * FROM results');
if(isset($result))
{	
	$i=0;
	
	while($res=$result->fetchArray()){;
		$row[$i]['link']=$res['link'];
		$row[$i]['date']=$res['date'];
		#$row[$i]['content']=$val['content'];
		$i++;
	}
	
	
}

#echo '<pre>';
#var_dump($row);
#echo '</pre>';

?>

<html>
<head>
	<title>AutoPastebin Results</title>
	<style>
	#body{
		width:1000px;
		margin-left:auto;
		margin-right:auto;
	}
	#head{
		text-align:center;
	}
	.even{
		background-color:aliceblue;
	}
	.link{
		width:450px;
		float:left;
		display:inline-block;
	}
	.date{
		width:500px;
		text-align:right;
		display:inline-block;
	}
	</style>
</head>
<body>
<div id="content">
	<div id="head">
		<h1>AutoPastebin Results</h1>
	</div>
	<div id="body">
	<?
		foreach($row as $key=>$val){
			if($key % 2 === 0){
				echo "<div class='row even'>";
					echo "<div class='link'><a href='".$val['link']."' target='_blank'>".$val['link']."</a></div>";
					echo "<div class='date'>".$val['date']."</div>";
				echo "</div>";
			}
			else{
				echo "<div class='row odd'>";
					echo "<div class='link'><a href='".$val['link']."' target='_blank'>".$val['link']."</a></div>";
					echo "<div class='date'>".$val['date']."</div>";
				echo "</div>";
			}
		}
	?>
	</div>
</div>
<? include './footer.php'?>
</body>
</html>
