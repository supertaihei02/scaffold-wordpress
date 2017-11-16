<?php
/* *******************************
 *        ShortCode関連
 * *******************************/
/**
 * スタイルされたリンクの追加
 * 
 * @param $args
 * @return string
 */
function buttonLinkFunc($args)
{
    $link = null;
    $name = null;
    $out = null;
    $target = '_self';
    extract(shortcode_atts([
        'link' => '「link」「name」を設定してください',
        'name' => '「link」「name」を設定してください',
        'out' => 'false'
    ], $args));

    $out = $out === 'true' ? true : false;
    if ($out) {
        $target = '_blank';
    }
    
    return "<div class='article-btn-custom'><a class='btn-green txt-center btn-arrow' target='{$target}' href='$link'><span>{$name}</span></a></div>";
}
add_shortcode('buttonLink', 'buttonLinkFunc');

function textLinkFunc($args)
{
    $link = null;
    $name = null;
    $out = null;
    $target = '_self';
    extract(shortcode_atts([
        'link' => '「link」「name」を設定してください',
        'name' => '「link」「name」を設定してください',
        'out' => 'false'
    ], $args));

    $out = $out === 'true' ? true : false;
    if ($out) {
        $target = '_blank';
    }

    return "<div class='article-lnk-custom'><a target='{$target}' href='$link'><span>{$name}</span></a></div>";
}
add_shortcode('textLink', 'textLinkFunc');

/**
 * 装飾文字の追加
 * 
 * @param $args
 * @return string
 */
function decorationFunc($args)
{
    $style = '';
    $text = '';
    $bold = false;
    $color = false;
    $size = false;
    
    extract(shortcode_atts([
        'text' => '「text」を設定してください',
        'bold' => false,
        'color' => false,
        'size' => false
    ], $args));
    
    $template = '<span style="%s">%s</span>';
    $style .= $color !== false ? " color: {$color};" : '';
    $style .= $size !== false ? " font-size: {$size};" : '';
    $text = $bold !== false ? "<b>{$text}</b>" : $text;
    
    return sprintf($template, $style, $text);
}
add_shortcode('deco', 'decorationFunc');

/* *******************************
 *          投稿関連
 * *******************************/

/**
 * 指定した条件の存在チェック
 * @param $condition
 * @return mixed
 */
function isExist($condition)
{
    $condition = argsInitialize($condition);
    $render_info = getPostsForRender($condition);
    return $render_info->is_exist;
}

/**
 * 指定した条件のカウント
 * @param $condition
 * @return mixed
 */
function countPosts($condition)
{
    $condition = argsInitialize($condition);
    $render_info = getPostsForRender($condition);
    return $render_info->found_posts;
}

/**
 * offsetで減らした件数分
 * 総取得件数を減らす
 * @param $found_posts
 * @param $query
 * @return mixed
 */
function adjustOffsetPagination($found_posts, $query)
{
    if (isset($_GET[SI_GET_P_OFFSET])) {
        return $found_posts - $query->query[SI_GET_P_OFFSET];
    }
    return $found_posts;
}
add_filter('found_posts', 'adjustOffsetPagination', 1, 2 );

/**
 * $postsの中に指定のポストタイプが
 * 1件でも存在するかどうか
 * 
 * @param $posts
 * @param $post_type
 * @return bool
 */
function isExistPostType($posts, $post_type)
{
    $is_exist = false;
    foreach ($posts as $post) {
        if ($post->post_type === $post_type) {
            $is_exist = true;
            break;
        }
    }
    return $is_exist;
}

/**
 * argsの初期値の編集
 * 
 * @param $args
 * @return mixed
 * @throws Exception
 */
