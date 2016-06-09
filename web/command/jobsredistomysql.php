<?php
/**
 * 职位浏览数和职位投递数全量同步到mysql
 */
ignore_user_abort(true); // 后台运行
#设置执行时间不限时 
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 0);
define('UC_ROOT', dirname(__FILE__) . '/');
define('UC_DATADIR', UC_ROOT . '../data/');
//#清除并关闭缓冲，输出到浏览器之前使用这个函数。
ob_end_clean();
//#控制隐式缓冲泻出，默认off，打开时，对每个 print/echo 或者输出命令的结果都发送到浏览器。
ob_implicit_flush(1);		
ini_set('memory_limit','500M');

require_once UC_ROOT . '../command/function.php';
//从redis中获取相应的基础数据
static  $rediscache;
$rediscache = getRedis();
$rediscache->select(1);

//初始化数据库
if (!@include UC_DATADIR . 'config.inc.php') {
    exit('The file <b>data/config.inc.php</b> does not exist, perhaps because of UCenter has not been installed, <a href="install/index.php"><b>Please click here to install it.</b></a>.');
}
require_once UC_ROOT . '../lib/db.class.php';
static $db;
$db = new ucserver_db();
$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
header('Content-type:text/html;charset=utf-8');


$i = 0;
$limit = 20;
$list = $rediscache->hmget('job_browse_num',array("bnum_324285","bnum_176081"));
dump($list); 
 //从redis中模拟分页scan出浏览职位的一部分数据
$list = $rediscache->scan("job_browse_num", $i,"bnum_*",$limit);
dump($list);   die;
while (true) {
   
    break;
}
?>