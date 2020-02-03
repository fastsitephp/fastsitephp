<?php

use FastSitePHP\Route;
// Este é um arquivo de rota funcional com muitos exemplos que é executado à partir de [home-page-en.php]
// Ao executar de um ambiente local a URL de uma rota será assim:
//     http://localhost:3000/FastSitePHP/website/app_data/sample-code/home-page-en.php/examples
// Este arquivo é também usado como um código fonte para páginas de documentação. Por exemplo
// a Página Referência Rápida [URL = '/:lang/quick-reference']. O código é organizado em
// blocos de comentário [EXAMPLE_CODE_START] e [EXAMPLE_CODE_END] os quais são utilizados pela classe
// [\App\Models\ExampleCode] ao carregar este arquivo.

// Se estiver rodando este arquivo diretamente, então, redirecione para o arquivo
// principal usando a rota do index '/examples' para este site.
if (!isset($app)) {
    $url = 'home-page-pt-BR.php/examples';
    header('Location: ' . $url, true, 302);
    exit();
}

// Esta página principal carrega o autoloader padrão do desenvolvimento. Se um
// autoloader de um fornecedor existir então inclua isso também. Alguns exemplos
// como [examples/logging] e [examples/markdown] não funcionarão a não ser que
// bibliotecas de terceiros sejam instaladas.
if (is_file('../../../vendor/autoload.php')) {
    include '../../../vendor/autoload.php';
}

// Exemplo de Roda Padrão
// Crie e mostre uma lista de todas as rotas
$app->get('/examples', function() use ($app) {
    // Obtém URLs para todas as Rotas
    $urls = [];
    foreach ($app->routes() as $route) {
        if ($route->pattern === '/hello/:name?') {
            $urls[] = '/hello/World';
            continue;
        } elseif ($route->pattern === '/examples/request-basic') {
            $urls[] = '/examples/request-basic?number=123';
            $urls[] = '/examples/request-basic?number=test';
            continue;
        }
        $urls[] = $route->pattern;
    }

    // Crie e Retorne HTML
    $html = [
        '<style>ul{list-style-type:none; padding:10px;} li{padding:10px;}</style>',
        '<ul>',
    ];
    foreach ($urls as $url) {
        $url = $app->rootUrl() . ltrim($url, '/');
        $html[] = sprintf('<li><a href="%s">%s</a></li>', $url, $url);
    }
    $html[] = '</ul>';
    return implode("\n", $html);
});

// Este bloco de comentário (e blocos similares) são para a página Referência Rápida:
/*
// EXAMPLE_CODE_START
// TITLE: Sintaxe do PHP - Visão Geral
<?php
// A sintaxe PHP é similar a sintaxe C so blocos [if], loops [for] e [while],
// [funções], [comentários] e mais são também similares a outras linguagens
// amplamente utilizadas como: JavaScript, C# e Java. Se você tiver alguma
// experiência com JavaScript é muito fácil começar a usar o PHP.

// Scripts PHP começam com [<?php] e linhas individuais devem ser finalizadas com
// um ponto e vírgula [;]. O comando [echo] exibe/imprime na tela um conteúdo.
// Salvando este exemplo em um arquivo e executando-o, simplesmente exibirá
// 'Olá Mundo!'.
echo 'Olá Mundo!';
// EXAMPLE_CODE_END
*/

$app->get('/examples/php-variables', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Sintaxe PHP - Variáveis
    // Em PHP todas as variáveis começam com um cifrão [$] seguido de um nome.
    // A criação de variáveis em seu primeiro uso Variáveis não precisam ser
    // declaradas com antecedência e são criadas em seu primeiro uso
    $value = 'Teste';

    // A tipagem de variáveis acontece dinamicamente no PHP; tipo de dados é
    // determinado pelo valor da variável e o tipo pode ser mudado.
    $value = (10 * 20); //valor
    $string = 'String'; //cadeia de texto
    $number = 123; //número
    $decimal = 123.456;
    $bool = true; //booleano
    $null = null; //nulo

    // Arrays podem ser definidos utilizando os caracteres [] como JavaScript
    // e outras linguagens quando em qualquer versão recente do PHP. Se estiver
    // utilizando versões anteriores (5.3 ou anterior) Arrays precisam ser
    // definidos utilizando a função [array()].
    $cities = ['Tóquio', 'São Paulo', 'Jacarta', 'Seul', 'Manila', 'Nova York']; //cidades

    // Uma vírgula extra pode ser inserida depois do último item
    $numbers = array(123, 456, 789,); //números

    // Os arrays PHP é na verdade um mapa ordenado (map) então eles são muitas
    // vezes utilizados como Dicionários, Hashes ou Arrays Associativos de outras
    // linguagens.
    $dias_do_mes = [
        'Janeiro' => 31,
        'Fevereiro' => 28,
        'Março' => 31,
        'Abril' => 30,
    ];

    // Objetos podem ser dinamicamente criados em PHP utilizando a classe
    // integrada [stdClass].
    $objeto = new \stdClass;
    $objeto->nome = 'FastSitePHP';
    $objeto->tipo = 'PHP Framework';

    // Objetos também podem ser criados dinamicamente fazendo casting de um array.
    $objeto2 = (object)[
        'nome' => 'FastSitePHP',
        'tipo' => 'PHP Framework',
    ];

    // Para verificar foi definida utilize a função [isset()]
    $definida1 = isset($objeto);  // true
    $definida2 = isset($objeto3); // false

    // Tipos adicionais incluem recursos como um arquivo e funções de callback.
    // EXAMPLE_CODE_END

    // Retorne Variáveis como JSON
    $app->json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
    return [
        'valor' => $value,
        'cities' => $cities,
        'numbers' => $numbers,
        'months_days' => $months_days,
        'object' => $object,
        'object2' => $object2,
        'defined1' => $defined1,
        'defined2' => $defined2,
    ];
});

$app->get('/examples/php-strings', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Sintaxe PHP - Strings
    // Similar a outras linguagens como JavaScript, Python e Ruby,
    // strings em PHP pode ser cercados por aspas simples ou aspas duplas
    $value = 'String em Aspas Simples';
    $value = "String em Aspas Duplas";

    // Para combinar ou concatenar strings utilize o caractere ponto [.]:
    $saudacao = 'Olá ' . 'Mundo';

    // Espaços não são obrigatórios entre o ponto [.] e as outras variáveis:
    $saudacao = 'Olá '.'Mundo';

    // Você pode adicionar algo a uma string utilizando o operador [.=]:
    $saudacao = 'Olá';
    $saudacao .= ' Mundo';

    // Similar ao Python e Ruby, aspas duplas expandem variáveis para
    // interpolação de texto (string). Isto imprime 'Olá Mundo':
    $nome = 'Mundo';
    $saudacao = "Olá ${name}";

    // Strings the múltiplas linhas utilizam [<<<] seguido por um
    // identificador definido pelo programador e finaliza a string com o
    // mesmo identificador iniciando em uma nova linha seguido de um [;].
    // Neste exemplo o identificador foi nomeado de [FDD] para Fim-Dos-Dados.
    // Strings multi linha utilizando esta sintaxe suportam interpolação
    // de strings.
    $multilinha1 = <<<FDD
Multi linha
Texto
${nome}
FDD;

    // Quando utiliza-se os caracteres ['] é similar a utilizar strings em
    // aspas simples então não há interpolação de string. O exemplo acima
    // imprime 'Mundo' ao invés de '${nome}' enquanto esta versão imprime
    // '${nome}'
    $multilinha2 = <<<'FDD'
Multi linha
Texto
${nome}
FDD;

    // Funções de String comuns utilizando esta string:
    $value = ' abcdefgh ';

    // Comprimento (length) de String e Trim
    $len = strlen($value);        // 10
    $len2 = strlen(trim($value)); // 8

    // Busca de String, frequentemente funções PHP retornam tipos de dados
    // diferentes. [strpos()] e [stripos()] são bons exemplos disso.
    // Se a string for encontrada, um número inteiro com a posição será retornado,
    // caso contrário, retornará um valor booleano falso.
    $pos = stripos($value, 'DEF'); // Sem diferenciação de maiúsculas e minúsculas = 4
    $pos2 = strpos($value, 'DEF'); // Cem diferenciação de maiúsculas e minúsculas = false

    // Dividir para um Array e unir um Array a uma String.
    // Ao invés de utilizar [split()/join()] PHP utiliza [explode/implode()].
    $value = '123,456,789';
    $array = explode(',', $value);
    $string = implode('_', $array);

    // Replace
    $texto = 'Azul e Vermelho';
    $busca = 'Vermelho';
    $substituir = 'Verde';
    $novo_valor = str_replace($busca, $substituir, $texto);

    // Internamente no PHP strings são implementadas como um array de bytes
    // então se você trabalha com arquivos ou dados binários você usa o
    // tipo de dado string. Isto pode apresentar um problem no entanto se
    // você precisa calcular o comprimento de uma string Unicode para um
    // usuário, encontrar a posição do caractere etc. To support codificações
    // diferentes o PHP inclui Funções de String Multibyte. Em termos gerais
    // elas tem os mesmos nomes e parâmetros que as outras funções de string
    // têm mas com o prefixo [mb_].
    $unicode = '测试';
    $ulen = strlen($unicode);     // 6
    $ulen2 = mb_strlen($unicode); // 2
    // EXAMPLE_CODE_END

    // Retorna Variáveis como JSON
    $app->json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
    return [
        'saudacao' => $saudacao,
        'multilinha1' => $multilinha1,
        'multilinha2' => $multilinha2,
        'len' => $len,
        'len2' => $len2,
        'pos' => $pos,
        'pos2' => $pos2,
        'array' => $array,
        'string' => $string,
        'novo_valor' => $novo_valor,
        'ulen' => $ulen,
        'ulen2' => $ulen2,
    ];
});

$app->get('/examples/php-logic', function() use ($app) {
    // Conteúdo gerado como texto plano
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: Sintaxe PHP - Declaração Lógica
    // Este exemplo gera dados para o cliente assim que validados:
    //    var_dump() = Função PHP que imprime uma variável e tipo de dado.
    //    echo "\n"  = Gera uma nova lina para formatação.
    $numero = 5;

    // Declaração [if] Básica
    //     Imprime: '[Número equivale a 5]'
    if ($numero === 5) {
        echo '[Número equivale a 5]';
    } else {
        echo '[Número não equivale a 5]';
    }

    // [if ... else]. O exemplo também mostrado utilizando o operador de
    // negação [!].
    //     Imprime: '[Número é positivo]'
    if (!is_int($numero)) {
        echo '[Número não é inteiro]';
    } elseif ($numero < 0) {
        echo '[Número é negativo]';
    } else {
        echo '[Número é positivo]';
    }

    // Expressão Ternária: (expressão ? true : false)
    //     Prints: [Número é par: não]
    $is_even = ($numero % 2 === 0 ? 'sim' : 'não');
    echo "[Número é par: ${is_even}]";

    // A declaração [if] pode ser utilizada para avaliar valores "verdade" para
    // outros tipos de dados. As três declarações abaixo avaliam para falso por
    // que os valores são vazios ou zero.

    $empty_array = array();
    if ($empty_array) {
        echo '[Array tem dados]';
    } else {
        echo '[Array está vazio]';
    }

    $empty_string = '';
    if ($empty_array) {
        echo '[String tem dados]';
    } else {
        echo '[String está vazia]';
    }

    $zero = 0;
    if ($zero) {
        echo '[Número não é zero]';
    } else {
        echo '[Número é zero]';
    }

    // É possível excluir a expressão do meio ao utilizar o operador ternário '?:'
    // (expressão ?: padrão). Isso é conhecido como o operador Elvis e retorna
    // o resultado da primeira expressão se for avaliada como uma "verdade" ou
    // a segunda expressão (valor padrão).

    // Imprime:
    //     [Operador Elvis: Padrão]
    $value = ($empty_string ?: 'Padrão');
    echo "[Operador Elvis: ${value}]";

    // Imprime:
    //     [Elvis Operator: 3]
    $value = ((1 + 2) ?: 'Entre um Valor');
    echo "[Operador Elvis: ${value}]";

    // Igual [==] vs. Idêntico (modo estrito) [===]
    // O PHP é similar ao JavaScript em comparações de tipos de dados

    // Essas expressões avaliam como verdadeiro [true] por que o tipo de dados
    // não tem de corresponder exatamente.
    echo "\n";
    var_dump(1 == true);
    var_dump(0 == '');
    var_dump(0 == 'a');
    var_dump('1' == '01');

    // Essas expressões avaliam como falso [false] por que o tipo de dados tem
    // que corresponder exatamente.
    echo "\n";
    var_dump(1 === true);
    var_dump(0 === '');
    var_dump(0 === 'a');
    var_dump('1' === '01');

    // Não Igual [!=] vs. Não Idêntico [!==]:
    echo "\n";
    var_dump(0 != '');  // false
    var_dump(0 !== ''); // true

    // Operadores Lógicos:
    echo "\n";
    var_dump(true && true);   // true
    var_dump(false && false); // false
    var_dump(false || true);  // true

    // Arrays podem ser facilmente comparados em PHP
    echo "\n";
    $array1 = [1, 2, 3];
    $array2 = [1, 2, 3];
    $array3 = [1, 2, 3, 4];
    var_dump($array1 === $array2); // true
    var_dump($array1 === $array3); // false

    // Declaração Switch
    // Assim como a declaração [if] a sintaxe para [switch] é similar às
    // linguagens baseadas no estilo C, como a própria C e JavaScript, então
    // as mesmas regras podem ser aplicadas.
    //
    // Este exemplo imprime o nome da estação à partir do calendário de 4
    // estações no hemisfério norte baseando-se no mês atual.

    echo "\n";
    $month = date('F');

    switch ($month) {
        case 'Março':
        case 'Abril':
        case 'Maio':
            echo 'Primavera';
            break;
        case 'Junho':
        case 'Julho':
        case 'Agosto':
            echo 'Verão';
            break;
        case 'Setembro':
        case 'Outubro':
        case 'Novembro':
            echo 'Outono';
            break;
        case 'Dezembro':
        case 'Janeiro':
        case 'Fevereiro':
            echo 'Inverno';
            break;
        default:
            echo 'Erro';
    }
    // EXAMPLE_CODE_END
});

$app->get('/examples/php-loops', function() use ($app) {
    //  Fonte de Dados:
    //  https://en.wikipedia.org/wiki/List_of_largest_cities

    // Conteúdo é gerado em texto puro
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: Sintaxe PHP - Loops
    // Just like the logic demo this example also outputs data as it is evaluated.
    // Assim como a demonstração de lógica, este exemplo também gera dados assim
    // que validados.

    // Define arrays com as maiores cidades no mundo (por área urbana)
    $cities = [
        "Tóquio", "São Paulo", "Jacarta", "Seul", "Manila",
        "Nova York", "Xangai", "Cairo", "Délhi"
    ];

    $cities_populacao = [
        'Tóquio'     => '36,923,000',
        'São Paulo' => '36,842,102',
        'Jacarta'   => '30,075,310',
        'Seul'     => '25,520,000',
        'Manila'    => '24,123,000',
        'Nova York'  => '23,689,255',
        'Xangai'  => '23,416,000',
        'Cairo'     => '22,439,541',
        'Délhi'     => '21,753,486',
    ];

    echo 'Maiores Cidades no Mundo' . "\n";
    echo str_repeat('-', 40) . "\n";

    // Fazer um Loop pelas listas de cidades utilizando [foreach].
    //     foreach (array as item)
    foreach ($cities as $city) {
        echo $city;
        echo "\n";
    }
    echo "\n";

    // Fazer um Loop por um Dicionário ou Array Associativo utilizando [foreach]
    //     foreach (array as key => value)
    foreach ($cities_populacao as $city => $populacao) {
        echo $city . ' = ' . $populacao;
        echo "\n";
    }
    echo "\n";

    // Loop [for] utilizando estilo de sintaxe C, isto imprime 0...9 em linhas
    // separadas
    for ($n = 0; $n < 10; $n++) {
        echo $n;
        echo "\n";
    }
    echo "\n";

    // Loops [while] e [do-while] também utilizam estilo de sintaxe C, então são
    // familiares para desenvolvedores JavaScript. [continue] e [break]
    // também funcionam como esperado.

    // Imprime números pares entre 0 e 8
    $n = 0;
    while ($n < 10) {
        if ($n % 2 !== 0) {
            $n++;
            continue;
        }

        echo $n;
        echo "\n";
        $n++;
    }
    echo "\n";


    // Imprime 0...4
    $n = 0;
    do {
        if ($n === 5) {
            break;
        }

        echo $n;
        echo "\n";
        $n++;
    } while ($n < 10);
    // EXAMPLE_CODE_END
});


$app->get('/examples/php-functions', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Sintaxe PHP - Funções
    // Definir e chamar funções em PHP é similar a outras linguagens estilo C.
    // Enquanto funções são facilmente definidas em PHP com muita taxa
    // frameworks populares e projetos PHP definem classes ao invés de funções.
    // PHP no entanto, tem muitas funções integradas que são utilizadas em
    // desenvolvimento
    function add($x, $y) {
        return $x + $y;
    }

    // Parâmetros opcionais podem ser especificados atribuindo um valor para
    // a variável.
    function increment($x, $y = 1) {
        return $x += $y;
    }

    // Funções Callback pode ser definidas e atribuídas para uma variável da
    // mesma forma que seria em JavaScript.
    $subtract = function($x, $y) {
        return $x - $y;
    };

    // Este código chama as funções acima e imprime "2"
    $x = 1;
    $y = 2;
    $z = add($x, $y);      // returns 3
    $z = increment($z);    // returns 4
    $z = increment($z, 2); // returns 6
    $z = $subtract($z, 4); // returns 2
    echo $z;
    echo '<br>';

    // Diferente do JavaScript as funções PHP não tem acesso a variáveis
    // fora de seu escopo (escopo pai/escopo superior). A palavra chave
    // [use] pode ser utilizada para passar variáveis fora do escopo da
    // função. Ao utilizar essa sintaxe e ao definir [$x] na função
    // chamada, [$x] não é definida à partir do escopo superior então
    // este código imprime "1".
    $scope_test = function() use ($x) {
        $x = 123;
    };
    $scope_test();
    echo $x;
    echo '<br>';

    // Esta versão é similar a versão acima no entanto a variável [$x] é
    // passada por referência utilizando o operador [&]. Esta versão
    // imprimirá "123" por que [$x] é modificada.
    $scope_test = function() use (&$x) {
        $x = 123;
    };
    $scope_test();
    echo $x;
    echo '<br>';
    // EXAMPLE_CODE_END
});

$app->get('/examples/php-classes', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Sintaxe PHP - Classes e Objetos
    // Como definir e utilizar classes em detalhe, foge do escopo desta
    // página de referência rápida entretanto, a sintaxe básica é mostrada
    // abaixo o que pode ajudar a começar.

    class Math
    {
        // Define uma Variável Membro
        public $value = 0;

        // Define um construtor de Classe com um parâmetro opcional.
        // Definir um construtor [__construct] é opcional.
        public function __construct($numero = 0)
        {
            $this->value = $numero;
            echo 'Classe criada como valor: ' . $numero . '<br>';
        }

        // Definir um destruidor de Classe
        public function __destruct()
        {
            echo 'Classed Destroyed<br><br>';
        }

        // Função pública que retorna a instância do objeto [$this]
        public function add($numero) {
            $this->value += $numero;
            return $this;
        }

        // Função sem parâmetro ou valor de retorno
        public function show()
        {
            echo 'Value: ' . $this->value . '<br>';
        }
    }

    // Imprime:
    /*
    Classe criada com o valor: 0
    Valor: 3
    Classe destruída
    */
    $math = new Math();
    $math->add(1)->add(2)->show();
    $math = null;

    // Imprime:
    /*
    Classe criada como valor: 10
    Valor: 15
    ...
    */
    $math = new Math(10);
    $math->add(5)->show();

    // Ler de uma variável associada:
    $value = $math->value;
    echo $value . '<br>';
    // EXAMPLE_CODE_END
});

