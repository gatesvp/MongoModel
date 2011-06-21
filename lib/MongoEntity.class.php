<?
/**
 * MongoEntity is a simple Object-oriented PHP class for interfacing with MongoDB.
 * Source: github.com/gatesvp/MongoModel
 * @author: Gaetan Voyer-Perrault (github.com/gatesvp/)
 * @date: 2010-10-25
 */

// TODO
// Add 'fsync' options on save/delete
// Add user authentication to connection string
// Persistent connections & pooling?

class MongoEntity {

  const MONGO_SERVER = 'localhost';
  const MONGO_PORT = 27017;

  protected static $_mongo_server = MongoEntity::MONGO_SERVER;  # Server IP or name
  protected static $_mongo_port = MongoEntity::MONGO_PORT; # Server port number
  protected static $_mongo_database = "test";        # Effectively the database name
  protected static $_mongo_collection = "test";      # Effectively the table name
  protected static $_mongo_connection_timeout = 5000;     # 5 second default timeout on connect
  protected static $_mongo_query_timeout = 1000;
  protected static $_mongo_is_replica_set = false;
  protected static $_mongo_user_name = '';
  protected static $_mongo_password = '';

  protected $_id;
  protected $_data = array();
  protected $_unset = array();
  protected $_set = array();
  protected $_increment = array();
  protected $_push = array();
  protected $_pop = array();
  protected $_addToSet = array();
  protected $_pushAll = array();
  protected $_pull = array();
  protected $_pullAll = array();  

  protected $_field_map = array();            # map of "incoming" names to "underlying" names
  
  function __construct($data = array()){
    $this->_set_data($data);
  }

  private function _set_data($data = array()){
    // Extra brains to extract the ID from the data.
    // Useful when it comes time to save.
    if(isset($data['id'])) { 
      $this->_id = $data['id']; 
      unset($data['id']);
    }
    elseif(isset($data['_id'])) { 
      $this->_id = $data['_id']; 
      unset($data['_id']);
    }

    // Set the actual local copy
    $this->_data = $data;
  }

  protected static function getConnectionString($server, $port, $userName, $password) {

    $auth_info = '';
    $db_info = '';
    $server_port = '';
    if($userName != '' && $password != ''){
      $auth_info = "$userName:$password";
      $db_info = static::$_mongo_database;
    }

    if(is_array($server) && is_array($port) && count($server) == count($port)){
      $connections = array();
      for($i = 0; $i < count($server); $i++){
        $connections[] = $server[$i].":".$port[$i];
      }
      $server_port = join(",", $connections);
    }
    elseif(!is_array($server) && !is_array($port)){
      $server_port = "$server:$port";
    }
    else{
      throw new Exception('Invalid Connection String');
    }

    if($auth_info){
      return "mongodb://".$auth_info.'@'.$server_port.'/'.$db_info;
    }
    else {
      return "mongodb://".$server_port;
    }

  }

  protected static function getConnection($server = null, $port = null, $userName = null, $password = null){
    if ($server == null) {
      $server = static::$_mongo_server;
    }
    if ($port == null) {
      $port = static::$_mongo_port;
    }
    if ($userName == null) {
      $userName = static::$_mongo_user_name;
    }
    if ($password == null) {
      $password = static::$_mongo_password;
    }

    // determine the connection string for this server/port
    $connectionString = self::getConnectionString($server, $port, $userName, $password);
    $connectionOptions = array("conect" => true, "timeout" => static::$_mongo_connection_timeout);

    if(static::$_mongo_is_replica_set){
      $connectionOptions['replicaSet'] = true;
    }

    $mongo = new Mongo($connectionString, $connectionOptions);
    return $mongo;
  }

