<?php
!defined('IN_UC') && exit('Access Denied');
class user extends base {
       protected $uc_offcn_user;
	   public $_company_id;
	   public $_uc_usertype;//1普通用户 2企业用户
       public $_uid;
       function __construct() {
	    if(!$this->session('uc_offcn_uid') && !$_COOKIE['uc_offcn_uid']){			
                $this->redirect($this->url('login','','foreuser'));
            }elseif(!empty($_COOKIE['uc_offcn_uid'])){
                $this->setsession('uc_offcn_uid',$_COOKIE['uc_offcn_uid']);
                $this->setsession('uc_offcn_username',$_COOKIE['uc_offcn_username']);
                $this->setsession('uc_offcn_phone',$_COOKIE['uc_offcn_phone']);	
            }
            $this->_uid = $this->session('uc_offcn_uid');
			$this->_uc_usertype = $this->session('uc_usertype');
			$this->_company_id = $this->session('uc_company_id');
			$this->BindCompany();
            parent::__construct();
        }  
		
		/*
		 * 判断是否已经绑定公司
		 */
		function BindCompany(){
			if($this->_uc_usertype == 2 && $this->_company_id == 0){
				IS_AJAX && ajaxReturns(0,'请绑定公司后进行操作',0);
				ShowMsg("请绑定公司后进行操作",$this->url('writeusertocompany','','userforcompany'));
				die;
			}
		}
        
		function actionindex() {
            $this->basevar['title'] = "基本信息-中公教育";
            $foreuser = $this->load('foreuser');
            $uid = $this->_uid;
            $userinfo = $foreuser->getRow($uid);
            $name = $userinfo['nickname']?$userinfo['nickname']:($userinfo['email']?substr_replace($userinfo['email'], "*****", 3, strpos($userinfo['email'], '@')-3):substr_replace($userinfo['phone'], "*****", 3, 5));
            $userinfo['username'] = $name;
            $arealist = S('areacache');
            $zonelist = array();
            foreach($arealist as $ka => $va){
                $zonelist[$va['areaid']] = $va;
            }
            
            $this->render('index',array('userinfo'=>$userinfo,'zonelist' => $zonelist));
		}
        

