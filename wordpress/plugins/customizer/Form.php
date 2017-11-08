<?php

class CustomizerForm
{
    /* *******************************
     *     Form設定ファイルの読み込み
     * *******************************/
    /**
     * @param $config
     * @param $path
     * @return array
     */
    static function configToElements($config, $path = [])
    {
        $elements = [];
        if (count($path) <= 1) { $path = []; }
        foreach ($config as $index => $field_group) {
            $wk_path = $path;
            $wk_path[] = $index;
            if (isset($field_group[SI_CUSTOM_FIELDS])) {
                $elements[] = CustomizerForm::buildInputGroup($field_group, [], [], [], $wk_path);
            } else if (isset($field_group[SI_FIELDS])) {
                $elements[] = self::buildInputBlock(self::getKey($wk_path), $field_group, [], [], $wk_path);
            } else {
                $elements[] = self::buildInputParts(self::getKey($wk_path), $field_group, [], $wk_path);
            }
        }
        return $elements;
    }

    /**
     * @param $group
     * @param array $args
     * @param array $block_args
     * @param array $field_args
     * @param $path
     * @return CustomizerElement
     */
    static function buildInputGroup($group, $args = [], $block_args = [], $field_args = [], $path = [])
    {
        $key = CustomizerUtils::getRequire($group, SI_KEY);
        $name = CustomizerUtils::getRequire($group, SI_NAME);
        $groups = CustomizerUtils::getRequire($group, SI_CUSTOM_FIELDS);
        $path[] = SI_CUSTOM_FIELDS;
        $elem = new CustomizerElement($key, $name, $args, $path);
        foreach ($groups as $index => $group) {
            $wk_path = $path;
            $wk_path[] = $index;
            $elem->addChildren(self::buildInputBlock(self::bond($key, $group[SI_KEY]), $group, $block_args, $field_args, $wk_path));
        }
        return $elem;
    }

    /**
     * @param $key
     * @param $block
     * @param array $args
     * @param array $field_args
     * @param $path
     * @return CustomizerElement
     */
    static function buildInputBlock($key, $block, $args = [], $field_args = [], $path = [])
    {
        $name = CustomizerUtils::getRequire($block, SI_NAME);
        $fields = CustomizerUtils::getRequire($block, SI_FIELDS);
        $path[] = SI_FIELDS;

        $elem = new CustomizerElement($key, $name, $args, $path);
        $elem->multiple = CustomizerUtils::get($block, SI_IS_MULTIPLE, false);
        foreach ($fields as $index => $field) {
            $wk_path = $path;
            $wk_path[] = $index;
            $field_args[SI_IS_MULTIPLE] = $elem->multiple;
            $elem->addChildren(self::buildInputParts(self::bond($key, $field[SI_KEY]), $field, $field_args, $wk_path));
        }

        return $elem;
    }

