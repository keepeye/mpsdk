<?php
/**
 * HttpClient.php.
 * @author keepeye <carlton.cheng@foxmail>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Keepeye\Mpsdk\Contracts;


interface HttpClient
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    public function get($url,$params=array());
    public function post($url,$params=array());
}