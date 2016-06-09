<?php
!defined('IN_UC') && exit('Access Denied');
class templates extends base {
        protected $uc_offcn_user;
        public $_uid;
        public $_companyid;
        function __construct() {
           if(!$this->session('uc_offcn_uid')){
                $this->redirect($this->url('index','','user'));
            }
            $this->uid = $this->session('uc_offcn_uid');
            $foreuser = $this->load('foreuser');
            $userinfo = $foreuser->getRow($this->uid);
            if($userinfo['identity'] != 2){
                $this->redirect($this->url('index','','user'));
            }
            parent::__construct();
        }    
        
	function actionindex() {
            $uid = $this->uid;
            //获取用户信息
            $companyid = $this->_companyid;
            //获取面试通知模板列表
            $templates = $this->load('templates');
            $list = $templates->getlist($uid);
            
            $this->render('index',array('list' => $list));
	}
        
        //添加面试通知模板
        function actioninsertinterview(){
            $uid = $this->uid;
            $request = new grequest();
            $tmpname = $request->getParam('tmpname') ? htmlspecialchars(trim($request->getParam('tmpname'))) : '';  //模板名称
            $site = $request->getParam('site') ? htmlspecialchars(trim($request->getParam('site'))) : ''; //地址
            $linkman = $request->getParam('linkman') ? htmlspecialchars(trim($request->getParam('linkman'))) : '';  //联系人
            $mobile = $request->getParam('mobile') ? htmlspecialchars(trim($request->getParam('mobile'))) : ''; //联系电话
            $tmpTip = $request->getParam('tmpTip') ? htmlspecialchars(trim($request->getParam('tmpTip'))) : ''; //补充内容
            
            if(!$tmpname){
                IS_AJAX && ajaxReturns(0,'模板名称不能为空',0);
            }
            
            $data = array(
                'uid' => $uid,
                'tmpname' => $tmpname,
                'site' => $site,
                'linkman' => $linkman,
                'mobile' => $mobile,
                'tmpTip' => $tmpTip,
            );
			$templates = $this->load('templates');
            $list = $templates->getlist($uid);
            if(empty($list)){
                $data['status'] = 1;
            }
            
            $return = $templates->addinterview($data);
            
            if($return){
                IS_AJAX && ajaxReturns(1,'添加成功',0);
            }else{
                IS_AJAX && ajaxReturns(0,'添加失败',0);
            }
        }
        
        //修改面试通知模板
        function actioneditinterview(){
            $uid = $this->uid;
            $request = new grequest();
            $id = $request->getParam('tmpid') ? (int)$request->getParam('tmpid') : 0;  //模板ID
            $tmpname = $request->getParam('tmpname') ? htmlspecialchars(trim($request->getParam('tmpname'))) : '';  //模板名称
            $site = $request->getParam('site') ? htmlspecialchars(trim($request->getParam('site'))) : ''; //地址
            $linkman = $request->getParam('linkman') ? htmlspecialchars(trim($request->getParam('linkman'))) : '';  //联系人
            $mobile = $request->getParam('mobile') ? htmlspecialchars(trim($request->getParam('mobile'))) : ''; //联系电话
            $tmpTip = $request->getParam('tmpTip') ? htmlspecialchars(trim($request->getParam('tmpTip'))) : ''; //补充内容

            if(!$id){
                IS_AJAX && ajaxReturns(0,'模板ID为空',0);
            }
            if(!$tmpname){
                IS_AJAX && ajaxReturns(0,'模板名称不能为空',0);
            }
            
            $data = array(
                'uid' => $uid,
                'tmpname' => $tmpname,
                'site' => $site,
                'linkman' => $linkman,
                'mobile' => $mobile,
                'tmpTip' => $tmpTip,
            );
            
            $templates = $this->load('templates');
            $return = $templates->editinterview($data,$id);
            
            if($return){
                IS_AJAX && ajaxReturns(1,'添加成功',0);
            }else{
                IS_AJAX && ajaxReturns(0,'添加失败',0);
            }
        }
        
        //设置面试通知默认模板
        function actionsetdefaultinterview(){
            $uid = $this->uid;
            $request = new grequest();
            $id = $request->getParam('id') ? (int)$request->getParam('id') : 0;  //模板ID
            $templates = $this->load('templates');
            $return = $templates->setdeaultinterview($uid,$id);
            if($return){
                IS_AJAX && ajaxReturns(1,'设置成功',0);
            }else{
                IS_AJAX && ajaxReturns(0,'设置失败',0);
            }
        }
        
