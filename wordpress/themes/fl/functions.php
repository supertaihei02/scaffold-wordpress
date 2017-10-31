<?php
$seo_meta = [];
$conditions = [];
$forms = [];
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
     *          Form内容設定
     * *******************************/
    $forms = [
        'contact' => [
            SI_KEY => 'seo',
            SI_NAME => 'SEO基本設定',
            SI_CUSTOM_FIELDS => [
                [
                    SI_KEY => 'base',
                    SI_NAME => 'SEO METAタグ',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'title_separator',
                            SI_NAME => 'タイトル分割記号',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => ' | ',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                        [
                            SI_KEY => 'default_description',
                            SI_NAME => 'Description',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
                            SI_DEFAULT => '',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                        [
                            SI_KEY => 'default_keywords',
                            SI_NAME => 'Keywords',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => '',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                        [
                            SI_KEY => 'default_ogp_image',
                            SI_NAME => 'OG:image',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
                            SI_DEFAULT => '',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                        [
                            SI_KEY => 'google_analytics_key',
                            SI_NAME => 'Google Analytics Key',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => '',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                    ]
                ],
            ]
        ]
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