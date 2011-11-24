<?php
/**
* Nicht lightweight framework
*
* @author Mantor Organization
* @version 1.4
* @package nicht
*
* Copyright (c) Mantor Organization, 2003-2011
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*    * Redistributions of source code must retain the above copyright
*      notice, this list of conditions and the following disclaimer.
*    * Redistributions in binary form must reproduce the above copyright
*      notice, this list of conditions and the following disclaimer in the
*      documentation and/or other materials provided with the distribution.
*    * Neither the name of  Mantor Organization nor the
*      names of its contributors may be used to endorse or promote products
*      derived from this software without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
* ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL Mantor Organization BE LIABLE FOR ANY
* DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
* ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
* SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

if(!defined('NICHT_DEBUG')) define('NICHT_DEBUG', false);

/**
* Nicht class
*
* Composition object model
*/
class Nicht
{
  /**
  * Nicht Environment object
  */
  public $env;

  /**
  * Nicht Access Control List object
  */
  protected $acl;

  /**
  * Nicht Database object
  */
  protected $db;

  /**
  * Nicht Navigation object
  */
  protected $nav;

  /**
  * Template object
  */
  protected $tpl;

  /**
  * Resource Queue: Used to identify avery resource to process before displaying a template
  */
  protected $resQueue = array();

  /**
  * Current user's requested resource
  */
  protected $resource;

  /**
  * Latest resource add to the queue
  */
  protected $lastRes;

  /**
  * Internal indicator to track if a Nicht redirection is taking place
  */
  protected $redirected = false;

  /**
  * Constructor initialize and jail the request.
  * Note that the env object is created and then retreive from session and copied over if
  * found in session.
  *
  * @param $tpl Template Object
  * @param $resource ResourceID requested
  */
  public function __construct ($tpl, $resource = null)
  {
    $this->env = $this->factory('env');
    if(isset($_SESSION['Nicht']['nichtEnv']))
    {
      $this->env = unserialize($_SESSION['Nicht']['nichtEnv']);
      if(NICHT_DEBUG) error_log('DEBUG: nicht: starting nicht, extracting env');
    } else {
      if(NICHT_DEBUG) error_log('DEBUG: nicht: starting nicht, env not found');
    }
    $this->acl = $this->factory('acl');
    $this->db  = $this->factory('db');
    $this->nav = $this->factory('nav');
    $this->tpl = $tpl;
    $this->resource  = $resource;

    $this->jail();
   }

  /**
  * Factory method: load requested object
  *
  * @param $type Object type
  * @return Object type asked
  */
  protected function factory ($type)
  {
    $class = constant('NICHT_MODULE_'.strtoupper($type));
    include(NICHT_PATH_ROOT.$class.'.class.php');
    return(new $class);
  }

  /**
  * Enforce navigation logic.
  * Override the request accordingly if the resource is unselected/unavailable or user is
  * unauthenticated/unauthorize.
  */
  protected function jail ()
  {
    if(!$this->isSelected())
    {
      if(NICHT_DEBUG) $debug[] = 'no request';
      $this->override(NICHT_PAGE_DEFAULT); // send default page is unselected
    } else {
      if(!$this->isAvailable())
      {
        if(NICHT_DEBUG) $debug[] = 'unavailable';
        $this->override(NICHT_PAGE_UNAVAILABLE); // send 404 if unavailable
      } else {
        if(NICHT_DEBUG) $debug[] = 'available';
        $this->redirect($this->resource);
        if(!$this->isAuthenticated())
        {
          if(NICHT_DEBUG) $debug[] = 'unauthenticated';
          if($this->isRestricted())
          {
            if(NICHT_DEBUG) $debug[] = 'restricted';
            $this->env->addMsg(NICHT_MSG_LOGINNEEDED);
            $this->override(NICHT_PAGE_LOGIN); // send login if authentication is needed
          }
        } else {
          if(NICHT_DEBUG) $debug[] = 'authenticated';
          if($this->isRestricted())
          {
            if(NICHT_DEBUG) $debug[] = 'restricted';
            if (!$this->isAuthorized())
            {
              if(NICHT_DEBUG) $debug[] = 'unauthorized';
              $this->override(NICHT_PAGE_UNAUTHORIZE); // send 403 if unauthorized
            } else {
              if(NICHT_DEBUG) $debug[] = 'authorized';
            }
          }
        }
      }
    }
    if(NICHT_DEBUG) error_log("DEBUG: nicht: jail: ".implode(', ', $debug));
  }

  /**
  * Override Resource to be loaded.
  * This function will override previously queued resource and force direct redirection.
  *
  * @param $resource resource id (number or name)
  */
  protected function override ($resource)
  {
    $this->resQueue = array($resource);
    if(NICHT_DEBUG) error_log("DEBUG: nicht: overriding with=$resource");
  }