        function actionuserinfo() {
            //修改用户信息暂时注销    
            $this->redirect($this->url('index','','foreuser'));exit;
            $request = new grequest();
            
            $this->basevar['title'] = "修改资料-中公教育";
            $foreuser_web = $this->load('foreuser');
	
            $userinfo = $foreuser_web->getRow($this->_uid);
            $arealist = S('areacache');
            $province = array();
            foreach($arealist as $ka => $va){
                if(!$va['parentid']){
                    $province[$va['areaid']] = $va;
                }
            }
            
            $city_list = array();
            foreach($arealist as $ka => $va){
                if($va['parentid']){
                    $city_list[$va['parentid']][] = $va;
                }
            }
            if($userinfo['province']){
                $city = $city_list[$userinfo['province']];
            }else{
                $city = array();
            }

            $userinfo['y'] = $userinfo['birth_year'] ? $userinfo['birth_year'] : date('Y',$userinfo['birthday']);
            $userinfo['m'] = $userinfo['birth_month'] ? $userinfo['birth_month'] : date('m',$userinfo['birthday']);
            $userinfo['logo'] = '';
            $company = $this->load('company');
            if($userinfo['logoid']){
                $touxiang = $company->getimagebyid($userinfo['logoid']);
                if($touxiang){
                    $userinfo['logo'] = $touxiang['imagepath'];
                }
            }
           
            
            $getbasicdata = S('getbasicdata');
            $education = unserialize($getbasicdata['education']);   //学历要求
            $work_experience = unserialize($getbasicdata['work_experience']); //工作经验

            if($_POST){
                $post['nickname'] = $request->getParam('nickname')?htmlspecialchars(strip_tags(trim($request->getParam('nickname')))):'';   //用户昵称       
                $post['realname'] = $request->getParam('realname')?htmlspecialchars(strip_tags(trim($request->getParam('realname')))):'';   //用户姓名
                $post['personal'] = $request->getParam('personal')?htmlspecialchars(strip_tags(trim($request->getParam('personal')))):'';   //个人说明
                if(!$post['nickname']){
                    ShowMsg('昵称不能为空！',$this->url('userinfo', '', 'user'));die;
                }
                if(!$post['realname']){
                    ShowMsg('真实姓名不能为空！',$this->url('userinfo', '', 'user'));die;
                }
                if(counums($post['personal'])>100){
                    ShowMsg('个人说明内容请不要超过100字！',$this->url('userinfo', '', 'user'));die;
                }

                $post['sex'] = intval(strip_tags($request->getParam('sex')));
                $post['birthday'] = strtotime(implode('-', $request->getParam('birthday'))); 
                $post['province'] = intval(strip_tags($request->getParam('province')));
                $post['city'] = intval(strip_tags($request->getParam('city')));
                $post['isjob'] = intval(strip_tags($request->getParam('isjob')));
                $post['school'] = htmlspecialchars(strip_tags(trim($request->getParam('school'))));
                $post['logoid'] = htmlspecialchars(strip_tags(trim($request->getParam('image_id'))));//头像id
                $post['address'] = htmlspecialchars(strip_tags(trim($request->getParam('address'))));//现居地
                $post['workexp'] = htmlspecialchars(strip_tags(trim($request->getParam('workexp'))));//工作经验
                $post['education'] = htmlspecialchars(strip_tags(trim($request->getParam('education'))));//最高学历
                $post['birth_year'] = $post['birthday'][0];
                $post['birth_month'] = $post['birthday'][1];
                $t = $foreuser_web->edit($post,$this->_uid);

                unset($post);
                if($t){
                    $this->setsession('uc_offcn_username',$post['nickname']);                    
                    ShowMsg('修改成功！',$this->url('userinfo', '', 'user'));
                }else{
                    ShowMsg('修改失败！',$this->url('userinfo', '', 'user'));
                }     
            }else{
                $this->render('userinfo',array('userinfo'=>$userinfo,'province'=>$province,'city'=>$city,'education' => $education,'work_experience' => $work_experience));   
            }
        }
        
        //获取地市
        function actionajaxarea(){
            $request = new grequest();
            $areaid = $request->getParam('areaid')?trim($request->getParam('areaid')):9;
            $arealist = S('areacache');
            $city = array();
            foreach($arealist as $ka => $va){
                if($va['parentid']){
                    $city[$va['parentid']][] = $va;
                }
            }
            $area = $city[$areaid];
            echo json_encode(array('area'=>$area));
        }
        
        //修改密码
        function actiondopassword() {
            $this->basevar['title'] = "修改密码-中公教育";
            $request = new grequest();
            if($_POST){
                $post['oldpassword'] = htmlspecialchars(trim($request->getParam('oldpassword')));
                $post['password'] = htmlspecialchars(trim($request->getParam('password')));
                $post['repassword'] = htmlspecialchars(trim($request->getParam('repassword')));
                if(!$post['oldpassword']){
                    ShowMsg('原密码不能为空！',$this->url('dopassword', '', 'user'));die;
                }
                if(!$post['password']){
                    ShowMsg('新密码不能为空！',$this->url('dopassword', '', 'user'));die;
                }
                if(!$post['repassword']){
                    ShowMsg('确认密码不能为空！',$this->url('dopassword', '', 'user'));die;
                }
                if($post['oldpassword']==$post['password']){
                    ShowMsg('新旧密码不能一致！',$this->url('dopassword', '', 'user'));die;
                }
                $foreuser = $this->load('foreuser');
                $userinfo = $foreuser->getRow($this->_uid);
                if($userinfo['password']!=md5(md5($post['oldpassword']).$userinfo['salt'])){
                    ShowMsg('原密码不正确！',$this->url('dopassword', '', 'user'));die;
                }
                if($post['password']!=$post['repassword']){
                    ShowMsg('两次密码不一致！',$this->url('dopassword', '', 'user'));die;
                }
                $salt = substr(uniqid(rand()), -6);
                $password['password'] = md5(md5($post['password']).$salt);
                $password['salt'] = $salt;
                $t = $foreuser->edit($password,$this->_uid);
                
                unset($post);
                if($t){
                    if(REIS_YQK && $userinfo['uc_uid']){
                        $reg['password'] = $password;
                        $reg['ucuid'] = $userinfo['uc_uid'];
                        curlst($reg,'password');
                    }
                    ShowMsg('密码修改成功！',$this->url('index', '', 'user'));die;
                }else{
                    ShowMsg('密码修改失败！',$this->url('dopassword', '', 'user'));die;
                }
                
            }else{
                $this->render('password');   
            }
        }
        
