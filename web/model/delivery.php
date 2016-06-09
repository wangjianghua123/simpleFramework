<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1078 2011-03-30 02:00:29Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');
/*
 * 简历投递管理
 */
class deliverymodel {
        private $tablename_jobs_deli = 'kjy_jobs_delivery';//投递基础表
        private $tablename_msg_queue = 'kjy_msg_queue';//投递消息队列表
        private $tablename_jobs_deli_log = 'kjy_job_delivery_logs'; //投递日志表
        private $tablename_jobs_folders = 'kjy_jobs_folders';//加入文件夹表
        private $tablename_jobs_deli_send = 'kjy_jobs_delivery_sendemail';//简历投递邮件发送表
        
        /*
         * 简历管理个数
         */
        function getdeliveryspagetotal($where){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            //userid
            if(!empty($where['hr_uid']) && $where['hr_uid'] != 0){
                $con .= " and hr_uid = :hr_uid";
                $conarr[":hr_uid"] = $where['hr_uid'];
            }
            //操作状态
            if(!empty($where['opreate_status'])){
                $con .= " and opreate_status = :opreate_status";
                $conarr[":opreate_status"] = $where['opreate_status'];
            }
            
            //不合适删除 0正常 1删除
            if(isset($where['folders_status'])){
                $con .= " and folders_status = :folders_status";
                $conarr[":folders_status"] = $where['folders_status'];
            }
            
            //职位名称
            if(!empty($where['typejob_name'])){
                $con .= " and typejob_name like :typejob_name";
                $conarr[":typejob_name"] = "%".$where['typejob_name']."%";
            }
            
            //已存文件夹状态
            if(isset($where['status'])){
                $con .= " and status = :status";
                $conarr[":status"] = $where['status'];
            }
            if(!empty($where['u_id'])){
                $row = init_db()->createCommand()->select('count(*) as total')
                        ->from($this->tablename_jobs_deli)
                        ->where(array("in","u_id",$where['u_id']))
                        ->where($con,  $conarr,true)
                        ->limit(1)
                        ->queryRow();
            }else{
                $row = init_db()->createCommand()->select('count(*) as total')
                        ->from($this->tablename_jobs_deli)
                        ->where($con,  $conarr)
                        ->limit(1)
                        ->queryRow();
            }
            return $row['total'];
        }
        
        /*
         * 简历管理列表分页
         */
        function getdeliveryspagelist($limit=10,$offset=0,$where=array(),$order="delivery_time desc"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            
            //userid
            if(!empty($where['hr_uid']) && $where['hr_uid'] != 0){
                $con .= " and hr_uid = :hr_uid";
                $conarr[":hr_uid"] = $where['hr_uid'];
            }
            
            //操作状态
            if(!empty($where['opreate_status'])){
                $con .= " and opreate_status = :opreate_status";
                $conarr[":opreate_status"] = $where['opreate_status'];
            }
            
            //不合适删除 0正常 1删除
            if(isset($where['folders_status'])){
                $con .= " and folders_status = :folders_status";
                $conarr[":folders_status"] = $where['folders_status'];
            }
            
            //职位名称
            if(!empty($where['typejob_name'])){
                $con .= " and typejob_name like :typejob_name";
                $conarr[":typejob_name"] = "%".$where['typejob_name']."%";
            }
            
            //已存文件夹状态
            if(isset($where['status'])){
                $con .= " and status = :status";
                $conarr[":status"] = $where['status'];
            }
            
            if(!empty($where['u_id'])){
                $row = init_db()->createCommand()->select('*')
                        ->from($this->tablename_jobs_deli)
                        ->where(array("in","u_id",$where['u_id']))
                        ->where($con,  $conarr,true)
                        ->limit($limit,$offset)
                        ->order($order)
                        ->queryAll();
            }else{
                $row = init_db()->createCommand()->select('*')
                        ->from($this->tablename_jobs_deli)
                        ->where($con,  $conarr)
                        ->limit($limit,$offset)
                        ->order($order)
                        ->queryAll();
            }
            return $row;
        }
        