  /**
  * Check if a resource request has been done.
  *
  * @return True if the resource is not null
  */
  protected function isSelected ()
  {
    $r = is_null($this->resource) ? false : true;
    return ($r);
  }

  /**
  * Check if asked page really exist.
  * NichtNav implementation validate if requested page is available. See 
  * NichtNav for more details.
  *
  * @return True if resource is available
  */
  protected function isAvailable ()
  {
    return ($this->nav->isAvailable($this->resource));
  }

  /**
  * Check if asked page has restricted access.
  * NichtAcl implementation evaluate if request is restricted. See NichtNav 
  * for more details.
  *
  * @return true if resource has a restricted access
  */
  protected function isRestricted ()
  {
    return ($this->nav->isRestricted($this->resource));
  }

  /**
  * Check is user is authenticated.
  * NichtEnv implementation validate if user is authenticated. See NichtNav
  * for more details.
  *
  * @return True if user is authenticated
  */
  protected function isAuthenticated ()
  {
    return ($this->env->isAuthenticated());
  }

  /**
  * Check if user is authorized to access the requested page.
  * Indicate if the user is authorized to access the requested resource. By 
  * default, the member list will be taken from the current request and the 
  * user from the current request.
  *
  * @param $members optional members list to verify user against
  * @param $user optional user to verify
  * @return True if user is authorized to access resource
  */
  public function isAuthorized ($members = NULL, $user = NULL)
  {
   if(is_null($user)) $user = $this->env->getUser();
   if(is_null($members)) $members = $this->nav->getMembers($this->resource);
   if($this->isAuthenticated())
     return ($this->acl->isAuthorized($members, $user));
   return(false);
  }

  /**
  * Execute procedure to log in the user.
  * The process start by redirecting bruteforcer. The login system response is
  * then used to send confirmation to the user. If authentication is 
  * sucessfull, the bruteforce count is reset and the user environment login 
  * process get executed.
  *
  * @param $user user to verify
  * @param $sPass user password
  * @param $aLoginMsg login messages
  * @return True if login is sucessfully completed
  */
  public function login ($user, $sPass)
  {
    if ($this->db->catchBruteForce($user))
    {
      if(NICHT_DEBUG) error_log("DEBUG: nicht: brute force detected, redirecting to default");
      $this->env->addMsg(NICHT_MSG_LOGINLIMIT);
      $this->redirect(NICHT_NAV_DEFAULT_PAGE);
      return(false);
    }

    $iExcepCode = NULL;
    $sExcepMessage = NULL;
    $bReturn = false;
    try {
      $sMsg = NICHT_MSG_LOGINSUCCESS;
      $auth = $this->factory('auth');
      $auth->login($user, $sPass);
      $this->env->flushMsg();
      $this->db->resetBruteForce();
      $this->env->login($user);
      session_regenerate_id(); // avoid session fixation attack
      $this->env->set($auth->get());
      unset($auth);
      $this->jail();
      $bReturn = true;
    } catch (Exception $e) {
      $iExcepCode = $e->getCode();
      $sExcepMessage = $e->getMessage();
      switch (true)
      {
        /* -001 to -099 = Failed login */
        case ($iExcepCode > -100):
          $sMsg = NICHT_MSG_LOGINFAILED;
          break;
        /* -100 to -199 = Suspended */
        case ($iExcepCode > -200):
          $sMsg = NICHT_MSG_SUSPENDED;
          break;
        /* else = Critical error */
        default:
          $sMsg = NICHT_MSG_ERROR;
          break;
      }
    }
    
    if(NICHT_DEBUG)
    {
      $debug = ($iExcepCode == NULL) ? 'succeed' : 'failed';
      error_log("DEBUG: nicht: login: $debug code=$iExcepCode, msg=$sExcepMessage");
      $this->env->addMsg("DEBUG: code=$iExcepCode, msg=$sExcepMessage");
    }
    
    $this->env->addMsg($sMsg);
    return($bReturn);
  }

  /**
  * Execute log out procedure.
  * Set environment authentication flag to unauthenticated, display
  * logout message and redirect browser using HTTP 302 header.
  *
  * @param $redirect optional resource to be redirected to
  */
  public function logout ($redirect = NICHT_PAGE_DEFAULT)
  {
    if(NICHT_DEBUG) error_log('DEBUG: nicht: loggout');
    $auth = $this->factory('auth');
    $auth->logout();
    $this->env->logout();
    $this->env->addMsg(NICHT_MSG_LOGOUTSUCCESS);
    header('Location: ./', 302);
    exit();
  }

