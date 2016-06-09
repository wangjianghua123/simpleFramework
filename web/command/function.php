<?php 
/**
  * A useful one that I use in development is the following
  * which dumps the target with syntax highlighting on by default
  */
 // 浏览器友好的变量输出
 function dump($var, $echo = true, $label = null, $strict = true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }
    else
        return $output;
}

//获取用户信息
function curl_user($url = '', $data = '', $t = 0) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $output = curl_exec($ch);
    curl_close($ch);
    if ($t) {
        echo $output;
    } else {
        return json_decode($output, true);
    }
}
/**
 * Ajax方式返回数据到客户端
 * @access protected
 * @param mixed $data 要返回的数据
 * @param String $type AJAX返回数据格式
 * @return void
 * 定义是否是ajax提交的获取 20103-09-14 18:00 wjh 
 */
function ajaxReturn($data, $type = '') {
    if (func_num_args() > 2) {  // 兼容3.0之前用法
        $args = func_get_args();
        array_shift($args);
        $info = array();
        $info['data'] = $data;
        $info['info'] = array_shift($args);
        $info['status'] = array_shift($args);
        $data = $info;
        $type = $args ? array_shift($args) : '';
    }
    if (empty($type))
        $type = 'JSON';
    switch (strtoupper($type)) {
        case 'JSON' :
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data));
        case 'XML' :
            // 返回xml格式数据
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($data));
        case 'JSONP':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            $handler = isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
            exit($handler . '(' . json_encode($data) . ');');
        case 'EVAL' :
            // 返回可执行的js脚本
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
        case 'JSONS':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:text/html; charset=utf-8');//用于兼容IE浏览器不返回ajax
            exit(json_encode($data));
        default :
            // 用于扩展其他返回格式数据
            exit();
    }
}


/**
 * AJAX返回数据标准  公共方法
 *
 * @param int $status
 * @param string $msg
 * @param mixed $data
 * @param string $dialog 弹窗使用 指定是哪个弹窗 比如 编辑edit 添加add等
 * 定义是否是ajax提交的获取 20103-09-14 18:00 wjh 
 */
function ajaxReturns($status = 1, $msg = '', $data = '', $dialog = '',$is_type= '') {
    if(empty($is_type)){
        ajaxReturn(array(
            'status' => $status,
            'msg' => $msg,
            'data' => $data,
            'dialog' => $dialog,
        ));
    }else{
        ajaxReturn(array(
            'status' => $status,
            'msg' => $msg,
            'data' => $data,
            'dialog' => $dialog,
        ),'jsons');
    }
    
}

/**
 * Ajax方式返回数据到客户端 手机端专用
 * @access protected
 * @param mixed $data 要返回的数据
 * @param String $type AJAX返回数据格式
 * @return void
 * 定义是否是ajax提交的获取 20103-09-14 18:00 wjh 
 */
function ajaxReturnforapp($data, $type = '') {
    if (func_num_args() > 2) {  // 兼容3.0之前用法
        $args = func_get_args();
        array_shift($args);
        $info = array();
        $info['data'] = $data;
        $info['infos'] = array_shift($args);
        $info['flag'] = array_shift($args);
        $data = $info;
        $type = $args ? array_shift($args) : '';
    }
    if (empty($type))
        $type = 'JSON';
    switch (strtoupper($type)) {
        case 'JSON' :
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data));
        case 'XML' :
            // 返回xml格式数据
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($data));
        case 'JSONP':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            $handler = isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
            exit($handler . '(' . json_encode($data) . ');');
        case 'EVAL' :
            // 返回可执行的js脚本
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
        default :
            // 用于扩展其他返回格式数据
            exit();
    }
}

/**
 * AJAX返回数据标准  公共方法 手机端专用
 *
 * @param int $flag 手机返回使用
 * @param string $msg
 * @param mixed $data
 * @param string $dialog 弹窗使用 指定是哪个弹窗 比如 编辑edit 添加add等
 * 定义是否是ajax提交的获取 2013-09-14 18:00 wjh 
 */
function ajaxReturnsforapp($flag = 1, $msg = '', $data = array()) {
    ajaxReturnforapp(array(
        'flag' => $flag,
        'infos' => $msg,
        'data' => $data
    ));
}


