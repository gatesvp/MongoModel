<?php

class TestMongoEntityArrays extends MongoTestCase{

  private function _init_array(){

    $data_set = array('a' => 1, 'b' => 2, 'c' => array('x','y','z'));
    $data = new MongoEntity($data_set);
    $this->assertTrue($data->save(true));

    return $data;

  }

  private function _init_array_and_load(){

    $this->_init_array();

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

  function testBasicArrayDotNotation(){

    $loaded = $this->_init_array_and_load();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($loaded->{'c.0'} == 'x') &&
            $this->assertTrue($loaded->{'c.1'} == 'y') &&
            $this->assertTrue($loaded->{'c.2'} == 'z') );

  }

  function testBasicArrayChange(){

    $loaded = $this->_init_array_and_load();
    $loaded->c = array('x', 'y', 'z', 'w');
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($reloaded->c[0] == 'x') &&
            $this->assertTrue($reloaded->c[1] == 'y') &&
            $this->assertTrue($reloaded->c[2] == 'z') &&
            $this->assertTrue($reloaded->c[3] == 'w') );

  }

  function testBasicArrayDotNotationChanged(){

    $loaded = $this->_init_array_and_load();
    $loaded->{'c.3'} = 'w';
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($loaded->{'c.0'} == 'x') &&
            $this->assertTrue($loaded->{'c.1'} == 'y') &&
            $this->assertTrue($reloaded->{'c.2'} == 'z') &&
            $this->assertTrue($reloaded->{'c.3'} == 'w') );

  }

  function testArrayPush(){

    $loaded = $this->_init_array_and_load();

    $loaded->push('c', 'w');
    $loaded->save(true);

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
    $this->assertTrue($loaded->save(true));  // push should only push one of these

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertEqual($reloaded->c[0], 'x') &&
            $this->assertEqual($reloaded->c[1], 'y') &&
            $this->assertEqual($reloaded->c[2], 'z') &&
            $this->assertEqual($reloaded->c[3], 'w') );

  }

  /* Note that this test is checking for failures */
  function testArrayPushExistingField(){

    $loaded = $this->_init_array_and_load();
    $loaded->push('b', 10);
    $this->assertFalse($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertFalse(is_array($reloaded->b)) &&
            $this->assertEqual($reloaded->b, 2) );

  }

  function testArrayPushEmptyField(){

    $loaded = $this->_init_array_and_load();
    $loaded->push('x', 10);
    $this->assertTrue($loaded->save(true));  // push should only push one of these

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->x)) &&
            $this->assertEqual($reloaded->x[0], 10) );

  }

  function testArrayPushMultiple(){

    $loaded = $this->_init_array_and_load();
    $loaded->push('c', 'w');
    $loaded->push('x', 5);
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single($loaded->id);

    return ($this->assertEqual($reloaded->a, 1) );

  }

  function testArrayPushAll(){

    $loaded = $this->_init_array_and_load();
    $loaded->pushAll('c', array(10,11,12));
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

    $loaded = new MongoEntity;
    $loaded->load_single();
    $loaded->pull('c', 'x');
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

    $loaded = new MongoEntity;
    $loaded->load_single($loaded->id);
    $loaded->pullAll('c', array('x', 'z'));
    $loaded->pullAll('c', array('w', 'q'));
    $this->assertTrue($loaded->save(true));

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
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity;
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(count($loaded->c) == 4) &&
            $this->assertTrue(count($reloaded->c) == 4) &&
            $this->assertTrue($reloaded->c[3] == 'g') &&
            $this->assertTrue(array_diff($loaded->c, $reloaded->c) == array()) );

  }

  function testAddToSetNewField(){
    $loaded = $this->_init_array_and_load();
    $loaded->addToSet('d', 'g');
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity;
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->d)) &&
            $this->assertTrue(is_array($reloaded->d)) &&
            $this->assertTrue(count($loaded->d) == 1) &&
            $this->assertTrue(count($reloaded->d) == 1) &&
            $this->assertTrue($reloaded->d[0] == 'g') &&
            $this->assertTrue(array_diff($loaded->d, $reloaded->d) == array()) );

  }

  function testAddToSetExistingField(){
    $loaded = $this->_init_array_and_load();
    $loaded->addToSet('b', 'g');
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity;
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->b)) &&
            $this->assertTrue(is_array($reloaded->b)) &&
            $this->assertTrue(count($loaded->b) == 2) &&
            $this->assertTrue(count($reloaded->b) == 2) &&
            $this->assertTrue($reloaded->b[1] == 'g') &&
            $this->assertTrue(array_diff($loaded->b, $reloaded->b) == array()) );

  }

  function testAddToSetArray(){
    $loaded = $this->_init_array_and_load();
    $loaded->addToSet('c', array('y','w','a'));
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity;
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue(is_array($reloaded->c)) &&
            $this->assertTrue(count($loaded->c) == 5) &&
            $this->assertTrue(count($reloaded->c) == 5) &&
            $this->assertTrue($reloaded->c[1] == 'y') &&
            $this->assertTrue($reloaded->c[3] == 'w') &&
            $this->assertTrue(array_diff($loaded->c, $reloaded->c) == array()) );
  }

  function testAddToSetArrayOnNewField(){
    $loaded = $this->_init_array_and_load();
    $loaded->addToSet('g', array('y','w','a'));
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity;
    $reloaded->load_single($loaded->id);

    return ($this->assertTrue(is_array($loaded->g)) &&
            $this->assertTrue(is_array($reloaded->g)) &&
            $this->assertTrue(count($loaded->g) == 3) &&
            $this->assertTrue(count($reloaded->g) == 3) &&
            $this->assertTrue($reloaded->g[0] == 'y') &&
            $this->assertTrue($reloaded->g[2] == 'a') &&
            $this->assertTrue(array_diff($loaded->g, $reloaded->g) == array()) );

  }

  function testAddToSetArrayParallel(){

    $obj1 = new MongoEntity;
    $obj2 = new MongoEntity;

    $obj1->id = 123;
    $obj1->addToSet("blah", 1);
    $obj2->id = 123;
    $obj2->addToSet("blah", 2);

    $obj1->save(true);
    $obj2->save(true);

    $loaded = new MongoEntity();
    $loaded->load_single(123);

    return ($this->assertTrue(is_array($loaded->blah)) &&
        $this->assertTrue(count($loaded->blah) == 2)
    );

  }

}
?>
