#!/usr/bin/expect -f

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongod --dbpath /home/pubuntu/mongo/code/MongoModel/data/set1 --logpath /home/pubuntu/mongo/code/MongoModel/data/set1/set1.log --replSet blah --port 6900 --oplogSize 3 --smallfiles --noprealloc --fork;
expect "blog";
send "\r";

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongod --dbpath /home/pubuntu/mongo/code/MongoModel/data/set2 --logpath /home/pubuntu/mongo/code/MongoModel/data/set2/set2.log --replSet blah --port 6901 --oplogSize 3 --smallfiles --noprealloc --fork;
expect "blog";
send "\r";

spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongod --dbpath /home/pubuntu/mongo/code/MongoModel/data/set3 --logpath /home/pubuntu/mongo/code/MongoModel/data/set3/set3.log --replSet blah --port 6902 --oplogSize 3 --smallfiles --noprealloc --fork;
expect "blog";
send "\r";

#spawn /home/pubuntu/mongo/mongodb-linux-i686-1.6.0/bin/mongo mongodb://localhost:6900,localhost:6901,localhost:6902/?replicaset=blah --eval "rs.initiate({ '_id' : 'blah', 'members' : [ {'_id' : 0, 'host' : 'localhost:6900'}, {'_id' : 1, 'host' : 'localhost:6901'}, {'_id' : 2, 'host' : 'localhost:6902'} ] })"
#expect "ok : 1";
#send "\r";