$app->get('/examples/php-encoding', function() {
    // Conteúdo é criado em texto puro
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: Sintaxe PHP - Codificação - JSON, Base64, Base64-URL
    // CLASS: Codificação\Json, Codificação\Base64Url
    // Criar um objeto e um array básicos para codificar
    $object = new \stdClass;
    $object->string = 'Test';
    $object->number = 123;
    $object->bool = true;

    $array = [
        'string' => 'Test',
        'number' => 123,
        'bool' => true,
    ];

    // -------------------------------------------
    // Codificar e Decodificar JSON
    // -------------------------------------------

    // Desde que o array PHP seja utilizado como um Dicionário ou Hash,
    // ambos exemplos imprimem:
    //     {"string":"Test","number":123,"bool":true}
    $json = json_encode($object);
    echo $json;
    echo "\n";

    $json = json_encode($array);
    echo $json;
    echo "\n\n";

    // Utilize o segundo parâmetro para JSON formatado
    $json = json_encode($object, JSON_PRETTY_PRINT);
    echo $json;
    echo "\n";

    // Decodifique e imprima o objeto com detalhes utilizando [print_r()]:
    $decoded = json_decode($json);
    print_r($decoded);
    echo "\n";

    // Por padrão objetos são decodificados como objetos da [stdClass].
    // Para retornar um array ao invés de um objeto passe [true] como o
    // segundo parâmetro.
    $decoded = json_decode($json, true);
    print_r($decoded);
    echo "\n";

    // Se houver um erro ao decodificar dados JSON será retornado [null].
    // Se você precisar Se você precisar lidar com um JSON inválido, você pode
    // fazer dessa forma:
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('Erro decodificando Dados JSON: ' . json_last_error_msg());
    }

    // O FastSitePHP inclui uma classe auxiliar JSON que lança excessẽs em erros
    // JSON ao invés de utilizar o comportamento padrão de retornar [false] ou
    // [null].
    $json = \FastSitePHP\Encoding\Json::encode($object);
    $decoded = \FastSitePHP\Encoding\Json::decode($json);

    /* Muitas vezes no entanto, na maioria dos códigos, simplesmente chamar
    [json_encode()] ou [json_decode()] será suficiente. Por padrão o PHP
    decodifica números grandes como ponto flutuante. Se você quer uma
    decodificação mais estrita para que venham em string, então você pode
    utilizar opções adicionais. É assim que a classe JSON do FastSitePHP
    decodifica quando é utilizada nas classes JWT, Encryptione SignedData.
    [JSON_BIGINT_AS_STRING] não está disponível no PHP 5.3 então o
    FastSitePHP utiliza código compatível. */
    $decoded = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

    // -------------------------------------------
    // Codifique e Decodifique Base64
    // -------------------------------------------

    // Imprime: "VGhpcyBpcyBhIHRlc3Q="
    $data = 'Isto é um teste';
    $base64 = base64_encode($data);
    echo $base64;
    echo "\n";

    // Ao decodificar, se houver um erro então [false] é retornado
    $decoded = base64_decode($base64);
    print_r($decoded);
    echo "\n\n";

    // ---------------------------------------------
    // Codifique e Decodifique o Formato Base64-URL
    // ---------------------------------------------

    /* O PHP não tem funções internas para o formato Base64-URL então o
    FastSitePHP inclui uma classe auxiliar com métodos estáticos. Eles se
    comportam de forma similar às funções integradas [base64_encode()] e
    [base64_decode()], então, se houver um erro, será retornado [false]. */

    $base64url = \FastSitePHP\Encoding\Base64Url::encode($data);
    echo $base64;
    echo "\n";

    $decoded = \FastSitePHP\Encoding\Base64Url::decode($base64url);
    print_r($decoded);
    echo "\n";
    // EXAMPLE_CODE_END
});

$app->get('/examples/php-error', function() use ($app) {
    // Comente códigos diferentes para testar or coloque um "exit();" depois
    // de certos blocos de código.

    // EXAMPLE_CODE_START
    // TITLE: Sintaxe PHP - Erros e Exceções
    // PHP usa ambos Erros, que são provocados, e Exceções que são lançadas.

    // PHP lida com erros de forma diferente de muitas linguagens. Por exemplo
    // linguagens um erro "divisão por zero" tanto lançaria uma Exceção ou seria
    // fatal e interromperia o programa e, em linguagens compiladas uma variável
    // indefinida não permitiria que o programa rodasse. Entretanto ao utilizar
    // PHP, a não ser que o relatório de erros esteja definido, ambos os erros
    // seriam simplesmente ignorados e o script continuaria com resultados
    // inesperados. Isso pode tornar programar em PHP difícil no início se você
    // estiver vindo de outra linguagem. FastSitePHP torna as coisas fáceis, por This can make programming
    // que roda o código em modo estrito e converte erros para exceções assim
    // que [app->setup()] é chamada.

    // Para controlar todos os erros e exceções de forma global em PHP, quatro
    // funções diferentes tem de ser definidas primeiro. Essas são controladas
    // automaticamente à partir de [app->setup()]:
    //   error_reporting()
    //   set_exception_handler()
    //   set_error_handler()
    //   register_shutdown_function()

    // Em PHP, a lógica de [try...catch], é similar a muitas linguagens como
    // JavaScript:
    try {
        throw new \Exception('Test');
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo '<br>';
    }

    // A existência de variáveis pode ser verificada.
    // Eeste código funciona e nenhum erro é desencadeado.
    if (isset($x) === false) {
        echo 'A variável [$x] não foi definida<br>';
    }

    // Descomentando as linhas abaixo desencadeará tipos diferentes de erros.
    // Ao utilizar os parâmetros padrão de desenvolvimento PHP os erros, muitas
    // vezes causarão uma mensagem de erro no meio do código e o código posterior
    // ainda será executado.
    //
    // echo $x;     // [E_NOTICE]  = "Variável indefinida: x"
    // echo 1 / 0;  // [E_WARNING] = "Divisão por zero"

    // FastSitePHP converte Erros em Exceções para que eles possam ser capturados
    try {
        echo $x;
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo '<br>';
    }

    try {
        echo 1 / 0;
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo '<br>';
    }
    // EXAMPLE_CODE_END
});

/*
// EXAMPLE_CODE_START
// TITLE: Olá Mundo com FastSitePHP
// CLASS: Aplicação
<?php
// Somente dois arquivos são necessários para rodar o FastSitePHP e eles podem
// estar no mesmo diretório que o [index.php] ou seus conteúdos podem ser
// integrados na página principal PHP.
require 'Application.php';
require 'Route.php';

// Crie o Objeto Application e opcionalmente configure controle de Erro e
// Fuso um horário.
$app = new FastSitePHP\Application();
$app->setup('UTC');

// Defina a rota padrão do 'Olá Mundo'
$app->get('/', function() {
    return 'Olá Mundo!';
});

// Retorne uma resposta JSON como um Objeto ou um Array
$app->get('/json', function() {
    return ['Olá' => 'Mundo'];
});

// Para todas as outras requisições, retorne a URL como resposta em texto puro.
// A palavra chave [use] torna a variável [$app] disponível para a função.
$app->get('/*', function() use ($app) {
    $app->header('Content-Type', 'text/plain');
    return $app->requestedPath();
});

// Rode a App
$app->run();
// EXAMPLE_CODE_END
*/

/*
// EXAMPLE_CODE_START
// CLASS: AppMin
<?php
// Apenas dois arquivos são necessários para rodar o FastSitePHP AppMin e eles
// podem estar no mesmo diretório que o [index.php] ou da página principal.
require 'AppMin.php';
require 'Route.php';

// Crie o Objeto AppMin e opcionalmente configure controle de Erro e
// Fuso Horário.
$app = new FastSitePHP\AppMin();
$app->setup('UTC');

// Define a rota 'Hello World' padrão
$app->get('/', function() {
    return 'Olá Mundo!';
});

// Retorne uma resposta JSON Return a JSON Response retornando um
// Objeto ou um Array
$app->get('/json', function() {
    return ['Olá' => 'Mundo'];
});

// Envie uma Resposta em Texto Puro e um Cabeçalho Personalizado. AppMin tem um
tamanho mínimo, então, parâmetros opcionais de URL como [:name?] e curingas de
// URL [*] não são suportados.
$app->get('/hello/:name', function($name) use ($app) {
    $app->headers = [
        'Content-Type' => 'text/plain',
        'X-Custom-Header' => $name,
    ];
    return 'Olá ' . $name;
});

// Rode a App
$app->run();
// EXAMPLE_CODE_END
*/

$app->get('/examples/app-basic-routes', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Objeto Application - Definindo Rotas Básicas
    // CLASS: Aplicação
    // O Objeto Application é o objeto chave no FastSitePHP. É utilizado para
    // definir rotas, fornecer informações e requisição, renderizar modelos,
    // enviar a resposta e mais. Se você estiver utilizando uma cópia deste
    // site ou um site inicial o Objeto Application estará disponível como a
    // variável [$app] e rotas são definidas na página [app.php].

    // Rota Básica
    // Envie uma Reposta HTML quando quando '/about' ou '/about/' for requisitado
    $app->get('/about', function() {
        return '<h1>Página About</h1>';
    });

    // Por padrão URLs diferenciam maiúsculas de minúsculas, entretanto, isso
    // pode ser desativado e '/ABOUT' corresponderia à rota acima.
    $app->case_sensitive_urls = false;

    // Se o mode estrito de URL estiver definido, então, a URL acima apenas
    // corresponderia a '/about' e '/about/' teria que ser definida
    // explicitamente.
    $app->strict_url_mode = true;
    $app->get('/about/', function() {
        return '<h1>Diretório About</h1>';
    });

    // A chamada de about utilizando [get()] corresponde somente a requisições
    // 'GET'. Se você gostaria de controlar ambos 'GET' e 'POST' ou outros
    // métodos com a mesma rota, você pode definir a rota utilizando a função
    // de rota [route()] e verificar se há dados enviados com a requisição
    // como mostrado abaixo. A função [route()] aceitará todos os métodos de
    // requisição.
    $app->route('/form', function() {
        if ($_POST) {
            // Controle dados postados de formulário
        }
        // Controle requisições GET, retorne o modelo renderizado etc
    });

    // Em acréscimo às Requisições GET, você pode manipular requisições
    // [POST, PUT, PATCH, and DELETE] utilizando funções nomeadas.
    $app->get('/method', function() { return 'get()'; });
    $app->post('/method', function() { return 'post()'; });
    $app->put('/method', function() { return 'put()'; });
    $app->patch('/method', function() { return 'patch()'; });
    $app->delete('/method', function() { return 'delete()'; });

    // A mesma URL ode ser definida múltiplas vezes e a primeira resposta
    // correspondente impedirá rotas adicionais de serem avaliadas. Neste
    // exemplo a rota '/example' retornará o texto 'Exemplo 2'.
    $app->get('/example', function() { return null; });
    $app->get('/example', function() { return 'Exemplo 2'; });
    $app->get('/example', function() { return 'Exemplo 3'; });

    // Além de retornar uma resposta, você pode também simplesmente exibir uma
    // resposta utilizando [echo] ou outras funções.
    $app->get('/echo-response', function() {
        echo 'Output';
    });
    // EXAMPLE_CODE_END

    // Retorna um Array JSON de todas as Rotas Definidas
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-parameter', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Define uma Rota com um Parâmetro
    // Envie uma resposta 'Olá FastSitePHP!' para a URL '/hello/FastSitePHP'.
    // O texto ':name' no modelo de rota define um parâmetro para a rota
    // por que inicia-se com o caractere ':'.
    $app->get('/hello/:name', function($name) {
        return 'Olá ' . $name;
    });
    // EXAMPLE_CODE_END

    // Retorna o Array JSON de todas as Rotas Definidas
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-optional-parameter', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Define uma Rota com um Parâmetro Opcional
    // Envie uma resposta 'Olá Mundo!' para a URL '/hello' ou no caso da variável
    // opcional [name] escapa e retorna uma mensagem como nome. A palavra chave
    // [use] faz a variável [$app] ficar disponível para a função e o ponto de
    // interrogação no modelo da URL ':name?' torna a variável opcional.
    $app->get('/hello/:name?', function($name = 'Mundo') use ($app) {
        return 'Olá ' . $app->escape($name) . '!';
    });

    // Além os parâmetros opcionais um caractere curinga '*' pode ser utilizado
    // no final da URL para manipular todas as requisições que correspondem ao
    // início da URL. Neste exemplo as seguintes  URLs seria ambas correspondidas.
    //     '/hello/world'
    //     '/hello/page1/page2/page3'
    $app->get('/hello/*', function() use ($app) {
        $app->header('Content-Type', 'text/plain');
        return $app->requestedPath();
    });
    // EXAMPLE_CODE_END

    // Retorna um Array JSON de toas as Rotas Definidas
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-controllers', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Defina a Rota que mapeia para uma Classe de Controle
    // CLASS: Aplicação
    // Definindo rotas com uma funções callback, permite prototipagem rápida
    // e funciona bem ao utilizar lógica mínima. Com o crescimento do código,
    // isso pode ser organizado em classes controller.

    // Opcionalmente especifique o Namespace raiz da classe controller. Ao usar
    // este arquivo se uma classe 'Exemplos' for criada, então, isso será
    // mapeado como 'App\Controllers\Examples'.
    $app->controller_root = 'App\Controllers';

    // Semelhante a [controller_root], [middleware_root] a qual se aplica às
    // funções [Route->filter()] e [$app->mount()].
    $app->middleware_root = 'App\Middleware';

    // As duas opções de formato são 'class' e 'class.method'. Ao utilizar só
    // nome de classe, a funções de rota [route(), get(), post(),  put(), etc]
    // serão utilizadas para o nome do método do controller correspondente.
    $app->get('/:lang/examples', 'Exemplos');
    $app->get('/:lang/examples/:page', 'Exemplo.getExample');

    // Exemplo de Classe de Controle
    class Exemplos
    {
        public function get(Application $app, $lang) { }
        public function getExample(Application $app, $lang, $page) { }
    }

    // Além de organizar o código em classes de controle, você pode também
    // separar rotas em arquivos diferentes utilizando a função [mount()]. A
    // função [mount()] carregará um arquivo no mesmo diretório somente se a
    // parte inicial da URL requisitada corresponder à URL de Mount. Um terceiro
    // parâmetro opcional aceita uma função callback ou string de 'Class.method'
    // e se o retorno for false, o arquivo não será carregado.
    $app->mount('/data/', 'routes-data.php');
    $app->mount('/secure/', 'routes-secure.php', function() {
        // Lógica ...
        return false;
    });
    $app->mount('/sysinfo/', 'routes-secure.php', 'Env.isLocalhost');
    // EXAMPLE_CODE_END

    // Returna um Array JSON de todas as rotas definidas
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-parameter-validation', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Validação de Parâmetro de Rota
    // O Objeto da Aplicação tem uma função [param()] que pode ser utilizado para
    // validar e converter os parâmetros da URL para um formato específico como
    // um número.

    // A função é definida como:
    //     param($name, $validation, $converter = null)

    // Parâmetros:
    //     Validation = ['any', 'int', 'float', 'bool'], uma expressão regular
    //         válida ou uma função Closure/Callback. Ao utilizar
    //         'int|float|bool' o tipo de dado será automaticamente convertido.
    //     Convertor = ['int', 'float', 'bool'] ou uma função Closure/Callback.

    // Exemplo Básico
    //     '/product/123' = Corresponde e [$product_id] será um número inteiro
    //     '/product/abc' = 404 Página não encontrada
    $app->param(':product_id', 'int');
    $app->get('/product/:product_id', function($product_id) {
        var_dump($product_id);
    });

    // Exemplos adicionais de Definição de Regras de Parâmetros. Para mais veja
    // a documentação completa e outros exemplos.

    $range_param = function($value) {
        $num = (int)$value;
        if ($num >= 5 && $num <= 10) {
            return true;
        } else {
            return false;
        }
    };

    $app
        ->param(':range1', $range_param)
        ->param(':range2', $range_param, 'int')
        ->param(':range3', $range_param, function($value) {
            return (int)$value;
        });

    $app->param(':float', 'float');
    $app->param(':bool', 'any', 'bool');

    $app->param(':regex1', '/^\d+$/');
    $app->param(':regex2', '/^[a-zA-Z]*$/');
    // EXAMPLE_CODE_END

    // Retorna um Array de JSON de todas as Rotas Definidas
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-filter', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Utilize Filtros de Rota
    // CLASS: Rota
    // Rotas podem ter funções filtro personalizadas atribuídas a elas para
    // rodar códigos específicos se uma rota for correspondida, realizar
    // validação ou outra tarefa requisitada pelo seu site. Funções Filtro rodam
    // somente se a rota corresponder a URL requisitada.

    // Definir algumas funções callback/closure
    $text_response = function() use ($app) {
        $app->header('Content-Type', 'text/plain');
    };
    $is_authenticated = function() {
        // Verificar Permissões de Usuário ...
        return true;
    };

    // Quando rotas são criadas [get(), route(), post(), etc], a rota criada é
    // retornada para que você possa chamar [filter()] depois de definir a rota.
    // Esta página será retornada como Texto Puro por que a função filtro define
    // o Cabeçalho de Resposta e não retorna valor.
    $app->get('/text-page', function($name) {
        return 'Olá';
    })->filter($text_response);

    // Uma rota pode ter múltiplos filtros e para clareza você pode colocar
    // funções filtro em linhas separadas. Esta página será somente chamada se
    // [$is_authenticated] retornar [true] e isso for também uma resposta em
    // texto.
    $app->get('/secure-text-page', function($name) {
        return 'Olá ' . $name;
    })
    ->filter($is_authenticated)
    ->filter($text_response);

    // A função [filter()] também aceita uma string representando uma classe e
    // método no formato de 'Classe.método'.
    $app->get('/phpinfo', function($name) {
        phpinfo();
    })
    ->filter('Env.isLocalhost');

    // Quando usar filtros de string, você pode especificar um namespace raiz
    // para as classes utilizando a propriedade de App [middleware_root].
    $app->middleware_root = 'App\Middleware';
    // EXAMPLE_CODE_END

    // Retorne um Array JSON de todas as Rotas Definidas
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-info', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Objeto de Aplicação - Informação de Básicas de Requisição
    // Muitos frameworks requerem valores especiais de configurações para que
    // possam manipular requisições. O FastSitePHP descobre isso automaticamente
    // e fornece algumas funções no Objeto Application para retornar
    // informações básicas de requisição.

    // Se seu site não usa um servidor proxy como balanceador de carga essas
    // funções pode ser utilizadas para criar URLs ou outras necessidades do app.
    //Se seu site utiliza um balanceador de cargas com cabeçalhos personalizados
    // para o host, então você utilizaria o objeto da requisição para obter a
    // URL raiz.

    // URL raiz ou base para o site. Isso é frequentemente necessário para
    // construir URL com caminhos completos em páginas web.
    //
    // Exemplos:
    //     # [index.php] especificado no URL
    //     Request: https://www.example.com/index.php/page
    //              https://www.example.com/index.php/page/page2
    //     Returns: https://www.example.com/index.php/
    //
    //     # [index.php] Localizado na pasta raiz
    //     Request: https://www.example.com/page
    //              https://www.example.com/page/page2
    //     Returns: https://www.example.com/
    //
    //     # [index.php] Localizado sob [site1]
    //     Request: https://www.example.com/site1/page
    //              https://www.example.com/site1/page/page2
    //     Returns: https://www.example.com/site1/
    //
    $root_url = $app->rootUrl();

    // Diretório raiz para o site. Geralmente necessário para construir URLs para
    // recursos estáticos como arquivos CSS ou JavaScript.
    //
    //     Request: https://www.example.com/index.php/page
    //              https://www.example.com/index.php/page/page2
    //              https://www.example.com/page
    //     Returns: https://www.example.com/
    //
    $root_dir = $app->rootDir();

    // Obtém a URL requisitada que exite depois da URL raiz. Isso será baseado
    // em onde o [index.php] ou entrada no arquivo PHP estiver localizada.
    //
    //     Request: https://www.example.com/index.php/test/test?test=test
    //              https://www.example.com/index.php/test/test
    //              https://www.example.com/test/test/
    //              https://www.example.com/test/test
    //              https://www.example.com/site1/index.php/test/test
    //     Returns: '/test/test'
    //
    // No exemplo acima ambos '/test/test/' e '/test/test' retornam '/test/test'
    // quando utiliza-se a propriedade padrão [$app->strict_url_mode = false]
    // caso contrário a URL exata será retornada.
    //
    $requested_path = $app->requestedPath();

    // Exemplo de utilização para construção de URL:
    $site_css = $app->rootDir() . 'css/site.css';
    $docs_link = $app->rootUrl() . '/documents';
    //
    // <link href="{{ $site_css }}" rel="stylesheet" />
    // <a href="{{ $docs_link }}">Documentos</a>
    // EXAMPLE_CODE_END

    // Retorne como JSON
    $app->json_options = JSON_PRETTY_PRINT;
    return [
        'rootUrl' => $root_url,
        'rootDir' => $root_dir,
        'requestedPath' => $requested_path,
    ];
});


