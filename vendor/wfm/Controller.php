<?php


namespace wfm;


// Контроллер
//Что же такое Контроллер в MVC? Его задача — обработать информацию, которую вводит пользователь, а также обновить Model. Именно в этой части схемы происходит взаимодействие с пользователем. Можно назвать Controller сборщиком информации. После выполнения этой задачи, Контроллер передает информацию в Model, где и происходит последующая организация хранения.

// если класс abstract то от него нельзя создать обьект можно только наследоваться от него
abstract class Controller
{

    public array $data = [];
    public array $meta = ['title' => '', 'keywords' => '', 'description' => ''];
    public false|string $layout = '';
    public string $view = '';
    public object $model;


    public function __construct(public $route = [])
    {

    }

    public function getModel()
    {
        $model = 'app\models\\' . $this->route['admin_prefix'] . $this->route['controller'];
        if (class_exists($model)) {
            $this->model = new $model();
        }
    }

    public function getView()
    {
        $this->view = $this->view ?: $this->route['action'];
        (new View($this->route, $this->layout, $this->view, $this->meta))->render($this->data);
    }

    public function set($data)
    {
        $this->data = $data;
    }

    public function setMeta($title = '', $description = '', $keywords = '')
    {
       $this->meta = [
           'title' => $title,
           'description' => $description,
           'keywords' => $keywords,
       ];
    }

    public function isAjax(): bool
    { // данная функция определяет отправлялся ли запрос Ajax или нет; Если да то будет true, если нет->false
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function loadView($view, $vars = []) // вид который нужно подключить, массив данных для данного вида
    { // данный метод отрабатывает только на ответ от AJAX запрос
        extract($vars);
        $prefix = str_replace('\\', '/', $this->route['admin_prefix']);
        require APP . "/views/{$prefix}{$this->route['controller']}/{$view}.php";
        die;
    }

    public function error_404($folder = 'Error', $view = 404, $response = 404)
        // http_response_code — Получает или устанавливает код ответа HTTP
    {
        http_response_code($response);
        $this->setMeta(___('tpl_error_404'));
        $this->route['controller'] = $folder;
        $this->view = $view;
    }

}