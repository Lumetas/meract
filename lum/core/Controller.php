<?php
namespace Lum\Core;

/**
 * Абстрактный базовый контроллер.
 *
 * Содержит общие методы для работы с HTTP-ответами.
 */
abstract class Controller
{
	/**
	 * Подготавливает HTML-контент для HTTP-ответа.
	 *
	 * Создает объект Response с HTML-данными, статусом 200 (OK)
	 * и устанавливает заголовок Content-Type.
	 *
	 * @param string $html HTML-контент для отправки
	 * @return Response Объект HTTP-ответа с подготовленными данными
	 */
	public static function prepare_html(string $html): Response
	{
		$r = new Response($html, 200);
		$r->header("Content-Type", "text/html");
		return $r;
	}
}