  public static function loadCollection($collectionName = null,$databaseName = null,$serverName = null,$portNumber = null,$userName = null, $password = null) {
    if (!isset($collectionName)) {
      $collectionName = static::$_mongo_collection;
    }
    if (!isset($serverName)) {
      $serverName = static::$_mongo_server;
    }
    if (!isset($portNumber)) {
      $portNumber = static::$_mongo_port;
    }
    if (!isset($databaseName)) {
      $databaseName = static::$_mongo_database;
    }
    if (!isset($userName)) {
      $userName = static::$_mongo_user_name;
    }
    if (!isset($password)) {
      $password = static::$_mongo_password;
    }
    try {
      $mongo = self::getConnection($serverName, $portNumber, $userName, $password);
      $db = $mongo->selectDB($databaseName);
      $collection = $db->selectCollection($collectionName);
      return $collection;
    }
    catch (Exception $e) {
      /* Add logging */
      print $e->getMessage()."\n";
    }
    return FALSE;
  }

  private function _remap_field($field){
    if(isset($this->_field_map[$field])){
      $field = $this->_field_map[$field];
    }
    return $field;
  }

  /**
   * Override for undefined "getters"
   * @param string $field -> the name of the property we are getting
   * @return
   */
  function __get($field){

    $field = $this->_remap_field($field);
    
    // special handling for fields named ID
    if(strtolower($field) == 'id'){
      return $this->_id;
    }

    // special handling for dot-notation fields
    $i = strpos($field, '.');
    if($i !== false){
      return $this->_getSubArray($field, $this->_data);
    }

    // standar handling for data fields
    if(isset($this->_data[$field])){
      return $this->_data[$field];
    }
    else{
      return null;
    }

  }

  /**
   * Recursive handler for sub-arrays/hashtables accessed using the dot notation.
   */
  private function _getSubArray($field_name, &$curr_array){

    $i = strpos($field_name, '.');

    if($i !== false){
      $field = substr($field_name, 0, $i);

      if(!isset($curr_array[$field])){
        return null;
      }
      else{
        $curr_array =& $curr_array[$field];
        return $this->_getSubArray(substr($field_name, $i + 1), $curr_array);
      }

    }
    else {
      return isset($curr_array[$field_name]) ? $curr_array[$field_name] : 0;
    }

  }

  /**
   * Override for undefined "setters"
   * @param string $field -> the name of the property we are setting
   * @param object $value -> value to which we are setting
   * @return
   */
  function __set($field, $value){

    $field = $this->_remap_field($field);

    // special handling for ID fields 
    if(strtolower($field) == 'id') {
      return $this->_id = $value;
    } 

    // special handling for dot-notation fields
    $i = strpos($field, '.');
    if($i !== false){
      $res = $this->_setSubArray($field, $this->_data, $value);
      $this->_set[$field] = $value;
    }

    // setting the value also adds to the $set array
    $this->_set[$field] = $value;
    return $this->_data[$field] = $value;
 
  }

  /**
   * Recursive handler for sub-arrays/hashtables accessed using the dot notation.
   */
  private function _setSubArray($field_name, &$curr_array, $value){

    $i = strpos($field_name, '.');

    if($i !== false){
      $field = substr($field_name, 0, $i);

      if(!isset($curr_array[$field])){
        $curr_array[$field] = array();
      }
      $curr_array =& $curr_array[$field];
      return $this->_setSubArray(substr($field_name, $i + 1), $curr_array, $value);

    }
    else {
      return $curr_array[$field_name] = $value;
    }

  }

  /**
   * Override for the 'isset' method on an undefined field
   * @param string $field -> name of the property we are checking
   * @return
   */
  function __isset($field){

    return isset($this->_data[$field]);

  }

  /**
   * Override for the 'unset' method on an undefined field
   * @param string $field -> name of the property we are unsetting
   * @return
   */
  function __unset($field){

    $this->_unset[$field] = 1;
    unset($this->_data[$field]);

  }

