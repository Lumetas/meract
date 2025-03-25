<?php
namespace Lum\Core;
use Lum\Core\Blueprint;
class Schema
{
	public function __construct(
        private \PDO $pdo // Добавляем PDO в конструктор
    ) {}

    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        
        $sql = $blueprint->compileCreate();
		$this->pdo->prepare($sql)->execute();
    }

    public function drop(string $table): void
    {
		$this->pdo->prepare("DROP TABLE $table;")->execute();
    }
}
