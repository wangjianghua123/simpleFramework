<?php

/**
 * 简历管理MODEL
 */
!defined('IN_UC') && exit('Access Denied');

class resumemodel {

    private $tablename = 'kjy_resume';
    private $tablename_com = 'kjy_company';
    private $tablename_img = 'kjy_images';
    private $tablename_edu = 'kjy_resume_education';
    private $tablename_intro = 'kjy_resume_intro';
    private $tablename_active = 'kjy_resume_active';
    private $tablename_work = 'kjy_resume_work';
    private $tablename_tem = 'kjy_resume_template';
    private $tablename_del_sta = 'kjy_resume_delivery_static';
    private $tablename_dis = 'kjy_resume_display';

    /**
     * 根据简历ids获取简历信息
     * @param type $data
     * @param type $field
     * @return type
     */
    function getresumeinfo($data, $field) {
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename)
                ->where(array("in", "id", $data))
                ->queryAll();
        return $row;
    }

    /**
     * 根据组合条件查询简历信息
     * @param type $where
     * @param type $field
     * @return type
     */
    function getresumebyarr($where, $fields = "*") {
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['uid']) && $where['uid'] != 0) {
            $con .= " AND u_id=:uid";
            $conarr[":uid"] = $where['uid'];
        }
        if (isset($where['status'])) {
            $con .= " AND d_status=:status";
            $conarr[":status"] = $where['status']; //正常状态
        }
        if (isset($where['rid'])) {
            $con .= " AND id=:id";
            $conarr[":id"] = $where['rid'];
        }
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename)
                ->where($con, $conarr)
                ->limit(1)
                ->queryRow();
        return $row;
    }

    /**
     * 修改我的简历
     * @return type
     */
    function edit($paper, $id) {
        return init_db()->createCommand()->update($this->tablename, $paper, 'id=:id', array(':id' => $id));
    }

    /**
     * 获取我的简历总数
     * @param type $where
     * @return type
     */
    function getresumetotal($where) {
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['uid']) && $where['uid'] != 0) {
            $con .= " AND u_id=:uid";
            $conarr[":uid"] = $where['uid'];
        }
        if (!empty($where['status'])) {
            $con .= " AND d_status=:status";
            $conarr[":status"] = $where['status']; //正常状态
        }
        $command = init_db()->createCommand()
                ->select("count(*) as total")
                ->from($this->tablename)
                ->where($con, $conarr)
                ->limit(1)
                ->queryRow();
        return $command['total'];
    }

    /**
     * 获取我的简历信息
     */
    function getresumelist($limit = 10, $offset = 0, $where, $fields = "*") {
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['uid']) && $where['uid'] != 0) {
            $con .= " AND u_id=:uid";
            $conarr[":uid"] = $where['uid'];
        }
        if (!empty($where['status'])) {
            $con .= " AND d_status=:status";
            $conarr[":status"] = $where['status']; //正常状态
        }
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename)
                ->where($con, $conarr)
                ->limit($limit, $offset)
                ->order('id desc')
                ->queryAll();
        return $row;
    }

    /**
     * 刷新简历
     * @param type $id
     * @return boolean
     */
    function refresh_resume($id) {
        $oldrefresh = init_db()->createCommand()->select('refresh_time')
                ->from($this->tablename)
                ->where('`id` =:id', array(':id' => $id))
                ->limit(1)
                ->queryRow();
        $now = time(); //获取当前时间
        $t = $now - $oldrefresh['refresh_time'];
        //if(($t / 86400) >=1){//每天只能刷新一次
        if ($t > 0 && ($t / 60) >= 1) {
            init_db()->createCommand()->update($this->tablename, array('refresh_time' => time()), 'id=:id', array(':id' => $id));
            return date('Y-m-d', $now);
        } else {
            return false;
        }
    }

    /**
     * 默认简历
     * @param type $uid
     * @param type $id
     */
    function default_resume($uid, $id) {
        init_db()->createCommand()->update($this->tablename, array('status' => 0), 'u_id=:u_id', array(':u_id' => $uid));
        return init_db()->createCommand()->update($this->tablename, array('status' => 1), 'id=:id', array(':id' => $id));
    }

    /**
     * 复制简历--基本信息
     * @param type $id
     * @return boolean
     */
    function copy_resume($id) {
        $data = $this->getresumebyarr(array("rid"=>$id));
        if (!empty($data)) {
            unset($data['id']);
            $data['create_time'] = time();//创建时间
            $data['update_time'] = time();//更新时间
            $data['refresh_time'] = 0;//刷新时间
            $data['status'] = 0;
            init_db()->createCommand()->insert($this->tablename, $data);
            return init_db()->getLastInsertID();
        } else {
            return false;
        }
    }
    
    /**
     * 复制简历--在校经历
     * @param type $rid
     * @param type $newrid
     * @return boolean
     */
    function copy_resumeactive($rid,$newrid){
        $data = $this->getresumeactivebyrid(array("rid"=>$rid));
        $ret = true;
        foreach ($data as $dk => $dv){
            unset($dv["ra_id"]);
            $dv["r_id"] = $newrid;
            $lastid = $this->addresumeactive($dv);
            if($lastid == false){
                $ret = false;
            }
        }
        return $ret;
    }
    
    /**
     * 复制简历--作品展示
     * @param type $rid
     * @param type $newrid
     * @return boolean
     */
    function copy_resumedisplay($rid,$newrid){
        $data = $this->getresumedisplaybyrid(array("rid"=>$rid));
        $ret = true;
        foreach ($data as $dk => $dv){
            unset($dv["rd_id"]);
            $dv["r_id"] = $newrid;
            $lastid = $this->addresumedisplay($dv);
            if($lastid == false){
                $ret = false;
            }
        }
        return $ret;
    }
    
    /**
     * 复制简历--教育经历
     * @param type $rid
     * @param type $newrid
     * @return boolean
     */
    function copy_resumeeducation($rid,$newrid){
        $data = $this->getresumeedubyrid(array("rid"=>$rid));
        $ret = true;
        foreach ($data as $dk => $dv){
            unset($dv["e_id"]);
            $dv["r_id"] = $newrid;
            $lastid = $this->addresumeeducation($dv);
            if($lastid == false){
                $ret = false;
            }
        }
        return $ret;
    }
    
    /**
     * 复制简历--详细信息（职业技能，荣誉奖励，自我评价等）
     * @param type $rid
     * @param type $newrid
     */
    function copy_resumeintro($rid,$newrid){
        $data = $this->getresumeintrobyrid(array("rid"=>$rid));
        unset($data["id"]);
        $data["r_id"] = $newrid;
        $ret = $this->addresumeintroinfo($data);
        return $ret;
    }
    
    /**
     * 复制简历--工作经历
     * @param type $rid
     * @param type $newrid
     * @return boolean
     */
    function copy_resumework($rid,$newrid){
        $data = $this->getresumeworkbyrid(array("rid"=>$rid));
        $ret = true;
        foreach ($data as $dk => $dv){
            unset($dv["w_id"]);
            $dv["r_id"] = $newrid;
            $lastid = $this->addresumework($dv);
            if($lastid == false){
                $ret = false;
            }
        }
        return $ret;
    }

    /**
     * 删除简历
     * @param type $id
     * @return type
     */
    function delete_resume($id) {
        return init_db()->createCommand()->delete($this->tablename, 'id=:id', array(':id' => $id));
    }

    /**
     * 获取公司名称信息
     * @param type $data
     * @param type $field
     */
    function getcompanyById($data, $field = "*") {
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_com)
                ->where(array("in", "c_id", $data))
                ->queryAll();
        return $row;
    }

    /**
     * 根据logoid获取logo信息
     * @param type $data
     * @param type $field
     * @return type
     */
    function getlogoById($data, $field = "*") {
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_img)
                ->where(array("in", "id", $data))
                ->queryAll();
        return $row;
    }

    /**
     * 添加用户简历基本信息
     * @param type $data
     * @return type
     */
    function addresumebasic($data) {
        init_db()->createCommand()->insert($this->tablename, $data);
        return init_db()->getLastInsertID();
    }

    /**
     * 修改用户简历基本信息
     * @param type $id
     * @param type $data
     * @return type
     */
    function editresumebasic($id, $data) {
        return init_db()->createCommand()->update($this->tablename, $data, 'id=:id', array(':id' => $id));
    }

    /**
     * 添加简历教育经历信息
     * @param type $data
     * @return type
     */
    function addresumeeducation($data) {
        init_db()->createCommand()->insert($this->tablename_edu, $data);
        return init_db()->getLastInsertID();
    }

    /**
     * 修改简历教育经历信息
     * @param type $id
     * @param type $data
     * @return type
     */
    function editresumeeducation($id, $data) {
        return init_db()->createCommand()->update($this->tablename_edu, $data, 'e_id=:id', array(':id' => $id));
    }
    
    /**
     * 根据简历编号修改简历教育经历信息
     * @param type $rid
     * @param type $data
     * @return type
     */
    function editreresumeedubyrid($rid,$data){
        return init_db()->createCommand()->update($this->tablename_edu, $data, 'r_id=:rid', array(':rid' => $rid));
    }
    
    /**
     * 根据简历编号修改简历社团活动信息
     * @param type $rid
     * @param type $data
     * @return type
     */
    function editreresumeactivebyrid($rid,$data){
        return init_db()->createCommand()->update($this->tablename_active, $data, 'r_id=:rid', array(':rid' => $rid));
    }
    
    /**
     * 根据简历编号修改简历社会实践经历信息
     * @param type $rid
     * @param type $data
     * @return type
     */
    function editreresumeworkbyrid($rid,$data){
        return init_db()->createCommand()->update($this->tablename_work, $data, 'r_id=:rid', array(':rid' => $rid));
    }
    

    /**
     * 删除简历教育经历信息
     * @param type $id
     * @return type
     */
    function deleteresumeeducation($id) {
        return init_db()->createCommand()->delete($this->tablename_edu, 'e_id=:id', array(':id' => $id));
    }

    /**
     * 添加简历详情信息(获奖经历、证书及技能、自我评价)
     * @param type $data
     * @return type
     */
    function addresumeintroinfo($data) {
        init_db()->createCommand()->insert($this->tablename_intro, $data);
        return init_db()->getLastInsertID();
    }

    /**
     * 更新简历详情信息(获奖经历、证书及技能、自我评价)
     * @param type $id
     * @param type $data
     * @return type
     */
    function editresumeintroinfo($id, $data) {
        return init_db()->createCommand()->update($this->tablename_intro, $data, 'id=:id', array(':id' => $id));
    }

    /**
     * 添加简历社团活动经历信息
     * @param type $data
     * @return type
     */
    function addresumeactive($data) {
        init_db()->createCommand()->insert($this->tablename_active, $data);
        return init_db()->getLastInsertID();
    }

    /**
     * 修改简历社团活动经历信息
     * @param type $id
     * @param type $data
     * @return type
     */
    function editresumeactive($id, $data) {
        return init_db()->createCommand()->update($this->tablename_active, $data, 'ra_id=:id', array(':id' => $id));
    }

    /**
     * 删除简历社团活动经历信息
     * @param type $id
     * @return type
     */
    function deleteresumeactive($id) {
        return init_db()->createCommand()->delete($this->tablename_active, 'ra_id=:id', array(':id' => $id));
    }

    /**
     * 添加简历社会实践经历信息
     * @param type $data
     * @return type
     */
    function addresumework($data) {
        init_db()->createCommand()->insert($this->tablename_work, $data);
        return init_db()->getLastInsertID();
    }

    /**
     * 修改简历社会实践经历信息
     * @param type $id
     * @param type $data
     * @return type
     */
    function editresumework($id, $data) {
        return init_db()->createCommand()->update($this->tablename_work, $data, 'w_id=:id', array(':id' => $id));
    }

    /**
     * 删除简历社会实践经历信息
     * @param type $id
     * @return type
     */
    function deleteresumework($id) {
        return init_db()->createCommand()->delete($this->tablename_work, 'w_id=:id', array(':id' => $id));
    }

    /**
     * 根据简历id获取简历社会经验信息
     * @param type $data
     * @param type $field
     * @return type
     */
    function getresumeworkinfo($where = array(), $field = "*") {
        $row = init_db()->createCommand()->select('*')
                ->from($this->tablename_work)
                ->where(array("in", "r_id", $where))
                ->order('w_end_year DESC')
                ->queryAll();
        return $row;
    }

    /**
     * 根据简历ids获取简历教育经历信息
     * @param type $data
     * @param type $field
     * @return type
     */
    function getresumeeduinfo($where = array(), $field = "*") {
        $row = init_db()->createCommand()->select('*')
                ->from($this->tablename_edu)
                ->where(array("in", "r_id", $where))
                ->order('end_year DESC')
                ->queryAll();
        return $row;
    }

    /**
     * 根据单个简历id获取简历教育经历信息
     * @param type $where
     * @param type $field
     * @param type $limit
     * @return type
     */
    function getresumeedubyrid($where, $field = "*", $limit = 5) {
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['rid']) && $where['rid'] != 0) {
            $con .= " AND r_id=:rid";
            $conarr[":rid"] = $where['rid'];
        }
        if (!empty($where['status'])) {
            $con .= " AND status=:status";
            $conarr[":status"] = $where['status'];
        }
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_edu)
                ->where($con, $conarr)
                ->limit($limit)
                ->order("e_id DESC")
                ->queryAll();
        return $row;
    }

    /**
     * 根据简历id获取简历详情信息(获奖经历、证书技能、特长兴趣、自我评价)
     * @param type $where
     * @param type $field
     * @param type $limit
     * @return type
     */
    function getresumeintrobyrid($where, $field = "*") {
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['rid']) && $where['rid'] != 0) {
            $con .= " AND r_id=:rid";
            $conarr[":rid"] = $where['rid'];
        }
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_intro)
                ->where($con, $conarr)
                ->limit(1)
                ->queryRow();
        return $row;
    }

    /**
     * 根据简历id获取社团活动经历信息
     * @param type $where
     * @param type $field
     * @param type $limit
     * @return type
     */
    function getresumeactivebyrid($where, $field = "*",$limit = "5") {
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['rid']) && $where['rid'] != 0) {
            $con .= " AND r_id=:rid";
            $conarr[":rid"] = $where['rid'];
        }
        if (!empty($where['status'])) {
            $con .= " AND status=:status";
            $conarr[":status"] = $where['status'];
        }
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_active)
                ->where($con, $conarr)    
                ->limit($limit)
                ->queryAll();
        return $row;
    }

    /**
     * 根据简历id获取社会实践经历信息
     * @param type $where
     * @param type $field
     * @param type $limit
     * @return type
     */
    function getresumeworkbyrid($where, $field = "*", $limit = 5) {
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['rid']) && $where['rid'] != 0) {
            $con .= " AND r_id=:rid";
            $conarr[":rid"] = $where['rid'];
        }
        if (!empty($where['status'])) {
            $con .= " AND status=:status";
            $conarr[":status"] = $where['status'];
        }
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_work)
                ->where($con, $conarr)
                ->limit($limit)
                ->queryAll();
        return $row;
    }
    
    /**
     * 获取模板信息
     * @param type $where
     * @param type $fields
     * @return type
     */
    function gettemplateinfo($where = array(),$fields="*"){
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['tid']) && $where['tid'] != 0) {
            $con .= " AND sub_typejobid=:tid";
            $conarr[":tid"] = $where['tid'];
        }
        if (!empty($where['jyid']) && $where['jyid'] != 0) {
            $con .= " AND u_experience=:jyid";
            $conarr[":jyid"] = $where['jyid'];
        }
        if (!empty($where['lgid']) && $where['lgid'] != 0) {
            $con .= " AND u_language=:lgid";
            $conarr[":lgid"] = $where['lgid'];
        }
        if(!empty($where)){
            $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_tem)
                ->where($con, $conarr)
                ->limit(1)
                ->queryRow();
        }else{
            $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_tem)
                ->where($con, $conarr)
                ->queryAll();
        }
        return $row;
    }
    
    /**
     * 根据简历编号删除社团活动
     * @param type $rid
     * @return type
     */
    function deleteactivebyrid($rid){
        return init_db()->createCommand()->delete($this->tablename_active, 'r_id=:rid', array(':rid' => $rid));
    }
    
    /**
     * 根据简历编号删除教育经历
     * @param type $rid
     * @return type
     */
    function deleteedubyrid($rid){
        return init_db()->createCommand()->delete($this->tablename_edu, 'r_id=:rid', array(':rid' => $rid));
    }
    
    /**
     * 根据简历编号删除详情信息
     * @param type $rid
     * @return type
     */
    function deleteintrobyrid($rid){
        return init_db()->createCommand()->delete($this->tablename_intro, 'r_id=:rid', array(':rid' => $rid));
    }

    /**
     * 根据简历编号删除社会实践
     * @param type $rid
     * @return type
     */
    function deleteworkbyrid($rid){
        return init_db()->createCommand()->delete($this->tablename_work, 'r_id=:rid', array(':rid' => $rid));
    }
    
    
    /**
     * 简历搜索使用
     * @param type $where
     * @return type
     */
    function searchresumepagetotal($where = array()) {
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['u_jobid'])) {
            $con .= " AND u_jobid=:u_jobid";
            $conarr[":u_jobid"] = $where['u_jobid'];
        }
        if (!empty($where['u_jobname'])) {
            $con .= " AND u_jobname=:u_jobname";
            $conarr[":u_jobname"] = $where['u_jobname'];
        }
        if (isset($where['u_job_type'])) {
            $con .= " AND u_job_type=:u_job_type";
            $conarr[":u_job_type"] = $where['u_job_type'];
        }
        if (!empty($where['u_salary'])) {
            $con .= " AND u_salary=:u_salary";
            $conarr[":u_salary"] = $where['u_salary'];
        }
        if (!empty($where['u_city'])) {
            $con .= " AND u_city=:u_city";
            $conarr[":u_city"] = $where['u_city'];
        }
        if (!empty($where['u_industry'])) {
            $con .= " AND u_industry=:industry";
            $conarr[":industry"] = $where['u_industry'];
        }
        $con .= " AND d_status=:d_status";
        $conarr[":d_status"] = 1;
        if(!empty($where['u_id'])){
            $command = init_db()->createCommand()
                        ->select("count(*) as total")
                        ->from($this->tablename)
                        ->where(array("in","u_id",explode(",",$where['u_id'])))
                        ->where($con, $conarr,true)
                        ->limit(1)
                        ->queryRow();
        }else{
            $command = init_db()->createCommand()
                        ->select("count(*) as total")
                        ->from($this->tablename)
                        ->where($con, $conarr)
                        ->limit(1)
                        ->queryRow();
        }
        return $command['total'];
    }

    /*
     * 简历搜索使用
     */
    function searchresumepagelist($limit = 10, $offset = 0, $where = array(),$fields = "*"){  
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['u_jobid'])) {
            $con .= " AND u_jobid=:u_jobid";
            $conarr[":u_jobid"] = $where['u_jobid'];
        }
        if (!empty($where['u_jobname'])) {
            $con .= " AND u_jobname=:u_jobname";
            $conarr[":u_jobname"] = $where['u_jobname'];
        }
        if (isset($where['u_job_type'])) {
            $con .= " AND u_job_type=:u_job_type";
            $conarr[":u_job_type"] = $where['u_job_type'];
        }
        if (!empty($where['u_salary'])) {
            $con .= " AND u_salary=:u_salary";
            $conarr[":u_salary"] = $where['u_salary'];
        }
        if (!empty($where['u_city'])) {
            $con .= " AND u_city=:u_city";
            $conarr[":u_city"] = $where['u_city'];
        }
        if (!empty($where['u_industry'])) {
            $con .= " AND u_industry=:industry";
            $conarr[":industry"] = $where['u_industry'];
        }
        $con .= " AND d_status=:d_status";
        $conarr[":d_status"] = 1;
        if(!empty($where['u_id'])){
                $row = init_db()->createCommand()->select($fields)
                        ->from($this->tablename)
                        ->where(array("in","u_id",explode(",",$where['u_id'])))
                        ->where($con, $conarr,true)
                        ->limit($limit,$offset)
                        ->queryAll();
        }else{
                $row = init_db()->createCommand()->select($fields)
                        ->from($this->tablename)
                        ->where($con, $conarr)
                        ->limit($limit,$offset)
                        ->queryAll();
        }
        return $row;
    }
    
    /**
     * 根据用户获取已投递简历状态信息
     * @param type $where
     * @param type $fields
     * @return type
     */
    public function getresumecast($where,$fields="*"){
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['uid'])) {
            $con .= " AND uid=:uid";
            $conarr[":uid"] = $where['uid'];
        }
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_del_sta)
                ->where($con, $conarr)
                ->limit(1)
                ->queryRow();
        return $row;
    }
    
    /**
     * 添加用户简历作品展示
     * @param type $data
     * @return type
     */
    function addresumedisplay($data) {
        init_db()->createCommand()->insert($this->tablename_dis, $data);
        return init_db()->getLastInsertID();
    }
    
    /**
     * 修改用户简历作品展示
     * @param type $rdid
     * @param type $data
     * @return type
     */
    function editresumedisplay($rdid,$data){
        return init_db()->createCommand()->update($this->tablename_dis, $data, 'rd_id=:id', array(':id' => $rdid));
    }
    
    /**
     * 根据简历id获取作品展示
     * @param type $where
     * @param type $fields
     * @param type $limit
     * @return type
     */
    function getresumedisplaybyrid($where, $limit=20, $fields = "*"){
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['rid']) && $where['rid'] != 0) {
            $con .= " AND r_id=:rid";
            $conarr[":rid"] = $where['rid'];
        }
        if (!empty($where['type'])) {
            $con .= " AND w_type=:type";
            $conarr[":type"] = $where['type'];
        }
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_dis)
                ->where($con, $conarr)
                ->limit($limit)
                ->queryAll();
        return $row;
    }

    /**
     * 根据简历编号删除作品展示
     * @param type $id
     * @return type
     */
    function deletehandworkbyid($id){
        return init_db()->createCommand()->delete($this->tablename_dis, 'rd_id=:id', array(':id' => $id));
    }
}

?>
