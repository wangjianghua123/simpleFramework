<?php

/**
 * 简历资讯
 */
!defined('IN_UC') && exit('Access Denied');

class about extends base {

    public $_uid;
    public $_uname;

    function __construct() {
        header("Content-type:text/html;charset=utf-8;");
        parent::__construct();
    }

    //简历资讯首页
    public function actionindex() {
        $this->render('index');
    }
}
