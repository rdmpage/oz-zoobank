<?php

// Grab files from data directory and process
// by deleteing bad DOIs

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
					
					$doi_prefix ='(10.1002\/9781444304879.fmatter|10.1080\/00222939109460407|10.1038\/040414c0|10.1163\/2468-1733_shafr_sim050070059|10.1017\/cbo9781107447271.004|10.1017\cbo9781139236683|10.1017\/cbo9781107326286.001|10.9783\/9781512815849-005|10.1017\/cbo9781139245746.015)';
					
					$doi_prefix = '(10.2476\/asjaa.36.25|10.12782\/specdiv.6.23|10.2307\/1445089|10.11646\/zootaxa.399.1.1|10.17161\/pcns.1808.3761)';
										
					if (preg_match('/' . $doi_prefix . '/i', $obj->doi))
					{			
						echo $obj->value . "\n";		
						echo $obj->doi . "\n";
						
						unset($obj->doi);
						$modified = true;

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
