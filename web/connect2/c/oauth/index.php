<?php
session_start();
$uri=substr($_GET['uri'],0,100);
$user=substr($_GET['user'],0,100);
require_once("../../API/qqConnectAPI.php");
$_SESSION['uri']=$uri;
$_SESSION['user']=$user;
$qc = new QC();
$qc->qq_login();
