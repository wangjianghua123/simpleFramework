<?php

/**
 * 简历管理
 */
!defined('IN_UC') && exit('Access Denied');

class resume extends base {
    public $_uid;
    public $_company_id;
    public $_uname;
    public $_cachebasicdata;
    public $_citydata;
    public $_experience;
    public $_education;
    public $_typejobdata;
    public $_typejobcache;
    public $_industry;
    public $_citytreedata;
    public $_salary;
    public $_jobnature;
    public $_arrival;
    public $_citycache;
    public $_usertype;
    public $webseo;

    function __construct() {
        parent::__construct();
        $this->_cachebasicdata = S('getbasicdata');
        $this->_citydata = S('areacache');
        $this->_uid = $this->session('uc_offcn_uid');
        $this->_company_id = $this->session('uc_company_id');
        $this->_uname = $this->session('uc_offcn_username');
        $this->_typejobdata = S('typejob_list'); //职位
        $this->_typejobcache = S('typejob');
        $this->_experience = unserialize($this->_cachebasicdata["work_experience"]);//工作经验缓存
        $this->_education = unserialize($this->_cachebasicdata["education"]);//学历
        $this->_industry = unserialize($this->_cachebasicdata["industry_classification"]);
        $this->_citytreedata = S('areatree');
        $this->_citycache = S('areacache');
        $this->_salary = unserialize($this->_cachebasicdata["expected_salary"]);
        $this->_jobnature = unserialize($this->_cachebasicdata["job_nature"]);
        $this->_arrival = unserialize($this->_cachebasicdata["arrival_time"]);
        $this->_usertype = $this->session('uc_usertype');
        $this->webseo = array(
            'title' => '我的简历库',
            'keywords' => '就业,求职,招聘,公司招聘,高薪职位,大学生就业',
            'description' => '找好工作,来快就业网'
        );
    }
    
    function userlogin(){
        if (!$this->session('uc_offcn_uid')) {
            IS_AJAX && ajaxReturns(0, '请重新登录后操作', 0);
            $this->redirect($this->url('login', '', 'foreuser'));
        } else {
            $this->_uid = $this->session('uc_offcn_uid');
            $this->_uname = $this->session('uc_offcn_username');
        }
    }
    
    /**
     * 查看用户身份1普通用户2企业用户
     */
    function checkusertype(){
        if($this->_usertype == 2){//只有普通用户才能访问
            ShowMsg("出错了，该链接不存在或您没有访问该链接的权限！", -1);
            die;
        }
    }

