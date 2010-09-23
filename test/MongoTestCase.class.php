<?php

class MongoTestCase extends UnitTestCase{

  function setUp(){

    $mongo = new Mongo();   
    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  function teardown(){

    $mongo = new Mongo();
    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

}
?>
