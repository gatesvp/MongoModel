<?php

class TestMongoEntityArrays extends MongoTestCase{

  private function _init_array(){

    $data_set = array('a' => 1, 'b' => 2, 'c' => array('x','y','z'));
    $data = new MongoEntity($data_set);
    $data->save(true);

    return $data;

  }

  private function _init_array_and_load(){

    $this->_init_array();

    $loaded = new MongoEntity();
    $loaded->load_single();

    return $loaded;

  }

  private function _init_hash(){

    $data_set = array('a' => 1, 'b' => 2, 'c' => array(0 => 'x', 5 => 'y', 10 => 'z'));
    $data = new MongoEntity($data_set);
    $data->save(true);

    return $data;

  }

  private function _init_hash_and_load(){

    $this->_init_hash();

    $loaded = new MongoEntity();
    $loaded->load_single();

    return $loaded;

  }

  function testBasicArray(){

    $loaded = $this->_init_array_and_load();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($loaded->c[0] == 'x') &&
            $this->assertTrue($loaded->c[1] == 'y') &&
            $this->assertTrue($loaded->c[2] == 'z') );

  }

  function testHashArray(){

    $loaded = $this->_init_hash_and_load();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($loaded->c[0] == 'x') &&
            $this->assertTrue($loaded->c[5] == 'y') &&
            $this->assertTrue($loaded->c[10] == 'z') );
  }

  function testBasicArrayChange(){

    $loaded = $this->_init_array_and_load();
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

  function testBasicHashArrayChanged(){

    $loaded = $this->_init_hash_and_load();
    $loaded->c = array(0 => 'x', 5 => 'y', 7 => 'yy', 10 => 'z', 15 => 'w');
    $loaded->save();

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertEqual($reloaded->c[0], 'x') &&
            $this->assertEqual($reloaded->c[5], 'y') &&
            $this->assertEqual($reloaded->c[7], 'yy') &&
            $this->assertEqual($reloaded->c[10], 'z') &&
            $this->assertEqual($reloaded->c[15], 'w') );
  }

  function testArrayPush(){

    $loaded = $this->_init_array_and_load();

    $loaded->push('c', 'w');
    $loaded->save();

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertEqual($reloaded->c[0], 'x') &&
            $this->assertEqual($reloaded->c[1], 'y') &&
            $this->assertEqual($reloaded->c[2], 'z') &&
            $this->assertEqual($reloaded->c[3], 'w') );

  }

  function testArrayMultiPushSingleField(){

    $loaded = $this->_init_array_and_load();
    $loaded->push('c', 'a');
    $loaded->push('c', 'w');
    $loaded->save(true);  // push should only push one of these

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertEqual($reloaded->c[0], 'x') &&
            $this->assertEqual($reloaded->c[1], 'y') &&
            $this->assertEqual($reloaded->c[2], 'z') &&
            $this->assertEqual($reloaded->c[3], 'w') );

  }

  function testArrayPushExistingField(){

    $loaded = $this->_init_array_and_load();
    $loaded->push('b', 10);
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertFalse(is_array($reloaded->b)) &&
            $this->assertEqual($reloaded->b, 2) );

  }

  function testArrayPushEmptyField(){

    $loaded = $this->_init_array_and_load();
    $loaded->push('x', 10);
    $loaded->save(true);  // push should only push one of these

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->x)) &&
            $this->assertEqual($reloaded->x[0], 10) );

  }

  function testArrayPushMultiple(){

    $loaded = $this->_init_array_and_load();
    $loaded->push('c', 'w');
    $loaded->push('x', 5);
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(is_array($reloaded->x)) &&
            $this->assertEqual($reloaded->c[3], 'w') &&
            $this->assertEqual($reloaded->x[0], 5) );

  }

  function testArrayPopEnd(){

    $loaded = $this->_init_array_and_load();
    $loaded->pop('c', true);
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertEqual(count($loaded->c), 2) &&
            $this->assertEqual($loaded->c[0], 'x') &&
            $this->assertEqual($loaded->c[1], 'y') &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertEqual(count($reloaded->c), 2) &&
            $this->assertEqual($reloaded->c[0], 'x') &&
            $this->assertEqual($reloaded->c[1], 'y') );

  }

  function testArrayPopFront(){

    $loaded = $this->_init_array_and_load();
    $loaded->pop('c', false);
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertEqual(count($loaded->c), 2) &&
            $this->assertEqual($loaded->c[0], 'y') &&
            $this->assertEqual($loaded->c[1], 'z') &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertEqual(count($reloaded->c), 2) &&
            $this->assertEqual($reloaded->c[0], 'y') &&
            $this->assertEqual($reloaded->c[1], 'z') );

  }

  function testArrayPopFail(){

    $loaded = $this->_init_array_and_load();
    $loaded->pop('a');
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertEqual($reloaded->a, 1) );

  }

  function testArrayPushAll(){

    $loaded = $this->_init_array_and_load();
    $loaded->pushAll('c', array(10,11,12));
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue($reloaded->c[0] == 'x') &&
            $this->assertTrue($reloaded->c[3] == 10) && 
            $this->assertTrue($reloaded->c[4] == 11) &&
            $this->assertTrue($reloaded->c[5] == 12) );

  }

  function testArrayPushAllMulti(){
    $loaded = $this->_init_array_and_load();
    $loaded->pushAll('c', array(10,11,12));
    $loaded->pushAll('c', array(6, 7, 8));
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue($reloaded->c[0] == 'x') &&
            $this->assertTrue($reloaded->c[3] == 10) && 
            $this->assertTrue($reloaded->c[4] == 11) &&
            $this->assertTrue($reloaded->c[5] == 12) &&
            $this->assertTrue($reloaded->c[6] == 6) &&
            $this->assertTrue($reloaded->c[7] == 7) &&
            $this->assertTrue($reloaded->c[8] == 8) );

  }

  function testArrayPullBasic(){
    $loaded = $this->_init_array_and_load();
    $loaded->pull('c', 'x');
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(count($loaded->c) == 2) &&
            $this->assertTrue(count($reloaded->c) == 2) &&
            $this->assertTrue(array_diff($loaded->c, $reloaded->c) == array()) );

  }

  function testArrayPullAdvanced(){
    $loaded = $this->_init_array_and_load();
    $loaded->push('c', 'x');
    $loaded->save(true);

    $loaded = new MongoEntity;
    $loaded->load_single();
    $loaded->pull('c', 'x');
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(count($loaded->c) == 2) &&
            $this->assertTrue(count($reloaded->c) == 2) &&
            $this->assertTrue(array_diff($loaded->c, $reloaded->c) == array()) );

  }

  function testArrayPullMulti(){
    $loaded = $this->_init_array_and_load();
    $loaded->pull('c', 'x');
    $loaded->pull('c', 'z');
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(count($loaded->c) == 1) &&
            $this->assertTrue(count($reloaded->c) == 1) &&
            $this->assertTrue($reloaded->c[0] == 'y') &&
            $this->assertTrue(array_diff($loaded->c, $reloaded->c) == array()) );

  }

  function testArrayPullAllBasic(){
    $loaded = $this->_init_array_and_load();
    $loaded->pullAll('c', array('x', 'z'));
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(count($loaded->c) == 1) &&
            $this->assertTrue(count($reloaded->c) == 1) &&
            $this->assertTrue($reloaded->c[0] == 'y') &&
            $this->assertTrue(array_diff($loaded->c, $reloaded->c) == array()) );

  }

  function testArrayPullAllMulti(){
    $loaded = $this->_init_array_and_load();
    $loaded->pushAll('c', array('w', 'q'));
    $loaded->save(true);

    $loaded = new MongoEntity;
    $loaded->load_single($loaded->id);
    $loaded->pullAll('c', array('x', 'z'));
    $loaded->pullAll('c', array('w', 'q'));
    $loaded->save(true);

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(count($loaded->c) == 1) &&
            $this->assertTrue(count($reloaded->c) == 1) &&
            $this->assertTrue($reloaded->c[0] == 'y') &&
            $this->assertTrue(array_diff($loaded->c, $reloaded->c) == array()) );

  }

  function testAddToSetBasic(){
    $loaded = $this->_init_array_and_load();
    $loaded->addToSet('c', 'g');
    $loaded->save(true);

    $reloaded = new MongoEntity;
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(count($loaded->c) == 4) &&
            $this->assertTrue(count($reloaded->c) == 4) &&
            $this->assertTrue($reloaded->c[3] == 'g') &&
            $this->assertTrue(array_diff($loaded->c, $reloaded->c) == array()) );

  }

}
?>
