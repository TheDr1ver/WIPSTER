<html>
<head>
	<link rel="stylesheet" type="text/css" href="./css/pastebin.css">
	<?
		
		#####
		##### GET VARS
		#####
		require './func/config.php';
		
		#####
		##### BUILD FUNCTIONS
		#####
		function pasteSearch($_POST){
			$newURL = './pastebinsearch.php?q='.urlencode($_POST['q']).'&p=0';
			header('Location: '.$newURL);
			die();
		}
		
		#####
		##### RUN FUNCTIONS
		#####
		
		if ($_SERVER['REQUEST_METHOD']=='POST'){
			$error=pasteSearch($_POST);
			#echo '<pre>'.var_dump($error).'</pre>';
		}
		
		#####
		##### GET SEARCH
		#####
		
		#echo '<pre>';
		if(isset($_GET['q'])){
			$query = urlencode($_GET['q']);
		}
		else{
			$query = '';
		}
		
		if (isset($_GET['p'])){
			$page = urlencode($_GET['p']);
		}
		else{
			$page=0;
		}
		#echo "query: ".$query;
		#echo "page: ".$page;
		
		if ($query!=''){
			
			$opts=array(
				'http'=>array(
					'method'=>"GET",
					'header'=>"Accept-language: en\r\n".
							"Referer: https://www.google.com/\r\n".
							"User Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36"
				)
			);
			$context = stream_context_create($opts);
			
			$search = file_get_contents('https://www.googleapis.com/customsearch/v1element?key='.$gcseKey.'&rsz=filtered_cse&num=10&hl=en&prettyPrint=false&source=gcsc&gss=.com&sig='.$gcseSig.'&cx='.$gcseCx.'&q='.$query.'&start='.$page.'&sort=date&googlehost=www.google.com&callback=google.search.Search.apiary13655&nocache=1395785766092', false, $context);
			$search = strstr($search, '(');
			$search = substr($search, 1, -2);
			#$search = strstr($search, ')');
			#echo '<br/>';
			#echo '######################RAW######################';
			#echo $search;
			#echo '<br/>';
			#echo '######################OBJ######################';
			#var_dump(json_decode($search));
			#echo '######################ARR######################';
			$results = json_decode($search, true);
			#var_dump($results);
			#echo '</pre>';
		}
		
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
		$htmlPages=array();
		if (isset($results)){
			#Get Titles and Content		
			
			foreach($results['results'] as $key=>$var){
				if(isset($var['cacheUrl'])){
					$htmlRes[$key]='<div class="res"><div class="title"><a href="'.$var['url'].'" target="_blank">'.$var['title'].'</a></div><div class="snip">'.$var['content'].'</div><div class="url">'.$var['formattedUrl'].'</div><div class="cache"><a href="'.$var['cacheUrl'].'" target="_blank">Cached Site</a></div></div>';
				}
				else{
					$htmlRes[$key]='<div class="res"><div class="title"><a href="'.$var['url'].'" target="_blank">'.$var['title'].'</a></div><div class="snip">'.$var['content'].'</div><div class="url">'.$var['formattedUrl'].'</div></div>';
				}
				
			}
			#Get Page Links
			
			if(isset($results['cursor']['pages'])){
				foreach($results['cursor']['pages'] as $key=>$var){
					if($var['start']===$page){
						$htmlPages[$key]="<span class='page'><b><i>".$var['label']."</a></i></b></span>";
					}
					else{
						$htmlPages[$key]="<span class='page'><a href='./pastebinsearch.php?q=".$query."&p=".$var['start']."'>".$var['label']."</a></span>";
					}
				
				}
			}
			else{
				$htmlPages[0]="<span class='page'>No results found.</span>";
			}
			
		}
		
		
	?>
</head>
<body>
	<div id="container">
		<div id="header">
			<h1>Search Various Pastebin Sites</h1>
			<p>Currently searches pastebin.com/* paste.frubar.net/* pastie.org/* leakedin.com/* slexy.org/*</p>
			<? #Public URL: https://www.google.com:443/cse/publicurl?cx=[Search Engine ID] ?>
			<? 
				if (isset($_GET['q'])){
					echo "<h3>Results for ".htmlentities($_GET['q'])." :</h3>";
				}
			?>
		</div>
		<div id="body">
			<div id="search">
			<!--
			<script>
			  (function() {
				var cx = '005542750775998057842:2h4oumzf_js';
				var gcse = document.createElement('script');
				gcse.type = 'text/javascript';
				gcse.async = true;
				gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
					'//www.google.com/cse/cse.js?cx=' + cx;
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(gcse, s);
			  })();
			</script>
			<gcse:searchresults-only></gcse:searchresults-only>
			-->
			<form action="./pastebinsearch.php" method="post">
				Search: 
				<input type="text" name="q" value='<? echo $gcseQuery; ?>' size="25" required />
				<input type="submit" name="submit" value="Search" />
			</form>
			</div>
			<div class="pages">
				<?
					foreach($htmlPages as $key=>$var){
						echo $var;
					}
				?>
			</div>
			<div id="results">
				<?
					foreach ($htmlRes as $key=>$var){
						echo $var;
					}
				?>
			</div>
			<div class="pages">
				<?
					foreach($htmlPages as $key=>$var){
						echo $var;
					}
				?>
			</div>
		</div>
		
	</div>
	<? include './footer.php';?>
</body>
</html>
