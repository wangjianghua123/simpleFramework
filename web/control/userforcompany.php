<?php

/*
 * 用户与公司关联关系
 */
!defined('IN_UC') && exit('Access Denied');

class userforcompany extends base {

    protected $uc_offcn_user;
    public $uid;
    public $_userinfo;
    public $_company_id;
    public $_uc_usertype; //1普通用户 2企业用户

    function __construct() {
        if (!$this->session('uc_offcn_uid')) {
            $this->redirect($this->url('index', '', 'user'));
        }
        $this->uid = $this->session('uc_offcn_uid');
        $this->_uc_usertype = $this->session('uc_usertype');
        $this->_company_id = $this->session('uc_company_id');
        parent::__construct();
    }

    /*
     * 判断是否已经绑定公司
     */

    function BindCompany() {
        if ($this->_uc_usertype == 2 && $this->_company_id == 0) {
            IS_AJAX && ajaxReturns(0, '请绑定公司后进行操作', 0);
            ShowMsg("请绑定公司后进行操作", $this->url('writeusertocompany', '', 'userforcompany'));
            die;
        } else if ($this->_uc_usertype == 1) {
            IS_AJAX && ajaxReturns(0, '对不起，您没有操作权限', 0);
            ShowMsg("对不起，您没有操作权限", $this->url('index', '', 'resume'));
            die;
        }
    }

    /*
     * 判断该账号是否不是招聘的
     */

    function BindCompanyfor() {
        if ($this->_uc_usertype == 1) {
            IS_AJAX && ajaxReturns(0, '对不起，您没有操作权限', 0);
            ShowMsg("对不起，您没有操作权限", $this->url('index', '', 'resume'));
            die;
        }
    }

    //用户解除公司绑定页面
    function actionindex() {
        if ($this->session('uc_company_id')) {
            //已绑定公司
            $company = $this->load('company');
            //获取绑定信息
            $service = $company->getusertocompany($this->uid);
            $companyid = $service['company_id'];
            //获取公司信息
            $companyinfo = $company->getcompanyinfo($companyid);
            $this->render('index', array('companyinfo' => $companyinfo));
        } else {
            $this->redirect($this->url('index', '', 'user'));
        }
    }

    //解除用户与公司的绑定
    function actionremoveset() {
        $this->BindCompany();
        init_db()->createCommand()->query("START TRANSACTION");
        $company = $this->load('company');
        $foreuser = $this->load('foreuser');
        $jobs = $this->load('jobs');

        //解除用户与公司关联关系
        $return = $company->removeset($this->uid);
        //解除用户发布的职位信息
        $rejob = $jobs->removejobforcompany($this->uid);
        //解除用户所收到的简历信息
        $redeli = $jobs->removejobfordelivery($this->uid);
        //修改用户类型-改为普通用户
        //$reuser = $foreuser->editidentity($this->uid, 1);

        if (false !== $return && false !== $rejob && false !== $redeli && false !== $reuser) {
            init_db()->createCommand()->query("COMMIT"); //成功提交
            $this->setsession('uc_company_id', '');
            $this->setcookie('uc_company_id', '');
            IS_AJAX && ajaxReturns(1, '解绑成功,', 0);
        } else {
            init_db()->createCommand()->query("ROLLBACK"); //失败回滚
            IS_AJAX && ajaxReturns(0, '操作失败', 0);
        }
    }

    //用户绑定公司--填写手机号和邮箱
    function actionwriteusertocompany() {
        $this->BindCompanyfor();
        $request = new grequest();
        $err = array();
        $foreuser = $this->load('foreuser');
        $userinfo = $foreuser->getRow($this->uid);
        $err_phone = $request->getParam('errphone') ? urldecode(trim($request->getParam('errphone'))) : '';
        $err_email = $request->getParam('erremail') ? urldecode(trim($request->getParam('erremail'))) : '';

        $this->render('writeusertocompany', array('userinfo' => $userinfo, 'err_phone' => $err_phone, 'err_email' => $err_email));
    }

