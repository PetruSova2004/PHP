<?php


namespace app\models;

use RedBeanPHP\R;
use wfm\Model;

class AppModel extends Model
{

    public static function create_slug($table, $field, $str, $id): string // Принимаем: название таблицы; название поля; строка названий категории; ид записи
    {
        $str = self::str2url($str); // переводим строку
        $res = R::findOne($table, "$field = ?", [$str]);
        if ($res) {
            $str = "{$str}-{$id}"; // к найденному slug мы добавляем ид категории чтобы мы могли видеть если они одинаковы и отличать их по ид
            $res = R::count($table, "$field = ?", [$str]);
            if ($res) {
                $str = self::create_slug($table, $field, $str, $id); // делаем slug уникальный
            }
        }
        return $str;
    }

    public static function str2url($str): string // данная функция принимает строку и переводит её
    {
        // strtolower() — Преобразует строку в нижний регистр
        // preg_replace() — Выполняет поиск и замену по регулярному выражению

        // переводим в транслит
        $str = self::rus2translit($str);
        // приводим всё к нижнему регистру
        $str = strtolower($str);
        // заменяем все ненужное нам на "-" в $str
        $str = preg_replace('~[^-a-z0-9]+~u', '-', $str);
        // удаляем начальные и конечные '-'
        $str = trim($str, "-");
        return $str;
    }

    public static function rus2translit($string): string
    {

        $converter = array(

            'а' => 'a', 'б' => 'b', 'в' => 'v',

            'г' => 'g', 'д' => 'd', 'е' => 'e',

            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',

            'и' => 'i', 'й' => 'y', 'к' => 'k',

            'л' => 'l', 'м' => 'm', 'н' => 'n',

            'о' => 'o', 'п' => 'p', 'р' => 'r',

            'с' => 's', 'т' => 't', 'у' => 'u',

            'ф' => 'f', 'х' => 'h', 'ц' => 'c',

            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',

            'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',

            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',


            'А' => 'A', 'Б' => 'B', 'В' => 'V',

            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',

            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',

            'И' => 'I', 'Й' => 'Y', 'К' => 'K',

            'Л' => 'L', 'М' => 'M', 'Н' => 'N',

            'О' => 'O', 'П' => 'P', 'Р' => 'R',

            'С' => 'S', 'Т' => 'T', 'У' => 'U',

            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',

            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',

            'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',

            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

        );

        // strtr — Преобразует заданные символы или заменяет подстроки
        return strtr($string, $converter);

    }

}