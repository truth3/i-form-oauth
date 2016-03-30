<?php
/**
 * Based on php-fig standards. Use this to autoload api framework classes.
 * Implementation based by philsturgeon: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * IMPORTANT::For production, we recommend composer and it's autoloading features
 *  [see composer.json for setup]
 *
 * @param string $class The fully-qualified class name.
 *
 * @return void
 */
spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'Iform\\';
    // base directory for the namespace prefix
    $base_dir = dirname(__FILE__) . '/';
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    
    if ('\\' !== $prefix[$len - 1]) {
        throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
    }
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    // if the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});
