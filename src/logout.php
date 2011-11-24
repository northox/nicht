<?php
if ($nicht->env->isAuthenticated())
{
  $nicht->logout();
}
$nicht->redirect(NICHT_PAGE_DEFAULT);
?>
