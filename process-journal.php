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
				
				if (isset($obj->parentreference))
				{
					$doi_prefix = '10\.'; 	// default
					$journal = ".*";		// default
					
					//if (preg_match('/^Sys.* B.*/i', $obj->parentreference))

					//if (preg_match('/^Zootaxa/i', $obj->parentreference))
					//if (preg_match('/^Zookeys/i', $obj->parentreference))
					// if (preg_match('/^Invert/i', $obj->parentreference))
					
					$journal = 'Journal of Natural History';
					$doi_prefix = '10.1080';	
					
					$journal = 'Zoological Journal of the Linnean Society';	
					$doi_prefix = '(10.1111|10.1093|10.1046)';		
					
					$journal = 'Invertebrate Taxonomy';	
					$doi_prefix = '(10.1071)';			
	
					
					if (preg_match('/^' . $journal . '/i', $obj->parentreference))
					{
						// echo $obj->parentreference;
						
						
						if (isset($obj->doi))					
						{
							echo $obj->doi . "\n";						
						
							// Check DOI makes sense
							if ($doi_prefix != '')
							{
								if (!preg_match('/' . $doi_prefix . '/', $obj->doi))
								{
									echo "\nWrong prefix\n";
									echo $obj->lsid . "\n";
									echo $obj->value . "\n";
									echo $obj->doi . "\n";
									
									
									// try again
									$doi = find_doi($obj->value);
									echo "DOI=$doi\n";	
									
									
									echo "-----\n";
									
								}
							}
						}
						
						// No DOI?
						if (!isset($obj->doi))
						{
							echo "\nLooking for DOI\n";
							echo $obj->lsid . "\n";
							echo $obj->value . "\n";
							$doi = find_doi($obj->value);
							echo "DOI=$doi\n";	
							
							// Add to object and save file
							if ($doi != '')
							{
								if (preg_match('/' . $doi_prefix . '/', $doi))
								{						
									$obj->doi = $doi;
								
									file_put_contents($full_filename, json_encode($obj));
								}
								
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
