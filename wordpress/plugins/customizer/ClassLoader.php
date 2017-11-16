<?php

class CustomizerClassLoader
{
    // class ファイルがあるディレクトリのリスト
    private static $dirs;

    static function manualLoad()
    {
        $not_classes = [
            'SystemDefine', 'Config', 'Setting', 'ConsoleManager', 'Template',
            'CustomFields', 'CustomPostTypes', 'Ajax',
        ];

        foreach ($not_classes as $class) {
            require SI_BASE_PATH . "/{$class}.php";
        }
    }
    
    /**
     * クラスが見つからなかった場合呼び出されるメソッド
     * @param  string $class 名前空間など含んだクラス名
     * @return bool 成功すればtrue
     */
    static function loadClass($class)
    {
        foreach (self::directories() as $directory) {
            $class = str_replace('Customizer', '', $class);
            $file_name = "{$directory}/{$class}.php";

            if (is_file($file_name)) {
                require $file_name;

                return true;
            }
        }
        return false; 
    }

    /**
     * ディレクトリリスト
     * @return array フルパスのリスト
     */
    private static function directories()
    {
        if (empty(self::$dirs)) {
            $plugin_dir = plugin_dir_path(__FILE__);
            self::$dirs = array(
                $plugin_dir,
                $plugin_dir . 'expand/twig',
                $plugin_dir . 'expand/google',
            );
        }

        return self::$dirs;
    }
}

// これを実行しないとオートローダーとして動かない
spl_autoload_register(array('CustomizerClassLoader', 'loadClass'));
CustomizerClassLoader::manualLoad();