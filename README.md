# 消息中间件

# 安装
基于composer安装

`php composer.phar require choate/xsexpress`

# 说明

## 消费者

### 消费者配置
```php
'xsexpress'        => [
    'class' => 'choate\xsexpress\XSExpress',
    'httpsqs' => [
        'class' => '\Httpsqs',
        ...
    ],
    'signatureKey' => 'test_express_signature',
]
```


### 消费者使用

```php
$xsexpress = Yii::$app->xsexpress;
while (($goods = $xsexpress->receipt('topic')) !== false) {
    // 业务逻辑
    // ...
    // 完成消费确认签名
    $xsexpress->signature($goods);
}
```

## 生产者

### 生产者配置

```php
'xsexpress'        => [
    'class' => 'choate\xsexpress\XSExpress',
    'httpsqs' => [
        'class' => '\Httpsqs',
        ...
    ],
    'db' => 'db',
]
```

### 生产者使用

```php
try {
    Yii::$app->xsexpress->ship('topic', ['test' => 123456]);
} catch (\Exception $e) {
    // 发货失败，可以进入本地队列重新推送
}
```

## 平台

### 守护进程配置

```php
'controllerMap' => [
    'express' => '\choate\xsexpress\controllers\ExpressController',
],
'components'          => [
    'xsexpress'        => [
        'class' => 'choate\xsexpress\XSExpress',
        'httpsqs' => [
            'class' => '\Httpsqs',
            ...
        ],
        'db' => 'db',
        'mutex' => 'fileMutex',
        'retryTime' => 300,
        'signatureKey' => 'test_express_signature',
    ],
],
```

### 平台使用

```php
./vendor/bin/yii express/recycle --appConfig=configPath
```
