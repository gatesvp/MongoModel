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

    $coll = AuthEntity::loadCollection();
    $coll->drop();

  }

  function teardown(){

    $coll = AuthEntity::loadCollection();
    $coll->drop();

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

  function testLoadBasic(){
    $data = new AuthEntity(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save(true));

    $data2 = new AuthEntity();
    $data2->load_single();

    return ($this->assertEqual($data2->a, $data->a) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertEqual($data2->b, $data->b));

  }
  
  function testCreateAuthBad(){
    $data = new AuthEntityBad();

    $data->a = 1;
    $data->b = 2;

    return ($this->assertFalse($data->save()));
  }

  function testLoadSpecific(){
    $data = new AuthEntity(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save(true));

    $data2 = new AuthEntity(array('a' => 10, 'b' => 8));
    $data2->save(true);
    $id = $data2->id;

    $data3 = new AuthEntity();
    $data3->load_single($id);

    return ($this->assertEqual($data2->a, 10) &&
      $this->assertEqual($data2->b, 8) &&
      $this->assertTrue($data2->a == $data3->a) &&
      $this->assertTrue($data2->b == $data3->b));
  }

  function testLoadSpecificFields(){
    $data = new AuthEntity(array('a' => 1, 'b' => 2, 'c' => 3));
    $this->assertTrue($data->save());
    $id = $data->id;

    $load = new AuthEntity();
    $load->load_single($id, array('a', 'b'));

    return ( $this->assertEqual($load->a, $data->a) &&
             $this->assertEqual($load->b, $data->b) &&
             $this->assertNull($load->c) &&
             $this->assertNotNull($data->c) );
  }

}
?>
