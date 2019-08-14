# oz-zoobank

Harvesting and interpreting ZooBank.

Having harvested ZooBank a few times before, I’m now creating an on-disk archive of the publications, one JSON file per ZooBank reference. These files also contain all the nomenclatural acts for that reference, and the authors and their ZooBank identifiers.

I’m processing the files, for example by reading them in a checking for DOIs (and whether the DOIs seem obviously wrong). Once the data is processed, we could, for example, export as JSON-LD for storage, or export as triples and add to a triple store. We could, for example, add them to a triple store of all eukaryote names…

## IPT

ZooBank IPT with daily updates http://zoobank.org:8080/ipt/resource?r=zoobank

## Upload

```
curl http://localhost:32773/blazegraph/namespace/alec/sparql?context-uri=http://zoobank.org -H 'Content-Type: text/rdf+n3' --data-binary '@z.nt'  --progress-bar | tee /dev/null

```

## Queries

```
select * where 
{
  ?s <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> "Paraulopus okamurai" .
  
  OPTIONAL {
    ?s <http://rs.tdwg.org/ontology/voc/Common#publishedIn> ?publishedIn .
   }
  OPTIONAL {
    ?s <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ?publishedInCitation .
    ?publishedInCitation <http://schema.org/name> ?title .
   }  
  OPTIONAL {
     ?s <http://rs.tdwg.org/ontology/voc/TaxonName#rankString> ?rank .
 }
}
```

```
select ?nameComplete ?doi where 
{
  ?s <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ?nameComplete .
  
	OPTIONAL {
    ?s <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ?publishedInCitation .
    ?publishedInCitation <http://schema.org/identifier> ?identifier .
      ?identifier <http://schema.org/propertyID> "doi" .
      ?identifier <http://schema.org/value> ?doi .
      
   }  
 
}
```

```
select  ?container_name ?datePublished ?doi
where 
{
  ?work <http://schema.org/isPartOf> ?container .
  ?container <http://schema.org/name> ?container_name .
  ?work <http://schema.org/datePublished> ?datePublished .
  
 OPTIONAL {
    ?work <http://schema.org/identifier> ?identifier .
      ?identifier <http://schema.org/propertyID> "doi" .
      ?identifier <http://schema.org/value> ?doi .      
   }  
 
  #FILTER (?datePublished = "2011")
  #FILTER (?container_name = "Acarologia")
  FILTER regex(?doi, "figure")
}
ORDER BY ?container_name
#ORDER BY ?datePublished
```

### Find author

```
select *
where 
{
  ?person <http://schema.org/familyName> "Chapple" .
  ?person <http://schema.org/name> ?name .

  
  ?role <http://schema.org/creator> ?person . 
  ?work <http://schema.org/creator> ?role . 
  
OPTIONAL {
    ?work <http://schema.org/identifier> ?identifier .
      ?identifier <http://schema.org/propertyID> "doi" .
      ?identifier <http://schema.org/value> ?doi .      
   }  
 
}
```

### List DOIs for a journal

```
select  ?container_name ?datePublished ?doi
where 
{
  ?work <http://schema.org/isPartOf> ?container .
  ?container <http://schema.org/name> ?container_name .
  ?work <http://schema.org/datePublished> ?datePublished .
  
 OPTIONAL {
    ?work <http://schema.org/identifier> ?identifier .
      ?identifier <http://schema.org/propertyID> "doi" .
      ?identifier <http://schema.org/value> ?doi .      
   }  
 
  FILTER (?container_name = "Herpetologica")
}
ORDER BY ?datePublished
```


## Author and roles
```
SELECT *
WHERE 
{
  
  VALUES ?work { <urn:lsid:zoobank.org:pub:D7588A4E-D06E-4524-BB49-AC16C3FEC849> } .
  ?work <http://schema.org/name> ?title .
  ?work <http://schema.org/creator> ?role . 
  ?role <http://schema.org/roleName> ?roleName . 
  ?role <http://schema.org/creator> ?person . 
  ?person <http://schema.org/familyName> ?familyName .
  ?person <http://schema.org/name> ?name .

  OPTIONAL {
    ?work <http://schema.org/identifier> ?identifier .
	?identifier <http://schema.org/propertyID> "doi" .
	?identifier <http://schema.org/value> ?doi .      
   }
  
 OPTIONAL {
    ?work <http://schema.org/identifier> ?identifier .
	?identifier <http://schema.org/propertyID> "zoobank" .
	?identifier <http://schema.org/value> ?zoobank .      
   } 

 
}



```


## Matching ZooBank ids to Wikidata entries for authors

### Extract ZooBank authors to SQL

Run `process-authors.php` to parse data dump and add authors to a SQL output.

### Get candidates for matching from Wikidata

We can select various people that we think should have Zoobank ids, such as authors in Wikispecies. Can make the query manageable by restricting to different categories of people (or just do everyone):

```
SELECT ?item ?itemLabel ?article ?zoobank
WHERE
{
	# Wikispecies articles
	?article 	schema:about ?item ;
	schema:isPartOf <https://species.wikimedia.org/> .
	?item wdt:P31 wd:Q5 .

	# only include people flagged as arachnologists
	#?item wdt:P106 wd:Q17344952 .

	# only include people flagged as dipterologist (4)
	#?item wdt:P106 wd:Q63146541 .
	# only include people flagged as carcinologist (1162)
	#?item wdt:P106 wd:Q16868721
	 
	# only include people flagged as entomologist (10886)
	# ?item wdt:P106 wd:Q3055126
	 
	?item wdt:P106 wd:Q27497422

	# Do they have a ZooBank author ID?
	OPTIONAL { ?item wdt:P2006 ?zoobank . }

	SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en" }
}

```

Get TSV dump of this query, upload to a table in MySQL then match to ZooBank:

```
CREATE TABLE `wikidata` (
  `item` varchar(255) NOT NULL DEFAULT '',
  `itemLabel` varchar(255) DEFAULT NULL,
  `article` varchar(255) DEFAULT NULL,
  `zoobank` varchar(64) DEFAULT NULL,
  KEY `itemLabel` (`itemLabel`),
  KEY `article` (`article`),
  KEY `zb` (`zoobank`),
  KEY `item` (`item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

Match to ZooBank assuming author names will be exactly the same:

```
SELECT CONCAT(REPLACE(item, 'http://www.wikidata.org/entity/', ''), '|P2006|"', id, '"') FROM zoobank_authors INNER JOIN wikidata ON zoobank_authors.name = wikidata.itemLabel WHERE wikidata.zoobank = '';

```

