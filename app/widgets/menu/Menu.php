<?php


namespace app\widgets\menu;


use RedBeanPHP\R;
use wfm\App;
use wfm\Cache;

class Menu
{

    // https://www.youtube.com/watch?v=fOMaYSmsiQU
    // https://www.youtube.com/watch?v=Qble3-723bs

    protected $data; // тут будут хранится данные для нашего виджета меню, те самые $category из БД
    protected $tree; // дерево которое будем формироваться из полученных данных
    protected $menuHtml; // HTML код сформированного меню
    protected $tpl; // шаблон который будет использовать(может быть переопределён)
    protected $container = 'ul'; // во что будет оборачиваться наше меню, по умол. это тег "/ul"
    protected $class = 'menu'; // это то что исп по умолчанью для нашего меню
    protected $cache = 3600; // время кеширования
    protected $cacheKey = 'ishop_menu'; // ключ по которым данные будут кешироваться
    protected $attrs = []; // это те атрибуты которые можно добавить к нашему меню
    protected $prepend = ''; // возможный код который можно добавить перед нашим меню
    protected $language; // активный язык

    public function __construct($options = [])
    {
        $this->language = App::$app->getProperty('language');
        $this->tpl = __DIR__ . '/menu_tpl.php';
        $this->getOptions($options);
        $this->run();
    }

    protected function getOptions($options)
    {
        foreach ($options as $k => $v) {
            if (property_exists($this, $k)) { // если у данного класса($this) существует свойство $k который должен быть объявлен выше
                $this->$k = $v; // тогда мы в эту опцию($this->$k) запишем опцию($v)
            }
        }
    }

    protected function run()
    {
        $cache = Cache::getInstance(); // создаем объект кеша
        $this->menuHtml = $cache->get("{$this->cacheKey}_{$this->language['code']}"); // получим кеш запроса: (ishop_menu_en / ishop_menu_ru)

        if (!$this->menuHtml) { // если такого кеша нет / он утратил свою актуальность, то мы должны заново получить меню
            $this->data = App::$app->getProperty("categories_{$this->language['code']}");
            $this->tree = $this->getTree(); // тут мы должны получить дерево
            $this->menuHtml = $this->getMenuHtml($this->tree);
            if ($this->cache) {
                $cache->set("{$this->cacheKey}_{$this->language['code']}", $this->menuHtml, $this->cache);
            }
        }

        $this->output();
    }

    protected function output()
    {
        $attrs = '';
        if (!empty($this->attrs)) {
            foreach ($this->attrs as $k => $v) {
                $attrs .= " $k='$v' ";
            }
        }
        echo "<{$this->container} class='{$this->class}' $attrs>";
        echo $this->prepend;
        echo $this->menuHtml;
        echo "</{$this->container}>";
    }

    protected function getTree()
    { // данная функция из обычного массива который нам возвр. метод R::getAssoc оно строит дерево
        // Знак & означает что данная переменная (в нашем случае $node) будет присваиваться по ссылке, тоесть мы цикле foreach меняем данные которые не входят в поле видимости данного цикла.
        $tree = [];
        $data = $this->data;
        foreach ($data as $id => &$node) {
            if (!$node['parent_id']) {
                $tree[$id] = &$node;
            } else {
                $data[$node['parent_id']]['children'][$id] = &$node;
            }
        }
        return $tree;
    }

    protected function getMenuHtml($tree, $tab = '')
    {
        $str = '';
        foreach ($tree as $id => $category) {
            $str .= $this->catToTemplate($category, $tab, $id);
        }
        return $str;
    }

    protected function catToTemplate($category, $tab, $id)
    {
        ob_start();
        require $this->tpl;
        return ob_get_clean();
    }

}