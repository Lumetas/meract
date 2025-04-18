<?php
namespace Meract\Core;

use Meract\Core\BlueprintFactory;
use PDO;

class Schema
{
    public function __construct(
        private PDO $pdo,
        private ?DatabaseDialectInterface $dialect = null
    ) {}

    public function create(string $table, callable $callback): void
    {
        $blueprint = BlueprintFactory::create($this->pdo, $table);
        $callback($blueprint);

        $queries = $blueprint->compileCreate();
        foreach ($queries as $query) {
            $this->pdo->exec($query);
        }
    }

    public function drop(string $table): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS $table");
    }
}