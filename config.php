<?php
use LUM\core\Storage;
use LUM\core\QRYLI;
return [
	"server" => [
		"host" => "0.0.0.0",
		"port" => 80,
		"initFunction" => function () {
			global $pdo;
			Storage::setTime(600);
			QRYLI::setPdo($pdo);
			echo "server started!\n";
		}
	],
	"database" => [
		"driver" => "sqlite",
		"sqlite_path" => __DIR__ . "/db.sqlite"
	],
	"worker" => [
		"enabled" => true,
		"endpoint" => "endpoint",
		"server-callback" => function ($data): string {
			echo $data . "\n";
			return "Понял";
		}
	]
];
