<?php

namespace app\controllers;

use app\models\Product;
use wfm\App;
use app\models\Breadcrumbs;

/** @property Product $model */
class ProductController extends AppController
{

    public function viewAction()
    {
        // $this->route это свойство массива которое мы получаем из Controller с помощью @property $model
        // compact — Создаёт массив, содержащий названия переменных и их значения

        $lang = App::$app->getProperty('language');
        $product = $this->model->get_product($this->route['slug'], $lang); // мы для выбранного товара получаем инфо
//        debug($product, 1);

        if (!$product) {
//            throw new \Exception("Товар по запросу {$this->route['slug']} не найден", 404);
            $this->error_404();
            return;
        }
//        debug($product['quantity']);


        $breadcrumbs = Breadcrumbs::getBreadcrumbs($product['category_id'], $product['title']);  // $product['title'] это то что мы должны записать в конце Хлебных Крошек


        $gallery = $this->model->get_gallery($product['id']);
        $this->setMeta($product['title'], $product['description'], $product['keywords']);
        $this->set(compact('product', 'gallery', 'breadcrumbs')); // тут мы передаём представления, с их помощью мы можем обращаться к ним в Вид(внутри HTML кода)

    }


}