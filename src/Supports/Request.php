<?php
/**
 * Request.php.
 * @author keepeye <carlton.cheng@foxmail>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Keepeye\Mpsdk\Supports;

class Request
{
    /**
     * 获取参数
     *
     * @param $name
     * @param string $default
     * @return string
     */
    public function get($name,$default='')
    {
        return isset($_GET[$name])?$_GET[$name]:(isset($_POST[$name])?$_POST[$name]:$default);
    }

    /**
     * 获取原始post数据
     * @return string
     * @throws Exception
     */
    public function rawPostData()
    {
        $data = file_get_contents('php://input');
        //低于5.6，php://input可能为空(已被读取过)，可从备用方法$HTTP_RAW_POST_DATA获取
        if (empty($data)) {
            if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
                $data = $GLOBALS['HTTP_RAW_POST_DATA'];
            } else {
                throw new Exception("没有读取到消息xml，php://input或HTTP_RAW_POST_DATA都为空", 500);
            }
        }
        return $data;
    }

    /**
     * 获取当前完整url
     *
     * @return string
     */
    public function fullUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
            || $_SERVER['SERVER_PORT'] === 443) ? 'https://' : 'http://';
        if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])){
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        }else{
            $host = $_SERVER['HTTP_HOST'];
        }
        return $protocol.$host.$_SERVER['REQUEST_URI'];
    }
}