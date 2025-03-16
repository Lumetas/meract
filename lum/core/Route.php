<?php
namespace LUM\core;
class Route {
    private static $routes = [];
    private static $server;
    private static $notFoundCallback;
    private static $requestLogger;
    private static $staticPath = null; // Путь к статической директории

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
    public static function setServer(Server $server, requestLogger $requestLogger) {
        self::$server = $server;
        self::$requestLogger = $requestLogger;
    }

    // Установка пути к статической директории
    public static function staticFolder($path) {
        self::$staticPath = rtrim($path, '/'); // Убираем trailing slash
    }

    // Запуск обработки маршрутов
    public static function startHandling($onStartCallback) {
        if (!self::$server) {
            throw new Exception("Server is not set. Use Route::setServer() to set the server instance.");
        }
        // Обработчик для сервера
        $handler = function (Request $request) {
            $method = $request->method;
            $uri = $request->uri;

			if ($uri == null) {return null;}
            self::$requestLogger->handle($request);

            // Поиск подходящего маршрута
            foreach (self::$routes[$method] as $routePath => $callback) {
                // Преобразуем маршрут в регулярное выражение
                $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $routePath);
                $pattern = "@^" . $pattern . "$@D";

                // Проверяем совпадение URI с маршрутом
                if (preg_match($pattern, $uri, $matches)) {
                    // Извлекаем параметры из URI
                    $routeData = [];
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $routeData[$key] = $value;
                        }
                    }

                    // Вызываем колбэк с объектом запроса и параметрами маршрута
                    return call_user_func($callback, $request, $routeData);
                }
            }

            // Если маршрут не найден, проверяем статические файлы
            if (self::$staticPath) {
                $filePath = self::$staticPath . '/' . ltrim($uri, '/'); // Формируем путь к файлу
                if (file_exists($filePath) && is_file($filePath)) {
                    // Возвращаем содержимое файла
					$response = new Response(file_get_contents($filePath), 200);
					$mime = self::getMimeType($filePath);
					$response->header("Content-Type", $mime);
					return $response;
                }
            }

            // Если маршрут и статический файл не найдены, вызываем 404 обработчик
            if (self::$notFoundCallback) {
                return call_user_func(self::$notFoundCallback, $request);
            } else {
                // Если 404 обработчик не зарегистрирован, возвращаем стандартный ответ
                return new Response("Not Found", 404);
            }
        };

        // Запуск сервера с обработчиком и callback-функцией
        self::$server->listen($handler, $onStartCallback);
    }

    // Вспомогательная функция для определения MIME-типа файла
    private static function getMimeType($filePath) {
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'html' => 'text/html',
            'txt'  => 'text/plain',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