        /*
         * 文件夹管理个数
         */
        function getfolderspagetotal($where){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            //userid
            if(!empty($where['hr_uid']) && $where['hr_uid'] != 0){
                $con .= " and hr_uid = :hr_uid";
                $conarr[":hr_uid"] = $where['hr_uid'];
            }
            
            $row = init_db()->createCommand()->select('count(*) as total')
                    ->from($this->tablename_jobs_folders)
                    ->where($con,  $conarr)
                    ->limit(1)
                    ->queryRow();
            return $row['total'];
        }
        
        /*
         * 文件夹管理列表分页
         */
        function getfolderspagelist($limit=10,$offset=0,$where=array(),$order="add_time desc"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            
            //userid
            if(!empty($where['hr_uid']) && $where['hr_uid'] != 0){
                $con .= " and hr_uid = :hr_uid";
                $conarr[":hr_uid"] = $where['hr_uid'];
            }
            
            $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_jobs_folders)
                    ->where($con,  $conarr)
                    ->limit($limit,$offset)
                    ->order($order)
                    ->queryAll();
            return $row;
        }
        
        
        /*
         * 批量获取投递信息
         */
        function getdeliverys($where=array(),$field="*"){
            //拼接where条件
            $con = ''; $conarr = array();
            
            //userid
            if(!empty($where['hr_uid']) && $where['hr_uid'] != 0){
                $con .= " hr_uid = :hr_uid";
                $conarr[":hr_uid"] = $where['hr_uid'];
            }
            
            $row = init_db()->createCommand()->select($field)
                    ->from($this->tablename_jobs_deli)
                    ->where(array("in","id",$where['id']))
                    ->where($con,  $conarr,true)
                    ->queryAll();
            return $row;
        }
        
        
        /*
         * 单个获取投递信息
         */
        function getdelivery($where=array(),$field="*"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            
            //userid
            if(!empty($where['hr_uid']) && $where['hr_uid'] != 0){
                $con .= " and hr_uid = :hr_uid";
                $conarr[":hr_uid"] = $where['hr_uid'];
            }
            
            
            //id
            if(!empty($where['id']) && $where['id'] != 0){
                $con .= " and  id = :id";
                $conarr[":id"] = $where['id'];
            }
            
            $row = init_db()->createCommand()->select($field)
                    ->from($this->tablename_jobs_deli)
                    ->where($con,  $conarr)
                    ->limit(1)
                    ->queryRow();
            return $row;
        }
        
        
        /*
         * 单个获取投递信息
         */
        function getfolder($where=array(),$field="*"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            
            //userid
            if(!empty($where['hr_uid']) && $where['hr_uid'] != 0){
                $con .= " and hr_uid = :hr_uid";
                $conarr[":hr_uid"] = $where['hr_uid'];
            }
            
            //old_rid
            if(!empty($where['old_rid']) && $where['old_rid'] != 0){
                $con .= " and  old_rid = :old_rid";
                $conarr[":old_rid"] = $where['old_rid'];
            }
            
            
            //id
            if(!empty($where['id']) && $where['id'] != 0){
                $con .= " and  id = :id";
                $conarr[":id"] = $where['id'];
            }
            
            $row = init_db()->createCommand()->select($field)
                    ->from($this->tablename_jobs_folders)
                    ->where($con,  $conarr)
                    ->limit(1)
                    ->queryRow();
            return $row;
        }
        
        /*
         * 获取投递状态的数字
         */
        function getdeliverycount($where=array(),$fields="*"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            
            //userid
            if(!empty($where['hr_uid']) && $where['hr_uid'] != 0){
                $con .= " and hr_uid = :hr_uid";
                $conarr[":hr_uid"] = $where['hr_uid'];
            }
            
            $row = init_db()->createCommand()->select($fields)
                    ->from($this->tablename_jobs_deli)
                    ->where($con,  $conarr)
                    ->queryAll();
            return $row;
        }
        
        
            
        /*
         * 投递信息
         */
        function insert($paper){
                init_db()->createCommand()->insert($this->tablename_jobs_deli,$paper);
                return init_db()->getLastInsertID();            
        }
        
        /*
         * 加入文件夹
         */
        function insert_folders($paper){
                init_db()->createCommand()->insert($this->tablename_jobs_folders,$paper);
                return init_db()->getLastInsertID();            
        }
        
        /*
         * 投递相关行为信息 简历操作行为状态 0 刚收到简历 1简历预览查看简历 2 不合适 3合适待沟通 4 发送面试通知
         */
        function insertlogs($paper){
                init_db()->createCommand()->insert($this->tablename_jobs_deli_log,$paper);
                return init_db()->getLastInsertID();            
        }
        
        
        
        /*
         * 获取投递行为的相关信息 简历操作行为状态 0 刚收到简历 1简历预览查看简历 2 不合适 3合适待沟通 4 发送面试通知
         */
        function getdeliverylogs($where=array(),$fields="*",$limit=1){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            
            //userid
            if(!empty($where['jd_id']) && $where['jd_id'] != 0){
                $con .= " and jd_id = :jd_id";
                $conarr[":jd_id"] = $where['jd_id'];
            }
            
            //行为状态 简历操作行为状态 0 刚收到简历 1简历预览查看简历 2 不合适 3合适待沟通 4 发送面试通知
            if(isset($where['status'])){
                $con .= " and status = :status";
                $conarr[":status"] = $where['status'];
            }
            
            $row = init_db()->createCommand()->select($fields)
                    ->from($this->tablename_jobs_deli_log)
                    ->where($con,  $conarr)
                    ->limit($limit)
                    ->queryAll();
            return $row;
        }
        
        /**
         * 获取一条投递行为的相关信息
         * @param type $where
         * @param type $fields
         * @param type $limit
         * @return type
         */
        function getdeliverylogbyjdid($where=array(),$fields="*",$limit=1){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            
            //userid
            if(!empty($where['jd_id']) && $where['jd_id'] != 0){
                $con .= " and jd_id = :jd_id";
                $conarr[":jd_id"] = $where['jd_id'];
            }
            
            //行为状态 简历操作行为状态 0 刚收到简历 1简历预览查看简历 2 不合适 3合适待沟通 4 发送面试通知
            if(isset($where['status'])){
                $con .= " and status = :status";
                $conarr[":status"] = $where['status'];
            }
            
            $row = init_db()->createCommand()->select($fields)
                    ->from($this->tablename_jobs_deli_log)
                    ->where($con,  $conarr)
                    ->limit($limit)
                    ->queryRow();
            return $row;
        }
        
        
        /**
        * 根据简历id获取简历社会经验信息
        * @param type $data
        * @param type $field
        * @return type
        */
       function getdeliverylogsinfo($where = array(), $field = "*") {
           //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            
            //行为状态 简历操作行为状态 0 刚收到简历 1简历预览查看简历 2 不合适 3合适待沟通 4 发送面试通知
            
            $con .= " and status = :status";
            $conarr[":status"] = 4;
            $row = init_db()->createCommand()->select('*')
                   ->from($this->tablename_jobs_deli_log)
                   ->where(array("in", "jd_id", $where))
                   ->where($con,$conarr,true)
                   ->queryAll();
           return $row;
       }
    
        
        /*
         * 添加消息队列
         */
        function insertqueue($paper){
            init_db()->createCommand()->insert($this->tablename_msg_queue,$paper);
            return init_db()->getLastInsertID();            
        }
        
        
       /**
        * 修改投递信息
        * @return type
        */
       function edit_delivery($paper,$id){
           return init_db()->createCommand()->update($this->tablename_jobs_deli,$paper,'id=:id',array(':id'=>$id));            
       }
       
       
       /**
        * 修改投递信息
        * @return type
        */
       function edit_deliveryinfo($paper,$id){
           return init_db()->createCommand()->update($this->tablename_jobs_deli,$paper,'id=:id',array(':id'=>$id));            
       }
       
       function edit_deliveryhrinfo($paper,$where){
           return init_db()->createCommand()->update($this->tablename_jobs_deli,$paper,'company_id=:cid AND hr_uid=:hruid',array(':cid'=>$where["cid"],":hruid"=>$where["hruid"]));
       }
      
       
       /**
        * 删除投递信息
        * @return type
        */
       function delete_deliverys($paper,$id){
           //return init_db()->createCommand()->delete($this->tablename_jobs_deli,'id=:id',array(':id'=>$id));
		   return init_db()->createCommand()->update($this->tablename_jobs_deli,$paper,'id=:id',array(':id'=>$id)); 
       }
       
       
       /**
        * 删除文件夹信息
        * @return type
        */
       function delete_folders($id){
           return init_db()->createCommand()->delete($this->tablename_jobs_folders,'id=:id',array(':id'=>$id));
       }
       
       /**
        * 根据编号删除投递信息
        * @param type $did
        * @return type
        */
       function deletedeliverybydid($did){
            return init_db()->createCommand()->delete($this->tablename_jobs_deli,'id=:did',array(":did"=>$did));
       }
       
       /**
        * 根据投递编号删除投递日志信息
        */
       function deletedeliverylogbytdid($jdid){
            return init_db()->createCommand()->delete($this->tablename_jobs_deli_log,'jd_id=:jdid',array(':jdid'=>$jdid));
       }

      /**
        * 根据投递编号修改投递日志信息
        */
       function editdeliverylogbytdid($paper,$jdid){
           return init_db()->createCommand()->update($this->tablename_jobs_deli_log,$paper,'jd_id=:jdid',array(':jdid'=>$jdid));
       }
       
       /**
        * 根据投递编号删除消息队列信息
        */
       function deletemsgquebydid($did){
           return init_db()->createCommand()->delete($this->tablename_msg_queue,'jd_id=:did',array(":did"=>$did));
       }


	   /**
        * 根据简历编号修改消息队列信息
        */
       function editmsgquerid($paper,$jdid){
           return init_db()->createCommand()->update($this->tablename_msg_queue,$paper,'jd_id=:jdid',array(':jdid'=>$jdid));
       }
       
       /**
         * 解绑用户与公司关系时，解绑公司与投递简历关联关系
         */
        function removejobdeliveryforcompany($uid){
            return init_db()->createCommand()->update($this->tablename_jobs_deli,array('status' => 1),'hr_uid = :hr_uid',array(':hr_uid'=>$uid));
        }
        
        /**
         * 根据简历编号获取简历投递信息
         * @param type $rid
         * @param type $fields
         * @return type
         */
        function getdeliveryinfobyrid($rid,$fields="*"){
            $row = init_db()->createCommand()->select($fields)
                    ->from($this->tablename_jobs_deli)
                    ->where("old_rid=:rid ",array(':rid'=>$rid)) 
                    ->queryAll();
            return $row;  
        }
        
        /**
         * 根据简历投递编号获取简历投递信息
         * @param type $rid
         * @param type $fields
         * @return type
         */
        function getdeliveryinfobydid($did,$fields="*"){
            $row = init_db()->createCommand()->select($fields)
                    ->from($this->tablename_jobs_deli)
                    ->where("id=:did ",array(':did'=>$did)) 
                    ->limit(1)
                    ->queryRow();
            return $row;  
        }
        
        /**
         * 添加简历投递后发送邮件至HR记录
         * @param type $paper
         * @return type
         */
        function insertresumesendemail($paper) {
            init_db()->createCommand()->insert($this->tablename_jobs_deli_send, $paper);
            return init_db()->getLastInsertID();
        }
        
        /**
         * 根据公司id获取已投递简历
         * @param type $cid
         * @param type $fields
         * @return type
         */
        function getresumesendemailbycid($where,$fields="*"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if(!empty($where['cid'])){
                $con .= " and company_id = :c_id";
                $conarr[":c_id"] = $where['cid'];
            }
            if(!empty($where['status'])){
                $con .= " and status = :status";
                $conarr[":status"] = $where['status'];
            }
            $row = init_db()->createCommand()->select($fields)
                    ->from($this->tablename_jobs_deli_send)
                    ->where($con,  $conarr)
                    ->queryAll();
            return $row;  
        }
        
        /**
         * 根据公司id修改已投递简历
         * @param type $data
         * @param type $cid
         * @return type
         */
        function updatesumesendemailbycid($data,$cid){
            return init_db()->createCommand()->update($this->tablename_jobs_deli_send,$data,'company_id=:cid',array(':cid'=>$cid));
        }
}

?>