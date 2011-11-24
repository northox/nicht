<?php
/* Load host base config */
include('local.conf.php');
setlocale(LC_ALL, "fr_CA.UTF-8");

/* Vars */
define('NICHT_BRUTEFORCE_LIMIT', 5);
define('NICHT_BRUTEFORCE_IGNORESEC', 600); // 10min
define('NICHT_REQUESTERID', 'i');
define('NICHT_SRC_SUFFIX', '.php');
define('NICHT_TPL_SUFFIX', '.html.php');

/* Paths */
define('NICHT_PATH_LIB', PATH_ROOT.'lib/');
define('NICHT_PATH_WWW', PATH_ROOT.'www/');
define('NICHT_PATH_MISC', PATH_ROOT.'misc/');
define('NICHT_PATH_SRC', PATH_ROOT.'src/');
define('NICHT_PATH_TPL', PATH_ROOT.'tpl/');
define('NICHT_PATH_ROOT', NICHT_PATH_LIB.'nicht/');
define('NICHT_CLASSPATH', NICHT_PATH_ROOT.'Nicht.class.php');

/* Template */
define('NICHT_TPLCLASSNAME', 'PhpNichtTpl');
define('NICHT_TPLCLASSPATH', NICHT_PATH_ROOT.'PhpNichtTpl.class.php');

/* Modules */
define('NICHT_MODULE_ENV', 'DefaultNichtEnv');
define('NICHT_MODULE_DB', 'MysqliNichtDb');
define('NICHT_MODULE_AUTH', 'MysqliNichtAuthPbkdf2');
define('NICHT_MODULE_ACL', 'MysqliNichtAcl');
define('NICHT_MODULE_NAV', 'MysqliNichtNav');

/* Cookie */
define('NICHT_COOKIE_NAME', 'NICHT');
define('NICHT_COOKIE_LIFE', 84600 * 5);
define('NICHT_COOKIE_PATH', '/');
define('NICHT_COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);

/* Default page */
define('NICHT_PAGE_DEFAULT', 'welcome');
define('NICHT_PAGE_DEFAULTAUTH', 'home');
define('NICHT_PAGE_UNAUTHORIZE', 'unauthorize');
define('NICHT_PAGE_UNAVAILABLE', 'unavailable');
define('NICHT_PAGE_LOGIN', 'login');
define('NICHT_PAGE_FATALERROR', '_fatalError');

/* Message */
define('NICHT_MSG_LOGINNEEDED', 'Authentication required.');
define('NICHT_MSG_LOGINLIMIT', 'Maximum login attempt exceded. Try again later.');
define('NICHT_MSG_LOGOUTSUCCESS', 'Logout completed with sucess.');
define('NICHT_MSG_LOGINSUCCESS', 'Login completed with sucess.');
define('NICHT_MSG_LOGINFAILED', 'Login failed.');
define('NICHT_MSG_SUSPENDED', 'Your account have been suspended.');
define('NICHT_MSG_ERROR', 'System error, please contact the administrator.');
?>
