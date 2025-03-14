<?php
abstract class Controller {
	public static function prepare_html (string $html) : Response{
		$r = new Response($html, 200);
		$r->header("Content-Type", "text/html");
		return $r;
	}
}

