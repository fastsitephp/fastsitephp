<?php
// This script creates the JSON files for the API Pages and is used with the main web site.

// In case autoloader is not found:
error_reporting(-1);
ini_set('display_errors', 'on');

// Autoloader for the Starter Site
// Run this for the Starter Site 'App\*' classes prior to other code
spl_autoload_register(function($class) {
    if (strpos($class, 'App\\') === 0) {
        $file_path = __DIR__ . '/../../starter-site/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file_path)) {
            require $file_path;
        }
    }
});

// Autoloader and App Setup
// This requires the [psr/log] dependency to be installed
require __DIR__ . '/../autoload.php';
require __DIR__ . '/../vendor/autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// Define Directories
$dir_src = realpath(__DIR__ . '/../src/');
$dir_save = realpath(__DIR__ . '/../website/app_data/api/en');
if (!is_dir($dir_src) || !is_dir($dir_save)) {
    echo 'ERROR - This Script is for use with the main website.';
    exit();
}
$starter_site = realpath(__DIR__ . '/../../starter-site/app');
if (!is_dir($starter_site)) {
    echo 'ERROR - Missing Starter Site, it should be downloaded and included when running this.';
    exit();
}

// Search for Classes (which are PHP files in specific directories).
// Exclude Abstract Classes and Interfaces as they are not needed for API docs.
$search = new \FastSitePHP\FileSystem\Search();
$files = $search
    ->dir($dir_src)
    ->fileTypes(['php'])
    ->excludeRegExPaths(['/Polyfill/', '/Templates/', '/Utilities/'])
    ->excludeRegExNames(['/^Abstract/', '/Interface/'])
    ->recursive(true)
    ->files();

// Convert File Names to Class Names
$class_names = [];
foreach ($files as $file) {
    $class = str_replace($dir_src, 'FastSitePHP', $file);
    $class = str_replace('/', "\\", $class);
    $class = str_replace('.php', '', $class);
    $class_names[] = $class;
}

// Add Starter Site Middleware.
// There are only 3 classes so they are hard-coded for now.
$class_names[] = 'App\Middleware\Auth';
$class_names[] = 'App\Middleware\Cors';
$class_names[] = 'App\Middleware\Env';

// Save each Class to a JSON File
$classes = [];
foreach ($class_names as $class_name) {
    $save_name = str_replace("\\", '_', $class_name) . '.json';
    $save_name = str_replace('FastSitePHP_', '', $save_name);
    $path = $dir_save . '/' . $save_name;
    echo 'Getting Description for Class: ' . $class_name . "\n";
    try {
        $class = getClassDescription($class_name);
        $classes[] = $class;
        echo 'Saving File: ' . $path . "\n";
        file_put_contents($path, json_encode($class, JSON_PRETTY_PRINT));        
    } catch (\Exception $e) {
        // Skip error from [OdbcDatabase] if building on a machine that doesn't
        // have the ODBC driver installed. when this happens generic files such
        // as [Classes.json] should not be committed to Git without manual editing.
        // Rather this allows for machines to update docs for the class/functions
        // being developed on them.
        if (strpos($e->getMessage(), 'SQL_CUR_USE_ODBC') == false) {
            echo $e;
            exit(1);
        }
    }
}

// Build a sort array of all classes by namespace
usort($classes, function($a, $b) {
    if ($a->namespace === $b->namespace) {
        return ($a->name < $b->name) ? -1 : 1;
    } else {
        return ($a->namespace < $b->namespace) ? -1 : 1;
    }
});
// Additional Custom Sorting - Swap Application and AppMin.
// This code assumes specific classes appear at the top.
$item1 = array_shift($classes);
$item2 = array_shift($classes);
array_unshift($classes, $item1);
array_unshift($classes, $item2);

// Save all class names to a single file
$list = [];
foreach ($classes as $class) {
    $list[] = [
        'name' => $class->short_name,
        'link' => $class->link,
    ];
}
$path = $dir_save . '/../Classes.json';
echo 'Saving File: ' . $path . "\n";
file_put_contents($path, json_encode($list, JSON_PRETTY_PRINT));

