<?php

/*

Harvest ZooBank Reference uuids from list

*/

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');

$uuids=array(
//'D338443D-5D0A-44E0-81DB-E9AC07310403',
//'D6547090-2354-428C-B7EA-59C8B3795394'
//'F2DEB5D3-A2EC-4599-8595-E57328D502E5'
'327D7F9C-1ACC-4323-9D03-8D3A7B2E83D8'
);


$force = false;

$basedir = dirname(__FILE__) . '/data';

foreach ($uuids as $uuid)
{
	echo $uuid  . "\n";
	
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
		// do we have it in CouchDB?
		if ($couch->exists($uuid))
		{
			echo "We have in CouchDB\n";
			$url = $config['couchdb_options']['prefix']
				. $config['couchdb_options']['host']
				. ':'
				. $config['couchdb_options']['port']
				. '/'
				. $config['couchdb_options']['database']
				. '/'
				. $uuid;
				
			$json = get($url);						
			
			$obj = json_decode($json);
			unset($obj->_id);
			unset($obj->_rev);
			
			$json = json_encode($obj);
			
			//echo $json;
		}
		else
		{
			echo "Fetch\n";
			$url = 'http://zoobank.org/References.json/' . strtolower($uuid);	
			$json = get($url);	
			
			// Remove array
			$json = preg_replace('/^\[/u', '', $json);				
			$json = preg_replace('/\]$/u', '', $json);				
		}
		
		file_put_contents($filename, $json);
	}
	
}

?>
