<?php
class DebugNichtAcl extends NichtAcl
{
  public function isAuthorized ($members, $user)
  {
    return(TRUE);
  }
}
?>
