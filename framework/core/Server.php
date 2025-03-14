<?php
class Server 
{
    /**
     * Текущий хост
     *
     * @var string
     */
    protected $host;

    /**
     * Текущий порт
     *
     * @var int
     */
    protected $port;

    /**
     * Привязанный сокет
     * 
     * @var resource|null
     */
    protected $socket = null;

    /**
     * Путь к SSL-сертификату
     *
     * @var string|null
     */
    protected $sslCertPath = null;

    /**
     * Путь к приватному ключу
     *
     * @var string|null
     */
    protected $sslKeyPath = null;

    /**
     * Разрешить самоподписанные сертификаты
     *
     * @var bool
     */
    protected $sslAllowSelfSigned = false;

    /**
     * Проверять сертификат клиента
     *
     * @var bool
     */
    protected $sslVerifyPeer = false;

    /**
     * Конструктор нового экземпляра Server
     * 
     * @param string $host
     * @param int $port
     * @param string|null $sslCertPath Путь к SSL-сертификату (необязательно)
     * @param string|null $sslKeyPath Путь к приватному ключу (необязательно)
     * @param bool $sslAllowSelfSigned Разрешить самоподписанные сертификаты (по умолчанию false)
     * @param bool $sslVerifyPeer Проверять сертификат клиента (по умолчанию false)
     * @return void
     */
    public function __construct($host, $port, $sslCertPath = null, $sslKeyPath = null, $sslAllowSelfSigned = false, $sslVerifyPeer = false)
    {
        $this->host = $host;
        $this->port = (int) $port;
        $this->sslCertPath = $sslCertPath;
        $this->sslKeyPath = $sslKeyPath;
        $this->sslAllowSelfSigned = $sslAllowSelfSigned;
        $this->sslVerifyPeer = $sslVerifyPeer;

        // Создаем сокет
        $this->createSocket();

        // Привязываем сокет
        $this->bind();
    }

