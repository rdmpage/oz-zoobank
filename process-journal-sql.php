<?php

// Grab files from data directory and process
// to dump SQL to update other databases



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
				
				if (isset($obj->parentreference))
				{
					$journal = ".*";		// default
					$journal = "ZooKeys";	
					$journal = "Records of the Australian Museum";
					$journal = "Invertebrate Systematics";
					
					$journal = "Zootaxa";
					$journal = "Bulletin of The British Arachnological Society";
					$journal = "Genus";
										
					if (preg_match('/' . $journal . '/i', $obj->parentreference))
					{
						$keys = array();
						
						// Export for AFD
						
						$keys[] = 'PUB_PARENT_JOURNAL_TITLE="' . addcslashes($journal, '"') . '"';

						if (1)
						{
							if (isset($obj->volume) && ($obj->volume != ''))
							{
								$keys[] = 'volume="' . addcslashes($obj->volume, '"') . '"';
							}

							if (isset($obj->startpage) && ($obj->startpage != ''))
							{
								$keys[] = 'spage="' . addcslashes($obj->startpage, '"') . '"';
							}
							
							if (count($keys) >= 3)
							{
								echo 'UPDATE bibliography SET zoobank="' . strtolower($obj->referenceuuid) . '" WHERE ' . join(' AND ', $keys) . ';' . "\n";
							}							
							
						}
						else
						{						
							// DOI matching
							if (isset($obj->doi))
							{
								$keys[] = 'doi="' . $obj->doi . '"';
							}
							
							if (count($keys) >= 2)
							{
								echo 'UPDATE bibliography SET zoobank="' . strtolower($obj->referenceuuid) . '" WHERE ' . join(' AND ', $keys) . ';' . "\n";
							}							
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
