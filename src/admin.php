<?php
require(NICHT_PATH_LIB.'StupidPass.class.php');
require(NICHT_PATH_ROOT.'MysqliNichtAuthPbkdf2.class.php');

if(submitted_new_user_form())
  $tpl->assign('messages', handle_form_submission());

$tpl->assign('title', 'Administration');

  function submitted_new_user_form(){
    return isset($_POST['username']) && isset($_POST['password']);
  }

  function handle_form_submission(){
    $messages = array();
    if(!password_confirmation_matches())
      $messages[] = 'Passwords does not match.';
    else
      $messages = create_new_user();

    return $messages;
  }
    
    function password_confirmation_matches(){
      return $_POST['password'] == $_POST['password_confirmation'];
    }

    function create_new_user(){
      $u = new User();
      $u->setUsername($_POST['username']);
      $u->setPassword($_POST['password']);
      $u->setAdmin(isset($_POST['role']));
      $u->save();
      return $u->getMessages();
    }

  class User {
    private $username;
    private $password;
    private $role;
    private $messages;
    private $salt;
    private $db;

    public function __construct(){
      $this->db = Singleton::getInstance()->getDb();
      $this->role = '';
      $this->messages = array();
    }

    public function setUsername($username){
      if(strlen($username) >= 4)
        $this->username = $username;
      else
        array_push($this->messages, 'Username is too short');
    }

    public function setPassword($password){
      if($this->validatesAgainstStupidPass($password)) {
        $this->salt = substr(md5(uniqid()), 0, 8);
        $this->password = base64_encode(pbkdf2($password, $this->salt));
      }
    }

    public function setAdmin($admin){
      if($admin)
        $this->role = 'administrator';
    }

    public function getMessages(){
      return $this->messages;
    }

    public function save(){
      if(empty($this->messages)){
        $this->insert();
        array_push($this->messages, 'User was successfully created');
      }
      return true;
    }
    
    private function validatesAgainstStupidPass($password){
      $stupidPass = new StupidPass();
      if(!$stupidPass->validate($password)) {
        foreach($stupidPass->get_errors() as $e)
          array_push($this->messages, $e);
        return false;
      }
      return true;
    }

    private function insert(){
      $sql = 'INSERT INTO `user` VALUES(NULL, ?, ?, ?, ?)';
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('ssss', $this->username, $this->password, $this->salt, $this->role);
      $stmt->execute();
      $stmt->close();
    }
  }
