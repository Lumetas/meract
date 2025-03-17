<?php
namespace LUM\core;
use PDO;
use PDOException;
class QRYLI
{
    private static $pdo;
    private $query = '';
    private $params = [];

    // Устанавливаем PDO
    public static function setPdo(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    // SELECT
    public static function select(string $columns = '*'): self
    {
        $instance = new self();
        $instance->query = "SELECT $columns ";
        return $instance;
    }

    // FROM
    public function from(string $table): self
    {
        $this->query .= "FROM $table ";
        return $this;
    }

    // WHERE
    public function where(string $condition, array $params = []): self
    {
        $this->query .= "WHERE $condition ";
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    // INSERT
    public static function insert(string $table, array $data): self
    {
        $instance = new self();
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $instance->query = "INSERT INTO $table ($columns) VALUES ($placeholders) ";
        $instance->params = array_values($data);
        return $instance;
    }

    // UPDATE
    public static function update(string $table, array $data): self
    {
        $instance = new self();
        $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $instance->query = "UPDATE $table SET $set ";
        $instance->params = array_values($data);
        return $instance;
    }

    // DELETE
    public static function delete(string $table): self
    {
        $instance = new self();
        $instance->query = "DELETE FROM $table ";
        return $instance;
    }

    // ORDER BY
    public function orderBy(string $column, string $order = 'ASC'): self
    {
        $this->query .= "ORDER BY $column $order ";
        return $this;
    }

    // LIMIT
    public function limit(int $limit): self
    {
        $this->query .= "LIMIT $limit ";
        return $this;
    }

    // JOIN
    public function join(string $table, string $on, string $type = 'INNER'): self
    {
        $this->query .= "$type JOIN $table ON $on ";
        return $this;
    }

    // GROUP BY
    public function groupBy(string $columns): self
    {
        $this->query .= "GROUP BY $columns ";
        return $this;
    }

    // Выполнение запроса
    public function run(): array
    {
        if (!self::$pdo) {
            throw new Exception("PDO object is not set. Use QB::setPdo() to set it.");
        }

        try {
            $stmt = self::$pdo->prepare($this->query);
            $stmt->execute($this->params);

            // Если это SELECT, возвращаем данные
            if (stripos($this->query, 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Для INSERT, UPDATE, DELETE возвращаем количество затронутых строк
            return ['affected_rows' => $stmt->rowCount()];
        } catch (PDOException $e) {
            // Обработка ошибок
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }
}