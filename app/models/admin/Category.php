<?php


namespace app\models\admin;


use app\models\AppModel;
use RedBeanPHP\R;
use wfm\App;

class Category extends AppModel
{

    public function category_validate(): bool // данный метод будет валидировать добавление категории
    {
        $errors = '';
        // в $_POST['category_description'] попадут данные которые ввел админ при добавлении категории
        foreach ($_POST['category_description'] as $lang_id => $item) {
            $item['title'] = trim($item['title']);
            if (empty($item['title'])) { // нам нужно чтобы title быть заполнен в обоих языках, другие поля могут быть пустыми
                $errors .= "Не заполнено Наименование во вкладке {$lang_id}<br>";
            }
        }
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST; // сохраняем данные из форм если они не прошли валидацию
            return false;
        }

        return true;
    }

    public function save_category(): bool
    {
        $lang = App::$app->getProperty('language')['id']; // берем ид активного языка
        R::begin(); // используем механизм транзакций
        try {
            $category = R::dispense('category'); // указываем куда будем сохранять данные
            $category->parent_id = post('parent_id', 'i');
            $category_id = R::store($category); // метод store возвращает ид сохранённой записи
            $category->slug = AppModel::create_slug('category', 'slug', $_POST['category_description'][$lang]['title'], $category_id); // передаем slug в БД и с помощью create_slug мы переводим фразу который ввел админ в title И проверяем чтобы этот slug был уникальным.
            R::store($category);

            foreach ($_POST['category_description'] as $lang_id => $item) {
                R::exec("INSERT INTO category_description (category_id, language_id, title, description, keywords, content) VALUES (?,?,?,?,?,?)", [ // вставляем в category_description параметры
                    $category_id,
                    $lang_id,
                    $item['title'], // получаем два экземпляра: один на русском другой на англ
                    $item['description'],
                    $item['keywords'],
                    $item['content'],
                ]);
            }
            R::commit(); // делаем комит в БД
            return true;
        } catch (\Exception $e) {
            R::rollback(); // откатим транзакцию если в try возникла ошибка
            return false;
        }
    }

    public function update_category($id): bool // принимаем ид категории которую нужно изменить
    {
        R::begin();
        try {
            $category = R::load('category', $id); // получаем все данные из category по $id
            if (!$category) {
                return false;
            }
            $category->parent_id = post('parent_id', 'i');
            R::store($category);

            foreach ($_POST['category_description'] as $lang_id => $item) {
                R::exec("UPDATE category_description SET title = ?, description = ?, keywords = ?, content = ? WHERE category_id = ? AND language_id = ?", [
                    $item['title'],
                    $item['description'],
                    $item['keywords'],
                    $item['content'],
                    $id,
                    $lang_id,
                ]);
            }
            R::commit();
            return true;
        } catch (\Exception $e) {
            R::rollback();
            return false;
        }
    }

    public function get_category($id): array // получаем инфо об категории
    {
        return R::getAssoc("SELECT cd.language_id, cd.*, c.* FROM category_description cd JOIN category c on c.id = cd.category_id WHERE cd.category_id = ?", [$id]);
        // Вернем ассоциативный массив где cd.language_id будет ключем массива, а все остальное(cd.*, c.*) данные
    }



}