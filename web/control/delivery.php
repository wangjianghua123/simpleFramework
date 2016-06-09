<?php
/**
 * 简历管理 投递箱 wjh
 */
!defined('IN_UC') && exit('Access Denied');

class delivery extends base{
    public $_uid;
    public $_company_id;
    public $_uc_usertype;//1普通用户 2企业用户
    public $_realname;
    public $_typejobdata ; //职位格式化后的 多级分类
    public $_typedeliverydata ; //职位格式化后的 多级分类
    public $_typedelivery;//职位类别
    public $_cachebasicdata;//基本信息数据
    public $_citydata;//地区信息
    public $_citytreedata; //地区树
    public $_salary; //期望薪水
    function __construct() {
        header("Content-type:text/html;charset=utf-8;");
        parent::__construct();
        if(!$this->session('uc_offcn_uid')){
            IS_AJAX && ajaxReturns(0,'请重新登录后操作',0);
            $this->redirect($this->url('login','','foreuser'));
        }else{
            $this->_uid = $this->session('uc_offcn_uid');
            $this->_company_id = $this->session('uc_company_id');
            $this->_uc_usertype = $this->session('uc_usertype');
            $this->_typejobdata = S('typejob_list');
            $this->_realname = $this->session('uc_offcn_username');
            $this->_typedeliverydata = S('typedelivery_list');
            $this->_typedelivery = S('typedelivery');
            $this->_cachebasicdata = S('getbasicdata');
            $this->_salary = unserialize($this->_cachebasicdata["expected_salary"]);
            $this->_citydata = S('areacache');
            $this->_citytreedata = S('areatree');
            //初始化判断
            $this->getcertstatus();
        }
    }
    
    
    /*
     * ajax获取各种类型的个数
     */
    function actionajaxGetCounts(){
        $where = $list = $counts = array();
        $where['hr_uid'] = $this->_uid;
        $deliverysmodel = $this->load('delivery');
        $list = $deliverysmodel->getdeliverycount($where,'id,u_id,job_id,opreate_status,folders_status');
        $list2 = $deliverysmodel->getfolderspagetotal($where);
        if(empty($list)){
            $counts['unhandlecnt'] = 0;
            $counts['preparecontcatcnt'] = 0;
            $counts['arrangedcnt'] = 0;
            $counts['havarefusedcnt'] = 0;
            $counts['autofiltercnt'] = 0;
            $counts['foldercnt'] = $list2;
            IS_AJAX && ajaxReturns(0,'暂无数据',$counts);
        }else{
            foreach($list as $k=>$v){
                if($v['opreate_status'] == 1){
                    $counts['unhandlecnt'] += 1;
                }
                if($v['opreate_status'] == 2){
                    $counts['preparecontcatcnt'] += 1;
                }
                if($v['opreate_status'] == 3){
                    $counts['arrangedcnt'] += 1;
                }
                if($v['opreate_status'] == 4 && $v['folders_status'] == 0){
                    $counts['havarefusedcnt'] += 1;
                }
                if($v['opreate_status'] == 5){
                    $counts['autofiltercnt'] += 1;
                }
            }
            $counts['foldercnt']  = $list2;
            IS_AJAX && ajaxReturns(0,'已有数据',$counts);
        }
    }
    
