<?php
/**
 * BaseMessage.php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    overtrue <i@overtrue.me>
 * @copyright 2015 overtrue <i@overtrue.me>
 * @link      https://github.com/overtrue
 * @link      http://overtrue.me
 */

namespace Keepeye\Mpsdk\Messages;

use Keepeye\Mpsdk\Supports\XML;

/**
 * 消息基类
 *
 * @property string      $from
 * @property string      $to
 * @property string      $staff
 *
 * @method BaseMessage to($to)
 * @method BaseMessage from($from)
 * @method BaseMessage staff($staff)
 * @method array       toStaff()
 * @method array       toReply()
 * @method array       toBroadcast()
 */
abstract class BaseMessage
{
    protected $attributes = array();

    /**
     * 生成用于主动推送的数据
     * @return array
     * @throws \Exception
     */
    public function buildForStaff()
    {
        if (!method_exists($this, 'toStaff')) {
            throw new \Exception(__CLASS__.'未实现此方法：toStaff()');
        }

        $base = array(
                 'touser'  => $this->to,
                 'msgtype' => $this->getDefaultMessageType(),
                );
        if (!empty($this->staff)) {
            $base['customservice'] = array('kf_account' => $this->staff);
        }

        return array_merge($base, $this->toStaff());
    }

    /**
     * 生成用于回复的数据
     * @return array
     * @throws \Exception
     */
    public function buildForReply()
    {
        if (!method_exists($this, 'toReply')) {
            throw new \Exception(__CLASS__.'未实现此方法：toReply()');
        }

        $base = array(
                 'ToUserName'   => $this->to,
                 'FromUserName' => $this->from,
                 'CreateTime'   => time(),
                 'MsgType'      => $this->getDefaultMessageType(),
                );

        return XML::build(array_merge($base, $this->toReply()));
    }

    /**
     * 获取默认的消息类型名称
     *
     * @return string
     */
    public function getDefaultMessageType()
    {
        $class = explode('\\', get_class($this));

        return strtolower(array_pop($class));
    }

    /**
     * 设置属性值，链式调用
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function with($name,$value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }


    /**
     * 魔术方法，获取属性
     *
     * @param $name
     * @return array|string
     */
    public function __get($name)
    {
        $getter = 'get'.ucfirst($name);
        if (method_exists($this,$getter)) {
            return $this->$getter();
        }
        return isset($this->attributes[$name])?$this->attributes[$name]:"";
    }

    /**
     * 魔术方法，设置属性
     *
     * @param $name
     * @param $value
     */
    public function __set($name,$value)
    {
        $setter = 'set'.ucfirst($name);
        if (method_exists($this,$setter)) {
            $this->$setter($value);
        }
        $this->with($name,$value);
    }
}
