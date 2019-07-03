<?php

// Grab files from data directory and process
// looking for possible problems , such as bad DOIs

require_once (dirname(__FILE__) . '/doi.php');


$fixes = array(
'067317CC-B719-4FF4-BCB3-D58335F41452' => '10.1371/journal.pone.0099072',
'0FDB780F-D990-43FC-8D5A-67BAD14D8AE5' => '10.1371/journal.pone.0131856',
'EE092893-8AFC-4100-AD2B-B0BC0886B823' => '10.1051/acarologia/20102013',
'4C7646AB-6352-4562-9B32-116761F75C15' => '10.5169/seals-401495',
);

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
					$journal = 'Invertebrate Systematics';	
					$doi_prefix = '(10.1071)';		
					
					// PLoS still several missing
					$doi_prefix = '10.1371\/journal'; 	// default
					$journal = "^Public Library of Science";		// default
						
					$doi_prefix = '10.3897'; 
					$doi_prefix = '^((?!fig).)*$';
					$doi_prefix = '^((?!supp).)*$';
					$journal = "ZooKeys";	
					
					// Most of these will need to be matched in JSTOR
					$journal = "Journal of Arachnology";
					$doi_prefix = '10.1636';
					
					$journal ='South African Journal of Marine Science';
					$doi_prefix = '10\.'; 
					
					$journal ='Insect Systematics & Evolution';
					$doi_prefix = '10\.'; 

					$journal ='Copeia';
					$doi_prefix = '(10.2307|10.1643)'; 
					
					$doi_prefix = '10\.'; 	// default
					$journal = ".*";		// default
					
					// Recent issues of this journal have DataCite DOIs FFS!
					$journal = 'Acarologia';
					$doi_prefix = '10.1051'; 
					
					
					// Chinese DOI agency (will need to do this differently)
					$journal = 'acta zootaxonomica sinica';
					$doi_prefix = '10.3969';

	
					// Datacite
					$journal = 'Beiträge zur Entomologie';
					$doi_prefix = '10.21248';
					
					// Recent issues of this journal have DataCite DOIs FFS!
					$journal = 'Acarologia';
					$doi_prefix = '(10.1051|10.24349)'; 
					
					$journal = 'Mitteilungen der Schweizerischen Entomologischen Gesellschaft';
					$doi_prefix = '10.5169'; 
					
					
					if (preg_match('/^' . $journal . '/i', $obj->parentreference) 
					//&& ($obj->year==2018)
					)
					{
						// echo $obj->parentreference;
						
						$modified = false;						
						
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
									
									
									// force try again
									unset($obj->doi);
									$modified = true;
									
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
							
							$lookup_crossref = true;
							
							$datacite_prefixes = '/(10.21248|10.24349|10.5169)/';
							
							$other_prefixes = '';
							
							if (preg_match($datacite_prefixes, $doi_prefix))
							{
								$lookup_crossref = false;
							}
														
							if ($lookup_crossref)
							{
								$doi = find_doi($obj->value);
							}
							else
							{
								$doi = find_openurl($obj);
							}
														
							echo "DOI=$doi\n";	
							
							if ($doi == '')
							{
								if (isset($fixes[$obj->referenceuuid]))
								{
									$doi = $fixes[$obj->referenceuuid];
								}
							}
							
							// Add to object and save file
							if ($doi != '')
							{
								if (preg_match('/' . $doi_prefix . '/', $doi))
								{						
									$obj->doi = $doi;
									$modified = true;
								}
								
							}
						}
						
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
