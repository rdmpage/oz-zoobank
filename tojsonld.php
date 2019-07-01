<?php

// Grab files from data directory and process

require_once(dirname(__FILE__) . '/vendor/autoload.php');

require_once(dirname(__FILE__) . '/taxon_name_parser.php');


$basedir = dirname(__FILE__) . '/data';

$filename = '/14/14400153-ac4e-4385-b257-1468a2fd81be.json';

$filename = '/f8/f8ddbcc1-c2d1-48f7-be99-55d9ed4c2234.json';

//$filename = '/29/291cad28-fea2-4c4b-b638-cb8b66807c28.json';

//$filename = '/04/04F84E9F-352F-414B-8CDE-6F4C58C7753F.json';

$filename = '/02/02943d33-6d53-4cb6-a6bd-47526ec80c67.json';

// swiftlet subspecies
//$filename = '/D3/D338443D-5D0A-44E0-81DB-E9AC07310403.json';


$json = file_get_contents($basedir . '/' . $filename);

echo $json;

$obj = json_decode($json);

print_r($obj);

	// remove empty fields
	$to_delete = array();
	
	foreach ($obj as $k => $v)
	{
		if ($v == '')
		{
			$to_delete[] = $k;
		}
	}
	
	foreach ($to_delete as $k)
	{
		unset($obj->{$k});
	}

	$triples = array();
	
	$sameAs = array();
	
	$guid = $obj->lsid;
	
	/*
	// DOI
	if (preg_match('/^10\./', $guid))
	{
		$guid = 'https://doi.org/' . strtolower($guid);
		
		$sameAs[] = $guid;
	}
	*/
	
	$subject_id = $guid; // fix this

	$s = '<' . $subject_id . '>';
	
	$type = 'CreativeWork';
	
	switch ($obj->referencetype)
	{
		case 'Book':
			$type = 'Book';	
			break;

		case 'Book Section':
			$type = 'Chapter';	
			break;
	
		case 'Journal Article':
			$type = 'ScholarlyArticle';	
			break;
			
		case 'Periodical':
			$type = 'Periodical';
			break;
			
		default:
			$type = 'CreativeWork';	
			break;
	}
	
	$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/' . $type . '> .';
	
	if (isset($obj->title))
	{
		$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes(strip_tags($obj->title), '"') . '" .';		
	}
	
	if (isset($obj->year))
	{
		$triples[] = $s . ' <http://schema.org/datePublished> ' . '"' . $obj->year . '" .';		
	}
	
	if (isset($obj->volume))
	{
		$triples[] = $s . ' <http://schema.org/volumeNumber> ' . '"' . $obj->volume . '" .';		
	}

	if (isset($obj->number))
	{
		$triples[] = $s . ' <http://schema.org/issueNumber> ' . '"' . $obj->number . '" .';		
	}
	
	if (isset($obj->startpage))
	{
		$triples[] = $s . ' <http://schema.org/pageStart> ' . '"' . $obj->startpage . '" .';		
	}
	
	if (isset($obj->endpage))
	{
		$triples[] = $s . ' <http://schema.org/pageEnd> ' . '"' . $obj->endpage . '" .';		
	}
	
	if (isset($obj->pagination))
	{
		$triples[] = $s . ' <http://schema.org/pagination> ' . '"' . $obj->pagination . '" .';		
	}

	if (isset($obj->parentreferenceid))
	{
		$container_id = 'urn:lsid:zoobank.org:pub:' . $obj->parentreferenceid;
		$triples[] = $s . ' <http://schema.org/isPartOf> <' . $container_id  . '> . ';		
		
		if (isset($obj->parentreference))
		{
			$triples[] = '<' . $container_id . '> <http://schema.org/name> ' . '"' . addcslashes($obj->parentreference, '"') . '" .';						
		}
	}
	
	// Identifiers

	// uuid
	if (1)
	{
		$triples[] = $s . ' <http://schema.org/identifier> "' . strtolower($obj->referenceuuid) . '" .';	
	}

	// lsid
	$identifier_id = '<' . $subject_id . '#zoobank' . '>';
	
	$triples[] = $s . ' <http://schema.org/identifier> ' . $identifier_id . '.';			
	$triples[] = $identifier_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/PropertyValue> .';			
	$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"zoobank"' . '.';
	$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . $obj->referenceuuid . '"' . '.';
	
	
	// DOI
	if (isset($obj->doi))
	{
		$identifier_id = '<' . $subject_id . '#doi' . '>';
		
		$triples[] = $s . ' <http://schema.org/identifier> ' . $identifier_id . '.';			
		$triples[] = $identifier_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/PropertyValue> .';			
		$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"doi"' . '.';
		$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . strtolower($obj->doi) . '"' . '.';
	}
	
	
	if (isset($obj->authors))
	{
	
		$n = count($obj->authors);
		for ($i = 0; $i < $n; $i++)
		{
			$index = $i + 1;
		
			// Author
			$author_id = '<urn:lsid:zoobank.org:author:' . $obj->authors[$i][0]->gnubuuid . '>';
			
			//$triples[] = $author_id . ' <http://schema.org/name> ' . '"' . addcslashes($reference->author[$i]->name, '"') . '" .';					
			
			if (isset($obj->authors[$i][0]->givenname) && ($obj->authors[$i][0]->givenname != ''))
			{
				$triples[] = $author_id . ' <http://schema.org/givenName> ' . '"' . addcslashes($obj->authors[$i][0]->givenname, '"') . '" .';					
			}
			if (isset($obj->authors[$i][0]->familyname) && ($obj->authors[$i][0]->familyname != ''))
			{
				$triples[] = $author_id . ' <http://schema.org/familyName> ' . '"' . addcslashes($obj->authors[$i][0]->familyname, '"') . '" .';					
			}
			
			// assume is a person, need to handle cases where this is not true
			$triples[] = $author_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . ' <http://schema.org/Person>' . ' .';			
		
			$use_role = true;
							
			if ($use_role)
			{
				// Role to hold author position
				$role_id = '<' . $subject_id . '#role/' . $index . '>';
				
				$triples[] = $role_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . ' <http://schema.org/Role>' . ' .';			
				$triples[] = $role_id . ' <http://schema.org/roleName> "' . $index . '" .';			
			
				$triples[] = $s . ' <http://schema.org/creator> ' .  $role_id . ' .';
				$triples[] = $role_id . ' <http://schema.org/creator> ' .  $author_id . ' .';
			}
			else
			{
				// Author is creator
				$triples[] = $s . ' <http://schema.org/creator> ' .  $author_id . ' .';						
			}
			
		}
	}		
	
	// names (usages)
	// lots of care needed here as ZooBank does some strange things with names!
	if (isset($obj->NomenclaturalActs))
	{
		$pp = new Parser();
	
		foreach ($obj->NomenclaturalActs as $act)
		{
			// original usage?
			
			if ($act->tnuuuid == $act->protonymuuid)
			{
			
				$r = $pp->parse($act->cleanprotonym);
	
				print_r($r);
			
			
			
				// original combination
			
				$act_id = $act->lsid;
				
				$triples[] = '<' . $act->lsid . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName> . ';
				
				$triples[] = '<' . $act_id . '> <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ' . $s . ' . ';
				
				// handle name strings, which ZooBank does horrible things to
				$triples[] = '<' . $act_id . '> <http://schema.org/name> "' . addcslashes($act->cleanprotonym, '"') . '" . ';
				
				$nameComplete = '';
				$rankString = $act->rankgroup;
				
				switch ($act->rankgroup)
				{
					case 'Genus':
						$parts = explode(' ', $act->namestring);
						$nameComplete = $parts[0];
						
						$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#uninomial> ' . '"' . addcslashes($parts[0], '"') . '" . ';
						break;
						
					case 'Species':
						$parts = explode(' ', $act->parentname);
						$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#genusPart> ' . '"' . addcslashes($parts[0], '"') . '" . ';
						$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#specificEpithet> ' . '"' . addcslashes($act->namestring, '"') . '" . ';

						$nameComplete =  $parts[0] . ' ' . $act->namestring;
						break;
						
					default:
						$nameComplete = $act->namestring;				
						break;
				}
				
				if ($nameComplete != '')
				{
					$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ' . '"' . addcslashes($nameComplete, '"') . '" . ';				
				}
				
				$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#rankString> ' . '"' . addcslashes(strtolower($rankString), '"') . '" . ';
				
				if (isset($act->NomenclaturalCode))
				{					
					$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#nomenclaturalCode> ' . ' <http://rs.tdwg.org/ontology/voc/TaxonName#' . $act->NomenclaturalCode . '> . ';
				}				
			
			}
		}
	}
	
	exit();

	//print_r($triples);

	$nt = join("\n", $triples);
	echo $nt  . "\n";	
	
	$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));
	
	//print_r($doc);

	// Context 


	
	$context = (object)array(
		'@vocab' => 'http://schema.org/',
		'tcommon' => 'http://rs.tdwg.org/ontology/voc/Common#',
		'tc' => 'http://rs.tdwg.org/ontology/voc/TaxonConcept#',
		'tn' => 'http://rs.tdwg.org/ontology/voc/TaxonName#',	
		'taxrefprop' => 'http://taxref.mnhn.fr/lod/property/',			
		'dwc' => 'http://rs.tdwg.org/dwc/terms/',
	);
	
	/*
	$frame = (object)array(
		'@context' => $context,
		'@type' => array('http://schema.org/' . $type,
	);

	$doc = jsonld_frame($doc, $frame);	
	
	*/
	
	$doc = jsonld_compact($doc, $context);
	
	echo json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	echo "\n";
	

/*
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
				$json = file_get_contents($basedir . '/' . $directory1 . '/' . $filename);
			
				//echo $json;
			
				$count++;
		
			}
		}
	}
}

echo $count . "\n";
*/

?>