function argsInitialize($args)
{
    if (!isset($args[SI_GET_P_POST_TYPE])) {
        throw new Exception(SI_GET_P_POST_TYPE . ' is require argument.');
    }
    // デフォルトの並び順
    if (!isset($args[SI_GET_P_ORDER_BY])) {
        $args[SI_GET_P_ORDER_BY] = 'date';
        $args[SI_GET_P_ORDER] = 'DESC';
    }
    
    // 複数のPOST TYPEに対応
    if (is_string($args[SI_GET_P_POST_TYPE])) {
        $args[SI_GET_P_POST_TYPE] = [$args[SI_GET_P_POST_TYPE]];
    }
    
    // Paging設定
    if (isset($args[SI_GET_P_PAGE])) {
        if (!isset($args[SI_GET_P_LIMIT])) {
            $args[SI_GET_P_LIMIT] = SI_DEFAULT_GET_COUNT;
        }
        $page = $args[SI_GET_P_PAGE];
        $offset = isset($args[SI_GET_P_OFFSET]) && intval($args[SI_GET_P_OFFSET]) !== -1 ? $args[SI_GET_P_OFFSET] : null;
        $args[SI_GET_P_OFFSET] = is_null($offset) ? ($page - 1) * $args[SI_GET_P_LIMIT] : $offset;
    }
    
    // TAGによる絞り込み
    if (isset($args[SI_GET_P_TAGS]) && isset($args[SI_GET_P_POST_TYPE]) && !isGetAllTags($args[SI_GET_P_TAGS])) {
        foreach ($args[SI_GET_P_POST_TYPE] as $post_type) {
            $taxonomies = CustomizerTaxonomiesSettings::get($post_type, []);
            foreach ($taxonomies as $taxonomy) {
                $args[SI_GET_P_TAX_QUERY][] = [
                    SI_GET_P_TAX_QUERY_FIELD => 'slug',
                    SI_GET_P_TAX_QUERY_TX => $post_type.SI_BOND.$taxonomy[SI_KEY],
                    SI_GET_P_TAX_QUERY_TERMS => CustomizerUtils::asArray($args[SI_GET_P_TAGS])
                ];
            }
        }

        // 複数ある場合は OR 条件で指定する
        if (count($args[SI_GET_P_TAX_QUERY]) > 1) {
            $args[SI_GET_P_TAX_QUERY][SI_GET_P_TAX_QUERY_RELATION] = 'OR';
        }
    }

    // Previewモードのパラメータセット
    if (CustomizerUtils::get($_GET, SI_GET_P_IS_PREVIEW, false) && is_numeric(CustomizerUtils::get($_GET, SI_GET_P_POST_ID, false))) {
        // Previewモードセット
        $args[SI_GET_P_IS_PREVIEW] = true;
        // Preview対象のpost_idをセット
        $args[SI_GET_P_POST_ID] = CustomizerUtils::get($_GET, SI_GET_P_POST_ID, 0);
        // Previewなので取得対象に下書きも加える
        $args[SI_GET_P_STATUS] = CustomizerUtils::asArray($args[SI_GET_P_STATUS]);
        $args[SI_GET_P_STATUS][] = SI_GET_P_STATUS_DRAFT;
    }
    
    // 年絞り込みのKEY変換
    if (isset($args[SI_GET_P_YEAR])) {
        $args['year'] = $args[SI_GET_P_YEAR];
    }
    if (isset($args[SI_GET_P_MONTH])) {
        $args['monthnum'] = $args[SI_GET_P_MONTH];
    }
    if (isset($args[SI_GET_P_DAY])) {
        $args['day'] = $args[SI_GET_P_DAY];
    }
    
    // get_postsに存在しない条件値を削除
    unset($args[SI_GET_P_TAGS]);
    unset($args[SI_GET_P_YEAR]);
    unset($args[SI_GET_P_MONTH]);
    unset($args[SI_GET_P_DAY]);

    return $args;
}

/**
 * 全件取得かどうかの判断
 * @param $tags
 * @return bool
 */
function isGetAllTags($tags)
{
    $result = false;
    if (is_numeric($tags) && intval($tags) === -1) {
        $result = true;
    } else if ($tags === 'all') {
        $result = true;
    }

    return $result;
}

/**
 * プレビュー等を考慮した
 * 複数件の記事取得
 * 
 * @param $args
 * @param null $customize
 * @return stdClass
 */
