<?php

/*
 *--------------------------------------------------------------------------
 * Include low level configuration
 *--------------------------------------------------------------------------
 * Для правильной работы системы QoreFramework необходимы базовые настройки,
 * объявленные в файле llc-local.php.
 */
$options = require __DIR__
	. DIRECTORY_SEPARATOR . '..'
	. DIRECTORY_SEPARATOR . 'qore.options.php';

$loader = (function($_options) {
   return require __DIR__
        . DIRECTORY_SEPARATOR . '..'
        . DIRECTORY_SEPARATOR . 'vendor'
        . DIRECTORY_SEPARATOR . 'autoload.php';
})($options);

/*
 *--------------------------------------------------------------------------
 * Create The Application
 *--------------------------------------------------------------------------
 * First we need to get an application instance. This creates an instance
 * of the application / container and bootstraps the application so it
 * is ready to receive HTTP / Console requests from the environment.
 */

require QORE_BOOT_PATH . DS . 'http.php';
