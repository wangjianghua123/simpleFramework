<?php
/**
 * 职位全量同步到solr
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
$solr = new Solrclient('','jobs');
header('Content-type:text/html;charset=utf-8');
$all = $db->createCommand()->select('*')
            ->from("kjy_solr_jobs")
            ->where("status = 0 and ttype = 4")
            ->limit(1)
            ->queryRow();
if(empty($all)){
    exit();
}

$alls = $db->createCommand()->select('count(*) as total')
            ->from("kjy_jobs")
            ->where("status=0")
            ->limit(1)
            ->queryRow();
$allcount = $alls["total"];//所有职位数
if($allcount == 0){
    exit();
}
while (true) {
    $limit = 1000; //每次只取固定数量的职位
    $allpage = ceil($allcount / $limit);//计算总页数
    if ($allpage == 0) {
        break;
    }
    for ($i = 1; $i <= $allpage; $i++) {//分页查询
        $list = $db->createCommand()->select('job_id,refresh_time')
                ->from("kjy_jobs")
                ->where("status=0")
                ->limit($limit,($i - 1) * $limit)
                ->queryAll();
        if(empty($list)){
            continue;
        }
        foreach ($list as $jk=>&$jv){
            $jv['refresh_time'] = array('set'=>$jv['refresh_time']);
        }
        $response = $solr->update($list);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }
    }
    //当更新solr成功后删除队列表数据
    $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$all['currtime'],':jid'=>$all['jid'],':ttype'=>$all['ttype']));
    unset($all,$alls,$allcount);
    echo "running once success\r\n";    
    break;
}
//当执行完毕后优化下表结构
$db->createCommand()->query("OPTIMIZE TABLE `kjy_solr_jobs`");
?>