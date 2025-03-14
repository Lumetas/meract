<?php
function run() {
	echo __DIR__;
	// Папки, которые должны быть созданы
	$requiredDirectories = [
		PROJECT_DIR.'/framework/commands',
		PROJECT_DIR.'/framework/core',
		PROJECT_DIR.'/app/controllers',
		PROJECT_DIR.'/app/models',
		PROJECT_DIR.'/app/routes',
		PROJECT_DIR.'/app/views',
		PROJECT_DIR.'/static',
	];

	// Файлы, которые должны быть созданы (если их нет)
	$requiredFiles = [
		/* 'index.php' => "<?php\n\n// Your index.php content here\n", */
		/* 'public/index.php' => "<?php\n\n// Your public/index.php content here\n", */
		/* 'console.php' => "<?php\n\n// Your console.php content here\n", */
	];

	// Функция для создания директории, если её нет
	function createDirectory($path) {
		if (!is_dir($path)) {
			mkdir($path, 0755, true); // Создаём директорию с правами 0755
			echo "Created directory: $path\n";
		} else {
			echo "Directory already exists: $path\n";
		}
	}

	// Функция для создания файла, если его нет
	function createFile($path, $content) {
		if (!file_exists($path)) {
			file_put_contents($path, $content);
			echo "Created file: $path\n";
		} else {
			echo "File already exists: $path\n";
		}
	}

	// Создаём все необходимые директории
	foreach ($requiredDirectories as $dir) {
		createDirectory($dir);
	}

	// Создаём все необходимые файлы
	foreach ($requiredFiles as $file => $content) {
		createFile($file, $content);
	}

	echo "Setup completed!\n";
}
