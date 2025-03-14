<?php
function run() {
	// Папки, которые должны быть созданы
	$requiredDirectories = [
		'framework/commands',
		'framework/core',
		'app/controllers',
		'app/models',
		'app/routes',
		'app/views',
		'public/static',
	];

	// Файлы, которые должны быть созданы (если их нет)
	$requiredFiles = [
		'index.php' => "<?php\n\n// Your index.php content here\n",
		'public/index.php' => "<?php\n\n// Your public/index.php content here\n",
		'console.php' => "<?php\n\n// Your console.php content here\n",
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
		createDirectory(__DIR__ . '/' . $dir);
	}

	// Создаём все необходимые файлы
	foreach ($requiredFiles as $file => $content) {
		createFile(__DIR__ . '/' . $file, $content);
	}

	echo "Setup completed!\n";
}
