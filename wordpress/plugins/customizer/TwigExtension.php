<?php

class CustomizerTwigExtension extends Twig_Extension
{
    function getFunctions()
    {
        return [
            new Twig_Function('getFormAction', [$this, 'getFormAction']),
            new Twig_Function('putSubmitButton', [$this, 'putSubmitButton']),
            new Twig_Function('getTerms', [$this, 'getTerms']),
            new Twig_Function('getPosts', [$this, 'getPosts']),
            new Twig_Function('formSettingForOptions', [$this, 'formSettingForOptions']),
            new Twig_Function('easyAttrs', [$this, 'easyAttrs']),
            new Twig_Function('renderFormAdmin', [$this, 'renderFormAdmin']),
            new Twig_Function('renderFormFront', [$this, 'renderFormFront']),
            new Twig_Function('renderFormByConfig', [$this, 'renderFormByConfig']),
            new Twig_Function('renderClasses', [$this, 'renderClasses']),
            new Twig_Function('renderAttributes', [$this, 'renderAttributes']),
            new Twig_Function('renderChild', [$this, 'renderChild']),
            new Twig_Function('isImage', [$this, 'isImage']),
            new Twig_Function('basename', [$this, 'basename']),
        ];
    }

    /**
     * 指定した条件のタグ一覧等の表示に利用
     * @param $condition_path
     * @return array
     */
    static function getTerms($condition_path)
    {
        return getTerms(CustomizerUtils::getCondition($condition_path));
    }

    /**
     * 指定した条件の投稿一覧の表示に利用
     * @param $condition_path
     * @return array
     */
    static function getPosts($condition_path)
    {
        return getPostsForTemplate(CustomizerUtils::getCondition($condition_path));
    }

    /**
     * Formの保存ロジックURL
     * @return string
     */
    static function getFormAction()
    {
        return plugin_dir_url(__FILE__) . 'Form.php';
    }

    /**
     * submit 配置
     * @param null $text
     * @param string $type
     * @param string $name
     * @param bool $wrap
     * @param null $other_attributes
     * @return string
     */
    static function putSubmitButton($text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null)
    {
        return get_submit_button( $text, $type, $name, $wrap, $other_attributes );
    }
    
    /**
     * 画像判定
     * @param $file_path
     * @return bool
     */
    static function isImage($file_path)
    {
        $result = false;
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        if (in_array($ext, CustomizerDefine::$IMAGE_EXTENSIONS)) {
            $result = true;
        }
        return $result;
    }

    /**
     * ファイル名取得
     * @param $file_path
     * @return string
     */
    static function basename($file_path)
    {
        return basename($file_path);
    }
    
    /* *******************************
     *        Options Form設定
     * *******************************/
    static function formSettingForOptions($option_group_keys, $success_url = null, $failure_url = null)
    {
        $key = 'update_option_with_sequence_';
        foreach (CustomizerUtils::asArray($option_group_keys) as $option_group) {
            $escaped = esc_attr($option_group);
            echo "<input type='hidden' name='option_groups[]' value='" . $escaped . "' />";
            $key .= $escaped;
        }

        if (is_null($success_url)) {
            $here = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            $here = str_replace(['?success', 'success', '?failure', 'failure'], '', $here);

            $query_sign = '?';
            if (strpos($here, '?') !== false) {
                $query_sign = '&';
            }
            // TODO 既に ? が付いていたら & をつける！！！ 
            
            $success_url = "{$here}{$query_sign}success";
            $failure_url = "{$here}{$query_sign}failure";
        }
        
        echo '<input type="hidden" name="action" value="update" />';
        echo "<input type=\"hidden\" name=\"success_url\" value=\"{$success_url}\" />";
        echo "<input type=\"hidden\" name=\"failure_url\" value=\"{$failure_url}\" />";

        if (is_admin()) {
            echo "<input type=\"hidden\" name=\"page_type\" value=\"admin\" />";
            $key .= '_admin';
        } else {
            echo "<input type=\"hidden\" name=\"page_type\" value=\"front\" />";
            $key .= '_front';
        }
        wp_nonce_field($key, $key);
    }
    
    /* *******************************
     *        HTML自動生成系
     * *******************************/
    static function renderFormAdmin($option, ...$keys)
    {
        self::renderFormByConfig('FormForAdmin.twig', $option, $keys);
    }

    static function renderFormFront($option, ...$keys)
    {
        self::renderFormByConfig('FormForFront.twig', $option, $keys);
    }
    
    static function renderFormByConfig($template, $option, $keys)
    {
        global $si_twig, $forms;
        if (isset($forms[$option])) {
            $config = [$option => $forms[$option]];
        } else {
            $config = CustomizerConfig::getFormSetting($option);
        }
        $config = CustomizerUtils::getConfig($config, $keys);
        $keys = array_keys($config);

        $elements = CustomizerForm::configToElements($config);
        $elements = CustomizerForm::applyInputValues($elements);
        $si_twig->display($template, [
            'keys' => $keys,
            'elements' => $elements,
        ]);
    }

    /**
     * 要素内容を全部一発で設定
     * 
     * @param CustomizerElement $element
     * @param array $attrs
     * @param array $classes
     * @return string
     */
    static function easyAttrs(CustomizerElement $element, $classes = [], $attrs = [])
    {
        $render = "id={$element->id} ";
        $render .= "name={$element->name} ";
        $render .= self::renderClasses($element, ...CustomizerUtils::asArray($classes));
        $render .= self::renderAttributes($element, CustomizerUtils::asArray($attrs));
        
        return $render;
    }


    // --------------
    // - Attributes -
    // --------------
    static function renderAttributes(CustomizerElement $element, $attributes)
    {
        $render = '';
        $render_attributes = $element->getRenderAttributes($attributes);
        foreach ($render_attributes as $key => $values) {
            if (is_null($values)) {
                // required, readonly等の valueがないケース
                $render .= " {$key}";
            } else {
                $values = CustomizerUtils::asArray($values);
                $values_string = implode(' ', $values);
                $render .= " {$key}={$values_string}";    
            }
        }
        return $render;
    }
    
    // -----------
    // - Classes -
    // -----------
    static function renderClasses(CustomizerElement $element, ...$classes)
    {
        $render = '';
        $classes = $element->getRenderClasses($classes);
        $class_string = implode(' ', $classes);
        if (!empty($class_string)) {
            $render = count($classes) > 1 ?
                "class=\"{$class_string}\"" :
                "class={$class_string}"; 
        }
        return $render;
    }
    
}