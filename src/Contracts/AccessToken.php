<?php
/**
 * AccessToken.php.
 * @author keepeye <carlton.cheng@foxmail>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Keepeye\Mpsdk\Contracts;


interface AccessToken
{
    /**
     * 获取token
     * @return string
     */
    public function get();
}