$app->get('/examples/app-dynamic', function() use ($app) {
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: Funçẽs Dinâmicas e Propriedades Lazy Loading
    // FastSitePHP permite que o objeto da aplicação tenha funções dinamicamente
    // atribuídas e propriedades lazy loading. Isso permite que funções
    // personalizadas e recursos compartilhados por muitas rotas serem organizados
    // sob um objeto global e pode permitir uma injeção simples e clara de
    // de dependência.

    // Exemplo JavaScript - Isto funciona para adicionar uma função dinamicamente
    // a um objeto:
    //
    // var obj = {};
    // obj.test = function() { alert('test'); };
    // obj.test();

    // Exemplo PHP - A função pode ser atribuída a uma propriedade, entretanto,
    // se chamada um erro é acionado - 'Call to undefined method ...'.
    $obj = new \stdClass;
    $obj->test = function() { echo 'teste'; };
    // $obj->test();

    // Ao utilizar o objeto aplicação do FastSitePHP você pode simplesmente
    // atribuir e usar funções como em JavaScript ou Ruby.
    $app->test = function() { echo 'test'; };
    $app->test();

    // A função nativa de PHP [method_exists()] não funcionará para funções
    // personalizadas, então para verificar se exite um método integrado ou
    // personalizado do App use isso.
    $exists = $app->methodExists('test');

    // A função [lazyLoad()] aceita um nome de propriedade e função de callbback.
    // Isso cria o objeto como uma propriedade do app somente se utilizada. Isso
    // é ideal para trabalhar com sites onde algumas páginas usam um recurso e
    // outras não.
    $app->lazyLoad('db', function() {
        return $pdo = new \PDO('sqlite::memory:');
    });

    // [$app->db] é definida aqui no primeiro uso.
    $sql = 'CREATE TABLE test (id INTEGER PRIMARY KEY, test)';
    $app->db->query($sql);

    // [$app->db] agora funciona como uma propriedade padrão já que ela foi
    // previamente chamada.
    $sql = 'SELECT * FROM sqlite_master';
    $records = $app->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    // EXAMPLE_CODE_END

    echo "\n\n";
    echo json_encode($exists);
    echo "\n\n";
    echo json_encode($records, JSON_PRETTY_PRINT);
});

/*
// EXAMPLE_CODE_START
// TITLE: Eventos da Aplicação
<?php
// Da mesma for que na demonstração Hello World, este código pode ser copiado
// para um [index.php] separado ou outro arquivo e então testado.

// Existem 5 eventos callback da Aplicação:
//     before(), beforeSend(), after(), notFound(), and error()
// Eles podem ser usados para manipular lógica personalizada enquanto a aplicação
// está rodando.

// Carregue Arquivos
require 'Application.php';
require 'Route.php';
// Ou utilize um Autoloader:
// require '../../vendor/autoload.php';

// Cria e Configura o objeto App
$app = new FastSitePHP\Application();
$app->setup('UTC');

// ------------------------------------------------------------------
// Defina Eventos
// ------------------------------------------------------------------

// Eventos [Before] serão chamados da função [run()] antes que qualquer rota seja
// correspondida. Todas funções de evento podem ser chamadas múltiplas vezes e
// rodarão na ordem que foram definidas.
$app->before(function() use ($app) {
    $app->content = '[before1]';
});
$app->before(function() use ($app) {
    $app->content .= '[before2]'; // Append
});

// Eventos [Before Send] serão chamados da função [run()] depois que uma rota
// corresponder ao recurso requisitado. Funções passadas para a função
// [beforeSend()] devem ser definidas como [function($content)] e elas devem
// retornar uma resposta caso contrário uma resposta 404 'Não encontrada' será
// enviada para o cliente.
$app->beforeSend(function($content) {
    return $content . '[beforeSend]';
});

// Eventos [Not Found] serão chamados da função [run()] depois que todas as rotas
// forem verificadas e não houverem rotas que correspondam ao recurso requisitado.
// Funções passadas para a função [notFound()] não recebem parâmetros e, se elas
// retornam uma resposta, são tratadas como uma rota padrão e chamará quaisquer
// funções definidas [beforeSend()] após.
$app->notFound(function() use ($app) {
    return $app->content . '[notFound]';
});

// Eventos [Error] serão acionados se uma exceção não manipulada for lançada, um
// erro for acionado ou uma rota não corresponder e acionar uma resposta 404 ou
// 405. Esta função pode ser utilizada para fazer log de erros ou manipular a
// resposta com um erro personalizado. if [exit()] não for chamada, então o
// modelo de erro especificado ou padrão do FastSitePHP será renderizado.
$app->error(function($response_code, $e) use ($app) {
    // $response_code = [null, 404, 405, or 500]
    // $e = [null, Exception, or Throwable]
    if ($app->requestedPath() === '/error-test-1') {
        echo $app->content . '[Custom Error]';
        exit();
    }
});

// Eventos [After] serão chamados da função [run()] depois que a resposta estiver
// sido enviada para o cliente. Funções passadas para a função [after()] devem
// ser definidas como [function($content)]; o parâmetro [$content] definido
// na callback é o conteúdo da resposta que foi enviada para o cliente e não
// pode ser modificado à partir daqui. A única forma que funções [after()] não
// serão chamadas é se seus scripts terminarem por uma declaração PHP [exit()]
// ou se a manipulação de erros não estiver configurada e algum erro ocorrer.
$app->after(function($content) {
    echo '[after]';
});

// ------------------------------------------------------------------
// Define Rotas
// ------------------------------------------------------------------

// Esta resposta resultará no seguinte:
//     [before1][before2][page][beforeSend][after]
$app->get('/', function() use ($app) {
    return $app->content . '[page]';
});

// Chame a URL '/test' e veja o seguinte:
//     [before1][before2][notFound][beforeSend][after]

// Esta resposta resultará no seguinte:
//    [before1][before2][Custom Error]
$app->get('/error-test-1', function() {
    throw new \Exception('Teste de Erro 1');
});

// Mostra a Página de Erro Padrão com [after] sendo mostrado na parte inferior
$app->get('/error-test-2', function() {
    throw new \Exception('Teste de Erro 2');
});

// ------------------------------------------------------------------
// ROda a App
// ------------------------------------------------------------------
$app->run();
// EXAMPLE_CODE_END
*/

/*
// EXAMPLE_CODE_START
// TITLE: Modelo PHP de Exemplo
<!--
// Estes são os conteúdos do arquivo [template.php] que é mostrado como um exemplo
// nesta página. Ao chamar [render()] o Objeto Application é passado como [$app]
// o que permite que [escape()] e outras funções sejam utilizadas. Alé da sintaxe
// padrão [if (expression) { code }] o PHP provê uma sintaxe alternativa para
// controlar estruturas ao utilizar modelos [if (expr): (code) endif].
//
// Os modelos PHP são de alto desempenho e usam pouquíssima memória, entretanto,
// a sintaxe pode ser considerada mais verbosa que muitos formatos de modelos
// modernos. Se você preferir utilizar um formato de modelo diferente, existem
// muitos mecanismos de modelos para PHP amplamente utilizados e de alta
// qualidade que podem ser integrados ao FastSitePHP.
-->

<h1><?= $app->escape($page_title) ?></h1>
<?php if (count($list) === 0): ?>
    <p>Não Foram Encontrados Registros</p>
<?php else: ?>
    <ol>
        <?php foreach ($list as $item): ?>
            <li><?= $app->escape($item) ?></li>
        <?php endforeach ?>
    </ol>
<?php endif ?>
<p><?= $app->escape($year) ?></p>
// EXAMPLE_CODE_END
*/

$app->get('/examples/app-render', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Aplicação - Renderiza Arquivos de Modelo Server-Side
    // Define o Diretório Modelo Raiz e Arquivos Principais Específicos
    $app->template_dir = __DIR__ . '/views/';
    // $app->header_templates = '_header.php';
    // $app->footer_templates = '_footer.php';
    // $app->error_template = 'error.php'; // Para respostas 500
    // $app->not_found_template = '404.php'; // Para respostas 404 e 405

    // De forma opcional mostre erros detalhados ao utilizar o modelo de erro
    // padrão e defina mensagens de erro. Com o modelo padrão, erros detalhados
    // serão exibidos quando rodar como localhost.
    $app->show_detailed_errors = true;
    // $app->error_page_title = 'Página de Erro Personalizada';
    // $app->error_page_message = 'Mensagem de Erro Personalizada';
    // $app->not_found_page_title = 'Página 404 Personalizada';
    // $app->not_found_page_message = 'Mensagem 404 Personalizada';
    // $app->method_not_allowed_title = 'Página 405 Personalizada';
    // $app->method_not_allowed_message = 'Mensagem 405 Personalizada';

    // Defina Dados para o Modelo. Variáveis pode ser definidas na propriedade
    // [locals] da App e elas podem ser passadas na função de renderização.
    $app->locals['year'] = date('Y');
    $data = [
        'page_title' => 'Modelo PHP de Exemplo',
        'list' => ['Item 1', 'Item 2', 'Item 3', 'Item 4'],
    ];

    // Renderiza o Modelo PHP e retorna uma string.
    // O código fonte do modelo é mostrado na seção de código do exemplo acima.
    $html = $app->render('template.php', $data);
    // EXAMPLE_CODE_END
    return $html;
});

$app->get('/examples/app-render-mustache', function() use ($app) {
    // NOTA - Para utilizar o Mustache, deve instalá-lo antes e requer que use
    // o Composer ou modifique o arquivo [autoload.php]. Isso renderiza o arquivo
    // [template.mustache.htm].

    // EXAMPLE_CODE_START
    // TITLE: Aplicativo - Renderiza com um Mecanismo de Modelo Personalizado
    // Defina um Mecanismo de Modelo Personalizado que utiliza o popular sistema
    // de Modelos Mustache.
    $app->engine(function($file, array $data = null) {
        $dir = __DIR__ . '/views/';
        $options = [
            'cache' => dirname(__FILE__).'/tmp/cache/mustache',
            'loader' => new Mustache_Loader_FilesystemLoader($dir, ['extension' => '.htm']),
        ];
        $mustache = new Mustache_Engine($options);
        $tmpl = $mustache->loadTemplate($file);
        $html = $tmpl->render($data);
        return $html;
    });

    // Defina os Dados para o Modelo
    $app->locals['year'] = date('Y');
    $data = [
        'page_title' => 'Exemplo de Modelo Mustache',
        'list' => ['Item 1', 'Item 2', 'Item 3', 'Item 4'],
        'has_list' => true,
    ];

    // Renderiza o Modelo
    $html = $app->render('template.mustache', $data);

    // Ao utilizar Modelos Personalizados, você pode definir Páginas de Erro e
    // Não Encontradas Personalizadas:
    // $app->error_template = 'error'; // Para Respostas 500
    // $app->not_found_template = '404'; // Para Respostas 404 e 405
    // EXAMPLE_CODE_END
    return $html;
});

$app->get('/examples/request-basic', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Objeto HTTP Request - Lendo Strings de Consulta, Campos de Formulários e Cookies
    // CLASS: Web\Request
    // O objeto Request pode ser utilizado obtendo informações do cliente
    // para uma requisição HTTP. Isso inclui strings de consulta, campos de formulários
    // cookies, cabeçalhos e mais. O objeto Request contém funções para
    // limpar e de, forma segura, ler informações do cliente.

    // Sem utilizar um framework, strings de consulta, variáveis de formulário,
    // e outras entradas de usuário, podem ser lidas através de superglobais PHP
    // Without using a Framework, Query Strings, Form Variables and other
    // [$_GET, $_POST, etc].
    // Exemplo, leia o campo da string de consulta [number]:
    $numero = $_GET['number'];

    // Se a string de consulta [type] não existe, então o código acima lançaria
    // uma exceção e, para obter de forma segura o valor, você poderia
    // primeiro verificar se foi definida.
    $numero = (isset($_GET['number']) ? $_GET['number'] : null);

    // Uma linha de código PHP adicional pode ser utilizada para forçar um valor
    // numérico:
    $numero = (int)$numero;

    // A requisição pode ser utilizada então para, seguramente,  ler os valores,
    // converter tipos de dados etc. Para usar o objeto Requisição, simplesmente
    // crie um:
    $req = new \FastSitePHP\Web\Request();

    // Você pode então ler strings de consultas por nome sem incluir lógica segura:
    $numero = $req->queryString('number');

    // Um segundo parâmetro opcional pode ser utilizado para converter para um
    // tipo de dados específico. Neste exemplo o valor será convertido para um
    // número inteiro se for válido, caso contrário, [null] será retornado.
    $numero = $req->queryString('number', 'int?');

    // Além de [queryString()] as funções [form()] e [cookie()] podem ser
    // utilizadas da mesma maneira.
    $value  = $req->form('field');
    $cookie = $req->cookie('name');

    // O objeto Request, também contém uma função auxiliar para manipular entradas
    // de usuário e objetos onde um valor pode ou não existir. Isso pode ser
    // utilizado para prevenir erros quando objetos JSON complexos são lidos e
    // para limpar dados de qualquer objeto ou array.
    //
    // Definição de Função:
    //     value($data, $key, $format = 'value?', $max_length = null)
    //
    // Dados de Exemplo:
    //     $_POST['input1'] = 'test';
    //     $_POST['input2'] = '123.456';
    //     $_POST['checkbox1'] = 'on';
    //     $json = [
    //         'app' => 'FastSitePHP',
    //         'strProp' => 'abc',
    //         'numProp' => '123',
    //         'items' => [ ['name' => 'item1'], ['name' => 'item2'] ],'
    //    ];
    //
    // Funções de Exemplo:
    //    'test'        = $req->value($_POST, 'input1');
    //    // Truncar a string para dois caracteres:
    //    'te'          = $req->value($_POST, 'input1',    'string', 2);
    //    123.456       = $req->value($_POST, 'input2',    'float');
    //    ''            = $req->value($_POST, 'missing',   'string'); // Faltando
    //    1             = $req->value($_POST, 'checkbox1', 'checkbox');
    //    0             = $req->value($_POST, 'checkbox2', 'checkbox'); // Faltando
    //    true          = $req->value($_POST, 'checkbox1', 'bool');
    //    'FastSitePHP' = $req->value($json,  'app');
    //    'abc'         = $req->value($json,  'strProp',   'string?');
    //    0             = $req->value($json,  'strProp',   'int');  // Int Inválido
    //    null          = $req->value($json,  'strProp',   'int?'); // Int Inválido
    //    123           = $req->value($json,  'numProp',   'int');
    //    'item1'       = $req->value($json,  ['items', 0, 'name']);
    //    'item2'       = $req->value($json,  ['items', 1, 'name']);
    //    null          = $req->value($json,  ['items', 2, 'name']); // Faltando
    //
    // Veja a documentação completa para mais. Se você precisa de validação
    // completa ao invés de limpeza de dados veja a classe [\FastSitePHP\Data\Validator].
    // EXAMPLE_CODE_END

    // Retorne A Reposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($numero),
        ]));
});

$app->route('/examples/request-content', function() use ($app) {
    // NOTA - esta função utiliza [$app->route()] que significa
    // que pode aceitar qualquer método [GET, POST etc]. Ao utilizar
    // o [GET] padrão, o tipo do corpo e conteúdo será vazio.

    // EXAMPLE_CODE_START
    // TITLE: Objeto HTTP Request - Requisite JSON e Conteúdo
    // CLASS: Web\Request
    // Create the Request Object
    $req = new \FastSitePHP\Web\Request();

    // Obtenha o tipo do conteúdo da requisição. Isso é um campo auxiliar que
    // retorna um valor simples baseado no cabeçalho 'Content-Type':
    //     'json'      = 'application/json'
    //     'form'      = 'application/x-www-form-urlencoded'
    //     'xml'       = 'text/xml' or 'application/xml'
    //     'text'      = 'text/plain'
    //     'form-data' = 'multipart/form-data'
    // Se diferente, o valor puro do cabeçalho será retornado e se o cabeçalho
    // nao estiver definido, então [null] será retornado.
    $type = $req->contentType();

    // O corpo/conteúdo da requisição pode ser lido à partir de [content()]. Se
    // o tipo da requisição for JSON, então o objeto será analisado e um
    // objeto/array será retornado. Se [contentType() === 'form'] então um array
    // será retornado, caso contrário, o corpo/conteúdo é retornado como uma
    // string. No PHP uma string pode também ser utilizada para dados binários
    // por que uma string é simplesmente um array de bytes.
    $body = $req->content();

    // A função [value()] pode ser utilizada para, de forma segura, ler valores
    // aninhados de um objeto JSON enviado. Veja outros exemplos e documentos
    // para mais sobre o uso da função [value().
    $value = $req->value($body,  ['items', 0, 'name']);
    // EXAMPLE_CODE_END

    // Retorne uma Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($type),
            json_encode($body),
            json_encode($value),
        ]));
});


$app->get('/examples/request-headers', function() use ($app) {
    // Sobrescreva o Cabeçalho da Requisição
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4';

    // EXAMPLE_CODE_START
    // TITLE: Objeto HTTP Request - Campos de Cabeçalho
    // CLASS: Web\Request
    // Crie o Objeto Request
    $req = new \FastSitePHP\Web\Request();

    // Lendo Campos Comuns de Cabeçalho pode ser feito através de funções:
    $origin = $req->origin();
    $userAgent = $req->userAgent();
    $referrer = $req->referrer();
    $client_ip = $req->clientIp();
    $protocol = $req->protocol();
    $host = $req->host();
    $port = $req->port();

    // Ao utilizar funções com cabeçalhos 'Accept' um array de dados é retornado
    // e um parâmetro opcional pode ser passado para retornar [true] ou [false]
    $accept_encoding = $req->acceptEncoding();
    $accept_language = $req->acceptLanguage();

    // Exemplo:
    //    Valor do Cabeçalho 'Accept-Language' = 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
    // Retorna:
    //    [
    //        ['value' => 'ru-RU', 'quality' => null],
    //        ['value' => 'ru',    'quality' => 0.8],
    //        ['value' => 'en-US', 'quality' => 0.6],
    //        ['value' => 'en',    'quality' => 0.4],
    //    ];

    $accept_en = $req->acceptLanguage('en'); // true
    $accept_de = $req->acceptLanguage('de'); // false

    // Qualquer cabeçalho pode ser lido ao utilizar a função [header()]:
    $content_type = $req->header('Content-Type');
    $user_agent = $req->header('User-Agent');

    // Chaves de Cabeçalho ignoram diferenciação de maiúsculas e minúsculas, então
    // todas as seguintes retornam o mesmo valor:
    $content_type = $req->header('content-type');
    $content_type = $req->header('CONTENT-TYPE');
    $content_type = $req->header('Content-Type');

    // Todos os cabeçalhos pode ser lidos à partir da função [headers()]:
    $headers = $req->headers();
    // EXAMPLE_CODE_END

    // Retorne uma Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($accept_en),
            json_encode($accept_de),
            json_encode($content_type),
            json_encode($user_agent),
            json_encode($headers),
        ]));
});

$app->get('/examples/request-proxy-headers', function() use ($app) {
    // Sobrescreva Configurações e Cabeçalhos para Demonstração
    $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = "' OR '1'='1 --, 127.0.0.1, 54.231.1.5";

    // EXAMPLE_CODE_START
    // TITLE: Objeto HTTP Request - Campos de Cabeçalho de Proxy
    // CLASS: Web\Request
    // Cria o Objeto da Request
    $req = new \FastSitePHP\Web\Request();

    // Cabeçalhos da Request de Proxy são usados por campos chave como IP do
    // cliente quando um servidor web está atrás de um servidor "proxy" em
    // uma rede local, por exemplo um balanceador de carga. Ler os valores
    // corretamente é importante para segurança, entretanto, em geral com qualquer
    // linguagem de programação ou framework, ler cabeçalhos de proxy é difícil
    // requer configuração extra. O FastSitePHP torna essa tarefa fácil sem a
    // necessidade de configuração.

    // Por exemplo, simplesmente ler o IP do cliente da requisição pode ser
    // feito lendo o valor de REMOTE_ADDR.
    $client_ip = $_SERVER['REMOTE_ADDR'];

    // Se o balanceador de carga estiver configurado para fornecer o IP do
    // Cliente, isso será normalmente um dos seguintes cabeçalhos de requisição:
    // [X-Forwarded-For, Client-Ip ou Forwarded]. Entretanto, desde que o usuário
    // final possa enviar dados com o cabeçalho de requisição, isso deve ser
    // lido corretamente. O cabeçalho padronizado [Forwarded] tem um formato
    // como este:
    //     'for=192.0.2.43, for="[2001:db8:cafe::17]";proto=http;by=203.0.113.43'
    // Enquanto cabeçalhos não padronizados mas amplamente utilizados tal como
    // [X-Forwarded-For], utilizam este formato:
    //     'client-ip1, client-ip2, proxy1, proxy2'
    // O FastSitePHP lida com ambos os formatos.

    // Por exemplo, assumamos que o balanceador de carga esteja em '10.0.0.1',
    // '10.0.0.2' é utilizado para filtragem adicional de conteúdo e
    // [X-Forwarded-For] entrou com o seguinte valor:

    //     [REMOTE_ADDR]      =   '10.0.0.1'
    //     [X-Forwarded-For]  =   "' OR '1'='1 --, 127.0.0.1, 54.231.1.5, 10.0.0.2"
    // Neste exemplo, o seguinte foi enviado:
    //     - Client - A SQL Injection String of "' OR '1'='1 --"
    //     - Client - A localhost IP [127.0.0.1]
    //     - Client - Actual IP [54.231.1.5]
    //     - Server - 10.0.0.2

    // Ao simplesmente ler o IP do cliente sem parâmetros, o IP do balanceador
    // de carga é retornado, que para esta exemplificação é '10.0.0.1'.
    $client_ip = $req->clientIp();

    // Então ao utilizar a configuração padrão 'from proxy', o correto IP do
    // usuário é retornado '54.231.1.5' is returned. Se nenhum servidor de proxy
    // for utilizado, então é seguro chamar as configurações padrão de
    // 'from proxy'.
    $user_ip = $req->clientIp('from proxy');

    // Ao utilizar proxies, um segundo parâmetro opcional de [$trusted_proxies]
    // está disponível. Este tem sua string definida por padrão como 'trust
    // local', entretanto um array de um IP específico ou de faixas de IP
    // (format CIDR) pode ser utilizado para uma filtragem mais específica. Além
    // disso o primeiro parâmetro [$option], pode também ser modificado para ler
    // de Cabeçalhos de Requisição diferentes.
    $user_ip = $req->clientIp('from proxy', 'trust local');

    // Além do IP de Cliente, valores de proxy também podem ser lidos para
    // [Protocol, Host, and Port]:
    $portocal = $req->protocol('from proxy'); // 'http' or 'https'
    $host = $req->host('from proxy');
    $port = $req->port('from proxy');
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $client_ip,
            $user_ip,
            $portocal,
            $host,
            $port,
        ]));
});

