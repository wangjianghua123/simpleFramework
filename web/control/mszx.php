<?php

/**
 * 简历资讯
 */
!defined('IN_UC') && exit('Access Denied');

class mszx extends base {

    public $_uid;
    public $_uname;

    function __construct() {
        header("Content-type:text/html;charset=utf-8;");
        parent::__construct();
    }

    //面试资讯首页
    public function actionindex() {
        //今日头条 调用文章上传最新数据7条
        $zixunmodel = $this->load('zixun');
        $where['status'] = 1;
        $today_head_list = $zixunmodel->getzixunpagelist(7,0,$where);

        $zhineng_manage_list = $zixunmodel->getzixunpagelist(10,0,array('catid'=>16,'status'=>1));//职能管理类
        $market_operation_list = $zixunmodel->getzixunpagelist(10,0,array('catid'=>17,'status'=>1));//市场运营类
        $zhuanye_jishu_list = $zixunmodel->getzixunpagelist(10,0,array('catid'=>18,'status'=>1));//专业技术类
        $yybd_list = $zixunmodel->getzixunpagelist(10,0,array('catid'=>19,'status'=>1));//面试语言表达
        $dtjq_list = $zixunmodel->getzixunpagelist(10,0,array('catid'=>20,'status'=>1));//面试答题技巧
        $zzly_list = $zixunmodel->getzixunpagelist(10,0,array('catid'=>21,'status'=>1));//面试着装礼仪

        $jingdian_weike_list = $zixunmodel->getzixunpagelist(8,0,array('catid'=>22,'status'=>1));//面试经典微课

        $this->render('index',array('today_head_list'=>$today_head_list,'zhineng_manage_list'=>$zhineng_manage_list,'market_operation_list'=>$market_operation_list,'zhuanye_jishu_list'=>$zhuanye_jishu_list,'yybd_list'=>$yybd_list,'dtjq_list'=>$dtjq_list,'zzly_list'=>$zzly_list,'jingdian_weike_list'=>$jingdian_weike_list));
    }

    //内容页面
    public function actioncontent() {
        $request = new grequest();
        $id = $request->getParam('id') ? (int)$request->getParam('id') : 0;

        $zixunmodel = $this->load('zixun');
        $where['id'] = $id;
        $article_info = $zixunmodel->getxixuninfo($where); //文章表详细
        $conent_info = $zixunmodel->getxixuncontentinfo($where); //内容表详细

        $catid = empty($article_info['catid']) ? 0 : $article_info['catid']; //栏目id

        //上一篇
        $where2 = array();
        $where2['previous_id'] = $id;
        $where2['catid'] = $catid;
        $where2['status'] = 1;
		$previous_page = $zixunmodel->getxixuninfo($where2,'*','id DESC');

        //下一篇
        $where3 = array();
        $where3['next_id'] = $id;
        $where3['catid'] = $catid;
        $where3['status'] = 1;
		$next_page = $zixunmodel->getxixuninfo($where3,'*','id ASC');

        $this->render('content',array('article_info'=>$article_info,'conent_info'=>$conent_info,'catid'=>$catid,'previous_page'=>$previous_page,'next_page'=>$next_page));
    }

    //列表页 >>更多
    public function actionlists() {
        $request = new grequest();
        $catid = $request->getParam('catid') ? (int)$request->getParam('catid') : 0;

        $zixunmodel = $this->load('zixun');
        $page = $request->getParam('page', 1);
        $limit = 20;

        $where = array();
        $where['status'] = 1;
        if(!empty($catid)) $where['catid'] = $catid;

        $total = $zixunmodel->getzixunpagecount($where);
        $lists = $zixunmodel->getzixunpagelist($limit, ($page - 1) * $limit, $where);
        $pages = $this->page($total, $page, $limit,5,$where);

        //栏目缓存
        $categorycache = S('categorycache');

        $this->render('lists',array('lists'=>$lists,'catid'=>$catid,'pages'=>$pages,'categorycache'=>$categorycache));
    }


}

?>