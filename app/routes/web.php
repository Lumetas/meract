<?php

use Lum\Core\Route;
use Lum\Core\Response;

Route::get('/', function ($rq) {
	return new Response('hello world!', 200);
});