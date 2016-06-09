<?php

!defined('IN_UC') && exit('Access Denied');

class jobsearch extends base {

    protected $uc_offcn_user;
    public $_uid;
    public $_typejobdata;

    function __construct() {
        $this->_uid = $this->session('uc_offcn_uid');
        $this->_typejobdata = S('typejob_list'); //职位
        parent::__construct();
    }

    /*
     * 从solr中获取数据
     */

    function actionindex() {
        $request = new grequest();
        //筛选条件
        $search = $request->getParam('search') ? htmlspecialchars(trim($request->getParam('search'))) : '';   //搜索字段
        if(!empty($search)){
            $search = $this->remove_xss($search);
            if(!$search){
                IS_AJAX && ajaxReturns(0, '页面不存在');die;
            }
        }
        $zone = $request->getParam('zone') ? (int) $request->getParam('zone') : '';   //工作地点
        $wexp = $request->getParam('wexp') ? (int) $request->getParam('wexp') : '';   //工作经验
        $educ = $request->getParam('educ') ? (int) $request->getParam('educ') : '';   //学历要求
        $exps = $request->getParam('exps') ? (int) $request->getParam('exps') : '';   //月薪范围
        $comn = $request->getParam('comn') ? (int) $request->getParam('comn') : ''; //公司性质
        $coms = $request->getParam('coms') ? (int) $request->getParam('coms') : ''; //公司规模
        $jobn = $request->getParam('jobn') ? (int) $request->getParam('jobn') : ''; //工作性质
        $scop = $request->getParam('scop') ? (int) $request->getParam('scop') : ''; //行业领域
        $page = $request->getParam('page') ? (int) $request->getParam('page') : 1;
        $typejobid = $request->getParam('typeid') ? (int) $request->getParam('typeid') : 0;
        $sort = $request->getParam('sort') ? trim($request->getParam('sort')) : 'refreshd'; //排序
        $search = urldecode($search); //linux下面nginx自动转换
        $getbasicdata = S('getbasicdata');
        $expected_salary = unserialize($getbasicdata['expected_salary']);   //月薪范围

        $where = array();
        //判断是否提交月薪范围条件
        if ($exps) {
            $where['salaryid'] = $exps;
        }
        //处理搜索字段
        //判断排序
        $create = 'created';
        $refresh = '';
        switch ($sort) {
            case 'created':
                $orderby = 'create_time desc';
                break;
            case 'refreshd':
                $orderby = 'refresh_time desc';
                break;
        }

        $where['search'] = $search;
        $where['zone'] = $zone;
        $where['wexp'] = $wexp;
        $where['educ'] = $educ;
        $where['comn'] = $comn;
        $where['coms'] = $coms;
        $where['jobn'] = $jobn;
        $where['scop'] = $scop;
        if(!empty($typejobid)){
            $where['typejobid'] = $typejobid;
        }
        $where['sort'] = $orderby;
        $where['status'] = 0;
        $where['company_ids'] = '';
        $searchdata = $where;
        $searchdata['exps'] = $exps;
        $searchdata['sort'] = $sort == 'refreshd' ? '' : $sort;
        //加载model
        $company = $this->load('company');
        $jobs = $this->load('jobs');

        $limit = 20;
        $where['limitc'] = $where['limit'] = $limit;
        $where['pagec'] = $where['page'] = $page;
        $where['user_id'] = $this->_uid;
        $jobsnum = 0;
        $jobslist = $companylist = array();
        //*************************************职位列表部分***************************************************
        //从solr中获取筛选条件下所有职位数量
        $jobsolrs = $jobs->getjobslistforsolr($where);
        //获取筛选条件下所有职位数量
        $jobsnum = $jobsolrs['nums'];
        if ($jobsnum == 0) { //也就是typejob_name 搜索为空
            $where['searchc'] = $search;
            unset($where['search']);
            $jobsolrs = $jobs->getjobslistforsolr($where);
            //获取筛选条件下所有职位数量
            $jobsnum = $jobsolrs['nums'];
        }
        //获取职位信息--按分页获取
        $jobslist = $jobsolrs['docs'];
        //根据职位信息获取对应的公司id串
        $jobcompanyids = array();
        foreach ($jobslist as $jk => $jv) {
            $jobcompanyids[] = $jv['company_id'];
        }
        $jcids = array_unique($jobcompanyids);
        //根据职位中的公司ids获取公司信息--用于职位列表中的公司属性
        $forcompany = $company->getsolrlistbyids($jcids);
        if (!empty($forcompany)) {
            foreach ($forcompany as $ck => $cv) {
                $tags = explode(',', $cv['c_tag']);
                $cv['c_tag'] = $tags;
                $cv['c_short_name'] = mb_strimwidth($cv['c_short_name'], 0, 30, "...", 'utf-8');
                $companylist[$cv['c_id']] = $cv;
            }
        }

        //职位分页
        $pagenum = ($jobsnum >= 600) ? 600 : $jobsnum;
        $pager = $this->pageAjax('jobsearch', $pagenum, $page, $limit, 5, $searchdata);
        //*********************************************************************************************
        //*************************************公司列表部分***************************************************
        //获取公司列表部分
        //判断是否有关键字搜索--有关键字则按关键字筛选公司
        $company_list = array();
        $company_total = 0;
        if ($search) {
            $comsolrs = $company->getlistsearchforsolr(array('searchc' => $search, 'limitc' => $limit, 'pagec' => $page));
            $company_list = $comsolrs['docs'];
            $company_total = $comsolrs['nums'];
        }
        $cids = array();
        //公司列表中的职位数
        if ($company_list) {
            foreach ($company_list as $key => $val) {
                $cids[] = $val['c_id'];
            }
        }
        $joblistall = $jobs->getjobsbycids($cids);
        $jobnum_arr = array();
        foreach ($joblistall as $kj => $vj) {
            $jobnum_arr[$vj['company_id']] += 1;
        }
        //公司分页
        $cpagenum = ($company_total >= 600) ? 600 : $company_total;
        $cpager = $this->pageAjax('comsearch', $cpagenum, $page, $limit, 5, $searchdata);
        //*********************************************************************************************            
        //搜索属性
        $company_nature = unserialize($getbasicdata['company_nature']);  //公司性质
        $company_size = unserialize($getbasicdata['company_size']);    //公司规模，人数
        $job_nature = unserialize($getbasicdata['job_nature']);  //工作性质
        $classification = unserialize($getbasicdata['industry_classification']); //行业领域
        $education = unserialize($getbasicdata['education']);   //学历要求
        $work_experience = unserialize($getbasicdata['work_experience']); //工作经验
        $company_tags = unserialize($getbasicdata['company_tags']);   //公司标签
        $development_stage = unserialize($getbasicdata['development_stage']);   //发展阶段
        //获取筛选用地区
        $area = S('areacache');
        $arealist = $province = $city = array();
        $searchzone = '';
        foreach ($area as $ka => $va) {
            if ($va['disabled'] == 1) {
                $arealist[$ka] = $va['name'];
            }
            $city[$va['areaid']] = $va['name'];
        }
        $areacodelist = S('arealistcache');
        $province = S('provincelistcache');
        $cityidArr = array(
            '1767', //北京市
            '1770', //上海市
            '1269', //广州市
            '1271', //深圳市
            '233', //杭州市
            '657', //南京市
            '836', //武汉市
            '916', //成都市
            '345', //沈阳市
            '1057'//西安市
        ); //搜索页面显示--工作地点
        if ($zone != 0) {
            if (false == in_array($zone, $cityidArr)) {
                $searchzone = $area[$zone]["name"];
            }
        }

        $codearray = array(
            1 => '求职最热门地区',
            2 => '华东、华中地区',
            3 => '东北、华北地区',
            4 => '西南、东南地区',
            5 => '西部、西北地区',
        );
        $searchdata['create'] = $create;
        $searchdata['refresh'] = $refresh;
        $firstclass = array_slice($classification, 0, 7, true);
        $lastclass = array_slice($classification, 7, count($classification), true);
        $seo_search = urldecode($searchdata['search']);
        if(!empty($zone) && !empty($arealist[$zone])){
            $seo_search = str_replace("市", "", $arealist[$zone]).$seo_search;
        }
        $webseo = array();
        if (!empty($search)) {
            if($search != "应届生"){
                //java招聘_java招聘职位_最新java招聘信息_IT类技术职位招聘-快就业
                //此处为了拼接"IT类" "技术"这两项
                $homeSarchCache = S('homesearchcache');
                foreach ($homeSarchCache as $hk => $hv){
                    foreach ($hv['child'] as $hvck => $hvcv){
                        foreach ($hvcv['child'] as $hvcvck => $hvcvcv){
                            if($search == $hvcvcv['name']){
                                $search_parentitle = $homeSarchCache[$hk]['child'][$hvck]['name'];
                                $search_parentname = mb_substr($homeSarchCache[$hk]['name'], 0, 3, 'UTF-8');//IT类职位，只取前三个字
                            }
                        }
                    }
                }
                if (!empty($search_parentitle) || !empty($search_parentname)) {
                    if($search_parentitle == "财务" || $search_parentitle == "人力" || $search_parentitle == "行政" ||$search_parentitle == "管培生"){
                        $search_parentitle = "";
                    }
                    if($search_parentname == "管培生"){
                        $search_parentname = "管培生类";
                    }
                    $webseo['title'] = $seo_search . '招聘_' . $seo_search . '招聘职位_最新' . $seo_search . '招聘信息_' . $search_parentname . $search_parentitle . '职位招聘-快就业';
                    $webseo['keywords'] = $seo_search . '招聘_' . $seo_search . '招聘职位_最新' . $seo_search . '招聘信息_' . $search_parentname . $search_parentitle.'职位招聘';
                    $webseo['description'] = '快就业人才招聘网为您提供最新的' . $seo_search . '招聘信息，帮您了解更多更全的高薪' . $seo_search . '招聘职位，页面中包含各省' . $seo_search . '招聘信息，点击上面的标题进入，总有一个' . $seo_search . '职位适合您！';
                } else {
                    $webseo['title'] = $seo_search .'求职_'.$seo_search .'职位_'.$seo_search .'求职_'.$seo_search .'招聘-快就业';
                    $webseo['keywords'] = $seo_search .'求职,'.$seo_search .'职位,'. $seo_search .'求职,'. $seo_search .'招聘';
                    $webseo['description'] = '快就业人才招聘网为您提供'.$seo_search.'招聘职位信息汇总，有更多的'.$seo_search.'岗位信息供您选择，帮您更快,更准的找到'.$seo_search.'岗位信息相关的工作【上快就业找最适合您的'.$seo_search.'招聘职位】';
                }
            }else{
                $webseo['title'] = '应届生求职_应届生职位_应届毕业生求职_应届生招聘-快就业';
                $webseo['keywords'] = '应届生求职,应届生职位,应届毕业生求职,应届生招聘';
                $webseo['description'] = '快就业人才招聘网为您提供应届生招聘职位信息汇总，有更多的应届生岗位信息供您选择，帮您更快,更准的找到应届生岗位信息相关的工作【上快就业找最适合您的应届生招聘职位】';
            }
        } else {
            if (!empty($comn)) {
                $qylx = '';
                switch ($comn) {
                    case 1://外资
                        $qylx = '外资企业';
                        break;
                    case 4:
                        $qylx = '国企';
                        break;
                    case 6:
                        $qylx = '上市公司';
                        break;
                }
                $webseo['title'] = $qylx . '招聘职位_' . $qylx . '招聘职位分类_' . $qylx . '岗位_' . $qylx . '职位信息汇总-快就业';
                $webseo['keywords'] = $qylx . '招聘职位,' . $qylx . '招聘职位分类,' . $qylx . '岗位,' . $qylx . '职位信息汇总';
                $webseo['description'] = '快就业人才招聘网为您提供' . $qylx . '招聘职位信息汇总，有更多的' . $qylx . '岗位信息供您选择，帮您更快,更准的找到' . $qylx . '岗位信息相关的工作【上快就业找最适合您的' . $qylx . '招聘职位】';
            }
        }
        if(!empty($typejobid) && empty($search)){
            $typejobs = S('typejob');
            $search = $typejobs[$typejobid]["typejobname"];
            $search_parentitle = $typejobs[$typejobs[$typejobid]["parentid"]]["typejobname"];
            $webseo['title'] = $search . '招聘_' . $search . '招聘职位_最新' . $search . '招聘信息_' . $search_parentitle . '职位招聘-快就业';
            $webseo['keywords'] = $search . '招聘_' . $search . '招聘职位_最新' . $search . '招聘信息_' . $search.'职位招聘';
            $webseo['description'] = '快就业人才招聘网为您提供最新的' . $search . '招聘信息，帮您了解更多更全的高薪' . $search . '招聘职位，页面中包含各省' . $search . '招聘信息，点击上面的标题进入，总有一个' . $search . '职位适合您！';
        }
        $this->render('index', array(
            'list' => $jobslist,
            'clist' => $company_list,
            'pager' => $pager,
            'cpager' => $cpager,
            'jobsnum' => $jobsnum ? $jobsnum : 0,
            'jobnumarr' => $jobnum_arr,
            'company_total' => $company_total,
            's' => $searchdata,
            'search' => $search,
            'companylist' => $companylist,
            'company_nature' => $company_nature,
            'company_size' => $company_size,
            'job_nature' => $job_nature,
            'classification' => $classification,
            'lastclass' => $lastclass,
            'expected_salary' => $expected_salary,
            'education' => $education,
            'work_experience' => $work_experience,
            'arealist' => $arealist,
            'areacodelist' => $areacodelist,
            'company_tags' => $company_tags,
            'development_stage' => $development_stage,
            'province' => $province,
            'searchzone' => $searchzone,
            'city' => $city,
            'code' => $codearray,
            '_industry_remarks' => unserialize($getbasicdata["industry_remarks"]),
            '_typejobdata' => $this->_typejobdata,
            'adverts_search_r' => $this->adverts_search_r,
            'seoinfo' => $webseo
        ));
    }

