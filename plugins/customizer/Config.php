<?php
phpinfo();
/* *******************************
 *       管理画面ログイン
 * *******************************/
define('LOGIN_KEY_PASSWORD', 'si');
define('LOGIN_PAGE', '/si-console/');

/* *******************************
 *          権限周り
 * *******************************/
// それぞれのRoleを割り当てるUserIDをセットする
define('REL_USER_ROLE', [
    ROLE_SUPER_ADMIN    => ['nakanishi'],
    ROLE_ADMIN    => ['admin'],
    ROLE_OPERATOR => ['mates']
]);

// adminに表示させないページ
define('ADMIN_FORBIDDEN_PAGES', [
    'plugins.php'               // プラグイン
]);

// 一般 Role に表示させないページ
define('BASIC_FORBIDDEN_PAGES', [
    'index.php',                // ダッシュボード
    'edit-comments.php',        // コメント
    'plugins.php',              // プラグイン
    'users.php',                // ユーザー
    'themes.php',               // 外観
    'edit-tags.php',            // 分類
    'options-general.php',      // 設定
    'tools.php',                // ツール
    'upload.php',               // メディア
]);

// 表示させないページに遷移した時のリダイレクト先
define('DEFAULT_PAGE_NAME', 'profile.php');

// 各 Roleの表示させないページを設定する
define('USER_FORBIDDEN_PAGES', [
    ROLE_SUPER_ADMIN => [],
    ROLE_ADMIN => ADMIN_FORBIDDEN_PAGES,
    ROLE_OPERATOR => BASIC_FORBIDDEN_PAGES
]);

/* *******************************
 *          記事の設定
 * *******************************/
// POST TYPE, TAXONOMYのKEYは何度か使うので、define して使う
define('POST_BOOKS',  'column');
define('TAX_BOOKS_CATEGORY',  'category');
define('TAX_BOOKS_REGION',  'region');
define('TAX_BOOKS_RELATION',  'relation');

define('POST_NEWS',  'news');
define('TAX_NEWS_TAGS',  'tags');

