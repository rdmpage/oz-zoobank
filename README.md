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





