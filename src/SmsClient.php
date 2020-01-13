<?php
/**
 * 服务 - 短信服务
 *
 * PHP version 7
 *
 * @category  PHP
 * @package   Yii2
 * @author    吴晓举 <wuxiaoju@moego.com>
 * @copyright 2017-2018 北京聚说科技有限公司
 * @license   https://www.moego.com/licence.txt Licence
 * @link      http://www.moego.com
 */
namespace Moego\Sms;

/**
 * 短信服务
 * 支持rpc接口和kafka topic模式
 *
 * PHP version 7
 *
 * @category  PHP
 * @package   Yii2
 * @author    吴晓举 <wuxiaoju@moego.com>
 * @copyright 2017-2018 北京聚说科技有限公司
 * @license   https://www.moego.com/licence.txt Licence
 * @link      http://www.moego.com
 */
class SmsClient
{
    /**
     * app access_key_id
     * @var string
     */
    protected $accessKeyId = '';
    
    /**
     * app access_key_secret
     * @var string
     */
    protected $accessKeySecret = '';
    
    /**
     * sms service host:port
     * @var string
     */
    protected $host = '';
    
    /**
     * request time
     * @var integer
     */
    protected $requestTime = 0;
    
    /**
     * 执行的接口方法
     * @var string
     */
    protected $method = '1.0::App\Rpc\Lib\SmsInterface::send';
    
    /**
     * 发送时生成的验证token
     * @var string
     */
    protected $token = '';
    
    /**
     * 短信目标编码
     * @var string
     */
    protected $templateNo = '';
    
    /**
     * 接收短信的手机号码
     * @var integer
     */
    protected $phone = 0;
    
    /**
     * 模板参数
     * @var array
     */
    protected $templateParas = [];
    
    /**
     * 请求block状态
     * true：等待执行结果
     * false：发出请求即结束
     * @var string
     */
    protected $block = true;
    
    /**
     * 手机号所属地区
     * @var string
     */
    protected $region = 'CN';
    
    /**
     * 短信发送方法
     */
    public const METHOD_SMS_SEND = '1.0::App\Rpc\Lib\SmsInterface::send';
    
    /**
     * kafka短信接收topic
     */
    public const KAFKA_TOPIC_SMS_SEND = 'moego-sms-send';
    
    /**
     * RPC定界符
     */
    public const RPC_EOL = "\r\n\r\n";
    
    /**
     * 构造方法
     * @param string $accessKeyId
     * @param string $accessKeySecret
     */
    public function __construct(string $accessKeyId, string $accessKeySecret)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
    }
    
    /**
     * 设置短信服务地址
     * @param string $host 接口地址
     * @return \Moego\Sms\SmsClient
     */
    public function setHost(string $host)
    {
        $this->host = 'tcp://' . $host;
        return $this;
    }
    
    /**
     * 设置手机号所属地区
     * @param string $region
     * @return \Moego\Sms\SmsClient
     */
    public function setRegion(string $region)
    {
        $this->region = $region;
        return $this;
    }
    
    /**
     * 生成请求token
     * @return \Moego\Sms\SmsClient
     */
    private function token()
    {
        $requestTime = time();
        $this->token = md5($this->accessKeyId . '.' . $requestTime . '.' . $this->accessKeySecret);
        $this->requestTime = $requestTime;
    }
    
    /**
     * 发送请求获取请求结果
     * @throws Exception
     * @return array|mixed
     */
    public function request()
    {
        // 初始化socket连接
        $fp = stream_socket_client($this->host, $errno, $errstr);
        if (!$fp) {
            throw new \Exception("stream_socket_client fail errno={$errno} errstr={$errstr}");
        }
        
        // 构造token
        $this->token();
        
        $param = [];
        // 短信发送请求构造参数请求包体
        if ($this->method == self::METHOD_SMS_SEND) {
            $param = [
                // 参数
                $this->templateParas,
                // 应用accessKeyId
                $this->accessKeyId,
                // phone
                $this->phone,
                // 模板编号
                $this->templateNo,
                // token
                $this->token,
                // requestTime
                $this->requestTime,
                // region
                $this->region
            ];
        }
        
        // 请求公共包体
        $request = [
            "jsonrpc" => '2.0',
            "method" => $this->method,
            'params' => $param,
            'id' => '',
            'ext' => [],
        ];
        $data = json_encode($request) . self::RPC_EOL;
        
        // 发送请求
        $len = fwrite($fp, $data);
        // 非阻塞情况，写入完成直接返回
        if ($len == strlen($data) && !$this->block) {
            fclose($fp);
            return ['code' => Status::STATUS_OK, 'id' => 0];
        }
        
        // 接收返回流
        $result = '';
        while (!feof($fp)) {
            $tmp = stream_socket_recvfrom($fp, 1024);           
            if ($pos = strpos($tmp, self::RPC_EOL)) {
                $result .= substr($tmp, 0, $pos);
                break;
            } else {
                $result .= $tmp;
            }
        }       
        fclose($fp);
        
        // 解析请求返回值
        $result = json_decode($result, true);
        if (isset($result['result'])) {
            return $result['result'];
        }
        
        // 服务端异常
        return ['code' => Status::STATUS_FAIL, 'id' => 0];
    }
    
    public function kafka($kafkaHost, $topic)
    {
        
    }
    
    /**
     * 设置执行接口
     * @param string $method 请求接口
     * @return \Moego\Sms\SmsClient
     */
    public function method(string $method)
    {
        $this->method = $method;
        return $this;
    }
    
    /**
     * 设置模板号
     * @param string $templateNo 模板号
     * @return \Moego\Sms\SmsClient
     */
    public function setTemplateNo(string $templateNo)
    {
        $this->templateNo = $templateNo;
        return $this;
    }
    
    /**
     * 设置接收手机号码
     * @param string $phone 手机号码
     * @return \Moego\Sms\SmsClient
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
        return $this;
    }
    
    /**
     * 设置短信模板参数
     * @param array $paras 模板参数
     * @return \Moego\Sms\SmsClient
     */
    public function setTemplateParas(array $paras)
    {
        $this->templateParas = $paras;
        return $this;
    }
}