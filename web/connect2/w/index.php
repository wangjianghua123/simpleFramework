<?php
define('LOCALHOST', 'http://www.kjiuye.com');
define('DBUSER', 6378);
session_start();
$uri=substr($_GET['uri'],0,100);
$user=substr($_GET['user'],0,100);
$_SESSION['uri']=$uri;
$_SESSION['user']=$user;
include_once( dirname(__FILE__).'/config.php' );
include_once( dirname(__FILE__).'/saetv2.ex.class.php' );
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
header("Location:".$code_url);
