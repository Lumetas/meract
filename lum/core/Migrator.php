<?php
namespace Lum\Core;

class Migrator 
{
    public function __construct(
        private string $migrationsPath,
        private \PDO $pdo // Добавляем PDO в конструктор
    ) {}

    public function migrate(?string $migrationName = null): void
    {
        $files = $this->getTargetFiles($migrationName);
        
        foreach ($files as $file) {
            $migration = $this->loadMigration($file);
            $migration->up();
            echo "Применена миграция: " . basename($file) . PHP_EOL;
        }
    }

    public function rollback(?string $migrationName = null): void
    {
        $files = $this->getTargetFiles($migrationName, true);
        
        foreach ($files as $file) {
            $migration = $this->loadMigration($file);
            $migration->down();
            echo "Удалена миграция: " . basename($file) . PHP_EOL;
        }
    }

    private function loadMigration(string $file): Migration
    {
		$migration = require $file;
		$migration->setPdo($this->pdo);
        
        if ($migration instanceof \Closure) {
            // Если миграция возвращает замыкание
            return $migration($this->pdo);
        }
        
        // Для обратной совместимости со старыми миграциями
        if ($migration instanceof Migration) {
            return $migration;
        }
        
        throw new \RuntimeException("Некорректный формат миграции в файле: " . basename($file));
    }

    private function getTargetFiles(?string $name, bool $reverse = false): array
    {
        $files = $name 
            ? [$this->findMigrationFile($name)]
            : $this->getMigrationFiles();
            
        return $reverse ? array_reverse($files) : $files;
    }

    private function getMigrationFiles(): array
    {
        return glob($this->migrationsPath . '/*.php') ?: [];
    }

    private function findMigrationFile(string $name): string
    {
        foreach ($this->getMigrationFiles() as $file) {
            if (str_contains($file, $name)) {
                return $file;
            }
        }
        throw new \RuntimeException("Миграция '$name' не найдена!");
    }
}
