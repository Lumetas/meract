## Описание
Это крайне небольшой фреймворк для языка PHP.

Его основной особенностью является построение сервера, в отличии от других фреймворков или же чистого применения где под каждый запрос весь код по новой интерпритировался, здесь используется свой web server.

Что позволяет сэкономить время на интепритации кода при запуске сервера, ведь код интерпритируется только один раз, а так же позволяет хранить некоторую временную информацию о пользователе напрямую в оперативной памяти. Не прибегая к сторонним средствам.


## Конфигурация
Конфигурация хранится в файле `config.php`, по умолчанию он выглядит так:
```
<?php
return [
	"server" => [
		"host" => "0.0.0.0",
		"port" => 80
	],
	
	"database" => [
		"driver" => "sqlite",
		"sqlite_path" => __DIR__ . "/db.sqlite"
	]
];
```
Здесь задаётся host и port сервера. Вы можете указать свою функцию при поднятии сервера, а так же свой логгер запросов:
```
<?php
return [
	"server" => [
		"host" => "0.0.0.0",
		"port" => 80,
		"requestLogger" => new class extends RequestLogger {
			public function handle($rq) {
				echo "test\n";
			}
		},

		"initFunction" => function () { echo "test start\n"; }
	]
];
```


## Установка
```
git clone https://github.com/Lumetas/lum_framework.git;
cd lum_framework;
composer install;
php console.php init;
```

## Запуск
Для запуска вам необходимо выполнить файл `index.php`, например вот так:
```
php index.php
```
После чего произойдёт разовая интерпритация и подключение всех классов, дальше сервер будет запущен на том хосту и порту что указаны в конфиге. Далее вы получите сообщение "server started!"

Оно вызывается функцией инициализации которую вы можете задать в конфигурационном файле.

Сервер начнёт слушать и принимать запросы выводя информацию о запросе в консоль, формат логов вы так же можете поменять как и было указано выше



## Роутеры и контроллеры
С таким устройством сервера без роутеров было бы невозможно жить, а без контроллеров была бы невозможна жизнь в MVC. Коей следует данный фреймворк. Очень во многом я вдохновляюсь laravel. Так что многое покажется для вас знакомым.

И так, вот все примеры синтаксиса роутеров:
```
Route::get('/', function(Request $rq) {
	$content = View::render("main", [
		"title" => "example lumframework project",
		"value" => IterateController::get()
	]);
	$r = new Response($content, 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/add/{num}', [IterateController::class, "add"]);

Route::get('/rem/{num}', [IterateController::class, "rem"]);

Route::staticFolder("static");

Route::notFound(function(Request $rq) {
	return new Response('is a 404 error', 404);
});
```
И контроллер используемый тут:
```
use LUM\core\Controller;
class IterateController extends Controller{
	private static $i = 0;
	public static function add($rq, $arr) {
		self::$i += $arr["num"];
		return self::prepare_html("value added");
	}
	public static function get(): int{
		return self::$i;
	}
	public static function rem($rq, $arr) {
		self::$i -= $arr["num"];
		return self::prepare_html("value removed");
	}
}

```
Мы можем передать в роутер путь, и коллбэк функцию, так же как и метода контроллера. Так же мы можем установить маршрут для ошибки 404 и директорию для статичных файлов.

Статический метода prepare_html который предоставляет класс Controller принимает html и возвращает, объект класса Response с установленным заголовком `Content-Type : text/html`, просто сокращает ненужный код в контроллерах.

Работает это следующим образом, когда приходит запрос, сервер сначала ищет по прописанным напрямую маршрутам, если не находит и имеется указанная статичная директория, ищет в ней. Если она не указана и/или такого файла нет, выполняется маршрут 404. Если он не установлен тогда пользователь просто увидит "not found"

## view
Шаблоны позволяют упрощать вывод. Синтаксис такой.
```
$content = View::render("main", [
    "property1" => "value1",
    "property2" => "value2"
]);
```
Данный метод рендерит указанный шаблон в html с переданными ему параметрами и возвращает этот самый html который вы может уже использовать на своё усмотрение. Пример есть в контроллерах выше.
Находясь в контроллере можно обернуть в prepare_html и сразу же вернуть.
```
return self::prepare_html(View::render("main", [
    "property1" => "value1",
    "property2" => "value2"
]));
```
### Синтаксис view
Вот самый основной синтаксис:
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $property1 ?></title>
</head>
<body>
    <h1>Welcome!</h1>
	<p>Your value: <?= $property2; ?></p>
