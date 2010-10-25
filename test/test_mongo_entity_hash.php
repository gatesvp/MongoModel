<?php

class TestMongoEntityHash extends MongoTestCase{

  private function _init_hash(){

    $data_set = array('a' => 1, 'b' => 2, 'c' => array(0 => 'x', 5 => 'y', 10 => 'z'));
    $data = new MongoEntity($data_set);
    $this->assertTrue($data->save(true));

    return $data;

  }

  private function _init_hash_and_load(){

    $this->_init_hash();

    $loaded = new MongoEntity();
    $loaded->load_single();

    return $loaded;

  }

  function testHashArray(){

    $loaded = $this->_init_hash_and_load();

    return ($this->assertTrue(is_array($loaded->c)) &&
            $this->assertTrue($loaded->c[0] == 'x') &&
            $this->assertTrue($loaded->c[5] == 'y') &&
            $this->assertTrue($loaded->c[10] == 'z') );
  }

  function testBasicHashArrayChanged(){

    $loaded = $this->_init_hash_and_load();
    $loaded->c = array(0 => 'x', 5 => 'y', 7 => 'yy', 10 => 'z', 15 => 'w');
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertTrue(is_array($reloaded->c)) &&
            $this->assertEqual($reloaded->c[0], 'x') &&
            $this->assertEqual($reloaded->c[5], 'y') &&
            $this->assertEqual($reloaded->c[7], 'yy') &&
            $this->assertEqual($reloaded->c[10], 'z') &&
            $this->assertEqual($reloaded->c[15], 'w') );
  }

  function testAdvancedHashArray(){

    $loaded = $this->_init_hash_and_load();
    $loaded->{'c.15'} = 'w';
    $loaded->{'c.12.blah'} = array('test' => 2);
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertEqual($loaded->c[15], 'w') &&
            $this->assertEqual($loaded->c[12]['blah']['test'], 2) &&
            $this->assertEqual($reloaded->c[15], 'w') &&
            $this->assertEqual($reloaded->c[12]['blah']['test'], 2));

  }

  function testHashArrayNewData(){

    $loaded = $this->_init_hash_and_load();
    $loaded->{'d.15'} = 'w';
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertEqual($loaded->d[15], 'w') &&
            $this->assertEqual($reloaded->d[15], 'w') &&
            $this->assertEqual($reloaded->{'d.15'}, 'w'));

  }

  function testAdvancedHashArrayDoubleUpdate(){

    $loaded = $this->_init_hash_and_load();
    $loaded->{'c.12.blah.test'} = 2;
    $loaded->{'c.12.blah.fie'} = 3;
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertEqual($loaded->c[12]['blah']['test'], 2) &&
            $this->assertEqual($loaded->c[12]['blah']['fie'], 3) &&
            $this->assertEqual($reloaded->c[12]['blah']['test'], 2) &&
            $this->assertEqual($reloaded->c[12]['blah']['fie'], 3) && 
            $this->assertEqual($reloaded->{'c.12.blah.fie'}, 3) );

  }

  function testAdvancedHashArrayReUpdate(){

    $loaded = $this->_init_hash_and_load();
    $loaded->{'c.12.blah.test'} = 2;
    $loaded->{'c.12.blah.test'} = 3;
    $this->assertTrue($loaded->save(true));

    $reloaded = new MongoEntity();
    $reloaded->load_single();

    return ($this->assertEqual($loaded->c[12]['blah']['test'], 3) &&
            $this->assertEqual($reloaded->c[12]['blah']['test'], 3));

  }

}
?>
