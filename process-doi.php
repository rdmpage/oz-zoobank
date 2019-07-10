<?php

// Grab files from data directory and process
// looking for possible problems , such as bad DOIs

require_once (dirname(__FILE__) . '/doi.php');


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
			
				//echo $json;
				
				$obj = json_decode($json);
				
				//echo ".";
				
				$modified = false;	
				
				if (isset($obj->doi))
				{
					$doi_prefix = '10.5962\/bhl'; 	// BHL
					$doi_prefix = '10.1163';
										
					if (preg_match('/' . $doi_prefix . '/i', $obj->doi)
						&& ($obj->year < 1800)
					)
					{			
						echo $obj->value . "\n";		
						echo $obj->doi . "\n";
						
						unset($obj->doi);
						//$modified = true;

						if ($modified)
						{
							file_put_contents($full_filename, json_encode($obj));
						}
						
					}
				}			
				$count++;
		
			}
		}
	}
}

echo $count . "\n";

?>
