<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 * ************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 */
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ERROR);
ini_set('display_errors', 1);
ini_set('error_log', '/home/web/test/web/log/err_' . date('Y-m-d') . '.txt');
define('ROOT_PATH', realpath(dirname(__FILE__)));
define('PUBLIC_URL', "http://www.test.com/");
define('UC_ROOT', dirname(__FILE__) . '/');
define('UC_DATADIR', UC_ROOT . '../data/');
define('RETURN_URL','stationresume/list');
session_start();
require_once(ROOT_PATH . "/alipay.config.php");
require_once(ROOT_PATH . "/lib/alipay_notify.class.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <?php
        //计算得出通知验证结果
        if (!@include UC_DATADIR . 'config.inc.php') {
            exit('The file <b>data/config.inc.php</b> does not exist, perhaps because of UCenter has not been installed, <a href="install/index.php"><b>Please click here to install it.</b></a>.');
        }
        require_once UC_ROOT . '../lib/db.class.php';

        $db_zf = new ucserver_db();
        $db_zf->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        //商户订单号
        $out_trade_no = $_GET['out_trade_no'];
        if ($verify_result) {//验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            //支付宝交易号
            $trade_no = $_GET['trade_no'];
            //交易状态
            $trade_status = $_GET['trade_status'];
            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
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
        } else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            if(!empty($out_trade_no) && !empty($_GET['total_fee'])){
                log_result_is("支付宝直接返回失败:" . $out_trade_no . ",pay:" . $_GET['total_fee'] . ",total:" . $orderinfo['pay_fee'] . ",交易号:" . $trade_no . ",状态:" . $_GET['trade_status']);
            }
            Header("HTTP/1.1 303 See Other"); //这条语句可以不写  
            $url = PUBLIC_URL . RETURN_URL;
            Header("Location: $url");
            exit;
        }

        function log_result_is($word) {
            $fp = fopen("/home/web/test/web/log/log_zfb".date('Y-m-d').".txt","a");
            flock($fp, LOCK_EX);
            fwrite($fp, mb_convert_encoding($word, "gb2312", "utf-8") . "\r\n\t" . mb_convert_encoding("请求地址：", "gb2312", "utf-8"). $_SERVER[REQUEST_URI] . "\r\t" . mb_convert_encoding("执行日期：", "gb2312", "utf-8") . date("Y-m-d H:i:s", time()). "\r\n");
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        ?>
        <title>跳转中......</title>
    </head>
    <body>
    </body>
</html>