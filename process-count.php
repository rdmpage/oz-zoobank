<?php

// Template for grabbing files from data directory and processing

$basedir = dirname(__FILE__) . '/data';



$containers = array();

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
				
				$obj = json_decode($json);
			
				if (isset($obj->parentreference))
				{
					if (!isset($containers[$obj->parentreference]))
					{
						$containers[$obj->parentreference] = 0;
					}
					$containers[$obj->parentreference]++;
				}
		
			}
		}
	}
}

//print_r($containers);

$cutoff = 100;
foreach ($containers as $k => $v)
{
	if ($v > $cutoff)
	{
		echo "$k $v\n";
	}
}

?>