        function actioncheckpass() {
            $request = new grequest();
            $password = $request->getParam('password')?htmlspecialchars(trim($request->getParam('password'))):'';
            $foreuser_web = $this->load('foreuser');
            $userinfo = $foreuser_web->getRow($this->_uid);
            if($userinfo['password']!=md5(md5($password).$userinfo['salt'])){
                echo json_encode(array('recode'=>0,'error'=>'密码不正确'));die;
            }else{
                echo json_encode(array('recode'=>1));die;
            }            
        }
        
//        //更换或者绑定手机
//        function actiondophone() {
//            $request = new grequest();
//            if(empty($_POST['flag'])){
//                $post['phone'] = htmlspecialchars(trim($request->getParam('phone')));
//                $post['verify'] = htmlspecialchars(trim($request->getParam('verify')));
//                $istype = $request->getParam('istype')?htmlspecialchars(trim($request->getParam('istype'))):0;
//                if(!$post['phone']){
//                    echo json_encode(array('recode'=>0,'error'=>'手机不能为空'));die;
//                }
//                if(!$post['verify']){
//                    echo json_encode(array('recode'=>0,'error'=>'验证码不能为空'));die;
//                }                
//                if($this->session('captcha')!= base64_encode($post['verify'])){
//                    echo json_encode(array('recode'=>0,'error'=>'验证码错误'));die;
//                }
//                $name = $istype==1?'更换':'绑定';
//                $foreuser_web = $this->load('foreuser');
//                $phone['phone'] = $post['phone'];
//                $phone['phone_validate'] = 1;
//                
//                $t = $foreuser_web->edit($phone,$this->_uid);
//                if($t){
//                    if($istype == 1){
//                            unset($_SESSION['captcha']);
//                            $this->setcookie("uc_offcn_UC_auth", '');
//                            session_destroy();
//                    }
//                    echo json_encode(array('recode'=>1,'success'=>'手机'.$name.'成功，请重新登录···'));die;
//                }else{
//                    echo json_encode(array('recode'=>0,'error'=>'手机'.$name.'失败！'));die;
//                }                
//            }elseif($_POST['flag'] == 1){
//				$post['phone'] = htmlspecialchars(trim($request->getParam('phone')));
//                $post['verify'] = htmlspecialchars(trim($request->getParam('verify')));
//                if(!$post['phone']){
//                    echo json_encode(array('recode'=>0,'error'=>'手机不能为空'));die;
//                }
//                if(!$post['verify']){
//                    echo json_encode(array('recode'=>0,'error'=>'验证码不能为空'));die;
//                }                
//                if($this->session('captcha')!= base64_encode($post['verify'])){
//                    echo json_encode(array('recode'=>0,'error'=>'验证码错误'));die;
//                }
//                $foreuser_web = $this->load('foreuser');
//                $arr['phone_validate'] = 1;
//                
//                $t = $foreuser_web->edit($arr,$this->_uid);
//                if($t){				
//                    echo json_encode(array('recode'=>1,'success'=>'手机验证成功，请重新登录···'));die;
//                }else{
//                    echo json_encode(array('recode'=>0,'error'=>'手机验证失败！'));die;
//                }          
//			}            
//        }
//
//        
//        //更换或者绑定邮箱
//        function actiondoemail() {
//            $request = new grequest();
//            $foreuser_web = $this->load('foreuser');
//            $istype = $request->getParam('istype')?htmlspecialchars(trim($request->getParam('istype'))):1;//状态：1是绑定，3是更换
//            $act = array(1=>'验证',3=>'更换');
//            $que = $request->getParam('que')?htmlspecialchars(trim($request->getParam('que'))):1;//状态：1是输入，2是发送，3是完成
//            $userinfo = $foreuser_web->getUserInfo(array('uid'=>$this->_uid),'*');
//            $this->basevar['title'] = "设置邮箱-中公教育";
//            $email = $userinfo['email'];
//            $ul = array('que'=>$que,'istype'=>$istype);
//            if($_POST||$que==2){
//                $post['password'] = htmlspecialchars(trim($request->getParam('password')));
//                $post['email'] = $request->getParam('email')?htmlspecialchars(trim($request->getParam('email'))):$email;
//                
//                if(!$post['email']){
//                    ShowMsg("Email不能为空！",$this->url('doemail', '', 'user'));die; 
//                }
//                
//                if($istype==3){
//                    if(!$post['password']){
//                        ShowMsg("账号密码不能为空！",$this->url('doemail', array('que'=>1,'istype'=>$istype), 'user'));die;   
//                    }
//                    if($userinfo['email']== $post['email']){
//                        ShowMsg("新旧Email不能一致！",$this->url('doemail', array('que'=>1,'istype'=>$istype), 'user'));die;
//                    }
//                    if($userinfo['password']!=md5(md5($post['password']).$userinfo['salt'])){
//                        ShowMsg("账号密码不正确！",$this->url('doemail', array('que'=>1,'istype'=>$istype), 'user'));die;
//                    }                    
//                }
//                $info = $foreuser_web->getValueEmail($post['email']);
//                if(time()-$info['times']<60){
//                    ShowMsg("60秒后可以重新发送！",$this->url('doemail', array('que'=>$que,'istype'=>$istype), 'user'));die;
//                }
//                $where['email'] = $post['email'];
//                $total = $foreuser_web->getUserInfo($where,'*');
//
//                if($que!=2){
//                    if($total){
//                        ShowMsg("Email已存在！",$this->url('doemail', array('que'=>1,'istype'=>$istype), 'user'));die;
//                    }else{ 
//                        if(REIS_YQK){
//                            $userinfo = curlst($where,'isemail');
//                            if($userinfo['recode']==2){
//                                ShowMsg("Email已存在！",$this->url('doemail', array('que'=>1,'istype'=>$istype), 'user'));die;
//                            }                            
//                        }
//                    }                    
//                }
//                
//                if($istype==1 && $que!=2){
//                    $seg['email'] = $post['email'];
//                    $foreuser_web->edit($seg,$this->_uid);
//                    if(REIS_YQK){
//                        $seg['ucuid'] = $userinfo['uc_uid'];
//                        curlst($seg,'editemail');
//                    }
//                }
//                $value_key = substr(sha1(uniqid(mt_rand(), true)), 0, 50);
//
//                $path = $this->url('editeamil', array('email' => $post['email'], 'value_key' => $value_key), 'foreuser');
//                $strti = array(
//                    'email' => $post['email'],
//                    'oldemail' => $istype==1?$this->_uid:$userinfo['email'],
//                    'valuekey' => $value_key,
//                    'times' => time(),
//                    'istype'=>$istype//判断是否是更改邮箱
//                );
//                $foreuser_web->addvaluekey($strti);
//                $subject = $act[$istype].'邮箱 【快就业】';
//                $message = $worktitle = $workbest = '';
//                $worktitle = '<div style="margin:0px 0;">';
//                if($userinfo["identity"]==1){
//                    //普通用户
//                    $worktitle .= '请点击以下链接'.$act[$istype].'你的邮箱地址，验证后';
//                    $worktitle .= ($istype==1) ? "就可以使用快就业的所有功能啦!" : "HR就可以看到你的新地址啦！";
//                    $workbest = "祝您求职征途梦想成真！";
//                }elseif($userinfo["identity"]==2){
//                    //企业用户
//                    $worktitle .= "请点击以下链接验证你的邮箱地址，验证后";
//                    $worktitle .= ($istype==1) ? "就可以免费发布职位啦！" : "新收到的简历将会发送到新的接收简历邮箱中！";
//                    $workbest = "祝您工作顺利！";
//                }
//                $worktitle .= '</div>';
//                $message = $worktitle.'<div style="word-break:break-all;word-wrap:break-word;"><a href="'.$path.'" target="_blank" style="color:#4c6c98;text-decoration:underline;">'.$path.'</a><br /></div>';
//                $message .= '<div style="margin:0px 0; color:#666;">（该链接在24小时内有效，24小时后需要重新获取）</div>';                       
//                $message .= '<div style="margin-top:20px;">如果以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。</div><br /><div style="margin:0px 0;">'.$workbest.'<br />快就业，就好业</div>';
//                $message .= '<div style="margin-top:20px; float:right">快就业团队<br/>'.date("Y-m-d").'</div></td></tr></tbody></table>';
//                SetEmails($post['email'],$message,$subject); 
//                $email = $post['email'];
//            }
//            $emailst = explode('@',$email);
//            $url = "http://mail.".$emailst[1];
//            $emails = str_replace(substr($email, 5, 9), "******", $email);            
//            $this->render('doemail', array('userinfo' => $userinfo,'ul'=>$ul,'act'=>$act,'url'=>$url,'emails'=>$emails,'email'=>$email));                  
//        }
//        
        function actionajaxsetemail(){
                $request = new grequest();
                $foreuser_web = $this->load('foreuser');
                $istype = $request->getParam('istype')?htmlspecialchars(trim($request->getParam('istype'))):1;//状态：1是绑定，3是更换
                $act = array(1=>'绑定',3=>'更换');
                $email = $request->getParam('email')?htmlspecialchars(trim($request->getParam('email'))):1;//状态：1是输入，2是发送，3是完成
                $emailst = explode('@',$email);
                $url = "http://mail.".$emailst[1];
                $info = $foreuser_web->getValueEmail($email);
                $userinfo = $foreuser_web->getUserInfo(array('uid'=>$this->_uid),'*');
                if(time()-$info['times']<60){
                    echo json_encode(array('mag'=>'60秒后可以重新发送！','recode'=>0));die;
                }
            
                $value_key = substr(sha1(uniqid(mt_rand(), true)), 0, 50);
                $path = $this->url('editeamil', array('email' => $email, 'value_key' => $value_key), 'foreuser');
                $strti = array(
                    'email' => $email,
                    'oldemail' => $istype==1?$this->_uid:$userinfo['email'],
                    'valuekey' => $value_key,
                    'times' => time(),
                    'istype'=>$istype//判断是否是更改邮箱
                );
                $foreuser_web->addvaluekey($strti);
                $subject = $act[$istype].'邮箱 【快就业】';
                $message = '';    
                $message = '<div class="jihuo">请点击链接'.$act[$istype].'邮箱：<a href="'.$path.'" target="_blank">'.$path.'</a><br /><font>（该链接<em>在24小时内</em>有效，24小时后需要重新获取）</font></div>';                       
                $t = SetEmails($email,$message,$subject);
                $emailst = explode('@',$email);
                $url = "http://mail.".$emailst[1];
                if($t){
                    echo json_encode(array('mag'=>'发送成功！','recode'=>1,'url'=>$url));die;
                }else{
                    echo json_encode(array('mag'=>'发送失败！','recode'=>0));die;
                }
                
        }
//        
//        //用户填写验证邮箱进行验证
//        function actionemailverific() {
//            ini_set('display_error',1);
//
//            $request = new grequest();
//            $this->basevar['title'] = "设置邮箱-快就业";
//            $foreuser_web = $this->load('foreuser');
//            $istype = 2;//状态：是验证
//            $que = $request->getParam('que')?htmlspecialchars(trim($request->getParam('que'))):1;//状态：1是输入，2是发送，3是完成
//            $userinfo = $foreuser_web->getUserInfo(array('uid'=>$this->_uid),'*');
//            $email = $userinfo['email'];
//            $ul = array('que'=>$que,'istype'=>$istype);
//            $emailst = explode('@',$email);
//            $url = "http://mail.".$emailst[1];
//            $emails = str_replace(substr($email, 5, 9), "******", $email);    
//            $this->render('emailverific', array('userinfo' => $userinfo,'url'=>$url,'emails'=>$emails,'email'=>$email));   
//        }
//        
//        //接送用户提交的邮箱 发送验证邮件
//        function actionajaxverif() {
//            $request = new grequest();
//            $foreuser_web = $this->load('foreuser');
//            $post['email'] = $request->getParam('email')?htmlspecialchars(trim($request->getParam('email'))):'bitao@offcn.com';
//            if(!$post['email']){
//                echo json_encode(array('recode'=>0,'mag'=>'Email不能为空！'));die; 
//            } 
//            $info = $foreuser_web->getValueEmail($post['email']);
//            if(time()-$info['times']<60){
//                echo json_encode(array('recode'=>0,'mag'=>'请不要频繁操作，60秒后可以重新发送！'));die; 
//            }
//            $where['email'] = $post['email'];
//            $total = $foreuser_web->getUserInfo($where);
//            if(!$total['total']){
//                if(REIS_YQK){
//                    $userinfo = curlst($where,'isemail');
//                    if($userinfo['recode']==1){
//                        echo json_encode(array('recode'=>0,'mag'=>'Email不存在！'));die; 
//                    }
//                }else{
//                    echo json_encode(array('recode'=>0,'mag'=>'Email不存在！'));die; 
//                }
//            }
//            
//            $value_key = substr(sha1(uniqid(mt_rand(), true)), 0, 50);
//
//            $path = $this->url('editeamil', array('email' => $post['email'], 'value_key' => $value_key), 'foreuser');
//            $strti = array(
//                'email' => $post['email'],
//                'valuekey' => $value_key,
//                'times' => time(),
//                'istype'=>2//邮箱验证
//            );
//            $foreuser_web->addvaluekey($strti);
//            $subject = '邮箱验证 【快就业】';
//            $message = '';    
//            $message = '<div class="jihuo">请点击链接'.'邮箱验证邮箱：<a href="'.$path.'" target="_blank">'.$path.'</a><br /><font>（该链接<em>在24小时内</em>有效，24小时后需要重新获取）</font></div>';                       
//            SetEmails($post['email'],$message,$subject);  
//            echo json_encode(array('recode'=>1,'mag'=>'发送成功！'));die;               
//        }
        
