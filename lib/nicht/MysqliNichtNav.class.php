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
 CREATE TABLE `Nicht_Nav` (
`name` VARCHAR( 255 ) NOT NULL COMMENT 'page name',
`id` INT NOT NULL COMMENT 'page id',
`members` TEXT NOT NULL COMMENT 'who can access',
`filename` VARCHAR( 255 ) NOT NULL COMMENT 'filesystem filename',
PRIMARY KEY ( `name` ) ,
UNIQUE (
`id`
)
) ENGINE = INNODB COMMENT = 'Navigation system: list of webpage, access name/id and members' 
*/

define('MYSQLI_NICHT_NAV_TABLE', 'Nicht_Nav');
define('MYSQLI_NICHT_NAV_COL_FILENAME', 'filename');
define('MYSQLI_NICHT_NAV_COL_NAME', 'name');
define('MYSQLI_NICHT_NAV_COL_ID', 'id');
define('MYSQLI_NICHT_NAV_COL_MEMBERS', 'members');

class MysqliNichtNav extends NichtNav
{
  protected $db;
  protected $sLoadedRequest = NULL;
  protected $sFileName = NULL;
  protected $sName = NULL;
  protected $iId = NULL;
  protected $aMembers = array();
  
  public function __construct()
  {
    $this->db = Singleton::getInstance()->getDb();;
  }

  protected function getRequestInfo ($sRequest)
  {
    // return if the function has already run for this request
    if($this->sLoadedRequest == $sRequest) return FALSE;
    $this->sLoadedRequest = $sRequest;

    $bIsNumeric = is_numeric($sRequest);
    $sQuery = 'SELECT `'.MYSQLI_NICHT_NAV_COL_FILENAME.'`, `'.MYSQLI_NICHT_NAV_COL_NAME.'`, `'.MYSQLI_NICHT_NAV_COL_ID.'`, `'.MYSQLI_NICHT_NAV_COL_MEMBERS.'` FROM `'.MYSQLI_NICHT_NAV_TABLE.'` WHERE ';
    if($bIsNumeric)
    {
      $sQuery .= '`'.MYSQLI_NICHT_NAV_COL_ID.'`=?;';
    } else {
      $sQuery .= '`'.MYSQLI_NICHT_NAV_COL_NAME.'`=?;';
    }
    if ($oStmt = $this->db->prepare($sQuery))
    {
      if($bIsNumeric)
      {
        $oStmt->bind_param("i", $sRequest);
      } else {
        $oStmt->bind_param("s", $sRequest);
      }
      $oStmt->execute();
      $oStmt->bind_result($this->sFileName, $this->sName, $sId, $sMembers);
      $oStmt->fetch();      
      $oStmt->close();
      $this->iId = intval($sId);

      if(empty($sMembers))
      {
        $this->aMembers = array();
      } else {
        $a = explode('|', $sMembers);
        // first and last are always empty
        array_pop($a);
        array_shift($a);
        $this->aMembers = $a;
      }
    }
  }
  
  public function isRestricted ($sRequest)
  {
    $this->getRequestInfo($sRequest);
    return(!empty($this->aMembers));
  }

  public function isAvailable ($sRequest)
  {
    $this->getRequestInfo($sRequest);
    return(!is_null($this->sFileName));
  }

  public function getSrcPath ($sRequest)
  {
    $this->getRequestInfo($sRequest);
    return(NICHT_PATH_SRC.$this->sFileName.NICHT_SRC_SUFFIX);
  }
  
  public function getTplPath ($sRequest)
  {
    $this->getRequestInfo($sRequest);
    return(NICHT_PATH_TPL.$this->sFileName.NICHT_TPL_SUFFIX);
  }
  
  public function getMembers ($sRequest)
  {
    $this->getRequestInfo($sRequest);
    return($this->aMembers);
  }
}
?>
