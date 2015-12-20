<?php
/**
 * OAuth.php.
 * @author keepeye <carlton.cheng@foxmail>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Keepeye\Mpsdk;

use Keepeye\Mpsdk\Supports\Request;
use Keepeye\Mpsdk\Contracts\HttpClient;
use Keepeye\Mpsdk\Supports\Http;

class OAuth
{
    const SCOPE_BASE = 'snsapi_base';
    const SCOPE_USERINFO = 'snsapi_userinfo';
    const API_GET_USERINFO           = 'https://api.weixin.qq.com/sns/userinfo';
    const API_GET_TOKEN      = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const API_REFRESH_TOKEN  = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
    const API_VALIDATE_TOKEN = 'https://api.weixin.qq.com/sns/auth';
    const API_CONNECT            = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    protected $appId;
    protected $appSecret;
    protected $request;
    protected $user;
    protected $http;

    /**
     * @param $appId
     */
    public function __construct($appId,$appSecret,HttpClient $http=null)
    {
        $this->setAppId($appId);
        $this->setAppSecret($appSecret);
        $this->request = new Request();
        $this->http = $this->setHttp($http?:new Http());
    }

    /**
     * 跳转授权url组装
     *
     * @param string $scope
     * @param string $state
     * @return string
     */
    public function url($scope=self::SCOPE_BASE,$state='STATE')
    {
        $redirect = $this->request->fullUrl();
        $params = array(
            'appid'         => $this->appId,
            'redirect_uri'  => $redirect,
            'response_type' => 'code',
            'scope'         => $scope,
            'state'         => $state,
        );
        return self::API_CONNECT.'?'.http_build_query($params).'#wechat_redirect';
    }

    /**
     * 跳转授权
     *
     * @param string $scope
     * @param string $state
     */
    public function connect($scope=self::SCOPE_BASE,$state='STATE')
    {
        header('Location:'.$this->url($scope, $state));
        exit;
    }

    /**
     * 获取access_token
     *
     * @param $code
     * @return bool|mixed
     */
    public function getAccessToken($code)
    {
        $params = array(
            'appid'      => $this->appId,
            'secret'     => $this->appSecret,
            'code'       => $code,
            'grant_type' => 'authorization_code',
        );
        return $this->http->get(self::API_GET_TOKEN,$params);
    }

    /**
     * 获取用户基本信息
     *
     * @param $openid
     * @param $accessToken
     * @return bool|mixed
     */
    public function getUserInfo($openid,$accessToken)
    {
        $params = array(
            'access_token' => $accessToken,
            'openid' => $openid,
            'lang' => 'zh_CN'
        );
        return $this->http->get(self::API_GET_USERINFO,$params);
    }

    /**
     * 获取授权用户
     *
     * @return array|bool|mixed
     */
    public function getUser()
    {
        if ($this->user || !$this->request->get('state') || !$code = $this->request->get('code')) {
            return $this->user;
        }
        $accessToken = $this->getAccessToken($code);
        if ($accessToken['scope'] !== 'snsapi_userinfo') {
            $user = array('openid' => $accessToken['openid']);
        } else {
            $user = $this->getUserInfo($accessToken['openid'],$accessToken['access_token']);
        }
        return $this->user = $user;
    }

    /**
     * 检查授权
     *
     * @param string $scope
     * @param string $state
     * @return mixed
     */
    public function authorize($scope=self::SCOPE_BASE,$state='STATE')
    {
        if (!$this->request->get('state') && !$this->request->get('code')) {
            $this->connect($scope, $state);
        }
        return $this->getUser();
    }

    /**
     * 刷新access_token
     *
     * @param $token
     * @return bool|mixed
     */
    public function refreshToken($token)
    {
        $params = array(
            'appid'         => $this->appId,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $token,
        );
        return $this->http->get(self::API_REFRESH_TOKEN, $params);
    }

    /**
     * 验证access_token是否有效
     *
     * @param $token
     * @param $openId
     * @return bool
     */
    public function validateToken($token,$openId)
    {
        $params = array(
            'openid'       => $openId,
            'access_token' => $token,
        );
        try {
            $this->http->get(self::API_VALIDATE_TOKEN, $params);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 设置appId
     *
     * @param $appId
     * @return $this
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    /**
     * 设置appsecret
     *
     * @param $secret
     * @return $this
     */
    public function setAppSecret($secret)
    {
        $this->appSecret = $secret;
        return $this;
    }

    /**
     * 设置http客户端
     *
     * @param HttpClient $http
     * @return $this
     */
    public function setHttp(HttpClient $http)
    {
        $this->http = $http;
        return $this;
    }
}