</body>
</html>
```
## Модели
Для работы прийдётся настроить базу данных. в вашем файле config.php
Примеры:
```
"database" => [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => 3306,
    "dbname" => "test",
    "username" => "root",
    "password" => "",
    "charset" => "utf8mb4"
]
```
```
"database" => [
    "driver" => "pgsql",
    "host" => "localhost",
    "port" => 5432,
    "dbname" => "test",
    "username" => "postgres",
    "password" => "password"
]
```
```
"database" => [
    "driver" => "sqlite",
    "sqlite_path" => __DIR__ . "/database.sqlite"
]
```
У вас должны быть установлены и включены модули pdo и другие.
```
use LUM\core\Model;
class TestModel extends Model{
	protected static $table = 'your_table'; // Имя таблицы
	protected $fillable = ['id', 'name'];

}
```
Вот так вы можете создать модель привязанную к таблице. Далее примеры использования данной модели. В рамках данного примера выполнение происходит внутри роута. Вы же должны делать это внутри контроллера.
```
Route::get('/', function (Request $rq) {
	$m = new TestModel(["name" => (string) random_int(0, 10000)]); // Создаём модель с случайным именем.
	$m->save(); //Сохраняем.
	$r = new Response("Запись создана", 200); //Создаём ответ. С текстом и статусом 200.
	$r->header("Content-Type", "text/html");// Устанавливаем тип html
	return $r;// возвращаем ответ.
});

Route::get('/show', function (Request $rq) {
	$m = new TestModel();//Создаём модель 
	$pices = OUTVAR::dump($m->all()); //$m->all() - Возвращает все записи. OUTVAR::dump делает var_dump в переменную

	$r = new Response("<pre>$pices</pre>", 200);// Выводим всё пользователю обрамляе в pre
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/up/{id}/{data}', function (Request $rq, array $data) {
	$test = TestModel::find((int) $data["id"]); //Создаём модель из записи с id полученным из запроса.
	$test->name = $data['data']; // Устанавливаем значение data из запроса в name.
	$test->save(); // сохраняем

	$pices = "Запись $data[id] обновлена";
	//Сообщаем пользователю.
	$r = new Response("<pre>$pices</pre>", 200);
	$r->header("Content-Type", "text/html");
	return $r;
});

Route::get('/del/{id}', function (Request $rq, array $data) {
	$test = TestModel::find((int) $data["id"]);// создаём модель из записи по id 
	$test->delete();// Удаляем запись.
	
	$pices = "Запись $data[id] Удалена";//Информируем пользователя.

	$r = new Response("<pre>$pices</pre>", 200);
	$r->header("Content-Type", "text/html");
	return $r;
});
```
Данные примеры кода охватывают стандартные CRUD операции выполненные через модели.


## Storage
Синтаксис:
```
Storage::setTime(int seconds); // Устанавливает время жизни записей.
Storage::set("property", "value" ?prefix); // Создаёт запись, при указании префикса запись на определённом префиксе
Storage::get("property", ?prefix); // Получает значение записи
Storage::update("property", ?prefix); // Обновляет время жизни записи.
Storage::remove("property", ?prefix); // Удаляет запись
Storage::handleDeletion(); // Удаляет все истёкшие записи.
```
Из-за устройства сервера у вас есть возможность прямо в PHP хранить какие-то данные в оперативной памяти. Но при большом их объёме может случиться переполнение. Поэтому регулярно выполняйте `Storage::handleDeletion();` чтобы избежать переполнения.

Данный функционал существует ТОЛЬКО для временного краткосрочного хранения мелких, но важных данных между запросами.


## Workers
Воркеры представляют собой систему очередей.

Начнём с конфигурации:
```
"worker" => [
	"enabled" => true,
	"endpoint" => "endpoint",
	"server-callback" => function (string $data): string {
		echo $data."\n";
		return "Понял";
	}
]
```
Так же вам будет необходимо создать таблицу `lum_workers`, со столбцами `primary id`, `string name`, `string message`

Далее создадим небольшой воркер `sleep`.

В файле `app/workers/sleep.php`:
```
<?php
use LUM\core\Worker;

return new class extends Worker {
    public function run(string $message) {
        sleep((int) $message);
        $result = self::sendToServer("Я подождал $message секунд");
        if ($result == "Понял") {
            echo "Меня услышали!\n";
        }
    }
};
```
И в любом месте кода нашего мастер процесса можем использовать:
```
Worker::register("sleep", "3");
```
Это создаст запись в таблице. После worker process когда дойдёт до выполнения этой записи возьмёт имя "sleep" и запустит метод run передав туда message.

Метод sendToServer отправит данные на endpoint. И в мастер процессе отработает колбэк функция воркера. Возвращаемое ей значение выйдет из метода sendToServer.

По факту это система очередей. Но благодоря сохранению состояния. Вы можете создать воркер для обработки большого количества информации. Результат отправить в мастер и сохранить в storage для быстрого ответа пользователю.

Для запуска воркера нужно повторно запустить `index.php` после запуска сервера.

## QRYLI
qryli это QueryBuilder. Для начала вам необходимо установить в класс объект pdo. Он хранится в глобальной переменной. Например вы можете установить его, а так же storage в initFunction в вашем `config.php`:
```
"initFunction" => function () {
	global $pdo;
	Storage::setTime(600);
	QRYLI::setPdo($pdo);
	echo "server started!\n";
}
```
Небольшие примеры использования:
```
QRYLI::insert("users", ["name" => "aaaaa"])->run();
$users = QRYLI::select('*')->from('users')->where('age > ?', [18])->orderBy('name')->limit(10)->run();
QRYLI::update('users', ['age' => 26])->where('id = ?', [1])->run();
QRYLI::delete('users')->where('id = ?', [1])->run();
```