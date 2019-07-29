<?php

// Grab files from data directory and process
// to dump list of authors and ZB ids



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
			
				$obj = json_decode($json);
				
				//print_r($obj);
				
				if (isset($obj->authors))
				{
					foreach ($obj->authors as $author)
					{
						//print_r($author[0]);
					
						$author_id = strtoupper($author[0]->gnubuuid);
						
						$parts = array();
					
						if (isset($author[0]->givenname) && ($author[0]->givenname != ''))
						{
							$parts[] = $author[0]->givenname;
						}
						if (isset($author[0]->familyname) && ($author[0]->familyname != ''))
						{
							$parts[] = $author[0]->familyname;
						}

						if (count($parts) > 0)
						{	
							echo 'REPLACE INTO zoobank_authors(id, name) VALUES ("' . $author_id . '", "' . addcslashes(join(' ', $parts), '"') . '");' . "\n";						
						}		
					
					}
				}
				//exit();
						
				$count++;
		
			}
		}
	}
}

echo $count . "\n";

?>