    /**
     * 我的简历库
     */
    function actionindex() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $page = $request->getParam('page') ? $request->getParam('page') : 0; //分页 
        $resumemodel = $this->load('resume');
        $jobsmodel = $this->load('jobs');
        $limit = 12;
        $list = $jobidArr = $jobArr = $jobinfoArr = array();
        $where['uid'] = $this->_uid;
        $total = $resumemodel->getresumetotal($where);
        $list = $resumemodel->getresumelist($limit, ($page - 1) * $limit, $where);
        if (!empty($list)) {
            $pager = $this->page($total, $page, $limit, 5, array('u_id' => $this->_uid, 'd_status' => 1));
        }
        $this->render('index', array(
            'list' => $list, 
            'total' => $total, 
            'pags' => $pager,
            'seoinfo'=>$this->webseo
        ));
    }

    /**
     * 刷新简历
     */
    function actionrefresh() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $id = $request->getParam('id') ? (int) $request->getParam('id') : 0; //id
        if (0 === $id) {
            IS_AJAX && ajaxReturns(0, '简历id为空', 0);
        }
        $resumemodel = $this->load('resume');
        $ret = $resumemodel->refresh_resume($id);
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '每天只能刷新一次', 0);
        } else {
            IS_AJAX && ajaxReturns(1, '刷新成功', $ret);
        }
    }

    /**
     * 设置默认简历
     */
    function actiondefault() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $id = $request->getParam('id') ? (int) $request->getParam('id') : 0; //id
        if ( 0 === $id) {
            IS_AJAX && ajaxReturns(0, '简历id为空', 0);
        }
        $resumemodel = $this->load('resume');
        $ret = $resumemodel->default_resume($this->_uid, $id);
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '设置失败', 0);
        } else {
            IS_AJAX && ajaxReturns(1, '设置成功', 0);
        }
    }

    /**
     * 复制简历
     */
    function actioncopyresume() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $id = $request->getParam('id') ? (int) $request->getParam('id') : 0; //id
        if ( 0 === $id) {
            IS_AJAX && ajaxReturns(0, '简历id为空', 0);
        }
        $resumemodel = $this->load('resume');
        $where['uid'] = $this->_uid;
        $where['status'] = 1;
        $total = $resumemodel->getresumetotal($where);
        if($total >= 6){
            IS_AJAX && ajaxReturns(0, '您的简历数量已超过上限,无法再添加。', 0);
        }
        init_db()->createCommand()->query("START TRANSACTION");//创建事务处理
        $newrid = $resumemodel->copy_resume($id);//复制简历基本信息
        if($newrid == false){
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0, '复制失败', 0);
        }
        $ret_active = $resumemodel->copy_resumeactive($id,$newrid);//复制简历在校经历
        $ret_display = $resumemodel->copy_resumedisplay($id,$newrid);//复制简历作品展示
        $ret_education = $resumemodel->copy_resumeeducation($id,$newrid);//复制简历教育经历
        $ret_intro = $resumemodel->copy_resumeintro($id,$newrid);//复制简历详情信息（职业技能，荣誉奖励，自我评价等）
        $ret_work = $resumemodel->copy_resumework($id,$newrid);//复制简历工作经历
        if($ret_active != false && $ret_display != false && $ret_education != false && $ret_intro != false && $ret_work != false){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1, '复制成功', 0);
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0, '复制失败1', 0);
        }
    }

    /**
     * 删除简历
     */
    function actiondelresume() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $id = $request->getParam('id') ? (int) $request->getParam('id') : 0; //id
        if ( 0 === $id) {
            IS_AJAX && ajaxReturns(0, '简历id为空', 0);
        }
        $resumemodel = $this->load('resume');
        $resumeinfo = $resumemodel->getresumebyarr(array("rid"=>$id));
        if($resumeinfo["u_id"]!== $this->_uid){
            ShowMsg("您没有权限删除此简历",'',0,3000);exit();
        }
        $where = array(
            "uid"=>$this->_uid,
            "rid"=>$id
        );
        init_db()->createCommand()->query("START TRANSACTION");
        $resumeactive = $resumemodel->getresumeactivebyrid($where);
        if(!empty($resumeactive)){
            $activeret = $resumemodel->deleteactivebyrid($id);//删除简历相关表-在校经历
        }else{
            $activeret = true;
        }
        $resumeedu = $resumemodel->getresumeedubyrid($where);
        if(!empty($resumeedu)){
            $eduret = $resumemodel->deleteedubyrid($id);//删除简历相关表-教育经历
        }else{
            $eduret = true;
        }
        $resumeintro = $resumemodel->getresumeintrobyrid($where);
        if(!empty($resumeintro)){
            $introret = $resumemodel->deleteintrobyrid($id);//删除简历相关表-简历详情
        }else{
            $introret = true;
        }
        $resumework = $resumemodel->getresumeworkbyrid($where);
        if(!empty($resumework)){
            $workret = $resumemodel->deleteworkbyrid($id);//删除简历相关表-社会实践
        }else{
            $workret = true;
        }
        $ret = $resumemodel->delete_resume($id);//删除简历基本信息
        //删除简历的同时删除该简历投递记录 投递日志 消息队列
        $deliverymodel = $this->load("delivery");
        $delivery = $deliverymodel->getdeliveryinfobyrid($id,"id");
        if(!empty($delivery)){
            //存在已投递记录
            foreach ($delivery as $dk => $dv){
                $delogret = $deliverymodel->deletedeliverylogbytdid($dv["id"]);//删除简历投递日志
                $deret = $deliverymodel->deletedeliverybydid($dv["id"]);//删除简历投递基本信息
                $msgret = $deliverymodel->deletemsgquebydid($dv["id"]);//删除简历投递消息队列
            }
        }else{
            $delogret = $deret = $msgret = true;
        }
        if(false !== $activeret && false !== $eduret && false !== $introret && false !== $workret && false !== $ret &&  false !== $deret && false !== $delogret && false !== $msgret){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'操作成功,该简历已删除',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'操作失败',0); 
        }
    }

    /**
     * 查看已投递简历状态
     */
    function actiondelivery() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $sort = $request->getParam('sort') ? (int) $request->getParam('sort') : 1; //排序方式
        $page = $request->getParam('page') ? $request->getParam('page') : 0; //分页 
        $jobsmodel = $this->load('jobs');
        $list = $companyidArr = $companyArr = $jobidArr = $jobArr = $jobinfoArr = $conarr = $castinfo = array();
        $limit = 12;
        $where['uid'] = $this->_uid;
        $where['auto_status'] = 0;//手动投递
        $total = $jobsmodel->getdeliverytotal($where);
        $list = $jobsmodel->getdeliverylist($limit, ($page - 1) * $limit, $where, $sort);
        if (!empty($list)) {
            $pager = $this->page($total, $page, $limit, 5, array('u_id' => $this->_uid, 'sort' => $sort,'auto_status'=>0));
            foreach ($list as $k => $v) {
                $jobidArr[$v['job_id']] = true;
                $companyidArr[$v['company_id']] = true;
            }
            $jobidArr = array_keys($jobidArr);
            $companyidArr = array_keys($companyidArr);
            $jobArr = $jobsmodel->getjobinfo($jobidArr, 'job_id,typejob_name,city');
            foreach ($jobArr as $k => $v) {
                $jobinfoArr[$v['job_id']] = $v;
            }
            $companyArr = $jobsmodel->getcompanyById($companyidArr, 'c_id,c_name');
            $companyinfoArr = array();
            foreach ($companyArr as $k => $v) {
                $companyinfoArr[$v['c_id']] = $v['c_name'];
            }
        }
        //已投递简历状态
        $resumemodel = $this->load("resume");
        $castinfo = $resumemodel->getresumecast(array("uid"=>$this->_uid));
        $this->webseo['title'] = '查看已投简历状态';
        $this->render('delivery', array(
            'list' => $list, 
            'total' => $total, 
            'pags' => $pager, 
            'job' => $jobinfoArr, 
            'company' => $companyinfoArr, 
            'sort' => $sort, 
            'city' => $this->_citydata,
            'castinfo'=>$castinfo,
            'seoinfo'=>$this->webseo
        ));
    }

    /**
     * 职位收藏夹
     */
    function actioncollections() {
        $this->userlogin();
        $this->checkusertype();
        $this->basevar['title'] = "我的简历库";
        $request = new grequest();
        $jobsmodel = $this->load('jobs');
        $list = $jobidArr = $companyidArr = $companyArr = $companyinfoArr = $jobArr = $jobinfoArr = $logoidArr = $logoArr = $logoinfoArr = $conarr = array();
        $page = $request->getParam('page') ? $request->getParam('page') : 0; //分页 
        $limit = 12;
        $where['uid'] = $this->_uid;
        $where['status'] = 0;
        $total = $jobsmodel->getcollectiontotal($where);
        $list = $jobsmodel->getcollectionlist($limit, ($page - 1) * $limit, $where);
        if (!empty($list)) {
            $pager = $this->page($total, $page, $limit, 5, $where);
            foreach ($list as $k => $v) {
                $jobidArr[$v['job_id']] = true;
                $list[$k]['is_delivery'] = $jobsmodel->userdetailforjob(array('u_id'=>$this->_uid,'job_id'=>$v['job_id']));
            }
            $jobidArr = array_keys($jobidArr);
            //根据职位编号获取职位相关信息
            $jobArr = $jobsmodel->getjobinfo($jobidArr, 'job_id,company_id,typejob_name,salary_start,salary_end,city,experience,education,position_temptation,create_time');
            //组合职位信息 键:职位编号 值:职位相关信息
            foreach ($jobArr as $k => $v) {
                $jobinfoArr[$v['job_id']] = $v;
                $companyidArr[$v['company_id']] = true;
            }
            $companyidArr = array_keys($companyidArr);
            //根据职位编号获取公司相关信息
            $companyArr = $jobsmodel->getcompanyById($companyidArr, 'c_id,c_name,c_logo_id');
            //组合职位信息 键:职位编号 值:公司相关信息
            foreach ($companyArr as $k => $v) {
                $companyinfoArr[$v['c_id']] = $v;
                $logoidArr[$v['c_logo_id']] = true;
            }
            $logoidArr = array_keys($logoidArr);
            //根据logoid获取图片信息
            $logoArr = $jobsmodel->getlogoById($logoidArr, 'id,imagepath');
            //组合logo信息 键:职位编号 值:logo相关信息
            foreach ($logoArr as $k => $v) {
                $logoinfoArr[$v['id']] = $v;
            }
        }
        $this->webseo['title'] = '职位收藏夹';
        $this->render('collections', array(
            'list' => $list, 
            'total' => $total, 
            'pags' => $pager, 
            'job' => $jobinfoArr, 
            'company' => $companyinfoArr, 
            'logo' => $logoinfoArr, 
            'work' => $this->_experience, 
            'edu' => $this->_education, 
            'city' => $this->_citydata,
            '_uid'=>$this->_uid,
            'seoinfo'=>$this->webseo
        ));
    }

    /**
     * 取消收藏职位
     */
    function actioncancelcollect() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $jid = $request->getParam('jid'); //职位编号
        if (empty($jid)) {
            IS_AJAX && ajaxReturns(0, '职位id为空', 0);
        }
        $jobmodel = $this->load('jobs');
        $ret = $jobmodel->cancel_collections($jid);
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '取消收藏失败', 0);
        } else {
            IS_AJAX && ajaxReturns(1, '取消收藏成功', 0);
        }
    }

    /**
     * 在线填写简历
     */
    function actiononline() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $jtid = ($request->getParam("jtid")!="") ? (int) $request->getParam("jtid") : -1;
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;
        $type = "";
        if($jtid== -1 && $rid!=0){
            $type = "edit";
        }
        $resumemodel = $this->load("resume");
        $imgmodel = $this->load("uploadfile");
        $userArr = $userinfo = $templateArr = $expArr = $eduinfo = $introinfo = $activeinfo = $workinfo = $secondtypejob = array();
        if($rid!=0){
            $where = array(
                "uid"=>$this->_uid,
                "rid"=>$rid
            );
            $expArr = $resumemodel->getresumebyarr($where,"*");
            if(!empty($expArr)){
                if($expArr["u_id"]!== $this->_uid){
                    ShowMsg("您没有权限查看此简历",'',0,3000);exit();
                }
                if($expArr['r_status'] == 1){//如果为附件简历，则跳转至附件简历页面
                    $this->redirect(PUBLIC_URL . "resume_a_" . $rid . ".html");
                }
                $basicinfo["id"] = $expArr["id"];
                $basicinfo["jobid"] = $expArr["u_jobid"];//期望职位id
                $basicinfo["jobname"] = $expArr["u_jobname"];//求职意向
                $basicinfo["industry"] = $expArr["u_industry"];//期望行业
                $basicinfo["city"] = $expArr["u_city"];//期望城市
                $basicinfo["salary"] = $expArr["u_salary"];//期望薪资
                $basicinfo["nature"] = $expArr["u_job_type"];//工作类型
                $basicinfo["remarks"] = $expArr["u_addition_remarks"];//补充说明
                $basicinfo["arrival"] = $expArr["arrival_id"];
                $basicinfo["updatetime"] = $expArr["update_time"];
            }
            //教育经历信息
            $eduinfo = $resumemodel->getresumeedubyrid(array("rid"=>$rid,"status"=>1),"*",5);
            
            //获奖经历、证书技能、特长兴趣、自我评价
            $introinfo = $resumemodel->getresumeintrobyrid(array("rid"=>$rid));

            //在校经历
            $activeinfo = $resumemodel->getresumeactivebyrid(array("rid"=>$rid,"status"=>1),"*",5);

            //工作经历
            $workinfo = $resumemodel->getresumeworkbyrid(array("rid"=>$rid,"status"=>1),"*",5);
            
            //作品展示
            $displayinfo = $resumemodel->getresumedisplaybyrid(array("rid"=>$rid),10);
            if(!empty($displayinfo)){
                $displayonline = $displayupload = array();
                foreach ($displayinfo as $dk => $dv){
                    if($dv["w_type"]==1){
                        $imgArr = $imgmodel->getimagebyid(array('id'=>$dv["w_image_id"]));
                        $dv["imgpath"] = $imgArr["imagepath"];
                        $displayupload[] = $dv;
                    }elseif($dv["w_type"]==2){
                        $displayonline[] = $dv;
                    }
                }
            }
        }
        //获取用户基本信息
        $usermodel = $this->load("foreuser");
        $userArr = $usermodel->getuserinfobyuid($this->_uid);
        if(!empty($userArr)){
            $userinfo["name"] = $userArr["realname"];//用户姓名
            $userinfo["gender"] = $userArr["sex"];//性别
            $userinfo["birth"] = ($userArr["birth_year"]!="" && $userArr["birth_month"]) ? $userArr["birth_year"]."/".$userArr["birth_month"] : "";//出生年月
            $userinfo["education"] = $userArr["education"];//学历
            $userinfo["address"] = $userArr["address"];//现居住地
            $userinfo["workexp"] = $userArr["workexp"];//工作经验
            $userinfo["phone"] = $userArr["phone"];//电话
            $userinfo["email"] = $userArr["email"];//邮箱
            $userinfo["phoneverify"] = $userArr["phone_validate"];//手机验证状态
            $userinfo["emailverify"] = $userArr["is_verify"];//邮箱验证状态
            $userinfo["logoid"] = $userArr["logoid"];//用户头像id
            if(0 != $userinfo["logoid"]){
                $imgArr = $imgmodel->getimagebyid(array('id'=>$userinfo["logoid"]));
                $userinfo["logo"] = $imgArr["imagepath"];//用户头像
            }
        }
        $this->webseo['title'] = '在线简历';
        $this->render('online', array(
            'userinfo'=>$userinfo,
            'rid'=>$rid,
            'jtid'=>$jtid,
            'basicinfo'=>$basicinfo,
            'typejobinfo'=>$this->_typejobcache,
            'eduinfo'=>$eduinfo,
            'introinfo'=>$introinfo,
            'activeinfo'=>$activeinfo,
            'workinfo'=>$workinfo,
            'type'=>$type,
            'displayupload'=>$displayupload,
            'displayonline'=>$displayonline,
            '_edu' => $this->_education,
            '_exp' => $this->_experience,
            '_typejobdata' => $this->_typejobdata,
            '_industry' => $this->_industry,
            '_city' => $this->_citytreedata,
            '_citycache'=>$this->_citycache,
            '_salary' => $this->_salary,
            '_jobnature' => $this->_jobnature,
            '_arrival'=>$this->_arrival,
            'seoinfo'=>$this->webseo
        ));
    }
    
    /**
     * 获取简历模板信息
     */
    function actiongettemplate(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $tid = $request->getParam("tid") ? (int) $request->getParam("tid") : 0;
        if(0 === $tid){
            IS_AJAX && ajaxReturns(0, '请选择一个职位类别', 0);
        }
        if($tid!=1){
            IS_AJAX && ajaxReturns(0,'未找到符合条件的模板',0);
        }
//        $resumemodel = $this->load("resume");
//        $where = array(
//            "tid"=>$tid
//        );
        //$templateinfo = $resumemodel->gettemplateinfo($where);
        $rediscache = $this->init_rediscaches();
        $rediscache->select('1');
        $templateinfotmp = $rediscache->hget('resume_template', 'rt_'.$tid);
        $templateinfo = json_decode($templateinfotmp,true);
        if(empty($templateinfo)){
            IS_AJAX && ajaxReturns(0,'未找到符合条件的模板',0);
        }
        IS_AJAX && ajaxReturns(1,'找到符合条件的模板', $templateinfo, 0);
    }

    /**
     * 保存简历基本信息
     */
    function actionaddbasic() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $resume_id = $request->getParam("rid") ? (int) $request->getParam("rid") : 0; //简历编号
        $name = $request->getParam("name") ? htmlspecialchars(trim($request->getParam("name"))) : ''; //姓名
        $phone = $request->getParam("phone") ? htmlspecialchars(trim($request->getParam("phone"))) : ''; //电话
        $email = $request->getParam("email") ? htmlspecialchars(trim($request->getParam("email"))) : ''; //邮箱
        $birth = $request->getParam("birth") ? htmlspecialchars(trim($request->getParam("birth"))) : ''; //出生年月
        $gender = $request->getParam("gender") ? (int) $request->getParam("gender") : 0; //性别
        $logoid = $request->getParam("logoid") ? (int) $request->getParam("logoid") : 0; //头像id
        $education = $request->getParam("education") ? (int) $request->getParam("education") : 0; //学历
        $edu_name = $request->getParam("edu_name") ? htmlspecialchars(trim($request->getParam("edu_name"))) : ''; //学历名称
        $addr = $request->getParam("addr") ? htmlspecialchars(trim($request->getParam("addr"))) : ''; //现居住地址
        $experience = $request->getParam("experience") ? (int) $request->getParam("experience") : 0; //工作经验
        $exp_name = $request->getParam("exp_name") ? htmlspecialchars(trim($request->getParam("exp_name"))) : ''; //工作经验名称
        $rstatus = $request->getParam("rstatus") ? (int) $request->getParam("rstatus") : 0;
        if (empty($name)) {
            IS_AJAX && ajaxReturns(0, '姓名不能为空', 0);
        }
        if (empty($phone)) {
            IS_AJAX && ajaxReturns(0, '手机号不能为空', 0);
        } else {
            if (!preg_match("/^1[0-9][0-9]{9}$/", $phone)) {
                IS_AJAX && ajaxReturns(0, '手机号码格式不正确', 0);
            }
        }
        if (empty($email)) {
            IS_AJAX && ajaxReturns(0, '邮箱地址不能为空', 0);
        } else {
            if (!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $email)) {
                IS_AJAX && ajaxReturns(0, '邮箱格式不正确', 0);
            }
        }
        $data = $showdata = array();
        //用户基础信息
        $data["realname"] = $name;
        $data["email"] = $email;
        $data["phone"] = $phone;
        $data["sex"] = $gender;
        $data["education"] = $education;
        $data["address"] = $addr;
        $data["workexp"] = $experience;
        $data["logoid"] = $logoid;
        if (!empty($birth)) {
            $birthArr = explode("/", $birth);
            if (!empty($birthArr)) {
                $data["birth_year"] = $birthArr[0];
                $data["birth_month"] = $birthArr[1];
            }
        }
        $usermodel = $this->load('foreuser');
        $resumemodel = $this->load('resume');
        $where['uid'] = $this->_uid;
        $total = $resumemodel->getresumetotal($where);
        if (false === $mret) {
            IS_AJAX && ajaxReturns(0, '修改基本信息失败', 0);
        }
         $mret = $usermodel->edit($data,$this->_uid);
        if(0 === $resume_id){
            if($total >= 6){
                IS_AJAX && ajaxReturns(0, '您的简历数量已超过上限,无法再添加。', 0);
            }
            $rdata = array("u_id" => $this->_uid,"create_time"=>time());
            if(0 !== $rstatus){
                $rdata["r_status"] = $rstatus;
            }
            $result = $resumemodel->addresumebasic($rdata);
            if (0 === $result) {
                IS_AJAX && ajaxReturns(0, '保存简历基本信息失败', 0);
            }
        }else{
            $result = $resume_id;
        }
        $showdata['rid'] = $result;
        $showdata['name'] = $name;
        $showdata['birth'] = $birth;
        $showdata['gender'] = $gender;
        $showdata['edu_name'] = $edu_name;
        $showdata['address'] = $addr;
        $showdata['exp_name'] = $exp_name;
        $showdata['phone'] = $phone;
        $showdata['email'] = $email;
        IS_AJAX && ajaxReturns(1, '保存简历基本信息成功', $showdata, 0);
    }

    /**
     * 保存求职意向信息
     */
    function actionsaveexpinfo() {
        $this->userlogin();
        $this->checkusertype();
        $resquest = new grequest();
        $resume_id = $resquest->getParam("rid") ? (int) $resquest->getParam("rid") : 0; //简历编号
        $jobid = $resquest->getParam("jobid") ? (int) $resquest->getParam("jobid") : 0; //求职意向编号
        $jobname = $resquest->getParam("jobname") ? htmlspecialchars(trim($resquest->getParam("jobname"))) : ''; //期望工作名称
        $industry = $resquest->getParam("industry") ? (int) $resquest->getParam("industry") : 0; //期望行业编号
        $industryname = $resquest->getParam("industryname") ? htmlspecialchars(trim($resquest->getParam("industryname"))) : ''; //期望行业名称
        $city = $resquest->getParam("city") ? (int) $resquest->getParam("city") : 0; //期望城市编号
        $cityname = $resquest->getParam("cityname") ? htmlspecialchars(trim($resquest->getParam("cityname"))) : ''; //期望城市名称
        $salary = $resquest->getParam("salary") ? (int) $resquest->getParam("salary") : 0; //期望薪水编号
        $salaryname = $resquest->getParam("salaryname") ? htmlspecialchars(trim($resquest->getParam("salaryname"))) : ''; //期望薪水名称
        $jobnature = $resquest->getParam(jobnature) ? (int) $resquest->getParam(jobnature) : 0; //期望工作类型编号
        $jobnaturename = $resquest->getParam("jobnaturename") ? htmlspecialchars($resquest->getParam("jobnaturename")) : ''; //期望工作类型名称
        $remarks = $resquest->getParam("remarks") ? htmlspecialchars(trim($resquest->getParam("remarks"))) : ''; //补充说明
        $type = $resquest->getParam("type") ? htmlspecialchars("type") : "";
        if (empty($resume_id)) {
            IS_AJAX && ajaxReturns(0, '请先填写简历基本信息', 0);
        }
        if (empty($jobid)) {
            IS_AJAX && ajaxReturns(0, '期望职位为空，请至少选择一个', 0);
        }
        if (empty($city)) {
            IS_AJAX && ajaxReturns(0, '期望城市为空，请至少选择一个', 0);
        }
        $resumename = $jobname;//简历名称
        $usermodel = $this->load('foreuser');
        $workexp = $usermodel->getuserinfobyuid($this->_uid,"workexp");
        if(!empty($workexp)){
            $resumename .= " ".$this->_experience[$workexp["workexp"]];
        }
        $resumename .= " ".$cityname; 
        $resumemodel = $this->load("resume");
        //简历求职意向基础数据
        $data = array(
            "title"=>$resumename,//简历名称(期望职位空格工作经验空格求职意向地点)
            "u_industry" => $industry, //期望行业
            "u_jobid" => $jobid, //职位编号
            "u_jobname" => $jobname, //职位名称
            "u_city" => $city, //期望城市
            "u_salary" => $salary, //期望薪资
            "u_job_type" => $jobnature, //工作类型
            "u_addition_remarks" => $remarks,//补充说明
            "update_time"=>time()
        );
        if(empty($type)){
            $data["d_status"] = 1;//更新简历状态--可投递
        }
        $ret = $resumemodel->editresumebasic($resume_id, $data);
        if (0 === $ret) {
            IS_AJAX && ajaxReturns(0, '保存求职意向信息失败', 0);
        } else {
            //页面显示数据
            $showdata = array(
                "jobname" => $jobname,
                "industry" => $industryname,
                "city" => $cityname,
                "salary" => $salaryname,
                "jobnature" => $jobnaturename,
                "remarks" => $remarks
            );
            IS_AJAX && ajaxReturns(1, '保存求职意向信息成功', $showdata, 0);
        }
    }

    /**
     * 保存教育经历信息
     */
    function actionsaveeduinfo() {
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $resume_id = $request->getParam("rid") ? (int) $request->getParam("rid") : 0; //简历编号
        $school = $request->getParam("school") ? htmlspecialchars(trim($request->getParam("school"))) : ''; //学校名称
        $professional = $request->getParam("professional") ? htmlspecialchars(trim($request->getParam("professional"))) : ''; //专业名称
        $eduid = $request->getParam("eduid") ? (int) $request->getParam("eduid") : 0;//学历编号
        $entrance = $request->getParam("entrance") ? (int) $request->getParam("entrance") : 0;//是否统招
        $education = $request->getParam("education") ? htmlspecialchars(trim($request->getParam("education"))) : ''; //学历名称
        $startdate = $request->getParam("startdate") ? htmlspecialchars(trim($request->getParam("startdate"))) : ''; //开始时间
        $enddate = $request->getParam("enddate") ? htmlspecialchars(trim($request->getParam("enddate"))) : ''; //结束时间
        $majorcourse = $request->getParam("majorcourse") ? htmlspecialchars(trim($request->getParam("majorcourse"))) : ''; //主修课程
        $minorcourse = $request->getParam("minorcourse") ? htmlspecialchars(trim($request->getParam("minorcourse"))) : ''; //辅修课程
        $eid = $request->getParam("eid") ? (int) $request->getParam("eid") : 0; //教育经历编号
        $jtid = $request->getParam("jtid") ? (int) $request->getParam("jtid") : 0;//简历模板编号
        if (0 === $resume_id) {
            IS_AJAX && ajaxReturns(0, '请先填写简历基本信息', 0);
        }
        if (empty($school)) {
            IS_AJAX && ajaxReturns(0, '请填写学校名称', 0);
        }
        if (empty($professional)) {
            IS_AJAX && ajaxReturns(0, '请填写专业名称', 0);
        }
        if (!isset($eduid)) {
            IS_AJAX && ajaxReturns(0, '专业信息为空，请至少选择一个', 0);
        }
        if (empty($startdate)) {
            IS_AJAX && ajaxReturns(0, '请填写就读开始时间', 0);
        }
        if (empty($enddate)) {
            IS_AJAX && ajaxReturns(0, '请填写就读结束时间', 0);
        }
        init_db()->createCommand()->query("START TRANSACTION");//创建事务处理
        $resumemodel = $this->load('resume');
        //教育经历基础数据
        $data = $showdata = array();
        $data["r_id"] = $resume_id;
        $data["school_name"] = $school;
        $data["prof_name"] = $professional;
        $data["education"] = $eduid;
        $data["is_entrance"] = $entrance;
        $startArr = explode("/", $startdate);
        if (!empty($startArr)) {
            $data["start_year"] = $startArr[0];
            $data["start_month"] = $startArr[1];
        }
        if($enddate!="至今"){
            $endArr = explode("/", $enddate);
            if (!empty($endArr)) {
                $data["end_year"] = $endArr[0];
                $data["end_month"] = $endArr[1];
            }
        }else{
            $data["end_year"] = 0;
            $data["end_month"] = 0;
        }
        
        $data["major_course"] = $majorcourse;
        $data["minor_courses"] = $minorcourse;
        $data["status"] = 1;
        if(0 === $jtid){
            if(0 === $eid){
                //暂时设置教育经历上限数量为5
                $eduinfo = $resumemodel->getresumeedubyrid(array("rid"=>$resume_id,"status"=>1));
                if(count($eduinfo) >= 5){
                    IS_AJAX && ajaxReturns(0, '对不起，您的教育经历数量已达到上限!', 0);
                }
                $ret = $resumemodel->addresumeeducation($data);
                $eid = $ret;
            }else{
                $ret = $resumemodel->editresumeeducation($eid, $data);
            }
        }else{
            if(0 === $eid){
                //选择了模板 则先将此简历编号对应的教育经历信息的status改为0(伪删除) 然后再添加数据
                $resumemodel->editreresumeedubyrid($resume_id, array("status"=>0));
                $ret = $resumemodel->addresumeeducation($data);
                $eid = $ret;
            }else{
                $ret = $resumemodel->editresumeeducation($eid, $data);
            }
        }
        if (false === $ret) {
            init_db()->createCommand()->query("ROLLBACK");//失败回滚
            IS_AJAX && ajaxReturns(0, '保存教育经历信息失败', 0);
        } else {
            init_db()->createCommand()->query("COMMIT");//成功提交
            //页面显示数据
            $showdata["eid"] = $eid;
            $showdata["school"] = $school;
            $showdata["professional"] = $professional;
            $showdata["education"] = $education;
            $showdata["eduid"] = $eduid;
            $showdata["entrance"] = $entrance;
            $showdata["startdate"] = $startdate;
            $showdata["enddate"] = $enddate;
            $showdata["majorcourse"] = $majorcourse;
            $showdata["minorcourse"] = $minorcourse;
            ajaxReturns(1, '保存教育经历信息成功', $showdata, 0,0,'jsons');
        }
    }
    
    /**
     * 删除简历教育经历信息
     */
    function actiondelresumeedu(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $id = $request->getParam('eid') ? (int) $request->getParam('eid') : 0; //eid
        if (empty($id)) {
            IS_AJAX && ajaxReturns(0, '教育经历id为空', 0);
        }
        $resumemodel = $this->load('resume');
        $ret = $resumemodel->deleteresumeeducation($id);
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '删除失败', 0);
        } else {
            IS_AJAX && ajaxReturns(1, '删除成功', 0);
        }
    }
    
    /**
     * 保存简历详情信息(获奖经历、证书及技能、自我评价)
     */
    function actionsaveintroinfo(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $resume_id = $request->getParam("rid") ? (int) $request->getParam("rid") : 0; //简历编号
        $winexp = $request->getParam("winexp") ? htmlspecialchars(trim($request->getParam("winexp"))) : ''; //获奖经历信息
        $jobskill = $request->getParam("jobskill") ? htmlspecialchars(trim($request->getParam("jobskill"))) : ''; //证书技能信息
        $evaluation = $request->getParam("evaluation") ? htmlspecialchars(trim($request->getParam("evaluation"))) : ''; //自我评价信息
        $interest = $request->getParam("interest") ? htmlspecialchars(trim($request->getParam("interest"))) : '';//特长兴趣信息
        $type = $request->getParam("type") ? htmlspecialchars(trim($request->getParam("type"))) : '';//类型
        if ($resume_id==0) {
            IS_AJAX && ajaxReturns(0, '请先填写简历基本信息', 0);
        }
        if(empty($type)){
            IS_AJAX && ajaxReturns(0, '缺少参数信息，请重新操作', 0);
        }
        $resumemodel = $this->load('resume');
        $data = $showdata = array();
        //详情信息基础数据
        $data["r_id"] = $resume_id;
        $info = "";
        switch($type){
            case "win":
                $showdata["content"] = $data["honor_award"] = $winexp;
                $info = "获奖经历";
                break;
            case "skill":
                $showdata["content"] =  $data["job_skill"] = $jobskill;
                $info = "证书技能";
                break;
            case "eval":
                $showdata["content"] = $data["evaluation"] = $evaluation;
                $info = "自我评价";
                break;
            case "interest":
                $showdata["content"] = $data["special_interest"] = $interest;
                $info = "特长兴趣";
                break;
                
        }
        //查看表中是否已有某简历相应数据,根据简历编号进行详情信息的操作
        $introexist = $resumemodel->getresumeintrobyrid(array("rid"=>$resume_id));
        if(empty($introexist)){
            $ret = $resumemodel->addresumeintroinfo($data);
        }else{
            $ret = $resumemodel->editresumeintroinfo($introexist["id"], $data);
        }
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '保存'.$info.'信息失败', 0);
        } else {
            IS_AJAX && ajaxReturns(1, '保存'.$info.'信息成功', $showdata, 0);
        }
    }
    
    /**
     * 保存在校经历信息
     */
    function actionsaveactiveinfo(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $resume_id = $request->getParam("rid") ? (int) $request->getParam("rid") : 0; //简历编号
        $raid = $request->getParam("raid") ? (int) $request->getParam("raid") : 0; //在校经历编号
        $jobname = $request->getParam("jobname") ? htmlspecialchars($request->getParam("jobname")) : ''; //职位名称
        $jobdepartment = $request->getParam("jobdepartment") ? htmlspecialchars($request->getParam("jobdepartment")) : ''; //部门名称
        $workstart = $request->getParam("workstart") ? htmlspecialchars($request->getParam("workstart")) : ''; //任职开始时间
        $workend = $request->getParam("workend") ? htmlspecialchars($request->getParam("workend")) : ''; //任职结束时间
        $workperformance = $request->getParam("workperformance") ? htmlspecialchars($request->getParam("workperformance")) : ''; //工作业绩
        $jtid = $request->getParam("jtid") ? (int) $request->getParam("jtid") : 0;//简历模板编号
        if (0 === $resume_id) {
            IS_AJAX && ajaxReturns(0, '请先填写简历基本信息', 0);
        }
        if (empty($jobname)) {
            IS_AJAX && ajaxReturns(0, '请填写职位名称', 0);
        }
        if (empty($jobdepartment)) {
            IS_AJAX && ajaxReturns(0, '请填写部门名称', 0);
        }
        if (empty($workstart)) {
            IS_AJAX && ajaxReturns(0, '请填写任职开始时间', 0);
        }
        if (empty($workend)) {
            IS_AJAX && ajaxReturns(0, '请填写任职结束时间', 0);
        }
        if (empty($workperformance)) {
            IS_AJAX && ajaxReturns(0, '请填写工作业绩', 0);
        }
        $resumemodel = $this->load('resume');
        //在校经历基础数据
        $data = $showdata = array();
        $data["r_id"] = $resume_id;
        $data["job_name"] = $jobname;
        $data["job_department"] = $jobdepartment;
        $data["status"] = 1;
        $startArr = explode("/", $workstart);
        if (!empty($startArr)) {
            $data["work_start_year"] = $startArr[0];
            $data["work_start_month"] = $startArr[1];
        }
        if($workend!="至今"){
            $endArr = explode("/", $workend);
            if (!empty($endArr)) {
                $data["work_end_year"] = $endArr[0];
                $data["work_end_month"] = $endArr[1];
            }
        }else{
            $data["work_end_year"] = 0;
            $data["work_end_month"] = 0;
        }
        
        $data["work_performance"] = $workperformance;
        if(0 === $jtid){
            if(0 === $raid){
                //暂时设置在校经历上限数量为5
                $activeinfo = $resumemodel->getresumeactivebyrid(array("rid"=>$resume_id,"status"=>1));
                if(count($activeinfo) >= 5){
                    IS_AJAX && ajaxReturns(0, '对不起，您的在校经历数量已达到上限!', 0);
                }
                $ret = $resumemodel->addresumeactive($data);
                $raid = $ret;
            }else{
                $ret = $resumemodel->editresumeactive($raid, $data);
            }
        }else{
            if(0 === $raid){
                //选择了模板 则先将此简历编号对应的在校经历信息的status改为0(伪删除) 然后再添加数据
                $resumemodel->editreresumeactivebyrid($resume_id, array("status"=>0));
                $ret = $resumemodel->addresumeactive($data);
                $raid = $ret;
            }else{
                $ret = $resumemodel->editresumeactive($raid, $data);
            }
        }
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '保存在校经历信息失败', 0);
        } else {
            //页面显示数据
            $showdata["raid"] = $raid;
            $showdata["jobname"] = $jobname;
            $showdata["jobdepartment"] = $jobdepartment;
            $showdata["startdate"] = $workstart;
            $showdata["enddate"] = $workend;
            $showdata["workperformance"] = $workperformance;
            IS_AJAX && ajaxReturns(1, '保存在校经历信息成功', $showdata, 0);
        }
    }
    
    /**
     * 删除简历在校经历信息
     */
    function actiondelresumeactive(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $id = $request->getParam('raid') ? (int) $request->getParam('raid') : 0; //raid
        if (empty($id)) {
            IS_AJAX && ajaxReturns(0, '在校经历id为空', 0);
        }
        $resumemodel = $this->load('resume');
        $ret = $resumemodel->deleteresumeactive($id);
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '删除失败', 0);
        } else {
            IS_AJAX && ajaxReturns(1, '删除成功', 0);
        }
    }
    
    /**
     * 保存简历工作经历信息
     */
    function actionsaveworkinfo(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $resume_id = $request->getParam("rid") ? (int) $request->getParam("rid") : 0; //简历编号
        $wid = $request->getParam("wid") ? (int) $request->getParam("wid") : 0; //工作经历编号
        $company = $request->getParam("company") ? htmlspecialchars(trim($request->getParam("company"))) : ''; //公司名称
        $addr = $request->getParam("addr") ? htmlspecialchars(trim($request->getParam("addr"))) : ''; //公司地址
        $companyjob = $request->getParam("companyjob") ? htmlspecialchars(trim($request->getParam("companyjob"))) : ''; //职位名称
        $subordinate = $request->getParam("subordinate") ? (int) $request->getParam("subordinate") : 0; //下属人数
        $industryid = $request->getParam("industry") ? (int) $request->getParam("industry") : 0; //行业id
        $industryname = $request->getParam("industryname") ? htmlspecialchars(trim($request->getParam("industryname"))) : ''; //行业名称
        $workstart = $request->getParam("workstart") ? htmlspecialchars(trim($request->getParam("workstart"))) : ''; //任职开始时间
        $workend = $request->getParam("workend") ? htmlspecialchars(trim($request->getParam("workend"))) : ''; //任职结束时间
        $jobduties = $request->getParam("jobduties") ? htmlspecialchars(trim($request->getParam("jobduties"))) : ''; //工作职责
        $jtid = $request->getParam("jtid") ? (int) $request->getParam("jtid") : 0;//简历模板编号
        if (0 === $resume_id) {
            IS_AJAX && ajaxReturns(0, '请先填写简历基本信息', 0);
        }
        if (empty($company)) {
            IS_AJAX && ajaxReturns(0, '请填写公司名称', 0);
        }
        if (empty($addr)) {
            IS_AJAX && ajaxReturns(0, '请填写公司地点', 0);
        }
        if (empty($companyjob)) {
            IS_AJAX && ajaxReturns(0, '请填写职位名称', 0);
        }
        if (empty($workstart)) {
            IS_AJAX && ajaxReturns(0, '请填写任职开始时间', 0);
        }
        if (empty($workend)) {
            IS_AJAX && ajaxReturns(0, '请填写任职结束时间', 0);
        }
        $resumemodel = $this->load('resume');
        //工作经历基础数据
        $data = $showdata = array();
        $data["r_id"] = $resume_id;
        $data["company_name"] = $company;
        $data["company_addr"] = $addr;
        $data["company_job"] = $companyjob;
        $data["subordinate"] = $subordinate;
        $data["industry"] = $industryid;
        $data["status"] = 1;
        $startArr = explode("/", $workstart);
        if (!empty($startArr)) {
            $data["w_start_year"] = $startArr[0];
            $data["w_start_month"] = $startArr[1];
        }
        if($workend!="至今"){
            $endArr = explode("/", $workend);
            if (!empty($endArr)) {
                $data["w_end_year"] = $endArr[0];
                $data["w_end_month"] = $endArr[1];
            }
        }else{
            $data["w_end_year"] = 0;
            $data["w_end_month"] = 0;
        }
        $data["job_duties"] = $jobduties;
        if(0===$jtid){
            if(0 === $wid){
                 //暂时设置工作经历上限数量为5
                $workinfo = $resumemodel->getresumeworkbyrid(array("rid"=>$resume_id,"status"=>1));
                if(count($workinfo) >= 5){
                    IS_AJAX && ajaxReturns(0, '对不起，您的工作经历数量已达到上限!', 0);
                }
                $ret = $resumemodel->addresumework($data);
                $wid = $ret;
            }else{
                $ret = $resumemodel->editresumework($wid, $data);
            }
        }else{
            if(0 === $wid){
                //选择了模板 则先将此简历编号对应的工作经历信息的status改为0(伪删除) 然后再添加数据
                $resumemodel->editreresumeworkbyrid($resume_id, array("status"=>0));
                $ret = $resumemodel->addresumework($data);
                $wid = $ret;
            }else{
                $ret = $resumemodel->editresumework($wid, $data);
            }
        }
        
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '保存工作经历信息失败', 0);
        } else {
            //页面显示数据
            $showdata["wid"] = $wid;
            $showdata["company"] = $company;
            $showdata["addr"] = $addr;
            $showdata["company"] = $company;
            $showdata["companyjob"] = $companyjob;
            $showdata["subordinate"] = $subordinate;
            $showdata["induid"] = $industryid;
            $showdata["industry"] = $industryname;
            $showdata["startdate"] = $workstart;
            $showdata["enddate"] = $workend;
            $showdata["jobduties"] = $jobduties;
            IS_AJAX && ajaxReturns(1, '保存工作经历信息成功', $showdata, 0);
        }
    }
    
    /**
     * 删除简历工作经历信息
     */
    function actiondelresumework(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $id = $request->getParam('wid') ? (int) $request->getParam('wid') : 0; //wid
        if (empty($id)) {
            IS_AJAX && ajaxReturns(0, '工作经历id为空', 0);
        }
        $resumemodel = $this->load('resume');
        $ret = $resumemodel->deleteresumework($id);
        if (false === $ret) {
            IS_AJAX && ajaxReturns(0, '删除失败', 0);
        } else {
            IS_AJAX && ajaxReturns(1, '删除成功', 0);
        }
    }
    
    /**
     * 图片上传
     */
    function actionimgupload(){
        $request = new grequest();
        $fromtype = $request->getParam("type") ? htmlspecialchars($request->getParam("type")) : "";
        if(empty($_FILES)){
            IS_AJAX && ajaxReturns(0, '没有文件了', 0);
        }
        $imguse = "resume/".$fromtype."/";//图片用途
        $type = array("jpg", "jpeg", "png", "gif"); //设置允许上传文件的类型
        $file = $_FILES["imgupload"];
        $pinfo = pathinfo($file["name"]);
        //判断文件类型   
        if(!in_array(strtolower($pinfo["extension"]), $type)) {
            $text = implode(",", $type);
            $error = "您只能上传以下类型文件: " . $text;
            IS_AJAX && ajaxReturns(0, $error, 0);
        }
        $picname = $file['name'];
        $picsize = $file['size'];
        if ($picname != "") {
            if ($picsize > 10240000) {
                IS_AJAX && ajaxReturns(0, '图片大小不能超过10M', 0);
            }
            //生成文件名称
            $pics = generateRandStr(10) .'.'.$pinfo["extension"];
            //上传路径
            list($y, $m, $d) = explode('-', date('Y-m-d'));
            $imgpath = 'uploads/'.$imguse.$y."/".$m."/".$d;
            $file_folder = ROOT_PATH."/".$imgpath;
            if(!file_exists($file_folder)){
                dir_create($file_folder);
            }
            $fullname = $file_folder."/".$pics;
            move_uploaded_file($file['tmp_name'],$fullname);
            $imgmodel = $this->load("uploadfile");
            //上传图片基础数据
            $filedata = array(
                "imagename"=>$file["name"],//图片原名
                "imagepath"=>$imgpath."/".$pics,//图片路径
                "imagesize"=>$file["size"],//图片大小
                "imageext"=>$pinfo["extension"],//图片扩展名
                "userid"=>$this->_uid,//上传人id
                "username"=>$this->_uname,//上传人姓名
                "uploadtime"=>time(),//上传时间
                "uploadip"=>get_client_ip(),//上传人IP
                "imagetype"=>5
            );
            $imgid = $imgmodel->imginsert($filedata);
            $this->setsession('uc_offcn_imgid',$imgid);
            if($fromtype == "face"){
                $usermodel = $this->load("foreuser");
                $old_logo = $usermodel->getuserinfobyuid($this->_uid,"logoid");
                if(!empty($old_logo["logoid"])){
                    $imgmodel->imgdelete($old_logo["logoid"]);
                }
                $updatedata = array(
                    "logoid"=>$imgid
                );
                $ret = $usermodel->edit($updatedata,$this->_uid);
                if(0 === $ret){
                    IS_AJAX && ajaxReturns(0, '更新简历基本信息中头像信息失败', 0);
                }
            }
            ajaxReturns(1, '图片上传成功', array("path" => $imgpath . "/" . $pics, 'imgid' => $imgid), 0, 'jsons');
        }else{
            ajaxReturns(0, '图片上传失败', 0, 0, 'jsons');
        }
    }
    
    /**
     * 图片裁剪
     */
    function actioncutImg(){
        $request = new grequest();
        $fromaction = (strpos($_SERVER["HTTP_REFERER"],"resume") != false) ? "resume" : "company";
        $fromtype = $request->getParam("fromtype") ? htmlspecialchars($request->getParam("fromtype")) : "";
        //list($width, $height) = getimagesize($_POST["imageSource"]);
        //$viewPortW = $_POST["viewPortW"];
        //$viewPortH = $_POST["viewPortH"];
        $pWidth = $_POST["imageW"];
        $pHeight = $_POST["imageH"];
        $ext = strtolower(end(explode(".", $_POST["imageSource"])));
        $function = $this->returnCorrectFunction($ext);
        $image = $function($_POST["imageSource"]);
        $width = imagesx($image);
        $height = imagesy($image);
        $image_p = imagecreatetruecolor($pWidth, $pHeight);
        $this->setTransparency($image, $image_p, $ext);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $pWidth, $pHeight, $width, $height);
        imagedestroy($image);
        $widthR = imagesx($image_p);
        $hegihtR = imagesy($image_p);
        $selectorX = $_POST["selectorX"];
        $selectorY = $_POST["selectorY"]+1;
        if ($_POST["imageRotate"]) {
            $angle = 360 - $_POST["imageRotate"];
            $image_p = imagerotate($image_p, $angle, 0);
            $pWidth = imagesx($image_p);
            $pHeight = imagesy($image_p);
            $diffW = abs($pWidth - $widthR) / 2;
            $diffH = abs($pHeight - $hegihtR) / 2;
            $_POST["imageX"] = ($pWidth > $widthR ? $_POST["imageX"] - $diffW : $_POST["imageX"] + $diffW);
            $_POST["imageY"] = ($pHeight > $hegihtR ? $_POST["imageY"] - $diffH : $_POST["imageY"] + $diffH);
        }
        $dst_x = $src_x = $dst_y = $src_y = 0;
        if ($_POST["imageX"] > 0) {
            $dst_x = abs($_POST["imageX"]);
        } else {
            $src_x = abs($_POST["imageX"]);
        }
        if ($_POST["imageY"] > 0) {
            $dst_y = abs($_POST["imageY"]);
        } else {
            $src_y = abs($_POST["imageY"]);
        }
        $viewport = imagecreatetruecolor($_POST["viewPortW"], $_POST["viewPortH"]);
        $this->setTransparency($image_p, $viewport, $ext);
        imagecopy($viewport, $image_p, $dst_x, $dst_y, $src_x, $src_y, $pWidth, $pHeight);
        imagedestroy($image_p);
        $selector = imagecreatetruecolor($_POST["selectorW"], $_POST["selectorH"]);
        $this->setTransparency($viewport, $selector, $ext);
        imagecopy($selector, $viewport, 0, 0, $selectorX, $selectorY, $_POST["viewPortW"], $_POST["viewPortH"]);
        //生成文件名称
        list($y, $m, $d) = explode('-', date('Y-m-d'));
        $file = "uploads/".$fromaction."/".$fromtype."/".$y."/".$m."/".$d."/small".basename($_POST["imageSource"]);
        $filepath = ROOT_PATH."/".$file;
        unlink(str_replace("small", "", $filepath));
        $this->parseImage($ext,$selector,$filepath);
        imagedestroy($viewport);
        //修改图片信息
        $imgid = $this->session("uc_offcn_imgid");
        $imgmodel = $this->load("uploadfile");
        $updatedata = array(
            "imagepath"=>$file
        );
        $imgmodel->imgedit($updatedata,$imgid);
        ajaxReturns(1, '图片裁剪成功',  array("imgid"=>$imgid,"path"=>$file), 0,'jsons');
    }
    
    function getjpegsize($img_loc) {
        $handle = fopen($img_loc, "rb") or die("Invalid file stream.");
        $new_block = NULL;
        if (!feof($handle)) {
            $new_block = fread($handle, 32);
            $i = 0;
            if ($new_block[$i] == "xFF" && $new_block[$i + 1] == "xD8" && $new_block[$i + 2] == "xFF" && $new_block[$i + 3] == "xE0") {
                $i += 4;
                if ($new_block[$i + 2] == "x4A" && $new_block[$i + 3] == "x46" && $new_block[$i + 4] == "x49" && $new_block[$i + 5] == "x46" && $new_block[$i + 6] == "x00") {
                    $block_size = unpack("H*", $new_block[$i] . $new_block[$i + 1]);
                    $block_size = hexdec($block_size[1]);
                    while (!feof($handle)) {
                        $i += $block_size;
                        $new_block .= fread($handle, $block_size);
                        if ($new_block[$i] == "xFF") {
                            $sof_marker = array("xC0", "xC1", "xC2", "xC3", "xC5", "xC6", "xC7", "xC8", "xC9", "xCA", "xCB", "xCD", "xCE", "xCF");
                            if (in_array($new_block[$i + 1], $sof_marker)) {
                                $size_data = $new_block[$i + 2] . $new_block[$i + 3] . $new_block[$i + 4] . $new_block[$i + 5] . $new_block[$i + 6] . $new_block[$i + 7] . $new_block[$i + 8];
                                $unpacked = unpack("H*", $size_data);
                                $unpacked = $unpacked[1];
                                $height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
                                $width = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
                                return array($width, $height);
                            } else {
                                $i += 2;
                                $block_size = unpack("H*", $new_block[$i] . $new_block[$i + 1]);
                                $block_size = hexdec($block_size[1]);
                            }
                        } else {
                            return FALSE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }

    function determineImageScale($sourceWidth, $sourceHeight, $targetWidth, $targetHeight) {
        $scalex = $targetWidth / $sourceWidth;
        $scaley = $targetHeight / $sourceHeight;
        return min($scalex, $scaley);
    }

    function returnCorrectFunction($ext) {
        $function = "";
        switch ($ext) {
            case "png":
                $function = "imagecreatefrompng";
                break;
            case "jpeg":
                $function = "imagecreatefromjpeg";
                break;
            case "jpg":
                $function = "imagecreatefromjpeg";
                break;
            case "gif":
                $function = "imagecreatefromgif";
                break;
        }
        return $function;
    }

    function parseImage($ext, $img, $file = null) {
        switch ($ext) {
            case "png":
                imagepng($img, ($file != null ? $file : ''));
                break;
            case "jpeg":
                imagejpeg($img, ($file ? $file : ''), 90);
                break;
            case "jpg":
                imagejpeg($img, ($file ? $file : ''), 90);
                break;
            case "gif":
                imagegif($img, ($file ? $file : ''));
                break;
        }
    }

    function setTransparency($imgSrc, $imgDest, $ext) {
        if ($ext == "png" || $ext == "gif") {
            $trnprt_indx = imagecolortransparent($imgSrc);
            // If we have a specific transparent color
            if ($trnprt_indx >= 0) {
                // Get the original image's transparent color's RGB values
                $trnprt_color = imagecolorsforindex($imgSrc, $trnprt_indx);
                // Allocate the same color in the new image resource
                $trnprt_indx = imagecolorallocate($imgDest, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                // Completely fill the background of the new image with allocated color.
                imagefill($imgDest, 0, 0, $trnprt_indx);
                // Set the background color for new image to transparent
                imagecolortransparent($imgDest, $trnprt_indx);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif ($ext == "png") {
                // Turn off transparency blending (temporarily)
                imagealphablending($imgDest, true);
                // Create a new transparent color for image
                $color = imagecolorallocatealpha($imgDest, 0, 0, 0, 127);
                // Completely fill the background of the new image with allocated color.
                imagefill($imgDest, 0, 0, $color);
                // Restore transparency blending
                imagesavealpha($imgDest, true);
            }
        }
    }
    
    /**
     * 修改简历到岗时间
     */
    function actioneditarrival(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;
        $did = $request->getParam("did") ? (int) $request->getParam("did") : 0;
        $updatedata = array(
            "arrival_id"=>$did
        );
        if($rid == 0){
            IS_AJAX && ajaxReturns(0, '请先填写简历基本信息', 0);
        }
        $resumemodel = $this->load("resume");
        $ret = $resumemodel->edit($updatedata,$rid);
        if(0 === $ret){
            IS_AJAX && ajaxReturns(0, '更新简历到岗时间信息失败', 0);
        }
        IS_AJAX && ajaxReturns(1, '更新成功', 0);
    }
    
    /**
     * HR预览简历
     */
    function actiondeliverypreview(){
        $this->userlogin();
        $request = new grequest();
        $this->basevar['title'] = "预览简历";
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;//简历编号
        $did = $request->getParam("did") ? (int) $request->getParam("did") : 0;//简历投递编号
        $userArr = $basic = $basicinfo = array();
        if(0 === $rid && 0 === $did){
            ShowMsg('请选择一个简历',$this->url('index','','resume'));exit();
        }
        $resumemodel = $this->load("resume");
        //判断简历投递接收用户是否是当前登录用户
        $deliverymodel = $this->load("delivery");
        $deinfo = $deliverymodel->getdeliveryinfobydid($did);
        
        if(!empty($deinfo)){
            if($deinfo["hr_uid"]!=$this->_uid){
                ShowMsg("您没有权限查看此简历",-1,0,3000);exit();
            }
            if($deinfo["old_rid"] != $rid){
                ShowMsg("没有找到符合条件的信息",-1,0,3000);exit();
            }
        }else{
            ShowMsg("没有找到符合条件的信息",-1,0,3000);exit();
        }
        $where = array(
            "uid" => $deinfo["u_id"],
            "rid" => $rid,
            "status" => 1
        );
        $basic = $resumemodel->getresumebyarr($where, "*");
        if(empty($basic)){
            ShowMsg("没有找到符合条件的信息",-1,0,3000);exit();
        }
        //用户基本信息
        $usermodel = $this->load("foreuser");
        $userArr = $usermodel->getuserinfobyuid($deinfo["u_id"]);
        if(empty($userArr)){
            ShowMsg('没有符合条件的信息',-1,0,3000);exit();
        }
        $imgmodel = $this->load("uploadfile");
        if($userArr["logoid"]!=0){
            $logoArr = $imgmodel->getimagebyid(array("id"=>$userArr["logoid"]),"imagepath");
        }
        //基本信息
        $basicinfo["rid"] = $rid;
        $basicinfo["name"] = $userArr["realname"];//姓名
        $basicinfo["experience"] = $this->_experience[$userArr["workexp"]];//工作经验
        $basicinfo["birth"] = $userArr["birth_year"].$userArr["birth_month"];//出生年月
        $basicinfo["gender"] = ($userArr["sex"]) == 0 ? "男" : "女";//性别
        $basicinfo["education"] = $this->_education[$userArr["education"]];//学历
        $basicinfo["addr"] = $userArr["address"];//现居
        $basicinfo["phone"] = $userArr["phone"];//手机号
        $basicinfo["email"] = $userArr["email"];//邮箱
        $basicinfo["logo"] = $logoArr["imagepath"];
        $basicinfo["jobname"] = $basic["u_jobname"];
        $basicinfo["industry"] = $basic["u_industry"];
        $basicinfo["city"] = $basic["u_city"];
        $basicinfo["salary"] = $basic["u_salary"];
        $basicinfo["nature"] = $basic["u_job_type"];
        $basicinfo["remarks"] = $basic["u_addition_remarks"];
        $basicinfo["uid"] = $basic["u_id"];
        $basicinfo["rstatus"] = $basic["r_status"];
        $basicinfo["rattach_id"] = $basic["rattach_id"];
        //教育经历信息
        $eduinfo = $resumemodel->getresumeedubyrid(array("rid"=>$rid),"*",5);
        foreach ($eduinfo as $ek => $ev){
            $eduinfo[$ek]["education"] = $this->_education[$ev["education"]];
        }
        
        //获奖经历、证书技能、特长兴趣、自我评价
        $introinfo = $resumemodel->getresumeintrobyrid(array("rid"=>$rid));
        
        //在校经历
        $activeinfo = $resumemodel->getresumeactivebyrid(array("rid"=>$rid),"*",5);
        
        //工作经历
        $workinfo = $resumemodel->getresumeworkbyrid(array("rid"=>$rid),"*",5);
        
        //作品展示
        $displayinfo = $resumemodel->getresumedisplaybyrid(array("rid"=>$rid),10);
        if(!empty($displayinfo)){
            $displayonline = $displayupload = array();
            foreach ($displayinfo as $dk => $dv){
                if($dv["w_type"]==1){
                    if(0 != $dv["w_image_id"]){
                        $imgArr = $imgmodel->getimagebyid(array('id'=>$dv["w_image_id"]));
                        $dv["imgpath"] = $imgArr["imagepath"];
                    }
                    $displayupload[] = $dv;
                }elseif($dv["w_type"]==2){
                    $displayonline[] = $dv;
                }
            }
        }
        
        $deliveryinfo = $deliverymodel->getdelivery(array("id"=>$did));
        $this->webseo['title'] = '预览简历';
        $this->render("preview",array(
            "basicinfo"=>$basicinfo,
            "eduinfo"=>$eduinfo,
            "introinfo"=>$introinfo,
            "activeinfo"=>$activeinfo,
            "workinfo"=>$workinfo,
            "source"=>"hr",
            "displayupload"=>$displayupload,
            "displayonline"=>$displayonline,
            "did"=>$did,
            "deliveryinfo"=>$deliveryinfo,
            "_edu" => $this->_education,
            "_exp" => $this->_experience,
            "_industry" => $this->_industry,
            "_city" => $this->_citycache,
            "_salary" => $this->_salary,
            "_jobnature" => $this->_jobnature,
            "_arrival"=>$this->_arrival,
            "seoinfo"=>$this->webseo
        ));
    }
    
    /**
     * 普通用户预览简历
     */
    function actionpreview(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $this->basevar['title'] = "预览简历";
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;//简历编号
        if(0 === $rid ){
            ShowMsg('请选择一个简历',$this->url('index','','resume'));exit();
        }
        $resumemodel = $this->load("resume");
        $ret_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
        $userArr = $basic = $basicinfo = array();
        $where = array(
            "rid"=>$rid
        );
        $basic = $resumemodel->getresumebyarr($where,"*");
        if($basic["u_id"]!== $this->_uid){
            ShowMsg("您没有权限查看此简历",$ret_url,0,3000);exit();
        }
        //用户基本信息
        $usermodel = $this->load("foreuser");
        $userArr = $usermodel->getuserinfobyuid($this->_uid);
        if(empty($userArr)){
            ShowMsg('没有符合条件的信息',$this->url('index','','resume'));exit();
        }
        $imgmodel = $this->load("uploadfile");
        if($userArr["logoid"]!=0){
            $logoArr = $imgmodel->getimagebyid(array("id"=>$userArr["logoid"]),"imagepath");
        }
        //基本信息
        $basicinfo["rid"] = $rid;
        $basicinfo["name"] = $userArr["realname"];//姓名
        $basicinfo["experience"] = $this->_experience[$userArr["workexp"]];//工作经验
        $basicinfo["birth"] = $userArr["birth_year"].$userArr["birth_month"];//出生年月
        $basicinfo["gender"] = ($userArr["sex"]) == 0 ? "男" : "女";//性别
        $basicinfo["education"] = $this->_education[$userArr["education"]];//学历
        $basicinfo["addr"] = $userArr["address"];//现居
        $basicinfo["phone"] = $userArr["phone"];//手机号
        $basicinfo["email"] = $userArr["email"];//邮箱
        $basicinfo["logo"] = $logoArr["imagepath"];
        $basicinfo["jobname"] = $basic["u_jobname"];
        $basicinfo["industry"] = $basic["u_industry"];
        $basicinfo["city"] = $basic["u_city"];
        $basicinfo["salary"] = $basic["u_salary"];
        $basicinfo["nature"] = $basic["u_job_type"];
        $basicinfo["remarks"] = $basic["u_addition_remarks"];
        $basicinfo["rstatus"] = $basic["r_status"];
        $basicinfo["rattach_id"] = $basic["rattach_id"];
        //教育经历信息
        $eduinfo = $resumemodel->getresumeedubyrid(array("rid"=>$rid),"*",5);
        foreach ($eduinfo as $ek => $ev){
            $eduinfo[$ek]["education"] = $this->_education[$ev["education"]];
        }
        
        //获奖经历、证书技能、特长兴趣、自我评价
        $introinfo = $resumemodel->getresumeintrobyrid(array("rid"=>$rid));
        
        //在校经历
        $activeinfo = $resumemodel->getresumeactivebyrid(array("rid"=>$rid),"*",5);
        
        //工作经历
        $workinfo = $resumemodel->getresumeworkbyrid(array("rid"=>$rid),"*",5);
        
        //作品展示
        $displayinfo = $resumemodel->getresumedisplaybyrid(array("rid"=>$rid),10);
        if(!empty($displayinfo)){
            $displayonline = $displayupload = array();
            foreach ($displayinfo as $dk => $dv){
                if($dv["w_type"]==1){
                    if(0 != $dv["w_image_id"]){
                        $imgArr = $imgmodel->getimagebyid(array('id'=>$dv["w_image_id"]));
                        $dv["imgpath"] = $imgArr["imagepath"];
                    }
                    $displayupload[] = $dv;
                }elseif($dv["w_type"]==2){
                    $displayonline[] = $dv;
                }
            }
        }
        $this->webseo['title'] = '预览简历';
        $this->render("preview",array(
            "basicinfo"=>$basicinfo,
            "eduinfo"=>$eduinfo,
            "introinfo"=>$introinfo,
            "activeinfo"=>$activeinfo,
            "workinfo"=>$workinfo,
            "displayupload"=>$displayupload,
            "displayonline"=>$displayonline,
            "type"=>"self",
            "_edu" => $this->_education,
            "_exp" => $this->_experience,
            "_industry" => $this->_industry,
            "_city" => $this->_citycache,
            "_salary" => $this->_salary,
            "_jobnature" => $this->_jobnature,
            "_arrival"=>$this->_arrival,
            "seoinfo"=>$this->webseo
        ));
    }
    
    /**
     * 预览简历--简历搜索/发送邮件
     */
    function actionpreviewall(){
        $request = new grequest();
        $this->basevar['title'] = "预览简历";
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;//简历编号
        $did = $request->getParam("did") ? (int) $request->getParam("did") : 0;//简历投递编号
        $type = $request->getParam("type") ? (int) $request->getParam("type") : 1;//2发送邮件
        $viewpage = ($type == 2) ? 'prevemail' : 'preview';
        if($type!=2){
            $this->userlogin();
        }
        if(0 === $rid ){
            ShowMsg('请选择一个简历',$this->url('index','','resume'));exit();
        }
        $resumemodel = $this->load("resume");
        $userArr = $basic = $basicinfo = array();
        $where = array(
            "rid"=>$rid,
            "status"=>1
        );
        $basic = $resumemodel->getresumebyarr($where,"*");
        //用户基本信息
        $usermodel = $this->load("foreuser");
        $userArr = $usermodel->getuserinfobyuid($basic["u_id"]);
        $logoArr = array();
        $imgmodel = $this->load("uploadfile");
        if($userArr["logoid"]!=0){
            $logoArr = $imgmodel->getimagebyid(array("id"=>$userArr["logoid"]),"imagepath");
        }
        //基本信息
        $basicinfo["name"] = $userArr["realname"];//姓名
        $basicinfo["experience"] = $this->_experience[$userArr["workexp"]];//工作经验
        $basicinfo["birth"] = $userArr["birth_year"].$userArr["birth_month"];//出生年月
        $basicinfo["gender"] = ($userArr["sex"]) == 0 ? "男" : "女";//性别
        $basicinfo["education"] = $this->_education[$userArr["education"]];//学历
        $basicinfo["addr"] = $userArr["address"];//现居
        $basicinfo["phone"] = $userArr["phone"];//手机号
        $basicinfo["email"] = $userArr["email"];//邮箱
        $basicinfo["logo"] = (!empty($logoArr)) ? $logoArr["imagepath"] : "";
        $basicinfo["rid"] = $basic["id"];
        $basicinfo["uid"] = $userArr["uid"];
        $basicinfo["jobname"] = $basic["u_jobname"];
        $basicinfo["industry"] = $basic["u_industry"];
        $basicinfo["city"] = $basic["u_city"];
        $basicinfo["salary"] = $basic["u_salary"];
        $basicinfo["nature"] = $basic["u_job_type"];
        $basicinfo["remarks"] = $basic["u_addition_remarks"];
        $basicinfo["rstatus"] = $basic["r_status"];
        //教育经历信息
        $eduinfo = $resumemodel->getresumeedubyrid(array("rid"=>$rid),"*",5);
        foreach ($eduinfo as $ek => $ev){
            $eduinfo[$ek]["education"] = $this->_education[$ev["education"]];
        }
        
        //获奖经历、证书技能、特长兴趣、自我评价
        $introinfo = $resumemodel->getresumeintrobyrid(array("rid"=>$rid));
        
        //在校经历
        $activeinfo = $resumemodel->getresumeactivebyrid(array("rid"=>$rid),"*",5);
        
        //工作经历
        $workinfo = $resumemodel->getresumeworkbyrid(array("rid"=>$rid),"*",5);
        //作品展示
        $displayinfo = $resumemodel->getresumedisplaybyrid(array("rid"=>$rid),10);
        if(!empty($displayinfo)){
            $displayonline = $displayupload = array();
            foreach ($displayinfo as $dk => $dv){
                if($dv["w_type"]==1){
                    if(0 != $dv["w_image_id"]){
                        $imgArr = $imgmodel->getimagebyid(array('id'=>$dv["w_image_id"]));
                        $dv["imgpath"] = $imgArr["imagepath"];
                    }
                    $displayupload[] = $dv;
                }elseif($dv["w_type"]==2){
                    $displayonline[] = $dv;
                }
            }
        }
        
        $this->render($viewpage,array(
            "basicinfo"=>$basicinfo,
            "eduinfo"=>$eduinfo,
            "introinfo"=>$introinfo,
            "activeinfo"=>$activeinfo,
            "workinfo"=>$workinfo,
            "type"=>$type,
            "displayupload"=>$displayupload,
            "displayonline"=>$displayonline,
            "source"=>"all",
            "did"=>$did,
            "_edu" => $this->_education,
            "_exp" => $this->_experience,
            "_industry" => $this->_industry,
            "_city" => $this->_citycache,
            "_salary" => $this->_salary,
            "_jobnature" => $this->_jobnature,
            "_arrival"=>$this->_arrival,
            "seoinfo"=>$this->webseo
        ));
    }
    
    /**
     * 下载已完成的简历至本地
     */
    function actiondownload(){
        $request = new grequest();
        $rid = $request->getParam("rid") ?  (int) $request->getParam("rid") : 0;//简历编号
        $type = $request->getParam("type") ? (int) $request->getParam("type") : 0;//文件类型
        $usermodel = $this->load("foreuser");
        $userinfo = $usermodel->getuserinfobyuid($this->_uid);
        switch ($type){
            case "1":
                $url = PUBLIC_URL."/resume/downresume/type/1/rid/".$rid;
                $content = file_get_contents($url);
                $this->html2word($content,mb_convert_encoding($userinfo["realname"], "gb2312","utf-8"));
                break;
            case "2":
                $url = PUBLIC_URL."/resume/downresume/type/2/rid/".$rid;
                $content = file_get_contents($url);
                $this->html2pdf($content,mb_convert_encoding($userinfo["realname"], "gb2312","utf-8"));
                break;
            default :
                ShowMsg("未知文件类型",-1);
                break;
        }
    }
    
    /**
     * 根据页面内容生成PDF文件
     * @param type $content
     * @param type $filename
     */
    function html2pdf($content,$filename){
        require_once UC_ROOT.'lib/pdf/tcpdf.php';
        //实例化 
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); 
        // 设置文档信息 
        $pdf->SetCreator('快就业'); //设定文档的创建者
        $pdf->SetAuthor('快就业'); //设定文档的作者
        $pdf->SetTitle('快就业-就好业');//设定文档标题 
        $pdf->SetSubject('TCPDF Tutorial'); //设定文档主题
        $pdf->SetKeywords('TCPDF, PDF, PHP');//设定文档关键字
        // 设置页眉和页脚信息 
        $pdf->SetHeaderData('logo.png', 25, '快就业', '快就业-就好业',array(0,64,255), array(0,64,128)); 
        $pdf->setFooterData(array(0,64,0), array(0,64,128)); 
        // 设置页眉和页脚字体 
        $pdf->setHeaderFont(Array('stsongstdlight', '', '10')); 
        $pdf->setFooterFont(Array('helvetica', '', '8')); 
        // 设置默认等宽字体 
        $pdf->SetDefaultMonospacedFont('courier'); 
        // 设置间距 
        $pdf->SetMargins(15, 18, 15); 
        $pdf->SetHeaderMargin(5); 
        $pdf->SetFooterMargin(10); 
        // 设置分页 
        $pdf->SetAutoPageBreak(TRUE, 25); 
        $pdf->setImageScale(1.25); 
        $pdf->setFontSubsetting(true); 
        //设置字体 
        $pdf->SetFont('stsongstdlight', '', 14);
        $pdf->AddPage(); 
        $pdf->writeHTML($content, true, false, true, false, '');
        //输出PDF 
        $outname = $filename.".pdf";
        $pdf->Output($outname, 'D'); 
    }
    
    /**
     * 根据页面内容生成WORD文件
     * @param type $content
     * @param type $filename
     */
    function html2word($content,$filename){
        ob_start(); //打开缓冲区 
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40"> 
        <head> 
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
        <xml><w:WordDocument><w:View>Print</w:View></xml> 
        </head>'; 
        echo $content.'</html>';
        header("Cache-Control: public"); 
        Header("Content-type: application/octet-stream"); 
        Header("Accept-Ranges: bytes"); 
        header("Content-type:text/html;charset=utf-8");
        if (strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE')) {
            Header('Content-Disposition: attachment; filename=' . $filename . '.doc');
        } else if (strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox')) {
            Header('Content-Disposition: attachment; filename=' . $filename . '.doc');
        } else {
            Header('Content-Disposition: attachment; filename=' . $filename . '.doc');
        }
        Header("Pragma:no-cache"); 
        Header("Expires:0"); 
        ob_end_flush();//输出全部内容到浏览器 
    }
    
    /**
     * 显示视频指导
     */
    function actionshowvideo(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $vid = $request->getParam("vid") ? (int) $request->getParam("vid") : 0;
        if (0 == $vid) {
            IS_AJAX && ajaxReturns(0, '请选择一项进行查看', 0);
        }
        $videoArr = array(
            '1' => '<iframe height=547 width=878 src="http://e.eoffcn.com/space.php?do=playvideo&op=play_demo&aid=1&lid=14036&ltype=31&iframe=3&width=878&height=547" frameborder=0 allowfullscreen> </iframe>',//工作经历
            '2' => '<iframe height=547 width=878 src="http://e.eoffcn.com/space.php?do=playvideo&op=play_demo&aid=1&lid=14037&ltype=31&iframe=3&width=878&height=547" frameborder=0 allowfullscreen> </iframe>',//基本信息
            '3' => '<iframe height=547 width=878 src="http://e.eoffcn.com/space.php?do=playvideo&op=play_demo&aid=1&lid=14038&ltype=31&iframe=3&width=878&height=547" frameborder=0 allowfullscreen> </iframe>',//教育经历
            '4' => '<iframe height=547 width=878 src="http://e.eoffcn.com/space.php?do=playvideo&op=play_demo&aid=1&lid=14039&ltype=31&iframe=3&width=878&height=547" frameborder=0 allowfullscreen> </iframe>',//求职意向
            '5' => '<iframe height=547 width=878 src="http://e.eoffcn.com/space.php?do=playvideo&op=play_demo&aid=1&lid=14040&ltype=31&iframe=3&width=878&height=547" frameborder=0 allowfullscreen> </iframe>',//兴趣爱好
            '6' => '<iframe height=547 width=878 src="http://e.eoffcn.com/space.php?do=playvideo&op=play_demo&aid=1&lid=14041&ltype=31&iframe=3&width=878&height=547" frameborder=0 allowfullscreen> </iframe>',//在校经历
            '7' => '<iframe height=547 width=878 src="http://e.eoffcn.com/space.php?do=playvideo&op=play_demo&aid=1&lid=14042&ltype=31&iframe=3&width=878&height=547" frameborder=0 allowfullscreen> </iframe>',//证书技能
            '8' => '<iframe height=547 width=878 src="http://e.eoffcn.com/space.php?do=playvideo&op=play_demo&aid=1&lid=14043&ltype=31&iframe=3&width=878&height=547" frameborder=0 allowfullscreen> </iframe>'//自我评价
        );
        $response = $this->renderPartial('showvideo', array('videoiframe'=>$videoArr[$vid]));
        IS_AJAX && ajaxReturns(1, 0, $response);
    }
    
    /**
     * 获取面试邀请信息
     */
    function actiongetinviteinfo(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $jdid = $request->getParam("jdid") ? (int) $request->getParam("jdid") : 0;
        if($jdid == 0){
            IS_AJAX && ajaxReturns(0, '缺少参数，请重新操作', 0);
        }
        $deliverymodel = $this->load("delivery");
        $deliveryinfo = $deliverymodel->getdeliverylogbyjdid(array("jd_id"=>$jdid,"status"=>4));
        $inviteinfo = unserialize($deliveryinfo["info"]);
        $response =  $this->renderPartial('interview', array('inviteinfo'=>$inviteinfo));
        IS_AJAX && ajaxReturns(1,0,$response); 
    }
    
    /**
     * 下载简历
     */
    function actiondownresume(){
        $request = new grequest();
        $type = $request->getParam("type") ? (int) $request->getParam("type") : 0;
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;
        if(0 === $rid ){
            ShowMsg('请选择一个简历',$this->url('index','','resume'));exit();
        }
        $resumemodel = $this->load("resume");
        $userArr = $basic = $basicinfo = $logoArr = array();
        $where = array(
            "rid"=>$rid,
            "status"=>1
        );
        $basic = $resumemodel->getresumebyarr($where,"*");
        //用户基本信息
        $usermodel = $this->load("foreuser");
        $userArr = $usermodel->getuserinfobyuid($basic["u_id"]);
        if($userArr["logoid"]!=0){
            $imgmodel = $this->load("uploadfile");
            $logoArr = $imgmodel->getimagebyid(array("id"=>$userArr["logoid"]),"imagepath");
        }
        //基本信息
        $basicinfo["name"] = $userArr["realname"];//姓名
        $basicinfo["experience"] = $this->_experience[$userArr["workexp"]];//工作经验
        $basicinfo["birth"] = $userArr["birth_year"].$userArr["birth_month"];//出生年月
        $basicinfo["gender"] = ($userArr["sex"]) == 0 ? "男" : "女";//性别
        $basicinfo["education"] = $this->_education[$userArr["education"]];//学历
        $basicinfo["addr"] = $userArr["address"];//现居
        $basicinfo["phone"] = $userArr["phone"];//手机号
        $basicinfo["email"] = $userArr["email"];//邮箱
        $basicinfo["logo"] = (!empty($logoArr)) ? $logoArr["imagepath"] : "";
        $basicinfo["rid"] = $basic["id"];
        $basicinfo["jobname"] = $basic["u_jobname"];
        $basicinfo["industry"] = $this->_industry[$basic["u_industry"]];
        $basicinfo["city"] = $this->_citycache[$basic["u_city"]]["name"];
        $basicinfo["salary"] = $this->_salary[$basic["u_salary"]];
        $basicinfo["nature"] = $this->_jobnature[$basic["u_job_type"]];
        $basicinfo["remarks"] = $basic["u_addition_remarks"];
        //教育经历信息
        $eduinfo = $resumemodel->getresumeedubyrid(array("rid"=>$rid),"*",5);
        foreach ($eduinfo as $ek => $ev){
            $eduinfo[$ek]["education"] = $this->_education[$ev["education"]];
        }
        
        //获奖经历、证书技能、特长兴趣、自我评价
        $introinfo = $resumemodel->getresumeintrobyrid(array("rid"=>$rid));
        //在校经历
        $activeinfo = $resumemodel->getresumeactivebyrid(array("rid"=>$rid),"*",5);
        
        //工作经历
        $workinfo = $resumemodel->getresumeworkbyrid(array("rid"=>$rid),"*",5);
        $this->webseo['title'] = '简历下载';
        $this->render("downresume",array(
            "basicinfo"=>$basicinfo,
            "eduinfo"=>$eduinfo,
            "introinfo"=>$introinfo,
            "activeinfo"=>$activeinfo,
            "workinfo"=>$workinfo,
            "type"=>$type,
            "seoinfo"=>$this->webseo
        ));
    }
    
    /**
     * HR用户获取简历用户联系方式
     */
    function actiongetcontact(){
        $this->userlogin();
        $request = new grequest();
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;
        $uid = $request->getParam("uid") ? (int) $request->getParam("uid") : 0;
        if(0 == $rid){
            IS_AJAX && ajaxReturns(0, '请选择一项进行查看', 0);
        }
        if($this->_usertype == 1){
            IS_AJAX && ajaxReturns(0, '您没有权限进行查看', 0);
        }
        $data = array();
        //HR获取简历用户联系方式基础数据
        $data["hruid"] = $this->_uid;
        $data["companyid"] = $this->_company_id;
        $data["rid"] = $rid;
        $model = $this->load("foreuser");
        //获取某公司已获取联系方式记录
        $contactedlist = $model->getcontactinfo($data["companyid"]);
        $count = count($contactedlist);
        if($count >= 50){
            IS_AJAX && ajaxReturns(2, '获取联系方式已超过上限', 0);
        }
        foreach ($contactedlist as $ctk => $ctv){
            $contactedinfo[] = $ctv["rid"];
        }
        if(!in_array($data["rid"], $contactedinfo)){
            //创建事务处理
            init_db()->createCommand()->query("START TRANSACTION");
            $ret = $model->addcontactinfo($data);
            if(false == $ret){
                init_db()->createCommand()->query("ROLLBACK"); //失败回滚
                $this->error("记录查看联系方式失败");
            }
            init_db()->createCommand()->query("COMMIT"); //成功提交
        }
        //查看用户联系方式并返回页面
        $userinfo = $model->getUserInfo(array("uid"=>$uid),"phone,email");
        IS_AJAX && ajaxReturns(1,"获取用户信息成功",$userinfo);
    }
    
    /**
     * 修改简历信息
     */
    function actioneditresume(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $resume_id = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;
        if(0 == $resume_id){
            IS_AJAX && ajaxReturns(0, '保存简历信息失败，请刷新后重试', 0);
        }
        $data = array();
        $model = $this->load("resume");
        $data["auto_status"] = 1;//更新简历推荐状态--用于系统自动推荐用户简历
        $ret = $model->editresumebasic($resume_id, $data);
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false == $ret){
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            $this->error("记录查看联系方式失败");
        }
        init_db()->createCommand()->query("COMMIT"); //成功提交
        IS_AJAX && ajaxReturns(1, '您的简历已经创建成功！');
    }
    
    /**
     * 保存简历作品展示
     */
    function actionsavehandwork(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $rdid = $request->getParam("rdid") ? (int) $request->getParam("rdid") : 0;//作品展示编号
        $resumeid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;//简历编号
        $type = $request->getParam("type") ? (int) $request->getParam("type") : 0;//类型1图片2在线作品
        $desc = $request->getParam("picdes") ? htmlspecialchars($request->getParam("picdes")) : "";//作品展示描述
        if(0 == $resumeid){
            IS_AJAX && ajaxReturns(0, '请先填写简历基本信息', 0);
        }
        $data = array();
        //作品展示表基础数据
        $data["r_id"] = $resumeid;
        $data["w_type"] = $type;
        $data["w_intro"] = $desc;
        if($type == 1){
            $pic = $request->getParam("pic") ? (int) $request->getParam("pic") : 0;//作品图片id
            $pictitle = $request->getParam("pictitle") ? htmlspecialchars($request->getParam("pictitle")) : "";//作品图片标题
            if(0 == $pic){
                IS_AJAX && ajaxReturns(0, '请上传作品展示图片', 0);
            }
            $data["w_image_id"] = $pic;
            $data["w_title"] = $pictitle;
        }else if ($type == 2) {
            $handworkurl = $request->getParam("handworkurl") ? htmlspecialchars($request->getParam("handworkurl")) : "";//在线作品地址
            if(empty($handworkurl)){
                IS_AJAX && ajaxReturns(0, '请输入在线作品地址', 0);
            }
            $data["w_url"] = $handworkurl;
        }
        $model = $this->load("resume");
        init_db()->createCommand()->query("START TRANSACTION");
        if($rdid == 0){
            $existinfo = $model->getresumedisplaybyrid(array("rid"=>$data["r_id"],"type"=>$data["w_type"]));
            if(count($existinfo) >=5){
                IS_AJAX && ajaxReturns(0, '对不起，您已经上传了足够多的作品了', 0);;
            }
            $ret = $model->addresumedisplay($data);
            $rdid = $ret;
        }else{
            $ret = $model->editresumedisplay($rdid,$data);
        }
        $data["rdid"] = $rdid;
        if (false == $ret) {
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0, '保存简历作品展示信息失败', 0);
        }
        init_db()->createCommand()->query("COMMIT"); //成功提交
        IS_AJAX && ajaxReturns(1, '保存简历作品展示信息成功', $data);
    }

    /**
     * 删除作品展示
     */
    function actiondelresumehandwork(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $rdid = $request->getParam("rdid") ? (int) $request->getParam("rdid") : 0;//作品编号
        if(0 == $rdid){
            IS_AJAX && ajaxReturns(0,'请选择一项作品进行操作');
        }
        $model = $this->load("resume");
        init_db()->createCommand()->query("START TRANSACTION");
        $ret = $model->deletehandworkbyid($rdid);
        if (false == $ret) {
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0, '删除简历作品展示信息失败', 0);
        }
        init_db()->createCommand()->query("COMMIT"); //成功提交
        IS_AJAX && ajaxReturns(1, '删除简历作品展示信息成功', $data);
    }
    
    /**
     * 上传附件简历页面
     */
    function actionappendix(){
        $this->userlogin();
        $this->checkusertype();
        $request = new grequest();
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;
        //获取用户基本信息
        $userArr = $userinfo = $expArr = $expinfo = $fileinfo = array();
        $usermodel = $this->load("foreuser");
        $userArr = $usermodel->getuserinfobyuid($this->_uid);
        $imgmodel = $this->load("uploadfile");
        if(!empty($userArr)){
            $userinfo["name"] = $userArr["realname"];//用户姓名
            $userinfo["gender"] = $userArr["sex"];//性别
            $userinfo["birth"] = $userArr["birth_year"]."/".$userArr["birth_month"];//出生年月
            $userinfo["education"] = $userArr["education"];//学历
            $userinfo["address"] = $userArr["address"];//现居住地
            $userinfo["workexp"] = $userArr["workexp"];//工作经验
            $userinfo["phone"] = $userArr["phone"];//电话
            $userinfo["phoneverify"] = $userArr["phone_validate"];//手机验证状态
            $userinfo["emailverify"] = $userArr["is_verify"];//邮箱验证状态
            $userinfo["email"] = $userArr["email"];//邮箱
            $userinfo["logoid"] = $userArr["logoid"];//用户头像id
            if($userinfo["logoid"] > 0){
                $imgArr = $imgmodel->getimagebyid(array('id'=>$userinfo["logoid"]));
                $userinfo["logo"] = $imgArr["imagepath"];//用户头像
            }
        }
        if($rid != 0){
            //求职意向信息
            $resumemodel = $this->load("resume");
            $where = array(
                "uid"=>$this->_uid,
                "rid"=>$rid
            );
            $expArr = $resumemodel->getresumebyarr($where,"*");
            if($expArr["u_id"]!== $this->_uid){
                ShowMsg("您没有权限查看此简历",'',0,3000);exit();
            }
            if(!empty($expArr)){
                $expinfo["rid"] = $rid;
                $expinfo["jobid"] = $expArr["u_jobid"];//期望职位id
                $expinfo["jobname"] = $expArr["u_jobname"];//求职意向
                $expinfo["industry"] = $expArr["u_industry"];//期望行业
                $expinfo["city"] = $expArr["u_city"];//期望城市
                $expinfo["salary"] = $expArr["u_salary"];//期望薪资
                $expinfo["nature"] = $expArr["u_job_type"];//工作类型
                $expinfo["remarks"] = $expArr["u_addition_remarks"];//补充说明
                $expinfo["arrival"] = $expArr["arrival_id"];
            }
            if($expArr["rattach_id"]!=0){
                $uploadmodel = $this->load("uploadfile");
                $fileinfo = $uploadmodel->getfileinfobyid(array("id"=>$expArr["rattach_id"]));
            }
        }
        $this->webseo['title'] = '附件简历';
        $this->render('appendix', array(
            'userinfo' => $userinfo,
            'fileinfo' => $fileinfo,
            'expinfo' => $expinfo,
            '_edu' => $this->_education,
            '_exp' => $this->_experience,
            '_typejobdata' => $this->_typejobdata,
            '_industry' => $this->_industry,
            '_city' => $this->_citytreedata,
            '_citycache' => $this->_citycache,
            '_salary' => $this->_salary,
            '_jobnature' => $this->_jobnature,
            '_arrival' => $this->_arrival,
            'seoinfo'=>$this->webseo
        ));
    }
    
    /**
     * 上传附件简历
     */
    function actionresumeupload(){
        $request = new grequest();
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;//简历编号
        if(0 === $rid){
            ajaxReturns(0, '请先填写简历基本信息', 0, 0, 'jsons');
        }
        if(empty($_FILES)){
            ajaxReturns(0, '没有文件了', 0, 0, 'jsons');
        }
        $use = "resume/appendix";//文件用途 简历/附件
        $time = date("Ymd");
        $type = array("doc","docx");//设置允许上传文件的类型
        $file = $_FILES["appendresume"];
        $pinfo = pathinfo($file["name"]);
        //判断文件类型   
        if(!in_array(strtolower($pinfo["extension"]), $type)) {
            $text = implode(",", $type);
            $error = "您只能上传以下类型文件: " . $text;
            ajaxReturns(0, $error, 0, 0, 'jsons');
        }
        $rand = rand(100, 999);
        //生成文件名称
        $pics = $time . $rand .'.'.$pinfo["extension"];
        //上传路径
        list($y, $m, $d) = explode('-', date('Y-m-d'));
        $filepath = 'uploads/'.$use."/".$y."/".$m."/".$d;
        $file_folder = ROOT_PATH."/".$filepath;
        if(!file_exists($file_folder)){
            dir_create($file_folder);
        }
        $fullname = $file_folder."/".$pics;
        move_uploaded_file($file['tmp_name'],$fullname);
        $filemodel = $this->load("uploadfile");
        //上传文件基础数据
        $filedata = array(
            "filename"=>$file["name"],//附件原名
            "filepath"=>$filepath."/".$pics,//图片路径
            "filesize"=>$file["size"],//文件大小
            "fileext"=>$pinfo["extension"],//文件扩展名
            "userid"=>$this->_uid,//上传人id
            "username"=>$this->_uname,//上传人姓名
            "uploadtime"=>time(),//上传时间
            "uploadip"=>get_client_ip()//上传人IP
        );
        $fileid = $filemodel->fileinsert($filedata);
        //将附件简历编号更新至简历基本信息
        $updatedata = array(
            "r_status"=>1,//简历类型 1附件简历
            "rattach_id"=>$fileid, //附件编号
            "d_status"=> 1//更新简历状态--可投递
        );
        $resumemodel = $this->load("resume");
        $ret = $resumemodel->edit($updatedata,$rid);
        if(0 === $ret){
            ajaxReturns(0, '更新简历附件信息失败', 0, 0, 'jsons');
        }
        ajaxReturns(1, '附件上传成功',  array("fileid"=>$fileid,"filename"=>$file["name"]), 0,'jsons');
    }
    
    /**
     * 删除已上传的附件简历
     */
    function actiondelattach(){
        $request = new grequest();
        $id = $request->getParam("id") ? (int) $request->getParam("id") : 0; //附件简历编号
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0; //简历编号
        if(0 === $id){
            IS_AJAX && ajaxReturns(0, '简历附件编号为空,请重新操作', 0);
        }
        if(0 === $rid){
            IS_AJAX && ajaxReturns(0, '简历编号不能为空', 0);
        }
        $filemodel = $this->load("uploadfile");
        $ret = $filemodel->filedelete($id);
        if(0 === $ret){
            IS_AJAX && ajaxReturns(0, '附件简历删除失败', 0);
        }
        //删除简历后更新简历基本信息中的附件简历id信息
        $resumemodel = $this->load("resume");
        $updatedata = array(
            "rattach_id"=>0,
            "d_status"=>0
        );
        $result = $resumemodel->edit($updatedata,$rid);
        if(0 === $result){
            IS_AJAX && ajaxReturns(0, '简历基本信息更新失败', 0);
        }
        IS_AJAX && ajaxReturns(1, '附件简历删除成功', 0);
    }
    
    /**
     * 下载附件简历
     */
    function actiondownappendix(){
        $this->userlogin();
        $request = new grequest();
        $rid = $request->getParam("rid") ? (int) $request->getParam("rid") : 0;
        $did = $request->getParam("did") ? (int) $request->getParam("did") : 0;
        if(0 == $rid){
            ShowMsg("简历信息不存在",-1);die;
        }
        $resume = $this->load('resume');
        $resumeinfo = $resume->getresumebyarr(array("rid"=>$rid),'id,u_id,rattach_id');
        if(empty($resumeinfo)){
            ShowMsg("简历信息不存在",-1);die;
        }
        if($resumeinfo["rattach_id"] == 0){
            ShowMsg('附件不可读或者不存在', -1);die;
        }
        if(0 == $did){
            //投递编号为空则认为是用户自己下载附件简历
            if($resumeinfo["u_id"] != $this->_uid){
                ShowMsg('对不起，您没有权限操作此简历', -1);die;
            }
        }else{
            //投递编号不为空，则认为是HR用户下载收到的附件简历
            $delivery = $this->load("delivery");
            $deliveryinfo = $delivery->getdeliveryinfobydid($did,"id,hr_uid");
            if(empty($deliveryinfo)){
                ShowMsg("简历信息不存在",-1);die;
            }
            if($deliveryinfo['hr_uid'] != $this->_uid){
                ShowMsg('对不起，您没有权限操作此简历', -1);die;
            }
        }
        $model = $this->load("uploadfile");
        $file = $model->getfileinfobyid(array("id"=>$resumeinfo["rattach_id"]),"filename,filepath");
        if(empty($file)){
            ShowMsg('附件不可读或者不存在', -1);die;
        }
        if(!empty($file)){
            $filename = $file['filename'];
            $tpath = PUBLIC_URL.$file['filepath'];
            if (!file_exists($tpath)){
                ShowMsg('附件不可读或者不存在', -1);die;
            }
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Type: application/octet-stream");
            header('Content-Disposition: attachment; filename="' . mb_convert_encoding($filename, "gb2312","utf-8"). '"');
            ob_clean();
            ob_end_flush();
            header("Content-Transfer-Encoding:­ binary");
            header("Content-Length: " . filesize($tpath));
            readfile($tpath);
        }
    }
}
?>
