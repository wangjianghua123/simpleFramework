<?php
class AppOffcnReturn{
	
	var $sign_key = 'offcnwx2015|';
	
	function __construct(){
		
	}
	function AppOffcnReturn(){
		$this->__construct();
	}
	
	function verifyReturn($para,$get_sign)
	{
		if(empty($para))
		{
			return false;
			exit;
		}
		
		//参数过滤
		$arg_arr = array();
		while(list($key,$val) = each($para)){
			if($key == 'sign' || $val == '')
			{
				continue;
			}
			else if(in_array($key,array('m','a')))
		    {
				continue;
			}
			else{
				$arg_arr[$key] = $para[$key];
			}
		}
		
		//数组排序
		ksort($arg_arr);
		reset($arg_arr);
		
		//拼接成字符串
		$sign = '';
		if(!empty($arg_arr)){
			foreach($arg_arr as $v)
			{
				$sign .= $this->sign_key . rawurlencode($v);
			}
		}else{
			$sign .= $this->sign_key;
		}
		$sign = md5($sign);
		//签名验证
		$is_sign = false;
		if($sign == $get_sign)
		{
			$is_sign = true;
		}
		
		return $is_sign;
		
	}
	
	function par_filter($para)
	{
				
	}
	
	/**
	 * 对数组按照键名进行正向排序
	 * @param unknown $par 排序前的数组
	 * @return 排序后的数组
	 */
	function arg_sort($par)
	{
		ksort($para);
		reset($para);
		return $para;
	}
	
	
	
	
	/**
	 * 对字符串进行签名
	 * @param  $prestr 需要签名的字符串
	 * @param  $key
	 * return 签名结果
	 */
	function md5_sign($prestr,$key)
	{
		$presstr = $prestr .$key;
		return md5($prestr);
	}
	
	//验证签名
	function md5_verify($prestr,$sign,$key)
	{
		$prestr = $prestr . $key;
		$mysign = md5($prestr);
		if($mysign == $sign)
		{
			return true;	
		}
		else
		{
			return false;
		}
			
	}
	
	
	
	
}