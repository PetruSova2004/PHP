<?php


namespace wfm;


class Router
{

    protected static array $routes = []; // вся таблица маршрутов
    protected static array $route = []; // тут попадает только маршрут который соответсвует рег. выражению который мы проверяем в

    // Для функций add:
    // тут в $regexp попадает шаблон рег выражению который будет описывать тот или инной URL-адрес
    // в $route попадает тот controller и тот action который необходимо соотнести с данным шаблоном адресом matchRoute()

    public static function add($regexp, $route = [])
    {
        self::$routes[$regexp] = $route;
    }

    public static function getRoutes(): array
    {
        return self::$routes; // возвр все наши маршруты
    }

    public static function getRoute(): array
    {
        return self::$route; // возвр конкретный маршрут с которым найденно соответсвие
    }

    // в removeQueryString() мы разбиваем запрос на 2 элемента массива, все что до '&' или '&' попадает в первый массив, тоесть там будет запрос который есть в $route, во втором элементе будет все допольнительные get параметры запроса

    // / explode() - она разбивает строку на элементы массива.
    // 1 аргумент указываем - что мы ищем; 2 аргументом - где мы ищем; 3 аргументом мы указываем на сколько частей массива разбить строку

    protected static function removeQueryString($url)
    {
        if ($url) {
            $params = explode('&', $url, 2);
            if (false === str_contains($params[0], '=')) {
                return rtrim($params[0], '/');
            }
        }
        return '';
    }

    public static function dispatch($url)
    {
        $url = self::removeQueryString($url);
        if (self::matchRoute($url)) {
            if (!empty(self::$route['lang'])) {
                App::$app->setProperty('lang', self::$route['lang']);
            }
            $controller = 'app\controllers\\' . self::$route['admin_prefix'] . self::$route['controller'] . 'Controller';
            if (class_exists($controller)) {

                // тут мы указываем что $controllerObject это обьект класса Controller
                /** @var Controller $controllerObject */
                $controllerObject = new $controller(self::$route); // тут создаем класс и мы в контроллер данного класса передаем маршрут($route)

                $controllerObject->getModel();

                $action = self::lowerCamelCase(self::$route['action'] . 'Action');
                if (method_exists($controllerObject, $action)) { // контроллер, метод контролера
                    $controllerObject->$action();
                    $controllerObject->getView();
                } else {
                    throw new \Exception("Метод {$controller}::{$action} не найден", 404);
                }
            } else {
                throw new \Exception("Контроллер {$controller} не найден", 404);
            }

        } else {
            throw new \Exception("Страница не найдена", 404);
        }
    }

    public static function matchRoute($url): bool
    {
        // matchRoute() ищет соответсвие между маршрутом($url) с поступившым запросом
        // в $pattern попадает шаблон рег выражения а в $route попадает массив с значениями
        // в $url мы передаем запрос от сервера
        // preg_match — Выполняет проверку на соответствие регулярному выражению
        // в preg_match мы указываем 1 аргументом что мы и мы ищем, 2 - в каком месте мы ишем, 3 - куда мы записываем соответсвие если оно присутсвует

        foreach (self::$routes as $pattern => $route) {
            // мы проверяем если $url соответсвует $pattern если да то мы данные записываем в $matches
            if (preg_match("#{$pattern}#", $url, $matches)) {
                foreach ($matches as $k => $v) { // в $matches попадают значение из $route
                    if (is_string($k)) {
                        $route[$k] = $v;
                    }
                }
                if (empty($route['action'])) {
                    $route['action'] = 'index';
                }
                if (!isset($route['admin_prefix'])) { // !isset - если у нас не сушествует ...
                    $route['admin_prefix'] = '';
                } else {
                    $route['admin_prefix'] .= '\\'; // \\ - нужны для пространства имен
                }
                $route['controller'] = self::upperCamelCase($route['controller']);
                self::$route = $route;
                return true;
            }
        }
        return false;
    }

    // CamelCase
    protected static function upperCamelCase($name): string
    {
        // new-product => new product
        $name = str_replace('-', ' ', $name);
        // new product => New Product
        $name = ucwords($name); // upperCaseWords - Каждое слово начинается с большой буквы
        $name = str_replace(' ', '', $name);
        // New Product => NewProduct
        return $name;
    }

    // camelCase
    protected static function lowerCamelCase($name): string
    {
        return lcfirst(self::upperCamelCase($name)); // lcfirst - делает первую букву в нижнем регистре
    }

}