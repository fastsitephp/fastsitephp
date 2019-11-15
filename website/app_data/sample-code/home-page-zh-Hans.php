<?php

// -------------------------------
// 设定
// -------------------------------

// 设置PHP自动加载器
// 这样可以动态加载类
require '../../../autoload.php';

// 或对于最小的站点，仅需要包含以下两个文件
// require '../vendor/fastsitephp/src/Application.php';
// require '../vendor/fastsitephp/src/Route.php';

// 创建具有时区错误处理和UTC的应用程序对象
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// -------------------------------
// 定义路线
// -------------------------------

// 发送回复“ 你好，世界！” 对于默认请求
$app->get('/', function() {
    return '你好，世界！';
});

// 发送回复“ Hello World！” URL'/ hello'或在可选的[name]
// 变量的情况下安全地转义并返回带有该名称的消息
// （例如：'/ hello / FastSitePHP'将输出'你好，FastSitePHP！'）
$app->get('/hello/:name?', function($name = 'World') use ($app) {
    return '你好，' . $app->escape($name) . '!';
});

// 发送JSON响应，其中包含带有基本站点信息的对象
$app->get('/site', function() use ($app) {
    return [
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    ];
});

// 发送包含基本请求信息的JSON响应
$app->get('/request', function() {
    $req = new \FastSitePHP\Web\Request();
    return [
        'acceptEncoding' => $req->acceptEncoding(),
        'acceptLanguage' => $req->acceptLanguage(),
        'origin' => $req->origin(),
        'userAgent' => $req->userAgent(),
        'referrer' => $req->referrer(),
        'clientIp' => $req->clientIp(),
        'protocol' => $req->protocol(),
        'host' => $req->host(),
        'port' => $req->port(),
    ];
});

// 使用HTTP响应标头将此文件的内容作为纯文本响应发送，
// 允许最终用户缓存页面，直到文件被修改为止
$app->get('/cached-file', function() {
    $file_path = __FILE__;
    $res = new \FastSitePHP\Web\Response();
    return $res->file($file_path, 'text', 'etag:md5', 'private');
});

// 以支持跨域资源共享（CORS）的JSON Web服务的形式返回用户的IP地址，
// 并明确告知浏览器不要缓存结果。 在此示例中，假定Web服务器位于代理服务器
// （例如负载均衡器）之后，并且可以安全地从中读取IP地址。 另外，
// 从过滤器函数调用cors（）函数，该过滤器函数仅在路由匹配时才调用，
// 并允许正确处理OPTIONS请求。
$app->get('/whats-my-ip', function() {
    $req = new \FastSitePHP\Web\Request();
    return [
        'ipAddress' => $req->clientIp('from proxy', 'trust local'),
    ];
})
->filter(function() use ($app) {
    $app
        ->noCache()
        ->cors('*');
});

// 定义一个函数，如果Web请求来自本地网络（例如127.0.0.1或10.0.0.1），
// 则返回true。 此功能将在过滤器中用于显示或隐藏路线。
$is_local = function() {
    // 使用无类域间路由（CIDR）比较请求IP
    $req = new \FastSitePHP\Web\Request();
    $private_ips = \FastSitePHP\Net\IP::privateNetworkAddresses();

    return \FastSitePHP\Net\IP::cidr(
        $private_ips, 
        $req->clientIp('from proxy')
    );
};

// 为从本地网络请求页面的用户提供PHP的详细环境信息。 如果请求来自互联网上的某人，
// 则将返回404响应“找不到页面”。 调用[phpinfo（）]会输出HTML响应，
// 因此路由不需要返回任何内容。
$app->get('/phpinfo', function() {
    phpinfo();
})
->filter($is_local);

// 为本地用户提供带有服务器信息的文本响应
$app->get('/server', function() {
    $config = new \FastSitePHP\Net\Config();
    $req = new \FastSitePHP\Web\Request();
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Host: {$config->fqdn()}",
            "Server IP: {$req->serverIp()}",
            "Network IP: {$config->networkIp()}",
            str_repeat('-', 80),
            $config->networkInfo(),
        ]));
})
->filter($is_local);

// 如果请求的URL以'/ examples'开头，则从当前目录加载一个PHP文件以
// 查找匹配的路由。 这是一个真实的文件，提供了更多示例。 如果您下载此站点，
// 则可以在[app_data / sample-code]中找到此代码和其他示例。
$app->mount('/examples', 'home-page-zh-Hans-examples.php');

// -------------------------------
// 运行应用程序
// -------------------------------
$app->run();
