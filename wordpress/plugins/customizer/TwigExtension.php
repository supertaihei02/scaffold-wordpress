<?php

class CustomizerTwigExtension extends Twig_Extension
{
    function getFunctions()
    {
        return [
            new Twig_Function('getFormAction', [$this, 'getFormAction']),
            new Twig_Function('getTerms', [$this, 'getTerms']),
            new Twig_Function('getPosts', [$this, 'getPosts']),
            new Twig_Function('formSettingForOptions', [$this, 'formSettingForOptions']),
            new Twig_Function('easyAttrs', [$this, 'easyAttrs']),
            new Twig_Function('renderFormByConfig', [$this, 'renderFormByConfig']),
            new Twig_Function('renderClasses', [$this, 'renderClasses']),
            new Twig_Function('renderAttributes', [$this, 'renderAttributes']),
            new Twig_Function('renderChild', [$this, 'renderChild']),
        ];
    }

    /**
     * 指定した条件のタグ一覧等の表示に利用
     * @param $condition_path
     * @return array
     */
    function getTerms($condition_path)
    {
        return getTerms(CustomizerUtils::getCondition($condition_path));
    }

    /**
     * 指定した条件の投稿一覧の表示に利用
     * @param $condition_path
     * @return array
     */
    function getPosts($condition_path)
    {
        return getPostsForTemplate(CustomizerUtils::getCondition($condition_path));
    }

    function getFormAction()
    {
        return plugin_dir_url(__FILE__) . 'Form.php';
    }
    
    /* *******************************
     *        Options Form設定
     * *******************************/
    function formSettingForOptions($option_group_keys, $success_url = null, $failure_url = null)
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
            $success_url = $here . '?success';
            $failure_url = $here . '?failure';
        }
        
        echo '<input type="hidden" name="action" value="update" />';
        echo "<input type=\"hidden\" name=\"success_url\" value=\"{$success_url}\" />";
        echo "<input type=\"hidden\" name=\"failure_url\" value=\"{$failure_url}\" />";
        wp_nonce_field($key, $key);
    }
    
    /* *******************************
     *        HTML自動生成系
     * *******************************/
    function renderFormByConfig($option, ...$keys)
    {
        global $si_twig;
        $config = CustomizerConfig::getFormSetting($option);
        $config = CustomizerUtils::getConfig($config, $keys);
        $keys = array_keys($config);

        $elements = CustomizerForm::configToElements($config);
        $si_twig->display(
            'FormForAdmin.twig', [
                'keys' => $keys,
                'elements' => CustomizerForm::applyInputValues($elements),
            ]
        );
    }

    /**
     * 要素内容を全部一発で設定
     * 
     * @param CustomizerElement $element
     * @param array $attrs
     * @param array $classes
     * @return string
     */
    function easyAttrs(CustomizerElement $element, $classes = [], $attrs = [])
    {
        $render = "id={$element->id} ";
        $render .= "name={$element->name} ";
        $render .= $this->renderClasses($element, ...CustomizerUtils::asArray($classes));
        $render .= $this->renderAttributes($element, CustomizerUtils::asArray($attrs));
        
        return $render;
    }


    // --------------
    // - Attributes -
    // --------------
    function renderAttributes(CustomizerElement $element, $attributes)
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
    function renderClasses(CustomizerElement $element, ...$classes)
    {
        $render = '';
        $class_string = implode(' ', $element->getRenderClasses($classes));
        if (!empty($class_string)) {
            $render = "class=\"{$class_string}\"";
        }
        return $render;
    }
    
}