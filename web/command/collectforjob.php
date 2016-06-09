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

header('Content-type:text/html;charset=utf-8');
require_once UC_ROOT.'../command/function.php';
require_once UC_ROOT.'../lib/db.class.php';
static $db;
$db = new ucserver_db();
$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
static $db_cj;
$db_cj = new ucserver_db();
$db_cj->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, 'cmsforzhaopin', UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);

//从redis中获取相应的基础数据
static  $rediscache;
$rediscache = getRedis();
$rediscache->select(10);
static $_citydata;
$_citydata = json_decode($rediscache->get('areacache'),true);
static $_typejob_list;
$_typejob_list = json_decode($rediscache->get('typejob'),true);

if (empty($_citydata)) {
    echo "cache null\n\n";die;
} 
foreach($_citydata as $k=>$v){
    $_citydata2[$v['name']] = $v;
}
static $jobsedu = array(
    '所有' => 0,
    '不限' => 0,
    '初中及以下' =>1,
    '高中' => 1,
    '中技' => 1,
    '中专' => 1,
    '大专' => 2,
    '本科' => 3,
    '硕士' => 4,
    '博士' => 5
);
static $jobsexps = array(
    '所有' => 0,
    '不限' => 0,
    '在读学生' =>1,
    '应届毕业生' => 1,
    '1年' => 2,
    '2年' => 3,
    '3-4年' => 4,
    '5-7年' => 5,
    '8-9年' => 6,
    '10年以上' => 7
);

