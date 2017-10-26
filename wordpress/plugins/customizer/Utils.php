<?php
class SiUtils
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

    static function asArray($value, $separator = ',')
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

        // そのままでは配列じゃないなら、配列として返す
        if (!is_array($result)){
            $result = array($result);
        }

        return $result;
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
        return array_reduce(SI_CUSTOM_POST_TYPES[SI_POST_TYPES], function ($reduced, $config) {
            $reduced[] = $config[SI_KEY];
            return $reduced;
        });
    }

    static function title($title)
    {
        return $title . SI_TITLE_SEPARATOR . get_bloginfo('name');
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
}

function draw($text, $raw = false)
{
    $value = $raw ? htmlspecialchars($text) : $text;
    echo $value;
}