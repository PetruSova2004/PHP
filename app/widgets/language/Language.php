<?php

namespace app\widgets\language;

use RedBeanPHP\R;
use wfm\App;

class Language
{

    protected $tpl; // в $tpl будет хранится шаблон который будет реализовывать данный widget(какой-то html код, тоесть внешний вид)
    protected $languages; // в $languages будем хранить все имеющийся языки
    protected $language; // в $language будем хранить активный язык который выбрал пользователь

    public function __construct()
    {
        $this->tpl = __DIR__ . '/lang_tpl.php';
        $this->run();
    }

    protected function run() // данный метод будет получать $languages и $language
    {
        $this->languages = App::$app->getProperty('languages');
        $this->language = App::$app->getProperty('language');
        echo $this->getHtml();
    }

    public static function getLanguages(): array
    {
        // Метод R::getAssoc возвращает ассоциативный массив, в данном случае ключем будет code, а всё остальное пойдет в качестве значения
        return R::getAssoc("SELECT code, title, base, id FROM language ORDER BY base DESC");
    }

    public static function getLanguage($languages) // данный метод будет получать от пользователя желаемый язык и записывать его в $language
    {
        // key() берет текущий ключ массива

        $lang = App::$app->getProperty('lang');
        // array_key_exists в данном случае проверяет если сушествует ключ $lang в массиве $languages
        if ($lang && array_key_exists($lang, $languages)) { // тут мы проверяем если передан код языка и он существует в списке доступных языков
            $key = $lang;
        } elseif (!$lang) { // тут мы попадаем если язык не передан
            $key = key($languages); // тут мы заберем из массива $languages первый элемент который должен быть базовым языком
        } else {
            $lang = h($lang);
            throw new \Exception("Not found language {$lang}", 404);
        }

//        var_dump($key);  // expected: string(2) "ru" / "en"

        $lang_info = $languages[$key];
        $lang_info['code'] = $key;
        return $lang_info;
    }

    protected function getHtml(): string
    {
        ob_start();
        require_once $this->tpl;
        return ob_get_clean();
    }

}