<?php
class sms_send 
{

	var $target = "http://sms.tdxt.cn/sms.aspx";
	var $sname   = 'dlxzjyu';			// 用户名
	var $spwd    = 'dlxzjyu';			// 密码
	var $userid  = 1497;
	public $msg  = '';
	public $state = 0;
	
	// 发送操作
	function post($data, $target) {
		$url_info = parse_url($target);
		$httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
		$httpheader .= "Host:" . $url_info['host'] . "\r\n";
		$httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
		$httpheader .= "Content-Length:" . strlen($data) . "\r\n";
		$httpheader .= "Connection:close\r\n\r\n";
		//$httpheader .= "Connection:Keep-Alive\r\n\r\n";
		$httpheader .= $data;

		$fd = @fsockopen($url_info['host'],80,$errno, $errstr,10);
		fwrite($fd, $httpheader);
		$gets = "";
		while(!feof($fd)) {
			$gets .= fread($fd, 128);
		}
		fclose($fd);
		return $gets;
	}

	function xml_to_array($xml)                              
	{                                                        
	  $array = (array)(simplexml_load_string($xml));         
	  foreach ($array as $key=>$item){                       
		$array[$key]  =  $this->struct_to_array((array)$item);      
	  }                                                      
	  return $array;                                         
	}     

	function struct_to_array($item) {                        
	  if(!is_string($item)) {                                
		$item = (array)$item;                                
		foreach ($item as $key=>$val){                       
		  $item  = $this->struct_to_array($val);             
		}                                                    
	  }                                                      
	  return $item;                                          
	}  
	// 发送方法
	function send($phone,$content){
		if(!$phone){
			$this->msg="缺少手机号码";
			return false;
		}
		if(!$content){
			$this->msg="没有要发送的短信内容";
			return false;
		}
		$post_data="action=send&userid=".$this->userid."&account=".$this->sname."&password=".$this->spwd."&mobile=".$phone."&content=".rawurlencode($content);
//		$post_data.=";
		$gets = $this->post($post_data,$this->target);
		$s=explode("\r\n\r\n",$gets);  
		$result = $this->xml_to_array($s['1']);  
		$this->resolve_result($result);
		return $this->msg;
	}
	
	public function resolve_result($array){
		switch($array['returnstatus'])
		{
			case 'Success':
				$this->msg="发送成功！";
				$this->state=1;
			break;
			case 'Faild':
				$this->msg=$array['message'];
				$this->state=0;
			break;
		}
		return true;
	}
	

	
}

?>