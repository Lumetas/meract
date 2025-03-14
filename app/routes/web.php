<?php
Route::get('/', function(Request $rq) {
	$h = IterateController::get();
	return new Response($h, 200);
});

Route::get('/add', function(Request $rq) {
	IterateController::add();
	return new Response("value added", 200);
});

Route::notFound(function(Request $rq) {
	return new Response('is a "TI DOLBOEB PAGE NOT FOUND"', 404);
});