function getPostsForRender($args, $customize = null)
{
    global $post;

    // もしもPreviewモードで、$customizeが指定されていない場合はデフォルトをセット
    if (is_null($customize) && CustomizerUtils::get($args, SI_GET_P_IS_PREVIEW, false) && is_numeric(CustomizerUtils::get($args, SI_GET_P_POST_ID, false))) {
        $customize = function ($args) {
            if (get_the_ID() === intval(CustomizerUtils::get($args, SI_GET_P_POST_ID, false))) {

                $result = true;
                $posts = get_posts([
                    SI_GET_P_STATUS => 'any',
                    SI_GET_P_POST_PARENT => intval(CustomizerUtils::get($args, SI_GET_P_POST_ID, 0)),
                    SI_POST_TYPE => 'revision',
                    SI_GET_P_LIMIT => 1,
                    'sort_column' => 'ID',
                    'sort_order' => 'desc',
                ]);
                if (!empty($posts)) {
                    $result = array_shift($posts);
                }
            } else if (get_post_status() === SI_GET_P_STATUS_PUBLISH) {
                $result = true;
            } else {
                $result = false;
            }

            return $result;
        };
    }

    $wp_query = new WP_Query($args);
    $simple_offset = 0;
    $post_contents = array();
    if ($wp_query->have_posts()) {
        // 投稿の取得
        $post_contents = $wp_query->get_posts();

        $ignore_draft_count = 0;
        $post_contents = array_reduce($post_contents, function($reduced, $post_content)
        use (&$post, $customize, $args, &$ignore_draft_count) {

            $post = $post_content;
            setup_postdata($post);
            $add_post = $post;

            // カスタマイズ
            if (is_callable($customize)) {
                $custom_post = $customize($args);
                if ($custom_post instanceof WP_Post) {
                    $add_post = $custom_post;
                } else if ($custom_post === true) {
                    $add_post = $post_content;
                } else {
                    $add_post = null;
                    $ignore_draft_count++;
                }
            }

            if (!is_null($add_post)) {
                $post_type = $post_content->post_type;
                $post_id = $add_post->ID;
                // 詳細画面へのリンクを付与
                if (CustomizerPostTypeSettings::get($post_type)[SI_ARCHIVE_PREVIEW]) {
                    $add_post->link = site_url() . "/{$post_type}/#{$post_type}{$post_id}";
                } else {
                    $add_post->link = get_the_permalink();
                }
                $reduced[] = $add_post;
            }

            return $reduced;
        }, array());

        wp_reset_postdata();


        // 単純に取得した投稿から数件無視する設定
        $simple_offset = 0;
        if (isset($args[SI_GET_P_SIMPLE_OFFSET]) && is_numeric($args[SI_GET_P_SIMPLE_OFFSET])) {
            $simple_offset = $args[SI_GET_P_SIMPLE_OFFSET];
            $wk_offset = $simple_offset;

            $loop = $wk_offset > 0;
            while ($loop) {
                array_shift($post_contents);
                --$wk_offset;
                $loop = $wk_offset > 0;
            }
        }

        // CustomizeとSIMPLE OFFSETで無視した件数を合算
        $simple_offset += $ignore_draft_count;

        // 連番情報を出力したい場合のINDEXを付与
        $index = 1;
        foreach ($post_contents as &$post_content) {
            $post_content->{SI_INDEX} = $index;
            $index++;
        }
    }
    // 返り値の作成
    $std = new stdClass;
    $std->is_exist = $wp_query->have_posts();
    $std->found_posts = $wp_query->found_posts < $simple_offset
        ? 0
        : $wp_query->found_posts - $simple_offset;

    $std->display_count = count($post_contents);
    $std->posts = $post_contents;

    return $std;
}

/**
 * 複数件の記事情報をTwig用の引数として返す
 * @param $args
 * @param null $customize
 * @return array
 */
function getPostsForTemplate($args, $customize = null)
{
    global $post;
    $template_args = [];
    $args = argsInitialize($args);
    $render_obj = getPostsForRender($args, $customize);
    foreach ($render_obj->posts as $post) {
        $unique_values['link'] = $post->link;
        $unique_values['index'] = $post->index;
        $formatted_arg = formatForTemplate($post, true);
        foreach ($unique_values as $key => $unique_value) {
            $formatted_arg[$key] = $unique_value;
        }
        $template_args[] = $formatted_arg;
    }

    return $template_args;
}

/**
 * 1件の記事情報をTwig用の引数として返す
 * @param $post_id
 * @return array
 */
