<?php


namespace app\controllers\admin;


use app\models\admin\User;
use app\models\AppModel;
use app\widgets\language\Language;
use RedBeanPHP\R;
use wfm\App;
use wfm\Controller;

class AppController extends Controller
{

    public false|string $layout = 'admin'; // тут мы указываем что будем использовать шаблон 'admin';

    public function __construct($route)
    {
        parent::__construct($route);

        if (!User::isAdmin() && $route['action'] != 'login-admin') {
            // если пользователь не авторизован как администратор и action из маршрута не равен login-admin, тогда мы делаем редирект на страницу авторизации админа

            redirect(ADMIN . '/user/login-admin');
        }

        new AppModel();
        App::$app->setProperty('languages', Language::getLanguages()); // кладём в контейнер список доступных языков
        App::$app->setProperty('language', Language::getLanguage(App::$app->getProperty('languages'))); // записываем активный язык для админки

        $lang = App::$app->getProperty('language');
        $categories = R::getAssoc("SELECT c.*, cd.* FROM category c 
                        JOIN category_description cd
                        ON c.id = cd.category_id
                        WHERE cd.language_id = ?", [$lang['id']]);
        App::$app->setProperty("categories_{$lang['code']}", $categories);
//        debug($categories);
    }

}