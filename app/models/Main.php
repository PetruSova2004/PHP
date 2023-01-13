<?php

namespace app\models;


use RedBeanPHP\R;

class Main extends AppModel // данный класс является моделю контролера MainController.php
{

    public function get_hits($lang, $limit): array
        // p.* = product *;  pd.* = product_description *; это соокрашение
    {
        return R::getAll("SELECT p.*, pd.* FROM product p JOIN product_description pd on p.id = pd.product_id WHERE p.status = 1 AND p.hit = 1 AND pd.language_id = ? LIMIT $limit", [$lang['id']]);

        // выберем всё из таблицы product и product_description где мы соединяем все через JOIN где эти две таблицы должны p.id должен быть равен pd.product_id при этом мы выбираем только те товары у которых p.status = 1, p.hit = 1, и pd.language_id должен совпадать с тем которым мы передадим ($lang) и выбери ровно указанное кол-во ($limit)
    }

}