$app->get('/examples/request-server-info', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Objeto Request - Informação do Servidor
    // CLASS: Web\Request
    // O Objeto Request pode retornar o IP do Servidor e tem uma função auxiliar
    // [isLocal()] que retorna true somente se ambos, o cliente requerente e o servidor,
    // estiverem no localhost ['127.0.0.1' ou '::1']. Em certas apps você pode
    // querer ativar certas funcionalidades para desenvolvimento ou operação
    // local e estas funções ajudam nisso.
    $req = new \FastSitePHP\Web\Request();
    $server_ip = $req->serverIp();
    $is_local  = $req->isLocal();

    // NOTA - o IP do servidor web é frequentemente diferente do verdadeiro IP
    // da rede. Para obter o IP da rede (localização do servidor), utilize o
    // Objeto Networking Config como alternativa:
    $config = new \FastSitePHP\Net\Config();
    $net_ip = $config->networkIp();
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $server_ip,
            $net_ip,
            json_encode($is_local),
        ]));
});

$app->get('/examples/response-content-type', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Resposta - Conteúdo, Códigos de Status, Cabeçalhos, Cookies e Arquivos
    // CLASS: Web\Response
    // Por padrão, quando uma string é retornada em uma rota, o servidor retorna
    // uma resposta HTML. Sem criar um Objeto Response, o Objeto Application
    // pode ser utilizado para especificar um cabeçalho 'Content-Type' diferente
    // que é o que os Navegadores e Clientes HTTP utilizam para determinar como
    // lidar com a resposta.
    $app->get('/app-text-response', function() use ($app) {
        $app->header('Content-Type', 'text/plain');
        return 'Resposta utilizando o Objeto Application';
    });

    // Ao utilizar o Objeto Response [contentType()] e [content()] são as
    // principais funções para especificar diferentes tipos de conteúdo.
    $app->get('/text-response', function() {
        $res = new \FastSitePHP\Web\Response();
        return $res->contentType('text')->content('Resposta em Texto');
    });

    // Ao utilizar o Objeto Response, propriedades são definidas através de
    // funções getter/setter e são encadeáveis para que possam ser utilizadas
    // em uma linha como mostrado acima ou separadas em múltiplas linhas como
    // é mostrado aqui.
    $app->get('/text-response2', function() {
        return (new \FastSitePHP\Web\Response())
            ->contentType('text')
            ->content('Resposta em Texto 2');
    });

    // Utilizando o Objeto Response
    $res = new \FastSitePHP\Web\Response();

    // Defina o Cabeçalho 'Content-Type'.
    // Todas as seguintes 3 chamadas de função, definem o mesmo valor. A
    // diferença é que [contentType()] é uma função auxiliar que permite
    // valores abreviados de [html, json, jsonp, text, css, javascript, xml].
    $res->contentType('text');
    $res->contentType('text/plain');
    $res->header('Content-Type', 'text/plain');

    // Definir Conteúdo
    // Para a maioria dos tipos de conteúdo, utilize uma string ao definir [content()].
    $res->content('<h1>FastSitePHP</h1>');

    // Para Conteúdo JSON ambos, Objetos e Arrays, são utilizados
    $object = [
        'título' => 'Demonstração',
        'número' => '123',
    ];

    $res
        ->contentType('json')
        ->content($object);

    // A função auxiliar [json()] define ambos, [contentType()] e [content()]
    $res->json($object);

    // Para JSON formatado, defina a opções [JSON_PRETTY_PRINT] antes de enviar
    // a resposta. POr padrão [JSON_UNESCAPED_UNICODE] é utilizada e o JSON é
    // minificado. Qualquer constante utilizada por [json_encode()] pode ser
    // definida aqui.
    $app->json_options = (JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $res->jsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // Códigos de Status
    // [$app] somente suporta [200, 201, 202, 204, 205, 404 e 500] e o Objeto
    // Response permite e trata respostas 304 juntamente com qualquer outro
    // código de status válido ou personalizado.
    $app->statusCode(201);
    $res->statusCode(500);

    // Uma função auxiliar [pageNotFound()] existe no Objeto Aplicação que pode
    // ser utilizada para enviar uma resposta 404 juntamente com a página 404
    // padrão ou personalizada.
    $app->get('/document/:name', function($name) use ($app) {
        if ($name !== 'test') {
            return $app->pageNotFound();
        }
        return 'Teste';
    });

    // Especifique um arquivo ou a resposta; o arquivo especificado será
    // transmitido para o cliente e enviado de uma maneira eficiente para a
    // memória, para que esta função seja chamada em arquivos muito grandes
    // impactando minimamente o servidor.
    $file_path = __FILE__;
    $res->file($file_path);

    // Incluir Mime-Type específico com Cabeçalhos para Armazenamento em Cache.
    // Outro tópico nesta página cobre armazenamento em cache em mais detalhe.
    $res->file($file_path, 'text', 'etag:md5', 'private');

    // Exemplo de Uso de Arquivo
    $app->get('/view-source-code', function() {
        $file_path = __FILE__;
        $res = new \FastSitePHP\Web\Response();
        return $res->file($file_path, 'download');
    });

    // Converter o nome ou tipo de um arquivo para um mime-type.
    //
    // Extensões de arquivo que mapeiam para um tipo MIME com a função são:
    //     Texto: htm, html, txt, css, csv, md, markdown, jsx
    //     Imagem: jpg, jpeg, png, gif, webp, svg, ico
    //     Aplicação: js, json, xml, pdf, woff
    //     Vídeo: mp4, webm, ogv, flv
    //     Áudio: mp3, weba, ogg, m4a, aac
    //
    // Se um tipo de arquivo não estiver associado com um mime-type, então um
    // tipo de arquivo de download 'application/octet-stream' será retornado.
    $mime_type = $res->fileTypeToMimeType('video.mp4');
    $mime_type = $res->fileTypeToMimeType('mp4');

    // Definir Cabeçalhos de Resposta e Cookies

    // Utilizando o Objeto Application
    $app->header('X-API-Key', 'App_1234');
    $app->cookie('X-API-Key', 'App_1234');

    // Ou utilizando o Objeto Response
    $res->header('X-API-Key', 'Res_1234');
    $res->cookie('X-API-Key', 'Res_1234');

    // Ao criar um Objeto Response o Objeto Aplicação pode ser passado e todas
    // as definições do App de [statusCode(), cors(), noCache(), headers(),
    // cookies() e [json_options] serão passadas para o Objeto Response.
    $res = new \FastSitePHP\Web\Response($app);
    // EXAMPLE_CODE_END

    // Modifique abaixo ou copie o código acima para testar diferentes respostas

    // return $res->content('Teste HTML com Definição de App');

    // return $res
    //     ->reset()
    //     ->contentType('text')
    //     ->cookie('X-API-Key', 'Res_123')
    //     ->content('Resposta em texto');

    $app->header('Content-Type', 'text/plain')->cookie('X-API-Key', 'App_1234');
    return 'Resposta utilizando o Objeto Application [' . $mime_type . ']';
});

$app->get('/examples/redirect', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Redirecionamentos HTTP
    // CLASS: Web\Response
    // Requisições HTTP pode ser redirecionadas utilizando o objeto App ou o
    // Response.
    // Ao utilizar o Objeto Ap e chamar [redirect()], o script PHP finaliza-se
    // imediatamente, entretanto, quaisquer eventos definidos por [after()] serão
    // chamados. Se seu site utiliza Server-side Unit Testing, você pode querer
    // utilizar o objeto resposta que comporta-se como uma rota regular e não
    // finaliza a execução do script.

    // O usuário faz esta requisição
    $app->get('/page1', function() use ($app) {
        $app->redirect('page3');
    });

    // Ou o usuário faz esta requisição
    $app->get('/page2', function() {
        $res = new \FastSitePHP\Web\Response();
        return $res->redirect('page3');
    });

    // O usuário verá então esta URL resposta
    $app->get('/page3', function() {
        return 'page3';
    });

    // O Código de Status de Resposta Padrão é [302 'Found'] (Temporary Redirect),
    // e um segundo parâmetro opcional para ambos App e Response permite códigos
    // de status de redirecionamento adicionais:
    //   301  Moved Permanently
    //   302  Found
    //   303  See Other
    //   307  Temporary Redirect
    //   308  Permanent Redirect
    $app->get('/old-page', function() use ($app) {
        $app->redirect('new-page', 301);
    });
    // EXAMPLE_CODE_END

    // Redirecionar e volta para o índice principal de exemplos
    $app->redirect('./');
});

$app->get('/examples/response-caching', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Resposta - Cabeçalhos de Cache  e Cache do Lado do Cliente
    // CLASS: Web\Response
    // Exemplos abaixo mostram como utilizar Cabeçalhos Response para controlar
    // como um Navegador ou Cliente HTTP armazena uma Página ou Recurso em cache.

    // Evitar que um Navegador ou Cliente Armazene um Arquivo ou Página em Cache.
    // Ambos os Objetos, Application e Response, têm uma função [noCache()].
    // Chamando essas funções enviará 3 Cabeçalhos Response para o cliente:
    //     Cache-Control: no-cache, no-store, must-revalidate
    //     Pragma: no-cache
    //     Expires: -1
    $app->noCache();

    $res = new \FastSitePHP\Web\Response();
    $res->noCache();

    // Se ao utilizar certos Cabeçalhos Response o Objeto Response enviará uma
    // resposta 304 "Not Modified" dependendo dos cabeçalhos da requisição.
    // Respostas 304 são utilizadas por Navegadores e outros Clientes pare
    // reutilizar recursos previamente obtidos de suas cópias armazenadas em
    // cache. Isto permite que o usuário veja recursos estáticos mais
    // rapidamente e reduz a quantidade de tráfego enviado do servidor.

    // Cabeçalho de resposta 'Cache-Control'. Este cabeçalho tem opções
    // diferentes para informar clientes de como eles podem armazenar uma
    // página em cache. Neste exemplo, somente usuários finais e não servidores
    // proxy podem armazenar em cache a reposta e eles devem revalidar isso
    // a cada vez.
    $res->cacheControl('private, must-revalidate'); // privado, deve revalidar

    // Cabeçalho de resposta 'Expires'. Este cabeçalho é utilizado para informar
    // o cliente de por quanto tempo o conteúdo é valido, entretanto dependendo
    // das opções de 'Cache-Control' este valor pode ser ignorado. Embora,
    // definindo esse valor não aciona uma resposta 304 e cabe ao navegador ou
    // cliente como lidar com isso.
    $res->expires('+1 month'); // mais um mês

    // Cabeçalho de Resposta 'ETag' (ETag é uma abreviação para Entity Tag). Uma
    // ETag representa um valor para o conteúdo (geralmente utilizando uma Hash).
    // Navegadores e Clientes enviarão um Cabeçalho de Requisição 'If-None-Match'
    // com a versão que eles tem armazenada e se corresponder, então o Objeto
    // Response enviará uma resposta 304 sem o conteúdo já que o navegador pode
    // utilizar a cópia local.
    $res->etag('hash:md5');

    // A função [etag()] também aceita a própria hash ou uma função de fechamento.
    $res->etag('0132456789abcdef');
    $res->etag(function($content) {
        return sha256($content);
    });

    // O segundo parâmetro opcional aceita as ETag do tipo 'strong' ou 'weak'.
    // O padrão é 'weak' e este é o recomendado para evitar erros complexos de
    // de armazenamento em cache. Se você precisa utilizar ETags 'strong',
    // provavelmente você deveria fazer testes extra.
    $res->etag('hash:sha256', 'weak');

    // Cabeçalho de Resposta 'Last-Modified'. Se estiver definido e o cliente
    // envia de volta um cabeçalho de requisição 'If-Modified-Since' que
    // corresponde, então uma resposta 304 é enviada. Ao definir o valor
    // utilize um Timestamp Unix ou uma String que pode ser analisada pela
    // função PHP [strtotime()].
    $res->lastModified('2019-01-01 13:01:30');

    // Cabeçalho de Resposta 'Vary'. O cabeçalho de resposta 'Vary' pode ser
    // utilizado para especificar regras para armazenamento HTTP em cache e
    // também prover dicas de conteúdo para Google e outros Mecanismos de
    // Busca.
    $res->vary('User-Agent, Referer');

    // Ao enviar um arquivo coo a resposta, você pode especificar parâmetros
    // opcionais [$cache_type e $cache_control]. Cache Type tem 3 opções válidas
    // mostradas abaixo e Cache Control defini a função [cacheControl()].
    $file_path = __FILE__;
    $content_type = 'text'; // texto
    $res->file($file_path, $content_type, 'etag:md5');
    $res->file($file_path, $content_type, 'etag:sha1',     'private'); // privado
    $res->file($file_path, $content_type, 'last-modified', 'public');// público

    // Ao enviar etags com [file()] e utilizando uma das duas 'etag:md5' ou
    // 'etag:sha1' a hash é calculada a cada vez. Se você utiliza ETags e tem
    // arquivo grandes ou frequentemente acessados, isso seria uma boa ideia
    // para salvar a hash quando o arquivo for criado pela primeira vez
    // definí-la através da função [etag()].
    $saved_hash = '0132456789abcdef';
    $res->file($file_path)->etag($saved_hash);
    // EXAMPLE_CODE_END

    // Modifique abaixo para testar diferentes cabeçalhos de cache.
    // Para facilmente testar utilize as ferramentas de desenvolvimento do
    // navegador com cache ativado, então faça mudanças e veja quando isso envia
    // 200 ou 304 quado a página é recarregada.
    $res->reset();

    // Descomente um e use-o, recarregue e você deveria ver um 304.
    // Então comente novamente e descomente outro valor e você deveria ver um
    // código de resposta 200.
    // return $res->file($file_path, 'text', 'etag:md5');
    // return $res->file($file_path, 'text', 'etag:sha1');

    // Após a primeira resposta você deveria ver um 304 a cada vez, até você
    // fazer uma mudança no conteúdo da resposta.
    $res->etag('hash:md5');
    return $res->content('Teste de Armazenamento em Cache - Altere-me');
});

$app->route('/examples/cors', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Cross-Origin Resource Sharing (CORS) - Compartilhamento de recursos entre origens
    // CLASS: Web\Response
    // CORS é comumente utilizado em APIs web para compartilhar dados de um site
    // ou domínio com outro domínio (recursos entre origens). Para incluir o
    // cabeçalho 'Access-Control-Allow-Origin' em sua resposta, utilize a função
    // [cors()]. Primeiro, certifique-se de definir os cabeçalhos CORS à partir
    // do Objeto do App.
    $app->cors('*');

    // Se você estiver utilizando o Objeto Response você pode passar o Objeto App para a
    // resposta em sua criação ou para sua função [cors()].
    $res = new \FastSitePHP\Web\Response($app);
    $res->cors($app);

    // Ao passar uma string o 'Access-Control-Allow-Origin' é validado e definido,
    // entretanto se você precisar passar CORS adicionais, utilize um array com
    // cabeçalhos nomeados.
    $app->cors([
        'Access-Control-Allow-Origin' => 'https://www.example.com',
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age' => 86400,
    ]);

    // Se estiver chamando um POST, PUT, DELETE ou outro Método de Requisição
    // você provavelmente precisa tratar requisições OPTIONS. Ao utilizar CORS e
    // uma requisição OPTIONS é processada, FastSItePHP definirá automaticamente
    // o cabeçalho 'Access-Control-Allow-Methods' baseando-se em como rotas são
    // definidas.
    // Para ter certeza que requisições OPTIONS sejam tratadas, primeiro crie
    // uma função que defina o valor CORS.
    $cors = function () use ($app) {
        $app->cors('*');
    };

    // Atribua a Função Filtro para as rotas que utilizam CORS:
    $app->post('/api-data', function() {
        return [ 'exemplo' => 'POST' ];
    })
    ->filter($cors);

    $app->put('/api-data', function() {
        return [ 'exemplo' => 'PUT' ];
    })
    ->filter($cors);

    // Se você não quer permitir que o FastSitePHP manipule requisições OPTIONS,
    // você pode desativar isso utilizando esta opção:
    $app->allow_options_requests = false;
    // EXAMPLE_CODE_END

    // Retorne uma resposta JSON com Request Info de um Cliente
    $req = new \FastSitePHP\Web\Request();
    $app->json_options = JSON_PRETTY_PRINT;
    return [
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
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
})
->filter(function() use ($app) {
    $app->cors('*');
});

$app->get('/examples/secure-cookies', function() use ($app) {
    // Chaves para Criptografia e Assinatura
    // IMPORTANTE - Estas são chaves publicadas são somente para teste, não utilize-as em produção!
    // Utilize a função [generateKey()] para criar suas próprias chaves.
    $app->config['ENCRYPTION_KEY'] = 'eada343fc415625494bfd1b065ba60c2a5c8508d353dbb872378c1356181c84f05c52ff60d1cc157957cbbf0101f9cb7d74b040b57192a6a820b5402132b9ab4';
    $app->config['SIGNING_KEY'] = 'ab2403a36467b59b20cc314bb211e1812668b3bffb00358c161f26fe003073ed';
    $app->config['JWT_KEY'] = 'fkeVxeElykoCBzRTIUjxwTD9MIg71nXxOEQl6HTrIvw=';

    // EXAMPLE_CODE_START
    // TITLE: Cookies Seguros
    // O FastSitePHP permite fácil manipulação de Cookies Seguros (Encrypted,
    // Signed ou JWT). Para utilizar, gere uma chave segura e salve-a com os
    // valores de configuração da app. Para mais sobre configuração e definições
    // de criptografia, veja outros documentos neste site. Chaves robustas são
    // importantes para segurança e são requeridas por padrão.

    // $app->config['ENCRYPTION_KEY'] = 'eada343fc415625494bfd1b065ba...';
    // $app->config['SIGNING_KEY'] = 'ab2403a36467b59b20cc314bb211e18...';
    // $app->config['JWT_KEY'] = 'fkeVxeElykoCBzRTIUjxwTD9MIg71nXxOEQ...';

    // O objeto Request tem três funções que utilizam as chaves de configuração
    // para ler e verificar os cookies seguros. Se os cookies não existirem,
    // forem inválidos, estiverem expirados etc [null] será retornado.
    $req = new \FastSitePHP\Web\Request();
    $decrypted = $req->decryptedCookie('encrypted');
    $verified = $req->verifiedCookie('signed');
    $jwt = $req->jwtCookie('jwt');

    // Dados criptografados e assinados podem ser de qualquer tipo básico
    // [Strings, Números, Objetos etc], enquanto while JWT requer um Objeto ou
    // um Array/Dicionário.
    $text = 'Momento da Requisição: ' . date(DATE_RFC2822);

    $user = new \stdClass;
    $user->id = 123;
    $user->name = 'Admin';
    $user->role = 'Admin';

    // Para enviar com a Response, passe os dados para o método de resposta
    // correspondente. Um terceiro parâmetro opcional de expiração exite para
    // ambas [signedCookie()] e [jwtCookie()], que tem como valor padrão 1 hora.
    // Isto aplica-se para os dados assinados ou JWT e não para o cookie.
    $res = new \FastSitePHP\Web\Response();
    $res->encryptedCookie('encrypted', $text);
    $res->signedCookie('signed', $user, '+20 minutes');
    $res->jwtCookie('jwt', $user, '+20 minutes');
    // EXAMPLE_CODE_END

    // Recarregue pelo menos uma vez para ver os dados. Na primeira vez que você
    // testar, valores nulos serão retornados.
    // Retorna Valores Descriptografados e Verificados.
    // Use as Ferramentas de Desenvolvedores para ver os Cookies.
    return $res->jsonOptions(JSON_PRETTY_PRINT)->json([
        'decrypted' => $decrypted,
        'verified' => $verified,
        'jwt' => $jwt,
    ]);
});

