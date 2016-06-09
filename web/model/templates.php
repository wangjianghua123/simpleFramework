<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1078 2011-03-30 02:00:29Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class templatesmodel {
    
    private $tablename = 'kjy_interviewtemplate';
    private $tablename_re = 'kjy_refusetemplate';
    
    //添加面试通知模板
    function addinterview($data){
         init_db()->createCommand()->insert($this->tablename,$data);
        return init_db()->getLastInsertID();    
    }
    
    //修改面试通知模板
    function editinterview($data,$id){
        return init_db()->createCommand()->update($this->tablename,$data,'id=:id',array(':id'=>$id));
    }
    
    //设置面试通知默认模板
    function setdeaultinterview($uid,$setid){
        //获取当前默认模板
        init_db()->createCommand()->update($this->tablename,array('status'=>0),'uid=:uid',array(':uid'=>$uid));
        return init_db()->createCommand()->update($this->tablename,array('status'=>1),'id=:id',array(':id'=>$setid));
    }
    
    //获取面试通知模板列表
    function getlist($uid){
        $rows = init_db()->createCommand()->select('*')
                ->from($this->tablename)
                ->where('uid = :uid',array(':uid'=>$uid))
                ->order('status desc,id asc')
                ->limit(5,0)
                ->queryAll();
        return $rows;
    }
    
    //删除面试通知模板
    function delinterview($id){
        return init_db()->createCommand()->delete($this->tablename,'id=:id',array(':id'=>$id));
    }
    
    //添加不合适通知模板
    function addrefuse($data){
        init_db()->createCommand()->insert($this->tablename_re,$data);
        return init_db()->getLastInsertID();
    }
        
    //修改不合适通知模板
    function editrefuse($data,$id){
        return init_db()->createCommand()->update($this->tablename_re,$data,'id=:id',array(':id'=>$id));
    }
    
    //设置不合适通知默认模板
    function setdeaultrefuse($uid,$setid){
        //获取当前默认模板
        init_db()->createCommand()->update($this->tablename_re,array('status'=>0),'uid=:uid',array(':uid'=>$uid));
        return init_db()->createCommand()->update($this->tablename_re,array('status'=>1),'id=:id',array(':id'=>$setid));
    }
    
    //获取不合适通知模板列表
    function getrefuselist($uid){
        $rows = init_db()->createCommand()->select('*')
                ->from($this->tablename_re)
                ->where('uid = :uid',array(':uid'=>$uid))
                ->order('status desc,id asc')
                ->limit(5,0)
                ->queryAll();
        return $rows;
    }
    
    
    //获取不合适通知模板列表
    function getrefuseinfos($where){
        //拼接where条件
        $con = ''; $conarr = array();
        $con .= "1=1";
        //userid
        if(!empty($where['uid']) && $where['uid'] != 0){
            $con .= " and uid = :uid";
            $conarr[":uid"] = $where['uid'];
        }
        
        if(isset($where['status'])){
            $con .= " and status = :status";
            $conarr[":status"] = $where['status'];
        }
        
        $rows = init_db()->createCommand()->select('*')
                ->from($this->tablename_re)
                ->where($con,  $conarr)
                ->queryAll();
        return $rows;
    }
    
    
    
    //获取通知模板列表
    function getinterviewinfos($where){
        //拼接where条件
        $con = ''; $conarr = array();
        $con .= "1=1";
        //userid
        if(!empty($where['uid']) && $where['uid'] != 0){
            $con .= " and uid = :uid";
            $conarr[":uid"] = $where['uid'];
        }
        
        if(isset($where['status'])){
            $con .= " and status = :status";
            $conarr[":status"] = $where['status'];
        }
        
        $rows = init_db()->createCommand()->select('*')
                ->from($this->tablename)
                ->where($con,  $conarr)
                ->queryAll();
        return $rows;
    }
    
    //删除不合适通知模板
    function delrefuse($id){
        return init_db()->createCommand()->delete($this->tablename_re,'id=:id',array(':id'=>$id));
    }
}

?>