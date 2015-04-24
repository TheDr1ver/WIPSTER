<!-- WIPSTER - Web Interface Portal & Security Threat Engine for REMnux -->
<!-- Pieced together by Nick Driver (@TheDr1ver) using REMnux and various other FOSS -->

<HTML>
	<HEAD>
		<TITLE>WIPSTER Beta - Web Interface Portal & Security Threat Engine for REMnux</TITLE>
		<LINK href="./css/index.css" rel="stylesheet" type="text/css">
	</HEAD>
	<BODY>
		<div id="container">
			<div id="header">
				<h1>WIPSTER Beta Dashboard</h1>
			</div>
			<div id="content">
				<div id="mastiff">
					<h1>WIPSTER</h1>
					<ul>
						<li><a href="./mastiffResults.php">View WIPSTER Results Dashboard</a></li>
						<?
						require('./func/config.php');
						if($critsPlugin==True){
							echo "<li><a href='./crits-upload.php'>Submit files to WIPSTER</a><br/></li>";
						}
						else{
							echo "<li><a href='./upload2.html'>Submit files to WIPSTER</a><br/></li>";
						}
						?>
						<li><a href="./search.php">Search WIPSTER Database</a></li>
					</ul>
				</div>
				<div id="urls">
					<h1>URL Research</h1>
					<ul>
						<li><a href="./urlResearch.php">Submit a URL</a></li>
						<li><a href="./urlResults.php">Recent URLs</a></li>
					</ul>
				</div>
				<div id="convert">
					<h1>Other Tools</h1>
					<ul>
						<li><a href="./convert.php">Convert Strings to Various Other Formats</a></li>
						<li><a href="./pastebinsearch.php">Search Various Pastebin Sites</a></li>
						
					</ul>
				</div>
			</div>
		</div>
	</BODY>
	<? include './footer.php';?>
</HTML>
