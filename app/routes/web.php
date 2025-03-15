<?php
Route::get('/1', function(Request $rq) {
	$r = new Response($rq->cookie("username"), 200);
	Storage::set("prop", "val=1");
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/2', function(Request $rq) {
	$r = new Response(Storage::get("prop"), 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/3', function(Request $rq) {
	$r = new Response($rq->cookie("username"), 200);
	Storage::set("prop", "val=2", "test");
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/4', function(Request $rq) {
	$r = new Response(Storage::get("prop", "test"), 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/add/{num}', [IterateController::class, "add"]);

Route::get('/rem/{num}', [IterateController::class, "rem"]);

Route::staticFolder("static");

Route::notFound(function(Request $rq) {
	return new Response('is a 404 error', 404);
});
