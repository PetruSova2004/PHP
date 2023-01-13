<?php


namespace wfm;

// Модель — постоянное хранилище данных, которые применяются во всей структуре. Она обеспечивает доступ к информации для просмотра, записи, отбора. Также Model называют мостом между View и Controller.


use Valitron\Validator;
use RedBeanPHP\R;

abstract class Model
{

    public array $attributes = []; // в данном массиве будут попадать все безопасные данные которые пользователь ввел на сайте через какие-то формы
    public array $errors = []; //тут будут попадать возможные ошибки которые будут возникать при валидации данных
    public array $rules = []; // это будет массив правил валидации
    public array $labels = []; // это свойство нам потребуется для того что бы указывать какое именно поле не прошло валидацию

    public function __construct()
    {
        Db::getInstance();
    }

    public function load($post = true)
    {
        $data = $post ? $_POST : $_GET; // если $post = true тогда мы берём данные из $_POST, в противном случае из $_GET
        foreach ($this->attributes as $name => $value) // проходимся по массиву attributes, берем ключ и значение
        {
            if (isset($data[$name])) { // проверяем если в массиве $data есть ключ $name
                $this->attributes[$name] = $data[$name]; // присваиваем attributes[$name] то что пользователь ввел в $data[$name]
            }
        }
    }

    public function validate($data): bool // данный метод будет производить валидацию данных
    {
        Validator::langDir(APP . '/languages/validator/lang'); // указываем где находится папка с языковыми файлами
        Validator::lang(App::$app->getProperty('language')['code']); // указываем какой из языковых файлах использовать, сюда попадёт ru или en в зависимости от активного языка
        $validator = new Validator($data); // аргумент это передаваемые данные
        $validator->rules($this->rules); // для объекта класса Validator передаем правила валидации из User
        $validator->labels($this->getLabels()); // получаем переводные фразы
        if ($validator->validate()) { // валидация будет пройдена(вернёт true)
            return true;
        } else {
            $this->errors = $validator->errors(); // тут мы получаем ошибки которые могут быть при валидации
//            debug($this->errors);
            return false;
        }
    }

    public function getErrors() // данный метод будет показывать ошибки
    {
        $errors = '<ul>';
        foreach ($this->errors as $error) { // проходимся по каждой ошибки
            foreach ($error as $item) { // проходимся по каждом сообщению об ошибке
                $errors .= "<li>{$item}</li>";
            }
        }
        $errors .= '</ul>';
        $_SESSION['errors'] = $errors; // записываем ошибки в сессию, записав их в сессию мы сможем их показать в шаблоне ishop.php
    }

    public function getLabels(): array // тут мы получаем переводные фразы / $labels
    {
        $labels = [];
        foreach ($this->labels as $k => $v) { // проходимся в цикле по массиву labels из User
            $labels[$k] = ___($v); // тут записываем в labels по ключу $k переводную фразу($v)
        }
        return $labels;
    }

    public function save($table): int|string // данная функция будет сохранять данные о пользователе в БД; $table это таблица в которой будем сохранять данные; вернет int id записи или string ошибку
    { // https://redbeanphp.com/index.php?p=/crud - тут можно прочитать если я тупой и не понимаю
        // Компоненты используются для переноса данных из базы данных и в нее.
        $tbl = R::dispense($table); // Создаем объект, и передаем в какую таблицу нужно вводить данные
        foreach ($this->attributes as $name => $value) { // attributes из User
            if ($value != '') {
                $tbl->$name = $value; // указываем свойство для объекта $tbl с нужными значениями; Для каждого свойства добавляем значение: email => n@mai.ru, password => ****** etc.
            }
        }
        return R::store($tbl); // сохраняем объект, тоесть мы добавляем $tbl в БД. Эта функция возвращает идентификатор первичного ключа вставленного компонента, тоесть ид добавленной записи
    }

    public function update($table, $id): int|string // $table это таблица которую нужно обновить по $id
    {
        // Чтобы загрузить компонент из базы данных, используйте функцию load() и передайте как тип компонента, так и идентификатор
        $tbl = R::load($table, $id); // получаем запись которую нужно обновить, и загружаем данную запись по id из указанной таблицы
        foreach ($this->attributes as $name => $value) {
            if ($value != '') { // обновляем данные
                $tbl->$name = $value;
            }
        }
        return R::store($tbl); // сохраняем указанный bean
    }
}