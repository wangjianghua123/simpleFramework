<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1078 2011-03-30 02:00:29Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class jobsmodel {
        private $tablename = 'kjy_jobs';
        private $tablename_counts = 'kjy_jobs_counts';
        private $tablename_jobs_intro = 'kjy_jobs_intro';
        private $tablename_jobs_deli = 'kjy_jobs_delivery';
        private $tablename_jobs_collect = 'kjy_collections';
        private $tablename_com = 'kjy_company';
        private $tablename_img = 'kjy_images';
        private $tablename_jobs_sub = 'kjy_jobs_subscribe';
        private $tablename_typejob = 'kjy_typejob';
        private $tablename_solr = 'kjy_solr_jobs';
        private $tablename_jobs_deli_sta = 'kjy_jobs_delivery_statistics';
        
        /**
        * 根据jobid获取职位信息
        * @param type $data
        * @param type $field
        * @return type
        */
        function gettypejobname($data,$field){
           $row = init_db()->createCommand()->select($field)
                   ->from($this->tablename)
                   ->where(array("in","typejob_id",$data))
                   ->queryAll();
           return $row;
        }
        
        
        /**
        * 根据jobid获取职位信息
        * @param type $data
        * @param type $field
        * @return type
        */
        function getjobinfo($data,$field){
           $row = init_db()->createCommand()->select($field)
                   ->from($this->tablename)
                   ->where(array("in","job_id",$data))
                   ->queryAll();
           return $row;
        }
        
        
        /**
        * 根据jobid获取职位信息
        * @param type $data
        * @param type $field
        * @return type
        */
        function getjobs($job_id,$field){
           $row = init_db()->createCommand()->select($field)
                   ->from($this->tablename)
                   ->where('job_id = :job_id', array(':job_id' => $job_id))
                   ->limit(1)
                   ->queryRow();
           return $row;
        }
        
        
       /**
        * 获取职位收藏总数
        * @param type $uid
        * @return type
        */
       function getcollectiontotal($where){
           //拼接where条件
           $con = '';$conarr = array();
           $con .= "1=1";
           if(!empty($where['uid']) && $where['uid'] != 0){
               $con .= " AND u_id=:uid";
               $conarr[":uid"] = $where['uid'];
           }
           if(isset($where['status'])){
               $con .= " AND c_status=:status";
               $conarr[":status"] = $where['status'];//正常状态
           }
           $command = init_db()->createCommand()
                  ->select("count(*) as total")
                  ->from($this->tablename_jobs_collect)
                  ->where($con,$conarr)
                  ->limit(1)
                  ->queryRow();
           return $command['total']; 
       }

       /**
        * 获取职位收藏列表
        * @param type $uid
        * @return type
        */
       function getcollectionlist($limit=12,$offset=0,$where){
           //拼接where条件
           $con = '';$conarr = array();
           $con .= "1=1";
           if(!empty($where['uid']) && $where['uid'] != 0){
               $con .= " AND u_id=:uid";
               $conarr[":uid"] = $where['uid'];
           }
           if(isset($where['status'])){
               $con .= " AND c_status=:status";
               $conarr[":status"] = $where['status'];//正常状态
           }
           $row = init_db()->createCommand()->select('*')
                   ->from($this->tablename_jobs_collect)
                   ->where($con,$conarr)
                   ->limit($limit,$offset)
                   ->queryAll();
           return $row;
       }
       
       
       /**
        * 获取职位收藏信息
        * @param type $uid
        * @return type
        */
       function getcollections($where){
           //拼接where条件
           $con = '';$conarr = array();
           $con .= "1=1";
           if(!empty($where['uid']) && $where['uid'] != 0){
               $con .= " AND u_id=:uid";
               $conarr[":uid"] = $where['uid'];
           }
           
           if(!empty($where['job_id'])){
               $con .= " AND job_id=:job_id";
               $conarr[":job_id"] = $where['job_id'];//职位id
           }
           
           if(!empty($where['job'])){
               $con .= " AND c_status=:status";
               $conarr[":status"] = $where['status'];//正常状态
           }
           $row = init_db()->createCommand()->select('*')
                   ->from($this->tablename_jobs_collect)
                   ->where($con,$conarr)
                   ->limit(1)
                   ->queryRow();
           return $row;
       }
       
       /**
        * 取消收藏职位
        * @param type $id
        * @return type
        */
        function cancel_collections($id){
           return init_db()->createCommand()->update($this->tablename_jobs_collect,array('c_status'=>1),'c_id=:cid',array(':cid'=>$id));
        }
       
       
        /*
         * 添加职位收藏信息
         */
        function collect_jobs($paper){
                init_db()->createCommand()->insert($this->tablename_jobs_collect,$paper);
                return init_db()->getLastInsertID();            
        }
        
        //修改职位收藏状态
        function updatecollect($id,$status){
           return init_db()->createCommand()->update($this->tablename_jobs_collect,array('c_status'=>$status),'c_id=:cid',array(':cid'=>$id));
        }
        
        /**
         * 根据logoid获取logo信息
         * @param type $data
         * @param type $field
         * @return type
         */
        function getlogoById($data,$field){
            $row = init_db()->createCommand()->select($field)
                    ->from($this->tablename_img)
                    ->where(array("in","id",$data))
                    ->queryAll();
            return $row;
        }
        
        /**
        * 刷新职位
        * @param type $id
        * @return boolean
        */
       function refresh_jobs($job_id){
            $oldrefresh = init_db()->createCommand()->select('refresh_time')
                       ->from($this->tablename)
                       ->where('`job_id` =:job_id',array(':job_id'=>$job_id))
                       ->limit(1)
                       ->queryRow();
            $now = time();//获取当前时间
            $t = $now - $oldrefresh['refresh_time'];
            //if(($t / 86400) >=1){//每天只能刷新一次
            if($t > 0 && ($t / 86400) >=1){
                $lastid = init_db()->createCommand()->update($this->tablename,array('refresh_time'=>time()),'job_id=:job_id',array(':job_id'=>$job_id));
                if($lastid){
                    //向专项slor队列表中插入数据
                    $arr['currtime'] = md5(uniqid(rand(), true));
                    $arr['jid'] = $job_id;//职位id
                    $arr['ttype'] = 2;//修改刷新时间
                    $arr['status'] = 0;
                    init_db()->createCommand()->insert($this->tablename_solr,$arr);
                }
                return date('Y-m-d H:i:s',$now);
            }else{
                return false;
            }
       }
       
        function getjobspagetotal($where){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            //userid
            if(!empty($where['uid']) && $where['uid'] != 0){
                $con .= " and uid = :uid";
                $conarr[":uid"] = $where['uid'];
            }
            //状态
            if(isset($where['status'])){
                $con .= " and status = :status";
                $conarr[":status"] = $where['status'];
            }
            
            //职位名称
            if(!empty($where['typejob_name'])){
                $con .= " and typejob_name like :typejob_name";
                $conarr[":typejob_name"] = "%".$where['typejob_name']."%";
            }
            
            $row = init_db()->createCommand()->select('count(*) as total')
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->limit(1)
                    ->queryRow();
            return $row['total'];
        }
        
        function getjobspagelist($limit=10,$offset=0,$where=array()){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            //userid
            if(!empty($where['uid']) && $where['uid'] != 0){
                $con .= " and uid = :uid";
                $conarr[":uid"] = $where['uid'];
            }
            //状态
            if(isset($where['status'])){
                $con .= " and status = :status";
                $conarr[":status"] = $where['status'];
            }
            
            //职位名称
            if(!empty($where['typejob_name'])){
                $con .= " and typejob_name like :typejob_name";
                $conarr[":typejob_name"] = "%".$where['typejob_name']."%";
            }
            
            $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->limit($limit,$offset)
                    ->queryAll();
            return $row;
        }
     
        /*
         * 获取职位信息 一键刷新职位时候用到
         */
        function getjobslist($where = array()) {  
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            //hrid
            if(!empty($where['uid'])){
                $con .= " and uid = :uid";
                $conarr[":uid"] = ($where['uid']);
            }
            $con .= " and status = :status";
            $conarr[":status"] = $where['status'];
            $rows = init_db()->createCommand()->select('*')
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->limit($limit,$start)
                    ->order($sort)
                    ->queryAll();
            return $rows; 
        }
        
        
        /*
         * 从solr中获取职位信息
         */
        function getjobslistforsolr($where = array()) {  
            //拼接where条件
            $search = array();
            //公司id
            $search['q'] = "*:*";
            //状态
            $search['fq'] = array("status:".(int)$where['status']);
            //职位类别查询
            if(!empty($where['search'])){
                array_unshift($search['fq'],"typejob_name:*".$where['search']."*");
            }
            //公司简称查询
            if(!empty($where['searchc'])){
                array_unshift($search['fq'],"c_short_name:*".$where['searchc']."*");
            }
            //工作性质
            if(!empty($where['jobn'])){
                array_unshift($search['fq'],"job_nature:".(int)$where['jobn']);
            }
            //月薪范围
            if(!empty($where['salaryid'])){
                array_unshift($search['fq'],"salary_id:*".$where['salaryid']);
            }
            //工作城市
            if(!empty($where['zone'])){
                array_unshift($search['fq'],"city:".(int)$where['zone']);
            }
            //工作经验
            if(!empty($where['wexp'])){
                array_unshift($search['fq'],"experience:".(int)$where['wexp']);
            }
            //学历要求
            if(!empty($where['educ'])){
                array_unshift($search['fq'],"education:".(int)$where['educ']);
            }
            //hrid
            if(!empty($where['uid'])){
                array_unshift($search['fq'],"uid:".(int)$where['uid']);
            }
            
            //公司性质
            if(!empty($where['comn'])){
                array_unshift($search['fq'],"c_property:".(int)$where['comn']);
            }
            //公司规模
            if(!empty($where['coms'])){
                array_unshift($search['fq'],"c_size:".(int)$where['coms']);
            }
            //公司行业领域
            if(!empty($where['scop'])){
                array_unshift($search['fq'],"c_industry:".(int)$where['scop']);
            }
            
            
            if(!empty($where['c_id'])){
                array_unshift($search['fq'],"company_id:".(int)$where['c_id']);
            }
            
            if(!empty($where['typejobid'])){
                array_unshift($search['fq'],"typejob_id:".(int)$where['typejobid']);
            }
            
            if(!empty($where['sort'])){
                $search['sort'] = $where['sort'];
            }else{
                $search['sort'] = 'refresh_time desc';
            }
            
            //分页处理
            $limit = $where['limit'] ? (int)$where['limit'] : 20;
            $page = $where['page'] ? (int)$where['page'] : 1;
            $start = ($page - 1) * $limit;
            $search['start'] = $start ? $start : 0;
            $search['rows'] = $limit ? $limit : 20;
            if(!empty($where['field'])){
                $search['fl'] = $where['field'];
            }else{
                $search['fl'] = "job_id,company_id,typejob_id,typejob_name,job_nature,salary_start,salary_end,city,experience,education,create_time,refresh_time";
            }
            $rows = init_solrs('jobs')->searchPage($search);
            return $rows; 
        }
        
        
        /*
         * 获取单个职位信息
         */
        function getjobsinfo($where = array()) {  
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if(!empty($where['job_id'])){
                $con .= " and job_id = :job_id";
                $conarr[":job_id"] = $where['job_id'];
            }
            if(isset($where['status'])){
                $con .= " and status = :status";
                $conarr[":status"] = $where['status'];
            }
            $rows = init_db()->createCommand()->select('*')
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->limit(1)
                    ->queryRow();
            return $rows; 
        }
        
        
        /*
         * 获取单个职位详情信息
         */
        function getjobsintroinfo($where = array()) {  
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if(!empty($where['job_id'])){
                $con .= " and job_id = :job_id";
                $conarr[":job_id"] = $where['job_id'];
            }
            
            $rows = init_db()->createCommand()->select('*')
                    ->from($this->tablename_jobs_intro)
                    ->where($con,  $conarr)
                    ->limit(1)
                    ->queryRow();
            return $rows; 
        }
        
        
        /*
         * 添加职位信息
         */
        function insert($paper){
                init_db()->createCommand()->insert($this->tablename,$paper);
                $lastid = init_db()->getLastInsertID();    
                if($lastid){
                    //向专项slor队列表中插入数据
                    $arr['currtime'] = md5(uniqid(rand(), true));
                    $arr['jid'] = $lastid;//职位id
                    $arr['ttype'] = 1;//添加
                    $arr['status'] = 0;
                    init_db()->createCommand()->insert($this->tablename_solr,$arr);
                }
                return $lastid;
        }
        
        /*
         * 添加职位详情信息
         */
        function insertintro($paper){
            init_db()->createCommand()->insert($this->tablename_jobs_intro,$paper);
            return init_db()->getLastInsertID();            
        }
        
        
       /**
        * 修改职位
        * @return type
        */
       function edit_jobs($paper,$id,$flag = 0){
           $lastid = init_db()->createCommand()->update($this->tablename,$paper,'job_id=:job_id',array(':job_id'=>$id));   
           if($lastid){
                //向专项slor队列表中插入数据
                $arr['currtime'] = md5(uniqid(rand(), true));
                $arr['jid'] = $id; //职位id
                if($flag == 1){ //下线
                    $arr['ttype'] = 3; //删除
                }else if($flag == 2){ //重新发布
                    $arr['ttype'] = 1; //添加
                }else{
                    $arr['ttype'] = 2; //修改
                }
                $arr['status'] = 0;
                init_db()->createCommand()->insert($this->tablename_solr,$arr);
            }
            return $lastid;
       }
       
       
       /**
        * 修改职位详情
        * @return type
        */
       function edit_jobsintro($paper,$id){
           return init_db()->createCommand()->update($this->tablename_jobs_intro,$paper,'job_id=:job_id',array(':job_id'=>$id));            
       }
       
       
       /**
        * 删除职位
        * @return type
        */
       function delete_jobs($id){
           $lastid =  init_db()->createCommand()->delete($this->tablename,'job_id=:job_id',array(':job_id'=>$id));
           if($lastid){
               //向专项slor队列表中插入数据
                $arr['currtime'] = md5(uniqid(rand(), true));
                $arr['jid'] = $id; //职位id
                $arr['ttype'] = 3; //删除
                $arr['status'] = 0;
                init_db()->createCommand()->insert($this->tablename_solr,$arr);
           }
           return $lastid;
       }
       
       /**
        * 删除职位详情
        * @return type
        */
       function delete_jobsintro($id){
           return init_db()->createCommand()->delete($this->tablename_jobs_intro,'job_id=:job_id',array(':job_id'=>$id));
       }
       
       /**
        * 修改在职职位
        * @return type
        */
       function edit_onlinejobs($paper,$where){
           $rtn = init_db()->createCommand()->update($this->tablename,$paper,'status=:status and uid = :uid',array(':status'=>$where['status'],':uid'=>$where['uid']));
           if($rtn){
                //向专项slor队列表中插入数据
                $arr['currtime'] = md5(uniqid(rand(), true));
                $arr['jid'] = $where['uid'];//这块就是hruid 进程通过uid去获取他发布的所有职位进行更新时间
                $arr['ttype'] = 5;//一键刷新时间
                $arr['status'] = 0;
                init_db()->createCommand()->insert($this->tablename_solr,$arr);
           }
           return $rtn;
       }
       
        /*
         * 获取职位数量
         */
        function getjobstotal($where) {  
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if($where['search']){
                $con .= " and typejob_name like :search";
                $conarr[":search"] = "%".$where['search'] . "%";
            }
            $con .= " and status = :status";
            $conarr[":status"] = 0;

            $row = init_db()->createCommand()->select('count(*) as total')
                    ->from($this->tablename)
                    ->where($con,  $conarr)
                    ->limit(1)
                    ->queryRow();
            return $row['total']; 
        }
        
        //根据公司ids获取职位信息
        function getjobsbycids($cids){
            $con = ''; $conarr = array();
            $con .= "1=1";
            $con .= " and status = :status";
            $conarr[":status"] = 0;
            $rows = init_db()->createCommand()->select('*')
                    ->from($this->tablename)
                    ->where(array('in','company_id',$cids))
                    ->queryAll();
            return $rows;
        }
       
        
        /**
         * 解绑用户与公司关系时，解绑公司与职位关联关系
         */
        function removejobforcompany($uid){
            $ret = init_db()->createCommand()->update($this->tablename,array('status' => 2),'uid = :uid',array(':uid'=>$uid));
            if($ret){
                //向专项slor队列表中插入数据
                $arr['currtime'] = md5(uniqid(rand(), true));
                $arr['jid'] = $uid;
                $arr['ttype'] = 6;//解除绑定
                $arr['status'] = 0;
                init_db()->createCommand()->insert($this->tablename_solr,$arr);
            }
            return $ret;
        }
        
        /**
         * 解绑用户与公司关系时，解绑公司与所投递的投递简历关联关系
         */
        function removejobfordelivery($uid){
            return init_db()->createCommand()->update($this->tablename_jobs_deli,array('status' => 1),'hr_uid = :hr_uid',array(':hr_uid'=>$uid));
        }
        
        
       /**
        * 获取简历投递总数
        */
       function getdeliverytotal($where){
           //拼接where条件
           $con = '';$conarr = array();
           $con .= "1=1";
           if(!empty($where['uid']) && $where['uid']!=0){
               $con .= " AND u_id=:uid";
               $conarr[":uid"] = $where['uid'];
           }
           if(!empty($where['auto_status'])){
               $con .= " AND auto_status=:astatus";
               $conarr[":astatus"] = $where['auto_status'];
           }
           $command = init_db()->createCommand()
                   ->select("count(*) as total")
                   ->from($this->tablename_jobs_deli)
                   ->where($con,$conarr)
                   ->limit(1)
                   ->queryRow();
           return $command['total']; 
       }
       
       /**
        * 获取公司名称信息
        * @param type $data
        * @param type $field
        */
       function getcompanyById($data,$field){
           $row = init_db()->createCommand()->select($field)
                   ->from($this->tablename_com)
                   ->where(array("in","c_id",$data))
                   ->queryAll();
           return $row;
       }
       /**
        * 获取简历投递记录
        */
       function getdeliverylist($limit=12,$offset=0,$where,$sort){
           $order = '';
           switch ($sort){
               case 1:
                   $order = 'delivery_time';
                   break;
               case 3:
                   $order = 'view_time';
                   break;
               case 4:
                   $order = 'invite_time';
                   break;
               default :
                   $order = 'browse_time';
           }
           //拼接where条件
           $con = '';$conarr = array();
           $con .= "1=1";
           if(!empty($where['uid']) && $where['uid']!=0){
               $con .= " AND u_id=:uid";
               $conarr[":uid"] = $where['uid'];
           }
           if(isset($where['auto_status'])){
               $con .= " AND auto_status=:astatus";
               $conarr[":astatus"] = $where['auto_status'];
           }
           $row = init_db()->createCommand()->select('*')
                   ->from($this->tablename_jobs_deli)
                   ->where($con,$conarr)
                   ->limit($limit,$offset)
                   ->order($order.' DESC')
                   ->queryAll();
           return $row;
       }
       
       //根据简历职位id和用户id获取用户对职位的投递信息
       function userdetailforjob($where){
           $con = '';$conarr = array();
           $con .= "1=1";
           if(!empty($where['u_id']) && $where['u_id']!=0){
               $con .= " AND u_id=:u_id";
               $conarr[":u_id"] = $where['u_id'];
           }
           if(!empty($where['job_id']) && $where['job_id']!=0){
               $con .= " AND job_id=:job_id";
               $conarr[":job_id"] = $where['job_id'];
           }
           $row = init_db()->createCommand()->select('*')
                   ->from($this->tablename_jobs_deli)
                   ->where($con,$conarr)
                   ->limit(1)
                   ->queryRow();
           return $row;
       }
       
       //投点简历-
       function deliveryresume($data){
            init_db()->createCommand()->insert($this->tablename_jobs_deli,$data);
            return init_db()->getLastInsertID();
       }
       
       /**
         * 职位筛选
         * 获取筛选条件下的所有职位
         * 
         */
        function getjobsbysearchtotal($where){
            //拼接where条件
            $con = ''; $conarr = array();
            $con .= "1=1";
            if(!empty($where['search'])){
                $con .= " and typejob_name like :search";
                $conarr[":search"] = "%".$where['search'] . "%";
            }
            //工作性质
            if(!empty($where['jobn'])){
                $con .= " and job_nature = :job_nature";
                $conarr[":job_nature"] = $where['jobn'];
            }
            //月薪范围
            if(!empty($where['salaryid'])){
                $con .= " and FIND_IN_SET(:salaryid,salary_id)";
                $conarr[":salaryid"] = $where['salaryid'];
            }
            //工作城市
            if(!empty($where['zone'])){
                $con .= " and city = :city";
                $conarr[":city"] = ($where['zone']);
            }
            //工作经验
            if(!empty($where['wexp'])){
                $con .= " and experience = :experience";
                $conarr[":experience"] = $where['wexp'];
            }
            //学历要求
            if(!empty($where['educ'])){
                $con .= " and education = ".(int)$where['educ'];
                $conarr[":education"] = $where['educ'];
            }
            
            $con .= " and status = :status";
            $conarr[":status"] = 0;
            
            if(!empty($where['company_ids'])){
                $row = init_db()->createCommand()->select('count(*) as total')
                    ->from($this->tablename)
                    ->where(array('in','company_id',$where['company_ids']))
                    ->where($con,$conarr,true)
                    ->queryRow();
            }else{
                if($where['comn'] || $where['coms'] || $where['scop']){
                    $row = array('total' => 0);
                }
                $row = init_db()->createCommand()->select('count(*) as total')
                    ->from($this->tablename)
                    ->where($con,$conarr)
                    ->limit(1)
                    ->queryRow();
            }
            return $row['total']; 
        }
        
        //从solr获取精选职位10个
        function getjingxuanjobs($fields="*"){
            $search = array();
            $search['q'] = "*:*";
            $search['fq'] =  array(1=>"salary_start:[1 TO 6]",2=>"salary_end:[7 TO 10]");
            $search['sort'] = "salary_start desc,salary_end desc,refresh_time desc";
            $search['fl'] = "job_id,company_id,typejob_name,salary_start,salary_end,city,experience,education,create_time,refresh_time";
            $rows = init_solrs('jobs')->search($search);
            return $rows;
        }
        //从solr获取最新职位10个
        function getzuixinjobs(){
            $search = array();
            $search['q'] = "status:0";
            $search['sort'] = "refresh_time desc";
            $search['fl'] = "job_id,company_id,typejob_name,salary_start,salary_end,city,experience,education,create_time,refresh_time";
            $rows = init_solrs('jobs')->search($search);
            return $rows; 
        }
        
        //用户绑定公司时   修改无主职位的发布用户
        function upwuzhujobs($uid,$cid){
            $re = init_db()->createCommand()->update($this->tablename,array('uid' => $uid,),'uid = :uid and company_id = :cid',array(':uid'=>0,':cid'=>$cid));
            if($re){
                //向专项slor队列表中插入数据
                $arr['currtime'] = md5(uniqid(rand(), true));
                $arr['jid'] = $uid;
                $arr['ttype'] = 7;//绑定
                $arr['status'] = 0;
                init_db()->createCommand()->insert($this->tablename_solr,$arr);
            }
            return $re;
        }
        
        /**
         * 添加用户职位订阅
         * @param type $data
         * @return type
         */
        function addjobsubscribe($data){
            init_db()->createCommand()->insert($this->tablename_jobs_sub,$data);
            return init_db()->getLastInsertID();
        }
        /**
         * 修改用户职位订阅
         * @param type $subid
         * @param type $data
         * @return type
         */
        function savejobsubscribe($subid,$data){
            return init_db()->createCommand()->update($this->tablename_jobs_sub, $data, 'id=:id', array(':id' => $subid));
        }
        
        /**
         * 根据用户获取职位订阅信息
         * @param type $uid
         * @param type $fields
         * @return type
         */
        function getjobsubscribebyuid($uid,$fields="*"){
            $row = init_db()->createCommand()->select($fields)
                    ->from($this->tablename_jobs_sub)
                    ->where('u_id =:uid',array(':uid'=>$uid))
                    ->limit(1)
                    ->queryRow();
            return $row;
        }
        
        /**
         * 删除职位订阅
         * @param type $id
         * @return type
         */
        function deljobsubscribe($id){
            return init_db()->createCommand()->delete($this->tablename_jobs_sub,'id=:id',array(':id'=>$id));
        }
        
        /**
         * 修改职位类别
         * @param type $data
         * @param type $id
         * @return type
         */
        function edittypejob($data,$id){
            return init_db()->createCommand()->update($this->tablename_typejob,$data,'typejobid=:tid',array(':tid'=>$id));
        }
        
        /**
         * 获取HR用户已接收简历状态数据统计总数
         * @param type $where
         * @return type
         */
        function getjobdelistatotal($where){
            //拼接where条件
           $con = '';$conarr = array();
           $con .= "1=1";
           if(!empty($where['hruid'])){
               $con .= " AND hruid=:hruid";
               $conarr[":hruid"] = $where['hruid'];
           }
           if(!empty($where['jobid'])){
               $con .= " AND jobid=:jobid";
               $conarr[":jobid"] = $where['jobid'];
           }
           if(!empty($where["starttime"]) && !empty($where["endtime"])){
               $con .= " AND tjtime BETWEEN ".$where["starttime"]." AND ".$where["endtime"];
           }
           $command = init_db()->createCommand()
                   ->select("count(*) as total")
                   ->from($this->tablename_jobs_deli_sta)
                   ->where($con,$conarr)
                   ->limit(1)
                   ->queryRow();
           return $command['total']; 
        }
        
        /**
         * 获取HR用户已接收简历状态数据统计列表
         * @param type $where
         * @param type $limit
         * @param type $offset
         * @return type
         */
        function getjobdelistalist($where,$limit=12){
            //拼接where条件
            $con = '';$conarr = array();
            $con .= "1=1";
            if(!empty($where['hruid'])){
                $con .= " AND hruid=:hruid";
                $conarr[":hruid"] = $where['hruid'];
            }
            if(!empty($where['jobid'])){
               $con .= " AND jobid=:jobid";
               $conarr[":jobid"] = $where['jobid'];
           }
           if(!empty($where["starttime"]) && !empty($where["endtime"])){
               $con .= " AND tjtime BETWEEN ".$where["starttime"]." AND ".$where["endtime"];
           }
            $row = init_db()->createCommand()->select('*')
                    ->from($this->tablename_jobs_deli_sta)
                    ->where($con,$conarr)
                    ->limit($limit)
                    ->queryAll();
            return $row;
        }
}
?>