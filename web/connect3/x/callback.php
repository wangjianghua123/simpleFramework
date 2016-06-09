<?php

//session_start();

include_once(dirname(__FILE__).'/indexredis.php' );

$uri=$_SESSION['uri'];

$user = $_SESSION['user'];

include_once( dirname(__FILE__).'/config.php' );

include_once( dirname(__FILE__).'/WeixinSDK.ex.class.php' );

$o = new WeixinSDK();

if (isset($_REQUEST['code'])) {

        $token = $o->getAccessToken($_REQUEST['code']);

        if($token['access_token']){

            $_SESSION['token'] = $token;

        }

        if(!$token){

            $o->parseToken($_SESSION['token']);

        }

        $_SESSION['wxopenid'] = $token['openid'];

        $userinfo = $o->call('sns/userinfo');

        $_SESSION['wxkey']=$userinfo['unionid'];

        $_SESSION['wxname']=$userinfo['nickname'];

        $_SESSION['wxsex']=$userinfo['sex'];

}

if($user){

    header("Location:http://pin.ujiuye.com/user/bangding/rqr/3/");

}else{

    header("Location:http://pin.ujiuye.com/foreuser/thirdparty/rqr/3/");

}

?>