  /**
  * Redirect user request.
  * The redirection is done by adding the resource on the resource queue.
  *
  * @param $resource resource to be redirected at
  */
  public function redirect ($resource)
  {
    $this->redirected = true;
    $this->resQueue[] = $resource;
    if(NICHT_DEBUG) error_log("DEBUG: nicht: redirecting with=$resource");
  }

  /**
  * Check if a Nicht redirection is taking place
  *
  * @return True if a redirection is active
  */
  public function isRedirected ()
  {
   return($this->redirected);
  }

  /**
  * Check if the resource queue is empty.
  * Indicate if more resource need to be loaded from the queue before displaying
  * a template.
  *
  * @return True if resource queue is empty
  */
  public function hasMore ()
  { 
   if (count($this->resQueue) == 0) return (false);
   return (true);
  }

  /**
  * Get resource path
  * Shift out a resource from the resource queue and mark it has being the 
  * latest loaded. The previous will be used if no more resource are on the
  * queue and no redirection/override arise. The php source path of this
  * resource is then returned.
  *
  * @return Path of resource to be loaded
  */
  public function loadSrc ()
  {
    $this->lastRes = array_shift($this->resQueue);
    if(NICHT_DEBUG) error_log("DEBUG: nicht: loading=$this->lastRes");
    return($this->nav->getSrcPath($this->lastRes));
  }

  /**
  * Display the template of the latest queued resource.
  * Assign environment to smarty, get the path and display the template of 
  * the latest resource loaded and flush the environment message queue.
  */
  public function display ()
  {
    if(NICHT_DEBUG) error_log("DEBUG: nicht: display");
    $this->tpl->assign('nichtEnv', $this->env);
    $this->tpl->display($this->nav->getTplPath($this->lastRes));
    $this->env->flushMsg();
  }
  
  /**
  * Destructor save the environment with the session.
  * The environment is serialized and saved via the session handler.
  */
  public function __destruct ()
  {
    $_SESSION['Nicht']['nichtEnv'] = serialize($this->env);
  }
}

/**
* Nicht Authentication class.
* This abstract class has to be extented and implement the authentication
* mechanism.
*/
abstract class NichtAuth
{
  /*
  * Used to exchange information with other modules.
  */
  protected $var;

  /**
  * Execute login procedure
  * This method is unused but implemented for future feature.
  *
  * @param $sInputUser Username supplied
  * @param $sInputPass Password supplied
  */
  final public function login ($sInputUser, $sInputPass)
  {
    $this->realLogin($sInputUser, $sInputPass);
  }

  /**
  * Execute logout procedure
  * This method is unused but implemented for future feature.
  */
  final public function Logout ()
  {
    $this->realLogout();
  }

  /**
  * Get variable
  * Used to exchange information with other modules.
  */
  final public function get ()
  {
    return($this->var);
  }

  /**
  * Execute extended login procedure
  * This method need to be implemented and include anything required to complete 
  * the login procedure on the foreign system. Any error should throw an exception. 
  * These will get caught, log and displayed to the user.
  *
  * @param $sInputUser Username supplied
  * @param $sInputPass Password supplied
  * @throw Exception errormsg, errorid
  */
  abstract protected function realLogin ($sInputUser, $sInputPass);

  /**
  * Execute extended logout procedure
  * This method need to be implemented and include anything required to complete 
  * the logout procedure on the foreign system. Any error should throw an exception. 
  * These will get caught, log and displayed to the user.
  *
  * @throw Exception errormsg, errorid
  */
  abstract protected function realLogout ();
}

/**
* Nicht Environment abstract class.
* This abstract class implement the messaging system and the authentication flags.
* It has to be extented to implement the session user environment.
*/
abstract class NichtEnv
{
  /*
  * Used to exchange information with other modules.
  */
  protected $var;

  /**
  * Message queue
  */
  private $msgs = array();
  
  /**
  * Authentication flag
  */
  private $authenticated = false;

  /**
  * Authentication Username
  */
  protected $user = NULL;

  /**
  * Add message to message queue.
  * This function will add a message to the message array or can trunk the array 
  * and add a new message.
  *
  * @param $sMsg Message
  * @param $bAppend Append message to queue default = true
  */ 
  final public function addMsg ($sMsg, $bAppend = true)
  {
    if($bAppend == true)
    {
      $this->msgs[] = $sMsg;
      if(NICHT_DEBUG) error_log("DEBUG: nicht: Adding message to queue=$sMsg");
    } else {
      $this->msgs = array($sMsg);
      if(NICHT_DEBUG) error_log("DEBUG: nicht: Overriding message queue=$sMsg");
    }
  }

  /**
  * Flush Message queue.
  */ 
  final public function flushMsg ()
  {
    $this->msgs = array();
  }

  /**
  * Get the message queue.
  * 
  * @return Message queue
  */ 
  final public function getMsg ()
  {
    return($this->msgs);
  }

