<?php

// Template for grabbing files from data directory and processing
// restricting list by date (e.g., )

$basedir = dirname(__FILE__) . '/data';

$count = 0;

$files1 = scandir($basedir);

$day = 60 * 60 * 24;

$stime = time() - (1 * $day); // 3 days

//$start_time = date("c", time() - $day);

//$start_time = date("c", time() - (60 * 60)); // hour



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
				
				$fullfilename = $basedir . '/' . $directory1 . '/' . $filename;
				
				$mtime = filemtime($fullfilename);
				
				$dtime = $mtime - $stime;
				
				if ($dtime > 0)
				{
					echo $filename . "\n";
					echo date("c", $mtime) . "\n";
					$json = file_get_contents($fullfilename);
			
					//echo $json;
			
					$count++;
				}
		
			}
		}
	}
}

echo $count . "\n";

?>
