<?php
	define('SESSIONTIME',86400);

	$lifeTime = 24 * 3600;
	session_set_cookie_params($lifeTime);
	set_magic_quotes_runtime(0);
	$mtime = explode(' ', microtime());
	$starttime = $mtime[1] + $mtime[0];
	$handler = new RedisSessionHandler(); //��ridis��������   ��ʱע�ͣ�
	session_set_save_handler(
		array($handler, 'open'), //������session_start()ʱִ��
		array($handler, 'close'),//�ڽű�ִ����ɻ����session_write_close() �� session_destroy()ʱ��ִ��,��������session�������ִ��
		array($handler, 'read'),//������session_start()ʱִ��,��Ϊ��session_startʱ,��ȥread��ǰsession����
		array($handler, 'write'),//�˷����ڽű�������ʹ��session_write_close()ǿ���ύSESSION����ʱִ��
		array($handler, 'destroy'),//������session_destroy()ʱִ��
		array($handler, 'gc')  //ִ�и�����session.gc_probability �� session.gc_divisor��ֵ����,ʱ������open,read֮��,session_start�����ִ��open,read��gc
	);
	session_start(); //��Ҳ�Ǳ���ģ���session��������session_set_save_handler����ִ��
	register_shutdown_function('session_write_close');


	

	//redis������װ
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

	// ��ȡRedis����
	function getRedis(){	
		while(!$bool){
			try{ 
				$redis = new \Redis();
				$redis->pconnect('127.0.0.1','6379');  //php�ͻ������õ�ip���˿�
				$bool=true;
			} catch(Exception $e) {
				sleep(30); // ����ʧ�� ����10��
			}
		}
		Return $redis;
	}
?>