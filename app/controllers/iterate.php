<?php
class IterateController extends Controller{
	private static $i = 0;
	public static function add($rq, $arr) {
		self::$i += $arr["num"];
		return self::prepare_html("value added");
	}
	public static function get(): int{
		return self::$i;
	}
	public static function rem($rq, $arr) {
		self::$i -= $arr["num"];
		return self::prepare_html("value removed");
	}
}
