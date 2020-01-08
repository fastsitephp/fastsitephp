<p align="center">
	<img src="https://raw.githubusercontent.com/fastsitephp/fastsitephp/master/website/public/img/FastSitePHP_Rocketship.png" alt="FastSitePHP">
</p>

# :star2: Bem vindo ao FastSitePHP!

**Obrigado pela visita!**

_Se está vendo esta mensagem então você é um dos primeiros visitantes!_ 🌠👍

FastSitePHP é um novo framework web que usa PHP. FastSitePHP foi projetado para desempenho rápido, flexibilidade de código, estabilidade a longo prazo, fácil utilização e uma melhor experiência geral de desenvolvimento. FastSitePHP é também mínimo em tamanho tornando-o rápido para baixar e fácil para começar sua utilização. Mesmo sendo novo (primeira publicação em November de 2019) FastSitePHP foi escrito durante vários anos e é extremamente estável contendo um grande número de testes de unidade.

Este repositório contém o framework FastSitePHP’s e o Website principal.

O FastSitePHP inclui muitos componentes independentes que podem ser utilizados sem o objeto principal da aplicação ou framework sendo fácil utilizá-lo com outros frameworks PHP em projetos.

## :dizzy: Por que utilizar o FastSitePHP?

|<img src="https://github.com/fastsitephp/fastsitephp/blob/master/website/public/img/icons/Performance.svg" alt="Grande Desempenho" width="60">|<img src="https://github.com/fastsitephp/fastsitephp/blob/master/website/public/img/icons/Lightswitch.svg" alt="Fácil de configurar e utilizar" width="60">|
|---|---|
|**Grande Desempenho** Com o FastSitePHP páginas complexas podem ser geradas em milésimos de segundo usando somente uma pequena quantidade de memória. Esse nível de desempenho até permite que sites sejam rápidos em máquinas não poderosas.|**Fácil de configurar** O FastSitePHP é desenvolvido de forma que sua configuração seja fácil em qualquer SO, que seu código seja de fácil leitura, de fácil utilização em desenvolvimento e muito mais. Como FastSitePHP sites e apps de alta qualidade podem ser desenvolvidos em um ritmo rápido usando poucas linhas de código e configuração mínima.|

|<img src="https://github.com/fastsitephp/fastsitephp/blob/master/website/public/img/icons/Samples.svg" alt="Rápido de aprender e depurar" width="60">|<img src="https://github.com/fastsitephp/fastsitephp/blob/master/website/public/img/icons/Security-Lock.svg" alt="Segurança Robusta" width="60">|
|---|---|
|**Rápido de aprender e depurar** O FastSitePHP é bem documentado e vem com exemplos práticos. O FastSitePHP provê mensagens de erro amigáveis fazendo com que erros possam ser corrigidos rapidamente mesmo se você tiver pouca ou nenhuma experiência com PHP.|**Securança Robusta** A segurança foi cuidadosamente planejada em todas as funcionalidades do FastSitePHP de forma que ele é seguro e de fácil utilização. As funcionalidades de segurança incluem criptografia (texto, objetos e arquivos files), cookies assinados, JWT, CORS, validação de servidores Proxy, Rate Limiting e mais.|

## :rocket: Teste isso online!

O site principal do FastSitePHP fornece um local para desenvolvimento o Code Playground onde você pode utilizar PHP, HTML, JavaScript, CSS e mais. Não há nada para installar e você pode trabalhar diretamente com PHP no servidor. Se você nunca teve contato com PHP antes esta é um boa maneira de aprender PHP.