function getPostForTemplate($post_id)
{
    global $post;

    // Preview対応
    $preview = CustomizerUtils::get($_GET, 'preview', false);
    $preview_id = CustomizerUtils::get($_GET, 'preview_id', false);

    if ($preview !== false && intval($preview_id) === $post_id) {
        $post = (function ($post_id) {
            $posts = get_posts([
                SI_GET_P_STATUS => 'any',
                SI_GET_P_POST_PARENT => $post_id,
                SI_POST_TYPE => 'revision',
                SI_GET_P_LIMIT => 1,
                'sort_column' => 'ID',
                'sort_order' => 'desc',
            ]);
            if (!empty($posts)) {
                $result = array_shift($posts);
            } else {
                $result = get_post($post_id);
            }

            return $result;
        })($post_id);
    } else {
        $post = get_post($post_id);
    }

    if (empty($post)) {
        return [];
    }
    
    return formatForTemplate($post);
}

/**
 * Post1件をテンプレート用に情報整理
 * 
 * @param $post
 * @param bool $force_request
 * @return array
 */
function formatForTemplate($post, $force_request = false)
{
    global $post, $si_customs;
    setup_postdata($post);
    if (empty($si_customs) || $force_request) {
        setCustoms($post->ID);
    }

    $args = [
        'title' => get_the_title(),
        'content' => get_the_content(),
        'link' => get_the_permalink(),
        'date' => get_the_date(),
    ];

    foreach ($si_customs[$post->ID] as $key => $custom) {
        $args[$key] = $custom;
    }

    wp_reset_postdata();
    resetPostGlobal();
    
    return $args;
}

/**
 * グローバル変数のリセット
 */
function resetPostGlobal()
{
    global $si_customs;
    $si_customs = [];
}

/**
 * 1件の記事のカスタムフィールド情報を取得して
 * $si_customsに保存
 * 
 * @param $post_id
 * @return bool
 */
function setCustoms($post_id)
{
    global $si_customs;

    $post = get_post($post_id);
    if ($post->post_parent !== 0) {
        $parent = get_post($post->post_parent);
        $post_type = $parent->post_type;
    } else {
        $post_type = $post->post_type;
    }
    $custom_data_list = get_post_custom($post_id);

    $si_customs[$post_id] = [];
    if (!empty($custom_data_list)) {
        $custom_fields_data = [];
        
        // Post Typeごとに値をまとめる
        $conf = CustomizerPostTypeSettings::get($post_type);
        if ($conf === false) {
            return false;
        }
        $this_type_conf = $conf[SI_CUSTOM_FIELDS];
        foreach ($this_type_conf as $field_group) {
            $group_key = $field_group[SI_KEY];
            foreach ($custom_data_list as $group_and_field_key => $custom_data) {
                // Keyのハイフンを置き換える
                
                $group_and_field_key = str_replace(SI_HYPHEN, SI_BOND, $group_and_field_key);
                // デフォルトで入るデータは弾く
                if (in_array($group_and_field_key, ['_edit_lock', '_edit_last'])) {
                    continue;
                }

                // このField Groupに存在しないカラムは弾く
                if (!in_array($group_and_field_key, getGroupAndFieldNames($post_type, $group_key))) {
                    continue;
                }
                
                // 配列がシリアライズされているので、オブジェクトに直して保存
                $serialized = array();
                if (!empty($custom_data)) {
                    $serialized = array_shift($custom_data);
                    $serialized = maybe_unserialize($serialized);
                }
                $custom_fields_data[$group_key][$group_and_field_key] = $serialized;
            }
        }
        
        // 使いやすいようにデータをまとめる
        foreach ($custom_fields_data as $group_key => $field_values) {
            $group_conf = siGetFieldGroupConfig($post_type, $group_key);
            if (!$group_conf[SI_IS_MULTIPLE]) {
                foreach ($field_values as $field_key => $field_value) {
                    // 単一のグループならそのまま 
                    $field_key = CustomizerUtils::formatKey($group_key, $field_key);
                    $si_customs[$post_id][$group_key][$field_key] = $field_value;
                    // 値が1つもないなら無視
                    if (empty($field_value)) {
                        $si_customs[$post_id][$group_key][$field_key] = null;
                    }                     
                }
            } else {
                // 複数グループなら、必ず同じ数ずつデータを保持しているので、Indexごとに値をまとめる
                $converted_data_list = [];
                $multi_data_set = [];
                foreach ($field_values as $field_key => $field_multi_values) {
                    if (empty($field_multi_values)) { continue; }
                    $field_key = CustomizerUtils::formatKey($group_key, $field_key);
                    foreach ($field_multi_values as $idx => $field_multi_value) {
                        $converted_data_list[$idx][$field_key] = $field_multi_value;
                    }
                }
                
                foreach ($converted_data_list as $data_set) {
                    // 値が1つもないなら無視
                    if (CustomizerUtils::isAllEmpty($data_set)) {
                        continue;
                    }
                    $multi_data_set[] = $data_set;
                }
                $si_customs[$post_id][$group_key] = $multi_data_set;
            }
        }
        
        // この記事のTaxonomy情報を付与する
        $si_customs[$post_id][SI_TAGS] = getCustomTerms($post_id);
    }
    return true;
}