/**
  +----------------------------------------------------------
 * 字符串截取，支持中文和其它编码
  +----------------------------------------------------------
 * @static
 * @access public
  +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function msubstr($str = "", $start = 0, $length = 0, $charset = "utf-8", $suffix = false) {
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix)
        return $slice . "…";
    return $slice;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 获取客户端浏览器
 * @return string
 */
function getBrower() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Maxthon')) {
        $browser = 'Maxthon';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 12.0')) {
        $browser = 'IE12.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 11.0')) {
        $browser = 'IE11.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0')) {
        $browser = 'IE10.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0')) {
        $browser = 'IE9.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) {
        $browser = 'IE8.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) {
        $browser = 'IE7.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
        $browser = 'IE6.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'NetCaptor')) {
        $browser = 'NetCaptor';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape')) {
        $browser = 'Netscape';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx')) {
        $browser = 'Lynx';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
        $browser = 'Opera';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
        $browser = 'Google';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')) {
        $browser = 'Firefox';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
        $browser = 'Safari';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iphone') || strpos($_SERVER['HTTP_USER_AGENT'], 'ipod')) {
        $browser = 'iphone';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'ipad')) {
        $browser = 'iphone';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'android')) {
        $browser = 'android';
    } else {
        $browser = 'other';
    }
    return $browser;
}


//创建目录
function CreateFolder($path) {
    if (!file_exists($path)) {
        CreateFolder(dirname($path));
        mkdir($path, 0777) or die('目录权限不够， 无法建立文件夹');
    }
}

/*

 * $msg 信息提示的内容 

 * $gourl 需要跳转的网址 

 * $onlymsg 1 表示不自动跳转 0表示自动跳转 

 * $limittime 跳转的时间 
 */

function ShowMsg($msg, $gourl, $onlymsg = 0, $limittime = 0) { //系统提示信息 
    global $dsql, $cfg_ver_lang;
    if (eregi("^gb", $cfg_ver_lang))
        $cfg_ver_lang = 'utf-8';

    $htmlhead = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\r\n";
    $htmlhead .= "<html><head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n";
    $htmlhead .= "<title>快就业</title>\r\n<link href=\"" . PUBLIC_URL . "/public/css/style.css\" rel=\"stylesheet\" type=\"text/css\" />\r\n";
    $htmlhead .= "<script type=\"text/javascript\" src=\"" . PUBLIC_URL . "/public/js/jquery.min.js\"></script>\r\n";
    $htmlhead .= "\r\n</head><body>\r\n";
    $htmlfoot = "</body>\r\n</html>\r\n";
    if ($limittime == 0)
        $litime = 1000;
    else
        $litime = $limittime;
    //$litime = 10000000000000;
    if ($gourl == "-1") {
        if ($limittime == 0)
            $litime = 1000;

        $gourl = "javascript:history.go(-1);";
    }
    if ($gourl == "" || $onlymsg == 1) {
        $msg = "<script>alert(\"" . str_replace("\"", "“", $msg) . "\");</script>";
    } else {
        $rmsg = "<div class=\"zg_404\"><div class=\"zg404 zgTz\">";
        $rmsg .= $msg;
        $rmsg .= "<a href=\"" . $gourl . "\">返回</a>";
        $rmsg .= "</div></div><script>";
        $rmsg .= '$(function(){
            $(".zg_404").height($(window).height()-124)
	})';
        $rmsg .= "
        function onTimesP(miao){
            miao--;
            $('#tt').html(miao);
            if(miao>0){
                setTimeout(\"onTimesP(\"+miao+\")\", ".$litime.");
            }else{
                window.location.href='" . $gourl . "';
            }    
        }
        onTimesP(3);";
        $rmsg .= "</script>";
        $msg = $htmlhead . $rmsg . $htmlfoot;
    }
    if (isset($dsql) && is_object($dsql))
        @$dsql->Close();

    echo $msg;
}

/**
 * 发送邮件--普通
 * @param type $email
 * @param type $message
 * @param type $subject
 * @return int
 */