  /**
  * Get authenticated username.
  * This method will return NULL if user is unauthenticated.
  *
  * @return Username
  */ 
  final public function getUser ()
  {
    return($this->user);
  }

  /**
  * Check is user is authenticated.
  *
  * @return True if authenticated
  */ 
  final public function isAuthenticated ()
  {
    return($this->authenticated);
  }

  /**
  * Set info from others modules.
  */
  final public function set ($var)
  {
    $this->var = $var;
  }

  /**
  * Execute environment login procedure.
  * Set username and authentication flag to yes then launch extended
  * environment login procedure.
  *
  * @param user username
  */ 
  final public function login ($user)
  {
    $this->authenticated = true;
    $this->user = $user;
    $this->realLogin();
  }
  
  /**
  * Execute environment logout procedure.
  * Unset authentication flags and launch extended environment logout 
  * procedure.
  */ 
  final public function logout ()
  {
    $this->authenticated = false;
    $this->user = NULL;
    $this->realLogout();
  }

  /**
  * Execute extended login procedure.
  * This can include anything required to complete the login procedure
  * from the environment perspective. 
  */ 
  abstract protected function realLogin();

  /**
  * Execute extended logout procedure.
  * This can include anything required to complete the logout procedure
  * from the environment perspective. 
  */  
  abstract protected function realLogout();
}

/**
* Nicht Database abstract class.
* This abstract class has to be extented to implement the database
* interaction needed for Nicht. At the moment, it is used to store, detect 
* and prevent brute force login attempts.
*/
abstract class NichtDb
{
  /**
  * Username of the current attempt
  */  
  protected $user;
  
  /**
  * User Ip address of the current attempt
  */  
  protected $ip;
  
  /**
  * Determine if attempt is identified has a bruteforce.
  * The method use the extended getBruteforceCount to evaluate if
  * number of maximum login attempt have been reach and use
  * extended increaseBruteForce method to increment the count.
  *
  * @param $user Username
  * @return True if identified has a bruteforce attack
  */
  final public function catchBruteForce ($user)
  {
    $this->user = $user;
    $this->ip = $_SERVER["REMOTE_ADDR"];
    if ($this->getBruteforceCount($this->ip, $this->user) > NICHT_BRUTEFORCE_LIMIT)
    {
      return(true);
    } else {
      $this->increaseBruteForceCount($this->ip, $this->user);
      return(false);
    }
  }
  
  /**
  * Reset count value.
  * Use the extended deleteBruteForce to reset the count of a sucessfull
  * logon attempt.
  */
  final public function resetBruteForce ()
  {
    $this->deleteBruteForce($this->ip, $this->user);
  }

  /**
  * Return the count.
  * This method need to be implemented to return the brute force count.
  *
  * @param $ip Ip address of the attempt
  * @param $user Username of the attempt
  * @return int Number of failed login attempt
  */
  abstract protected function getBruteforceCount ($ip, $user);

  /**
  * Increase the count.
  * This method need to be implemented to increase the count value.
  *
  * @param $ip Ip address of the attempt
  * @param $user Username of the attempt
  */
  abstract protected function increaseBruteForceCount ($ip, $user);

  /**
  * Delete the count.
  * This method need to be implemented to erase the count value.
  *
  * @param $ip Ip address of the attempt
  * @param $user Username of the attempt
  */
  abstract protected function deleteBruteForce ($ip, $user);
}

/**
* Nicht Navigation abstract class.
* This abstract class has to be extented to implement the navigation
* scheme. This include what resource is available, who is authorised
* and where it is (path).
*/
abstract class NichtNav
{
  /**
  * Check if resource has a restricted access.
  * This method need to be implemented to erase the count value.
  *
  * @param $resource Resource Requested
  * @return True if access is restricted
  */
  abstract public function isRestricted ($resource);
   
  /**
  * Get resource php source path.
  *
  * @param $resource Resource Requested
  * @return string Php source path
  */
  abstract public function getSrcPath ($resource);
   
  /**
  * Get resource template path.
  *
  * @param $resource Resource Requested
  * @return string Templace path
  */ 
  abstract public function getTplPath ($resource);

  /**
  * Check if resource is available.
  *
  * @param $resource ResourceID
  * @return true if user is authorized to access resource
  */
  abstract public function isAvailable ($resource);
}

/**
* Nicht Access Control List abstract class.
* This abstract class has to be extented to implement the ACL system.
*/
abstract class NichtAcl
{
  /**
  * Check if user is authorized to access the requested resource.
  *
  * @param $members Authorized groups
  * @param $user User to be verified
  * @return true if user is authorized to access resource
  */
  abstract public function isAuthorized ($members, $user);
}
?>
