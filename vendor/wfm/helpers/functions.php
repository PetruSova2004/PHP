<?php

function debug($data, $die = false)
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
    if ($die) {
        die;
    }
}

function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES); // ENT_QUOTES Преобразует как двойные, так и одинарные кавычки.
}

function redirect($http = false)
{
    if ($http) {
        $redirect = $http;
    } else {
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PATH; // тут мы проверяем если в $_SERVER существует адрес с которого пришел пользователь а в противном случае мы отправим его на главную страницу
    }
    header("Location: $redirect"); // header() используется для отправки HTTP-заголовка.
    die();

}

function base_url()
    // данная функция будет сохранять язык который мы выбрали при переходе на другую страницу
{
    return PATH . '/' . (\wfm\App::$app->getProperty('lang') ? \wfm\App::$app->getProperty('lang') . '/' : '');
    // мы проверяем если 'lang' существует то мы его прикрепляем к нашему URL адресу если его нет тогда мы добавляем пустую строку
    // 'lang' это часть URL адреса который появляется при выборе языков("en")
}

/**
 * @param string $key Key of GET array
 * @param string $type Values 'i', 'f', 's'
 * @return float|int|string
 */
function get($key, $type = 'i')
{
    $param = $key;
    $$param = $_GET[$param] ?? '';  // мы получаем который находится в массиве $_GET по ключу
    // $$ - переменная переменной, тоесть в $_GET[$param] попадет то что мы передадим в $key
    if ($type == 'i') {
        return(int)$$param; // мы вернем типа integer то что находится в $$param
    } elseif ($type == 'f') {
        return(float)$$param;
    } else {
        return trim($$param);
    }
}

/**
 * @param string $key Key of POST array
 * @param string $type Values 'i', 'f', 's'
 * @return float|int|string
 */
function post($key, $type = 's')
{
    $param = $key;
    $$param = $_POST[$param] ?? '';
    if ($type == 'i') {
        return (int)$$param;
    } elseif ($type == 'f') {
        return (float)$$param;
    } else {
        return trim($$param);
    }
}

function __($key)
{
    echo \wfm\Language::get($key);
}

function ___($key)
{
    return \wfm\Language::get($key);
}

function get_cart_icon($id)
{ // array_key_exists проверяет если 1-ый параметр есть в массиве второго параметра
    if (!empty($_SESSION['cart']) && array_key_exists($id, $_SESSION['cart'])) { // если $id есть в $_SESSION['cart']
        $icon = '<i class="fas fa-luggage-cart"></i>';
    } else {
        $icon = '<i class="fas fa-shopping-cart"></i>';
    }
    return $icon;
}

function get_field_value($name) // данная функция нужна для того чтобы не потерять данные из input при обновлений
{
    return isset($_SESSION['form_data'][$name]) ? h($_SESSION['form_data'][$name]) : ''; // если в form_data существует такой ключ как $name тогда мы вернём данное значение и обработаем ее фунцией h() а если там ничего нет то вернем пустую строку
}

function get_field_array_value($name, $key, $index) // Тут мы будем сохранять данные формы;$name это будет то что попало в category_description, $key - id языка, $index - title
{
    return isset($_SESSION['form_data'][$name][$key][$index]) ? h($_SESSION['form_data'][$name][$key][$index]) : '';
}