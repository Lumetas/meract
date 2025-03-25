<?php
namespace Lum\Core;
use Lum\Core\Model;
abstract class Worker
{
    public static function register(string $name, string $message): void
    {
        $work = new WorkerInstance(["name" => $name, "message" => $message]);
        $work->save();
    }

    public static function sendToServer(string $message): mixed
    {
        global $config;
        $ch = curl_init("http://localhost:" . $config['server']['port'] . '/worker-' . $config['worker']['endpoint'] . '?data=' . urlencode($message)); // such as http://example.com/example.xml
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

class WorkerInstance extends Model
{
    protected static $table = "lum_workers";
    protected $fillable = ['id', 'name', 'message'];

    public static function setTable($table)
    {
        // self::$table = $table;
    }
}