function SetEmails($email, $message, $subject = '找回密码 【快就业】',$subtitle = '快就业') {
    $logoimg = ($subtitle == '快就业') ? PUBLIC_URL . 'public/images/zg_logo.jpg' : 'http://pin.ujiuye.com/public/images/zg_logo.jpg';
    $messages = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta name="description" content="">
                <meta name="keywords" content="">
                <title>' . $subject . '</title>
                <link href="' . PUBLIC_URL . '/public/css/public.css" rel="stylesheet" type="text/css" />
                <script language="javascript" src="' . PUBLIC_URL . '/public/js/jquery.min.js"></script>
                </head>

                <body>
                <table style="margin:0;color:#333; font:16px/26px \'微软雅黑\',\'宋体\',Arail; " border="0" cellpadding="0" cellspacing="0" width="600">
                  <tbody><tr><td style="height:40px; padding-left:20px; padding-top:20px;"><a href="" target="_blank"><img src="' . $logoimg . '" width="122" height="47" /></a></td></tr>
                      <tr style="background-color:#fff;"><td style="padding:10px 20px;">
                  <div style=" font-size:28px; font-weight:bold; color:#333;">Hi！亲爱的<strong style="color:#164a84">' . $email . '</strong></div>
                  <div style="margin:12px 0;">欢迎加入'. $subtitle .'网！</div>
                  ' . $message . '
                </body>
                </html>';
    require UC_ROOT . './release/PHPMailer/class.phpmailer.php';
    $mail = new PHPMailer();
    $mail->CharSet = "utf-8";
    $mail->IsSMTP(); // send via SMTP
    $mail->Host = "mail.offcn.com";
    $mail->SMTPAuth = true; // turn on SMTP authentication
    $mail->Username = "webmaster@offcn.com"; // SMTP username
    $mail->Password = "zg.0911,188JY"; // SMTP password
    $mail->From = "webmaster@offcn.com";
    $mail->FromName = "【" .$subtitle . "】";
    $mail->AddAddress($email); // optional name
    $mail->IsHTML(true); // send as HTML
    $mail->Subject = $subject;
    $mail->Body = $messages;
    $mail->Send();
    return 1;
}

/**
 * 发送邮件--给HR用户发送简历
 * @param type $email
 * @param type $messages
 * @param type $subject
 * @return int
 */
function SetToHREmails($email, $messages, $subject) {
    require_once UC_ROOT . '../release/PHPMailer/class.phpmailer.php';
    $mail = new PHPMailer();
    $mail->CharSet = "utf-8";
    $mail->IsSMTP(); // send via SMTP
    $mail->Host = "mail.offcn.com";
    $mail->SMTPAuth = true; // turn on SMTP authentication
    $mail->Username = "webmaster@offcn.com"; // SMTP username
    $mail->Password = "zg.0911,188JY"; // SMTP password
    $mail->From = "webmaster@offcn.com";
    $mail->FromName = "【快就业】";
    $mail->AddAddress($email); // optional name
    $mail->IsHTML(true); // send as HTML
    $mail->Subject = $subject;
    $mail->Body = $messages;
    $mail->Send();
    return 1;
}

/**
 * 裁剪图片
 * @param type $ori
 * @return type
 */
function resize($ori) {
    if (preg_match('/^http:\/\/[a-zA-Z0-9]+/', $ori)) {
        return $ori;
    }
    $info = getImageInfo(ROOT_PATH . $ori);
    if ($info) {
        //上传图片后切割的最大宽度和高度
        $width = 500;
        $height = 500;
        $scrimg = ROOT_PATH . $ori;
        if ($info['type'] == 'jpg' || $info['type'] == 'jpeg') {
            $im = imagecreatefromjpeg($scrimg);
        }
        if ($info['type'] == 'gif') {
            $im = imagecreatefromgif($scrimg);
        }
        if ($info['type'] == 'png') {
            $im = imagecreatefrompng($scrimg);
        }
        if ($info['width'] <= $width && $info['height'] <= $height) {
            return;
        } else {
            if ($info['width'] > $info['height']) {
                $height = intval($info['height'] / ($info['width'] / $width));
            } else {
                $width = intval($info['width'] / ($info['height'] / $height));
            }
        }
        $newimg = imagecreatetruecolor($width, $height);
        imagecopyresampled($newimg, $im, 0, 0, 0, 0, $width, $height, $info['width'], $info['height']);
        imagejpeg($newimg, ROOT_PATH . $ori);
        imagedestroy($im);
    }
    return;
}

/**
 * 计算字符串长度
 * @param type $str
 * @return type
 */
