<?php


namespace app\models;


use RedBeanPHP\R;

class Wishlist extends AppModel
{

    public function get_product($id): array|null|string
    {
        return R::getCell("SELECT id FROM product WHERE status = 1 AND id = ?", [$id]);
    }

    public function add_to_wishlist($id) // тут мы добавляем товар в избранное
    {
        // array_shift() извлекает первое значение массива array и возвращает его, сокращая размер array на один элемент.
        // implode() — Объединяет элементы массива в строку
        $wishlist = self::get_wishlist_ids(); // сюда нам придёт массив с ид которые уже в избранном
        if (!$wishlist) { // если $wishlist пустой тогда мы должны добавить туда текущий id товара в куки, тоесть записать товар в куки
            setcookie('wishlist', $id, time() + 3600 * 24 * 30, '/'); // куки у нас называются wishlist, мы добавляем туда $id, сколько мы будем хранить(30 дней), делаем это для всего домена
        } else {
            if (!in_array($id, $wishlist)) { // если мы добавляем не первый товар то мы должны проверить а сколько у нас там уже товаров
                if (count($wishlist) > 5) {
                    array_shift($wishlist); // убираем первый элемент массива
                }
                $wishlist[] = $id; // добавляем $id в конец массива
                $wishlist = implode(',', $wishlist); // Объединяем данные массива в строку
                setcookie('wishlist', $wishlist, time() + 3600 * 24 * 30, '/');
            }
        }
    }

    public static function get_wishlist_ids(): array //тут мы будем получать товары которые уже находятся в избранном
    {
        // array_slice — Выбирает срез массива
        $wishlist = $_COOKIE['wishlist'] ?? ''; // если есть что-то в куках тогда мы берем те данные в противном случае запишем туда пустую строку
        if ($wishlist) { // мы взяли что-то из куках и мы ожидаем что там у нас будет строка с ids через запятую ','.
            $wishlist = explode(',', $wishlist); // разбиваем строку на массив через separator ','.
        }
        if (is_array($wishlist)) {
            $wishlist = array_slice($wishlist, 0, 6); // выбираем элементы массива с 0 по 6
            $wishlist = array_map('intval', $wishlist); // каждый элемент массива будет приведен к числу
            return $wishlist; // возвращаем массив с ids;
        }
        return []; // если мы не попали ни в одну из выше условий тогда вернём пустой массив
    }

    public function get_wish_products($lang): array // данная функция будет по id получать товары из избранного
    {
        $wishlist = self::get_wishlist_ids();
        if ($wishlist) {
            $wishlist = implode(',', $wishlist); // получаем строку с ids из $wishlist
            return R::getAll("SELECT p.*, pd.* FROM product p JOIN product_description pd on p.id = pd.product_id WHERE p.status = 1 AND p.id IN ($wishlist) AND pd.language_id = ? LIMIT 6", [$lang['id']]);
        }
        return [];
    }

    public function delete_from_wishlist($id): bool
    {
        // unset — Удаляет переменную
        // implode — Соединяет элементы массива строкой

        $wishlist = self::get_wishlist_ids(); // получаем массив id
        $key = array_search($id, $wishlist); // ищем первый попавшийся $id в массиве $wishlist
        if (false !== $key) { // если $key существует
            unset($wishlist[$key]); // удаляем товар по ключу $key
            if ($wishlist) { // если в массиве остались элементы после удаления $key тогда мы вернём данный массив без $key
                $wishlist = implode(',', $wishlist); // соединяем массив в строку
                setcookie('wishlist', $wishlist, time() + 3600 * 24 * 30, '/');
            } else { // если мы удалили последний товар тогда мы удаляем и куку
                setcookie('wishlist', '', time() - 3600, '/');
            }
            return true;
        }
        return false;
    }
}