        //删除面试通知模板
        function actiondelinterview(){
            $uid = $this->uid;
            $request = new grequest();
            $id = $request->getParam('id') ? (int)$request->getParam('id') : 0;  //模板ID
            if(!$id){
                IS_AJAX && ajaxReturns(0,'删除失败',0);
            }
            $templates = $this->load('templates');
            $return = $templates->delinterview($id);
            if($return){
                IS_AJAX && ajaxReturns(1,'删除成功',0);
            }else{
                IS_AJAX && ajaxReturns(0,'删除失败',0);
            }
        }
        
        //不合适通知模板列表
        function actionrefuse(){
            //获取面试通知模板列表
            $templates = $this->load('templates');
            $list = $templates->getrefuselist($this->uid);
            
            $this->render('refuse',array('list' => $list));
        }
        
        //添加不合适通知模板
        function actionaddrefuse(){
            $request = new grequest();
            $title = $request->getParam('tmpname') ? htmlspecialchars(trim($request->getParam('tmpname'))) : '';  //模板名称
            $content = $request->getParam('tmpTip') ? htmlspecialchars(trim($request->getParam('tmpTip'))) : ''; //内容
            
            if(!$title){
                IS_AJAX && ajaxReturns(0,'模板名称不能为空',0);
            }
            
            $data = array(
                'uid' => $this->uid,
                'title' => $title,
                'content' => $content,
            );
            $templates = $this->load('templates');
            $list = $templates->getrefuselist($this->uid);
            if(empty($list)){
                $data['status'] = 1;
            }else{
				$data['status'] = 0;
			}
            $return = $templates->addrefuse($data);
            
            if($return){
                IS_AJAX && ajaxReturns(1,'添加成功',0);
            }else{
                IS_AJAX && ajaxReturns(0,'添加失败',0);
            }
        }
        
        //修改不合适通知模板
        function actioneditrefuse(){
            $uid = $this->uid;
            $request = new grequest();
            $id = $request->getParam('tmpid') ? (int)$request->getParam('tmpid') : 0;  //模板ID
            $title = $request->getParam('tmpname') ? htmlspecialchars(trim($request->getParam('tmpname'))) : '';  //模板名称
            $content = $request->getParam('tmpTip') ? htmlspecialchars(trim($request->getParam('tmpTip'))) : ''; //补充内容
            
            if(!$id){
                IS_AJAX && ajaxReturns(0,'模板ID为空',0);
            }
            if(!$title){
                IS_AJAX && ajaxReturns(0,'模板名称不能为空',0);
            }
            
            $data = array(
                'uid' => $uid,
                'title' => $title,
                'content' => $content,
            );
            
            $templates = $this->load('templates');
            $return = $templates->editrefuse($data,$id);
            
            if($return){
                IS_AJAX && ajaxReturns(1,'添加成功',0);
            }else{
                IS_AJAX && ajaxReturns(0,'添加失败',0);
            }
        }
        
        //设置面试通知默认模板
        function actionsetdefaultrefuse(){
            $uid = $this->uid;
            $request = new grequest();
            $id = $request->getParam('id') ? (int)$request->getParam('id') : 0;  //模板ID
            $templates = $this->load('templates');
            $return = $templates->setdeaultrefuse($uid,$id);
            if($return){
                IS_AJAX && ajaxReturns(1,'设置成功',0);
            }else{
                IS_AJAX && ajaxReturns(0,'设置失败',0);
            }
        }
        
        //删除不合适通知模板
        function actiondelrefuse(){
            $uid = $this->uid;
            $request = new grequest();
            $id = $request->getParam('id') ? (int)$request->getParam('id') : 0;  //模板ID
            if(!$id){
               IS_AJAX && ajaxReturns(0,'删除失败',0);
            }
            $templates = $this->load('templates');
            $return = $templates->delrefuse($id);
            if($return){
                IS_AJAX && ajaxReturns(1,'删除成功',0);
            }else{
                IS_AJAX && ajaxReturns(0,'删除失败',0);
            }
        }
}

?>