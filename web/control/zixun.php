<?php

/**
 * 简历资讯
 */
!defined('IN_UC') && exit('Access Denied');

class zixun extends base {

    public $_uid;
    public $_uname;
    public $webseo;
    function __construct() {
        header("Content-type:text/html;charset=utf-8;");
        parent::__construct();
        $this->webseo = array(
            'title' => '找好工作,来快就业网',
            'keywords' => '就业,求职,招聘,公司招聘,高薪职位,大学生就业',
            'description' => '找好工作,来快就业网'
        );
    }

    //简历资讯首页
    public function actionindex() {
        //今日头条 调用文章上传最新数据7条
        $zixunmodel = $this->load('zixun');
        $where['status'] = 1;
        $today_head_list = $zixunmodel->getzixunpagelist(7, 0, $where);

        //$fengmian_list = $zixunmodel->getzixunpagelist(5,0,array('catid'=>6,'status'=>1));//简历经典封面
        //$geshi_list = $zixunmodel->getzixunpagelist(5,0,array('catid'=>11,'status'=>1));//简历经典格式
        //$zhengwen_list = $zixunmodel->getzixunpagelist(10,0,array('catid'=>12,'status'=>1));//简历经典正文
        //简历撰写
        $zhuanxie_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 2, 'status' => 1)); //简历内容撰写
        $zhizuo_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 3, 'status' => 1)); //简历制作技巧
        $toudi_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 4, 'status' => 1)); //简历投递技巧
        //$jingdian_weike_list = $zixunmodel->getzixunpagelist(8,0,array('catid'=>13,'status'=>1));//简历经典微课
        //面试真题
        $new_zhenti_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 24, 'status' => 1)); //面试最新真题速递
        $jd_zhenti_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 25, 'status' => 1)); //面试经典真题回顾
        $zhenti_zd_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 26, 'status' => 1)); //面试真题及作答解析
        //面试技巧
        $hr_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 28, 'status' => 1)); //HR淡面试
        $ly_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 29, 'status' => 1)); //面试礼仪及应对技巧
        $xd_list = $zixunmodel->getzixunpagelist(10, 0, array('catid' => 30, 'status' => 1)); //面试经验及心得分享
        //广告位获取
        $adverts2 = $this->actiongetAdverts(1);
        $this->webseo = array(
            'title' => '求职简历信息_求职应聘面试真题_求职面试技巧汇总-快就业网',
            'keywords' => '求职简历信息_求职应聘面试真题_求职面试技巧汇总',
            'description' => '快就业人才招聘网整理最新人才求职招聘信息，包括求职简历如何撰写、求职面试真题及解析、求职面试技巧|经验等，找个人求职信息、求职面试技巧经验,请登陆快就业网http://www.kjiuye.com'
        );
        $this->render('index', array(
            'today_head_list' => $today_head_list, 'fengmian_list' => $fengmian_list, 'geshi_list' => $geshi_list,
            'zhengwen_list' => $zhengwen_list, 'zhuanxie_list' => $zhuanxie_list, 'zhizuo_list' => $zhizuo_list,
            'toudi_list' => $toudi_list, 'jingdian_weike_list' => $jingdian_weike_list, 'new_zhenti_list' => $new_zhenti_list,
            'jd_zhenti_list' => $jd_zhenti_list, 'zhenti_zd_list' => $zhenti_zd_list, 'hr_list' => $hr_list, 'ly_list' => $ly_list,
            'xd_list' => $xd_list, 'adverts_j' => $adverts2['adverts_j'], 'adverts_r' => $adverts2['adverts_r'],
            'seoinfo' => $this->webseo
        ));
    }

    //2015-12-30 new by dxl
    public function actionzuanxie() {
        $zixunmodel = $this->load('zixun');
        //面试技巧
        $zhuanxie_list = $zixunmodel->getzixunpagelist(6, 0, array('catid' => 2, 'status' => 1)); //简历内容撰写
        $zhizuo_list = $zixunmodel->getzixunpagelist(6, 0, array('catid' => 3, 'status' => 1)); //简历制作技巧
        $toudi_list = $zixunmodel->getzixunpagelist(6, 0, array('catid' => 4, 'status' => 1)); //简历投递技巧
        //简历模板
        $fm_list = $zixunmodel->getzixunpagelist(6, 0, array('catid' => 6, 'status' => 1)); //模板封面
        $zw_list = $zixunmodel->getzixunpagelist(6, 0, array('catid' => 12, 'status' => 1)); //模板正文
        //广告位获取
        $adverts2 = $this->actiongetAdverts(1);
        $adverts3 = $this->actiongetAdverts(3);
        $webseo = array(
            'title' => '求职简历模板下载_简历模板封面下载-快就业网',
            'keywords' => '求职简历模板下载_简历模板封面下载',
            'description' => '快就业人才招聘网提供求职简历模板，有求职应聘的小伙伴可以点击上面的标题来下载海量的求职简历模板与简历模板封面下载，拿起你的鼠标行动吧！'
        );
        $this->render('zuanxie', array(
            'adverts_j' => $adverts_j,
            'adverts_r' => $adverts_r,
            'zhuanxie_list' => $zhuanxie_list,
            'zhizuo_list' => $zhizuo_list,
            'toudi_list' => $toudi_list,
            'fm_list' => $fm_list,
            'zw_list' => $zw_list,
            'adverts_j' => $adverts2['adverts_j'],
            'adverts_r' => $adverts2['adverts_r'],
            'adverts_bm' => $adverts3['adverts_bm'],
            'seoinfo' => $webseo
        ));
        //$this->render('zuanxie');
    }

    //列表页 >>更多
    public function actionzxlists() {
        $request = new grequest();
        $catid = $request->getParam('catid') ? (int) $request->getParam('catid') : 0;

        $zixunmodel = $this->load('zixun');
        $page = $request->getParam('page', 1);
        $limit = 20;

        $where = array();
        $where['status'] = 1;
        if (!empty($catid))
            $where['catid'] = $catid;

        $total = $zixunmodel->getzixunpagecount($where);
        $lists = $zixunmodel->getzixunpagelist($limit, ($page - 1) * $limit, $where);
        $pages = $this->page($total, $page, $limit, 5, $where);

        //栏目缓存
        $categorycache = S('categorycache');

        //广告位获取
        $adverts_lr = $this->actiongetAdverts(1);

        $this->render('zxlists', array(
            'lists' => $lists, 
            'catid' => $catid, 
            'pages' => $pages, 
            'categorycache' => $categorycache, 
            'adverts_lr' => $adverts_lr['adverts_r'],
            'seoinfo' => $this->webseo
        ));
    }

    //2015-12-30 new by dxl
    public function actionmsztjq() {
        $zixunmodel = $this->load('zixun');
        //面试技巧
        $hr_list = $zixunmodel->getzixunpagelist(6, 0, array('catid' => 28, 'status' => 1)); //HR淡面试
        $ly_list = $zixunmodel->getzixunpagelist(6, 0, array('catid' => 29, 'status' => 1)); //面试礼仪及应对技巧
        $xd_list = $zixunmodel->getzixunpagelist(6, 0, array('catid' => 30, 'status' => 1)); //面试经验及心得分享
        //简历模板
        $fm_list = $zixunmodel->getzixunpagelist(7, 0, array('catid' => 6, 'status' => 1)); //模板封面
        $zw_list = $zixunmodel->getzixunpagelist(7, 0, array('catid' => 12, 'status' => 1)); //模板正文

        $rand_list = $zixunmodel->getzixunpagelist(1, 0, array('catid' => 26, 'status' => 1)); //随机面试题
        //广告位获取
        $adverts2 = $this->actiongetAdverts(1);
        $adverts3 = $this->actiongetAdverts(3);
        $webseo = array(
            'title' => '求职面试真题模拟考场_求职面试真题点评-快就业网',
            'keywords' => '求职面试真题模拟考场,求职面试真题点评',
            'description' => '快就业人才招聘网提供求职面试真题模拟考场，有求职应聘的小伙伴可以点击上面的标题来模拟，每道求职面试题都有点评哦，快来看看你的差距在哪吧！'
        );
        $this->render('msztjq', array(
            'adverts_j' => $adverts_j,
            'adverts_r' => $adverts_r,
            'hr_list' => $hr_list,
            'ly_list' => $ly_list,
            'xd_list' => $xd_list,
            'fm_list' => $fm_list,
            'zw_list' => $zw_list,
            'adverts_j' => $adverts2['adverts_j'],
            'adverts_r' => $adverts2['adverts_r'],
            'adverts_bm' => $adverts3['adverts_bm'],
            'rand_list' => $rand_list[0],
            'seoinfo' => $webseo
        ));
    }

    /*
     * 随机获取面试真题解析 
     */

    public function actiongetrandcontent() {
        $request = new grequest();
        $vid = $request->getParam('vid') ? (int) $request->getParam('vid') : 0;
        $zixunmodel = $this->load('zixun');
        $rand_list = $zixunmodel->getzixunpagelist(200, 0, array('catid' => 26, 'status' => 1), 'id'); //随机面试题
        if (empty($zixunmodel)) {
            unset($rand_list);
            IS_AJAX && ajaxReturns(0, "面试题库暂无数据", 0);
        }
        $rarray = array();
        foreach ($rand_list as $rk => $rv) {
            $rarray[] = (int) $rv['id'];
        }
        //随机一个
        $rands = array_rand($rarray);
        if (empty($rarray[$rands])) {
            IS_AJAX && ajaxReturns(0, "面试题库暂无数据", 0);
        }
        $rand_lists = $zixunmodel->getrandxixuninfo(array('id' => $rarray[$rands]));
        if (empty($rand_lists)) {
            IS_AJAX && ajaxReturns(0, "面试题库暂无数据", 0);
        }
        $html = '<input type="hidden" id="vid" value="' . $rand_lists['id'] . '"><p>[面试题]' . $rand_lists['title'] . '</p><a href="' . $this->url('content', array('id' => $rand_lists['id']), 'zixun') . '" target="_blank" class="xkjy_dp">快就业专家点评</a>';
        IS_AJAX && ajaxReturns(1, "面试题库随机成功", $html);
    }

    public function actiondownload() {
        $request = new grequest();
        $id = $request->getParam('id') ? (int) $request->getParam('id') : 0;

        //根据id判断该篇文章是否存在
        $zixunmodel = $this->load('zixun');
        $where['id'] = $id;
        $article_info = $zixunmodel->getxixuninfo($where); //文章表详细

        $fileurl = $article_info['enclosure'];

        if (!empty($fileurl)) {
            $pos = strrpos($fileurl, '/');
            $len = strlen($fileurl);
            $name = substr($fileurl, $pos + 1, $len);
            $file_path = substr($fileurl, 0, $pos + 1);
        } else {
            $fileurl = $article_info['images'];
            $pos = strrpos($fileurl, '/');
            $len = strlen($fileurl);
            $name = substr($fileurl, $pos + 1, $len);
            $file_path = substr($fileurl, 0, $pos + 1);
        }
        //echo '<pre>';
        //print_r($name);exit;
        //$file_dir='E:/web2/zhaopin/web/upload/files/fm';
        $file_dir = $_SERVER['DOCUMENT_ROOT'] . $file_path;
        if (!file_exists($file_dir . $name)) {
            header("Content-type: text/html; charset=utf-8");
            echo "File not found!";
            exit;
        } else {
            $file = fopen($file_dir . $name, "r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . filesize($file_dir . $name));
            Header("Content-Disposition: attachment; filename=" . $name);
            echo fread($file, filesize($file_dir . $name));
            fclose($file);
        }
    }

    //2015-12-30 by dxl完成
    //获取广告位信息
    public function actiongetAdverts($flag = 0) {
        //广告位获取
        $adverts = S('advertcache');
        if ($flag == 1) {
            $adverts_j = $adverts[4][0]; //资讯首页中间大焦点图
            $adverts_r = multi_array_sort($adverts[5], 'ordid'); //资讯首页右边3个小的
            $adverts2['adverts_j'] = $adverts_j;
            $adverts2['adverts_r'] = $adverts_r;
        } else if ($flag == 2) {
            $adverts_lr = multi_array_sort($adverts[7], 'ordid'); //资讯列表详情右边3个小的
            $adverts2['adverts_lr'] = $adverts_lr;
        } else if ($flag == 3) {
            $adverts_bm = multi_array_sort($adverts[9], 'ordid'); //资讯列表详情右边3个小的
            $adverts2['adverts_bm'] = $adverts_bm;
        }
        unset($adverts);
        return $adverts2;
    }

    //内容页面
    public function actioncontent() {
        $request = new grequest();
        $id = $request->getParam('id') ? (int) $request->getParam('id') : 0;

        $zixunmodel = $this->load('zixun');
        $where['id'] = $id;
        $article_info = $zixunmodel->getxixuninfo($where); //文章表详细
        $conent_info = $zixunmodel->getxixuncontentinfo($where); //内容表详细
        //修改点击量
        $data = array();
        $data['hits'] = $article_info['hits'] + 1;
        $zixunmodel->edit($data, $id);

        $catid = empty($article_info['catid']) ? 0 : $article_info['catid']; //栏目id
        //上一篇
        $where2 = array();
        $where2['previous_id'] = $id;
        $where2['catid'] = $catid;
        $where2['status'] = 1;
        $previous_page = $zixunmodel->getxixuninfo($where2, '*', 'id DESC');

        //下一篇
        $where3 = array();
        $where3['next_id'] = $id;
        $where3['catid'] = $catid;
        $where3['status'] = 1;
        $next_page = $zixunmodel->getxixuninfo($where3, '*', 'id ASC');

        //广告位获取
        $adverts_lr = $this->actiongetAdverts(2);
        switch($catid){
            case 2:
                $this->webseo['title'] = $article_info['title'].'_求职简历-快就业网';
                break;
            case 3:
                $this->webseo['title'] = $article_info['title'].'_简历制作技巧-快就业网';
                break;
            case 4:
                $this->webseo['title'] = $article_info['title'].'_投简历技巧-快就业网';
                break;
            case 24:
                $this->webseo['title'] = $article_info['title'].'_求职面试真题-快就业网';
                break;
            case 25:
                $this->webseo['title'] = $article_info['title'].'_面试经典题-快就业网';
                break;
            case 26:
                $this->webseo['title'] = $article_info['title'].'_求职面试真题解析-快就业网';
                break;
            case 28:
                $this->webseo['title'] = $article_info['title'].'_HR面试经验-快就业网';
                break;
            case 29:
                $this->webseo['title'] = $article_info['title'].'_求职面试礼仪-快就业网';
                break;
            case 30:
                $this->webseo['title'] = $article_info['title'].'_求职面试经验-快就业网';
                break;
        }
        $this->render('content', array(
            'article_info' => $article_info, 
            'conent_info' => $conent_info, 
            'catid' => $catid,
            'previous_page' => $previous_page, 
            'next_page' => $next_page, 
            'adverts_lr' => $adverts_lr['adverts_lr'],
            'seoinfo' => $this->webseo
        ));
    }

    //列表页 >>更多
    public function actionlists() {
        $request = new grequest();
        $catid = $request->getParam('catid') ? (int) $request->getParam('catid') : 0;

        $zixunmodel = $this->load('zixun');
        $page = $request->getParam('page', 1);
        $limit = 20;

        $where = array();
        $where['status'] = 1;
        if (!empty($catid))
            $where['catid'] = $catid;

        $total = $zixunmodel->getzixunpagecount($where);
        $lists = $zixunmodel->getzixunpagelist($limit, ($page - 1) * $limit, $where);
        $pages = $this->page($total, $page, $limit, 5, $where);

        //栏目缓存
        $categorycache = S('categorycache');

        //广告位获取
        $adverts_lr = $this->actiongetAdverts(2);
        switch($catid){
            case 2:
                $this->webseo['title'] = '求职简历_求职简历模板_求职简历格式/范文_求职简历内容-快就业网';
                $this->webseo['keywords'] = '求职简历,求职简历模板,求职简历模板下载,求职简历范文,求职简历内容撰写';
                $this->webseo['description'] = '快就业人才招聘网教你如何写求职简历，规范求职简历内容，提供求职简历模板、求职简历范文等，还不会写求职简历的小伙伴们，快快登陆快就业网http://www.kjiuye.com 查看吧！';
                break;
            case 3:
                $this->webseo['title'] = '求职简历制作技巧_简历制作技巧全解_个人简历制作-快就业网';
                $this->webseo['keywords'] = '求职简历制作,求职简历制作技巧,简历制作技巧全解,个人简历制作';
                $this->webseo['description'] = '快就业人才招聘网教你如何制作求职简历，传授求职简历制作技巧，全方位解读简历制作技巧等，求职简历制作的相关问题，登陆快就业网www.kjiuye.com 查看吧！';
                break;
            case 4:
                $this->webseo['title'] = '投简历_投简历邮件正文_投简历技巧_简历投递注意事项-快就业网';
                $this->webseo['keywords'] = '投简历,投简历邮件正文,投简历技巧,简历投递注意事项';
                $this->webseo['description'] = '快就业人才招聘网教你如何投递求职简历、如何写投简历邮件正文及在投简历过程中注意事项，更多的投简历技巧的方法，请关注快就业网www.kjiuye.com 查看吧！';
                break;
            case 24:
                $this->webseo['title'] = '求职面试真题_求职动机面试题_求职面试题汇总-快就业网';
                $this->webseo['keywords'] = '求职面试真题,求职动机面试题,求职面试题汇总';
                $this->webseo['description'] = '快就业人才招聘网整理求职面试真题、求职动机面试题、求职面试题汇总，更多的求职面试题，请关注快就业网www.kjiuye.com！';
                break;
            case 25:
                $this->webseo['title'] = '求职面试经典题_求职面试经典英语口语-快就业网';
                $this->webseo['keywords'] = '求职面试经典题,求职面试经典英语口语';
                $this->webseo['description'] = '快就业人才招聘网整理求职面试经典题，包括最常见的英文面试问题，应届毕业生面试题目，常问EQ测试题，经典教师面试问题等，更多信息请关注快就业网www.kjiuye.com！';
                break;
            case 26:
                $this->webseo['title'] = '常见求职面试真题_求职面试试题及答案_面试真题解析-快就业网';
                $this->webseo['keywords'] = '常见求职面试题,求职面试试题及答案';
                $this->webseo['description'] = '快就业人才招聘网整理求职面试真题，包括常见求职面试真题，求职面试真题及答案解析等，更多信息请关注快就业网www.kjiuye.com！';
                break;
            case 28:
                $this->webseo['title'] = 'HR面试及回答技巧_HR解答求职面试常见问题_HR面试经验之谈-快就业网';
                $this->webseo['keywords'] = 'HR面试及回答技巧,HR解答求职面试常见问题,HR面试经验之谈';
                $this->webseo['description'] = '快就业人才招聘网不断整理HR谈面试的相关文章，帮助应聘者更好的理解HR面试过程中所提问题的涵义，在HR面试中有更好的回答技巧，更多信息请关注快就业网www.kjiuye.com！';
                break;
            case 29:
                $this->webseo['title'] = '求职面试礼仪_求职面试礼仪与技巧_职场面试礼仪-快就业网';
                $this->webseo['keywords'] = '求职面试礼仪,求职面试礼仪与技巧,职场面试礼仪';
                $this->webseo['description'] = '快就业人才招聘网教你如何在求职面试过程中注意求职面试礼仪，教你如何使用在求职面试过程中的面试礼仪技巧，帮助你在求职应聘的过程中更好的掌握面试官的心理，更多的相关信息请关注快就业网www.kjiuye.com';
                break;
            case 30:
                $this->webseo['title'] = '求职面试经验总结_求职面试经验分享_应聘职场面试经历-快就业网';
                $this->webseo['keywords'] = '求职面试经验总结,求职面试经验分享,应聘职场面试经历';
                $this->webseo['description'] = '快就业人才招聘网整理应聘者在求职面试的过程中的面试经验，分享求职面试经验文章，应聘职场面试经历，缺少经验的小伙伴快快登陆快就网 www.kjiuye.com 查看吧！';
                break;
        }
        $this->render('lists', array(
            'lists' => $lists, 
            'catid' => $catid, 
            'pages' => $pages, 
            'categorycache' => $categorycache, 
            'adverts_lr' => $adverts_lr['adverts_lr'],
            'seoinfo' => $this->webseo
        ));
    }

    //简历经典封面>>更多列表
    public function actionfengmian() {
        $categorycache = S('categorycache'); //栏目缓存
        $zixunmodel = $this->load('zixun'); //model类

        $where = '';
        $catlist = array();
        $where = ' and catid in (7,8,9,10)';
        $cats = $zixunmodel->group_course('catid', $where, 'catid');
        if (!empty($cats)) {
            foreach ($cats as $key => $val) {
                $list = array();
                $list = $zixunmodel->getzixunall(array('catid' => $val['catid']), 'id,title,images,inputtime');
                $catlist[$val['catid']] = $list;
            }
        }

        $this->render('fengmian', array(
            'catlist' => $catlist, 
            'bmtype' => $bmtype, 
            'categorycache' => $categorycache,
            'seoinfo' => $this->webseo
        ));
    }

    //导航-简历模板精选
    public function actionmoban() {
        $categorycache = S('categorycache'); //栏目缓存
        $zixunmodel = $this->load('zixun'); //model类

        $where = '';
        $catlist = array();
        $where = ' and catid in (6,11,12)';
        $cats = $zixunmodel->group_course('catid', $where, 'catid');
        if (!empty($cats)) {
            foreach ($cats as $key => $val) {
                $list = array();
                $list = $zixunmodel->getzixunpagelist(8, 0, array('catid' => $val['catid']), 'id,title,images,inputtime');
                $catlist[$val['catid']] = $list;
            }
        }
        $this->render('moban', array(
            'catlist' => $catlist, 
            'bmtype' => $bmtype, 
            'categorycache' => $categorycache,
            'seoinfo' => $this->webseo
        ));
    }

}
