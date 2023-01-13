<?php


namespace wfm;

// Шаблон Registry предназначен в первую очередь на решение проблемы глобальной области видимости. Это очень частая задача, когда нужно обеспечить общий доступ к данным, но при этом не желательно засорять глобальную область видимости. С помощью Registry, как раз и можно это сделать.

class Registry
{

    use TSingleton;

    protected static array $properties = [];

    public function setProperty($name, $value)
    {
        self::$properties[$name] = $value;   // name => value
    }

    public function getProperty($name)
    {
        return self::$properties[$name] ?? null;
    }

    public function getProperties(): array
    {
        return self::$properties;
    }

}