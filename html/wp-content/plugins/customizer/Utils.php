<?php
class CustomizerUtils
{
    static function isAllEmpty($values)
    {
        $result = true;
        foreach ($values as $value) {
            if (!empty($value)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    static function get($obj, $key, $not_found = null)
    {
        if (empty($obj)) {
            return $not_found;
        }

        if (!empty($obj[$key])) {
            return $obj[$key];
        } else {
            return $not_found;
        }
    }

    static function getRequire($obj, $key)
    {
        if (!empty($obj) && isset($obj[$key])) {
            return $obj[$key];
        }

        throw new Exception(
            "$key is require."
        );
    }

    static function asArray($value, $accept_associative = true, $separator = ',')
    {
        // 空なら、空の配列を返す
        if ($value !== 0 && empty($value)) {
            return array();
        }

        $result = array();
        // Json形式
        if (self::isJson($value)) {
            $result = json_decode($value, true);
        }
        // 区切り文字形式
        else if (is_string($value)) {
            $result = explode($separator, $value);
        }

        // 上記のどちらでもないなら、そのまま
        if (empty($result)) {
            $result = $value;
        }

        // 連想配列の場合はそれを配列に包んで返す(デフォルト無効)
        if (is_array($result) && !self::is_vector($result) && !$accept_associative) {
            $result = array($result);
        }
        
        // そのままでは配列じゃないなら、配列として返す
        if (!is_array($result)){
            $result = array($result);
        }

        return $result;
    }

    /**
     * 純粋な配列(not 連想配列)かどうか
     * @param array $arr
     * @return bool
     */
    static function is_vector(array $arr) {
        return array_values($arr) === $arr;
    }

    static function isJson($json) {
        $temp = @json_decode($json, true);
        if (!$temp) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }
    
    static function strposArray($haystack, $needles) {
        $result = false;
        if (is_array($needles)) {
            foreach ($needles as $str) {
                if (is_array($str)) {
                    $pos = strpos_array($haystack, $str);
                } else {
                    $pos = strpos($haystack, $str);
                }
                $result = $pos;
                if ($result !== false) {
                    break;
                }
            }
        } else {
            $result = strpos($haystack, $needles);
        }
        
        return $result;
    }

    static function isCustomizeSingle($current_post_type)
    {
        $result = false;
        if (is_single() && in_array($current_post_type, self::getCustomizePostTypes())) {
            $result = true;
        }
        return $result;
    }

    static function getCustomizePostTypes()
    {
        return array_reduce(CustomizerPostTypeSettings::getAll(), function ($reduced, $config) {
            $reduced[] = $config[SI_KEY];
            return $reduced;
        });
    }

    static function title($title)
    {
        $sep = CustomizerDatabase::getOption('seo_base_title_separator', '｜', true);
        return $title . $sep . get_bloginfo('name');
    }

    static function createDir($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    static function isFile($file_path)
    {
        clearstatcache();
        return is_file($file_path);
    }

    /**
     * 現在のページの種別を返す
     * @return string
     */
    static function getPageType()
    {
        $page_type = SI_PAGE_TYPE_404;
        // Topページ
        if (is_home()) {
            $page_type = SI_PAGE_TYPE_HOME;
        }
        // なんらかの POST_TYPE の一覧ページ
        else if (is_archive()) {
            $page_type = SI_PAGE_TYPE_ARCHIVE;
        }
        // なんらかの POST_TYPE の詳細ページ
        else if (is_single()) {
            $page_type = SI_PAGE_TYPE_SINGLE;
        }
        // なんらかの 固定ページ
        else if (is_page()) {
            $page_type = SI_PAGE_TYPE_PAGE;
        }
        // 検索結果ページ
        else if (is_search()) {
            $page_type = SI_PAGE_TYPE_SEARCH;
        }
        return $page_type;
    }

    /**
     * Twigでは ハイフンをkeyにできないから
     * ハイフンをアンダーバーに変換
     * 
     * @param $parent_key
     * @param $child_key
     * @return mixed
     */
    static function formatKey($parent_key, $child_key)
    {
        return str_replace($parent_key . SI_BOND, '', $child_key);
    }

    static function isAjax()
    {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            return true;
        }
        return false;
    }

    static function isWpAjax()
    {
        if (strpos(admin_url('admin-ajax.php'), $_SERVER['PHP_SELF']) !== false) {
            return true;
        }
        return false;
    }
    
    static function getCondition($condition_keys) 
    {
        global $conditions;    
        return self::getConfig($conditions, $condition_keys);
    }

    static function getConfig($config, $keys)
    {
        $wk_config = $config;
        foreach (CustomizerUtils::asArray($keys) as $config_key) {
            if (!isset($wk_config[$config_key])) {
                throw new Exception("[{$config_key}] is not exist.");
                break;
            }
            $wk_config = $wk_config[$config_key];
        }
        return $wk_config;
    }

    static function encrypt($data)
    {
        return password_hash($data, PASSWORD_DEFAULT);
    }
}