// Save all classes with all prop/function names to a single file
$list = [];
foreach ($classes as $class) {
    $info = new stdClass;
    $info->name = $class->name;    
    $info->link = $class->link;

    $short_name = explode('\\', $class->name);
    $short_name = $short_name[count($short_name) - 1];

    $info->properties = [];
    foreach ($class->properties as $prop) {
        $info->properties[] = [
            'name' => $short_name . ($prop->isStatic ? '::' : '->') . $prop->name,
            'link' => $info->link . '#' . $prop->target,
        ];
    }

    $info->methods = [];
    foreach ($class->methods as $fn) {
        $info->methods[] = [
            'name' => $short_name . ($fn->isStatic ? '::' : '->') . $fn->definition,
            'link' => $info->link . '#' . $fn->target,
        ];
    }

    $list[] = $info;
}
$path = $dir_save . '/../Classes_and_Function.json';
echo 'Saving File: ' . $path . "\n";
file_put_contents($path, json_encode($list, JSON_PRETTY_PRINT));


/**
 * Return Object representing the class based on DocBlock Comments
 * 
 * @param string $class_name
 * @return stdClass
 */
function getClassDescription($class_name) {
    $class = new ReflectionClass($class_name);
    list($desc, $attr) = parseDocComment($class->getDocComment());

    $class_json = new stdClass;
    $class_json->name = $class->name;
    $class_json->short_name = str_replace('FastSitePHP\\', '', $class->name);
    $class_json->link = str_replace('\\', '_', $class_json->short_name);
    $class_json->namespace = $class->getNamespaceName();
    $class_json->description = $desc;
    $class_json->attributes = $attr;
    $class_json->properties = [];
    $class_json->methods = [];
    $class_json->links = [];
    foreach ($attr as $item) {
        if (strpos($item, '@link ') === 0) {
            $class_json->links[] = trim(substr($item, 6));
        }
    }

    // Update default properties that get expanded
    $hardcoded_defaults = [
        'FastSitePHP\Net\HttpClient.cainfo_path' => "__DIR__ . '/cacert.pem'",
        'FastSitePHP\Data\Log\HtmlLogger.temlate_file' => "__DIR__ . '/../../Templates/html-template.php'",
        'FastSitePHP\Data\Log\HtmlLogger.date_format' => '\DateTime::ISO8601',
        'FastSitePHP\Data\Log\FileLogger.date_format' => '\DateTime::ISO8601',
        'FastSitePHP\Data\Log\FileLogger.line_break' => 'PHP_EOL',
    ];
    
    $props = $class->getProperties(ReflectionProperty::IS_PUBLIC);
    $default_values = $class->getDefaultProperties();
    foreach ($props as $prop) {
        // Build Object
        list($desc, $attr) = parseDocComment($prop->getDocComment());
        $class_prop = new stdClass;
        $class_prop->name = $prop->name;
        $class_prop->target = 'prop_' . $prop->name;
        $class_prop->isStatic = $prop->isStatic();
        $class_prop->defaultValue = json_encode($default_values[$prop->name]);
        $class_prop->description = $desc;
        $class_prop->attributes = $attr;
        $class_prop->dataType = null;
        $class_prop->links = [];
        foreach ($attr as $item) {
            if (strpos($item, '@var ') === 0) {
                $class_prop->dataType = str_replace('|', "\n", substr($item, 5));
            } elseif (strpos($item, '@link ') === 0) {
                $class_prop->links[] = trim(substr($item, 6));
            }
        }
        $full_name = $class->name . '.' . $prop->name;
        if (isset($hardcoded_defaults[$full_name])) {
            $class_prop->defaultValue = $hardcoded_defaults[$full_name];
        }
        // Add to Array for the Class
        $class_json->properties[] = $class_prop;
    }

    // Exclude certain methods which do not apply. Currently these are all defined
    // in [AbstractCrypto] which is shared by several classes but not all classes
    // use each function.
    $exclude_methods = [
        'FastSitePHP\Security\Crypto\SignedData.encryptThenAuthenticate',
        'FastSitePHP\Security\Crypto\SignedData.keyType',
        'FastSitePHP\Security\Crypto\SignedData.pbkdf2Algorithm',
        'FastSitePHP\Security\Crypto\SignedData.pbkdf2Iterations',
        'FastSitePHP\Security\Crypto\FileEncryption.hashingAlgorithm',
        'FastSitePHP\Security\Crypto\FileEncryption.keySizeHmac',
        'FastSitePHP\Security\Crypto\FileEncryption.exceptionOnError',
        'FastSitePHP\Security\Crypto\FileEncryption.allowNull',
    ];
    
    $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
        // Get the method definition directly from the file
        $file = $method->getFileName();
        $start_line = $method->getStartLine();
        $content = file_get_contents($file);
        $content = explode("\n", $content);
        $definition = $content[$start_line-1];
        $definition = str_replace('public static function', '', $definition);
        $definition = str_replace('public function', '', $definition);
        $definition = str_replace('function __construct', '__construct', $definition);
        $definition = str_replace('function __destruct', '__destruct', $definition);

        // Build Object
        list($desc, $attr) = parseDocComment($method->getDocComment());
        $class_method = new stdClass;
        $class_method->definition = trim($definition);
        $class_method->target = 'fn_' . $method->name;
        $class_method->isStatic = $method->isStatic();
        $class_method->isGetterSetter = false;
        $class_method->description = $desc;
        $class_method->attributes = $attr;
        $class_method->returnType = null;
        $class_method->links = [];
        foreach ($attr as $item) {
            if (strpos($item, '@return ') === 0 && trim($item) !== '@return void') {
                $class_method->returnType = str_replace('|', ' | ', substr($item, 8));
                if (strpos($item, '$this') !== false && strpos($item, '|') !== false) {
                    $class_method->isGetterSetter = true;
                }
            } elseif (strpos($item, '@link ') === 0) {
                $class_method->links[] = trim(substr($item, 6));
            }
        }

        // Add to Array
        $full_name = $class->name . '.' . $method->name;
        if (!in_array($full_name, $exclude_methods, true)) {
            $class_json->methods[] = $class_method;
        }
    }
    return $class_json;
}

