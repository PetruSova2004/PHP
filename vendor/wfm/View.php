<?php


namespace wfm;

// Вид
// Благодаря этому компоненту, данным, которые запрашиваются у «Модели», задается вид их вывода. Если упомянуть web-приложение, то во View генерируется и отображается код HTML. Также Представление выполняет перехват действий юзера, после чего осуществляется передача этого действия Controller. Характерный пример — кнопка, которая генерируется Представлением. Когда пользователь нажмет кнопку, действие запускается уже в Controller’е.
//
//Важный момент: информация не передается в Controller напрямую (между View и Контроллером нет прямой связи — соединение происходит посредством Модели).

use RedBeanPHP\R;

class View
{

    public string $content = '';

    public function __construct(
        public $route,
        public $layout = '',
        public $view = '',
        public $meta = [],
    )
    {
        if (false !== $this->layout) {
            $this->layout = $this->layout ?: LAYOUT; // $this->layout будет равняться тому что там есть либо будет равно LAYOUT
        }
    }

    public function render($data)
    {
        // extract — Импортирует переменные из массива в текущую таблицу символов
        if (is_array($data)) {
            extract($data);
        }
        $prefix = str_replace('\\', '/', $this->route['admin_prefix']);
        // admin\ => admin/
        $view_file = APP . "/views/{$prefix}{$this->route['controller']}/{$this->view}.php";
        if (is_file($view_file)) {
            ob_start();
            require_once $view_file;
            $this->content = ob_get_clean();
        } else {
            throw new \Exception("Не найден вид {$view_file}", 500);
        }

        if (false !== $this->layout) { // $layout это рамка, а $view это картинка внутри рамки
            $layout_file = APP . "/views/layouts/{$this->layout}.php";
            if (is_file($layout_file)) {
                require_once $layout_file;
            } else {
                throw new \Exception("Не найден шаблон {$layout_file}", 500);
            }
        }
    }

    public function getMeta()
    {
        $out = '<title>' . App::$app->getProperty('site_name') . ' :: ' . h($this->meta['title']) . '</title>' . PHP_EOL;
        $out .= '<meta name="description" content="' . h($this->meta['description']) . '">' . PHP_EOL;
        $out .= '<meta name="keywords" content="' . h($this->meta['keywords']) . '">' . PHP_EOL;
        return $out;
    }

    public function getDbLogs() // данной функцией мы получаем все sql запросы которые мы указали, которые мы сделали с помошью getRow()
    {
        if (DEBUG) {
                $logs = R::getDatabaseAdapter()
                    ->getDatabase()
                    ->getLogger();
                $logs = array_merge($logs->grep('SELECT'), $logs->grep('select'), $logs->grep('INSERT'), $logs->grep('UPDATE'), $logs->grep('DELETE'));
                debug($logs);
        }
    }

    public function getPart($file, $data = null) // первым аргументом мы получаем подключаемый файл/подключаемую часть шаблона, а вторым аргументом передаваемые сюда данные по умолчанию будет null
    {
        if (is_array($data)) {
            extract($data);
        }
        $file = APP . "/views/{$file}.php";
        if (is_file($file)) {
            require $file;
        } else {
            echo "File {$file} not found...";
        }
    }

}