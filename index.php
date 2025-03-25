<?php

// Подключаем автозагрузку Composer
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lum/core/RecursiveLoad.php';

// Подключаем файлы core
requireFilesRecursively(__DIR__ . '/lum/core');
requireFilesRecursively(__DIR__ . '/app/core');

use Lum\Core\Database;
use Lum\Core\Route;
use Lum\Core\Server;
use Lum\Core\Request;
use Lum\Core\Response;
use Lum\Core\RequestLogger;

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
	echo "Ошибка запуска сервера, возможно проблемы с конфигурацией или сервер уже запущен\n";
	echo $e->getMessage()."\n";
	exit();
}
// Подключаем пользовательские файлы (контроллеры, модели и т.д.)
requireFilesRecursively(__DIR__ . '/app/models');
requireFilesRecursively(__DIR__ . '/app/controllers');

// Подключаем роуты
requireFilesRecursively(__DIR__ . '/app/routes');

if (isset($config['worker'], $config['worker']['enabled'], $config['worker']['endpoint'], $config['worker']['server-callback']) && $config['worker']['enabled']) {
	Route::get("/worker-".$config['worker']['endpoint'], function (Request $rq) {
		global $config;
		return new Response($config['worker']['server-callback'] ($rq->parameters["data"]), 200);
	});
}


// Запуск сервера
if (isset($config["server"]["initFunction"])){
	Route::startHandling($config["server"]["initFunction"]);
} else {
	Route::startHandling(function () {
		echo "Server started!\n";
	});
}
