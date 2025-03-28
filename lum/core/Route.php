<?php
namespace Lum\Core;

/**
 * Класс для управления маршрутизацией HTTP-запросов.
 *
 * Поддерживает:
 * - Регистрацию маршрутов для различных HTTP-методов
 * - Обработку динамических параметров в URL
 * - Обслуживание статических файлов
 * - Кастомные обработчики 404 ошибок
 * - Логирование запросов
 */
class Route 
{
	/** @var array Массив зарегистрированных маршрутов */
	private static $routes = [];

	/** @var Server Экземпляр сервера */
	private static $server;

	/** @var callable Обработчик для 404 ошибок */
	private static $notFoundCallback;

	/** @var RequestLogger Логгер запросов */
	private static $requestLogger;

	/** @var string|null Путь к директории со статическими файлами */
	private static $staticPath = null;

	/**
	 * Регистрирует GET-маршрут.
	 *
	 * @param string $path Путь маршрута (может содержать параметры в фигурных скобках)
	 * @param callable $callback Функция-обработчик
	 * @return void
	 */
	public static function get(string $path, callable $callback): void
	{
		self::$routes['GET'][$path] = $callback;
	}

	/**
	 * Регистрирует POST-маршрут.
	 *
	 * @param string $path Путь маршрута (может содержать параметры в фигурных скобках)
	 * @param callable $callback Функция-обработчик
	 * @return void
	 */
	public static function post(string $path, callable $callback): void
	{
		self::$routes['POST'][$path] = $callback;
	}

	/**
	 * Устанавливает обработчик для 404 ошибок.
	 *
	 * @param callable $callback Функция-обработчик
	 * @return void
	 */
	public static function notFound(callable $callback): void
	{
		self::$notFoundCallback = $callback;
	}

	/**
	 * Устанавливает сервер и логгер запросов.
	 *
	 * @param Server $server Экземпляр сервера
	 * @param RequestLogger $requestLogger Логгер запросов
	 * @return void
	 */
	public static function setServer(Server $server, RequestLogger $requestLogger): void
	{
		self::$server = $server;
		self::$requestLogger = $requestLogger;
	}

	/**
	 * Устанавливает путь к директории со статическими файлами.
	 *
	 * @param string $path Абсолютный путь к директории
	 * @return void
	 */
	public static function staticFolder(string $path): void
	{
		self::$staticPath = rtrim($path, '/');
	}

	/**
	 * Запускает обработку маршрутов.
	 *
	 * @param callable $onStartCallback Функция, вызываемая при старте сервера
	 * @throws Exception Если сервер не был установлен
	 * @return void
	 */
	public static function startHandling(callable $onStartCallback): void
	{
		if (!self::$server) {
			throw new Exception("Server is not set. Use Route::setServer() to set the server instance.");
		}

		$handler = function (Request $request) {
			$method = $request->method;
			$uri = $request->uri;

			if ($uri == null) {
				return null;
			}

			self::$requestLogger->handle($request);

			// Обработка динамических маршрутов
			foreach (self::$routes[$method] as $routePath => $callback) {
				$pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $routePath);
				$pattern = "@^" . $pattern . "$@D";

				if (preg_match($pattern, $uri, $matches)) {
					$routeData = [];
					foreach ($matches as $key => $value) {
						if (is_string($key)) {
							$routeData[$key] = $value;
						}
					}

					return call_user_func($callback, $request, $routeData);
				}
			}

			// Обработка статических файлов
			if (self::$staticPath) {
				$filePath = self::$staticPath . '/' . ltrim($uri, '/');
				if (file_exists($filePath) && is_file($filePath)) {
					$response = new Response(file_get_contents($filePath), 200);
					$mime = self::getMimeType($filePath);
					$response->header("Content-Type", $mime);
					return $response;
				}
			}

			// Обработка 404 ошибки
			if (self::$notFoundCallback) {
				return call_user_func(self::$notFoundCallback, $request);
			}

			return new Response("Not Found", 404);
		};

		self::$server->listen($handler, $onStartCallback);
	}

	/**
	 * Определяет MIME-тип файла по его расширению.
	 *
	 * @param string $filePath Путь к файлу
	 * @return string MIME-тип файла
	 */
	private static function getMimeType(string $filePath): string
	{
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
