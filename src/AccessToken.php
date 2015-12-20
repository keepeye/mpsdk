<?php
/**
 * AccessToken.php.
 * @author keepeye <carlton.cheng@foxmail>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Keepeye\Mpsdk;

use Keepeye\Mpsdk\Contracts\HttpClient;
use Keepeye\Mpsdk\Supports\Http;

class AccessToken implements Contracts\AccessToken
{
    const API_URL = "https://api.weixin.qq.com/cgi-bin/token";

    protected $appId;
    protected $appSecret;
    /**
     * @var HttpClient http客户端
     */
    protected $http;

    public function __construct($appId=null,$appSecret=null,HttpClient $http=null)
    {
        $this->setAppId($appId);
        $this->setAppSecret($appSecret);
        $this->setHttp($http?:new Http());
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $params = array(
            'grant_type' => 'client_credential',
            'appid' => $this->getAppId(),
            'secret' => $this->getAppSecret()
        );
        $result = $this->http->get(self::API_URL,$params);
        return $result['access_token'];
    }

    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
        return $this;
    }

    public function getAppSecret()
    {
        return $this->appSecret;
    }

    public function setHttp(HttpClient $http)
    {
        $this->http = $http;
        return $this;
    }

    public function getHttp()
    {
        return $this->http;
    }
}
