<?php
$seo_meta = [];
$conditions = [];
if (isActiveCustomizer()) {
    /* *******************************
     *       TDKをまとめて指定する
     * 読み込まれる "Twigファイル名" がKEY
     * *******************************/
    $seo_meta = [
        // Top Page
        SI_PAGE_TYPE_HOME => [
            SI_TITLE => 'HOME title',
            SI_DESCRIPTION => 'HOME desc',
            SI_KEYWORDS => 'HOME keywords',
            SI_OGP_IMAGE => SI_DEFAULT_OGP_IMAGE
        ],
        // 404 Page
        SI_PAGE_TYPE_404 => [
            SI_TITLE => '404 title',
            SI_DESCRIPTION => '',
            SI_KEYWORDS => '',
            SI_OGP_IMAGE => SI_DEFAULT_OGP_IMAGE
        ],
        // NEWS ARCHIVE Page
        SI_PAGE_TYPE_ARCHIVE . SI_HYPHEN . 'news' => [
            SI_TITLE => 'NEWS ARCHIVE title',
            SI_DESCRIPTION => 'NEWS ARCHIVE desc',
            SI_KEYWORDS => 'NEWS ARCHIVE keywords',
            SI_OGP_IMAGE => SI_DEFAULT_OGP_IMAGE
        ],
    ];
    
    /* *******************************
     *   記事取得条件をまとめて指定する
     * *******************************/
    $conditions = [
        // Top Page
        'home' => [
            /*
             * News記事を 8件取得
             */
            'news' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_POST_TYPE => POST_NEWS,
                SI_GET_P_LIMIT => 8,
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
            ]
        ],
        // News Page 
        'news-archive' => [
            'terms' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_T_TAXONOMIES => POST_NEWS.'_categories',
                SI_GET_T_HIDE_EMPTY => false,
                SI_GET_T_TAGS => SiUtils::get($_GET, SI_GET_T_TAGS, -1),
            ],
            'news' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_POST_TYPE => POST_NEWS,
                SI_GET_P_LIMIT => SiUtils::get($_GET, SI_GET_P_LIMIT, 4),
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
                SI_GET_P_PAGE => SiUtils::get($_GET, SI_GET_P_PAGE, 1),
                SI_GET_P_TAGS => SiUtils::get($_GET, SI_GET_P_TAGS, -1),
                SI_GET_P_YEAR => SiUtils::get($_GET, SI_GET_P_YEAR, ''),
            ]
        ],
    ];

    /* *******************************
     * タイトルタグを自動生成する機能を削除
     * *******************************/
    remove_action('wp_head', '_wp_render_title_tag', 1);
}

/**
 * Customizerプラグインの有効チェック
 * @return bool
 */
function isActiveCustomizer()
{
    $plugin = 'customizer/index.php';
    if (function_exists('is_plugin_active')) {
        return is_plugin_active($plugin);
    } else {
        return in_array(
            $plugin,
            get_option('active_plugins')
        );
    }
}