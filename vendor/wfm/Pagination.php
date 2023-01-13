<?php


namespace wfm;


class Pagination
{

    public $currentPage; // текущея старница
    public $perpage; // сколько товаров выводить на одну страницу
    public $total; // общее кол-во товаров
    public $countPages; // общее кол-во страниц
    public $uri; // тут хранятся параметры запроса

    public function __construct($page, $perpage, $total)
        // мы передаем: 1) номер текущей страницы, 2) сколько товаров мы хотим видеть на странице, 3) общее кол-во товаров
    {
        $this->perpage = $perpage;
        $this->total = $total;
        $this->countPages = $this->getCountPages();
        $this->currentPage = $this->getCurrentPage($page);
        $this->uri = $this->getParams();
    }

    public function getHtml() // тут мы строим ссылки
    {
        $back = null; // ссылка НАЗАД
        $forward = null; // ссылка ВПЕРЕД
        $startpage = null; // ссылка В НАЧАЛО
        $endpage = null; // ссылка В КОНЕЦ
        $page2left = null; // вторая страница слева
        $page1left = null; // первая страница слева
        $page2right = null; // вторая страница справа
        $page1right = null; // первая страница справа

        // $back
        if ($this->currentPage > 1) {
            $back = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage - 1) . "'>&lt;</a></li>";
        }

        // $forward
        if ($this->currentPage < $this->countPages) {
            $forward = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage + 1) . "'>&gt;</a></li>";
        }

        // $startpage
        if ($this->currentPage > 3) {
            $startpage = "<li class='page-item'><a class='page-link' href='" . $this->getLink(1) . "'>&laquo;</a></li>";
        }

        // $endpage
        if ($this->currentPage < ($this->countPages - 2)) {
            $endpage = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->countPages) . "'>&raquo;</a></li>";
        }

        // $page2left
        if ($this->currentPage - 2 > 0) {
            $page2left = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage - 2) . "'>" . ($this->currentPage - 2) . "</a></li>";
        }

        // $page1left
        if ($this->currentPage - 1 > 0) {
            $page1left = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage - 1) . "'>" . ($this->currentPage - 1) . "</a></li>";
        }

        // $page1right
        if ($this->currentPage + 1 <= $this->countPages) {
            $page1right = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage + 1) . "'>" . ($this->currentPage + 1) . "</a></li>";
        }

        // $page2right
        if ($this->currentPage + 2 <= $this->countPages) {
            $page2right = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage + 2) . "'>" . ($this->currentPage + 2) . "</a></li>";
        }

        return '<nav aria-label="Page navigation example"><ul class="pagination">' . $startpage . $back . $page2left . $page1left . '<li class="page-item active"><a class="page-link">' . $this->currentPage . '</a></li>' . $page1right . $page2right . $forward . $endpage . '</ul></nav>';
    }

    public function getLink($page) // задача данного метода корректно сформировать ссылку
    {
        // str_contains — Определяет, содержит ли строка заданную подстроку

        if ($page == 1) { // если мы на 1 странице мы не будем добавлять 'page=', верни $this->uri и убери '?&' из него
            return rtrim($this->uri, '?&');
        }

        if (str_contains($this->uri, '&')) { // если в $this->uri есть '&' то мы добавим 'page='
            return "{$this->uri}page={$page}";
        } else {
            if (str_contains($this->uri, '?')) {
                return "{$this->uri}page={$page}";
            } else {
                return "{$this->uri}?page={$page}";
            }
        }
    }

    public function __toString()
    { // данный метод позволяет нам при обращении к экземпляру класса как к строке(через echo) будет вызываться данный метод getHtml() и вернёт нам построенную пагинацию.
        return $this->getHtml();
    }

    public function getCountPages() // вернёт общее кол-во страниц
    {
        // ceil() - округляет число в большую сторону
        return ceil($this->total / $this->perpage) ?: 1;
    }

    public function getCurrentPage($page) // передаём номер запрошенной страницы, который заберём из GET параметра
    {
        if (!$page || $page < 1) $page = 1;
        if ($page > $this->countPages) $page = $this->countPages; // если номер страницы($page) больше чем общее количество страниц тогда мы $page присвоим $this->countPages - общее кол-во страниц
        return $page;
    }

    public function getStart() // задача этого метода состоит в том, чтобы указать в дальнейшем sql запросе с какого товара необходимо начать выборку товара
    {
        return ($this->currentPage - 1) * $this->perpage;
    }

    public function getParams() // задача данной функций заключается в том чтобы забрать возможные GET параметры(если они там были) исключая 'page=' потому что мы его будем добавлять самостоятельно
    {
        // preg_match — Выполняет проверку на соответствие регулярному выражению
        $url = $_SERVER['REQUEST_URI']; // Это http запрос который мы сделали при нажатий на что-то
//        var_dump($url);
        $url = explode('?', $url); // разделяем на два элемента массива то что попало в $url, то что до '?' попадёт в первый элемент массива то что после во второй
//        debug($url);
        $uri = $url[0]; // присваиваем то что было до '?' в $url
//        debug($uri);
        if (isset($url[1]) && $url[1] != '') { // если дальше есть какие-то GET параметры
            $uri .= '?';
            $params = explode('&', $url[1]); // разделяем то что попало в $url[1] по символу '&'
//            debug($params);
            foreach ($params as $param) {
                if (!preg_match("#page=#", $param)) $uri .= "{$param}&";
                // если мы столкнулись с 'page=' то мы его пропустим, а если 'page=' отсутствует тогда мы в $uri допишем "{$param}&".
            }
        }
//        debug($uri);
        return $uri;
    }

}
