<?php

// get from RSS
// horribly out of date :(

require_once('./utils.php');


$rss = 'http://zoobank.org/rss/rss.xml';


$url = 'https://grateful-crawdad.glitch.me/feed?q='
	. urlencode($rss);
	
//echo $url . "\n";
	
$json = get($url);

//echo $json;

$obj = json_decode($json);

//print_r($obj);

$force = false;
//$force = true;

$basedir = dirname(__FILE__) . '/data';


foreach ($obj->items as $item)
{
	if (preg_match('/:pub:/', $item->url))
	{
		$uuid = $item->id;

		echo $uuid  . "\n";


		$uuid = strtolower($uuid);
	
	
		$uuid_path = create_path_from_sha1($uuid, $basedir);
	
		//echo $uuid_path  . "\n";
	
		$filename = $uuid_path . '/' . $uuid . '.json';
	
		echo $filename . "\n";
	
		$go = true;
	
		if (file_exists($filename) && !$force)
		{
			echo "File exists\n";
			$go = false;
		}
	
		if ($go)
		{
			$url = 'http://zoobank.org/References.json/' . strtolower($uuid);	
			$json = get($url);	
			
			if ($json != '')
			{
			
				// Remove array
				$json = preg_replace('/^\[/u', '', $json);				
				$json = preg_replace('/\]$/u', '', $json);
			}				
		}
		
		if ($json != '')
		{
			file_put_contents($filename, $json);
		}
	
	}
	
}

?>
