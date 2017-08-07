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
 * 複数件の投稿に対する描画処理
 * 
 * @param $template_slug
 * @param $template_name
 * @param array $args
 * @param string $none_slug
 * @param string $none_name
 * @param string $before
 * @param string $after
 */
function renderPosts($template_name, $args, $before = null, $after = null, $template_slug = SI_DEFAULT_TEMPLATE_SLUG,  $none_slug = 'template-parts/content', $none_name = 'none')
{
    global $post;
    
    // 初期値の設定
    if (!isset($args[SI_GET_P_ORDER_BY])) {
        $args[SI_GET_P_ORDER_BY] = 'date';
        $args[SI_GET_P_ORDER] = 'DESC';
    }
    // Post Typeを複数対応する
    if (isset($args[SI_GET_P_POST_TYPE])) {
        if (is_string($args[SI_GET_P_POST_TYPE])) {
            $args[SI_GET_P_POST_TYPE] = [$args[SI_GET_P_POST_TYPE]];
        }
        $posts = getPostsForRender($args);
    } else {
        $posts = get_posts($args);
    }

    if (!empty($posts)) {
        if (is_callable($before)) {
            $before($posts, $args);
        }
        foreach ($posts as $post) {
            if (isset($args[SI_GET_P_POST_ID]) && 
                $post->ID === $args[SI_GET_P_POST_ID]) {
                continue;
            }
            setup_postdata($post);
            setCustoms($post->ID);
            
            get_template_part($template_slug, $template_name);
        }
        if (is_callable($after)) {
            $after($posts, $args);
        }
        wp_reset_postdata();
        resetPostGlobal();
    } else {
        get_template_part($none_slug, $none_name);
    }
}
add_action('wp_ajax_getPosts', 'getPosts');
add_action('wp_ajax_nopriv_getPosts', 'getPosts');

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

function getPostsForRender($args)
{
    // ページング処理
    $args = (function ($args) {
        if (isset($args[SI_GET_P_PAGE]) && isset($args[SI_GET_P_POST_TYPE]) && isset($args[SI_COUNT_TYPE])) {
            // 複数 POST TYPE対応
            if (count($args[SI_GET_P_POST_TYPE]) === 1) {
                $config = siGetPostTypeConfig(reset($args[SI_GET_P_POST_TYPE]));
                $count = $config[SI_COUNT_TYPE][$args[SI_COUNT_TYPE]];
            } else {
                $count = SI_DEFAULT_GET_COUNT;
            }
            $page = $args[SI_GET_P_PAGE];

            // $argsに条件値を追加
            $args[SI_GET_P_LIMIT] = $count;
            if ($count !== -1) {
                $args[SI_GET_P_OFFSET] = ($page - 1) * $count;
            }
        }
        // get_postsに存在しない条件値を削除
        unset($args[SI_GET_P_PAGE]);
        unset($args[SI_COUNT_TYPE]);
        return $args;
    })($args);

    // Taxonomyによる絞り込み
    $args = (function ($args) {
        if (isset($args[SI_GET_P_TAGS]) && isset($args[SI_GET_P_POST_TYPE]) && $args[SI_GET_P_TAGS] !== -1) {
            foreach ($args[SI_GET_P_POST_TYPE] as $post_type) {
                $taxonomies = siGetTaxonomiesConfig($post_type);
                foreach ($taxonomies as $taxonomy) {
                    $args[SI_GET_P_TAX_QUERY][] = [
                        SI_GET_P_TAX_QUERY_FIELD => 'slug',
                        SI_GET_P_TAX_QUERY_TX => $post_type.'_'.$taxonomy[SI_KEY],
                        SI_GET_P_TAX_QUERY_TERMS => SiUtils::asArray($args[SI_GET_P_TAGS])
                    ];
                }
            }

            // 複数ある場合は OR 条件で指定する
            if (count($args[SI_GET_P_TAX_QUERY]) > 1) {
                $args[SI_GET_P_TAX_QUERY][SI_GET_P_TAX_QUERY_RELATION] = 'OR';
            }
        }
        // get_postsに存在しない条件値を削除
        unset($args[SI_GET_P_TAGS]);
        return $args;
    })($args);

    // --- 取得 ---
    return get_posts($args);
}

/**
 * 1件の投稿に対する描画処理
 * 
 * @param $post_id
 * @param $template_slug
 * @param $template_name
 * @param string $none_slug
 * @param string $none_name
 */
function renderPost($post_id, $template_name, $template_slug = SI_DEFAULT_TEMPLATE_SLUG, $none_slug = 'template-parts/content', $none_name = 'none')
{
    global $post;
    
    // --- 取得 ---
    $post = get_post($post_id);
    if (!empty($post)) {
        setup_postdata($post);
        setCustoms($post->ID);

        get_template_part($template_slug, $template_name);

        wp_reset_postdata();
        resetPostGlobal();
    } else {
        get_template_part($none_slug, $none_name);
    }
}

function resetPostGlobal()
{
    global $si_posts, $si_customs;
    $si_posts = [];
    $si_customs = [];
}

