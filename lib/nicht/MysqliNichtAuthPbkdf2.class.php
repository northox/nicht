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

class MysqliNichtAuthPbkdf2 extends NichtAuth
{
  protected $db;

  public function __construct()
  {
    $this->db = Singleton::getInstance()->getDb();
  }

  protected function realLogin ($user, $pass)
  {
    $query = 'SELECT '.MYSQLI_NICHT_AUTH_COL_PASS.', '.MYSQLI_NICHT_AUTH_COL_SALT.'
              FROM '.MYSQLI_NICHT_AUTH_TABLE.'
              WHERE '.MYSQLI_NICHT_AUTH_COL_USER.'=?;';
    if ($stmt = $this->db->prepare($query))
    {
      $stmt->bind_param('s', $user); // user is case insensitive
      $stmt->bind_result($dbhash, $dbsalt);
      $stmt->execute();
      $stmt->fetch();
      if(empty($dbhash))  
        throw new Exception ('Cant find this username', -1);
      if(base64_encode(pbkdf2($pass, $dbsalt)) != $dbhash)
        throw new Exception ('Bad Password', -2);
      return;
    }
    throw new Exception ('Something went terribly wrong' -10);
  }

  protected function realLogout ()
  {
  }
}

/** PBKDF2 Implementation (described in RFC 2898)
 *  author: http://www.itnewb.com/v/Encrypting-Passwords-with-PHP-for-Storage-Using-the-RSA-PBKDF2-Standard
 *
 *	@param   string  p   password
 *	@param   string  s   salt
 *	@param   int     c   iteration count (use 1000 or higher)
 *	@param   int     kl  derived key length
 *	@param   string  a   hash algorithm
 *
 *	@return  string  derived key
*/
function pbkdf2( $p, $s, $c = 20000, $kl = 32, $a = 'sha256' )
{
	$hl = strlen(hash($a, null, true));	# Hash length
	$kb = ceil($kl / $hl);				      # Key blocks to compute
	$dk = '';							              # Derived key
	# Create key
	for ( $block = 1; $block <= $kb; $block ++ ) {
		# Initial hash for this block
		$ib = $h = hash_hmac($a, $s . pack('N', $block), $p, true);
		# Perform block iterations
		for ( $i = 1; $i < $c; $i ++ ) 
			# XOR each iterate
			$ib ^= ($h = hash_hmac($a, $h, $p, true));
		$dk .= $ib; # Append iterated block
	}
	# Return derived key of correct length
	return substr($dk, 0, $kl);
}
?>