    //用户绑定公司--选择公司
    function actionsetusertocompany() {
        $this->BindCompanyfor();
        $request = new grequest();
        $phone = $request->getParam('bindphone') ? htmlspecialchars(trim($request->getParam('bindphone'))) : '';  //手机号
        $email = $request->getParam('bindemail') ? htmlspecialchars(trim($request->getParam('bindemail'))) : '';
        if (!$this->checkphone($phone)) {
            $err['errphone'] = '手机号格式不正确，请重新输入';
        }
        if (!$this->checkemail($email)) {
            $err['erremail'] = '邮箱格式不正确，请重新输入';
        }
//        $filteremail = array(
//            1 => 'qq',
//            2 => '163',
//            3 => '126',
//            4 => 'sina',
//            5 => 'sohu'
//        );
//        $param2 = explode("@", $email);
//        $num = strpos($param2[1], ".");
//        $check = substr($param2[1], 0, $num);
//        if (false !== in_array($check, $filteremail)) {
//            ShowMsg("请输入企业邮箱。如无企业邮箱，请按下述温馨提醒中的内容操作。", "-1", 0, 3000);
//            die;
//        }
        if ($err) {
            $this->redirect($this->url('writeusertocompany', $err, 'userforcompany'));
        }

        $domain_str = substr(strstr($email, '@'), 1);
        $domain_arr = explode('.', $domain_str);
        $domain_num = count($domain_arr);
        $domain = $domain_arr[($domain_num - 2)] . '.' . $domain_arr[$domain_num - 1];
        $company = $this->load('company');
        //$company_list = $company->getlistbydomain($domain);
        $createcompany = $company->getcompanyinfobyuid($this->uid);
//        if (!empty($createcompany)) {
//            $company_list[] = $createcompany;
//        }
        $this->render('setusertocompany', array('companylist' => $createcompany, 'phone' => $phone, 'email' => $email));
    }

    //用户绑定公司--选择公司绑定
    function actionuserbindcompany() {
        $this->BindCompanyfor();
        $request = new grequest();
        $phone = $request->getParam('phone') ? htmlspecialchars(trim($request->getParam('phone'))) : '';  //手机号
        $email = $request->getParam('email') ? htmlspecialchars(trim($request->getParam('email'))) : '';    //邮箱地址
        //$comid = $request->getParam('comid') ? htmlspecialchars(trim($request->getParam('comid'))) : '';    //公司id
        $c_name = $request->getParam('c_name') ? htmlspecialchars(trim($request->getParam('c_name'))) : '';    //公司名称
        $showapply = $request->getParam('showapply') ? (int) $request->getParam('showapply') : 0;
        $isexist = $request->getParam('isexist') ? (int) $request->getParam('isexist') : 0;
        if(!empty($showapply)){
            $this->render('certapply', array('showapply' => $showapply));
        }
        if (empty($c_name)) {
            $err['erremail'] = '公司信息不存在';
        }
        if (!$this->checkphone($phone)) {
            $err['errphone'] = '手机号格式不正确，请重新输入';
        }
        if (!$this->checkemail($email)) {
            $err['erremail'] = '邮箱格式不正确，请重新输入';
        }
        //根据id获取公司信息
        $company = $this->load('company');
//        $companyinfo = $company->getcompanyinfo($comid);
//        if (!$companyinfo) {
//            $err['erremail'] = '公司信息有误，请重新选择';
//        }
        if ($c_name) {
            $iscreate = $company->getcompanyinfobyuid($this->uid, 'c_id,c_name,c_add_userid');
            //添加公司
            $c_data = array(
                'c_name' => $c_name,
                'c_add_userid' => $this->uid,
                'c_add_username' => $this->session('uc_offcn_username'),
                'c_add_time' => time()
            );
            if (empty($iscreate)) {
                $comid = $company->addcompany($c_data);
            } else {
                $comid = $iscreate['c_id'];
                //ShowMsg("您已创建公司：".$iscreate['c_name']."不能再创建了，请使用该公司名称和信息。", $this->url('setusertocompany', array('bindphone'=>$phone,'bindemail'=>$email), 'userforcompany'),0,2000);die;
                $company->editcompany($comid,$c_data,1);
            }
        } else {
            $err['erremail'] = '请重新选择或添加公司';
        }
        if ($err) {
            $this->redirect($this->url('setusertocompany', array('email' => $email), 'userforcompany'));
        }
        $bindinfo = array(
            'comid'=>$comid,
            'phone'=>$phone,
            'email'=>$email,
            'cname'=>$c_name,
            'exist'=>$isexist
        );
        $this->render('certapply', array('bindinfo' => $bindinfo));
        //发送邮件 按照刘斌老师要求，企业用户绑定公司后，不发送确认邮件，页面跳转至上传企业营业执照页。2016-02-18 FMJ
//        $value_key = substr(sha1(uniqid(mt_rand(), true)), 0, 50);
//        $foreuser = $this->load('foreuser');
//        $path = $this->url('verificationbindemail', array('phone' => $phone, 'email' => $email, 'value_key' => $value_key), 'userforcompany');
//        $strti = array(
//            'email' => $email,
//            'oldemail' => $this->uid . '_' . $phone . '_' . $comid,
//            'valuekey' => $value_key,
//            'times' => time(),
//            'istype' => 4//邮箱验证
//        );
//        $foreuser->addvaluekey($strti);
//        if (strpos($_SERVER['HTTP_REFERER'], "pin.ujiuye.com")){
//            $path = 'http://pin.ujiuye.com/userforcompany/verificationbindemail/email/'.$email.'/value_key/'.$value_key;
//            $issend = $this->sendemail_ujiuye(2, 4, $email, $path);
//        }else{
//            $issend = $this->sendemail(2, 4, $email, $path);
//        }
//        if ($issend)
//            $this->render('userbindcompany', array('phone' => $phone, 'email' => $email, 'comid' => $comid));
//        else
//            IS_AJAX && ajaxReturns(0, '邮件发送失败', 0);
    }