// ポストタイプやタクソノミーの設定
define('SI_CUSTOM_POST_TYPES', [
    // POST TYPEの設定
    SI_POST_TYPES => [
        [
            // このカスタムポストタイプに投稿できるRole
            SI_ALLOW_ROLES => [ROLE_ADMIN, ROLE_OPERATOR],
            // POST TYPEのID
            SI_KEY  => POST_BOOKS,
            // POST TYPEの表示名称
            SI_NAME => 'BOOKS',
            /*
             * < リッチエディタを使用するかどうか >
             * - SI_RICH_EDITOR_NOT_USE    : 全てのRoleで使用しない
             * - SI_RICH_EDITOR_ONLY_ADMIN : ADMIN だけ使用
             * - SI_RICH_EDITOR_USE        : だれでも使用
             */
            SI_USE_RICH_EDITOR => SI_RICH_EDITOR_ONLY_ADMIN, 
            // 管理画面でこのPOST_TYPEが表示される順序に関係する数値。それぞれずらすこと。
            SI_MENU_POSITION => 6,
            // 表示件数関連(指定が必要ならそれぞれ -1 を設定する)
            SI_COUNT_TYPE => [
                // 記事一覧
                SI_LIST_COUNT => -1,
                // 関連記事一覧
                SI_RELATED_COUNT => -1
            ],
            // アーカイブを有効にするか否か
            SI_HAS_ARCHIVE => true,
            // Custom Fieldsの設定
            SI_CUSTOM_FIELDS => [
                // グループ階層
                [
                    // グループID
                    SI_KEY  => 'books-base',
                    // 項目のラベル
                    SI_NAME => '基本情報',
                    // 動的に増やせる項目なのかどうか
                    SI_IS_MULTIPLE => false,
                    // 入力項目リスト
                    SI_FIELDS => [
                        // 入力項目
                        [
                            // 項目ID(一意)
                            SI_KEY  => 'img',
                            // 項目のラベル
                            SI_NAME => 'メイン画像(推奨: 882x450)',
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
                        [
                            SI_KEY  => 'author',
                            SI_NAME => '著者',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'price',
                            SI_NAME => '価格',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'spec',
                            SI_NAME => '仕様',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'day_of_issue',
                            SI_NAME => '発行日',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'content',
                            SI_NAME => '本の内容紹介',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ],
                    ]
                ],
                [
                    SI_KEY  => 'books-samples',
                    SI_NAME => 'サンプルページ',
                    SI_IS_MULTIPLE => true,
                    SI_FIELDS => [
                        [
                            // 項目ID(一意)
                            SI_KEY  => 'img',
                            // 項目のラベル
                            SI_NAME => '画像(推奨: 882x450)',
                            // 入力必須かどうか(SI_IS_MULTIPLE=true の場合は無効)
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE
                        ],
                        [
                            SI_KEY  => 'caption',
                            SI_NAME => 'ページ説明',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ],
                    ]
                ],
                [
                    SI_KEY  => 'books-support',
                    SI_NAME => '書籍サポート',
                    SI_IS_MULTIPLE => true,
                    SI_FIELDS => [
                        [
                            SI_KEY  => 'name',
                            SI_NAME => '表示名',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'link',
                            SI_NAME => 'リンク',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                    ]
                ],
            ]
        ],
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
            SI_USE_RICH_EDITOR => SI_RICH_EDITOR_ONLY_ADMIN,
            // 管理画面でこのPOST_TYPEが表示される順序に関係する数値。それぞれずらすこと。
            SI_MENU_POSITION => 7,
            // 表示件数関連(指定が必要ならそれぞれ -1 を設定する)
            SI_COUNT_TYPE => [
                // 記事一覧
                SI_LIST_COUNT => -1,
                // 関連記事一覧
                SI_RELATED_COUNT => -1
            ],
            // アーカイブを有効にするか否か
            SI_HAS_ARCHIVE => false,
            // Custom Fieldsの設定
            SI_CUSTOM_FIELDS => [
                // グループ階層
                [
                    // グループID
                    SI_KEY  => 'news-base',
                    // 項目のラベル
                    SI_NAME => '基本情報',
                    // 動的に増やせる項目なのかどうか
                    SI_IS_MULTIPLE => false,
                    // 入力項目リスト
                    SI_FIELDS => [
                        // 入力項目
                        [
                            // 項目ID(一意)
                            SI_KEY  => 'img',
                            // 項目のラベル
                            SI_NAME => 'メイン画像(推奨: 882x450)',
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
                        [
                            SI_KEY  => 'caption',
                            SI_NAME => 'メイン画像キャプション',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'bold',
                            SI_NAME => '小見出し',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'content',
                            SI_NAME => '内容',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ]
                    ]
                ],
            ]
        ],
    ],
    // TAXONOMYの設定
    SI_TAXONOMIES   => [
        // POST TYPEをKEY値にする
        POST_BOOKS => [
            [
                // TAXONOMYのID
                SI_KEY  => TAX_BOOKS_CATEGORY,
                // TAXONOMYの表示名称
                SI_NAME => 'カテゴリ',
                // 初期登録する TERM 情報
                SI_DEFAULT => []
            ],
            [
                // TAXONOMYのID
                SI_KEY  => TAX_BOOKS_REGION,
                // TAXONOMYの表示名称
                SI_NAME => '地域',
                // 初期登録する TERM 情報
                SI_DEFAULT => []
            ],
            [
                // TAXONOMYのID
                SI_KEY  => TAX_BOOKS_RELATION,
                // TAXONOMYの表示名称
                SI_NAME => '書籍属性',
                // 初期登録する TERM 情報
                SI_DEFAULT => []
            ],
        ],
        POST_NEWS => [
            [
                // TAXONOMYのID
                SI_KEY  => TAX_NEWS_TAGS,
                // TAXONOMYの表示名称
                SI_NAME => 'Tags',
                // 初期登録する TERM 情報
                SI_DEFAULT => [
                    [
                        SI_KEY  => 'news',
                        SI_NAME => 'お知らせ'
                    ],
                    [
                        SI_KEY  => 'update',
                        SI_NAME => '更新情報'
                    ]
                ]
            ]
        ]
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
define('SI_DEFAULT_GET_COUNT', 30);

/* *******************************
 *             関数群
 * *******************************/
function siGetPostTypeConfig($arg_post_type)
{
    $conf = false;
    foreach (SI_CUSTOM_POST_TYPES[SI_POST_TYPES] as $post_type) {
        if ($arg_post_type === $post_type[SI_KEY]) {
            $conf = $post_type;
            break;
        }
    }

    if ($conf === false) {
        throw new Exception("[ {$arg_post_type} ] is not Post Type.");
    }
    
    return $conf;
}

function siGetFieldGroupConfig($arg_post_type, $arg_group_key)
{
    $conf = false;
    foreach (siGetPostTypeConfig($arg_post_type)[SI_CUSTOM_FIELDS] as $group) {
        if ($arg_group_key === $group[SI_KEY]) {
            $conf = $group;
            break;
        }
    }

    if ($conf === false) {
        throw new Exception("[ $arg_post_type => $arg_group_key ] is not exist.");
    }

    return $conf;
}

function siGetTaxonomiesConfig($arg_post_type)
{
    $conf = false;
    foreach (SI_CUSTOM_POST_TYPES[SI_TAXONOMIES] as $post_type_key => $taxonomies) {
        if ($arg_post_type === $post_type_key) {
            $conf = $taxonomies;
            break;
        }
    }

    if ($conf === false) {
        throw new Exception("[ $arg_post_type ] has not Taxonomies.");
    }

    return $conf;
}

function siGetTaxonomyConfig($arg_post_type, $taxonomy_key)
{
    $conf = false;
    foreach (SI_CUSTOM_POST_TYPES[SI_TAXONOMIES] as $post_type_key => $taxonomies) {
        if ($arg_post_type === $post_type_key) {
            foreach ($taxonomies as $taxonomy) {
                if ($taxonomy_key === $taxonomy[SI_KEY]) {
                    $conf = $taxonomy;
                }
            }
            break;
        }
    }

    if ($conf === false) {
        throw new Exception("[ $arg_post_type => $taxonomy_key ] is not Taxonomy.");
    }

    return $conf;
}