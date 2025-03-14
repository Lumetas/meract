<?php 
include "Server.php";
include "Route.php";

$i = 0;

Route::setServer(new Server('0.0.0.0', 80));

Route::get('/', function(Request $rq) {
	$GLOBALS["i"] += 1;
	return new Response("is a main page $GLOBALS[i]", 200);
});

Route::get('/test', function(Request $rq) {
	return new Response("is a test page", 200);
});

Route::notFound(function(Request $rq) {
	return new Response('is a "TI DOLBOEB PAGE NOT FOUND"', 404);
});

Route::startHandling(function () {
	echo "Server started!\n";
});
