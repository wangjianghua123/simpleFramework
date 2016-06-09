<?php
/*
 * 职位采集对接
 */
ignore_user_abort(true); // 后台运行
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai'); 
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors',0);
ini_set('log_errors',0);

define('UC_ROOT', dirname(__FILE__).'/');
define('UC_DATADIR', UC_ROOT.'../data/');

//初始化数据库
if(!@include UC_DATADIR.'config.inc.php') {
   exit('The file <b>data/config.inc.php</b> does not exist, perhaps because of UCenter has not been installed, <a href="install/index.php"><b>Please click here to install it.</b></a>.');
}
require_once UC_ROOT.'../command/function.php';
require_once UC_ROOT.'../lib/db.class.php';
static $db;
$db = new ucserver_db();
$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);

header('Content-type:text/html;charset=utf-8');

while (true) {
    $jobs = $message = $data = $jobs = $jobsinfo = array();
    $jobs = $db->createCommand()->select('*')
            ->from("kjy_jobs")
            ->where("salary_start = 1 and salary_end = 1 and flag = 0")
            ->limit(1)
            ->queryRow();
    if(empty($jobs)){
         echo "deal over\n\n";
         continue;
    }
    $message = $db->createCommand()->select('*')
            ->from("v9_collection_content")
            ->where("company_id = :cid and title = :title ",array(":cid "=>$jobs['company_id'],":title"=>$jobs['typejob_name']))
            ->limit(1)
            ->queryRow();
    if (empty($message)) {
        echo "deal over\n\n";
        continue;
    } 
    
    //UPDATE  `v9_collection_content` SET STATUS =1
    if(!empty($message['data'])){
        $data = string2array($message['data']);
        if(empty($data)){
            echo "data is null\n\n";
            continue;
        }
        if(!empty($data['job_basic'])){
            $job_basic = $job_basic_tmp = $job_basics_tmp = $job_basics = array();
            $job_basic_tmp = trim(strip_tags(str_replace('<td class="txt_1" width="12%">',",",str_replace("&nbsp;",";", $data['job_basic']))));
            $job_basics_tmp = array_filter(explode(",",$job_basic_tmp));
            foreach($job_basics_tmp as $jobk=>$jobv){
                $jobvarr = $jobvarr2 = $jobvarr3 = $jobvarr4 = $jobvarr5 = array();
                $jobvarr = explode("：",$jobv);
                if($jobvarr[0] == "薪资范围"){
                    $jobvarr2 = explode(";", $jobvarr[1]);
                    $jobvarr3 = explode("-",$jobvarr2[0]);
                    if(false !== strpos($jobvarr[1],'万/年')){
                            $job_basics[$jobvarr[0]."开始"] = (int)ceil((((int)$jobvarr3[0]*10000)/12)/1000);
                            $job_basics[$jobvarr[0]."结束"] = (int)ceil((((int)$jobvarr3[1]*10000)/12)/1000);
                    }else if(false !== strpos($jobvarr[1],'/年')){
                            $job_basics[$jobvarr[0]."开始"] = (int)ceil((((int)$jobvarr3[0])/12)/1000);
                            $job_basics[$jobvarr[0]."结束"] = (int)ceil((((int)$jobvarr3[1])/12)/1000);
                    }else if(false !== strpos($jobvarr[1],'/天')){
                        $job_basics[$jobvarr[0]."开始"] = -1;
                        $job_basics[$jobvarr[0]."结束"] = -1;
                    }else{
                        $job_basics[$jobvarr[0]."开始"] = (int)ceil(((int)$jobvarr3[0])/1000);
                        $job_basics[$jobvarr[0]."结束"] = (int)ceil(((int)$jobvarr3[1])/1000);
                    }
                }
                unset($jobvarr,$jobvarr2,$jobvarr3);
            }
            
            if($job_basics["薪资范围开始"] == -1 && $job_basics["薪资范围结束"] == -1){
                    continue;
            }
            $edit = array(
                'salary_start' => $job_basics["薪资范围开始"],
                'salary_end' => $job_basics["薪资范围结束"],
                'flag'=>1
            );
            //创建事务处理 添加公司基本信息和 公司详情信息
            $db->createCommand()->query("START TRANSACTION");
            $jobsid = $db->createCommand()->update("kjy_jobs", $edit, 'job_id=:job_id', array(':job_id' => $jobs['job_id']));
            if(false !== $jobsid){
                $db->createCommand()->query("COMMIT"); //成功提交
                echo 'job success<br>';
            }else{
                $db->createCommand()->query("ROLLBACK"); //失败回滚
                echo 'job error<br>';
            }   
            continue;
        }else{
            echo 'basic data is null \n\n';
            continue;
        }
    }else{
        echo "data is null\n\n";
        continue;
    }
}
?>