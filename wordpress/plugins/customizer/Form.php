<?php

class CustomizerForm
{
    /* *******************************
     *     Form設定ファイルの読み込み
     * *******************************/
    /**
     * @param $config
     * @return array
     */
    static function configToElements($config)
    {
        $elements = [];
        foreach ($config as $field_group) {
            $elements[] = CustomizerForm::buildInputGroup($field_group);
        }
        return $elements;
    }

    /**
     * @param $group
     * @param array $args
     * @param array $block_args
     * @param array $field_args
     * @return CustomizerElement
     */
    static function buildInputGroup($group, $args = [], $block_args = [], $field_args = [])
    {
        $key = CustomizerUtils::getRequire($group, SI_KEY);
        $name = CustomizerUtils::getRequire($group, SI_NAME);
        $groups = CustomizerUtils::getRequire($group, SI_CUSTOM_FIELDS);

        $elem = new CustomizerElement($key, $name, $args);
        foreach ($groups as $group) {
            $elem->addChildren(self::buildInputBlock($key, $group, $block_args, $field_args));
        }
        return $elem;
    }

    /**
     * @param $parent_key
     * @param $block
     * @param array $args
     * @param array $field_args
     * @return CustomizerElement
     */
    static function buildInputBlock($parent_key, $block, $args = [], $field_args = [])
    {
        $key = self::bond($parent_key, CustomizerUtils::getRequire($block, SI_KEY));
        $name = CustomizerUtils::getRequire($block, SI_NAME);
        $fields = CustomizerUtils::getRequire($block, SI_FIELDS);

        $elem = new CustomizerElement($key, $name, $args);
        foreach ($fields as $field) {
            $field_args[SI_IS_MULTIPLE] = CustomizerUtils::get($block, SI_IS_MULTIPLE, false);
            $elem->addChildren(self::buildInputParts($key, $field, $field_args));
        }

        return $elem;
    }

    /**
     * @param $parent_key
     * @param $field
     * @param array $args
     * @return CustomizerElement
     */
    static function buildInputParts($parent_key, $field, $args = [])
    {
        $input_type = CustomizerUtils::getRequire($field, SI_FIELD_TYPE);
        $choice_values = CustomizerUtils::get($field, SI_FIELD_CHOICE_VALUES, []);

        $key = self::bond($parent_key, CustomizerUtils::getRequire($field, SI_KEY));
        $name = CustomizerUtils::getRequire($field, SI_NAME);

        $elem = new CustomizerElement($key, $name, $args);
        $elem->multiple = CustomizerUtils::get($args, SI_IS_MULTIPLE, false);
        $elem->autoload = CustomizerUtils::get($field, SI_FIELD_OPTION_AUTOLOAD, false);

        // $argsの値を追加
        $elem->addClasses(CustomizerUtils::get($args, SI_ELEM_CLASSES, []));
        $elem->addAttributes(CustomizerUtils::get($args, SI_ELEM_ATTRS, []));
        $elem->addChildren(CustomizerUtils::get($args, SI_ELEM_CHILDREN, []));

        // $fieldの値を追加
        $elem->addClasses(CustomizerUtils::get($field, SI_ELEM_CLASSES, []));
        $elem->addAttributes(CustomizerUtils::get($field, SI_ELEM_ATTRS, []));
        $elem->addChildren(CustomizerUtils::get($field, SI_ELEM_CHILDREN, []));

        // 特殊Attrをセット
        if (CustomizerUtils::get($field, SI_FIELD_IS_REQUIRE, false)) {
            $elem->addAttributes('required');
        }
        $elem->input_type = $input_type;
        $elem->default_value = CustomizerUtils::get($field, SI_DEFAULT, null);
        $elem->choice_values = $choice_values;

        return $elem;
    }

    /* *******************************
     *   ElementにDB保存値をセット&調整
     * *******************************/
    /**
     * @param $elements
     * @return mixed
     */
    static function applyInputValues($elements)
    {
        return array_reduce($elements, function ($reduced, $element) {
            /**
             * @var $element CustomizerElement
             */
            if ($element->isInput()) {
                $wrap = new CustomizerElement('dummy');
                $wrap->addChildren(self::recursiveApplyInputValues($element));
                foreach ($wrap->children as $child) {
                    $reduced[] = $child;
                }
            } else {
                foreach (self::recursiveApplyInputValues($element) as $one_element) {
                    $reduced[] = $one_element;
                }
            }
            return $reduced;
        }, []);
    }

