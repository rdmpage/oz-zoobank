<?php

// Template for grabbing files from data directory and processing
// restricting list by date (e.g., )
// Add DOIs to recent files

require_once (dirname(__FILE__) . '/doi.php');


$basedir = dirname(__FILE__) . '/data';

$count = 0;

$files1 = scandir($basedir);

$day = 60 * 60 * 24;

$stime = time() - (2 * $day); // 3 days



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
			
					$obj = json_decode($json);
	
					if (is_array($obj))
					{
						$obj = $obj[0];
						$modified = true;
					}
	
					print_r($obj);
	
					if (!isset($obj->doi))
					{
						$modified = false;
				
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
							file_put_contents($fullfilename, json_encode($obj));
						}	
					}					
			
					$count++;
				}
		
			}
		}
	}
}

echo $count . "\n";

?>
