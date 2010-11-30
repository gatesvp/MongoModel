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
    $this->start_mongo_replica();

    $this->addFile('test_mongo_entity_basic.php');
    $this->addFile('test_mongo_entity_increment.php');
    $this->addFile('test_mongo_entity_arrays.php');
    $this->addFile('test_mongo_entity_hash.php');
    $this->addFile('test_mongo_entity_replica.php');
    $this->addFile('test_mongo_factory.php');

  }

  function __destruct(){

    $this->stop_mongo_basic();
    $this->stop_mongo_replica();

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

  private function stop_mongo_basic(){
    print "Stopping server basic\n";
  
    try { 
      $mongo = new Mongo();
      $db = $mongo->selectDB("admin");
      $db->command(array("fsync" => 1));
      $db->command(array("shutdown" => 1));
    }
    catch (Exception $e) { }
  }

  function start_mongo_replica(){

    print "Starting server replica\n";
    $output = shell_exec('/home/pubuntu/mongo/code/MongoModel/test/start_mongo_replica.sh');
    print "Waiting for server to boot\n";
    do{
      $start_check = false;
      try {
        $mongo = new Mongo("mongodb://localhost:6900,localhost:6901,localhost:6902", array('replicaset' => true));
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

  private function stop_mongo_replica(){
    print "Stopping server replica\n";
    try { 
      $mongo = new Mongo("mongodb://localhost:6900,localhost:6901,localhost:6902", array('replicaset' => true));
      $db = $mongo->selectDB("admin");
      $db->command(array("fsync" => 1));
      $db->command(array("shutdown" => 1));
    }
    catch (Exception $e) {
      print $e->getMessage();
    }
  }

}


