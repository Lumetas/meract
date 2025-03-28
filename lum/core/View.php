<?php
namespace Lum\Core;

/**
 * Класс для работы с представлениями (шаблонами).
 *
 * Обеспечивает:
 * - Рендеринг PHP-шаблонов
 * - Передачу данных в шаблоны
 * - Буферизацию вывода
 */
class View
{
	/**
	 * @var string Путь к директории с шаблонами
	 */
	private static $viewPath = 'app/views';

	/**
	 * Рендерит указанный шаблон с переданными данными.
	 *
	 * @param string $template Имя шаблона (без расширения .php)
	 * @param array $data Ассоциативный массив данных для шаблона
	 * @return string Содержимое отрендеренного шаблона
	 * @throws Exception Если файл шаблона не найден
	 *
	 * @example
	 * $html = View::render('home', ['title' => 'Главная страница']);
	 */
	public static function render(string $template, array $data = []): string
	{
		// Формируем полный путь к файлу шаблона
		$templatePath = self::$viewPath . '/' . $template . '.php';

		// Проверяем существование файла шаблона
		if (!file_exists($templatePath)) {
			throw new Exception("Template file not found: $templatePath");
		}

		// Извлекаем переменные из массива данных
		extract($data);

		// Включаем буферизацию вывода
		ob_start();
		include $templatePath;
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Устанавливает новый путь к директории с шаблонами.
	 *
	 * @param string $path Абсолютный или относительный путь
	 * @return void
	 */
	public static function setViewPath(string $path): void
	{
		self::$viewPath = rtrim($path, '/');
	}
}
