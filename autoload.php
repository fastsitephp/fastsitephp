<?php

// Minimal Autoloader for loading [FastSitePHP] and [App] namespaces in a 
// Development Environment. Typically in most PHP Sites a root [vendor] 
// directory will exist with an [autoload.php] file in it.

spl_autoload_register(function($class) {
    if (strpos($class, 'FastSitePHP\\') === 0) {
        $file_path = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, 12)) . '.php';
        if (is_file($file_path)) {
            require $file_path;
        }
    } elseif (strpos($class, 'App\\') === 0) {
        $file_path = __DIR__ . '/website/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file_path)) {
            require $file_path;
        }
    }
});
