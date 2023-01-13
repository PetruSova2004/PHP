<?php

use wfm\Router;

// ^ - начало строки;  $ - конец строки;

Router::add('^admin/?$', ['controller' => 'Main', 'action' => 'index', 'admin_prefix' => 'admin']);
Router::add('^admin/(?P<controller>[a-z-]+)/?(?P<action>[a-z-]+)?$', ['admin_prefix' => 'admin']);

Router::add('^(?P<lang>[a-z]+)?/?product/(?P<slug>[a-z0-9-]+)/?$', ['controller' => 'Product', 'action' => 'view']); // это маршрут для продуктов
Router::add('^(?P<lang>[a-z]+)?/?category/(?P<slug>[a-z0-9-]+)/?$', ['controller' => 'Category', 'action' => 'view']); // это маршрут для категорий

Router::add('^(?P<lang>[a-z]+)?/?search/?$', ['controller' => 'Search', 'action' => 'index']); // тут мы делаем чтоб запрос шёл на SearchController

Router::add('^(?P<lang>[a-z]+)?/?wishlist/?$', ['controller' => 'Wishlist', 'action' => 'index']);

Router::add('^(?P<lang>[a-z]+)?/?page/(?P<slug>[a-z0-9-]+)/?$', ['controller' => 'Page', 'action' => 'view']);

Router::add('^(?P<lang>[a-z]+)?/?$', ['controller' => 'Main', 'action' => 'index']);

Router::add('^(?P<controller>[a-z-]+)/(?P<action>[a-z-]+)/?$');
Router::add('^(?P<lang>[a-z]+)/(?P<controller>[a-z-]+)/(?P<action>[a-z-]+)/?$'); // если мы не в одну из выше перечисленных правил тогда мы попадём сюда: у нас возможно будет язык, потом контролер, потом action.
