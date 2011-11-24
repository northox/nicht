<?php
function fatalError()
{
  ob_start();
  header('HTTP/1.1 503 Service Temporarily Unavailable');
  header('Status: 503 Service Temporarily Unavailable');
  header('Retry-After: 3600');
  include(NICHT_PATH_TPL.NICHT_PAGE_FATAL_ERROR.NICHT_SUFFIX_TPL);
  exit();
}

/**
* Singleton class
*
* Share common object
*/
class Singleton {
  private static $instance;
  private $db = NULL;

  private function __construct() {}
  private function __clone() {}

  public static function getInstance()
  {
    if (self::$instance === null)
      self::$instance = new self;
    return self::$instance;
  }

  // Lazy design pattern implementation
  private function getLazyDb()
  {
    // return if the function has already run
    if($this->db !== NULL) return;

    $this->db = new mysqli(MYSQLI_HOSTNAME, MYSQLI_USER, MYSQLI_PASS, MYSQLI_DATABASE);
    if (mysqli_connect_errno())
    {
      error_log("Connect failed: ".mysqli_connect_error());
      fatalError();
    }
    $this->db->query("SET NAMES 'utf8'");
  }

  public function getDb()
  {
    $this->getLazyDb();
    return($this->db);
  }
}
    
abstract class Session
{
  public $db;

  public function __construct(&$db, $maxlifetime = NULL) 
  {
    $this->db = $db;

    // set garbage collector maxlifetime from config if none set on object creation.
    if($maxlifetime === NULL) 
    {
      $this->maxlifetime = ini_get('session.gc_maxlifetime');
    } else {
      $this->maxlifetime = $maxlifetime;
    }

    // register handler
    if(!session_set_save_handler(array(&$this,'open'),
                                 array(&$this,'close'),
                                 array(&$this,'get'),
                                 array(&$this,'set'),
                                 array(&$this,'del'),
                                 array(&$this,'gc')
                                ))
    {
      throw new Exception('Handler error.');
    }
    session_start();
  }

  abstract public function open($path, $name);
  abstract public function close();
  abstract public function get($id);
  abstract public function set($id,$data);
  abstract public function del($id);
  abstract public function gc($maxlifetime);
}

/*
CREATE TABLE IF NOT EXISTS `session` (
  `id` varchar(32) NOT NULL,
  `lastupdate` datetime NOT NULL,
  `created` datetime NOT NULL,
  `datum` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `lastupdate` (`lastupdate`),
  KEY `created` (`created`)
) ENGINE=InnoDB COMMENT='session data in mysql instead of /tmp/';
*/ 
class MysqliSession extends Session
{
  public function open($path, $name) 
  {
    return(TRUE);
  }
  
  public function close() 
  {
    $this->gc($this->maxlifetime); // -----------------------------------------?? anywhere better then this?
  }
  
  public function get($id)
  {
    $res = $this->db->query("SELECT `datum` FROM session WHERE id = '$id' AND (UNIX_TIMESTAMP(lastupdate) - UNIX_TIMESTAMP()) < '$this->maxlifetime';"); 
    return($res->num_rows == 1 ? $res->fetch_object()->datum : ' ');
  }
 
  public function set($id, $data) 
  {
    $iAffected = 0;
    $sQuery = "INSERT INTO session (id,datum,created,lastupdate) VALUES (?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE datum=?,lastupdate=NOW();";
    if($oStmt = $this->db->prepare($sQuery))
    {
      $oStmt->bind_param("sss", $id, $data, $data);
      $oStmt->execute();
      $iAffected = $oStmt->affected_rows;
      $oStmt->close();
    }
    return($iAffected === 1 ? TRUE : FALSE);
  }
 
  public function del($id) 
  {
    $this->db->query("DELETE FROM session WHERE id = '$id';");
    return($this->db->affected_rows === 1 ? TRUE : FALSE);
  }

  public function gc($maxlifetime) 
  {
    $this->db->query("DELETE FROM session WHERE (UNIX_TIMESTAMP(lastupdate) - UNIX_TIMESTAMP()) > '".$maxlifetime."';");
    return($this->db->affected_rows);
  }

  public function __destruct() 
  {
    session_write_close();
  }
}
?>
