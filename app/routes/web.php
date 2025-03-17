<?php
use LUM\core\Route;
use LUM\core\Request;
use LUM\core\Response;
use LUM\core\OUTVAR;
use LUM\core\Worker;
use LUM\core\QRYLI;
Route::get('/', function (Request $rq) {
	QRYLI::insert("your_table", ["name" => "aaaaa"])->run();
	/* file_put_contents(time(), time()); */
	$m = new TestModel(["name" => (string) random_int(0, 10000)]);
	$m->save();
	$r = new Response($rq->cookie("username", "У вас нет имени"), 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/show', function (Request $rq) {
	/* file_put_contents(time(), time()); */
	$m = new TestModel();
	$pices = OUTVAR::dump($m->all());

	// var_dump($pices);
	$r = new Response("<pre>$pices</pre>", 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/work/{time}', function (Request $rq, array $data) {
	/* file_put_contents(time(), time()); */
	Worker::register("sleep", $data['time']);


	$r = new Response("<pre>Worker registered</pre>", 200);
	$r->header("Content-Type", "text/html");
	return $r;
});


Route::get('/up/{id}/{data}', function (Request $rq, array $data) {
	/* file_put_contents(time(), time()); */
	$test = TestModel::find((int) $data["id"]);
	$test->name = $data['data'];
	$test->save();

	$pices = "Запись $data[id] обновлена";

	// var_dump($pices);
	$r = new Response("<pre>$pices</pre>", 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/del/{id}', function (Request $rq, array $data) {
	/* file_put_contents(time(), time()); */
	$test = TestModel::find((int) $data["id"]);
	$test->delete();
	
	$pices = "Запись $data[id] Удалена";

	// var_dump($pices);
	$r = new Response("<pre>$pices</pre>", 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::staticFolder("static");

Route::notFound(function (Request $rq) {
	return new Response('is a 404 error', 404);
});