    //验证邮箱 过滤QQ、新浪 搜狐 网易等邮箱
    function actioncheck_email() {
        $request = new grequest();
        $param = $request->getParam('param') ? htmlspecialchars(trim($request->getParam('param'))) : '';
        $exis = $request->getParam('exis') ? htmlspecialchars(trim($request->getParam('exis'))) : 1;
        $filteremail = array(
            1 => 'qq',
            2 => '163',
            3 => '126',
            4 => 'sina',
            5 => 'sohu'
        );
        $param2 = explode("@", $param);
        $num = strpos($param2[1], ".");
        $check = substr($param2[1], 0, $num);
        if (false !== in_array($check, $filteremail)) {
            $result['status'] = 'n';
            $result['info'] = '请输入企业邮箱。如无企业邮箱，请按下述温馨提醒中的内容操作。';
        } else {
            $result['status'] = 'y';
            $result['info'] = '邮箱验证通过';
        }
        echo json_encode($result);
    }

    //用户绑定公司--选择公司绑定 ，重新发送邮件
    function actionagainsendemail() {
        $this->BindCompanyfor();
        $request = new grequest();
        $phone = $request->getParam('phone') ? htmlspecialchars(trim($request->getParam('phone'))) : '';  //手机号
        $email = $request->getParam('email') ? htmlspecialchars(trim($request->getParam('email'))) : '';    //邮箱地址
        $comid = $request->getParam('comid') ? htmlspecialchars(trim($request->getParam('comid'))) : '';    //邮箱地址
        if (!$comid || !$this->checkphone($phone) || !$this->checkemail($email)) {
            IS_AJAX && ajaxReturns(0, '重新发送失败', 0);
        }
        //根据id获取公司信息
        //$company = $this->load('company');
        //$companyinfo = $company->getcompanyinfo($comid);
        //发送邮件
        $value_key = substr(sha1(uniqid(mt_rand(), true)), 0, 50);
        $foreuser = $this->load('foreuser');
        $path = $this->url('verificationbindemail', array('phone' => $phone, 'email' => $email, 'value_key' => $value_key), 'userforcompany');
        $strti = array(
            'email' => $email,
            'oldemail' => $this->uid . '_' . $phone . '_' . $comid,
            'valuekey' => $value_key,
            'times' => time(),
            'istype' => 4
        );
        $foreuser->addvaluekey($strti);
        if (strpos($_SERVER['HTTP_REFERER'], "pin.ujiuye.com")){
            $path = 'http://pin.ujiuye.com/userforcompany/verificationbindemail/email/'.$email.'/value_key/'.$value_key;
            $issend = $this->sendemail_ujiuye(2, 4, $email, $path);
        }else{
            $issend = $this->sendemail(2, 4, $email, $path);
        }
        if ($issend)
            IS_AJAX && ajaxReturns(1, '邮件发送成功', 0);
        else
            IS_AJAX && ajaxReturns(0, '邮件发送失败', 0);
    }

