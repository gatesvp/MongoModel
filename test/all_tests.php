<?php

require_once('simpletest/autorun.php');

class AllTests extends TestSuite{
  function AllTests(){
    $this->TestSuite('All Tests');
    $this->addFile('test_mongo_entity_basic.php');
  }
}

