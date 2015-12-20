<?php
/**
 * Http.php.
 * @author keepeye <carlton.cheng@foxmail>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Keepeye\Mpsdk\Supports;

use Keepeye\Mpsdk\Contracts\HttpClient;

class Http implements HttpClient
{
    public function send($url, $method = self::METHOD_GET, $params = array())
    {
        if ($method === self::METHOD_GET) {
            $url .= (stripos($url, '?') ? '&' : '?') . http_build_query($params);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if ($method == self::METHOD_POST) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        $res = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);
        return $this->parseResponse($res);
    }

    public function parseResponse($res)
    {
        if (empty($res)) {
            throw new Exception('请求微信接口未响应');
        }
        $contents = json_decode($res,true);
        if (isset($contents['errcode']) && 0 !== $contents['errcode']) {
            if (empty($contents['errmsg'])) {
                $contents['errmsg'] = 'Unknown';
            }
            throw new Exception($contents['errmsg'], $contents['errcode']);
        }
        if ($contents === array('errcode' => '0', 'errmsg' => 'ok')) {
            return true;
        }
        return $contents;
    }
}
