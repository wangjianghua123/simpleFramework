<?php
/**
 * 公司全量同步到solr
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

//初始化数据库
if (!@include UC_DATADIR . 'config.inc.php') {
    exit('The file <b>data/config.inc.php</b> does not exist, perhaps because of UCenter has not been installed, <a href="install/index.php"><b>Please click here to install it.</b></a>.');
}
require_once UC_ROOT . '../command/function.php';
require_once UC_ROOT . '../lib/db.class.php';
require_once UC_ROOT . '../lib/Solrclient.class.php';
static $db;
static $solr;
$db = new ucserver_db();
$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
//初始化solr
$solr = new Solrclient('','companys');
header('Content-type:text/html;charset=utf-8');
$all = $db->createCommand()->select('count(*) as total')
            ->from("kjy_company")
            ->limit(1)
            ->queryRow();
$allcount = $all["total"];//所有公司数
if($allcount == 0){
    exit();
}
while (true) {
    $limit = 1000; //每次只取固定数量的公司
    $allpage = ceil($all["total"] / $limit);//计算总页数
    if ($allpage == 0) {
        break;
    }
    $hridtmp = $hridArr = $receivedlist = $hrlist = $jobresumelist = array();
    for ($i = 1; $i <= $allpage; $i++) {//分页查询
        $list = $db->createCommand()->select('c_id,c_name,c_short_name,c_industry,c_city,c_property,c_size,c_develop_stage,c_logo_id,c_tag,c_homepage,c_intro,c_verify_status,c_add_userid,c_add_time')
                ->from("kjy_company")
                ->where("c_verify_status=0")
                ->limit($limit,($i - 1) * $limit)
                ->queryAll();
        if(empty($list)){
            continue;
        }
        foreach($list as $k=>&$v){
            if(empty($v['c_short_name'])){
                $v['c_short_name'] = $v['c_name'];
            }
        }
        $response = $solr->update($list);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }
    }
    unset($all,$allcount);
    echo "running once success\r\n";
    break;
}
?>