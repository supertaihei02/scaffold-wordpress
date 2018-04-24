<?php

class CustomizerForm
{
    public $success = false;
    public $success_url, $failure_url;
    
    function __construct()
    {
        $this->success_url = site_url();
        $this->failure_url = site_url();
    }

    function success($success_url = null)
    {
        $this->success = true;
        if (!empty($success_url)) {
            $this->success_url = $success_url;    
        }
    }

    function failure($failure_url = null)
    {
        $this->success = false;
        if (!empty($failure_url)) {
            $this->failure_url = $failure_url;
        }
    }
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

        // 特殊設定値をセット
        $elem->addExtras(CustomizerUtils::get($field, SI_EXTRA, []));

        // input系要素には全て inputクラスを付ける
        $elem->addClasses('input');
        
        $elem->input_type = $input_type;
        $elem->default_value = CustomizerUtils::get($field, SI_DEFAULT, null);
        $elem->choice_values = $choice_values;
        return $elem;
    }

    /* *******************************
     *   ElementにDB保存値をセット&調整
     * *******************************/
    /**
     * リソースから保存値を取得
     * 
     * @param CustomizerElement $element
     * @param string $resource_type
     * @param array $get_resource_args
     * @param bool $default
     * @return array|bool|mixed
     * @throws Exception
     */
    static function getData(CustomizerElement $element, $default = false, $resource_type = SI_RESOURCE_TYPE_OPTION_WITH_SEQUENCES, $get_resource_args = [])
    {
        switch ($resource_type) {
            case SI_RESOURCE_TYPE_OPTION_WITH_SEQUENCES:
                $result = CustomizerDatabase::getOption($element->id, $default);
                break;
            case SI_RESOURCE_TYPE_POST_META:
                $result = get_post_meta($get_resource_args[SI_GET_P_POST_ID], $element->name, true);
                $result = empty($result) ? $default : $result;
                break;
            case SI_RESOURCE_TYPE_TERM_META:
                $result = get_term_meta($get_resource_args[SI_TERM_ID], $element->name, true);
                $result = empty($result) ? $default : $result;
                break;
            case SI_RESOURCE_TYPE_SPREAD_SHEET:
                $result = [];
                break;
            case SI_RESOURCE_TYPE_DO_NOT_GET:
                $result = $default;
                break;
            default:
                throw new Exception("{$resource_type} is not exist.");
                break;
        }

        return $result;
    }
    
    /**
     * @param $elements
     * @param string $resource_type
     * @param array $get_resource_args
     * @return mixed
     */
    static function applyInputValues($elements, $resource_type = SI_RESOURCE_TYPE_OPTION_WITH_SEQUENCES, $get_resource_args = [])
    {
        return array_reduce($elements, function ($reduced, $element) use ($resource_type, $get_resource_args){
            
            $elements = self::recursiveApplyInputValues(
                $element, null, null,
                $resource_type, $get_resource_args);
            /**
             * @var $element CustomizerElement
             */
            foreach ($elements as $one_element) {
                $reduced[] = $one_element;
            }
            return $reduced;
        }, []);
    }

    /**
     * @param CustomizerElement $element
     * @param null $sequence
     * @param null $value
     * @param string $resource_type
     * @param array $get_resource_args
     * @return array|mixed
     */
    static function recursiveApplyInputValues(CustomizerElement $element, $sequence = null, $value = null, $resource_type = SI_RESOURCE_TYPE_OPTION_WITH_SEQUENCES, $get_resource_args = [])
    {
        $elements = [];

        if ($element->isInput()) {
            if (is_null($sequence) && is_null($value)) {
                $value = self::getData($element, false, $resource_type, $get_resource_args);
                if ($value === false) {
                    $value = $element->default_value;
                    $element->is_data_exist = false;
                } else {
                    $value = array_shift($value);
                    $element->is_data_exist = true;
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
            $sequences = (function ($element, $resource_type, $get_resource_args) {
                $sample = reset($element->children);
                $sample_values = self::getData($sample, [0], $resource_type, $get_resource_args);
                return array_keys($sample_values);
            })($element, $resource_type, $get_resource_args);
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
                /**
                 * @var $child CustomizerElement
                 */
                $child_values = self::getData($child, [$child->default_value], $resource_type, $get_resource_args);

                foreach ($child_values as $child_sequence => $child_value) {
                    $grandson = clone $child;
                    $grandson = self::changeSequence($grandson, $child_sequence, $child_value);
                    $blocks[$child_sequence]->addChildren(
                        self::recursiveApplyInputValues(
                            $grandson, $child_sequence, $child_value,
                            $resource_type, $get_resource_args
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
            $element->children = array_reduce($element->children, function ($reduced, $child) use ($resource_type, $get_resource_args){
                $children = self::recursiveApplyInputValues(
                    $child, null, null,
                    $resource_type, $get_resource_args
                );
                foreach ($children as $one_element) {
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
        // 特別な値なら変換する
        $element->value = self::convertDateTimeExtraValue($element->value);
        
        // 他の要素のname情報が必要な場合は追加する
        if (array_key_exists(SI_EXTRA_SET_ATTR_NAME, $element->extras)) {
            $target_keys = $element->extras[SI_EXTRA_SET_ATTR_NAME];
            $target_keys = CustomizerUtils::asArray($target_keys);
            $wk_path = $element->config_path;
            array_pop($wk_path);
            $wk_path = self::getKey($wk_path);
            foreach ($target_keys as $target_key) {
                $element->addAttributes([
                    $target_key => CustomizerForm::bond(
                        $wk_path, 
                            $target_key . SI_HYPHEN . $element->sequence
                    )
                ]);
            }
        }

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

            case SI_FIELD_TYPE_DATE:
                if (isset($element->extras[SI_DATE_EXTRA_MIN_DATE_SETTING])) {
                    $element->addAttributes([
                        'min' => self::convertDateTimeExtraValue(
                            $element->extras[SI_DATE_EXTRA_MIN_DATE_SETTING]
                        )
                    ]);
                }
                if (isset($element->extras[SI_DATE_EXTRA_MAX_DATE_SETTING])) {
                    $element->addAttributes([
                        'max' => self::convertDateTimeExtraValue(
                            $element->extras[SI_DATE_EXTRA_MAX_DATE_SETTING]
                        )
                    ]);
                }
                break;
                
            case SI_FIELD_TYPE_TIME:
                if (!empty($element->value)) {
                    $converted = new DateTime($element->value);
                    $element->value = $converted->format(SI_SYSTEM_TIME_FORMAT);
                }
                if (isset($element->extras[SI_DATE_EXTRA_MIN_DATE_SETTING])) {
                    $converted = self::convertDateTimeExtraValue(
                        $element->extras[SI_DATE_EXTRA_MIN_DATE_SETTING]
                    );
                    $converted = new DateTime($converted);
                    $converted = $converted->format(SI_SYSTEM_TIME_FORMAT);
                    $element->addAttributes([
                        'min' => $converted
                    ]);
                }
                if (isset($element->extras[SI_DATE_EXTRA_MAX_DATE_SETTING])) {
                    $converted = self::convertDateTimeExtraValue(
                        $element->extras[SI_DATE_EXTRA_MAX_DATE_SETTING]
                    );
                    $converted = new DateTime($converted);
                    $converted = $converted->format(SI_SYSTEM_TIME_FORMAT);
                    $element->addAttributes([
                        'max' => $converted
                    ]);
                }
                break;
        }

        return $element;
    }

    /**
     * "today" 等特殊な値を日付等に変換する
     * @param $setting_value
     * @return bool|string
     */
    static function convertDateTimeExtraValue($setting_value)
    {
        $result = $setting_value;
        // 今日
        if ($setting_value === SI_DATE_EXTRA_TODAY) {
            $now = new DateTime();
            $result = $now->format(SI_SYSTEM_DATE_FORMAT.SI_SYSTEM_ZERO_TIME);
        } 
        // 今
        else if ($setting_value === SI_DATE_EXTRA_NOW) {
            $now = new DateTime();
            $result = $now->format(SI_SYSTEM_DATE_FORMAT.SI_SYSTEM_TIME_FORMAT);
        }
        // 複雑な設定
        else if (is_array($setting_value) && ($datetime = self::configToDateTimeString($setting_value)) !== false) {
            $result = $datetime;
        }
        return $result;
    }

    /**
     * 複雑な時間設定処理
     * @param $config
     * @return bool | string
     */
    static function configToDateTimeString($config)
    {
        $result = false;
        $is_time_setting = false;
        $now = new DateTime();
        
        $extra_settings = [
            SI_DATE_EXTRA_TODAY_BEFORE, SI_DATE_EXTRA_TODAY_AFTER, SI_DATE_EXTRA_SET_TIME
        ];
        foreach ($extra_settings as $extra_setting) {
            if (array_key_exists($extra_setting, $config)) {
                $is_time_setting = true;
                $now = self::buildDateTime($now, $extra_setting, $config[$extra_setting]);
            }
        }

        if ($is_time_setting) {
            $result = $now->format(SI_SYSTEM_DATE_FORMAT.SI_SYSTEM_TIME_FORMAT);
        }
        
        return $result;
    }

    /**
     * 設定にしたがって時刻を修正する
     * @param DateTime $current_date
     * @param $extra_key
     * @param $extra_value
     * @return DateTime
     */
    static function buildDateTime(DateTime $current_date, $extra_key, $extra_value)
    {
        try {
            switch ($extra_key) {
                case SI_DATE_EXTRA_TODAY_BEFORE:
                    $current_date->modify("-{$extra_value} day");
                    break;
                case SI_DATE_EXTRA_TODAY_AFTER:
                    $current_date->modify("+{$extra_value} day");
                    break;
                case SI_DATE_EXTRA_SET_TIME:
                    $times = explode(':', $extra_value);
                    // フォーマットがおかしければ例外が飛ぶようにしている
                    if (count($times) === 3) {
                        list($h, $i, $s) = $times;
                    } else {
                        $s = 0;
                        list($h, $i) = $times;
                    }
                    $current_date->setTime($h, $i, $s);
                    break;
            }
        } catch (Exception $exception) {
            global $si_logger;
            $si_logger->debug(
                $exception->getMessage() .
                PHP_EOL . $exception->getTraceAsString()
            );
        }

        return $current_date;
    }

    /**
     * Elementsの中から再帰的にInput要素のみを抽出
     * @param $elements
     * @return mixed
     */
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
    static function common($args, $config = null)
    {
        global $si_logger;
        
        $delete_names = CustomizerUtils::asArray(CustomizerUtils::get($args, 'delete_names', []));
        $option_group = CustomizerUtils::getRequire($args, 'option_group');
        $success_url = CustomizerUtils::getRequire($args, 'success_url');
        $failure_url = CustomizerUtils::getRequire($args, 'failure_url');
        $page_type = CustomizerUtils::getRequire($args, 'page_type');

        /*
         * セキュリティチェック
         */
        $nonce_key = "update_option_with_sequence_{$option_group}_{$page_type}";
        $nonce_value = CustomizerUtils::getRequire($args, $nonce_key);

        if (!wp_verify_nonce($nonce_value, $nonce_key)) {
            $si_logger->debug('Invalid Nonce.');
            return $failure_url;
        }

        /*
         * 保存対象の抽出
         */
        $form_info = is_null($config) ? CustomizerConfig::getFormSetting($option_group, false) : $config;
        if ($form_info === false) {
            $si_logger->debug('Missing Form Information.');
            return $failure_url;
        }
        $elements = self::configToElements($form_info);
        $save_targets = self::extractInputElements($elements);
        
        $result_set = [
            $delete_names, $option_group, $success_url,
            $failure_url, $page_type, $save_targets
        ];
        return $result_set;
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
    
    /**
     * @param $args
     * @return bool
     */
    function update($args)
    {
        /*
         * セキュリティチェック
         */
        $common = self::common($args);
        if (is_string($common)) {
            $this->failure($common);
            return false;
        }
        
        /*
         * POSTデータ取得
         */
        list($delete_names, $option_groups, $success_url, 
            $failure_url, $page_type, $save_targets) = $common;
        
        /*
         * 削除処理
         */
        foreach ($delete_names as $delete_name) {
            list($option_key, $sequence) = explode(SI_HYPHEN, $delete_name);
            CustomizerDatabase::deleteOption($option_key, $sequence);
        }
        
        /*
         * 保存処理
         */
        $post_keys = array_keys($args);
        foreach ($save_targets as $save_target) {
            /**
             * @var $save_target CustomizerElement
             */
            $id = $save_target->id;
            $save_keys = self::getSaveTargetKeys($post_keys, $id. SI_HYPHEN);

            foreach ($save_keys as $save_key) {
                list($option_key, $sequence) = explode(SI_HYPHEN, $save_key);
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

        $this->success($success_url);
        return true;
    }

    function addSpreadSheetRecord($args)
    {
        global $si_logger;
        /*
         * セキュリティチェック
         */
        $common = self::common($args);
        if (is_string($common)) {
            $this->failure($common);
            return false;
        }

        /*
         * POSTデータ取得
         */
        list($delete_names, $option_group, $success_url,
            $failure_url, $page_type, $save_targets) = $common;

        /*
         * 保存対象を1つのオブジェクトにまとめる
         */
        $save_data = [];
        $post_keys = array_keys($args);
        foreach ($save_targets as $save_target) {
            /**
             * @var $save_target CustomizerElement
             */
            $id = $save_target->id;
            $save_keys = self::getSaveTargetKeys($post_keys, $id. SI_HYPHEN);

            foreach ($save_keys as $save_key) {
                list($option_key, $sequence) = explode(SI_HYPHEN, $save_key);
                $save_data[$option_key][$sequence] = $args[$save_key];
            }
        }

        try {
            // Spread Sheet ID
            $spread_sheet_id = CustomizerDatabase::getOption("google_spread_sheet_{$option_group}_spread_sheet_id", false, true);
            if (empty($spread_sheet_id)) {
                throw new Exception('Spread Sheet IDが設定されていません');
            }
            
            // Spread Sheet Name
            $setting = CustomizerFormSettings::get('google_spread_sheet');
            $setting = CustomizerConfig::getFieldSetting($setting, $option_group);
            $spread_sheet_id_setting = CustomizerConfig::getInputSetting($setting, 'spread_sheet_id');
            $spread_sheet_name = 'Sheet1';
            if (array_key_exists(SI_SPREAD_SHEET_TARGET_SHEET_NAME, $spread_sheet_id_setting[SI_EXTRA])) {
                $spread_sheet_name = $spread_sheet_id_setting[SI_EXTRA][SI_SPREAD_SHEET_TARGET_SHEET_NAME];
            }
            
            CustomizerSpreadSheet::addRecord($save_data, $spread_sheet_id, $spread_sheet_name);
        } catch (Exception $exception) {
            $si_logger->debug($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            $this->failure($common);
            return false;
        }
        
        $this->success($success_url);
        return true;
    }

    static function wpPostUpdate()
    {
        $args = $_POST;

        /*
        * セキュリティチェック
        */
        try {
            $common = self::common($args);
        } catch (Exception $e) {
            return false;
        }
        if (is_string($common)) {
            return false;
        }

        $post_id = CustomizerUtils::getRequire($args, 'post_ID');

        /*
         * POSTデータ取得
         */
        list($delete_names, $option_groups, $success_url,
            $failure_url, $page_type, $save_targets) = $common;

        /*
         * 保存処理
         */
        $post_keys = array_keys($args);
        foreach ($save_targets as $save_target) {
            /**
             * @var $save_target CustomizerElement
             */
            $id = $save_target->id;
            $save_keys = self::getSaveTargetKeys($post_keys, $id. SI_HYPHEN);
            /*
             * 同じkeyのデータをまとめる
             */
            $save_group_key = null;
            $save_data = [];
            foreach ($save_keys as $save_key) {
                list($option_key, $sequence) = explode(SI_HYPHEN, $save_key);
                $save_group_key = $option_key;
                $save_data[$sequence] = $args[$save_key];
            }
            // すべて強制的に更新 or 追加する
            update_metadata(
                'post',
                $post_id,
                $save_group_key,
                $save_data
            );
        }
        
        return $post_id;
    }

    static function wpTermUpdate($term_id)
    {
        $args = $_POST;

        /*
        * セキュリティチェック
        */
        try {
            $term = get_term($term_id);
            $common = self::common($args, [$term->taxonomy => siSearchTaxonomyConfig($term->taxonomy)]);
        } catch (Exception $e) {
            return false;
        }
        if (is_string($common)) {
            return false;
        }

        /*
         * POSTデータ取得
         */
        list($delete_names, $option_groups, $success_url,
            $failure_url, $page_type, $save_targets) = $common;

        /*
         * 保存処理
         */
        $post_keys = array_keys($args);
        foreach ($save_targets as $save_target) {
            /**
             * @var $save_target CustomizerElement
             */
            $id = $save_target->id;
            $save_keys = self::getSaveTargetKeys($post_keys, $id. SI_HYPHEN);
            /*
             * 同じkeyのデータをまとめる
             */
            $save_group_key = null;
            $save_data = [];
            foreach ($save_keys as $save_key) {
                list($option_key, $sequence) = explode(SI_HYPHEN, $save_key);
                $save_group_key = $option_key;
                $save_data[$sequence] = $args[$save_key];
            }
            // すべて強制的に更新 or 追加する
            update_term_meta(
                $term_id,
                $save_group_key,
                $save_data
            );
        }

        return $term_id;
    }
}

/* *******************************
 *          保存処理起動
 * *******************************/
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $form = null;
    $do_redirect = true;
    $actions = isset($_POST['actions']) ? $_POST['actions'] : [];
    if (empty($actions)) {
        return;
    }

    require dirname(dirname(dirname(__DIR__))) . '/wp-load.php';
    $form = new CustomizerForm();
    foreach ($actions as $action) {

        // 追加MODE[問い合わせフォーム等] => 独自options
        if (password_verify(SI_FORM_ACTION_SAVE_ADD, $action)) {
            
        }
        // 更新MODE[設定項目等] => 独自options
        else if (password_verify(SI_FORM_ACTION_SAVE_UPDATE, $action)) {
            $form->update($_POST);
        }
        // 投稿情報のMETA情報として保存 => post_meta
        else if (password_verify(SI_FORM_ACTION_SAVE_WP_POST, $action)) {
            $do_redirect = false;
        }
        // 投稿情報のMETA情報として保存 => term_meta
        else if (password_verify(SI_FORM_ACTION_SAVE_WP_TERM, $action)) {
            $do_redirect = false;
        }
        // スプレッドシートに保存 => Google Spread Sheet
        else if (password_verify(SI_FORM_ACTION_SAVE_SPREAD_SHEET, $action)) {
            $form->addSpreadSheetRecord($_POST);
        }
        // メールの送信 => Mail BOX
        else if (password_verify(SI_FORM_ACTION_SEND_MAIL, $action)) {

        }
    }

    $redirect = $form->failure_url;
    if ($form->success) {
        $redirect = $form->success_url;
    }
    if ($do_redirect) {
        wp_redirect($redirect);
        die();
    }
}