  /**
   * Loads a single entry based on the given ID field.
   * If no ID is given then the first entry loaded.
   * @param object $id [optional] -> ID of the document to find
   * @param array $fields [optional] -> fields to return
   * @return Success of the load
   */
  public function load_single($id = null, $fields = array()){

    try{
      $collection = $this->loadCollection();
      if($collection){
        $query = array();
        $mapped_fields = array();

        foreach($fields as $f) { $mapped_fields[] = $this->_remap_field($f); }

        if($id != null) { $query['_id'] = $id; }
        $query_result = $collection->find($query, $mapped_fields)->timeout($this->_mongo_query_timeout);

        if($query_result->hasNext()){
          $result = $query_result->getNext();

          $this->_set_data($result);

          return TRUE;
        }
      }
    }
    catch(MongoCursorTimeoutException $e) {
      // Log exception?
    }
    catch(Exception $e){
      // Log exception?
    }
    return FALSE;

  }

  /**
   * Save the current entry, based on the edits performed.
   * The data will attempt to save
   * @param boolean safe [optional] -> indicates that we should wait for a server response, default of false
   * @param boolean upsert [optional] -> indicates that this object can be created if it does not already exist, default true.
   * @return Success of the save (based on assumptions)
   */
  public function save($safe = false, $upsert = true){
    $collection = self::loadCollection();
    
    if($collection){
      
      if(isset($this->_id)){ 
        $id_query = array();
        $id_query['_id'] = $this->_id; 
        
        $update_commands = array();
        if(count($this->_set) > 0){ $update_commands['$set'] = $this->_set; }
        if(count($this->_unset) > 0){ $update_commands['$unset'] = $this->_unset; }
        if(count($this->_increment) > 0) { $update_commands['$inc'] = $this->_increment; }
        if(count($this->_push) > 0) {
          $update_commands['$push'] = array();
          foreach($this->_push as $field => $value) {
            $update_commands['$push'][$field] = $value;
          }
        }
        if(count($this->_pop) > 0) {
          $update_commands['$pop'] = array();
          foreach($this->_pop as $field => $value) {
            $update_commands['$pop'][$field] = $value;
          }
        }
        if(count($this->_pushAll) > 0) {
          foreach($this->_pushAll as $field => $value) {
            $update_commands['$pushAll'][$field] = $value;
           }
        }
        if(count($this->_pull) > 0) {
          foreach($this->_pull as $field => $value) {
            $update_commands['$pull'][$field] = $value;
           }
        }
        if(count($this->_pullAll) > 0) {
          foreach($this->_pullAll as $field => $value) {
            $update_commands['$pullAll'][$field] = $value;
           }
        }
        if(count($this->_addToSet) > 0) {
          foreach($this->_addToSet as $field => $value) {
            foreach($value as $v){
              $update_commands['$addToSet'][$field]['$each'][] = $v;
            }
          }
        }

        $update_flags = array("upsert" => $upsert, "safe" => $safe);

        try{
          if(count($update_commands) > 0){
            $res = $collection->update($id_query, $update_commands, $update_flags);
            return $res;
          }
          else { 
            return true;
          }
        }
        catch(MongoCursorException $e) {
          return false;
        }
        catch(MongoCursorTimeoutException $e){
          return false;
        }
      }
      else { 
        try{
          $res = $collection->insert($this->_data, array('safe' => $safe));
          $success = !$safe ? ($res) : (isset($res['ok']) && $res['ok'] == true);
          if($success) { 
            $this->_id = $this->_data['_id']; 
            unset($this->_data['_id']);
          }
          return $success;
        }
        catch(MongoCursorException $e) {
          return false;
        }
        catch(MongoCursorTimeoutException $e){
          return false;
        }
      }
    }

    return FALSE;

  }

  public function delete($safe = false){
     $args = array('justOne' => true, 'safe' => $safe);
     $collection = $this->loadCollection();

     if($collection && isset($this->_id)){
       $res = $collection->remove(array('_id' => $this->_id), $args);
       return (!$safe) ? $res : (isset($res['ok']) && $res['ok'] == 1);
     }

     return false;

  }

  /**
   * Increment
   *
  */
  public function increment($field, $amount = 1){

    $field = $this->_remap_field($field);

    if(isset($this->_data[$field])){
      $this->_data[$field] += $amount;
    }
    else{
      $this->_data[$field] = $amount;
    }

    $this->_increment[$field] = $amount;
  }

