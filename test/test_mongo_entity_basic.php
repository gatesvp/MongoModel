<?php

require_once('../lib/MongoEntity.class.php');

class TestMongoEntityBasic extends UnitTestCase{

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

    return ($this->assertTrue($data->save()) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateFromConstructor(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    return ($this->assertTrue($data->save()) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateSafeUpsert(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    return ($this->assertTrue($data->save(true, true)) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateUnsafeUpsert(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    return ($this->assertTrue($data->save(false, true)) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateSafe(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    return ($this->assertTrue($data->save(false, false)) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateUnsafe(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    return ($this->assertTrue($data->save(true, false)) &&
      $this->assertNotNull($data->id) );
  }

  function testLoadBasic(){
    $data = new MongoEntity(array('a' => 1, 'b' => 2));
    $data->save();

    $data2 = new MongoEntity();
    $data2->load_single();

    return ($this->assertTrue($data2->a == $data->a) && $this->assertTrue($data2->b == $data->b));
  }

  function testLoadSpecific(){
    $data = new MongoEntity(array('a' => 1, 'b' => 2));
    $data->save();

    $data2 = new MongoEntity(array('a' => 10, 'b' => 8));
    $data2->save();
    $id = $data2->id;

    $data3 = new MongoEntity();
    $data3->load_single($id);

    return ($this->assertTrue($data2->a == $data3->a) && $this->assertTrue($data2->b == $data3->b));
  }

  function testLoadSpecificFields(){
    $data = new MongoEntity(array('a' => 1, 'b' => 2, 'c' => 3));
    $data->save();
    $id = $data->id;

    $load = new MongoEntity();
    $load->load_single($id, array('a', 'b'));

    return ( $this->assertEqual($load->a, $data->a) &&
             $this->assertEqual($load->b, $data->b) &&
             $this->assertNull($load->c) &&
             $this->assertNotNull($data->c) );
  }

  function testUpdateOnLoaded(){

    $data = new MongoEntity(array('a' => 1, 'b' => 2));
    $data->save();
    $id = $data->id;

    $data2 = new MongoEntity();
    $data2->load_single($id);
    $data2->c = 10;
    $data2->save();

    $id2 = $data2->id;

    return ( $this->assertEqual($id, $id2) &&
             $this->assertNull($data->c) &&
             $this->assertNotNull($data2->c) );

  }

  function testUpdateOnUnloaded(){

    $data = new MongoEntity(array('a' => 1, 'b' => 2));
    $data->save();
    $id = $data->id;

    $data2 = new MongoEntity();
    $data2->id = $id;
    $data2->c = 3;
    $this->assertTrue($data2->save());

    $loaded = new MongoEntity();
    $loaded->load_single($id);

    return ( $this->assertEqual($id, $loaded->id) &&
             $this->assertNull($data->c) &&
             $this->assertNotNull($loaded->c) );

  }

  function testDeleteOnLoadedUnsafe(){

    $data = new MongoEntity(array('a' => 1, 'b' => 2));
    $data->save();
    $id = $data->id;

    $loaded = new MongoEntity();
    $loaded->load_single($id);

    $this->assertTrue($loaded->delete(false));  // unsafe mode

    $reloaded = new MongoEntity();
    $reloaded->load_single($id);

    return $this->assertNull($reloaded->a);

  }

  function testDeleteOnLoadedSafe(){

    $data = new MongoEntity(array('a' => 1, 'b' => 2));
    $data->save();
    $id = $data->id;

    $loaded = new MongoEntity();
    $loaded->load_single($id);

    $this->assertTrue($loaded->delete(true));  // safe mode

    $reloaded = new MongoEntity();
    $reloaded->load_single($id);

    return $this->assertNull($reloaded->a);

  }

  function testDeleteOnUnloadedUnsafe(){

    $data = new MongoEntity(array('a' => 1, 'b' => 2));
    $data->save();
    $id = $data->id;

    $loaded = new MongoEntity();
    $loaded->id = $id;
    $this->assertTrue($loaded->delete(false));  // unsafe mode

    $reloaded = new MongoEntity();
    return $this->assertFalse( $reloaded->load_single($id) );

  }

  function testDeleteOnUnloadedSafe(){

    $data = new MongoEntity(array('a' => 1, 'b' => 2));
    $data->save();
    $id = $data->id;

    $loaded = new MongoEntity();
    $loaded->id = $id;
    $this->assertTrue($loaded->delete(true));  // safe mode

    $reloaded = new MongoEntity();
    return $this->assertFalse( $reloaded->load_single($id) );

  }

}
?>
