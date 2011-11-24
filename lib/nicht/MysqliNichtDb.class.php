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
*
CREATE TABLE `Nicht_bruteforcer` (
  `count` tinyint(2) NOT NULL COMMENT 'bruteforce attempt',
  `user` varchar(320) NOT NULL COMMENT 'username bruteforced. Can be a simple username to an email address: 64char (local part) + 1char (@) + 255char (domain)',
  `ip` varchar(15) NOT NULL COMMENT 'ip address of the bruteforce source: 192.168.222.222',
  KEY `user` (`user`),
  KEY `ip` (`ip`)
) ENGINE=INNODB DEFAULT CHARSET=ascii COMMENT='Anti bruteforce';
*/

define('MYSQLI_NICHT_BRUTEFORCE_TABLE', 'Nicht_AntiBruteForce');
define('MYSQLI_NICHT_BRUTEFORCE_COL_COUNT', 'count');
define('MYSQLI_NICHT_BRUTEFORCE_COL_IP', 'ip');
define('MYSQLI_NICHT_BRUTEFORCE_COL_USER', 'user');
define('MYSQLI_NICHT_BRUTEFORCE_COL_TIME', 'utime');

class MysqliNichtDb extends NichtDb
{
  protected $db;
  
  public function __construct()
  {
    $this->db = Singleton::getInstance()->getDb();;
  }

  public function getBruteforceCount($ip, $user)
  {
    $iCount = 0;
    $query = 'SELECT '.MYSQLI_NICHT_BRUTEFORCE_COL_COUNT.'
              FROM '.MYSQLI_NICHT_BRUTEFORCE_TABLE.'
              WHERE '.MYSQLI_NICHT_BRUTEFORCE_COL_IP.'=?
              AND '.MYSQLI_NICHT_BRUTEFORCE_COL_USER.'=?';
    if ($stmt = $this->db->prepare($query))
    {
      $stmt->bind_param("ss", $ip, $user);
      $stmt->execute();
      $stmt->bind_result($iCount);
      $stmt->fetch();
      $stmt->close();
    } 
    return($iCount);   
  }

  public function increaseBruteForceCount($ip, $user)
  {
    $query = 'INSERT INTO '.MYSQLI_NICHT_BRUTEFORCE_TABLE.' ('.MYSQLI_NICHT_BRUTEFORCE_COL_TIME.', '.MYSQLI_NICHT_BRUTEFORCE_COL_COUNT.', '.MYSQLI_NICHT_BRUTEFORCE_COL_USER.', '.MYSQLI_NICHT_BRUTEFORCE_COL_IP.') 
              VALUES (CURRENT_TIMESTAMP,0,?,?)
              ON DUPLICATE KEY UPDATE '.MYSQLI_NICHT_BRUTEFORCE_COL_COUNT.' = '.MYSQLI_NICHT_BRUTEFORCE_COL_COUNT.'+1, '.MYSQLI_NICHT_BRUTEFORCE_COL_TIME.'=CURRENT_TIMESTAMP, '.MYSQLI_NICHT_BRUTEFORCE_COL_IP.'=? AND '.MYSQLI_NICHT_BRUTEFORCE_COL_USER.'=?;';
    if ($stmt = $this->db->prepare($query))
    {
      $stmt->bind_param("ssss", $ip, $user, $ip, $user);
      $stmt->execute();
      $iAffected = $stmt->affected_rows;
      $stmt->close();
    }
  }

  public function deleteBruteForce($ip, $user)
  {
    $query = 'DELETE FROM '.MYSQLI_NICHT_BRUTEFORCE_TABLE.' 
              WHERE '.MYSQLI_NICHT_BRUTEFORCE_COL_IP.'=?
              AND '.MYSQLI_NICHT_BRUTEFORCE_COL_USER.'=?';
    if ($stmt = $this->db->prepare($query))
    {
      $stmt->bind_param("ss", $ip, $user);
      $stmt->execute();
      $stmt->close();
    }
  }
}
?>
