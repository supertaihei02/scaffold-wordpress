<?php
/* *******************************
 *   使用しないメニューを非表示にする
 * *******************************/
function remove_admin_menus() {
    global $menu;

    $roles = [];
    $user = wp_get_current_user();
    if (!empty($user->data)) {
        $user_id = $user->data->user_login;
        $roles = siGetMyRole($user_id);
    }
    
    
    $all_post_types = SI_CUSTOM_POST_TYPES;

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
    if (empty($user_id) || !siCanIDo($user_id, [ROLE_ADMIN, ROLE_SUPER_ADMIN])) {
        // unsetで非表示にするメニューを指定
        unset($menu[20]);       // 固定ページ
    }
    // 本プラグインを利用する限り通常の投稿は利用する必要がない
    unset($menu[5]);            // デフォルトの投稿

    // 自分で作成した Custom Post Type の表示可否
    $positions = array_reduce($all_post_types[SI_POST_TYPES], function ($reduced, $post_type) use ($roles){
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
    }
    
}
add_action( 'admin_init', 'redirect_dashboard' );

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
        
    if (!empty($html)) {
        echo "<style>{$html}</style>";
    }
}
add_action('admin_print_styles', 'admin_preview_css_custom');

// タイトルの表示
add_theme_support( 'custom-header' );
add_theme_support( 'title-tag' );

// 管理ツールバー非表示
add_filter( 'show_admin_bar', '__return_false' );

/* *******************************
 *   ログイン画面
 * *******************************/
add_action( 'login_init', 'admin_login_init');
function admin_login_init()
{
    if(!defined('LOGIN_KEY') || password_verify(LOGIN_KEY_PASSWORD, LOGIN_KEY) === false) {
        header('Location:' . site_url() . '/404.php');
        exit;
    }
}

add_filter( 'site_url', 'admin_login_site_url', 10, 4);
function admin_login_site_url( $url, $path, $orig_scheme, $blog_id)
{
    if(($path == 'wp-login.php' || preg_match( '/wp-login\.php\?action=\w+/', $path) ) && (is_user_logged_in() || strpos( $_SERVER['REQUEST_URI'], LOGIN_PAGE) !== false) ) {
        $url = str_replace( 'wp-login.php', LOGIN_PAGE, $url);
    }
    return $url;
}

add_filter( 'wp_redirect', 'admin_login_wp_redirect', 10, 2);
function admin_login_wp_redirect( $location, $status) {
    if(is_user_logged_in() && strpos( $_SERVER['REQUEST_URI'], LOGIN_PAGE) !== false ) {
        $location = str_replace( 'wp-login.php', LOGIN_PAGE, $location);
    }
    return $location;
}

add_action('wp_logout','redirect_logout_page');
function redirect_logout_page(){
    $url = site_url(LOGIN_PAGE);
    wp_safe_redirect($url);
    exit();
}

/* *******************************
 *   記事一覧のプレビューを別窓で
 * *******************************/
function blankPreview($hook)
{
    if ($hook == 'edit.php') {
        wp_enqueue_script('blankPreview', plugins_url('js/blankPreview.js', __FILE__));
    }
}
add_action( 'admin_enqueue_scripts', 'blankPreview' );