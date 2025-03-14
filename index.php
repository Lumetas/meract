<?php 
include "Server.php";

class HUI {
	public static function penis (Request $rq) {
	/* var_dump($request->method); */
	$r = new Response(json_encode(
		[ 
			"a" => "b",
			"c" => "d",
			"m" => $rq->method,
			"this" => "is hui penis method"
		]
	), 500);

	$r->header( 'Content-Type', 'application/json; charset=utf-8' );
	return $r;
	}

}
$server = new Server( '0.0.0.0', 80 );

$server->listen([HUI::class, 'penis']);
