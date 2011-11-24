<?php
define('POST_USER_VAR', 'user');
define('POST_PASS_VAR', 'password');

/* Nicht AntiBruteforce: Delete expired bruteforce attempt */
$utime = mktime() - (NICHT_BRUTEFORCE_IGNORESEC);
$db->query("DELETE FROM Nicht_AntiBruteForce WHERE utime < $utime;");

if ($nicht->env->isAuthenticated())
{
  $nicht->redirect(NICHT_PAGE_DEFAULTAUTH);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $bIsAuthenticated = $nicht->login(@$_POST[POST_USER_VAR], @$_POST[POST_PASS_VAR]);
  if ($bIsAuthenticated && !$nicht->isRedirected())
  {
    // To avoid rePOSTing when the user hit 'page back' after a login
    header('Location: ?'.NICHT_REQUESTERID.'='.NICHT_PAGE_DEFAULTAUTH);
    exit();
  } elseif ($bIsAuthenticated) {
    // urlencode must be used to avoid http header injection
    $url = urlencode($_SERVER["QUERY_STRING"]);
    $url = str_replace('%3D', '=', $url);
    $url = str_replace('%26', '&', $url);
    header('Location: ?'.$url);
    exit();
  }
}

$tpl->assign('title', 'Authentication');
?>
