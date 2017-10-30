<?php
$seo_meta = [];
$conditions = [];
if (isActiveCustomizer()) {
    /* *******************************
     * TDKをページごとにまとめて指定する
     * 読み込まれる "Twigファイル名(拡張子なし)"
     * をKEYとして設定する
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
        // News Archive Page
        SI_PAGE_TYPE_ARCHIVE . SI_HYPHEN . 'news' => [
            SI_TITLE => 'NEWS ARCHIVE title',
            SI_DESCRIPTION => 'NEWS ARCHIVE desc',
            SI_KEYWORDS => 'NEWS ARCHIVE keywords',
            SI_OGP_IMAGE => SI_DEFAULT_OGP_IMAGE
        ],
    ];
    
    /* *******************************
     * 記事取得条件をページごとにまとめて指定する
     * 読み込まれる "Twigファイル名(拡張子なし)"
     * をKEYとして設定する
     * onLoad に指定した条件はページ読み込み時に
     * その条件で記事を取得したものが渡される
     * *******************************/
    $conditions = [
        // Top Page
        SI_PAGE_TYPE_HOME => [
            /*
             * [読み込み時]
             */
            'onLoad' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_POST_TYPE => POST_NEWS,
                SI_GET_P_LIMIT => 8,
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
            ]
        ],
        // Search Page
        SI_PAGE_TYPE_SEARCH => [
            /*
             * [読み込み時]
             */
            'onLoad' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_SEARCH_KEYWORDS => CustomizerUtils::get($_GET, SI_GET_P_SEARCH_KEYWORDS),
                SI_GET_P_PAGE => CustomizerUtils::get($_GET, SI_GET_P_PAGE, 1),
                SI_GET_P_POST_TYPE => CustomizerUtils::get($_GET, SI_POST_TYPE, 'any'),
                SI_GET_P_LIMIT => CustomizerUtils::get($_GET, SI_GET_P_LIMIT, 15),
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
            ]
        ],
        // News Archive Page
        SI_PAGE_TYPE_ARCHIVE . SI_HYPHEN . 'news' => [
            /*
             * [読み込み時]
             */
            'onLoad' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_POST_TYPE => POST_NEWS,
                SI_GET_P_LIMIT => CustomizerUtils::get($_GET, SI_GET_P_LIMIT, 4),
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
                SI_GET_P_PAGE => CustomizerUtils::get($_GET, SI_GET_P_PAGE, 1),
                SI_GET_P_TAGS => CustomizerUtils::get($_GET, SI_GET_P_TAGS, -1),
                SI_GET_P_YEAR => CustomizerUtils::get($_GET, SI_GET_P_YEAR, ''),
            ],
            'api' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_POST_TYPE => POST_NEWS,
                SI_GET_P_LIMIT => CustomizerUtils::get($_GET, SI_GET_P_LIMIT, 2),
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
                SI_GET_P_PAGE => CustomizerUtils::get($_GET, SI_GET_P_PAGE, 1),
                SI_GET_P_TAGS => CustomizerUtils::get($_GET, SI_GET_P_TAGS, -1),
                SI_GET_P_YEAR => CustomizerUtils::get($_GET, SI_GET_P_YEAR, ''),
            ],
            'terms' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_T_TAXONOMIES => POST_NEWS.'_categories',
                SI_GET_T_HIDE_EMPTY => false,
                SI_GET_T_TAGS => CustomizerUtils::get($_GET, SI_GET_T_TAGS, -1),
            ],
        ],
        // News Single Page
        SI_PAGE_TYPE_SINGLE . SI_HYPHEN . 'news' => [
            /*
             * [読み込み時]
             */
            'onLoad' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_POST_TYPE => POST_NEWS,
                SI_GET_P_LIMIT => CustomizerUtils::get($_GET, SI_GET_P_LIMIT, 4),
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
                SI_GET_P_PAGE => CustomizerUtils::get($_GET, SI_GET_P_PAGE, 1),
                SI_GET_P_TAGS => CustomizerUtils::get($_GET, SI_GET_P_TAGS, -1),
                SI_GET_P_YEAR => CustomizerUtils::get($_GET, SI_GET_P_YEAR, ''),
            ],
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