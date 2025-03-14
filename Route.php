<?php
class Route {
    private static $routes = [];
    private static $server;
	private static $notFoundCallback;
	private static $requestHandler;

    // Регистрация GET-маршрута
    public static function get($path, $callback) {
        self::$routes['GET'][$path] = $callback;
    }

    // Регистрация POST-маршрута
    public static function post($path, $callback) {
        self::$routes['POST'][$path] = $callback;
    }

    // Регистрация обработчика 404
    public static function notFound($callback) {
        self::$notFoundCallback = $callback;
    }

    // Установка сервера
    public static function setServer(Server $server, requestHandler $requestHandler) {
        self::$server = $server;
		self::$requestHandler = $requestHandler;
    }

    // Запуск обработки маршрутов
    public static function startHandling($onStartCallback) {
        if (!self::$server) {
            throw new Exception("Server is not set. Use Route::setServer() to set the server instance.");
        }

        // Обработчик для сервера
        $handler = function (Request $request) {
			self::$requestHandler->handle($request);
            $method = $request->method;
            $uri = $request->uri;

            // Поиск подходящего маршрута
            if (isset(self::$routes[$method][$uri])) {
                $callback = self::$routes[$method][$uri];
                return call_user_func($callback, $request);
            } else {
                // Если маршрут не найден, вызываем 404 обработчик
                if (self::$notFoundCallback) {
                    return call_user_func(self::$notFoundCallback, $request);
                } else {
                    // Если 404 обработчик не зарегистрирован, возвращаем стандартный ответ
                    return new Response("Not Found", 404);
                }
            }
        };

        // Запуск сервера с обработчиком и callback-функцией
        self::$server->listen($handler, $onStartCallback);
    }
}
