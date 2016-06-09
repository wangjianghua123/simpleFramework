<?php
/**
 * 职位管理 wjh
 */
!defined('IN_UC') && exit('Access Denied');

class jobs extends base{
    public $_uid;
    public $_uc_usertype;//1普通用户 2企业用户
    public $_company_id;
    public $_typejobdata ; //职位格式化后的 多级分类
    public $_typejob;//职位类别
    public $_cachebasicdata;//基本信息数据
    public $_citydata;//地区信息
    public $_citytreedata; //地区树
    public $webseo = array(
        'title' => '找好工作,来快就业网',
        'keywords' => '就业,求职,招聘,公司招聘,高薪职位,大学生就业',
        'description' => '找好工作,来快就业网'
    );
    function __construct() {
        header("Content-type:text/html;charset=utf-8;");
        parent::__construct();
        $this->_typejobdata = S('typejob_list');
        $this->_typejob = S('typejob');
        $this->_cachebasicdata = S('getbasicdata');
        $this->_citydata = S('areacache');
        $this->_citytreedata = S('areatree');
    }
    
    function userlogin(){
        if(!$this->session('uc_offcn_uid')){
            IS_AJAX && ajaxReturns(2,'请重新登录后操作','请重新登录后操作');
            $this->redirect($this->url('login','','foreuser'));
        }else{
            $this->_uid = $this->session('uc_offcn_uid');
            $this->_company_id = $this->session('uc_company_id');
            $this->_uc_usertype = $this->session('uc_usertype');
        }
    }
    
    /**
     * 有效职位库
     */
    function actionindex(){
        $this->userlogin();
        $this->getcertstatus();
        $this->basevar['title'] = "有效职位";
        $this->render('index',array(
            'seoinfo' => $this->webseo
        ));
    }
    
    /*
     * 该公司HR发布的招聘职位
     */
    function actiongetPositions() {
        $this->userlogin();
        $this->getcertstatus();
        //发布招聘职位
        $request = new grequest();
        $page = $request->getParam('page') ? $request->getParam('page'):1;//分页 
        $searchvalue = $request->getParam('searchvalue') ? htmlspecialchars(trim($request->getParam('searchvalue'))):'';//职位名称搜索
        $limit = 12;
        $jobsmodel = $this->load('jobs');
        $total = 0;
        $list = $jobidArr = $jobArr = $jobinfoArr = $where = array();
        $where['uid'] = $this->_uid;
        $where['c_id'] = $this->_company_id;
        $where['status'] = 0;
        $where['search'] = $searchvalue; //typejob_name
        $where['limit'] = $limit;
        $where['page'] = $page;
        $jobsolrs = $jobsmodel->getjobslistforsolr($where);
        if(!empty($jobsolrs['nums'])){
            $list = $jobsolrs['docs'];
            $total = $jobsolrs['nums'];
        }
        if(!empty($list)){
            $pager = '';
            if($total >= $limit){
                $pager = $this->pageAjax('jobsearch', $total, $page, $limit, 5, $where);
            }
            foreach ($list as $k => $v){
                $jobBNumArr['bnum_'.$v['job_id']] = true;
                $jobDNumArr['dnum_'.$v['job_id']] = true;
            }
            $jobBNumArr = array_keys($jobBNumArr);
            $jobDNumArr = array_keys($jobDNumArr);
            $rediscache = $this->init_rediscaches();
            $rediscache->select('1');
            $bnums = $rediscache->hmget('job_browse_num',$jobBNumArr);
            $dnums = $rediscache->hmget('job_browse_num',$jobDNumArr);
        }
        
        $response = $this->renderPartial("ajaxjobspage", array(
            'list'=>$list,
            'total'=>$total,
            'pags'=>$pager,
            'searchvalue'=>$searchvalue,
            'bnums'=>$bnums,
            'dnums'=>$dnums,
            '_citydata'=>$this->_citydata,
            '_cachebasicdata'=>$this->_cachebasicdata,
            "page" => $page
        ));
        $searchs['total'] = $total;
        $searchs['searchvalue'] = $searchvalue;
        IS_AJAX && ajaxReturns(1, $pager, $response, $searchs);
    }
    
    
    /**
     * 已下线职位库
     */
    function actionexpired(){
        $this->userlogin();
        $this->getcertstatus();
        $this->render('expired',array(
            'seoinfo' => $this->webseo
        ));
    }
    
    /*
     * 该公司HR发布的下线招聘职位
     */
    function actiongetExpiredPositions() {
        $this->userlogin();
        $this->getcertstatus();
        //发布招聘职位
        $request = new grequest();
        $page = $request->getParam('page') ? $request->getParam('page'):1;//分页 
        $searchvalue = $request->getParam('searchvalue') ? htmlspecialchars(trim($request->getParam('searchvalue'))):'';//职位名称搜索
        $limit = 12;
        $jobsmodel = $this->load('jobs');
        $total = 0;
        $list = $jobidArr = $jobArr = $jobinfoArr = $where = array();
        $where['uid'] = $this->_uid;
        $where['c_id'] = $this->_company_id;
        $where['status'] = 1;
        $where['search'] = $searchvalue; //typejob_name
        $where['limit'] = $limit;
        $where['page'] = $page;
//        $jobsolrs = $jobsmodel->getjobslistforsolr($where);
        $total = $jobsmodel->getjobspagetotal($where);
        $list = $jobsmodel->getjobspagelist($limit,($page-1)*$limit,$where);

//        if(!empty($jobsolrs['nums'])){
//            $list = $jobsolrs['docs'];
//            $total = $jobsolrs['nums'];
//        }
        if(!empty($list)){
            $pager = '';
            if($total >= $limit){
                $pager = $this->pageAjax('jobsearch', $total, $page, $limit, 5, $where);
            }
            foreach ($list as $k => $v){
                $jobBNumArr['bnum_'.$v['job_id']] = true;
                $jobDNumArr['dnum_'.$v['job_id']] = true;
            }
            $jobBNumArr = array_keys($jobBNumArr);
            $jobDNumArr = array_keys($jobDNumArr);
            $rediscache = $this->init_rediscaches();
            $rediscache->select('1');
            $bnums = $rediscache->hmget('job_browse_num',$jobBNumArr);
            $dnums = $rediscache->hmget('job_browse_num',$jobDNumArr);
        }
        
        $response = $this->renderPartial("ajaxexpiredjobspage", array(
            'list'=>$list,
            'total'=>$total,
            'pags'=>$pager,
            'searchvalue'=>$searchvalue,
            'bnums'=>$bnums,
            'dnums'=>$dnums,
            '_citydata'=>$this->_citydata,
            '_cachebasicdata'=>$this->_cachebasicdata,
            "page" => $page
        ));
        $searchs['total'] = $total;
        $searchs['searchvalue'] = $searchvalue;
        IS_AJAX && ajaxReturns(1, $pager, $response, $searchs);
    }
    
