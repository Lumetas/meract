<?php
return [
	"server" => [
		"host" => "0.0.0.0",
		"port" => 80
	],
	"database" => [
		"driver" => "sqlite",
		"sqlite_path" => __DIR__ . "/db.sqlite"
	],
	"worker" => [
		"enabled" => true,
		"endpoint" => "endpoint",
		"server-callback" => function ($data): string {
			echo $data."\n";
			return "Понял";
		}
	]
];
