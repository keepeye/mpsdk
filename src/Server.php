<?php
/**
 * Server.php.
 * @author keepeye <carlton.cheng@foxmail>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Keepeye\Mpsdk;


use Keepeye\Mpsdk\Messages\BaseMessage;
use Keepeye\Mpsdk\Supports\Exception;
use Keepeye\Mpsdk\Supports\Request;
use Keepeye\Mpsdk\Supports\XML;
use Keepeye\Mpsdk\Supports\Crypt;
use Keepeye\Mpsdk\Message;

class Server
{
    protected $token;
    protected $appId;
    protected $encodingAESKey;
    public $request;
    public $input;
    public $encoding = false;
    public $listeners = array();

    public function __construct($token, $appId, $encodingAESKey = null)
    {
        $this->token = $token;
        $this->appId = $appId;
        $this->encodingAESKey = $encodingAESKey;
        $this->request = new Request();
    }

    /**
     * 验证消息签名
     *
     * @throws Exception
     */
    public function checkSignature()
    {
        $data = array(
            $this->token,
            $this->request->get('timestamp'),
            $this->request->get('nonce'),
        );
        $sign = $this->request->get('signature');
        sort($data, SORT_STRING);
        $signature = sha1(implode($data));
        if ($signature != $sign) {
            throw new Exception('signature错误', 400);
        }
        if ($echostr = $this->request->get('echostr')) {
            exit(strip_tags($echostr));
        }
    }

    public function getCrypter()
    {
        static $crypter;
        if (!is_null($crypter)) return $crypter;

        if (!$this->token || !$this->encodingAESKey) {
            throw new Exception('加密模式必须提供token和encodingAESKey');
        }
        return $crypter = new Crypt($this->appId, $this->token, $this->encodingAESKey);
    }

    /**
     * 解密消息
     *
     * @param $input
     * @return array
     * @throws Exception
     */
    public function decrypt($input)
    {
        return $this->getCrypter()->decryptMsg(
            $this->request->get('msg_signature'),
            $this->request->get('nonce'),
            $this->request->get('timestamp'),
            $input
        );
    }

    /**
     * 加密消息
     *
     * @param $input
     * @return string
     * @throws Exception
     */
    public function encrypt($input)
    {
        return $this->getCrypter()->encryptMsg(
            $input,
            $this->request->get('nonce'),
            $this->request->get('timestamp')
        );
    }

    /**
     * 解析xml
     *
     * @throws Exception
     */
    public function parseInput()
    {
        $xml = $this->request->rawPostData();
        $input = XML::parse($xml);
        //消息加密处理
        if ($this->request->get('encrypt_type') == 'aes') {
            $this->encoding = true;
            $input = $this->decrypt($xml);
        }
        $this->input = $input;
    }


    /**
     * 处理事件
     *
     * @return mixed|string
     */
    protected function handleEvent()
    {
        //有注册事件
        if (isset($this->listeners[$this->input['Event']])) {
            return call_user_func($this->listeners[$this->input['Event']],$this->input);
        }
        //是否注册了all事件
        if (isset($this->listeners['global'])) {
            return call_user_func($this->listeners['global'],$this->input);
        }
        //没有处理，返回空字符串
        return "";
    }

    /**
     * 生成回复内容
     *
     * @param $content
     * @return string
     * @internal param mixed $response
     *
     */
    protected function respond($content)
    {
        $return = "";
        if (empty($content)) {
            return $return;
        }
        //字符串类型,自动转化为text消息
        if (is_string($content)) {
            $response = Message::make('text')->with('content', $content);
        }
        if ($response instanceof BaseMessage) {
            //基本属性设置
            $response->with('from',$this->input['ToUserName'])
                ->with('to',$this->input['FromUserName']);
            //构建xml
            $return = $response->buildForReply();
            //加密模式处理
            if ($this->encoding) {
                $return = $this->getCrypter()->encryptMsg(
                    $return,
                    $this->request->get('nonce'),
                    $this->request->get('timestamp')
                );
            }
        }
        return $return;
    }

    /**
     * 处理请求
     *
     * @throws Exception
     */
    public function handle()
    {
        $this->checkSignature();
        $this->parseInput();
        //普通消息转为message事件
        if (!isset($this->input['Event'])) {
            $this->input['Event'] = 'message';
        }
        return $this->respond($content = $this->handleEvent());
    }

    /**
     * 绑定事件
     *
     * @param $eventName
     * @param $callback
     */
    public function on($eventName,$callback)
    {
        $this->listeners[$eventName] = $callback;
    }
}
