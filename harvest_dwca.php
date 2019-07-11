<?php

/*

Harvest reference uuids from ZooBnak DWCA file, then fetch data either form ZooBank 
or from local CouchDB we have from earlier work.


*/

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/utils.php');

// Parse a TSV file and extract references
$filename = 'dwca-zoobank-v1/taxon.txt';
$filename = 'dwca-zoobank-v1.378/taxon.txt'; // https://doi.org/10.15468/wkr0kn
$filename = 'dwca-zoobank-v1.392/taxon.txt'; // http://zoobank.org:8080/ipt/resource?r=zoobank

$keys = array();
$index_to_key = array();


$force 		= false;
$doi_lookup = true;

$count = 0;

$skip_list = array(
'2c6327e1-5560-4db4-b9ca-76a0fa03d975'
);

$basedir = dirname(__FILE__) . '/data';

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = trim(fgets($file_handle));
		
	$parts = explode("\t",$row);
	
	if (count($parts) <= 1) break;
	
	if ($count == 0)
	{
		$keys = $parts;
		
		$n = count($keys);
		for ($i = 0; $i < $n; $i++)
		{
			$index_to_key[$keys[$i]] = $i;
		}
		
		/*
		print_r($parts);
		print_r($index_to_key);
		exit();
		*/
	}
	else
	{
		
		$uuid = $parts[$index_to_key['namePublishedInID']];
		
		if ($uuid != '')
		{			
			if (!in_array($uuid, $skip_list))
			{
				//lookup_uuid($namePublishedInID, $force, $doi_lookup);
				
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
						
						if ($json != '')
						{
							$obj = json_decode($json);
							
							if (is_array($obj))
							{
								$obj = $obj[0];
							}
							
							$json = json_encode($obj);
						}
					}
					
					if ($json != '')
					{
						file_put_contents($filename, $json);
					}
				}


			}
		}
	}
	
	$count++;
	
	
	if ($count > 20000) 
	{
		//exit();
	}
	
}

?>
