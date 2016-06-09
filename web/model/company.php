<?php

/*
  [UCenter] (C)2001-2099 Comsenz Inc.
  This is NOT a freeware, use is subject to license terms

  $Id: user.php 1078 2011-03-30 02:00:29Z monkey $
 */
!defined('IN_UC') && exit('Access Denied');

class companymodel {

    private $tablename = 'kjy_company';
    private $tablename_job = 'kjy_jobs';
    private $tablename_img = 'kjy_images';
    private $tablename_ser = 'kjy_members_service';
    private $tablename_tro = 'kjy_company_intro';
    private $tablename_thu = 'kjy_company_thunder';
    private $tablename_deal = 'kjy_delivery_deal_static';
    private $tablename_hrcert = 'kjy_hr_certificate';
    private $tablename_solr = 'kjy_solr_companys';
    private $tablenamejob_solr = 'kjy_solr_jobs';
    
    /*
     * 从solr中获取公司信息列表
     * 不筛选搜索字段
     */
    function getlistsearchforsolr($where) {
        //拼接where条件
        $search = array();
        $search['q'] = "*:*";
        //状态
        $search['fq'] = array();
        //公司简称查询
        if(!empty($where['searchc'])){
            array_unshift($search['fq'],"c_short_name:*".$where['searchc']."*");
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
        //分页处理
        $limit = $where['limitc'] ? (int)$where['limitc'] : 20;
        $page = $where['pagec'] ? (int)$where['pagec'] : 1;
        $start = ($page - 1) * $limit;
        $search['start'] = $start ? $start : 0;
        $search['rows'] = $limit ? $limit : 20;
        $search['fl'] = "c_id,c_short_name,c_industry,c_city,c_develop_stage,c_logo_id,c_tag";
        $rows = init_solrs('companys')->searchPage($search);
        return $rows;
    }

    /**
     * 用户绑定公司—选择公司页面
     * 根据域名获取公司列表
     */
    function getlistbydomain($domain,$fields="*") {
        $con = '';
        $conarr = array();
        $con .= "1=1";

        if (!empty($domain)) {
            $con .= " and c_homepage like :domain";
            $conarr[":domain"] = "%" . $domain . "%";
        }
        $con .= " and c_verify_status = :c_verify_status";
        $conarr[":c_verify_status"] = 0;

        $rows = init_db()->createCommand()->select($fields)
                ->from($this->tablename)
                ->where($con, $conarr)
                ->queryAll();
        return $rows;
    }

    
    //根据公司id从solr中获取相关的公司信息
    function getsolrlistbyids($ids){
        $search = array();
        if(empty($ids)){
            return $search;
        }
        $para = array();
        foreach($ids as $ik=>$iv){
            $para[] = "c_id:".$iv;
        }
        $search['q'] = implode(" or ", $para);
        $search['rows'] = count($ids); //加个数
        $search['fl'] = "c_id,c_short_name,c_industry,c_develop_stage,c_logo_id,c_tag";
        $rows = init_solrs('companys')->search($search);
        return $rows;
    }

    //根据公司id  获取公司信息 
    function getcompanyinfobyid($cid,$fields="*") {
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename)
                ->where('c_id = :c_id', array(':c_id' => $cid))
                ->limit(1)
                ->queryRow();
        return $row;
    }