function counums($str) {
    $str = preg_replace('/[\x80-\xff]{1,3}/', ' ', $str, -1); 
    $num = strlen($str);
    return $num;
}

/**
 * 获取图片信息
 * @param type $img
 * @return boolean
 */
function getImageInfo($img) {
    $imageInfo = getimagesize($img);
    if ($imageInfo !== false) {
        $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
        $info = array(
            "width" => $imageInfo[0],
            "height" => $imageInfo[1],
            "type" => $imageType,
            "mime" => $imageInfo['mime'],
        );
        return $info;
    } else {
        return false;
    }
}

function curlst($data, $action = 'index', $type = 'gwy', $t = 0) {
    $url = "http://ti.offcn.com/" . $type . '/page_school/' . $action;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $output = curl_exec($ch);
    curl_close($ch);
    if ($t) {
        echo $output;
    } else {
        return json_decode($output, true);
    }
}

//获取用户信息
function curl_login($url = '', $data = '', $t = 0) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);    //注意，毫秒超时一定要设置这个 是可以支持毫秒级别超时设置的 
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000);  //超时毫秒，cURL 7.16.2中被加入。从PHP 5.2.3起可使用
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 30000);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $output = curl_exec($ch);
    $info = curl_error($ch);
    curl_close($ch);
    if (!empty($info)) {
        $error = array();
        $error['error'] = $info;
        if (!empty($error)) {
            return json_decode($error, true);
        }
    }
    if ($t) {
        echo $output;
    } else {
        return json_decode($output, true);
    }
}

//UC_authcode()	可选，借用用户中心的函数加解密 Cookie
function UC_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key ? $key : UC_KEY);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 获得随机数字符串
 * @param int $length 随机数的长度
 * @return string 随机获得的字串
 */
function generateRandStr($length) {
    $mt_string = 'AzBy0CxDwEv1FuGtHs2IrJqK3pLoM4nNmOlP5kQjRi6ShTgU7fVeW8dXcY9bZa';
    $randstr = '';
    for ($i = 0; $i < $length; $i++) {
        $randstr .= $mt_string[mt_rand(0, 61)];
    }
    return $randstr;
}

function headers(){
     header("Content-type:text/html;charset=utf-8");
}

/**
 * 二维数组排序
 */
function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){  
    if(is_array($multi_array)){  
        $key_array = array();
        foreach ($multi_array as $row_array){  
            if(is_array($row_array)){  
                $key_array[] = $row_array[$sort_key];  
            }else{  
                return -1;  
            }  
        }  
    }else{  
        return -1;  
    }  
    array_multisort($key_array,$sort,$multi_array);  
    return $multi_array;  
}

/**
 * 二维数组去重
 */
function arr_unique($data = array()){
    $tmp = array();
    foreach($data as $key => $value){
        //把一维数组键值与键名组合
        foreach($value as $key1 => $value1){
            $value[$key1] = $key1 . '_|_' . $value1;
        }
        $tmp[$key] = implode(',|,', $value);
    }

    //对降维后的数组去重复处理
    $tmp = array_unique($tmp);

    //重组二维数组
    $newArr = array();
    foreach($tmp as $k => $tmp_v){
        $tmp_v2 = explode(',|,', $tmp_v);
        foreach($tmp_v2 as $k2 => $v2){
            $v2 = explode('_|_', $v2);
            $tmp_v3[$v2[0]] = $v2[1];
        }
        $newArr[$k] = $tmp_v3;
    }
    return $newArr;
}

/**
 * 缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name, $value = '', $options = null) {
    static $cache = '';
    if (is_array($options)) {
        // 缓存操作的同时初始化
        $type = isset($options['type']) ? $options['type'] : UC_CACHE_TYPE;
        require_once UC_ROOT . 'command/cache/driver/' . $type . '.class.php';
        $cache = Cache::getInstance($type, $options);
        $cache->select('10');
    } elseif (is_array($name)) { // 缓存初始化
        $type = isset($name['type']) ? $name['type'] : UC_CACHE_TYPE;
        require_once UC_ROOT . 'command/cache/driver/' . $type . '.class.php';
        $cache = Cache::getInstance($type, $name);
        $cache->select('10');
        return $cache;
    } elseif (empty($cache)) { // 自动初始化
        $type = isset($options['type']) ? $options['type'] : UC_CACHE_TYPE;
        require_once UC_ROOT . 'command/cache/driver/' . $type . '.class.php';
        $cache = Cache::getInstance();
        $cache->select('10');
    }
    if ('' === $value) { // 获取缓存
        return $cache->get($name);
    } elseif (is_null($value)) { // 删除缓存
        return $cache->rm($name);
    } else { // 缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : NULL;
        } else {
            $expire = is_numeric($options) ? $options : NULL;
        }
        return $cache->set($name, $value, $expire);
    }
}

/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code>
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @param boolean $save 是否保存结果
 * @return mixed
 */
