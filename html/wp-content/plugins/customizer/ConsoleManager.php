<?php
/* *******************************
 *   使用しないメニューを非表示にする
 * *******************************/
function remove_admin_menus()
{
    global $menu;

    $roles = [];
    $user = wp_get_current_user();
    if (!empty($user->data)) {
        $user_id = $user->data->user_login;
        $roles = siGetMyRole($user_id);
    }
    
    $all_post_types = CustomizerPostTypeSettings::getAll();

    // 表示したくないMenuを削除 
    if (empty($roles)) {
        foreach (BASIC_FORBIDDEN_PAGES as $menu_name) {
            remove_menu_page($menu_name);
        }
    } else {
        foreach ($roles as $role) {
            $ignore_menus = USER_FORBIDDEN_PAGES[$role];
            foreach ($ignore_menus as $menu_name) {
                remove_menu_page($menu_name);
            }
        }
    }
    
    // システム管理者以外のユーザーの場合は常に削除する項目
    if (empty($roles)) {
        foreach (BASIC_HIDDEN_MENUS as $menu_idx) {
            unset($menu[$menu_idx]);
        }
    } else {
        foreach ($roles as $role) {
            $ignore_menus = USER_HIDDEN_MENUS[$role];
            foreach ($ignore_menus as $menu_idx) {
                unset($menu[$menu_idx]);
            }
        }
    }

    // 自分で作成した Custom Post Type の表示可否
    $positions = array_reduce($all_post_types, function ($reduced, $post_type) use ($roles) {
        $authorized = false;
        foreach ($roles as $role) {
            if (in_array($role, $post_type[SI_ALLOW_ROLES]) || $role === ROLE_SUPER_ADMIN) {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            $reduced[] = $post_type[SI_MENU_POSITION];
        }
        return $reduced;
    }, array());
    foreach ($positions as $position) {
        unset($menu[$position]);
    }
}

add_action('admin_menu', 'remove_admin_menus');

/* *******************************
 *  遷移できるページをコントロールする
 * *******************************/
function redirect_dashboard()
{
    $cut_url = function ($url) {
        $remove = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"];
        return str_replace($remove, '', $url);
    };

    // Ajax通信の場合は画面は無関係なので無視
    if (CustomizerUtils::isWpAjax()) {
        return true;
    }
    
    $user = wp_get_current_user();
    if (!empty($user->data)) {
        $user_id = $user->data->user_login;
        foreach (siGetForbiddenPages($user_id) as $menu_file) {
            $move = $cut_url(admin_url($menu_file));
            if ($_SERVER['PHP_SELF'] === $move) {
                wp_redirect(admin_url(DEFAULT_PAGE_NAME));
                exit();
            }
        }
    } else {
        wp_redirect(admin_url(DEFAULT_PAGE_NAME));
        exit();
    }

    return false;
}

add_action('admin_init', 'redirect_dashboard');

/* *******************************
 *   CSSレベルで非表示にするもの
 * *******************************/
function admin_preview_css_custom()
{
    $user = wp_get_current_user();
    if (!empty($user->data)) {
        $user_id = $user->data->user_login;
    }
    $html = '';
    // ADMIN USERと、通常権限USERに隠す項目
    if (empty($user_id) || !siCanIDo($user_id, [ROLE_SUPER_ADMIN])) {
        // 管理画面の最上部にある「更新」「コメント」「新規投稿」のボタンを隠す
        $html .= ' #wp-admin-bar-updates { display:none; !important; }';
        $html .= ' #wp-admin-bar-comments { display:none; !important; }';
        $html .= ' #wp-admin-bar-new-content { display:none; !important; }';
        // 管理画面の左メニューにある「設定（XML-Sitemap）」「AWS」を非表示にする
        $html .= ' #toplevel_page_amazon-web-services { display:none; !important; }';
    }
    
    // = = = Page単位で隠す項目 = = =
    $base_name = basename($_SERVER["REQUEST_URI"]);
    // タクソノミー編集画面
    if (CustomizerUtils::strposArray($base_name, ['edit-tags.php', 'term.php']) !== false) {
        // Descriptionは要らないから隠す
        $html .= ' .term-description-wrap { display:none; !important; }';
        // SI_TAX_USE_HIERARCHICAL_PARENT がfalseなら「親カテゴリ」を隠す
        $term_conf = siSearchTaxonomyConfig(CustomizerUtils::get($_GET, 'taxonomy', array()));
        if (!CustomizerUtils::get($term_conf, SI_TAX_USE_HIERARCHICAL_PARENT, false)) {
            $html .= ' .term-parent-wrap { display:none; !important; }';
        }
    } 
    // 記事編集画面
    else if (CustomizerUtils::strposArray($base_name, ['post-new', 'post.php']) !== false) {
        // 管理画面記事編集ページにある「タグ追加ボタン」を非表示にする(タグ追加は MENU画面から行う)
        $html .= ' div[id$="-adder"] { display:none; !important; }';
    }

    if (!empty($html)) {
        echo "<style>{$html}</style>";
    }
}

add_action('admin_print_styles', 'admin_preview_css_custom');

// タイトルの表示
add_theme_support('custom-header');
add_theme_support('title-tag');

// 管理ツールバー非表示
add_filter('show_admin_bar', '__return_false');

/* *******************************
 *   ログイン画面
 * *******************************/
//add_action('login_init', 'admin_login_init');
//function admin_login_init()
//{
//    if (!defined('LOGIN_KEY') || password_verify(LOGIN_KEY_PASSWORD, LOGIN_KEY) === false) {
//        header('Location:' . site_url() . '/404.php');
//        exit;
//    }
//}
//
//add_filter('site_url', 'admin_login_site_url', 10, 4);
//function admin_login_site_url($url, $path, $orig_scheme, $blog_id)
//{
//    if (($path == 'wp-login.php' || preg_match('/wp-login\.php\?action=\w+/', $path)) && (is_user_logged_in() || strpos($_SERVER['REQUEST_URI'], LOGIN_PAGE) !== false)) {
//        $url = str_replace('wp-login.php', LOGIN_PAGE, $url);
//    }
//    return $url;
//}
//
//add_filter('wp_redirect', 'admin_login_wp_redirect', 10, 2);
//function admin_login_wp_redirect($location, $status)
//{
//    if (is_user_logged_in() && strpos($_SERVER['REQUEST_URI'], LOGIN_PAGE) !== false) {
//        $location = str_replace('wp-login.php', LOGIN_PAGE, $location);
//    }
//    return $location;
//}
//
//add_action('wp_logout', 'redirect_logout_page');
//function redirect_logout_page()
//{
//    $url = site_url(LOGIN_PAGE);
//    wp_safe_redirect($url);
//    exit();
//}

/* *******************************
 * - 記事一覧のプレビューを別窓で
 * - 記事一覧クイック編集保存時リロード
 * *******************************/
add_action('admin_enqueue_scripts', 'blankPreview');
function blankPreview($hook)
{
    if ($hook == 'edit.php') {
        wp_enqueue_script('blankPreview', plugins_url('js/blankPreview.js', SI_PLUGIN_PATH));
        wp_enqueue_script('inline-edit-post-override', plugins_url('js/inline-edit-post.js', SI_PLUGIN_PATH), array('inline-edit-post'));
    }
}

/* *******************************
 * 順序パラメータによる並び替えに対応
 * *******************************/
add_action('pre_get_posts', 'customOrder');
function customOrder($wp_query)
{
    // 管理画面の時
    if (is_admin()) {
        // アーカイブページ かつ page-attributes をサポートしている時
        if ($wp_query->is_post_type_archive()
            && post_type_supports($wp_query->query_vars['post_type'], 'page-attributes')
        ) {
            if (!isset($wp_query->query_vars['orderby'])) {
                // orderby が明示的に指定されていなければ、順序(menu_order)で並び替える
                $wp_query->query_vars['orderby'] = array(
                    'menu_order' => 'DESC',
                    'date' => 'DESC'
                );
            }
            if (!isset($wp_query->query_vars['order'])) {
                // order が明示的に指定されていなければ、順序(menu_order)の数字が大きい方から並べる為にDESCを指定
                $wp_query->query_vars['order'] = 'DESC';
            }
        }
    }
}

/* *******************************
 *  記事一覧に順序情報を表示させる
 * *******************************/
add_filter( 'manage_posts_columns', 'setCustomColumns' );
function setCustomColumns($columns) {
    if (is_admin()) {
        // 日付は一番後ろに表示したいから一度消す
        unset($columns['date']);
        // 付与されているタグ
        $columns['tag'] = 'タグ';
        // アーカイブページ かつ page-attributes をサポートしている時
        if (is_post_type_archive() && post_type_supports(get_post_type(), 'page-attributes')) {
            $columns['menu_order'] = '優先順位[大きい順]';
        }
        // 日付は一番後ろに表示したいから最後に追加
        $columns['date'] = '日時';
    }
    return $columns;
}

add_action( 'manage_posts_custom_column' , 'customColumns', 10, 2 );
function customColumns($column_name, $post_id)
{

    // 管理画面の時
    if (is_admin()) {
        switch ($column_name) {
            case 'menu_order':
                // アーカイブページ かつ page-attributes をサポートしている時
                if (is_post_type_archive() && post_type_supports(get_post_type(), 'page-attributes')) {
                    echo get_post_field('menu_order', $post_id);
                }
                break;
            case 'tag' :
                // 投稿情報を取得
                $post = get_post($post_id);
                // その投稿タイプからタクソノミーを取得
                $taxonomies = get_object_taxonomies( $post->post_type, 'objects' );

                $tags = [];
                foreach ($taxonomies as $taxonomy_slug => $taxonomy ) {
                    // 投稿に付けられたタームを取得
                    $terms = get_the_terms($post->ID, $taxonomy_slug);
                    if (!empty( $terms )) {
                        $tags += $terms;
                    }
                }
                $tag_names = [];
                foreach ($tags as $tag) {
                    $tag_names[] = $tag->name;
                }
                if (!empty($tag_names)) {
                    echo implode(',', $tag_names);
                } else {
                    echo __('None');
                }
                break;
        }
        
    }
}

/* *******************************
 * 投稿一覧リストの上にタグフィルター
 * *******************************/
function customStrictManagePosts()
{
    global $post_type, $tag;
    
    try {
        $taxonomy_keys = array();
        foreach (CustomizerTaxonomiesSettings::get($post_type) as $conf) {
            $taxonomy_key = "{$post_type}_{$conf[SI_KEY]}";
            if (is_object_in_taxonomy($post_type, $taxonomy_key)) {
                $taxonomy_keys[] = $taxonomy_key;
            }
        }
        
        if (!empty($taxonomy_keys)) {
            $dropdown_options = array(
                'show_option_all' => 'タグ一覧',
                'hide_empty' => 0,
                'hierarchical' => 1,
                'show_count' => 0,
                'orderby' => 'name',
                'selected' => $tag,
                'name' => 'tag',
                'taxonomy' => $taxonomy_keys,
                'value_field' => 'slug'
            );
            wp_dropdown_categories( $dropdown_options );
        }
    } catch (Exception $e) {
        // タクソノミー設定がない
    }
}
add_action('restrict_manage_posts', 'customStrictManagePosts');

//投稿一覧で「全てのタグ」選択時は$_GET['tag']をセットしない
function customLoadEdit()
{
    if (isset($_GET['tag']) && '0' === $_GET['tag']) {
        unset ($_GET['tag']);
    }
}
add_action('load-edit.php', 'customLoadEdit');

// defaultだと絞り込みが post_tag というタグに指定されちゃうのを修正
function restrictForCustomTaxonomy($query) {
    global $pagenow;
    global $typenow;
    
    if ($pagenow === 'edit.php' && !empty($_GET['tag'])) {
        $tax_query = array(
            'relation' => 'OR',
        );
        $filters = get_object_taxonomies($typenow);
        foreach ($filters as $tax_slug) {
            $tax_query[] = array(
                'taxonomy' => $tax_slug,
                'field'    => 'slug',
                'terms'    => array($_GET['tag']),
            );
        }
        $query->tax_query = new WP_Tax_Query($tax_query);
    }
    return $query;
}
add_filter('parse_tax_query','restrictForCustomTaxonomy');

/* *******************************
 *  編集エディタの余計な整形機能を停止       
 * *******************************/
/**
 * @param $init_array
 * @return mixed
 */
function overrideMceOptions( $init_array ) {
    global $allowedposttags;

    $init_array['valid_elements']          = '*[*]';
    $init_array['extended_valid_elements'] = '*[*]';
    $init_array['valid_children']          = '+a[' . implode( '|', array_keys( $allowedposttags ) ) . ']';
    $init_array['indent']                  = true;
    $init_array['wpautop']                 = false;
    $init_array['force_p_newlines']        = false;
    $init_array['apply_source_formatting'] = false;

    return $init_array;
}

add_filter('tiny_mce_before_init', 'overrideMceOptions');

/* *******************************
 *  プレビューボタンのリンク先を変更する       
 * *******************************/
function getPreviewPostLink ($url)
{
    $preview_url = $url;
    $post_type = get_post_type();
    $conf = CustomizerPostTypeSettings::get($post_type);
    if (CustomizerUtils::get($conf, SI_ARCHIVE_PREVIEW, false)) {
        $post_id = get_the_ID();
        $add_query = array(
            'preview' => true,
            'post_id' => $post_id,
        );

        $preview_url = site_url() . '/' . $post_type;
        $preview_url = add_query_arg($add_query, $preview_url);
        $preview_url .= "#{$post_type}{$post_id}";
    }

    return $preview_url;
}
add_filter('preview_post_link', 'getPreviewPostLink');

/* *******************************
 *  設定画面の作成
 * *******************************/
add_action('admin_menu', 'CustomizerSetting::initialize');
