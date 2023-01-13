<?php

namespace wfm;

// Смысл любого кэширования состоит в том, чтобы организовать некий буфер, в котором будет храниться уже готовый результат выполнения: то есть вместо выполнения кода, будет отданы данные из кэша. Использовать кэш нужно в тех участках кода, которые являются наиболее ресурсозатратными.

class Cache
{
    // md5() Возвращает хеш в виде 32-символьного шестнадцатеричного числа.
    // file_put_contents — Пишет данные в файл
    // serialize() Генерирует пригодное для хранения представление переменной.
    // Это полезно для хранения или передачи значений PHP между скриптами без потери их типа и структуры.

    use TSingleton;

    public function set($key, $data, $seconds = 3600): bool  // данная функция будет что-то писать в кеш
    {
        $content['data'] = $data;
        $content['end_time'] = time() + $seconds;
        if (file_put_contents(CACHE . '/' . md5($key) . '.txt', serialize($content))) {
            return true;
        } else {
            return false;
        }
    }

    public function get($key)  // данная функция будет что-то получать из кеша
    {
        $file = CACHE . '/' . md5($key) . '.txt';
        if (file_exists($file)) {
            $content = unserialize(file_get_contents($file));
            if (time() <= $content['end_time']) {
                return $content['data'];
            }
            unlink($file);
        }
        return false;
    }

    public function delete($key)  // данная функция будет что-то удалять из кеша
    {
        $file = CACHE . '/' . md5($key) . '.txt';
        if (file_exists($file)) {
            unlink($file);
        }
    }

}