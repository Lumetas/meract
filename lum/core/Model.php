<?php
namespace LUM\core;
use PDO;
use PDOException;

abstract class Model
{
    // Имя таблицы в базе данных
    protected static $table;

    // Первичный ключ таблицы (по умолчанию 'id')
    protected $primaryKey = 'id';

    // Поля, которые можно массово назначать
    protected $fillable = [];

    // Атрибуты модели
    protected $attributes = [];

    // Конструктор
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    // Заполнение атрибутов
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public static function first()
    {

        $pdo = self::getPdo();
        $table = self::getTable();

        // Получаем id первой записи
        $stmt = $pdo->prepare("SELECT id FROM {$table} ORDER BY id ASC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['id'])) {
            // Используем метод find() для создания объекта модели
            return static::find($result['id']);
        }
        return null;
    }

    public static function last()
    {
        $pdo = self::getPdo();
        $table = self::getTable();

        // Получаем id последней записи
        $stmt = $pdo->prepare("SELECT id FROM {$table} ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['id'])) {
            // Используем метод find() для создания объекта модели
            return static::find($result['id']);
        }
        return null;
    }


    // Получение подключения к базе данных
    protected static function getPdo()
    {
        global $pdo; // Используем глобальную переменную с PDO
        return $pdo;
    }

    // Получение имени таблицы
    protected static function getTable()
    {
        if (static::$table === null) {
            // Если имя таблицы не задано, используем имя класса в нижнем регистре
            $className = (new \ReflectionClass(static::class))->getShortName();
            static::$table = strtolower($className) . 's'; // Например, User -> users
        }
        return static::$table;
    }

    // Поиск записи по ID
    public static function find($id)
    {
        $pdo = self::getPdo();
        $table = self::getTable();
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new static($result);
        }
        return null;
    }

    // Получение всех записей
    public static function all()
    {
        $pdo = self::getPdo();
        $table = self::getTable();
        $stmt = $pdo->query("SELECT * FROM {$table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Сохранение модели (создание или обновление)
    public function save()
    {
        if (isset($this->attributes[$this->primaryKey])) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    // Создание новой записи
    protected function insert()
    {
        $pdo = self::getPdo();
        $table = self::getTable();

        $columns = implode(', ', array_keys($this->attributes));
        $values = ':' . implode(', :', array_keys($this->attributes));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($this->attributes);

        // Устанавливаем ID новой записи
        $this->attributes[$this->primaryKey] = $pdo->lastInsertId();
    }

    // Обновление записи
    protected function update()
    {
        $pdo = self::getPdo();
        $table = self::getTable();

        $set = [];
        foreach ($this->attributes as $key => $value) {
            if ($key !== $this->primaryKey) {
                $set[] = "{$key} = :{$key}";
            }
        }
        $set = implode(', ', $set);

        $sql = "UPDATE {$table} SET {$set} WHERE {$this->primaryKey} = :{$this->primaryKey}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($this->attributes);
    }

    // Удаление записи
    public function delete()
    {
        $pdo = self::getPdo();
        $table = self::getTable();

        $sql = "DELETE FROM {$table} WHERE {$this->primaryKey} = :{$this->primaryKey}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->primaryKey => $this->attributes[$this->primaryKey]]);
    }

    // Магический метод для доступа к атрибутам
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    // Магический метод для установки атрибутов
    public function __set($name, $value)
    {
        if (in_array($name, $this->fillable)) {
            $this->attributes[$name] = $value;
        }
    }
}