function setCustoms($post_id)
{
    global $si_customs;

    $post = get_post($post_id);
    $post_type = $post->post_type;
    $custom_data_list = get_post_custom($post_id);
    if (!empty($custom_data_list)) {
        $custom_fields_data = [];
        
        // Post Typeごとに値をまとめる
        $this_type_conf = siGetPostTypeConfig($post_type)[SI_CUSTOM_FIELDS];
        foreach ($this_type_conf as $field_group) {
            $group_key = $field_group[SI_KEY];
            foreach ($custom_data_list as $group_and_field_key => $custom_data) {
                // デフォルトで入るデータは弾く
                if (in_array($group_and_field_key, ['_edit_lock', '_edit_last'])) {
                    continue;
                }

                // このField Groupに存在しないカラムは弾く
                if (!in_array($group_and_field_key, getGroupAndFieldNames($post_type, $group_key))) {
                    continue;
                }
                
                // 配列がシリアライズされているので、オブジェクトに直して保存
                $serialized = array_shift($custom_data);
                $custom_fields_data[$group_key][$group_and_field_key] = maybe_unserialize($serialized);
            }
        }
        
        // 使いやすいようにデータをまとめる
        foreach ($custom_fields_data as $group_key => $field_values) {
            $group_conf = siGetFieldGroupConfig($post_type, $group_key);
            if (!$group_conf[SI_IS_MULTIPLE]) {
                // 値が1つもないなら無視
                if (SiUtils::isAllEmpty($field_values)) {
                    continue; 
                }
                // 単一のグループならそのまま 
                $si_customs[$post_id][$group_key] = $field_values;
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
                    if (SiUtils::isAllEmpty($data_set)) {
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
}

function getGroupAndFieldNames($post_type, $arg_group_key)
{
    $names = [];
    foreach (siGetPostTypeConfig($post_type)[SI_CUSTOM_FIELDS] as $field_group) {
        if ($arg_group_key !== $field_group[SI_KEY]) {
            continue;
        }
        foreach ($field_group[SI_FIELDS] as $field) {
            $names[] = $arg_group_key.'-'.$field[SI_KEY];
        }
        break;
    }
    
    return $names;
}

function setPosts($posts)
{
    global $si_posts;
    $si_posts = $posts;
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
function renderTerms($template_name, $args, $template_slug = SI_DEFAULT_TEMPLATE_SLUG,  $none_slug = 'template-parts/content', $none_name = 'none')
{
    // --- 取得 ---
    // taxonomyの指定
    $taxonomies = SiUtils::getRequire($args, SI_GET_T_TAXONOMIES);
    unset($args[SI_GET_T_TAXONOMIES]);
    
    // --- 独自パラメータを取得しておく
    // 指定のTermに印をつける
    $current_terms = SiUtils::get($args, SI_GET_T_TAGS, -1);
    $current_class = SiUtils::get($args, SI_GET_T_CUR_CLASS, 'cur');
    unset($args[SI_GET_T_TAGS]);
    unset($args[SI_GET_T_CUR_CLASS]);

    // DBから取得
    $terms = get_terms(SiUtils::asArray($taxonomies), $args);
    
    if (!empty($terms)) {
        $custom_terms = [];
        $plane = true;

        if ($current_terms === -1) {
            $current_terms = [];
        } else {
            $current_terms = SiUtils::asArray($current_terms);
        }
        
        foreach ($terms as $term) {
            // 指定中のtermには印をつけておく
            if (in_array($term->slug, $current_terms)) {
                $custom_terms[$term->slug][SI_GET_T_CUR_CLASS] = $current_class;
                $plane = false;
            } else {
                $custom_terms[$term->slug][SI_GET_T_CUR_CLASS] = '';
            }
        }

        $custom_terms[SI_TERMS] = $terms;
        $custom_terms[SI_IS_PLANE] = $plane;
        $custom_terms[SI_CUR_CLASS] = $current_class;
        
        setTerms($custom_terms);

        get_template_part($template_slug, $template_name);

        resetTermGlobal();
    } else {
        get_template_part($none_slug, $none_name);
    }
}

function setTerms($terms)
{
    global $si_terms;
    $si_terms = $terms;
}

function resetTermGlobal()
{
    global $si_terms;
    $si_terms = [];
}

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
        if (!empty( $terms )) {
            $result += $terms;
        }
    }

    return $result;
}

/* *******************************
 *        Search 関連
 * *******************************/
/**
 * @param $keyword
 * @param int $page
 * @param int $limit
 * @return WP_Query
 */
function getSearchQuery($keyword, $page = 1, $limit = 10)
{
    global $wpdb;
    $keyword = '%' . $wpdb->esc_like($keyword) . '%';
    $post_ids_meta = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%s'",
        $keyword
    ));
    $post_ids_post = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_title LIKE '%s' OR post_content LIKE '%s'",
        $keyword, $keyword)
    );
    $post_ids = array_merge($post_ids_meta, $post_ids_post);

    $args = array(
        'paged' => $page,
        'posts_per_page' => $limit,
        'post_type' => 'any',
        'post_status' => 'publish',
        'post__in' => $post_ids
    );

    // 検索した結果、マッチしなかったケースでは何もマッチさせない
    if ($keyword !== '%%' && empty($post_ids)) {
        $args = array('ID' => -999 );
    }
    return new WP_Query($args);
}

add_action( 'wp_ajax_get_search_result', 'getSearchResults');
add_action( 'wp_ajax_nopriv_get_search_result', 'getSearchResults');
function getSearchResults()
{
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
    $query = getSearchQuery($keyword, $page, $limit);

    $posts = array();
    while ($query->have_posts()) {
        $query->the_post();
        $data = array();
        $data['post_id'] = get_the_ID();
        $data['title'] = strip_tags(get_the_title());
        $data['link'] = get_the_permalink();
        $posts[] = $data;
    }

    $results = array(
        'search_word' => $keyword,
        'max_page' => $query->max_num_pages,
        'current_page' => $page,
        'found_count' => $query->found_posts,
        'display_count' => $query->post_count,
        'posts' => $posts
    );

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    die();
}