$app->get('/examples/db-query', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Conecte a um Banco de Dados e rode Consultas SQL
    // CLASS: Data\Database
    // O FastSitePHP fornece um classe Database que é uma fina camada que
    // envolve o PDO, reduzindo a quantidade de código necessária ao consultar
    // um banco de dados. Um exemplo adicional nesta página, mostra como
    // utilizar PDO.

    // Conecte a um Bando de Dados. Este exemplo utiliza SQLite com um bd
    // temporário na memória.
    $dsn = 'sqlite::memory:';
    $db = new \FastSitePHP\Data\Database($dsn);

    // Dependendo da conexão, quatro parâmetros adicionais podem também serem
    // utilizados:
    /*
    $user = null;
    $password = null;
    $persistent = false;
    $options = [];
    $db = new Database($dsn, $user, $password, $persistent, $options);
    */

    // Cria tabelas e registros de teste. A função [execute()] é utilizada para
    // consulta de ação (INSERT, UPDATE, DELETE, CREATE etc) e retorna o número
    // de linhas afetadas.

    $db->execute('CREATE TABLE page_types (id INTEGER PRIMARY KEY, page_type)');

    $sql = 'CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT,';
    $sql .= ' type_id, title, content)';
    $db->execute($sql);

    // Este exemplo utiliza áspas duplas para a string ["] por que strings SQL
    // incluem o caractere áspas simples ['] para texto.
    $sql = "INSERT INTO page_types (id, page_type) VALUES (1, 'text/plain')";
    $rows_added = $db->execute($sql);

    // Um segundo parâmetro opcional pode ser utilizado. Isto é recomendado
    // para prevenir ataques de SQL Injection via entradas de usuário. O ponto
    // de interrogação [?] é um caractere que representa um espaço reservado
    // a ser utilizado pela expressão SQL.
    $sql = 'INSERT INTO page_types (id, page_type) VALUES (?, ?)';
    $params = [2, 'text/html'];
    $rows_added += $db->execute($sql, $params);

    // Múltiplos registros pode ser adicionados (ou atualizados etc) ao
    // utilizar [executeMany()]
    $sql = 'INSERT INTO pages (type_id, title, content) VALUES (?, ?, ?)';
    $records = [
        [1, 'Página de Teste em Texto', 'Isto é um teste.'],
        [2, 'Página de Teste em HTML', '<h1>Teste<h1><p>Isto é um teste.</p>'],
    ];
    $rows_added += $db->executeMany($sql, $records);

    // Além de utilizar [?], você também pode utilizar parâmetros nomeados no
    // formato ":name". Parâmetros nomeados pode fazer com que o código seja
    // mais fácil de ler.
    $sql = 'INSERT INTO pages (type_id, title, content)';
    $sql .= ' VALUES (:type_id, :title, :content)';
    $params = [
        'type_id' => 1,
        'title'   => 'Parâmetros Nomeados',
        'content' => 'Teste com Parâmetros Nomeados.',
    ];
    $rows_added += $db->execute($sql, $params);

    // Obtenha a ID da última linha ou valor sequencial inserido
    $last_id = $db->lastInsertId();

    // Consulte Múltiplos Registros
    // Retorna um Array de Registros (Array Associativo para cada Registro).
    $sql = 'SELECT * FROM pages';
    $records = $db->query($sql);

    // Consulta um registro. Retorna um Array Associativo ou [null] se não
    // encontrado. Ambas [query()] e [queryOne()] suportam parâmetros opcionais
    // ao consultar.
    $sql = 'SELECT * FROM pages WHERE id = ?';
    $params = [1];
    $record = $db->queryOne($sql, $params);

    // A classe [Database] também contém funções adicionais tal como
    // [queryValue(), queryList() e querySets()] para simplificar e reduzir a
    // quantidade de código necessária ao trabalhar com bancos de dados.
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $rows_added,
            $last_id,
            json_encode($records, JSON_PRETTY_PRINT),
            json_encode($record, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/db-pdo', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Conecte a um Banco de Dados e rode Consultas SQL utilizando PDO
    // Conecte a um Banco de Dados utilizando PHP Data Objects (PDO). Este
    // exemplo utiliza SQLite com um bd temporário em memória.
    $dsn = 'sqlite::memory:';
    $user = null;
    $password = null;
    $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ];
    $pdo = new \PDO($dsn, $user, $password, $options);

    // Crie tabelas e registros test.

    $pdo->query('CREATE TABLE page_types (id INTEGER PRIMARY KEY, page_type)');

    $sql = 'CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT,';
    $sql .= ' type_id, title, content)';
    $pdo->query($sql);

    // Este exemplo utiliza áspas duplas para a string ["] por que strings SQL
    // incluem o caractere áspas simples ['] para texto.
    $sql = "INSERT INTO page_types (id, page_type) VALUES (1, 'text/plain')";
    $stmt = $pdo->query($sql);
    $rows_added = $stmt->rowCount();

    // Este exemplo utiliza um prepared statement com um array de parâmetros.
    // Isto é recomendado quando houver uma entrada por usuário para prevenir
    // ataques de SQL Injection. O ponto e interrogação [?] é um caractere que
    // representa um espaço reservado a ser utilizado na expressão SQL.
    $sql = 'INSERT INTO page_types (id, page_type) VALUES (?, ?)';
    $params = [2, 'text/html'];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows_added += $stmt->rowCount();

    // Múltiplos registros podem ser adicionados (ou atualizados etc) em um loop
    // utilizando um prepared statement.
    $sql = 'INSERT INTO pages (type_id, title, content) VALUES (?, ?, ?)';
    $records = [
        [1, 'Página de Teste em Texto', 'Isto é um teste.'],
        [2, 'Página de Teste em HTML', '<h1>Teste<h1><p>Isto é um teste.</p>'],
    ];
    $stmt = $pdo->prepare($sql);

    foreach ($records as $record) {
        $stmt->execute($record);
        $rows_added += $stmt->rowCount();
    }

    // Além de utilizar [?], você também pode utilizar parâmetros nomeados no
    // formato ":name". Parâmetros nomeados pode fazer com que o código seja
    // mais fácil de ler.
    $sql = 'INSERT INTO pages (type_id, title, content)';
    $sql .= ' VALUES (:type_id, :title, :content)';
    $params = [
        'type_id' => 1,
        'title'   => 'Parâmetros Nomeados',
        'content' => 'Teste com Parâmetros Nomeados.',
    ];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows_added += $stmt->rowCount();

    // Obtenha a ID da última linha ou valor sequencial inserido
    $last_id = $pdo->lastInsertId();

    // Consulte Múltiplos Registros
    // Retorna um Array de Registros (Array Associativo para cada Registro).
    $sql = 'SELECT * FROM pages';
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Consulta um registro utilizando parâmetros. Retorna um Array Associativo
    // ou [false] se não for encontrado.
    $sql = 'SELECT * FROM pages WHERE id = ?';
    $params = [1];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $record = $stmt->fetch(\PDO::FETCH_ASSOC);

    // As funções [fetchAll()] e [fetch()] também suportam um número de
    // opções para o valor retornado incluindo Indexed-Arrays utilizando
    // [PDO::FETCH_NUM], Objetos Anônimos utilizando [PDO::FETCH_OBJ] e
    // classes personalizadas utilizando [PDO::FETCH_CLASS].
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $rows_added,
            $last_id,
            json_encode($records, JSON_PRETTY_PRINT),
            json_encode($record, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/db-connection', function() use ($app) {
    // Crie um bd SQLite temporário para Teste e adicione algumas tabelas
    $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test.sqlite';
    $db = new \FastSitePHP\Data\Database('sqlite:' . $file_path);
    $db->execute('CREATE TABLE IF NOT EXISTS page_types (id INTEGER PRIMARY KEY AUTOINCREMENT, page_type)');
    $db->execute('CREATE TABLE IF NOT EXISTS pages (id INTEGER PRIMARY KEY AUTOINCREMENT, type_id, title, content)');

    // EXAMPLE_CODE_START
    // TITLE: COnectar a um Banco de Dados
    // CLASS: Data\Database
    // A classe Database do FastSitePHP ou a classe PHP integrada PDO,
    // pode conectar a bancos de dados diferentes. A classe Database do
    // FastSitePHP fornece um classe Database que é uma fina camada que
    // envolve o PDO, reduzindo a quantidade de código necessária ao consultar
    // um banco de dados.

    // O exemplos abaixo mostram coo construir strings de conexão e rodar
    // uma query para um número diferente bancos de dados. Se você baixar
    // este site, o código abaixo pode ser modificado e testado para seu
    // ambiente; or simplesmente copie o que você precisa para seu site
    // ou app.

    // Ao especificar o hostname (Nome do Server), muitas vezes você pode
    // somente especificar o nome do servidor (exemplo: 'db-server') ou
    // o nome de domínio totalmente qualificado (FQDN) (example
    // 'db-server.example.com') baseado-se em como sua rede é configurada.
    // Por exemplo em uma rede interna, simplesmente utilizando o nome
    // de servidor funcionará, mas através de uma VPN é geralmente
    // necessário utilizar o FDQN.

    // ----------------------------------------------------------------------------
    // MySQL
    //   Format Básico:
    //     "mysql:host={hostname};dbname={database}";
    //
    // Este exemplo também mostra o uso da opção [MYSQL_ATTR_INIT_COMMAND]
    // para definir o fuso horário para UTC quando a conexão é criada.
    //
    // Se você tem um site ou aplicação que tem usuários em múltiplos
    // fuso horários ou países, um modelo de aplicação que funciona bem
    // é o de salvar todas as datas e horários em UTC e daí formatar
    // baseando-se no fuso horário selecionado pelo usuário.
    //
    $dsn = 'mysql:host=localhost;dbname=wordpress;charset=utf8';
    $user = 'root';
    $password = 'wordpress';
    $options = [
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
    ];
    $sql = 'SELECT table_schema, table_name';
    $sql .= ' FROM information_schema.tables';
    $sql .= " WHERE table_type = 'BASE TABLE'";

    // ----------------------------------------------------------------------------
    // Oracle
    //   Formato:
    //      "oci:dbname=//{hostname}:{port-number}/{database}"
    $dsn = 'oci:dbname=//server:1521/hr';
    $user = 'sys';
    $password = 'password';
    $options = [];
    $sql = 'SELECT OWNER, TABLE_NAME FROM ALL_TABLES ORDER BY OWNER, TABLE_NAME';

    // Além do formato padrão, você pode também especificar uma string TNS
    // completa
    $tns = '(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)';
    $tns .= '(HOST=server.example.com)(PORT=1521)))';
    $tns .= '(CONNECT_DATA=(SERVICE_NAME=dbname)))';
    $dsn = 'oci:dbname=' . $tns;

    // ----------------------------------------------------------------------------
    // SQL Server
    $dsn = 'sqlsrv:Server=db-server;Database=DbName';
    $user = 'sa';
    $password = 'password';
    $options = [];
    $sql = 'SELECT SCHEMA_NAME(schema_id) AS schema_name, name FROM sys.tables';

    // SQL Server (utilizando ODBC)
    // Se o driver PDO nativo de SQL Server não estiver instalado e o
    // Driver PDO para ODBC estiver e a Conexão ODBC estiver definida,
    // você poderia utilizar isso:
    $dsn = 'odbc:DRIVER={SQL Server};SERVER=db-server;DATABASE=DbName;';

    // ----------------------------------------------------------------------------
    // IBM (utilizando ODBC)
    // Este exemplo mostra uma conexão a um IBM DB2 ou AS/400 através do
    // iSeries.
    // Opções ODBC variarão com base no driver instalado ou utilizado.
    $dsn = 'odbc:DRIVER={iSeries Access ODBC Driver};';
    $dsn .= 'HOSTNAME=AS400.EXAMPLE.COM;';
    $dsn .= 'PORT=56789;';
    $dsn .= 'SYSTEM=SYSTEM;';
    $dsn .= 'PROTOCOL=TCPIP;';
    $dsn .= 'UID=USER;';
    $dsn .= 'PWD=PASSWORD;';
    $user = null;
    $password = null;
    $options = [];
    $sql = 'SELECT SYSTEM_TABLE_SCHEMA, TABLE_NAME, TABLE_TEXT';
    $sql .= ' FROM QSYS2.SYSTABLES';
    $sql .= " WHERE SYSTEM_TABLE_SCHEMA IN 'QSYS'";
    $sql .= ' ORDER BY SYSTEM_TABLE_SCHEMA, TABLE_NAME';
    $sql .= ' FETCH FIRST 100 ROWS ONLY';

    // ----------------------------------------------------------------------------
    // PostgreSQL
    $dsn = 'pgsql:host=localhost;port=5432;dbname=dbname;';
    $user = 'postgres';
    $password = 'password';
    $options = [];
    $sql = 'SELECT table_schema, table_name';
    $sql .= ' FROM information_schema.tables';
    $sql .= " WHERE table_type = 'BASE TABLE'";

    // ----------------------------------------------------------------------------
    // SQLite
    //   Exemplo utilizando um caminho de arquivo:
    //     'sqlite:/var/www/app_data/db.sqlite'
    //     'sqlite:C:\inetpub\wwwroot\db.sqlite'
    //   Banco de Dados em Memória:
    //     'sqlite::memory:'
    $dsn = 'sqlite:' . $file_path;
    $user = null;
    $password = null;
    $options = [];
    $sql = 'SELECT * FROM sqlite_master';

    // ----------------------------------------------------------------------------
    // Opção e Conexão Persistente
    //
    // Muitos drivers de Bancos de Dados PHP suportam conexões persistentes
    // o que permite um melhor desempenho.
    $persistent = false;

    // ============================================================================
    // Conecte utilizando PHP Data Objects (PDO)
    $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
    if ($persistent) {
        $options[\PDO::ATTR_PERSISTENT] = true;
    }
    $pdo = new \PDO($dsn, $user, $password, $options);

    // Consulte utilizando PDO
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // =================================================================================
    // Conecte e Consulte utilizando a classe Database do FastSitePHP.
    // Somente o DSN (Data Source Name/Nome da Fonte de Dados) é um parâmetro necessário.
    $db = new \FastSitePHP\Data\Database($dsn, $user, $password, $persistent, $options);
    $records = $db->query($sql);

    // =================================================================================
    // Além da classe Database do FastSitePHP, [OdbcDatabase] e [Db2Database] também
    // podem ser utilizadas para suportar ambientes, e especialmente Bancos de Dados IBM.
    //
    // Ao utilizar a classe [OdbcDatabase] o DSN será o mesmo que o do PDO excluindo
    // o prefixo 'odbc:'.
    /*
    $odbc = new OdbcDatabase($dsn, $user, $password, $persistent, $options);
    $db2  = new Db2Database($dsn, $user, $password, $persistent, $options);
    */

    // ============================================================================
    // Lazy Loading com FastSitePHP
    //
    // O objeto Application do FastSitePHP tem uma função [lazyLoad()] que aceita
    // um nome de propriedade e função callback. Isso cria o objeto como uma
    // propriedade da app somente se utilizada. Isto é ideal para trabalhar com
    // sites onde algumas páginas conectam a um banco de dados e algumas não, ou
    // se você tem um site que conecta-se à múltiplos bacos de dados mas nem todas
    // as páginas utilizam à cada um.
    $app->lazyLoad('db', function() use ($dsn, $user, $password) {
        return new \FastSitePHP\Data\Database($dsn, $user, $password);
    });

    // Consulta para registros. O banco de dados é conectado aqui somente quando usado pela primeira vez.
    $records = $app->db->query($sql);

    // ============================================================================
    // Para obter uma lista dos drivers disponíveis no computador chame
    // [phpinfo()] e veja o resultado ou chame a seguinte função para obter uma
    // array de nomes de drivers. Uma lista completa de drivers PDO pode ser
    // encontrada em:
    //   http://php.net/manual/en/pdo.drivers.php
    // Se você precisar de um driver que não estiver disponível ou ativado em
    // seu servidor, eles geralmente são fáceis de serem instalados e ativados.
    $drivers = \PDO::getAvailableDrivers();
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($drivers),
            json_encode($records, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/data-validator', function() use ($app) {
    // Definia manualmente valores POST para a demonstração. Em PHP, variáveis
    // Superglobais como [$_POST] podem ser sobrescritas.
    $_POST = [
        'age' => '10',
        'phone' => 123,
        'site_user' => 'user',
        'site_password' => 'password',
    ];

    // EXAMPLE_CODE_START
    // TITLE: Valiando Entradas de Usuário
    // CLASS: Data\Validator
    // Para muitos apps validação client side (webpage ou app) fornece retorno
    // imediato para usuários e limita a necessidade de requisição web extra,
    // porém usuários podem iludir a validação utilizando DevTools ou outros
    // métodos, então para os dados que precisam ser validados utilizar
    // validação server-side é importante.

    // O FastSitePHP provê uma classe que permite que várias regras sejam
    // facilmente definidas e rodem contra um objeto (ou Array
    // Associativo/Dicionário).

    // Regras comuns podem ser copiadas de forma simples de controles Input HTML.

    // HTML Exemplo:
    /*
        <input name="nome" title="Nome" required>
        <input name="idade" title="Idade" required min="13" max="99">
        <input name="telefone" title="Telefone" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}">
    */

    // O Código do FastSitePHP Code para Validar Postagem de Formulário
    // utilizando o HTML acima.
    // Campos de Postagem de Formulários vem na array PHP Superglobal [$_POST]
    // e isso pode ser simplesmente passado para a classe [Validator].
    $v = new \FastSitePHP\Data\Validator();
    $v->addRules([
        // Campo,  Título,  Regras
        ['nome',  'Nome',  'required'],
        ['idade',   'Idade',   'required min="13" max="99"'],
        ['telefone', 'Telefone', 'pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"'],
    ]);
    list($errors, $fields) = $v->validate($_POST);
    if ($errors) {
        // Lógica de Erros
        // [$errors] retorna uma array de mensagens de erro para o usuário final
        // [$fields] retorna uma array de campos únicos que tiveram um erro em
        // conjunto com uma array de mensagens de erro para cada campo.
        // Campos pode ser utilizados pelo app cliente para evidenciar campos de
        // formulário, etc.
    }

    // Além da utilização de strings para as regras você pode também utilizar
    // arrays. Isso pode conceder melhor desempenho se você tiver um site de
    // alto tráfego, contudo, isso roda de forma muito rápida de qualquer forma.
    $v = new \FastSitePHP\Data\Validator();
    $v->addRules([
        ['nomes',  'Nome',  ['required' => true]],
        ['idade',   'Idade',   [
            'required' => true,
            'min' => '13',
            'max' => '99',
        ]],
        ['telefone', 'Telefone', ['pattern' => '[0-9]{3}-[0-9]{3}-[0-9]{4}']],
    ]);

    // A classe Validator suporta um número de regras HTML5 juntamente com
    // algumas regras personalizadas:
    //     'exists', 'required', 'type', 'minlength', 'maxlength',
    //     'length', 'min', 'max', 'pattern', 'list',

    // A regra [type] suporta vários tipos de dados HTML5, junto com muitos
    // tipos de dados personalizados:
    //      'text', 'password', 'tel', 'number', 'range', 'date',
    //      'time', 'datetime', 'datetime-local', 'email', 'url',
    //      'unicode-email', 'int', 'float', 'json', 'base64',
    //      'base64url', 'xml', 'bool', 'timezone', 'ip', 'ipv4',
    //      'ipv6', 'cidr', 'cidr-ipv4', 'cidr-ipv6',

    // Além das regras padrão, regras personalizadas pode ser definidas
    // utilizando funções callback que retornam true/false ou uma string de
    // mensagem personalizada de erro
    $v = new \FastSitePHP\Data\Validator();
    $v
        ->addRules([
            ['site_user',     'Site User', 'check-user required'],
            ['site_password', 'Password',  'check-password required'],
        ])
        ->customRule('check-user', function($value) {
            return ($value === 'admin');
        })
        ->customRule('check-password', function($value) {
            return ($value === 'secret' ? true : 'Senha Inválida');
        });

    list($errors, $fields) = $v->validate($_POST);
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($errors, JSON_PRETTY_PRINT),
            json_encode($fields, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/http-client', function() use ($app) {
    // Demonstração inicial que roda a não ser que o código seja modificado.
    // Baixe e retorne um página HTML.
    // Comente iso para rodar o código completo.
    return \FastSitePHP\Net\HttpClient::get('https://www.example.com/')->content;

    // Para utilizar, modifique esses para valores válidos em seu sistema
    // Salvando um arquivo requer acesso de escrita no diretório atual.
    $save_path = __DIR__ . '/test-download.txt';
    $url = 'https://httpbin.org/anything';
    $file_path = 'C:\Users\Public\Pictures\Thumbnails\Desert.jpg';
    if (!is_file($file_path)) {
        return 'Modifique o código para apontar para um arquivo real';
    }

    // EXAMPLE_CODE_START
    // TITLE: Utilizando o Cliente HTTP
    // CLASS: Net\HttpClient, Net\HttpResponse
    // HttpClient pode ser utilizado para simplificar a comunicação com
    // outros serviços web, APIs HTTP e funciona muito bem para chamar
    // e retornar o resultado de serviços locais - por exemplo um serviço
    // AI/ML (Artificial Intelligence / Machine Learning) escrito em
    // Python com TensorFlow ou scikit-learn.

    // Faça um requisição HTTP GET simples e verifique o resultado
    $res = \FastSitePHP\Net\HttpClient::get($url);
    if ($res->error) {
        // Um erro seria retornado em uma eventual falha grave como tempo limite
        // atingido ou um erro de certificado SSL. Uma resposta 404 ou 500 do
        // servidor seria tratada verificando o [status_code].
        $error = $res->error;
    } else {
        $status_code = $res->status_code; // 200, 404, 500 etc
        $headers = $res->headers; // Array de Cabeçalhos de Resposta
        $content = $res->content; // Conteúdo da Resposta como uma String - HTML, Texto etc
        $info = $res->info; // Array de Informações como Estatísticas de Tempo
    }

    // Realize uma Requisição HTTP GET e leia o Resultado JSON. Se o Content-Type
    // da Resposta for 'application/json' então [$res->json] conterá um array
    // caso contrário, conterá null. Cabeçalhos de Requisição podem receber um
    // parâmetro opcional.
    $headers = [
        'X-API-Key' => 'ab82050cf5907934fa1d0f6f66284642a01d1ba2280656870c',
        'X-Custom-Header' => 'Teste',
    ];
    $res_json = \FastSitePHP\Net\HttpClient::get($url, $headers);
    $json = $res->json;
    $text = $res->content;

    // Envie uma Requisição HTTP POST como JSON e também como um Form.
    // Dados podem ser um Array ou um Objeto e Cabeçalhos são opcionais.
    $data = [
        'text' => 'teste',
        'num' => 123,
    ];
    $res_post = \FastSitePHP\Net\HttpClient::postJson($url, $data, $headers);
    $res_form = \FastSitePHP\Net\HttpClient::postForm($url, $data);

    // Ao utilizar PHP 5.5 ou mais recente, 'multipart/form-data' Form Posts são
    // suportados com a classe integrada [CURLFile]:
    /*
    $data = [
        'field1' => 'teste',
        'file' => new \CURLFile($file_path),
    ];
    */

    // Salve o Conteúdo da Resposta como um Download de Arquivo
    // Assim como [postJson ()] e [postForm ()] Request Headers são opcionais.
    $res_file = \FastSitePHP\Net\HttpClient::downloadFile($url, $save_path, $headers);
    $saved_path = $res_file->content;

    // O código de demonstração acima mostra as quatro funções estáticas
    // auxiliares [get(), postJson(), postForm() e downloadFile()]. Opções
    // adicionais estão disponíveis quando utilizar a HttpClient como um
    // objeto com o mesmo método [request()].

    // Envie uma Requisição PUT com um arquivo como o Corpo de Requisição
    $http = new \FastSitePHP\Net\HttpClient();
    $res_put = $http->request($url, [
        'method' => 'PUT',
        'headers' => $headers,
        'send_file' => $file_path,
    ]);
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $http_res = $res;
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            'GET = ',
            json_encode($http_res, JSON_PRETTY_PRINT),
            str_repeat('-', 80),
            'GET (JSON) = ',
            json_encode($res_json, JSON_PRETTY_PRINT),
            str_repeat('-', 80),
            'POST (JSON) = ',
            json_encode($res_post, JSON_PRETTY_PRINT),
            str_repeat('-', 80),
            'POST (Form) = ',
            json_encode($res_form, JSON_PRETTY_PRINT),
            str_repeat('-', 80),
            'Download (File) = ',
            json_encode($res_file, JSON_PRETTY_PRINT),
            str_repeat('-', 80),
            'PUT (File) = ',
            json_encode($res_put, JSON_PRETTY_PRINT),
        ]));
});

