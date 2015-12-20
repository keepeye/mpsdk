# mpsdk
一个微信公众号开发的sdk

### AccessToken获取

access_token是一个凭证，公众号大部分接口都用到它，普通的用法如下：

    $token = new \Keepeye\Mpsdk\AccessToken($appId,$appSecret);//只需要传入appId和appSecret
    echo $token->get();//得到一长串字符串就是access_token了

考虑到多应用情况下access_token需要统一管理，这时候就不能通过appId和appSecret去获取了，那样会导致其他应用的access_token失效。

假设某公众号的access_token是统一通过一个内部http接口获取的

    GET http:://10.1.2.3/getAccessToken

那么你只需要自己新建一个类，实现接口 `Keepeye\Mpsdk\Contracts\AccessToken` 即可，例如

    class MyAccessToken implements \Keepeye\Mpsdk\Contracts\AccessToken
    {
        public function get()
        {
            return file_get_contents('http:://10.1.2.3/getAccessToken');
        }
    }

    $myAccessToken = new MyAccessToken();
    echo $myAccessToken->get();

之所以要约定接口，因为其他类依赖AccessToken对象，参考之后的接口。