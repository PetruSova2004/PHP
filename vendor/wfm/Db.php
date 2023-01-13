<?php


namespace wfm;

use RedBeanPHP\R;

// RedBeanPHP — это простой в использовании инструмент ORM для PHP.
//Автоматически создает таблицы и столбцы по ходу работы.
//Никаких настроек, просто выстрелил и забыл.
//Никаких сложных инструментов для работы с пакетами, никаких автозагрузчиков, только ОДИН файл.

class Db
{

    use TSingleton; // использование данного трейта позволяет нам создавать только один обьект данного класса

    private function __construct()
    {
        $db = require_once CONFIG . '/config_db.php';
        R::setup($db['dsn'], $db['user'], $db['password']); // с помошью библиотеки Redbean мы используем ее функцию setup() и подключаем базу данных
        if (!R::testConnection()) {  // testConnection() возврщ true или false если поключение утсановленo/не устнановлено
            throw new \Exception('No connection to DB', 500);
        }
        R::freeze(true); // мы останавливаем изменение базы данных на лету
        if (DEBUG) {
            R::debug(true, 3); // debug() собирает в массив sql-запросы возврашает запросы которые он будет выполнять
        }
        R::ext('xdispense', function( $type ){ // эта функция нужна если для метода dispense если в БД есть таблицы с '_' в названии
            return R::getRedBean()->dispense( $type );
        });
    }

}