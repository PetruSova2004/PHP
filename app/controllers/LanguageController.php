<?php

namespace app\controllers;

use app\models\Cart;
use wfm\App;

class LanguageController extends AppController  // здесь мы переключаем языки
{

    public function changeAction()
    {
        $lang = get('lang', 's');  // получаем то что попало в lang
        if ($lang) {
            if (array_key_exists($lang, App::$app->getProperty('languages'))) {
                // отрезаем базоый URL
                $url = trim(str_replace(PATH, '', $_SERVER['HTTP_REFERER']), '/');

                // разбиваем на 2 части... 1-я часть - возможный бывший язык
                $url_parts = explode('/', $url, 2); // по разделителю '/' мы разбиваем $url на 2 части: [0]=>'en' [1]=>'product/apple'

                // ищем первую часть (бывший язык) в массиве языков
                if (array_key_exists($url_parts[0], App::$app->getProperty('languages'))) {
                    // присваиваем первой части новый язык, если он не является базовым
                    if ($lang != App::$app->getProperty('language')['code']) {
                        $url_parts[0] = $lang;
                    } else {
                        // если это базовый язык - удалим язык из URL
                        //  array_shift — Извлекает первый элемент массива
                        // array_unshift — Добавляет один или несколько элементов в начало массива
                        array_shift($url_parts);
                    }
                } else {
                    // присваиваем первой части новый язык, если он не является базовым
                    if ($lang != App::$app->getProperty('language')['code']) {
                        array_unshift($url_parts, $lang);
                        // мы в $url_parts добавляем код языка($lang)
                    }
                }

                Cart::translate_cart(App::$app->getProperty('languages')[$lang]);
//                Cart::translate_cart(App::$app->getProperty('languages')[$lang]); // из всех языков мы берем только $lang

                $url = PATH . '/' . implode('/', $url_parts);
                redirect($url);
            }
        }
        redirect();
    }
}