<?php

abstract class CustomizerBaseConfig
{
    /**
     * @param $config_key
     * @param bool $default
     * @return array | bool
     */
    static function get($config_key, $default = false)
    {
        $result = $default;
        $callable = get_called_class() . "::{$config_key}";
        if (is_callable($callable)) {
            $result = $callable();
        }

        return $result;
    }

    static function getAll()
    {
        $result = [];
        $class_name = get_called_class();
        foreach (get_class_methods($class_name) as $method) {
            if (in_array($method, ['get', 'getAll'])) {
                continue;
            }
            $callable = $class_name . "::{$method}";
            if (is_callable($callable)) {
                $result[$method] = $callable();
            }
        }
        
        return $result;
    }
}