<?php

require_once('simpletest/autorun.php');
require_once('../lib/MongoEntity.class.php');
require_once('../lib/MongoFactory.class.php');
require_once('MongoTestCase.class.php');

class AllTests extends TestSuite{

  private $mongod_path;
  private $mongo_data_path;
  private $log_file;

  function AllTests(){

    $this->TestSuite('All Tests');

    $this->start_mongo_basic();

    $this->addFile('test_mongo_entity_basic.php');
    $this->addFile('test_mongo_entity_increment.php');
    $this->addFile('test_mongo_entity_arrays.php');
    $this->addFile('test_mongo_entity_hash.php');
    $this->addFile('test_mongo_factory.php');

  }

  function __destruct(){

    print "Stopping server basic\n";
    $mongo = new Mongo();
    $db = $mongo->selectDB("admin");
    
    try { 
      $db->command(array("fsync" => 1));
      $db->command(array("shutdown" => 1));
    }
    catch (Exception $e) { }

  }

  function start_mongo_basic(){

    print "Starting server basic\n";
    $output = shell_exec('/home/pubuntu/mongo/code/MongoModel/test/start_mongo_basic.sh');
    print "Waiting for server to boot\n";
    do{
      $start_check = false;
      try {
        $mongo = new Mongo();
        $start_check = true;
      }
      catch (Exception $e){
        $start_check = false;
      }
    } while (!$start_check);

    if(preg_match('/forked process: (\d*)/', $output, $matches) !== false){
      $pid = $matches[1];
      return $pid;
    }

    return 0;

  }

}


