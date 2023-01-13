<?php


namespace app\models;


use PHPMailer\PHPMailer\PHPMailer;
use RedBeanPHP\R;
use app\controllers\CartController;
use wfm\App;

class Order extends AppModel
{

    public static function saveOrder($data): int|false // данный метод будет сохранять товар, и возвращает номер заказа (int) либо false
    {
        // тут мы используем механизм транзакций
        R::begin();
        try { // тут мы должны выполнять нужные SQL-запросы
            $order = R::dispense('orders'); // в таблицу orders мы будем сохранять заказы
            $order->user_id = $data['user_id']; // заполняем все данные которые нужно ввести в $order
            $order->note = $data['note']; // в колонку note попадёт данные из $data['note'],
            $order->total = $_SESSION['cart.sum'];
            $order->qty = $_SESSION['cart.qty'];
            $order_id = R::store($order); // получаем номер заказа и сохраняем данные из $order
            self::saveOrderProduct($order_id, $data['user_id']); // получаем $data['user_id'] из переданного аргумента из CartController в функцию saveOrder

            R::commit(); // данный метод будет сохранять продукты заказа
            return $order_id; // возвращаем номер заказа
        } catch (\Exception $e) { // в catch у нас будут попадать возможные ошибки
            R::rollback(); // если в try где-то будет ошибка то R::commit() не отработает потому что будет выброшено исключение которое мы поймаем в catch (\Exception $e) и с помощью R::rollback() мы откатим все запросы которые были выполнены в данный момент в рамках данной транзакций
            return false;
        }
    }

    public static function saveOrderProduct($order_id, $user_id) // ид товара, ид пользователя
    {
        // array_merge — Сливает один или большее количество массивов
        $sql_part = '';
        $binds = []; // тут мы будем записывать placeholder для постановки в SQL-запросе
        foreach ($_SESSION['cart'] as $product_id => $product) {
            // если цифровой товар
            if ($product['is_download']) {
                $download_id = R::getCell("SELECT download_id FROM product_download WHERE product_id = ?", [$product_id]); //получаем ид цифрового товара который прикреплен к данному продукту ($product_id)
                $order_download = R::xdispense('order_download');// Создаем объект, и передаем в какую таблицу нужно вводить данные, используем x перед dispense из-за того что в БД есть таблицы с '_' в названии

                $order_download->order_id = $order_id; // передаем данные которые нужно записать в колонку order_id
                $order_download->user_id = $user_id; // передаем $user_id в колонку user_id
                $order_download->product_id = $product_id;
                $order_download->download_id = $download_id;
                R::store($order_download); // мы сохраняем $order_download
            }

            // нужно выгрузить информацию о заказанном товаре в таблицу order_product
            $sum = $product['qty'] * $product['price'];
            $sql_part .= "(?,?,?,?,?,?,?),"; // это параметры нашего запроса(?), их должно быть ровно такое же кол-во как и таблиц в БД куда мы будем отправлять данные
            $binds = array_merge($binds, [$order_id, $product_id, $product['title'], $product['slug'], $product['qty'], $product['price'], $sum]); // Заменяем те "Вопросики" которые мы указали в $sql_part на данные которые нужно ввести. В $binds мы будем доза писывать каждый новый товар со значениями товара которые мы передали
        }
        $sql_part = rtrim($sql_part, ',');// после того как мы пробежимся в цикле у нас в $sql_part останется лишняя ',' тут мы ее будем удалять.

        // Методом exec в ReadBeanPHP мы можем выполнить произвольный SQL запрос
        R::exec("INSERT INTO order_product (order_id, product_id, title, slug, qty, price, sum) VALUES $sql_part", $binds); // Урок 38 part2 минута ~19.00 разбор данного sql запроса
    }

    public static function mailOrder($order_id, $user_email, $tpl): bool // в данном методе мы будем отправлять письма на email
        // $tpl это шаблон письма
    {
        //sprintf — Возвращает отформатированную строку

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP(); // здесь мы указываем что будем отправлять данные через SMTP
            $mail->SMTPDebug = 3; // устанавливаем уровень отладки, сможем их увидеть в Exception
            $mail->CharSet = 'UTF-8'; // устанавливаем кодировку
            $mail->Host = App::$app->getProperty('smtp_host');// берем из params настройки для соединения с почтовым сервисом
            $mail->SMTPAuth = App::$app->getProperty('smtp_auth');
            $mail->Username = App::$app->getProperty('smtp_username');
            $mail->Password = App::$app->getProperty('smtp_password');
            $mail->SMTPSecure = App::$app->getProperty('smtp_secure');
            $mail->Port = App::$app->getProperty('smtp_port'); // всё это мы взяли из params
            $mail->isHTML(true); // указываем если наше письмо будет поддерживать HTML, оно может быть просто текстовое письмо

            $mail->setFrom(App::$app->getProperty('smtp_from_email'), App::$app->getProperty('site_name')); // указываем первым аргументом от кого было отправлено данное письмо, а вторым аргументом можем указать какое-то текстовое сообщение
            $mail->addAddress($user_email); // доб адрес куда мы отправляем письмо
            $mail->Subject = sprintf(___('cart_checkout_mail_subject'), $order_id);// Тут находится тема письма. Вместо маркера(%d) в конце cart_checkout_mail_subject будет подставлен $order_id

            ob_start(); // включаем буферизацию
            require \APP . "/views/mail/{$tpl}.php"; // подключаем шаблоны
            $body = ob_get_clean(); // берем тот HTML который у нас получился

            $mail->Body = $body; // добавляем $body в свойство Body из PHPMailer
            return $mail->send(); // пытаемся отправить письмо
        } catch (\Exception $e) { // сюда попадут ошибки
//            debug($e,1);
            return false;
        }
    }
}