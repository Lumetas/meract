<?php
class IterateController {
	private static $i = 0;
	public static function add($rq, $arr) {
		self::$i += $arr["num"];
		return new Response("value added", 200);
	}
	public static function get(): int{
		return self::$i;
	}
	public static function rem($rq, $arr) {
		self::$i -= $arr["num"];
		return new Response("value removed", 200);
	}
}
