<?php

namespace app\controllers;

use app\models\Wishlist;
use wfm\App;

/** @property Wishlist $model */
class WishlistController extends AppController
{

    public function indexAction()
    {
        $lang = App::$app->getProperty('language');
        $products = $this->model->get_wish_products($lang); // данные о всех товарах которые в избранном
//        debug($products);
        $this->setMeta(___('wishlist_index_title'));
        $this->set(compact('products')); // передаем products в вид чтобы мы могли выводить их
    }

    public function addAction()
    {
        // json_encode()-Возвращает строку, содержащую JSON-представление для указанного value. Если параметр является массивом (array) или объектом (object), он будет рекурсивно сериализован.

        $id = get('id'); // из массива GET получаем значение которое попало в 'id', в нашем случае там будет id товара который мы хотим добавить в избранное.
        if (!$id) {
            $answer = ['result' => 'error', 'text' => ___('tpl_wishlist_add_error')]; // мы ожидаем ответ в формате JSON поэтому нам нужен массив и с помощью JSON.encode() мы его корректно преобразуем.
            exit(json_encode($answer)); // тут мы возвращаем ответ на наш ajax запрос при ошибке
        }

        $product = $this->model->get_product($id); // получаем товар по $id
        if ($product) {
            $this->model->add_to_wishlist($id);
            $answer = ['result' => 'success', 'text' => ___('tpl_wishlist_add_succes')];
        } else {
            $answer = ['result' => 'error', 'text' => ___('tpl_wishlist_add_error')];
        }
        exit(json_encode($answer)); // тут мы передаём ответ в ajax запрос
        // %2C - это закодированная запятая которую мы видим в куках
    }

    public function deleteAction()
    {
        $id = get('id');

        if ($this->model->delete_from_wishlist($id)) {
            $answer = ['result' => 'success', 'text' => ___('tpl_wishlist_delete_success')];
        } else {
            $answer = ['result' => 'error', 'text' => ___('tpl_wishlist_delete_error')];
        }
        exit(json_encode($answer));
    }
}