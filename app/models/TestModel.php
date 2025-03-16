<?php
use LUM\core\Model;
class TestModel extends Model{
	protected static $table = 'your_table'; // Имя таблицы
	protected $fillable = ['id', 'name'];

}
