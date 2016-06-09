<?php

!defined('IN_UC') && exit('Access Denied');
session_cache_limiter('public');

class home extends base {
    public $homeSearchCache;
    public $homeSearch = array(
//        array(
//            'id'=>1,
//            'pid'=>0,
//            'name'=>'IT类职位',
//            'child'=>array(
//                array(
//                    'id'=>10,
//                    'pid'=>1,
//                    'name'=>'技术',
//                    'child'=>array(
//                        array('id'=>1001,'pid'=>10,'name'=>'JAVA'),
//                        array('id'=>1002,'pid'=>10,'name'=>'PHP'),
//                        array('id'=>1003,'pid'=>10,'name'=>'C'),
//                        array('id'=>1004,'pid'=>10,'name'=>'Android'),
//                        array('id'=>1005,'pid'=>10,'name'=>'iOS'),
//                        array('id'=>1006,'pid'=>10,'name'=>'移动端'),
//                        array('id'=>1007,'pid'=>10,'name'=>'运维'),
//                        array('id'=>1008,'pid'=>10,'name'=>'测试'),
//                        array('id'=>1009,'pid'=>10,'name'=>'网络安全'),
//                        array('id'=>1010,'pid'=>10,'name'=>'ERP'),
//                        array('id'=>1011,'pid'=>10,'name'=>'算法'),
//                        array('id'=>1012,'pid'=>10,'name'=>'游戏'),
//                    ),
//                ),
//                array(
//                    'id'=>11,
//                    'pid'=>1,
//                    'name'=>'设计',
//                    'child'=>array(
//                        array('id'=>1101,'pid'=>11,'name'=>'网页'),
//                        array('id'=>1102,'pid'=>11,'name'=>'Flash'),
//                        array('id'=>1103,'pid'=>11,'name'=>'APP'),
//                        array('id'=>1104,'pid'=>11,'name'=>'UI'),
//                        array('id'=>1105,'pid'=>11,'name'=>'平面'),
//                        array('id'=>1106,'pid'=>11,'name'=>'交互'),
//                        array('id'=>1107,'pid'=>11,'name'=>'美工')
//                    )
//                ),
//                array(
//                    'id'=>12,
//                    'pid'=>1,
//                    'name'=>'运营',
//                    'child'=>array(
//                        array('id'=>1201,'pid'=>12,'name'=>'SEO'),
//                        array('id'=>1202,'pid'=>12,'name'=>'SEM'),
//                        array('id'=>1203,'pid'=>12,'name'=>'网络推广'),
//                        array('id'=>1204,'pid'=>12,'name'=>'网站编辑'),
//                        array('id'=>1205,'pid'=>12,'name'=>'网店运营'),
//                        array('id'=>1206,'pid'=>12,'name'=>'淘宝客服'),
//                        
//                    )
//                )
//            ),
//        ),
        array(
            'id'=>2,
            'pid'=>0,
            'name'=>'财务类职位',
            'child'=>array(
                array(
                    'id'=>30,
                    'pid'=>3,
                    'name'=>'财务',
                    'child'=>array(
                        array('id'=>3001,'pid'=>30,'name'=>'会计'),
                        array('id'=>3002,'pid'=>30,'name'=>'出纳'),
                        array('id'=>3003,'pid'=>30,'name'=>'结算'),
                        array('id'=>3004,'pid'=>30,'name'=>'税务'),
                        array('id'=>3005,'pid'=>30,'name'=>'审计'),
                        array('id'=>3005,'pid'=>30,'name'=>'风控')
                    )
                ),
            )
        ),
        array(
            'id'=>3,
            'pid'=>0,
            'name'=>'市场类职位',
            'child'=>array(
                array(
                    'id'=>20,
                    'pid'=>2,
                    'name'=>'市场',
                    'child'=>array(
                        array('id'=>2001,'pid'=>20,'name'=>'策划'),
                        array('id'=>2001,'pid'=>20,'name'=>'创意'),
                        array('id'=>2001,'pid'=>20,'name'=>'市场推广'),
                        array('id'=>2001,'pid'=>20,'name'=>'数据分析')
                    )
                ),
                array(
                    'id'=>21,
                    'pid'=>2,
                    'name'=>'销售',
                    'child'=>array(
                        array('id'=>2101,'pid'=>21,'name'=>'销售专员'),
                        array('id'=>2102,'pid'=>21,'name'=>'课程顾问'),
                        array('id'=>2103,'pid'=>21,'name'=>'网络销售'),
                        array('id'=>2104,'pid'=>21,'name'=>'咨询'),
                        array('id'=>2105,'pid'=>21,'name'=>'销售代表')
                    )
                ),
                array(
                    'id'=>22,
                    'pid'=>2,
                    'name'=>'渠道',
                    'child'=>array(
                        array('id'=>2201,'pid'=>22,'name'=>'拓展'),
                        array('id'=>2202,'pid'=>22,'name'=>'淘宝客服'),
                        array('id'=>2203,'pid'=>22,'name'=>'客户代表'),
                        array('id'=>2204,'pid'=>22,'name'=>'店面'),
                        array('id'=>2205,'pid'=>22,'name'=>'代理')
                    )
                ),
                array(
                    'id'=>23,
                    'pid'=>2,
                    'name'=>'公关',
                    'child'=>array(
                        array('id'=>2301,'pid'=>23,'name'=>'媒介'),
                        array('id'=>2302,'pid'=>23,'name'=>'广告'),
                        array('id'=>2303,'pid'=>23,'name'=>'品牌')
                    )
                ),
            )
        ),
        array(
            'id'=>4,
            'pid'=>0,
            'name'=>'人力类职位',
            'child'=>array(
                array(
                    'id'=>40,
                    'pid'=>4,
                    'name'=>'人力',
                    'child'=>array(
                        array('id'=>4001,'pid'=>40,'name'=>'薪资福利'),
                        array('id'=>4002,'pid'=>40,'name'=>'绩效考核'),
                        array('id'=>4003,'pid'=>40,'name'=>'员工招聘'),
                        array('id'=>4004,'pid'=>40,'name'=>'员工关系')
                    )
                ),
            )
        ),
        array(
            'id'=>5,
            'pid'=>0,
            'name'=>'行政类职位',
            'child'=>array(
                array(
                    'id'=>50,
                    'pid'=>5,
                    'name'=>'行政',
                    'child'=>array(
                        array('id'=>5001,'pid'=>50,'name'=>'前台'),
                        array('id'=>5002,'pid'=>50,'name'=>'行政助理'),
                        array('id'=>5003,'pid'=>50,'name'=>'文秘'),
                        array('id'=>5004,'pid'=>50,'name'=>'采购')
                    )
                ),
            )
        ),
        array(
            'id'=>6,
            'pid'=>0,
            'name'=>'管培生职位',
            'child'=>array(
                array(
                    'id'=>60,
                    'pid'=>6,
                    'name'=>'管培生',
                    'child'=>array(
                        array('id'=>6001,'pid'=>60,'name'=>'管理培训生'),
                        array('id'=>6002,'pid'=>60,'name'=>'培训生'),
                        array('id'=>6003,'pid'=>60,'name'=>'储备经理'),
                        array('id'=>6004,'pid'=>60,'name'=>'储备干部')
                    )
                ),
            )
        )
    );
    function __construct() {
        parent::__construct();
        $this->homeSearchCache = S('homesearchcache');
        if(empty($this->homeSearchCache)){
            $caches['type'] = UC_CACHE_TYPE;
            $caches['expire'] = CACHE_SAVE_MAX_TIME;
            S('homesearchcache', $this->homeSearch, $caches);
            $this->homeSearchCache = S('homesearchcache');
        }
    }

