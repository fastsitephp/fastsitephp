<?php

// -------------------------------
// Installer
// -------------------------------

// Configurer un chargeur automatique PHP
// Cela permet aux classes d'être chargées dynamiquement
require '../../../autoload.php';

// OU pour un site minimal, seuls les 2 fichiers suivants doivent être inclus
// require '../vendor/fastsitephp/src/Application.php';
// require '../vendor/fastsitephp/src/Route.php';

// Créer l'objet d'application avec gestion des erreurs et UTC 
// pour le fuseau horaire
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// -------------------------------
// Définir des itinéraires
// -------------------------------

// Envoyer une réponse de "Bonjour le Monde!" pour les demandes par défaut
$app->get('/', function() {
    return 'Bonjour le Monde!';
});

// Envoyer une réponse "Bonjour le Monde!" pour l'URL '/bonjour' ou
// dans le cas de la variable optionnelle [nom] échapper en toute sécurité
// et retourner un message avec le nom
// (exemple: '/bonjour/FastSitePHP' affichera 'Bonjour le FastSitePHP!')
$app->get('/bonjour/:nom?', function($nom = 'Monde') use ($app) {
    return 'Bonjour le ' . $app->escape($nom) . '!';
});

// Envoyer une réponse JSON contenant un objet avec
// des informations de base sur le site
$app->get('/site', function() use ($app) {
    return [
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    ];
});

// Envoyer une réponse JSON contenant des informations de demande de base
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

// Envoyer le contenu de ce fichier sous forme de réponse en texte brut
// à l'aide d'en-têtes de réponse HTTP qui permettent à l'utilisateur
// final de mettre en cache la page jusqu'à ce que le fichier soit modifié
$app->get('/cached-file', function() {
    $file_path = __FILE__;
    $res = new \FastSitePHP\Web\Response();
    return $res->file($file_path, 'text', 'etag:md5', 'private');
});

// Renvoyez l'adresse IP de l'utilisateur en tant que service Web JSON qui
// prend en charge le partage de ressources d'origine croisée (CORS) et
// indique spécifiquement au navigateur de ne pas mettre en cache les
// résultats. Dans cet exemple, le serveur Web est supposé être derrière un
// serveur proxy (par exemple un équilibreur de charge) et l'adresse IP est
// lue en toute sécurité à partir de celui-ci. De plus, la fonction cors() est
// appelée à partir d'une fonction de filtrage qui n'est appelée que si la
// route est mise en correspondance et permet un traitement correct d'une
// demande OPTIONS.
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

// Définissez une fonction qui renvoie true si la demande Web provient
// d'un réseau local (par exemple 127.0.0.1 ou 10.0.0.1). Cette fonction
// sera utilisée dans un filtre pour afficher ou masquer les itinéraires.
$is_local = function() {
    // Comparer l'IP de demande à l'aide du
    // Classless Inter-Domain Routing (CIDR)
    $req = new \FastSitePHP\Web\Request();
    $private_ips = \FastSitePHP\Net\IP::privateNetworkAddresses();

    return \FastSitePHP\Net\IP::cidr(
        $private_ips, 
        $req->clientIp('from proxy')
    );
};

// Fournissez des informations détaillées sur l'environnement à partir de PHP
// pour les utilisateurs qui demandent la page à partir d'un réseau local.
// Si la demande provient d'une personne sur Internet, une «Page non trouvée»
// de réponse 404 sera retournée. L'appel de [phpinfo()] génère une réponse
// HTML afin que la route n'ait rien à renvoyer.
$app->get('/phpinfo', function() {
    phpinfo();
})
->filter($is_local);

// Fournir une réponse texte avec des informations
// sur le serveur pour les utilisateurs locaux
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

// Si l'url demandée commence par '/ examples', chargez un fichier PHP pour
// les routes correspondantes depuis le répertoire courant. Il s'agit d'un
// vrai fichier qui fournit de nombreux autres exemples. Si vous téléchargez
// ce site, ce code et d'autres exemples peuvent être trouvés dans
// [app_data/sample-code].
$app->mount('/examples', 'home-page-en-examples.php');

// -------------------------------
// Exécutez l'application
// -------------------------------
$app->run();