/**
 * Parse a DocBlock Comment
 * 
 * @param string|bool $comment
 * @return array
 */
function parseDocComment($comment) {
    if ($comment === false) {
        return [null, []];
    }
    $lines = explode("\n", str_replace("\r\n", "\n", $comment));
    foreach ($lines as &$line) {
        $line = trim($line);
    }
    $last_line = count($lines)-1;
    if (trim($lines[0]) !== '/**' || trim($lines[$last_line]) !== '*/') {
        echo 'Comment start/end format error: ';
        var_dump($comment);
        exit();
    }

    // Loop from 2nd to 2nd last line
    $new_comment = '';
    $attributes = [];
    for ($n = 1; $n < $last_line; $n++) {
        // Get Line and Next Line
        $cur_line = $lines[$n];
        $next_line = $lines[$n+1];
        $cur_line_is_blank = ($cur_line === '*');
        $cur_line_is_attr = (isset($cur_line[2]) && $cur_line[2] === '@');
        $next_line_is_blank = ($next_line === '*');
        $next_line_is_indented = (isset($next_line[2]) && $next_line[2] === ' ');
        $next_line_is_attr = (isset($next_line[2]) && $next_line[2] === '@');

        // Append Line, New Line, Space, etc
        if ($cur_line_is_blank) {
            $new_comment .= "\n";
        } elseif ($cur_line_is_attr) {
            $attributes[] = rtrim(substr($cur_line, 2));
        } else {
            $new_comment .= rtrim(substr($cur_line, 2));
            if ($next_line_is_blank || $next_line_is_indented || $next_line_is_attr) {
                $new_comment .= "\n";
            } else {
                $new_comment .= ' ';
            }
        }
    }
    return [trim($new_comment), $attributes];
}