    /**
     * 简历管理相关 5大状态
     * 简历投递操作状态 1待处理 2待沟通 3已安排 4不合适 5自动过滤 默认是全部的
     */
    function actionindex(){
        $request = new grequest();
        $status = $request->getParam('status') ? (int)$request->getParam('status'): 1;//处理状态
        $page = $request->getParam('page') ? $request->getParam('page'):0;//分页 
        $exps = $request->getParam('exps') ? $request->getParam('exps'):0;//工作经验
        $educ = $request->getParam('educ') ? $request->getParam('educ'):0;//学历要求 
        $sorted = $request->getParam('sorted') ? $request->getParam('sorted'):'default';//排序
        $keyword = $request->getParam('keyword') ? htmlspecialchars(trim($request->getParam('keyword'))):'';//职位名称搜索
        if(!empty($keyword)){
            
        }
        $limit = 12;
        $deliverysmodel = $this->load('delivery');
        $list = $where = $where2 = array();
        
//        if(!isset($exps)){
//            $where2['workexp'] = $exps;
//        }
//        if(isset($educ)){
//            $where2['education'] = $educ;
//        }
//        if(!empty($where2)){
//            $info = $infotmp = array();
//            $members = $this->load('foreuser');
//            $info = $members->getUserSearch($where2,'uid,username');
//            if(!empty($info)){
//                foreach($info as $ik=>$iv){
//                     $infotmp[$iv['uid']] = true;
//                }
//            }
//            $infotmp = array_keys($infotmp);
//            $where['u_id'] = $infotmp;
//        }
        $where['hr_uid'] = $this->_uid;
        $where['opreate_status'] = $status;
        $where['folders_status'] = 0; //1 不合适删除
        if($sorted == 'default'){
                $order = "delivery_time desc";
        }else if($sorted == 'patchrate'){
                $order = "match_rate desc";
        }
        $total = $deliverysmodel->getdeliveryspagetotal($where);
        $list = $deliverysmodel->getdeliveryspagelist($limit,($page-1)*$limit,$where,$order);
        if(!empty($list)){
            $pager = '';
            if($total >= $limit){
                $pager = $this->page($total,$page,$limit,5,array('status'=>$where['status']));
            }
            $resumeidArr = $resumeArr = $resumeinfoArr = $resumeeArr = $resumeeinfoArr = $resumewArr = $resumewinfoArr = 
            $jobidArr = $jobArr = $jobinfoArr =  $useridArr = $userArr = $userinfoArr = $ulogoidArr = $ulogoArr = $ulogoinfoArr = 
            $jobdelidArr = $jobdelArr = $jobdelinfoArr = array();
            foreach ($list as $k => $v){
                $resumeidArr[$v['old_rid']] = true;
                $jobidArr[$v['job_id']] = true;
                $useridArr[$v['u_id']] = true;
                $jobdelidArr[$v['id']] = true;
            }
            //职位信息
            $jobsmodel = $this->load('jobs');
            $jobidArr = array_keys($jobidArr);
            $jobArr = $jobsmodel->getjobinfo($jobidArr, 'job_id,typejob_name');
            if(!empty($jobArr)){
                foreach ($jobArr as $k => $v) {
                    $jobinfoArr[$v['job_id']] = $v['typejob_name'];
                }
            }
            //简历基本信息
            $resumemodel = $this->load('resume');
            $resumeidArr = array_keys($resumeidArr);
            $resumeArr = $resumemodel->getresumeinfo($resumeidArr, 'id,u_city');
            if(!empty($resumeArr)){
                foreach ($resumeArr as $rk => $rv) {
                    $resumeinfoArr[$rv['id']] = $rv;
                }
            }
            
            
            //获取简历工作经验
            $resumewArr = $resumemodel->getresumeworkinfo($resumeidArr, 'w_id,r_id,company_name,compnay_job');
            if(!empty($resumewArr)){
                foreach ($resumewArr as $rwk => $rwv) {
                    $resumewinfoArr[$rwv['r_id']] = $rwv;
                }
            }
            //获取简历教育经历
            $resumeeArr = $resumemodel->getresumeeduinfo($resumeidArr, 'e_id,r_id,school_name,prof_name,education');
            if(!empty($resumeeArr)){
                foreach ($resumeeArr as $rek => $rev) {
                    $resumeeinfoArr[$rev['r_id']] = $rev;
                }
            }
            //用户基本信息 简历的基本信息放到用户表中
            $members = $this->load('foreuser');
            $useridArr = array_keys($useridArr);
            $userArr = $members->getinfobyuid($useridArr, 'uid,username,email,realname,phone,logoid,workexp');
            if(!empty($userArr)){
                foreach ($userArr as $uk => $uv) {
                    $userinfoArr[$uv['uid']] = $uv;
                    $ulogoidArr[$uv['logoid']] = true;
                }
            }

            //获取个人简历头像
            $uploadfiesmodel = $this->load('uploadfile');
            $ulogoidArr = array_keys($ulogoidArr);
            $ulogoArr = $uploadfiesmodel->getimageinfo($ulogoidArr, 'id,imagepath');
            if(!empty($ulogoArr)){
                foreach ($ulogoArr as $uk => $uv) {
                    $ulogoinfoArr[$uv['id']] = $uv['imagepath'];
                }
            }

            //通知面试信息
            $jobdelidArr = array_keys($jobdelidArr);
            $jobdelArr = $deliverysmodel->getdeliverylogsinfo($jobdelidArr, 'id,jd_id,typejob_name,info');
            if(!empty($jobdelArr)){
                foreach ($jobdelArr as $jk => $jv) {
                    $jobdelinfoArr[$jv['jd_id']] = $jv;
                }
            }
            unset($jobidArr,$jobArr,$resumeidArr,$resumeArr,$useridArr,$userArr,$resumewArr,$resumeeArr,$jobdelArr,$jobdelidArr);
        }
        $this->render('index', array(
            'keyword'=>$keyword,
            'exps'=>$exps,
            'educ'=>$educ,
            'status'=>$status,
            'list'=>$list,
            'sorted'=>$sorted,
            'total'=>$total,
            'pags'=>$pager,
            'title'=>$title,
            'ulogoinfoArr'=>$ulogoinfoArr,
            'resumewinfoArr'=>$resumewinfoArr,
            'resumeeinfoArr'=>$resumeeinfoArr,
            'userinfoArr'=>$userinfoArr,
            'resumeinfoArr'=>$resumeinfoArr,
            'jobinfoArr'=>$jobinfoArr,
            'jobdelinfoArr'=>$jobdelinfoArr,
            '_cachebasicdata'=>$this->_cachebasicdata,
            '_citydata'=>$this->_citydata
            )
        );
    }
    
    
    /**
     * 已存入文件夹 重新建表
     */
    function actionfolders(){
        $request = new grequest();
        $status = $request->getParam('status') ? (int)$request->getParam('status'): 1;//处理状态
        $page = $request->getParam('page') ? $request->getParam('page'):0;//分页 
        $exps = $request->getParam('exps') ? $request->getParam('exps'):0;//工作经验
        $educ = $request->getParam('educ') ? $request->getParam('educ'):0;//学历要求 
        
        $keyword = $request->getParam('keyword') ? htmlspecialchars(trim($request->getParam('keyword'))):'';//职位名称搜索
        if(!empty($keyword)){
            
        }
        $limit = 12;
        $deliverysmodel = $this->load('delivery');
        $list = $where = array();
        $where['hr_uid'] = $this->_uid;
        $order = "add_time desc";
        $total = $deliverysmodel->getfolderspagetotal($where);
        $list = $deliverysmodel->getfolderspagelist($limit,($page-1)*$limit,$where,$order);
        if(!empty($list)){
            $pager = '';
            if($total >= $limit){
                $pager = $this->page($total,$page,$limit,5,array('status'=>$where['status']));
            }
            $resumeidArr = $resumeArr = $resumeinfoArr = $resumeeArr = $resumeeinfoArr = $resumewArr = $resumewinfoArr = 
            $useridArr = $userArr = $userinfoArr = $ulogoidArr = $ulogoArr = $ulogoinfoArr =  array();
            foreach ($list as $k => $v){
                $resumeidArr[$v['old_rid']] = true;
                $useridArr[$v['u_id']] = true;
            }
            //简历基本信息
            $resumemodel = $this->load('resume');
            $resumeidArr = array_keys($resumeidArr);
            $resumeArr = $resumemodel->getresumeinfo($resumeidArr, 'id,u_city');
            if(!empty($resumeArr)){
                foreach ($resumeArr as $rk => $rv) {
                    $resumeinfoArr[$rv['id']] = $rv;
                }
            }
            
            
            //获取简历工作经验
            $resumewArr = $resumemodel->getresumeworkinfo($resumeidArr, 'w_id,r_id,company_name,compnay_job');
            if(!empty($resumewArr)){
                foreach ($resumewArr as $rwk => $rwv) {
                    $resumewinfoArr[$rwv['r_id']] = $rwv;
                }
            }
            //获取简历教育经历
            $resumeeArr = $resumemodel->getresumeeduinfo($resumeidArr, 'e_id,r_id,school_name,prof_name,education');
            if(!empty($resumeeArr)){
                foreach ($resumeeArr as $rek => $rev) {
                    $resumeeinfoArr[$rev['r_id']] = $rev;
                }
            }
            //用户基本信息 简历的基本信息放到用户表中
            $members = $this->load('foreuser');
            $useridArr = array_keys($useridArr);
            $userArr = $members->getinfobyuid($useridArr, 'uid,username,email,realname,phone,logoid,workexp');
            if(!empty($userArr)){
                foreach ($userArr as $uk => $uv) {
                    $userinfoArr[$uv['uid']] = $uv;
                    $ulogoidArr[$uv['logoid']] = true;
                }
            }

            //获取个人简历头像
            $uploadfiesmodel = $this->load('uploadfile');
            $ulogoidArr = array_keys($ulogoidArr);
            $ulogoArr = $uploadfiesmodel->getimageinfo($ulogoidArr, 'id,imagepath');
            if(!empty($ulogoArr)){
                foreach ($ulogoArr as $uk => $uv) {
                    $ulogoinfoArr[$uv['id']] = $uv['imagepath'];
                }
            }
            unset($resumeidArr,$resumeArr,$useridArr,$userArr,$resumewArr,$resumeeArr);
        }
        $this->render('folders', array(
            'keyword'=>$keyword,
            'exps'=>$exps,
            'educ'=>$educ,
            'status'=>$status,
            'list'=>$list,
            'total'=>$total,
            'pags'=>$pager,
            'title'=>$title,
            'ulogoinfoArr'=>$ulogoinfoArr,
            'resumewinfoArr'=>$resumewinfoArr,
            'resumeeinfoArr'=>$resumeeinfoArr,
            'userinfoArr'=>$userinfoArr,
            'resumeinfoArr'=>$resumeinfoArr,
            '_cachebasicdata'=>$this->_cachebasicdata,
            '_citydata'=>$this->_citydata
            )
        );
    }
    
    
    /*
     * 搜索简历
     */
    function actionsearch(){
		$this->render('search2');
//        $this->render('search', 
//                array(
//                    '_typejobdata' => $this->_typejobdata,
//                    '_cachebasicdata' => $this->_cachebasicdata,
//                    '_citytreedata' => $this->_citytreedata,
//                    '_citydata' => $this->_citydata,
//                    '_salary' => $this->_salary
//                )
//        );
    }
    
    
    /*
     * 简历搜索操作
     */
    function actiondosearch(){
		die;
        $request = new grequest();
        $page = $request->getParam('page') ? $request->getParam('page'):0;//分页 
        $typejob_id = $request->getParam('positionType') ? (int)$request->getParam('positionType'):0;// 职位类型id
        //$typejob_name = $request->getParam('positionName') ? htmlspecialchars(trim($request->getParam('positionName'))) : '';// 职位名称
        $industry = ($request->getParam("industry")!="") ? (int) $request->getParam("industry")-1 : -1;//行业领域
        $job_nature = $request->getParam('jobNature') ? (int)$request->getParam('jobNature'):0;// 工作性质
        $expsalary = $request->getParam('expsalary') ? (int)trim($request->getParam('expsalary')):0;// 月薪范围
        $city = $request->getParam('workAddress') ? (int)$request->getParam('workAddress'):0;// 工作城市id
        $experience = $request->getParam('workYear') ? (int)$request->getParam('workYear'):-1;// 工作经验
        $education = $request->getParam('education') ? (int)$request->getParam('education'):-1;// 学历要求
        if(empty($typejob_id) && empty($typejob_name) && empty($job_nature) && empty($expsalary) && empty($city) && $experience == -1 && $education  == -1 && $industry == -1){
            ShowMsg('请至少选择一个条件搜索', $this->url('search','','delivery'));
        }
        
        $limit = 12;
        $resumemodel = $this->load('resume');
        $list = $where = $where2 = array();
        if($typejob_id){
            $where['u_jobid'] = $typejob_id;
            $where2['positionType'] = $typejob_id;
        }
        if($typejob_name){
            $where['u_jobname'] = $typejob_name;
        }
        if($job_nature){
            $where['u_job_type'] = $job_nature;
            $where2["jobNature"] = $job_nature;
        }
        if($expsalary){
            $where['u_salary'] = $expsalary;
            $where2['expsalary'] = $expsalary;
        }
        if($city){
            $where['u_city'] = $city;
            $where2['workAddress'] = $city;
        }
        if(isset($experience) && $experience >= 0){
            $where2['workexp'] = $experience;
        }
        if(isset($education) && $education >= 0){
            $where2['education'] = $education;
        }
        if(!empty($where2)){
           $members = $this->load('foreuser');
           $minfo = $members->getUserSearch($where2,'uid,username,realname,phone');
           if(!empty($minfo)){
               foreach($minfo as $mk=>$mv){
                  $uidarr[] = $mv['uid'];
               }
               $where['u_id'] = implode(",", $uidarr);
           }
        }
        if(isset($industry) && $industry >= 0){
            $where['u_industry'] = $industry;
            $where2['industry'] = $industry+1;
        }
        $total = $resumemodel->searchresumepagetotal($where);
        $list = $resumemodel->searchresumepagelist($limit,($page-1)*$limit,$where,'id,title,u_id,u_jobid,u_jobname,u_city,u_salary,u_job_type,u_addition_remarks,create_time');
        if(!empty($list)){
            $pager = '';
            if($total >= $limit){
                $pager = $this->page($total,$page,$limit,5,$where2);
            }
            $resumeidArr  = $resumeeArr = $resumeeinfoArr = $resumewArr = $resumewinfoArr = 
            $useridArr = $userArr = $userinfoArr = $ulogoidArr = $ulogoArr = $ulogoinfoArr = array();
            foreach ($list as $k => $v){
                $resumeidArr[$v['id']] = true;
                $useridArr[$v['u_id']] = true;
            }
            
            $resumeidArr = array_keys($resumeidArr);
            //获取简历工作经验
            $resumewArr = $resumemodel->getresumeworkinfo($resumeidArr, 'w_id,r_id,company_name,compnay_job');
            if(!empty($resumewArr)){
                foreach ($resumewArr as $rwk => $rwv) {
                    $resumewinfoArr[$rwv['r_id']] = $rwv;
                }
            }
            //获取简历教育经历
            $resumeeArr = $resumemodel->getresumeeduinfo($resumeidArr, 'e_id,r_id,school_name,prof_name,education');
            if(!empty($resumeeArr)){
                foreach ($resumeeArr as $rek => $rev) {
                    $resumeeinfoArr[$rev['r_id']] = $rev;
                }
            }
            //用户基本信息 简历的基本信息放到用户表中
            $members = $this->load('foreuser');
            $useridArr = array_keys($useridArr);
            $userArr = $members->getinfobyuid($useridArr, 'uid,username,email,realname,phone,logoid,workexp');
            if(!empty($userArr)){
                foreach ($userArr as $uk => $uv) {
                    $userinfoArr[$uv['uid']] = $uv;
                    $ulogoidArr[$uv['logoid']] = true;
                }
            }

	    //获取个人简历头像
            $uploadfiesmodel = $this->load('uploadfile');
            $ulogoidArr = array_keys($ulogoidArr);
            $ulogoArr = $uploadfiesmodel->getimageinfo($ulogoidArr, 'id,imagepath');
            if(!empty($ulogoArr)){
                foreach ($ulogoArr as $uk => $uv) {
                    $ulogoinfoArr[$uv['id']] = $uv['imagepath'];
                }
            }

            unset($jobidArr,$jobArr,$resumeidArr,$useridArr,$userArr,$resumewArr,$resumeeArr);
        }
        $this->render('dosearch', array(
            'list'=>$list,
            'total'=>$total,
            'pags'=>$pager,
            'ulogoinfoArr'=>$ulogoinfoArr,
            'resumewinfoArr'=>$resumewinfoArr,
            'resumeeinfoArr'=>$resumeeinfoArr,
            'userinfoArr'=>$userinfoArr,
            '_cachebasicdata'=>$this->_cachebasicdata,
            '_citydata'=>$this->_citydata
            )
        );
    }
       
