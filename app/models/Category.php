<?php

namespace app\models;

use RedBeanPHP\R;
use wfm\App;

class Category extends AppModel
{
    public function get_category($slug, $lang): array
    {
        return R::getRow("SELECT c.*, cd.* FROM category c JOIN category_description cd on c.id = cd.category_id WHERE c.slug = ? AND cd.language_id = ?", [$slug, $lang['id']]);
    }

    public function getIds($id)
    {
        $lang = App::$app->getProperty('language')['code'];
        $categories = App::$app->getProperty("categories_{$lang}");
        $ids = ''; // тут мы будем хранить id которые были перечисленны через ',' потом мы эти id подставим в SQL запрос через оператор IN
        foreach ($categories as $k => $v) { // $к будет id категорий а $v данные о ней
            if ($v['parent_id'] == $id) { // тут мы проверяем кто наследуется от $id
                $ids .= $k . ',';
                $ids .= $this->getIds($k); // рекурсивно вызываем функцию и передаём новый id, мы будем искать если у данного $k есть дочерние элементы
            }
        }
        return $ids;
    }

    public function get_products($ids, $lang, $start, $perpage): array
    {
        $sort_values = [
            'title_asc' => 'ORDER BY title ASC',
            'title_desc' => 'ORDER BY title DESC',
            'price_asc' => 'ORDER BY price ASC',
            'price_desc' => 'ORDER BY price DESC',
        ];
        $order_by = '';
        if (isset($_GET['sort']) && array_key_exists($_GET['sort'], $sort_values)) { // проверяем если в адресной строке будет ключ 'sort' и значение $_GET['sort'] будет в нашем массиве $sort_values. Если мы проходим данную проверку значит мы в $order_by запишем значение из массива $sort_values по ключу $_GET['sort']
            $order_by = $sort_values[$_GET['sort']];
        }

        return R::getAll("SELECT p.*, pd.* FROM product p JOIN product_description pd on p.id = pd.product_id WHERE p.status = 1 AND p.category_id IN ($ids) AND pd.language_id = ? $order_by LIMIT $start, $perpage", [$lang['id']]);
    }

    public function get_count_products($ids): int // получаем сколько товаров данной категорий существует
    {
        return R::count('product', "category_id IN ($ids) AND status = 1"); // SELECT count(*) FROM product WHERE category_id IN ($ids)
    }

}