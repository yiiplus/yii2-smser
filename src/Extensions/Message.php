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
namespace Moego\Sms\Extensions;

use Yii;
use Moego\Sms\SmsClient;
use Moego\Sms\Status;
use app\extensions\ApiException;

/**
 * Message 发短信
 *
 * @category  PHP
 * @package   Yii2
 * @author    吴晓举 <wuxiaoju@moego.com>
 * @copyright 2017-2018 北京聚说科技有限公司
 * @license   https://www.moego.com/licence.txt Licence
 * @link      http://www.moego.com
 */
class Message extends yii\base\Component
{
    /*
     * 短信模板集合
     */
    public $templates;
    
    /*
     * 短信模版id
     */
    public $productId;
    
    /*
     * 短信秘要
     */
    public $productKey;
    
    /*
     * rpc地址（host:port）
     */
    public $host;
    
    /*
     * kafka地址 (host:port)
     */
    public $kafka;
 
    /**
     * 发短信
     *
     * @param string $phone 电话号码
     *
     * @return array
     * @throws ApiException
     */
    public function sendMessage($phone, $code = 0)
    {
        // 未主动传人时，随机生成一个
        if ($code == 0) {
            $code = rand(1000, 9999);
        }
        
        $client = new SmsClient($this->productId, $this->productKey);
        
        $result = $client->setHost($this->host)
        ->setTemplateNo($this->templates['VerificationCodeTemplateId'])
        ->setTemplateParas(['code' => $code])
        ->setPhone($phone)
        ->request();
    
        switch ($result['code']) {
            case Status::STATUS_OK:
                return $code;
            case Status::STATUS_LIMIT:
                throw new ApiException(ApiException::USER_MESSAGE_LIMIT);
            default:
                return false;
        }
    }
}