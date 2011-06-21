#!/bin/bash
ulimit -v unlimited;

#rm -rf ../data/auth
mkdir -p ../data/auth

mongod --dbpath ../data/auth --logpath ../data/auth/mongo.log --port 6904 --auth --fork;

sleep 3

mongo localhost:6904/admin auth_config.js --quiet 

