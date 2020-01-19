<?php

// -------------------------------
// Configuração
// -------------------------------

// Configure um autoloader de PHP
// Isso permite que classes sejam dinamicamente carregadas
require '../../../autoload.php';

// OU para um site mínimo somente é necessário incluir so 2 seguintes arquivos
// require '../vendor/fastsitephp/src/Application.php';
// require '../vendor/fastsitephp/src/Route.php';

// Crie o Objecto da Aplicação com Tratamento de Erro e UTC no fuso horário
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// -------------------------------
// Defina Rotas
// -------------------------------

// Envie uma resposta 'Olá Mundo!' para requisições padrão
$app->get('/', function() {
    return 'Hello World!';
});

// Envie uma resposta 'Olá Mundo!' para a URL '/hello' ou no caso da variável
// opcional [name]  variable safely escape and return a message with the name
// (exemplo: '/hello/FastSitePHP' mostrará 'Olá FastSitePHP!')
$app->get('/hello/:name?', function($name = 'Mundo') use ($app) {
    return 'Olá ' . $app->escape($name) . '!';
});

// Envie uma reposta JSON que contenha um objeto com informações básicas do Site
$app->get('/site', function() use ($app) {
    return [
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    ];
});

// Envie uma reposta JSON que contenha informações básicas de Requisição
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

// Envie o conteúdo deste arquivo como uma resposta em textopuro utilizando
// cabeçalos HTTP Response permitig que o usuário final faça um cache da
// página até que o arquivo seja modificado
$app->get('/cached-file', function() {
    $file_path = __FILE__;
    $res = new \FastSitePHP\Web\Response();
    return $res->file($file_path, 'text', 'etag:md5', 'private');
});

// Retorna o endereço de IP do usuário como um serviço web JSON que suporta
// Cross-Origin Resource Sharing (CORS) e diz especificamente para o navegador
// para não fazer cache dos resultados. Neste exemplo assume-se que o Servidor
// Web esteja atrás de um servidor proxy (por exemplo um Balanceador de Carga
// e o endereço de IP é lido de forma segura. Além disso a função cors() é
// é chamada de um função filtro a qual somente é chamada se a rota corresponder
// e permite tratar corretamente uma requisição OPTIONS.
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

// Define uma função que retorna true se a requisição web vier de uma rede
// local (por exemplo 127.0.0.1 ou 10.0.0.1). Esta função será utilizada em
// um filtro para mostrar ou ocultar rotas.
$is_local = function() {
    // Compare Request IP using Classless Inter-Domain Routing (CIDR)
    $req = new \FastSitePHP\Web\Request();
    $private_ips = \FastSitePHP\Net\IP::privateNetworkAddresses();

    return \FastSitePHP\Net\IP::cidr(
        $private_ips,
        $req->clientIp('from proxy')
    );
};

// Prove informações detalhadas do ambiente PHP para usuários requisitando a
// página de uma rede local. Se a requisição vier de alguém na Internet então
// uma repostas 404 'Página não encontrada' seria retornada. Chamando [phpinfo()]
// retorna um resposta HTML para que a rota não tenha que retornar alguma coisa.
$app->get('/phpinfo', function() {
    phpinfo();
})
->filter($is_local);

// Provê uma resposta em texto com informações do Servidor para usuários locais
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

// Se a URL requisitada inicia com '/examples' então carregue um arquivo PHP
// para as rotas correspondentes à partir do diretório atual. Este é um arquivo
// que provê muitos outros exemplos. Se você baixar este site, este código
// e outros exemplos podem ser encontrados em [app_data/sample-code].
$app->mount('/examples', 'home-page-pt-BR-examples.php');

// -------------------------------
// Roda a aplicação
// -------------------------------
$app->run();
