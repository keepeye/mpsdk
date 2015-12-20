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