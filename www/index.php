<?php
/* Load conf */
include('../conf.php');
include(NICHT_CLASSPATH);
include(NICHT_TPLCLASSPATH);
include(NICHT_PATH_MISC.'utils.php');

if(NICHT_HTTPS == true && $_SERVER['HTTPS'] != "on"){
  header('Location: '.HTTPS_URL.$_SERVER['REQUEST_URI']);
  exit();
}

/* Setup session */
session_set_cookie_params(0, NICHT_COOKIE_PATH, NICHT_COOKIE_DOMAIN, NICHT_HTTPS, true);
session_name(NICHT_COOKIE_NAME);
session_start();

$tplClass = NICHT_TPLCLASSNAME;
$tpl = new $tplClass;
$tpl->template_dir = NICHT_PATH_TPL;
$db = Singleton::getInstance()->getDb();

if(isset($_GET[NICHT_REQUESTERID]))
  $nicht = new Nicht($tpl, $_GET[NICHT_REQUESTERID]);
else
  $nicht = new Nicht($tpl);

while($nicht->hasMore())
  include($nicht->loadSrc());

$nicht->display();
?>