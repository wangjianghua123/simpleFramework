<?php
/* *
 * 类名：ysepay_service
 * 功能：银盛B2C接口
 * 版本：V0.1
 * 日期：2014-04-24
 * 署名：
 * 说明：	
 */
class ysepay_service {
	
	function ysepay_service()
	{
		$this->param = array ();
		$this->param['usercode']            = "offcn_ys";
		$this->param['pfxpath']             = ROOT_PATH."/pay/lib/offcn_ys.pfx";
		$this->param['businessgatecerpath'] = ROOT_PATH."/pay/lib/businessgate.cer";
		
		$this->param['pfxpassword']         = "lyzml0911offcn";
 		$this->param['host']                = "pay.ysepay.com";
		$this->param['xmlbackmsg_url']      =  $this->param['host']."/businessgate/yspay.do";
                
	}
	
	/**
	 * B2C 即时到帐
	 * @input db
	 * @input datetime
	 * @input orderid
	 * @input amount 99.99 保留到分
	 * @input BankType
	 * @input BankAccountType
	 * @return success
	 * @return msg
	 * @return url
	 */
	function b2c_ysepay($input){

		$MsgCode          = "S3001";
		$BankType         = $input['BankType'];
		$BankAccountType  = $input['BankAccountType'];
		$datetime         = $input['datetime'];
		$date             = date('Ymd',strtotime($datetime));
		$datetime_string  = self::datetime2string($datetime);
		$orderid          = $input['orderid'];
		$amount           = $input['amount'];
		$amount_units_fen = $amount*100;

		$xml =
		'<?xml version="1.0" encoding="GBK"?>
		<yspay>
		  <head>
			<Ver>1.0</Ver>
			<Src>'.$this->param['usercode'].'</Src>
			<MsgCode>'.$MsgCode.'</MsgCode>
			<Time>'.$datetime_string.'</Time>
		  </head>
		  <body>
			<Order>
			  <OrderId>'.$orderid.'</OrderId>
			  <BusiCode>01000010</BusiCode>
			  <ShopDate>'.$date.'</ShopDate>
			  <Cur>CNY</Cur>
			  <Amount>'.(int)$amount_units_fen.'</Amount>
			  <Note>01000010网上购物</Note>
			  <Timeout>10000</Timeout>
			  <BankType>'.$BankType.'</BankType>
			  <BankAccountType>'.$BankAccountType.'</BankAccountType>
			</Order>
			<Payee>
			  <UserCode>'.$this->param['usercode'].'</UserCode>
			  <Name>北京中公未来教育咨询有限公司</Name>
			  <Amount>'.$amount_units_fen.'</Amount>
			</Payee>
			<Notice>
			  <PgUrl>'.PUBLIC_URL.'return_url_ys.php</PgUrl>
			  <BgUrl>'.PUBLIC_URL.'return_url_bg.php</BgUrl>
			</Notice>
		  </body>
		</yspay>';
		$sign_encrypt = self::sign_encrypt(array('data'=>$xml));

		$url = 'http://'.$this->param['xmlbackmsg_url']."?".http_build_query(array(
			'src'     => $this->param['usercode'],
			'msgCode' => $MsgCode,
			'msgId'   => time(),
			'check'   => $sign_encrypt['check'],
			'msg'     => $sign_encrypt['msg'],
		));

		$return = array('success'=>0,'msg'=>'内部错误，请与客服联系！');
		
		if(strlen($url)){
			$return['success'] = 1;
			$return['url']     = $url;
			$return['msg']     = "获取成功";
		}
		
		return $return;
	}
	
	
	/***
	 *日期转字符
	 *输入参数：yyyy-MM-dd HH:mm:ss
	 *输出参数：yyyyMMddHHmmss
	 */
	function datetime2string($datetime){
		return preg_replace('/\-*\:*\s*/','',$datetime);
	}
	
	
	/**
	 * 验签转明码
	 * @input  check
	 * @input  msg
	 * @return data
	 * @return success
	 */
	function unsign_crypt($input){
		$check  = trim($input['check'],'+');
		$msg    = $input['msg'];
		$return = array('success'=>0,'msg'=>'','check'=>'');
                $publickeyFile = $this->param['businessgatecerpath']; //公钥
		$certificateCAcerContent = file_get_contents($publickeyFile);

		$certificateCApemContent =  '-----BEGIN CERTIFICATE-----'.PHP_EOL.chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL).'-----END CERTIFICATE-----'.PHP_EOL;
                $success = openssl_public_decrypt (base64_decode($check),$finaltext,openssl_get_publickey($certificateCApemContent));

