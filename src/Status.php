<?php declare(strict_types=1);
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
 * 常量定义
 *
 * 相关状态/常量定义
 *
 * @category  PHP
 * @package   Yii2
 * @author    吴晓举 <wuxiaoju@moego.com>
 * @copyright 2017-2018 北京聚说科技有限公司
 * @license   https://www.moego.com/licence.txt Licence
 * @link      http://www.moego.com
 */
class Status
{
    /**
     * 成功下发短信
     */
    public const STATUS_OK = 'OK';
    
    /**
     * 达到用户发送上限
     */
    public const STATUS_LIMIT = 'USER.LIMIT';
    
    /**
     * 所选通道下发失败
     */
    public const STATUS_FAIL = 'FAIL';
    
    /**
     * TOKEN非法
     */
    public const STATUS_TOKEN_INVALID = 'INVALID';
    
    /**
     * 手机参数填写错误
     */
    public const STATUS_MOBILE_NUMBER_ILLEGAL = 'PHONE.ILLEGAL';
    
    /**
     * 无可用通道信息
     */
    public const STATUS_NO_CHANNEL_AVAILABLE = 'CHANNEL.NO_AVAILABLE';
}
