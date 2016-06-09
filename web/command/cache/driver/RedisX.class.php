<?php
!defined('IN_UC') && exit('Access Denied');
/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class RedisX extends Cache {
   /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            die('没有安装redis驱动');
        }
        $options = array_merge(array (
            'host'          => '127.0.0.1',
            'port'          => 6379,
            'timeout'       => false,
            'persistent'    => false,
        ),$options);

        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   2592000;
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   '';        
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;        
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        $name = $this->number == 10 ? $name : $this->options['prefix'].$name;
        $value = $this->handler->get($name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =  $this->number == 10 ? $name : $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire) && $expire) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }
    
    /*
     * 连接哪个库
     * @access public
     * @param int $num 缓存库id 默认0
     * @return boolean
     */
    public function select($num) {
        $this->number = $num;
        return $this->handler->select($num);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
        $name   =  $this->number == 10 ? $name : $this->options['prefix'].$name;
        return $this->handler->delete($name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flushDB();
    }



     /**
     +----------------------------------------------------------
     * 写入缓存（数据结构：hash）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param integer $index 索引
     * @param string $value  存储数据
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    public function hset($name, $index, $value)
    {
        N('cache_write',1);
        return $this->handler->hset($name, $index, $value);
    }


    /**
     +----------------------------------------------------------
     * 写入缓存（数据结构：hash hsetnx）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param integer $index 索引
     * @param string $value  存储数据
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    public function hsetnx($name, $index, $value)
    {
        N('cache_write',1);
        return $this->handler->hsetnx($name, $index, $value);
    }


    /**
     +----------------------------------------------------------
     * HINCRBY hash自增方法（自增1） 
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param integer $index 索引
     * @param string $value  存储数据
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
     public function hincrby($name, $index, $value) {
         return $this->handler->hincrby($name, $index, $value);
     }



    /**
     +----------------------------------------------------------
     * 批量写入缓存（数据结构：hash）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param string $value  存储数据
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    public function hmset($name, $value)
    {
        N('cache_write',1);
        return $this->handler->hmset($name, $value);
    }
    
    /**
     +----------------------------------------------------------
     * 批量读取hash数据（数据结构：hash）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * +----------------------------------------------------------
     * @param string $keys 缓存变量值数组 array('field1', 'field2')
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function hmget($name,$keys = array())
    {
        N('cache_read',1);
        return $this->handler->hmget($name,$keys);
    }
    
    /**
     +----------------------------------------------------------
     * hash表长度
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function hlen($name)
    {
        N('cache_read',1);
        return $this->handler->hlen($name);
    }
    
    /**
     +----------------------------------------------------------
     * 判断hash表中该索引是否存在
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param integer $index 索引
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    public function hexists($name, $index)
    {
        N('cache_read',1);
        return $this->handler->hexists($name, $index);
    }
    /**
     +----------------------------------------------------------
     * 判断hash表中该索引是否存在
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param integer $index 索引
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
     public function exists($name, $index)
    {
        N('cache_read',1);
        return $this->handler->exists($name);
    }
    /**
     +----------------------------------------------------------
     * 读取缓存（数据结构：hash）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param integer $index 索引
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function hget($name, $index)
    {
        N('cache_read',1);
        return $this->handler->hget($name, $index);
    }
    
    /**
     +----------------------------------------------------------
     * 读取hash所有数据（数据结构：hash）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function hgetall($name)
    {
        N('cache_read',1);
        return $this->handler->hgetall($name);
    }
    
    
    
    /**
     +----------------------------------------------------------
     * 读取缓存（数据结构：列表）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param bool $right  默认右侧弹出，反之左侧
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function pop($name, $right = true)
    {
        N('cache_read',1);
        $fun = $right ? 'rPop' : 'lPop';
        return $this->handler->$fun($name);
    }
    
    /**
     +----------------------------------------------------------
     * 写入缓存（数据结构：列表）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param string $value 存储数据
     * @param bool $right  默认右侧押入，反之左侧
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function push($name, $value, $right = true)
    {
        N('cache_write',1);
        $fun = $right ? 'rPush' : 'lPush';
        return $this->handler->$fun($name, $value);
    }
    
  
    
    /**
     +----------------------------------------------------------
     * 写入缓存（数据结构：有序集合）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param string $value  存储数据
     * @param integer $index 索引
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    public function zadd($name, $value, $index)
    {
        N('cache_write',1);
        return $this->handler->zadd($name, $value, $index);
    }
    
    public function zrevrange($name, $off, $end)
    {
        N('cache_read',1);
        return $this->handler->zrevrange($name, $off, $end);
    }

    /**
     +----------------------------------------------------------
     * 获取该索引对应的值在整个集合中的排名（数据结构：有序集合）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param string $index 索引
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function zrevrank($name, $index)
    {
        N('cache_read',1);
        return $this->handler->zrevrank($name, $index);
    }
    /**
     +----------------------------------------------------------
     * 获取该索引对应的值在整个集合中的排名（数据结构：有序集合）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param string $index 索引
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function zrank($name, $index)
    {
        N('cache_read',1);
        return $this->handler->zrank($name, $index);
    } 
    /**
     +----------------------------------------------------------
     * 获取该索引对应的值（数据结构：有序集合）
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param string $index 索引
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function zscore($name, $index)
    {
        N('cache_read',1);
        return $this->handler->zscore($name, $index);
    }
    
    /**
     +----------------------------------------------------------
     * 删除缓存(数据结构：有序集合)
     *
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param string $index 索引
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function zrem($name, $index)
    {
        return $this->handler->zrem($name, $index);
    }

    /**
     +----------------------------------------------------------
     * 增加事务
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function multi(){
        return $this->handler->multi();
    }

    /**
     +----------------------------------------------------------
     * 增加事务
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function exec(){
        return $this->handler->exec();
    }
   /**
     +----------------------------------------------------------
     * zrangebyscore
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
     public function zrangebyscore($name, $value, $start)
    {
        N('cache_read',1);
        return $this->handler->zrangebyscore($name, $value, $start);
    }
    /**
     +----------------------------------------------------------
     * INCR 自增方法（自增1） 
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return int
     +----------------------------------------------------------
     */
     public function increase($key) {
         return $this->handler->incr($key);
     }

}
