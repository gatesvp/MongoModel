<?php

require_once('../lib/MongoEntity.class.php');

class TestMongoEntityIncrement extends UnitTestCase{

  function setUp(){

    $mongo = new Mongo();   
    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  function teardown(){

    $mongo = new Mongo();
    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  function testIncrementOnLoaded(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    $data->save();

    $data->increment('a', 5);
    $data->save();

    if($this->assertEqual($data->a, 6)){

      $loaded = new MongoEntity();
      $loaded->load_single();

      return $this->assertEqual($loaded->a, 6);
    }
    else{
      return false;
    }

  }

  function testIncrementOnUnloaded(){

    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    $data->save();

    $id = $data->id;

    $fire_and_forget = new MongoEntity();
    $fire_and_forget->id = $id;
    $fire_and_forget->increment('a', 5);
    $fire_and_forget->save();

    $loaded = new MongoEntity();
    $loaded->load_single();
    
    return $this->assertEqual($loaded->a, 6);

  }

}
?>
