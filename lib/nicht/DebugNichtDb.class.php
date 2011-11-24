<?php
class DebugNichtDb extends NichtDb
{
  public function getBruteforceCount($ip, $user)
  {
    return(0);
  }

  public function increaseBruteForceCount($ip, $user)
  {
  }

  public function deleteBruteForce($ip, $user)
  {
  }
}
?>
