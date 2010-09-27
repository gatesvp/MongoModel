<?php

class TestMongoFactory extends MongoTestCase{

  private function _init_entry(){
    $data_set = array('a' => 1, 'b' => 2);
    $data = new MongoEntity($data_set);
    $data->save(true);

    return $data;
  }

  private function _init_entry2(){
    $data_set = array('a' => 5, 'b' => 2);
    $data = new MongoEntity($data_set);
    $data->save(true);

    return $data;
  }

  public function testFactoryLoadObjectById(){
    
    $data = $this->_init_entry();

    $data2 = MongoFactory::LoadObjectById('MongoEntity', $data->id);

    return($this->assertEqual($data->a, $data2->a) &&
           $this->assertEqual($data->b, $data2->b) );

  }

  public function testFactoryLoadObjectsByQueryNoQuery(){

    $data1 = $this->_init_entry();
    $data2 = $this->_init_entry();

    $data_set = MongoFactory::LoadObjectsByQuery('MongoEntity');

    return ($this->assertEqual(count($data_set), 2) &&
            $this->assertEqual($data_set[0]->a, 1));

  }

  public function testFactoryLoadObjectsByQuerySimpleQuery(){

    $data1 = $this->_init_entry();
    $data2 = $this->_init_entry2();

    $data_set = MongoFactory::LoadObjectsByQuery('MongoEntity', array('a' => 5));

    return ($this->assertEqual(count($data_set), 1) &&
            $this->assertEqual($data_set[0]->a, 5));

  }

  public function testFactoryLoadObjectsByQuerySimpleQuery2(){

    $data1 = $this->_init_entry();
    $data2 = $this->_init_entry2();

    $data_set = MongoFactory::LoadObjectsByQuery('MongoEntity', array('b' => 2));

    return ($this->assertEqual(count($data_set), 2) &&
            $this->assertEqual($data_set[0]->b, 2) &&
            $this->assertEqual($data_set[1]->b, 2));

  }

  public function testFactoryLoadObjectsByQuerySimpleQueryGTE(){

    $data1 = $this->_init_entry();
    $data2 = $this->_init_entry2();

    $data_set = MongoFactory::LoadObjectsByQuery('MongoEntity', array('a' => array('$gte' => 4)));

    return ($this->assertEqual(count($data_set), 1) &&
            $this->assertEqual($data_set[0]->a, 5));

  }

  public function testFactoryLoadObjectFailure(){

    $data_set = MongoFactory::LoadObjectsByQuery('MongoBlah');

    return $this->assertNull($data_set);

  }

}
?>
