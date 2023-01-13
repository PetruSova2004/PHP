<?php

namespace wfm;

class Language
{
    // массив со всеми переводными фразами страницы(шаблона и вида)
    public static array $lang_data = [];
    // массив с переводными фразами шаблона
    public static array $lang_layout = [];
    // массив с переводными фразами вида
    public static array $lang_view = [];

    public static function load($code, $view) // данная функция будет загружать переводные фразы в массивы.  Мы передаем код языка, и вид
    {
        $lang_layout = APP . "/languages/{$code}.php"; // это путь к файлу шаблона
        $lang_view = APP . "/languages/{$code}/{$view['controller']}/{$view['action']}.php"; // это путь к файлу вида
        if (file_exists($lang_layout)) {
            self::$lang_layout = require_once $lang_layout;
        }
        if (file_exists($lang_view)) {
            self::$lang_view = require_once $lang_view;
        }
        // array_merge — Сливает один или большее количество массивов
        self::$lang_data = array_merge(self::$lang_layout, self::$lang_view); // мы сливаем в один массив $lang_layout и $lang_view



    }

    public static function get($key) // данная функция по ключу возвращать переводную фразу
    {
        return self::$lang_data[$key] ?? $key;
    }
}