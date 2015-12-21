# mpsdk
一个微信公众号开发的sdk，借鉴 https://github.com/overtrue/wechat

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

###网页授权oauth

基本用法很简单，只需要在控制器执行之前加上下面的逻辑:

    $oauth = new \Keepeye\Mpsdk\OAuth($appId,$appSecret);
    if (!$_SESSION['openid']) {
        if ($user = $oauth->authorize('snsapi_userinfo')) {
            $_SESSION['openid'] = $user['openid'];
            $_SESSION['nickname'] = $user['nickname'];
            //...如果第二个参数是snsapi_base，那么这里只会取到openid
        } else {
            exit('用户禁止授权');
        }
    }

当然，这里有个陷阱，授权成功后url上是带有code参数的，如果用户将页面分享出去，再次访问这个地址会报code无效错误。

我个人建议这么处理：

    //控制器过滤器逻辑如下，我们把微信授权单独在一个控制器中处理
    if (!$_SESSION['openid']) {
        $_SESSION['referer'] = Request::fullUrl();//将当前url保存到session
        return Response::redirect('/auth/wxOauth');
    }

    //假设下面是 /auth/wxOauth 的部分代码
    if ($user = $oauth->authorize('snsapi_userinfo')) {
        $_SESSION['openid'] = $user['openid'];
        $_SESSION['nickname'] = $user['nickname'];
        //...如果第二个参数是snsapi_base，那么这里只会取到openid
        //授权成功以后就相当于登录成功，跳转回登录前的页面，这样就避免了url中残留code参数
        $referer = $_SESSION['referer'];
        unset($_SESSION['referer']);
        return Response::redirect($referer);
    } else {
        exit('用户禁止授权');
    }
