# SMS

Support Yii.

Step1: 安装, `composer require "yiiplus/yii2-smser"`

Step2: 完善配置文件 到`config/main.php` 配置文件:

```
	'sms' => [
        'class' => 'Moego\Sms\Extensions\Message',
        'productId' => 'xxxxxx',
        'productKey' => 'xxxxxxxxx',
        'templates' => [
            'VerificationCodeTemplateId' => 'xxx',
        ],
        'host' => 'xxxxxxx', 
        'kafka' => ''      
    ],

```

Step3: 使用

