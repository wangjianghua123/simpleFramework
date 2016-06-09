<?php
/**
 * PHP SDK for weibo.com (using OAuth2)
 * 
 * @author Elmer Zhang <freeboy6716@gmail.com>
 */

class WeixinSDK
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $GetRequestCodeURL = 'https://open.weixin.qq.com/connect/qrconnect';
    
    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $GetAccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    
    /**
     * API根路径
     * @var string
     */
    protected $ApiBase = 'https://api.weixin.qq.com/';
    
    public $Token;
    
    public $AppKey = WB_AKEY;
    
    public $Callback = WB_CALLBACK_URL; 
    
    public $AppSecret = WB_SKEY; 
    
    public $GrantType = 'authorization_code';
    
    public function getRequestCodeURL()
    {

        $params = array(
                'appid' => $this->AppKey,          
                'redirect_uri'=>$this->Callback,
                'response_type'=>'code',
                'scope'=>'snsapi_login'
        );
        return $this->GetRequestCodeURL . '?' . http_build_query($params);
    }
    
    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     */
    public function getAccessToken($code, $extend = null){
        $params = array(
                'appid'     => $this->AppKey,
                'secret'    => $this->AppSecret,
                'grant_type'    => $this->GrantType,
                'code'          => $code,
        );
        
        //$data = $this->http($this->GetAccessTokenURL . '?' .http_build_query($params));
        $data = file_get_contents($this->GetAccessTokenURL . '?' .http_build_query($params));
        $result = json_decode($data,true);
        if($result['access_token']){
            $this->Token = $result;
        }
        return $this->Token;
    }

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api){
        /* 腾讯微博调用公共参数 */
        $params = array(
            'access_token'       =>$this->Token['access_token'],
            'openid'             => $this->openid(),
        );
        $url = $this->ApiBase . $api . '?' .http_build_query($params);
        $data = file_get_contents($url);
        return json_decode($data, true);
    }
    
    
    /**
     * 解析access_token方法请求后的返回值
     */
    function parseToken($result)
    {
        if($result['access_token'] && $result['expires_in']){
            $this->Token    = $result;
            return $result;
        } else
            return 0;
    }
    
    /**
     * 获取当前授权应用的openid
     */
    public function openid()
    {
        $data = $this->Token;
        if(!empty($data['openid']))
            return $data['openid'];
        else
            exit('没有获取到微信用户ID！');
    }    
    
}

?>

