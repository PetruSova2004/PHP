<?php


namespace wfm;

// set_exception_handler — Задаёт пользовательский обработчик исключений
// set_error_handler — Задаёт пользовательский обработчик ошибок
// file_put_contents — Пишет данные в файл

// Throwable является родительским интерфейсом для всех объектов, выбрасывающихся с помощью выражения throw, включая классы Error и Exception.

// http_response_code — Получает или устанавливает код ответа HTTP
// ob_start — Включение буферизации вывода
// register_shutdown_function — Регистрирует функцию, которая выполнится при завершении работы скрипта
// error_get_last — Получение информации о последней произошедшей ошибке
// ob_end_clean — Очистить (стереть) буфер вывода и отключить буферизацию вывода

// (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR) - это все фатальные ошибки

//Буферизация вывода в PHP — способ указать, что нужно отдельно сохранить данные перед их отправкой в браузер. Эти данные в последствии можно получить, а затем поместить в переменную, работать с ними и, уже позже, отправить в браузер.



class ErrorHandler
{

    public function __construct()
    {
        // https://habr.com/ru/post/161483/
        if (DEBUG) {
            error_reporting(-1);
        } else {
            error_reporting(0);
        }
        set_exception_handler([$this, 'exceptionHandler']);
        set_error_handler([$this, 'errorHandler']);
        ob_start();
        register_shutdown_function([$this, 'fatalErrorHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->logError($errstr, $errfile, $errline);
        $this->displayError($errno, $errstr, $errfile, $errline);
    }

    public function fatalErrorHandler()
    {
        $error = error_get_last();
        if (!empty($error) && $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)) {
            $this->logError($error['message'], $error['file'], $error['line']);
            ob_end_clean();
            $this->displayError($error['type'], $error['message'], $error['file'], $error['line']);
        } else {
            ob_end_flush();
        }
    }

    public function exceptionHandler(\Throwable $e)
    {
        $this->logError($e->getMessage(), $e->getFile(), $e->getLine());
        $this->displayError('Исключение', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
    }

    protected function logError($message = '', $file = '', $line = '')
    {
        file_put_contents(
            LOGS . '/errors.log',
            "[" . date('Y-m-d H:i:s') . "] Текст ошибки: {$message} | Файл: {$file} | Строка: {$line}\n=================\n",
            FILE_APPEND);
    }

    protected function displayError($errno, $errstr, $errfile, $errline, $responce = 500)
    {
        if ($responce == 0) {
            $responce = 404;
        }
        http_response_code($responce);
        if ($responce == 404 && !DEBUG) {
            require WWW . '/errors/404.php';
            die;
        }
        if (DEBUG) {
            require WWW . '/errors/development.php';
        } else {
            require WWW . '/errors/production.php';
        }
        die;
    }

}