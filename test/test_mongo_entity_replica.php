<?php

class MongoReplicaTest extends MongoEntity {

  protected static $_mongo_server = array('localhost','localhost','localhost');  # Server IP or name
  protected static $_mongo_port = array(6900,6901,6902); # Server port number
  protected static $_mongo_database = "test";        # Effectively the database name
  protected static $_mongo_collection = "test";      # Effectively the table name
  protected static $_mongo_connection_timeout = 5000;     # 5 second default timeout on connect
  protected static $_mongo_query_timeout = 1000;

}

class TestMongoReplica extends MongoTestCase{

  function testCreateBasic(){
    $data = new MongoReplicaTest();

    $data->a = 1;
    $data->b = 2;

    return ($this->assertTrue($data->save()) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateFromConstructor(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoReplicaTest($data_set);

    return ($this->assertTrue($data->save()) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateSafeUpsert(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoReplicaTest($data_set);

    return ($this->assertTrue($data->save(true, true)) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateUnsafeUpsert(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoReplicaTest($data_set);

    return ($this->assertTrue($data->save(false, true)) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateSafe(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoReplicaTest($data_set);

    return ($this->assertTrue($data->save(false, false)) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateUnsafe(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoReplicaTest($data_set);

    return ($this->assertTrue($data->save(true, false)) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

  function testLoadBasic(){
    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save(true));

    $data2 = new MongoReplicaTest();
    $data2->load_single();

    return ($this->assertEqual($data2->a, $data->a) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertEqual($data2->b, $data->b));
  }

  function testLoadSpecific(){
    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save(true));

    $data2 = new MongoReplicaTest(array('a' => 10, 'b' => 8));
    $data2->save();
    $id = $data2->id;

    $data3 = new MongoReplicaTest();
    $data3->load_single($id);

    return ($this->assertEqual($data2->a, 10) &&
      $this->assertEqual($data2->b, 8) &&
      $this->assertTrue($data2->a == $data3->a) && 
      $this->assertTrue($data2->b == $data3->b));
  }

  function testLoadSpecificFields(){
    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2, 'c' => 3));
    $this->assertTrue($data->save());
    $id = $data->id;

    $load = new MongoReplicaTest();
    $load->load_single($id, array('a', 'b'));

    return ( $this->assertEqual($load->a, $data->a) &&
             $this->assertEqual($load->b, $data->b) &&
             $this->assertNull($load->c) &&
             $this->assertNotNull($data->c) );
  }

  function testUpdateOnLoaded(){

    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());
    $id = $data->id;

    $data2 = new MongoReplicaTest();
    $data2->load_single($id);
    $data2->c = 10;
    $data2->save();

    $id2 = $data2->id;

    return ( $this->assertEqual($id, $id2) &&
             $this->assertNull($data->c) &&
             $this->assertNotNull($data2->c) &&
             $this->assertEqual($data2->c, 10) );

  }

  function testUpdateOnUnloaded(){

    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());
    $id = $data->id;

    $data2 = new MongoReplicaTest();
    $data2->id = $id;
    $data2->c = 3;
    $this->assertTrue($data2->save());

    $loaded = new MongoReplicaTest();
    $loaded->load_single($id);

    return ( $this->assertEqual($id, $loaded->id) &&
             $this->assertNull($data->c) &&
             $this->assertNotNull($loaded->c) );

  }

  function testDeleteOnLoadedUnsafe(){

    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());
    $id = $data->id;

    $loaded = new MongoReplicaTest();
    $loaded->load_single($id);

    $this->assertTrue($loaded->delete(false));  // unsafe mode

    $reloaded = new MongoReplicaTest();
    $reloaded->load_single($id);

    return $this->assertNull($reloaded->a);

  }

  function testDeleteOnLoadedSafe(){

    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());
    $id = $data->id;

    $loaded = new MongoReplicaTest();
    $loaded->load_single($id);

    $this->assertTrue($loaded->delete(true));  // safe mode

    $reloaded = new MongoReplicaTest();
    $reloaded->load_single($id);

    return $this->assertNull($reloaded->a);

  }

  function testDeleteOnUnloadedUnsafe(){

    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());
    $id = $data->id;

    $loaded = new MongoReplicaTest();
    $loaded->id = $id;
    $this->assertTrue($loaded->delete(false));  // unsafe mode

    $reloaded = new MongoReplicaTest();
    return $this->assertFalse( $reloaded->load_single($id) );

  }

  function testDeleteOnUnloadedSafe(){

    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());
    $id = $data->id;

    $loaded = new MongoReplicaTest();
    $loaded->id = $id;
    $this->assertTrue($loaded->delete(true));  // safe mode

    $reloaded = new MongoReplicaTest();
    return $this->assertFalse( $reloaded->load_single($id) );

  }

  function testFieldUnsettingNew(){
    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    unset($data->a);
    $this->assertTrue($data->save());

    $loaded = new MongoReplicaTest();
    $loaded->load_single();

    return ($this->assertNull($data->a) && 
            $this->assertNull($loaded->a) && 
            $this->assertNotNull($data->b) && 
            $this->assertNotNull($loaded->b) );
  }

  function testFieldUnsettingExisting(){

    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());

    $loaded = new MongoReplicaTest();
    $loaded->load_single();
    unset($loaded->a);
    $loaded->save(true);

    $reloaded = new MongoReplicaTest();
    $reloaded->load_single();
    
    return ($this->assertNull($loaded->a) && 
            $this->assertNull($reloaded->a) && 
            $this->assertNotNull($loaded->b) && 
            $this->assertNotNull($reloaded->b) );

  }

  function testBlankSave(){

    $data = new MongoReplicaTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());

    $loaded = new MongoReplicaTest();
    $loaded->load_single();
    $loaded->save();

    $reloaded = new MongoReplicaTest();
    $reloaded->load_single();

    return ($this->assertEqual($loaded->a, $reloaded->a) &&
            $this->assertEqual($loaded->b, $reloaded->b) );

  }

}
?>
