<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1078 2011-03-30 02:00:29Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class homemodel {
        private $tablename_mf = 'manufacturer';
        private $tablename_course = 'course';
        private $tablename_choice = 'choice';
        private $tablename_course_field = 'course_field';
        private $tablename_setting = 'setting';
     
        /*
         * 首页广告列表
         */
        function getmarflist($field = '*',$adpid=0,$limit) {
            $rows = init_db()->createCommand()->select($field)
                    ->from($this->tablename_mf)
                    ->where('status=1 and adpid =:adpid',array(':adpid'=>$adpid))
                    ->order('seq asc')
                    ->limit($limit,0)
                    ->queryAll();
            return $rows;               
        }
		/*
         * 广告列表获得1
         */
        function getmarflist1($examtype,$field = '*',$column_id=0) {
            $rows = init_db()->createCommand()->select($field)
                    ->from($this->tablename_mf)
                    ->where('exam_type = :exam_type AND status=1 and column_id =:column_id',array(':exam_type'=>$examtype,':column_id'=>$column_id))
                    ->order('number asc')
                    ->queryAll();
            return $rows;               
        }
		/*
         * 首页广告列表2
         */
        function getmarflist2($field = '*') {
            $rows = init_db()->createCommand()->select($field)
                    ->from($this->tablename_mf)
                    ->where('status=1')
                    ->order('number asc')
                    ->queryAll();
            return $rows;               
        }
        function gettypelist($where, $limit = '', $field = 'co.id, co.typejob_pid, co.typejob_id, co.title, co.lessonnum, co.price, co.preferential'){
                if($where['typejob_pid']){
                        $sqlpre[] .= 'ch.typejob_pid=:typejob_pid';
                        $sqlnet[':typejob_pid'] = $where['typejob_pid'];
                }
                if($where['typejob_id']){
                        $sqlpre[] .= 'ch.typejob_id=:typejob_id';
                        $sqlnet[':typejob_id'] = $where['typejob_id'];
                }
                if($where['exam_type']){
                        $sqlpre[] .= 'co.exam_type=:exam_type';
                        $sqlnet[':exam_type'] = $where['exam_type'];
                }

                if($sqlpre){
                        $sql = implode(" and ", $sqlpre);
                }else{
                        $sql = "1=1";
                        $sqlnet = array();
                }
                if($limit){
                   $rows = init_db()->createCommand()->select($field)
                        ->from($this->tablename_course_field.' ch')
                        ->where('co.status=1 and '.$sql, $sqlnet)
                        ->leftJoin('course co', " ch.course_id=co.id")
                        ->order('ch.xkzx_order asc,ch.course_id desc')
                        ->queryAll();
                 }else{
                        $num = $this->get_choice_count();
                        $limit = $num['c'];
                        $rows = init_db()->createCommand()->select($field)
                        ->from($this->tablename_course_field.' ch')
                        ->where('co.status=1 and '.$sql, $sqlnet)
                        ->leftJoin('course co', " ch.course_id=co.id")
                        ->order('ch.xkzx_order asc,ch.course_id desc')
                        ->queryAll();
                 }

                 return $rows;
        }
		
		function get_choice_count(){
			$sqlpre=$sqlnet='';
			$sql ='1=1';
			$sqlnet = array();
			
			$row = init_db()->createCommand()->select('count(*) as c')
			 ->from($this->tablename_choice)
			 ->where($sql,$sqlnet)
			 ->limit(1)
			 ->queryRow();
			return $row;
		}
        
        function getsetting($type) {
            $rows = init_db()->createCommand()->select("*")
                    ->from($this->tablename_setting)
                    ->where('type =:type',array(':type'=>$type))
                    ->limit(1)
                    ->queryRow();
            return $rows;  
        }
		
}

?>