    //验证用户绑定公司中的邮件连接
    function actionverificationbindemail() {
        $this->BindCompanyfor();
        $this->basevar['title'] = "验证邮箱 【快就业】";
        $request = new grequest();

        if ($_GET) {
            $post['email'] = htmlspecialchars(trim($request->getParam('email')));
            $post['value_key'] = htmlspecialchars(trim($request->getParam('value_key')));
            $foreuser = $this->load('foreuser');
            $info = $foreuser->getValue($post);
            if (!$info) {
                ShowMsg("抱歉，您的验证链接失效！<br>请重新绑定公司", $this->url('index', '', 'user'));
                die;
            }

            if (time() - $info['times'] > 86400) {
                //$foreuser->delValue($info['id']);
                ShowMsg("抱歉，您的验证链接失效！<br>请重新绑定公司", $this->url('index', '', 'user'));
                die;
            }

            if ($info['istype'] != 4) {
                //$foreuser->delValue($info['id']);
                ShowMsg("抱歉，您的验证链接失效！<br>请重新绑定公司", $this->url('index', '', 'user'));
                die;
            }
            $bindinfo = explode('_', $info['oldemail']);
            $userid = $bindinfo[0];
            $userphone = $bindinfo[1];
            $companyid = $bindinfo[2];
            $useremail = $post['email'];
            $company = $this->load('company');
            $companyinfo = $company->getcompanyinfo($companyid);
            if (empty($companyinfo)) {
                //$foreuser->delValue($info['id']);
                ShowMsg("抱歉，您的验证链接失效！<br>请重新绑定公司", $this->url('index', '', 'user'));
                die;
            }
            //将用户状态修改为企业用户
            $foreuser->editidentity($userid, 2);
            //查询用户id是否绑定过公司
            $bindold = $company->getusertocompany($userid);

            $binddata = array(
                'uid' => $userid,
                'company_id' => $companyid,
                'contact_phone' => $userphone,
                'contact_email' => $useremail,
                'status' => 0
            );
            $this->setsession('uc_company_id', $companyid);
            $this->setcookie('uc_company_id', $companyid, 3600 * 24);
            init_db()->createCommand()->query("START TRANSACTION");
            if ($bindold) {
                $company->edituserforcompany($bindold['id'], $binddata);
            } else {
                $company->adduserforcompany($binddata);
            }
            //修改公司对应下的无主职位
            $jobs = $this->load('jobs');
            $jobs->upwuzhujobs($userid, $companyid);
            //处理公司无 添加人和添加时间 的公司
            //判断是否存在公司的添加人和添加时间
            if (empty($companyinfo['c_add_userid']) || $companyinfo['c_add_userid'] == 0) {
                //$userinfo = $foreuser->getRow($this->uid);
                $upcarr = array(
                    'c_add_userid' => $userid,
                    'c_add_username' => $userphone,
                    'c_add_time' => time()
                );
                $company->editcompany($companyid, $upcarr, 1);
            }
            //如果已有该公司简历，则将该用户赋值给该公司
            $deliverymodel = $this->load("delivery");
            $tobesendlist = $deliverymodel->getresumesendemailbycid(array("cid" => $companyid, "status" => 0));
            if (!empty($tobesendlist)) {
                $jobde = $deliverymodel->edit_deliveryhrinfo(array("hr_uid" => $userid), array("cid" => $companyid, "hruid" => 0)); //更新简历投递表中的HR用户
                $deret = $deliverymodel->updatesumesendemailbycid(array("hr_uid" => $userid), $companyid); //更新HR邮箱投递表中的HR用户
                if (false === $deret && false === $jobde) {
                    init_db()->createCommand()->query("ROLLBACK"); //失败回滚
                    IS_AJAX && ajaxReturns(0, '更新公司已接收简历至用户失败', 0);
                }
            }
            $msg = '绑定公司邮箱已验证成功！<br />邮箱：' . $useremail . '<br />公司：' . $companyinfo['c_name'] . '<br />绑定成功！';
            $foreuser->delValue($info['id']);
            init_db()->createCommand()->query("COMMIT"); //成功提交
            $this->render('emailverific', array(
                'msg' => $msg,
                'companyid' => $companyid,
            ));
        }
    }

    //修改接收简历简历邮箱
    function actionupresumeemail() {
        $this->BindCompany();
        $company = $this->load('company');
        $cservice = $company->getusertocompany($this->uid);
        $email = $cservice['contact_email'];

        $this->render('upemail', array('email' => $email));
    }

