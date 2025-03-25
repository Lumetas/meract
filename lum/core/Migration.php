<?php
namespace Lum\Core;
use Lum\Core\Schema;
abstract class Migration
{
	protected Schema $schema;

	public function setPdo(\PDO $pdo)
	{
		$this->schema = new Schema($pdo);
	}

	abstract public function up();
	abstract public function down();
}
