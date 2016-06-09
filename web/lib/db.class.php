<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: db.class.php 1059 2011-03-01 07:25:09Z monkey $
*/


class ucserver_db {
	var $querynum = 0;
	var $link;
	var $histories;

	var $dbhost;
	var $dbuser;
	var $dbpw;
	var $dbcharset;
	var $pconnect;
	var $tablepre;
	var $time;
	var $sql;
	var $select;
	var $from;
	var $where;
	var $limit;
	var $order;
	var $leftjoin;
	var $goneaway = 5;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset = '', $pconnect = 0, $tablepre='', $time = 0) {
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpw = $dbpw;
		$this->dbname = $dbname;
		$this->dbcharset = $dbcharset;
		$this->pconnect = $pconnect;
		$this->tablepre = $tablepre;
		$this->time = $time;

		if($pconnect) {
			if(!$this->link = mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				$this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link = mysql_connect($dbhost, $dbuser, $dbpw)) {
				$this->halt('Can not connect to MySQL server');
			}
		}

		if($this->version() > '4.1') {
			if($dbcharset) {
				mysql_query("SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary", $this->link);
			}

			if($this->version() > '5.0.1') {
				mysql_query("SET sql_mode=''", $this->link);
			}
		}

		if($dbname) {
			mysql_select_db($dbname, $this->link);
		}
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function result_first($sql) {
		$query = $this->query($sql);
		return $this->result($query, 0);
	}

	function fetch_first($sql) {
		$query = $this->query($sql);
		return $this->fetch_array($query);
	}

	function fetch_all($sql, $id = '') {
		$arr = array();
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) {
			$id ? $arr[$data[$id]] = $data : $arr[] = $data;
		}
                //file_put_contents('/home/offcn/wwwroot/phpwww/ucenterfile/files/lognew2.txt',"\n".json_encode($sql), FILE_APPEND);
		return $arr;
	}

	function cache_gc() {
		$this->query("DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time");
	}

	function query($sql, $type = '', $cachetime = FALSE) {
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link)) && $type != 'SILENT') {
			$this->halt('MySQL Query Error', $sql);
		}
		$this->querynum++;
		$this->histories[] = $sql;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}
       
	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
        
	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		return mysql_get_server_info($this->link);
	}
        public function __destruct() {
            $this->close();
        }
	function close() {
		return mysql_close($this->link);
	}
        function escape($value) {
		return mysql_real_escape_string($value, $this->link);
	}
	function createCommand($sql=''){           
		$this->sql=$sql;
		$this->ini();
		return $this;
	}
	function ini(){
		$this->select=$this->from=$this->leftjoin=$this->where=$this->order='';
		$this->limit=' limit 200';
	}
	function select($s){
		$this->select='select '.$s;
		return $this;
	}
	function from($f){
		$this->from=' from '.$f;
		  return $this;
	}
	function getLastInsertID(){
		return $this->insert_id();
	}
	function insert($t,$v){
		$str='';
		foreach ($v as $k=>$tv)
		{
			$str.=',`'.$k.'`=\''.$this->escape($tv).'\'';
		}
		$str=ltrim($str,',');
		$this->sql='insert into '.$t.' set '.$str;
		$this->query($this->sql);
	}
	 function update($table,$field,$w,$a=array()){
		$str='';
		foreach ($field as $k=>$tv)
		{
			$str.=',`'.$k.'`=\''.$this->escape($tv).'\'';
		}
		$str=ltrim($str,',');
		
		$where='';
			 if($a){
			$v= array_values($a);
			$a=  array_keys($a);         
		  
		foreach ($v as &$t){
			$t='\''.$this->escape($t).'\'';
		}
			$where=' where '.str_replace($a,$v,$w);
		} elseif($w)
			$where=' where '.$w;
		$this->sql='update '.$table.' set '.$str.$where;
		
		return $this->query($this->sql);
	}
	  function delete($table,$w,$a=array()){          
		$where='';
		if(!trim($w))
			return false;
			 if($a){
			$v= array_values($a);
			$a=  array_keys($a);         
		  
		foreach ($v as &$t){
			$t='\''.$this->escape($t).'\'';
		}
			$where=' where '.str_replace($a,$v,$w);
		} elseif($w)
			$where=' where '.$w;
		$this->sql='delete from '.$table.$where;
		return $this->query($this->sql);
	}
	/**
	 * 
	 * @param type $a 类似：array(':a',':b',':c')要替换的key
	 * @param type $v 类似: array(2,3,4);要替换的值
	 * @return \ucserver_db
	 */
	function bindValues($a){
		   $v= array_values($a);
			$a=  array_keys($a);
		 if($a&&$v){
		foreach ($v as &$t){
			$t='\''.$this->escape($t).'\'';
		}
			$this->sql=str_replace($a,$v,$this->sql);
			
		}
		return $this;
	}       
	/**
	 * where(array('in','field',array(1,2,3,4)))
	 * where('field=:i',array(':i'=>2))
	 * @param type $w
	 * @param type $a
	 * @return \ucserver_db
	 */
	function where($w,$a=array(),$isand=false){
		if(is_array($w)){
                    if($w[0]==='in'){                   
                        foreach ($w[2] as &$t){
                            $t='\''.$this->escape($t).'\'';
                        }
                        $tstr=  implode(',', $w[2]); 
                        if($tstr === ''||$tstr==='\'\'')
                                $this->halt('MySQL Query Error');
                        if($isand===true)
                        {
                           $this->where=$this->where? $this->where.' and '.$w[1].' in ('.$tstr.')' : ' where '.$w[1].' in ('.$tstr.')';
                        }
                        else
                            $this->where=' where '.$w[1].' in ('.$tstr.')';
                    }
		}
		elseif($a){
			$v= array_values($a);
			$a=  array_keys($a);  
			foreach ($v as &$t){
                            $t='\''.$this->escape($t).'\'';
			}
			if($isand===true)
                        {
                           $this->where=$this->where? $this->where.' and '.str_replace($a,$v,$w) :' where '.str_replace($a,$v,$w);  
                        }
                        else
                            $this->where=' where '.str_replace($a,$v,$w);
		} elseif($w)
		{
			if($isand===true)
                        {
                            $this->where=$this->where?' where '.$w:$this->where.' and '.$w; 
                        }
                        else
                            $this->where=' where '.$w;
		}
                return $this;
	}
	function leftJoin($add1,$add2){
		$this->leftjoin=' left join '.$add1.' on '.$add2;  
		return $this;
	}
	function limit($limit,$offset=0){
		$this->limit=' limit '.(int)$offset.','.(int)$limit;
		return $this;
	}
	function order($o){
		$this->order=' order by '.$o;
		  return $this;
	}
	function group($o){
		$this->order=' group by '.$o;
		  return $this;
	}
	function groupsql(){
		if(!$this->sql)
		{
			$this->sql=$this->select.$this->from.$this->leftjoin.$this->where.$this->order.$this->limit;
		}            
	}
	function queryAll($t=0){
		$this->groupsql();
		 if($t){
			 return $this->sql;
		 }else{
			return $this->fetch_all($this->sql);
		 }
	}
	function execute(){
		return $this->query($this->sql);
	}
	function queryRow($t=0){
		 $this->groupsql();
		 if($t){
			 return $this->sql;
		 }else{
			return $this->fetch_first($this->sql); 
		 }
	}

	function halt($message = '', $sql = '') {
		$error = mysql_error();
		$errorno = mysql_errno();
		if($errorno == 2006 && $this->goneaway-- > 0) {
			$this->connect($this->dbhost, $this->dbuser, $this->dbpw, $this->dbname, $this->dbcharset, $this->pconnect, $this->tablepre, $this->time);
			$this->query($sql);
		} elseif(DEBUG_ENABLE) {
			ob_start();
			debug_print_backtrace();
			$s= ob_get_clean();
			exit(str_replace("\n", '<br>', $s));
		}
	}
}

?>