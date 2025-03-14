<?php
Route::get('/', function(Request $rq) {
	return new Response("is a main page", 200);
});

Route::get('/test', function(Request $rq) {
	return new Response("is a test page", 200);
});

Route::notFound(function(Request $rq) {
	return new Response('is a "TI DOLBOEB PAGE NOT FOUND"', 404);
});
