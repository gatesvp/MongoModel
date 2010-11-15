#!/usr/bin/expect -f
#ulimit -v unlimited;

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongod --dbpath /home/pubuntu/mongo/code/MongoModel/data --logpath /home/pubuntu/mongo/code/MongoModel/data/mongo.log --fork;

expect "blog";

send "\r";

