<?php
use LUM\core\Model;
class TestModel extends Model{
	protected static $table = 'users'; // Имя таблицы
	protected $fillable = ['name'];

}
