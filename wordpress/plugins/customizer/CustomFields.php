<?php

class CustomizerFields
{
    static function setEvents()
    {
        add_action('add_meta_boxes', 'CustomizerFields::customFields');
        add_action('save_post', 'CustomizerForm::wpPostUpdate');
        add_action('admin_enqueue_scripts', 'CustomizerForm::cannotPreview');
    }

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
    function isSavedCustomValues($post_id)
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
}

/* *******************************
 *   Custom Taxonomies Fields
 * *******************************/
// Hook: 要素を一覧画面に追加するための関数
function addTaxonomyField($taxonomy)
{
    siTaxonomyFormRender($taxonomy);
}

add_action('add_tag_form_fields', 'addTaxonomyField');
foreach (SI_CUSTOM_POST_TYPES[SI_TAXONOMIES] as $post_type => $terms) {
    foreach ($terms as $term) {
        $filter_name = $post_type . SI_BOND . $term[SI_KEY];
        add_action($filter_name . '_add_form_fields', 'addTaxonomyField');
    }
}

// Hook: 要素を詳細画面に追加するための関数
function addTaxonomyFieldDetail($term)
{
    siTaxonomyFormRender($term->taxonomy, $term->slug);
}

add_action('edit_tag_form', 'addTaxonomyFieldDetail');


// Hook: 保存処理
function editTerms($term_id)
{
    $my_nonce = isset($_POST[NONCE_NAME]) ? $_POST[NONCE_NAME] : null;
    if (!wp_verify_nonce($my_nonce, wp_create_nonce(__FILE__))) {
        return false;
    }
    // 処理の必要可否
    if (empty($_POST)) {
        return false;
    }
    if (empty($_POST['taxonomy'])) {
        return false;
    }
    if (empty(siSearchTaxonomyConfig($_POST['taxonomy'])[SI_CUSTOM_FIELDS])) {
        return false;
    }

    foreach (siSearchTaxonomyConfig($_POST['taxonomy'])[SI_CUSTOM_FIELDS] as $custom_field_group) {
        $group_key = $custom_field_group[SI_KEY];
        // 項目保存
        foreach ($custom_field_group[SI_FIELDS] as $custom_field) {
            $data_key = $group_key . SI_BOND . $custom_field[SI_KEY];
            if (!isset($_POST[$data_key])) {
                break;
            }
            update_term_meta(
                $term_id,
                $data_key,
                $_POST[$data_key]
            );
        }
        // multi対応なら、serialも保存する
        if ($custom_field_group[SI_IS_MULTIPLE]) {
            $serial_key = $group_key . SI_BOND . 'serial';
            if (!isset($_POST[$serial_key])) {
                break;
            }
            update_term_meta(
                $term_id,
                $serial_key,
                $_POST[$serial_key]
            );
        }
    }

    return $term_id;
}

add_action('create_term', 'editTerms');
add_action('edit_terms', 'editTerms');

/**
 * APIから呼んで、次のCustomFieldGroupを生成する用途
 */
function siBuildEmptyGroupForTerm()
{
    $serial = $_GET['next_serial'];
    $args = [
        SI_ARRAY_INDEX => 0,
        SI_VALUE_INDEX => $serial,
        SI_IS_FIRST => false,
        SI_IS_LAST => true,
        SI_BEFORE_FIELD_GROUP => $_GET['group_id']
    ];

    foreach (SI_CUSTOM_POST_TYPES[SI_TAXONOMIES] as $post_type => $terms) {
        if ($post_type !== $_GET['post_type']) {
            continue;
        }
        $args[SI_POST_TYPE] = $post_type;
        foreach ($terms as $term_conf) {
            foreach ($term_conf[SI_CUSTOM_FIELDS] as $custom_field_group) {
                if ($custom_field_group[SI_KEY] !== $_GET['field_group']) {
                    continue;
                }
                $args[SI_GROUP_INFO] = $custom_field_group;
                break;
            }
            if (!empty($args[SI_GROUP_INFO])) {
                break;
            }
        }
        if (!empty($args[SI_GROUP_INFO])) {
            break;
        }
    }
    if (isset($args[SI_GROUP_INFO])) {
        drawFrameForTerm($args, $serial);
    }
    die();
}

add_action('wp_ajax_siBuildEmptyGroupForTerm', 'siBuildEmptyGroupForTerm');

function drawFrameForTerm($args, $serial, $post = null, $is_first_in_group = false)
{
    $group_key = $args[SI_GROUP_INFO][SI_KEY] . $serial;
    $group_name = $args[SI_GROUP_INFO][SI_NAME];
    if ($is_first_in_group) {
        echo '<tr><td><hr></td></tr>';
    }
    ?>
    <tr id="<?php echo $group_key; ?>" class="form-field form-required term-name-wrap">
        <th scope="row"><label><?php echo $group_name; ?></label></th>
        <td><?php siRender($post, ['args' => $args], true); ?></td>
    </tr>
    <?php
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

function hiddenField()
{
    echo "<style> .hidden { display: none !important; } </style>";
}

add_action('wp_enqueue_scripts', 'hiddenField');
add_action('admin_enqueue_scripts', 'hiddenField');
