<?php

$basedir = dirname(__FILE__) . '/data';

$count = 0;

$files1 = scandir($basedir);

foreach ($files1 as $directory1)
{
	if (preg_match('/^[a-z0-9]{2}$/i', $directory1))
	{	
		/*
		$files2 = scandir($basedir . '/' . $directory1);

		foreach ($files2 as $directory2)
		{
			if (preg_match('/^[a-z0-9]{2}$/i', $directory2))
			{
				$files3 = scandir($basedir . '/' . $directory1 . '/' . $directory2);
			
				foreach ($files3 as $directory3)
				{
					if (preg_match('/^[a-z0-9]{2}$/i', $directory3))
					{
		*/
						//$files = scandir($basedir . '/' . $directory1 . '/' . $directory2 . '/' . $directory3);
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
						
						
		/*
					}
				}
			}
		}
		*/
	}
}

echo $count . "\n";

?>
