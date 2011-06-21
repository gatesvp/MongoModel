<?php

class MongoDynamicTest extends MongoEntity {

   public static function loadCollection($collectionName = null,$databaseName = null,$serverName = null,$portNumber = null){
$coll = parent::loadCollection("bar", "baz");

var_dump($coll);

return $coll;

   }

}

class TestMongoDynamic extends MongoTestCase{

  function testCreateBasic(){
    $data = new MongoDynamicTest();

    $data->a = 1;
    $data->b = 2;

    return ($this->assertTrue($data->save()) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

  function testCreateFromConstructor(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoDynamicTest($data_set);

    return ($this->assertTrue($data->save()) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertNotNull($data->id) );
  }

  function testLoadBasic(){
    $data = new MongoDynamicTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save(true));

    $data2 = new MongoDynamicTest();
    $data2->load_single();

    return ($this->assertEqual($data2->a, $data->a) &&
      $this->assertEqual($data->a, 1) &&
      $this->assertEqual($data->b, 2) &&
      $this->assertEqual($data2->b, $data->b));
  }

  function testLoadSpecific(){
    $data = new MongoDynamicTest(array('a' => 1, 'b' => 2));
    $this->assertTrue($data->save());

    $data2 = new MongoDynamicTest(array('a' => 10, 'b' => 8));
    $data2->save();
    $id = $data2->id;

    $data3 = new MongoDynamicTest();
    $data3->load_single($id);

    return ($this->assertEqual($data2->a, 10) &&
      $this->assertEqual($data2->b, 8) &&
      $this->assertTrue($data2->a == $data3->a) && 
      $this->assertTrue($data2->b == $data3->b));
  }

  function testLoadSpecificFields(){
    $data = new MongoDynamicTest(array('a' => 1, 'b' => 2, 'c' => 3));
    $this->assertTrue($data->save(true));
    $id = $data->id;

    $load = new MongoDynamicTest();
    $load->load_single($id, array('a', 'b'));

    return ( $this->assertEqual($load->a, $data->a) &&
             $this->assertEqual($load->b, $data->b) &&
             $this->assertNull($load->c) &&
             $this->assertNotNull($data->c) );
  }

}
?>
