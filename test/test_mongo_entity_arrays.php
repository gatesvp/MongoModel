<?php

require_once('../lib/MongoEntity.class.php');

class TestMongoEntityArrays extends UnitTestCase{

  function setUp(){

    $mongo = new Mongo();   
    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  function teardown(){

    $mongo = new Mongo();
    $mongo->selectDB('test')->selectCollection('test')->drop();

  }

  private function _init_array(){

    $data_set = array('a' => 1, 'b' => 2, 'c' => array('x','y','z'));
    $data = new MongoEntity($data_set);
    $data->save();

  }

  private function _init_hash(){

    $data_set = array('a' => 1, 'b' => 2, 'c' => array(0 => 'x', 5 => 'y', 10 => 'z'));
    $data = new MongoEntity($data_set);
    $data->save();

  }

  function testBasicArray(){

    $this->_init_array();

    $loaded = new MongoEntity();
    $loaded->load_single();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($loaded->c[0] == 'x') &&
            $this->assertTrue($loaded->c[1] == 'y') &&
            $this->assertTrue($loaded->c[2] == 'z') );

  }

  function testHashArray(){

    $this->_init_hash();

    $loaded = new MongoEntity();
    $loaded->load_single();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($loaded->c[0] == 'x') &&
            $this->assertTrue($loaded->c[5] == 'y') &&
            $this->assertTrue($loaded->c[10] == 'z') );
  }

  function testBasicArrayChange(){

    $this->_init_array();

    $loaded = new MongoEntity();
    $loaded->load_single();
    $loaded->c = array('x', 'y', 'z', 'w');
    $loaded->save();

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($reloaded->c[0] == 'x') &&
            $this->assertTrue($reloaded->c[1] == 'y') &&
            $this->assertTrue($reloaded->c[2] == 'z') &&
            $this->assertTrue($reloaded->c[3] == 'w') );

  }

  function testHashArrayChanged(){

    $this->_init_hash();

    $loaded = new MongoEntity();
    $loaded->load_single();
    $loaded->c = array(0 => 'x', 5 => 'y', 10 => 'z', 15 => 'w');
    $loaded->save();

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue($reloaded->c[0] == 'x') &&
            $this->assertTrue($reloaded->c[5] == 'y') &&
            $this->assertTrue($reloaded->c[10] == 'z') &&
            $this->assertTrue($reloaded->c[15] == 'w') );
  }

  function testArrayPush(){

    $this->_init_array();

    $loaded = new MongoEntity();
    $loaded->load_single();
    $loaded->push('c', 'w');
    $loaded->save();

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue($reloaded->c[0] == 'x') &&
            $this->assertTrue($reloaded->c[1] == 'y') &&
            $this->assertTrue($reloaded->c[2] == 'z') &&
            $this->assertTrue($reloaded->c[3] == 'w') );

  }

  function testArrayMultiPush(){

    $this->_init_array();

    $loaded = new MongoEntity();
    $loaded->load_single();
    $loaded->push('c', 'a');
    $loaded->push('c', 'w');
    $loaded->save();  // push should only push one of these

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue($reloaded->c[0] == 'x') &&
            $this->assertTrue($reloaded->c[1] == 'y') &&
            $this->assertTrue($reloaded->c[2] == 'z') &&
            $this->assertTrue($reloaded->c[3] == 'w') );

  }

  function testArrayPushExistingField(){

    $this->_init_array();

    $loaded = new MongoEntity();
    $loaded->load_single();
    $loaded->push('b', 10);
    $loaded->save();  // push should only push one of these

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->b)) &&
            $this->assertTrue($reloaded->b[0] == 2) &&
            $this->assertTrue($reloaded->b[1] == 10) );

  }

  function testArrayPushBlankField(){

    $this->_init_array();

    $loaded = new MongoEntity();
    $loaded->load_single();
    $loaded->push('x', 10);
    $loaded->save();  // push should only push one of these

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->x)) &&
            $this->assertTrue($reloaded->x[0] == 10) );

  }

}
?>
