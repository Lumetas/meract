<?php
namespace Lum\Core;
class View {
    private static $viewPath = 'app/views'; // Путь к директории с шаблонами

    // Рендеринг шаблона
    public static function render($template, $data = []) {
        // Полный путь к файлу шаблона
        $templatePath = self::$viewPath . '/' . $template . '.php';

        // Проверяем, существует ли файл шаблона
        if (!file_exists($templatePath)) {
            throw new Exception("Template file not found: $templatePath");
        }

        // Извлекаем данные в переменные
        extract($data);

        // Начинаем буферизацию вывода
        ob_start();
        include $templatePath; // Включаем файл шаблона
        $content = ob_get_clean(); // Получаем содержимое буфера

        return $content;
    }
}
