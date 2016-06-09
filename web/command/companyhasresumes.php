<?php

/**
 * 被投递简历的公司
 * 用于前台投递公司简历处理及时率和后台查看未绑定公司收到简历记录
 * @author FMJ
 */
ignore_user_abort(true); // 后台运行
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 0);
define('UC_ROOT', dirname(__FILE__) . '/');
define('UC_DATADIR', UC_ROOT . '../data/');
//初始化数据库
if (!@include UC_DATADIR . 'config.inc.php') {
    exit('The file <b>data/config.inc.php</b> does not exist, perhaps because of UCenter has not been installed, <a href="install/index.php"><b>Please click here to install it.</b></a>.');
}
require_once UC_ROOT . '../command/function.php';
require_once UC_ROOT . '../lib/db.class.php';
static $db;
$db = new ucserver_db();
$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
header('Content-type:text/html;charset=utf-8');
static $delitotal;
$delitotal = $db->createCommand()->select('count(*) as total')
        ->from("kjy_jobs_delivery")
        ->limit(1)
        ->queryRow(); //所有已投递简历
if ($delitotal["total"] == 0) {
    exit();
}
while (true) {
    $limit = 100;
    $companyidtmp = $compnyidArr = $hasresumecompanys = $hascidArr = array();
    $allpage = ceil($delitotal["total"] / $limit); //计算总页数
    if ($allpage == 0) {
        break;
    }
    for ($i = 1; $i <= $allpage; $i++) {//分页查询
        $companyidArr = $db->createCommand()->select('company_id,hr_uid')
                ->from("kjy_jobs_delivery")
                ->limit($limit, ($i - 1) * $limit)
                ->queryAll(); //所有被投递简历的公司
        foreach ($companyidArr as $ak => $av) {
            $isexist = $db->createCommand()->select('id,company_id,hr_uid')
                    ->from("kjy_company_hasresumes")
                    ->where("company_id=" . $av['company_id'])
                    ->limit(1)
                    ->queryRow();
            if (!empty($isexist)) {
                //判断公司的hr_uid是否有变化，如果有则更新
                if ($isexist['hr_uid'] != $av['hr_uid']) {
                    $db->createCommand()->update("kjy_company_hasresumes", array('hr_uid' => $av['hr_uid']), 'id=:id', array(':id' => $isexist['id']));
                }
                continue;
            }
            $insertdata = array(
                'company_id' => $av['company_id'],
                'hr_uid' => $av['hr_uid']
            );
            //创建事务处理
            $db->createCommand()->query("START TRANSACTION");
            $db->createCommand()->insert('kjy_company_hasresumes', $insertdata);
            $ret = $db->getLastInsertID();
            if (false !== $ret) {
                $db->createCommand()->query("COMMIT"); //成功提交
            } else {
                $db->createCommand()->query("ROLLBACK"); //失败回滚
            }
        }
    }
    unset($delitotal);
    unset($allpage);
    echo "running once success";
    break;
}
?>
