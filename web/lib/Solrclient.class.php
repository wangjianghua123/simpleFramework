<?php
/*
 * solr php client
 */
class Solrclient {
    private $updateData = array();
    private $deleteData = array();
    private $host = "localhost";
    private $port = "8983";
    private $core = "companys";
    public function __construct($upUrl="",$core=''){
	$this->core = $core ? $core : $this->core ;
        $this->updateUrl = 'http://' . $this->host . ':' . $this->port . '/solr/'.$this->core.'/update?commit=true';
        $this->updateUrl = $upUrl ? $upUrl : $this->updateUrl;
        $this->updateOptimizeUrl = 'http://' . $this->host . ':' . $this->port . '/solr/'.$this->core.'/update?optimize=true';
        $this->updateOptimizeUrl = $upUrl ? $upUrl : $this->updateOptimizeUrl;
        $this->searchUrl = 'http://' . $this->host . ':' . $this->port . '/solr/'.$this->core.'/';
    }
    /**
     * post提交solr更新
     * @param  $updatedata
     * @return mixed
     */
    public function solrUpdate($updatedata=""){
        $contentType = "Content-type:application/json";
        $response = $this->curlPost($this->updateUrl,$updatedata,1200,'',$contentType);
        return $response;
    }
    
    /**
     * solr优化索引
     * @param  $updatedata
     * @return mixed
     */
    public function solrOptimize($updatedata=""){
        $response = $this->curlPost($this->updateUrl,$updatedata,1200);
        return $response;
    }
    
    //将消息添加到数组(添加记录)
    public function addData($ids = array()){
        foreach($ids as $id){
            //模拟数据，添加记录或者覆盖记录
            $this->updateData[] = array(
                                    "id"=>$id,
                                    "content"=>"msg",
                                );
        }
        return $this;
    }


    //删除记录
    public function addDelete($ids = array()){
        foreach($ids as $id){
            $this->deleteData[] = '"delete":{"query":"id:'.$id.'"}';
        }
        return $this;
    }

    //更新记录
    public function addUpdateData($ids = array()){
        //模拟一组数据,用来描述更新所需数据格式
        $this->updateData[] = array(
                                "id"=>1,
                                "content" => array("set"=>"msg1"),
                            );
        return $this;
    }
    
    /*
     * 单个或者批量更新 只支持二维数组 如果是一维数组 就组装成 array($array)这样的
     * 如果是原子更新的话 {"job_id": "2001","uid":{"set":"0"}} uid就得拼装成这样传过来
     */
    public function update($data){
            $updateJson = json_encode($data);
            $response = $this->solrUpdate($updateJson);
            return json_decode($response,true);
    }

    public function deleteIndex($data){
            foreach($data as $key=>$id){
                    $deleteData[]= '"delete":{"query":"'.$key.':'.$id.'"}'; //$key字段名 $id字段值
            }
            $deleteJson = implode(",",$deleteData);
            $deleteJson = "{{$deleteJson}}";
            $response =  $this->solrUpdate($deleteJson);
            return json_decode($response,true);;
    }
    
    /*
     * 搜索数据 分页
     */
    public function searchPage($params = array()){
        $parameter['wt'] = "json";
        $parameter['indent'] = true;
        if(!empty($params['q'])){ //条件
            $parameter['q'] = $params['q'];
        }
        if(!empty($params['fq'])){ //多字段and查询
            foreach($params['fq'] as $fk=>$fv){
                $parameter['fq'][] = $fv;
            }
        }
        if(!empty($params['sort'])){ //分页开始 类似offset
            $parameter['sort'] = $params['sort'];
        }
        if(!empty($params['start'])){
            $parameter['start'] = $params['start'];
        }else{
            $parameter['start'] = 0;
        }
        
        if(!empty($params['rows'])){ //分页开始 类似limit
            $parameter['rows'] = $params['rows'];
        }else{
            $parameter['rows'] = 10;
        }
        
        if(false !== $params['facet']){  //facet
            $parameter['facet'] = $params['facet'];
            $parameter['facet.field'] = $params['facet.field'];
            if($params['facet.limit'] > 0){
                $parameter['facet.limit'] = $params['facet.limit'] ? $params['facet.limit'] : $parameter['rows'];
            }
        }
        
        if(!empty($params['fl'])){  //查询字段
            $parameter['fl'] = $params['fl'];
        }
        $searchUrl = $this->generateSearchUrl("select", $parameter);
        $contentType = "Content-type:application/json";
        $response = $this->curlPost($searchUrl, '',60, '', $contentType);
        $list = $data = array();
        $data = json_decode($response,true);
        if($data['responseHeader']['status'] === 0 && $data['response']['numFound'] > 0){ //代表有数据
            $list['docs'] = $data['response']['docs'];
            $list['nums'] = $data['response']['numFound'];
            if(!empty($data['facet_counts']['facet_fields'])){
                $facetmp = $facet = array();
                $facetmp = array_chunk($data['facet_counts']['facet_fields'][$parameter['facet.field']], 2);
                foreach ($facetmp as $fk=>$fv){
                   $facet[$fv[0]] = $fv[1];
                }
                $list['facet'] = $facet; 
            }
            unset($data);
        }
        return $list; 
    }