    /*
     * 添加职位
     */
    function actionaddjobs(){
        $this->userlogin();
        $this->BindCompany();
        $this->render('addjobs', 
                array('_typejobdata'=>$this->_typejobdata,
                    '_cachebasicdata'=>$this->_cachebasicdata,
                    '_citytreedata'=>$this->_citytreedata,
                    '_citydata'=>$this->_citydata,
                    'seoinfo' => $this->webseo
                )
        );
    }
    
    /*
     * 判断是否已经绑定公司
     */
    function BindCompany(){
        if($this->_uc_usertype == 2 && $this->_company_id == 0){
            IS_AJAX && ajaxReturns(0,'请绑定公司后进行操作',0);
            ShowMsg("请绑定公司后进行操作",$this->url('writeusertocompany','','userforcompany'));die;
        }else if($this->_uc_usertype == 1){
            IS_AJAX && ajaxReturns(0,'对不起，您没有操作权限',0);
            ShowMsg("对不起，您没有操作权限",$this->url('index','','resume'));
            die;
        }
        $companymodel = $this->load("company");
        $basic = $companymodel->getcompanyinfo($this->_company_id);
//        if(!empty($basic['c_add_userid']) && $basic['c_add_userid'] != $this->_uid){
//            IS_AJAX && ajaxReturns(0,'该公司不是您创建的,无法进行下一步操作',0);
//            ShowMsg("该公司不是您创建的,无法进行下一步操作",$this->url('index','','user'));die;
////        }
//        if(168 ==  $this->_uid){
//			dump(empty($basic['c_name']) || empty($basic['c_short_name']) || empty($basic['c_homepage']));
//		}
        if(empty($basic['c_name']) || empty($basic['c_short_name']) || empty($basic['c_homepage']) || empty($basic['c_addr'])){
            IS_AJAX && ajaxReturns(0,'请先完善公司信息,公司简称、公司主页、公司地址必填',0);
            ShowMsg("请先完善公司信息,公司简称、公司主页、公司地址必填",$this->url('index','','company'));die;
        }
        $this->getcertstatus();
    }

