<?php

// Testing RDF output

require_once(dirname(__FILE__) . '/tojsonld.php');


$basedir = dirname(__FILE__) . '/data';

// flies
$filename = '/14/14400153-ac4e-4385-b257-1468a2fd81be.json';

//$filename = '/f8/f8ddbcc1-c2d1-48f7-be99-55d9ed4c2234.json';

// fish, my author parsing of AFD has broken (first author has only one name)
//$filename = '/29/291cad28-fea2-4c4b-b638-cb8b66807c28.json';

// journal
//$filename = '/04/04F84E9F-352F-414B-8CDE-6F4C58C7753F.json';

// snails in zookeys
//$filename = '/02/02943d33-6d53-4cb6-a6bd-47526ec80c67.json';

// swiftlet subspecies
//$filename = '/D3/D338443D-5D0A-44E0-81DB-E9AC07310403.json';

// blind fish (no  nomen acts!!)
//$filename = 'D6/D6547090-2354-428C-B7EA-59C8B3795394.json';

$filename = '/ef/ef2e2649-a046-47b5-a4a8-fc2e0fa683e0.json';

//$filename = 'd2/d207fa08-c30c-4993-9870-0f60674f2744.json';

$filename = 'F2/F2DEB5D3-A2EC-4599-8595-E57328D502E5.json';

$filename = '32/327D7F9C-1ACC-4323-9D03-8D3A7B2E83D8.json';

$filename = '8c/8c2a02a8-31cd-4615-8d0d-a63fa46870bb.json';
$filename = 'd3/d3b2a85b-82ca-45ef-9e13-2c68032a20b1.json';

$filename = 'd7/d7588a4e-d06e-4524-bb49-ac16c3fec849.json';


$json = file_get_contents($basedir . '/' . $filename);

echo $json;

$obj = json_decode($json);

zoobank_to_jsonld($obj, 'jsonld');
//zoobank_to_jsonld($obj);

?>