static $jobsnature = array(
    '不限' => 0,
    '全职' => 1,
    '兼职' => 2
);
static $salarydata = array("不限","2k以下","2k-5k","5k-10k","10k-15k","15k-25k","25k-50k","50k以上");
while (true) {
    $message = $data = $jobs = $jobsinfo = array();
    $message = $db_cj->createCommand()->select('*')
            ->from("v9_collection_content")
            ->where("status2 = :status2 and status = :status and company_id > :cid",array(":status2 "=>0,":status"=>2,":cid"=>0))
            ->limit(1)
            ->queryRow();
    if (empty($message)) {
        echo "deal over\n\n";
        //然后修改采集状态
        $update = array();
        $update['status2'] = 2;//修改状态
        $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
        unset($update);
        continue;
    } 
    if($message['typejobid'] == 0){
        echo "typejobid is null\n\n";
        //然后修改采集状态
        $update = array();
        $update['status2'] = 2;//修改状态
        $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
        unset($update);
        continue;
    }
    
    //UPDATE  `v9_collection_content` SET STATUS =1
    if(!empty($message['data'])){
        $data = string2array($message['data']);
        if(empty($data)){
            echo "data is null\n\n";
            //然后修改采集状态
            $update = array();
            $update['status2'] = 2;//修改状态
            $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
            unset($update);
            continue;
        }
        if(!empty($data['job_basic'])){
            $job_basic = $job_basic_tmp = $job_basics_tmp = $job_basics = $job_basics2 = array();
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
                }elseif($jobvarr[0] == "工作地点"){
                    $jobvarr4 = explode("-", $jobvarr[1]);
                    $job_basics[$jobvarr[0]] = $jobvarr4[0]."市";
                }else{
                    $job_basics[$jobvarr[0]] = $jobvarr[1];
                }
                unset($jobvarr,$jobvarr2,$jobvarr3);
            }
            //以天为单位的去掉
            if($job_basics["薪资范围开始"] == -1 && $job_basics["薪资范围结束"] == -1){
                    //然后修改采集状态
                    $update = array();
                    $update['status2'] = 1;//修改状态
                    $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
                    unset($update,$jobs,$jobsinfo);
                    continue;
            }
            $jobs['company_id'] = $message['company_id'];
            $jobs['typejob_id'] = $message['typejobid'];//职位类别id
            //$jobs['typejob_name'] = $_typejob_list[$message['typejobid']]['typejobname'];//职位名称
            $jobs['typejob_name'] = $message['title'];//职位名称
            $jobs['department'] = ""; //所在部门
            $jobs['job_nature'] =  false !== strpos($message['title'],'兼职') ? 2 : 1; //工作性质
            $jobs['salary_start'] = $job_basics["薪资范围开始"]; //月薪开始
            $jobs['salary_end'] = $job_basics["薪资范围结束"];//月薪结束
            $jobs['salary_id'] =  getsyssalaryid($salarydata,$jobs["salary_start"],$jobs["salary_end"]);
            $jobs['city'] = (int)$_citydata2[$job_basics["工作地点"]]['areaid'];//工作城市
            $jobs['prov'] = (int)$_citydata[$jobs['city']]['parentid'];//工作省份 方便搜索使用
            $jobs['experience'] = $jobsexps[$job_basics["工作年限"]];//工作经验
            $jobs['education'] = $jobsedu[$job_basics["学;;;;历"]];//学历
            
            $jobs['position_temptation'] = "";//职位诱惑
            $jobs['addr'] = $data['job_address'];//工作地址
            $jobs['refresh_time'] = $jobs['create_time'] = strtotime($job_basics['发布日期']);
            $jobsinfo['intro'] = trim($data['content']);//职位详情
            $db = new ucserver_db();
            $db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
            //创建事务处理 添加公司基本信息和 公司详情信息
            $db->createCommand()->query("START TRANSACTION");
            $db->createCommand()->insert('kjy_jobs',$jobs);
            $jobsid = $jobsintroid = 0;
            $jobsid = $db->getLastInsertID();     
            if(false !== $jobsid){
                $jobsinfo['job_id'] = $jobsid;
                $db->createCommand()->insert('kjy_jobs_intro',$jobsinfo);
                $jobsintroid = $db->getLastInsertID(); 
                if(false !== $jobsintroid){
                    //修改公司的所在城市信息
                    $update2 = array();
                    $update2['c_city'] = $jobs['city'];
                    $db->createCommand()->update('kjy_company',$update2,'c_id=:c_id',array(':c_id'=>$message['company_id'])); 
                    unset($update);
                    $db->createCommand()->query("COMMIT"); //成功提交
                    echo 'job success<br>';
                }else{
                    $db->createCommand()->query("ROLLBACK"); //失败回滚
                }
            }else{
                $db->createCommand()->query("ROLLBACK"); //失败回滚
            }     
            //然后修改采集状态
            $update = array();
            $update['status2'] = 1;//修改状态
            $db_cj = new ucserver_db();
            $db_cj->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, 'cmsforzhaopin', UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
            $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
            unset($update,$jobs,$jobsinfo);
            continue;
        }else{
            echo 'basic data is null \n\n';
            //然后修改采集状态
            $update = array();
            $update['status2'] = 2;//修改状态
            $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
            unset($update);
            continue;
        }
    }else{
        echo "data is null\n\n";
        //然后修改采集状态
        $update = array();
        $update['status2'] = 2;//修改状态
        $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
        unset($update);
        continue;
    }
}


/**
 * 根据薪资范围获取系统相对应的薪资编号
 * @param type $start
 * @param type $end
 */
function getsyssalaryid($salarydata,$start = 0, $end = 0) {
    $salary_id = "";
    unset($salarydata[0]); //去掉“不限”
    foreach ($salarydata as $sk => $sv) {
        $sv = str_replace(array('k', '以', '上', '下'), "", $sv);
        if (strstr($sv, '-')) {
            $sArr = explode('-', $sv);
            if ($start >= $sArr[0] && $start <= $sArr[1]) {
                $salary_id = addtostring($salary_id,$sk);
            }
            if ($end >= $sArr[0] && $end <= $sArr[1]) {
                $salary_id =addtostring($salary_id,$sk);
            }
            if ($start < $sArr[0] && $end >= $sArr[0] && $end >= $sArr[1]) {
                $salary_id = addtostring($salary_id,$sk);
            }
        } else {
            if($start >= $sv && $end >= $sv && $sv!=2){
                $salary_id = addtostring($salary_id,$sk);
            }
        }
    }
    return substr($salary_id, 0, strlen($salary_id) - 1);
}
function addtostring($str,$key){
    if(empty($str)){
        $str .= $key.",";
    }else{
        $strArr = explode(',', $str);
        if(!in_array($key, $strArr)){
            $str .= $key.",";
        }
    }
    return $str;
}
?>