    /**
     * @param CustomizerElement $element
     * @return array
     */
    static function recursiveApplyInputValues(CustomizerElement $element)
    {
        $elements = [];

        if ($element->isInput()) {
            $values = CustomizerDatabase::getOption($element->id);
            if ($values === false) {
                $element->name .= SI_HYPHEN . '0';
                $element->value = $element->default_value;
                $elements[] = self::customElement($element);
            } else {
                foreach ($values as $sequence => $value) {
                    $one_element = clone $element;
                    $one_element->name .= SI_HYPHEN . $sequence;
                    $one_element->value = $value;
                    $elements[] = self::customElement($one_element);

                }
            }
        } else {
            $elements[] = self::customElement($element);
        }

        if ($element->hasChild()) {
            $element->children = array_reduce($element->children, function ($reduced, $child) {
                foreach (self::recursiveApplyInputValues($child) as $one_element) {
                    $reduced[] = $one_element;
                }
                return $reduced;
            }, []);
        }

        return $elements;
    }

    /**
     * @param CustomizerElement $element
     * @return CustomizerElement
     */
    static function customElement(CustomizerElement $element)
    {
        switch ($element->input_type) {
            case SI_FIELD_TYPE_SELECT:
            case SI_FIELD_TYPE_RADIO:
            case SI_FIELD_TYPE_CHECKBOX:
                $values = CustomizerUtils::asArray($element->value);
                $input_type = $element->input_type;
                $element->choice_values = array_reduce($element->choice_values, function ($reduced, $choice_value) use ($values, $input_type) {
                    $choice_value[SI_SELECTED] = '';
                    if (in_array($choice_value[SI_KEY], $values)) {
                        $choice_value[SI_SELECTED] = $input_type === SI_FIELD_TYPE_SELECT ? 'selected' : 'checked';
                    }
                    $reduced[] = $choice_value;
                    return $reduced;
                }, []);
                break;
        }

        return $element;
    }

    static function extractInputElements($elements)
    {
        return array_reduce($elements, function ($reduced, $element) {
            /**
             * @var $element CustomizerElement
             */
            $children = $element->children;
            $element->children = [];

            if ($element->isInput()) {
                $reduced[] = $element;
            }

            if (!empty($children)) {
                foreach (self::extractInputElements($children) as $extractInputElement) {
                    $reduced[] = $extractInputElement;
                }
            }
            
            return $reduced;
        }, []);
    }

    /**
     * @param $key1
     * @param $key2
     * @return string
     */
    static function bond($key1, $key2)
    {
        return $key1 . SI_BOND . $key2;
    }

    /* *******************************
     *          保存処理
     * *******************************/
    /**
     * @param $args
     */
    static function update($args)
    {
        if ($args['action'] !== 'update') { die('不正なページ遷移です'); }
        
        $option_groups = CustomizerUtils::getRequire($args, 'option_groups');
        $success_url = CustomizerUtils::getRequire($args, 'success_url');
        $failure_url = CustomizerUtils::getRequire($args, 'failure_url');
        
        
        /*
         * セキュリティチェック
         */
        $nonce_key = '';
        foreach ($option_groups as $option_group) {
            $nonce_key .= $option_group;
        }
        $nonce_key = "update_option_with_sequence_{$nonce_key}";
        $nonce_value = CustomizerUtils::getRequire($args, $nonce_key);
        
        if (!wp_verify_nonce($nonce_value, $nonce_key)) {
            wp_redirect($failure_url);
            exit();
        }
        
        /*
         * 保存対象の抽出
         */
        $save_targets = [];
        foreach ($option_groups as $option_group) {
            $form_info = CustomizerConfig::getFormSetting($option_group, false);
            if ($form_info === false) {
                wp_redirect($failure_url);
                exit();
            }
            $elements = self::configToElements($form_info);
            $save_targets[$option_group] = self::extractInputElements($elements);
        }
        
        /*
         * 保存処理
         */
        $post_keys = array_keys($args);
        foreach ($save_targets as $option_group => $input_list) {
            foreach ($input_list as $save_target) {
                /**
                 * @var $save_target CustomizerElement
                 */
                $id = $save_target->id;
                $save_keys = self::getSaveTargetKeys($post_keys, $id. SI_HYPHEN);

                foreach ($save_keys as $save_key) {
                    list($option_key, $sequence) = explode('-', $save_key);
                    // すべて強制的に更新 or 追加する
                    CustomizerDatabase::addOption(
                        $option_key,
                        $args[$save_key],
                        $sequence,
                        $save_target->autoload,
                        true
                    );
                }
            }
            
        }

        wp_redirect($success_url);
        exit();
    }

    static function getSaveTargetKeys(&$post_keys, $part_key)
    {
        $save_keys = [];
        $post_keys = array_reduce($post_keys, function ($reduced, $post_key) use (&$save_keys, $part_key){
            if (strpos($post_key, $part_key) !== false) {
                $save_keys[] = $post_key;
            } else {
                $reduced[] = $post_key;
            }
            return $reduced;
        });
        
        return $save_keys;
    }

}

/* *******************************
 *          保存処理起動
 * *******************************/
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    require_once(dirname(dirname(dirname(__DIR__))) . '/wp-load.php');
    CustomizerForm::update($_POST);
}
