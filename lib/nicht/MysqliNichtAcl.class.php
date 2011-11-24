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

require(NICHT_PATH_ROOT.'MysqliGroupUserControl.inc.php');

class MysqliNichtAcl extends NichtAcl
{
  protected $db;
 
  public function __construct ()
  {
    $this->db = Singleton::getInstance()->getDb();
  }
 
  public function isAuthorized ($group, $id)
  {
    /* Authorized if in Nicht special root group */
    if(in_array(MYSQLI_NICHT_ACL_GROUP_ROOT, $group)) return (TRUE);
    
    /* Authorized if id is in members group */
    $return = FALSE;
    $id = getUserId($id);
    $sql = 'SELECT '.MYSQLI_NICHT_ACL_COL_GROUPNAME.
           ' FROM '.MYSQLI_NICHT_ACL_TABLE.
           ' WHERE '.MYSQLI_NICHT_ACL_COL_GROUPNAME.' = ? '.
           ' AND '.MYSQLI_NICHT_ACL_COL_MEMBERS." LIKE '%|$id|%'";
    if($stmt = $this->db->prepare($sql))
    {
      $stmt->bind_param('s', $groupParam);
      for ($i = 0, $j = count($group); $i < $j && $return == FALSE; $i++)
      {
        $groupParam = $group[$i];
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows == 1) $return = TRUE;
        $stmt->close();
      }
    }
    return($return);
  }
}
?>
