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
  protected $_mongo_timeout = 5000;           # 5 second default timeout on connect

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
    $this->_data = $data;
    if(isset($data['id'])) { $this->_id = $data['id']; }
    elseif(isset($data['_id'])) { $this->_id = $data['_id']; }
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
      /* TODO FIX */
      print "Error\n";
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

  public function save($safe = false, $upsert = true, $reload = false){
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

}
