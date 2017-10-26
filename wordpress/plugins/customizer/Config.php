<?php
/* *******************************
 *       タイムゾーン
 * *******************************/
date_default_timezone_set('Asia/Tokyo');

/* *******************************
 *       テンプレートエンジン Twig
 * *******************************/
define('SI_TWIG_DEBUG', true);
define('SI_TWIG_TEMPLATE_DIR', get_template_directory());
define('SI_TWIG_CACHE_DIR', get_template_directory() . '/twig_cache');

/* *******************************
 *       ログ関係
 * *******************************/
// ログの出力先
define('SI_LOG_DIR', __DIR__ . '/log');
// ログレベル
define('SI_LOG_LEVEL', 9);
// ログに時間情報を含める
define('SI_LOG_OUTPUT_TIME', true);

/* *******************************
 *       SEO設定
 * *******************************/
define('SI_TITLE_SEPARATOR', ' | ');
define('SI_DEFAULT_DESCRIPTION', 'default description');
define('SI_DEFAULT_KEYWORDS', 'default,keywords');
define('SI_DEFAULT_OGP_IMAGE', '/wp-content/themes/fl/images/ogp.png');
define('SI_GOOGLE_ANALYTICS_ID', 'XXXXX');

/* *******************************
 *       管理画面ログイン(改修予定)
 * *******************************/
//define('LOGIN_KEY_PASSWORD', 'framelunch');
//define('LOGIN_PAGE', '/wp-console/');

/* *******************************
 *       WP CRONの有効化設定
 * *******************************/
// 主電源。これを入れないと全部動かない
define('CUSTOMIZER_CRON_MAIN_POWER', false);
// モジュールごとの有効化設定
define('CUSTOMIZER_CRON', [
    // 予約投稿機能が利用できない場合は擬似cronを利用する
    'ReservationPost' => true
]);

/* *******************************
 *    権限周り(管理画面の非表示設定)
 * *******************************/
// それぞれのRoleを割り当てるUserIDをセットする
define('REL_USER_ROLE', [
    ROLE_SUPER_ADMIN    => ['framelunch'],
    ROLE_ADMIN    => ['admin'],
    ROLE_OPERATOR => ['operator']
]);

// ==== DATA SET [ USER_FORBIDDEN_PAGES ]====
define('ADMIN_FORBIDDEN_PAGES', [
    'plugins.php'               // プラグイン
]);
define('BASIC_FORBIDDEN_PAGES', [
    'index.php',                // ダッシュボード
    'edit-comments.php',        // コメント
    'plugins.php',              // プラグイン
    'users.php',                // ユーザー
    'themes.php',               // 外観
    'options-general.php',      // 設定
    'tools.php',                // ツール
//    'upload.php',               // メディア
//    'edit-tags.php',            // 分類
]);
// 各 Roleの表示させないページを設定する
define('USER_FORBIDDEN_PAGES', [
    ROLE_SUPER_ADMIN => [],
    ROLE_ADMIN => ADMIN_FORBIDDEN_PAGES,
    ROLE_OPERATOR => BASIC_FORBIDDEN_PAGES
]);

// 表示させないページに遷移した時のリダイレクト先
define('DEFAULT_PAGE_NAME', 'profile.php');

// ==== DATA SET [ USER_HIDDEN_MENUS ]====
define('ADMIN_HIDDEN_MENUS', [
    5,  // デフォルトの「投稿」
]);
define('BASIC_HIDDEN_MENUS', [
    5,  // デフォルトの「投稿」
    20, // 固定ページ
]);
// 各 Roleの表示させないMENUを設定する
define('USER_HIDDEN_MENUS', [
    ROLE_SUPER_ADMIN => ADMIN_HIDDEN_MENUS,
    ROLE_ADMIN => ADMIN_HIDDEN_MENUS,
    ROLE_OPERATOR => BASIC_HIDDEN_MENUS
]);

/* *******************************
 *          記事の設定
 * *******************************/
// POST TYPE, TAXONOMYのKEYは何度か使うので、define して使う
define('POST_NEWS',  'news');