function getGroupAndFieldNames($post_type, $arg_group_key, $term_mode = false, $term_key = null)
{
    $names = [];
    if ($term_mode) {
        $custom_fields = siGetTaxonomyConfig($post_type, $term_key);
    } else {
        $custom_fields = CustomizerPostTypeSettings::get($post_type);
    }
    foreach ($custom_fields[SI_CUSTOM_FIELDS] as $field_group) {
        if ($arg_group_key !== $field_group[SI_KEY]) {
            continue;
        }
        foreach ($field_group[SI_FIELDS] as $field) {
            $names[] = CustomizerForm::bond($arg_group_key, $field[SI_KEY]);
        }
        break;
    }
    
    return $names;
}

function filterForSentence($text)
{
    // short code適用に伴って勝手に p タグがつくのを防止
    remove_filter('the_content', 'wpautop');
    // short code適用
    $text = apply_filters('the_content', $text);
    // p タグがつく機能を元に戻す
    add_filter('the_content', 'wpautop');
    return nl2br(trim($text));
}

/* *******************************
 *        Taxonomy関連
 * *******************************/
/**
 * 指定条件のTermsを全部取得する
 * @param $args
 * @return array
 */
function getTerms($args)
{
    // --- 取得 ---
    // taxonomyの指定
    $taxonomies = CustomizerUtils::getRequire($args, SI_GET_T_TAXONOMIES);
    unset($args[SI_GET_T_TAXONOMIES]);

    // --- 独自パラメータを取得しておく
    // 指定のTermに印をつける
    $current_terms = CustomizerUtils::get($args, SI_GET_T_TAGS, -1);
    $current_class = CustomizerUtils::get($args, SI_GET_T_CUR_CLASS, 'cur');
    unset($args[SI_GET_T_TAGS]);
    unset($args[SI_GET_T_CUR_CLASS]);

    // DBから取得
    $terms = get_terms(CustomizerUtils::asArray($taxonomies), $args);

    if (empty($terms)) {
        return [];
    }

    // Termsをカスタマイズ
    $custom_terms = [];
    $plane = true;

    if ($current_terms === -1) {
        $current_terms = [];
    } else {
        $current_terms = CustomizerUtils::asArray($current_terms);
    }

    foreach ($terms as &$term) {
        // タームのメタ情報を付与
        $term->meta = getFormattedTermMeta($term);

        // 指定中のtermには印をつけておく
        if (in_array($term->slug, $current_terms)) {
            $term->is_current = true;
            $term->current_class = $current_class;
            $plane = false;
            $custom_terms[SI_CUR_CLASS] = $current_class;
        } else {
            $term->is_current = false;
        }
    }
    $custom_terms[SI_TERMS] = $terms;
    $custom_terms[SI_IS_PLANE] = $plane;

    return $custom_terms;
}

/**
 * 投稿についているタクソノミーと
 * そのカスタムフィールド情報を取得する
 * @param $post_id
 * @return array|false|WP_Error
 */
function getCustomTerms($post_id){
    // 投稿 ID から投稿オブジェクトを取得
    $post = get_post($post_id);

    // その投稿から投稿タイプを取得
    $post_type = $post->post_type;

    // その投稿タイプからタクソノミーを取得
    $taxonomies = get_object_taxonomies( $post_type, 'objects' );

    $result = [];
    foreach ($taxonomies as $taxonomy_slug => $taxonomy ) {
        // 投稿に付けられたタームを取得
        $terms = get_the_terms($post->ID, $taxonomy_slug);

        if (empty($terms)) {
            continue;
        }
        // タームのメタ情報を付与
        foreach ($terms as &$term) {
            $term->meta = getFormattedTermMeta($term);
        }
        $result += $terms;
    }

    return $result;
}

