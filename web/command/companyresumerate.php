<?php

/**
 * 公司简历处理及时率
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
        ->from("kjy_company_hasresumes")
        ->where("hr_uid>0")
        ->limit(1)
        ->queryRow();
if($delitotal["total"] == 0){
    exit("no data...");
}
while (true) {
    $limit = 100;
    $companyidtmp = $companyidArr = $companylist = $companydelivery = array();
    $allpage = ceil($delitotal["total"] / $limit); //计算总页数
    if ($allpage == 0) {
        break;
    }
    for ($i = 1; $i <= $allpage; $i++) {//分页查询
        $companyidArr = $db->createCommand()->select('company_id')
                ->from("kjy_company_hasresumes")
                ->limit($limit,($i - 1) * $limit)
                ->where("hr_uid>0")
                ->queryAll(); //所有被投递简历的公司
        foreach ($companyidArr as $ak => $av) {
            $companyidtmp[] = $av["company_id"];
        }
        $companylist = array_unique($companyidtmp); //通过函数过滤唯一性
        if (empty($companylist)) {
            continue;
        }
        foreach ($companylist as $clk => $clv) {
            //根据公司获取收到的简历
            $companydelivery = $db->createCommand()->select('company_id,opreate_status,browse_time,invite_time,delivery_time,hr_uid')
                    ->from("kjy_jobs_delivery")
                    ->where("company_id=" . $clv)
                    ->limit($delitotal["total"])
                    ->queryAll(); //所有被投递简历的公司
            if (empty($companydelivery)) {
                continue;
            }
            $cresumeall = count($companydelivery);
            $cwcresumeall = 0; //公司已完成简历总数
            foreach ($companydelivery as $adk => $adv) {
                if($adv["hr_uid"] == 0){
                    continue;//过滤掉未绑定HR用户
                }
                if ($adv["opreate_status"] == 1) {
                    continue; //过滤掉未处理的简历
                }
                //判断简历浏览时间是否在投递后的7天内
                $cdealtime = 1;
                $opreatetime = ($adv["opreate_status"] == 4) ? $adv["browse_time"] : $adv["invite_time"];
                $timediff = $opreatetime - $adv["delivery_time"];
                $days = intval($timediff / 86400);
                $cdealtime += $days;
                if ($days < 7) {
                    //简历状态为不合适、已安排则认为已处理完成
                    if ($adv["opreate_status"] == 3 || $adv["opreate_status"] == 4) {
                        $cwcresumeall++;
                    }
                }
                //简历处理及时率 该公司所有职位收到的简历中，在投递后7天内处理完成的简历所占比例
                $dealrate = ($cresumeall != 0 && $cwcresumeall != 0) ? round(($cwcresumeall / $cresumeall) * 100) : 0;
                //简历处理用时 该公司的所有职位管理者完成简历处理的平均用时
                $rodealtime = round(($cdealtime / $cwcresumeall))!=0 ? round(($cdealtime / $cwcresumeall)) : 1;
                $dealtime = ($cdealtime != 0 && $cwcresumeall != 0) ? $rodealtime : 0;
                $dealdata = array(
                    "cid" => $adv['company_id'], //公司编号
                    "deal_rate" => $dealrate, //简历处理及时率
                    "deal_time" => $dealtime//简历处理平均用时
                );
                //创建事务处理
                $db->createCommand()->query("START TRANSACTION");
                //判断公司简历处理记录是否存在
                $dealinfo = $db->createCommand()->select("*")
                        ->from('kjy_delivery_deal_static')
                        ->where("cid=:cid", array(":cid" => $dealdata["cid"]))
                        ->limit(1)
                        ->queryRow();
                if (empty($dealinfo)) {
                    $db->createCommand()->insert('kjy_delivery_deal_static', $dealdata);
                    $ret = $db->getLastInsertID();
                    if (false !== $ret) {
                        $db->createCommand()->query("COMMIT"); //成功提交
                    } else {
                        $db->createCommand()->query("ROLLBACK"); //失败回滚
                    }
                    continue;
                } else {
                    $db->createCommand()->update('kjy_delivery_deal_static', $dealdata, 'id=:id', array(':id' => $dealinfo['id']));
                    continue;
                }
            }
        }
    }
    unset($delitotal);
    unset($allpage);
    echo "running once success";
    break;
}
?>
