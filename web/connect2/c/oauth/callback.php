<?php
session_start();
$seturl = 'http://www.kjiuye.com/';
require_once("../../API/qqConnectAPI.php");
$qc = new QC();

$key= $qc->qq_callback();
$openid= $qc->get_openid();
$qc1=new QC($key,$openid);
$arr = $qc1->get_user_info();
$user = $_SESSION['user'];
session_destroy();
include_once('./indexredis.php' );
$_SESSION['qqkey']=$key;
$_SESSION['qqopenid']=$openid;

$_SESSION['qqname']=$arr["nickname"];
if($arr["gender"]=='男')
	
$_SESSION['qqsex']=1;//男
	else
$_SESSION['qqsex']=0;//男	/account/login/third?rqr=
$uri=$_SESSION['uri']?$_SESSION['uri']:$seturl;
unset($_SESSION['uri']);
unset($_SESSION['user']);
$key1 = session_id();
if($user){
    header("Location:".$seturl."user/bangding/rqr/1/");
}else{
    header("Location:".$seturl."foreuser/thirdparty/rqr/1/");
}
?>