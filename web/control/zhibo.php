<?php

/**
 * 简历直播回顾
 */
!defined('IN_UC') && exit('Access Denied');

class zhibo extends base {

    public $_uid;
    public $_uname;
    public $webseo = array(
        'title' => 'HR名师在线直播聊聊求职招聘那些事-快就业网',
        'keywords' => 'HR名师,求职招聘',
        'description' => '快就业人才招聘网提供HR名师在线直播平台，每周三与您不见不散，一起来聊聊求职招聘那些事吧！'
    );

    function __construct() {
        header("Content-type:text/html;charset=utf-8;");
        parent::__construct();
    }

    public function actionindex() {
        //广告位获取
        $adverts = S('advertcache');
        $adverts_j = $adverts[6][0]; //中间大焦点图
        $adverts_r = multi_array_sort($adverts[7], 'ordid'); //右边3个小的
        $this->render('index', array(
            'adverts_j' => $adverts_j,
            'adverts_r' => $adverts_r,
            'seoinfo'   => $this->webseo
        ));
    }

    //2015-12-30 by dxl
    public function actionindexnew() {
        //广告位获取
        $adverts = S('advertcache');
        $adverts_t = $adverts[11][0]; //顶部大焦点图
        $adverts_j = $adverts[6][0]; //中间大焦点图
        $adverts_r = multi_array_sort($adverts[5], 'ordid'); //右边3个小的
        //直播互动列表
        $liveactive = S('liveactivecache');
        $this->render('indexnew', array(
            'adverts_j' => $adverts_j,
            'adverts_r' => $adverts_r,
            'adverts_t' => $adverts_t,
            'liveactive' => $liveactive,
            'seoinfo'   => $this->webseo
        ));
    }

}

?>