function getFormattedTermMeta($term)
{
    $meta_data = [];
    if (empty($term)) {
        return [];
    }
    $custom_data_list = get_term_meta($term->term_id);
    if (!empty($custom_data_list)) {
        $custom_fields_data = [];

        $this_type_conf = siSearchTaxonomyConfig($term->taxonomy);
        if ($this_type_conf === false) {
            return [];
        }
        
        // Post Typeごとに値をまとめる
        foreach ($this_type_conf[SI_CUSTOM_FIELDS] as $field_group) {
            $group_key = $field_group[SI_KEY];

            foreach ($custom_data_list as $group_and_field_key => $custom_data) {
                // デフォルトで入るデータは弾く
                if (in_array($group_and_field_key, ['_edit_lock', '_edit_last'])) {
                    continue;
                }
                // このField Groupに存在しないカラムは弾く
                $group_and_field_key = str_replace($this_type_conf[SI_KEY].SI_BOND, '', $group_and_field_key);
                if (!in_array($group_and_field_key, getGroupAndFieldNames($this_type_conf[SI_POST_TYPE], $group_key, true, $this_type_conf[SI_KEY]))) {
                    continue;
                }
                // 配列がシリアライズされているので、オブジェクトに直して保存
                $serialized = array_shift($custom_data);
                $group_and_field_key = str_replace($group_key.SI_BOND, '', $group_and_field_key);
                $custom_fields_data[$group_key][$group_and_field_key] = maybe_unserialize($serialized);
            }
        }

        // 使いやすいようにデータをまとめる
        foreach ($custom_fields_data as $group_key => $field_values) {
            $group_conf = siGetTaxonomyFieldGroupConfig($term->taxonomy, $group_key);
            if (!$group_conf[SI_IS_MULTIPLE]) {
                // 値が1つもないなら無視
                if (CustomizerUtils::isAllEmpty($field_values)) {
                    continue;
                }
                // 単一のグループならそのまま 
                $meta_data[$group_key] = $field_values;
            } else {
                // 複数グループなら、必ず同じ数ずつデータを保持しているので、Indexごとに値をまとめる
                $converted_data_list = [];
                $multi_data_set = [];
                foreach ($field_values as $field_key => $field_multi_values) {
                    foreach ($field_multi_values as $idx => $field_multi_value) {
                        $converted_data_list[$idx][$field_key] = $field_multi_value;
                    }
                }

                foreach ($converted_data_list as $data_set) {
                    // 値が1つもないなら無視
                    if (CustomizerUtils::isAllEmpty($data_set)) {
                        continue;
                    }
                    $multi_data_set[] = $data_set;
                }
                $meta_data[$group_key] = $multi_data_set;
            }
        }
    }
    
    return $meta_data;
}

/* *******************************
 *        Search 関連
 * *******************************/
/**
 * サイト内検索
 * 投稿・固定ページ全てから検索
 * 
 * @param $args
 * @return WP_Query
 */
function getSearchQuery($args)
{
    global $wpdb;
    // POST_META, POSTSからサイト内検索
    $keyword = '%' . $wpdb->esc_like($args[SI_GET_P_SEARCH_KEYWORDS]) . '%';
    $post_ids_meta = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%s'",
        $keyword
    ));
    $post_ids_post = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_title LIKE '%s' OR post_content LIKE '%s'",
        $keyword, $keyword)
    );
    $post_ids = array_merge($post_ids_meta, $post_ids_post);
    $args[SI_GET_P_POST_IN] = $post_ids;

    // 検索した結果、マッチしなかったケースでは何もマッチさせない
    if ($keyword !== '%%' && empty($post_ids)) {
        $args = array('ID' => -999 );
    }
    return new WP_Query($args);
}

function getSearchForTemplate($args)
{
    global $post;
    $posts = [];
    $wp_query = getSearchQuery($args);
    foreach ($wp_query->get_posts() as $post) {
        $posts[] = formatForTemplate($post, true);
    }

    $next_page = CustomizerUtils::get($args, SI_GET_P_PAGE, 1) + 1;
    $page_total = ceil($wp_query->found_posts / $args[SI_GET_P_LIMIT]);
    $next = $next_page > $page_total ? -1 : $next_page;
    
    return [
        'posts' => $posts,
        'search_word' => $args[SI_GET_P_SEARCH_KEYWORDS],
        'count' => intval($wp_query->found_posts),
        'display_count' => intval($wp_query->post_count),
        'max_page' => intval($page_total),
        'current_page' => intval($args[SI_GET_P_PAGE]),
        'next' => intval($next),
    ];
}

