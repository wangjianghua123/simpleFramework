<?php
!defined('IN_UC') && exit('Access Denied');
/*
 * 简历资讯
 */
class zixunmodel {
    private $tablename_article = 'kjy_article';//文章表
    private $tablename_category = 'kjy_article_category';//栏目表(文章分类表)
    private $tablename_content = 'kjy_article_data'; //文章内容表

    //count
    function getzixunpagecount($where) {
        $con = '';$conarr = array();
        $con = "1=1";
        if($where['catid']){
            $con .= " and catid = :catid";
            $conarr[":catid"] = $where['catid'];
        }

        if($where['status']){
            $con .= " and status = :status ";
            $conarr[":status"] =$where['status'];
        }

        $row = init_db()->createCommand()->select('count(*) as total')
            ->from($this->tablename_article)
            ->where($con, $conarr)
            ->queryRow();
        return $row['total'];
    }

    //分页列表
    function getzixunpagelist($limit=10,$offset=0,$where=array(),$file='*',$order="id desc"){
        //拼接where条件
        $con = ''; $conarr = array();
        $con .= "1=1";

        if(!empty($where['id']) && $where['id'] != 0){
            $con .= " AND id=:id";
            $conarr[":id"] = $where['id'];
        }

        if(isset($where['catid'])){
            $con .= " AND catid=:catid";
            $conarr[":catid"] = $where['catid'];//栏目类别
        }

        if(isset($where['status'])){
            $con .= " AND status=:status";
            $conarr[":status"] = $where['status'];//正常状态
        }

        $result = init_db()->createCommand()->select($file)
                ->from($this->tablename_article)
                ->where($con,  $conarr)
                ->limit($limit,$offset)
                ->order($order)
                ->queryAll();
        return $result;
    }

    //列表(栏目类别(一次查询多个栏目类别，资讯首页’简历经典封面‘>>更多 用到的)
    //用到In了 . 比如：catid in (1,2,3)
    function getzixuninpagelist($limit=20,$offset=0,$materids,$order="id desc"){
         $result = init_db()->createCommand()->select('*')
                ->from($this->tablename_article)
                ->where(array('in','catid',$materids))
                ->limit($limit,$offset)
                ->where('status=:status',array(':status'=>1),true)
                ->order($order)
                ->queryAll();
        return $result;
    }

    //栏目group by
    function group_course($field="*",$where="",$group_by="catid") {
        return init_db()->createCommand()->fetch_all("select ".$field." from ".$this->tablename_article." where 1 ".$where." group by ".$group_by);
    }

    //列表
    function getzixunall($where=array(),$field="*",$order="id desc"){
        //拼接where条件
        $con = ''; $conarr = array();
        $con .= "1=1";

        if(!empty($where['id']) && $where['id'] != 0){
            $con .= " AND id=:id";
            $conarr[":id"] = $where['id'];
        }

        if(isset($where['catid'])){
            $con .= " AND catid=:catid";
            $conarr[":catid"] = $where['catid'];//栏目类别
        }

        if(isset($where['status'])){
            $con .= " AND status=:status";
            $conarr[":status"] = $where['status'];//正常状态
        }

        $result = init_db()->createCommand()->select($field)
                ->from($this->tablename_article)
                ->where($con,  $conarr)
                ->order($order)
                ->queryAll();
        return $result;
    }

    //详细
    function getxixuninfo($where,$field="*",$order="id desc") {
        //拼接where条件
        $con = ''; $conarr = array();
        $con = "1=1";

        if(!empty($where['previous_id']) && $where['previous_id'] != 0){ //previous_id  上一篇
            $con .= " AND id<:id";
            $conarr[":id"] = $where['previous_id'];
        }

        if(!empty($where['next_id']) && $where['next_id'] != 0){ //next_id  下一篇
            $con .= " AND id>:id";
            $conarr[":id"] = $where['next_id'];
        }

        if(!empty($where['id']) && $where['id'] != 0){
            $con .= " AND id=:id";
            $conarr[":id"] = $where['id'];
        }

        if(isset($where['catid'])){
            $con .= " AND catid=:catid";
            $conarr[":catid"] = $where['catid'];//栏目类别
        }

        if(isset($where['status'])){
            $con .= " AND status=:status";
            $conarr[":status"] = $where['status'];//正常状态
        }

        $info = init_db()->createCommand()->select($field)
            ->from($this->tablename_article)
            ->where($con,  $conarr)
            ->order($order)
            ->queryRow();
        return $info;
    }
    
    
    //随机算出一个id求数据
    function getrandxixuninfo($where,$field="*") {
        //拼接where条件
        $con = ''; $conarr = array();
        $con = "1=1";
        
        if(!empty($where['id']) && $where['id'] != 0){
            $con .= " AND id=:id";
            $conarr[":id"] = $where['id'];
        }
        
        if(isset($where['catid'])){
            $con .= " AND catid=:catid";
            $conarr[":catid"] = $where['catid'];//栏目类别
        }

        $info = init_db()->createCommand()->select($field)
            ->from($this->tablename_article)
            ->where($con,  $conarr)
            ->limit(1)
            ->queryRow();
        return $info;
    }

    //内容详细
    function getxixuncontentinfo($where,$field="*") {
        //拼接where条件
        $con = ''; $conarr = array();
        $con = "1=1";

        if(!empty($where['id']) && $where['id'] != 0){
            $con .= " AND id=:id";
            $conarr[":id"] = $where['id'];
        }

        $info = init_db()->createCommand()->select($field)
            ->from($this->tablename_content)
            ->where($con,  $conarr)
            ->queryRow();
        return $info;
    }

    //修改文章点击量
    function edit($paper,$id){
        return init_db()->createCommand()->update($this->tablename_article,$paper,'id=:id',array(':id'=>$id));
    }
}

?>