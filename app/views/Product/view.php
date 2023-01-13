<?php

use wfm\View;

/** @var $this View */
?>
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light p-2">
            <?= $breadcrumbs; ?>
            <!-- данный $breadcrumbs мы записываем в представления в ProductController и вот почему он тут доступен, но IDE показывает нам ошибку -->
        </ol>
    </nav>
</div>


<div class="container py-3">
    <div class="row">

        <div class="col-md-4 order-md-2">

            <h1><?= $product['title'] ?></h1>  <!-- тут $product доступен благодаря ,,Магией фреймворка,, -->

            <ul class="list-unstyled">
                <li><i class="fas fa-check text-success"></i>Есть в наличий!!</li>
                <li><i class="fas fa-hand-holding-usd"></i> <span
                            class="product-price"><?php if ($product['old_price']): ?><small>
                            $<?= $product['old_price'] ?></small><?php endif; ?>
                        $<?= $product['price'] ?></li>
            </ul>

            <div id="product">
                <div class="input-group mb-3">
                    <input id="input-quantity" type="text" class="form-control" name="quantity" value="1">
                    <button class="btn btn-danger add-to-cart" type="submit"
                            data-id="<?= $product['id'] ?>"><?php __('product_view_buy'); ?></button>
                </div>
            </div>

        </div>

        <div class="col-md-8 order-md-1">

            <ul class="thumbnails list-unstyled clearfix">

                <li class="thumb-main text-center"><a class="thumbnail" href="<?= $product['img'] ?>"
                                                      data-effect="mfp-zoom-in"><img src="<?= $product['img'] ?>"
                                                                                     alt=""></a></li>

                <?php if (!empty($gallery)): ?>
                    <?php foreach ($gallery as $item): ?>
                        <li class="thumb-additional"><a class="thumbnail" href="<?= $item['img'] ?>"
                                                        data-effect="mfp-zoom-in"><img src="<?= $item['img'] ?>" alt=""></a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <?= $product['content']; ?>


        </div>

    </div>
</div>