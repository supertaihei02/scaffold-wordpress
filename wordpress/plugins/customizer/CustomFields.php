<?php
/**
 * Custom Fieldの生成
 */
function siCustomFields() {
    global $post;
    $all_post_info = SI_CUSTOM_POST_TYPES;
    foreach ($all_post_info[SI_POST_TYPES] as $post_type) {
        $is_first = true;
        foreach ($post_type[SI_CUSTOM_FIELDS] as $custom_field_group) {

            $value_indexes = [0];
            if ($custom_field_group[SI_IS_MULTIPLE]) {
                // 動的な項目の場合はValueIndex情報を更新する
                $stored_serial = get_post_meta($post->ID, $custom_field_group[SI_KEY]. SI_BOND .'serial', true);
                if (!empty($stored_serial)) {
                    $value_indexes = $stored_serial;
                }
            }

            end($value_indexes);
            $final_key = key($value_indexes);
            $before_group_key = null;
            // 各グループの最初の Name だけ色を変えて見やすくする
            $is_first_in_group = true;
            $first_name_style = "<span style=\"color: #0073aa;\">● %s</span>";
            $others_name_style = "<span>%s</span>";
            foreach ($value_indexes as $array_index => $value_index) {
                // multiの時はmeta_box_keyにくっ付ける
                $group_multi_key = $custom_field_group[SI_IS_MULTIPLE] ? $value_index : '';
                $group_key = $custom_field_group[SI_KEY].$group_multi_key;
                
                $name_template = $is_first_in_group ? $first_name_style : $others_name_style;
                add_meta_box(
                    $group_key,
                    sprintf($name_template, $custom_field_group[SI_NAME]),
                    'siRender',
                    $post_type[SI_KEY],
                    'advanced',
                    // 並び順を保持
                    $is_first ? 'high' : 'sorted',
                    // render関数に渡す引数
                    [
                        SI_IS_FIRST => $is_first,
                        SI_IS_LAST => ($final_key === $array_index),
                        SI_ARRAY_INDEX => $array_index,
                        SI_VALUE_INDEX => $value_index,
                        SI_GROUP_INFO => $custom_field_group,
                        SI_POST_TYPE => $post_type[SI_KEY],
                        SI_BEFORE_FIELD_GROUP => $before_group_key
                    ]
                );
                $is_first = false;
                $is_first_in_group = false;
                $before_group_key = $group_key;
            }
            
            
        }
    }
}
add_action('add_meta_boxes', 'siCustomFields');

/**
 * 出力処理
 * 
 * @param $post
 * @param $args
 * @param bool $term_mode
 */
function siRender($post, $args, $term_mode = false) {
    // 引数
    $args = $args['args'];
    $post_type = $args[SI_POST_TYPE];
    $group_info = $args[SI_GROUP_INFO];
    $fields = $group_info[SI_FIELDS];
    $array_idx = $args[SI_ARRAY_INDEX];
    $value_idx = $args[SI_VALUE_INDEX];
    $bf_group_id = $args[SI_BEFORE_FIELD_GROUP];
    
    // 初回だけ nonce を出力 
    if ($args[SI_IS_FIRST]) {
        wp_nonce_field(wp_create_nonce(__FILE__), NONCE_NAME);
    }
    
    // --- 出力のための情報を整理 ---
    $group_key = $group_info[SI_KEY];
    $serial_number = '';
    $multiple_sign = '';
    $serial_html = '';
    if ($group_info[SI_IS_MULTIPLE]) {
        $serial_number = $value_idx;
        $multiple_sign = '[]';
        
        $serial_html = siGetInput([
            SI_KEY  => 'serial',
            SI_NAME => '',
            SI_FIELD_IS_REQUIRE => false,
            SI_FIELD_TYPE => SI_FIELD_TYPE_HIDDEN
        ], $group_key, $serial_number, $serial_number, $multiple_sign);
    }
    $group_id = $group_key . $serial_number;
    
    // グループ枠 ?>
    <div id="inner-<?php echo $group_id;?>" class="<?php echo $group_key;?>">
        <?php echo $serial_html; ?>
        <?php foreach ($fields as $field) :
            if ($term_mode) {
                $value = empty($post) ? '' : get_term_meta($post, $group_key.SI_BOND.$field[SI_KEY], true);
            } else {
                $value = empty($post) ? '' : get_post_meta($post->ID, $group_key.SI_BOND.$field[SI_KEY], true);
            }
            if (!empty($value) && $group_info[SI_IS_MULTIPLE]) {
                $value = $value[$array_idx];
            }
            $label = siGetLabel($field, $group_key, $serial_number);
            $input = siGetInput($field, $group_key, $value, $serial_number, $multiple_sign);
        // 各項目 ?>    
        <?php echo '<br />'.$label.$input; ?>
        <?php endforeach; ?>
    </div>
    <?php

    if ($group_info[SI_IS_MULTIPLE]) {
        // --- ボタングループ追加 ---
        echo "<div style='text-align: right'>";
        // --- 削除ボタン
        $del_button_class = 'field-del-button button ';
        echo "<div data-group-key=\"{$group_key}\" data-bf-group-id=\"{$bf_group_id}\" data-delete-target=\"{$group_id}\" class=\"{$del_button_class}\" >削除</div>";

        // --- 追加ボタン
        $add_button_class = 'field-add-button button button-primary button-large';
        // 最後だけ、追加ボタンが見えるようにする
        if (!$args[SI_IS_LAST]) {
            $add_button_class .= ' hidden';
        }
        $next_serial = $value_idx + 1;
        echo "<div data-append-target=\"{$group_id}\" data-post-type=\"{$post_type}\" data-field-group=\"{$group_key}\" data-next-serial=\"{$next_serial}\" class=\"{$add_button_class}\" style=\"margin-left: 5px;\" >追加</div>";
        echo "</div>";
    }
}

