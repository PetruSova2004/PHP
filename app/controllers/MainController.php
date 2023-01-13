<?php

namespace app\controllers;


use app\models\Main;
use RedBeanPHP\R;
use wfm\App;
use wfm\Cache;
use wfm\Language;


/** @property Main $model */
class MainController extends AppController  // данный класс отвечает за главную страницу
{

    public function indexAction()
    {
        $lang = App::$app->getProperty('language');
        $slides = R::findAll('slider');

        $products = $this->model->get_hits($lang, 6); // тут мы передаем информацию о товаре($lang) и сколько товаров нужно вывести в хиты

        $this->set(compact('slides', 'products'));
        $this->setMeta(___('main_index_meta_title'), ___('main_index_meta_description'), ___('main_index_meta_keywords'));
    }

}