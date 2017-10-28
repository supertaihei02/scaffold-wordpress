<?php

class CustomizerTwigExtension extends Twig_Extension
{
    function getFunctions()
    {
        return [
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
        return getTerms(SiUtils::getCondition($condition_path));
    }

    /**
     * 指定した条件の投稿一覧の表示に利用
     * @param $condition_path
     * @return array
     */
    function getPosts($condition_path)
    {
        return getPostsForTemplate(SiUtils::getCondition($condition_path));
    }
    
    /* *******************************
     *        Options Form設定
     * *******************************/
    function formSettingForOptions($option_group_keys)
    {
        if (function_exists('settings_fields')) {
            foreach (SiUtils::asArray($option_group_keys) as $option_group) {
                settings_fields($option_group);
                do_settings_sections($option_group);
            }    
        }
    }
    
    /* *******************************
     *        HTML自動生成系
     * *******************************/
    function renderFormByConfig($option, ...$keys)
    {
        global $si_twig;
        $config = CustomizerConfig::getFormSetting($option);
        $config = SiUtils::getConfig($config, $keys);
        $keys = array_keys($config);

        $si_twig->display(
            'FormForAdmin.twig', [
                'keys' => $keys,
                'elements' => CustomizerForm::configToElements($config),
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
        $render .= $this->renderClasses($element, ...SiUtils::asArray($classes));
        $render .= $this->renderAttributes($element, SiUtils::asArray($attrs));
        
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
                $values = SiUtils::asArray($values);
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