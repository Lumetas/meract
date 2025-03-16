<?php
use LUM\core\Route;
use LUM\core\Request;
use LUM\core\Response;
Route::get('/', function(Request $rq) {
	global $pdo;
	if (isset($pdo)){
		/* echo 1; */
	}
	/* file_put_contents(time(), time()); */
	$r = new Response($rq->cookie("username", "У вас нет имени"), 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/add/{num}', [IterateController::class, "add"]);

Route::get('/rem/{num}', [IterateController::class, "rem"]);

Route::staticFolder("static");

Route::notFound(function(Request $rq) {
	return new Response('is a 404 error', 404);
});
