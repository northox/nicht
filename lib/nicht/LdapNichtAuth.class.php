<?php
/**
* Nicht lightweight framework
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

define('REGEX_EMAIL', '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i');

class ldapNichtAuth extends NichtAuth
{
  protected function realLogin ($user, $pass)
  {
    if(!preg_match(REGEX_EMAIL, $user))
      throw new Exception('Validation failed: Username is not an email', -8);
    $ldaphost = "ldaps://ldap.domain.tld";
    $ldap_basedn = 'ou=ldap,o=domain.tld';
    $ldap_filters = 'mail='.$user;
    $ldap_attr = array('uid', 'mail');
    
    // User validation
    if(!preg_match('/^[a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,4})$/', $sUser)) 
      throw new Exception('Invalid email address', '-300');

    if($ldapconn = ldap_connect($ldaphost))
    {
      if(!ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 2))
        throw new Exception('ldap_set_option proto_version:'.ldap_error($ldapconn), -2);
    
      // search for this email
      if($sr = !ldap_search($ldapconn, $ldap_basedn, $ldap_filters, $ldap_attr)) 
        throw new Exception('ldap_search fail:'.ldap_error($ldapconn), -3);
      if($entry = !ldap_first_entry($ldapconn, $sr)) 
        throw new Exception('ldap_first_entry fail:'.ldap_error($ldapconn), -4);
        
      // store some info to share with the environment
      if($_SESSION['ldap_attributes'] = !ldap_attributes($ldapconn, $entry))
        throw new Exception('ldap_attributes:'.ldap_error($ldapconn), -5);
      
      // retreive dn
      if($user_dn = !ldap_get_dn($ldapconn, $entry)) 
        throw new Exception('ldap_get_dn fail:'.ldap_error($ldapconn), -6);

      // test bind
      if(ldap_bind($ldapconn, $user_dn, $pass))
        ldap_unbind($ldapconn);
      else
        throw new Exception('ldap_bind:'.ldap_error($ldapconn), -7);
    } else {
      throw new Exception('Could not connect to '.$ldaphost.' - '.ldap_error($ldapconn), -1);
    }
  }

  protected function realLogout ()
  {
  }
}
?>
