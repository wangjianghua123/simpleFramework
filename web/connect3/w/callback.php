<?php
session_start();
$seturl = 'http://pin.ujiuye.com/';

$uri=$_SESSION['uri']?$_SESSION['uri']:$seturl;
$user = $_SESSION['user'];
unset($_SESSION['uri']);
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

if (isset($_REQUEST['code'])) {
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = WB_CALLBACK_URL;
 
	$token = $o->getAccessToken( 'code', $keys ) ;
	session_destroy();
	include_once('indexredis.php' );
	$_SESSION['wbopenid']=$token['uid'];
	$userinfo=$o->get('users/show', array('uid'=>$token['uid']) );
	   
	$_SESSION['wbkey']=$token['access_token'];
	$_SESSION['wbname']=$userinfo['screen_name'];
	if($userinfo['gender']=='m')
		$_SESSION['qqsex']=1;
	else
		$_SESSION['qqsex']=0;
     
}

if($user){
    header("Location:".$seturl."user/bangding/rqr/2");
}else{
    header("Location:".$seturl."foreuser/thirdparty/rqr/2");
}
?>
