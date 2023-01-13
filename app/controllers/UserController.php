<?php

namespace app\controllers;

use app\models\User;
use wfm\App;
use wfm\Pagination;

/*** @property User $model */
class UserController extends AppController
{

    public function credentialsAction() // эта функция будет менять учетные данные пользователя
    {
        if (!User::checkAuth()) {
            redirect(base_url() . 'user/login');
        }

        if (!empty($_POST)) {
            $this->model->load(); // загружаем данные в модель которые мы хотим поменять в attributes по правилам из rules

            if (empty($this->model->attributes['password'])) { // если пароль пуст при попытке его поменять тогда его нужно удалить чтобы он не валидировалься и чтобы у нас работал optional из rules, это будет происходить в том случае если мы не хотим менять пароль.
                unset($this->model->attributes['password']);
            }
            unset($this->model->attributes['email']); // удаляем email из attributes в любом случае чтобы у нас работал параметр optional из rules потому что мы не будем его менять никогда

            if (!$this->model->validate($this->model->attributes)) { // если данные не прошли валидацию
                $this->model->getErrors();
            } else {
                if (!empty($this->model->attributes['password'])) { // если у нас есть пароль то мы будем его хеш.
                    $this->model->attributes['password'] = password_hash($this->model->attributes['password'], PASSWORD_DEFAULT);
                }

                if ($this->model->update('user', $_SESSION['user']['id'])) { // тут мы меняем пароль/имя/адрес в таблице user, для пользователя по id, на то что ввел пользователь при смене данных
                    $_SESSION['success'] = ___('user_credentials_success');
                    foreach ($this->model->attributes as $k => $v) { // после того как мы поменяли учетные данные в БД, нам нужно поменять их и в сессии
                        if (!empty($v) && $k != 'password') { // меняем сессионные данные, мы не держим пароль в сессии
                            $_SESSION['user'][$k] = $v;
                        }
                    }
                } else {
                    $_SESSION['errors'] = ___('user_credentials_error');
                }
            }
            redirect();
        }

        $this->setMeta(___('user_credentials_title'));
    }

    public function signupAction()
    {
        if (User::checkAuth()) { // если пользователь авторизован тогда мы сделаем редирект на главную
            redirect(base_url());
        }

        if (!empty($_POST)) {
            $data = $_POST;
            $this->model->load($data); // тут мы получаем только те данные которые нам нужны, которые мы указали в attributes из User
            if (!$this->model->validate($data) || !$this->model->checkUnique()) { // если есть ошибка валидации или мы данные который ввел пользователь повторяются с данными из БД при регистрации
                $this->model->getErrors(); // показываем ошибки и записываем их в сессию
                $_SESSION['form_data'] = $data;
            } else {
                $this->model->attributes['password'] = password_hash($this->model->attributes['password'], PASSWORD_DEFAULT); // получаем хеш пароля который ввел пользователь
                if ($this->model->save('user')) { // если получим true при вводе данных в БД
                    $_SESSION['success'] = ___('user_signup_success_register');
                } else {
                    $_SESSION['errors'] = ___('user_signup_error_register');
                }
            }
            redirect(); // редирект происходит на эту же страницу
        }


        $this->setMeta(___('tpl_signup'));
    }


    public function loginAction()
    {
        if (User::checkAuth()) { // если пользователь авторизован тогда мы сделаем редирект на главную
            redirect(base_url());
        }

        if (!empty($_POST)) {
            if ($this->model->login()) { // если пользователь авторизовался
                $_SESSION['success'] = ___('user_login_success_login');
                redirect(base_url()); // редирект на главную страницу
            } else { // если что-то пошло не так
                $_SESSION['errors'] = ___('user_login_error_login');
                redirect(); // редирект на эту же страницу
            }
        }

        $this->setMeta('tpl_login');
    }

    public function logoutAction()
    {
        if (User::checkAuth()) { // если пользователь авторизован
            unset($_SESSION['user']); // удаляем сессию
        }
        redirect(base_url() . 'user/login');

    }

    public function cabinetAction()
    {
        if (!User::checkAuth()) {// если пользователь не авторизован тогда мы делаем редирект на страницу авторизации
            redirect(base_url() . 'user/login');
        }
        $this->setMeta(___('tpl_cabinet')); // отправляем мета-данные
    }

    public function ordersAction() // эта функция будет показывать все заказы пользователя
    {
        if (!User::checkAuth()) {
            redirect(base_url() . 'user/login');
        }

        $page = get('page');
//        $perpage = App::$app->getProperty('pagination');
        $perpage = 3; // указываем сколько товаров вызывать на одну страницу
        $total = $this->model->get_count_orders($_SESSION['user']['id']); // общее кол-во заказов пользователя
        $pagination = new Pagination($page, $perpage, $total); // указываем пагинацию
        $start = $pagination->getStart(); // указываем с какой записи мы должны начинать пагинацию

        $orders = $this->model->get_user_orders($start, $perpage, $_SESSION['user']['id']); // получаем все заказы пользователя

        $this->setMeta(___('user_orders_title'));
        $this->set(compact('orders', 'pagination', 'total')); // передаем данные чтобы мы могли к ним обращаться в виде
    }

    public function orderAction() // данная функция будет возвращать заказ который пользователь хочет увидеть в личном кабинете заказов
    {
        if (!User::checkAuth()) {
            redirect(base_url() . 'user/login');
        }

        $id = get('id');
        $order = $this->model->get_user_order($id); // получаем заказ
        if (!$order) {
            throw new \Exception('Not found order', 404);
        }

        $this->setMeta(___('user_order_title'));
        $this->set(compact('order'));
    }

    public function filesAction() // данная функция будет выводить файлы для скачивания
    {
        if (!User::checkAuth()) {
            redirect(base_url() . 'user/login');
        }

        $lang = App::$app->getProperty('language');
        $page = get('page'); // берем текущею страницу для пагинации
        $perpage = App::$app->getProperty('pagination'); // значение, которое указывает сколько товаров выводить на страницу
//        $perpage = 1;
        $total = $this->model->get_count_files(); // общее количество цифровых файлов
        $pagination = new Pagination($page, $perpage, $total); // создаем пагинацию
        $start = $pagination->getStart(); // выводим пагинацию

        $files = $this->model->get_user_files($start, $perpage, $lang); // общее количество цифровых файлов пользователя; Чтобы мы могли увидеть товары в файлах у них status должен быть = 1!
        $this->setMeta(___('user_files_title'));
        $this->set(compact('files', 'pagination', 'total'));
    }

    public function downloadAction() // этот метод будет вызываться при скачивании файла
    {
        if (!User::checkAuth()) {
            redirect(base_url() . 'user/login');
        }

        $id = get('id');
        $lang = App::$app->getProperty('language');
        $file = $this->model->get_user_file($id, $lang); // получаем запрошенный файл
//        debug($file, 1);
        // При скачивании файла мы будем принимать его по $file['original_name'], а отдавать по $file['original_name']
        if ($file) {
            // тут мы считаем файл и отдаем его
            $path = WWW . "/downloads/{$file['filename']}";
            if (file_exists($path)) { // если такой файл существует мы должны его считать и отдать
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"'); // тут мы указываем оригинальное имя файла под который мы будем отдавать данный файл
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($path));
                readfile($path); // читаем файл по указанному пути
                exit(); // завершаем выполнение кода
            } else {
                $_SESSION['errors'] = ___('user_download_error');
            }
        }
        redirect();
    }

}