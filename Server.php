<?php
class Server 
{
	/**
	 * The current host
	 *
	 * @var string
	 */
	protected $host = null;
	
	/**
	 * The current port
	 *
	 * @var int
	 */
	protected $port = null;
	
	/**
	 * The binded socket
	 * 
	 * @var resource
	 */
	protected $socket = null;
	
	/**
	 * Construct new Server instance
	 * 
	 * @param string 			$host
	 * @param int 				$port
	 * @return void
	 */
	public function __construct( $host, $port )
	{
		$this->host = $host;
		$this->port = (int) $port;
		
		// create a socket
		$this->createSocket();
		
		// bind the socket
		$this->bind();
	}
	
	/**
	 *  Create new socket resource 
	 *
	 * @return void
	 */
	protected function createSocket()
	{
		$this->socket = socket_create( AF_INET, SOCK_STREAM, 0 );
	}
	
	/**
	 * Bind the socket resourece
	 *
	 * @throws ClanCats\Station\PHPServer\Exception
	 * @return void
	 */
	protected function bind()
	{
		if ( !socket_bind( $this->socket, $this->host, $this->port ) )
		{
			throw new Exception( 'Could not bind: '.$this->host.':'.$this->port.' - '.socket_strerror( socket_last_error() ) );
		}
	}
	
	/**
	 * Listen for requests 
	 *
	 * @param callable 				$callback
	 * @return void 
	 */
	public function listen( $callback )
	{
		// check if the callback is valid
		if ( !is_callable( $callback ) )
		{
			throw new Exception( 'The given argument should be callable.' );
		}
		
		while ( 1 ) 
		{
			// listen for connections
			socket_listen( $this->socket );
			
			// try to get the client socket resource
			// if false we got an error close the connection and continue
			if ( !$client = socket_accept( $this->socket ) ) 
			{
				socket_close( $client ); continue;
			}
			
			// create new request instance with the clients header.
			// In the real world of course you cannot just fix the max size to 1024..
			$request = Request::withHeaderString( socket_read( $client, 1024 ) );
			
			// execute the callback 
			$response = call_user_func( $callback, $request );
			
			// check if we really recived an Response object
			// if not return a 404 response object
			if ( !$response || !$response instanceof Response )
			{
				$response = Response::error( 404 );
			}
			
			// make a string out of our response
			$response = (string) $response;
			
			// write the response to the client socket
			socket_write( $client, $response, strlen( $response ) );
			
			// close the connetion so we can accept new ones
			socket_close( $client );
		}
	}
}





class Response 
{
	/**
	 * An array of the available HTTP response codes
	 *
	 * @var array
	 */
	protected static $statusCodes = [
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',
	
		// Success 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
	
		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		// 306 is deprecated but reserved
		307 => 'Temporary Redirect',
	
		// Client Error 4xx
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
	
		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	];
	
	/**
	 * Returns a simple response based on a status code
	 *
	 * @param int			$status
	 * @return Response
	 */
	public static function error( $status )
	{
		return new static( "<h1>PHPServer: ".$status." - ".static::$statusCodes[$status]."</h1>", $status );
	}
	
	/**
	 * The current response status
	 *
	 * @var int
	 */
	protected $status = 200;
	
	/**
	 * The current response body
	 *
	 * @var string
	 */
	protected $body = '';
	
	/**
	 * The current response headers
	 *
	 * @var array
	 */
	protected $headers = [];
	
	/**
	 * Construct a new Response object
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
		
		// set inital headers
	}
	
	/**
	 * Return the response body
	 *
	 * @return string
	 */
	public function body()
	{
		return $this->body;
	}
	
	/**
	 * Add or overwrite an header parameter header 
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
	 * Build a header string based on the current object
	 *
	 * @return string
	 */
	public function buildHeaderString()
	{
		$lines = [];
		
		// response status 
		$lines[] = "HTTP/1.1 ".$this->status." ".static::$statusCodes[$this->status];
		
		// add the headers
		foreach( $this->headers as $key => $value )
		{
			$lines[] = $key.": ".$value;
		}
		
		return implode( " \r\n", $lines )."\r\n\r\n";
	}
	
	/**
	 * Create a string out of the response data
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
	 * The request method
	 *
	 * @var string 
	 */
	public $method = null;
	
	/**
	 * The requested uri
	 *
	 * @var string
	 */
	public $uri = null;
	
	/**
	 * The request params
	 *
	 * @var array
	 */
	protected $parameters = [];
	
	/**
	 * The request params
	 *
	 * @var array
	 */
	public $headers = [];
	
	/**
	 * Create new request instance using a string header
	 *
	 * @param string 			$header
	 * @return Request
	 */
	public static function withHeaderString( $header )
	{
		$lines = explode( "\n", $header );
		
		// method and uri
		list( $method, $uri ) = explode( ' ', array_shift( $lines ) );
		
		$headers = [];
		
		foreach( $lines as $line )
		{
			// clean the line
			$line = trim( $line );
			
			if ( strpos( $line, ': ' ) !== false )
			{
				list( $key, $value ) = explode( ': ', $line );
				$headers[$key] = $value;
			}
		}	
		
		// create new request object
		return new static( $method, $uri, $headers );
	}
	
	/**
	 * Request constructor
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
		
		// split uri and parameters string
		@list( $this->uri, $params ) = explode( '?', $uri );

		// parse the parmeters
		parse_str($params ?? '', $this->parameters);
	}
	
	/**
	 * Return the request method
	 *
	 * @return string
	 */
	public function method()
	{
		return $this->method;
	}
	
	/**
	 * Return the request uri
	 *
	 * @return string
	 */
	public function uri()
	{
		return $this->uri;
	}
	
	/**
	 * Return a request header
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
	 * Return a request parameter
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
