<?php

// Подключаем автозагрузку Composer
require __DIR__ . '/vendor/autoload.php';

// Проверяем, что скрипт запущен из командной строки
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Проверяем, передан ли аргумент
if ($argc < 2) {
    die("Usage: php console.php <command>\n");
}

// Получаем имя команды (первый аргумент)
$commandName = $argv[1];

// Путь к файлу команды
$commandFile = __DIR__ . "/framework/commands/{$commandName}.php";

// Проверяем, существует ли файл команды
if (!file_exists($commandFile)) {
    die("Error: Command '$commandName' not found.\n");
}

// Подключаем файл команды
require $commandFile;

// Запускаем команду (предполагаем, что в файле команды есть функция run())
if (function_exists('run')) {
    run();
} else {
    die("Error: Command '$commandName' does not have a 'run' function.\n");
}