    /*
     * 单个不合适操作
     */
    function actionrefuse(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        if(empty($uid)){
            IS_AJAX && ajaxReturns(0,'投递用户id为空',0);
        }
        $where = array();
        $deliverysmodel = $this->load('delivery');
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,old_rid,u_id,hr_uid,job_id,company_id,opreate_status');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['opreate_status'] == 4){
            IS_AJAX && ajaxReturns(0,'已经操作过不合适',0);
        }
        

        //获取公司信息
        $companym = $this->load("company");
        $companyinfo = $companym->getcompanyinfo($demlist['company_id'],"c_id,c_name,c_short_name");
        //获取职位信息
        $jobs = $this->load("jobs");
        $jobsinfo = $jobs->getjobs($demlist['job_id'],"job_id,typejob_name");
        
        //查找不合适的模板
        $templates = $this->load("templates");
        unset($where);
        $where['uid'] = $this->_uid;
        $temlist = $templates->getrefuseinfos($where);
//        if(empty($temlist)){
//            IS_AJAX && ajaxReturns(0,'请联系管理员添加不合适模板',0);
//        }
        unset($where);
        $members = $this->load("foreuser");
        $where['uid'] = $uid;
        $mlist = $members->getUserInfo($where,'uid,email,phone,username,realname');
        $response =  $this->renderPartial('refuse',array('id'=>$id,'uid'=>$uid,'mlist'=>$mlist,'temlist'=>$temlist,'companyinfo'=>$companyinfo,'jobsinfo'=>$jobsinfo));
        IS_AJAX && ajaxReturns(1,0,$response); 
    }
    
    
    /*
     * 单个不合适提交操作
     */
    function actiondorefuse(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
        $title = $request->getParam('title') ? trim($request->getParam('title')): '';// 模板名称
        $ctitle = $request->getParam('ctitle') ? trim($request->getParam('ctitle')): '';// 公司和职位
        $realname = $request->getParam('realname') ? trim($request->getParam('realname')): '';// 邮箱
        $email = $request->getParam('email') ? trim($request->getParam('email')): '';// 邮箱
        $content = $request->getParam('content') ? htmlspecialchars(trim($request->getParam('content'))): '';// 模板内容
        
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        
        if(empty($uid)){
            IS_AJAX && ajaxReturns(0,'投递用户id为空',0);
        }
        
        if(empty($title)){
            IS_AJAX && ajaxReturns(0,'投递模板名称为空',0);
        }
		
        if(empty($email)){
            IS_AJAX && ajaxReturns(0,'求职者邮箱为空',0);
        }

        if(empty($ctitle)){
            IS_AJAX && ajaxReturns(0,'投递的公司和职位信息为空',0);
        }
        
        if(empty($content)){
            IS_AJAX && ajaxReturns(0,'投递模板内容为空',0);
        }
        
        $deliverysmodel = $this->load('delivery');
        
        $where = $insert = $logs = $edit = array();
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,old_rid,u_id,opreate_status');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['opreate_status'] == 4){
            IS_AJAX && ajaxReturns(0,'已经操作过不合适',0);
        }
        
        //将消息插入消息队列表 后期可用redis代替
        //修改状态
        $edit = array();
        $edit['opreate_status'] = 4;
        $edit['browse_status'] = 1;
        $edit['browse_time'] = time();//更新简历浏览时间 用于计算简历处理用时
        $flags = $deliverysmodel->edit_delivery($edit,$id);
        
        //添加日志 前端求职用户查看状态
        $insert['jd_id'] = $logs['jd_id'] = $id;
        $insert['note'] = $logs['note'] = '简历被标记为不合适';
        $insert['info'] =  $logs['info'] = 
		serialize(
                array(
                    '求职者：'=>$realname,
                    '主题：'=>$ctitle.'招聘反馈通知',
                    '拒绝内容：'=>$content,
                    '求职邮箱：'=>$email
                )
         );
        $insert['status'] = $logs['status'] = 2; //不合适
        $insert['add_time'] = $logs['add_time'] = time();
        $insert['add_userid'] = $logs['add_userid'] = $this->_uid;
        $flaglog = $deliverysmodel->insertlogs($logs);
        
        //将消息插入消息队列表 后期可用redis代替 用于后面发送邮件使用
        $insert['uid'] = $demlist['u_id'] ? $demlist['u_id'] : $uid;
        $insert['r_id'] = $demlist['old_rid'];
        $flag = $deliverysmodel->insertqueue($insert);

        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $flag &&  false !== $flags && false !== $flaglog){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'操作成功,该简历已到不合适',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'操作失败',0); 
        }
    }
    
    /*
     * 批量不合适
     */
    function actionpatchrefuse(){
        $request = new grequest();
        $id = $request->getParam('id') ? $request->getParam('id'):0;// 投递简历id
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        $ids = explode(",", $id);
        if(empty($ids)){
           IS_AJAX && ajaxReturns(0,'投递简历id为空',0); 
        }
        
        $deliverysmodel = $this->load('delivery');
        //查找相关信息
        $where['id'] = $ids;
        $where['hr_uid'] = $this->_uid;
        $delinfo = $deliverysmodel->getdeliverys($where,'id,old_rid,u_id,hr_uid,company_id,job_id');
        if(empty($delinfo)){
            IS_AJAX && ajaxReturns(0,'所要操作的内容不存在',0);
        }
        //查找不合适的模板
        $templates = $this->load("templates");
        $where = array();
        $where['uid'] = $this->_uid;
        $where['status'] = 1;
        $temlist = $templates->getrefuseinfos($where);
        if(empty($temlist)){
            IS_AJAX && ajaxReturns(0,'默认的不合适模版不存在',0);
        }	
        $companym = $this->load("company");
        $jobs = $this->load("jobs");
        $members = $this->load("foreuser");
        foreach($delinfo as $k=>$v){
            $f = $fl = $fla = true;  
            $insert = $edit = $logs = $companyinfo = $jobsinfo = $where = $mlist = array();
            //获取公司信息
            $companyinfo = $companym->getcompanyinfo($v['company_id'],"c_id,c_name,c_short_name");
            //获取职位信息
            $jobsinfo = $jobs->getjobs($v['job_id'],"job_id,typejob_name");
            //修改状态
            $edit['opreate_status'] = 4;
            $flags = $deliverysmodel->edit_delivery($edit,$v['id']);
            if(false === $flags){
                $fl = false;
            }
            
            $where['uid'] = $v['u_id'];
            $mlist = $members->getUserInfo($where,'uid,email,phone,username,realname');
            //添加日志 前端求职用户查看状态
            $insert['jd_id'] = $logs['jd_id'] = $v['id'];
            $insert['note'] = $logs['note'] = '简历被标记为不合适';
            $insert['info'] = $logs['info'] = 
                serialize(
                    array(
                            '求职者：'=>$mlist['realname'],
                            '主题：'=>$companyinfo['c_name'].':'.$jobsinfo['typejob_name'].'招聘反馈通知',
                            '拒绝内容：'=>$content,
                            '求职邮箱：'=>$mlist['email']
                    )
                );
            $insert['status'] = $logs['status'] = 2; //批量不合适
            $insert['add_time'] = $logs['add_time'] = time();
            $insert['add_userid'] = $logs['add_userid'] = $this->_uid;
            $flaglog = $deliverysmodel->insertlogs($logs);
            if(false === $flaglog){
                $fla = true;
            }
            
            //将消息插入消息队列表 后期可用redis代替  用于后面发送邮件使用
            $insert['uid'] = $v['u_id'];
            $insert['r_id'] = $v['old_rid'];
            $flag = $deliverysmodel->insertqueue($insert);
            if(false === $flag){
                $f = false;
            }
        }
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $f &&  false !== $fl && false !== $fla){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'操作成功,简历已批量到不合适',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'操作失败',0); 
        }
    }
    
    
    
    /*
     * 转发操作
     */
    function actionforward(){
        IS_AJAX && ajaxReturns(0,'等待后续开发',0);
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        //查找投递的信息
        $deliverymodels = $this->load("delivery");
        $where = array();
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverymodels->getdelivery($where,'id,old_rid,u_id,hr_uid,job_id,forward_status,forward_email');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['forward_status'] == 1){
            IS_AJAX && ajaxReturns(0,'已经转发过了',$demlist['forward_email']);
        }
        
        $jobidArr = $jobArr = $jobinfoArr =  $useridArr = $userArr = $userinfoArr = array();
        //职位信息
        $jobsmodel = $this->load('jobs');
        $jobidArr = array(0=>$demlist['job_id']);
        $jobArr = $jobsmodel->getjobinfo($jobidArr, 'job_id,typejob_name');
        foreach ($jobArr as $k => $v) {
            $jobinfoArr[$v['job_id']] = $v['typejob_name'];
        }
        
        if(empty($jobinfoArr[$demlist['job_id']])){
            IS_AJAX && ajaxReturns(0,'职位信息不存在',0);
        }

        //用户基本信息
        $members = $this->load('foreuser');
        $useridArr = array(0=>$demlist['u_id']);
        $userArr = $members->getinfobyuid($useridArr, 'uid,username,email,realname,phone');
        foreach ($userArr as $uk => $uv) {
            $userinfoArr[$uv['uid']] = $uv;
        }
        
        if(empty($userinfoArr[$demlist['u_id']])){
            IS_AJAX && ajaxReturns(0,'用户信息不存在',0);
        }
        
        unset($where,$jobArr,$jobidArr,$useridArr,$userArr);
        $response =  $this->renderPartial('forward',array('id'=>$id,'demlist'=>$demlist,'jobinfoArr'=>$jobinfoArr,'userinfoArr'=>$userinfoArr));
        IS_AJAX && ajaxReturns(1,0,$response); 
    }
    
    
    
    /*
     * 转发功能操作
     */
    function actiondoforward(){
        IS_AJAX && ajaxReturns(0,'等待后续开发',0);
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
        $email = $request->getParam('email') ? htmlspecialchars(trim($request->getParam('email'))): '';// 用户邮箱
        $title = $request->getParam('title') ? trim($request->getParam('title')): '';// 模板名称
        $content = $request->getParam('content') ? htmlspecialchars(trim($request->getParam('content'))): '';// 模板内容
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        
        if(empty($uid)){
            IS_AJAX && ajaxReturns(0,'投递用户id为空',0);
        }
        
        if(empty($title)){
            IS_AJAX && ajaxReturns(0,'主题为空',0);
        }
        
        if(empty($content)){
            IS_AJAX && ajaxReturns(0,'内容为空',0);
        }
        
        $deliverysmodel = $this->load('delivery');
        $where = array();
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,old_rid,u_id,hr_uid,job_id,forward_status,forward_email');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['forward_status'] == 1){
            IS_AJAX && ajaxReturns(0,'已经转发过了',$demlist['forward_email']);
        }
        
        //将消息插入消息队列表 后期可用redis代替
