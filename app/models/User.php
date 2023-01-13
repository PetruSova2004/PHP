<?php

namespace app\models;

use RedBeanPHP\R;

class User extends AppModel
{
    public array $attributes = [
        'email' => '',
        'password' => '',
        'name' => '',
        'address' => '',
    ];

    public array $rules = [ // тут мы храним правила валидации данных которые получаем от пользователя, если одно из правил ниже не пройдет валидацию мы получим ошибку.

        // required- Поле, обязательное для заполнения
        // email- Действующий электронный адрес
        // lengthMin- Строка должна быть больше заданной длины

        'required' => ['email', 'password', 'name', 'address',], // валидатор проверит чтобы все эти поля были заполнены, в противном случае вернёт false
        'email' => ['email',], // данное поле должно пройти валидацию на email адрес
        'lengthMin' => [
            ['password', 6], // строка password должна быть больше 6 символов
        ],
        'optional' => ['email', 'password'] // если эти данные отсутствуют при заполнении формы тогда они не будут проходить через валидацию, в противном случае они будут проходить её, мы это делаем если не хотим менять эти данные
    ];

    public array $labels = [ // указываем как будет называться для ошибок эти $labels
        'email' => 'tpl_signup_email_input',
        'password' => 'tpl_signup_password_input',
        'name' => 'tpl_signup_name_input',
        'address' => 'tpl_signup_address_input',
    ];

    public static function checkAuth(): bool
    {
        return isset($_SESSION['user']);
    }

    public function checkUnique($text_error = ''): bool // проверяем уникальность вводных данных при регистрации
    {
        $user = R::findOne('user', 'email = ?', [$this->attributes['email']]); // для таблицы user мы ищем email где он равен значению $this->attributes['email']
         if ($user) { // если мы нашли совпадение тогда вернём false
             $this->errors['unique'][] = $text_error ?: ___('user_signup_error_email_unique'); // если у нас $text_error было передано тогда мы берем его, а в противном случае используем переводную фразу
             return false;
         } else { // выдает true когда данные не повторяются
             return true;
         }
    }

    public function login($is_admin = false): bool // данный метод нужен для регистраций пользователей и админа
    {
        $email = post('email'); // берем из массива $_POST данные по ключу email
        $password = post('password');
        if ($email && $password) {
            if ($is_admin) { // если это админ тогда у него будет роль администратора
                $user = R::findOne('user', "email = ? AND role = 'admin'", [$email]);
            } else { // в таблице user ищем чтобы email был равен $email
                $user = R::findOne('user', "email = ?", [$email]);
            }

            if ($user) { // если мы получили user из БД тогда мы должны проверить корректность пароля
                if (password_verify($password, $user->password)) {
                    // если $password соответствует паролю который мы получили от метода findOne который вернул нам объект с данными о user с соответствующим email, тогда мы должны поместить в сессию все данные о пользователе.

                    foreach ($user as $k => $v) { // в $k попадает: email, password, name, address...
                        if (!$k != 'password') { // нам не нужно вводить пароль в сессию, он будет проигнорирован
                            $_SESSION['user'][$k] = $v;
                        }
                    }
                    return true; // если пароль прошел верификацию
                }
            }
        }
        return false;
    }

    public function get_count_orders($user_id): int // берем общее кол-во заказов пользователя
    {
        return R::count('orders', 'user_id = ?', [$user_id]);
    }

    public function get_user_orders($start, $perpage, $user_id): array // получаем заказы пользователя
    {
        return R::getAll("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT $start, $perpage", [$user_id]);
    }

    public function get_user_order($id): array
    {
        return R::getAll("SELECT o.*, op.* FROM orders o JOIN order_product op on o.id = op.order_id WHERE o.id = ?", [$id]);
    }

    public function get_count_files(): int // берем общее количество цифровых файлов
    {
        return R::count('order_download', 'user_id = ? AND status = 1', [$_SESSION['user']['id']]);
    }

    public function get_user_files($start, $perpage, $lang): array // получаем файлы, которые заказал пользователь
    {
        return R::getAll("SELECT od.*, d.*, dd.* FROM order_download od JOIN download d on d.id = od.download_id JOIN download_description dd on d.id = dd.download_id WHERE od.user_id = ? AND od.status = 1 AND dd.language_id = ? LIMIT $start, $perpage", [$_SESSION['user']['id'], $lang['id']]);
    }

    public function get_user_file($id, $lang): array // получаем один файл по $id который выбрал пользователь
    {
        return R::getRow("SELECT od.*, d.*, dd.* FROM order_download od JOIN download d on d.id = od.download_id JOIN download_description dd on d.id = dd.download_id WHERE od.user_id = ? AND od.status = 1 AND od.download_id = ? AND dd.language_id = ?", [$_SESSION['user']['id'], $id, $lang['id']]);
    }

}