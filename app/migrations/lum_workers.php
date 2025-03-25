<?php

use Lum\Core\Migration;

return new class extends Migration {
    public function up()
    {
        $this->schema->create('lum_workers', function ($table) {
            $table->id();               // Автоинкрементный первичный ключ
            $table->string('name');     // Строковое поле name
            $table->string('message');  // Строковое поле message
        });
    }

    public function down()
	{
        $this->schema->drop('lum_workers');
    }
};
