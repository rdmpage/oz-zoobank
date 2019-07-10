<?php

// Attempt to match citation string to DOI

//----------------------------------------------------------------------------------------
function find_doi($string)
{
	$doi = '';
	
	$url = 'https://mesquite-tongue.glitch.me/search?q=' . urlencode($string);
	
	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	if ($data != '')
	{
		$obj = json_decode($data);
		
		//print_r($obj);
		
		if (count($obj) == 1)
		{
			if ($obj[0]->match)
			{
				$doi = $obj[0]->id;
			}
		}
		
	}
	
	return $doi;
			
}

//----------------------------------------------------------------------------------------
function find_openurl($reference)
{
	$identifiers = new stdclass;
	
	$parameters = array();
	
	if (isset($reference->title) && ($reference->title != ''))
	{
		$atitle = $reference->title;
		$atitle = strip_tags($atitle);

		$parameters ['atitle'] = $atitle;
	}
	
	
	if (isset($reference->parentreference) && ($reference->parentreference != ''))
	{
		// clean
		$title = $reference->parentreference;
		$title = preg_replace('/,\s+\(.*\)$/u', '', $title);
		
		$parameters ['title'] = $title;
	}

	if (isset($reference->volume) && ($reference->volume != ''))
	{
		$parameters ['volume'] = $reference->volume;
	}

	if (isset($reference->startpage) && ($reference->startpage != ''))
	{
		$parameters ['spage'] = $reference->startpage;
	}
	
	$url = 'http://localhost/~rpage/microcitation/www/api_openurl.php?' . http_build_query($parameters);
	
	echo "\n$url\n";	
	
	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	if ($data != '')
	{
		$obj = json_decode($data);
		
		//print_r($obj);
		
		if ($obj->found)
		{
			if (isset($obj->results[0]->doi))
			{
				$identifiers->doi = $obj->results[0]->doi;
			}

			if (isset($obj->results[0]->handle))
			{
				$identifiers->handle = $obj->results[0]->handle;
			}

			if (isset($obj->results[0]->jstor))
			{
				$identifiers->jstor = $obj->results[0]->jstor;
			}


		}
		
	}
	
	
	return $identifiers;
			
}

?>
