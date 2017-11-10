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
            new Twig_Function('displayFormAdmin', [$this, 'displayFormAdmin']),
            new Twig_Function('displayFormFront', [$this, 'displayFormFront']),
            new Twig_Function('displayFormByConfig', [$this, 'displayFormByConfig']),
            new Twig_Function('renderClasses', [$this, 'renderClasses']),
            new Twig_Function('renderAttributes', [$this, 'renderAttributes']),
            new Twig_Function('renderChild', [$this, 'renderChild']),
            new Twig_Function('getCurrentMaxSequence', [$this, 'getCurrentMaxSequence']),
            new Twig_Function('updateSequence', [$this, 'updateSequence']),
            new Twig_Function('isImage', [$this, 'isImage']),
            new Twig_Function('basename', [$this, 'basename']),
            new Twig_Function('isAdmin', [$this, 'isAdmin']),
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

    /**
     * 管理画面かどうかを返す
     */
    static function isAdmin()
    {
        return is_admin();
    }
    
    /* *******************************
     *        Options Form設定
     * *******************************/
    static function formSettingForOptions($option_group_key, $success_url = null, $failure_url = null)
    {
        $key = 'update_option_with_sequence_';
        $escaped = esc_attr($option_group_key);
        $key .= $escaped;
        
        $config = CustomizerConfig::getFormSetting($escaped);
        $actions = CustomizerUtils::getRequire($config[$escaped], SI_FORM_ACTION);

        if (is_null($success_url)) {
            $here = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            $here = str_replace(['?success', 'success', '?failure', 'failure'], '', $here);

            $query_sign = '?';
            if (strpos($here, '?') !== false) {
                $query_sign = '&';
            }
            $success_url = "{$here}{$query_sign}success";
            $failure_url = "{$here}{$query_sign}failure";
        }

        echo "<input type='hidden' name='option_group' value='{$escaped}' />";
        echo "<input type=\"hidden\" name=\"success_url\" value=\"{$success_url}\" />";
        echo "<input type=\"hidden\" name=\"failure_url\" value=\"{$failure_url}\" />";

        if (is_admin()) {
            echo "<input type=\"hidden\" name=\"page_type\" value=\"admin\" />";
            $key .= '_admin';
        } else {
            echo "<input type=\"hidden\" name=\"page_type\" value=\"front\" />";
            $key .= '_front';
        }

        foreach (CustomizerUtils::asArray($actions) as $action) {
            $hashed = CustomizerUtils::encrypt($action);
            echo "<input type=\"hidden\" name=\"actions[]\" value=\"{$hashed}\" />";
        }
        wp_nonce_field($key, $key);
    }
    
    /* *******************************
     *        HTML自動生成系
     * *******************************/
    static function displayFormAdmin(...$paths)
    {
        self::displayForm('CallAdminForm.twig', $paths);
    }

    static function displayFormFront(...$paths)
    {
        self::displayForm('CallFrontForm.twig', $paths);
    }
    
    static function displayForm($template, $paths)
    {
        global $si_twig;
        if (is_admin()) {
            wp_enqueue_media();
            wp_enqueue_script('customizer-admin-upload', plugins_url('js/adminFileUpload.js', __FILE__), ['jquery']);
        } else {
            wp_enqueue_script('customizer-admin-upload', plugins_url('js/frontFileUpload.js', __FILE__), ['jquery']);
        }
        
        $config = self::getConfig($paths);
        $si_twig->display($template, [
            'root' => reset($paths),
            'elements' => self::getRenderElements($config, $paths),
        ]);
    }

    static function renderForm($template, $paths)
    {
        global $si_twig;

        $config = self::getConfig($paths);
        return $si_twig->render($template, [
            'root' => reset($paths),
            'elements' => self::getRenderElements($config, $paths),
        ]);
    }

    /**
     * Form設定取得
     * @param $paths
     * @return mixed
     */
    static function getConfig($paths)
    {
        $paths = CustomizerUtils::asArray($paths);
        $root = count($paths) <= 1 ? array_shift($paths) : reset($paths);
        $config = CustomizerConfig::getFormSetting($root);
        return CustomizerUtils::getConfig($config, $paths);   
    }

    static function getRenderElements($config, $paths = [])
    {
        $elements = CustomizerForm::configToElements($config, $paths);
        return CustomizerForm::applyInputValues($elements);
    }

    /**
     * 要素内容を全部一発で設定
     * 
     * @param CustomizerElement $element
     * @param array $attrs
     * @param array $classes
     * @param array $ignore
     * @return string
     */
    static function easyAttrs(CustomizerElement $element, $classes = [], $attrs = [], $ignore = [])
    {
        $render = '';
        if (!in_array('id', $ignore)) {
            $render .= "id={$element->id} ";
        }
        if (!in_array('name', $ignore)) {
            $render .= "name={$element->name} ";
        }
        if (!in_array('class', $ignore)) {
            $render .= self::renderClasses($element, ...CustomizerUtils::asArray($classes));
        }
        if (!in_array('attr', $ignore)) {
            $render .= self::renderAttributes($element, CustomizerUtils::asArray($attrs));
        }
        
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
        $render = htmlspecialchars($render);
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
            $class_string = htmlspecialchars($class_string);
            $render = " class=\"{$class_string}\""; 
        }
        return $render;
    }

    // ------------
    // - Children -
    // ------------
    static function getCurrentMaxSequence($children)
    {
        $max_sequence = 0;
        /**
         * @var $child CustomizerElement
         */
        foreach ($children as $child) {
            $max_sequence = $child->sequence > $max_sequence ? 
                $child->sequence : $max_sequence;
        }
        
        return $max_sequence;
    }

    static function updateSequence(CustomizerElement $element, $sequence)
    {
        if (!empty($sequence)) {
            $element->sequence = $sequence;
        }
        return $element;
    }
}