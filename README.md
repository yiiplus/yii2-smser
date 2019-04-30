Yii2-smser
=============
基于Yii2搭建的sms短信平台，集成了各大短信厂商扩展，接入更快，更全面。。

## 版本
|版本 |时间|
| ----- | ----- |
|1.0| 2019年5月1日

# 特点
- 支持内容短信，模版短信，内容语音，语音验证码
- 支持一个或多个备用代理器(服务商)。
- 支持代理器调度方案热更新，可随时更新/删除/新加代理器。
- 允许推入队列，并自定义队列实现逻辑(与队列系统松散耦合)。
- 内置国内主流服务商的代理器。
- [自定义代理器](#自定义代理器)和[寄生代理器](#寄生代理器)。

# 服务商

| 服务商 | 模板短信 | 内容短信 | 语音验证码 | 最低消费  |  最低消费单价 | 资费标准
| ----- | :-----: | :-----: | :------: | :-------: | :-----: | :-----:
| [创蓝235](http://)      | √ | √ | √ | ￥55(1千条) | ￥0.055/条 | [资费标准](http://www.yunpian.com/price.html)
| [Luosimao](http://luosimao.com)        | × | √ | √ | ￥850(1万条) | ￥0.085/条 | [资费标准](https://luosimao.com/service/sms#sms-price)
| [云片网络](http://www.yunpian.com)      | × | √ | √ | ￥55(1千条) | ￥0.055/条 | [资费标准](http://www.yunpian.com/price.html)
| [容联·云通讯](http://www.yuntongxun.com) | √ | × | √ | 充值￥500   | ￥0.055/条 | [资费标准](http://www.yuntongxun.com/price/price_sms.html)
| [SUBMAIL](http://submail.cn)           | √ | × | √ | ￥100(1千条) | ￥0.100/条 | [资费标准](https://www.mysubmail.com/chs/store#/message)
| [云之讯](http://www.ucpaas.com/)        | √ | × | √ | -- | ￥0.050/条 | [资费标准](http://www.ucpaas.com/service/sms.html)
| [聚合数据](https://www.juhe.cn/)        | √ | × | √ | -- | ￥0.035/条 | [资费标准](https://www.juhe.cn/docs/api/id/54)
| [阿里大鱼](https://www.alidayu.com/)    | √ | × | √ | -- | ￥0.045/条 | [资费标准](https://www.alidayu.com/service/price)
| [SendCloud](https://sendcloud.sohu.com/) | √ | × | √ | -- | ￥0.048/条 | [资费标准](https://sendcloud.sohu.com/price.html)
| [短信宝](http://www.smsbao.com/)          | × | √ | √ | ￥5(50条) | ￥0.040/条(100万条) | [资费标准](http://www.smsbao.com/fee/)
| [腾讯云](https://www.qcloud.com/product/sms) | √ | √ | √ | -- | ￥0.045/条 | [资费标准](https://www.qcloud.com/product/sms#price)
| [阿里云](https://www.aliyun.com/product/sms) | √ | × | × | -- | ￥0.045/条 | [资费标准](https://cn.aliyun.com/price/product#/mns/detail)

# 安装
安装此扩展的首选方法是通过 [composer](http://getcomposer.org/download/).

执行命令

```bash

composer require yiiplus/yii2-sms "^1.0.0"

```
或添加配置到项目目录下的composer.json

```
"require": {
    ...
    "yiiplus/yii2-sms": "^1.0.0",
    ...
}
```

# 快速上手
### 1. 配置
- 配置代理器所需参数

为你需要用到的短信服务商(即代理器)配置必要的参数。可以在组件配置中键为`agents`的数组中配置，示例如下：

```php
//example:
'sms' => [
  'class' => 'common\extensions\sms\SmsCommon',
  'agents' => [
      'Luosimao' => [
        'apikey' => 'your api key',
        'voiceApikey' => 'your voice api key',
       ],
       
      'YunPian' => [
            'apikey' => 'your api key',
       ],
        
      'SmsBao' => [
            'username' => 'your username',
            'password'  => 'your password'
       ]
  ]
]);
```

- 配置代理器调度方案

可以在组件配置中键为`scheme`的数组中配置，示例如下：

```php
'sms' => [
  'class' => 'common\extensions\sms\SmsCommon',
  'scheme' => [
       //被使用概率为2/3
      'Qcloud' => '20',
      
      //被使用概率为1/3，且为备用代理器
      'YunPian' => '10 backup',
      
      //仅为备用代理器
      'SmsBao' => '0 backup',
  ],
```

> **调度方案解析：**
> 如果按照以上配置，那么系统首次会尝试使用`Luosimao`或`YunPian`发送短信，且它们被使用的概率分别为`2/3`和`1/3`。
> 如果使用其中一个代理器发送失败，那么会启用备用代理器，按照配置可知备用代理器有`YunPian`和`SmsBao`，那么会依次调用直到发送成功或无备用代理器可用。
> 值得注意的是，如果首次尝试的是`YunPian`，那么备用代理器将会只使用`SmsBao`，也就是会排除使用过的代理器。

### 2. Example

```php
use yiiplus\sms\Sms;

// 接收人手机号
$to = '1882****3329';

// 短信模版
$templates = [
    'YunTongXun' => 'your_temp_id',
    'SubMail'    => 'your_temp_id'
];

// 模版数据
$tempData = [
    'code' => '87392',
    'minutes' => '5'
];

// 短信内容
$content = '【签名】这是短信内容...';

// 只希望使用模板方式发送短信，可以不设置content(如:云通讯、Submail、Ucpaas)
Sms::make()->to($to)->template($templates)->data($tempData)->send();

// 只希望使用内容方式发送，可以不设置模板id和模板data(如:短信宝、云片、luosimao)
Sms::make()->to($to)->content($content)->send();

// 同时确保能通过模板和内容方式发送，这样做的好处是可以兼顾到各种类型服务商
Sms::make()->to($to)
    ->template($templates)
    ->data($tempData)
    ->content($content)
    ->send();

// 语音验证码
Sms::voice('02343')->to($to)->send();
```

# API

## API - 发送相关

### Sms::make()

生成发送短信的sms实例，并返回实例。

```php
$sms = Sms::make();

//创建实例的同时设置短信内容：
$sms = Sms::make('【签名】这是短信内容...');

//创建实例的同时设置短信模版：
$sms = Sms::make('YunTongXun', 'your_temp_id');
//或
$sms = Sms::make([
    'YunTongXun' => 'your_temp_id',
    'SubMail' => 'your_temp_id',
    ...
]);
```

### Sms::voice()

生成发送语音验证码的sms实例，并返回实例。

```php
$sms = Sms::voice();

//创建实例的同时设置验证码
$sms = Sms::voice($code);
```

> - 如果你使用`Luosimao`语音验证码，还需用在配置文件中`Luosimao`选项中设置`voiceApikey`。
> - **语音文件ID**即是在服务商配置的语音文件的唯一编号，比如阿里大鱼[语音通知](http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.oORhh9&apiId=25445)的`voice_code`。
> - **模版语音**是另一种语音请求方式，它是通过模版ID和模版数据进行的语音请求，比如阿里大鱼的[文本转语音通知](http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.f04PJ3&apiId=25444)。

### type($type)

设置实例类型，可选值有`Sms::TYPE_SMS`和`Sms::TYPE_VOICE`，返回实例对象。

### to($mobile)

设置发送给谁，并返回实例。

```php
$sms->to('1828*******');

//兼容腾讯云
$sms->to([86, '1828*******'])
```

### template($agentName, $id)

指定代理器设置模版或批量设置，并返回实例。

```php
//设置指定服务商的模板id
$sms->template('YunTongXun', 'your_temp_id')
    ->template('SubMail', 'your_temp_id');

//一次性设置多个服务商的模板id
$sms->template([
    'YunTongXun' => 'your_temp_id',
    'SubMail' => 'your_temp_id',
    ...
]);
```

### data($key, $value)

设置模板短信的模板数据，并返回实例对象。

```php
//单个数据
$sms->data('code', $code);

//同时设置多个数据
$sms->data([
    'code' => $code,
    'minutes' => $minutes
]);
```

> 通过`template`和`data`方法的组合除了可以实现模版短信的数据填充，还可以实现模版语音的数据填充。

### content($text)

设置内容短信的内容，并返回实例对象。

> 一些内置的代理器(如SmsBao、YunPian、Luosimao)使用的是内容短信(即直接发送短信内容)，那么就需要为它们设置短信内容。

```php
$sms->content('【签名】这是短信内容...');
```

### code($code)

设置语音验证码，并返回实例对象。

### file($agentName, $id)

设置语音文件，并返回实例对象。

```php
$sms->file('Agent1', 'agent1_file_id')
    ->file('Agent2', 'agent2_file_id');

//或
$sms->file([
    'Agent1' => 'agent1_file_id',
    'Agent2' => 'agent2_fiile_id',
]);
```

### params($agentName, $params)

直接设置参数到服务商提供的原生接口上，并返回实例对象。

```php
$sms->params('Agent1', [
    'callbackUrl' => ...,
    'userData'    => ...,
]);

//或
$sms->params([
    'Agent1' => [
        'callbackUrl' => ...,
        'userData'    => ...,
    ],
    'Agent2' => [
        ...
    ],
]);
```

### all([$key])

获取Sms实例中的短信数据，不带参数时返回所有数据，其结构如下：

```php
[
    'type'      => ...,
    'to'        => ...,
    'templates' => [...],
    'data'      => [...], // template data
    'content'   => ...,
    'code'      => ...,   // voice code
    'files'     => [...], // voice files
    'params'    => [...],
]
```

### agent($name)

临时设置发送时使用的代理器(不会影响备用代理器的正常使用)，并返回实例，`$name`为代理器名称。

```php
$sms->agent('SmsBao');
```
> 通过该方法设置的代理器将获得绝对优先权，但只对当前短信实例有效。

### send()

请求发送短信/语音验证码。

```php
//会遵循是否使用队列
$result = $sms->send();

//忽略是否使用队列
$result = $sms->send(true);
```

> `$result`数据结构请参看[task-balancer](https://github.com/toplan/task-balancer)

# 自定义代理器

- step 1

可将配置项(如果有用到)加入到`config/phpsms.php`中键为`agents`的数组里。

```php
//example:
'sms' => [
  'class' => 'common\extensions\sms\SmsCommon',
  'agents' => [
    'Foo' => [
        'key' => 'your api key',
    ],
  ]
```

- step 2

新建一个继承`Yiiplus\PhpSms\Agent`抽象类的代理器类，建议代理器类名为`FooAgent`，建议命名空间为`Yiiplus\PhpSms`。

> 如果类名不为`FooAgent`或者命名空间不为`Yiiplus\PhpSms`，在使用该代理器时则需要指定代理器类

- step 3

实现相应的接口，可选的接口有:

| 接口           | 说明         |
| ------------- | :----------: |
| ContentSms    | 发送内容短信   |
| TemplateSms   | 发送模版短信   |
| VoiceCode     | 发送语音验证码 |
| ContentVoice  | 发送内容语音   |