function N($key, $step=0,$save=false) {
    static $_num    = array();
    if (!isset($_num[$key])) {
        $_num[$key] = (false !== $save)? S('N_'.$key) :  0;
    }
    if (empty($step)){
        return $_num[$key];
    }else{
        $_num[$key] = $_num[$key] + (int)$step;
    }
    if(false !== $save){ // 保存结果
        S('N_'.$key,$_num[$key],$save);
    }
    return null;
}
/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
 * @return string
 */
function to_guid_string($mix) {
    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists_case($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}


/**
 * 区分大小写的文件存在判断
 * @param string $filename 文件地址
 * @return boolean
 */
function file_exists_case($filename) {
    if (is_file($filename)) {
        if (IS_WIN) {
            if (basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}


// 获取Redis连接
function getRedis(){	
    while(!$bool){
        try{ 
            $redis = new \Redis();
//            if("PONG" !== $redis->ping()){
//                //发送命令进行重启后再进行连接  
//                $cmd = 'redis-server --service-restart';
//                exec($cmd); 
//            }
            $redis->pconnect('127.0.0.1','6379');  //php客户端设置的ip及端口
            $bool=true;
        } catch(Exception $e) {
            sleep(30); // 连接失败 休眠10秒
        }
    }
    Return $redis;
}


//开始时间转换 转换为时间戳
function zh($val) {
    $arr = explode('-', $val);
    $dd = mktime(0, 0, 0, $arr[1], $arr[2], $arr[0]);
    return $dd;
}

//精确时间 精确到秒
function zh_jq($val) {
    $arr = explode(' ', $val);
    $arr1 = explode('-', $arr[0]);
    $arr2 = explode(':', $arr[1]);
    $dd = mktime($arr2[0], $arr2[1], $arr2[2], $arr1[1], $arr1[2], $arr1[0]);
    return $dd;
}

// 结束时间 转换为时间戳
function zh_end($val) {
    $arr = explode('-', $val);
    $dd = mktime(23, 59, 59, $arr[1], $arr[2], $arr[0]);
    return $dd;
}


/**
 * 将字符串转换为数组
 *
 * @param	string	$data	字符串
 * @return	array	返回数组格式，如果，data为空，则返回空数组
 */
function string2array($data) {
    if ($data == '')
        return array();
    @eval("\$array = $data;");
    return $array;
}

/**
 * 将数组转换为字符串
 *
 * @param	array	$data		数组
 * @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
 * @return	string	返回字符串，如果，data为空，则返回空
 */
function array2string($data, $isformdata = 1) {
    if ($data == '')
        return '';
    if ($isformdata)
        $data = new_stripslashes($data);
    return addslashes(var_export($data, TRUE));
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
    if (!is_array($string))
        return stripslashes($string);
    foreach ($string as $key => $val)
        $string[$key] = new_stripslashes($val);
    return $string;
}

/**
 * 创建目录
 * @param type $path
 * @param type $mode
 * @return boolean
 */
function dir_create($path, $mode = 0777) {
    if (is_dir($path))
        return TRUE;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

/**
 * 转化 \ 为 /
 * @param type $path
 * @return string
 */
function dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

function getcontact($name) {
    $contact = array(
        'tel' => '400-091-0808',
        'time' => '9:00 - 22:00',
        'time2' => '服务时间：任何时候都欢迎<br>　　　　　您提出问题',
        'email' => 'kefu@kjiuye.com',
        'zxqq' => 'http://wpa.qq.com/msgrd?v=3&uin=3208534819&site=qq&menu=yes',
        'zxname' => '答疑解惑',
        'zglogo' => PUBLIC_URL . 'public/images/zg_logo.jpg'
    );
    if (!empty($name)) {
        return $contact[$name];
    } else {
        return '';
    }
}

?>