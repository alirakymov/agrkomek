<?php

# Сепаратор директорий

define('DS', DIRECTORY_SEPARATOR);
# Перенос строки
define('N', "\n");
# CLI режим
define('IS_CLI', php_sapi_name() === 'cli');

$options = array_merge_recursive(
    [ 'constants' =>  [ 'QORE_PATH' => dirname(__FILE__),  'PROJECT_PATH' => dirname(__FILE__) ] ],
    json_decode(file_get_contents(dirname(__FILE__) . '/qore.options.json'), true) ?: [],
);

$replacements = [];
foreach ($options['constants'] as $name => $value) {
    $value = str_replace( array_keys($replacements), array_values($replacements), $value);
    $replacements[sprintf('${%s}', $name)] = $value;
    if (preg_match('/^.+_PATH$/', $name)) {
        is_dir($value) || mkdir($value);
        $value = is_dir($value) ? realpath($value) : $value;
    }
    $options['constants'][$name] = $value;
    defined($name) OR define($name, $options['constants'][$name]);
}

# Prepare autoload namespaces
if (isset($options['namespaces'])) {
    foreach ($options['namespaces'] as $namespace => $directory) {
        unset($options['namespaces'][$namespace]);
        list($namespace, $directory) = str_replace( array_keys($replacements), array_values($replacements), [$namespace, $directory]);
        $options['namespaces'][$namespace] = $directory;
    }
}

$requiredConstants = [
    # -- HELPERS
    'DS', 'N', 'IS_CLI',
    # - Project constants
    'PROJECT_PATH', 'PROJECT_CONFIG_PATH', 'PROJECT_STORAGE_PATH',
    'PROJECT_FRONTAPP_PATH', 'PROJECT_DATA_PATH',
    # - Qore constants
    'QORE_PATH', 'QORE_FRONT_PATH', 'QORE_PROJECTS_PATH',
    'QORE_CONFIG_PATH', 'QORE_BOOT_PATH',
];

/*
*--------------------------------------------------------------------------
* Check for constants defines
*--------------------------------------------------------------------------
* Проверить на наличие жизенноважных для работы системы постоянных
*--------------------------------------------------------------------------
*/
foreach ($requiredConstants as $constant) {
    if (! defined($constant)) {
        echo sprintf('Constant `%s` is undefined! Please read manuals for install qore framework! <br>', $constant);
    } elseif (preg_match('/^.+_PATH$/', $constant) && ! is_dir(constant($constant)) && ! mkdir(constant($constant), 0744, true)) {
        echo sprintf( 'Constant path of `%s` (%s) is undefined! Please read manuals for install qore framework! <br>',
            $constant, $options['constants'][$constant]
        );
    }
}

return $options;
