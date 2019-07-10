<?php

// Function to convert ZooBank to RDF

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/taxon_name_parser.php');

function zoobank_to_jsonld($obj, $format = 'nt')
{
	if (isset($obj->lsid) &&  $obj->lsid != '')
	{
		$guid = $obj->lsid;	
	}
	else
	{		
		if (isset($obj->referenceuuid))
		{
			$guid = 'http://zoobank.org/References/' . $obj->referenceuuid;
		}
		else
		{
			return;
		}
	}

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
	
	
	$subject_id = $guid; 

	$s = '<' . $subject_id . '>';
	
	$type = 'CreativeWork';
	
	if (isset($obj->referencetype))
	{
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
		$container_id = 'urn:lsid:zoobank.org:pub:' . strtoupper($obj->parentreferenceid);
		$triples[] = $s . ' <http://schema.org/isPartOf> <' . $container_id  . '> . ';		
		
		if (isset($obj->parentreference))
		{
			$triples[] = '<' . $container_id . '> <http://schema.org/name> ' . '"' . addcslashes($obj->parentreference, '"') . '" .';						
		}
	}
	
	// Identifiers

	if (isset($obj->referenceuuid))
	{
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
	}
			
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
			$author_id = '<urn:lsid:zoobank.org:author:' . strtoupper($obj->authors[$i][0]->gnubuuid) . '>';
			
			//$triples[] = $author_id . ' <http://schema.org/name> ' . '"' . addcslashes($reference->author[$i]->name, '"') . '" .';					
			
			$parts = array();
						
			if (isset($obj->authors[$i][0]->givenname) && ($obj->authors[$i][0]->givenname != ''))
			{
				$triples[] = $author_id . ' <http://schema.org/givenName> ' . '"' . addcslashes($obj->authors[$i][0]->givenname, '"') . '" .';					
				
				$parts[] = $obj->authors[$i][0]->givenname;
			}
			if (isset($obj->authors[$i][0]->familyname) && ($obj->authors[$i][0]->familyname != ''))
			{
				$triples[] = $author_id . ' <http://schema.org/familyName> ' . '"' . addcslashes($obj->authors[$i][0]->familyname, '"') . '" .';					
				
				$parts[] = $obj->authors[$i][0]->familyname;
			}
			
			// combine to create complete name
			if (count($parts) > 0)
			{
				$triples[] = $author_id . ' <http://schema.org/name> ' . '"' . addcslashes(join(' ', $parts), '"') . '" .';									
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
			//print_r($act);
			
			// To keep sane we just handle new names, other things get very messy very quickly 
			if ($act->tnuuuid == $act->protonymuuid)
			{
			
				//$act_id = $act->lsid;
			
				$act_id = 'urn:lsid:zoobank.org:act:' . strtoupper($act->tnuuuid);
			
				// it's a taxonomic name
				$triples[] = '<' . $act_id . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName> . ';
				if (isset($act->NomenclaturalCode))
				{					
					$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#nomenclaturalCode> ' . ' <http://rs.tdwg.org/ontology/voc/TaxonName#' . $act->NomenclaturalCode . '> . ';
				}				
				
				// if original combination we link to publication
				if ($act->tnuuuid == $act->protonymuuid)
				{
					// original combination so we have publication
					$triples[] = '<' . $act_id . '> <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ' . $s . ' . ';
				}
				else
				{
			
				}
				
			
				// rank is a mess, ZooBank has "rankgroup" and "taxonnamerankid",
				// and the same rankgroup can have multiple taxonnamerankids. I think
				// this is a way to say that "species" and "subspecies" are part of the same rank group.
			
				$rankstring = '';
			
				switch ($act->rankgroup)
				{
					case 'Genus':
						switch ($act->taxonnamerankid)
						{
							case 63:
								$rankstring	= 'subgenus';
								break;
										
							case 60:
							default:
								$rankstring	= 'genus';
								break;
						}
						break;
					
					case 'Species':
						switch ($act->taxonnamerankid)
						{					
							case 73:
								$rankstring	= 'subspecies';
								break;
										
							case 70:
							default:
								$rankstring	= 'species';
								break;
						}
						break;					
					
					
					default:
						$rankstring =  strtolower($act->rankgroup);
						break;
				}
			
				$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#rankString> ' . '"' . addcslashes(strtolower($rankstring), '"') . '" . ';

				// names are store din all sorts of horribel ways so try and make sense of 
				// "cleanprotonym" which is complete name for a new name, but may be
				// something entirely different for new combinations
							
				$r = $pp->parse($act->cleanprotonym);
	
				//print_r($r);
			
				if ($r->scientificName->parsed)
				{
					$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ' . '"' . addcslashes($r->scientificName->canonical, '"') . '" . ';				
					
					$authorship = '';
			
					switch ($rankstring)
					{
						case 'family':
							break;
					
						case 'genus':
							if (isset($r->scientificName->details[0]->genus))
							{
								$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#uninomial> ' . '"' . addcslashes($r->scientificName->details[0]->genus->epitheton, '"') . '" . ';					
								if (isset($r->scientificName->details[0]->genus->authorship))
								{
									$authorship = $r->scientificName->details[0]->genus->authorship;		
								}				
							}											
							break;
					
						case 'subgenus':
							if (isset($r->scientificName->details[0]->genus))
							{
								$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#genusPart> ' . '"' . addcslashes($r->scientificName->details[0]->genus->epitheton, '"') . '" . ';					
							}				
							if (isset($r->scientificName->details[0]->infragenus))
							{
								$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#infragenericEpithet> ' . '"' . addcslashes($r->scientificName->details[0]->infragenus->epitheton, '"') . '" . ';					
								if (isset($r->scientificName->details[0]->infragenus->authorship))
								{
									$authorship = $r->scientificName->details[0]->infragenus->authorship;		
								}												
							}				
							break;
					
						case 'species':
						case 'subspecies':					
							if (isset($r->scientificName->details[0]->genus))
							{
								$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#genusPart> ' . '"' . addcslashes($r->scientificName->details[0]->genus->epitheton, '"') . '" . ';					
							}
					
							if (isset($r->scientificName->details[0]->infragenus))
							{
								$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#infragenericEpithet> ' . '"' . addcslashes($r->scientificName->details[0]->infragenus->epitheton, '"') . '" . ';					
							}									
					
							if (isset($r->scientificName->details[0]->species))
							{
								$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#specificEpithet> ' . '"' . addcslashes($r->scientificName->details[0]->species->epitheton, '"') . '" . ';					
								if (isset($r->scientificName->details[0]->species->authorship))
								{
									$authorship = $r->scientificName->details[0]->species->authorship;		
								}												
							}

							if (isset($r->scientificName->details[0]->infraspecies))
							{
								$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#infraspecificEpithet> ' . '"' . addcslashes($r->scientificName->details[0]->infraspecies->epitheton, '"') . '" . ';					
								if (isset($r->scientificName->details[0]->infraspecies->authorship))
								{
									$authorship = $r->scientificName->details[0]->infraspecies->authorship;		
								}												
							}					
							break;
			
						default:
							break;
			
					}
					
					if (isset($act->usageauthors))
					{
						$triples[] = '<' . $act_id . '>  <http://rs.tdwg.org/ontology/voc/TaxonName#authorship> ' . '"' . addcslashes($act->usageauthors, '"') . '" . ';										
					}
					
				}
			}			

		}
	}

	
	$nt = join("\n", $triples);
	
	if ($format == 'nt')
	{
		echo $nt  . "\n";	
	}
	if ($format == 'jsonld')
	{
	
		$doc = jsonld_from_rdf($nt, array('format' => 'application/nquads'));
	
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
	}
}

?>
