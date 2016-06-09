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
$solr = new Solrclient('','companys');
header('Content-type:text/html;charset=utf-8');
while (true) {
    $list = $db->createCommand()->select('*')
            ->from("kjy_solr_companys")
            ->where("status = 0")
            ->limit(1)
            ->queryRow();
    if(empty($list)){
        echo "deal over\n\n";
        break;
    }
    if(empty($list['cid'])){
         //当从公司表中找不到相关信息 则删除队列信息
        $db->createCommand()->delete("kjy_solr_companys",'currtime = :currtime and cid = :cid and ttype = :ttype',array(':currtime'=>$list['currtime'],':cid'=>$list['cid'],':ttype'=>$list['ttype']));
        continue;
    }
    if(false !== in_array($list['ttype'],array(1,2))){ //添加修改
        //拿公司信息
        $comlist = array();
        $comlist = $db->createCommand()->select('c_id,c_name,c_short_name,c_industry,c_city,c_property,c_size,c_develop_stage,c_logo_id,c_tag,c_homepage,c_intro,c_verify_status,c_add_userid,c_add_time')
            ->from("kjy_company")
            ->where("c_id = ".$list['cid'])
            ->limit(1)
            ->queryRow();
        if(empty($comlist)){
            //当从公司表中找不到相应的数据 则删除队列表数据
            $db->createCommand()->delete("kjy_solr_companys",'currtime = :currtime and cid = :cid and ttype = :ttype',array(':currtime'=>$list['currtime'],':cid'=>$list['cid'],':ttype'=>$list['ttype']));
            continue;
        }
        $response = $solr->update(array($comlist));
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_companys",'currtime = :currtime and cid = :cid and ttype = :ttype',array(':currtime'=>$list['currtime'],':cid'=>$list['cid'],':ttype'=>$list['ttype']));
        }
    }
    else if($list['ttype'] == 3) { //删除数据
        $list2 = array();
        $list2['c_id'] = $list['cid'];
        $response = $solr->deleteIndex($list2);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_companys",'currtime = :currtime and cid = :cid and ttype = :ttype',array(':currtime'=>$list['currtime'],':cid'=>$list['cid'],':ttype'=>$list['ttype']));
        }
    } else if($list['ttype'] == 4){ //hr解绑操作
        //hruid去拿到绑定的公司信息进行修改hruid操作
        //拿职位信息
        $joblist = array();
        $joblist = $db->createCommand()->select('c_id,c_add_userid,c_add_username')
            ->from("kjy_company")
            ->where("c_add_userid = ".$list['cid'])
            ->queryAll();
        if(empty($joblist)){
            //当从公司表中找不到相应的数据 则删除队列表数据
            $db->createCommand()->delete("kjy_solr_companys",'currtime = :currtime and cid = :cid and ttype = :ttype',array(':currtime'=>$list['currtime'],':cid'=>$list['cid'],':ttype'=>$list['ttype']));
            continue;
        }
        foreach ($joblist as $jk=>&$jv){
            $jv['c_add_userid'] = array('set'=>0); 
            $jv['c_add_username'] = array('set'=>''); 
        }
        $response = $solr->update($joblist);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_companys",'currtime = :currtime and cid = :cid and ttype = :ttype',array(':currtime'=>$list['currtime'],':cid'=>$list['cid'],':ttype'=>$list['ttype']));
        }
    } else if($list['ttype'] == 5){ //hr重新绑定操作
        //hruid去拿到绑定的公司信息进行修改hruid操作
        //拿职位信息
        $joblist = array();
        $joblist = $db->createCommand()->select('c_id,c_add_userid,c_add_username')
            ->from("kjy_company")
            ->where("c_add_userid = ".$list['cid'])
            ->queryAll();
        if(empty($joblist)){
            //当从公司表中找不到相应的数据 则删除队列表数据
            $db->createCommand()->delete("kjy_solr_companys",'currtime = :currtime and cid = :cid and ttype = :ttype',array(':currtime'=>$list['currtime'],':cid'=>$list['cid'],':ttype'=>$list['ttype']));
            continue;
        }
        foreach ($joblist as $jk=>&$jv){
            $jv['c_add_userid'] = array('set'=>$jv['c_add_userid']);  //重新赋值企业用户信息
            $jv['c_add_username'] = array('set'=>$jv['c_add_username']); 
        }
        $response = $solr->update($joblist);
        if($response['responseHeader']['status'] !== 0){
           echo " Failure!!!";
        }else{
            //当更新solr成功后删除队列表数据
            $db->createCommand()->delete("kjy_solr_companys",'currtime = :currtime and cid = :cid and ttype = :ttype',array(':currtime'=>$list['currtime'],':cid'=>$list['cid'],':ttype'=>$list['ttype']));
        }
    }
    continue;
}
//当执行完毕后优化下表结构
$db->createCommand()->query("OPTIMIZE TABLE `kjy_solr_companys`");
?>