/* *******************************
 *          ブログ基本 情報
 * *******************************/
function getBasicInfo()
{
    ob_start();
    wp_head();
    $wp_head = ob_get_contents();
    ob_end_clean();
    
    return [
        'lang' => get_language_attributes('html'),
        'charset' => get_bloginfo('charset'),
        'wp_head' => $wp_head,
        'body_class' => join( ' ', get_body_class()),
        'ga_tag' => CustomizerDatabase::getOption('seo_base_google_analytics_tag', null, true)
    ];
}

/* *******************************
 *          SEO META 情報
 * *******************************/
function getSeoMeta($key)
{
    global $post, $si_customs, $seo_meta;
    
    $def_desc = CustomizerDatabase::getOption('seo_base_default_description', null, true);
    $def_keywords = CustomizerDatabase::getOption('seo_base_default_keywords', null, true);
    $def_og_image = CustomizerDatabase::getOption('seo_base_default_ogp_image', null, true);
    $blog_name = get_bloginfo('name');
    $ogp_url = empty(get_the_permalink()) ? site_url() : get_the_permalink();
    $defaults = [
        SI_TITLE => get_the_title(),
        SI_DESCRIPTION => $def_desc . ' ' . $blog_name,
        SI_KEYWORDS => $def_keywords . ',' . $blog_name,
        SI_OGP_IMAGE => site_url() . $def_og_image,
    ];

    /*
     * 記事詳細画面のページのメタ情報を取得
     */
    if (!empty($post) && CustomizerUtils::isCustomizeSingle($post->post_type)) {
        // Custom Fieldsの値を取得
        setCustoms($post->ID);
        $custom = CustomizerUtils::get($si_customs, $post->ID, []);
        list($title, $description, $keywords, $ogp_image) = (function ($seo) use ($defaults) {
            $title = CustomizerUtils::get($seo, 'seo-title', $defaults[SI_TITLE]);
            $description = CustomizerUtils::get($seo, 'seo-description', $defaults[SI_DESCRIPTION]);
            $keywords = CustomizerUtils::get($seo, 'seo-keywords', $defaults[SI_KEYWORDS]);
            $ogp_image = CustomizerUtils::get($seo, 'seo-img', $defaults[SI_OGP_IMAGE]);
            
            // 個別ページで設定していない場合はデフォルトをセット
            $description = empty($description) ? $defaults[SI_DESCRIPTION] : $description;
            $keywords = empty($keywords) ? $defaults[SI_KEYWORDS] : $keywords;
            $ogp_image = empty($ogp_image) ? $defaults[SI_OGP_IMAGE] : $ogp_image;
            return [$title, $description, $keywords, $ogp_image];
        })(CustomizerUtils::get($custom, 'seo', []));
    }
    /*
     * 記事詳細画面 "以外の" ページのメタ情報を取得
     * 基本的に functions.php で $seo_meta を定義すること
     */
    else if (isset($seo_meta[$key])) {
        $seo = $seo_meta[$key];
        $title = CustomizerUtils::get($seo, SI_TITLE, $defaults[SI_TITLE]);
        $description = CustomizerUtils::get($seo, SI_DESCRIPTION, $defaults[SI_DESCRIPTION]);
        $keywords = CustomizerUtils::get($seo, SI_KEYWORDS, $defaults[SI_KEYWORDS]);
        $ogp_image = CustomizerUtils::get($seo, SI_OGP_IMAGE, $defaults[SI_OGP_IMAGE]);
    }
    /*
     * $seo_meta に定義されていないページはデフォルトを出力
     */
    else {
        $title = $defaults[SI_TITLE];
        $description = $defaults[SI_DESCRIPTION];
        $keywords = $defaults[SI_KEYWORDS];
        $ogp_image = $defaults[SI_OGP_IMAGE];
    }
    
    return [
        SI_TITLE => CustomizerUtils::title($title),
        SI_DESCRIPTION => $description,
        SI_KEYWORDS => $keywords,
        SI_OGP_IMAGE => $ogp_image,
        SI_OGP_URL => $ogp_url,
        SI_OGP_SITE_NAME => $blog_name
    ];
}