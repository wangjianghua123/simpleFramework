<?php

/**
 * 公司信息管理
 * @author fmj
 */
!defined('IN_UC') && exit('Access Denied');

class company extends base {

    public $_uid;
    public $_uname;
    public $_company_id;
    public $_uc_usertype; //1普通用户 2企业用户
    public $_cachebasicdata;
    public $_industry;
    public $_companysize;
    public $_companynature;
    public $_tags;
    public $_citycache;
    public $_experience;
    public $_education;

    function __construct() {
        header("Content-type:text/html;charset=utf-8;");
        parent::__construct();
        $this->_uc_usertype = $this->session('uc_usertype');
        $this->_company_id = $this->session('uc_company_id');
        $this->_cachebasicdata = S('getbasicdata');
        $this->_industry = unserialize($this->_cachebasicdata["industry_classification"]); //公司行业
        $this->_companysize = unserialize($this->_cachebasicdata["company_size"]); //公司规模
        $this->_companynature = unserialize($this->_cachebasicdata["company_nature"]); //公司性质
        $this->_tags = unserialize($this->_cachebasicdata["company_tags"]); //公司标签
        $this->_citycache = S('areacache');
        $this->_experience = unserialize($this->_cachebasicdata["work_experience"]); //工作经验缓存
        $this->_education = unserialize($this->_cachebasicdata["education"]); //学历
    }

    function userlogin() {
        if (!$this->session('uc_offcn_uid')) {
            IS_AJAX && ajaxReturns(0, '请重新登录后操作', 0);
            $this->redirect($this->url('login', '', 'foreuser'));
        } else {
            $this->_uid = $this->session('uc_offcn_uid');
            $this->_uname = $this->session('uc_offcn_username');
        }
    }

    /*
     * 判断是否已经绑定公司
     */

    function BindCompany() {
        if ($this->_uc_usertype == 2 && $this->_company_id == 0) {
            IS_AJAX && ajaxReturns(0, '请绑定公司后进行操作', 0);
            ShowMsg("请绑定公司后进行操作", $this->url('writeusertocompany', '', 'userforcompany'));
            die;
        }
    }

    /**
     * 公司logo图上传
     */
    function actionimgupload() {
        $this->userlogin();
        $this->BindCompany();
        if (empty($_FILES)) {
            ajaxReturns(0, '没有文件了', 0, 0, 'jsons');
        }
        $request = new grequest();
        $cid = $request->getParam('cid') ? (int) $request->getParam('cid') : 0; //公司编号
        $fromaction = 'company';
        $fromtype = $request->getParam('fromtype') ? htmlspecialchars($request->getParam('fromtype')) : "";
        $imgtype = 1;
        $imguse = $fromaction . "/" . $fromtype . "/"; //图片用途
        $time = date("Ymd");
        $type = array("jpg", "jpeg", "png", "gif"); //设置允许上传文件的类型
        $file = $_FILES["logoupload"];
        $pinfo = pathinfo($file["name"]);
        //判断文件类型   
        if (!in_array(strtolower($pinfo["extension"]), $type)) {
            $text = implode(",", $type);
            $error = "您只能上传以下类型文件: " . $text;
            ajaxReturns(0, $error, 0, 0, 'jsons');
        }
        $picname = $file['name'];
        $picsize = $file['size'];
        if ($picname != "") {
            if ($picsize > 10240000) {
                ajaxReturns(0, '图片大小不能超过10M', 0, 0, 'jsons');
            }
            $rand = rand(100, 999);
            //生成文件名称
            $pics = $time . $rand . '.' . $pinfo["extension"];
            //上传路径
            list($y, $m, $d) = explode('-', date('Y-m-d'));
            $imgpath = 'uploads/' . $imguse . $y . "/" . $m . "/" . $d;
            $file_folder = ROOT_PATH . "/" . $imgpath;
            if (!file_exists($file_folder)) {
                dir_create($file_folder);
            }
            $fullname = $file_folder . "/" . $pics;
            move_uploaded_file($file['tmp_name'], $fullname);
            $imgmodel = $this->load("uploadfile");
            //上传图片基础数据
            $filedata = array(
                "imagename" => $file["name"], //图片原名
                "imagepath" => $imgpath . "/" . $pics, //图片路径
                "imagesize" => $file["size"], //图片大小
                "imageext" => $pinfo["extension"], //图片扩展名
                "userid" => $this->_uid, //上传人id
                "username" => $this->_uname, //上传人姓名
                "uploadtime" => time(), //上传时间
                "uploadip" => get_client_ip(), //上传人IP
                "imagetype" => $imgtype
            );
            $imgid = $imgmodel->imginsert($filedata);
            $this->setsession("uc_offcn_imgid", $imgid);
            if (0 !== $cid) {
                $updatedata = array("c_logo_id" => $imgid);
                $companymodel = $this->load("company");
                $companymodel->editcompany($cid, $updatedata,1);
            }
            ajaxReturns(1, '图片上传成功', array("path" => "/" . $imgpath . "/" . $pics, "logoid" => $imgid), 0, 'jsons');
        } else {
            ajaxReturns(0, '图片上传失败', null, 0, 'jsons');
        }
    }

