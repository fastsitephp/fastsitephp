<?php

// -------------------------------
// Preparar
// -------------------------------

// Configurar un cargador automático de PHP
// Esto permite que las clases se carguen dinámicamente
require '../../../autoload.php';

// O para un sitio mínimo, solo se deben incluir los siguientes 2 archivos
// require '../vendor/fastsitephp/src/Application.php';
// require '../vendor/fastsitephp/src/Route.php';

// Cree el objeto de aplicación con manejo de errores y UTC para la zona horaria
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// -------------------------------
// Definir rutas
// -------------------------------

// Enviar una respuesta de '¡Hola Mundo!' para solicitudes predeterminadas
$app->get('/', function() {
    return '¡Hola Mundo!';
});

// Enviar una respuesta '¡Hola Mundo!' para la URL '/hola' o en el caso de
// la variable opcional [nombre], salga y devuelva un mensaje con el nombre
// de forma segura (ejemplo: '/hola/FastSitePHP' generará '¡Hola FastSitePHP!')
$app->get('/hola/:nombre?', function($nombre = 'Mundo') use ($app) {
    return '¡Hola ' . $app->escape($nombre) . '!';
});

// Enviar una respuesta JSON que contenga un objeto con
// información básica del sitio
$app->get('/site', function() use ($app) {
    return [
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    ];
});

// Enviar una respuesta JSON que contenga información básica de solicitud
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

// Envíe el contenido de este archivo como una respuesta de texto sin formato
// utilizando Encabezados de respuesta HTTP que permiten al usuario final
// almacenar en caché la página hasta que se modifique el archivo
$app->get('/cached-file', function() {
    $file_path = __FILE__;
    $res = new \FastSitePHP\Web\Response();
    return $res->file($file_path, 'text', 'etag:md5', 'private');
});

// Devuelva la dirección IP del usuario como un servicio web JSON que admite el
// uso compartido de recursos de origen cruzado (CORS) y le indica
// específicamente al navegador que no guarde en caché los resultados. En este
// ejemplo, se supone que el servidor web está detrás de un servidor proxy
// (por ejemplo, un equilibrador de carga) y la dirección IP se lee de forma
// segura. Además, la función cors () se llama desde una función de filtro
// que solo se llama si la ruta coincide y permite el manejo correcto de
// una solicitud de OPTIONS.
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

// Defina una función que devuelva verdadero si la solicitud web proviene de una
// red local (por ejemplo, 127.0.0.1 o 10.0.0.1). Esta función se usará en un
// filtro para mostrar u ocultar rutas.
$is_local = function() {
    // Compare la solicitud de IP utilizando
    // Classless Inter-Domain Routing (CIDR)
    $req = new \FastSitePHP\Web\Request();
    $private_ips = \FastSitePHP\Net\IP::privateNetworkAddresses();

    return \FastSitePHP\Net\IP::cidr(
        $private_ips, 
        $req->clientIp('from proxy')
    );
};

// Proporcione información detallada del entorno de PHP para los usuarios que
// soliciten la página desde una red local. Si la solicitud proviene de alguien
// en Internet, se devolverá una respuesta 404 "Page not found". Llamar a
// [phpinfo()] genera una respuesta HTML, por lo que la ruta no necesita
// devolver nada.
$app->get('/phpinfo', function() {
    phpinfo();
})
->filter($is_local);

// Proporcionar una respuesta de texto con información del
// servidor para usuarios locales
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

// Si la url solicitada comienza con '/examples', cargue un archivo PHP para
// las rutas coincidentes desde el directorio actual. Este es un archivo real
// que proporciona muchos más ejemplos. Si descarga este sitio, este código
// y otros ejemplos se pueden encontrar en [app_data/sample-code].
$app->mount('/examples', 'home-page-en-examples.php');

// -------------------------------
// Ejecuta la aplicación
// -------------------------------
$app->run();
