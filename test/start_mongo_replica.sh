#!/usr/bin/expect -f
#ulimit -v unlimited;

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongod --dbpath /home/pubuntu/mongo/code/MongoModel/data/set1 --logpath /home/pubuntu/mongo/code/MongoModel/data/set1.log --replSet blah --port 6900 --oplogSize 3 --smallfiles --fork;
expect "blog";
send "\r";

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongod --dbpath /home/pubuntu/mongo/code/MongoModel/data/set2 --logpath /home/pubuntu/mongo/code/MongoModel/data/set2.log --replSet blah --port 6901 --oplogSize 3 --smallfiles --fork;
expect "blog";
send "\r";

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongod --dbpath /home/pubuntu/mongo/code/MongoModel/data/set3 --logpath /home/pubuntu/mongo/code/MongoModel/data/set3.log --replSet blah --port 6902 --oplogSize 3 --smallfiles --fork;
expect "blog";
send "\r";

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongo localhost:6900 --eval "rs.initiate({ '_id' : 'foo', 'members' : [ {'_id' : 0, 'host' : 'localhost:6900'}, {'_id' : 1, 'host' : 'localhost:6901'}, {'_id' : 2, 'host' : 'localhost:6902'} ] })"
expect "ok : 1";
send "\r";