    /*
     * ajax获取职位分页数据
     */

    function actiongetjobs() {
        $request = new grequest();
        $page = $request->getParam('page') ? (int) $request->getParam('page') : 1;
        if ($page == 1) {
            IS_AJAX && ajaxReturns(2, '数据已存在');
        }
        //筛选条件
        $search = $request->getParam('search') ? trim($request->getParam('search')) : '';   //搜索字段
        $zone = $request->getParam('zone') ? (int) $request->getParam('zone') : '';   //工作地点
        $wexp = $request->getParam('wexp') ? (int) $request->getParam('wexp') : '';   //工作经验
        $educ = $request->getParam('educ') ? (int) $request->getParam('educ') : '';   //学历要求
        $exps = $request->getParam('exps') ? (int) $request->getParam('exps') : '';   //月薪范围
        $comn = $request->getParam('comn') ? (int) $request->getParam('comn') : ''; //公司性质
        $coms = $request->getParam('coms') ? (int) $request->getParam('coms') : ''; //公司规模
        $jobn = $request->getParam('jobn') ? (int) $request->getParam('jobn') : ''; //工作性质
        $scop = $request->getParam('scop') ? (int) $request->getParam('scop') : ''; //行业领域
        $typejobid = $request->getParam('typeid') ? (int) $request->getParam('typeid') : ''; //职位类别
        $sort = $request->getParam('sort') ? trim($request->getParam('sort')) : 'refreshd'; //排序
        $getbasicdata = S('getbasicdata');
        $expected_salary = unserialize($getbasicdata['expected_salary']);   //月薪范围
        $where = array();
        //判断是否提交月薪范围条件
        if ($exps) {
            $where['salaryid'] = $exps;
        }
        //处理搜索字段
        //判断排序
        $create = 'created';
        $refresh = '';
        switch ($sort) {
            case 'created':
                $orderby = 'create_time desc';
                break;
            case 'refreshd':
                $orderby = 'refresh_time desc';
                break;
        }
        $where['search'] = $search;
        $where['zone'] = $zone;
        $where['wexp'] = $wexp;
        $where['educ'] = $educ;
        $where['comn'] = $comn;
        $where['coms'] = $coms;
        $where['jobn'] = $jobn;
        $where['scop'] = $scop;
        $where['sort'] = $orderby;
        if(!empty($typejobid)){
            $where['search'] = '';
            $where['typejobid'] = $typejobid;
        }
        $searchdata = $where;
        $searchdata['exps'] = $exps;
        $searchdata['sort'] = $sort == 'refreshd' ? '' : $sort;
        //加载model
        $company = $this->load('company');
        $jobs = $this->load('jobs');
        $limit = 20;
        $where['limitc'] = $where['limit'] = $limit;
        $where['pagec'] = $where['page'] = $page;
        $where['user_id'] = $this->_uid;
        $jobsnum = 0;
        $jobslist = $companylist = array();
        //*************************************职位列表部分***************************************************
        //从solr中获取筛选条件下所有职位数量
        $jobsolrs = $jobs->getjobslistforsolr($where);
        //获取筛选条件下所有职位数量
        $jobsnum = $jobsolrs['nums'];
        if ($jobsnum == 0) { //也就是typejob_name 搜索为空
            $where['searchc'] = $search;
            unset($where['search']);
            $jobsolrs = $jobs->getjobslistforsolr($where);
            //获取筛选条件下所有职位数量
            $jobsnum = $jobsolrs['nums'];
        }
        //获取职位信息--按分页获取
        $jobslist = $jobsolrs['docs'];
        //根据职位信息获取对应的公司id串
        $jobcompanyids = array();
        foreach ($jobslist as $jk => $jv) {
            $jobcompanyids[] = $jv['company_id'];
        }
        $jcids = array_unique($jobcompanyids);
        //根据职位中的公司ids获取公司信息--用于职位列表中的公司属性
        $forcompany = $company->getsolrlistbyids($jcids);
        if (!empty($forcompany)) {
            foreach ($forcompany as $ck => $cv) {
                $tags = explode(',', $cv['c_tag']);
                $cv['c_tag'] = $tags;
                $cv['c_short_name'] = mb_strimwidth($cv['c_short_name'], 0, 30, "...", 'utf-8');
                $companylist[$cv['c_id']] = $cv;
            }
        }

        //职位分页
        $pagenum = ($jobsnum >= 600) ? 600 : $jobsnum;
        $pager = $this->pageAjax('jobsearch', $pagenum, $page, $limit, 5, $searchdata);
        $classification = unserialize($getbasicdata['industry_classification']); //行业领域
        $education = unserialize($getbasicdata['education']);   //学历要求
        $work_experience = unserialize($getbasicdata['work_experience']); //工作经验
        $company_tags = unserialize($getbasicdata['company_tags']);   //公司标签
        $development_stage = unserialize($getbasicdata['development_stage']);   //发展阶段
        //获取筛选用地区
        $area = S('areacache');
        $arealist = $province = $city = array();
        $searchzone = '';
        foreach ($area as $ka => $va) {
            if ($va['disabled'] == 1) {
                $arealist[$ka] = $va['name'];
            }
            if ($va['parentid'] == 0) {
                $province[] = $va;
            }
            if ($va['areaid'] == $zone) {
                if ($va['disabled'] != 1) {
                    $searchzone = $va['name'];
                }
            }
            $city[$va['areaid']] = $va['name'];
        }

        $searchdata['create'] = $create;
        $searchdata['refresh'] = $refresh;
        $firstclass = array_slice($classification, 0, 6, true);
        $lastclass = array_slice($classification, 6, count($classification), true);
        $response = $this->renderPartial('jobspage', array(
            'list' => $jobslist,
            'pager' => $pager,
            'page' => $page,
            'sort' => $sort,
            'jobsnum' => $jobsnum ? $jobsnum : 0,
            's' => $searchdata,
            'search' => $search,
            'companylist' => $companylist,
            'classification' => $classification,
            'firstclass' => $firstclass,
            'lastclass' => $lastclass,
            'expected_salary' => $expected_salary,
            'education' => $education,
            'work_experience' => $work_experience,
            'arealist' => $arealist,
            'company_tags' => $company_tags,
            'development_stage' => $development_stage,
            'province' => $province,
            'searchzone' => $searchzone,
            'city' => $city,
        ));
        IS_AJAX && ajaxReturns(1, $pager, $response);
    }

