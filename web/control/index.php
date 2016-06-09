<?php
!defined('IN_UC') && exit('Access Denied');

class index extends base {
	function __construct() {
		parent::__construct();
	}
	function actionindex() {
          echo 'hello world kjy';
            $this->render('index');
	}
}

?>