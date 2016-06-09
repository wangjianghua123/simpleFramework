<?php
	define('SESSIONTIME',86400);

	$lifeTime = 24 * 3600;
	session_set_cookie_params($lifeTime);
	set_magic_quotes_runtime(0);
	$mtime = explode(' ', microtime());
	$starttime = $mtime[1] + $mtime[0];
	$handler = new RedisSessionHandler(); //因ridis配置问题   暂时注释，
	session_set_save_handler(
		array($handler, 'open'), //在运行session_start()时执行
		array($handler, 'close'),//在脚本执行完成或调用session_write_close() 或 session_destroy()时被执行,即在所有session操作完后被执行
		array($handler, 'read'),//在运行session_start()时执行,因为在session_start时,会去read当前session数据
		array($handler, 'write'),//此方法在脚本结束和使用session_write_close()强制提交SESSION数据时执行
		array($handler, 'destroy'),//在运行session_destroy()时执行
		array($handler, 'gc')  //执行概率由session.gc_probability 和 session.gc_divisor的值决定,时机是在open,read之后,session_start会相继执行open,read和gc
	);
	session_start(); //这也是必须的，打开session，必须在session_set_save_handler后面执行
	register_shutdown_function('session_write_close');


	

	//redis操作封装
	class RedisSessionHandler
	{
		function open($savePath, $sessionName)
		{
			return true;
		}

		function close()
		{
			return true;
		}
		function read($id)
		{
			 $r = init_cache();
			 $r->select(9);
			 return $r->get('sess_'.$id);
		}
		function write($id,$data)
		{
			 $r = init_cache();
			 $r->select(9);
			 return $r->setex('sess_'.$id,SESSIONTIME,$data);
		}

		function destroy($id)
		{
		 $r = init_cache();
			 $r->select(9);
			 return $r->del('sess_'.$id);
		}

		function gc($maxlifetime)
		{
			return true;
		}
	}

	function init_cache() {
	    $cache;
		if(!$cache){
			$cache = getRedis();
		}
		return $cache;
	}

	// 获取Redis连接
	function getRedis(){	
		while(!$bool){
			try{ 
				$redis = new \Redis();
				$redis->pconnect('127.0.0.1','6379');  //php客户端设置的ip及端口
				$bool=true;
			} catch(Exception $e) {
				sleep(30); // 连接失败 休眠10秒
			}
		}
		Return $redis;
	}
?>