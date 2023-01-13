<?php

namespace app\controllers;

use app\models\Page;
use wfm\App;

/** @property Page $model */
class PageController extends AppController
//compact — Создаёт массив, содержащий названия переменных и их значения
{
    public function viewAction()
    {
        $lang = App::$app->getProperty('language');
        $page = $this->model->get_page($this->route['slug'], $lang); // берем данные об страницах из футера
//        debug($page, 1);

        if (!$page) { // если ничего не попало в $page тогда выведем 404
            $this->error_404();
            return;
        }

        $this->setMeta($page['title'], $page['description'], $page['keywords']);
        $this->set(compact('page'));
    }

}