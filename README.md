# SMS

Support Yii.

Step1: 修改文件 `composer.json`， 添加：

```
	"repositories" : [
		{
			"type" : "vcs",
			"url" : "ssh://git@code.mocaapp.cn:22/services/service-sms-php-yii-sdk.git"
		}
	],
```

Step2: 安装, `composer require "moego/service-sms-php-yii-sdk"`

Step3: 完善配置文件 到`config/main.php` 配置文件:

```
	'sms' => [
        'class' => 'Moego\Sms\Extensions\Message',
        'productId' => '947',
        'productKey' => '73n59N01uW50M3DB',
        'templates' => [
            'VerificationCodeTemplateId' => '240',
        ],
        'host' => '127.0.0.1:18306', 
        'kafka' => ''      
    ],

```

Step4: 使用