    /*
     * 搜索数据
     */
    public function search($params = array()){
        $parameter['wt'] = "json";
        $parameter['indent'] = true;
        if(!empty($params['q'])){ //条件
            $parameter['q'] = $params['q'];
        }
        if(!empty($params['fq'])){ //多字段and查询
            foreach($params['fq'] as $fk=>$fv){
                $parameter['fq'][] = $fv;
            }
        }
        if(!empty($params['sort'])){ //分页开始 类似offset
            $parameter['sort'] = $params['sort'];
        }
        if(!empty($params['start'])){
            $parameter['start'] = $params['start'];
        }else{
            $parameter['start'] = 0;
        }
        
        if(!empty($params['rows'])){ //分页开始 类似limit
            $parameter['rows'] = $params['rows'];
        }else{
            $parameter['rows'] = 10;
        }
        
        if(false !== $params['facet']){  //facet
            $parameter['facet'] = $params['facet'];
            $parameter['facet.field'] = $params['facet.field'];
            if($params['facet.limit'] > 0){
                $parameter['facet.limit'] = $params['facet.limit'] ? $params['facet.limit'] : $parameter['rows'];
            }
        }
        
        if(!empty($params['fl'])){  //查询字段
            $parameter['fl'] = $params['fl'];
        }
        $searchUrl = $this->generateSearchUrl("select", $parameter);
        $contentType = "Content-type:application/json";
        $response = $this->curlPost($searchUrl, '',60, '', $contentType);
        $list = $data = array();
        $data = json_decode($response,true);
        if($data['responseHeader']['status'] === 0 && $data['response']['numFound'] > 0){ //代表有数据
            $list = $data['response']['docs'];
            if(!empty($data['facet_counts']['facet_fields'])){
                $facetmp = $facet = array();
                $facetmp = array_chunk($data['facet_counts']['facet_fields'][$parameter['facet.field']], 2);
                foreach ($facetmp as $fk=>$fv){
                   $facet[$fv[0]] = $fv[1];
                }
                $list['facet'] = $facet; 
            }
            unset($data);
        }
        return $list; 
    }



    //生成搜索链接
    private function generateSearchUrl( $servlet, $params = array()){
        $queryString = http_build_query($params, null, "&");
        $queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);
        return $this->searchUrl . $servlet .'?'. $queryString;
    }

        
    
    //对象转数组
    private static function object2array($object = null){
        $object = is_object($object)?get_object_vars($object):$object;
        $object = (!$object)?"x":$object;
        if(is_array($object)){
            foreach ($object as $k=>$v){
                if(is_object($v))
                    $object[$k] = self::object2array($v);
            }
        }
        return $object;
    }
    
    
    private function curlPost($url, $post, $timeout=10, $charset='gb2312', $contentType = "") {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $header[] = 'Host:'.parse_url($url, PHP_URL_HOST);
        $header[] = $contentType?$contentType:'Content-type: application/x-www-form-urlencoded;charset='.$charset;
        $header[] = 'Content-Length:'.strlen($post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
//      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if($post){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $contents = curl_exec($ch);
        curl_close($ch);
        return $contents;
    }
   

}   
    


?> 