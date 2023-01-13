<?php

namespace app\controllers;

use app\models\Cart;
use app\models\User;
use wfm\App;
use app\models\Order;

/** @property Cart $model */ // будет @property от модели Cart и это будет $model
class CartController extends AppController
{

    public function addAction(): bool
    {

        $lang = App::$app->getProperty('language');
        $id = get('id'); // тут мы получаем из JS константу 'id' из массива $_GET
        $qty = get('qty'); // тут мы получаем из JS константу 'qty'; Кол-во заказов товара

        if (!$id) { // в метод get() данные будут int если это не так то вернем false
            return false;
        }

        $product = $this->model->get_product($id, $lang); // тут попадает массив со всеми значеньями из SQL запроса
//        debug($product, 1); // можно посмотреть что попадает в $product если забыл
        if (!$product) { // $product может быть пустым массивом, в таком случае вернем false
            return false;
        }

        $this->model->add_to_cart($product, $qty); // добавляем товар в корзину
        if ($this->isAjax()) { // если у нас запрос шел от Ajax то мы подключим какой-то вид без шаблона а если нет то мы сделаем редирект на ту страницу с которой пришел пользователь но данные запишем в корзину
            $this->loadView('cart_modal'); // тут мы подключаем данный вид
        }
        redirect();
        return true;
    }

    public function showAction()
    {
        $this->loadView('cart_modal');
    }

    public function deleteAction()  // данный метод будет удалять товар из корзины
    {
        $id = get('id'); // получаем из $_GET ид который передали при клике на удаление товара
        if (isset($_SESSION['cart'][$id])) {
            $this->model->delete_item($id);
        }
        if ($this->isAjax()) {
            $this->loadView('cart_modal');
        }
        redirect();
    }

    public function clearAction()  // Данная функция будет удалять всё из корзины
    {
        if (empty($_SESSION['cart'])) {
            return false;
        }
        unset($_SESSION['cart']); // в unset() мы удаляем переданные переменные
        unset($_SESSION['cart.qty']);
        unset($_SESSION['cart.sum']);
        $this->loadView('cart_modal');
        return true;
    }

    public function viewAction()
    { // у нас корзина находится в сессии и мы просто отправляем мета-данные
        $this->setMeta(___('tpl_cart_title'));
    }


    public function checkoutAction() // данная функция нужна чтобы мы могли при заказе зарегистрировать пользователя если он не авторизован
    {
        if (!empty($_POST)) { //
            // регистрация пользователя, если не авторизован
            if (!User::checkAuth()) {
                $user = new User();
                $user->load(); // метод load находится в Model, для класса User вызываем данный метод
                if (!$user->validate($user->attributes) || !$user->checkUnique()) { // если данные не прошли валидацию
                    $user->getErrors();
                    $_SESSION['form_data'] = $user->attributes; // делаем это чтобы форма сохранилась, и покажем пользователю
                    redirect();
                } else {
                    $user->attributes['password'] = password_hash($user->attributes['password'], PASSWORD_DEFAULT); // хешируем пароль
                    if (!$user_id = $user->save('user')) { // Тут мы регистрируем пользователя по ключу $user_id и получаем его ид благодаря методу store который возвращает ид добавленного товара
                        // если мы не получили ид пользователя тогда возникла ошибка сохранении
                        $_SESSION['errors'] = ___('cart_checkout_error_register');
                        redirect();
                    }
                }
            }

            // Сохраняем заказ если пользователь авторизован. Если мы дошли сюда тогда все данные прошли валидацию
            $data['user_id'] = $user_id ?? $_SESSION['user']['id']; // $user_id у нас есть только в том случае если пользователь не был авторизован и мы его зарегистрировали и получили его id, в противном случае берём его из сессии
            $data['note'] = post('note'); // тут мы получаем примечания, которые пришли от пользователя
            $user_email = $_SESSION['user']['email'] ?? post('email');

            if (!$order_id = Order::saveOrder($data)) { // если мы не получили номер заказа из массива $data
                $_SESSION['errors'] = ___('cart_checkout_error_save_order'); // записываем сообщение об ошибки
            } else { // будем выгружать заказ и очищать корзину в случае успеха; Если письмо было успешно отправлено
                Order::mailOrder($order_id, $user_email, 'mail_order_user'); // отправляем письмо на email
                Order::mailOrder($order_id, App::$app->getProperty('admin_email'), 'mail_order_admin');
                unset($_SESSION['cart']);
                unset($_SESSION['cart.sum']);
                unset($_SESSION['cart.qty']);
                $_SESSION['success'] = ___('cart_checkout_order_success');
            }
        }
        redirect();
    }
}