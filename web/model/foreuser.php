<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1078 2011-03-30 02:00:29Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class foreusermodel {
        // private $tablename = 'user';
        private $tablename = 'kjy_members';
        private $tablename_sp = 'kjy_setphone';
        private $tablename_fg = 'kjy_forget';
        private $tablename_ut = 'kjy_members_third';
        private $tablename_con = 'kjy_members_contacted';
        
        function insert($paper){
            
                init_db()->createCommand()->insert($this->tablename,$paper);
                return init_db()->getLastInsertID();            
        }
     
        
        function edit($paper,$uid){
                return init_db()->createCommand()->update($this->tablename,$paper,'uid=:uid',array(':uid'=>$uid));                
        }    
    
        //验证用户信息
        function getUserInfo($where,$field="count(*) as total"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if($where['email']){
                $con .= " and email =:email";
                $conarr[":email"] = $where['email'];
            }
            if($where['phone']){
                $con .= " and phone =:phone";
                $conarr[":phone"] = $where['phone'];
            }
            if($where['phone_validate']){
                $con .= " and phone_validate =:validate";
                $conarr[":validate"] = $where['phone_validate'];
            }
            if($where['uid']){
                $con .= " and uid =:uid";
                $conarr[":uid"] = $where['uid'];
            }
            if($where['password']){
                $con .= " and password =:password";
                $conarr[":password"] = $where['password'];
            }
            if($where['ucuid']){
                $con .= " and uc_uid =:uc_uid";
                $conarr[":uc_uid"] = $where['ucuid'];
            }
           /*  //除自己之外的用户
            if($where['uid']){
                $con .= " and uid !=:uid";
                $conarr[":uid"] = $where['uid'];
            }  */          
            $row = init_db()->createCommand()->select($field)
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->limit(1)
                    ->queryRow();
            return $row;
        }

        function getUserInfo1($where,$field="count(*) as total"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if($where['email']){
                $con .= " and email =:email";
                $conarr[":email"] = $where['email'];
            }
            if($where['phone']){
                $con .= " and phone =:phone";
                $conarr[":phone"] = $where['phone'];
            }
            if($where['uid']){
                $con .= " and uid =:uid";
                $conarr[":uid"] = $where['uid'];
            }
            if($where['ucuid']){
                $con .= " and uc_uid =:uc_uid";
                $conarr[":uc_uid"] = $where['ucuid'];
            }
            //除自己之外的用户
            if($where['uid']){
                $con .= " and uid !=:uid";
                $conarr[":uid"] = $where['uid'];
            }           
            $row = init_db()->createCommand()->select($field)
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->queryRow();
            return $row;
        }
		
        
        function getRow($uid,$fileds="*"){
            $row = init_db()->createCommand()->select($fileds)
                    ->from($this->tablename)
                    ->where('uid =:uid',array(':uid'=>$uid))
                    ->queryRow();
            return $row;
        }

        
        //别删除该方法 获取用户相关信息 in
        function getinfobyuid($uids,$field = '*'){
            if(!$uids){
                return false;
            }
            $rows = init_db()->createCommand()->select($field)
                    ->from($this->tablename)
                    ->where(array('in','uid',$uids))
                    ->queryAll();
            return $rows;            
        }
        
        /**
         * 根据单个用户id查询信息
         * @param type $uid
         * @param type $field
         * @return type
         */
        function getuserinfobyuid($uid,$field = "*"){
             $row = init_db()->createCommand()->select($field)
                    ->from($this->tablename)
                    ->where("uid=:uid ",array(':uid'=>$uid))         
                    ->queryRow();
            return $row;      
        }
       
        function getPhone($phone){
           $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename)
                    ->where("phone=:phone ",array(':phone'=>$phone))         
                    ->queryRow();
            return $row;                
        }
        
        function getEmail($email){
           $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename)
                    ->where("email=:email ",array(':email'=>$email))         
                    ->queryRow();
            return $row;                
        }
        
        //修改用户状态（普通OR企业用户）
        function editidentity($uid,$type){
            return init_db()->createCommand()->update($this->tablename,array('identity'=>$type),'uid=:uid',array(':uid'=>$uid));
        }
       
        function usercount($where=array()){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if($where['email']){
                $con .= " and email =:email";
                $conarr[":email"] = $where['email'];
            }
            if($where['phone']){
                $con .= " and phone =:phone";
                $conarr[":phone"] = $where['phone'];
            }
            if($where['uid']){
                $con .= " and uid =:uid";
                $conarr[":uid"] = $where['uid'];
            }   
			if($where['status']){
                $con .= " and status =:status";
                $conarr[":status"] = $where['status'];
            } 
            $row = init_db()->createCommand()->select("count(*) as total")
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->queryRow();

            return $row['total'];			
		}

        function userlists($where=array(),$files="*",$limit=10,$offset=0){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if($where['email']){
                $con .= " and email =:email";
                $conarr[":email"] = $where['email'];
            }
            if($where['phone']){
                $con .= " and phone =:phone";
                $conarr[":phone"] = $where['phone'];
            }
            if($where['uid']){
                $con .= " and uid =:uid";
                $conarr[":uid"] = $where['uid'];
            }    
            if($where['status']){
                $con .= " and status =:status";
                $conarr[":status"] = $where['status'];
            } 
            $row = init_db()->createCommand()->select($files)
                    ->from($this->tablename)
                    ->where($con,  $conarr)
		    ->limit($limit,$offset)
                    ->order('uid desc')
                    ->queryAll();
            return $row;			
	}
		
        function getUserAll($where=array(),$files="*",$limit=10,$offset=0){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if($where['email']){
                $con .= " and email =:email";
                $conarr[":email"] = $where['email'];
            }
            if($where['phone']){
                $con .= " and phone =:phone";
                $conarr[":phone"] = $where['phone'];
            }
            if($where['uid']){
                $con .= " and uid =:uid";
                $conarr[":uid"] = $where['uid'];
            }    
	    if($where['status']){
                $con .= " and status =:status";
                $conarr[":status"] = $where['status'];
            } 
            $row = init_db()->createCommand()->select($files)
                    ->from($this->tablename)
                    ->where($con,  $conarr)
         	    ->limit($limit,$offset)
                    ->order('uid desc')
                    ->queryAll();
            return $row;			
        }
              
        function setinsert($paper){
                init_db()->createCommand()->insert($this->tablename_sp,$paper);
                return init_db()->getLastInsertID();            
        }
        function setRow($phone){
            $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_sp)
                    ->where('phone =:phone and times >:times',array(':phone'=>$phone,':times'=>strtotime(date("Y-m-d",time()))))
                    ->order('times desc')
                    ->queryRow();
            return $row;
        }        
       
        function settotal($phone){
            $row = init_db()->createCommand()->select('count(*) as total')
                    ->from($this->tablename_sp)
                    ->where('phone =:phone and times >:times',array(':phone'=>$phone,':times'=>strtotime(date("Y-m-d",time()))))
                    ->queryRow();
            return $row['total'];
        }
        
        function addvaluekey($param) {
            init_db()->createCommand()->insert($this->tablename_fg,$param);
        }
        function getValueEmail($email){
           $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_fg)
                    ->where("email=:email ",array(':email'=>$email))         
                    ->queryRow();
            return $row;                
        }    

        function getValue($paper){
           $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_fg)
                    ->where("email=:email and valuekey=:valuekey",array(':email'=>$paper['email'],':valuekey'=>$paper['value_key']))         
                    ->queryRow();
            return $row;                
        }
           
        function delValue($prim){              
                  return init_db()->createCommand()->delete($this->tablename_fg,'id=:id',array(':id'=>$prim));
        }
        
        function getCustmerthird($type,$openid) {
            $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_ut)
                    ->where('`type` =:type and `openid` =:openid',array(':type'=>$type,':openid'=>$openid))
                    ->limit(1)
                    ->queryRow();
            return $row;
        }
        
        function getCustmerthird3($key) {
            $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_ut)
                    ->where('`key` =:key',array(':key'=>$key))
                    ->limit(1)
                    ->queryRow();
            return $row;
        }        
        
        function getuserthird($uid) {
            $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_ut)
                    ->where('`uid` =:uid',array(':uid'=>$uid))
                    ->limit(10)
                    ->queryAll();
            return $row;
        }    
        
        function getusertied($uid,$type) {
            $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_ut)
                    ->where('`uid` =:uid  and `type` = :type',array(':uid'=>$uid,":type"=>$type))
                    ->limit(1)
                    ->queryRow();
            return $row;
        }        
        function deltiedValue($prim){              
            return init_db()->createCommand()->delete($this->tablename_ut,'id=:id',array(':id'=>$prim));
        }  
        
        function insertthird($paper){
            init_db()->createCommand()->insert($this->tablename_ut,$paper);
            return init_db()->getLastInsertID();            
        }
        
        function editut($paper,$id){
            return init_db()->createCommand()->update($this->tablename_ut,$paper,'id=:id',array(':id'=>$id));            
        }
        
        
        /*
         * 简历搜索使用 通过条件获取uid
         */
        function getUserSearch($where=array(),$files="*"){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if(isset($where['education'])){
                $con .= " and education =:education";
                $conarr[":education"] = $where['education'];
            }
            if(isset($where['workexp'])){
                $con .= " and workexp =:workexp";
                $conarr[":workexp"] = $where['workexp'];
            }
            $row = init_db()->createCommand()->select($files)
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->queryAll();
            return $row;			
        }
        
        /**
         * 根据公司获取已查看用户联系方式总数
         * @param type $cid
         * @param type $field
         * @return type
         */
        function getcontactinfo($cid,$field="*"){
            $row = init_db()->createCommand()->select($field)
                    ->from($this->tablename_con)
                    ->where('`companyid` =:cid',array(':cid'=>$cid))
                    ->queryAll();
            return $row;
        }
        
        /**
         * 记录HR查看联系方式
         * @param type $cid
         * @param type $field
         * @return type
         */
        function addcontactinfo($data){
            init_db()->createCommand()->insert($this->tablename_con,$data);
            return init_db()->getLastInsertID();    
        }

}

?>