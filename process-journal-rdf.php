<?php

error_reporting(E_ALL ^ E_DEPRECATED);

// Grab files from data directory and process
// and generate RDF

require_once (dirname(__FILE__) . '/tojsonld.php');

$basedir = dirname(__FILE__) . '/data';

$count = 0;

$files1 = scandir($basedir);

foreach ($files1 as $directory1)
{
	if (preg_match('/^[a-z0-9]{2}$/i', $directory1))
	{
		$files = scandir($basedir . '/' . $directory1);
		//print_r($files);
	
		foreach ($files as $filename)
		{
			if (preg_match('/\.json/', $filename))
			{
				//echo $filename . "\n";
				
				$full_filename = $basedir . '/' . $directory1 . '/' . $filename;
				
				$json = file_get_contents($full_filename);
				
				if ($json == '') 
				{
					echo $filename . "\n";
					exit();
				}
							
				$obj = json_decode($json);
				
				if ($obj)
				{				
					zoobank_to_jsonld($obj);
				}
				
				/*
				if (isset($obj->parentreference))
				{

					//if (preg_match('/^Zootaxa/i', $obj->parentreference))
					//if (preg_match('/^Zookeys/i', $obj->parentreference))
					// if (preg_match('/^Invert/i', $obj->parentreference))
					
					$journal = 'Journal of Natural History';
					$journal = 'Beiträge zur Entomologie';
					
					if (preg_match('/^' . $journal . '/i', $obj->parentreference))
					{

						zoobank_to_jsonld($obj);
						
					}
				}	
				*/
						
				$count++;
		
			}
		}
	}
}

?>
