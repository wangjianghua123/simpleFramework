<?php
/**
 * HR下载站内简历订单管理
 */
!defined('IN_UC') && exit('Access Denied');
class stationmodel{
    private $tablename = 'kjy_station_order';
    
    /**
     * 根据用户编号获取订单信息
     * @param type $uid
     * @param type $fileds
     * @return type
     */
    function getorderbyuid($uid,$fileds="*"){
        $row = init_db()->createCommand()->select($fileds)
                ->from($this->tablename)
                ->where('`uid` =:uid',array(':uid'=>$uid))
                ->order("add_time DESC")
                ->limit(1)
                ->queryRow();
        return $row;
    }
    
    /**
     * 添加订单信息
     * @param type $data
     * @return type
     */
    function addorder($data){
        init_db()->createCommand()->insert($this->tablename, $data);
        return init_db()->getLastInsertID();
    }
    
    /**
     * 修改订单信息
     * @param type $data
     * @param type $id
     * @return type
     */
    function editorder($data,$id){
        return init_db()->createCommand()->update($this->tablename, $data, 'id=:id', array(':id' => $id));
    }
}

