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
    $this->start_mongo_auth();

    $this->addFile('test_mongo_entity_basic.php');
    $this->addFile('test_mongo_entity_increment.php');
    $this->addFile('test_mongo_entity_arrays.php');
    $this->addFile('test_mongo_entity_hash.php');
    $this->addFile('test_mongo_entity_replica.php');
#    $this->addFile('test_mongo_entity_dynamic_collection.php');
    $this->addFile('test_mongo_entity_auth.php');
    $this->addFile('test_mongo_factory.php');

  }

  function __destruct(){

    $this->stop_mongo_basic();
    $this->stop_mongo_replica();
    $this->stop_mongo_auth();

  }

  function start_mongo_basic(){

    print "Starting server basic\n";
    $output = shell_exec('./start_mongo_basic.sh');
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
      $this->stop_mongo_node(new Mongo());
    }
    catch (Exception $e) { 
      // note exception means that shutdown succeeded
    }
  }

  function start_mongo_replica(){

    print "Starting server replica\n";
    $output = shell_exec('./start_mongo_replica.sh');
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
    try { 
      print "Stopping server replica 1\n";
      $this->stop_mongo_node(new Mongo("mongodb://localhost:6900", array('replicaset' => true)));
    }
    catch (Exception $e) {
      // note exception means that shutdown succeeded
    }
    try {
      print "Stopping server replica 2\n";
      $this->stop_mongo_node(new Mongo("mongodb://localhost:6901", array('replicaset' => true)));
    }
    catch (Exception $e) {
      // note exception means that shutdown succeeded
    }
    try {
      print "Stopping server replica 3\n";
      $this->stop_mongo_node(new Mongo("mongodb://localhost:6902", array('replicaset' => true)));
    }
    catch (Exception $e) {
      // note exception means that shutdown succeeded
    }
  }

  function start_mongo_auth(){

    print "Starting server auth\n";
    $output = shell_exec('./start_mongo_auth.sh');
    print "Waiting for server to boot\n";
    do{
      $start_check = false;
      try {
        $mongo = new Mongo('mongodb://theadmin:anadminpassword@localhost:6904/admin');
        #$mongo = new Mongo("localhost:6904");
        $start_check = true;
      }
      catch (Exception $e){
        $start_check = false;
      }
      print("...\n");
      sleep(1);
    } while (!$start_check);

    if(preg_match('/forked process: (\d*)/', $output, $matches) !== false){
      $pid = $matches[1];
      return $pid;
    }

    return 0;

  }

  private function stop_mongo_auth(){
    print "Stopping server auth\n";
  
    try { 
      $this->stop_mongo_node(new Mongo('localhost:6904'));
    }
    catch (Exception $e) { 
      // note exception means that shutdown succeeded
    }
  }

  private function stop_mongo_node($mongo){
    $db = $mongo->selectDB("admin");
    $db->command(array("fsync" => 1));
    try { $db->command(array("shutdown" => 1)); }
    catch (Exception $e) { if($e->getMessage() != "no db response") { throw $e; } }
  }

}


