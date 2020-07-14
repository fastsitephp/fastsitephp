<?php
// This is a working route file with many examples that runs from [home-page-en.php]
// When running from a local environment the URL of a route will look like this:
//     http://localhost:3000/FastSitePHP/website/app_data/sample-code/home-page-en.php/examples
// This file is also used as the source of code for documentation pages. For example
// the Quick Reference Page [URL = '/:lang/quick-reference']. Code is organized in
// blocks of [EXAMPLE_CODE_START] and [EXAMPLE_CODE_END] comments which are used
// by the class [\App\Models\ExampleCode] when reading this file.

// If running this file directly then redirect to the main file
// using the index route '/examples' for this file.
if (!isset($app)) {
    $url = 'home-page-en.php/examples';
    header('Location: ' . $url, true, 302);
    exit();
}

// The root page loads the default development autoloader. If a vendor autoloader
// exists then include it as well. A few examples such as [examples/logging] and
// [examples/markdown] will not work unless third-party libraries are installed.
if (is_file('../../../vendor/autoload.php')) {
    include '../../../vendor/autoload.php';
}

// Default Examples Route
// Build and display a list of all routes
$app->get('/examples', function() use ($app) {
    // Get URL's for all Routes
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

    // Build and Return HTML
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

// This commented out code block (and similar code blocks) are for the Quick Reference Page:
/*
// EXAMPLE_CODE_START
// TITLE: PHP Syntax - Overview
<?php
// PHP is similar to the C style syntax so [if] statements, [for] and [while]
// loops, [functions], [comments], and more are also similar to other
// widely used languages such as JavaScript, C#, and Java. If you have
// some JavaScript experience it is very easy to get started with PHP.

// PHP Scripts start with [<?php] and individual lines must end with a
// semicolon [;]. The [echo] statement outputs/prints content on the screen. This
// example if saved as a file simply ouputs 'Hello World!'.
echo 'Hello World!';
// EXAMPLE_CODE_END
*/

$app->get('/examples/php-variables', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: PHP Syntax - Variables
    // In PHP all variables start with a dollar sign character [$] followed by
    // the variable name. Variables are created when they are first used and
    // not declared ahead of time.
    $value = 'Test';

    // Variables are dynamically typed in PHP; the data type is determined
    // by the value of the variable and the type can be changed.
    $value = (10 * 20);
    $string = 'String';
    $number = 123;
    $decimal = 123.456;
    $bool = true;
    $null = null;

    // Arrays can be defined using [] characters like JavaScript and other languages
    // when using any recent version of PHP. If using an old version of PHP
    // (5.3 or below) Arrays need to be defined using the [array()] function.
    $cities = ['Tokyo', 'São Paulo', 'Jakarta', 'Seoul', 'Manila', 'New York City'];

    // An extra comma can be included after the last item
    $numbers = array(123, 456, 789,);

    // PHP Array's is actually an ordered map so they are often used like
    // Dictionaries, Hashes, or Associative Arrays from other languages.
    $months_days = [
        'January' => 31,
        'February' => 28,
        'March' => 31,
        'April' => 30,
    ];

    // Objects can be dynamically created in PHP using the built-in [stdClass].
    $object = new \stdClass;
    $object->name = 'FastSitePHP';
    $object->type = 'PHP Framework';

    // Object can also be created dynamically when casting from an array.
    $object2 = (object)[
        'name' => 'FastSitePHP',
        'type' => 'PHP Framework',
    ];

    // To check if variable is defined use the [isset()] function
    $defined1 = isset($object);  // true
    $defined2 = isset($object3); // false

    // Additional types include Resources such as a file, and callback functions.
    // EXAMPLE_CODE_END

    // Return Variables as JSON
    $app->json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
    return [
        'value' => $value,
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
    // TITLE: PHP Syntax - Strings
    // Similar to other languages such as JavaScript, Python, and Ruby,
    // strings in PHP can be either single-quoted or double-quoted.
    $value = 'Single Quoted String';
    $value = "Double Quoted String";

    // To combine or concatenate strings use the dot character [.]:
    $greeting = 'Hello ' . 'World';

    // Spaces are not required between the dot [.] and the other variables:
    $greeting = 'Hello '.'World';

    // You can append to a string using the [.=] operator:
    $greeting = 'Hello';
    $greeting .= ' World';

    // Similar to Python and Ruby, double-quote strings expand variables
    // for string interpolation. This prints 'Hello World':
    $name = 'World';
    $greeting = "Hello ${name}";

    // Multi-line strings use [<<<] followed by a programmer-defined identifier
    // and end the string the same identifier starting on a new code line followed
    // by [;]. In this example the identifier is [EOD] for end-of-data. Multiline
    // strings using this syntax support string interpolation.
    $multiline1 = <<<EOD
Multi-line
String
${name}
EOD;

    // When using ['] characters is similar to using single-quotated strings
    // so there is no string interpolation. The above example prints 'World'
    // instead of '${name}' while this version prints '${name}'.
    $multiline2 = <<<'EOD'
Multi-line
String
${name}
EOD;

    // Common String Functions using this string:
    $value = ' abcdefgh ';

    // String Length and Trim
    $len = strlen($value);        // 10
    $len2 = strlen(trim($value)); // 8

    // String Search, often PHP functions return mixed data types.
    // [strpos()] and [stripos()] are good examples. If the string
    // is found an integer with the position is returned otherwise
    // a boolean value of false is returned.
    $pos = stripos($value, 'DEF'); // Case-insenstive = 4
    $pos2 = strpos($value, 'DEF'); // Case-Senstive = false

    // Split to an Array and Join an Array to a String.
    // Rather than using [split()/join()] PHP uses [explode/implode()].
    $value = '123,456,789';
    $array = explode(',', $value);
    $string = implode('_', $array);

    // Replace
    $text = 'Blue and Red';
    $search = 'Red';
    $replace = 'Green';
    $new_value = str_replace($search, $replace, $text);

    // Internally in PHP strings are implemented as an array of bytes so if you
    // work with binary files or data you use the string data type. This can
    // present a problem though if you need to calculate the length of a Unicode
    // String for a user, find the character position, etc. To support different
    // encodings PHP includes Multibyte String Functions. In general they have the
    // same name and params as other string functions but are prefixed with [mb_].
    $unicode = '测试';
    $ulen = strlen($unicode);     // 6
    $ulen2 = mb_strlen($unicode); // 2
    // EXAMPLE_CODE_END

    // Return Variables as JSON
    $app->json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
    return [
        'greeting' => $greeting,
        'multiline1' => $multiline1,
        'multiline2' => $multiline2,
        'len' => $len,
        'len2' => $len2,
        'pos' => $pos,
        'pos2' => $pos2,
        'array' => $array,
        'string' => $string,
        'new_value' => $new_value,
        'ulen' => $ulen,
        'ulen2' => $ulen2,
    ];
});

$app->get('/examples/php-logic', function() use ($app) {
    // Content is output as plain text
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: PHP Syntax - Logic Statements
    // This example outputs data to the client as it is evaluated:
    //    var_dump() = PHP function to print a variable and the data type.
    //    echo "\n"  = Output a new line for formatting.
    $number = 5;

    // Basic [if] Statement
    //     Prints: '[Number equals 5]'
    if ($number === 5) {
        echo '[Number equals 5]';
    } else {
        echo '[Number does not equal 5]';
    }

    // [if ... else]. The example also shows using the not operator [!].
    //     Prints: '[Number is positive]'
    if (!is_int($number)) {
        echo '[Number is not a integer]';
    } elseif ($number < 0) {
        echo '[Number is negative]';
    } else {
        echo '[Number is positive]';
    }

    // Ternary Expression: (expression ? true : false)
    //     Prints: [Number is even: no]
    $is_even = ($number % 2 === 0 ? 'yes' : 'no');
    echo "[Number is even: ${is_even}]";

    // The [if] statement can be used to evaluate "truthy" values for
    // other data types. The 3 statements below all evaluate to false
    // because the values are empty or zero.

    $empty_array = array();
    if ($empty_array) {
        echo '[Array has data]';
    } else {
        echo '[Array is empty]';
    }

    $empty_string = '';
    if ($empty_array) {
        echo '[String has data]';
    } else {
        echo '[String is empty]';
    }

    $zero = 0;
    if ($zero) {
        echo '[Number is not Zero]';
    } else {
        echo '[Number is Zero]';
    }

    // It's possible to exclude the middle expression when using the ternary
    // operator '?:' (expression ?: default). This is known as the Elvis operator
    // and returns either the result of the first expression if it evaluates
    // as a "truthy" value or the 2nd expression (default value).

    // Prints:
    //     [Elvis Operator: Default]
    $value = ($empty_string ?: 'Default');
    echo "[Elvis Operator: ${value}]";

    // Prints:
    //     [Elvis Operator: 3]
    $value = ((1 + 2) ?: 'Enter Value');
    echo "[Elvis Operator: ${value}]";

    // Equal [==] vs. Identical (Strict Mode) [===]
    // PHP is similar to JavaScript when comparing on data types.

    // These expressions all evalute to [true] because
    // the data type does not have to match exactly.
    echo "\n";
    var_dump(1 == true);
    var_dump(0 == '');
    var_dump(0 == 'a');
    var_dump('1' == '01');

    // These expressions all evalute to [false] because
    // the data type has to match exactly.
    echo "\n";
    var_dump(1 === true);
    var_dump(0 === '');
    var_dump(0 === 'a');
    var_dump('1' === '01');

    // Not Equal [!=] vs. Not Identical [!==]:
    echo "\n";
    var_dump(0 != '');  // false
    var_dump(0 !== ''); // true

    // Logical Operators:
    echo "\n";
    var_dump(true && true);   // true
    var_dump(false && false); // false
    var_dump(false || true);  // true

    // Arrays can be easily compared in PHP
    echo "\n";
    $array1 = [1, 2, 3];
    $array2 = [1, 2, 3];
    $array3 = [1, 2, 3, 4];
    var_dump($array1 === $array2); // true
    var_dump($array1 === $array3); // false

    // Switch Statement
    // Just like the [if] statement the syntax for [switch] is similar
    // to C style languages such as C and JavaScript so the same
    // fallthrough rules apply.
    //
    // This example prints the season name from 4-season calendar
    // in the Northern Hemisphere based on the current month.

    echo "\n";
    $month = date('F');

    switch ($month) {
        case 'March':
        case 'April':
        case 'May':
            echo 'Spring';
            break;
        case 'June':
        case 'July':
        case 'August':
            echo 'Summer';
            break;
        case 'September':
        case 'October':
        case 'November':
            echo 'Fall (Autumn)';
            break;
        case 'December':
        case 'January':
        case 'February':
            echo 'Winter';
            break;
        default:
            echo 'Error';
    }
    // EXAMPLE_CODE_END
});

$app->get('/examples/php-loops', function() use ($app) {
    //  Data Source:
    //  https://en.wikipedia.org/wiki/List_of_largest_cities

    // Content is output as plain text
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: PHP Syntax - Loops
    // Just like the logic demo this example also outputs data as it is evaluated.

    // Define arrays with the largest cities in the world (by urban area)
    $cities = [
        'Tokyo', 'São Paulo', 'Jakarta', 'Seoul', 'Manila',
        'New York City', 'Shanghai', 'Cairo', 'Delhi',
    ];

    $cities_population = [
        'Tokyo'     => '36,923,000',
        'São Paulo' => '36,842,102',
        'Jakarta'   => '30,075,310',
        'Seoul'     => '25,520,000',
        'Manila'    => '24,123,000',
        'New York'  => '23,689,255',
        'Shanghai'  => '23,416,000',
        'Cairo'     => '22,439,541',
        'Delhi'     => '21,753,486',
    ];

    echo 'Largest Cities in the World' . "\n";
    echo str_repeat('-', 40) . "\n";

    // Loop through the list of cities using the [foreach] loop.
    //     foreach (array as item)
    foreach ($cities as $city) {
        echo $city;
        echo "\n";
    }
    echo "\n";

    // Loop through a Dictionary or Associative Array using [foreach]
    //     foreach (array as key => value)
    foreach ($cities_population as $city => $population) {
        echo $city . ' = ' . $population;
        echo "\n";
    }
    echo "\n";

    // [for] loop using C style syntax, this prints 0...9 on seperate lines
    for ($n = 0; $n < 10; $n++) {
        echo $n;
        echo "\n";
    }
    echo "\n";

    // [while] and [do-while] loops also use C style syntax so they will be familiar
    // to JavaScript developers. [continue] and [break] also work as expected.

    // Prints even numbers between 0 and 8
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


    // Prints 0...4
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
    // TITLE: PHP Syntax - Functions
    // Defining and calling functions in PHP is similar to other C style languages.
    // While functions are easily defined in PHP most often popular Frameworks and
    // PHP projects define classes instead of functions. PHP however has many
    // built-in functions that are used in development.
    function add($x, $y) {
        return $x + $y;
    }

    // Optional parameters can be specified by assigning a value to the variable.
    function increment($x, $y = 1) {
        return $x += $y;
    }

    // Callback functions can be defined and set to a variable just as you would
    // use in JavaScript.
    $subtract = function($x, $y) {
        return $x - $y;
    };

    // This code calls the above functions and prints "2"
    $x = 1;
    $y = 2;
    $z = add($x, $y);      // returns 3
    $z = increment($z);    // returns 4
    $z = increment($z, 2); // returns 6
    $z = $subtract($z, 4); // returns 2
    echo $z;
    echo '<br>';

    // Unlike JavaScript PHP functions do not have access to variables in the
    // parent scope. The [use] keyword can be used to pass variables from the
    // parent scope. When using this syntax and setting [$x] in the called function
    // [$x] does not get set from the parent scope so this code prints "1".
    $scope_test = function() use ($x) {
        $x = 123;
    };
    $scope_test();
    echo $x;
    echo '<br>';

    // This version is similar to the above version however the variable [$x] is
    // passed by-reference using the [&] operator. This version will print "123"
    // because [$x] gets modified.
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
    // TITLE: PHP Syntax - Classes and Objects
    // Full details of how to define and use classes is beyond the scope of this
    // quick reference page however the basic syntax is shown below which can
    // help you get started.

    class Math
    {
        // Define a Member Variable
        public $value = 0;

        // Define a Class Constructor with an Optional Parameter.
        // Defining [__construct] is optional.
        public function __construct($number = 0)
        {
            $this->value = $number;
            echo 'Class Created with Value: ' . $number . '<br>';
        }

        // Define a Class Destructor
        public function __destruct()
        {
            echo 'Class Destroyed<br><br>';
        }

        // Public function that returns the object instance [$this]
        public function add($number) {
            $this->value += $number;
            return $this;
        }

        // Function with no parameter or return value
        public function show()
        {
            echo 'Value: ' . $this->value . '<br>';
        }
    }

    // Prints:
    /*
    Class Created with Value: 0
    Value: 3
    Class Destroyed
    */
    $math = new Math();
    $math->add(1)->add(2)->show();
    $math = null;

    // Prints:
    /*
    Class Created with Value: 10
    Value: 15
    ...
    */
    $math = new Math(10);
    $math->add(5)->show();

    // Read from a member variable:
    $value = $math->value;
    echo $value . '<br>';
    // EXAMPLE_CODE_END
});

$app->get('/examples/php-encoding', function() {
    // Content is output as plain text
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: PHP Syntax - Encoding - JSON, Base64, Base64-URL
    // CLASS: Encoding\Json, Encoding\Base64Url
    // Create a Basic Object and Array for Encoding
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
    // Encode and Decode JSON
    // -------------------------------------------

    // Since PHP Array's are used like a Dictionary or Hash, both examples print:
    //     {"string":"Test","number":123,"bool":true}
    $json = json_encode($object);
    echo $json;
    echo "\n";

    $json = json_encode($array);
    echo $json;
    echo "\n\n";

    // Use the 2nd Parameter for formatted JSON
    $json = json_encode($object, JSON_PRETTY_PRINT);
    echo $json;
    echo "\n";

    // Decode and print the object with details using [print_r()]:
    $decoded = json_decode($json);
    print_r($decoded);
    echo "\n";

    // By default objects are decoded as [stdClass] objects. To return an array
    // instead pass [true] as the 2nd parameter.
    $decoded = json_decode($json, true);
    print_r($decoded);
    echo "\n";

    // If there is an error decoding JSON data [null] will be returned.
    // If you need to handle invalid JSON you can do so like this:
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('Error decoding JSON Data: ' . json_last_error_msg());
    }

    // FastSitePHP includes a JSON helper class which throws exceptions on
    // JSON errors instead of the default behavior of returning [false] or [null].
    $json = \FastSitePHP\Encoding\Json::encode($object);
    $decoded = \FastSitePHP\Encoding\Json::decode($json);

    // Often though in most code simply calling [json_encode()] or [json_decode()]
    // will be enough. By default, PHP decodes large numbers as floats. If you
    // want stricter decoding so they come in strings, then you can use additional
    // options. This is how FastSitePHP's JSON class decodes as it is used in the
    // JWT, Encryption, and SignedData classes. [JSON_BIGINT_AS_STRING] is not
    // avaiable on PHP 5.3 so FastSitePHP uses compatible code.
    $decoded = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

    // -------------------------------------------
    // Encode and Decode Base64
    // -------------------------------------------

    // Prints: "VGhpcyBpcyBhIHRlc3Q="
    $data = 'This is a test';
    $base64 = base64_encode($data);
    echo $base64;
    echo "\n";

    // When decoding if there is an error then [false] is returned
    $decoded = base64_decode($base64);
    print_r($decoded);
    echo "\n\n";

    // -------------------------------------------
    // Encode and Decode Base64-URL Format
    // -------------------------------------------

    // PHP does not include built-in functions for Base64-URL format so
    // FastSitePHP includes a helper class with static methods. They behave
    // similar to the built-in functions [base64_encode()]  and [base64_decode()]
    // so if there is an error then [false] is returned.

    $base64url = \FastSitePHP\Encoding\Base64Url::encode($data);
    echo $base64;
    echo "\n";

    $decoded = \FastSitePHP\Encoding\Base64Url::decode($base64url);
    print_r($decoded);
    echo "\n";
    // EXAMPLE_CODE_END
});

$app->get('/examples/php-error', function() use ($app) {
    // Comment out different code to test or place "exit();"
    // after certain blocks of code.

    // EXAMPLE_CODE_START
    // TITLE: PHP Syntax - Errors and Exceptions
    // PHP uses both Errors that are triggered and Exceptions that are thrown.

    // PHP handles errors a differently than many languages. For example in many
    // languages a "divide by zero" error would either throw an Exception or be
    // fatal and halt the program and in compiled languages an undefined variable
    // would not allow the program to run. However when using PHP unless error
    // reporting is set both of these errors would simply be ignored and the
    // script could continue with unexpected results. This can make programming
    // with PHP difficult at first if you are coming from another language.
    // FastSitePHP makes things easy because it runs code in strict mode and
    // converts errors to exceptions once [app->setup()] is called.

    // To handle all errors and exceptions globally in PHP, four different functions
    // have to be first set. These are automatically handled from [app->setup()]:
    //   error_reporting()
    //   set_exception_handler()
    //   set_error_handler()
    //   register_shutdown_function()

    // In PHP [try...catch] logic is similar to many languages such as JavaScript:
    try {
        throw new \Exception('Test');
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo '<br>';
    }

    // Variables can be checked if they might not exist.
    // This code works and no error is triggered.
    if (isset($x) === false) {
        echo 'Variable [$x] is not defined<br>';
    }

    // Uncommenting the lines below will trigger different types of errors.
    // When using PHP default development settings the errors will often cause
    // an error message in the middle of the code and code after will still
    // be executed.
    //
    // echo $x;     // [E_NOTICE]  = "Undefined variable: x"
    // echo 1 / 0;  // [E_WARNING] = "Division by zero"

    // FastSitePHP converts Errors to Exceptions so they can be caught.
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
// TITLE: Hello World with FastSitePHP
// CLASS: Application
<?php
// Only two files are required to run FastSitePHP and they can
// be in the same directory as [index.php] or the contents can
// be embedded in the main php page.
require 'Application.php';
require 'Route.php';

// Create the Application Object and optionally setup
// Error Handling and a Timezone.
$app = new FastSitePHP\Application();
$app->setup('UTC');

// Define the 'Hello World' default route
$app->get('/', function() {
    return 'Hello World!';
});

// Return a JSON Response by returning an Object or an Array
$app->get('/json', function() {
    return ['Hello' => 'World'];
});

// For all other requests, return the URL as a plain text response.
// The [use] keyword makes the [$app] variable available to the function.
$app->get('/*', function() use ($app) {
    $app->header('Content-Type', 'text/plain');
    return $app->requestedPath();
});

// Run the App
$app->run();
// EXAMPLE_CODE_END
*/

/*
// EXAMPLE_CODE_START
// CLASS: AppMin
<?php
// Only two files are required to run FastSitePHP AppMin and they can
// be in the same directory as [index.php] or the main php page.
require 'AppMin.php';
require 'Route.php';

// Create the AppMin Object and optionally setup
// Error Handling and a Timezone.
$app = new FastSitePHP\AppMin();
$app->setup('UTC');

// Define the 'Hello World' default route
$app->get('/', function() {
    return 'Hello World!';
});

// Return a JSON Response by returning an Object or an Array
$app->get('/json', function() {
    return ['Hello' => 'World'];
});

// Send a Plain Text Response and Custom Header. AppMin is minimal in size so
// optional URL parameters [:name?] and Wildcard URL's [*] are not supported.
$app->get('/hello/:name', function($name) use ($app) {
    $app->headers = [
        'Content-Type' => 'text/plain',
        'X-Custom-Header' => $name,
    ];
    return 'Hello ' . $name;
});

// Run the App
$app->run();
// EXAMPLE_CODE_END
*/

$app->get('/examples/app-basic-routes', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Application Object - Defining Basic Routes
    // CLASS: Application
    // The Application Object is the key Object in FastSitePHP. It is used to
    // define routes, provide request info, render templates, send the response,
    // and more. If you are using a copy of this site or a starter site the
    // Application Object will be available as the variable [$app] and routes
    // are defined in the page [app.php].

    // Basic Route
    // Send an HTML Response when either '/about' or '/about/' is requested
    $app->get('/about', function() {
        return '<h1>About Page</h1>';
    });

    // By default URL's are case-sensitive however this can be
    // turned off and then '/ABOUT' would match the above route.
    $app->case_sensitive_urls = false;

    // If setting URL strict mode then the above URL would only match
    // to '/about' and '/about/' would have to be explicitly defined.
    $app->strict_url_mode = true;
    $app->get('/about/', function() {
        return '<h1>About Directory</h1>';
    });

    // The about call using [get()] matches only 'GET' requests. If you would like
    // to handle both 'GET' and 'POST' or other methods with the same route you
    // can define the route using the [route()] function then check the if there is
    // data sent with the request as shown below. The [route()] function will accept
    // all request methods.
    $app->route('/form', function() {
        if ($_POST) {
            // Handle posted form data
        }
        // Handle GET request, return rendered template, etc
    });

    // In addition to GET Requests you can handle [ POST, PUT, PATCH, and DELETE]
    // Requests using named functions.
    $app->get('/method', function() { return 'get()'; });
    $app->post('/method', function() { return 'post()'; });
    $app->put('/method', function() { return 'put()'; });
    $app->patch('/method', function() { return 'patch()'; });
    $app->delete('/method', function() { return 'delete()'; });

    // The same URL can be defined multiple times and the first matching response
    // will stop additional routes from being evaluated. In this example the route
    // '/example' will return the text 'Example 2'.
    $app->get('/example', function() { return null; });
    $app->get('/example', function() { return 'Example 2'; });
    $app->get('/example', function() { return 'Example 3'; });

    // In addition to returning a response you can also simply output a response
    // using [echo] or other functions.
    $app->get('/echo-response', function() {
        echo 'Output';
    });
    // EXAMPLE_CODE_END

    // Return JSON Array of all Defined Routes
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-parameter', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Define a Route with a Parameter
    // Send a response 'Hello FastSitePHP!' for the URL '/hello/FastSitePHP'.
    // The ':name' text in the route pattern defines a parameter for the route
    // because it starts with the ':' character.
    $app->get('/hello/:name', function($name) {
        return 'Hello ' . $name;
    });
    // EXAMPLE_CODE_END

    // Return JSON Array of all Defined Routes
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-optional-parameter', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Define a Route with an Optional Parameter
    // Send a response 'Hello World!' for the URL '/hello' or in the case of the
    // optional [name] variable safely escape and return a message with the name.
    // The [use] keyword makes the [$app] variable available to the function
    // and the question mark in the URL pattern ':name?' makes the variable optional.
    $app->get('/hello/:name?', function($name = 'World') use ($app) {
        return 'Hello ' . $app->escape($name) . '!';
    });

    // In addition to optional parameters a wildcard character '*' can be used at
    // the end of the URL to handle all requests that match the start of the URL.
    // In this example the following two URL's would both be matched.
    //     '/hello/world'
    //     '/hello/page1/page2/page3'
    $app->get('/hello/*', function() use ($app) {
        $app->header('Content-Type', 'text/plain');
        return $app->requestedPath();
    });
    // EXAMPLE_CODE_END

    // Return JSON Array of all Defined Routes
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-controllers', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Define a Route that maps to a Controller Class
    // CLASS: Application
    // Defining routes with callback functions allows for fast prototyping
    // and works well when minimal logic is used. As code grows in size it
    // can be organized into controller classes.

    // Optionally specify the Controller Class Root Namespace. When using this if a
    // class 'Examples' is created then it will map to 'App\Controllers\Examples'.
    $app->controller_root = 'App\Controllers';

    // Similar to [controller_root] is [middleware_root] which applies to
    // [Route->filter()] and [$app->mount()] functions.
    $app->middleware_root = 'App\Middleware';

    // The two format options are 'class' and 'class.method'. When using only
    // class name then the route function [route(), get(), post(),  put(), etc]
    // will be used for the method name of the matching controller.
    $app->get('/:lang/examples', 'Examples');
    $app->get('/:lang/examples/:page', 'Examples.getExample');

    // Controller Class Example
    class Examples
    {
        public function get(Application $app, $lang) { }
        public function getExample(Application $app, $lang, $page) { }
    }

    // In addition to organizing code into controller classes you can also separate
    // routes into separate files using the [mount()] function. The mount function
    // will load a file in the same directory only if the starting part of the
    // Requested URL matches the Mount URL. An optional 3rd parameter accepts a
    // callback function or string of 'Class.method' and if false is returned
    // then the file won't be loaded.
    $app->mount('/data/', 'routes-data.php');
    $app->mount('/secure/', 'routes-secure.php', function() {
        // Logic ...
        return false;
    });
    $app->mount('/sysinfo/', 'routes-secure.php', 'Env.isLocalhost');
    // EXAMPLE_CODE_END

    // Return JSON Array of all Defined Routes
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-parameter-validation', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Route Parameter Validation
    // The Application Object has a [param()] function which can be used to
    // validate and convert URL parameters to a specific format such as a number.

    // The function is defined as:
    //     param($name, $validation, $converter = null)

    // Parameters:
    //     Validation = ['any', 'int', 'float', 'bool'], a valid regular expression,
    //         or a Closure/Callback function. When using 'int|float|bool' the data
    //         type will automatically be converted.
    //     Convertor = ['int', 'float', 'bool'] or a Closure/Callback function.

    // Basic Example
    //     '/product/123' = Match and [$product_id] will be an integer
    //     '/product/abc' = 404 Page Not Found
    $app->param(':product_id', 'int');
    $app->get('/product/:product_id', function($product_id) {
        var_dump($product_id);
    });

    // Additional Examples of Defining Parameter Rules. For more see full
    // documentation and other examples.

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

    // Return JSON Array of all Defined Routes
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-route-filter', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Use Route Filters
    // CLASS: Route
    // Routes can have custom filter functions assigned to them to run specific
    // code if a route is matched, perform validation, or another task required
    // by your site. Filter functions only run if the route is matched to the
    // requested URL.

    // Define some callback/closure functions
    $text_response = function() use ($app) {
        $app->header('Content-Type', 'text/plain');
    };
    $is_authenticated = function() {
        // Check User Permissions ...
        return true;
    };

    // When routes are created [get(), route(), post(), etc], the created route
    // is returned so you can call [filter()] after defining the route.
    // This page will be returned as Plain Text page because the filter function
    // sets the Response Header and returns no value.
    $app->get('/text-page', function($name) {
        return 'Hello';
    })->filter($text_response);

    // A route can have multiple filters and for clarity you may want to put
    // filter functions on seperate lines. This page will only be called if
    // [$is_authenticated] returns [true] and it will also be a text response.
    $app->get('/secure-text-page', function($name) {
        return 'Hello ' . $name;
    })
    ->filter($is_authenticated)
    ->filter($text_response);

    // The [filter()] function also accepts a string representing
    // a class and method in the format of 'Class.method'.
    $app->get('/phpinfo', function($name) {
        phpinfo();
    })
    ->filter('Env.isLocalhost');

    // When using string filters you can specify a root namespace
    // for the classes using the App property [middleware_root].
    $app->middleware_root = 'App\Middleware';
    // EXAMPLE_CODE_END

    // Return JSON Array of all Defined Routes
    $app->json_options = JSON_PRETTY_PRINT;
    return $app->routes();
});

$app->get('/examples/app-info', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Application Object - Basic Request Info
    // Many frameworks require special configuration values in order to handle
    // requests. FastSitePHP figures this out automatically and provides several
    // functions in the Application Object to return basic request info.

    // If your site does not use a proxy server such as load balancer then these
    // functions can be used for building URL's or other app needs. If your site
    // uses a load balancer with custom host headers then you would want to use
    // the request object to obtain the root URL.

    // Root or Base URL for the Site. This is often needed to build full path
    // URL's on web pages.
    //
    // Examples:
    //     # [index.php] specified in the URL
    //     Request: https://www.example.com/index.php/page
    //              https://www.example.com/index.php/page/page2
    //     Returns: https://www.example.com/index.php/
    //
    //     # [index.php] Located in Root Folder
    //     Request: https://www.example.com/page
    //              https://www.example.com/page/page2
    //     Returns: https://www.example.com/
    //
    //     # [index.php] Located under [site1]
    //     Request: https://www.example.com/site1/page
    //              https://www.example.com/site1/page/page2
    //     Returns: https://www.example.com/site1/
    //
    $root_url = $app->rootUrl();

    // Root Directory for the Site. Often needed to build URL's for Static
    // Resources such as CSS or JavaScript files.
    //
    //     Request: https://www.example.com/index.php/page
    //              https://www.example.com/index.php/page/page2
    //              https://www.example.com/page
    //     Returns: https://www.example.com/
    //
    $root_dir = $app->rootDir();

    // Get the Requested URL which exits after the Root URL. This will be
    // based on where the [index.php] or entry PHP file is located.
    //
    //     Request: https://www.example.com/index.php/test/test?test=test
    //              https://www.example.com/index.php/test/test
    //              https://www.example.com/test/test/
    //              https://www.example.com/test/test
    //              https://www.example.com/site1/index.php/test/test
    //     Returns: '/test/test'
    //
    // In the above example both '/test/test/' and '/test/test' return
    // '/test/test' when using the default property [$app->strict_url_mode = false]
    // otherwise the exact URL would be returned.
    //
    $requested_path = $app->requestedPath();

    // Example usage for building URL's:
    $site_css = $app->rootDir() . 'css/site.css';
    $docs_link = $app->rootUrl() . '/documents';
    //
    // <link href="{{ $site_css }}" rel="stylesheet" />
    // <a href="{{ $docs_link }}">Documents</a>
    // EXAMPLE_CODE_END

    // Return as JSON
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
    // TITLE: Dynamic Functions and Lazy Loading Properties
    // FastSitePHP allows for the Application Object to be assigned dynamic
    // functions and lazy loading properties. This allows for custom functions
    // and resources shared by many routes to be organized under a global object
    // and can allow for simple and clear dependancy injection.

    // JavaScript Example - This works to add a function dynamically to an object:
    //
    // var obj = {};
    // obj.test = function() { alert('test'); };
    // obj.test();

    // PHP Example - The function can be assigned to a property however if called
    // an error is triggered - 'Call to undefined method ...'.
    $obj = new \stdClass;
    $obj->test = function() { echo 'test'; };
    // $obj->test();

    // When using FastSitePHP's Application object you can simply assign and use
    // functions just like in JavaScript or Ruby.
    $app->test = function() { echo 'test'; };
    $app->test();

    // The native PHP function [method_exists()] will not work for custom functions
    // so to check if either a built-in or custom App method exists use this.
    $exists = $app->methodExists('test');

    // The [lazyLoad()] function accepts a property name and callback function.
    // It creates the object as a property of the app only if used. This is ideal
    // for working with sites where some pages use a resource and some do not.
    $app->lazyLoad('db', function() {
        return $pdo = new \PDO('sqlite::memory:');
    });

    // [$app->db] gets set here on first use.
    $sql = 'CREATE TABLE test (id INTEGER PRIMARY KEY, test)';
    $app->db->query($sql);

    // [$app->db] now works as a standard property as it was previously called.
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
// TITLE: Application Events
<?php
// Just like the Hello World Demo this code can be copied to a seperate
// [index.php] or other file and then tested.

// There are 5 Application callback events:
//     before(), beforeSend(), after(), notFound(), and error()
// They can be used to handle custom logic while the application is running.

// Load Files
require 'Application.php';
require 'Route.php';
// Or use an Autoloader:
// require '../../vendor/autoload.php';

// Create and Setup App Object
$app = new FastSitePHP\Application();
$app->setup('UTC');

// ------------------------------------------------------------------
// Define Events
// ------------------------------------------------------------------

// [Before] Events will be called from the [run()] function prior to any routes
// being matched. All Event functions can be called multiple times and will
// run in order that they are defined.
$app->before(function() use ($app) {
    $app->content = '[before1]';
});
$app->before(function() use ($app) {
    $app->content .= '[before2]'; // Append
});

// [Before Send] Events will be called from the [run()] function after
// a route has been matched to the requested resource. Functions passed
// to the [beforeSend()] function should be defined as [function($content)]
// and they must return a response otherwise a 404 'Not found' response will
// be sent to the client.
$app->beforeSend(function($content) {
    return $content . '[beforeSend]';
});

// [Not Found] Events will be called from the [run()] function after all
// routes have been checked with no routes matching the requested resource.
// Functions passed to the [notFound()] function take no parameters and
// if they return a response then it be handled as a standard route and
// will call any defined [beforeSend()] functions afterwards.
$app->notFound(function() use ($app) {
    return $app->content . '[notFound]';
});

// [Error] Events will be triggered if an unhandled Exception is thrown,
// an error is triggered, or a route is not matched and would trigger a
// 404 or 405 response. This function can be used to log errors or handle
// the response with a custom error. If [exit()] is not called then the
// specified  or standard FastSitePHP error template will be rendered.
$app->error(function($response_code, $e) use ($app) {
    // $response_code = [null, 404, 405, or 500]
    // $e = [null, Exception, or Throwable]
    if ($app->requestedPath() === '/error-test-1') {
        echo $app->content . '[Custom Error]';
        exit();
    }
});

// [After] Events will be called from the [run()] function after the response
// has been sent to the client. Functions passed to the [after()] function
// should be defined as [function($content)]; the [$content] parameter defined
// in the callback is the contents of the response that was sent to the client
// and it cannot be modified from here. The only way that [after()]  functions
// will not get called is if their script is terminated early from  PHP's
// [exit()] statement or if the error handling is not setup and an error occurs.
$app->after(function($content) {
    echo '[after]';
});

// ------------------------------------------------------------------
// Define Routes
// ------------------------------------------------------------------

// This response will output the following:
//     [before1][before2][page][beforeSend][after]
$app->get('/', function() use ($app) {
    return $app->content . '[page]';
});

// Call URL '/test' and see the following:
//     [before1][before2][notFound][beforeSend][after]

// This response will output the following:
//    [before1][before2][Custom Error]
$app->get('/error-test-1', function() {
    throw new \Exception('Error Test 1');
});

// Displays Standard Error Page with [after] showing at very bottom
$app->get('/error-test-2', function() {
    throw new \Exception('Error Test 2');
});

// ------------------------------------------------------------------
// Run the App
// ------------------------------------------------------------------
$app->run();
// EXAMPLE_CODE_END
*/

/*
// EXAMPLE_CODE_START
// TITLE: PHP Template Example
<!--
// This is the contents of the file [template.php] which is shown as an example
// on this page. When calling [render()] the Application Object is passed as
// [$app] which allows for [escape()] and other functions to be used. In addition
// to the standard [if (expression) { code }] syntax PHP provides an alternative
// syntax for control structures when using templates [if (expr): (code) endif].
//
// PHP templates are high performance and use very little memory however the
// syntax can be considered more verbose than many modern template formats. If
// you prefer to use a different template format there are many widely used and
// high quality template engines for PHP that can be intergrated with FastSitePHP.
-->

<h1><?= $app->escape($page_title) ?></h1>
<?php if (count($list) === 0): ?>
    <p>No Records found</p>
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
    // TITLE: Application - Render Server-Side Template Files
    // Set the Template Root Directory and Specific Core Files
    $app->template_dir = __DIR__ . '/views/';
    // $app->header_templates = '_header.php';
    // $app->footer_templates = '_footer.php';
    // $app->error_template = 'error.php'; // For 500 Responses
    // $app->not_found_template = '404.php'; // For 404 and 405 Responses

    // Optionally show detailed errors when using the default error
    // template and set custom error messages. With the default template
    // detail errors will be displayed when running from localhost.
    $app->show_detailed_errors = true;
    // $app->error_page_title = 'Custom Error Page';
    // $app->error_page_message = 'Custom Error Message';
    // $app->not_found_page_title = 'Custom 404 Page';
    // $app->not_found_page_message = 'Custom 404 Message';
    // $app->method_not_allowed_title = 'Custom 405 Page';
    // $app->method_not_allowed_message = 'Custom 405 Message';

    // Define Data for the Template. Variables can be defined in the App's
    // [locals] property and they can be passed on the render function.
    $app->locals['year'] = date('Y');
    $data = [
        'page_title' => 'PHP Template Example',
        'list' => ['Item 1', 'Item 2', 'Item 3', 'Item 4'],
    ];

    // Render the PHP Template and return a string.
    // The template source is shown in the above example code section.
    $html = $app->render('template.php', $data);
    // EXAMPLE_CODE_END
    return $html;
});

$app->get('/examples/app-render-mustache', function() use ($app) {
    // NOTE - To use Mustache it must be installed and requires
    // using Composer or modifying the [autoload.php] file.
    // This renders the file [template.mustache.htm].

    // EXAMPLE_CODE_START
    // TITLE: Application - Render with a Custom Template Engine
    // Define a Custom Template Engine that uses the
    // popular Mustache Template System.
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

    // Define Data for the Template
    $app->locals['year'] = date('Y');
    $data = [
        'page_title' => 'Mustache Template Example',
        'list' => ['Item 1', 'Item 2', 'Item 3', 'Item 4'],
        'has_list' => true,
    ];

    // Render the Template
    $html = $app->render('template.mustache', $data);

    // When using Custom Templates you can define Custom Error and Not Found Pages:
    // $app->error_template = 'error'; // For 500 Responses
    // $app->not_found_template = '404'; // For 404 and 405 Responses
    // EXAMPLE_CODE_END
    return $html;
});

$app->get('/examples/request-basic', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: HTTP Request Object - Reading Query Strings, Form Fields, and Cookies
    // CLASS: Web\Request
    // The request object can be used obtain info from the client for an
    // HTTP request. This includes query strings, form fields, cookies,
    // headers, and more. The request object also contains functions to
    // sanitize (“clean”) and safely read client info.

    // Without using a Framework, Query Strings, Form Variables and other
    // User Input can be read through PHP Superglobals [$_GET, $_POST, etc].
    // Example, read the Query String Field [number]:
    $number = $_GET['number'];

    // If the query string [type] does not exist then the above code
    // would throw an exception so to safely get the value you can first
    // check if it is set.
    $number = (isset($_GET['number']) ? $_GET['number'] : null);

    // An additional line of PHP code can be used to force a numeric value:
    $number = (int)$number;

    // The Request object can be used instead to safely read the values, convert
    // data types, etc. To use the Request object simply create one:
    $req = new \FastSitePHP\Web\Request();

    // You can then read query strings by name without including safety logic:
    $number = $req->queryString('number');

    // An optional 2nd parameter can be used to convert to a specific data type.
    // In this example the value will be converted to an interger if it is valid
    // otherwise null will be returned.
    $number = $req->queryString('number', 'int?');

    // In addition to [queryString()] functions [form()] and [cookie()] can be
    // used in the same manner.
    $value  = $req->form('field');
    $cookie = $req->cookie('name');

    // The Request object also contains a helper function to handle user input
    // or objects where a value may or may not exist. This can be used to prevent
    // errors when reading complex JSON object and to to sanitize (“clean”) data
    // from any object or array.
    //
    // Function Definititon:
    //     value($data, $key, $format = 'value?', $max_length = null)
    //
    // Data Example:
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
    // Function Examples:
    //    'test'        = $req->value($_POST, 'input1');
    //    // Truncate the string to 2 characters:
    //    'te'          = $req->value($_POST, 'input1',    'string', 2);
    //    123.456       = $req->value($_POST, 'input2',    'float');
    //    ''            = $req->value($_POST, 'missing',   'string'); // Missing
    //    1             = $req->value($_POST, 'checkbox1', 'checkbox');
    //    0             = $req->value($_POST, 'checkbox2', 'checkbox'); // Missing
    //    true          = $req->value($_POST, 'checkbox1', 'bool');
    //    'FastSitePHP' = $req->value($json,  'app');
    //    'abc'         = $req->value($json,  'strProp',   'string?');
    //    0             = $req->value($json,  'strProp',   'int');  // Invalid Int
    //    null          = $req->value($json,  'strProp',   'int?'); // Invalid Int
    //    123           = $req->value($json,  'numProp',   'int');
    //    'item1'       = $req->value($json,  ['items', 0, 'name']);
    //    'item2'       = $req->value($json,  ['items', 1, 'name']);
    //    null          = $req->value($json,  ['items', 2, 'name']); // Missing
    //
    // See full documentation for more. If you need full validation rather than
    // data cleaning see the [\FastSitePHP\Data\Validator] class.
    // EXAMPLE_CODE_END

    // Return Text Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($number),
        ]));
});

$app->route('/examples/request-content', function() use ($app) {
    // NOTE - this function uses [$app->route()] which means
    // it can accept any method [GET, POST, etc]. When using
    // the default [GET] the body and content type will be empty.

    // EXAMPLE_CODE_START
    // TITLE: HTTP Request Object - Request JSON and Content
    // CLASS: Web\Request
    // Create the Request Object
    $req = new \FastSitePHP\Web\Request();

    // Get the Request Content Type. This is a helper field that returns
    // a simple value based on the 'Content-Type' header:
    //     'json'      = 'application/json'
    //     'form'      = 'application/x-www-form-urlencoded'
    //     'xml'       = 'text/xml' or 'application/xml'
    //     'text'      = 'text/plain'
    //     'form-data' = 'multipart/form-data'
    // If different the raw header value will be returned and if the header
    // is not defined then [null] will be returned.
    $type = $req->contentType();

    // The Request body/content can be read from [content()]. If the Request Type
    // is JSON then the object will be parsed and an object/array will be returned.
    // If [contentType() === 'form'] then an array will be returned otherwise the
    // body/content is returned as a string. In PHP a string can also be used for
    // binary data as a string is simply array of bytes.
    $body = $req->content();

    // The [value()] function can be used to safely read nested values from a
    // submitted JSON object. See other examples and docs for more on using the
    // [value() function.
    $value = $req->value($body,  ['items', 0, 'name']);
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // Overwrite Request Header
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4';

    // EXAMPLE_CODE_START
    // TITLE: HTTP Request Object - Header Fields
    // CLASS: Web\Request
    // Create the Request Object
    $req = new \FastSitePHP\Web\Request();

    // Reading Common Header Fields can be done through functions:
    $origin = $req->origin();
    $userAgent = $req->userAgent();
    $referrer = $req->referrer();
    $client_ip = $req->clientIp();
    $protocol = $req->protocol();
    $host = $req->host();
    $port = $req->port();

    // When using functions with 'Accept' Headers an array of data is returned,
    // and an optional parameter can be passed to return [true] or [false].
    $accept_encoding = $req->acceptEncoding();
    $accept_language = $req->acceptLanguage();

    // Example:
    //    'Accept-Language' Header Value = 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
    // Returns:
    //    [
    //        ['value' => 'ru-RU', 'quality' => null],
    //        ['value' => 'ru',    'quality' => 0.8],
    //        ['value' => 'en-US', 'quality' => 0.6],
    //        ['value' => 'en',    'quality' => 0.4],
    //    ];

    $accept_en = $req->acceptLanguage('en'); // true
    $accept_de = $req->acceptLanguage('de'); // false

    // Any header can be read when using the [header()] function:
    $content_type = $req->header('Content-Type');
    $user_agent = $req->header('User-Agent');

    // Header Keys are Case-insensitive so the following all return the same value:
    $content_type = $req->header('content-type');
    $content_type = $req->header('CONTENT-TYPE');
    $content_type = $req->header('Content-Type');

    // All headers can be read from the [headers()] function:
    $headers = $req->headers();
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // Overwrite Settings and Headers for Demo
    $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = "' OR '1'='1 --, 127.0.0.1, 54.231.1.5";

    // EXAMPLE_CODE_START
    // TITLE: HTTP Request Object - Proxy Header Fields
    // CLASS: Web\Request
    // Create the Request Object
    $req = new \FastSitePHP\Web\Request();

    // Request Proxy Headers are used for key fields such as client IP when a
    // web server sits behind a “proxy” server on a local network, for example
    // a load balancer. Reading the values correctly is important for security,
    // however in general with any programming language or framework reading proxy
    // headers if often difficult and requires extra config. FastSitePHP makes
    // the task easy with no config required.

    // For example, simply reading the Client IP of the request can be done
    // by reading the value of REMOTE_ADDR.
    $client_ip = $_SERVER['REMOTE_ADDR'];

    // If the load balancer is configured to provide the Client IP it will
    // usually be one of the following Request Headers [X-Forwarded-For,
    // Client-Ip, or Forwarded]. However since the end user can send data with
    // the Request Header it must be read correctly. The standardized header
    // [Forwarded] has a format like this:
    //     'for=192.0.2.43, for="[2001:db8:cafe::17]";proto=http;by=203.0.113.43'
    // While non-standard but widely used headers such as [X-Forwarded-For] use
    // this format:
    //     'client-ip1, client-ip2, proxy1, proxy2'
    // FastSitePHP handles both formats.

    // For example assume the load balancer is at '10.0.0.1', '10.0.0.2' is used
    // for additional content filtering, and [X-Forwarded-For] came in with the
    // the following value:
    //     [REMOTE_ADDR]      =   '10.0.0.1'
    //     [X-Forwarded-For]  =   "' OR '1'='1 --, 127.0.0.1, 54.231.1.5, 10.0.0.2"
    // In this example, the following was submitted:
    //     - Client - A SQL Injection String of "' OR '1'='1 --"
    //     - Client - A localhost IP [127.0.0.1]
    //     - Client - Actual IP [54.231.1.5]
    //     - Server - 10.0.0.2

    // When simply reading Client IP without any parameters the IP of the load
    // balancer is returned for this example which is '10.0.0.1'.
    $client_ip = $req->clientIp();

    // Then when using the default 'from proxy' setting the correct User IP
    // value of '54.231.1.5' is returned. If no proxy server is used then the
    // default settings of 'from proxy' are safe to call.
    $user_ip = $req->clientIp('from proxy');

    // When using proxies an optional 2nd parameter of [$trusted_proxies] is
    // avaiable. This defaults to the string 'trust local', however an array
    // of specific IP or IP Ranges (CIDR format) can be used for more specific
    // filtering. Additionally the first parameter [$option] can also be
    // modified to read from different Request Headers.
    $user_ip = $req->clientIp('from proxy', 'trust local');

    // In addition to Client IP, proxy values can also be read for
    // [Protocol, Host, and Port]:
    $portocal = $req->protocol('from proxy'); // 'http' or 'https'
    $host = $req->host('from proxy');
    $port = $req->port('from proxy');
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // TITLE: Request Object - Server Info
    // CLASS: Web\Request
    // The Request Object can return the Server IP and has a helper function
    // [isLocal()] that returns true only if both the requesting client and
    // the web server are on localhost ['127.0.0.1' or '::1']. In certain apps
    // you may want to enable certain features for development or local work
    // and these functions help with that.
    $req = new \FastSitePHP\Web\Request();
    $server_ip = $req->serverIp();
    $is_local  = $req->isLocal();

    // NOTE - the Web Server IP is often different than the actual Network IP.
    // To obtain the network IP (location of the server) use the Networking
    // Config Object instead:
    $config = new \FastSitePHP\Net\Config();
    $net_ip = $config->networkIp();
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // TITLE: Response - Content, Status Codes, Headers, Cookies, and Files
    // CLASS: Web\Response
    // By default when a string is returned in a route the server returns an
    // HTML response. Without creating a Response Object, the Application Object
    // can be used to specify a different 'Content-Type' Header which is what
    // Browsers and HTTP Clients use to determine how to handle the response.
    $app->get('/app-text-response', function() use ($app) {
        $app->header('Content-Type', 'text/plain');
        return 'Response using the Application Object';
    });

    // When using the Response Object [contentType()] and [content()]
    // are the main functions to specify different content types.
    $app->get('/text-response', function() {
        $res = new \FastSitePHP\Web\Response();
        return $res->contentType('text')->content('Text Response');
    });

    // When using the Response Object, properties are set through getter/setter
    // functions and are chainable so they can be used on one line as shown
    // above or seperated to multiple lines as shown here.
    $app->get('/text-response2', function() {
        return (new \FastSitePHP\Web\Response())
            ->contentType('text')
            ->content('Text Response 2');
    });

    // Using the Response Object
    $res = new \FastSitePHP\Web\Response();

    // Set the 'Content-Type' Header.
    // The following 3 function calls all set the same value.
    // The difference is that [contentType()] is a helper function which allows
    // for short-hand values of [html, json, jsonp, text, css, javascript, xml].
    $res->contentType('text');
    $res->contentType('text/plain');
    $res->header('Content-Type', 'text/plain');

    // Set Content
    // For most content types use a string when setting [content()].
    $res->content('<h1>FastSitePHP</h1>');

    // For JSON Content either Objects and Arrays are used
    $object = [
        'title' => 'Demo',
        'number' => '123',
    ];

    $res
        ->contentType('json')
        ->content($object);

    // The helper [json()] function sets both [contentType()] and [content()]
    $res->json($object);

    // For formatted JSON set the option [JSON_PRETTY_PRINT] before sending
    // the Response. By default [JSON_UNESCAPED_UNICODE] is used and JSON
    // is minimized. Any constant used by [json_encode()] can be set here.
    $app->json_options = (JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $res->jsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // Status Codes
    // [$app] only supports [200, 201, 202, 204, 205, 404, and 500]
    // and the Response Object allows and handles 304 Responses along
    // with any other valid or custom status codes.
    $app->statusCode(201);
    $res->statusCode(500);

    // A helper function [pageNotFound()] exists on the Application Object that
    // can be used to send a 404 response along with default or custom 404 page.
    $app->get('/document/:name', function($name) use ($app) {
        if ($name !== 'test') {
            return $app->pageNotFound();
        }
        return 'Test';
    });

    // Specify a file for the response; the file specified will be streamed to the
    // client and sent in a memory efficient manner so this function can be called
    // on very large files with minimal performance impact for the server.
    $file_path = __FILE__;
    $res->file($file_path);

    // Include specific Mime-Type along with Headers for Caching.
    // Another topic on this page covers caching in more detail.
    $res->file($file_path, 'text', 'etag:md5', 'private');

    // Example File Usage
    $app->get('/view-source-code', function() {
        $file_path = __FILE__;
        $res = new \FastSitePHP\Web\Response();
        return $res->file($file_path, 'download');
    });

    // Convert a file name or file type to a mime-type.
    //
    // File extensions that map to a Mime type with the function are:
    //     Text: htm, html, txt, css, csv, md, markdown, jsx
    //     Image: jpg, jpeg, png, gif, webp, svg, ico
    //     Application: js, json, xml, pdf, woff
    //     Video: mp4, webm, ogv, flv
    //     Audio: mp3, weba, ogg, m4a, aac
    //
    // If a file type is not associated with a mime-type then a file
    // download type of 'application/octet-stream' will be returned.
    $mime_type = $res->fileTypeToMimeType('video.mp4');
    $mime_type = $res->fileTypeToMimeType('mp4');

    // Set Response Headers and Cookies

    // Using the Application Object
    $app->header('X-API-Key', 'App_1234');
    $app->cookie('X-API-Key', 'App_1234');

    // Or using the Response Object
    $res->header('X-API-Key', 'Res_1234');
    $res->cookie('X-API-Key', 'Res_1234');

    // When creating a Response Object the Application Object can be
    // passed and all App settings from [statusCode(), cors(), noCache(), headers(),
    // cookies(), and [json_options] will be passed to the Response Object.
    $res = new \FastSitePHP\Web\Response($app);
    // EXAMPLE_CODE_END

    // Modify below or copy code from above to test different responses

    // return $res->content('HTML Test with Settings from App');

    // return $res
    //     ->reset()
    //     ->contentType('text')
    //     ->cookie('X-API-Key', 'Res_123')
    //     ->content('Text Response');

    $app->header('Content-Type', 'text/plain')->cookie('X-API-Key', 'App_1234');
    return 'Response using the Application Object [' . $mime_type . ']';
});

$app->get('/examples/redirect', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: HTTP Redirects
    // CLASS: Web\Response
    // HTTP Requests can be redirected using either the App or Response Object.
    // When using the App Object and calling [redirect()] the PHP script ends
    // immediately however any events defined from [after()] will be called.
    // If your site uses Server-side Unit Testing you may want to use the response
    // object which behaves as a regular route and doesn’t end script execution.

    // User makes this request
    $app->get('/page1', function() use ($app) {
        $app->redirect('page3');
    });

    // Or User makes this request
    $app->get('/page2', function() {
        $res = new \FastSitePHP\Web\Response();
        return $res->redirect('page3');
    });

    // User will then see this URL and Response
    $app->get('/page3', function() {
        return 'page3';
    });

    // The default Response Status Code is [302 'Found'] (Temporary Redirect),
    // and an optional 2nd parameter for both App and Response allow for
    // additional redirect response status codes:
    //   301  Moved Permanently
    //   302  Found
    //   303  See Other
    //   307  Temporary Redirect
    //   308  Permanent Redirect
    $app->get('/old-page', function() use ($app) {
        $app->redirect('new-page', 301);
    });
    // EXAMPLE_CODE_END

    // Redirect back to the main examples index
    $app->redirect('./');
});

$app->get('/examples/response-caching', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Response - Cache Headers and Client-Side Caching
    // CLASS: Web\Response
    // Examples below show how to use Response Headers to control how a Browser
    // or HTTP Client caches a Page or Resource.

    // Prevent a Browser or Client from Caching a Page or File.
    // Both the Application and the Response Objects have a [noCache()] function.
    // Calling these functions will send 3 Response Headers to the client:
    //     Cache-Control: no-cache, no-store, must-revalidate
    //     Pragma: no-cache
    //     Expires: -1
    $app->noCache();

    $res = new \FastSitePHP\Web\Response();
    $res->noCache();

    // If using certain Response Headers the Response Object will send a 304
    // "Not Modified" Response depending on the Request Headers. 304 Responses
    // are used by Browsers and other Clients to re-use previously fetched resources
    // from their cached copy. This allows the user to see static resources more
    // quickly and reduces the amount of traffic sent from the server.

    // 'Cache-Control' Response Header. This header has different options to tell
    // clients how they can cache a page. In this example only end users and not
    // proxy servers can cache the response and they must re-validate it each time.
    $res->cacheControl('private, must-revalidate');

    // 'Expires' Response Header. This header is used to tell a client how long the
    // content is valid for, however depending on 'Cache-Control' options this value
    // may be ignored. Setting this value though does not trigger a 304 response and
    // it's up to the browser or client how to handle it.
    $res->expires('+1 month');

    // 'ETag' Response Header (ETag is short for Entity Tag). An ETag represents a
    // unique value for the content (often using a Hash). Browsers and Clients will
    // send back an 'If-None-Match' Request Header with the version that they have
    // cached and if it matches then the Response Object will send a 304 Response
    // without the content since the browser can use the local copy.
    $res->etag('hash:md5');

    // The [etag()] function also accepts the hash itself or a closure function.
    $res->etag('0132456789abcdef');
    $res->etag(function($content) {
        return sha256($content);
    });

    // The optional 2nd parameter accepts the ETag Type of either 'strong' or 'weak'.
    // The default is 'weak' and that is recommended to avoid complex caching errors.
    // If you need to use 'strong' ETags you would likely want to do extra testing.
    $res->etag('hash:sha256', 'weak');

    // 'Last-Modified' Response Header. If set and if the client sends back an
    // 'If-Modified-Since' Request Header that matches then a 304 Response will
    // be sent. When setting the value use a Unix Timestamp or String that can be
    // parsed by the PHP Function [strtotime()].
    $res->lastModified('2019-01-01 13:01:30');

    // 'Vary' Response Header. The 'Vary' Response Header can be used to
    // specify rules for HTTP Caching and also to provide content hints to
    // Google and other Search Engines.
    $res->vary('User-Agent, Referer');

    // When sending a file as the response you can specify optional parameters
    // [$cache_type and $cache_control]. Cache Type has 3 valid options shown
    // below and Cache Control sets the [cacheControl()] function.
    $file_path = __FILE__;
    $content_type = 'text';
    $res->file($file_path, $content_type, 'etag:md5');
    $res->file($file_path, $content_type, 'etag:sha1',     'private');
    $res->file($file_path, $content_type, 'last-modified', 'public');

    // When sending etags with [file()] and using either 'etag:md5' or 'etag:sha1'
    // the hash is calculated each time. If you use ETags and have large files
    // or frequently accessed files it would be a good idea to save the hash
    // when the file is first created and set it through the [etag()] function.
    $saved_hash = '0132456789abcdef';
    $res->file($file_path)->etag($saved_hash);
    // EXAMPLE_CODE_END

    // Modify below to test different cache headers.
    // To easily test use browser dev tools with caching enabled,
    // then make changes and see when it sends either 200 or 304
    // when you refresh the page.
    $res->reset();

    // Uncomment one and use it, refresh and you should see a 304.
    // Then comment it out and uncomment the other value and you
    // should see a 200 Response Code.
    // return $res->file($file_path, 'text', 'etag:md5');
    // return $res->file($file_path, 'text', 'etag:sha1');

    // After the first response you should see a 304 each time
    // until you make a change on the Response Content.
    $res->etag('hash:md5');
    return $res->content('Caching Test - Change Me');
});

$app->route('/examples/cors', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Cross-Origin Resource Sharing (CORS)
    // CLASS: Web\Response
    // CORS is commonly used in Web API's to share data from one site or
    // domain with another domain (cross-orign resource). To include the
    // 'Access-Control-Allow-Origin' Header in your response use the [cors()]
    // function. First make sure to set CORS headers from the App Object.
    $app->cors('*');

    // If you're using the Response Object, pass the App Object to either the
    // Response at its creation or to its [cors()] function.
    $res = new \FastSitePHP\Web\Response($app);
    $res->cors($app);

    // When passing a string the 'Access-Control-Allow-Origin' is validated
    // and set, however, if you need to pass additional CORS, use an array
    // with named headers instead.
    $app->cors([
        'Access-Control-Allow-Origin' => 'https://www.example.com',
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age' => 86400,
    ]);

    // If calling a POST, PUT, DELETE or other Request Method you may need
    // to handle OPTIONS requests. When using CORS and an OPTIONS request is
    // processed, FastSitePHP will automatically set the header
    // 'Access-Control-Allow-Methods' based on how routes are defined.
    // To make sure OPTIONS requests are handled first create a function
    // that sets the CORS value.
    $cors = function () use ($app) {
        $app->cors('*');
    };

    // Assign the Filter Function to the routes that use CORS:
    $app->post('/api-data', function() {
        return [ 'example' => 'POST' ];
    })
    ->filter($cors);

    $app->put('/api-data', function() {
        return [ 'example' => 'PUT' ];
    })
    ->filter($cors);

    // If you do not want to allow FastSitePHP to handle OPTIONS
    // requests you can turn it off using this option:
    $app->allow_options_requests = false;
    // EXAMPLE_CODE_END

    // Return a JSON Response with Request Info from the Client
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
    // Keys for Encryption and Signing
    // IMPORTANT - These are publish keys for testing only, do not use them in production!
    // Use the [generateKey()] function to create your own keys.
    $app->config['ENCRYPTION_KEY'] = 'eada343fc415625494bfd1b065ba60c2a5c8508d353dbb872378c1356181c84f05c52ff60d1cc157957cbbf0101f9cb7d74b040b57192a6a820b5402132b9ab4';
    $app->config['SIGNING_KEY'] = 'ab2403a36467b59b20cc314bb211e1812668b3bffb00358c161f26fe003073ed';
    $app->config['JWT_KEY'] = 'fkeVxeElykoCBzRTIUjxwTD9MIg71nXxOEQl6HTrIvw=';

    // EXAMPLE_CODE_START
    // TITLE: Secure Cookies
    // FastSitePHP allows for easy handling of Secure Cookies (Encrypted, Signed,
    // or JWT). To use generate a secure key and save it with app config values.
    // For more on config and crypto settings see other docs on this site.
    // Strong keys are important for security and are required by default.

    // $app->config['ENCRYPTION_KEY'] = 'eada343fc415625494bfd1b065ba...';
    // $app->config['SIGNING_KEY'] = 'ab2403a36467b59b20cc314bb211e18...';
    // $app->config['JWT_KEY'] = 'fkeVxeElykoCBzRTIUjxwTD9MIg71nXxOEQ...';

    // The Request object has three functions that use the config keys to read
    // and verify the secure cookies.If the cookies don't exist, are invalid,
    // are expired etc then [null] will be returned.
    $req = new \FastSitePHP\Web\Request();
    $decrypted = $req->decryptedCookie('encrypted');
    $verified = $req->verifiedCookie('signed');
    $jwt = $req->jwtCookie('jwt');

    // Encrypted and Signed Data can be of any basic type [Strings, Numbers,
    // Objects, etc], while JWT's require an Object or an Array/Dictionary.
    $text = 'Request Time: ' . date(DATE_RFC2822);

    $user = new \stdClass;
    $user->id = 123;
    $user->name = 'Admin';
    $user->role = 'Admin';

    // To send with the Response pass data to the corresponding response method.
    // An optional 3rd parameter exits for an expiration time for both
    // [signedCookie()] and [jwtCookie()] that defaults to 1 hour. This
    // applies to the signed data or JWT and not the cookie itself.
    $res = new \FastSitePHP\Web\Response();
    $res->encryptedCookie('encrypted', $text);
    $res->signedCookie('signed', $user, '+20 minutes');
    $res->jwtCookie('jwt', $user, '+20 minutes');
    // EXAMPLE_CODE_END

    // Refresh at least once to see data, the first time you try the URL
    // null values will be returned.
    // Return Decrypted and Verified Values.
    // Use Dev Tools to see the Cookies.
    return $res->jsonOptions(JSON_PRETTY_PRINT)->json([
        'decrypted' => $decrypted,
        'verified' => $verified,
        'jwt' => $jwt,
    ]);
});

$app->get('/examples/db-query', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Connect to a Database and run SQL Statements
    // CLASS: Data\Database
    // FastSitePHP provides a Database class which is a thin wrapper for PDO to
    // reduce the amount of code needed when querying a database. An additional
    // example on this page shows how to use PDO.

    // Connect to a Database, this example uses SQLite with a temp in-memory db.
    $dsn = 'sqlite::memory:';
    $db = new \FastSitePHP\Data\Database($dsn);

    // Depending on the connection 4 additional parameters can also be used:
    /*
    $user = null;
    $password = null;
    $persistent = false;
    $options = [];
    $db = new Database($dsn, $user, $password, $persistent, $options);
    */

    // Create tables and test records. The function [execute()] is used for
    // action queries (INSERT, UPDATE, DELETE, CREATE, etc) and returns the
    // number of affected rows.

    $db->execute('CREATE TABLE page_types (id INTEGER PRIMARY KEY, page_type)');

    $sql = 'CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT,';
    $sql .= ' type_id, title, content)';
    $db->execute($sql);

    // This example uses a double-quotes for the string ["] because SQL strings
    // include the single-quote character ['] for text.
    $sql = "INSERT INTO page_types (id, page_type) VALUES (1, 'text/plain')";
    $rows_added = $db->execute($sql);

    // An optional 2nd parameter for parameters can be used. This is recommended
    // when for user input to prevent SQL Injection Attacks. The Question Mark [?]
    // is the placeholder character to use in the SQL statement.
    $sql = 'INSERT INTO page_types (id, page_type) VALUES (?, ?)';
    $params = [2, 'text/html'];
    $rows_added += $db->execute($sql, $params);

    // Multiple records can be added (or updated, etc) when using [executeMany()]
    $sql = 'INSERT INTO pages (type_id, title, content) VALUES (?, ?, ?)';
    $records = [
        [1, 'Text Test Page', 'This is a test.'],
        [2, 'HTML Test Page', '<h1>Test<h1><p>This is a test.</p>'],
    ];
    $rows_added += $db->executeMany($sql, $records);

    // In addition to using [?] you can also used named parameters in the
    // format of ":name". Named parameters can make the code easier to read.
    $sql = 'INSERT INTO pages (type_id, title, content)';
    $sql .= ' VALUES (:type_id, :title, :content)';
    $params = [
        'type_id' => 1,
        'title'   => 'Named Parameters',
        'content' => 'Test with Named Parameters.',
    ];
    $rows_added += $db->execute($sql, $params);

    // Get the id of the last inserted row or sequence value
    $last_id = $db->lastInsertId();

    // Query for Multiple Records
    // Returns an Array of Records (Associative Array for each Record).
    $sql = 'SELECT * FROM pages';
    $records = $db->query($sql);

    // Query for one record. Returns an Associative Array or [null] if not found.
    // Both [query()] and [queryOne()] support optional parameters when querying.
    $sql = 'SELECT * FROM pages WHERE id = ?';
    $params = [1];
    $record = $db->queryOne($sql, $params);

    // The [Database] class also contains additional functions such as
    // [queryValue(), queryList() and querySets()] to simplify and reduce
    // the amount code needed when working with databases.
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // TITLE: Connect to a Database and run SQL Statements using PDO
    // Connect to a Database using PHP Data Objects (PDO). This example uses
    // SQLite with a temp in-memory db.
    $dsn = 'sqlite::memory:';
    $user = null;
    $password = null;
    $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ];
    $pdo = new \PDO($dsn, $user, $password, $options);

    // Create tables and test records.

    $pdo->query('CREATE TABLE page_types (id INTEGER PRIMARY KEY, page_type)');

    $sql = 'CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT,';
    $sql .= ' type_id, title, content)';
    $pdo->query($sql);

    // This example uses a double-quotes for the string ["] because SQL strings
    // include the single-quote character ['] for text.
    $sql = "INSERT INTO page_types (id, page_type) VALUES (1, 'text/plain')";
    $stmt = $pdo->query($sql);
    $rows_added = $stmt->rowCount();

    // This example uses a prepare statement with an array of parameters. This is
    // recommended when for user input to prevent SQL Injection Attacks. The
    // Question Mark [?] is the placeholder character to use in the SQL statement.
    $sql = 'INSERT INTO page_types (id, page_type) VALUES (?, ?)';
    $params = [2, 'text/html'];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows_added += $stmt->rowCount();

    // Multiple records can be added (or updated, etc) in a loop
    // using a prepared statement.
    $sql = 'INSERT INTO pages (type_id, title, content) VALUES (?, ?, ?)';
    $records = [
        [1, 'Text Test Page', 'This is a test.'],
        [2, 'HTML Test Page', '<h1>Test<h1><p>This is a test.</p>'],
    ];
    $stmt = $pdo->prepare($sql);

    foreach ($records as $record) {
        $stmt->execute($record);
        $rows_added += $stmt->rowCount();
    }

    // In addition to using [?] you can also use named parameters in the
    // format of ":name". Named parameters can make the code easier to read.
    $sql = 'INSERT INTO pages (type_id, title, content)';
    $sql .= ' VALUES (:type_id, :title, :content)';
    $params = [
        'type_id' => 1,
        'title'   => 'Named Parameters',
        'content' => 'Test with Named Parameters.',
    ];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows_added += $stmt->rowCount();

    // Get the id of the last inserted row or sequence value
    $last_id = $pdo->lastInsertId();

    // Query for Multiple Records
    // Returns an Array of Records (Associative Array for each Record).
    $sql = 'SELECT * FROM pages';
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Query for one record using parameters. Returns an Associative Array
    // or [false] if not found.
    $sql = 'SELECT * FROM pages WHERE id = ?';
    $params = [1];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $record = $stmt->fetch(\PDO::FETCH_ASSOC);

    // Functions [fetchAll()] and [fetch()] also support a number of options
    // for the return format including Indexed-Arrays using [PDO::FETCH_NUM],
    // Anonymous Objects using [PDO::FETCH_OBJ] and custom classes using
    // [PDO::FETCH_CLASS].
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // Create a temp SQLite db for Testing and add a few tables
    $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test.sqlite';
    $db = new \FastSitePHP\Data\Database('sqlite:' . $file_path);
    $db->execute('CREATE TABLE IF NOT EXISTS page_types (id INTEGER PRIMARY KEY AUTOINCREMENT, page_type)');
    $db->execute('CREATE TABLE IF NOT EXISTS pages (id INTEGER PRIMARY KEY AUTOINCREMENT, type_id, title, content)');

    // EXAMPLE_CODE_START
    // TITLE: Connect to a Database
    // CLASS: Data\Database
    // FastSitePHP’s Database class or PHP's built-in PDO class can connect to
    // different databases. FastSitePHP’s Database class provides a thin wrapper
    // over PDO to reduce the amount of code needed when querying a database.

    // Examples below show how to build connection strings and run a query for
    // a number of different databases. If you download this site, the code below
    // can be modified and tested for your environment; or simply copy what you
    // need to your site or app.

    // When specifying the hostname (Server Name), you can often specify just the
    // server name (example: 'db-server') or the fully-qualified domain name (FQDN)
    // (example 'db-server.example.com') based on how your network is setup.
    // For example on an internal network simply using the server name will work
    // but through VPN using the FQDN is often required.

    // ----------------------------------------------------------------------------
    // MySQL
    //   Basic Format:
    //     "mysql:host={hostname};dbname={database}";
    //
    // This example also shows using the [MYSQL_ATTR_INIT_COMMAND]
    // option to set the timezone to UTC when the connection is created.
    //
    // If you have a site or application that has users in multiple timezones or
    // countries an application design that works well is to save all dates and
    // times in UTC and then format based on the users selected timezone.
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
    //   Format:
    //      "oci:dbname=//{hostname}:{port-number}/{database}"
    $dsn = 'oci:dbname=//server:1521/hr';
    $user = 'sys';
    $password = 'password';
    $options = [];
    $sql = 'SELECT OWNER, TABLE_NAME FROM ALL_TABLES ORDER BY OWNER, TABLE_NAME';

    // In addition to the standard format you can also specify a full TNS string
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

    // SQL Server (using ODBC)
    // If the native SQL Server PDO driver is not installed and the
    // PDO ODBC Driver is installed and a ODBC Connection is setup
    // you could use this:
    $dsn = 'odbc:DRIVER={SQL Server};SERVER=db-server;DATABASE=DbName;';

    // ----------------------------------------------------------------------------
    // IBM (using ODBC)
    // This example show a connection to an IBM DB2 or AS/400 through iSeries.
    // ODBC Options will vary based on the driver installed and used.
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
    //   Example using a file path:
    //     'sqlite:/var/www/app_data/db.sqlite'
    //     'sqlite:C:\inetpub\wwwroot\db.sqlite'
    //   In-Memory Database:
    //     'sqlite::memory:'
    $dsn = 'sqlite:' . $file_path;
    $user = null;
    $password = null;
    $options = [];
    $sql = 'SELECT * FROM sqlite_master';

    // ----------------------------------------------------------------------------
    // Persistent Connection Option
    //
    // Many PHP Database drivers support persistent connections which can allow
    // for better performance.
    $persistent = false;

    // ============================================================================
    // Connect using PHP Data Objects (PDO)
    $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
    if ($persistent) {
        $options[\PDO::ATTR_PERSISTENT] = true;
    }
    $pdo = new \PDO($dsn, $user, $password, $options);

    // Query using PDO
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // =================================================================================
    // Connect and Query using FastSitePHP's Database class.
    // Only the DSN (Data Source Name) is a required parameter.
    $db = new \FastSitePHP\Data\Database($dsn, $user, $password, $persistent, $options);
    $records = $db->query($sql);

    // =================================================================================
    // In addition to FastSitePHP's Database class [OdbcDatabase] and [Db2Database]
    // can also be used for supported enviroments, and especially for IBM Databases.
    //
    // When using the class [OdbcDatabase] the DSN will be the same as the PDO DSN
    // excluding the 'odbc:' prefix.
    /*
    $odbc = new OdbcDatabase($dsn, $user, $password, $persistent, $options);
    $db2  = new Db2Database($dsn, $user, $password, $persistent, $options);
    */

    // ============================================================================
    // Lazy Loading with FastSitePHP
    //
    // FastSitePHP’s Application object has a function [lazyLoad()] which accepts
    // a property name and callback function. It creates the object as a property
    // of the app only if used. This is ideal for working with sites where some
    // pages connect to a database and some pages do not, or if you have a site
    // that connects to multiple databases but not all pages use each database.
    $app->lazyLoad('db', function() use ($dsn, $user, $password) {
        return new \FastSitePHP\Data\Database($dsn, $user, $password);
    });

    // Query for records. The database gets connected to here only when first used.
    $records = $app->db->query($sql);

    // ============================================================================
    // To obtain a list of available drivers on the computer call [phpinfo()]
    // and view the result or call the following function to get an array of
    // driver names. A full list of PDO Drivers can be found at:
    //   http://php.net/manual/en/pdo.drivers.php
    // If you need a driver and it is not available or enabled on your server
    // they are generally easy to install and enable.
    $drivers = \PDO::getAvailableDrivers();
    // EXAMPLE_CODE_END

    // Return Text Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($drivers),
            json_encode($records, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/data-validator', function() use ($app) {
    // Manually set Form POST Values for the Demo. In PHP
    // Superglobal variables such as [$_POST]  can be overwritten.
    $_POST = [
        'age' => '10',
        'phone' => 123,
        'site_user' => 'user',
        'site_password' => 'password',
    ];

    // EXAMPLE_CODE_START
    // TITLE: Validating User Input
    // CLASS: Data\Validator
    // For many apps validating client side (webpage or app) provides instant
    // feedback to users and limits need for extra web request, however users
    // can bypass validation by using DevTools or other methods so for data
    // that needs to be validated using server-side validation is important.

    // FastSitePHP provides a class that allows for many rules to be easily
    // defined and run against an object (or Associative Array/Dictionary).

    // Common rules can simply be copied from HTML Input controls.

    // HTML Example:
    /*
        <input name="name" title="Name" required>
        <input name="age" title="Age" required min="13" max="99">
        <input name="phone" title="Phone" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}">
    */

    // FastSitePHP Code to Validate Form Post using the above HTML.
    // Form Post Fields come in the PHP Superglobal array [$_POST]
    // and it can simply be passed to the [Validator] class.
    $v = new \FastSitePHP\Data\Validator();
    $v->addRules([
        // Field,  Title,   Rules
        ['name',  'Name',  'required'],
        ['age',   'Age',   'required min="13" max="99"'],
        ['phone', 'Phone', 'pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"'],
    ]);
    list($errors, $fields) = $v->validate($_POST);
    if ($errors) {
        // Error Logic
        // [$errors] returns an array of error messages for the end user
        // [$fields] returns an array of unique fields that had an error
        // along with an array of error messages for each field.
        // Fields can be used by a client app to highlight form fields, etc.
    }

    // In addition to using strings for the rules you can also use arrays.
    // This can provide better performance if you have a high traffic site,
    // however it runs very fast either way.
    $v = new \FastSitePHP\Data\Validator();
    $v->addRules([
        ['name',  'Name',  ['required' => true]],
        ['age',   'Age',   [
            'required' => true,
            'min' => '13',
            'max' => '99',
        ]],
        ['phone', 'Phone', ['pattern' => '[0-9]{3}-[0-9]{3}-[0-9]{4}']],
    ]);

    // The validator class supports a number of HTML5 rules along
    // with some custom rules:
    //     'exists', 'required', 'type', 'minlength', 'maxlength',
    //     'length', 'min', 'max', 'pattern', 'list',

    // The [type] rule supports a number of HTML5 data types along
    // with many custom data types:
    //      'text', 'password', 'tel', 'number', 'range', 'date',
    //      'time', 'datetime', 'datetime-local', 'email', 'url',
    //      'unicode-email', 'int', 'float', 'json', 'base64',
    //      'base64url', 'xml', 'bool', 'timezone', 'ip', 'ipv4',
    //      'ipv6', 'cidr', 'cidr-ipv4', 'cidr-ipv6',

    // In addition to standard rules custom rules can be defined using
    // callback functions that return true/false or a custom error
    // message string:
    $v
        ->addRules([
            ['site_user',     'Site User', 'check-user required'],
            ['site_password', 'Password',  'check-password required'],
        ])
        ->customRule('check-user', function($value) {
            return ($value === 'admin');
        })
        ->customRule('check-password', function($value) {
            return ($value === 'secret' ? true : 'Invalid Password');
        });

    list($errors, $fields) = $v->validate($_POST);
    // EXAMPLE_CODE_END

    // Return Text Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($errors, JSON_PRETTY_PRINT),
            json_encode($fields, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/http-client', function() use ($app) {
    // Initial Demo that runs unless the code is modified.
    // Download and return an HTML page.
    // Comment this out to run full code.
    return \FastSitePHP\Net\HttpClient::get('https://www.example.com/')->content;

    // To use, modify these to valid values from your system
    // Saving a file requires write access in the current directory.
    $save_path = __DIR__ . '/test-download.txt';
    $url = 'https://httpbin.org/anything';
    $file_path = 'C:\Users\Public\Pictures\Thumbnails\Desert.jpg';
    if (!is_file($file_path)) {
        return 'Modify the code to point to a real file';
    }

    // EXAMPLE_CODE_START
    // TITLE: Using the HTTP Client
    // CLASS: Net\HttpClient, Net\HttpResponse
    // The HttpClient can be used to simplify communication with other Web Services,
    // HTTP API’s, and works great for calling and returning the result of local
    // services – for example an AI/ML (Artificial Intelligence / Machine Learning)
    // Service written in Python with TensorFlow or scikit-learn.

    // Perform a simple HTTP GET Request and check the result
    $res = \FastSitePHP\Net\HttpClient::get($url);
    if ($res->error) {
        // An error would be returned in the event of a major failure such as
        // a timeout or SSL Cert Error. A 404 or 500 Response from the server
        // would be handled by checking the [status_code].
        $error = $res->error;
    } else {
        $status_code = $res->status_code; // 200, 404, 500, etc
        $headers = $res->headers; // Array of Response Headers
        $content = $res->content; // Response Content as a String - HTML, Text, etc
        $info = $res->info; // Array of Info such as Time Stats
    }

    // Perform an HTTP GET Request and read the JSON Result. If the Response
    // Content-Type is 'application/json' then [$res->json] will contain an array
    // otherwise null. Request Headers can be passed an optional paramater.
    $headers = [
        'X-API-Key' => 'ab82050cf5907934fa1d0f6f66284642a01d1ba2280656870c',
        'X-Custom-Header' => 'Test',
    ];
    $res_json = \FastSitePHP\Net\HttpClient::get($url, $headers);
    $json = $res->json;
    $text = $res->content;

    // Submit a HTTP POST Request as JSON and also as a Form.
    // Data can be either an Array or Object and Headers are optional.
    $data = [
        'text' => 'test',
        'num' => 123,
    ];
    $res_post = \FastSitePHP\Net\HttpClient::postJson($url, $data, $headers);
    $res_form = \FastSitePHP\Net\HttpClient::postForm($url, $data);

    // When using PHP 5.5 or later 'multipart/form-data' Form Posts are supported
    // with the PHP built-in class [CURLFile]:
    /*
    $data = [
        'field1' => 'test',
        'file' => new \CURLFile($file_path),
    ];
    */

    // Save the Response Content as a File Download
    // Just like [postJson()] and [postForm()] Request Headers are optional.
    $res_file = \FastSitePHP\Net\HttpClient::downloadFile($url, $save_path, $headers);
    $saved_path = $res_file->content;

    // The above code demo shows the 4 helper static functions [get(), postJson(),
    // postForm(), and downloadFile()], additional options are available when using
    // the HttpClient as an object with the [request()] method.

    // Submit a PUT Request with a file as the Request Body
    $http = new \FastSitePHP\Net\HttpClient();
    $res_put = $http->request($url, [
        'method' => 'PUT',
        'headers' => $headers,
        'send_file' => $file_path,
    ]);
    // EXAMPLE_CODE_END

    // Return Text Response
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

// ** NOTE - using the below example requires a localhost GraphQL Service
//      Then you will need to add a query string or use standard GraphQL POST, examples:
//          ?query={countries{iso,country}}
//          ?query=query($country:String!){regions(country:$country){name}}&variables={"country":"US"}
//
// EXAMPLE_CODE_START
// TITLE: GraphQL Service using HttpClient
// FIND_REPLACE: {"/examples":""}
// GraphQL is a popular technology for developing API's. It has been ported to
// many languages including PHP, however the reference implementation, the most
// commonly used version, and also high in performance is GraphQL with NodeJS
// and Express. This route can be copied or modified to allow GraphQL from PHP
// using any GraphQL service on localhost or from another URL.
$app->route('/examples/graphql', function() {
    try {
        $url = 'http://localhost:4000/graphql';

        // If an 'Authorization' Request Header was
        // sent then pass it to the GraphQL Service.
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

        // Check Response, an error typically would occur not for data
        // errors but rather HTTP errors (i.e.: If the service is down).
        if ($res->error) {
            throw new \Exception($res->error);
        }

        // Return Object for JSON Response
        return $res->json;
    } catch (\Exception $e) {
        // Return unexpected error as a 200 response
        // using standard error format used by GraphQL.
        return [
            'errors' => [
                ['message' => $e->getMessage()]
            ],
        ];
    }
})->filter(function() use ($app) {
    // Use CORS to allow web pages to access this service from any host (URL)
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
    // NOTE - to run this modify code with an SMTP Server that
    // you have access to and uncomment the [return] line of
    // code below. If you have a gmail account you can use it
    // to test this function. It will likely fail at first and
    // provide a message of settigs that you need to set in order
    // to allow gmail to send through SMTP.
    //
    // If you do not have access to an email server and want to
    // try this code then comment out the code before the line
    // [$timeout = 2;] and run just Gmail SMTP Commands without
    // sending an email.
    //
    return 'Modify the code to run';

    // Output Plain Text. With this example code when logging is used,
    // messages are sent as soon as they occur.
    header('Content-Type: text/plain');

    // EXAMPLE_CODE_START
    // TITLE: Send an Email through an SMTP Server
    // CLASS: Net\SmtpClient, Net\Email
    // Define Email Settings
    $from = 'noreply@example.com';
    $to = 'user.name@example.com';
    $subject = 'Email Test from FastSitePHP at ' . date(DATE_RFC2822);
    $body = '<h1>Email Title</h1><p style="color:blue;">This is a test.</p>';

    // Create an Email Object
    $email = new \FastSitePHP\Net\Email($from, $to, $subject, $body);

    // The Email Class also has many additional settings and can be created
    // without specifying any parameters. When setting [From] or [Reply-To]
    // email addresses one of the following formats can be used:
    //   String: 'Email Address'
    //   Array: ['Email', 'Name']
    // And when specifying who to send email to any of the formats can be used:
    //   String 'Email Address'
    //   Array: ['Email', 'Name']
    //   Array: ['Email Address 1', 'Email Address 2', '...']
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

    // File attachements are also supported:
    //
    // $email->attachFile($file_path);

    // SMTP Servers that support Unicode Emails can use [allowUnicodeEmails(true)].
    // When used the SMTP Client sends a SMTPUTF8 option if the server supports it.
    //
    // $email->allowUnicodeEmails(true)->from('无回复@example.com');

    // SMTP Settings
    $host = 'smtp.example.com';
    $port = 25;
    $auth_user = null;
    $auth_pass = null;

    // Create SMTP Client and Send Email.
    // Once the variable for the SMTP Client is no longer used or set to null
    // then it automatically sends a 'QUIT' command to the SMTP Server and closes
    // the connection.
    $smtp = new \FastSitePHP\Net\SmtpClient($host, $port);
    if ($auth_user !== null) {
        $smtp->auth($auth_user, $auth_pass);
    }
    $smtp->send($email);
    $smtp = null;

    // Additional options can be specified for timeout (in seconds) and for logging
    $timeout = 2;
    $debug_callback = function($message) {
        echo '[' . date('H:i:s') . '] ' . trim($message) . "\n";
    };

    // The [SmtpClient] Class also supports an easy to use API for communicating
    // with SMTP Servers. In this example Gmail is used and several commands are
    // performed. Messages are logged to the [$debug_callback] function.
    $host = 'smtp.gmail.com';
    $port = 587;
    $smtp2 = new \FastSitePHP\Net\SmtpClient($host, $port, $timeout, $debug_callback);
    $smtp2->help();
    $smtp2->noop();
    $smtp2->quit();
    $smtp2->close();

    // One or more emails can also be sent using App Config Values or System
    // Enviroment Variables. This type of setup can be used to prevent sensitive
    // authentication info from being saved with the main code logic.
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
    $dir_path = __DIR__ . '/../../../src';

    // EXAMPLE_CODE_START
    // TITLE: Search for Files and Directories (Folders)
    // CLASS: FileSystem\Search
    // Create a FileSystem Search Object
    $search = new \FastSitePHP\FileSystem\Search();

    // For basic usage specify a root directory with the [dir()] command and then
    // call either [files()] or [dirs()]. An array of matching names will be returned.
    $files = $search->dir($dir_path)->files();

    // [all()] can be used to return both directories and files
    list($dirs, $files) = $search->dir($dir_path)->all();

    // Functions are chainable so breaking them up
    // one per line can make the code easier to read.
    $dirs = $search
        ->dir($dir_path)
        ->dirs();

    // URL lists can also be generated from matching files.
    $url_root = 'http://www.example.com/';
    $urls = $search
        ->dir($dir_path)
        ->urlFiles($url_root);

    // A number of different criteria functions exist and can be used to filter
    // the results. In this example a recursive search is used to find PHP files
    // that contain the text 'FileSystem'. When a recursive search is used the
    // full file paths are returned unless [includeRoot(false)] is set.
    // See documentation and examples for all functions.
    $files = $search
        ->dir($dir_path)
        ->recursive(true)
        ->fileTypes(['php'])
        ->includeText(['FileSystem'])
        ->files();
    // EXAMPLE_CODE_END

    // Return Text Response
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

$app->get('/examples/file-system-sync', function() use ($app) {
    // Modify here if testing locally
    $dir_from = __DIR__ . '/../../../../Test/src1';
    $dir_to = __DIR__ . '/../../../../Test/src2';

    // EXAMPLE_CODE_START
    // TITLE: File System Sync
    // CLASS: FileSystem\Sync
    // Create a FileSystem Sync Object
    $sync = new FastSitePHP\FileSystem\Sync();

    // Sync files and directories (folders) from [dirFrom(path)] to [dirTo(path)].
    // The sync is recursive so all files and directories are synced in all
    // sub-directories. Required functions are [dirFrom, dirTo, and sync].
    // To view the results call [printResults()] after calling [sync()].
    // All options with defaults are shown below.
    $sync
        ->dirFrom($dir_from)
        ->dirTo($dir_to)
        ->excludeNames(['package-lock.json'])
        ->excludeRegExPaths(['/node_modules/'])
        ->summaryTitle('File System Sync Results')
        ->hashAlgo('sha256')
        ->dryRun(false) // Set to [true] for testing
        ->sync()
        ->printResults();
    // EXAMPLE_CODE_END
});

$app->get('/examples/markdown', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Convert Markdown to HTML using PHP
    // FastSitePHP includes the high performance library Parsedown for
    // converting Markdown format to HTML.

    // Make sure to load the vendor autoloader
    require '../../../vendor/autoload.php';

    // Create Parsedown Object
    $Parsedown = new Parsedown();

    // Convert to HTML from a Text String
    $html = $Parsedown->text('Hello **FastSitePHP**!');

    // Read a File and convert to HTML
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
    // FastSitePHP includes two logging classes that implement the widely used
    // [Psr\Log] Interface.

    // Create a file logger. Log messages are appended and the file is created
    // when the first message is added.
    $file = __DIR__ . '/log.txt';
    $file_logger = new \FastSitePHP\Data\Log\FileLogger($file);

    // Create an HTML Logger
    // This class can be used for temporary development logs because it outputs an
    // HTML table of logged messages after the response is sent or depending on
    // options can be used to replace the original response. The parameter
    // [$replace_response] is optional.
    $replace_response = false;
    $html_logger = new \FastSitePHP\Data\Log\HtmlLogger($app, $replace_response);

    // Log messages using one of the following functions:
    //     emergency(), alert(), critical(), error(),
    //     warning(), notice(), info(), debug()
    $file_logger->info('This is a Test.');
    $html_logger->error('Application Test');

    // Additionally data can be passed to the message with placeholders
    $html_logger->info('User {name} created', [
        'name' => 'Admin'
    ]);

    // The date format can be any valid value for the PHP function [date()].
    // Default is [\DateTime::ISO8601].
    $file_logger->date_format = 'Y-m-d H:i:s';

    // For the file logger the output format can be controlled by properties.
    //
    // Default Format:
    //     '{date} {level} - {message}{line_break}';
    //
    // Line Breaks default based on the OS:
    //     "\r\n" - Windows
    //     "\n"   - Other OS's
    $file_logger->log_format = '[{level}] {message}{line_break}';
    $file_logger->line_break = '^^';

    // You can also customize the HTML Logger with your own template:
    // $html_logger->template_file = 'YOUR_TEMPLATE.php';
    // EXAMPLE_CODE_END

    $html = '<html><body style="background-color:green; padding:0;"><div style="padding:20px;">';
    $html .= 'Class = ' . get_class($html_logger);
    $html .= '<br>Psr\Log\LoggerInterface = ' . json_encode($html_logger instanceof Psr\Log\LoggerInterface);
    $html .= '</body></html>';
    return $html;
});

$app->get('/examples/network-info', function() {
    // EXAMPLE_CODE_START
    // TITLE: Get Network and Server Info
    // CLASS: Net\Config
    // Create a Networking Config Object
    $config = new \FastSitePHP\Net\Config();

    // Get a (fqdn) 'fully-qualified domain name' for the server ['server.example.com']
    $host = $config->fqdn();

    // Get the Network IPv4 Address for the computer or server
    $ip = $config->networkIp();

    // Get a list of all IPv4 Addresses for the computer or server
    $ip_list = $config->networkIpList();

    // Get a text string of info from the server using one of the following commands:
    // - Linux / Unix = [ip addr] or [ifconfig]
    // - Mac          = [ifconfig]
    // - Windows      = [ipconfig]
    $info = $config->networkInfo();

    // Convert the Network Info String to an Object
    $info = $config->parseNetworkInfo($info);
    // EXAMPLE_CODE_END

    // Format and return as a text response
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
    // TITLE: Get Environment and System Info
    // CLASS: Environment\System
    // Create an Environment System Object
    $sys = new \FastSitePHP\Environment\System();

    // Get an array of basic information related to the Operating System
    // [ 'OS Type', 'Version Info', 'Release Version', 'Host Name', 'CPU Type' ]
    $os_info = $sys->osVersionInfo();

    // Get a text string of detailed system info using one of the following commands:
    // - Linux   = File: '/etc/os-release'
    // - FreeBSD = uname -mrs
    // - IBM AIX = uname -a
    // - Mac     = system_profiler SPSoftwareDataType SPHardwareDataType
    // - Windows = ver
    $info = $sys->systemInfo();

    // Get an array of information related to free, used, and total space for
    // a filesystem drive or disk partition. This function allows for specific
    // drives or partitions to be specified.
    // - *nix    = $sys->diskSpace('/dev/disk0')
    // - Windows = $sys->diskSpace('C:')
    $disk_space = $sys->diskSpace();

    // Windows only function that returns an array of drive letters
    // mapped to the server. Returns an empty array for other OS's.
    $mapped_drives = $sys->mappedDrives();
    // EXAMPLE_CODE_END

    // Format and return as a text response
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
    // In order to run this without error add a [.env]
    // file to this directory or modify the code below.
    // Using default code an error will be thrown until
    // required keys are also added.
    $dir = __DIR__;
    $file_path = __DIR__ . '/.env';

    // EXAMPLE_CODE_START
    // TITLE: Use a [.env] File
    // CLASS: Environment\DotEnv
    // Loads environment variables from a [.env] file into [getenv()] and [$_ENV].
    // FastSitePHP's DotEnv is a port of the Node package [dotenv] so the same
    // syntax used by node projects is supported.
    $vars = \FastSitePHP\Environment\DotEnv::load($dir);

    // Use variables from the file after reading it. Variables are only set
    // from the file if they do not already exist.
    $value = getenv('DB_CONNECTION');
    $value = $_ENV['DB_CONNECTION'];

    // Load a file using [.env] file format. The full path of the file is
    // specified so it can be named anything.
    $vars = \FastSitePHP\Environment\DotEnv::loadFile($file_path);

    // Optionally require keys to exist in the file.
    $required_vars = ['DB_ORACLE', 'DB_SQL_SERVER'];
    $vars = \FastSitePHP\Environment\DotEnv::load($dir, $required_vars);
    // EXAMPLE_CODE_END

    // Format and return as a text response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(json_encode($vars, JSON_PRETTY_PRINT));
});

$app->get('/examples/encryption', function() use ($app) {
    $data = ['User'=>'Admin', 'Password'=>'123'];

    // EXAMPLE_CODE_START
    // TITLE: Security - Encrypt and Decrypt Data
    // CLASS: Security\Crypto\Encryption, Security\Crypto
    // Generate a Key for Encryption.
    // The key is a long hex string of secure random bytes.
    // The key would typically be saved with your app or in config.
    $crypto = new \FastSitePHP\Security\Crypto\Encryption();
    $key = $crypto->generateKey();

    // Encrypt and Decrypt using the Crypto Helper Class with Config Settings.
    // Data of different data types can be encrypted and returned in the
    // same format (string, int, object, etc).
    $app->config['ENCRYPTION_KEY'] = $key;
    $encrypted_text = \FastSitePHP\Security\Crypto::encrypt($data);
    $decrypted_data = \FastSitePHP\Security\Crypto::decrypt($encrypted_text);

    // Encrypt and Decrypt using the Encryption Class. This class
    // provides many additional options that are not in the helper class.
    $encrypted_text = $crypto->encrypt($data, $key);
    $decrypted_data = $crypto->decrypt($encrypted_text, $key);

    // EXAMPLE_CODE_END

    // Return Text Response
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
    // Build a Random File
    $rand = \bin2hex(\FastSitePHP\Security\Crypto\Random::bytes(6));
    $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'crypto_test_' . $rand;
    file_put_contents($file_path, 'This is a Test');

    // EXAMPLE_CODE_START
    // TITLE: Security - Encrypt and Decrypt a File
    // CLASS: Security\Crypto\FileEncryption, Security\Crypto
    // FastSitePHP allows for fast authenticated encryption of any size file
    // (even large files that are many gigs in size). The code used for encryption
    // is compatible with shell commands and a Bash Script [encrypt.sh] that works
    // on Linux and Unix Computers. The Bash Script can be downloaded from this site,
    // and will work on most Linux OS's without having to install anything.

    // Generate a Key for Encryption
    $crypto = new \FastSitePHP\Security\Crypto\FileEncryption();
    $key = $crypto->generateKey();

    // Build file paths of files to save based on the original name
    $enc_file = $file_path . '.enc';
    $output_file = $enc_file . '.decrypted';

    // Encrypt and Decrypt using the Crypto Helper Class with Config Settings.
    // A [FileEncryption] class also exists with additional options.
    $app->config['ENCRYPTION_KEY'] = $key;
    \FastSitePHP\Security\Crypto::encryptFile($file_path, $enc_file);
    \FastSitePHP\Security\Crypto::decryptFile($enc_file, $output_file);
    // EXAMPLE_CODE_END

    // Read Files for the Response
    $contents_start = file_get_contents($file_path);
    $contents_enc = bin2hex(file_get_contents($enc_file));
    $contents_dec = file_get_contents($output_file);

    // Delete created files
    // To see files comment out this code, then view most recent files in temp folder.
	$files = array($file_path, $enc_file, $output_file);
	foreach ($files as $file) {
		if (is_file($file)) {
			unlink($file);
		}
	}

    // Return Text Response
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
    // TITLE: Security - Encode and Decode a JSON Web Token (JWT)
    // CLASS: Security\Crypto\JWT, Security\Crypto
    // The JWT Payload can be either an Object or an Array (Dictionary).
    $payload = [
        'User' => 'John Doe',
        'Roles' => ['Admin', 'SQL Editor']
    ];

    // Generate a Key for Encoding (Signing).
    // The key is a long hex string of secure random bytes.
    // The key would typically be saved with your app or in config.
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $key = $jwt->generateKey();

    // Encode and Decode JWT with the Crypto Helper Class with Config Settings.
    // When using the default parameters with the helper class the data has a
    // 1-hour timeout.
    $app->config['JWT_KEY'] = $key;
    $token = \FastSitePHP\Security\Crypto::encodeJWT($payload);
    $data  = \FastSitePHP\Security\Crypto::decodeJWT($token);

    // Encode (Sign) and Decode (Verify) using the JWT Class. When using
    // default settings with the JWT Class, no timeout is specified, all
    // claims are validated, and a secure key is required.
    $token = $jwt->encode($payload, $key);
    $data  = $jwt->decode($token, $key);

    // Add Claims to the Payload and use an Insecure Key for Compatibility
    // with other sites (Often online demos of JWT are shown using simple
    // passwords for the key). By default keys are required to be secure
    // with proper length and in either Base64 or Hex format.

    $payload = $jwt->addClaim($payload, 'exp', '+10 minutes');
    $payload = $jwt->addClaim($payload, 'iss', 'example.com');

    $jwt
        ->useInsecureKey(true)
        ->allowedIssuers(['example.com']);

    $insecure_key = 'password123';
    $token = $jwt->encode($payload, $insecure_key);
    $data  = $jwt->decode($token, $insecure_key);
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // TITLE: Security - Encode and Decode JWT using RSA
    // CLASS: Security\Crypto\JWT
    // The JWT Payload can be either an Object or an Array (Dictionary).
    $payload = new \stdClass;
    $payload->User = 'John Doe';
    $payload->Roles = ['Admin', 'SQL Editor'];

    // Create JWT Class, specify 'RS256' Algoritm, and generate Key Pair
    $jwt = new \FastSitePHP\Security\Crypto\JWT();
    $jwt
        ->algo('RS256')
        ->allowedAlgos(['RS256']);

    list($private_key, $public_key) = $jwt->generateKey();

    // Encode (Sign) and Decode (Verify)
    $token = $jwt->encode($payload, $private_key);
    $data  = $jwt->decode($token, $public_key);
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // TITLE: Security - Sign and Verify Data
    // CLASS: Security\Crypto\SignedData, Security\Crypto
    // Using the [SignedData] is similar in concept to using JWT.
    // A client can read the data but not modify it.

    // Generate a Key for Signing.
    // The key is a long hex string of secure random bytes.
    // The key would typically be saved with your app or in config.
    $csd = new \FastSitePHP\Security\Crypto\SignedData();
    $key = $csd->generateKey();

    // Sign and Verify using the Crypto Helper Class with Config Settings.
    // When using the default parameters with the helper class the data has
    // a 1-hour timeout. Data of different data types can be signed and
    // verified to the original format (string, int, object, etc).
    $app->config['SIGNING_KEY'] = $key;
    $signed_text   = \FastSitePHP\Security\Crypto::sign($data);
    $verified_data = \FastSitePHP\Security\Crypto::verify($signed_text);

    // Sign and Verify using the SignedData Class. The SignedData Class
    // allows for additional options and doesn't use config settings.
    // The parameter [$expire_time] is optional.
    $expire_time   = '+20 minutes';
    $signed_text   = $csd->sign($data, $key, $expire_time);
    $verified_data = $csd->verify($signed_text, $key);
    // EXAMPLE_CODE_END

    // Format and return as a text response
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
    // TITLE: Security - Hash and Verify Passwords
    // CLASS: Security\Password
    // Saving User Passwords using a one-way hashing function is important for
    // secure applications. FastSitePHP’s Password class provides support for
    // bcrypt (default) and Argon2.

    // Example of a User Password, this value should not be saved to a database
    $password = 'Password123';

    // Create a Password Object
    $pw = new \FastSitePHP\Security\Password();

    // Hash the Password, this will create hash text that looks like this:
    //   '$2y$10$cDpu8TnONBhpBFPEKTTccu/mYhSppqNLDNCfOYLfBWI3K/FzFgC2y'
    // The value will change everytime and is safe to save to a database.
    $hash = $pw->hash($password);

    // Verify a Password - returns [true] or [false]
    $verified = $pw->verify($password, $hash);

    // Create a randomly generated password that is 12 characters in length
    // and contains the following:
    //   4 Uppercase Letters (A - Z)
    //   4 Lowercase Letters (a - z)
    //   2 Digits (0 - 9)
    //   2 Special Characters (~, !, @, #, $, %, ^, &, *, ?, -, _)
    $strong_password = $pw->generate();

    // Specify a different BCrypt Cost of 12 instead of the default value 10
    $pw->cost(12);
    $hash2 = $pw->hash($password);
    $verified2 = $pw->verify($password, $hash2);

    // When using PHP 7.2 or later Argon2 can be used
    if (PHP_VERSION_ID >= 70200) {
        $pw->algo('Argon2');
        $argon_hash = $pw->hash($password);
        $argon_verified = $pw->verify($password, $argon_hash);
    }
    // EXAMPLE_CODE_END

    // Format and return as a text response
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
    // TITLE: Security - Generate a new RSA Key Pair
    // CLASS: Security\Crypto\PublicKey
    // Generate a new RSA Key Pair
    $key_pair = \FastSitePHP\Security\Crypto\PublicKey::generateRsaKeyPair();
    list($private_key, $public_key) = $key_pair;

    // Generate a new 3072-Bit RSA Key
    $bits = 3072;
    $key_pair = \FastSitePHP\Security\Crypto\PublicKey::generateRsaKeyPair($bits);
    list($private_key2, $public_key2) = $key_pair;
    // EXAMPLE_CODE_END

    // Format and return as a text response
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
    // TITLE: Generate a string of random bytes
    // CLASS: Security\Crypto\Random
    // Generate cryptographically secure pseudo-random bytes that
    // are suitable for cryptographic use and secure applications.
    $bytes = \FastSitePHP\Security\Crypto\Random::bytes(32);

    // Convert the bytes to another format:
    $hex_bytes = bin2hex($bytes);
    $base64_bytes = base64_encode($bytes);

    // When using PHP 7 or newer you can simply call [random_bytes()]
    $bytes = random_bytes(32);
    // EXAMPLE_CODE_END

    return $hex_bytes . '<br>' . $base64_bytes;
});

$app->get('/examples/csrf-session', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Security - CSRF using Session
    // CLASS: Security\Web\CsrfSession
    // One call to a static function creates a token on GET Requests
    // and validates it with Requests POST, PUT, DELETE, etc.
    // If there is an error with the token then an exception is
    // thrown which will cause 500 response with the error page.
    \FastSitePHP\Security\Web\CsrfSession::setup($app);

    // The token is assigned a locals value in the Application Object
    $token = $app->locals['csrf_token'];

    // This allows it to be used with templating code.
    // Tokens are validated from [setup()] but not automatically added
    // to forms so they must be added through templating or by code.
    //
    // <meta name="X-CSRF-Token" content="{{ $csrf_token }}">
    // <input name="X-CSRF-Token" value="{{ $csrf_token }}">

    // A good place to call this function is on route filters
    // of pages that use authentication. Example:

    // Create a filter function to assign to multiple routes
    $csrf_session = function() use ($app) {
        \FastSitePHP\Security\Web\CsrfSession::setup($app);
    };

    // Use the function when defining a route
    $app->get('/form', function() use ($app) {
        return $app->render('form.php');
    })
    ->filter($csrf_session);
    // EXAMPLE_CODE_END

    // Format and return as a text response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            $token,
        ]));
});

$app->get('/examples/csrf-stateless', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Security - Stateless CSRF
    // CLASS: Security\Web\CsrfStateless
    // Stateless CSRF Tokens are not stored in Session but rather use a crypto
    // keyed-hash message authentication code (HMAC) to create and verify the token.

    // A secure secret key is required.
    // The key would typically be saved with your app or in config.
    $key = \FastSitePHP\Security\Web\CsrfStateless::generateKey();

    // To use the Key it must be saved to either a config value or
    // an environment variable before calling [setup()].
    $app->config['CSRF_KEY'] = $key;
    // putenv("CSRF_KEY=${key}");

    // A unique identifier for the user is also required. This doesn't have
    // to be secret and can be a simple as an numeric field in a database.
    $user_id = 1;

    // Setup and validate stateless CSRF Tokens
    \FastSitePHP\Security\Web\CsrfStateless::setup($app, $user_id);

    // Optionally add a timeout, this CSRF token will expire after 5 minutes
    $expire_time = '+5 minutes';
    \FastSitePHP\Security\Web\CsrfStateless::setup($app, $user_id, $expire_time);

    // The same logic is used when using the [CsrfSession] class so
    // the token is assigned a locals value in the Application Object
    // which allows for it to be used with templating code.
    $token = $app->locals['csrf_token'];
    //
    // <meta name="X-CSRF-Token" content="{{ $csrf_token }}">
    // <input name="X-CSRF-Token" value="{{ $csrf_token }}">

    // Also just like [CsrfSession] a good place to call [setup()]
    // is on route filter functions.
    $csrf = function() use ($app, $user_id) {
        \FastSitePHP\Security\Web\CsrfStateless::setup($app, $user_id);
    };

    $app->get('/form', function() use ($app) {
        return $app->render('form.php');
    })
    ->filter($csrf);
    // EXAMPLE_CODE_END

    // Format and return as a text response
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
    // TITLE: IP Addresses and Validation
    // CLASS: Net\IP
    // With FastSitePHP you can easily compare an IP Address to an accepted range
    // of IP’s using CIDR Notation. CIDR Notation (Classless Inter-Domain Routing)
    // is a compact representation of an IP address and its associated routing
    // prefix. It is used regularly when working with digital networks and often
    // needed for websites when handling IP Addresses for security.

    // Check if IP Address '10.10.120.12' is in the '10.0.0.0/8' range
    // Returns [true]
    $matches = \FastSitePHP\Net\IP::cidr('10.0.0.0/8', '10.10.120.12');

    // Check if IP Address '10.10.120.12' is in the '172.16.0.0/12' range
    // Returns [false]
    $matches2 = \FastSitePHP\Net\IP::cidr('172.16.0.0/12', '10.10.120.12');

    // IPv6 is also supported
    $matches3 = \FastSitePHP\Net\IP::cidr('fe80::/10', 'fe80::b091:1117:497a:9dc1');

    // Get an array of Private Network Addresses in CIDR Notation
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

    // The array from [privateNetworkAddresses()] can be used with the [cidr()]
    // function to check if an IP address is from a private network or from the
    // public internet. The [cidr()] function accepts the CIDR Parameter as
    // either an array or a string.
    $matches4 = \FastSitePHP\Net\IP::cidr($private_addr, '10.10.120.12');

    // Get Info about a CIDR string when calling [cidr()] with only 1 parameter.
    // This example returns the following:
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

    // Example of CIDR Info when using IPv6:
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

    // Return Text Response
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
    // TITLE: File System Security
    // CLASS: FileSystem\Security
    // The FileSystem Security Class contains functions for validating files.

    // Prevent Path Traversal Attacks by verifying if a file name exists in a
    // specified directory. Path Traversal Attacks can happen if a user is
    // allowed to specify a file on a file system through input and uses a
    // pattern such as '/../' to obtain files from another directory.

    // Examples:

    // Assume both files exist and would return [true] from built-in function
    // [is_file()]. [false] would be returned for the 2nd file when using
    // [Security::dirContainsFile()].
    $file1 = 'user_image.jpg';
    $file2 = '../../index.php';
    $file_exists_1 = \FastSitePHP\FileSystem\Security::dirContainsFile($dir, $file1);
    $file_exists_2 = \FastSitePHP\FileSystem\Security::dirContainsFile($dir, $file2);

    // The function [dirContainsFile()] only allows for files directly under the root
    // folder so another function exists to search sub-directories [dirContainsPath()].
    $path1 = 'icons/clipboard.svg'; // Returns [true]
    $path2 = '../../app/index.php'; // Returns [false]
    $path_exists_1 = \FastSitePHP\FileSystem\Security::dirContainsPath($dir, $path1);
    $path_exists_2 = \FastSitePHP\FileSystem\Security::dirContainsPath($dir, $path2);

    // [dirContainsPath()] contains an optional 3rd parameter [$type] which defaults
    // to 'file' and allows for one of the following options ['file', 'dir', 'all'].
    $path3 = 'icons';
    $exists = \FastSitePHP\FileSystem\Security::dirContainsPath($dir, $path3, 'dir');

    // [dirContainsDir()] can be used to check directories/folders.
    $dir1 = 'icons';
    $dir2 = '../../app';
    $dir_exists_1 = \FastSitePHP\FileSystem\Security::dirContainsDir($dir, $file1);
    $dir_exists_2 = \FastSitePHP\FileSystem\Security::dirContainsDir($dir, $file2);

    // Validate Image Files
    // The [fileIsValidImage()] function can be used to verify if image files
    // created from user input are valid. For example a malicious user may try
    // to rename a PHP script or executable file as an image and upload it to
    // a site. Returns [true] if an image file [jpg, gif, png, webp, svg]
    // is valid and the file's extension matches the image type.
    $is_image = \FastSitePHP\FileSystem\Security::fileIsValidImage($image_file);
    // EXAMPLE_CODE_END

    // NOTE - most values equal [false] because the files/dirs won't exist.
    // Code here is for example only, modify if you want to test.
    return [$file_exists_1, $file_exists_2, $path_exists_1, $path_exists_2, $exists, $dir_exists_1, $dir_exists_2, $is_image];
});

