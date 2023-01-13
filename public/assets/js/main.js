$(function () {

    // AJAX позволяет асинхронно извлекать контент с внутреннего сервера без обновления страницы. Таким образом, он позволяет обновлять содержимое веб-страницы без перезагрузки.

    // знак $ используется для ссылки на объект jQuery.

    // Запрос GET передает данные в URL в виде пар "имя-значение" (другими словами, через ссылку), а запрос POST передает данные в теле запроса

    // CART

    function showCart(cart) { // данная функция выводит на экран корзину с добавленнами продуктами
        $('#cart-modal .modal-cart-content').html(cart);
        const myModalEl = document.querySelector('#cart-modal');
        const modal = bootstrap.Modal.getOrCreateInstance(myModalEl);
        modal.show();

        // count-items это иконка в header; cart-qty это кол-во которое выводим в модальном окне
        if ($('.cart-qty').text()) {
            // если у элемента с классом cart-qty текст не пустой тогда к элементу с классом count-items добавим текст класса cart-qty
            $('.count-items').text($('.cart-qty').text());
        } else {
            $('.count-items').text('0');  // тут мы в count-items запишем 0
        }
    }

    $('#get-cart').on('click', function (e) { // id get-cart находится в header.php
        // в этой функций мы при клике на корзину показываем её содержимое
        e.preventDefault();
        $.ajax({
            url: 'cart/show',  // отправляем ajax запрос на controller: cart, action: show
            type: 'GET',
            success: function (res) {  // это PHP функция которую мы вызовем при успешном клике
                showCart(res);
            },
            error: function () {
                alert('Error!');
            },
        });
    });

    $('#cart-modal .modal-cart-content').on('click', '.del-item', function (e) { // при клике на .del-item мы будем удалять и родители этого элемента: #cart-modal и .modal-cart-content.
        e.preventDefault();
        const id = $(this).data('id');  // получаем ид элемента который хотим удалить
        $.ajax({
            url: 'cart/delete',  // указываем адрес по которому пойдет запрос, это то что мы указали в href
            type: 'GET',
            data: {id: id},
            success: function (res) {
                const url = window.location.toString(); // получаем текущий URL адрес
                if (url.indexOf('cart/view') !== -1) { // если мы находимся на странице cart/view
                    window.location = url; // перезапрашиваем страницу
                } else {
                    showCart(res);
                }
            },
            error: function () {
                alert('Error!');
            }
        });
    });

    $('#cart-modal .modal-cart-content').on('click', '#clear-cart', function () {// тут мы удаляем всю корзину сразу
        $.ajax({
            url: 'cart/clear',
            type: 'GET',
            success: function (res) {
                showCart(res);
            },
            error: function () {
                alert('Error!');
            }
        });
    });

    $('.add-to-cart').on('click', function (e) { // при клике на добавление в корзину делаем:
        e.preventDefault();
        const id = $(this).data('id'); // тут будет ид товара($product)
        const qty = $('#input-quantity').val() ? $('#input-quantity').val() : 1;  // если есть input-quantity то мы берем его значение если его нет то по умолчанью ставим 1; Это указывает сколько единиц мы выбрали при клике.
        const $this = $(this);  // this указывает на текущий объект события

        $.ajax({
            url: 'cart/add',
            type: 'GET',
            data: {id: id, qty: qty},  // это GET параметры которые мы должны передать и они попадают в массив $_GET
            success: function (res) { // в res мы записываем ответ который пришел от ajax запроса, нам должен придти шаблон с выведенных в цикле товаров он находится в views/Cart/cart_modal
                showCart(res);
                $this.find('i').removeClass('fa-shopping-cart').addClass('fa-luggage-cart');
            },
            error: function () {
                alert('Error!');
            },
        });
    });

    // CART

    $('#input-sort').on('change', function () {
        window.location = PATH + window.location.pathname + '?' + $(this).val();
    })

    $('.open-search').click(function (e) {
        e.preventDefault();
        $('#search').addClass('active');
    });
    $('.close-search').click(function () {
        $('#search').removeClass('active');
    });

    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $('#top').fadeIn();
        } else {
            $('#top').fadeOut();
        }
    });

    $('#top').click(function () {
        $('body, html').animate({scrollTop: 0}, 700);
    });

    $('.sidebar-toggler .btn').click(function () {
        $('.sidebar-toggle').slideToggle();
    });

    $('.thumbnails').magnificPopup({
        type: 'image',
        delegate: 'a',
        gallery: {
            enabled: true
        },
        removalDelay: 500,
        callbacks: {
            beforeOpen: function () {
                this.st.image.markup = this.st.image.markup.replace('mfp-figure', 'mfp-figure mfp-with-anim');
                this.st.mainClass = this.st.el.attr('data-effect');
            }
        }
    })

    $('#languages button').on('click', function () {
        const lang_code = $(this).data('langcode');
        window.location = PATH + '/language/change?lang=' + lang_code;
    });


    $('.product-card').on('click', '.add-to-wishlist', function (e) { // при клике на сердечко с .add-to-wishlist.
        // parse() объекта JSON является глобальной функцией, предназначенной для анализа (парсинга) строк в формате JSON, при необходимости преобразует и возвращает значения, полученные в ходе анализа.

        e.preventDefault();
        const id = $(this).data('id'); // получаем id товара который хотим добавить в избранное
        const $this = $(this); // это текущая ссылка
        $.ajax({
            url: 'wishlist/add', // указывем куда будет уходить наш запрос
            type: 'GET', // метод передачи данных
            data: {id: id}, // данные которые мы передаём, в данном случае мы передаем id товара который хотим добавить в избранное по ключу id
            success: function (res) {
                res = JSON.parse(res); // получаем ответ в res и парсим его как JSON строку
                Swal.fire( // это функция библиотеки sweetalert2
                    res.text, // передаем текст вывода слайдера
                    '',
                    res.result // и результат вывода
                );
                if (res.result == 'success') {
                    $this.removeClass('add-to-wishlist').addClass('delete-from-wishlist');
                    $this.find('i').removeClass('far fa-heart').addClass('fas fa-hand-holding-heart');
                }

            },
            error: function () {
                alert('Error on main.js addToWishList')
            }
        });
    })

    $('.product-card').on('click', '.delete-from-wishlist', function (e) { // удаление товара по клику
        e.preventDefault();
        const id = $(this).data('id');
        const $this = $(this);
        $.ajax({
            url: 'wishlist/delete',
            type: 'GET',
            data: {id: id},
            success: function (res) {
                const url = window.location.toString(); // получаем текущий URL адрес
                if (url.indexOf('wishlist') !== -1) { // если мы находимся на странице избранного
                    window.location = url; // перезапрашиваем страницу
                } else {
                    res = JSON.parse(res);
                    Swal.fire(
                        res.text,
                        '',
                        res.result
                    );
                    if (res.result == 'success') {
                        $this.removeClass('delete-from-wishlist').addClass('add-to-wishlist');
                        $this.find('i').removeClass('fas fa-hand-holding-heart').addClass('far fa-heart'); // находим иконку, удаляем класс и добавляем другой класс
                    }
                }
            },
            error: function () {
                alert('Error!');
            }
        });
    });

});