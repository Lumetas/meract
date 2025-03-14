<?php
Route::get('/', function(Request $rq) {
	$content = View::render("main", [
		"title" => "example lumframework project",
		"value" => IterateController::get()
	]);
	$r = new Response($content, 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/add/{num}', [IterateController::class, "add"]);

Route::get('/rem/{num}', [IterateController::class, "rem"]);

Route::staticFolder("static");

Route::notFound(function(Request $rq) {
	return new Response('is a 404 error', 404);
});