/**
 * Lavel要素の作成
 * 
 * @param $field_info
 * @param $group_key
 * @param string $serial_number
 * @return string
 */
function siGetLabel($field_info, $group_key, $serial_number = '')
{
    $label = '';
    $id = $group_key .SI_BOND. $field_info[SI_KEY];
    $id = empty($serial_number) ? $id : $id.SI_BOND.$serial_number;
    $require = $field_info[SI_FIELD_IS_REQUIRE] ? '(必須)' : '(任意)';
    switch ($field_info[SI_FIELD_TYPE]) {
        case SI_FIELD_TYPE_TEXT:
        case SI_FIELD_TYPE_TEXTAREA:
        case SI_FIELD_TYPE_FILE:
        case SI_FIELD_TYPE_CHECKBOX:
            $label = "<label for=\"{$id}\">{$field_info[SI_NAME]} {$require} ：</label><br />";
            break;
        default:
            break;
    }
    
    return $label;
}

/**
 * Input要素の作成
 * 
 * @param $field_info
 * @param $group_key
 * @param $value
 * @param string $serial_number
 * @param string $multiple_sign
 * @return string
 */
function siGetInput($field_info, $group_key, $value, $serial_number = '', $multiple_sign = '')
{
    $COMMON_STYLE = "width:90%;";
    $IMAGE_STYLE = "width: 200px;";
    $FILE_SELECT_STYLE = "width: 200px;";
    
    // ID, Nameの作成
    $id = $group_key .SI_BOND. $field_info[SI_KEY];
    $name = $id.$multiple_sign;
    $id = empty($serial_number) ? $id : $id.SI_BOND.$serial_number;
    
    // Require判断(Multiの場合は常に無効)
    $require = $field_info[SI_FIELD_IS_REQUIRE] ? 'required' : '';
    $require = empty($serial_number) ? $require : '';

    // 値をエスケープ
    $value = htmlspecialchars($value);
    
    switch ($field_info[SI_FIELD_TYPE]) {
        case SI_FIELD_TYPE_TEXTAREA:
            $input = "<textarea id=\"{$id}\" name=\"$name\" {$require} style=\"{$COMMON_STYLE}\">{$value}</textarea>";
            break;
        case SI_FIELD_TYPE_TEXT:
        case SI_FIELD_TYPE_HIDDEN:
            $input = "<input type=\"{$field_info[SI_FIELD_TYPE]}\" id=\"{$id}\" name=\"{$name}\" value=\"{$value}\" {$require} style=\"{$COMMON_STYLE}\" />";
            break;
        case SI_FIELD_TYPE_FILE:
            $media_type = 'hidden';
            $value = empty($value) ? $value : esc_url($value);
            $link_id = $id . '-img';
            $input_class = 'readonly';
            $media_class = 'button upload-btn';
            $del_media_class = 'upload-clear-btn';
            $img_html = empty($value) ? "<img src=\"\" style=\"$IMAGE_STYLE\" alt='未選択'>" : "<img style=\"$IMAGE_STYLE\" src=\"{$value}\">";
            $input = "<p id=\"{$id}\">{$img_html}<input type=\"{$media_type}\" name=\"{$name}\" value=\"{$value}\" {$require} style=\"{$COMMON_STYLE}\" class=\"{$input_class}\"/></p>";
            $input .= "<p><a href=\"javascript:void(0);\" data-url-input=\"{$id}\" id=\"{$link_id}\" class=\"{$media_class}\" >ファイル選択</a><a style=\"{$FILE_SELECT_STYLE}\" href=\"javascript:void(0);\" data-url-input=\"{$id}\" class=\"{$del_media_class}\">削除</a></p>";
            break;
        case SI_FIELD_TYPE_CHECKBOX:
            $data_type = 'hidden';
            $checkbox_class = 'chk';
            $value = $value === 'on' ? true : false;
            $checked = boolval($value) ? 'checked' : '';
            $value = boolval($value) ? 'on' : 'off';
            // CHECKBOXはチェックされていない場合はFORMで値が送信されない。
            // しかし、このデータが配列で保存される場合、それでは困るため、実際の保存データばHIDDENに保存する
            $input = "<input type=\"{$field_info[SI_FIELD_TYPE]}\" data-real-value-input=\"{$id}\" class=\"{$checkbox_class}\" {$checked} {$require} />";
            $input .= "<input id=\"{$id}\" name=\"{$name}\" type=\"{$data_type}\" value=\"{$value}\" />";
            break;  
            
        default:
            $input = '';
            break;
    }
    
    return $input;
}

