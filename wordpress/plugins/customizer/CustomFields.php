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
        add_action('create_term', 'CustomizerForm::wpTermUpdate');
        add_action('edit_terms', 'CustomizerForm::wpTermUpdate');
        add_action('edit_tag_form', 'CustomizerFields::addTaxonomyFieldDetail');
        add_action('add_tag_form_fields', 'CustomizerFields::addTaxonomyField');
        foreach (CustomizerTaxonomiesSettings::getAll() as $post_type => $terms) {
            foreach ($terms as $term) {
                $filter_name = $post_type . SI_BOND . $term[SI_KEY];
                add_action($filter_name . '_add_form_fields', 'CustomizerFields::addTaxonomyField');
            }
        }
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
            'CallWpPost.twig',
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
                        wp_enqueue_script('cannotPreview', plugins_url('js/cannotPreview.js', SI_PLUGIN_PATH));
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
        self::displayTaxonomyFields($term->taxonomy, $term->term_id);
    }

    static function displayTaxonomyFields($taxonomy, $term_id = null)
    {
        global $si_twig;
        $conf = [$taxonomy => siSearchTaxonomyConfig($taxonomy)];
        $get_type = is_null($term_id) ? SI_RESOURCE_TYPE_DO_NOT_GET : SI_RESOURCE_TYPE_TERM_META;
        $args = is_null($term_id) ? [] : [
            SI_TERM_ID => $term_id
        ];
        $elements = CustomizerTwigExtension::getRenderElements(
            $conf, $taxonomy, $get_type, $args
        );
        $si_twig->display(
            'CallWpTerm.twig',
            [
                'elements' => $elements,
                'root' => $taxonomy,
                'config' => $conf
            ]
        );
    }
}