//        $insert = array();
//        $insert['uid'] = $uid;
//        $insert['r_id'] = $demlist['old_rid'];
//        $insert['receive'] = $email;
//        $insert['title'] = $title;
//        $insert['content'] = $content;
//        $flag = $deliverysmodel->insertqueue($insert);
        $flag = true;
        //更新投递信息中的转发状态和转发邮箱
        $edit = array();
        $edit['forward_status'] = 1;
        $edit['forward_email'] = $email;
        $flags = $deliverysmodel->edit_delivery($edit,$id);
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $flag && false !== $flags){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'操作成功,该简历已转发到相应邮箱，稍后就会受到邮件',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'操作失败',0); 
        }
    }
    
    
    /*
     * 查看联系方式操作
     */
    function actionview(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        
        $where = array();
        $deliverysmodel = $this->load('delivery');
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,old_rid,u_id,hr_uid,job_id,opreate_status');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['opreate_status'] == 2){
            IS_AJAX && ajaxReturns(0,'已经查看过了',0);
        }
        
        
        //查找不合适的模板
        $members = $this->load("foreuser");
        unset($where);
        $where['uid'] = $uid;
        $mlist = $members->getUserInfo($where,'uid,email,phone,username,realname');
        if(empty($mlist)){
            IS_AJAX && ajaxReturns(0,'该用户不存在',0);
        }
        $response =  $this->renderPartial('view',array('id'=>$id,'uid'=>$uid,'mlist'=>$mlist));
        IS_AJAX && ajaxReturns(1,0,$response); 
    }
    
    
    /*
     * 查看联系方式操作
     */
    function actiondoview(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
//        $realname = $request->getParam('realname') ? htmlspecialchars(trim($request->getParam('realname'))): '';// 用户名称
//        $email = $request->getParam('email') ? htmlspecialchars(trim($request->getParam('email'))): '';// 用户邮箱
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        
        if(empty($uid)){
            IS_AJAX && ajaxReturns(0,'投递用户id为空',0);
        }
        
//        if(empty($realname)){
//            IS_AJAX && ajaxReturns(0,'联系人为空',0);
//        }
//        
//        if(empty($email)){
//            IS_AJAX && ajaxReturns(0,'联系邮箱为空',0);
//        }
        
        $where = array();
        
        $deliverysmodel = $this->load('delivery');
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,opreate_status');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['opreate_status'] == 2){
            IS_AJAX && ajaxReturns(0,'已经查看过了',0);
        }

        //获取hr的相关信息
        $uids = array(0=>$this->_uid);
        $members = $this->load("foreuser");
        $mlist = $members->getinfobyuid($uids,'uid,email,phone,username,realname');
        foreach($mlist as $mk=>$mv){
            $mlists[$mv['uid']] = $mv;
        }

        //修改状态 到待沟通
        $edit = array();
        $edit['opreate_status'] = 2;
        $edit['browse_status'] = 1; //被查看联系方式
        $edit['browse_time'] = time();//查看时间
        $edit['view_status'] = 1; //被查看联系方式
        $edit['view_time'] = time();//查看时间
        $flags = $deliverysmodel->edit_delivery($edit,$id);
        //添加日志
        $logs = array();
        $logs['jd_id'] = $id;
        $logs['note'] = '你的简历已经通过初筛，企业承诺与您进行沟通';
        $logs['info'] = serialize(array('联系人：'=>$mlists[$this->_uid]['realname'],'联系邮箱：'=>$mlists[$this->_uid]['email']));
        $logs['status'] = 3; //待沟通
        $logs['add_time'] = time();
        $logs['add_userid'] = $this->_uid;
        $flaglog = $deliverysmodel->insertlogs($logs);
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $flags && false !== $flaglog){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'操作成功,该简历已到待沟通',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'操作失败',0); 
        }
    }
    
    
    
    /*
     * 通知面试操作
     */
    function actioninterview(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        //查找不合适的模板
        $where = $mlist = $uids = array();
        $deliverysmodel = $this->load('delivery');
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,old_rid,u_id,company_id,job_id,hr_uid,job_id,invite_status,invite_time');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['invite_status'] == 1 && $demlist['invite_time'] > 0){
            IS_AJAX && ajaxReturns(0,'已经邀请过了',0);
        }
        
        //获取公司信息
        $companym = $this->load("company");
        $companyinfo = $companym->getcompanyinfo($demlist['company_id'],"c_id,c_name,c_short_name");
        //获取职位信息
        $jobs = $this->load("jobs");
        $jobsinfo = $jobs->getjobs($demlist['job_id'],"job_id,typejob_name");
        
        $uids = array(0=>(int)$this->_uid,1=>$uid);
        $members = $this->load("foreuser");
        $mlist = $members->getinfobyuid($uids,'uid,email,phone,username,realname');
        foreach($mlist as $mk=>$mv){
            $mlists[$mv['uid']] = $mv;
        }
        
        if(empty($mlist)){
            IS_AJAX && ajaxReturns(0,'该用户不存在',0);
        }
        
        
        $templates = $this->load("templates");
        unset($where);
        $where['uid'] = $this->_uid;
        $teminfo = $templates->getinterviewinfos($where);
