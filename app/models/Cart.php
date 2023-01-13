<?php

namespace app\models;


use RedBeanPHP\R;

class Cart extends AppModel
{

    public function get_product($id, $lang): array // мы принимаем ид продукта и язык на который должны вернуть продукт
    {
        return R::getRow("SELECT p.*, pd.* FROM product p JOIN product_description pd on p.id = pd.product_id WHERE p.status = 1 AND p.id = ? AND pd.language_id = ?", [$id, $lang['id']]);
        // вторым параметром функций getRow мы передали данные для которого мы поставили знак вопроса('?'), их нужно ставить в том порядке в котором указаны знаки вопроса; Мы получим товар и язык на который нужно вывести данный товар. Вся информация будет введенная в один массив
    }

    public function add_to_cart($product, $qty = 1) // 1 аргумент это продукт который нужно добавить, 2 это кол-во продуктов которое нужно добавить
    {
        $qty = abs($qty); // abs() вернет кол-во товара без знака '-'

        // Цифровой товар мы будем добавлять только один раз!
        // если это цифровой товар и он уже лежит в нашей корзине, мы больше добавлять этот товар не будем
        if ($product['is_download'] && isset($_SESSION['cart'][$product['id']])) {
            return false;
        }

        // если существует продукт в нашей корзине то мы добавляем его еще раз, тоесть если данного товара в корзине уже 3 их станет 4
        if (isset($_SESSION['cart'][$product['id']])) {
            $_SESSION['cart'][$product['id']]['qty'] += $qty;
        } else { // если мы попали сюда значит товар не попал в корзину
            if ($product['is_download']) {
                $qty = 1;
            }
            $_SESSION['cart'][$product['id']] = [
                'title' => $product['title'],
                'slug' => $product['slug'],
                'price' => $product['price'],
                'qty' => $qty, // это количество
                'img' => $product['img'],
                'is_download' => $product['is_download'],
            ];
        }

        $_SESSION['cart.qty'] = !empty($_SESSION['cart.qty']) ? $_SESSION['cart.qty'] + $qty : $qty;
        // если корзина пуста то мы запишем просто $qty а если там было что-то то мы прибавим $qty

        $_SESSION['cart.sum'] = !empty($_SESSION['cart.sum']) ? $_SESSION['cart.sum'] + $qty * $product['price'] : $qty * $product['price'];
        // Если у нас не пусто $_SESSION['cart.sum'] то мы прибавим $qty умноженное на $product['price'], а если пуст $_SESSION['cart.sum'] то запишем туда $qty * $product['price']
        return true;
    }

    public function delete_item($id)
    {
        // unset() удаляет перечисленные переменные.

        $qty_minus = $_SESSION['cart'][$id]['qty'];
        $sum_minus = $_SESSION['cart'][$id]['qty'] * $_SESSION['cart'][$id]['price'];
        $_SESSION['cart.qty'] -= $qty_minus;
        $_SESSION['cart.sum'] -= $sum_minus;
        unset($_SESSION['cart'][$id]);

    }

    public static function translate_cart($lang) // данный метод будет переводить продукты из корзины
    {
        if (empty($_SESSION['cart'])) {
            return; // тут мы просто выходим из данной функций
        }
        $ids = implode(',', array_keys($_SESSION['cart']));
        // вернет строку с разделителем ',' всех элементов массива: 1,2,5...

        // получим title и id активного языка
        $products = R::getAll("SELECT p.id, pd.title FROM product p JOIN product_description pd on p.id = pd.product_id WHERE p.id IN ($ids) AND pd.language_id = ?", [$lang['id']]);
        // Получим только p.id и pd.title из product и присоединяем pd по вот такому признаку: p.id = pd.product_id где p.id есть в $ids и pd.language_id есть в массиве $lang

//        debug($products);

        foreach ($products as $product) {
            $_SESSION['cart'][$product['id']]['title'] = $product['title'];
        }

    }



}

/*Array
(
    [product_id] => Array
        (
            [qty] => QTY
            [title] => TITLE
            [price] => PRICE
            [img] => IMG
        )
    [product_id] => Array
        (
            [qty] => QTY
            [title] => TITLE
            [price] => PRICE
            [img] => IMG
        )
    )
    [cart.qty] => QTY,
    [cart.sum] => SUM
*/



/* Это попадёт в $products от SQL запроса от метода translate_cart(активный язык)
    Array
(
    [0] => Array
        (
            [id] => 2
            [title] => Apple cinema 30"
        )

    [1] => Array
        (
            [id] => 5
            [title] => Digital product
        )

)
*/