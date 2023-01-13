<!-- Default box -->  <!-- тут у нас выводятся все категории -->
<div class="card">

    <div class="card-header">
        <a href="<?= ADMIN ?>/category/add" class="btn btn-default btn-flat"><i class="fas fa-plus"></i> Добавить категорию</a>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <?php new \app\widgets\menu\Menu([ // передаем массив настроек
                'cache' => 0, // тут указываем время кеша, мы не будем кешировать в админке данные
                'cacheKey' => 'admin_menu', // если захотим использовать кеширование, то указываем куда кешировать данные
                'class' => 'table table-bordered',
                'tpl' => APP . '/widgets/menu/admin_table_tpl.php', // шаблон виджета который будем использовать
                'container' => 'table',
            ]); ?>
        </div>

    </div>
</div>
<!-- /.card -->