    /*
     * ajax获取公司列表信息
     */

    function actiongetcompany() {
        $request = new grequest();
        $page = $request->getParam('cpage') ? (int) $request->getParam('cpage') : 1;
        if ($page == 1) {
            IS_AJAX && ajaxReturns(2, '数据已存在');
        }
        //筛选条件
        $search = $request->getParam('search') ? trim($request->getParam('search')) : '';   //搜索字段
        $getbasicdata = S('getbasicdata');
        $classification = unserialize($getbasicdata['industry_classification']); //行业领域

        $where = array();
        $where['search'] = $search;

        //加载model
        $company = $this->load('company');
        $jobs = $this->load('jobs');

        $limit = 20;
        $where['limit'] = $limit;
        $where['page'] = $page;

        $company_list = array();
        $company_total = 0;
        if ($search) {
            $comsolrs = $company->getlistsearchforsolr(array('searchc' => $search, 'limitc' => $limit, 'pagec' => $page));
            $company_list = $comsolrs['docs'];
            $company_total = $comsolrs['nums'];
        }

        $cids = array();
        $clogoids = array();
        //获取公司职位数
        if ($company_list) {
            foreach ($company_list as $key => $val) {
                $cids[] = $val['c_id'];
            }
        }

        $joblistall = $jobs->getjobsbycids($cids);
        $jobnum_arr = array();
        foreach ($joblistall as $kj => $vj) {
            $jobnum_arr[$vj['company_id']] += 1;
        }
        //职位分页
        $pagenum = ($company_total >= 600) ? 600 : $company_total;
        $pager = $this->pageAjax('comsearch', $pagenum, $page, $limit, 5, $searchdata);

        //获取筛选用地区
        $area = S('areacache');
        $city = array();
        foreach ($area as $ka => $va) {
            $city[$va['areaid']] = $va['name'];
        }

        $response = $this->renderPartial('companypage', array(
            'list' => $company_list,
            'pager' => $pager,
            'cpage' => $page,
            'jobnumarr' => $jobnum_arr,
            //'logolist' => $logolist,
            'classification' => $classification,
            'city' => $city
        ));
        IS_AJAX && ajaxReturns(1, $pager, $response);
    }

    function actiongetcity() {
        $request = new grequest();
        $pid = $request->getParam('pid');
        $area = S('areacache');
        $city = array();
        foreach ($area as $ka => $va) {
            if ($va['parentid'] > 0) {
                $city[$va['parentid']][] = $va;
            }
        }
        IS_AJAX && ajaxReturns(1, '', $city[$pid]);
    }
    
    function remove_xss($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }

}
?>