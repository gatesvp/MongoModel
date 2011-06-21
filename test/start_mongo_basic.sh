#!/bin/bash
ulimit -v unlimited;

#rm -rf ../data/basic
mkdir -p ../data/basic

mongod --dbpath ../data/basic --logpath ../data/basic/mongo.log --fork --smallfiles;