  public function push($field, $value){

    $field = $this->_remap_field($field);

    if(isset($this->_data[$field]) && is_array($this->_data[$field])){
      array_push($this->_data[$field], $value);
    }
    else if(isset($this->_data[$field])){
      /* invalid case, we can't do anything */
    }
    else {
      $this->_data[$field] = array($value);
    }

    $this->_push[$field] = $value;

  }

  public function pop($field, $back = true){

    $field = $this->_remap_field($field);

    if(isset($this->_data[$field]) && is_array($this->_data[$field])){
      if($back){
        array_pop($this->_data[$field]);
        $this->_pop[$field] = 1;
      }
      else {
        array_shift($this->_data[$field]);
        $this->_pop[$field] = -1;
      }
    }

  }

  public function pushAll($field, $values = array()){

    $field = $this->_remap_field($field);

    if(is_array($this->_data[$field])){
      array_splice($this->_data[$field], count($this->_data[$field]), 0, $values);

    }
    else if(isset($this->_data[$field])){
      /* invalid case, we can't do anything */
    }
    else {
      $this->$field = array($value);
    }

    if(isset($this->_pushAll[$field]) && is_array($this->_pushAll[$field])){
      $this->_pushAll[$field] = array_merge($this->_pushAll[$field], $values);
    }
    else{
      $this->_pushAll[$field] = $values;
    }

  }

  public function pull($field, $value){

    $field = $this->_remap_field($field);

    if(is_array($this->_data[$field])){

      /* Basic logic here is to make a copy of the array and remove all matches.
         We're then using array_values to "re-zero" the indexes and re-assign the _data value */
      $current_array = $this->_data[$field];
      $current_size = count($current_array);

      for($i = 0; $i < $current_size; $i++){ 
        if($current_array[$i] == $value){ 
          unset($current_array[$i]); 
        }
      }

      $this->_data[$field] = array_values($current_array);

      /* If we attempt a second pull, then we're technically doing a "pullAll"
         So we append the existing data and new data to "pullAll", then we unset "pull".
      */
      if(isset($this->_pull[$field])){
        if(isset($this->_pullAll[$field]) && is_array($this->_pullAll[$field])){
          $this->_pullAll[$field] = array_merge($this->_pullAll[$field], array($this->_pull[$field], $value));
        }
        else{
          $this->_pullAll[$field] = array($this->_pull[$field], $value);
        }
        unset($this->_pull[$field]);
      }
      else{
        $this->_pull[$field] = $value;
      }
    }

  }

  public function pullAll($field, $values = array()){

    foreach($values as $v){
      $this->pull($field, $v);
    }

  }

  public function addToSet($field, $values = array()){
    /* 
       This method has four cases:
       - field exists vs. field DNE
       - values is array vs. !is array
    */
    $field = $this->_remap_field($field);

    if(!isset($this->_data[$field])){
      if(is_array($values)){
        $this->_addToSet[$field] = $values;
        $this->_data[$field] = $values;
      }
      else{
        $this->_addToSet[$field] = array($values);
        $this->_data[$field] = array($values);
      }
    }
    else if(is_array($values)){

      foreach($values as $v){
        $this->_addToSet[$field][] = $v;
 
        if($this->_data[$field]){       
          $found = false;
          foreach($this->_data[$field] as $f){
            if($v == $f){
              $found = true;
              continue;
            }
          }

          if(!$found){
            $this->_data[$field][] = $v;
          }
        }
        else{

          $this->_data[$field][] = $v;

        }
      }

    }
    else if(is_array($this->_data[$field])){
      $this->_addToSet[$field][] = $values;

      $found = false;
      if($this->_data[$field]){
        foreach($this->_data[$field] as $f){
          if($values == $f){
            $found = true;
            continue;
          }
        }
      }

      if(!$found){
        $this->_data[$field][] = $values;
      }

    }
    elseif($this->_data[$field] != null){
      $this->_set[$field] = array($this->_data[$field], $values);
      $this->_data[$field] = array($this->_data[$field], $values);
    }
    else{
      $this->_addToSet[$field] = array($values);
      $this->_data[$field] = array($values);
    }

  }
}
