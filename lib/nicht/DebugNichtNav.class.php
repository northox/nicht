<?php
class DebugNichtNav extends NichtNav
{
  public function isRestricted ($resource)
  {
    return(FALSE);
  }

  public function isAvailable ($resource)
  {
    return(TRUE);
  }

  public function getSrcPath ($resource)
  {
    return(NICHT_PATH_SRC.$resource.NICHT_SRC_SUFFIX);
  }
  
  public function getTplPath ($resource)
  {
    return(NICHT_PATH_TPL.$resource.NICHT_TPL_SUFFIX);
  }
}
?>