// ** NOTA - utilizar o exemplo abaixo requer um Serviço GraphQL em localhost
//      Então você precisará adicionar uma string de consulta ou utilizar um
//      GraphQL POST padrão. Exemplos:
//          ?query={countries{iso,country}}
//          ?query=query($country:String!){regions(country:$country){name}}&variables={"country":"US"}
//
// EXAMPLE_CODE_START
// TITLE: Serviço GraphQL utilizando HttpClient
// FIND_REPLACE: {"/examples":""}
// GraphQL é uma tecnologia popular para desenvolver APIs. Ela foi portada para
// muitas linguagens incluindo PHP, contudo, a implementação referencial, a versão
// mais comumente utilizada e também de alto desempenho é a GraphQL com NodeJS e
// Express. Esta rota pode ser copiada ou modificada para permitir utilizar
// GraphQL à partir do PHP utilizando qualquer serviço GraphQL no localhost ou
// de outra URL.
$app->route('/examples/graphql', function() {
    try {
        $url = 'http://localhost:4000/graphql';

        // Se um Cabeçalho de Requisição 'Authorization' foi enviado, então,
        // passe-o para o Serviço GraphQL.
        $req = new \FastSitePHP\Web\Request();
        $auth = $req->header('Authorization');
        $headers = ($auth === null ? null : ['Authorization' => $auth]);

        // Submit GraphQL Request
        if ($req->method() === 'GET') {
            $url .= '?query=' . urlencode($req->queryString('query'));
            $url .= '&variables=' . urlencode($req->queryString('variables'));
            $url .= '&operationName=' . urlencode($req->queryString('operationName'));
            $res = \FastSitePHP\Net\HttpClient::get($url, $headers);
        } else {
            $res = \FastSitePHP\Net\HttpClient::postJson(
                $url,
                $req->content(),
                $headers
            );
        }

        // Verifique a Resposta, um erro tipicamente ocorreria não para erros
        // de dados, mas sim para erros HTTP (i.e.: Se o serviço estiver caído).
        if ($res->error) {
            throw new \Exception($res->error);
        }

        // Retorne Objeto para uma Resposta JSON
        return $res->json;
    } catch (\Exception $e) {
        // Retorne erro inesperado como uma resposta 200 utilizando o formato
        // de erro padrão usado pelo GraphQL.
        return [
            'errors' => [
                ['message' => $e->getMessage()]
            ],
        ];
    }
})->filter(function() use ($app) {
    // Utilize CORS para permitir que páginas web acessem este serviço de
    // qualquer host (URL)
    if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] !== 'null') {
        $app->cors([
            'Access-Control-Allow-Origin' => $_SERVER['HTTP_ORIGIN'],
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    } else {
        $app->cors('*');
    }
});
// EXAMPLE_CODE_END

$app->get('/examples/smtp-client', function() use ($app) {
    // NOTA - para rodar isto, modifique o código com um
    // Servidor SMTP que vocẽ tenha acesso e descomente a
    // linha [return] do código abaixo. Se você tiver uma
    // conta GMail, você pode usá-la para testar esta
    // função. Possivelmente isso falhará de início e
    // fornecerá uma mensagem de configurações que você
    // precisa definir para que o gmail funcione para
    // envio através de SMTP.
    //
    // Se você não tem acesso a um servidor de e-mail e quer
    // testar este código, então, comente o código antes da
    // linha [$timeout = 2;] e rode somente Comandos SMTP no
    // Gmail sem enviar um e-mail.
    //
    return 'Modifique o código para rodar';

    // Gera Texto Puro. Com este código exemplo, quando logging é utilizado,
    // mensagens são enviadas assim que elas ocorrem..
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: Envia um E-mail via um Servidor SMTP
    // CLASS: Net\SmtpClient, Net\Email
    // Defina as Configurações de E-mail
    $from = 'noreply@example.com';
    $to = 'user.name@example.com';
    $subject = 'E-mail de Teste de FastSitePHP em ' . date(DATE_RFC2822);
    $body = '<h1>Título do E-mail</h1><p style="color:blue;">Isto é um teste.</p>';

    // Cria um Objeto E-mail
    $email = new \FastSitePHP\Net\Email($from, $to, $subject, $body);

    // A Classe Email também tem várias definições adicionais e pode ser criada
    // sem especificar quaisquer parâmetros. Ao definir os endereços de e-mail
    // de [From] ou [Reply-To], um os seguintes formatos pode ser utilizado:
    //   String: 'Email Address'
    //   Array: ['Email', 'Name']
    // E quando especificar para quem enviar o e-mail para qualquer um dos
    // formatos, pode utilizar:
    //   String 'Endereço de E-mail'
    //   Array: ['E-mail', 'Nome']
    //   Array: ['Endereço de E-mail 1', 'Endereço de E-mail 2', '...']
    /*
    $email = new \FastSitePHP\Net\Email();
    $email
        ->from(['noreply@example.com', 'No Reply'])
        ->replyTo('test@example.com')
        ->to(['email1@example.com', 'email2@example.com'])
        ->cc('email3@example.com')
        ->bcc('email4@example.com')
        ->priority('High')
        ->header('X-Transaction-ID', '123abc');
    */

    // Arquivos anexos também são suportados:
    //
    // $email->attachFile($file_path);

    // Servidores SMTP que suportam E-mails Unicode pode utilizar
    // [allowUnicodeEmails(true)]. Quando utilizado, O Cliente SMTP envia uma
    // opção SMTPUTF8 se o servidor suportá-la.
    //
    // $email->allowUnicodeEmails(true)->from('无回复@example.com');

    // Configurações SMTP
    $host = 'smtp.example.com';
    $port = 25;
    $auth_user = null;
    $auth_pass = null;

    // Cria Cliente SMTP e Envia E-mail.
    // Uma vez que a variável para o Client SMTP não estiver mais em uso ou
    // definida como null, então, um comando 'QUIT' é automaticamente enviado
    // para o Servidor SMTP e a conexão é fechada.
    $smtp = new \FastSitePHP\Net\SmtpClient($host, $port);
    if ($auth_user !== null) {
        $smtp->auth($auth_user, $auth_pass);
    }
    $smtp->send($email);
    $smtp = null;

    // Opções adicionais podem ser especificadas, em segundos, para timeout e
    // para logging
    $timeout = 2;
    $debug_callback = function($message) {
        echo '[' . date('H:i:s') . '] ' . trim($message) . "\n";
    };

    // A Classe [SmtpClient] também suporta uma API de fácil utilização para
    // comunicar com Servidores SMTP. Neste exemplo Gmail é utilizado e diversos
    // comandos são realizados. Mensagens são logadas para a função
    // [$debug_callback].
    $host = 'smtp.gmail.com';
    $port = 587;
    $smtp2 = new \FastSitePHP\Net\SmtpClient($host, $port, $timeout, $debug_callback);
    $smtp2->help();
    $smtp2->noop();
    $smtp2->quit();
    $smtp2->close();

    // Um ou mais e-mails pode também ser enviados utilizando Valores de
    // Configuração de App ou Variáveis de Ambiente do Sistema. Este tipo de
    // configuração pode ser utilizada para prevenir que dados de autenticação
    // sensíveis sejam salvos com o código lógico principal.
    /*
    $app->config['SMTP_HOST'] = $host;
    $app->config['SMTP_PORT'] = $port;
    $app->config['SMTP_TIMEOUT'] = $timeout;
    $app->config['SMTP_USER'] = $auth_user;
    $app->config['SMTP_PASSWORD'] = $auth_pass;

    \FastSitePHP\Net\SmtpClient::sendEmails([$email]);
    */
    // EXAMPLE_CODE_END
});

$app->get('/examples/file-system-search', function() use ($app) {
    $dir_path = __DIR__ . '/../../vendor/fastsitephp';

    // EXAMPLE_CODE_START
    // TITLE: Busque por Arquivos e Diretórios (Pastas)
    // CLASS: FileSystem\Search
    // Crie um Objeto Search do Sistema de Arquivos
    $search = new \FastSitePHP\FileSystem\Search();

    // Para utilização básica, especifique um diretório raiz com o comando
    // [dir()] e então chame [files()] ou [dirs()]. Um array the nomes correspondentes
    // será retornado.
    $files = $search->dir($dir_path)->files();

    // Funções são encadeáveis então quebrá-las em uma por linha pode tornar o
    // código mais fácil de ler.
    $dirs = $search
        ->dir($dir_path)
        ->dirs();

    // URL lists can also be generated from matching files.
    $url_root = 'http://www.example.com/';
    $urls = $search
        ->dir($dir_path)
        ->urlFiles($url_root);

    // Existem várias funções com critérios diferentes e podem ser utilizadas
    // para filtrar os resultados. Neste exemplo uma busca recursiva é utilizada
    // para encontrar arquivos PHP que contenham o texto 'FileSystem'. Quando
    // uma busca recursiva é utilizada, o caminho completo dos arquivos é
    // retornado a não ser que [includeRoot(false)] esteja definida.
    // Veja a documentação e exemplos para todas as funções.
    $files = $search
        ->dir($dir_path)
        ->recursive(true)
        ->fileTypes(['php'])
        ->includeText(['FileSystem'])
        ->files();
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            realpath($dir_path),
            json_encode($urls, JSON_PRETTY_PRINT),
            json_encode($files, JSON_PRETTY_PRINT),
            json_encode($dirs, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/markdown', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Converta Markdown para HTML utilizando PHP
    // O FastSitePHP inclui a biblioteca de alto desempenho Parsedown para
    // converter o formato Markdown para HTML.

    // Certifique-se de carrear o autoloader do fornecedor
    require '../../../vendor/autoload.php';

    // Crie o Objeto Parsedown
    $Parsedown = new Parsedown();

    // Converta para HTML de uma String de Texto
    $html = $Parsedown->text('Olá **FastSitePHP**!');

    // Leia um Arquivo e converta para HTML
    $file_path = __DIR__ . '/views/example.md';
    $md = file_get_contents($file_path);
    $html = $Parsedown->text($md);
    // EXAMPLE_CODE_END
    return $html;
});

$app->get('/examples/logging', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Logging
    // CLASS: Data\Log\FileLogger, Data\Log\HtmlLogger
    // O FastSitePHP inclui duas classes de logging que implementam a amplamente
    // utilizada Interface [Psr\Log].

    // Cria um arquivo logger. Mensagens de log são adicionadas e o arquivo é
    // criado quando a primeira mensagem é adicionada.
    $file = __DIR__ . '/log.txt';
    $file_logger = new \FastSitePHP\Data\Log\FileLogger($file);

    // Crie um Logger HTML
    // Esta classe pode ser utilizada para logs temporários de desenvolvimento
    // porque isto gera uma tabela HTML das mensagens registradas depois que a
    // resposta é enviada ou, dependendo das opções, pode ser utilizada para
    // substituir a resposta original. O parâmetro [$replace_response] é opcional.
    $replace_response = false;
    $html_logger = new \FastSitePHP\Data\Log\HtmlLogger($app, $replace_response);

    // Registre mensagens utilizando uma das seguintes funções:
    //     emergency(), alert(), critical(), error(),
    //     warning(), notice(), info(), debug()
    $file_logger->info('Isto é um teste.');
    $html_logger->error('Aplicação de Teste');

    // Dados adicionais podem ser passados para a mensagem através de espaços
    // reservados
    $html_logger->info('Usuário {name} criado', [
        'name' => 'Admin'
    ]);

    // O formato de data pode ser qualquer valor válido para a função [date()] do PHP.
    // O padrão é [\DateTime::ISO8601].
    $file_logger->date_format = 'Y-m-d H:i:s';

    // Para o arquivo de registro o formato gerado pode ser controlado pelas
    // propriedades.
    //
    // Formato Padrão:
    //     '{date} {level} - {message}{line_break}';
    //
    // Quebras de Linha padrão baseando-se no Sistema Operacional (SO):
    //     "\r\n" - Windows
    //     "\n"   - Outros SOs
    $file_logger->log_format = '[{level}] {message}{line_break}';
    $file_logger->line_break = '^^';

    // Você pode também personalizar o HTML Logger com seu próprio modelo:
    // $html_logger->template_file = 'SEU_MODELO.php';
    // EXAMPLE_CODE_END

    $html = '<html><body style="background-color:green; padding:0;"><div style="padding:20px;">';
    $html .= 'Class = ' . get_class($html_logger);
    $html .= '<br>Psr\Log\LoggerInterface = ' . json_encode($html_logger instanceof Psr\Log\LoggerInterface);
    $html .= '</body></html>';
    return $html;
});

$app->get('/examples/network-info', function() {
    // EXAMPLE_CODE_START
    // TITLE: Obtenha Informações de Rede e do Servidor
    // CLASS: Net\Config
    // Crie um Objeto de Configuração de Rede
    $config = new \FastSitePHP\Net\Config();

    // Obtenha um (fqdn) 'fully-qualified domain name' para o servidor ['servidor.example.com']
    $host = $config->fqdn();

    // Obtenha o endereço IPv4 da Rede para o computador ou servidor
    $ip = $config->networkIp();

    // Obtenha uma lista de todos os endereços IPv4 para o computador ou servidor
    $ip_list = $config->networkIpList();

    // Obtenha um string de texto de informações do servidor utilizando um
    // dos seguintes comandos:
    // - Linux / Unix = [ip addr] ou [ifconfig]
    // - Mac          = [ifconfig]
    // - Windows      = [ipconfig]
    $info = $config->networkInfo();

    // Converte a String de Informações de Rede em um Objeto
    $info = $config->parseNetworkInfo($info);
    // EXAMPLE_CODE_END

    // Format e retorna como uma resposta em texto
    $ip_list = json_encode($ip_list, JSON_PRETTY_PRINT);
    $info = json_encode($info, JSON_PRETTY_PRINT);
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Host: {$host}",
            "Network IP: {$ip}",
            "IP List: {$ip_list}",
            str_repeat('-', 80),
            $config->networkInfo(),
            str_repeat('-', 80),
            $info,
        ]));
});

$app->get('/examples/environ-system', function() {
    // EXAMPLE_CODE_START
    // TITLE: Obtém Informações de Ambiente e Sistema
    // CLASS: Environment\System
    // Cria um Objeto do Sistema de Ambiente
    $sys = new \FastSitePHP\Environment\System();

    // Obtém um array de informações básicas relacionadas ao Sistema Operacional
    // [ 'OS Type', 'Version Info', 'Release Version', 'Host Name', 'CPU Type' ]
    $os_info = $sys->osVersionInfo();

    // Obtém uma string de texto de informações detalhadas do sistema utilizando
    // um dos seguintes comandos:
    // - Linux   = File: '/etc/os-release'
    // - FreeBSD = uname -mrs
    // - IBM AIX = uname -a
    // - Mac     = system_profiler SPSoftwareDataType SPHardwareDataType
    // - Windows = ver
    $info = $sys->systemInfo();

    // Obtém um array de informações relacionado espaço livre, usado e total
    // para um drive do sistema de arquivos ou partição do disco. Esta função
    // permite que drives específicos ou partições sejam especificadas.
    // - *nix    = $sys->diskSpace('/dev/disk0')
    // - Windows = $sys->diskSpace('C:')
    $disk_space = $sys->diskSpace();

    // Função somente de Windows que retorna um array de letras de unidades
    // mapeadas para o servidor. Retorna um array vazio para outros SOs.
    $mapped_drives = $sys->mappedDrives();
    // EXAMPLE_CODE_END

    // Formate e retorne como uma resposta em texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($os_info, JSON_PRETTY_PRINT),
            str_repeat('-', 80),
            $info,
            str_repeat('-', 80),
            json_encode($disk_space, JSON_PRETTY_PRINT),
            str_repeat('-', 80),
            json_encode($mapped_drives, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/environ-dotenv', function() {
    // Para rodar isto sem erros, adicione um arquivo [.env] a este diretório
    // ou modifique o código abaixo.
    // Utilizando código padrão, um erro será lançado até que as chaves
    // necessárias também sejam adicionadas.
    $dir = __DIR__;
    $file_path = __DIR__ . '/.env';

    // EXAMPLE_CODE_START
    // TITLE: Utilize um arquivo [.env]
    // CLASS: Environment\DotEnv
    // Carrega variáveis de ambiente de um arquivo [.env] para dentro de
    // [getenv()] e [$_ENV]. O FastSitePHP DotEnv é um porte do pacote Node
    // [dotenv] então a mesma sintaxe utilizada por projetos Node é suportada.
    $vars = \FastSitePHP\Environment\DotEnv::load($dir);

    // Utilize variáveis de um arquivo depois de lê-lo. Variáveis são somente
    // definidas de um arquivo se elas ainda não existirem.
    $value = getenv('DB_CONNECTION');
    $value = $_ENV['DB_CONNECTION'];

    // Carregue um arquivo utilizando o formato [.env]. O caminho completo do
    // arquivo é especificado de forma que possa receber qualquer nome.
    $vars = \FastSitePHP\Environment\DotEnv::loadFile($file_path);

    // Opcionalmente, exija que hajam chaves no arquivo.
    $required_vars = ['DB_ORACLE', 'DB_SQL_SERVER'];
    $vars = \FastSitePHP\Environment\DotEnv::load($dir, $required_vars);
    // EXAMPLE_CODE_END

    // Formate  retorne como uma resposta em texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(json_encode($vars, JSON_PRETTY_PRINT));
});

$app->get('/examples/encryption', function() use ($app) {
    $data = ['User'=>'Admin', 'Password'=>'123'];

    // EXAMPLE_CODE_START
    // TITLE: Segurança - Criptografe e Descriptografe Dados
    // CLASS: Security\Crypto\Encryption, Security\Crypto
    // Gere uma Chave para Criptografia.
    // A chave é uma longa string hexadecimal de bytes aleatórios seguros.
    // A chave seria tipicamente salva com seu app ou nas configurações.
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();
    $key = $crypto->generateKey();

    // Criptografe e Descriptografe utilizando a classe auxiliar Cypto com
    // definições de configuração.
    // Dados de diferentes tipos de dados pode ser criptografados e retornados
    // no mesmo formato (string, int, objeto etc).
    $app->config['ENCRYPTION_KEY'] = $key;
    $encrypted_text = \FastSitePHP\Security\Crypto::encrypt($data);
    $decrypted_data = \FastSitePHP\Security\Crypto::decrypt($encrypted_text);

    // Criptografe e Descriptografe utilizando a classe Encryption. Esta classe
    // fornece muitas opções adicionais que não estão na classe auxiliar.
    $encrypted_text = $crypto->encrypt($data, $key);
    $decrypted_data = $crypto->decrypt($encrypted_text, $key);

    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Key: {$key}",
            "Encrypted: {$encrypted_text}",
            'Decrypted: ' . json_encode($decrypted_data),
        ]));
});