$app->get('/examples/rate-limiting', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Security - Rate Limiting
    // CLASS: Security\Web\RateLimit
    // Rate Limit Class
    $rate_limit = new \FastSitePHP\Security\Web\RateLimit();

    // Using the RateLimit class requires an instance of
    // [\FastSitePHP\Data\KeyValue\StorageInterface].
    // In this example SQLite is used. When multiple servers are used behind
    // a load balancer an in-memory cache db such as Redis can be used.
    $file_path = sys_get_temp_dir() . '/ratelimit-cache.sqlite';
    $storage = new \FastSitePHP\Data\KeyValue\SqliteStorage($file_path);

    // There are 2 required options [storage] and [id].
    // [id] represents the user - IP Address, User ID, etc.
    //
    // [max_allowed] and [duration] will commonly be used and represent
    // the rate at which the event is allowed. If not specified then a
    // default of 1 is used which allows for 1 request per second.
    $options = [
        'max_allowed' => 1, // Requests, Events, etc
        'duration' => 1, // In seconds
        'storage' => $storage,
        'id' => $_SERVER['REMOTE_ADDR'],
    ];

    // Check the Request
    list($allowed, $headers) = $rate_limit->allow($options);
    // $allowed = bool
    // $headers = Array of headers that can be used for logic
    //            or sent with the response

    // One thing to be aware of when filtering by IP is that many users can have
    // the same IP if they are accessing your site from the same office or location.

    // Option examples:

    // Limit to 10 requests every 20 seconds
    $options = [ 'max_allowed' => 10, 'duration' => 20, ];

    // Limit to 2 requests per minute
    $options = [ 'max_allowed' => 2, 'duration' => 60, ];

    // Limit to 2 requests per day
    $options = [ 'max_allowed' => 10, 'duration' => (60 * 60 * 24), ];

    // If using the [RateLimit] class for multiple
    // uses then you need to specify an optional key.
    $options = [ 'key' => 'messages-sent' ];
    $options = [ 'key' => 'accounts-created' ];

    // The [RateLimit] class allows for different rate limiting algorithms;
    // the default is 'fixed-window-counter' which puts a fixed amount on
    // the number of requests for the given duration, but allows for bursts.
    // The 'token-bucket' allows for rate limiting at a timed rate however
    // it can allow for a higher number requests than the specified [max_allowed].
    //
    // For basic usage with a small number of [max_allowed] such as
    // "1 request per second" they will behave the same, however if specifying
    // a larger number such as "10 requests per 20 seconds" then there will
    // be a difference so if you are using rate limiting for web requests with
    // a larger number you may want to compare the differences using example code
    // and see related links in the API docs.
    //
    $options = [ 'algo' => 'fixed-window-counter' ];
    $options = [ 'algo' => 'token-bucket' ];

    // The [filterRequest()] function can be used to filter the request.
    // When used if the user's rate limit is reached then a 429 [Too Many Requests]
    // response is sent and [exit()] is called to stop the script execution.
    $filter_request = function() use ($app, $storage) {
        // Get User IP (example if using a load-balancer)
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

    // When using [filterRequest()] the following Response Headers can sent
    // to the client depending on which options are used:
    //   Retry-After            Standard Header
    //   X-RateLimit-Limit      Human readable description of the rate limit
    //   X-RateLimit-Remaining  Requests allowed for the given time frame
    //   X-RateLimit-Reset      Unix Timestamp for the limit to reset
    // EXAMPLE_CODE_END

    // Return Text Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            'Allowed: ' . json_encode($allowed),
            'Headers: ' . json_encode($headers, JSON_PRETTY_PRINT),
        ]));
});

