<?php
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

/* *******************************
 *            描画関連
 *    プラグインコア機能には無関与
 * *******************************/
// 複数のPOST TYPEをまたぐ時の投稿取得件数
define('SI_DEFAULT_GET_COUNT', 10);

class CustomizerConfig
{
    /* *******************************
     *          Form 設定
     * *******************************/
    static function getFormSetting($key, $throw = true)
    {
        global $forms;

        if (isset($forms[$key])) {
            return [$key => $forms[$key]];
        }
        
        switch ($key) {
            /* *******************************
             *          CustomFields
             * *******************************/
            case 'news':
                $setting = [
                    'news' => CustomizerPostTypeSettings::news()
                ];
                break;
            /* *******************************
             *              Form
             * *******************************/
            case 'test':
                $setting = [
                    'test' => CustomizerFormSettings::test()
                ];
                break;
            case SI_SETTING_FORM_BACKBONE:
                $setting = [
                    SI_SETTING_FORM_BACKBONE => CustomizerFormSettings::backbone()
                ];
                break;
            case SI_SETTING_FORM_SEO:
                $setting = [
                    SI_SETTING_FORM_SEO => CustomizerFormSettings::seo()
                ];
                break;
            case SI_SETTING_FORM_ALL:
                $setting = [
                    // 'test' => self::test(),
                    SI_SETTING_FORM_BACKBONE => CustomizerFormSettings::backbone(),
                    SI_SETTING_FORM_SEO => CustomizerFormSettings::seo(),
                ];
                break;
            default:
                if ($throw) {
                    throw new Exception("{$key} is no exist.");
                }
                $setting = false;
                break;
        }
        
        return $setting;
    }

    
}

class CustomizerPostTypeSettings
{
    /**
     * @param $post_type
     * @param bool $default
     * @return array | bool
     */
    static function get($post_type, $default = false)
    {
        $result = $default;
        $callable = "CustomizerPostTypeSettings::{$post_type}";
        if (is_callable($callable)) {
            $result = $callable();
        }

        return $result;
    }
    
    static function getAll()
    {
        return [
            'news' => self::news()
        ];
    }
    static function news()
    {
        return [
            SI_FORM_ACTION => SI_FORM_ACTION_SAVE_WP_POST,
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
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
                            SI_DEFAULT => null,
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY  => 'topic',
                            SI_NAME => '見出し',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
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
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
                            SI_DEFAULT => null,
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
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
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
                            SI_DEFAULT => null,
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY  => 'text',
                            SI_NAME => '記事テキスト',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
                            SI_DEFAULT => '記事本文を記載します',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ]
                    ]
                ],
                // SEOのTDKフィールド(基本的にはあった方がいいと思う)
                SI_DEFAULT_SEO_FIELDS
            ],
        ];
    }
}

class CustomizerTaxonomiesSettings
{
    /**
     * @param $post_type
     * @param bool $default
     * @return array | bool
     */
    static function get($post_type, $default = false)
    {
        $result = $default;
        $callable = "CustomizerTaxonomiesSettings::{$post_type}";
        if (is_callable($callable)) {
            $result = $callable();
        }

        return $result;
    }
    
    static function getAll()
    {
        return [
            'news' => self::news()
        ];
    }
    
    static function news()
    {
        return [
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
        ];
    }
    
}

/**
 * Form の設定を返すクラス
 * Class CustomizerFormSettings
 */