        function actionheadport() {
            $this->basevar['title'] = "头像设置-中公教育";
            $foreuser_web = $this->load('foreuser');
            $user_head = $foreuser_web->getRowHead($this->_uid);
            $this->render('headport',array('user_head'=>$user_head));   
        }
        function actionupload() {
            if (!empty($_FILES)) {
                $type=array("jpg","jpeg","png");//设置允许上传文件的类型 //2015-4-24 17:19:59 zpf plus

                $destination_folder= ROOT_PATH.'/uploads/'; //上传文件路径
                
                $file = $_FILES["uploadedfile"];    //upfile未上传文本框的名字
                
                $pinfo = pathinfo($file["name"]);
                
                if($file['size']>20000000){
                    echo json_encode(array('retcode'=>0,'error'=>'图片太大！'),true);die;
                }
                
                $a = $pinfo['extension'];
                //判断文件类型   
                if(!in_array(strtolower($a),$type))   
                {    
                    $text=implode(",",$type);   
                    //$error = "您只能上传以下类型文件: ".$text."<br>";//2015-4-24 17:19:59 zpf edit 
                    $error = "您只能上传以下类型文件: ".$text;//2015-4-24 17:19:59 zpf plus 
                    echo json_encode(array('retcode'=>0,'error'=>$error),true); die;
                }
                
                if (!is_uploaded_file($file['tmp_name']))
                //是否存在文件
                {
                     echo json_encode(array('retcode'=>0,'err'=>'图片不存在!'),true);
                     exit;
                }   

                if ($file["error"] > 0)
                {
                  echo json_encode(array('retcode'=>0,'error'=>'头像上传失败！'),true);die;
                }
                
                $uid = $this->_uid;
                $destination = $destination_folder.$uid.'/';
                
                if(!file_exists($destination)){
                    mkdir($destination);
                }                
                
                $this->deldir($destination);
                
                $new_file_name = 'avatar_ori_'.time().'.'.$a;
                $targetFile = $destination . $new_file_name;
                move_uploaded_file($file['tmp_name'],$targetFile);
                //缩略289
                $img=new Image($targetFile,4,300,300,$targetFile);
                $img->outimage();
                
                $img1 = '/uploads/'.$uid.'/'.$new_file_name;
                resize($img1);
                $ret['retcode'] = 1;
                $ret['success'] = $img1;
            } else {
                echo json_encode(array('retcode'=>0,'error'=>'没有文件了！'),true);die;
            }
            $foreuser_web = $this->load('foreuser');
            $seit = array('smallavatar'=>'','mediumavatar'=>'','largeavatar'=>'','backupavatar'=>'');
            $foreuser_web->edituh($seit,$uid);
            $userinfo = $foreuser_web->getRowHead($uid);
            $post['slefavatar'] = $img;
            $post['uid'] = $uid;
            if(empty($userinfo)){
                $foreuser_web->insertuh($post);
            }
            exit( json_encode( $ret ) );
        }
        function actionresize() {
            $uid = $this->_uid;
            if( !$image = $_POST["img"] ){
                $ret['result_code'] = 101;
                $ret['result_des'] = "图片不存在";
            } else {

                    $dfilet = ROOT_PATH.'/uploads/'.$this->_uid.'/';
                    $sfile = ROOT_PATH. $image;
                    $file_tmp=explode('/', $sfile);
                    
                    $file=$file_tmp[count($file_tmp)-1];
                    
                    $x=$_REQUEST['x'];
                    $y=$_REQUEST['y'];
                    $width=$_REQUEST['w'];
                    $height=$_REQUEST['h'];
                    
                    //裁剪
                    $value1=$x.','.$y;
                    $value2=$width.','.$height;
                    $files = explode('.',$file);
                    $filest = explode('_',$files[0]);
                    $file = $filest[0].'_'.$filest[1].'.'.$files[1];
                    $dfile = $dfilet.'small_'.$file;
                    
                    $img = new Image($sfile,2,$value1,$value2,$dfile);
                    $img->outimage();
                    $filename = $img->getImageName();

                    //缩略200
                    $thumbname01 = str_replace("ori", "200", $file);
                    $dfile1 = $dfilet.$thumbname01;
                    $img1=new Image($filename,1,300,300,$dfile1);
                    $img1->outimage();

                    $filename1 = $img1->getImageName();    

					
                    $ret['result_code'] = 1;
                    $ret['result_des'] = array(
                        "backup"=> str_replace(ROOT_PATH, "", $filename1),
                    );                        
                    
            }
            $foreuser_web = $this->load('foreuser');
            $userinfo = $foreuser_web->getRowHead($uid);

            $post['backupavatar'] = $ret['result_des']['backup'];
            $post['slefavatar'] = $image;
            $this->setsession('userhead',$post['smallavatar']);
            if(!empty($userinfo)){
                $foreuser_web->edituh($post,$uid);
            }else{
                $post['uid'] = $uid;
                $foreuser_web->insertuh($post);
            }            
            echo json_encode($ret);
            exit();            
        }     
        function deldir($dir) {
            //先删除目录下的文件：
            $dh=opendir($dir);
            while ($file=readdir($dh)) {
              if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
              }
            }
            closedir($dh);
        }
        
        //第三方相关操作
        function actiontied() {
            $foreuser = $this->load('foreuser');
            $userthri = $foreuser->getuserthird($this->_uid);
            if(!empty($userthri)){
                foreach ($userthri as $k=>$v){
                    $userthris[$v['type']] = $v;
                }
            }
            $change = "";
            if ($this->session('uc_offcn_change')) {
                $change = $this->session('uc_offcn_change');
                unset($_SESSION['uc_offcn_change']);
            }
            if (($_GET['type'] == 5)) {
                $this->render('tied_it', array('userthris' => $userthris, 'url' => $url, 'change' => $change));
                exit;
            }
            $this->render('tied', array('userthris' => $userthris,'url'=>$url,'change'=>$change));   
        }
        
        function actionuntied() {
            $request = new grequest();
            $foreuser = $this->load('foreuser');
            $opeion = array('qq'=>1,"weibo"=>2,"weixin"=>3);
            $type = $request->getParam('cha')?htmlspecialchars(trim($request->getParam('cha'))):'';
            if(!$type||!$opeion[$type]){
                ShowMsg('类型不存在！',$this->url('tied', '', 'user'));die;
            }
            $uid = $this->_uid;
            $user = $foreuser->getusertied($uid,$opeion[$type]);
            if($user['id']){
                $foreuser->deltiedValue($user['id']);
                $userinfo = $foreuser->getRow($user['uid']);
                $name = $userinfo['nickname']?$userinfo['nickname']:($userinfo['email']?substr_replace($userinfo['email'], "*****", 3, 5):substr_replace($userinfo['phone'], "*****", 3, 5));
                if($this->session('uc_offcn_username')!=$name&&$_SESSION['rqr']==$opeion[$type]){
                    $this->setsession('uc_offcn_username',$name);
                    $this->setcookie('uc_offcn_username',$name);
                    $data= array("uid"=>$userinfo['uid'], 'nickname'=>$userinfo['nickname'], 'phone'=>$userinfo['phone'], 'is_img'=>0, 'uc_uid'=>$userinfo['uc_uid']);
                    $data_str = str_replace('+', '%2B', uc_authcode(json_encode($data), "ENCODE"));
                    $this->setsession('uc_offcn_change',$data_str);
                }
                ShowMsg('解绑成功！',$this->url('tied', '', 'user'));die;
            }else{
                $name = array("qq"=>'QQ','weixin'=>'微信','weibo'=>'微博');
                ShowMsg('请先绑定你的'.$name[$type].'！',$this->url('tied', '', 'user'));die;
            }
        }
        
        function actionbangding() {
            if ($_REQUEST['rqr'] == 1) { //qq登陆
                $key = $_SESSION['qqkey'];
                $openid = $_SESSION['qqopenid'];
                $nickname = $_SESSION['qqname'];
            } else if ($_REQUEST['rqr'] == 2) {
                $key = $_SESSION['wbkey'];
                $openid = $_SESSION['wbopenid'];
                $nickname = $_SESSION['wbname'];
            }else if($_REQUEST['rqr'] == 3){
                $key = $_SESSION['wxkey'];
                $openid = $_SESSION['wxopenid'];
                $nickname = $_SESSION['wxname'];
            }
            $uid = $this->_uid;
            $fu = $this->load("foreuser");
            if($key&&$openid){
                $user = $fu->getCustmerthird($_REQUEST['rqr'],$openid);
                if(empty($user)){
                    $seg['uid'] = $uid;
                    $seg['nickname'] = $nickname;
                    $seg['key'] = $key;
                    $seg['openid'] = $openid;
                    $seg['type'] = $_REQUEST['rqr'];
                    $seg['times'] = time();
                    $fu->insertthird($seg);                    
                }else{
                    if($uid!=$user["uid"]){
                        ShowMsg('该账号已被绑定',$this->url('tied', '', 'user'));die;
                    }else{
                        ShowMsg('已绑定成功！',$this->url('tied', '', 'user'));die;
                    }
                }
                if($_REQUEST['rqr']==1){
                    unset($_SESSION['qqkey']);
                    unset($_SESSION['qqopenid']);
                    unset($_SESSION['qqname']);
                    unset($_SESSION['qqsex']);                        
                }elseif($_REQUEST['rqr']==2){
                    unset($_SESSION['wbkey']);
                    unset($_SESSION['wbopenid']);
                    unset($_SESSION['wbname']);
                    unset($_SESSION['wbsex']);                          
                }else if($_REQUEST['rqr']==3){
                    unset($_SESSION['wxkey']);
                    unset($_SESSION['wxopenid']);
                    unset($_SESSION['wxname']);
                    unset($_SESSION['wxsex']);    
                }                
                ShowMsg('绑定成功！',$this->url('tied', '', 'user'));die;
            }            
        }
}

?>