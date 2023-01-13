<?php

namespace app\controllers;

use app\models\Breadcrumbs;
use app\models\Category;
use wfm\App;
use wfm\Pagination;

/** @property Category $model */
class CategoryController extends AppController
{
    // compact — Создаёт массив, содержащий названия переменных и их значения: Переменная => Значение

    public function viewAction()
    {
        $lang = App::$app->getProperty('language');
        $category = $this->model->get_category($this->route['slug'], $lang); // получаем данные о выбранной категории
//        debug($category);

        if (!$category) {
            $this->error_404();
            return;
        }

        $breadcrumbs = Breadcrumbs::getBreadcrumbs($category['id']);

        $cats = App::$app->getProperty("categories_{$lang['code']}");
//        debug($cats); // данные о всех категориях на выбранном языке
        $ids = $this->model->getIds($category['id']); // тут получаем id вложенных категорий
        $ids = !$ids ? $category['id'] : $ids . $category['id']; // если мы не достали вложенных категорий в этом случае('?') мы кладём туда текущую категорию, а в противном случае мы в $ids дописываем текущую категорию

        $page = get('page'); // вернём параметр page, данный параметр есть в http запросе и он указывает на какой мы странице (#page=#).
        $peerage = App::$app->getProperty('pagination'); // берем pagination из params.php
        $total = $this->model->get_count_products($ids); // тут мы получаем сколько товаров данной категорий существует
        $pagination = new Pagination($page, $peerage, $total);
        $start = $pagination->getStart(); // тут мы указываем с какого товара начинать выборку товара

        $products = $this->model->get_products($ids, $lang, $start, $peerage);
//        debug($products, 1);
        $this->setMeta($category['title'], $category['description'], $category['keywords']);
        $this->set(compact('products', 'category', 'breadcrumbs', 'total', 'pagination'));

    }

}