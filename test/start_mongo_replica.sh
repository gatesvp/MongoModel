#!/bin/bash
ulimit -v unlimited

#rm -rf ../data/set1
#rm -rf ../data/set2
#rm -rf ../data/set3

mkdir -p ../data/set1
mkdir -p ../data/set2
mkdir -p ../data/set3

mongod --dbpath ../data/set1 --logpath ../data/set1/set1.log --replSet blah --port 6900 --oplogSize 3 --smallfiles --noprealloc --fork;

mongod --dbpath ../data/set2 --logpath ../data/set2/set2.log --replSet blah --port 6901 --oplogSize 3 --smallfiles --noprealloc --fork;

mongod --dbpath ../data/set3 --logpath ../data/set3/set3.log --replSet blah --port 6902 --oplogSize 3 --smallfiles --noprealloc --fork;

sleep 3

mongo localhost:6900/admin --quiet --eval "printjson(rs.initiate({ '_id' : 'blah', 'members' : [ {'_id' : 0, 'host' : 'localhost:6900'}, {'_id' : 1, 'host' : 'localhost:6901'}, {'_id' : 2, 'host' : 'localhost:6902'} ] }))"
#expect "ok : 1";
#send "\r";