// ポストタイプやタクソノミーの設定
define('SI_CUSTOM_POST_TYPES', [
    // POST TYPEの設定
    SI_POST_TYPES => [
        [
            // このカスタムポストタイプに投稿できるRole
            SI_ALLOW_ROLES => [ROLE_ADMIN, ROLE_OPERATOR],
            // POST TYPEのID
            SI_KEY  => POST_NEWS,
            // POST TYPEの表示名称
            SI_NAME => 'NEWS',
            /*
             * < リッチエディタを使用するかどうか >
             * - SI_RICH_EDITOR_NOT_USE    : 全てのRoleで使用しない
             * - SI_RICH_EDITOR_ONLY_ADMIN : ADMIN だけ使用
             * - SI_RICH_EDITOR_USE        : だれでも使用
             */
            SI_USE_RICH_EDITOR => SI_RICH_EDITOR_USE,
            // 管理画面でこのPOST_TYPEが表示される順序に関係する数値。それぞれずらすこと。
            SI_MENU_POSITION => 7,
            // アーカイブを有効にするか否か
            SI_HAS_ARCHIVE => true,
            // 記事詳細画面がデザイン上なくて、プレビュー機能で一覧画面を表示したい場合は true
            SI_ARCHIVE_PREVIEW => false,
            // 管理画面の一覧画面で独自に並び替えができるようにするかどうか
            SI_USE_ORIGINAL_ORDER => false,
            // Custom Fieldsの設定
            SI_CUSTOM_FIELDS => [
                // グループ階層
                [
                    // グループID
                    SI_KEY  => 'archive',
                    // 項目のラベル
                    SI_NAME => '[一覧画面] 基本情報',
                    // 動的に増やせる項目なのかどうか
                    SI_IS_MULTIPLE => false,
                    // 入力項目リスト
                    SI_FIELDS => [
                        // 入力項目
                        [
                            SI_KEY  => 'img',
                            SI_NAME => 'サムネイル画像[横XXX×縦XXX]',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE
                        ],
                        [
                            SI_KEY  => 'topic',
                            SI_NAME => '見出し',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ]
                    ]
                ],
                [
                    // グループID
                    SI_KEY  => 'single_basic',
                    // 項目のラベル
                    SI_NAME => '[詳細画面] 基本情報',
                    // 動的に増やせる項目なのかどうか
                    SI_IS_MULTIPLE => false,
                    // 入力項目リスト
                    SI_FIELDS => [
                        // 入力項目
                        [
                            SI_KEY  => 'img',
                            SI_NAME => 'メイン画像[横XXX×縦XXX]',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE
                        ]
                    ]
                ],
                [
                    // グループID
                    SI_KEY  => 'single_options',
                    // 項目のラベル
                    SI_NAME => '[詳細画面] 記事情報',
                    // 動的に増やせる項目なのかどうか
                    SI_IS_MULTIPLE => true,
                    // 入力項目リスト
                    SI_FIELDS => [
                        // 入力項目
                        [
                            SI_KEY  => 'img',
                            SI_NAME => '画像[横XXX×縦XXX]',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE
                        ],
                        [
                            SI_KEY  => 'text',
                            SI_NAME => '記事テキスト',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ]
                    ]
                ],
                // SEOのTDKフィールド(基本的にはあった方がいいと思う)
                SI_DEFAULT_SEO_FIELDS
            ],
        ],
    ],
    // TAXONOMYの設定
    SI_TAXONOMIES => [
        // POST TYPEをKEY値にする
        POST_NEWS => [
            [
                // TAXONOMYのID
                SI_KEY  => 'categories',
                // TAXONOMYの表示名称
                SI_NAME => 'カテゴリ',
                // タグの編集が管理画面上からできるようにするかどうか
                SI_TAX_SHOW_UI => true,
                // タクソノミーの入力形式(true: チェックボックス選択, false: テキスト入力)
                SI_TAX_HIERARCHICAL => true,
                // カテゴリの親子関係を作れるか否か(SI_TAX_HIERARCHICALが true の場合のみ有効)
                SI_TAX_USE_HIERARCHICAL_PARENT => false,
                // 初期登録する TERM 情報
                SI_DEFAULT => [],
                // カスタムフィールド
                SI_CUSTOM_FIELDS => [
                    // グループ階層
                    [
                        // グループID
                        SI_KEY  => 'images',
                        // 項目のラベル
                        SI_NAME => 'タグ画像',
                        // 動的に増やせる項目なのかどうか
                        SI_IS_MULTIPLE => false,
                        // 入力項目リスト
                        SI_FIELDS => [
                            // 入力項目
                            [
                                // 項目ID(一意)
                                SI_KEY  => 'img',
                                // 項目のラベル
                                SI_NAME => 'サムネイル画像[横162×縦162]',
                                // 入力必須かどうか(SI_IS_MULTIPLE=true の場合は無効)
                                SI_FIELD_IS_REQUIRE => false,
                                /*
                                 * 項目の Input Type
                                 * - text:         SI_FIELD_TYPE_TEXT
                                 * - textarea:     SI_FIELD_TYPE_TEXTAREA
                                 * - hidden:       SI_FIELD_TYPE_HIDDEN
                                 * - file:         SI_FIELD_TYPE_FILE
                                 */
                                SI_FIELD_TYPE => SI_FIELD_TYPE_FILE
                            ],
                        ]
                    ]
                ]
            ]
        ],
    ],
    /*
     * (上級者向け。っていうかこれ使うならこのプラグイン使わなくていいレベル。)
     * 詳細な設定をしたいPOST TYPEはここに PostTypeKeyを入れて
     * 個別にSocialInnovation_CustomPostTypes.phpに実装する 
     */
    SI_UNIQUE_SETTINGS => []
]);

/* *******************************
 *            描画関連
 *    プラグインコア機能には無関与
 * *******************************/
// テンプレートパートディレクトリ(基本は変更不要)
define('SI_DEFAULT_TEMPLATE_SLUG', 'template-parts/content');
// 複数のPOST TYPEをまたぐ時の投稿取得件数
define('SI_DEFAULT_GET_COUNT', 10);
