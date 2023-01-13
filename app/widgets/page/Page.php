<?php


namespace app\widgets\page;


use RedBeanPHP\R;
use wfm\App;
use wfm\Cache;

class Page
{

    protected $language; // это активный язык
    protected string $container = 'ul'; // это контейнер для выведенных списках
    protected string $class = 'page-menu';
    protected int $cache = 3600; // это значение указывает на сколько по умолчанию кешировать данные
    protected string $cacheKey = 'ishop_page_menu'; // это название кеша
    protected string $menuPageHtml; // это готовый HTML код
    protected string $prepend = ''; // это возможные данные которые мы хотим добавить перед меню
    protected $data; // это данные которые мы будем доставать из БД

    public function __construct($options = []) // мы будем создавать экземпляр класса и передавать в него какие-то опции
    {
        $this->language = App::$app->getProperty('language');
        $this->getOptions($options);
        $this->run();
    }

    protected function getOptions($options) // данный метод собирает передаваемые опции при создании экземпляра объекта и заполнит этими значениями свойства которые объявлены выше, P.S: опций находятся в footer
    {
        foreach ($options as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    protected function run() // данный метод запускает построение виджета
    {
        $cache = Cache::getInstance(); // создаем обьект класса Cache
        $this->menuPageHtml = $cache->get("{$this->cacheKey}_{$this->language['code']}"); // пытаемся по ключу забрать данные из кеша

        if (!$this->menuPageHtml) { // если данных нет в кеше тогда мы берем их из БД
            $this->data = R::getAssoc("SELECT p.*, pd.* FROM page p 
                        JOIN page_description pd
                        ON p.id = pd.page_id
                        WHERE pd.language_id = ?", [$this->language['id']]);
            $this->menuPageHtml = $this->getMenuPageHtml(); // из полученных данных мы построим меню
            if ($this->cache) { // если у нас кеширование включено то мы кладем это сформированное меню в кеш
                $cache->set("{$this->cacheKey}_{$this->language['code']}", $this->menuPageHtml, $this->cache);
            }
        }

        $this->output();
    }

    protected function getMenuPageHtml() // формируем нужный HTML код
    {
        $html = '';
        foreach ($this->data as $k => $v) {
            $html .= "<li><a href='page/{$v['slug']}'>{$v['title']}</a></li>";
        }
        return $html;
    }

    protected function output() // тут мы обернём данные в контейнер
    {
        echo "<{$this->container} class='{$this->class}'>"; // открываем контейнер
        echo $this->prepend;
        echo $this->menuPageHtml; // передаем данные
        echo "</{$this->container}>"; // закрываем контейнер
    }

}