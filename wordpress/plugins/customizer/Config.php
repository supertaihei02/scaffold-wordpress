<?php
/* *******************************
 *       管理画面ログイン
 * *******************************/
define('LOGIN_KEY_PASSWORD', 'framelunch');
define('LOGIN_PAGE', '/wp-console/');

/* *******************************
 *          権限周り
 * *******************************/
// それぞれのRoleを割り当てるUserIDをセットする
define('REL_USER_ROLE', [
    ROLE_SUPER_ADMIN    => ['framelunch'],
    ROLE_ADMIN    => ['admin'],
    ROLE_OPERATOR => ['operator']
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
define('POST_RECRUIT',  'recruit');

define('POST_NEWS',  'news');
define('TAX_NEWS_SEMINAR',  'seminar');
define('TAX_NEWS_RELEASE',  'release');


// ポストタイプやタクソノミーの設定
define('SI_CUSTOM_POST_TYPES', [
    // POST TYPEの設定
    SI_POST_TYPES => [
        [
            // このカスタムポストタイプに投稿できるRole
            SI_ALLOW_ROLES => [ROLE_ADMIN, ROLE_OPERATOR],
            // POST TYPEのID
            SI_KEY  => POST_RECRUIT,
            // POST TYPEの表示名称
            SI_NAME => 'RECRUIT',
            /*
             * < リッチエディタを使用するかどうか >
             * - SI_RICH_EDITOR_NOT_USE    : 全てのRoleで使用しない
             * - SI_RICH_EDITOR_ONLY_ADMIN : ADMIN だけ使用
             * - SI_RICH_EDITOR_USE        : だれでも使用
             */
            SI_USE_RICH_EDITOR => SI_RICH_EDITOR_NOT_USE, 
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
                    SI_KEY  => 'recruit',
                    // 項目のラベル
                    SI_NAME => '募集情報',
                    // 動的に増やせる項目なのかどうか
                    SI_IS_MULTIPLE => false,
                    // 入力項目リスト
                    SI_FIELDS => [
                        // 入力項目
                        [
                            // 項目ID(一意)
                            SI_KEY  => 'detail',
                            // 項目のラベル
                            SI_NAME => '仕事内容',
                            // 入力必須かどうか(SI_IS_MULTIPLE=true の場合は無効)
                            SI_FIELD_IS_REQUIRE => true,
                            /*
                             * 項目の Input Type
                             * - text:         SI_FIELD_TYPE_TEXT
                             * - textarea:     SI_FIELD_TYPE_TEXTAREA
                             * - hidden:       SI_FIELD_TYPE_HIDDEN
                             * - file:         SI_FIELD_TYPE_FILE
                             */
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ],
                        [
                            SI_KEY  => 'eng_name',
                            SI_NAME => '英名',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'requirement',
                            SI_NAME => '応募要項',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ],
                        [
                            SI_KEY  => 'employment_type',
                            SI_NAME => '雇用形態',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'working_hours',
                            SI_NAME => '勤務時間',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'salary',
                            SI_NAME => '給与',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'welfare',
                            SI_NAME => '待遇・福利厚生',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ],
                        [
                            SI_KEY  => 'vacation',
                            SI_NAME => '休日・休暇',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ],
                        [
                            SI_KEY  => 'location',
                            SI_NAME => '勤務地',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
                        ],
                    ]
                ],
                // グループ階層
                [
                    // グループID
                    SI_KEY  => 'links',
                    // 項目のラベル
                    SI_NAME => 'リクルーティングサービスリンク',
                    // 動的に増やせる項目なのかどうか
                    SI_IS_MULTIPLE => true,
                    // 入力項目リスト
                    SI_FIELDS => [
                        // 入力項目
                        [
                            // 項目ID(一意)
                            SI_KEY  => 'link',
                            // 項目のラベル
                            SI_NAME => 'リンクURL',
                            // 入力必須かどうか(SI_IS_MULTIPLE=true の場合は無効)
                            SI_FIELD_IS_REQUIRE => false,
                            /*
                             * 項目の Input Type
                             * - text:         SI_FIELD_TYPE_TEXT
                             * - textarea:     SI_FIELD_TYPE_TEXTAREA
                             * - hidden:       SI_FIELD_TYPE_HIDDEN
                             * - file:         SI_FIELD_TYPE_FILE
                             */
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                        [
                            SI_KEY  => 'name',
                            SI_NAME => '表示名称',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ],
                    ]
                ]
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
            SI_USE_RICH_EDITOR => SI_RICH_EDITOR_USE,
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
            SI_HAS_ARCHIVE => true,
            // Custom Fieldsの設定
            SI_CUSTOM_FIELDS => [
                // グループ階層
                [
                    // グループID
                    SI_KEY  => 'news',
                    // 項目のラベル
                    SI_NAME => '',
                    // 動的に増やせる項目なのかどうか
                    SI_IS_MULTIPLE => false,
                    // 入力項目リスト
                    SI_FIELDS => [
                        // 入力項目
                        [
                            // 項目ID(一意)
                            SI_KEY  => 'topic',
                            // 項目のラベル
                            SI_NAME => '見出し',
                            // 入力必須かどうか(SI_IS_MULTIPLE=true の場合は無効)
                            SI_FIELD_IS_REQUIRE => true,
                            /*
                             * 項目の Input Type
                             * - text:         SI_FIELD_TYPE_TEXT
                             * - textarea:     SI_FIELD_TYPE_TEXTAREA
                             * - hidden:       SI_FIELD_TYPE_HIDDEN
                             * - file:         SI_FIELD_TYPE_FILE
                             */
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
                        ]
                    ]
                ],
            ]
        ],
    ],
    // TAXONOMYの設定
    SI_TAXONOMIES   => [
        // POST TYPEをKEY値にする
        POST_NEWS => [
            [
                // TAXONOMYのID
                SI_KEY  => 'tags',
                // TAXONOMYの表示名称
                SI_NAME => 'カテゴリ',
                // 初期登録する TERM 情報
                SI_DEFAULT => [
                    [
                        SI_KEY => TAX_NEWS_SEMINAR,
                        SI_NAME => 'SEMINAR',
                    ],
                    [
                        SI_KEY => TAX_NEWS_RELEASE,
                        SI_NAME => 'RELEASE',
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