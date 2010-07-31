<?
class MongoEntity {

  const MONGO_SERVER = 'localhost';
  const MONGO_PORT = 27017;

  /**
   * See here for more ideas:
   * http://github.com/ibwhite/simplemongophp
   * This likely needs a parent factory for returns arrays of queried objects.
   */

  protected $_mongo_server = MongoEntity::MONGO_SERVER;  # Server IP or name
  protected $_mongo_port = MongoEntity::MONGO_PORT; # Server port number
  protected $_mongo_database = "test";        # Effectively the database name
  protected $_mongo_collection = "test";      # Effectively the table name
  protected $_mongo_connection_timeout = 5000;     # 5 second default timeout on connect
  protected $_mongo_query_timeout = 1000;

  protected $_id;
  protected $_data = array();
  protected $_unset = array();
  protected $_set = array();
  protected $_increment = array();
  protected $_addToSet = array();
  
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

  protected static function getConnectionString($server,$port) {
    return "$server:$port";
  }

  protected function getDatabase($server = null, $port = null){
    if ($server == null) {
      $server = $this->_mongo_server;
    }
    if ($port == null) {
      $port = $this->_mongo_port;
    }

    // determine the connection string for this server/port
    $connectionString = self::getConnectionString($server,$port);

    // if we haven't already connected to this mongo server on this port, do so
    $mongo = new Mongo($connectionString
                        ,array("connect" => true
                              ,"timeout" => $this->_mongo_timeout));
    return $mongo;
  }

  protected function loadCollection($collectionName = null,$serverName = null
                                   ,$portNumber = null,$databaseName = null) {
    if (!isset($collectionName)) {
      $collectionName = $this->_mongo_collection;
    }
    if (!isset($serverName)) {
      $serverName = $this->_mongo_server;
    }
    if (!isset($portNumber)) {
      $portNumber = $this->_mongo_port;
    }
    if (!isset($databaseName)) {
      $databaseName = $this->_mongo_database;
    }
    try {
      $connectionString= self::getConnectionString($serverName,$portNumber);
      $mongo = $this->getDatabase($serverName,$portNumber);
      $db = $mongo->selectDB($databaseName);
      $collection = $db->selectCollection($collectionName);
      return $collection;
    }
    catch (Exception $e) {
      /* Add logging */
    }
    return FALSE;
  } // function loadCollection($collectionName,$serverName,$portNumber
    //                        ,$databaseName = null)

  /**
   * Override for undefined "getters"
   * @param string $field -> the name of the property we are getting
   * @return
   */
  function __get($field){

    if($field == 'id'){
      return $this->_id;
    }
    
    if(isset($this->_field_map[$field])){
      $field = $this->_field_map[$field];
    }

    if(strpos($field, '.') !== FALSE){
      return $this->_getSubArray($field, $this->_data);
    }

    if(isset($this->_data[$field])){
      if(is_array($this->_data[$field])){
        $this->_data[$field] = new ArrayObject($this->_data[$field]);
      }
      return $this->_data[$field];
    }
    else{
      return null;
    }
  }

  /**
   * Used to recursively load parameters requested via "dot notation".
   * These are basically nested arrays within the data.
   *
   * @param object $field
   * @param object $data
   * @return
   */
  function _getSubArray($field, &$data){

    $i = strpos($field, '.');

    if($i !== FALSE){
      $f = substr($field, 0, $i);

      if(isset($data[$f])){
        return $this->_getSubArray(substr($field, $i+1), $data[$f]);
      }
      else{
        return null;
      }

    }
    else{
      if(isset($this->_data[$field])){
        if(is_array($this->_data[$field])){
          $this->_data[$field] = new ArrayObject($this->_data[$field]);
        }
        return $this->_data[$field];
      }
      else{
        return null;
      }
    }
  }

  /**
   * Override for undefined "setters"
   * @param string $field -> the name of the property we are setting
   * @param object $value -> value to which we are setting
   * @return
   */
  function __set($field, $value){
    
    if(isset($this->_field_map[$field])){
      $field = $this->_field_map[$field];
    }

    if(strpos($field,'.') !== FALSE){
      return $this->_setSubArray($field, $value, $data);
    }
    else{
      $this->_set[$field] = $value;
      return $this->_data[$field] = $value;
    }

  }

  /**
   * Support for the "dot-notation" when setting.
   * @param object $field
   * @param object $value
   * @param object $data
   * @return
   */
  function _setSubArray($field, $value, &$data){

    $i = strpos($field, '.');

    if($i !== FALSE){
      $f = substr($field, 0, $i);

      if(!isset($data[$f])){
        $data[$f] = array();
      }
      return $this->_setSubArray($f, $value, $data[$f]);
    }
    else{

      return $data[$f] = $value;

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
   * @param array $fields [optional] -> fields to return. Format is an array of "field" => 1 for inclusions, "field" => -1 for exclusions.
   * @return Success of the load
   */
  public function load_single($id = null, $fields = array()){

    try{
      $collection = $this->loadCollection();
      if($collection){
        $query = array();
        if($id != null) { $query['_id'] = $id; }
        $query_result = $collection->find($query, $fields)->timeout($this->_mongo_query_timeout);

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
   * @param object $id [optional] -> ID of the document to find
   * @param array $fields [optional] -> fields to return. Format is an array of "field" => 1 for inclusions, "field" => -1 for exclusions.
   * @return Success of the load
   */
  public function save($safe = false, $upsert = true, $reload = false){
    $collection = $this->loadCollection();
    
    if($collection){
      $id_query = array();
      if(isset($this->_id)){ $id_query['_id'] = $this->_id; }
      else { $id_query['_id'] = new MongoId(); }

      $update_commands = array();
      if(count($this->_set) > 0){ $update_commands['$set'] = $this->_set; }
      if(count($this->_unset) > 0){ $update_commands['$unset'] = $this->_unset; }
      if(count($this->_increment) > 0) { $update_commands['$inc'] = $this->_increment; }

      return $collection->update(
        $id_query,
        $update_commands, 
        array("upsert" => $upsert, "safe" => $safe)
      );
    }

    return FALSE;

  }

  /**
   * Increment
  public function increment($field, $amount = 1){
    $this->$field = $this->$field + $amount;
    unset($this->_set[$field]);  // These two concepts are incompatible.
    $this->_increment[$field] = $amount;
  }

}
