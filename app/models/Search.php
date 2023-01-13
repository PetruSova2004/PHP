<?php

namespace app\models;

use RedBeanPHP\R;

class Search extends AppModel
{

    public function get_count_find_products($s, $lang) // тут мы получаем кол-во найденных продуктов, передаем поисковый запрос и активный язык
    {
        return R::getCell("SELECT COUNT(*) FROM product p JOIN product_description pd on p.id = pd.product_id WHERE p.status = 1 AND pd.language_id = ? AND pd.title LIKE ?", [$lang['id'], "%{$s}%"]);
        // Благодаря оператору LIKE мы можем искать по части строки, с помощью такой записи "%{$s}%" мы говорим что $s может быть в середине строки, тоесть находит любые значения, которые имеют «$s» в любой позиции
    }

    public function get_find_products($s, $lang, $start, $perpage): array // данный метод будет возвращать сами продукты
    {
        return R::getAll("SELECT p.*, pd.* FROM product p JOIN product_description pd ON p.id = pd.product_id WHERE p.status = 1 AND pd.language_id = ? AND pd.title LIKE ? LIMIT $start, $perpage", [$lang['id'], "%{$s}%"]);
    }

}