//        
//        if(empty($teminfo)){
//            IS_AJAX && ajaxReturns(0,'通知模板信息不存在,请联系管理员添加',0);
//        }
        unset($where,$mlist,$uids);
        $response =  $this->renderPartial('interview',array('id'=>$id,'uid'=>$uid,'mlist'=>$mlists,'temlist'=>$teminfo,'companyinfo'=>$companyinfo,'jobsinfo'=>$jobsinfo));
        IS_AJAX && ajaxReturns(1,0,$response); 
    }
    
    
    /*
     * 通知面试操作
     */
    function actiondointerview(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
        $email = $request->getParam('email') ? htmlspecialchars(trim($request->getParam('email'))): '';// 用户邮箱
        $realname = $request->getParam('realname') ? htmlspecialchars(trim($request->getParam('realname'))): '';// 联系人
        $phone = $request->getParam('phone') ? htmlspecialchars(trim($request->getParam('phone'))): '';// 联系电话
        $title = $request->getParam('title') ? htmlspecialchars(trim($request->getParam('title'))): '';// 主题
        $interviewtime = $request->getParam('interviewtime') ? htmlspecialchars(trim($request->getParam('interviewtime'))): '';// 面试时间
        $temid = $request->getParam('temid') ? (int)$request->getParam('temid'):0;// 模板id
        $content = $request->getParam('content') ? htmlspecialchars(trim($request->getParam('content'))): '';// 补充内容
        $address = $request->getParam('address') ? htmlspecialchars(trim($request->getParam('address'))): '';// 面试地点
        
       
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        
        if(empty($uid)){
            IS_AJAX && ajaxReturns(0,'投递用户id为空',0);
        }
        
        
        if(empty($email)){
            IS_AJAX && ajaxReturns(0,'联系邮箱为空',0);
        }
        
        
        if(empty($realname)){
            IS_AJAX && ajaxReturns(0,'联系人为空',0);
        }
        
        if(empty($phone)){
            IS_AJAX && ajaxReturns(0,'联系人电话为空',0);
        }
        
        if(empty($title)){
            IS_AJAX && ajaxReturns(0,'主题为空',0);
        }
        
        if(empty($interviewtime)){
            IS_AJAX && ajaxReturns(0,'面试时间为空',0);
        }
        
        if(empty($temid)){
            IS_AJAX && ajaxReturns(0,'通知模板为空',0);
        }
        
//        if(empty($content)){
//            IS_AJAX && ajaxReturns(0,'补充内容为空',0);
//        }
        
        if(empty($address)){
            IS_AJAX && ajaxReturns(0,'面试地址为空',0);
        }
  
        $deliverysmodel = $this->load('delivery');
        $where = $insert = $logs = array();
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,old_rid,u_id,hr_uid,job_id,invite_status,invite_time');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['invite_status'] == 1 && $demlist['invite_time'] > 0){
            IS_AJAX && ajaxReturns(0,'已经邀请过了',0);
        }
        
        $uids = array(0=>$uid);
        $members = $this->load("foreuser");
        $mlist = $members->getinfobyuid($uids,'uid,email,phone,username,realname');
        foreach($mlist as $mk=>$mv){
            $mlists[$mv['uid']] = $mv;
        }
        //添加日志 前端求职查看
        $insert['jd_id'] = $logs['jd_id'] = $id;
        $insert['note'] = $logs['note'] = $this->_realname.'给你发来面试通知';
        $insert['info'] = $logs['info'] = 
        serialize(
                array(
                    '求职者：'=>$mlists[$uid]['realname'],
                    '主题：'=>$title,
                    '面试时间：'=>$interviewtime,
                    '联系人：'=>$realname,
                    '联系电话：'=>$phone,
                    '补充内容：'=>$content,
                    '面试地点：'=>$address,
                    '联系邮箱：'=>$email,
                    '模板id：'=>$temid
                )
        );
        $insert['status'] = $logs['status'] = 4; //发送面试邀请通知
        $insert['add_time'] = $logs['add_time'] = time();
        $insert['add_userid'] = $logs['add_userid'] = $this->_uid;
        $flaglog = $deliverysmodel->insertlogs($logs);
        
        //将消息插入消息队列表 后期可用redis代替 用于后面发送邮件使用
        $insert['uid'] = $uid;
        $insert['r_id'] = $demlist['old_rid'];
        $flag = $deliverysmodel->insertqueue($insert);
        
        //修改状态 到待沟通
        $edit = array();
        $edit['invite_status'] = 1;
        $edit['opreate_status'] = 3; //已安排
        $edit['invite_time'] = time();//查看时间
        $flags = $deliverysmodel->edit_delivery($edit,$id);
        
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $flag && false !== $flaglog && false !== $flags){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'面试通知操作成功',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'面试通知操作失败',0); 
        }
    }
    
    
    
    /*
     * 标记面试操作
     */
    function actionviewtag(){
        die("非法请求");
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        $where = array();
        
        $deliverysmodel = $this->load('delivery');
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,opreate_status');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['opreate_status'] == 3){
            IS_AJAX && ajaxReturns(0,'已安排过了',0);
        }
        //获取投递邀请面试通知数据
        $where['jd_id'] = $id;
        $where['status'] = 4;
        $delogs = $deliverysmodel->getdeliverylogs($where);
        if(empty($delogs)){
            IS_AJAX && ajaxReturns(0,'还没有发送邀请通知，请先点面试进行通知后操作',0);
        }
        //查找不合适的模板
        $members = $this->load("foreuser");
        unset($where);
        $where['uid'] = $this->_uid;
        $mlist = $members->getUserInfo($where,'uid,email,phone,username,realname');
        if(empty($mlist)){
            IS_AJAX && ajaxReturns(0,'该用户不存在',0);
        }
        $response =  $this->renderPartial('viewtag',array('id'=>$id,'uid'=>$uid,'mlist'=>$mlist,'delogs'=>unserialize($delogs[0]['info'])));
        IS_AJAX && ajaxReturns(1,0,$response); 
    }
    
    
    /*
     * 标记面试操作
     */
    function actiondoviewtag(){
        die("非法请求");
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 投递简历id
        $uid = $request->getParam('uid') ? (int)$request->getParam('uid'):0;// 用户id
       
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'投递简历id为空',0);
        }
        
        if(empty($uid)){
            IS_AJAX && ajaxReturns(0,'投递用户id为空',0);
        }
        $deliverysmodel = $this->load('delivery');
        $where['hr_uid'] = $this->_uid;
        $where['id'] = $id;
        $demlist = $deliverysmodel->getdelivery($where,'id,opreate_status');
        if(empty($demlist)){
            IS_AJAX && ajaxReturns(0,'投递信息找不到',0);
        }
        
        if($demlist['opreate_status'] == 3){
            IS_AJAX && ajaxReturns(0,'已经标记已安排过了',0);
        }
        
        //修改状态 到待沟通
        $edit = array();
        $edit['opreate_status'] = 3; //已安排
        $flags = $deliverysmodel->edit_delivery($edit,$id);
        
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $flags){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'操作成功,该简历已到已安排',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'操作失败',0); 
        }
    }
    
    
    
    /*
     * 单个加入文件夹
     */
    function actionjoinfolders(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 简历id
       
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'简历id为空',0);
        }
        
        $resume = $this->load('resume');
        //根据简历id获取简历信息，
        $resumeinfo = $resume->getresumebyarr(array('rid'=>$id),'id,u_id');
        if(!$resumeinfo){
            IS_AJAX && ajaxReturns(0,'简历信息有误，请核对后再加入文件夹',0);
        }
        
        //投递简历
        $delivery = $this->load('delivery');
        $data = array(
            'old_rid' => $id,
            'u_id' => $resumeinfo['u_id'],
            'hr_uid' => $this->_uid,
            'add_time' => time(),
        );
        
        $folders = $delivery->getfolder($data);
        if(!empty($folders)){
            IS_AJAX && ajaxReturns(0,'已经加入过文件夹',0);
        }
        
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        $delivery_id = $delivery->insert_folders($data);
        if($delivery_id){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'加入文件夹成功！',0);
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'加入文件夹失败',0);
        }
    }
    
    
    /*
     * 单个移出文件夹
     */
    function actionoutfolders(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 文件夹id
       
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'id为空',0);
        }
        $deliverysmodel = $this->load('delivery');
        $data = array(
            'id' => $id
        );
        $folders = $deliverysmodel->getfolder($data);
        if(empty($folders)){
            IS_AJAX && ajaxReturns(0,'已经移出文件夹',0);
        }
        
        $flags = $deliverysmodel->delete_folders($id);
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $flags){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'该简历已经移出文件夹',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'移出失败',0); 
        }
    }
    
    
    /*
     * 批量加入文件夹
     */
    function actionpatchjoinfolders(){
        $request = new grequest();
        $id = $request->getParam('id') ? $request->getParam('id'):0;// 投递简历id 多个
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'简历id为空',0);
        }
        $ids = explode(",", $id);
        if(empty($ids)){
           IS_AJAX && ajaxReturns(0,'简历id为空',0); 
        }
        $deliverysmodel = $this->load('delivery');
        $resume = $this->load('resume');
        //修改状态 加入文件夹
        $flag = true;
        $i = 0;
        foreach($ids as $k=>$id){
            $data = array(
                'old_rid' => $id,
				'u_id' => $resumeinfo['u_id'],
                'hr_uid' => $this->_uid,
            );
            $folders = $deliverysmodel->getfolder($data);
            if(!empty($folders)){ //当已经存在后就不能再次加入文件夹
                $i++;
                continue;
            }
            $resumeinfo = array();
            //根据简历id获取简历信息，
            $resumeinfo = $resume->getresumebyarr(array('rid'=>$id),'id,u_id');
            $data = array(
                'old_rid' => $id,
                'u_id' => $resumeinfo['u_id'],
                'hr_uid' => $this->_uid,
                'add_time' => time(),
            );
            $flags = $deliverysmodel->insert_folders($data);
            if(false === $flags){
                $flag = false;
            }
        }
        if($i == count($ids)){
            IS_AJAX && ajaxReturns(0,'简历已经加入过了',0); 
        }
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $flag){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'简历批量加入文件夹',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'加入失败',0); 
        }
    }
    
    
    /*
     * 批量移出文件夹
     */
    function actionpatchoutfolders(){
        $request = new grequest();
        $id = $request->getParam('id') ? $request->getParam('id'):0;// 投递简历id 多个
        if(empty($id)){
            IS_AJAX && ajaxReturns(0,'id为空',0);
        }
        $ids = explode(",", $id);
        if(empty($ids)){
           IS_AJAX && ajaxReturns(0,'id为空',0); 
        }
        $deliverysmodel = $this->load('delivery');
        //修改状态 移出文件夹
        $flag = true;
        $i = 0;
        foreach($ids as $k=>$id){
            $data = array(
                'id' => $id
            );
            $folders = $deliverysmodel->getfolder($data);
            if(empty($folders)){ //当为空的时候表示文件夹已经不存在该简历
                $i++;
                continue;
            }
            $flags = $deliverysmodel->delete_folders($id);
            if(false === $flags){
                $flag = false;
            }
        }
        if($i == count($ids)){
            IS_AJAX && ajaxReturns(0,'简历已经移出过了',0); 
        }
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(false !== $flag){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'简历已经批量移出文件夹',0); 
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'移出失败',0); 
        }
    }
    
    
    /**
     * 删除投递的简历
     */
    function actiondeldeliverys(){
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id'):0;//投递id
        if(empty($id)){
           IS_AJAX && ajaxReturns(0,'投递id为空',0); 
        }
        $deliverysmodel = $this->load('delivery');
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
		$edit = $edit2 = $edit3 = array();
		$edit['folders_status'] = 1;
		$edit2['del_status'] = 1;
		$edit3['status'] = 10;
        $ret = $deliverysmodel->delete_deliverys($edit,$id);
		$ret2 = $deliverysmodel->editdeliverylogbytdid($edit2,$id);
		$ret3 = $deliverysmodel->editmsgquerid($edit3,$id);
        if(false !== $ret && false !== $ret2 && false !== $ret3){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'投递信息删除成功',0);
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'投递信息删除失败',0);
        }
    }
}
?>