    //修改接收简历简历邮箱--发送验证邮件
    function actionsendemail() {
        $request = new grequest();
        $email = $request->getParam('newemail') ? htmlspecialchars(trim($request->getParam('newemail'))) : '';
        if ($email) {
            //判断该邮箱是否已被使用
            $company = $this->load('company');
            $cservice = $company->getusertocompany($this->uid);
            if ($email == $cservice['contact_email']) {
                IS_AJAX && ajaxReturns(0, '<em></em>新的简历接收邮箱不能与原邮箱相同', 0);
                exit;
            }
            $domain_str = substr(strstr($email, '@'), 1);
            $domain_arr = explode('.', $domain_str);
            $domain_num = count($domain_arr);
            $domain = $domain_arr[($domain_num - 2)] . '.' . $domain_arr[$domain_num - 1];
            $oldemail = $cservice['contact_email'];
            $old_str = substr(strstr($oldemail, '@'), 1);
            $old_arr = explode('.', $old_str);
            $old_num = count($old_arr);
            $old = $old_arr[($old_num - 2)] . '.' . $domain_arr[$old_num - 1];
            if ($domain != $old) {
                IS_AJAX && ajaxReturns(0, '<em></em>邮箱后缀不同，解绑对当前公司的招聘服务<br />才可绑定其他公司的邮箱接收简历', 0);
                exit;
            }
            if ($cservice) {
                $value_key = substr(sha1(uniqid(mt_rand(), true)), 0, 50);
                $path = $this->url('upresumreceive', array('email' => $email, 'value_key' => $value_key), 'userforcompany');
                $strti = array(
                    'email' => $email,
                    'oldemail' => $this->uid . '_' . $cservice['contact_email'] . '_' . $cservice['company_id'],
                    'valuekey' => $value_key,
                    'times' => time(),
                    'istype' => 4 //修改投递简历邮箱
                );
                $fu = $this->load("foreuser");
                $fu->addvaluekey($strti);
                if (strpos($_SERVER['HTTP_REFERER'], "pin.ujiuye.com")){
                    $path = 'http://pin.ujiuye.com/userforcompany/upresumreceive/email/'.$email.'/value_key/'.$value_key;
                    $this->sendemail_ujiuye(2, 4, $email, $path, '【优聘】 修改简历接收邮箱');
                }else{
                    $this->sendemail(2, 4, $email, $path, '【快就业】 修改简历接收邮箱');
                }
                $emailst = explode('@', $email);
                $url = "http://mail." . $emailst[1];
                IS_AJAX && ajaxReturns(1, '验证邮件发送成功', $url);
            } else {
                IS_AJAX && ajaxReturns(0, '未查询到公司关系', 0);
            }
        } else {
            IS_AJAX && ajaxReturns(0, '邮箱地址为空', 0);
        }
    }

    //修改接收简历简历邮箱--验证邮件,修改简历接收邮箱
    function actionupresumreceive() {
        $this->BindCompanyfor();
        $this->basevar['title'] = "验证邮箱 【快就业】";
        $request = new grequest();
        if ($_GET) {
            $post['email'] = htmlspecialchars(trim($request->getParam('email')));
            $post['value_key'] = htmlspecialchars(trim($request->getParam('value_key')));
            $foreuser = $this->load('foreuser');
            $info = $foreuser->getValue($post);
            if (!$info) {
                ShowMsg("抱歉，您的验证链接失效！<br>请重新绑定公司", $this->url('index', '', 'user'));
                die;
            }
            if (time() - $info['times'] > 86400) {
                $foreuser->delValue($info['id']);
                ShowMsg("抱歉，您的验证链接失效！<br>请重新绑定公司", $this->url('index', '', 'user'));
                die;
            }
            if ($info['istype'] != 4) {
                $foreuser->delValue($info['id']);
                ShowMsg("抱歉，您的验证链接失效！<br>请重新绑定公司", $this->url('index', '', 'user'));
                die;
            }
            $bindinfo = explode('_', $info['oldemail']);
            $userid = $bindinfo[0];
            $oldemail = $bindinfo[1];
            $companyid = $bindinfo[2];
            $useremail = $post['email'];

            $company = $this->load('company');
            $companyinfo = $company->getcompanyinfo($companyid);
            if (!$companyinfo) {
                $foreuser->delValue($info['id']);
                ShowMsg("抱歉，您的验证链接失效！<br>请重新绑定公司", $this->url('index', '', 'user'));
                die;
            }

            $company->upresumreceive($userid, $companyid, $useremail);

            $foreuser->delValue($info['id']);
            $msg = '邮件验证成功，<br />简历接收邮箱：' . $oldemail . '<br />修改为：' . $useremail . '<br />修改成功！';
            $this->render('emailverific', array(
                'msg' => $msg,
            ));
        }
    }

    function actioniscreatecompany() {
        $company = $this->load('company');
        $service = $company->getusertocompany($this->uid);
        $iscreate = $company->getcompanyinfobyuid($this->uid, 'c_id,c_name,c_add_userid');
        if (empty($service) && !empty($iscreate)) {
            IS_AJAX && ajaxReturns(0, '您已创建公司：<font style="color:red;">' . $iscreate['c_name'] . '</font>，不能再创建了，请使用该公司名称和信息。');
        } else {
            IS_AJAX && ajaxReturns(1, '尚未创建公司');
        }
    }

}

?>