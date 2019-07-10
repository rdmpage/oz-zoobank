<?php

/*

Process UUIDs and add DOIs

*/

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/doi.php');
require_once (dirname(__FILE__) . '/utils.php');


$uuids=array(
'22334107-0784-466E-8288-D6E29F87F6E2'
);


$force = false;

$basedir = dirname(__FILE__) . '/data';

foreach ($uuids as $uuid)
{
	echo $uuid  . "\n";
	
	$modified = false;
	
	$uuid_path = create_path_from_sha1($uuid, $basedir);
	
	$filename = $uuid_path . '/' . $uuid . '.json';
	
	$json = file_get_contents($filename);
				
	$obj = json_decode($json);
	
	//print_r($obj);
	
	if (!isset($obj->doi))
	{
		$doi = '';
		$handle = '';
		$jstor = '';
	
		echo "\nLooking for DOI\n";
		echo $obj->lsid . "\n";
		echo $obj->value . "\n";
		
		$lookup_crossref = true; // default

		//$lookup_crossref = false; // force local search
											
		if ($lookup_crossref)
		{
			echo "CrossRef\n";
			$doi = find_doi($obj->value);
		}
		else
		{
			echo "LOCAL\n";
			$ids = find_openurl($obj);
			
			//print_r($ids);
			
			if (isset($ids->doi))
			{
				$doi = $ids->doi;
			}
			if (isset($ids->handle))
			{
				$handle = $ids->handle;
				echo "Handle=$handle\n";
			}
			if (isset($ids->jstor))
			{
				$jstor = $ids->jstor;
				echo "JSTOR=$jstor\n";
			}
		}
									
		
		
		
		// Add to object and save file
		if ($doi != '')
		{
			echo "DOI=$doi\n";	
		
			$obj->doi = $doi;
			$modified = true;
		}
		
		if ($handle != '')
		{
			echo "Handle=$handle\n";
		
			//$obj->doi = $doi;
			//$modified = true;
		}

		if ($jstor != '')
		{
			echo "JSTOR=$jstor\n";
		
			//$obj->doi = $doi;
			//$modified = true;
		}
		
		//rint_r($obj);
		
		if ($modified)
		{
			file_put_contents($filename, json_encode($obj));
		}		
		
		
			
	}	

}

?>