$app->get('/examples/file-encryption', function() use ($app) {
    // Gere um Arquivo Aleatório
    $rand = \bin2hex(\FastSitePHP\Security\Crypto\Random::bytes(6));
    $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crypto_test_' . $rand;
    file_put_contents($file_path, 'Isto é um Teste');

    // EXAMPLE_CODE_START
    // TITLE: Segurança - Criptografe e Descriptografe um Arquivo
    // CLASS: Security\Crypto\FileEncryption, Security\Crypto
    // O FastSitePHP permite uma autenticação de criptografia rápida de qualquer
    // tamanho de arquivo(mesmo grandes arquivos que estão nos gigabytes de
    // tamanho). O código utilizado para a criptografia é compatível com comandos
    // de shell e um script Bash [encrypt.sh] que funciona em Computadores
    // Linux e Unix. O script Bash pode ser baixado deste site e funcionará na
    // maioria dos sistemas Linux sem que nada seja instalado.

    // Gere uma Chave para Criptografia
    $crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
    $key = $crypto->generateKey();

    // Construa caminhos de arquivos para salvar, baseando-se no nome original
    $enc_file = $file_path . '.enc';
    $output_file = $enc_file . '.decrypted';

    // Criptografe e Descriptografe utilizando a classe auxiliar Crypto com
    // definições de configuração. Uma classe [FileEncryption] também existe com
    // opções adicionais.
    $app->config['ENCRYPTION_KEY'] = $key;
    \FastSitePHP\Security\Crypto::encryptFile($file_path, $enc_file);
    \FastSitePHP\Security\Crypto::decryptFile($enc_file, $output_file);
    // EXAMPLE_CODE_END

    // Leia Arquivos para a Resposta
    $contents_start = file_get_contents($file_path);
    $contents_enc = bin2hex(file_get_contents($enc_file));
    $contents_dec = file_get_contents($output_file);

    // Apague aquivos criados
    // Para ver aquivos, descomente este código, então, veja os arquivos mais
    // recentes na pasta temporária.
	$files = array($file_path, $enc_file, $output_file);
	foreach ($files as $file) {
		if (is_file($file)) {
			unlink($file);
		}
	}

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Key: {$key}",
            "Path: {$file_path}",
            "Start: {$contents_start}",
            "Encrypted: {$contents_enc}",
            "Decrypted: {$contents_dec}",
        ]));
});

$app->get('/examples/jwt-hmac', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Segurança - Codifique e Decodifique um Token JSON Web (JWT)
    // CLASS: Security\Crypto\JWT, Security\Crypto
    // A carga do JWT pode ser um Objeto ou um Array (Dicionário).
    $payload = [
        'User' => 'John Doe',
        'Roles' => ['Admin', 'SQL Editor']
    ];

    // Gere um Chave para Codificação (Assinando).
    // A chave é uma longa string hexadecimal de bytes aleatórios seguros.
    // A chave seria tipicamente salva com seu app ou nas configurações.
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt->generateKey();

    // Codifique e Decodifique o JWT com a Classe Auxiliar Crypto e Definições
    // de Configuração.
    // Ao utilizar os parâmetros padrão com ao classe auxiliar, os dados tem
    // validade de uma hora.
    $app->config['JWT_KEY'] = $key;
    $token = \FastSitePHP\Security\Crypto::encodeJWT($payload);
    $data  = \FastSitePHP\Security\Crypto::decodeJWT($token);

    // Codifique (Assine) e Decodifique (Verifique) utilizando a classe JWT. Ao
    // utilizar as definições padrão com a classe JWT, nenhuma expiração é
    // especificada, todas as reivindicações são validadas e uma chave é
    // necessária.
    $token = $jwt->encode($payload, $key);
    $data  = $jwt->decode($token, $key);

    // Adicione Reivindicações à Carga Válida e utilize uma Chave Insegura para
    // Compatibilidade com ouros sits (Geralmente demonstrações online de JWT
    // são mostradas utilizando senhas simples para a chave). Por padrão, chaves
    // necessitam ser seguras, com comprimento apropriado e no formato Base64 ou
    // Hexadecimal.

    $payload = $jwt->addClaim($payload, 'exp', '+10 minutes');
    $payload = $jwt->addClaim($payload, 'iss', 'example.com');

    $jwt
        ->useInsecureKey(true)
        ->allowedIssuers(['example.com']);

    $insecure_key = 'password123';
    $token = $jwt->encode($payload, $insecure_key);
    $data  = $jwt->decode($token, $insecure_key);
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Key: {$key}",
            "Token: {$token}",
            'Verified: ' . json_encode($data),
        ]));
});

$app->get('/examples/jwt-rsa', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Segurança - Codifique e Decodifique o JWT utilizado RSA
    // CLASS: Security\Crypto\JWT
    // A carga do JWT pode ser um Objeto ou um Array (Dicionário)
    $payload = new \stdClass;
    $payload->User = 'John Doe';
    $payload->Roles = ['Admin', 'SQL Editor'];

    // Crie uma Classe JWT, especifique o Algorítmo 'RS256 e gere um Par de Chaves
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $jwt
        ->algo('RS256')
        ->allowedAlgos(['RS256']);

    list($private_key, $public_key) = $jwt->generateKey();

    // Encode (Sign) and Decode (Verify)
    $token = $jwt->encode($payload, $private_key);
    $data  = $jwt->decode($token, $public_key);
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $private_key,
            $public_key,
            $token,
            "\n",
            json_encode($data),
        ]));
});

$app->get('/examples/signed-data', function() use ($app) {
    $data = ['User'=>'Admin', 'Roles'=>['Admin']];

    // EXAMPLE_CODE_START
    // TITLE: Segurança - Sign and Verify Data
    // CLASS: Security\Crypto\SignedData, Security\Crypto
    // Utilizar [SignedData] tem um conceito parecido ao de utilizar JWT.
    // Um cliente pode ler os dados mas não modificá-los.

    // Gere uma Chave para Assinar.
    // A chave é uma longa string hexadecimal de bytes aleatórios seguros.
    // A chave seria tipicamente salva com seu app ou nas configurações.
    $csd = new \FastSitePHP\Security\Crypto\SignedData();
    $key = $csd->generateKey();

    // Assine e Verifique utilizando a Classe Auxiliar Cypto com Definições de
    // Configuração. Ao utilizar os parâmetros padrão com a classe auxiliar, os
    // dados expiram em uma hora. Dados para diferentes tipos de dados podem
    // ser assinados e verificados em seu format original (string, int, object,
    // etc).
    $app->config['SIGNING_KEY'] = $key;
    $signed_text   = \FastSitePHP\Security\Crypto::sign($data);
    $verified_data = \FastSitePHP\Security\Crypto::verify($signed_text);

    // Assina e Verifica utilizando a Classe SignedData Class. A Classe
    // SignedData permite opções adicionais e não utiliza definições de
    // configuração. O parâmetro [$expire_time] é opcional.
    $expire_time   = '+20 minutes';
    $signed_text   = $csd->sign($data, $key, $expire_time);
    $verified_data = $csd->verify($signed_text, $key);
    // EXAMPLE_CODE_END

    // Formate e retorne como uma resposta em texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Key: {$key}",
            "Signed: {$signed_text}",
            'Verified: ' . json_encode($verified_data),
        ]));
});

$app->get('/examples/password', function() use ($app) {
    $argon_hash = null;
    $argon_verified = null;

    // EXAMPLE_CODE_START
    // TITLE: Segurança - Hash e Verifique Senhas
    // CLASS: Security\Password
    // Salvando Senhas de Usuário utilizando uma função hash unidirecional é
    // importante para segurar aplicações. A classe Password do FastSitePHP
    // provê suporte para bcypt (padrão) e Argon2.

    // Exemplo de uma Senha de Usuário. Este valor não deveria ser gravado em
    // um banco de dados
    $password = 'Password123';

    // Crie um Objeto Password
    $pw = new \FastSitePHP\Security\Password();

    // Hash de Senha. Isto criará uma hash textual que parece que isso:
    //   '$2y$10$cDpu8TnONBhpBFPEKTTccu/mYhSppqNLDNCfOYLfBWI3K/FzFgC2y'
    // O valor mudará toda vez e é seguro gravá-lo em um banco de dados.
    $hash = $pw->hash($password);

    // Verifique a Senha - retorna [true] ou [false]
    $verified = $pw->verify($password, $hash);

    // Cria uma senha aleatoriamente gerada que tem 12 caracteres de
    // comprimento e contém o seguinte:
    //   4 Letras Maiúsculas (A - Z)
    //   4 Letras Minúsculas (a - z)
    //   2 Dígitos (0 - 9)
    //   2 Caracteres Especiais (~, !, @, #, $, %, ^, &, *, ?, -, _)
    $strong_password = $pw->generate();

    // Especifique um custo BCrypt de 12 ao invés do valor padrão 10
    $pw->cost(12);
    $hash2 = $pw->hash($password);
    $verified2 = $pw->verify($password, $hash2);

    // Ao utilizar PHP 7.2 ou mais recente, Argon2 pode ser utilizada
    if (PHP_VERSION_ID >= 70200) {
        $pw->algo('Argon2');
        $argon_hash = $pw->hash($password);
        $argon_verified = $pw->verify($password, $argon_hash);
    }
    // EXAMPLE_CODE_END

    // Formate e retorne como uma resposta em texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $hash,
            json_encode($verified),
            $strong_password,
            $hash2,
            json_encode($verified2),
            $argon_hash,
            json_encode($argon_verified),
        ]));
});

$app->get('/examples/create-rsa-key-pair', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Segurança - Gere um novo Par de Chaves RSA
    // CLASS: Security\Crypto\PublicKey
    // Gere um novo Par de Chaves RSA
    $key_pair = \FastSitePHP\Security\Crypto\PublicKey::generateRsaKeyPair();
    list($private_key, $public_key) = $key_pair;

    // Gere uma nova chave RSA 3072-Bit
    $bits = 3072;
    $key_pair = \FastSitePHP\Security\Crypto\PublicKey::generateRsaKeyPair($bits);
    list($private_key2, $public_key2) = $key_pair;
    // EXAMPLE_CODE_END

    // Formate e retorne como uma resposta em texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $private_key,
            $public_key,
            $private_key2,
            $public_key2,
        ]));
});

$app->get('/examples/random-bytes', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Gere uma string de bytes aleatórios
    // CLASS: Security\Crypto\Random
    // Gere bytes pseudo-aleatórios criptograficamente seguros, adequados para
    // uso criptográfico e aplicativos seguros.
    $bytes = \FastSitePHP\Security\Crypto\Random::bytes(32);

    // Converte os bytes para outro formato:
    $hex_bytes = bin2hex($bytes);
    $base64_bytes = base64_encode($bytes);

    // Ao utilizar PHP 7 ou mais recente, você pode simplesmente chamar
    // [random_bytes()]
    $bytes = random_bytes(32);
    // EXAMPLE_CODE_END

    return $hex_bytes . '<br>' . $base64_bytes;
});

$app->get('/examples/csrf-session', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Segurança - CSRF utilizando Session
    // CLASS: Security\Web\CsrfSession
    // Uma chamada para um função estática cria um token em Requisições GET e
    // valida isso com Requisições POST, PUT, DELETE etc. Se não há erro com o
    // token, então um exceção é lançada, o que causará uma resposta 500 com a
    // página de erro.
    \FastSitePHP\Security\Web\CsrfSession::setup($app);

    // O token recebe um valor locals no Objeto da Aplicação
    $token = $app->locals['csrf_token'];

    // Isto permite que seja utilizado com código de modelo. Tokens são
    // validados à partir por [setup()] mas não automaticamente adicionado a
    // formulários, então eles devem ser adicionados através de modelos ou por
    // código.
    //
    // <meta name="X-CSRF-Token" content="{{ $csrf_token }}">
    // <input name="X-CSRF-Token" value="{{ $csrf_token }}">

    // Um bom lugar para chamar esta função é nos filtros de rota das páginas
    // que utilizam autenticação. Exemplo:

    // Crie uma função filtro para atribuir para múltiplas rotas
    $csrf_session = function() use ($app) {
        \FastSitePHP\Security\Web\CsrfSession::setup($app);
    };

    // Utilize a função quando definir uma rotaUse the function when defining a route
    $app->get('/form', function() use ($app) {
        return $app->render('form.php');
    })
    ->filter($csrf_session);
    // EXAMPLE_CODE_END

    // Formate e retorne como uma resposta em texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $token,
        ]));
});

$app->get('/examples/csrf-stateless', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Segurança - Stateless CSRF
    // CLASS: Security\Web\CsrfStateless
    // Tokens Stateless CSRF não são armazenados em Sessão, mas ao invés disso
    // utilizam um código de autenticação de mensagem criptografada com hash
    // (HMAC) para criar e verificar o token.

    // Uma chave segura secreta é requerida.
    // A chave seria tipicamente salva com seu app ou nas configurações.
    $key = \FastSitePHP\Security\Web\CsrfStateless::generateKey();

    // Para utilizar a Chave, essa deve ser salva em um valor de configuração ou
    // em uma variável de ambiente antes de chamar [setup()].
    $app->config['CSRF_KEY'] = $key;
    // putenv("CSRF_KEY=${key}");

    // Um identificador único para o usuário é também necessário. Isto não tem de
    // ser um segredo e pode ser simplesmente um campo numérico em um banco de
    // dados.
    $user_id = 1;

    // Configura e valida token stateless CSRF
    \FastSitePHP\Security\Web\CsrfStateless::setup($app, $user_id);

    // Opcionalmente adicione um tempo de expiração, este token CSRF expirará depois de 5 minutos
    $expire_time = '+5 minutes';
    \FastSitePHP\Security\Web\CsrfStateless::setup($app, $user_id, $expire_time);

    // A mesma lógica é utilizada ao usar a classe [CsrfSession], então o token
    // é atribuído um valor locals no Objeto da Aplicação permitindo que seja
    // utilizado com código de modelo.
    $token = $app->locals['csrf_token'];
    //
    // <meta name="X-CSRF-Token" content="{{ $csrf_token }}">
    // <input name="X-CSRF-Token" value="{{ $csrf_token }}">

    // Também da mesma forma que [CsrfSession] um bom lugar para chamar
    // [setup()] é nas funções filtro de rota.
    $csrf = function() use ($app, $user_id) {
        \FastSitePHP\Security\Web\CsrfStateless::setup($app, $user_id);
    };

    $app->get('/form', function() use ($app) {
        return $app->render('form.php');
    })
    ->filter($csrf);
    // EXAMPLE_CODE_END

    // Formate e retorne como uma resposta em texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $key,
            $token,
        ]));
});

$app->get('/examples/net-ip', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Endereços IP e Validações
    // CLASS: Net\IP
    // Com o FastSitePHP você pode facilmente comparar um endereço de IP a uma
    // reconhecida faixa de IPs utilizando Notação CIDR. Notação CIDR
    // (Classless Inter-Domain Routing) é uma representação compacta de endereços
    // de IP e os prefixos de suas rotas associadas. Isto é utilizado regularmente
    // ao trabalhar com redes digitais e frequentemente necessário para websites
    // quando lidando com endereços de IP por segurança.

    // Verifique se o endereço de IP '10.10.120.12' está na faixa '10.0.0.0/8'
    // Retorna [true]
    $matches = \FastSitePHP\Net\IP::cidr('10.0.0.0/8', '10.10.120.12');

    // Check if IP Address '10.10.120.12' is in the '172.16.0.0/12' range
    // Retorna [false]
    $matches2 = \FastSitePHP\Net\IP::cidr('172.16.0.0/12', '10.10.120.12');

    // IPv6 também é suportado
    $matches3 = \FastSitePHP\Net\IP::cidr('fe80::/10', 'fe80::b091:1117:497a:9dc1');

    // Obtém um array de Endereços de Rede Privados em Notação CIDR
    //   [
    //     '127.0.0.0/8',      // IPv4 localhost
    //     '10.0.0.0/8',       // IPv4 Private Network, RFC1918 24-bit block
    //     '172.16.0.0/12',    // IPv4 Private Network, RFC1918 20-bit block
    //     '192.168.0.0/16',   // IPv4 Private Network, RFC1918 16-bit block
    //     '169.254.0.0/16',   // IPv4 local-link
    //     '::1/128',          // IPv6 localhost
    //     'fc00::/7',         // IPv6 Unique local address (Private Network)
    //     'fe80::/10',        // IPv6 local-link
    //   ]
    $private_addr = \FastSitePHP\Net\IP::privateNetworkAddresses();

    // O array de [privateNetworkAddresses()] pode ser utilizado com a função
    // [cidr()] para verificar se um endereço de IP é de uma rede privada ou de
    // da internet pública. A função [cidr()] aceita o Parâmetro CIDR como um
    // array ou uma string.
    $matches4 = \FastSitePHP\Net\IP::cidr($private_addr, '10.10.120.12');

    // Obtém informações sobre a sting CIDR ao chamar [cidr()] com somente 1
    // parâmetro.
    // Este exemplo retorna o seguinte:
    //   [
    //     'CIDR_Notation' => '10.63.5.183/24',
    //     'Address_Type' => 'IPv4',
    //     'IP_Address' => '10.63.5.183',
    //     'Subnet_Mask' => '255.255.255.0',
    //     'Subnet_Mask_Bits' => 24,
    //     'Cisco_Wildcard' => '0.0.0.255',
    //     'Network_Address' => '10.63.5.0',
    //     'Broadcast' => '10.63.5.255',
    //     'Network_Range_First_IP' => '10.63.5.0',
    //     'Network_Range_Last_IP' => '10.63.5.255',
    //     'Usable_Range_First_IP' => '10.63.5.1',
    //     'Usable_Range_Last_IP' => '10.63.5.254',
    //     'Addresses_in_Network' => 256,
    //     'Usable_Addresses_in_Network' => 254,
    //  ]
    $info = \FastSitePHP\Net\IP::cidr('10.63.5.183/24');

    // Exemplo de informações CIDR quando utiliza IPv6:
    //   [
    //     'CIDR_Notation' => 'fe80::b091:1117:497a:9dc1/48',
    //     'Address_Type' => 'IPv6',
    //     'IP_Address' => 'fe80::b091:1117:497a:9dc1',
    //     'Subnet_Mask' => 'ffff:ffff:ffff::',
    //     'Subnet_Mask_Bits' => 48,
    //     'Network_Address' => 'fe80::',
    //     'Network_Range_First_IP' => 'fe80::',
    //     'Network_Range_Last_IP' => 'fe80::ffff:ffff:ffff:ffff:ffff',
    //     'Addresses_in_Network' => '1208925819614629174706176',
    //   ]
    $info_ip6 = \FastSitePHP\Net\IP::cidr('fe80::b091:1117:497a:9dc1/48');
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($matches),
            json_encode($matches2),
            json_encode($matches3),
            json_encode($private_addr, JSON_PRETTY_PRINT),
            json_encode($matches4),
            json_encode($info, JSON_PRETTY_PRINT),
            json_encode($info_ip6, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/file-system-security', function() use ($app) {
    $dir = __DIR__;
    $image_file = __DIR__ . '/files/favicon.png';

    // EXAMPLE_CODE_START
    // TITLE: Segurança de Sistema de Arquivos
    // CLASS: FileSystem\Security
    // A Classe FileSystem Security contém funções para validar arquivos.

    // Previna ataques Path Traversal verificando se um nome de arquivo existe
    // em um diretório específico. Ataque Path Transversal podem ocorrer se um
    // usuário tem concedida a permissão de especificar um arquivo em um
    // sistema de arquivos através e input e usa um padrão como '/../' para
    // obter arquivos de outro diretório.

    // Exemplos:

    // Assuma que ambos os arquivos existem e retornariam [true] da função
    // integrada [is_file()]. [false] seria retornado para o segundo arquivo
    // ao utilizar [Security::dirContainsFile()].
    $file1 = 'user_image.jpg';
    $file2 = '../../index.php';
    $file_exists_1 = \FastSitePHP\FileSystem\Security::dirContainsFile($dir, $file1);
    $file_exists_2 = \FastSitePHP\FileSystem\Security::dirContainsFile($dir, $file2);

    // A função [dirContainsFile()] só permite que arquivos diretamente sob a
    // pasta raiz então outra função existe para procurar subdiretórios a
    // [dirContainsPath()].
    $path1 = 'icons/clipboard.svg'; // Retorna  [true]
    $path2 = '../../app/index.php'; // Retorna  [false]
    $path_exists_1 = \FastSitePHP\FileSystem\Security::dirContainsPath($dir, $path1);
    $path_exists_2 = \FastSitePHP\FileSystem\Security::dirContainsPath($dir, $path2);

    // [dirContainsDir()] pode ser utilizada para verificar diretórios/pastas.
    $dir1 = 'icons';
    $dir2 = '../../app';
    $dir_exists_1 = \FastSitePHP\FileSystem\Security::dirContainsDir($dir, $file1);
    $dir_exists_2 = \FastSitePHP\FileSystem\Security::dirContainsDir($dir, $file2);

    // Valide Arquivos de Imagem
    // A função [fileIsValidImage()] pode ser utilizada para verificar se
    // arquivos de imagem criados de outro input de usuário, são válidos. Por
    // exemplo um usuário malicioso pode tentar renomear um script PHP ou
    // arquivo executável como se fosse uma imagem e enviá-lo para um site.
    // Retorna [true] se um arquivo de imagem [jpg, gif, png, webp, svg] for
    // válido e a extensão do arquivo corresponder ao tipo de imagem.
    $is_image = \FastSitePHP\FileSystem\Security::fileIsValidImage($image_file);
    // EXAMPLE_CODE_END

    // NOTA - [$result1, $path_exists_1, $dir_exists_1] são todas iguais a
    // [false] por que os arquivos/diretórios não existirão. O código aqui é só
    // para exemplificação, modifique-o se você quiser fazer testes.
    return [$result1, $result2, $path_exists_1, $path_exists_2, $dir_exists_1, $dir_exists_2, $is_image];
});

