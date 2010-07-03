<?
class MongoBase {

  const MONGO_USERDATA_SERVER = '10.201.16.70'; # pkc-srdb01
  const MONGO_USERDATA_PORT = 6969;

  /**
   * See here for more ideas:
   * http://github.com/ibwhite/simplemongophp
   * This likely needs a parent factory for returns arrays of queried objects.
   */

  protected $_mongo_server = MongoBase::MONGO_USERDATA_SERVER;  # Server IP or name
  protected $_mongo_port = MongoBase::MONGO_USERDATA_PORT; # Server port number
  protected $_mongo_database = "temp";        # Effectively the database name
  protected $_mongo_collection = "test";      # Effectively the table name
  protected $_mongo_timeout = 5000;           # 5 second default timeout on connect

  protected $_id;
  protected $_data = array();
  protected $_unset = array();
  
  protected $_field_map = array();            # map of "incoming" names to "underlying" names
  
  protected static $_mongo = array ();
  protected $_collections = array ();

  function __construct($data = array()){

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
    if(!array_key_exists($connectionString, self::$_mongo)){
      $mongo = new Mongo($connectionString
                        ,array("connect" => true
                              ,"timeout" => $this->_mongo_timeout));
      self::$_mongo[$connectionString] = $mongo;
    }
    return self::$_mongo[$connectionString];
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
      if (!array_key_exists($connectionString, $this->_collections)) {
        $this->_collections[$connectionString] = array();
      }
      if (!array_key_exists($collectionName
                           ,$this->_collections[$connectionString])) {
        $mongo = $this->getDatabase($serverName,$portNumber);
        $db = $mongo->selectDB($databaseName);
        if ($db) {
            $collection = $db->selectCollection($collectionName);
        }
        $this->_collections[$connectionString][$collectionName] = $collection;
      }
      return $this->_collections[$connectionString][$collectionName];
    }
    catch (Exception $e) {
      error_log($error);
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
    unset($this->_data[$field]);
  }

  function unset_field($field){
    unset($this->_data[$field]);
    $this->_unset[$field] = 1;
  }
  
  /**
   *
   * @param object $query [optional]
   * @param object $fields [optional]
   * @param object $sort [optional]
   * @param object $limit [optional]
   * @param object $skip [optional]
   * @return Success of the load
   */
  public function load_single($query = array(), $fields = array()){

    try{
      $collection = $this->loadCollection();
      if($collection){
        $query_result = $collection->find($query)->timeout(250);

        if($query_result->hasNext()){
          // If we leave _id in the results mongo becomes confused by future save attempts. It thinks we're trying to change the id.
          // If the ID becomes needed (for some reason), this will need to be changed.
          $result = $query_result->getNext();

          if(isset($result[_id])) {
            $this->_id = $result[_id];
            unset($result[_id]);
          }

          $this->_data = $result;

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
   * Default save based off of updating by MongoId.
   * Saves the object "as-is".
   * Can be overridden with "alternate" key.
   * @param boolean $safe option to force a "safe" update. Defaults to "unsafe".
   * @return Success of the save
   */
  public function save($safe = false, $upsert = true, $multiple = false){
    $collection = $this->loadCollection();
    
    if($collection){
      $update_commands = array();
      if(count($this->_data) > 0){ $update_commands['$set'] = $this->_data; }
      if(count($this->_unset) > 0){ $update_commands['$unset'] = $this->_unset; }

      return $collection->update(
        array("_id" => new MongoId($this->_data['_id'])),
        $update_commands, 
        array("upsert" => $upsert, "multiple" => $multiple, "safe" => $safe));
    }

    return FALSE;

  }

  /**
   * Adds the given item to the given "field" if it doesn't already exist.
   * If the "field" is not an array, then it is converted to an array and the given item is added.
   * @param string $field
   * @param object $item
   * @return
   */
  function add_to_set($field, $item){

    if(isset($this->_field_map[$field])){
      $field = $this->_field_map[$field];
    }
    
    $curr = $this->_data[$field];
    
    if($curr == null){
      // Field does not exist, make it an array
      $new_item = array($item);
    }
    elseif(!is_array($curr)){
      // Field does exist, but is not an array, make it one
      $new_item = array($curr, $item);
    }
    elseif(is_array($curr)){
      $new_item = new ArrayObject(array_unique(array_merge($curr,array($item))));
    }

    $this->_data[$field] = $new_item;

  }

}
