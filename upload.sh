#!/bin/sh

echo 'z-0.nt'
curl http://localhost:32775/blazegraph/namespace/alec/sparql?context-uri=http://zoobank.org -H 'Content-Type: text/rdf+n3' --data-binary '@z-0.nt'  --progress-bar | tee /dev/null
echo ''
sleep 5
echo 'z-500000.nt'
curl http://localhost:32775/blazegraph/namespace/alec/sparql?context-uri=http://zoobank.org -H 'Content-Type: text/rdf+n3' --data-binary '@z-500000.nt'  --progress-bar | tee /dev/null
echo ''
sleep 5
echo 'z-1000000.nt'
curl http://localhost:32775/blazegraph/namespace/alec/sparql?context-uri=http://zoobank.org -H 'Content-Type: text/rdf+n3' --data-binary '@z-1000000.nt'  --progress-bar | tee /dev/null
echo ''
sleep 5
echo 'z-1500000.nt'
curl http://localhost:32775/blazegraph/namespace/alec/sparql?context-uri=http://zoobank.org -H 'Content-Type: text/rdf+n3' --data-binary '@z-1500000.nt'  --progress-bar | tee /dev/null
echo ''
sleep 5
echo 'z-2000000.nt'
curl http://localhost:32775/blazegraph/namespace/alec/sparql?context-uri=http://zoobank.org -H 'Content-Type: text/rdf+n3' --data-binary '@z-2000000.nt'  --progress-bar | tee /dev/null
echo ''
sleep 5
echo 'z-2500000.nt'
curl http://localhost:32775/blazegraph/namespace/alec/sparql?context-uri=http://zoobank.org -H 'Content-Type: text/rdf+n3' --data-binary '@z-2500000.nt'  --progress-bar | tee /dev/null
echo ''
sleep 5
echo 'z-3000000.nt'
curl http://localhost:32775/blazegraph/namespace/alec/sparql?context-uri=http://zoobank.org -H 'Content-Type: text/rdf+n3' --data-binary '@z-3000000.nt'  --progress-bar | tee /dev/null
echo ''
sleep 5
