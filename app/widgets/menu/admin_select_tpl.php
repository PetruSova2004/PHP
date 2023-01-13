<?php // тут выводим при добавлении родительской категории
$parent_id = \wfm\App::$app->getProperty('parent_id'); // тут мы храним ид родителя текущей категории
$get_id = get('id');
// $id это ид языка
?>

<!-- Фактически мы проходимся в цикле по массиву категории и берем каждую категорию и проверяем ее ид равен ид родительской категории которую мы хотим изменить тогда нам нужно выбрать эту категорию -->
<option value="<?= $id ?>" <?php if ($id == $parent_id) echo ' selected'; ?> <?php if ($get_id == $id) echo ' disabled'; ?>>
    <?= $tab . $category['title'] ?>
</option>
<?php if(isset($category['children'])): ?>
    <?= $this->getMenuHtml($category['children'], '&nbsp;' . $tab. '-') ?>
<?php endif; ?>
