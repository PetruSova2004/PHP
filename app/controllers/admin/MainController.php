<?php


namespace app\controllers\admin;


use RedBeanPHP\R;

class MainController extends AppController
{
    // compact() — Создаёт массив, содержащий названия переменных и их значения

    public function indexAction()
    {
        $orders = R::count('orders'); // кол-во общих заказов
        $new_orders = R::count('orders', 'status = 0'); // заказы где status = 0; это новые заказы
        $users = R::count('user'); // кол-во всех пользователей
        $products = R::count('product'); // кол-во всех продуктов из БД

        $title = 'Главная страница';
        $this->setMeta('Админка :: Главная страница');
        $this->set(compact('title', 'orders', 'new_orders', 'users', 'products'));
    }

}