<?php
namespace Lum\Core;
use Lum\Storage;

class Session {
    public function __construct(
        private Request $req
    ) {}

	public static function start(Request $req) : Session {
		return new self($req);
	}

	public function set (string $property, mixed $value) : void {
		
	}

	public function get (string $property) : mixed {

	}
	
	public function end(Response $resp) : Response {

		return $resp;
	}
}