[https://www.fastsitephp.com/en/playground](https://www.fastsitephp.com/en/playground)

<p align="center">
<img src="https://github.com/fastsitephp/static-files/raw/master/img/screenshots/Playground.png" alt="Code Playground do FastSitePHP">
</p>

## :rocket: Começando

**Começar com PHP e o FastSitePHP é extemamente fácil.** Se não tiver PHP instalado siga as instruções para Windows, Mac e Linux on the getting started page:
<a href="https://www.fastsitephp.com/en/getting-started" target="_blank">https://www.fastsitephp.com/en/getting-started</a>

Assim que o PHP estiver instalado você pode rodar o site da linha de comando como mostrado abaixo ou se você utiliza um editor de código ou IDE [Visual Studio Code, GitHub Atom, etc] então você pode rodar o site diretamente. Veja a página acima Começando para mais.

### Baixe e rode o site principal e o framework completo (~1.2 mb)

~~~
# Baixe este Repositório
cd {root-directory}
php -S localhost:3000
~~~

Para incluir suporte a renderização de documentos markdown à partir do servidor ou para funções de criptografia com versões mais antigas do PHP (PHP5) antes de qualquer coisa execute o script de instalação.

~~~
cd {root-directory}
php ./scripts/install.php
~~~

### Instalar utilizando o Composer (Gerenciador de Pacotes e/ou dependências PHP) (~470 kb)

O framework FastSitePHP pode também ser instalado usando o Composer. Quando instalado via Composer somente os arquivos essenciais são incluídos e não o repositório completo com o site principal. O tamanho dos arquivos baixados é pequeno, então é rápido incluí-lo em um projeto já existente ou usá-lo para iniciar novos projetos. Quando instalado com o auxilio do Composer as classes do FastSitePHP podem ser utilizadas com outros frameworks PHP como Symfony, Laravel, Zend.

~~~
composer require fastsitephp/fastsitephp
~~~

### Comece com um Site Inicial (~32 kb)

Um site inicial também existe e inclui vários páginas de exemplo e fornece uma estrutura básica de diretório/arquivo. É pequeno e rápido para configurar.

[https://github.com/fastsitephp/starter-site](https://github.com/fastsitephp/starter-site)

<p align="center">
<img src="https://github.com/fastsitephp/static-files/raw/master/img/starter_site/2019-06-17/home-page.png" alt="Site inicial FastSitePHP" width="500">
</p>

## :page_facing_up: Código de Exemplo

```php
<?php

// -------------------------------
// Configurar
// -------------------------------

// Configure um PHP Autoloader
// Isso permite que classes sejam dinamicamente carregadas
require '../../../autoload.php';

// Ou um site mínimo possuindo somente 2 arquivos como requisito de inclusão
// require '../vendor/fastsitephp/src/Application.php';
// require '../vendor/fastsitephp/src/Route.php';

// Crie o objeto da aplicação com tratamento de erros e utilizando o fuso horário UTCs
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// -------------------------------
// Definindo Rotas
// -------------------------------

// Envie uma resposta 'Olá Mundo!' para requisições padrão
$app->get('/', function() {
    return 'Olá Mundo!';
});

// Envie uma resposta 'Olá Mundo!' para a URL '/hello' ou no caso da
// variável opcional [name], escapar com segurança e retornar uma
// mensagem como nome
// (exemplo: '/hello/FastSitePHP' mostrará 'Olá FastSitePHP!')
$app->get('/hello/:name?', function($name = 'Mundo') use ($app) {
    return 'Olá ' . $app->escape($name) . '!';
});

// Envie uma Resposta JSON que contenha um Objeto com informação
// básica do site
$app->get('/site', function() use ($app) {
    return [
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    ];
});

// Envie uma resposta JSON que contenha informações básicas da requisição
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

// Envie o conteúdo  deste arquivo como uma resposta em texto puro
// usando cabeçalhos HTTP Response que da permissão ao usuário para
// armazenar a página em cache até o arquivos ser modificado
$app->get('/cached-file', function() {
    $file_path = __FILE__;
    $res = new \FastSitePHP\Web\Response();
    return $res->file($file_path, 'text', 'etag:md5', 'private');
});

// Retorne o endereço de IP do usuário como um serviço web JSON que suporta CORS
// (Cross-Origin Resource Share - Compartilhamento de Recursos de Origem Cruzada)
// e especificamente diz ao navegador para não armazenar em cache os resultados.
// Neste exemplo assume-se que servidor web está atrás de um servidor proxy (por
// exemplo um Balanceador de Carga) e o endereço de IP é lido de forma segura.
// Além disso a função cors() é chamado à partir de um função filtro que somente
// é chamada se a rota é equivalente e permite o correto tratamento de uma
// requisição OPTIONS.

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

// Define uma função que retorna verdadeiro (true) se a requisição web
// está vindo de uma rede local (por exemplo 127.0.0.1 ou 10.0.0.1). Esta
// função será utilizada em um filtro para mostar ou ocultar rotas.
$is_local = function() {
    // Compare a requisição e IP usando Classless Inter-Domain Routing (CIDR)
    $req = new \FastSitePHP\Web\Request();
    $private_ips = \FastSitePHP\Net\IP::privateNetworkAddresses();

    return \FastSitePHP\Net\IP::cidr(
        $private_ips,
        $req->clientIp('from proxy')
    );
};

// Fornece informações de ambiente detalhadas do PHP para usuários requisitando
// a página de uma rede local. Se a requisição está vindo de alguém na Internet
// então uma resposta 404 'Página não encontrada' seria retornada. Chamando [phpinfo()]
// produz uma resposta HTML para que a rota não precise retornar nada.
$app->get('/phpinfo', function() {
    phpinfo();
})
->filter($is_local);

// Fornece uma em texto com informações do servidor para usuários locais
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

// Se a URL requisitada começa com '/examples' então carregue um arquivo
// para a rota correspondente à partir do diretório atual. Este é um arquivo
// real que fornece muitos outros exemplos. Se você baixar este site, este código
// e outros exemplos podem ser encontrados em [app_data/sample-code].
$app->mount('/examples', 'home-page-en-examples.php');

// -------------------------------
// Rodar a aplicação
// -------------------------------
$app->run();
```

## :handshake: Contribuindo

**Todas as contribuições são bem vindas.** Para mudanças significativas incluindo novas classes, mudanças disruptivas no código existente, atualizando gráficos e arquivos, por favor abra um pedido antes para discutir o que você gostaria de mudar. Alguns exemplo de itens para contribuir:

* Erros de digitação e gramática - Se vir algum por favor corrija e envie.
* Adicionando mais páginas de demonstração - As páginas de demonstração normalmente utilzam mais HTML, CSS e JavaScript que PHP, então se você é um desenvolvedor web e não sabe PHP pode facilmente aprender durante o desenvolvimento.
* Testes de unidade adicionais e metodologias de teste
* Documentação e Tutoriais adicionais
* Classes e funcionalidades adicionais
* Novas ideias - Se você tem ideias de como melhorar por favor abra um pedido para que possamos discutir.

O arquivo [docs/to-do-list.txt](https://github.com/fastsitephp/fastsitephp/blob/master/docs/to-do-list.txt) contém a lista completa de itens pendentes e é um lugar para começar.

## :moneybag: Procura-se Tradutores Remunerados!

**Você é fluente em Inglês e outra língua? <a href="https://www.fastsitephp.com/en/translators-needed" target="_blank">Se sim por favor entre em contato</a>.**

FastSitePHP está procurando por tradutores pagos para que possa ser traduzido para vários idiomas rapidamente. Traduções podem ser feitas em várias etapas assim, mesmo se você tiver somente uma ou duas horas será o suficiente para ajudar e será pago por isso.

Idiomas atualmente precisando de tradutores: Arabic, Chinese Simplified (zh-Hans), French, German, Italian, Japanese, Korean, Persian, Russian

## :question: FAQ

**Por o FastSitePHP foi Criado?**

O código essencial do FastSitePHP foi iniciado em 2013 quado o o autor estava desenvolvendo um website usando PHP. Originalmente frameworks PHP populares foram comparados, testados e um foi inicialmente escolhido. No entanto na época (<a href="https://www.techempower.com/benchmarks/" target="_blank"> e mesmo agora na maior parte</a>) a maioria dos frameworks PHP eram extremamente lentos comparados a frameworks em outras linguagens e ao próprio PHP.

Enquanto o site era desenvolvido o framework e componentes foram sendo substituídos um por um por classes individuais e assim que todos os frameworks e classes de terceiros foram removidos o site teve um desempenho sessenta vezes (10x) mais rápido, usando um décimo (1/10) da memória, alcançando uma pontuação de 100 no Google Speed Test e erros inesperados de servidor se foram. Então durante o período de 6 anos o código principal foi desenvolvido no FastSitePHP.

**Eu já sei JavaScript/Node, Python, C#, Java etc. Por que eu deveria aprender PHP?**

* PHP é a mais amplamente utilizada linguagem de programação no mundo para websites dinâmicos em server-side; Isso inclui muitos dos mais populares sites no mundo.
* O PHP tem grande documentação e uma grande comunidade de desenvolvedores que faz com que aprender e encontrar recursos seja fácil.
* Suporte a banco de dados já integrado. Todos os principais fornecedores (Microsoft, Oracle, etc) tem dado suporte ao PHP por anos com extensões de banco de dados nativas de alto desempenho.
* Funciona em qualquer ambiente. A mais recente versão do  PHP pode funcionar virtualmente em qualquer servidor ou computador. Isso inclui Windows IIS, Linux/Apache, Raspberry Pi e mesmo em servidores IBM legados.
* Desenvolvimento e configuração de servidor rápidos - simplesmente faça mudanças no arquivo PHP e recarregue a página. Não há processo de compilação para compilar programas e serviços para parar e reiniciar quando forem feitas mudanças.
* Aprender uma linguagem adicioal permite aprender novas ideias e conceitos e melhora suas habilidades gerais.
* Renda - mais linguagens = mais dinheiro e um melhor currículo. Enquanto em média PHP paga menos que muitas outras linguagens populares; sites grandes e sites que dependem de empresas de design geralmente pagam mais _(alta renda)_ por desenvolvimento em PHP. Tendo PHP em seu currículo permite novas oportunidades. Além disso se você está pagando desenvolvedores para desenvolverem um site em PHP isso pode resultar em um site mais em conta.

**Qual o tamanho do FastSitePHP?**

- **Framework** (~19,000 linha de código PHP, ~470 kb como um arquivo zip)
- **Testes de Unidade** (~25,000 linhas de código)

**Quais versões do PHP são suportadas?**

Todas as versões do PHP da 5.3 a 7.4.

## :memo: Licença

Este projeto está sob o licenciamento **MIT** - veja o arquivo da [LICENÇA](LICENSE) para detalhes.

Arte (SVG Files) localizadas em [website/public/img] e [website/public/img/icons] estão licenciadas sob duplo licenciamento **MIT License** e <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" style="font-weight:bold;">Creative Commons Attribution 4.0 International License</a>.
