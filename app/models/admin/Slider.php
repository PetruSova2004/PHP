<?php


namespace app\models\admin;


use app\models\AppModel;
use RedBeanPHP\R;

class Slider extends AppModel
{

    public function get_slides(): array
    {
        return R::getAssoc("SELECT * FROM slider");
    }

    public function update_slider(): void
    {
        if (!isset($_POST['gallery'])) {
            R::exec("DELETE FROM slider"); // Удаляем всё из slider
        }
        // array_diff — Вычислить расхождение массивов
        // в $_POST['gallery'] к нам приходят пути к картинкам
        if (isset($_POST['gallery']) && is_array($_POST['gallery'])) {
            $gallery = self::get_slides();
            if ( (count($gallery) != count($_POST['gallery'])) || array_diff($gallery, $_POST['gallery']) || array_diff($_POST['gallery'], $gallery) ) { // если что-то меняется
                R::exec("DELETE FROM slider"); // Удаляем всё из slider
                $sql = "INSERT INTO slider (img) VALUES ";
                foreach ($_POST['gallery'] as $item) {
                    $sql .= "(?),";
                }
                $sql = rtrim($sql, ',');
                R::exec($sql, $_POST['gallery']);
            }
        }
    }

}