    function actionindex() {
        $jobmodel = $this->load("jobs");
        $company = $this->load('company');
        $getbasicdata = S('getbasicdata');
        //获取用户登陆状态
        $uid = $this->session('uc_offcn_uid') ? $this->session('uc_offcn_uid') : 0;
        //获取精选职位
        $jingxuan = $jobmodel->getjingxuanjobs();
        foreach ($jingxuan as $jk => $jv) {
            $jingxuan[$jk]["typejob_name"] = $this->strCut($jv["typejob_name"], 30);
        }
        //根据职位信息获取对应的公司id串
        $jobcompanyids = array();
        foreach ($jingxuan as $jk => $jv) {
            $jobcompanyids[] = $jv['company_id'];
        }
        //获取最新职位
        $zuixin = $jobmodel->getzuixinjobs();
        foreach ($zuixin as $zk => $zv) {
            $jobcompanyids[] = $zv['company_id'];
            $zuixin[$zk]["typejob_name"] = $this->strCut($zv["typejob_name"], 30);
        }
        $jcids = array_unique($jobcompanyids);

        //根据ids获取公司信息--用于职位列表中的公司属性
        $forcompany = $company->getsolrlistbyids($jcids);
        $clogoids = array();
        $companylist = array();
        foreach ($forcompany as $ck => $cv) {
            $tags = explode(',', $cv['c_tag']);
            $cv['c_tag'] = $tags;
            $cv['c_short_name'] = $this->strCut($cv['c_short_name'], 30);
            $companylist[$cv['c_id']] = $cv;
            $clogoids[] = $cv['c_logo_id'];
        }
        //根据ids获取公司logo
        $logo = $company->getlogobyids($clogoids, 1);
        $logolist = array();
        foreach ($logo as $lk => $lv) {
            $logolist[$lv['id']] = $lv;
        }

        //获取筛选用地区
        $area = S('areacache');
        $city = array();
        foreach ($area as $ka => $va) {
            $city[$va['areaid']] = $va['name'];
        }
        $arealist = S('arealistcache');
        $province = S('provincelistcache');
        $codearray = array(
            1 => '求职最热门地区',
            2 => '华东、华中地区',
            3 => '东北、华北地区',
            4 => '西南、东南地区',
            5 => '西部、西北地区',
        );

        $work_experience = unserialize($getbasicdata['work_experience']); //工作经验
        $development_stage = unserialize($getbasicdata['development_stage']);   //发展阶段
        $classification = unserialize($getbasicdata['industry_classification']); //行业领域
        $company_tags = unserialize($getbasicdata['company_tags']);   //公司标签
        $company_nature = unserialize($getbasicdata['company_nature']); //公司性质
        $education = unserialize($getbasicdata['education']); //学历
        $webseo = array(
            'title' => '快就业-人才招聘快_求职面试快_网上找工作，上快就业',
            'keywords' => '人才招聘，求职招聘，网上找工作，最新招聘信息，快就业，快就业人才网',
            'description' => '【快就业人才招聘网kjiuye.com】提供最新真实人才求职与企业招聘信息，如互联网、通信、能源、金融、教育、医疗等。企业人才招聘、个人求职面试、网上找工作，上快就业。'
        );
        $this->render('index', array(
            'adverts_t' => $this->adverts_t,
            'adverts_j' => $this->adverts_j,
            'adverts_f' => $this->adverts_f,
            'adverts_r' => $this->adverts_r,
            'adverts_rend' => $this->adverts_rend,
            'uid' => $uid,
            'jingxuan' => $jingxuan,
            'zuixin' => $zuixin,
            'companylist' => $companylist,
            'logolist' => $logolist,
            'work_experience' => $work_experience,
            'development_stage' => $development_stage,
            'classification' => $classification,
            'company_tags' => $company_tags,
            'province' => $province,
            'arealist' => $arealist,
            'city' => $city,
            'code' => $codearray,
            'companynature' => $company_nature,
            'education' => $education,
            'seoinfo' => $webseo,
            'homeSearch'=>$this->homeSearchCache
        ));
    }

    function strCut($str, $length) {
        $newstr = mb_strimwidth($str, 0, $length, "...", 'utf-8');
        return $newstr;
    }

}

?>