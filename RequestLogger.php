<?php
class RequestLogger {
	public function handle(Request $rq){
		$uri = $rq->uri;
		$method = $rq->method;
		$time = date('l jS \of F Y h:i:s A');

		echo "$method -> $uri	|	$time\n";
	}
}
