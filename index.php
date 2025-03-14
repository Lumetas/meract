<?php

// Подключаем автозагрузку Composer
require __DIR__ . '/vendor/autoload.php';

// Подключаем файлы из framework/core
requireFilesRecursively(__DIR__ . '/framework/core');
requireFilesRecursively(__DIR__ . '/app/core');

$config = require "config.php";


// Инициализация сервера
if (isset($config["server"]["requestLogger"])){
	Route::setServer(new Server($config["server"]["host"], $config["server"]["port"]), $config["server"]["requestLogger"]);
} else {
	Route::setServer(new Server($config["server"]["host"], $config["server"]["port"]), new RequestLogger);
}
// Подключаем пользовательские файлы (контроллеры, модели и т.д.)
requireFilesRecursively(__DIR__ . '/app/models');
requireFilesRecursively(__DIR__ . '/app/controllers');

// Подключаем роуты
requireFilesRecursively(__DIR__ . '/app/routes');

// Запуск сервера
if (isset($config["server"]["initFunction"])){
	Route::startHandling($config["server"]["initFunction"]);
} else {
	Route::startHandling(function () {
		echo "Server started!\n";
	});
}

/**
 * Рекурсивно подключает все PHP-файлы из указанной директории.
 *
 * @param string $directory Путь к директории.
 */
function requireFilesRecursively($directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            require $file->getPathname();
        }
    }
}
