<?php

class TestMongoEntityIncrement extends MongoTestCase{

  private function _init_entry(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    $this->assertTrue($data->save(true));

    return $data;
  }

  function testIncrementOnLoaded(){

    $data = $this->_init_entry();
    $data->increment('a', 5);

    if($data->save(true) && $this->assertEqual($data->a, 6)){

      $loaded = new MongoEntity();
      $loaded->load_single();

      return $this->assertEqual($loaded->a, 6);
    }
    else{
      return false;
    }

  }

  function testIncrementOnUnloaded(){

    $data = $this->_init_entry();

    $id = $data->id;

    $fire_and_forget = new MongoEntity();
    $fire_and_forget->id = $id;
    $fire_and_forget->increment('a', 5);
    $this->assertTrue($fire_and_forget->save(true));  // Not truly "fire & forget" b/c of "safe".

    $loaded = new MongoEntity();
    $loaded->load_single($id);
    
    return $this->assertEqual($loaded->a, 6);

  }

}
?>
