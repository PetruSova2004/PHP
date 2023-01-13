<?php

namespace app\controllers;

use app\models\Search;
use wfm\App;
use wfm\Pagination;

/** @property Search $model */
class SearchController extends AppController
{

    public function indexAction()
    {
        $s = get('s', 's'); // тут мы получаем то что ввёл пользователь по ключу input, и указываем какой тип данных мы ожидаем, данный input находится в header ~ 36 строка
        $lang = App::$app->getProperty('language');
        $page = get('page'); // сюда попадает номер текущей страницы
        $perpage = App::$app->getProperty('pagination');
        $total = $this->model->get_count_find_products($s, $lang); // получаем общее кол-во запрошенных продуктов
        $pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart(); // получаем с какой записи из БД начинаем выборку

        $products = $this->model->get_find_products($s, $lang, $start, $perpage); // получаем элементы которые в названии содержат $s
//        debug($products);
        $this->setMeta(___('tpl_search_title'));
        $this->set(compact('s', 'products', 'pagination', 'total'));
    }

}