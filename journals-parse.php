<?php

error_reporting(E_ALL);
require_once (dirname(__FILE__) . '/vendor/autoload.php');
use Sunra\PhpSimple\HtmlDomParser;


$basedir = dirname(__FILE__) . '/html';

$files = scandir($basedir);

// zoobank
$index = array_search('1f938786-dcc5-4d49-b6c1-b05534f5154d.html', $files); // search the value to find index
if($index !== false){
   unset($files[$index]);  // $arr = ['b', 'c']
}

// 50652332-e742-43b6-ba21-73e10015faa5
$index = array_search('50652332-e742-43b6-ba21-73e10015faa5.html', $files); // search the value to find index
if($index !== false){
   unset($files[$index]);  // $arr = ['b', 'c']
}

// 78f99150-21c2-4639-b359-f3e2302df0b7
$index = array_search('78f99150-21c2-4639-b359-f3e2302df0b7.html', $files); // search the value to find index
if($index !== false){
   unset($files[$index]);  // $arr = ['b', 'c']
}

// 91bd42d4-90f1-4b45-9350-eef175b1727a
$index = array_search('91bd42d4-90f1-4b45-9350-eef175b1727a.html', $files); // search the value to find index
if($index !== false){
   unset($files[$index]);  // $arr = ['b', 'c']
}
// debugging
//$files=array('4c657076-9e76-4aef-80da-b381776faddd.html');



foreach ($files as $filename)
{
	echo "filename=$filename\n";
	
	if (preg_match('/\.html$/', $filename))
	{	
		$html = file_get_contents($basedir . '/' . $filename);
		
		$dom = HtmlDomParser::str_get_html($html);
		
		// Name
		foreach ($dom->find('h2[class=actName]') as $h2)
		{
			echo trim($h2->plaintext) . "\n";
		}	


		// Get ISSN
		foreach ($dom->find('tr') as $tr)
		{
			foreach ($tr->find('th[scope=row]') as $th)
			{
				if (preg_match('/ISSN/', $th->plaintext))
				{
					echo $th->plaintext . "\n";
					
					foreach ($tr->find('td') as $td)
					{
						echo $td->plaintext . "\n";
					}
				
				}
			}
		
		}

		
	}

}

		
?>