    /**
     * @param $key
     * @param $field
     * @param array $args
     * @param $path
     * @return CustomizerElement
     */
    static function buildInputParts($key, $field, $args = [], $path = [])
    {
        $input_type = CustomizerUtils::getRequire($field, SI_FIELD_TYPE);
        $choice_values = CustomizerUtils::get($field, SI_FIELD_CHOICE_VALUES, []);

        $name = CustomizerUtils::getRequire($field, SI_NAME);

        $elem = new CustomizerElement($key, $name, $args, $path);
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
            foreach (self::recursiveApplyInputValues($element) as $one_element) {
                $reduced[] = $one_element;
            }
            return $reduced;
        }, []);
    }

    /**
     * @param $value
     * @param $sequence
     * @param CustomizerElement $element
     * @return array
     */
    static function recursiveApplyInputValues(CustomizerElement $element, $sequence = null, $value = null)
    {
        $elements = [];

        if ($element->isInput()) {
            if (is_null($sequence) && is_null($value)) {
                $value = CustomizerDatabase::getOption($element->id);
                if ($value === false) {
                    $value = $element->default_value;
                } else {
                    $value = array_shift($value);
                }
            }
            
            $element = self::changeSequence($element, $sequence, $value);
            $elements[] = self::customElement($element);
        }
        /*
         * Input要素でなく multiple なら Block要素である
         * multiple要素の場合は Block Elementごと増やす
         */
        else if ($element->multiple && $element->hasChild()) {
            // --- 子Inputに保存されている値の階層数分 Blockを増やす ---
            // 子要素をサンプリングして値の階層数を取得
            $sequences = (function ($element) {
                $sample = reset($element->children);
                $sample_values = CustomizerDatabase::getOption($sample->id);
                return array_keys($sample_values);
            })($element);
            
            // 子要素数分Blockを複製
            $before_block_id = '';
            $last_sequence = end($sequences);
            $blocks = array_reduce($sequences, function ($reduced, $sequence) use ($element, $last_sequence, &$before_block_id) {
                $block = clone $element;
                // ID, nameの値変更
                $block = self::changeSequence($block, $sequence);
                // 子要素はこれから追加するから一旦クリア
                $block->children = [];
                // 2つ目以降は layer_name不要
                if (!empty($reduced)) {
                    $block->layer_name = null;
                }
                // 1つ前のblock情報を保持
                $block->before_block_id = $before_block_id;
                // 一番最後のには印をつける
                if ($sequence === $last_sequence && $block->multiple) {
                    $block->multiple_last_block = true;
                }
                $reduced[$sequence] = $block;
                
                // 次に保存するblock_idの保持
                $before_block_id = $block->id;
                return $reduced;
            }, []);
            
            // 値の入った子要素をsequenceごとにBlockに追加
            foreach ($element->children as $child) {
                $child_values = CustomizerDatabase::getOption($child->id);
                foreach ($child_values as $child_sequence => $child_value) {
                    $grandson = clone $child;
                    $grandson = self::changeSequence($grandson, $child_sequence, $child_value);
                    $blocks[$child_sequence]->addChildren(
                        self::recursiveApplyInputValues(
                            $grandson, $child_sequence, $child_value
                        )
                    );
                }
            }
            // Block要素を追加
            $elements = array_reduce($blocks, function ($reduced, $block) {
                $reduced[] = $block;
                return $reduced;
            }, $elements);
        }
        /*
         * 単純に子要素を持つElement
         */
        else if ($element->hasChild()) {
            $element->children = array_reduce($element->children, function ($reduced, $child) {
                foreach (self::recursiveApplyInputValues($child) as $one_element) {
                    $reduced[] = $one_element;
                }
                return $reduced;
            }, []);
            $elements[] = self::customElement($element);
        } 
        /*
         * 最下層
         */
        else {
            $elements[] = self::customElement($element);
        }

        return $elements;
    }

    /**
     * @param $sequence
     * @param $elements
     * @return mixed
     */
    static function changeSequenceInfo($sequence, $elements)
    {
        return array_reduce($elements, function ($reduced, $element) use ($sequence) {
            /**
             * @var $element CustomizerElement
             */
            foreach (self::recursiveChangeInputKeys($sequence, $element) as $one_element) {
                $reduced[] = $one_element;
            }
            return $reduced;
        }, []);
    }
    
    static function recursiveChangeInputKeys($sequence, CustomizerElement $element, $is_set_default = true)
    {
        $elements = [];
        
        $value = $is_set_default ? $element->default_value : null;
        $element = self::changeSequence($element, $sequence, $value);
        
        if ($element->hasChild()) {
            $element->children = array_reduce($element->children, function ($reduced, $child) use ($sequence) {
                foreach (self::recursiveChangeInputKeys($sequence, $child) as $one_element) {
                    $reduced[] = $one_element;
                }
                return $reduced;
            }, []);
        }
        
        $elements[] = self::customElement($element);
        return $elements;
    }

    static function changeSequence(CustomizerElement $element, $sequence, $value = null)
    {
        if (is_numeric($sequence)) {
            $element->sequence = $sequence;
        }

        if (($pos = strrpos($element->id, SI_HYPHEN)) !== false) {
            $element->id = substr($element->id, 0, $pos);
        }
        if (($pos = strrpos($element->name, SI_HYPHEN)) !== false) {
            $element->name = substr($element->name, 0, $pos);
        }
        $element->id .= SI_HYPHEN . $element->sequence;
        $element->name .= SI_HYPHEN . $element->sequence;

        if ($element->isInput()) {
            if (is_null($value)) {
                $element->value = $element->default_value;
            } else {
                $element->value = $value;
            }
        }
        
        return $element;
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

    static function getKey($paths)
    {
        $root = reset($paths);
        $key_track = [$root];
        $setting = CustomizerConfig::getFormSetting($root);
        foreach ($paths as $path) {
            $setting = $setting[$path];
            if (is_numeric($path)) {
                $key_track[] = $setting[SI_KEY];
            }
        }
        
        return implode(SI_BOND, $key_track);
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
        $page_type = CustomizerUtils::getRequire($args, 'page_type');
        
        
        /*
         * セキュリティチェック
         */
        $nonce_key = '';
        foreach ($option_groups as $option_group) {
            $nonce_key .= $option_group;
        }
        $nonce_key = "update_option_with_sequence_{$nonce_key}_{$page_type}";
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
