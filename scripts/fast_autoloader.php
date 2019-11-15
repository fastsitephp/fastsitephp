<?php

/**
 * Default Autoloader for FastSitePHP
 *
 * This is a minimal PHP Autoloader that can be modified for loading specific
 * namespaces and projects. Typically PHP frameworks and projects use Composer
 * however this file is designed to use less memory and run faster.
 *
 * If running the default install script [install.php] then this file gets
 * copied as the [vendor/autoload.php] file. If you run Composer to update your
 * project and have no new dependencies then you can replace the Composer
 * version with this version, however if you install additional packages
 * the use Composer’s autoloader or modify this file. Composer also overwrites
 * [autoload.php] each time it runs and update so it's generally not a problem
 * to overwrite it.
 *
 * Because FastSitePHP is minimal in size using Composer can double the time it
 * takes to generate a basic page. However this is typically a very small number
 * (only thousand's of a second).
 *
 * @link http://php.net/manual/en/function.spl-autoload-register.php
 * @link https://www.php-fig.org/psr/psr-4/
 */

spl_autoload_register(function($class) {
    // Get Namespace and if no Namespace then just the Class Name
    $pos = strpos($class, '\\');
    $namespace = ($pos === false ? $class : substr($class, 0, $pos));

    // Define Namespaces Here. This works based on the widely used PSR-4 Format
    // which requires each "\" in the class name to have it's own folder.
    $namespaces = array(
        'FastSitePHP' => '/fastsitephp/src/',
        'App' => '/../app/',
        'Parsedown' => '/erusev/parsedown/',
        'Psr' => '/psr/log/Psr/'
    );

    // Build path and load file if it exists
    if (isset($namespaces[$namespace])) {
        $root_dir = __DIR__ . $namespaces[$namespace];
        $class_path = ($pos === false ? $class : substr($class, $pos + 1));
        $file_path = $root_dir . str_replace('\\', '/', $class_path) . '.php';
        if (is_file($file_path)) {
            require $file_path;
            return;
        }
    }

    // Uncomment, modify, and use if needed.
    //
    // Handle projects that use older PRS-0 style file naming where an
    // underscore character is used to sperate folders.
    /*
    $pos = strpos($class, '_');
    if ($pos !== false) {
        $namespace = substr($class, 0, $pos);
        $namespaces = array(
            'Mustache' => '/mustache/src/Mustache/',
        );
        if (isset($namespaces[$namespace])) {
            $root_dir = __DIR__ . $namespaces[$namespace];
            $class_path = substr($class, $pos + 1);
            $file_path = $root_dir . str_replace('_', '/', $class_path) . '.php';
            if (is_file($file_path)) {
                require $file_path;
                return;
            }
        }
    }
    */
});