<?php

namespace app\models;

use wfm\App;

class Breadcrumbs extends AppModel
{

    public static function getBreadcrumbs($category_id, $name = ''): string // $name нужен для того чтобы продолжить ,,Хлебные Крошки,,(в нём можно указать где мы находимся)
    {
        $lang = App::$app->getProperty('language')['code'];
        $categories = App::$app->getProperty("categories_{$lang}"); // тут попадает все данные о всех категорий из БД по выбранному языку в $lang которые мы взяли из sql запроса из ProductController
//        debug($categories, 1);
        $breadcrumbs_array = self::getParts($categories, $category_id); // тут попадают 'Хлебные Крошки' которые мы ниже выведем на экран, мы передаём все данные о категориях и ид категорий выбранного товара
        $breadcrumbs = "<li class='breadcrumb-item'><a href='" . base_url() . "'>" . ___ ('tpl_home_breadcrumbs') . "</a></li>"; // тут мы выводим ссылку на Главную страницу
        if ($breadcrumbs_array) { // тут мы выводим на экран цепочку "Хлебных Крошек" если они true
            foreach ($breadcrumbs_array as $slug => $title) {
                // $slug это ссылка на страницу на которую пойдёт запрос при нажатий(noutbuki) а $title это название дочерних элементов которые мы выводим(Notebooks/Mac к примеру) 'Урок 20 мин 15' если забыл
                $breadcrumbs .= "<li class='breadcrumb-item'><a href='category/{$slug}'>{$title}</a></li>";
            }
        }
        if ($name) { // $name это название данного товара который мы сейчас смотрим(iMac к примеру)
            $breadcrumbs .= "<li class='breadcrumb-item active'>$name</li>";
        }
        return $breadcrumbs;

    }

    public static function getParts($cats, $id): array|false // принимаем категории и ид, возврашаем те самые Хлебные Крошки
    {
        if (!$id) {
            return false;
        }
        $breadcrumbs = []; // тут мы будем складывать данные о "Хлебных Крошек"
        foreach ($cats as $k => $v) { // проходимся по всем элементам массива $cats
            if (isset($cats[$id])) { // проверяем если существует категория с переданным ид
                $breadcrumbs[$cats[$id]['slug']] = $cats[$id]['title']; // мы записываем в 'slug' из $breadcrumbs 'tile' из $cats[$id]
                $id = $cats[$id]['parent_id'];
            } else {
                break;
            }
        }
        return array_reverse($breadcrumbs, true); // переворачиваем массив для того чтобы родительский элемент из 'Хлебных Крошек' шёл первым
    }

}