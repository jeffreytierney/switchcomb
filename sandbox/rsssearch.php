<?php

	set_time_limit(0);
	$sitename = "RSS Search";
	$title = "RSS Search";
	$query = isset($_GET["q"]) ? $_GET["q"] : false;
	if($query) {
		$start = 0;
		$count=10;
		$lang="en";
		$region="us";
		$siteresultsurl = "http://boss.yahooapis.com/ysearch/web/v1/" . urlencode($query) . "?appid=_bJsHmjV34Eqdx7lSprvxeBw_SM21Evh879a1zrqDpF2RPkWv6VArfurI4p6RfeVtV.mUZsb&lang=$lang&region=$region&start=$start&count=$count";
		
		$boss_results = getBoss($siteresultsurl);
		foreach($boss_results as $id=>$result) {
			$result["rss"] = checkForRss($result["url"]);
			$results[] = $result;
		}
	}
	else $results = array();

	function getBoss($url) {
		global $json;
	
		$handle = fopen($url, "r");
		$contents = stream_get_contents($handle);
		fclose($handle);
		
		$myArray = json_decode($contents);
		$myArray = get_object_vars($myArray);
		if (isset($myArray["ysearchresponse"])) {
			$response_obj = get_object_vars($myArray["ysearchresponse"]);
			foreach($response_obj as $result_id=>$result_data) {
				//echo $result_id . "<br>";
			}
		}
		else {
			//echo "blah - nothing";
			return array();
		}
		if (isset($response_obj["resultset_web"])) {
			//$result_obj = get_object_vars($response_obj["resultset_web"]);
			$result_obj = $response_obj["resultset_web"];
			$return_array = array();
			foreach($result_obj as $result_id=>$result_data) {
				$return_array[] = get_object_vars($result_data);
			}
			return $return_array;
		}
		return array();
	}
	
	function checkForRss($url) {
		$handle = fopen($url, "r");
		$contents = stream_get_contents($handle);
		fclose($handle);
		
		$return_array = array();
		
		$rss_re = "/\<link.*href=\"([^\"]*)\"[^\>]+\/\>/i";
		$type_re = "/type\=\"application\/(?:rss|atom)\+xml\"/i";
		
		$total_matches = preg_match_all($rss_re, $contents, $matches);
		if($matches) {
			
			
			for($i=0; $i<sizeof($matches[0]); $i++) {
				//$type_matches = array();
				preg_match($type_re, $matches[0][$i], $type_matches);
				if ($type_matches) {
					$return_array[] = $matches[1][$i];
				}
			}
			
		}
		return $return_array;
		
		//<link rel="alternate" type="application/atom+xml" title="Gmail Atom Feed" href="feed/atom" />

	}
	

?>


<html>
	<head>
		<title><?= $title ?></title>
		
		
	</head>
	<body>
		<form>
			<input type="text" name="q" value="<?php echo urldecode($query) ?>"/>
			<input type="submit" value="Search" />
		</form>
		<div id="results">
			<?php foreach($results as $id=>$result): ?>
				<?php if($result["rss"]): ?>
					<a href="<?php echo $result["url"] ?>"><?php echo $result["title"] ?></a> - <?php echo $result["url"] ?><br/>
						<ul>
						<?php foreach($result["rss"] as $rss_id=>$rss_result): ?>
							<li><?php echo $rss_result ?></li>
						<?php endforeach; ?>
						</ul>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</body>
</html>
