<?php

class CustomizerFields
{
    static function setEvents()
    {
        // Custom Field
        add_action('add_meta_boxes', 'CustomizerFields::customFields');
        add_action('save_post', 'CustomizerForm::wpPostUpdate');

        // Tips
        add_action('admin_enqueue_scripts', 'CustomizerFields::cannotPreview');
        add_action('wp_enqueue_scripts', 'CustomizerFields::hiddenField');
        add_action('admin_enqueue_scripts', 'CustomizerFields::hiddenField');
        
        // Taxonomies
//        add_action('create_term', 'CustomizerForm::wpTermUpdate');
//        add_action('edit_terms', 'CustomizerForm::wpTermUpdate');
//        add_action('edit_tag_form', 'CustomizerFields::addTaxonomyFieldDetail');
//        add_action('add_tag_form_fields', 'CustomizerFields::addTaxonomyField');
//        foreach (CustomizerTaxonomiesSettings::getAll() as $post_type => $terms) {
//            foreach ($terms as $term) {
//                $filter_name = $post_type . SI_BOND . $term[SI_KEY];
//                add_action($filter_name . '_add_form_fields', 'CustomizerFields::addTaxonomyField');
//            }
//        }
    }

    /* *******************************
     *       Custom Field
     * *******************************/
    static function customFields($post_type)
    {
        global $post;
        // POST TYPEのコンフィグを取得
        $conf = CustomizerConfig::getFormSetting($post_type, false);
        if ($conf === false) {
            return false;
        }

        if (is_admin()) {
            wp_enqueue_media();
            wp_enqueue_script('customizer-admin-upload', plugins_url('js/adminFileUpload.js', __FILE__), ['jquery']);
        } else {
            wp_enqueue_script('customizer-admin-upload', plugins_url('js/frontFileUpload.js', __FILE__), ['jquery']);
        }

        $elements = CustomizerTwigExtension::getRenderElements($conf, $post_type, SI_RESOURCE_TYPE_POST_META, [
            SI_GET_P_POST_ID => $post->ID
        ]);

        add_meta_box(
            'custom',
            '拡張項目',
            'CustomizerFields::addMetaBox',
            $post_type,
            'advanced',
            // 並び順を保持
            'high',
            // render関数に渡す引数
            [
                'elements' => $elements,
                'root' => $post_type
            ]
        );

        return true;
    }

    static function addMetaBox($post, $args)
    {
        global $si_twig;
        $args = $args['args'];
        $si_twig->display(
            'CallWpPostForm.twig',
            $args
        );
    }

    /* *******************************
     *  Previewする前に下書き保存させる
     * *******************************/
    /**
     * @param $hook
     */
    static function cannotPreview($hook)
    {
        global $post;

        if ($hook == 'post.php' || $hook == 'post-new.php') {
            if (!empty($post) && in_array($post->post_status, array('auto-draft', 'draft'))) {
                if ($post->post_type != 'page') {
                    if (!self::isSavedCustomValues($post->ID)) {
                        wp_enqueue_script('cannotPreview', plugins_url('js/cannotPreview.js', __FILE__));
                    }
                }
            }
        }
    }

    /**
     * 入力必須な項目が保存されているかによって
     * このフィールドが1度は保存されているかどうかを判断する
     * (必須項目がない場合はいつでも true を返す)
     *
     * @param $post_id
     * @return bool
     */
    static function isSavedCustomValues($post_id)
    {
        $isSaved = true;
        $custom_values = get_post_custom($post_id);
        unset($custom_values['_edit_lock']);
        unset($custom_values['_edit_last']);

        if (empty($custom_values)) {
            $isSaved = false;
        }

        return $isSaved;
    }

    /**
     * hiddenクラスをつけて要素を消せるようにする
     */
    static function hiddenField()
    {
        echo "<style> .hidden { display: none !important; } </style>";
    }

    /* *******************************
     *   Custom Taxonomies Fields
     * *******************************/
    static function addTaxonomyField($taxonomy)
    {
        self::displayTaxonomyFields($taxonomy);
    }

    static function addTaxonomyFieldDetail($term)
    {
        self::displayTaxonomyFields($term->taxonomy, $term->slug);
    }

    static function displayTaxonomyFields($taxonomy, $slug = null)
    {
        $config = siSearchTaxonomyConfig($taxonomy);
        global $si_logger; $si_logger->develop($config, null, 'take1');
    }
}

/**
 * $taxonomyの管理画面にFormを追加する
 * @param $taxonomy
 * @param null $slug
 */
function siTaxonomyFormRender($taxonomy, $slug = null)
{
    $config = siSearchTaxonomyConfig($taxonomy);
    if (empty($config[SI_CUSTOM_FIELDS])) {
        return;
    }
    $custom_fields = $config[SI_CUSTOM_FIELDS];
    if (empty($custom_fields)) {
        return;
    }

    // Taxonomy情報を取得
    $term_id = (function ($taxonomy, $slug) {
        if (is_null($slug)) {
            return null;
        }
        $taxonomies = get_terms($taxonomy, [
            'hide_empty' => false,
            'slug' => $slug
        ]);
        $current_taxonomy = array_shift($taxonomies);
        return intval($current_taxonomy->term_id);
    })($taxonomy, $slug);

    echo '<table class="form-table"><tbody>';

    $is_first = true;
    foreach ($custom_fields as $custom_field_group) {
        // 引数の作成
        $value_indexes = (function ($term_id, $custom_field_group) {
            if (!$custom_field_group[SI_IS_MULTIPLE]) {
                return [0];
            }
            // 動的な項目の場合はValueIndex情報を更新する
            $stored_serial = get_term_meta($term_id, $custom_field_group[SI_KEY] . SI_BOND . 'serial', true);
            if (empty($stored_serial)) {
                return [0];
            }
            return $stored_serial;
        })($term_id, $custom_field_group);
        end($value_indexes);
        $final_key = key($value_indexes);
        $before_group_key = null;
        $is_first_in_group = true;

        foreach ($value_indexes as $array_index => $value_index) {
            // multiの時はmeta_box_keyにくっ付ける
            $group_multi_key = $custom_field_group[SI_IS_MULTIPLE] ? $value_index : '';
            $group_key = $custom_field_group[SI_KEY] . $group_multi_key;

            $render_config = [
                SI_IS_FIRST => $is_first,
                SI_IS_LAST => ($final_key === $array_index),
                SI_ARRAY_INDEX => $array_index,
                SI_VALUE_INDEX => $value_index,
                SI_GROUP_INFO => $custom_field_group,
                SI_POST_TYPE => $config[SI_POST_TYPE],
                SI_BEFORE_FIELD_GROUP => $before_group_key
            ];
            drawFrameForTerm($render_config, $value_index, $term_id, $is_first_in_group);

            $is_first = false;
            $is_first_in_group = false;
            $before_group_key = $group_key;
        }
    }
    echo '</tbody></table>';
}
