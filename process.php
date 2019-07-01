<?php

// Grab files from data directory and process

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
				$json = file_get_contents($basedir . '/' . $directory1 . '/' . $filename);
			
				//echo $json;
			
				$count++;
		
			}
		}
	}
}

echo $count . "\n";

?>