		$return = array('data'=>'','success'=>0);
		if($success){
			$return = array(
				'data'    => base64_decode($msg),
				'success' => 1 ,
			);
		}
		return $return;
	}
	
	/***
	 * 签名加密
	 * @input  data
	 * @return success
	 * @return check
	 * @return msg
	 */
	function sign_encrypt($input){
		$input['data'] = iconv("UTF-8","GBK//IGNORE",$input['data']);
		
		$return = array('success'=>0,'msg'=>'','check'=>'');
			
		$pkcs12 = file_get_contents($this->param['pfxpath']); //私钥

		if (openssl_pkcs12_read($pkcs12, $certs, $this->param['pfxpassword'])) {
			$privateKey = $certs['pkey']; 
			$publicKey  = $certs['cert'];
			
			$signedMsg = ""; 
			if (openssl_sign($input['data'], $signedMsg, $privateKey,OPENSSL_ALGO_MD5)) { 
				$return['success'] = 1;
				$return['check']   = sprintf('%-256s',base64_encode($signedMsg));
				$return['msg']     = base64_encode($input['data']);
			}
		}
		
		return $return;
	}
        
        
        function b2c_ysepay_new($input){

		$MsgCode          = "S3001";
		$BankType         = $input['BankType'];
		$BankAccountType  = $input['BankAccountType'];
		$datetime         = $input['datetime'];
		$date             = date('Ymd',strtotime($datetime));
		$datetime_string  = self::datetime2string($datetime);
		$orderid          = $input['orderid'];
		$amount           = $input['amount'];
		$amount_units_fen = $amount*100;
                $Remark           = $input['Remark'];

		$xml =
		'<?xml version="1.0" encoding="GBK"?>
		<yspay>
		  <head>
			<Ver>1.0</Ver>
			<Src>'.$this->param['usercode'].'</Src>
			<MsgCode>'.$MsgCode.'</MsgCode>
			<Time>'.$datetime_string.'</Time>
		  </head>
		  <body>
			<Order>
			  <OrderId>'.$orderid.'</OrderId>
			  <BusiCode>01000010</BusiCode>
			  <ShopDate>'.$date.'</ShopDate>
			  <Cur>CNY</Cur>
			  <Amount>'.(int)$amount_units_fen.'</Amount>
			  <Note>01000010网上购物</Note>
			  <Timeout>10000</Timeout>
			  <BankType>'.$BankType.'</BankType>
			  <BankAccountType>'.$BankAccountType.'</BankAccountType>
                          <Remark>'.$Remark.'</Remark>
			</Order>
			<Payee>
			  <UserCode>'.$this->param['usercode'].'</UserCode>
			  <Name>北京中公未来教育咨询有限公司</Name>
			  <Amount>'.$amount_units_fen.'</Amount>
			</Payee>
			<Notice>
			  <PgUrl>'.HTTP_SERVER.'return_url_ys.php</PgUrl>
			  <BgUrl>'.HTTP_SERVER.'return_url_bg.php</BgUrl>
			</Notice>
		  </body>
		</yspay>';

		$sign_encrypt = self::sign_encrypt(array('data'=>$xml));

		$url = 'http://'.$this->param['xmlbackmsg_url']."?".http_build_query(array(
			'src'     => $this->param['usercode'],
			'msgCode' => $MsgCode,
			'msgId'   => time(),
			'check'   => $sign_encrypt['check'],
			'msg'     => $sign_encrypt['msg'],
		));

		$return = array('success'=>0,'msg'=>'内部错误，请与客服联系！');
		
		if(strlen($url)){
			$return['success'] = 1;
			$return['url']     = $url;
			$return['msg']     = "获取成功";
		}
		
		return $return;
	}
        
        
        
}
?>

