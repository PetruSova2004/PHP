<?php

namespace app\controllers;

use app\models\AppModel;
use app\models\Wishlist;
use app\widgets\language\Language;
use RedBeanPHP\R;
use wfm\App;
use wfm\Controller;

class AppController extends Controller
{

    public function __construct($route)  // в $route попадает путь с которым сервер нашел соответствие
    {
        parent::__construct($route);
        new AppModel();

        App::$app->setProperty('languages', Language::getLanguages()); // хранится массив языков
        App::$app->setProperty('language', Language::getLanguage(App::$app->getProperty('languages'))); // тут хранится активный язык

        $lang = App::$app->getProperty('language');
        \wfm\Language::load($lang['code'], $this->route);
//        debug(\wfm\Language::$lang_data);

        $categories = R::getAssoc("SELECT c.*, cd.* FROM category c 
                        JOIN category_description cd 
                        ON c.id = cd.category_id
                        WHERE cd.language_id = ?", [$lang['id']]);
        // получаем через SQL запрос все данные из c, cd с учетом их id и языка
//        debug($categories);

        App::$app->setProperty("categories_{$lang['code']}", $categories); // мы записываем данные в контейнер, тут будет хранится вся информация о $categories и мы сожем к ней обратится через categories_{$lang['code']}.

        App::$app->setProperty('wishlist', Wishlist::get_wishlist_ids()); // запишем в контейнер под ключём wishlist id товаров которые попали в избранное.
//        debug(App::$app->getProperty('wishlist'));
    }

}