$app->get('/examples/image', function() use ($app) {
    // Uncomment [return] line and modify to use a
    // file that exits on your computer
    return 'Modify the code to run';

    // Image Paths
    $file_path = 'C:\Users\Public\Pictures\Desert.jpg';
    $save_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Modified Test Image.jpg';

    // EXAMPLE_CODE_START
    // TITLE: Open and Edit Images Files
    // CLASS: Media\Image
    // Use the Media Image Class to open an image. If the image is invalid or the
    // file extension doesn't match the file type then an exception will be thrown.
    // Supported file extensions = [jpg, jpeg, gif, png, webp]
    $img = new \FastSitePHP\Media\Image();
    $img->open($file_path);

    // Generate a Thumbnail or Resize the Image to a specified max width and height.
    //
    // When both width and height are specified the image will be sized to the
    // smaller of the two values so it fits. If only width or only height are
    // specified then image will be sized proportionally to the value.
    $max_width = 200; // Pixels
    $max_height = 200;
    $img->resize($max_width, $max_height);

    // Images can also be cropped to a specific dimension.
    // This can be used with JavaScript or App cropping libraries to allow users
    // to generate thumbnails from a full uploaded image. For example allow
    // a user to crop an uploaded image to a profile thumbnail.
    $left = 50;
    $top = 40;
    $width = 120;
    $height = 80;
    $target_width = $width * 2; // Optional
    $target_height = $height * 2; // Optional
    $img->crop($left, $top, $width, $height, $target_width, $target_height);

    // Images can be rotated which is useful for sites that allow users to upload
    // images because images can often upload with incorrect rotation depending on
    // the mobile device or a user may simply want to change the rotation.
    $degrees = 180;
    $img->rotateLeft();
    $img->rotateRight();
    $img->rotate($degrees);

    // Save Quality (0 to 100) can be specified when saving JPG or WEBP images.
    // And Compression-Level (0 to 9) can be specified when saving PNG files.
    $img->saveQuality(90);   // Default Quality
    $img->pngCompression(6); // Default Compression-Level

    // Overwrite an existing image by simply calling [save()] without
    // a path or save to a new file by specifying a full file path.
    $img->save($save_path);

    // Optionally close the image to free memory when finished working with it.
    // This happens automatically when the variable is no longer used.
    $img->close();
    // EXAMPLE_CODE_END

    // Return Text Response
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
    // TITLE: Handle Language Translations for a Site or App
    // CLASS: Lang\I18N
    // FastSitePHP provides an easy to use Internationalization (i18n) API for
    // sites and apps that need to support multiple languages. The code is
    // structured but minimal in size so if you have different translation needs
    // you can simply copy and modify the class.

    // Translations are saved as JSON files in the same directory using the name
    // format of “{name}.{lang}.json”. An optional main file named “_.{lang}.json”
    // if found will loaded first. The main file “_” is useful for storing key
    // translations such as menus, page headers, page footers, etc.

    // An optional fallback language can be specified so that missing translations
    // default to another language. This allows partially translated sites to use
    // this API.

    // Since the API is simple and easy to use there are only two functions to call:
    // [langFile()] and [textFile()].

    // Example Files:
    //     _.en.json
    //     _.es.json
    //     header.en.json
    //     header.es.json
    //     about.en.json

    // Using this code the above files will be loaded in the order listed.
    $app->config['I18N_DIR'] = __DIR__ . '/i18n';
    $app->config['I18N_FALLBACK_LANG'] = 'en';

    \FastSitePHP\Lang\I18N::langFile('header', 'es');
    \FastSitePHP\Lang\I18N::langFile('about', 'es');

    // Typical usage is allowed for an app to load a language
    // file based on the Requested URL:
    $app->get('/:lang/about', function($lang) {
        \FastSitePHP\Lang\I18N::langFile('about', $lang);
    });

    // [setup()] can be called for each request to make sure
    // that a language file is always loaded for template rendering when
    // [$app->render()] is called.
    //
    // This is useful if your site uses PHP or other templates for rendering
    // and expects the [i18n] default file to always be available. For example
    // an unexpected error or call to [$app->pageNotFound()] can trigger a
    // template to be rendered.
    \FastSitePHP\Lang\I18N::setup($app);

    // Loaded translations are set to the app property ($app->locals['i18n'])
    // so that they can be used with template rendering and the calling page.

    // When using a URL format of [https://www.example.com/{lang}/{pages}]
    // and a fallback language the user will be re-directed to the same page
    // with the fallback language if the specified language doesn't exist.

    // When [langFile()] is called and the language is verified as valid
    // it is set to the app property ($app->lang).

    // The other I18N function [textFile()] simply takes a full file path
    // containing the text '{lang}' along with the selected language and then loads
    // the file or if it doesn't exist, the matching file for the fallback language.
    $file_path = $app->config['I18N_DIR'] . '/test-{lang}.txt';
    $content = \FastSitePHP\Lang\I18N::textFile($file_path, $app->lang);

    // Use [getUserDefaultLang()] to get the default language for the user based
    // on the 'Accept-Language' Request Header and available languages for the site.
    //
    // This is useful to provide custom content for the user or to redirect to the
    // user's language when they access the default URL.
    //
    // Requires config values I18N_DIR and I18N_FALLBACK_LANG.
    $default_lang = \FastSitePHP\Lang\I18N::getUserDefaultLang();
    // EXAMPLE_CODE_END

    // Return Text Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            json_encode($app->locals['i18n'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $content,
            $default_lang,
        ]));
});

