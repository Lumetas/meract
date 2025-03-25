<?php
namespace Lum\Core;

class Blueprint
{
	private string $table;
	private array $columns = [];

	public function __construct(string $table)
	{
		$this->table = $table;
	}

	public function id(): void
	{
		$this->columns[] = 'id INT AUTO_INCREMENT PRIMARY KEY';
	}

	public function string(string $column, int $length = 255): self
	{
		$this->columns[] = "{$column} VARCHAR({$length})";
		return $this;
	}

	public function unique(): self
	{
		$lastColumn = array_pop($this->columns);
		$this->columns[] = "{$lastColumn} UNIQUE";
		return $this;
	}

	public function timestamps(): void
	{
		$this->columns[] = 'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
		$this->columns[] = 'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
	}

	public function compileCreate(): string
	{
		return sprintf(
			'CREATE TABLE %s (%s)',
			$this->table,
			implode(', ', $this->columns)
		);
	}
}
