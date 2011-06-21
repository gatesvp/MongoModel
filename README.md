MongoModel
----------

MongoModel is a simple set of object wrappers classes for storing PHP objects in MongoDB.

MongoModel is comprised of two major classes (in the lib directory).
* MongoEntity: the base class with all of the CRUD operations from which other classes will inherit.
* MongoFactory: a simple factory class for loading an array of MongoEntity objects. Can be inherited to provide a specific factory for you specific needs.

Basic Usage
===========

    class User extends MongoEntity {

      protected static $_mongo_database = "users";        # Effectively the database name
      protected static $_mongo_collection = "user";      # Effectively the table name
 
    }

This allows you to store objects in the 'users' database and the 'user' collection.

    $user = new User();

    $user_data = array("first_name" => "Gates", "last_name" => "VP");
    $user = new User($user_data);
    if($user->save()){
      print("saved the user");
    }

Shorten field names
===================
....

More details to come.

