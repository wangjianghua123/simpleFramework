<?php

/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 * ************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ERROR);
ini_set('display_errors', 0);
ini_set('error_log', '/home/web/test/web/log/err_' . date('Y-m-d') . '.txt');
define('ROOT_PATH', realpath(dirname(__FILE__)));
define('PUBLIC_URL', "http://www.test.com/");
define('UC_ROOT', dirname(__FILE__) . '/');
define('UC_DATADIR', UC_ROOT . '../data/');
define('RETURN_URL', 'stationresume/list');
session_start();
require_once(ROOT_PATH . "/alipay.config.php");
require_once(ROOT_PATH . "/lib/alipay_notify.class.php");
//计算得出通知验证结果
define('VERSION', '1.5.4');

//计算得出通知验证结果
if (!@include UC_DATADIR . 'config.inc.php') {
    exit('The file <b>data/config.inc.php</b> does not exist, perhaps because of UCenter has not been installed, <a href="install/index.php"><b>Please click here to install it.</b></a>.');
}
require_once UC_ROOT . 'lib/db.class.php';
$db_zf = new ucserver_db();
$db_zf->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();
if ($verify_result) { //验证成功
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //请在这里加上商户的业务逻辑程序代
    //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
    //商户订单号
    $out_trade_no = $_POST['out_trade_no'];
    //支付宝交易号
    $trade_no = $_POST['trade_no'];
    //交易状态
    $trade_status = $_POST['trade_status'];

    //如果有做过处理，不执行商户的业务程序
    if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序
        //注意：
        //该种交易状态只在两种情况下出现
        //1、开通了普通即时到账，买家付款成功后。
        //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
        $trade_no = $_POST['trade_no'];
        //交易状态
        $trade_status = $_POST['trade_status'];
        if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
            /*
             *  判断该笔订单是否在商户网站中已经做过处理
             *  如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
             *  如果有做过处理，不执行商户的业务程序
             */
            $orderinfo = $userinfo = $update = array();
            //根据订单id获取订单相应的数据
            $orderinfo = $db_zf->createCommand()->select('*')
                    ->from("kjy_station_order")
                    ->where("order_id = :order_code", array(":order_code" => $out_trade_no))
                    ->limit(1)
                    ->queryRow();
            if (!empty($orderinfo)) {
                $uid = $orderinfo['uid'];
                $userinfo = $db_zf->createCommand()->select('uid')
                        ->from("kjy_members")
                        ->where("uid = :user_id", array(":user_id" => $uid))
                        ->limit(1)
                        ->queryRow();
                $orderinfo['order_phone'] = empty($orderinfo['order_phone']) ? $userinfo['phone'] : $orderinfo['order_phone'];
                if ($orderinfo['order_status'] == 1) {//如果订单状态已经为已支付
                    $url = PUBLIC_URL . RETURN_URL;
                    Header("Location:" . $url);
                    exit;
                }
                if ($orderinfo['pay_fee'] == (float) $_GET['total_fee']) {
                    //修改订单状态和时间
                    $update['code'] = $trade_no; //支付宝交易号
                    $update['order_status'] = 1;
                    $order_status = 1;
                    $update['pay_time'] = time();
                    $oflag = $db_zf->createCommand()->update("kjy_station_order", $update, 'id=:id', array(':id' => $orderinfo['id']));
                    //插入短信队列
                    //$offcnname = '【快就业】';
//                        $insert = array();
//                        $insert['oid'] = $orderinfo['order_id'];
//                        $insert['uid'] = $orderinfo['uid'] ? $orderinfo['uid'] : $uid;
//                        $insert['phone'] = $orderinfo['order_phone'];
//                        if (!empty($orderinfo['order_user_passwd'])) {
//                            $insert['messages'] = "支付成功，请使用账号" . $orderinfo['order_phone'] . "密码" . $orderinfo['order_user_passwd'] . $offcnname;
//                        } else {
//                            $insert['messages'] = "支付成功，请使用账号" . $orderinfo['order_phone'] . "登陆" . $offcnname;
//                        }
                    log_result_is("支付宝直接返回:" . $out_trade_no . ",pay:" . $_GET['total_fee'] . ",total:" . $orderinfo['pay_fee'] . ",交易号:" . $trade_no . ",状态:" . $_GET['trade_status']);
                    $url = PUBLIC_URL . RETURN_URL;
                    Header("Location:" . $url);
                    exit;
                } else {
                    $url = PUBLIC_URL . RETURN_URL;
                    Header("Location:" . $url);
                    exit;
                }
            }
        } else {
            log_result_is("支付宝直接返回:" . $out_trade_no . ",pay:" . $_GET['total_fee'] . ",total:" . $orderinfo['pay_fee'] . ",交易号:" . $trade_no . ",状态:" . $_GET['trade_status']);
            $url = PUBLIC_URL . RETURN_URL;
            Header("Location:" . $url);
            exit;
        }
    }
    //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——   
    echo "success";  //请不要修改或删除
    exit;
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} else {
    //验证失败
    echo "fail";
    //调试用，写文本函数记录程序运行情况是否正常
    log_result_is("支付宝fail:" . $_POST['out_trade_no'] . ",,pay:" . $_POST['total_fee'] . ",,total:" . $ordercourseinfo['total'] . ",,交易号:" . $trade_no . ",,状态:" . $_POST['trade_status']);
    exit;
}

function log_result_is($word) {
    $fp = fopen("/home/web/test/web/log/log_zfb" . date('Y-m-d') . ".txt", "a");
    flock($fp, LOCK_EX);
    fwrite($fp, $word . "：执行日期：" . strftime("%Y%m%d%H%I%S", time()) . "\t\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

function log_result($msg, $name) {
    $logFile = '/home/web/test/web/log/log_zfb' . $name . date('Y-m-d') . '.txt';
    $msg = date('Y-m-d H:i:s') . ' >>> ---' . $msg . "\r\n";
    file_put_contents($logFile, $msg, FILE_APPEND);
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

?>