class CustomizerFormSettings
{
    static function test()
    {
        return [
            SI_KEY => 'test',
            SI_NAME => '全種類テスト',
            SI_FORM_ACTION => SI_FORM_ACTION_SAVE_UPDATE,
            SI_CUSTOM_FIELDS => [
                [
                    SI_KEY => 'single',
                    SI_NAME => 'シングル',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'text',
                            SI_NAME => 'テキスト',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => ['length' => 20],
                            SI_ELEM_CLASSES => ['test', 'text'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'textarea',
                            SI_NAME => 'テキストエリア',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'textarea'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'checkbox',
                            SI_NAME => 'チェックボックス',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_CHECKBOX,
                            SI_DEFAULT => ['banana', 'orange'],
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'checkbox'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'apple',
                                    SI_NAME => 'りんご',
                                ],
                                [
                                    SI_KEY => 'banana',
                                    SI_NAME => 'バナナ',
                                ],
                                [
                                    SI_KEY => 'orange',
                                    SI_NAME => 'オレンジ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'hidden',
                            SI_NAME => '見えない',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_HIDDEN,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'hidden'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'file',
                            SI_NAME => 'ファイル',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
                            SI_DEFAULT => '',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'file'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'radio',
                            SI_NAME => 'ラジオボタン',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_RADIO,
                            SI_DEFAULT => 'on',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'checkbox'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'on',
                                    SI_NAME => 'オン',
                                ],
                                [
                                    SI_KEY => 'off',
                                    SI_NAME => 'オフ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'select',
                            SI_NAME => 'セレクトボックス',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_SELECT,
                            SI_DEFAULT => 'orange',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'select'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'apple',
                                    SI_NAME => 'りんご',
                                ],
                                [
                                    SI_KEY => 'banana',
                                    SI_NAME => 'バナナ',
                                ],
                                [
                                    SI_KEY => 'orange',
                                    SI_NAME => 'オレンジ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'number',
                            SI_NAME => 'ナンバー',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_NUMBER,
                            SI_DEFAULT => 5,
                            SI_ELEM_ATTRS => [ 'min' => 1, 'max' => 9, 'step' => 1 ],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                    ]
                ],
                [
                    SI_KEY => 'multi',
                    SI_NAME => 'マルチ',
                    SI_IS_MULTIPLE => true,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'text',
                            SI_NAME => 'テキスト',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => ['length' => 20],
                            SI_ELEM_CLASSES => ['test', 'text'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'textarea',
                            SI_NAME => 'テキストエリア',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'textarea'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'checkbox',
                            SI_NAME => 'チェックボックス',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_CHECKBOX,
                            SI_DEFAULT => ['banana', 'orange'],
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'checkbox'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'apple',
                                    SI_NAME => 'りんご',
                                ],
                                [
                                    SI_KEY => 'banana',
                                    SI_NAME => 'バナナ',
                                ],
                                [
                                    SI_KEY => 'orange',
                                    SI_NAME => 'オレンジ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'hidden',
                            SI_NAME => '見えない',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_HIDDEN,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'hidden'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'file',
                            SI_NAME => 'ファイル',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
                            SI_DEFAULT => '',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'file'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'radio',
                            SI_NAME => 'ラジオボタン',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_RADIO,
                            SI_DEFAULT => 'on',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'checkbox'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'on',
                                    SI_NAME => 'オン',
                                ],
                                [
                                    SI_KEY => 'off',
                                    SI_NAME => 'オフ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'select',
                            SI_NAME => 'セレクトボックス',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_SELECT,
                            SI_DEFAULT => 'orange',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'select'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'apple',
                                    SI_NAME => 'りんご',
                                ],
                                [
                                    SI_KEY => 'banana',
                                    SI_NAME => 'バナナ',
                                ],
                                [
                                    SI_KEY => 'orange',
                                    SI_NAME => 'オレンジ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'number',
                            SI_NAME => 'ナンバー',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_NUMBER,
                            SI_DEFAULT => 5,
                            SI_ELEM_ATTRS => [ 'min' => 1, 'max' => 9, 'step' => 1 ],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                    ]
                ],
                [
                    SI_KEY => 'multi2',
                    SI_NAME => 'マルチ2',
                    SI_IS_MULTIPLE => true,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'text',
                            SI_NAME => 'テキスト',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => ['length' => 20],
                            SI_ELEM_CLASSES => ['test', 'text'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'textarea',
                            SI_NAME => 'テキストエリア',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'textarea'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'checkbox',
                            SI_NAME => 'チェックボックス',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_CHECKBOX,
                            SI_DEFAULT => ['banana', 'orange'],
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'checkbox'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'apple',
                                    SI_NAME => 'りんご',
                                ],
                                [
                                    SI_KEY => 'banana',
                                    SI_NAME => 'バナナ',
                                ],
                                [
                                    SI_KEY => 'orange',
                                    SI_NAME => 'オレンジ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'hidden',
                            SI_NAME => '見えない',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_HIDDEN,
                            SI_DEFAULT => 'デフォルト',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'hidden'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'file',
                            SI_NAME => 'ファイル',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
                            SI_DEFAULT => '',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'file'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'radio',
                            SI_NAME => 'ラジオボタン',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_RADIO,
                            SI_DEFAULT => 'on',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'checkbox'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'on',
                                    SI_NAME => 'オン',
                                ],
                                [
                                    SI_KEY => 'off',
                                    SI_NAME => 'オフ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'select',
                            SI_NAME => 'セレクトボックス',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_SELECT,
                            SI_DEFAULT => 'orange',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['test', 'select'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'apple',
                                    SI_NAME => 'りんご',
                                ],
                                [
                                    SI_KEY => 'banana',
                                    SI_NAME => 'バナナ',
                                ],
                                [
                                    SI_KEY => 'orange',
                                    SI_NAME => 'オレンジ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                        [
                            SI_KEY => 'number',
                            SI_NAME => 'ナンバー',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_NUMBER,
                            SI_DEFAULT => 5,
                            SI_ELEM_ATTRS => [ 'min' => 1, 'max' => 9, 'step' => 1 ],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                        ],
                    ]
                ]
            ]
        ];
    }

