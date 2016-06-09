<?php
//session_start();
include_once(dirname(__FILE__).'/indexredis.php' );
$uri=substr($_GET['uri'],0,100);
$user=substr($_GET['user'],0,100);
$_SESSION['uri']=$uri;
$_SESSION['user']=$user;
include_once( dirname(__FILE__).'/config.php' );
include_once( dirname(__FILE__).'/WeixinSDK.ex.class.php' );
$o = new WeixinSDK();
$code_url = $o->getRequestCodeURL();
header("Location:".$code_url);