    /**
     * Создание нового ресурса сокета
     *
     * @return void
     */
    protected function createSocket()
    {
        if ($this->sslCertPath && $this->sslKeyPath) {
            // Если указаны пути к сертификату и ключу, создаем SSL-контекст
            $context = stream_context_create([
                'ssl' => [
                    'local_cert' => $this->sslCertPath,         // Путь к сертификату
                    'local_pk' => $this->sslKeyPath,           // Путь к приватному ключу
                    'allow_self_signed' => $this->sslAllowSelfSigned, // Разрешить самоподписанные сертификаты
                    'verify_peer' => $this->sslVerifyPeer,      // Проверять сертификат клиента
                ]
            ]);

            $this->socket = stream_socket_server(
                "tcp://{$this->host}:{$this->port}", 
                $errno, 
                $errstr, 
                STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, 
                $context
            );

            if (!$this->socket) {
                throw new Exception("Не удалось создать сокет: $errstr ($errno)");
            }

            // Включаем шифрование на сокете
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_SSLv23_SERVER);
        } else {
            // Если SSL не используется, создаем обычный сокет
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

            if (!$this->socket) {
                throw new Exception("Не удалось создать сокет: " . socket_strerror(socket_last_error()));
            }
        }
    }

    /**
     * Привязка ресурса сокета
     *
     * @throws Exception
     * @return void
     */
    protected function bind()
    {
        if ($this->sslCertPath && $this->sslKeyPath) {
            // Для SSL сокет уже привязан через stream_socket_server
            return;
        }

        if (!socket_bind($this->socket, $this->host, $this->port)) {
            throw new Exception('Could not bind: ' . $this->host . ':' . $this->port . ' - ' . socket_strerror(socket_last_error()));
        }

        // Переводим сокет в режим прослушивания
        if (!socket_listen($this->socket)) {
            throw new Exception('Could not listen on socket: ' . socket_strerror(socket_last_error()));
        }
    }

    /**
     * Ожидание запросов
     *
     * @param callable $callback
     * @param callable $init_callback
     * @return void 
     */
    public function listen($callback, $init_callback)
    {
        if (is_callable($init_callback)) {
            $init_callback();
        }

        if (!is_callable($callback)) {
            throw new Exception('Переданный аргумент должен быть вызываемым.');
        }

        while (true) {
            if ($this->sslCertPath && $this->sslKeyPath) {
                // Для SSL используем stream_socket_accept
                $client = stream_socket_accept($this->socket);

                if (!$client) {
                    continue;
                }

                // Включаем шифрование на клиентском сокете
                stream_socket_enable_crypto($client, true, STREAM_CRYPTO_METHOD_SSLv23_SERVER);

                $request = Request::withHeaderString(fread($client, 1024));
            } else {
                // Для обычного сокета используем socket_accept
                if (!$client = socket_accept($this->socket)) {
                    echo "Ошибка при принятии соединения: " . socket_strerror(socket_last_error()) . "\n";
                    continue;
                }

                // Читаем данные из сокета
                $requestData = socket_read($client, 1024);
                if ($requestData === false) {
                    echo "Ошибка при чтении данных: " . socket_strerror(socket_last_error()) . "\n";
                    socket_close($client);
                    continue;
                }

                $request = Request::withHeaderString($requestData);
            }

            // Вызываем callback для обработки запроса
            $response = call_user_func($callback, $request);

            // Если ответ неверный, возвращаем ошибку 404
            if (!$response || !$response instanceof Response) {
                $response = Response::error(404);
            }

            // Отправляем ответ клиенту
            if ($this->sslCertPath && $this->sslKeyPath) {
                // Для SSL используем fwrite
                fwrite($client, (string) $response);
                fclose($client);
            } else {
                // Для обычного сокета используем socket_write
                socket_write($client, (string) $response, strlen((string) $response));
                socket_close($client);
            }
        }
    }
}
class Response 
{
	/**
	 * Массив доступных HTTP-кодов ответов
	 *
	 * @var array
	 */
	protected static $statusCodes = [
		// Информационные 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',

		// Успешные 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		// Перенаправления 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		// 306 устарел, но зарезервирован
		307 => 'Temporary Redirect',

		// Ошибки клиента 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		// Ошибки сервера 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	];

	/**
	 * Возвращает простой ответ на основе статусного кода
	 *
	 * @param int			$status
	 * @return Response
	 */
	public static function error( $status )
	{
		return new static( "<h1>PHPServer: ".$status." - ".static::$statusCodes[$status]."</h1>", $status );
	}

	/**
	 * Текущий статус ответа
	 *
	 * @var int
	 */
	protected $status = 200;

	/**
	 * Текущее тело ответа
	 *
	 * @var string
	 */
	protected $body = '';

	/**
	 * Текущие заголовки ответа
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Конструктор нового объекта Response
	 *
	 * @param string 		$body
	 * @param int 			$status
	 * @return void
	 */
	public function __construct( $body, $status = null )
	{
		if ( !is_null( $status ) )
		{
			$this->status = $status;
		}

		$this->body = $body;

		// установка начальных заголовков
	}

	/**
	 * Возвращает тело ответа
	 *
	 * @return string
	 */
	public function body()
	{
		return $this->body;
	}

	/**
	 * Добавляет или перезаписывает параметр заголовка
	 *
	 * @param string 			$key
	 * @param string 			$value
	 * @return void
	 */
	public function header( $key, $value )
	{
		$this->headers[ucfirst($key)] = $value;
	}

	/**
	 * Создает строку заголовка на основе текущего объекта
	 *
	 * @return string
	 */
	public function buildHeaderString()
	{
		$lines = [];

		// статус ответа
		$lines[] = "HTTP/1.1 ".$this->status." ".static::$statusCodes[$this->status];

		// добавление заголовков
		foreach( $this->headers as $key => $value )
		{
			$lines[] = $key.": ".$value;
		}

		return implode( " \r\n", $lines )."\r\n\r\n";
	}

	/**
	 * Преобразует данные ответа в строку
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->buildHeaderString().$this->body();
	}
}



class Request 
{
	/**
	 * Метод запроса
	 *
	 * @var string 
	 */
	public $method = null;

	/**
	 * Запрошенный URI
	 *
	 * @var string
	 */
	public $uri = null;

	/**
	 * Параметры запроса
	 *
	 * @var array
	 */
	public $parameters = [];

	/**
	 * Заголовки запроса
	 *
	 * @var array
	 */
	public $headers = [];

	/**
	 * Создание нового экземпляра запроса с использованием строки заголовка
	 *
	 * @param string 			$header
	 * @return Request
	 */
	public static function withHeaderString( $header )
	{
		$lines = explode( "\n", $header );

		// метод и URI
		@list( $method, $uri ) = explode( ' ', array_shift( $lines ) );
		$headers = [];

		foreach( $lines as $line )
		{
			// очистка строки
			$line = trim( $line );

			if ( strpos( $line, ': ' ) !== false )
			{
				list( $key, $value ) = explode( ': ', $line );
				$headers[$key] = $value;
			}
		}	

		// создание нового объекта запроса
		return new static( $method, $uri, $headers );
	}

	/**
	 * Конструктор запроса
	 *
	 * @param string 			$method
	 * @param string 			$uri
	 * @param array 			$headers
	 * @return void
	 */
	public function __construct( $method, $uri, $headers = [] ) 
	{
		$this->headers = $headers;
		$this->method = strtoupper( $method );

		// разделение URI и строки параметров
		@list( $this->uri, $params ) = explode( '?', $uri );

		// разбор параметров
		parse_str($params ?? '', $this->parameters);
	}

	/**
	 * Возвращает метод запроса
	 *
	 * @return string
	 */
	public function method()
	{
		return $this->method;
	}

	/**
	 * Возвращает URI запроса
	 *
	 * @return string
	 */
	public function uri()
	{
		return $this->uri;
	}

	/**
	 * Возвращает заголовок запроса
	 *
	 * @return string
	 */
	public function header( $key, $default = null )
	{
		if ( !isset( $this->headers[$key] ) )
		{
			return $default;
		}

		return $this->headers[$key];
	}

	/**
	 * Возвращает параметр запроса
	 *
	 * @return string
	 */
	public function param( $key, $default = null )
	{
		if ( !isset( $this->parameters[$key] ) )
		{
			return $default;
		}

		return $this->parameters[$key];
	}
}