/**
 * 値の保存
 * 
 * @param $post_id
 * @return mixed
 */
function siSaveCustomFields($post_id) {
    global $post;
    $my_nonce = isset($_POST[NONCE_NAME]) ? $_POST[NONCE_NAME] : null;
    if(!wp_verify_nonce($my_nonce, wp_create_nonce(__FILE__))) {
        return false;
    }
    // 処理の必要可否
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return false; }
    if (empty($post)) { return false; }
    if(!current_user_can('edit_post', $post_id)) { return false; }

    foreach (SI_CUSTOM_POST_TYPES[SI_POST_TYPES] as $post_type) {
        if ($post_type[SI_KEY] === $_POST['post_type']) {
            foreach ($post_type[SI_CUSTOM_FIELDS] as $custom_field_group) {
                $group_key = $custom_field_group[SI_KEY];
                // 項目保存
                foreach ($custom_field_group[SI_FIELDS] as $custom_field) {
                    $data_key = $group_key.SI_BOND.$custom_field[SI_KEY];
                    if (!isset($_POST[$data_key])) {
                        break;
                    }

                    update_metadata(
                            'post',
                            $post_id,
                            $data_key,
                            $_POST[$data_key]
                    );
                }
                // multi対応なら、serialも保存する
                if ($custom_field_group[SI_IS_MULTIPLE]) {
                    $serial_key = $group_key. SI_BOND.'serial';
                    if (!isset($_POST[$serial_key])) {
                        break; 
                    }
                    update_metadata(
                        'post',
                        $post_id,
                        $serial_key,
                        $_POST[$serial_key]
                    );
                }
            }
            break;
        }
    }
    return $post_id;
}
add_action('save_post', 'siSaveCustomFields');

/**
 * APIから呼んで、次のCustomFieldGroupを生成する用途
 */
