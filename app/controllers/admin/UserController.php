<?php


namespace app\controllers\admin;

use app\models\admin\User;
use RedBeanPHP\R;
use wfm\Pagination;

/** @property User $model */
class UserController extends AppController
{

    public function indexAction()
    {
        $page = get('page');
        $perpage = 20;
        $total = R::count('user');
        $pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart();

        $users = $this->model->get_users($start, $perpage);
        $title = 'Список пользователей';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title', 'users', 'pagination', 'total'));
    }

    public function viewAction()
    {
        $id = get('id');
        $user = $this->model->get_user($id); // получаем информацию о пользователе
        if (!$user) {
            throw new \Exception('Not found user', 404);
        }

        $page = get('page');
        $perpage = 1;
        $total = $this->model->get_count_orders($id); // берем общее кол-во заказов пользователя
        $pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart();

        $orders = $this->model->get_user_orders($start, $perpage, $id); // получаем заказы пользователя
        $title = 'Профиль пользователя';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title', 'user', 'pagination', 'total', 'orders'));
    }

    public function addAction()
    {
        if (!empty($_POST)) {
            $this->model->load(); // загружаем данные в атрибуты модели
            if (!$this->model->validate($this->model->attributes) || !$this->model->checkUnique('Этот E-mail уже занят')) { // валидируем и проверяем введенные данные
                $this->model->getErrors();
                $_SESSION['form_data'] = $_POST;
            } else {
                $this->model->attributes['password'] = password_hash($this->model->attributes['password'], PASSWORD_DEFAULT);
                if ($this->model->save('user')) { // сохраняем всё в таблицу user
                    $_SESSION['success'] = 'Пользователь добавлен';
                } else {
                    $_SESSION['errors'] = 'Ошибка добавления пользователя';
                }
            }
            redirect();
        }
        $title = 'Новый пользователь';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title'));
    }

    public function editAction()
    {
        $id = get('id');
        $user = $this->model->get_user($id); // получаем данные о пользователе по ид
        if (!$user) {
            throw new \Exception('Not founud user', 404);
        }

        if (!empty($_POST)) {
            $this->model->load(); // загружаем данные в атрибуты модели
//            debug($this->model->attributes,1);
            if (empty($this->model->attributes['password'])) {
                unset($this->model->attributes['password']);
            }

            if (!$this->model->validate($this->model->attributes) || !$this->model->checkEmail($user)) {
                $this->model->getErrors();
            } else {
                if (!empty($this->model->attributes['password'])) { // если мы попали сюда значит админ хочет поменять пароль пользователю
                    $this->model->attributes['password'] = password_hash($this->model->attributes['password'], PASSWORD_DEFAULT);
                }
                if ($this->model->update('user', $id)) {
                    $_SESSION['success'] = 'Данные пользователя обновлены. Перезайдите, если вы обновляли свои данные';
                } else {
                    $_SESSION['errors'] = 'Ошибка обновления профиля пользователя';
                }
            }
            redirect();
        }

        $title = 'Редактирование пользователя';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title', 'user'));
    }

    public function loginAdminAction()
    {
        if ($this->model::isAdmin()) { // если пользователь обращается к странице авторизации, но при этом он уже авторизирован тогда делаем редирект на главную страницу админки
            redirect(ADMIN);
        }

        $this->layout = 'login'; // указываем какой шаблон будем использовать из layouts
        if (!empty($_POST)) { // если переданные данные из формы
            if ($this->model->login(true)) { // пытаемся авторизовать пользователя как админа
                $_SESSION['success'] = 'Вы успешно авторизованы';
            } else {
                $_SESSION['errors'] = 'Логин/пароль введены неверно';
            }
            if ($this->model::isAdmin()) { // если появились в сессии данные администратора тогда мы выводим главную страницу админки
                redirect(ADMIN);
            } else { // делаем редирект на страницу авторизации заново
                redirect();
            }
        }

    }

    public function logoutAction() // данная функция будет выходить из учетной записи администратора
    {
        if ($this->model::isAdmin()) {
            unset($_SESSION['user']);
        }
        redirect(ADMIN . '/user/login-admin');
    }

}