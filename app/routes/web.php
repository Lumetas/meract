<?php
Route::get('/', function(Request $rq) {
	$h = IterateController::get();
	return new Response($h, 200);
});

Route::get('/add/{num}', [IterateController::class, "add"]);

Route::get('/rem/{num}', [IterateController::class, "rem"]);

Route::staticFolder("static");

Route::notFound(function(Request $rq) {
	return new Response('is a 404 error', 404);
});
