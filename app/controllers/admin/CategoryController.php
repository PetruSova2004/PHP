<?php


namespace app\controllers\admin;


use app\models\admin\Category;
use RedBeanPHP\R;
use wfm\App;

/** @property Category $model */
class CategoryController extends AppController
{

    public function indexAction()
    {
        $title = 'Категории';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title'));
    }

    public function deleteAction() // данная функция будет удалять категории
    {
        $id = get('id'); // получаем id категории из widgets/menu/admin_table_tpl
        $errors = '';
        $children = R::count('category', 'parent_id = ?', [$id]);
        $products = R::count('product', 'category_id = ?', [$id]); // если существуют товары в данной категории мы не сможем ее удалить
        if ($children) {
            $errors .= 'Ошибка! В категории есть вложенные категории<br>';
        }
        if ($products) {
            $errors .= 'Ошибка! В категории есть товары<br>';
        }
        if ($errors) {
            $_SESSION['errors'] = $errors;
        } else {
            R::exec("DELETE FROM category WHERE id = ?", [$id]);
            R::exec("DELETE FROM category_description WHERE category_id = ?", [$id]);
            $_SESSION['success'] = 'Категория удалена';
        }
        redirect();
    }

    public function addAction() // добавляем категорию
    {
        if (!empty($_POST)) {
            if ($this->model->category_validate()) { // если модель прошла валидацию
                if ($this->model->save_category()) { // если категория сохранилась
                    $_SESSION['success'] = 'Категория сохранена';
                } else {
                    $_SESSION['errors'] = 'Ошибка!';
                }
            }

//            debug($_POST); // чтобы посмотреть данные нужно убрать редирект, после него данные пропадают
            redirect();
        }
        $title = 'Добавление категории';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title'));

    }

    public function editAction()
    {
        $id = get('id');
        if (!empty($_POST)) {
//            debug($_POST['category_description'], 1);
            if ($this->model->category_validate()) {
                if ($this->model->update_category($id)) {
                    $_SESSION['success'] = 'Категория обновлена';
                } else {
                    $_SESSION['errors'] = 'Ошибка!';
                }
            }
            redirect();
        }
        $category = $this->model->get_category($id); // получаем данные о категории на обеих языках
//        debug($category);
        if (!$category) {
            throw new \Exception('Not found category', 404);
        }
        $lang = App::$app->getProperty('language')['id']; // получаем ид активного языка
//        debug($lang);
        App::$app->setProperty('parent_id', $category[$lang]['parent_id']); // по ключу parent_id мы поместим в контейнер ид родителя текущей категории
        $title = 'Редактирование категории';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title', 'category'));
    }

}