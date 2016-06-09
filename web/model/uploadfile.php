<?php
/**
 * 上传文件MODEL(图片、简历附件等)
 */
!defined('IN_UC') && exit('Access Denied');
class uploadfilemodel{
    private $tablename_img = 'kjy_images';
    private $tablename_attach = 'kjy_attachment';

    /**
     * 根据ids获取图片信息
     * @param type $data
     * @param type $field
     * @return type
     */
    function getimageinfo($data, $field) {
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_img)
                ->where(array("in", "id", $data))
                ->queryAll();
        return $row;
    }
    
    /**
     * 根据id获取图片信息
     * @param type $id
     * @param type $field
     * @return type
     */
    function getimagebyid($where,$field = "*"){
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['id']) && $where['id'] != 0) {
            $con .= " AND id=:id";
            $conarr[":id"] = $where['id'];
        }
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_img)
                ->where($con,$conarr)
                ->limit(1)
                ->queryRow();
        return $row;
    }

    /**
     * 根据ids获取对应的图片
     * @param type $ids
     * @return type
     */
    function getimagebyids($idArr,$fields = "*") {
        $rows = init_db()->createCommand()->select($fields)
                ->from($this->tablename_img)
                ->where(array('in', 'id', $idArr))
                ->queryAll();
        return $rows;
    }
    
    /**
     * 根据id获取附件信息
     * @param type $data
     * @param type $field
     * @return type
     */
    function getfileinfo($data, $field="*") {
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_attach)
                ->where(array("in", "id", $data))
                ->queryAll();
        return $row;
    }
    
    function getfileinfobyid($where,$field = "*"){
        //拼接where条件
        $con = '';
        $conarr = array();
        $con .= "1=1";
        if (!empty($where['id']) && $where['id'] != 0) {
            $con .= " AND id=:id";
            $conarr[":id"] = $where['id'];
        }
        $row = init_db()->createCommand()->select($field)
                ->from($this->tablename_attach)
                ->where($con,$conarr)
                ->queryRow();
        return $row;
    }
    
    /**
     * 增加上传图片信息
     * @param type $paper
     * @return type
     */
    function imginsert($paper) {
        init_db()->createCommand()->insert($this->tablename_img, $paper);
        return init_db()->getLastInsertID();
    }
    
    /**
     * 修改上传图片信息
     * @param type $paper
     * @param type $id
     * @return type
     */
    function imgedit($paper,$id){
        return init_db()->createCommand()->update($this->tablename_img,$paper,'id=:id',array(':id'=>$id)); 
    }
    
    /**
     * 删除上传图片信息
     * @param type $id
     * @return type
     */
    function imgdelete($id){
        $imgpath = init_db()->createCommand()->select("imagepath")
                ->from($this->tablename_img)
                ->where('id=:id', array(':id' => $id))
                ->queryRow();
        $fullpath = ROOT_PATH.'/'.$imgpath["imagepath"];
        unlink($fullpath);
        return init_db()->createCommand()->delete($this->tablename_img,'id=:id',array(':id'=>$id));
    }
    
    /**
     * 增加上传附件信息
     * @param type $paper
     * @return type
     */
    function fileinsert($paper) {
        init_db()->createCommand()->insert($this->tablename_attach, $paper);
        return init_db()->getLastInsertID();
    }
    
    /**
     * 删除上传附件信息
     * @param type $paper
     * @return type
     */
    function filedelete($id) {
        $filepath = init_db()->createCommand()->select("filepath")
                ->from($this->tablename_attach)
                ->where('id=:id', array(':id' => $id))
                ->queryRow();
        $fullpath = ROOT_PATH.'/'.$filepath["filepath"];
        unlink($fullpath);
        return init_db()->createCommand()->delete($this->tablename_attach,'id=:id',array(':id'=>$id));
    }
}
?>
