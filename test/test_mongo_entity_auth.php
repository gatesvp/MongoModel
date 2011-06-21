<?php

class AuthEntity extends MongoEntity{

  protected static $_mongo_port = 6904;
  protected static $_mongo_database = 'projectx';
  protected static $_mongo_user_name = 'joe';
  protected static $_mongo_password = 'passwordForJoe';

}

class AuthEntityBad extends MongoEntity{

  protected static $_mongo_port = 6904;
  protected static $_mongo_database = 'projectx';
  protected static $_mongo_user_name = 'blah';
  protected static $_mongo_password = 'blah';

}

class TestAuthEntityAuth extends UnitTestCase{

  function setUp(){

#    $mongo = new Mongo();   
#    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  function teardown(){

#    $mongo = new Mongo();
#    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  function testCreateAuth(){
    $data = new AuthEntity();

    $data->a = 1;
    $data->b = 2;

    return ($this->assertTrue($data->save()) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

}
?>