    /*
     * 添加职位提交
     */
    function actiondoaddjobs(){
        $this->userlogin();
        $this->BindCompany();
        $request = new grequest();
        $typejob_id = $request->getParam('positionType') ? (int)$request->getParam('positionType'):0;// 职位类型id
        $typejob_name = $request->getParam('positionName') ? htmlspecialchars(trim($request->getParam('positionName'))) : '';// 职位名称
        $job_nature = $request->getParam('jobNature') ? (int)$request->getParam('jobNature'):0;// 工作性质
        $salary_start = $request->getParam('salaryMin') ? (int)trim($request->getParam('salaryMin')):0;// 月薪范围开始
        $salary_end = $request->getParam('salaryMax') ? (int)trim($request->getParam('salaryMax')):0;// 月薪范围结束
        $department = $request->getParam('department') ? htmlspecialchars(trim($request->getParam('department'))):'';// 所属部门
        $city = $request->getParam('workAddress') ? (int)$request->getParam('workAddress'):0;// 工作城市id
        $experience = ($request->getParam('workYear')!="") ? (int)$request->getParam('workYear'):-1;// 工作经验
        $education = ($request->getParam('education')!="") ? (int)$request->getParam('education'):-1;// 学历要求
        $position_temptation = $request->getParam('positionAdvantage') ? htmlspecialchars(trim($request->getParam('positionAdvantage'))):0;// 职位诱惑
        $addr = $request->getParam('positionAddress') ?  htmlspecialchars(trim($request->getParam('positionAddress'))):'';// 工作地址
        $intro = $request->getParam('positionDetail') ?  trim($request->getParam('positionDetail')):'';// 职位描述
        $intro = htmlspecialchars(preg_replace('/style=".*?"/i', '', $intro));//过滤标签中的style属性
        if(empty($typejob_id)){
            IS_AJAX && ajaxReturns(0,'职位类型为空，请至少选择一个',0);
        }
        if(empty($typejob_name)){
            IS_AJAX && ajaxReturns(0,'职位名称为空，请填写',0);
        }
        if(!isset($job_nature)){
            IS_AJAX && ajaxReturns(0,'工作性质为空，请至少选择一个',0);
        }
        if(empty($salary_start)){
            IS_AJAX && ajaxReturns(0,'月薪范围开始为空，请填写',0);
        }
        if(empty($salary_end)){
            IS_AJAX && ajaxReturns(0,'月薪范围结束为空，请填写',0);
        }
        if($salary_start == $salary_end){
            IS_AJAX && ajaxReturns(0,'最高月薪需大于最低月薪',0);
        }
        if($salary_start > $salary_end){
            IS_AJAX && ajaxReturns(0,'最高月薪不能低于最低月薪',0);
        }
        if(($salary_start*2) < $salary_end){
            IS_AJAX && ajaxReturns(0,'最高月薪不能大于最低月薪的2倍',0);
        }
        if(empty($department)){
            IS_AJAX && ajaxReturns(0,'所属部门为空，请填写',0);
        }
        if(!isset($city)){
            IS_AJAX && ajaxReturns(0,'所在城市为空，请至少选择一个',0);
        }
        if($experience == -1){
            IS_AJAX && ajaxReturns(0,'工作经验为空，请至少选择一个',0);
        }
        if($education == -1){
            IS_AJAX && ajaxReturns(0,'学历要求为空，请至少选择一个',0);
        }
        if(empty($position_temptation)){
            IS_AJAX && ajaxReturns(0,'职位诱惑不能为空',0);
        }
        if(empty($addr)){
            IS_AJAX && ajaxReturns(0,'工作地址为空，请填写',0);
        }
        if(empty($intro)){
            IS_AJAX && ajaxReturns(0,'职位描述为空，请填写',0);
        }
        $insert = $insertintro = array();
        //职位基础表数据
        $insert['typejob_id']  = $typejob_id;
        $insert['typejob_name'] = $typejob_name;
        $insert['job_nature'] = $job_nature;
        $insert['salary_start'] = $salary_start;
        $insert['salary_end'] = $salary_end;
        $insert['salary_id'] = $this->getsyssalaryid($salary_start,$salary_end);
        $insert['department'] = $department;
        $insert['city'] = $city;
        $insert['prov'] = !empty($this->_citydata[$city]['parentid']) ? $this->_citydata[$city]['parentid'] : 0;
        $insert['experience'] = $experience;
        $insert['education'] = $education;
        $insert['position_temptation'] = $position_temptation;
        $insert['addr'] = $addr;
        $insert['uid'] = $this->_uid; //添加人
        $insert['company_id'] = $this->_company_id; //添加人公司
        $insert['create_time'] = time(); //添加时间
        $insert['refresh_time'] = time();//刷新时间 用于在首页显示最新职位(默认按照刷新时间排序)
        //职位描述详情表数据
        $insertintro['intro'] = $intro;
        $jobsmodel = $this->load("jobs");
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        $job_id = $jobsmodel->insert($insert);
        if(false !== $job_id){
            $insertintro['job_id'] = $job_id;
            $jobintro_id = $jobsmodel->insertintro($insertintro);
            if(false !== $jobintro_id){
                init_db()->createCommand()->query("COMMIT"); //成功提交
                IS_AJAX && ajaxReturns(1,'职位添加成功,请稍后查询',0);
            }else{
                init_db()->createCommand()->query("ROLLBACK"); //失败回滚
                IS_AJAX && ajaxReturns(0,'职位添加失败,数据正在回滚中。。。',0);
            }
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'职位添加失败,数据正在回滚中。。。',0);
        }
    }
    
    
    /*
     * 修改职位
     */
    function actioneditjobs(){
        $this->userlogin();
        $this->getcertstatus();
        //获取职位基本信息和职位详情信息
        $request = new grequest();
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;// 职位id
        if(empty($job_id)){
            ShowMsg("职位id为空",-1);die;
        }
        $jobinfo = $jobintroinfo = $where = array();
        $where['job_id'] = $job_id;
        $jobsmodel = $this->load("jobs");
        $jobinfo = $jobsmodel->getjobsinfo($where); //职位信息
        if($jobinfo['uid'] != $this->_uid){
            ShowMsg("不是您发布的职位不能修改",-1);die;
        }
        $jobintroinfo = $jobsmodel->getjobsintroinfo($where);//职位详情信息
        $this->render('editjobs', 
                array('_typejobdata'=>$this->_typejobdata,
                    '_typejob'=>$this->_typejob,
                    '_cachebasicdata'=>$this->_cachebasicdata,
                    '_citydata'=>$this->_citydata,
                    '_citytreedata'=>$this->_citytreedata,
                    'jobinfo'=>$jobinfo,
                    'jobintroinfo'=>$jobintroinfo,
                    'job_id'=>$job_id,
                    'seoinfo'=>$this->webseo
                )
        );
    }
    
    /*
     * 修改职位提交
     */
    function actiondoeditjobs(){
        $this->userlogin();
        $this->getcertstatus();
        $request = new grequest();
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;// 职位id
        $typejob_id = $request->getParam('positionType') ? (int)$request->getParam('positionType'):0;// 职位类型id
        $typejob_name = $request->getParam('positionName') ? htmlspecialchars(trim($request->getParam('positionName'))) : '';// 职位名称
        $job_nature = $request->getParam('jobNature') ? (int)$request->getParam('jobNature'):0;// 工作性质
        $salary_start = $request->getParam('salaryMin') ? (int)trim($request->getParam('salaryMin')):0;// 月薪范围开始
        $salary_end = $request->getParam('salaryMax') ? (int)trim($request->getParam('salaryMax')):0;// 月薪范围结束
        $department = $request->getParam('department') ? htmlspecialchars(trim($request->getParam('department'))):'';// 所属部门
        $city = $request->getParam('workAddress') ? (int)$request->getParam('workAddress'):0;// 工作城市id
        $experience = ($request->getParam('workYear')!="") ? (int)$request->getParam('workYear'):-1;// 工作经验
        $education = ($request->getParam('education')!="") ? (int)$request->getParam('education'):-1;// 学历要求
        $position_temptation = $request->getParam('positionAdvantage') ? htmlspecialchars(trim($request->getParam('positionAdvantage'))):0;// 职位诱惑
        $addr = $request->getParam('positionAddress') ?  htmlspecialchars(trim($request->getParam('positionAddress'))):'';// 工作地址
        $intro = $request->getParam('positionDetail') ? trim($request->getParam('positionDetail')): '';// 职位描述
        $intro = htmlspecialchars(preg_replace('/style=".*?"/i', '', $intro));//过滤标签中的style属性
        if(empty($job_id)){
            IS_AJAX && ajaxReturns(0,'职位id为空',0);
        }

        $jobsmodel = $this->load("jobs");
        $where['job_id'] = $job_id;
        $jobinfo = $jobsmodel->getjobsinfo($where); //职位信息
        if ($jobinfo['uid'] != $this->_uid) {
            ShowMsg("不是您发布的职位不能修改", -1);
            die;
        }


        if(empty($typejob_id)){
            IS_AJAX && ajaxReturns(0,'职位类型为空，请至少选择一个',0);
        }
        if(empty($typejob_name)){
            IS_AJAX && ajaxReturns(0,'职位名称为空，请填写',0);
        }
        if(empty($job_nature)){
            IS_AJAX && ajaxReturns(0,'工作性质为空，请至少选择一个',0);
        }
        if(empty($salary_start)){
            IS_AJAX && ajaxReturns(0,'月薪范围开始为空，请填写',0);
        }
        if(empty($salary_end)){
            IS_AJAX && ajaxReturns(0,'月薪范围结束为空，请填写',0);
        }
        if(empty($department)){
            IS_AJAX && ajaxReturns(0,'所属部门为空，请填写',0);
        }
        if(empty($city)){
            IS_AJAX && ajaxReturns(0,'所在城市为空，请至少选择一个',0);
        }
        if($experience == -1){
            IS_AJAX && ajaxReturns(0,'工作经验为空，请至少选择一个',0);
        }
        if($education == -1){
            IS_AJAX && ajaxReturns(0,'学历要求为空，请至少选择一个',0);
        }
        if(empty($position_temptation)){
            IS_AJAX && ajaxReturns(0,'职位诱惑为空，请填写',0);
        }
        if(empty($addr)){
            IS_AJAX && ajaxReturns(0,'工作地址为空，请填写',0);
        }
        if(empty($intro)){
            IS_AJAX && ajaxReturns(0,'职位描述为空，请填写',0);
        }
        $edit = $editintro = array();
        //职位基础表数据
        $edit['typejob_id']  = $typejob_id;
        $edit['typejob_name'] = $typejob_name;
        $edit['job_nature'] = $job_nature;
        $edit['salary_start'] = $salary_start;
        $edit['salary_end'] = $salary_end;
        $edit['salary_id'] = $this->getsyssalaryid($salary_start,$salary_end);
        $edit['department'] = $department;
        $edit['city'] = $city;
        $edit['prov'] = !empty($this->_citydata[$city]['parentid']) ? $this->_citydata[$city]['parentid'] : 0;
        $edit['experience'] = $experience;
        $edit['education'] = $education;
        $edit['position_temptation'] = $position_temptation;
        $edit['addr'] = $addr;
        $edit['uid'] = $this->_uid; //修改人
        $edit['company_id'] = $this->_company_id; //修改人公司
        $edit['update_time'] = time(); //修改时间
        //职位描述详情表数据
        $editintro['intro'] = $intro;
        

        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        $flag = $jobsmodel->edit_jobs($edit,$job_id);
        if(false !== $job_id){
            $jobintro_id = $jobsmodel->edit_jobsintro($editintro,$job_id);
            if(false !== $jobintro_id){
                init_db()->createCommand()->query("COMMIT"); //成功提交
                IS_AJAX && ajaxReturns(1,'职位修改成功,请稍后查询',0);
            }else{
                init_db()->createCommand()->query("ROLLBACK"); //失败回滚
                IS_AJAX && ajaxReturns(0,'职位修改失败,数据正在回滚中。。。',0);
            }
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'职位修改失败,数据正在回滚中。。。',0);
        }
    }
    
    
    
    /*
     * 职位预览
     */
    function actionpreview(){
        $this->userlogin();
        $this->getcertstatus();
        $request = new grequest();
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;// 职位id
        $typejob_id = $request->getParam('positionType') ? (int)$request->getParam('positionType'):0;// 职位类型id
        $typejob_name = $request->getParam('positionName') ? htmlspecialchars(trim($request->getParam('positionName'))) : '';// 职位名称
        $job_nature = $request->getParam('jobNature') ? (int)$request->getParam('jobNature'):0;// 工作性质
        $salary_start = $request->getParam('salaryMin') ? (int)trim($request->getParam('salaryMin')):0;// 月薪范围开始
        $salary_end = $request->getParam('salaryMax') ? (int)trim($request->getParam('salaryMax')):0;// 月薪范围结束
        $department = $request->getParam('department') ? htmlspecialchars(trim($request->getParam('department'))):'';// 所属部门
        $city = $request->getParam('workAddress') ? (int)$request->getParam('workAddress'):0;// 工作城市id
        $experience = $request->getParam('workYear') ? (int)$request->getParam('workYear'):0;// 工作经验
        $education = $request->getParam('education') ? (int)$request->getParam('education'):0;// 学历要求
        $create_time = $request->getParam('create_time') ? $request->getParam('create_time'):0;// 学历要求
        $position_temptation = $request->getParam('positionAdvantage') ? htmlspecialchars(trim($request->getParam('positionAdvantage'))):0;// 职位诱惑
        $addr = $request->getParam('positionAddress') ?  htmlspecialchars(trim($request->getParam('positionAddress'))):'';// 工作地址
        $intro = $request->getParam('positionDetail') ? trim($request->getParam('positionDetail')): '';// 职位描述
        $intro = htmlspecialchars(preg_replace('/style=".*?"/i', '', $intro));//过滤标签中的style属性
        $jobinfo = array();
        //职位基础表数据
        $jobinfo['typejob_id']  = $typejob_id;
        $jobinfo['typejob_name'] = $typejob_name;
        $jobinfo['job_nature'] = $job_nature;
        $jobinfo['salary_start'] = $salary_start;
        $jobinfo['salary_end'] = $salary_end;
        $jobinfo['department'] = $department;
        $jobinfo['city'] = $city;
        $jobinfo['experience'] = $experience;
        $jobinfo['education'] = $education;
        $jobinfo['position_temptation'] = $position_temptation;
        $jobinfo['addr'] = $addr;
        $jobinfo['create_time'] = $create_time;
        //职位描述详情表数据
        $jobinfo['intro'] = $intro;
        //公司信息
        $companymodel = $this->load("company");
        $companyinfo = $companymodel->getcompanyinfobyid($this->_company_id);
        $this->render('preview', 
                array(
                    '_typejobdata'=>$this->_typejobdata,
                    '_typejob'=>$this->_typejob,
                    '_cachebasicdata'=>$this->_cachebasicdata,
                    '_citydata'=>$this->_citydata,
                    'jobinfo'=>$jobinfo,
                    'companyinfo'=>$companyinfo,
                    'seoinfo'=>$this->webseo
                )
        );
    }
    
    
    
    /*
     * 职位详情页面
     */
    function actionjobsdetail(){
        $uid = $this->session('uc_offcn_uid');
        //获取职位基本信息和职位详情信息
        $request = new grequest();
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;// 职位id
        if(empty($job_id)){
            $this->error("职位id为空");
        }
        $jobinfo = $jobintroinfo = $where = $jobinfocoll = array();
        $where['job_id'] = $job_id;
        $jobsmodel = $this->load("jobs");
        $jobinfo = $jobsmodel->getjobsinfo($where); //职位信息
        $jobintroinfo = $jobsmodel->getjobsintroinfo($where);//职位详情信息
        $str = preg_replace("/<strong>职位描述:<\/strong>/",' ',$jobintroinfo['intro']);
        $str=preg_replace("/<(\/?div.*?)>/si","",$str);
        $jobintroinfo['intro'] = $str;
        $getbasicdata = $this->_cachebasicdata;
        $company_nature = unserialize($getbasicdata['company_nature']);  //公司性质
        $company_size = unserialize($getbasicdata['company_size']);    //公司规模，人数
        $classification = unserialize($getbasicdata['industry_classification']); //行业领域
        $work_experience = unserialize($getbasicdata['work_experience']); //工作经验
        $education = unserialize($getbasicdata['education']); //学历
        $job_nature = unserialize($getbasicdata['job_nature']); //工作性质
        
        //公司信息
        $company_id = $jobinfo['company_id'];
        $company = $this->load('company');
        $companyinfo = $company->getcompanyinfobyid($company_id);
        //公司简介
        $company_intro = $company->getcompanyintrobyid($company_id);

        $logoimage = $company->getlogobyids(array($companyinfo['c_logo_id']),1);
        
        $companyinfo['property'] = $company_nature[$companyinfo['c_property']];
        $companyinfo['size'] = $company_size[$companyinfo['c_size']];
        $companyinfo['industry'] = $classification[$companyinfo['c_industry']];
        $companyinfo['logo'] = $logoimage[0]['imagepath'];
        $homepage = $companyinfo["c_homepage"];
        $preg = '|^http://|';  //正则，匹配以http://开头的字符串
        if (!preg_match($preg, $homepage)) {  //如果不能匹配
            $homepage = 'http://' . $homepage;
        }
        if($homepage == "http://"){
            $homepage = "";
        }
        $companyinfo["c_homepage"] = $homepage;
        //根据职位id获取职位信息-判断是否投递简历
        $resume = $this->load('resume');
        $detailinfo = '';
        $collections = '';
        if($uid){
            //职位收藏信息
            $collections = $jobsmodel->getcollections(array('uid'=>$uid,'job_id'=>$job_id));
            $detailinfo = $jobsmodel->userdetailforjob(array('u_id'=>$uid,'job_id'=>$job_id));
            $resumelist = $resume->getresumelist(12,0,array('uid'=>$uid,'status'=>1));
        }
        //从redis获取职位浏览数和投递数
        $rediscache = $this->init_rediscaches();
        $rediscache->select('1');
        $jobexpire = $rediscache->hget('job_browse_num', 'time_'.$job_id); //获取职位浏览过期时间
        $bnum = $rediscache->hget('job_browse_num', 'bnum_'.$job_id); //单个浏览数
        $dnum = $rediscache->hget('job_browse_num', 'dnum_'.$job_id); //单个投递数
        $rediscache->hset('job_browse_num', 'time_'.$job_id,time());
        if(empty($jobexpire)){
            $num = $rediscache->hsetnx('job_browse_num', 'bnum_'.$job_id, 1);
            if (false === $num) {
                $rediscache->hincrby('job_browse_num', 'bnum_'.$job_id, 1);
            }
            $bnum = $rediscache->hget('job_browse_num', 'bnum_'.$job_id); //单个
        }else{
            $t = time();
            if(($t - $jobexpire) > 60){
                $rediscache->hincrby('job_browse_num', 'bnum_'.$job_id, 1);
                $bnum = $rediscache->hget('job_browse_num', 'bnum_'.$job_id); //单个
            }
        }
        $jobinfocount = array('browse_num'=>$bnum,'delivery_num'=>$dnum);
        $cityname = str_replace("市", "", $this->_citydata[$jobinfo['city']]['name']);
        $webseo_detail = array(
//            'title' => $jobinfo['typejob_name'] . '招聘-' . $companyinfo['c_short_name'] . '-快就业',
//            'keywords' => $jobinfo['typejob_name'] . ',' . $jobinfo['typejob_name'] . '招聘,' . $companyinfo['c_short_name'] . '招聘' . $jobinfo['typejob_name'],
//            'description' => $jobinfo['typejob_name'] . ' ' . $this->_citydata[$jobinfo['city']]['name'] . ' ' . $education[$jobinfo['education']] . ' ' . $education[$jobinfo['experience']] . ' ' . $job_nature[$jobinfo['job_nature']] . ' 找好工作,来快就业网'
            'title' => $cityname . $jobinfo['typejob_name'] . '招聘-' . $companyinfo['c_short_name'] . '招聘' . $jobinfo['typejob_name'] . "（" . $cityname . "地区）" . '-快就业',
            'keywords' => $cityname . $jobinfo['typejob_name'] . '招聘,' . $cityname . $jobinfo['typejob_name'] . '招聘信息',
            'description' => $companyinfo['c_short_name'] . $cityname . '地区招聘' . $jobinfo['typejob_name'] . '，为' . $cityname . '地区求职者提供' . $jobinfo['typejob_name'] . '岗位。【登陆快就业平台，找' . $cityname . '地区最适合您的工作】'
        );
        $this->render('jobsdetail', 
                array('_typejobdata'=>$this->_typejobdata,
                    '_typejob'=>$this->_typejob,
                    '_cachebasicdata'=>$this->_cachebasicdata,
                    '_citydata'=>$this->_citydata,
                    '_citytreedata'=>$this->_citytreedata,
                    '_work_experience' => $work_experience,
                    '_education' => $education,
                    '_job_nature' => $job_nature,
                    'jobinfo'=>$jobinfo,
                    'jobinfocoll'=>$collections,
                    'jobintroinfo'=>$jobintroinfo,
                    'job_id'=>$job_id,
                    '_uid'=>$uid,
                    'company_intro' => $company_intro,
                    'companyinfo' => $companyinfo,
                    'detailinfo' => $detailinfo,
                    'jobinfocount' => $jobinfocount,
                    'resumelist' => $resumelist,
                    'seoinfo' => $webseo_detail
                )
        );
    }
    
    /*
     * 提交简历弹窗
     */
    function actionviewtag(){
        $this->userlogin();
        //获取用户简历
        $resume = $this->load('resume');
        $request = new grequest();
        $job_id = $request->getParam('id') ? (int)$request->getParam('id'):0;// 职位id

        //根据职位id和用户id  判断该职位是否已经投递过简历
        $jobs = $this->load('jobs');
        $detailinfo = $jobs->userdetailforjob(array('u_id'=>$this->_uid,'job_id'=>$job_id));
        if($detailinfo){
            IS_AJAX && ajaxReturns(0,'已向该职位投递过简历，请勿重复投递','已向该职位投递过简历，请勿重复投递');exit;
        }
        //获取该用户所填写的可以投递的简历信息
        $resumelist = $resume->getresumelist(12,0,array('uid'=>$this->_uid,'status'=>1));
        $mrresume = '';
        foreach($resumelist as $key => $val){
            if($val['status']){
               $mrresume = $val;
            }
        }
        if(!$mrresume && $resumelist){
            $mrresume = $resumelist[0];
        }
        $response =  $this->renderPartial('viewtag',array('resumelist'=>$resumelist,'mrresume'=>$mrresume,'job_id'=>$job_id));
        IS_AJAX && ajaxReturns(1,0,$response); 
    }
    
    /*
     * 职位详情页面--投递简历
     */
    function actiondeliveryresume(){
        $this->userlogin();
        $request = new grequest();
        $job_id = $request->getParam('jid') ? (int)$request->getParam('jid'):0;// 职位id
        $rid = $request->getParam('rid') ? (int)$request->getParam('rid'):0;// 简历id
        $pd = $request->getParam('pd') ? (int)$request->getParam('pd'):'';//不修改继续投递
        $uid = $this->_uid;
        
        //根据职位id获取职位信息-判断职位是否存在
        $jobs = $this->load('jobs');
        $detailinfo = $jobs->userdetailforjob(array('u_id'=>$this->_uid,'job_id'=>$job_id));
        if($detailinfo){
            IS_AJAX && ajaxReturns(0,'已向该职位投递过简历，请勿重复投递','已向该职位投递过简历，请勿重复投递');
        }
        
        $resume = $this->load('resume');
        //根据简历id获取简历信息
        $resumeinfo = $resume->getresumebyarr(array('uid'=>$this->_uid,'rid'=>$rid),'*');
        if(!$resumeinfo){
            IS_AJAX && ajaxReturns(0,'简历信息有误，请核对后再行投递','简历信息有误，请核对后再行投递');
        }
        $usermodel = $this->load('foreuser');
        $userinfo = $usermodel->getuserinfobyuid($resumeinfo["u_id"]);
        if(empty($userinfo)){
            IS_AJAX && ajaxReturns(0,'用户信息有误，请刷新重试','用户信息有误，请刷新重试');
        }
        //根据职位id获取职位信息
        $jobsinfo = $jobs->getjobsinfo(array('job_id'=>$job_id));
        if(!$jobsinfo){
            IS_AJAX && ajaxReturns(0,'职位信息有误，请刷新重试','职位信息有误，请刷新重试');
        }
        $companymodel = $this->load("company");
        $comanyinfo = $companymodel->getcompanyinfobyid($jobsinfo["company_id"]);
        if(empty($comanyinfo)){
            IS_AJAX && ajaxReturns(0,'职位所属公司信息有误，请刷新重试','职位所属公司信息有误，请刷新重试');
        }
        if($uid == $jobsinfo['uid']){
            IS_AJAX && ajaxReturns(0,' 不能向自己发布的职位投递简历','不能向自己发布的职位投递简历');
        }
        $msg = '';
        $bppnum = 6;
        //判断期望行业
        if(($resumeinfo['u_industry']) != $comanyinfo['c_industry']){
            $msg .= '期望行业、';
            $bppnum -= 1;
        }
        //判断工作性质
        if($resumeinfo['u_job_type'] != $jobsinfo['job_nature']){
            $msg .= '工作类型、';
            $bppnum -= 1;
        }
        //判断月薪范围
        $jobsalaryArr = explode(',',$jobsinfo['salary_id']);
        if(!in_array($resumeinfo['u_salary'], $jobsalaryArr)){
            $msg .= '期望薪水、';
            $bppnum -= 1;
        }
        //判断工作城市 
        if($resumeinfo['u_city'] != $jobsinfo['city']){
            $msg .= '工作城市、';
            $bppnum -= 1;
        }
        //判断工作经验
        if($userinfo['workexp'] != $jobsinfo['experience']){
            $msg .= '工作经验、';
            $bppnum -= 1;
        }
        //判断学历要求
        if($userinfo['education'] != $jobsinfo['education']){
            $msg .= '学历、';
            $bppnum -= 1;
        }
        if($pd != 1){
            $match = round(($bppnum/6)*100);
            $remsg = trim($msg, '、');
            if(!empty($msg)){
                $response =  $this->renderPartial('jlppd',array('remsg'=>$remsg,'match'=>$match,'job_id'=>$job_id,'rid' => $rid));
                IS_AJAX && ajaxReturns(0,0,$response);exit;
            }
        }

        $data = array(
            'old_rid' => $rid,
            'u_id' => $uid,
            'hr_uid' => $jobsinfo['uid'],
            'company_id' => $jobsinfo['company_id'],
            'job_id' => $job_id,
            'delivery_status' => 1,
            'delivery_time' => time(),
            'match_rate'=>$bppnum
        );
        $srdata = array(
            'old_rid' => $rid,
            'u_id' => $uid,
            'hr_uid'=> $jobsinfo['uid'],
            'company_id' => $jobsinfo['company_id'],
            'status'=>0,
            'delivery_time' => time()
        );
        //投递简历
        $delivery = $this->load('delivery');
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        $delivery_id = $delivery->insert($data);
        $srdata["did"] = $delivery_id;
        $srid = $delivery->insertresumesendemail($srdata);//简历投递后记录至邮件发送记录表
        if($delivery_id && $srid){
            //添加日志
            $logdata = array(
                'jd_id' => $delivery_id,
                'note' => '已成功接收投递的简历',
                'info' => '已成功接收投递的简历',
                'status' => 0,
                'add_time' => time(),
            );
            $getppd = $delivery->insertlogs($logdata);
            $rediscache = $this->init_rediscaches();
            $rediscache->select('1');
            $num = $rediscache->hsetnx('job_browse_num', 'dnum_'.$job_id, 1);
            if (false === $num) {
                $rediscache->hincrby('job_browse_num', 'dnum_'.$job_id, 1);
            }
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'简历已成功投出去了，请静候佳音！',$delivery_id);
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'简历投递失败','简历投递失败');
        }
    }
    
    /*
     * 收藏职位
     */
    function actioncollectjobs(){
        $this->userlogin();
        $request = new grequest();
        $c_id = $request->getParam('c_id') ? (int)$request->getParam('c_id'):0;// 职位id
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;// 职位id
        $flag = $request->getParam('flag') ? (int)$request->getParam('flag'):0;// 职位收藏状态类型
        if(empty($job_id)){
           IS_AJAX && ajaxReturns(0,'职位id为空',0); 
        }
        $jobsmodel = $this->load('jobs');
        $collections = $jobsmodel->getcollections(array('uid'=>$this->_uid,'job_id'=>$job_id));
        if($flag == 2){ //已收藏
            if(empty($c_id)){
                IS_AJAX && ajaxReturns(0,'收藏信息为空',0);  
            }
            $ret = $jobsmodel->cancel_collections($c_id);
            if($ret){
                IS_AJAX && ajaxReturns(1,'已取消收藏职位',$ret);
            }else{
                IS_AJAX && ajaxReturns(0,'操作失败',$ret);
            }
        }else{
            if($collections){
                $ret = $jobsmodel->updatecollect($collections['c_id'],0);
            }else{
                $edit = array();
                $edit['u_id'] = $this->_uid;
                $edit['job_id'] = $job_id;
                $edit['c_time'] = time();
                $ret = $jobsmodel->collect_jobs($edit,$job_id);
            }
            if(false === $ret){
                IS_AJAX && ajaxReturns(0,'职位收藏失败',0);
            }else{
                IS_AJAX && ajaxReturns(1,'职位收藏成功',$ret);
            }
        }
    }
    
    
    /**
     * 下线职位
     */
    function actionjoboffline(){
        $this->userlogin();
        $this->getcertstatus();
        $request = new grequest();
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;// 职位id
        if(empty($job_id)){
           IS_AJAX && ajaxReturns(0,'职位id为空',0); 
        }
        $jobsmodel = $this->load('jobs');
        $where['job_id'] = $job_id;
        $jobinfo = $jobsmodel->getjobsinfo($where); //职位信息
        if($jobinfo['uid'] != $this->_uid){
            IS_AJAX && ajaxReturns(0,'不是您发布的职位不能下线',0);
        }
        $edit = array();
        $edit['status'] = 1;
        $edit['expired_time'] = time();
        $ret = $jobsmodel->edit_jobs($edit,$job_id,1);
        if(false === $ret){
            IS_AJAX && ajaxReturns(0,'职位下线失败',0);
        }else{
            IS_AJAX && ajaxReturns(1,'职位下线成功',$ret);
        }
    }
    
    
    /**
     * 重新发布职位
     */
    function actionjobonline(){
        $this->userlogin();
        $this->getcertstatus();
        $request = new grequest();
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;// 职位id
        if(empty($job_id)){
           IS_AJAX && ajaxReturns(0,'职位id为空',0); 
        }
        $jobsmodel = $this->load('jobs');
        $where['job_id'] = $job_id;
        $jobinfo = $jobsmodel->getjobsinfo($where); //职位信息
        if($jobinfo['uid'] != $this->_uid){
            IS_AJAX && ajaxReturns(0,'不是您发布的职位不能恢复发布',0);
        }
        $edit = array();
        $edit['status'] = 0;
        $edit['expired_time'] = 0;
        $ret = $jobsmodel->edit_jobs($edit,$job_id,2);
        if(false === $ret){
            IS_AJAX && ajaxReturns(0,'职位重新发布失败',0);
        }else{
            IS_AJAX && ajaxReturns(1,'职位重新发布成功',$ret);
        }
    }
    
    /**
     * 刷新职位
     */
    function actionrefreshjob(){
        $this->userlogin();
        $this->getcertstatus();
        $request = new grequest();
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;// 职位id
        if(empty($job_id)){
           IS_AJAX && ajaxReturns(0,'职位id为空',0); 
        }
        $jobsmodel = $this->load('jobs');
        $where['job_id'] = $job_id;
        $jobinfo = $jobsmodel->getjobsinfo($where); //职位信息
        if($jobinfo['uid'] != $this->_uid){
            IS_AJAX && ajaxReturns(0,'不是您发布的职位不能刷新职位',0);
        }
        $ret = $jobsmodel->refresh_jobs($job_id);
        if(false === $ret){
            IS_AJAX && ajaxReturns(0,'每天只能刷新一次',0);
        }else{
            IS_AJAX && ajaxReturns(1,'职位刷新成功,请稍后查询,谢谢',$ret);
        }
    }
    
    /**
     * 一键刷新全部在职职位
     */
    function actionrefreshonlinejobs(){
        $this->userlogin();
        $this->getcertstatus();
        $times = $this->sessionrefreshalljob('alljob_'.$this->_uid);
        if(!empty($times)){
            $t = time() - $times; 
            if($t > 0 && ($t / 86400) < 1){
                IS_AJAX && ajaxReturns(0,'每天只能一键刷新一次',0);die;
            }
        }
        $where = $edit = array();
        $where['uid'] = $this->_uid;
        $where['status'] = 0;
        $where['limit'] = 1;
        $jobsmodel = $this->load('jobs');
        //从solr中获取职位个数
        $list = $jobsmodel->getjobslistforsolr($where);
        if(empty($list['nums'])){
            IS_AJAX && ajaxReturns(0,'没有任何在职职位',0);
        }
        $edit['refresh_time'] = time();
        $ret = $jobsmodel->edit_onlinejobs($edit,$where);
        if(false === $ret){
            IS_AJAX && ajaxReturns(0,'一键刷新失败',0);
        }else{
            $this->setsessionrefreshalljob('alljob_'.$this->_uid, time());
            IS_AJAX && ajaxReturns(1,'一键刷新成功,请稍后查询,谢谢',0);
        }
    }
   
    
    /**
     * 删除职位
     */
    function actiondeljobs(){
        $this->userlogin();
        $this->getcertstatus();
        $request = new grequest();
        $job_id = $request->getParam('job_id') ? (int)$request->getParam('job_id'):0;//职位id
        if(empty($job_id)){
           IS_AJAX && ajaxReturns(0,'职位id为空',0); 
        }
        $jobsmodel = $this->load('jobs');
        $where['job_id'] = $job_id;
        $jobinfo = $jobsmodel->getjobsinfo($where); //职位信息
        if($jobinfo['uid'] != $this->_uid){
            IS_AJAX && ajaxReturns(0,'不是您发布的职位不能删除',0);
        }
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        $ret = $jobsmodel->delete_jobs($job_id);
        $ret_info = $jobsmodel->delete_jobsintro($job_id);
        if(false !== $ret && false !== $ret_info){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'职位删除成功',0);
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'职位删除失败',0);
        }
    }
    
    /**
     * 职位订阅
     */
    function actionsubscribe(){
        $this->userlogin();
        $this->basevar['title'] = "职位订阅";
        $userinfo = $subinfo = array();
        $usermodel = $this->load("foreuser");
        $userinfo = $usermodel->getuserinfobyuid($this->_uid);
        $jobmodel = $this->load("jobs");
        $subinfo = $jobmodel->getjobsubscribebyuid($this->_uid);
        
        $this->render('subscribe',array(
            'email'=>$userinfo['email'],
            'subinfo'=>$subinfo,
            '_typejobcache'=>$this->_typejob,
            '_typejobdata'=>$this->_typejobdata,
            '_citycache'=>$this->_citydata,
            '_city'=>$this->_citytreedata,
            '_development'=>unserialize($this->_cachebasicdata["development_stage"]),
            '_industry'=>unserialize($this->_cachebasicdata["industry_classification"]),
            '_salary'=>  unserialize($this->_cachebasicdata["expected_salary"]),
            'seoinfo'=>$this->webseo
        ));
    }
    
    /**
     * 保存职位订阅
     */
    function actionsavesubscribe(){
        $this->userlogin();
        $request = new grequest();
        $email = $request->getParam("email") ? htmlspecialchars($request->getParam("email")) : "";//接收邮箱
        $cycle = $request->getParam("cycle") ? (int)$request->getParam("cycle") : 0;//邮件发送周期
        $jobid = $request->getParam("jobid") ? (int)$request->getParam("jobid") : 0;//职位id
        $cityid = $request->getParam("cityid") ? (int)$request->getParam("cityid") : 0;//工作地点id
        $depid = $request->getParam("depid") ? (int)$request->getParam("depid") : 0;//发展阶段id
        $industry = $request->getParam("industry") ? (int)$request->getParam("industry") : 0;//行业领域id
        $salary = $request->getParam("salary") ? (int)$request->getParam("salary") : 0;//月薪范围id
        $subid = $request->getParam("subid") ? (int)$request->getParam("subid") : 0;//订阅编号
        if(empty($email)){
            IS_AJAX && ajaxReturns(0,'接收邮箱不能为空',0); 
        }
        if(!isset($cycle)){
            IS_AJAX && ajaxReturns(0,'请选择邮件发送周期',0); 
        }
        if(!isset($jobid)){
            IS_AJAX && ajaxReturns(0,'请选择一个职位名称',0); 
        }
        if(!isset($cityid)){
            IS_AJAX && ajaxReturns(0,'请选择一个工作地点',0); 
        }
        if(!isset($depid)){
            IS_AJAX && ajaxReturns(0,'请选择一个发展阶段',0); 
        }
        if(!isset($industry)){
            IS_AJAX && ajaxReturns(0,'请选择一个行业领域',0); 
        }
        if(!isset($salary)){
            IS_AJAX && ajaxReturns(0,'请选择一个月薪范围',0); 
        }
        //职位订阅基础数据
        $data = array(
            "email"=>$email,
            "cycle"=>$cycle,
            "jobid"=>$jobid,
            "cityid"=>$cityid,
            "developmentid"=>$depid,
            "industryid"=>$industry,
            "salaryid"=>$salary,
            "u_id"=>$this->_uid//添加人
        );
        $jobmodel = $this->load("jobs");
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        if(0 !== $subid){
            $subret = $jobmodel->savejobsubscribe($subid,$data);
        }else{
            $subret = $jobmodel->addjobsubscribe($data);
        }
        if(false !== $subret){
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'职位订阅保存成功',0);
        }else{
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'职位订阅保存失败,数据正在回滚中。。。',0);
        }
    }
    
    /**
     * 删除职位订阅
     */
    function actiondelsubscribe(){
        $this->userlogin();
        $request = new grequest();
        $subid = $request->getParam("subid") ? (int) $request->getParam("subid") : 0;//订阅编号
        if(!isset($subid)){
            IS_AJAX && ajaxReturns(0,'请选择一个职位订阅',0);
        }
        $jobmodel = $this->load("jobs");
        
        //创建事务处理
        init_db()->createCommand()->query("START TRANSACTION");
        $ret = $jobmodel->deljobsubscribe($subid);
        if(false === $ret){
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0,'职位订阅删除失败,数据正在回滚中。。。',0);
        }else{
            init_db()->createCommand()->query("COMMIT"); //成功提交
            IS_AJAX && ajaxReturns(1,'职位订阅删除成功',0);
        }
    }
    /**
     * 根据薪资范围获取系统相对应的薪资编号
     * @param type $start
     * @param type $end
     */
    function getsyssalaryid($start=0,$end=0){
        $salary_id = "";
        $_salaryArr = unserialize($this->_cachebasicdata["expected_salary"]);
        unset($_salaryArr[0]); //去掉“不限”
        foreach ($_salaryArr as $sk => $sv) {
            $sv = str_replace(array('k', '以', '上', '下'), "", $sv);
            if (strstr($sv, '-')) {
                $sArr = explode('-', $sv);
                if ($start >= $sArr[0] && $start <= $sArr[1]) {
                    $salary_id = $this->addtostring($salary_id, $sk);
                }
                if ($end >= $sArr[0] && $end <= $sArr[1]) {
                    $salary_id = $this->addtostring($salary_id, $sk);
                }
                if ($start < $sArr[0] && $end >= $sArr[0] && $end >= $sArr[1]) {
                    $salary_id = $this->addtostring($salary_id, $sk);
                }
            } else {
                if ($start >= $sv && $end >= $sv && $sv != 2) {
                    $salary_id = $this->addtostring($salary_id, $sk);
                }
            }
        }
        return substr($salary_id, 0, strlen($salary_id) - 1);
    }

    function addtostring($str, $key) {
        if (empty($str)) {
            $str .= $key . ",";
        } else {
            $strArr = explode(',', $str);
            if (!in_array($key, $strArr)) {
                $str .= $key . ",";
            }
        }
        return $str;
    }
    
    /**
     * 数据统计
     */
    function actionstatistics() {
        $this->userlogin();
        $this->getcertstatus();
        //HR用户发布职位列表
        $jobsmodel = $this->load("jobs");
        $where['uid'] = $this->_uid;
        $where['status'] = 0;
        $where['limit'] = 100;
        $where['page'] = 1;
        $where['field'] = 'job_id,typejob_name';
        $joblist = array();
        //从solr中读取职位信息
        $jobsolrs = $jobsmodel->getjobslistforsolr($where);
        if(!empty($jobsolrs['nums'])){
            $joblist = $jobsolrs['docs'];
        }
        $this->render('statistics', array(
            'joblist' => $joblist,
            'seoinfo' => $this->webseo
        ));
    }
    
    /**
     * 数据统计搜索
     */
    function actionsearchsta(){
        $this->userlogin();
        $this->getcertstatus();
        $request = new grequest();
        $jobid = $request->getParam("jobid") ? (int) $request->getParam("jobid") : 0;//职位id
        $stime = $request->getParam("stime") ? htmlspecialchars($request->getParam("stime")) : "";//统计开始日期
        $etime = $request->getParam("etime") ? htmlspecialchars($request->getParam("etime")) : "";//统计结束日期
        $where = array();
        $where["hruid"] = $this->_uid;
        if(0!==$jobid){
            $where["jobid"] = $jobid;
        }
        if(!empty($stime) && !empty($etime)){
            $etime .= " 23:59:59";
            $starttime = strtotime($stime);
            $endtime = strtotime($etime);
            if($endtime < $starttime){
                IS_AJAX && ajaxReturns(0,'统计结束时间不能小于统计开始时间2',0);
            }
            $where["starttime"] = $starttime;
            $where["endtime"] = $endtime;
        }
        $model = $this->load("jobs");
        $list = array();
        $total = $model->getjobdelistatotal($where);
        if($total == 0){
            IS_AJAX && ajaxReturns(2,"无符合条件数据",$list);
        }
        $list = $model->getjobdelistalist($where,$total);
        if(empty($list)){
            IS_AJAX && ajaxReturns(2,"无符合条件数据",$list);
        }
        $data = array();
        foreach ($list as $lk => $lv){
            $data["resumesums"]+=$lv["resumesums"];
            $data["browsesums"]+=$lv["browsesums"];
            $data["viewsums"]+=$lv["viewsums"];
            $data["malesums"]+=$lv["malesums"];
            $data["femalesums"]+=$lv["femalesums"];
            $data["dzsums"]+=$lv["dzsums"];
            $data["bksums"]+=$lv["bksums"];
            $data["yjssums"]+=$lv["yjssums"];
        }
        if (!empty($list)) {
            IS_AJAX && ajaxReturns(1,"获取统计信息成功",$data);
        }
    }
}
?>