function siBuildEmptyGroup()
{
    $serial = $_GET['next_serial'];
    $args = [
        SI_ARRAY_INDEX => 0,
        SI_VALUE_INDEX => $serial,
        SI_IS_FIRST => false,
        SI_IS_LAST => true,
        SI_BEFORE_FIELD_GROUP => $_GET['group_id']
    ];
    foreach (SI_CUSTOM_POST_TYPES[SI_POST_TYPES] as $post_type) {
        if ($post_type[SI_KEY] !== $_GET['post_type']) {
            continue;
        }
        $args[SI_POST_TYPE] = $post_type[SI_KEY];

        foreach ($post_type[SI_CUSTOM_FIELDS] as $custom_field_group) {
            if ($custom_field_group[SI_KEY] !== $_GET['field_group']) {
                continue;
            }
            $args[SI_GROUP_INFO] = $custom_field_group;
            break;
        }
        break;
    }

    if (isset($args[SI_GROUP_INFO])) {
        drawFrame($args, $serial);
    }
    die();
}
add_action('wp_ajax_siBuildEmptyGroup', 'siBuildEmptyGroup');


function drawFrame($args, $serial, $post = null)
{
    $group_key = $args[SI_GROUP_INFO][SI_KEY] . $serial;
    $group_name = $args[SI_GROUP_INFO][SI_NAME];
    ?>
    <div id="<?php echo $group_key; ?>" class="postbox ">
        <button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">パネルを閉じる: <?php echo $group_name; ?></span><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle ui-sortable-handle"><span><?php echo $group_name; ?></span></h2>
        <div class="inside">
            <?php siRender($post, ['args' => $args]); ?>
        </div>
    </div>
    <?php
}

/**
 * 
 */
function cannotPreview($hook)
{
    global $post;
    
    if ($hook == 'post.php' || $hook == 'post-new.php') {
        if (!empty($post) && in_array($post->post_status, array('auto-draft', 'draft'))) {
            if ($post->post_type != 'page') {
                if (!isSavedCustomValues($post->ID)) {
                    wp_enqueue_script('cannotPreview', plugins_url('js/cannotPreview.js', __FILE__));
                }
            }
        }
    }
}
add_action( 'admin_enqueue_scripts', 'cannotPreview' );

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

/* *******************************
 *   Custom Taxonomies Fields
 * *******************************/
// Hook: 要素を一覧画面に追加するための関数
function addTaxonomyField($taxonomy) {
    siTaxonomyFormRender($taxonomy);
}
add_action( 'add_tag_form_fields', 'addTaxonomyField');
foreach (SI_CUSTOM_POST_TYPES[SI_TAXONOMIES] as $post_type => $terms) {
    foreach ($terms as $term) {
        $filter_name = $post_type . SI_BOND . $term[SI_KEY];
        add_action( $filter_name.'_add_form_fields', 'addTaxonomyField');
    }
}

// Hook: 要素を詳細画面に追加するための関数
function addTaxonomyFieldDetail($term) {
    siTaxonomyFormRender($term->taxonomy, $term->slug);
}
add_action( 'edit_tag_form', 'addTaxonomyFieldDetail');


// Hook: 保存処理
function editTerms($term_id) {
    $my_nonce = isset($_POST[NONCE_NAME]) ? $_POST[NONCE_NAME] : null;
    if(!wp_verify_nonce($my_nonce, wp_create_nonce(__FILE__))) {
        return false;
    }
    // 処理の必要可否
    if (empty($_POST)) { return false; }
    if (empty($_POST['taxonomy'])) { return false; }
    if (empty(siSearchTaxonomyConfig($_POST['taxonomy'])[SI_CUSTOM_FIELDS])) { return false; }
    
    foreach (siSearchTaxonomyConfig($_POST['taxonomy'])[SI_CUSTOM_FIELDS] as $custom_field_group) {
        $group_key = $custom_field_group[SI_KEY];
        // 項目保存
        foreach ($custom_field_group[SI_FIELDS] as $custom_field) {
            $data_key = $group_key.SI_BOND.$custom_field[SI_KEY];
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
            $serial_key = $group_key. SI_BOND. 'serial';
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
add_action( 'create_term', 'editTerms' );
add_action( 'edit_terms', 'editTerms' );

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
    if ($is_first_in_group) { echo '<tr><td><hr></td></tr>'; }
    ?>
    <tr id="<?php echo $group_key; ?>" class="form-field form-required term-name-wrap">
        <th scope="row"><label><?php echo $group_name;?></label></th>
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
            $stored_serial = get_term_meta($term_id, $custom_field_group[SI_KEY]. SI_BOND. 'serial', true);
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
            $group_key = $custom_field_group[SI_KEY].$group_multi_key;

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
