<?php

abstract class CustomizerBaseConfig
{
    /**
     * 追加したい構造体
     * @return array
     */
    static function getAdditionalConfig()
    {
        return [];
    }
    
    /**
     * @param $config_key
     * @param bool $default
     * @return array | bool
     */
    static function get($config_key, $default = false)
    {
        $result = $default;
        $config = [];
        $get_user_config = get_called_class() . "::getAdditionalConfig";
        $get_system_config = get_called_class() . "::{$config_key}";

        if (is_callable($get_user_config)) {
            $config = $get_user_config();
        }

        if (isset($config[$config_key])) {
            $result = $config[$config_key];
        } else if (is_callable($get_system_config)) {
            $result = $get_system_config();
        }

        return $result;
    }

    static function getAll()
    {
        $result = [];
        $config = [];
        $get_user_config = get_called_class() . "::getAdditionalConfig";
        $class_name = get_called_class();

        if (is_callable($get_user_config)) {
            $config = $get_user_config();
        }
        
        foreach (get_class_methods($class_name) as $method) {
            if (in_array($method, ['get', 'getAll', 'getAdditionalConfig']) || isset($config[$method])) {
                continue;
            }
            $callable = $class_name . "::{$method}";
            if (is_callable($callable)) {
                $result[$method] = $callable();
            }
        }
        
        return array_merge($config, $result);
    }
}