$app->get('/examples/l10n', function() use ($app) {
    // EXAMPLE_CODE_START
    // TITLE: Formatting Dates, Times, and Numbers
    // CLASS: Lang\L10N
    // FastSitePHP provides an easy to use Localization (l10n) API to allow date
    // and number formatting with a user’s local language and regional settings.

    // Create a new Lang L10N Object
    $l10n = new \FastSitePHP\Lang\L10N();

    // Settings can optionally be passed when the class is first created.
    /*
    $locale = 'en-US';
    $timezone = 'America/Los_Angeles';
    $l10n = new \FastSitePHP\Lang\L10N($locale, $timezone);
    */

    // Use the [timezone()] function to get or set the timezone that will be used
    // when formatting dates and times.
    //
    // If you have a site or application that has users in multiple timezones or
    // countries an application design that works well is to save all dates and
    // times in UTC and then format based on the users selected timezone.
    //
    // This example prints:
    /*
        UTC                 = 2030-01-01 00:00
        Asia/Tokyo          = 2030-01-01 09:00
        America/Los_Angeles = 2029-12-31 16:00
    */
    $date_time = '2030-01-01 00:00:00';
    $timezones = ['UTC', 'Asia/Tokyo', 'America/Los_Angeles'];
    foreach ($timezones as $timezone) {
        // Change the Timezone
        $l10n->timezone($timezone);
        // Print the formated date and time
        echo $l10n->timezone();
        echo ' = ';
        echo $l10n->formatDateTime($date_time);
        echo '<br>';
    }
    echo '<br>';

    // Change the Timezone back to UTC for the next examples
    $l10n->timezone('UTC');

    // The [$date_time] parameter for the functions [formatDateTime(), formatDate(),
    // and formatTime()] is either a Unix Timestamp (int) or a string in format
    // of 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD'
    $date_time = 1896181200;
    $date_time = '2030-02-01 13:00:00';

    // Print Date Time with different locales using [locale()] and
    // [formatDateTime()] functions. This example prints:
    /*
        ko    = 2030. 2. 1. 오후 1:00
        bn    = ১/২/২০৩০ ১:০০ PM
        en-US = 2/1/2030, 1:00 PM
        de-CH = 01.02.2030, 13:00
        ar    = ‏١‏/٢‏/٢٠٣٠ ١:٠٠ م
    */
    $locales = ['ko-KR', 'bn-BD', 'en-US', 'de-CH', 'ar'];
    foreach ($locales as $locale) {
        // Change the Locale
        $l10n->locale($locale);
        // Print the formated date and time
        echo $l10n->locale();
        echo ' = ';
        echo $l10n->formatDateTime($date_time);
        echo '<br>';
    }
    echo '<br>';

    // In addition to [formatDateTime()] functions [formatDate()] and
    // [formatTime()] can be used to show only a date or time. Prints:
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

    // Print a formatted Number with different locales using [locale()] and
    // [formatNumber()] functions. Decimal places are optional and default
    // to 0. This example prints:
    /*
        en-US =  1,234,567,890.12345
        en-IN = 1,23,45,67,890.12345
        fr    =  1 234 567 890,12345
        fa    =  ۱٬۲۳۴٬۵۶۷٬۸۹۰٫۱۲۳۴۵
    */
    $number = 1234567890.12345;
    $decimals = 5;
    $locales = ['en-US', 'en-IN', 'fr', 'fa'];
    foreach ($locales as $locale) {
        // [locale()] is a chainable getter and setter function
        // so it can be set and read from the same line.
        echo $l10n->locale($locale)->locale();
        echo ' = ';
        echo $l10n->formatNumber($number, $decimals);
        echo '<br>';
    }

    // Get supported Locales, Languages, and Timezones
    $locales    = $l10n->supportedLocales();
    $langugages = $l10n->supportedLanguages();
    $timezones  = $l10n->supportedTimezones();
    // EXAMPLE_CODE_END

    echo '<br><b>Locales:</b><br>';
    echo json_encode($locales);
    echo '<br><br><b>Languages:</b><br>';
    echo json_encode($langugages);
    echo '<br><br><b>Timezones:</b><br>';
    echo json_encode($timezones);
});

