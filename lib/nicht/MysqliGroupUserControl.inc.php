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

require (NICHT_PATH_ROOT.'MysqliNicht.conf.php');

function getUserId($username)
{
  $db = Singleton::getInstance()->getDb();
  $sql = "SELECT ".MYSQLI_NICHT_AUTH_COL_ID." FROM ".MYSQLI_NICHT_AUTH_TABLE." WHERE ".MYSQLI_NICHT_AUTH_COL_USER." = ?;";
  if($stmt = $db->prepare($sql))
  {
    $stmt->bind_param('s', strtolower($username)); // username is case insensitive
    $stmt->execute();
    $stmt->bind_result($userId);
    $stmt->fetch();
    $stmt->close();
    
  }
  if (is_numeric($userId)) return $userId;
  throw new Exception('');
}

function getUserIdMemberOf($userId)
{
  $db = Singleton::getInstance()->getDb();
  $aReturn = array();
  $sql = "SELECT ".MYSQLI_NICHT_ACL_COL_GROUPNAME." FROM Nicht_Acl WHERE ".MYSQLI_NICHT_ACL_COL_MEMBERS." LIKE '%|".$userId."|%';";
  $oResult = $db->query($sql);
  while($a = $oResult->fetch_row())
    $aReturn[] = $a[0];
  $oResult->close();
  return($aReturn);
}

function addUserIdToGroup($userId, $groups, $bAdd = true)
{
  $db = Singleton::getInstance()->getDb();
  $bNoError = TRUE;
  $aTodo = array();
  if($stmt = $db->prepare("SELECT members FROM Nicht_Acl WHERE name = ?;"))
  {
    $stmt->bind_param('s', $sGroup);
    foreach($groups as $sGroup)
    {
      $stmt->execute();
      $stmt->bind_result($sMembers);
      if($stmt->fetch())
      {
        $aMembers = explode('|', $sMembers);
        array_pop($aMembers); // remove first |
        array_shift($aMembers); // remove last |
        if(($bAdd === TRUE && !in_array($userId, $aMembers)) || ($bAdd === FALSE && in_array($userId, $aMembers)))
        {
          $bAdd ? $aMembers[] = $userId : $aMembers = array_diff($aMembers, array($userId));
          $sMembers = '|'.implode('|', $aMembers).'|'; // should look like this: |x|x|x|newUserId|
          $aTodo[] = array($sGroup, $sMembers);
        }
      }
    }
    $stmt->close();
  } else {
    $bNoError = FALSE;
  }
  // bulk update
  if($stmt = $db->prepare("UPDATE Nicht_Acl SET members = ? WHERE name = ?;"))
  {
    $stmt->bind_param('ss', $sM , $sG);
    foreach($aTodo as $a)
    {
      $sM = $a[1];
      $sG = $a[0];
      $stmt->execute();
    }
    $stmt->close();
  } else {
    $bNoError = FALSE;
  }
  return($bNoError);
}

function delUserIdFromGroup($userId, $groups)
{
  return(addUserIdToGroup($userId, $groups, FALSE));
}

/*
ACID transaction of group and user
*/
function setGroup($userId, $newGroups)
{
  $db = Singleton::getInstance()->getDb();
  $db->autocommit(FALSE);
  $error = FALSE;

  $oldGroups = getUserIdMemberOf($userId);
  $toAdd = array_diff($newGroups, $oldGroups);
  $toDel = array_diff($oldGroups, $newGroups);
  if(count($toAdd) > 0)
    if(!addUserIdToGroup($userId, $toAdd))
      $error = TRUE;
  if(count($toDel) > 0)
    if(!delUserIdFromGroup($userId, $toDel))
      $error = TRUE;
  if($error == TRUE)
    $db->rollback();

  $db->autocommit(TRUE);
  return($error);
}
?>
