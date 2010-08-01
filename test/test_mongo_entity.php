<?php

require_once('../lib/MongoEntity.class.php');

class TestMongoEntity extends UnitTestCase{

  function setUp(){

    $mongo = new Mongo();   
    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  function teardown(){

    $mongo = new Mongo();
    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  function testCreateBasic(){
    $data = new MongoEntity();

    $data->a = 1;
    $data->b = 2;

    return $this->assertTrue($data->save());
  }

  function testCreateFromConstructor(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    return $this->assertTrue($data->save());
  }

  function testLoadBasic(){
    $data = new MongoEntity();
    $data->a = 1;
    $data->b = 2;

    if($data->save()){
      $data2 = new MongoEntity();
      $data2->load_single();
      return ($this->assertTrue($data2->a == 1) && $this->assertTrue($data2->b == 2));
    }
    else { return false; }
  }

}
?>
