<?php

// Подключаем автозагрузку Composer
require __DIR__ . '/vendor/autoload.php';

// Подключаем файлы core
requireFilesRecursively(__DIR__ . '/lum/core');
requireFilesRecursively(__DIR__ . '/app/core');

use LUM\core\Database;
use LUM\core\Route;
use LUM\core\Server;
use LUM\core\RequestLogger;
$config = require "config.php";
try {
	$database = Database::getInstance($config['database']);
	$pdo = $database->getPdo();
} catch (Exception $e) {
	throw new Exception("Проблема с базой данных, проверь config.php");
}
// Инициализация сервера

try {
	if (isset($config["server"]["requestLogger"])){
		@Route::setServer(new Server($config["server"]["host"], $config["server"]["port"]), $config["server"]["requestLogger"]);
	} else {
		@Route::setServer(new Server($config["server"]["host"], $config["server"]["port"]), new RequestLogger);
	}

} catch (Exception $e) {
	if (isset($config['worker'], $config['worker']['table'], $config['worker']['fileColumn'], $config['worker']['dataColumn'])){
		echo "Сервер уже запущен. Воркер настроен. Запуск.\n";
		while (true) {
		}
	} else {
		echo "Сервер уже запущен. Воркер не настроен. Завершение.\n";
		exit();
	}
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
