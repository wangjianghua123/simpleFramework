<?php
/*
 * 公司采集对接
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
header('Content-type:text/html;charset=utf-8');
//公司性质
/*
 * 1 互联网/电子商务 计算机软件 IT服务(系统/数据/维护) 电子技术/半导体/集成电路 计算机硬件 通信/电信/网络设备 通信/电信运营、增值服务 网络游戏
 * 2 基金/证券/期货/投资 保险 银行 信托/担保/拍卖/典当
 * 3 能源/矿产/采掘/冶炼 石油/石化/化工 电气/电力/水利 环保
 * 4 汽车/摩托车 大型设备/机电设备/重工业 加工制造（原料加工/模具） 仪器仪表及工业自动化 印刷/包装/造纸 办公用品及设备 航空/航天研究与制造
 * 5 医药/生物工程 医疗设备/器械
 * 6 房地产/建筑/建材/工程 家居/室内设计/装饰装潢 物业管理/商业中心
 * 7 快速消费品（食品/饮料/烟酒/日化） 耐用消费品（服饰/纺织/皮革/家具/家电） 贸易/进出口 零售/批发 租赁服务
 * 8 交通/运输 物流/仓储
 * 9 医疗/护理/美容/保健/卫生服务 酒店/餐饮 旅游/度假 各类中介服务 财会/法律/人力资源服务等 广告/会展/公关 检验/检测/认证 外包服务
 * 10 教育/培训/院校 礼品/玩具/工艺美术/收藏品/奢侈品 媒体/出版/影视/文化传播 娱乐/体育/休闲
 * 11 农/林/牧/渔 跨领域经营 其他
 */
static $companyindustry = array(
    '互联网/电子商务'=>1,//计算机/互联网/电商
    '计算机软件'=>1,//计算机/互联网/电商
    '计算机硬件'=>1,//计算机/互联网/电商
    '计算机服务(系统、数据服务、维修)'=>1,//计算机/互联网/电商
    '网络游戏'=>1,//计算机/互联网/电商
    '通信/电信/网络设备'=>1,//通信/电子
    '通信/电信运营、增值服务'=>1,//通信/电子
    '电子技术/半导体/集成电路'=>1,//通信/电子
    '仪器仪表/工业自动化'=>4,//通信/电子
    '会计/审计'=>9,// 金融/银行/保险/证券
    '金融/投资/证券'=>2,// 金融/银行/保险/证券
    '银行'=>2,// 金融/银行/保险/证券
    '信托/担保/拍卖/典当'=>2,// 金融/银行/保险/证券
    '保险'=>2,// 金融/银行/保险/证券
    '教育/培训/院校'=>10,//教育培训
    '学术/科研'=>10,//教育培训
    '法律'=>9,//教育培训
    '交通/运输/物流'=>8,//物流/运输
    '航天/航空'=>4,//物流/运输
    '石油/化工/矿产/地质'=>3,//能源/冶炼/化工/环保
    '采掘业/冶炼'=>3,//能源/冶炼/化工/环保
    '电气/电力/水利'=>3,//能源/冶炼/化工/环保
    '新能源'=>3,//能源/冶炼/化工/环保
    '原材料和加工'=>4,//能源/冶炼/化工/环保
    '制药/生物工程'=>5,//医疗/制药
    '医疗/护理/卫生'=>9,//医疗/制药
    '医疗设备/器械'=>5,//医疗/制药
    '中介服务'=>9,//贸易/中介
    '贸易/进出口'=>7,//贸易/中介
    '快速消费品(食品、饮料、化妆品)'=>7,//消费品
    '奢侈品/收藏品/工艺品/珠宝'=>10,//消费品
    '餐饮业'=>9,//服务/酒店/餐饮/体育/娱乐
    '酒店/旅游'=>9,//服务/酒店/餐饮/体育/娱乐
    '娱乐/休闲/体育'=>10,//服务/酒店/餐饮/体育/娱乐
    '美容/保健'=>9,//服务/酒店/餐饮/体育/娱乐
    '生活服务'=>9,//服务/酒店/餐饮/体育/娱乐
    '机械/设备/重工'=>4, //制造/汽车/机械/仪表
    '汽车及零配件'=>4, //制造/汽车/机械/仪表
    '广告'=>9,//广告/传媒/会展
    '影视/媒体/艺术/文化传播'=>10,//广告/传媒/会展
    '文字媒体/出版'=>10,//广告/传媒/会展
    '公关/市场推广/会展'=>9,//广告/传媒/会展
    '印刷/包装/造纸'=>4,//广告/传媒/会展
    '房地产'=>6,//房地产
    '物业管理/商业中心'=>6,//房地产
    '建筑/建材/工程'=>6,//建筑
    '家居/室内设计/装潢'=>6 //建筑
);
while (true) {
    $message = $data = $company  = array();
    $message = $db_cj->createCommand()->select('*')
            ->from("v9_collection_content")
            ->where("status = :status and company_id > :company_id",array(":status"=>2,":company_id"=>0))
            ->limit(1)
            ->queryRow();
    if (empty($message)) {
        echo "deal over\n\n";
        //然后修改采集状态
        $update = array();
        $update['status'] = 3;//修改状态
        $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
        unset($update);
        break;
    } 
    
    if($message['typejobid'] == 0){
        echo "typejobid is null\n\n";
        //然后修改采集状态
        $update = array();
        $update['status'] = 3;//修改状态
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
            $update['status'] = 3;//修改状态
            $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
            unset($update);
            continue;
        }
        //当这些为空的时候 改状态为4
        if(empty($data['company_name']) && empty($data['company_industry']) && empty($data['company_nature']) && empty($data['company_size']) && empty($data['company_tag'])
           && empty($data['company_url']) && empty($data['company_address']) && empty($data['company_intro'])){
            //然后修改采集状态
            $update = array();
            $update['status'] = 4;//修改状态
            $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
            unset($update);
            continue;
        }
        $company = array();
        $company_industry = str_replace("&nbsp;&nbsp;",",", $data['company_industry']);
        $company_industrys = array();
        if(!empty($company_industry)){
            $company_industrys = explode(",",$company_industry);
            $company_industrys = array_flip(array_filter($company_industrys));
            foreach($company_industrys as $ck=>$cv){
                if($companyindustry[$ck]){
                    $company['c_industry'] = $companyindustry[$ck];
                    break;
                }else{
                   $company['c_industry'] = 0; 
                }
            }
        }else{
            $company['c_industry'] = 0; 
        }
        $db = new ucserver_db();
        $db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);

        //创建事务处理 添加公司基本信息和 公司详情信息
        $db->createCommand()->query("START TRANSACTION");
        //修改公司信息
        $comsid = $db->createCommand()->update("kjy_company", $company, 'c_id=:c_id', array(':c_id' => $message['company_id']));
        if(false !== $comsid){
            $db->createCommand()->query("COMMIT"); //成功提交
        }else{
            $db->createCommand()->query("ROLLBACK"); //失败回滚
        }     
        //修改采集信息
        $update['status'] = 5;//公司id
        $db_cj = new ucserver_db();
        $db_cj->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, 'cmsforzhaopin', UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
        $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
        unset($update,$company);
        echo 'company success<br>';
        continue;
    }else{
        echo "data is null\n\n";
        //然后修改采集状态
        $update = array();
        $update['status'] = 3;//修改状态
        $db_cj->createCommand()->update('v9_collection_content',$update,'id=:id',array(':id'=>$message['id'])); 
        unset($update);
        continue;
    }
}
?>