$app->get('/examples/starter-site', function() use ($app) {
    // This route is included so code shows in the API docs, but it doesn't run here.
    // Download and run the starter site to try the actual classes because they are
    // not included with the framework.  
    return '<a href="https://github.com/fastsitephp/starter-site">Try it on the starter site</a>';

    // EXAMPLE_CODE_START
    // TITLE: Starter Site Middleware
    // CLASS: App\Middleware\Cors, App\Middleware\Auth, App\Middleware\Env

    // The FastSitePHP Starter Site includes several examples pages and provides
    // a basic directory/file structure. The site is designed to provide structure
    // for basic content (JavaScript, CSS, etc) while remaining small in size so
    // that it is easy to remove files you don’t need and customize it for your site.
    //
    //     https://github.com/fastsitephp/starter-site
    //
    // Core Middleware classes are provided and can be modified for your site.
    //
    // To use them specify the 'Class.method' on route filter functions or
    // when mounting additional files.

    // Require a user to be logged in in order to use a page
    $app->get('/secure-page', 'SecureController')->filter('Auth.hasAccess');

    // Require an authenticated user and use CORS
    $app
        ->get('/api/:record_type', 'ApiController.getData')
        ->filter('Cors.acceptAuth')
        ->filter('Auth.hasAccess');

    // Only run a route from localhost
    $app->get('/server-info', function() {
        phpinfo();
    })
    ->filter('Env.isLocalhost');
    
    // Only load a file if running from localhost
    $app->mount('/sysinfo/', 'routes-sysinfo.php', 'Env.isLocalhost');
    // EXAMPLE_CODE_END
});

// NOTE - Use this as a template for new routes, spaces need to be added between '// EXAMPLE_', etc
/*
$app->get('/examples/template', function() use ($app) {
    //EXAMPLE_CODE_START
    //TITLE: New Route Template, Fix Spaces to work
    //CLASS: Dir\Class1, Dir\Class2
    //EXAMPLE_CODE_END

    // Return Text Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
        ]));
});
*/
