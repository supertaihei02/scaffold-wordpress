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

        if (isset($obj[$key])) {
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
}