    static function backbone()
    {
        return [
            SI_KEY => 'backbone',
            SI_NAME => '基幹設定',
            SI_FORM_ACTION => SI_FORM_ACTION_SAVE_UPDATE,
            SI_CUSTOM_FIELDS => [
                [
                    SI_KEY => 'log',
                    SI_NAME => 'ログ',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'output_dir',
                            SI_NAME => 'ログ出力先ディレクトリ',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => __DIR__ . '/log',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                        [
                            SI_KEY => 'level',
                            SI_NAME => 'ログレベル',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_NUMBER,
                            SI_DEFAULT => 1,
                            SI_ELEM_ATTRS => [ 'min' => 1, 'max' => 9, 'step' => 1 ],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                        [
                            SI_KEY => 'include_time',
                            SI_NAME => 'ログに時間情報を含めるかどうか',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_RADIO,
                            SI_DEFAULT => 'on',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'on',
                                    SI_NAME => 'オン',
                                ],
                                [
                                    SI_KEY => 'off',
                                    SI_NAME => 'オフ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                    ]
                ],
                [
                    SI_KEY => 'template',
                    SI_NAME => 'テンプレートエンジン',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'debug_mode',
                            SI_NAME => 'デバッグモード',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_RADIO,
                            SI_DEFAULT => 'off',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'on',
                                    SI_NAME => 'オン',
                                ],
                                [
                                    SI_KEY => 'off',
                                    SI_NAME => 'オフ',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                        [
                            SI_KEY => 'theme_template_dir',
                            SI_NAME => 'テンプレートファイル置き場',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => get_template_directory(),
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                        [
                            SI_KEY => 'theme_template_cache_dir',
                            SI_NAME => 'テンプレートキャッシュ場所',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => get_template_directory() . '/twig_cache',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                    ]
                ],
            ]
        ];
    }

    static function seo()
    {
        return [
            SI_KEY => 'seo',
            SI_NAME => 'SEO基本設定',
            SI_FORM_ACTION => SI_FORM_ACTION_SAVE_UPDATE,
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
                            SI_KEY => 'google_analytics_tag',
                            SI_NAME => 'Google Analytics Tag',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
                            SI_DEFAULT => '',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                        ],
                    ]
                ],
            ]
        ];
    }
}