$app->get('/examples/rate-limiting', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Segurança - Limitação de Frequência
    // CLASS: Security\Web\RateLimit
    // Classe de Limitação de Frequência
    $rate_limit = new \FastSitePHP\Security\Web\RateLimit();

    // Utilizando a classe RateLimit requer uma instância de [\FastSitePHP\Data
    // \KeyValue\StorageInterface].
    // Neste exemplo SQLite é utilizado. Quando múltiplos servidores são
    // usados atrás de um balanceador de carga, um bd de cache em memória como
    // o Redis pode ser utilizado.
    $file_path = sys_get_temp_dir() . '/ratelimit-cache.sqlite';
    $storage = new \FastSitePHP\Data\KeyValue\SqliteStorage($file_path);

    // Há duas opções obrigatórias [storage] e [id]. [id] representa o usuário -
    // Endereço de IP, ID de Usuário etc.
    //
    // [max_allowed] e [duration] serão comumente utilizadas e representam a
    // taxa na qual o evento é permitido. Se não especificado, então, um
    // padrão de 1 é usado o qual permite 1 requisição por segundo.
    $options = [
        'max_allowed' => 1, // Requisições, Eventos etc
        'duration' => 1, // Em segundos
        'storage' => $storage,
        'id' => $_SERVER['REMOTE_ADDR'],
    ];

    // Verifique a Requisição
    list($allowed, $headers) = $rate_limit->allow($options);
    // $allowed = bool
    // $headers = Array de cabeçalhos pode ser utilizado para lógica ou
    //            ou enviado com a resposta

    // Uma coisa para estar ciente ao filtrar por IP é que vários usuários podem
    // estar como o mesmo IP se eles estiverem acessando seu site de um mesmo
    // escritório ou localização.

    // Exemplos de opções:

    // Limitar a 10 requisições a cada 20 segundos
    $options = [ 'max_allowed' => 10, 'duration' => 20, ];

    // Limitar a 2 requisições por minuto
    $options = [ 'max_allowed' => 2, 'duration' => 60, ];

    // Limitar a 2 requisições por dia
    $options = [ 'max_allowed' => 10, 'duration' => (60 * 60 * 24), ];

    // Se estiver utilizando a classe [RateLimit] para múltiplas utilizações,
    // então, você precisa especificar uma chave opcional.
    $options = [ 'key' => 'messages-sent' ];
    $options = [ 'key' => 'accounts-created' ];

    // A classe [RateLimit] permite diferentes algorítimos de limitação de
    // taxa; o padrão é 'fixed-window-counter' o qual coloca uma quantidade
    // fixa no número de requisições para a duração dada, mas permite rajadas.
    // O 'token-bucket' permite limitar a taxa por uma taxa
    // cronometrada, entretanto, isso pode permitir um número maior de requisições
    // do que o especificado [max_allowed].
    //
    // Para utilização básica com um número pequeno de [max_allowed] tal como
    // "1 requisição por segundo",  ele comportarão-se da mesma forma, no
    // entanto, se especificar um número maior como "10 requisições por 20
    // segundos", então, haverá um diferença, assim se você estiver utilizando
    // limitação de taxa para requisições web com um número grande você
    // pode querer comparar as diferenças utilizando código exemplo e ver links
    // relacionados nos documentos da API.
    //
    $options = [ 'algo' => 'fixed-window-counter' ];
    $options = [ 'algo' => 'token-bucket' ];

    // A função [filterRequest()] pode ser utilizada para filtrar a requisição.
    // Ao ser utilizada, se a limitação de taxa do usuário é atingida,
    // então, uma resposta 409 [Too Many Requests] é enviada e [exit()] é
    // chamada para parar a execução do script.
    $filter_request = function() use ($app, $storage) {
        // Obtém o IP de Usuário (exemplo se estiver utilizando um balanceador
        // de carga)
        $req = new \FastSitePHP\Web\Request();
        $user_ip = $req->clientIp('from proxy');

        // Check rate
        $rate_limit = new \FastSitePHP\Security\Web\RateLimit();
        $rate_limit->filterRequest($app, [
            'storage' => $storage,
            'id' => $user_ip,
        ]);
    };
    $app->get('/api', function() {})->filter($filter_request);

    // Quando utilizar [filterRequest()] os seguintes Cabeçalhos de Response
    // podem ser enviados para o cliente dependendo de quais opções são
    // utilizadas
    //   Retry-After            Cabeçalho Padrão
    //   X-RateLimit-Limit      Descrição legível por humanos do limite da taxa
    //   X-RateLimit-Remaining  Requisições permitidas para o período de tempo dado
    //   X-RateLimit-Reset      Registro de data e hora Unix para o limite redefinir
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            'Allowed: ' . json_encode($allowed),
            'Headers: ' . json_encode($headers, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/image', function() use ($app) {
    // Descomente a linha [return] e modifique-a para utilizar um arquivo que
    // exista em seu computador
    return '';

    // Caminhos da Imagem
    $file_path = 'C:\Users\Public\Pictures\Desert.jpg';
    $save_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Modified Test Image.jpg';

    // EXAMPLE_CODE_START
    // TITLE: Abra e Edite Arquivos de Imagens
    // CLASS: Media\Image
    // Utilize a Classe Media Image para abrir uma imagem. Se a imagem for
    // inválida ou a extensão do arquivo não corresponder ao tipo de arquivo,
    // então, uma exceção será lançada. Extensões de arquivos suportadas =
    // [jpg, jpeg, gif, png, webp]
    $img = new \FastSitePHP\Media\Image();
    $img->open($file_path);

    // Gera uma Miniatura ou Redimensiona a Imagem para um máximo especificado
    // de largura e altura.
    //
    // Quando ambas largura e altura são especificadas, a imagem será
    // redimensionada para o menor dos dois valores para que ela se ajuste. Se
    // somente a largura ou somente a altura for especificada, então, a imagem
    // será dimensionada proporcionalmente para o valor.
    $max_width = 200; // Pixels
    $max_height = 200;
    $img->resize($max_width, $max_height);

    // Imagens pode também serem cortadas para uma dimensão especificada.
    // Isto pode ser utilizado com JavaScript ou bibliotecas de corte para Apps
    // para permitir que usuários gerem miniaturas de uma imagem completa enviada.
    // Por exemplo, permita o usuário cortar uma imagem enviada para uma
    // miniatura de perfil.
    $left = 50;
    $top = 40;
    $width = 120;
    $height = 80;
    $target_width = $width * 2; // Opcional
    $target_height = $height * 2; // Opcional
    $img->crop($left, $top, $width, $height, $target_width, $target_height);

    // Imagens podem ser rotacionadas o que é útil para sites que permitem
    // usuários enviar imagens, por que imagens podem, geralmente, ser enviadas
    // com a rotação incorreta dependendo do dispositivo móvel ou um usuário
    // pode simplesmente querer modificar a rotação.
    $degrees = 180;
    $img->rotateLeft();
    $img->rotateRight();
    $img->rotate($degrees);

    // Qualidade de Salvamento (0 to 100) pode ser especificada quando for
    // salvar imagen JPG ou WEBP. E Nível de Compressão (0 to 9) pode ser
    // especificado ao salvar arquivos PNG.
    $img->saveQuality(90);   // Qualidade Padrão
    $img->pngCompression(6); // Nível de Compressão Padrão

    // Sobrescreva uma imagem existente simplesmente chamando [save()] sem um
    // caminho ou salve para um novo arquivo especificando um caminho completo
    // de arquivo.
    $img->save($save_path);

    // Opcionalmente feche a imagem para liberar memória quando terminar de
    // trabalhar com ela. Isto acontece automaticamente quando a variável não é
    // mais utilizada.
    $img->close();
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            'Original Image: ' . realpath($file_path),
            'Image saved to: ' . $save_path,
        ]));
});

$app->get('/examples/i18n', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Manipule Traduções de Idiomas para um Site ou Aplicação
    // CLASS: Lang\I18N
    // O FastSitePHP provê uma API de Internacionalização (i18n) de fácil
    // utilização para sites e apps que precisam suportar múltiplos idiomas.
    // O código é estruturado mas mínimo em seu tamanho, assim se você tem
    // necessidades diferentes de tradução, você pode simplesmente copiar e
    // modificar a classe.

    // Traduções são salvas como arquivos JSON no mesmo diretório utilizando o
    // formato de nome “{nome}.{idioma}.json”. Um arquivo principal opcional
    // nomeado “_.{idioma}.json” se encontrado será lido primeiro. O arquivo
    // principal "_" é útil para armazenar traduções chave tal como menus,
    // cabeçalho de página, rodapés de páginas etc.

    // Um idioma de fallback opcional pode ser especificado assim traduções
    // não encontradas são obtidas de outro idioma. Isto permite que sites
    // parcialmente traduzidos utilizem esta API.

    // Já que a API é simples e fácil de utilizar, existem somente duas funções
    // para chamar:
    // [langFile()] e [textFile()].

    // Arquivos de Exemplo:
    //     _.en.json
    //     _.es.json
    //     header.en.json
    //     header.es.json
    //     about.en.json

    // Utilizando este código, os aquivos acima serão carregados na ordem listada.
    $app->config['I18N_DIR'] = __DIR__ . '/i18n';
    $app->config['I18N_FALLBACK_LANG'] = 'en';

    \FastSitePHP\Lang\I18N::langFile('header', 'es');
    \FastSitePHP\Lang\I18N::langFile('about', 'es');

    // Uso típico é permitido para um app carregar um arquivo de idioma
    // baseando-se na URL Requisitada:
    $app->get('/:lang/about', function($lang) {
        \FastSitePHP\Lang\I18N::langFile('about', $lang);
    });

    // [setup()] pode ser chamada por cada requisição para ter certeza que o
    // arquivo de idioma seja sempre carregado para a renderização de um modelo
    // quando [$app->render()] é chamada.
    //
    // Isto é útil se seu site utiliza PHP ou outros modelos para renderizar e
    // espera que o arquivo [i18n] padrão sempre esteja disponível. Por exemplo
    // um erro inesperado ou chamada de [$app->pageNotFound()] pode acionar um
    // modelo para que seja renderizado.
    \FastSitePHP\Lang\I18N::setup($app);

    // Traduções carregadas são definidas na propriedade da app
    // ($app->locals['i18n']), de forma que elas podem ser utilizadas com
    // renderização de modelo e chamada de página.

    // Ao utilizar um formato de URL [https://www.example.com/{lang}/{pages}]
    // e um idioma fallback, o usuário será redirecionado para a mesma página
    // com o idioma fallback se o idioma especificado não existir.

    // Quando [langFile()] é chamada e o idioma é verificado como válido, isto é
    // definido na propriedade do app ($app->lang).

    // A outra função I18N [textFile()] simplesmente recebe um caminho completo
    // de arquivo contento o texto '{lang}' juntamente com o idioma selecionado
    // e então carrega o arquivo ou, se este não existir, o arquivo que
    // corresponde ao idioma de fallback.
    $file_path = $app->config['I18N_DIR'] . '/test-{lang}.txt';
    $content = \FastSitePHP\Lang\I18N::textFile($file_path, $app->lang);
    // EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($app->locals['i18n'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $content,
        ]));
});

$app->get('/examples/l10n', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Formatando Datas, Horas e Números
    // CLASS: Lang\L10N
    // O FastSitePHP provê uma API de Localização (l10n) de fácil utilização
    // para permitir formatação de datas e números com a linguagem local do
    // usuário e configurações regionais.

    // Cria um novo Objeto Lang L10N
    $l10n = new \FastSitePHP\Lang\L10N();

    // Definições são passadas de forma opcional quando a classe é criada
    /*
    $locale = 'pt-BR';
    $timezone = 'America/Sao_Paulo';
    $l10n = new \FastSitePHP\Lang\L10N($locale, $timezone);
    */

    // Utiliza a função [timezone()] para obter ou definir o fuso horário que
    // será utilizado ao formatar datas e horários.
    //
    // Se você tem um site ou aplicação que tenha usuários em múltiplos fusos
    // horários our países, um design de aplicação que funciona bem é salvar
    // todas as datas e horários em UTC e daí formatá-los baseando-se no fuso
    // horário escolhido pelo usuário.
    //
    // Este exemplo imprime:
    /*
        UTC                 = 2030-01-01 00:00
        Asia/Tokyo          = 2030-01-01 09:00
        America/Los_Angeles = 2029-12-31 16:00
    */
    $date_time = '2030-01-01 00:00:00';
    $timezones = ['UTC', 'Asia/Tokyo', 'America/Los_Angeles'];
    foreach ($timezones as $timezone) {
        // Mude o Fuso Horário
        $l10n->timezone($timezone);
        // Imprime a data e horário formatados
        echo $l10n->timezone();
        echo ' = ';
        echo $l10n->formatDateTime($date_time);
        echo '<br>';
    }
    echo '<br>';

    // Mude o Fuso Horário de volta para UTC para os próximos exemplos
    $l10n->timezone('UTC');

    // O parâmetro [$date_time] para as funções [formatDateTime(), formatDate()
    // e formatTime()] é um carimbo de data/hora Unix (int) ou uma string no
    // formato de 'YYYY-MM-DD HH:MM:SS' ou 'YYYY-MM-DD'
    $date_time = 1896181200;
    $date_time = '2030-02-01 13:00:00';

    // Imprima Data e Hora com localizações diferentes utilizando as funções
    // [locale()] e [formatDateTime()]. Este exemplo imprime:
    /*
        ko    = 2030. 2. 1. 오후 1:00
        bn    = ১/২/২০৩০ ১:০০ PM
        en-US = 2/1/2030, 1:00 PM
        de-CH = 01.02.2030, 13:00
        ar    = ‏١‏/٢‏/٢٠٣٠ ١:٠٠ م
    */
    $locales = ['ko-KR', 'bn-BD', 'en-US', 'de-CH', 'ar'];
    foreach ($locales as $locale) {
        // Mude a Localização
        $l10n->locale($locale);
        // Imprima a data e hora formatados
        echo $l10n->locale();
        echo ' = ';
        echo $l10n->formatDateTime($date_time);
        echo '<br>';
    }
    echo '<br>';

    // Além de [formatDateTime()] as funções [formatDate()] e [formatTime()]
    // podem ser utilizadas para mostrar somente uma data ou hora. Imprime:
    /*
        01/02/2030
        13:00:00
    */
    $l10n->locale('fr-FR');
    echo $l10n->formatDate($date_time);
    echo '<br>';
    echo $l10n->formatTime($date_time);
    echo '<br>';
    echo '<br>';

    // Imprima um Número formatado com diferentes localizações utilizando as
    // funções [locale()] e [formatNumber()]. Posições decimais são opcionais
    // e seu padrão é 0. Este exemplo imprime:
    /*
        en-US =  1,234,567,890.12345
        en-IN = 1,23,45,67,890.12345
        fr    =  1 234 567 890,12345
        fa    =  ۱٬۲۳۴٬۵۶۷٬۸۹۰٫۱۲۳۴۵
    */
    $numero = 1234567890.12345;
    $decimals = 5;
    $locales = ['en-US', 'en-IN', 'fr', 'fa'];
    foreach ($locales as $locale) {
        // [locale()] é uma função getter e setter encadeável assim ela pode ser
        // definida e lida de uma mesma linha.
        echo $l10n->locale($locale)->locale();
        echo ' = ';
        echo $l10n->formatNumber($numero, $decimals);
        echo '<br>';
    }

    // Obtenha Localizações, Idiomas e Fusos Horários suportados
    $locales    = $l10n->supportedLocales();
    $languages = $l10n->supportedLanguages();
    $timezones  = $l10n->supportedTimezones();
    // EXAMPLE_CODE_END

    echo '<br><b>Localizações:</b><br>';
    echo json_encode($locales);
    echo '<br><br><b>Idiomas:</b><br>';
    echo json_encode($languages);
    echo '<br><br><b>Fusos Horários:</b><br>';
    echo json_encode($timezones);
});

$app->get('/examples/starter-site', function() use ($app) {
    // Essa rota está inclusa para que o código seja exibido nos documentos da
    // API, mas não é executado aqui. Faça o download e execute o site inicial
    // para experimentar as classes reais, porque elas não estão incluídas no
    // framework.
    return '<a href="https://github.com/fastsitephp/starter-site">Teste-a no Site Inicial</a>';

    // EXAMPLE_CODE_START
    // TITLE: Starter Site Middleware
    // CLASS: App\Middleware\Cors, App\Middleware\Auth, App\Middleware\Env

    // The FastSitePHP Starter Site inclui várias páginas de exemplos e fornece uma
    // estrutura básica de diretório / arquivo. O site foi projetado para fornecer
    // estrutura para conteúdo básico (JavaScript, CSS etc.), mantendo um tamanho
    // pequeno, para facilitar a remoção de arquivos desnecessários e a
    // personalização para o seu site.
    //
    //     https://github.com/fastsitephp/starter-site
    //
    // As classes de Middleware são fornecidas e podem ser modificadas para
    // o seu site.
    //
    // Para utilizá-las especifique 'Class.method' nas funções filtro da rota
    // ou quando montando arquivos adicionais.

    // Exige que um usuário esteja logado para utilizar uma página
    $app->get('/secure-page', 'SecureController')->filter('Auth.hasAccess');

    // Exige um usuário autenticado e utilize CORS
    $app
        ->get('/api/:record_type', 'ApiController.getData')
        ->filter('Cors.acceptAuth')
        ->filter('Auth.hasAccess');

    // Somente rode uma rota de localhost
    $app->get('/server-info', function() {
        phpinfo();
    })
    ->filter('Env.isLocalhost');

    // Somente carregue um arquivo se estiver rodando à partir de localhost
    $app->mount('/sysinfo/', 'routes-sysinfo.php', 'Env.isLocalhost');
    // EXAMPLE_CODE_END
});

// NOTA - Utilize isto como um modelo para novas rotas, espaços precisam ser
// adicionados entre '// EXAMPLE_', etc
/*
$app->get('/examples/template', function() use ($app) {
    //EXAMPLE_CODE_START
    //TITLE: Novo Modelo de Rota, Repare os Espaços para que funcione
    //CLASS: Dir\Class1, Dir\Class2
    //EXAMPLE_CODE_END

    // Retorne Resposta em Texto
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
        ]));
});
*/