    //根据公司id获取公司简介
    function getcompanyintrobyid($cid,$fields="*") {
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_tro)
                ->where('c_id = :c_id', array(':c_id' => $cid))
                ->limit(1)
                ->queryRow();
        return $row;
    }

    //根据公司id获取公司风采信息
    function getcompanythunderbyid($cid,$fields="*") {
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_thu)
                ->where('c_id = :c_id', array(':c_id' => $cid))
                ->order('c_image_status desc')
                ->queryAll();
        return $row;
    }

    //根据ids获取公司对应类别的图片
    function getlogobyids($ids, $type,$fields="*") {
        $rows = init_db()->createCommand()->select($fields)
                ->from($this->tablename_img)
                ->where(array('in', 'id', $ids))
                ->where('imagetype = :imagetype', array(':imagetype' => $type), true)
                ->queryAll();
        return $rows;
    }
    
    //根据id获取对应的图片信息
    function getimagebyid($id,$fields="*"){
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_img)
                ->where('id = :id', array(':id' => $id))
                ->limit(1)
                ->queryRow();
        return $row;
    }

    //根据公司id获取公司信息
    function getcompanyinfo($cid,$fields="*") {
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename)
                ->where('c_id = :c_id', array(':c_id' => $cid))
                ->limit(1)
                ->queryRow();
        return $row;
    }

    //根据用户id获取用户和公司对应关系
    function getusertocompany($uid, $status = 0,$fields="*") {
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_ser)
                ->where('uid = :uid and status = :status', array(':uid' => $uid, ':status' => $status))
                ->limit(1)
                ->queryRow();
        return $row;
    }
    
    /**
     * 根据公司id获取公司与用户服务关系
     * @param type $cid
     * @param type $status
     * @param type $fields
     * @return type
     */
    function getcompanybindbycid($cid,$status = 0,$fields="*"){
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename_ser)
                ->where('company_id = :cid and status = :status', array(':cid' => $cid, ':status' => $status))
                ->limit(1)
                ->queryRow();
        return $row;
    }
    
    /**
     * 根据用户ID获取所创建的公司
     * @param type $uid
     * @param type $fields
     * @return type
     */
    function getcompanyinfobyuid($uid,$fields='*') {
        $row = init_db()->createCommand()->select($fields)
                ->from($this->tablename)
                ->where('c_add_userid = :uid', array(':uid' => $uid))
                ->limit(1)
                ->queryRow();
        return $row;
    }

    //解除公司和用户的绑定关系
    function removeset($uid) {
        $ret = init_db()->createCommand()->update($this->tablename_ser, array('status' => 1), 'uid=:uid', array(':uid' => $uid));
        if($ret){
            if($ret) {
                //向专项slor队列表中插入数据
                $arr['currtime'] = md5(uniqid(rand(), true));
                $arr['cid'] = $uid;
                $arr['ttype'] = 4; //解除绑定
                $arr['status'] = 0;
                init_db()->createCommand()->insert($this->tablename_solr, $arr);
            }
        }
        return $ret;
    }

    //添加用户与公司的绑定关系
    function adduserforcompany($data) {
        init_db()->createCommand()->insert($this->tablename_ser, $data);
        return init_db()->getLastInsertID();
    }

    //用户与公司重新绑定
    function edituserforcompany($id, $data) {
        return init_db()->createCommand()->update($this->tablename_ser, $data, 'id=:id', array(':id' => $id));
    }

    //修改简历接收邮箱
    function upresumreceive($uid, $company_id, $email) {
        return init_db()->createCommand()->update($this->tablename_ser, array('contact_email' => $email), 'company_id=:company_id AND uid = :uid', array(':company_id' => $company_id, ':uid' => $uid));
    }

    /**
     * 添加公司信息
     * @param type $data
     * @return type
     */
    function addcompany($data) {
        init_db()->createCommand()->insert($this->tablename, $data);
        $lastid = init_db()->getLastInsertID();   
        if($lastid) {
            //向专项slor队列表中插入数据
            $arr['currtime'] = md5(uniqid(rand(), true));
            $arr['cid'] = $lastid; //公司id
            $arr['ttype'] = 1; //添加
            $arr['status'] = 0;
            init_db()->createCommand()->insert($this->tablename_solr, $arr);
        }
        return $lastid;
    }

    /**
     * 修改公司基本信息
     * @param type $id
     * @param type $data
     * @return type
     */
    function editcompany($cid, $data,$st = 0,$flag = 0) {
        $lastid = init_db()->createCommand()->update($this->tablename, $data, 'c_id=:cid', array(':cid' => $cid));
        if($st == 1){
            //向专项slor队列表中插入数据
            $arr['currtime'] = md5(uniqid(rand(), true));
            $arr['cid'] = $cid;//公司id
            $arr['ttype'] = 2;//修改公司
            $arr['status'] = 0;
            init_db()->createCommand()->insert($this->tablename_solr,$arr);
            if($flag == 1){
                $comlist = init_db()->createCommand()->select("job_id,company_id")
                    ->from($this->tablename_job)
                    ->where('company_id = :company_id', array(':company_id' => $cid))
                    ->queryAll();
                if(!empty($comlist)){
                    $arr2['currtime'] =  md5(uniqid(rand(), true));
                    $arr2['jid'] = $comlist['job_id'];//公司id
                    $arr2['ttype'] = 2;//修改公司去更新职位
                    $arr2['status'] = 0;
                    init_db()->createCommand()->insert($this->tablenamejob_solr,$arr2);
                }
            }
        }
        return $lastid;
    }

    /**
     * 添加公司简介信息
     * @param type $data
     * @return type
     */
    function addbrief($data) {
        init_db()->createCommand()->insert($this->tablename_tro, $data);
        return init_db()->getLastInsertID();
    }

    /**
     * 修改公司简介信息
     * @param type $id
     * @param type $data
     * @return type
     */
    function editbrief($bid, $data) {
        $ret = init_db()->createCommand()->update($this->tablename_tro, $data, 'id=:bid', array(':bid' => $bid));
        if (false === $ret) {
            return 0;
        } else {
            return $bid;
        }
    }

    /**
     * 添加公司风采图关系
     * @param type $data
     * @return type
     */
    function insertcompanystyle($data) {
        init_db()->createCommand()->insert($this->tablename_thu, $data);
        return init_db()->getLastInsertID();
    }

    /**
     * 删除公司风采图关系
     * @param type $id
     */
    function delcompanystyle($id) {
        return init_db()->createCommand()->delete($this->tablename_thu, 'c_image_id=:id', array(':id' => $id));
    }

    /**
     * 修改公司风采图关系
     * @param type $id
     * @param type $data
     * @return type
     */
    function editcompanystyle($tid, $data) {
        $cidArr = init_db()->createCommand()->select('c_id')
                ->from($this->tablename_thu)
                ->where('c_image_id = :tid', array(':tid' => $tid))
                ->limit(1)
                ->queryRow();
        init_db()->createCommand()->update($this->tablename_thu, array("c_image_status"=>0), 'c_id=:cid', array(':cid' => $cidArr["c_id"]));
        return init_db()->createCommand()->update($this->tablename_thu, $data, 'c_image_id=:tid', array(':tid' => $tid));
    }
    
    function getcompanydealinfo($where,$fields="*"){
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['cid'])) { 
            $con .= " AND cid = :cid";
            $conarr[":cid"] = $where['cid'];
        }
        $rows = init_db()->createCommand()->select($fields)
                ->from($this->tablename_deal)
                ->where($con, $conarr)
                ->limit(1)
                ->queryRow();
        return $rows;
    }
    
    /**
     * 添加HR企业认证申请
     * @param type $data
     * @return type
     */
    function inserthrcertificate($data) {
        init_db()->createCommand()->insert($this->tablename_hrcert, $data);
        return init_db()->getLastInsertID();
    }
    
    /**
     * 修改HR企业认证申请
     * @param type $id
     * @param type $data
     * @return type
     */
    function edithrcertificate($id,$data){
        return init_db()->createCommand()->update($this->tablename_hrcert, $data, 'id=:id', array(':id' => $id));
    }
    
    /**
     * 获取认证信息
     * @param type $where
     * @param type $fields
     * @return type
     */
    function getcertbyarr($where,$fields="*"){
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['cid'])) { 
            $con .= " AND company_id = :cid";
            $conarr[":cid"] = $where['cid'];
        }
        if (!empty($where['uid'])) { 
            $con .= " AND hr_uid = :uid";
            $conarr[":uid"] = $where['uid'];
        }
        $rows = init_db()->createCommand()->select($fields)
                ->from($this->tablename_hrcert)
                ->where($con, $conarr)
                ->limit(1)
                ->queryRow();
        return $rows;
    }
    
    
    /**
     * 根据公司id获取认证信息
     * @param type $where
     * @param type $fields
     * @return type
     */
    function getcertbycid($where,$fields="*"){
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['cid'])) { 
            $con .= " AND company_id = :cid";
            $conarr[":cid"] = $where['cid'];
        }
        $rows = init_db()->createCommand()->select($fields)
                ->from($this->tablename_hrcert)
                ->where($con, $conarr)
                ->limit(1)
                ->queryRow();
        return $rows;
    }

}

?>