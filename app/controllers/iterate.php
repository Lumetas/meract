<?php
class IterateController {
	private static $i = 0;
	public static function add() {
		self::$i ++;
	}
	public static function get(): int{
		return self::$i;
	}
}
