#!/usr/bin/expect -f
#ulimit -v unlimited;

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongod --dbpath /home/pubuntu/mongo/code/MongoModel/data/basic --logpath /home/pubuntu/mongo/code/MongoModel/data/basic/mongo.log --fork --smallfiles;

expect "blog";

send "\r";

