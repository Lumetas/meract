<?php
use Lum\Core\Migrator;
return new class {
	public function run($argv, $argc) {
		global $pdo;
		$migrator = new Migrator(PROJECT_DIR."/app/migrations", $pdo);
		if ($argc < 1) {
			$migrator->rollback();
		} else {
			$migrator->rollback($argv[0]);
		}
	}
};
