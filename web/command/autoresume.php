<?php
/**
 * 自动发简历 点击保存后算一个自动发送的机制
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
static $all;
//普通在线简历自动投放
$all = $db->createCommand()->select('count(*) as total')
            ->from("kjy_resume")
            ->where('r_status = :r_status',array(':r_status'=>0))
            ->limit(1)
            ->queryRow();
if(empty($all)){
    die;
}
while (true) {
    $allcount = $all["total"];//所有匹配简历总数
    if($allcount == 0){
        break;
    }
    $limit = 100; //每次只取固定数量的简历
    $allpage = ceil($allcount / $limit);//计算总页数
    if ($allpage == 0) {
        break;
    }
    for ($i = 1; $i <= $allpage; $i++) {//分页查询
        $resumeArr  = array();
        //所有匹配到的自动投递在线简历
        $resumeArr = $db->createCommand()->select('id,u_id,u_jobid,u_jobname,u_city,u_salary,u_job_type')
                    ->from("kjy_resume")
                    ->limit($limit,($i - 1) * $limit)
                    ->queryAll();
        if(empty($resumeArr)){
            continue;
        }
        //循环简历信息
        foreach ($resumeArr as $k => $v) {
            $jobsinfo = $company_ids = $users_ids = $companyinfo = $companyinfo2 = $userinfo = $userinfo2 = $exitsjobs = array();
            $jobsinfo = $db->createCommand()->select('job_id,uid,company_id,typejob_id,typejob_name,job_nature,salary_id,city,experience,education')
                    ->from("kjy_jobs")
                    ->where('typejob_name like :typejob_name',array(':typejob_name'=>$v['u_jobname']."%"))
                    ->queryAll(); //根据用户获取所有已投递简历  这块模糊查询 前面不要% 尽量走索引
            if (empty($jobsinfo)) {
                continue;
            }
            foreach ($jobsinfo as $jdk => $jdv) {//遍历匹配到的所有职位
                $company_ids[$jdv['company_id']] = true;
                $users_ids[$jdv['uid']] = true;
            }
            //公司信息
            $companyinfo = $db->createCommand()->select("c_id,c_industry")
                ->from("kjy_company")
                ->where(array('in', 'c_id', $ids))
                ->queryAll();
            if(empty($companyinfo)){
                continue;
            }
            foreach ($companyinfo as $ck=>$cv){
                $companyinfo2[$cv['c_id']] = $cv;
            }
            unset($companyinfo);
            //用户信息
            $userinfo = $db->createCommand()->select("uid,workexp,education")
                ->from("kjy_members")
                ->where("status = :status and identity = :identity",array(":status"=>1,":identity"=>1))
                ->queryAll();
            if(empty($userinfo)){
                continue;
            }
            foreach ($userinfo as $uk=>$uv){
                $userinfo2[$uv['uid']] = $uv;
            }
            unset($userinfo);
            //遍历匹配到的所有职位 查找匹配度
            foreach ($jobsinfo as $jdk => $jdv) {
                if($jdv['uid'] == 0){
                    continue;
                }
                if($jdv['uid'] > 0 && $v['u_id'] == $jdv['uid']){
                    continue;
                }
                $bppnum = 6;
                //判断期望行业
                if(($v['u_industry']) != $comanyinfo2[$jdv['c_id']]['c_industry']){
                    $bppnum -= 1;
                }
                //判断工作性质
                if($v['u_job_type'] != $jdv['job_nature']){
                    $bppnum -= 1;
                }
                //判断月薪范围
                $jobsalaryArr = explode(',',$jdv['salary_id']);
                if(!in_array($v['u_salary'], $jobsalaryArr)){
                    $bppnum -= 1;
                }
                //判断工作城市 
                if($v['u_city'] != $jdv['city']){
                    $bppnum -= 1;
                }
                //判断工作经验
                if($userinfo2[$v['u_id']]['workexp'] != $jdv['experience']){
                    $bppnum -= 1;
                }
                //判断学历要求
                if($userinfo2[$v['u_id']]['education'] != $jdv['education']){
                    $bppnum -= 1;
                }
                if($bppnum != 6){
                    continue;
                }
                $data = array(
                    'old_rid' => $v['id'],
                    'u_id' => $v['u_id'],
                    'hr_uid' => $jdv['uid'],
                    'company_id' => $jdv['company_id'],
                    'job_id' => $jdv['job_id'],
                    'delivery_status' => 1,
                    'delivery_time' => time(),
                    'auto_status' => 1
                );
                $srdata = array(
                    'old_rid' => $v['id'],
                    'u_id' => $v['u_id'],
                    'hr_uid' => $jdv['uid'],
                    'company_id' => $jdv['company_id'],
                    'status'=>0,
                    'delivery_time' => time()
                );
                //判断是不是已经把某个人的简历投递给这个简历了 投递后就不管了
               $exitsjobs = $db->createCommand()->select('*')
                    ->from("kjy_jobs_delivery")
                    ->where("old_rid = :old_rid and u_id = :u_id and hr_uid = :hr_uid and company_id = :company_id and job_id = :job_id and auto_status = :auto_status",
                            array(":old_rid"=>$v['id'],":u_id"=>$v['u_id'],":hr_uid"=>$jdv['uid'],":company_id"=>$jdv['company_id'],":job_id"=>$jdv['job_id'],":auto_status"=>1))
                    ->limit(1)
                    ->queryRow();
                if(!empty($exitsjobs)){
                    continue;
                }
                //投递简历
                //创建事务处理
                $db->createCommand()->query("START TRANSACTION");
                $db->createCommand()->insert("kjy_jobs_delivery",$data);//简历投递后记录表
                $delivery_id =  $db->getLastInsertID();
                $db->createCommand()->insert("kjy_jobs_delivery_sendemail", $srdata);//简历投递后记录至邮件发送记录表
                $srid = $db->getLastInsertID();
                if($delivery_id && $srid){
                    //添加日志
                    $logdata = array(
                        'jd_id' => $delivery_id,
                        'note' => '已成功接收投递的简历',
                        'info' => '已成功接收投递的简历',
                        'status' => 0,
                        'add_time' => time(),
                    );
                    $db->createCommand()->insert("kjy_job_delivery_logs",$logdata);
                    $getppd = $db->getLastInsertID();

                    //获取职位投递数信息
                    $jobcount = $db->createCommand()->select('*')
                                ->from("kjy_jobs_counts")
                                ->where('job_id = :job_id',array(':job_id'=>$jdv['job_id']))
                                ->limit(1)
                                ->queryRow();
                    if($jobcount){
                        //职位简历投递数量加+1
                        $db->createCommand()->query("update kjy_jobs_counts set `delivery_num` = delivery_num+1 where job_id='".(int)$jdv['job_id']."'");
                    }else{
                        $db->createCommand()->insert("kjy_jobs_counts",array('job_id' =>$jdv['job_id'],'browse_num'=>1,'delivery_num'=>1));
                    }
                    $db->createCommand()->query("COMMIT"); //成功提交
                }else{
                    $db->createCommand()->query("ROLLBACK"); //失败回滚
                    continue;
                }
            }
        }
    }
    unset($all);
    echo "running once success";
    break;
}
?>