    /**
     * 保存公司基本信息
     */
    function actionsavecompany() {
        $this->userlogin();
        $this->BindCompany();
        $request = new grequest();
        $cid = $request->getParam('cid') ? (int) $request->getParam('cid') : 0; //公司编号
        $shortname = $request->getParam('shortname') ? htmlspecialchars(trim($request->getParam('shortname'))) : ''; //公司简称
        $website = $request->getParam('website') ? htmlspecialchars(trim($request->getParam('website'))) : ''; //公司主页
        $cominfo = $request->getParam('cominfo') ? htmlspecialchars(trim($request->getParam('cominfo'))) : ''; //公司一句话简介
        $industry = $request->getParam('industry') ? (int) $request->getParam('industry') : 0; //公司行业
        $industryname = $request->getParam('industryname') ? htmlspecialchars(trim($request->getParam('industryname'))) : ''; //公司行业名称
        $size = $request->getParam('size') ? (int) $request->getParam('size') : 0; //公司规模
        $sizename = $request->getParam('sizename') ? htmlspecialchars(trim($request->getParam('sizename'))) : ''; //公司规模名称
        $nature = $request->getParam('nature') ? (int) $request->getParam('nature') : 0; //公司性质
        $naturename = $request->getParam('naturename') ? htmlspecialchars(trim($request->getParam('naturename'))) : ''; //公司性质名称
        $logoid = $request->getParam('logoid') ? (int) $request->getParam('logoid') : 0; //公司logoid
        $type = $request->getParam('type') ? htmlspecialchars(trim($request->getParam('type'))) : ''; //类型
        if(0 == $cid){
            ajaxReturns(0, '您还没有绑定公司，请先绑定公司', 0 , 'jsons');die;
        }
        if($type == "short"){
            $sotype = 1;
            if (empty($shortname)) {
                IS_AJAX && ajaxReturns(0, '公司简称不能为空', 0);
            }
            if (empty($website)) {
                IS_AJAX && ajaxReturns(0, '公司主页不能为空', 0);
            }
            if (empty($cominfo)) {
                IS_AJAX && ajaxReturns(0, '公司一句话简介不能为空', 0);
            }
            $data = $showdata = array();
            //公司基础数据
            $data["c_short_name"] = $shortname; //公司简称
            $data["c_homepage"] = $website; //公司主页
            if ($logoid != 0) {
                $data["c_logo_id"] = $logoid; //公司logo
            }
            $data["c_intro"] = $cominfo; //公司一句话简介
            $showdata["shortname"] = $shortname;
            $showdata["website"] = $website;
            $showdata["cominfo"] = $cominfo;
        }
        if (empty($type)) {//新增
            $sotype = 0;
            if (0 === $industry) {
                IS_AJAX && ajaxReturns(0, '公司行业为空，请至少选择一项', 0);
            }
            if (0 === $size) {
                IS_AJAX && ajaxReturns(0, '公司规模为空，请至少选择一项', 0);
            }
            if (0 === $nature) {
                IS_AJAX && ajaxReturns(0, '公司性质为空，请至少选择一项', 0);
            }
            $data["c_industry"] = $industry; //公司行业
            $data["c_property"] = $nature; //公司性质
            $data["c_size"] = $size; //公司规模
            $showdata["industry"] = $industryname;
            $showdata["size"] = $sizename;
            $showdata["nature"] = $naturename;
        }
        $companymodel = $this->load("company");
//        if (0 == $cid) {
//            $data["c_add_userid"] = $this->_uid; //添加人id
//            $data["c_add_username"] = $this->_uname; //添加人姓名
//            $data["c_add_time"] = time(); //添加时间
//            $ret = $companymodel->addcompany($data);
//            $cid = $ret;
//        } else {
          $ret = $companymodel->editcompany($cid, $data,$sotype);
        //}
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '保存公司信息失败', 0);
        }
        $showdata["cid"] = $cid;
        IS_AJAX && ajaxReturns(1, '保存公司信息成功', $showdata, 0);
    }

    /**
     * 添加公司简介信息
     */
    function actiondoaddbrief() {
        $this->userlogin();
        $this->BindCompany();
        $request = new grequest();
        $cid = $request->getParam('cid') ? (int) $request->getParam('cid') : 0; //公司编号
        $bid = $request->getParam('bid') ? (int) $request->getParam('bid') : 0; //公司简介编号
        $content = $request->getParam('content') ? htmlspecialchars(trim($request->getParam('content'))) : ''; //简介内容
        if (0 === $cid) {
            IS_AJAX && ajaxReturns(0, '请先填写公司基本信息', 0);
        }
        if (empty($content)) {
            IS_AJAX && ajaxReturns(0, '公司简介内容不能为空', 0);
        }
        $data = $showdata = array();
        $data["c_id"] = $cid;
        $data["c_intro"] = $content;
        $companymodel = $this->load("company");
        $ret = ( $bid != 0 ) ? $companymodel->editbrief($bid, $data) : $companymodel->addbrief($data);
        if (0 === $ret) {
            IS_AJAX && ajaxReturns(0, '添加公司简介信息失败', 0);
        }
        $showdata["bid"] = $ret;
        $showdata["brief"] = $content;
        IS_AJAX && ajaxReturns(1, '保存公司简介信息成功', $showdata, 0);
    }

    /**
     * 修改公司信息（标签、地址）
     */
    function actiondoeditcompany() {
        $this->userlogin();
        $this->BindCompany();
        $request = new grequest();
        $cid = $request->getParam('cid') ? (int) $request->getParam('cid') : 0; //公司编号
        $type = $request->getParam('type') ? htmlspecialchars(trim($request->getParam('type'))) : ''; //类型
        $tags = ($request->getParam('tags') != "") ? htmlspecialchars(trim($request->getParam('tags'))) : '-1'; //标签id，以逗号隔开
        $addr = $request->getParam('addr') ? htmlspecialchars(trim($request->getParam('addr'))) : ''; //公司地址
        if (0 === $cid) {
            IS_AJAX && ajaxReturns(0, '请先填写公司基本信息', 0);
        }
        $data = array();
        $sotype = 0;//是否向solor中放数据0否1是
        if ($type == "tag") {
            $info = "标签";
            $sotype = 1;
            if ($tags == -1) {
                IS_AJAX && ajaxReturns(0, '公司标签为空,请至少选择一个', 0);
            }
            $data["c_tag"] = $tags;
        }
        if ($type == "addr") {
            $info = "地址";
            if (empty($addr)) {
                IS_AJAX && ajaxReturns(0, '公司地址不能为空', 0);
            }
            $data["c_addr"] = $addr;
        }
        $companymodel = $this->load("company");
        $ret = $companymodel->editcompany($cid, $data,$sotype,1);
        if (0 === $ret) {
            IS_AJAX && ajaxReturns(0, '保存公司' . $info . '信息失败', 0);
        }
        IS_AJAX && ajaxReturns(1, '保存公司' . $info . '信息成功', 0);
    }

    /**
     * 公司风采图上传
     */
    function actioncompanystyleupload() {
        $this->userlogin();
        $this->BindCompany();
        $request = new grequest();
        $cid = $request->getParam('cid') ? (int) $request->getParam('cid') : 0; //公司编号
        if (0 === $cid) {
            IS_AJAX && ajaxReturns(0, '请先填写公司信息', 0);
        }
        if (empty($_FILES)) {
            IS_AJAX && ajaxReturns(0, '没有文件了，请重新上传', 0);
        }
        $imagetype = 3; //公司风采图
        $imguse = "company/style/";
        $time = date("Ymd");
        $type = array("jpg", "jpeg", "png", "gif"); //设置允许上传文件的类型
        $file = $_FILES["styleupload"];
        $pinfo = pathinfo($file["name"]);
        //判断文件类型   
        if (!in_array(strtolower($pinfo["extension"]), $type)) {
            $text = implode(",", $type);
            $error = "您只能上传以下类型文件: " . $text;
            IS_AJAX && ajaxReturns(0, $error, 0);
        }
        $picsize = $file['size'];
        if ($picsize > 10240000) {
            IS_AJAX && ajaxReturns(0, '图片大小不能超过10M', 0);
        }
        $rand = rand(100, 999);
        //生成文件名称
        $pics = $time . $rand . '.' . $pinfo["extension"];
        //上传路径
        list($y, $m, $d) = explode('-', date('Y-m-d'));
        $imgpath = 'uploads/' . $imguse . $y . "/" . $m . "/" . $d;
        $file_folder = ROOT_PATH . '/' . $imgpath;
        if (!file_exists($file_folder)) {
            dir_create($file_folder);
        }
        $fullname = $file_folder . "/" . $pics;
        $ret = move_uploaded_file($file['tmp_name'], $fullname);
        $imgmodel = $this->load("uploadfile");
        //上传图片基础数据
        $filedata = array(
            "imagename" => $file["name"], //图片原名
            "imagepath" => $imgpath . "/" . $pics, //图片路径
            "imagesize" => $file["size"], //图片大小
            "imageext" => $pinfo["extension"], //图片扩展名
            "userid" => $this->_uid, //上传人id
            "username" => $this->_uname, //上传人姓名
            "uploadtime" => time(), //上传时间
            "uploadip" => get_client_ip(), //上传人IP
            "imagetype" => $imagetype
        );
        $imgid = $imgmodel->imginsert($filedata);
        //公司风采关系表基础数据
        $thunderdata = array(
            "c_id" => $cid, //公司id
            "c_image_id" => $imgid//风采图id
        );
        $companymodel = $this->load("company");
        $thunret = $companymodel->insertcompanystyle($thunderdata);
        $this->setsession('uc_offcn_imgid', $imgid);
        //现有公司风采图
        $thunder = $companymodel->getcompanythunderbyid($cid);
        foreach ($thunder as $tk => $tv) {
            $imgidArr[] = $tv["c_image_id"];
        }
        $styleimgArr = $imgmodel->getimagebyids($imgidArr, "id,imagepath");
        if (0 === $thunret) {
            ajaxReturns(0, '公司风采图关系添加失败', 0, 0, 'jsons');
        }
        ajaxReturns(1, '图片上传成功', array("path" => $imgpath . "/" . $pics, "imgid" => $imgid, "styleid" => $thunret, "status" => $ret, "thunder" => $styleimgArr), 0, 'jsons');
    }

    /**
     * 删除公司风采图
     */
    function actiondodelstyleimg() {
        $this->userlogin();
        $this->BindCompany();
        $request = new grequest();
        $imgid = $request->getParam('imgid') ? (int) $request->getParam('imgid') : 0; //图片id
        if (0 === $imgid) {
            IS_AJAX && ajaxReturns(0, '请选择一个公司风采图', 0);
        }
        $companymodel = $this->load("company");
        $uploadmodel = $this->load("uploadfile");
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        $ret = $companymodel->delcompanystyle($imgid);
        $imgret = $uploadmodel->imgdelete($imgid);
        if (0 === $ret) {
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚  
            IS_AJAX && ajaxReturns(0, '删除公司风采图关系失败', 0);
        }
        if (0 === $imgret) {
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚  
            IS_AJAX && ajaxReturns(0, '删除公司风采图失败', 0);
        }
        init_db()->createCommand()->query("COMMIT"); //成功提交
        IS_AJAX && ajaxReturns(1, '删除公司风采图成功', 0);
    }

    /**
     * 将风采图设为封面
     */
    function actiondosetcover() {
        $this->userlogin();
        $this->BindCompany();
        $request = new grequest();
        $tid = $request->getParam('tid') ? (int) $request->getParam('tid') : 0; //公司风采图关系id
        if (0 === tid) {
            IS_AJAX && ajaxReturns(0, '请选择一个风采图', 0);
        }
        $companymodel = $this->load("company");
        $data = array("c_image_status" => 1);
        $ret = $companymodel->editcompanystyle($tid, $data);
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '将公司风采图设为封面失败', 0);
        }
        IS_AJAX && ajaxReturns(1, '将公司风采图设为封面成功', 0);
    }

    /**
     * 预览公司信息
     */
    function actionpreview() {
        $request = new grequest();
        $cid = $request->getParam('cid') ? (int) $request->getParam('cid') : 0; //公司id
        if (0 === $cid) {
            showMsg("请选择一个公司", $this->url('index', '', 'company'), 0, 3000);
            exit();
        }
        $companymodel = $this->load("company");
        $basic = $companymodel->getcompanyinfo($cid);
        if ($basic['c_edit_time'] == 0) {
            $lastlogin = $this->getdaybytime($basic['c_add_time']);
        } else {
            $lastlogin = $this->getdaybytime($basic['c_edit_time']);
        }
        $companybindinfo = $companymodel->getcompanybindbycid($cid);
        if (empty($companybindinfo)) {
            //如果公司还未被用户绑定，则显示公司的添加时间
            $lastlogin = $this->getdaybytime($basic["c_add_time"]);
        }
        $imgmodel = $this->load("uploadfile");
        //公司logo
        if ($basic["c_logo_id"] != 0) {
            $logoimg = $imgmodel->getimagebyid(array("id" => $basic["c_logo_id"]));
        }

        //公司简介
        $introinfo = $companymodel->getcompanyintrobyid($cid);
        //公司标签
        if (!empty($basic["c_tag"])) {
            $ctagidArr = explode(",", $basic["c_tag"]);
        }
        $ctagArr = array();
        foreach ($ctagidArr as $ck => $cv) {
            if ($this->_tags[$cv]) {
                $ctagArr[] = $this->_tags[$cv];
            } else {
                $ctagArr[] = $cv; //自定义标签
            }
        }
        //公司风采图
        $thunderinfo = $companymodel->getcompanythunderbyid($cid);
        $imgidArr = array();
        foreach ($thunderinfo as $tk => $tv) {
            $imgidArr[] = $tv["c_image_id"];
        }
        $styleimgArr = $imgmodel->getimagebyids($imgidArr, "imagepath");
        //公司相关统计信息
        $dealinfo = $dealinfo = $companymodel->getcompanydealinfo(array("cid" => $cid));
        $homepage = $basic["c_homepage"];
        $preg = '|^http://|';  //正则，匹配以http://开头的字符串
        if (!preg_match($preg, $homepage)) {  //如果不能匹配
            $homepage = 'http://' . $homepage;
        }
        if($homepage == "http://"){
            $homepage = "";
        }
        $companyinfo = array(
            "logo" => $logoimg["imagepath"], //公司logo图
            "shortname" => !empty($basic["c_short_name"]) ? $basic["c_short_name"] : $basic["c_name"], //公司简称
            "verify" => $basic["c_verify_status"], //审核状态
            "intro" => $basic["c_intro"], //一句话简介
            "industry" => $basic["c_industry"], //公司行业
            "size" => $basic["c_size"], //公司规模
            "nature" => $basic["c_property"], //公司性质
            "homepage" => $homepage, //公司主页
            "brief" => $introinfo["c_intro"], //公司简介
            "tags" => $ctagArr, //标签
            "addr" => $basic["c_addr"], //公司地址
            "style" => $styleimgArr, //公司风采图
            "lastlogin" => $lastlogin//公司用户最后登录时间
        );
        $webseo = array(
            'title' => $companyinfo['shortname'] . '招聘信息_工资待遇_电话_地址-快就业网',
            'keywords' => $companyinfo['shortname'] . '招聘,[公司简称]最新招聘信息,' . $companyinfo['shortname'] . '职位招聘,' . $companyinfo['shortname'] . '求职',
            'description' => mb_substr(strip_tags($companyinfo["brief"]), 0, 50, 'utf-8')
        );
        $this->render("preview", array(
            "cid" => $cid,
            "companyinfo" => $companyinfo,
            "dealinfo" => $dealinfo,
            "_industry" => $this->_industry,
            "_size" => $this->_companysize,
            "_nature" => $this->_companynature,
            "_tags" => $this->_tags,
            "_city" => $this->_citycache,
            "_exp" => $this->_experience,
            "_edu" => $this->_education,
            "seoinfo" => $webseo
        ));
    }

    /*
     * 该公司的招聘职位
     */
    function actiongetPositions() {
        //发布招聘职位
        $request = new grequest();
        $limit = 10;
        $page = $request->getParam('page') ? $request->getParam('page') : 0; //分页 
        $cid = $request->getParam('cid') ? (int) $request->getParam('cid') : 0; //公司id
        if (0 === $cid) {
            IS_AJAX && ajaxReturns(0, "请选择一个公司", 0);
        }
        $jobmodel = $this->load("jobs");
        $searchArr = array(
            "limit"=>$limit,
            "page"=>$page,
            "c_id" => $cid,
            "sort" => 'create_time desc'
        );
        //从solr中获取职位信息
        $jobsolrs = $jobmodel->getjobslistforsolr($searchArr);
        $companyjobs = $jobsolrs['docs'];
        $total = $jobsolrs['nums'];
        $pager = $this->pageAjax('jobsearch', $total, $page, $limit, 5, $searchArr);
        $response = $this->renderPartial("jobspage", array(
            "companyjobs" => $companyjobs,
            "_city" => $this->_citycache,
            "_exp" => $this->_experience,
            "_edu" => $this->_education,
            "total" => $total,
            "pags" => $pager,
            "page" => $page
        ));
        IS_AJAX && ajaxReturns(1, $pager, $response, $total);
    }

    /**
     * 我的公司主页
     */
    function actionindex() {
        $request = new grequest();
        $this->userlogin();
        $this->BindCompany();
        $this->basevar['title'] = "我的公司主页";
        $companymodel = $this->load("company");
        //根据用户获取绑定公司
        $bindcompanyArr = $companymodel->getusertocompany($this->_uid);
        $cid = $bindcompanyArr["company_id"];
        $basic = $companymodel->getcompanyinfo($cid);
        if (!empty($basic)) {
            if ($basic['c_add_userid'] != $this->_uid) {  //判断公司是否是登陆人创建的 不是则不能修改公司信息
                $this->redirect(PUBLIC_URL."company_".$cid.".html");
            }
        }
        //公司logo
        $imgmodel = $this->load("uploadfile");
        if ($basic["c_logo_id"] != 0) {
            $logoimg = $imgmodel->getimagebyid(array("id" => $basic["c_logo_id"]));
        }
        
        //公司简介
        $introinfo = $companymodel->getcompanyintrobyid($cid);
        //公司标签
        $ctagArr = $ctagidArr = array();
        if (!empty($basic["c_tag"])) {
            $ctagidArr = explode(",", $basic["c_tag"]);
        }
        foreach ($ctagidArr as $ck => $cv) {
            if ($this->_tags[$cv]) {
                $ctagArr[] = $this->_tags[$cv];
            } else {
                $ctagArr[] = $cv; //自定义标签
            }
        }
        $thunder = $thunderinfo = array();
        //公司风采图
        $thunder = $companymodel->getcompanythunderbyid($cid);
        foreach ($thunder as $tk => $tv) {
            $imginfo = $imgmodel->getimagebyid(array("id" => $tv["c_image_id"]), "imagepath");
            if (!empty($imginfo)) {
                $tv["imagepath"] = $imginfo["imagepath"];
            }
            $thunderinfo[$tv["c_image_id"]] = $tv;
        }
        $lastlogintime = $this->getdaybytime($basic["c_edit_time"]); //企业用户最后登陆时间
        $dealinfo = $companymodel->getcompanydealinfo(array("cid" => $cid));
        $homepage = $basic["c_homepage"];
        $preg = '|^http://|';  //正则，匹配以http://开头的字符串
        if (!preg_match($preg, $homepage)) {  //如果不能匹配
            $homepage = 'http://' . $homepage;
        }
        if($homepage == "http://"){
            $homepage = "";
        }
        $companyinfo = array(
            "cid" => $cid,
            "logo" => $logoimg["imagepath"], //公司logo图
            "logoid" => $basic["c_logo_id"],
            "name" => $basic["c_name"],
            "companyshortname" => !empty($basic["c_short_name"]) ? $basic["c_short_name"] : $basic["c_name"], //公司简称,
            "verify" => $basic["c_verify_status"], //审核状态
            "intro" => $basic["c_intro"], //一句话简介
            "industry" => $basic["c_industry"], //公司行业
            "size" => $basic["c_size"], //公司规模
            "nature" => $basic["c_property"], //公司性质
            "homepage" => $homepage, //公司主页
            "brief" => $introinfo["c_intro"], //公司简介
            "briefid" => $introinfo["id"], //公司简介id
            "tags" => $ctagArr, //标签
            "addr" => $basic["c_addr"], //公司地址
            "lastlogin" => $lastlogintime//用户最新登录时间
        );
        $webseo = array(
            'title' => $companyinfo['companyshortname'] . '-快就业网',
            'keywords' => $companyinfo['companyshortname'] . '招聘,' . $companyinfo['companyshortname'] . '最新招聘信息,' . $companyinfo['companyshortname'] . '职位招聘,' . $companyinfo['companyshortname'] . '求职',
            'description' => mb_substr(strip_tags($companyinfo["brief"]), 0, 50, 'utf-8')
        );
        $this->render('index', array(
            "companyinfo" => $companyinfo,
            "thunderinfo" => $thunderinfo,
            "dealinfo" => $dealinfo,
            "_industry" => $this->_industry,
            "_size" => $this->_companysize,
            "_nature" => $this->_companynature,
            "_tags" => $this->_tags,
            "_city" => $this->_citycache,
            "_exp" => $this->_experience,
            "_edu" => $this->_education,
            "seoinfo" => $webseo
        ));
    }

    /**
     * 根据时间戳转换
     * @param type $time
     * @return type
     */
    function getdaybytime($time) {
        //获取今天凌晨的时间戳
        $day = strtotime(date('Y-m-d', time()));
        //获取昨天凌晨的时间戳
        $yesterday = strtotime(date('Y-m-d', strtotime('-1 day')));
        //获取前天凌晨的时间戳
        $beforeday = strtotime(date('Y-m-d', strtotime('-2 day')));

        if ($time > $yesterday) {
            $str = "今天";
        } elseif ($time < $day && $time >= $yesterday) {
            $str = "昨天";
        } elseif ($time < $yesterday && $time >= $beforeday) {
            $str = "前天";
        } else {
            $str = ($time != 0) ? date("Y-m-d", $time) : 0;
        }
        return $str;
    }

}

?>
