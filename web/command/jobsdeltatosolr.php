<?php
/**
 * 职位增量同步到solr
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
while (true) {
    $list = $db->createCommand()->select('*')
            ->from("kjy_solr_jobs")
            ->where("status = 0 and ttype in(1,2,3,5,6,7)")
            ->limit(1)
            ->queryRow();
    if(empty($list)){
        echo "deal over\n\n";
        break;
    }
    if(empty($list['jid'])){
         //当从职位表中找不到相关信息 则删除队列信息
        $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
        continue;
    }
    if(false !== in_array($list['ttype'],array(1,2))){ //添加修改
        //拿职位信息
        $joblist = array();
        $joblist = $db->createCommand()->select('job_id,company_id,uid,typejob_id,typejob_name,job_nature,salary_start,salary_end,salary_id,prov,city,experience,education,create_time,refresh_time,status')
            ->from("kjy_jobs")
            ->where("job_id = ".$list['jid'])
            ->limit(1)
            ->queryRow();
        //从公司中获取一些冗余数据
        $comlist = $db->createCommand()->select("c_id,c_name,c_short_name,c_industry,c_property,c_size")
                ->from("kjy_company")
                ->where("c_id = ".$joblist['company_id'])
                ->limit(1)
                ->queryRow();
        if(!empty($comlist)){
            $joblist['c_short_name'] = !empty($comlist['c_short_name']) ? $comlist['c_short_name'] : $comlist['c_name'];
            $joblist['c_industry'] = (int)$comlist['c_industry'];
            $joblist['c_property'] = (int)$comlist['c_property'];
            $joblist['c_size'] = (int)$comlist['c_size'];
        }
        if(empty($joblist)){
            //当从职位表中找不到相关信息 则删除队列信息
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
            continue;
        }
        $response = $solr->update(array($joblist));
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
        }
    } else if($list['ttype'] == 3) { //删除
        $list2 = array();
        $list2['job_id'] = $list['jid'];
        $response = $solr->deleteIndex($list2);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
        }
    } else if($list['ttype'] == 5){ //hr一键刷新
        //hr一键刷新 先根据hrid找到下面的所有发布的执行进行更新时间
        //拿职位信息
        $joblist = array();
        $joblist = $db->createCommand()->select('job_id,refresh_time')
            ->from("kjy_jobs")
            ->where("uid = ".$list['jid'])
            ->queryAll();
        if(empty($joblist)){
            //当从职位表中找不到相关信息 则删除队列信息
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
            continue;
        }
        foreach ($joblist as $jk=>&$jv){
            $jv['refresh_time'] = array('set'=>$jv['refresh_time']);
        }
        $response = $solr->update($joblist);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
        }
    } else if($list['ttype'] == 6){ //hr解绑职位
        //hr一键刷新 先根据hrid找到下面的所有发布的执行进行更新时间
        //拿职位信息
        $joblist = array();
        $joblist = $db->createCommand()->select('job_id,uid')
            ->from("kjy_jobs")
            ->where("uid = ".$list['jid'])
            ->queryAll();
        if(empty($joblist)){
            //当从职位表中找不到相关信息 则删除队列信息
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
            continue;
        }
        foreach ($joblist as $jk=>&$jv){
            $jv['uid'] = array('set'=>0);
        }
        $response = $solr->update($joblist);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
        }
    } else if($list['ttype'] == 7){ //hr重新绑定职位
        //hr一键刷新 先根据hrid找到下面的所有发布的执行进行更新时间
        //拿职位信息
        $joblist = array();
        $joblist = $db->createCommand()->select('job_id,uid')
            ->from("kjy_jobs")
            ->where("uid = ".$list['jid'])
            ->queryAll();
        if(empty($joblist)){
            //当从职位表中找不到相关信息 则删除队列信息
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
            continue;
        }
        foreach ($joblist as $jk=>&$jv){
            $jv['uid'] = array('set'=>$jv['uid']); //重新赋值企业用户id
        }
        $response = $solr->update($joblist);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_jobs",'currtime = :currtime and jid = :jid and ttype = :ttype',array(':currtime'=>$list['currtime'],':jid'=>$list['jid'],':ttype'=>$list['ttype']));
        }
    }
    continue;
}
//当执行完毕后优化下表结构
$db->createCommand()->query("OPTIMIZE TABLE `kjy_solr_jobs`");
?>