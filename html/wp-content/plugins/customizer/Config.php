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
            case SI_SETTING_BACKBONE:
                $setting = [
                    SI_SETTING_BACKBONE => CustomizerFormSettings::backbone()
                ];
                break;
            case SI_SETTING_SEO:
                $setting = [
                    SI_SETTING_SEO => CustomizerFormSettings::seo()
                ];
                break;
            case SI_SETTING_GOOGLE_SPREAD_SHEET:
                $setting = [
                    SI_SETTING_GOOGLE_SPREAD_SHEET => CustomizerFormSettings::google_spread_sheet()
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
    
    /* *******************************
     *      Form設定の中のField設定
     * *******************************/
    /**
     * @param $config
     * @param $field_key
     * @return bool | array
     */
    static function getFieldSetting($config, $field_key)
    {
        $result = false;
        if (!isset($config[SI_CUSTOM_FIELDS])) {
            return $result;
        }

        foreach ($config[SI_CUSTOM_FIELDS] as $field) {
            if ($field[SI_KEY] === $field_key) {
                $result = $field;
                break;
            }
        }
        
        return $result;
    }

    /**
     * @param $config
     * @param $input_key
     * @return bool | array
     */
    static function getInputSetting($config, $input_key)
    {
        $result = false;
        if (!isset($config[SI_FIELDS])) {
            return $result;
        }

        foreach ($config[SI_FIELDS] as $input) {
            if ($input[SI_KEY] === $input_key) {
                $result = $input;
                break;
            }
        }

        return $result;
    }
    
}

/**
 * Post Typeの設定
 * Class CustomizerPostTypeSettings
 */
class CustomizerPostTypeSettings extends CustomizerBaseConfig
{
    static function getAdditionalConfig()
    {
        global $post_types;
        return empty($post_types) ? [] : $post_types;
    }
    
    static function news()
    {
        return [
            SI_FORM_ACTION => SI_FORM_ACTION_SAVE_WP_POST,
            // このカスタムポストタイプに投稿できるRole
            SI_ALLOW_ROLES => [ROLE_ADMIN, ROLE_OPERATOR],
            // POST TYPEのID
            SI_KEY  => 'news',
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
                        ]
                    ]
                ],
                // SEOのTDKフィールド(基本的にはあった方がいいと思う)
                SI_DEFAULT_SEO_FIELDS
            ],
        ];
    }
}

/**
 * Taxonomyの設定
 * Class CustomizerTaxonomiesSettings
 */
class CustomizerTaxonomiesSettings extends CustomizerBaseConfig
{
    static function getAdditionalConfig()
    {
        global $taxonomies;
        return empty($taxonomies) ? [] : $taxonomies;
    }
    
    static function news()
    {
        return [
            [
                SI_FORM_ACTION => SI_FORM_ACTION_SAVE_WP_TERM,
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
                                SI_NAME => 'サムネイル画像',
                                // 入力必須かどうか(SI_IS_MULTIPLE=true の場合は無効)
                                SI_FIELD_IS_REQUIRE => false,
                                SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
                                SI_DEFAULT => 'デフォルト',
                                SI_ELEM_ATTRS => ['width' => 160],
                                SI_ELEM_CLASSES => [],
                                SI_FIELD_CHOICE_VALUES => [],
                                SI_FIELD_OPTION_AUTOLOAD => false,
                                SI_EXTRA => [],
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
    
}

/**
 * Form の設定
 * Class CustomizerFormSettings
 */
class CustomizerFormSettings extends CustomizerBaseConfig
{
    static function getAdditionalConfig()
    {
        global $forms;
        return empty($forms) ? [] : $forms;
    }

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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
                        ],
                        [
                            SI_KEY => 'date',
                            SI_NAME => '日付',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_DATE,
                            SI_DEFAULT => [
                                SI_DATE_EXTRA_TODAY_AFTER => 1,
                                SI_DATE_EXTRA_SET_TIME => '17:00:00',
                            ],
                            SI_ELEM_ATTRS => ['length' => 20],
                            SI_ELEM_CLASSES => ['test', 'date'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [
                                SI_DATE_EXTRA_MIN_DATE_SETTING => [
                                    SI_DATE_EXTRA_TODAY_AFTER => 1,
                                    SI_DATE_EXTRA_SET_TIME => '17:00',    
                                ],
                                SI_DATE_EXTRA_MAX_DATE_SETTING => [
                                    SI_DATE_EXTRA_TODAY_AFTER => 30,
                                    SI_DATE_EXTRA_SET_TIME => '05:00',
                                ],
                            ],
                        ],
                        [
                            SI_KEY => 'time',
                            SI_NAME => '時刻',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TIME,
                            SI_DEFAULT => '17:00',
                            SI_ELEM_ATTRS => [
                                'step' => 1800
                            ],
                            SI_ELEM_CLASSES => ['test', 'time'],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [
                                SI_DATE_EXTRA_MIN_DATE_SETTING => [
                                    SI_DATE_EXTRA_TODAY_AFTER => 1,
                                    SI_DATE_EXTRA_SET_TIME => '17:00',
                                ],
                                SI_DATE_EXTRA_MAX_DATE_SETTING => [
                                    SI_DATE_EXTRA_TODAY_AFTER => 30,
                                    SI_DATE_EXTRA_SET_TIME => '23:30',
                                ],
                            ],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_DEFAULT => SI_BASE_PATH . '/log',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => true,
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
                        ]
                    ]
                ],
                [
                    SI_KEY => 'enable_services',
                    SI_NAME => '拡張機能の有効化設定',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'google_spread_sheet',
                            SI_NAME => 'Google Spread Sheet',
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
                            SI_EXTRA => [],
                        ]
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
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
                            SI_EXTRA => [],
                        ],
                    ]
                ],
            ]
        ];
    }

    static function google_spread_sheet()
    {
        $result = [
            SI_KEY => 'google_spread_sheet',
            SI_NAME => 'SPREAD SHEET設定',
            SI_FORM_ACTION => SI_FORM_ACTION_SAVE_UPDATE,
            SI_CUSTOM_FIELDS => []
        ];
        
        // 共通設定
        $result[SI_CUSTOM_FIELDS][] = [
            SI_KEY => 'common',
            SI_NAME => '共通設定',
            SI_IS_MULTIPLE => false,
            SI_FIELDS => [
                [
                    SI_KEY => 'secrets',
                    SI_NAME => 'クライアント情報ファイルパス',
                    SI_FIELD_IS_REQUIRE => false,
                    SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                    SI_DEFAULT => SI_BASE_PATH . '/expand/google/credentials/client_secret.json',
                    SI_ELEM_ATTRS => ['readonly'],
                    SI_ELEM_CLASSES => [],
                    SI_FIELD_CHOICE_VALUES => [],
                    SI_FIELD_OPTION_AUTOLOAD => true,
                    SI_EXTRA => [],
                ],
                [
                    SI_KEY => 'credentials',
                    SI_NAME => '認証情報ファイルパス',
                    SI_FIELD_IS_REQUIRE => false,
                    SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                    SI_DEFAULT => SI_BASE_PATH . '/expand/google/credentials/client_credentials.json',
                    SI_ELEM_ATTRS => ['readonly'],
                    SI_ELEM_CLASSES => [],
                    SI_FIELD_CHOICE_VALUES => [],
                    SI_FIELD_OPTION_AUTOLOAD => true,
                    SI_EXTRA => [],
                ],
                [
                    SI_KEY => 'auth_button',
                    SI_NAME => '認証情報の作成',
                    SI_FIELD_IS_REQUIRE => false,
                    SI_FIELD_TYPE => SI_FIELD_TYPE_BUTTON,
                    SI_DEFAULT => null,
                    SI_ELEM_ATTRS => [],
                    SI_ELEM_CLASSES => [
                        'auth_google_client', 'button'
                    ],
                    SI_FIELD_CHOICE_VALUES => [],
                    SI_FIELD_OPTION_AUTOLOAD => false,
                    SI_EXTRA => [],
                ]
            ]
        ];
        
        foreach (CustomizerGoogleSpreadSheetSettings::getAll() as $key => $settings) {
            $result[SI_CUSTOM_FIELDS][] = [
                SI_KEY => $key,
                SI_NAME => $settings[SI_NAME],
                SI_IS_MULTIPLE => false,
                SI_FIELDS => [
                    [
                        // ここのkeyは変えない(Javascriptで利用している)
                        SI_KEY => 'spread_sheet_id',
                        SI_NAME => 'Spread Sheet ID',
                        SI_FIELD_IS_REQUIRE => false,
                        SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                        SI_DEFAULT => null,
                        SI_ELEM_ATTRS => [],
                        SI_ELEM_CLASSES => [],
                        SI_FIELD_CHOICE_VALUES => [],
                        SI_FIELD_OPTION_AUTOLOAD => true,
                        SI_EXTRA => [
                            SI_SPREAD_SHEET_TARGET_SHEET_NAME => 'シート1'
                        ],
                    ],
                    [
                        // ここのkeyは変えない(Javascriptで利用している)
                        SI_KEY => 'spread_sheet_url',
                        SI_NAME => 'Spread Sheet URL',
                        SI_FIELD_IS_REQUIRE => false,
                        SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                        SI_DEFAULT => null,
                        SI_ELEM_ATTRS => [],
                        SI_ELEM_CLASSES => [],
                        SI_FIELD_CHOICE_VALUES => [],
                        SI_FIELD_OPTION_AUTOLOAD => true,
                        SI_EXTRA => [],
                    ],
                    [
                        SI_KEY => 'edit_sheet_button',
                        SI_NAME => 'シートを編集する',
                        SI_FIELD_IS_REQUIRE => false,
                        SI_FIELD_TYPE => SI_FIELD_TYPE_LINK_BUTTON,
                        SI_DEFAULT => null,
                        SI_ELEM_ATTRS => [],
                        SI_ELEM_CLASSES => ['button'],
                        SI_FIELD_CHOICE_VALUES => [],
                        SI_FIELD_OPTION_AUTOLOAD => true,
                        SI_EXTRA => [
                            SI_EXTRA_SET_ATTR_NAME => 'spread_sheet_url',
                            SI_LINK_BUTTON_EXTRA_LINK_OPTION_BY_OTHER_ELEMENT => 'spread_sheet_url',
                        ],
                    ],
                    [
                        SI_KEY => 'create_button',
                        SI_NAME => 'Spread Sheetの作成',
                        SI_FIELD_IS_REQUIRE => false,
                        SI_FIELD_TYPE => SI_FIELD_TYPE_BUTTON,
                        SI_DEFAULT => null,
                        SI_ELEM_ATTRS => [
                            'sheet_name' => $settings[SI_NAME],
                            'option_group' => $key
                        ],
                        SI_ELEM_CLASSES => [
                            'create_spread_sheet', 'button'
                        ],
                        SI_FIELD_CHOICE_VALUES => [],
                        SI_FIELD_OPTION_AUTOLOAD => false,
                        SI_EXTRA => [
                            SI_EXTRA_SET_ATTR_NAME => [
                                'spread_sheet_id',
                                'spread_sheet_url'
                            ],
                        ],
                    ]
                ]
            ];
        }
        
        return $result;
    }
}

/**
 * Class CustomizerGoogleSpreadSheetSettings
 */
class CustomizerGoogleSpreadSheetSettings extends CustomizerBaseConfig
{
    static function reservation()
    {
        return [
            SI_KEY => 'reservation',
            SI_NAME => '予約情報',
            SI_FORM_ACTION => SI_FORM_ACTION_SAVE_SPREAD_SHEET,
            SI_CUSTOM_FIELDS => [
                [
                    SI_KEY => 'customer',
                    SI_NAME => 'お客様情報',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'kana',
                            SI_NAME => '予約者氏名（カナ）',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => null,
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                        [
                            SI_KEY => 'contact_method',
                            SI_NAME => 'ご連絡方法',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_RADIO,
                            SI_DEFAULT => 'tel',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['contact_method'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'tel',
                                    SI_NAME => '電話',
                                ],
                                [
                                    SI_KEY => 'mail',
                                    SI_NAME => 'メール',
                                ],
                                [
                                    SI_KEY => 'line',
                                    SI_NAME => 'LINE',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                        [
                            SI_KEY => 'contact',
                            SI_NAME => 'ご連絡先',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => null,
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                    ]
                ],
                [
                    SI_KEY => 'reserve_info',
                    SI_NAME => 'ご予約内容',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'date',
                            SI_NAME => 'ご予約日付',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_DATE,
                            SI_DEFAULT => [
                                SI_DATE_EXTRA_TODAY_AFTER => 1,
                                SI_DATE_EXTRA_SET_TIME => '17:00:00',
                            ],
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [
                                SI_DATE_EXTRA_MIN_DATE_SETTING => [
                                    SI_DATE_EXTRA_TODAY_AFTER => 1,
                                    SI_DATE_EXTRA_SET_TIME => '17:00',
                                ],
                                SI_DATE_EXTRA_MAX_DATE_SETTING => [
                                    SI_DATE_EXTRA_TODAY_AFTER => 30,
                                    SI_DATE_EXTRA_SET_TIME => '05:00',
                                ],
                            ],
                        ],
                        [
                            SI_KEY => 'select',
                            SI_NAME => 'ご来店時間',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_SELECT,
                            SI_DEFAULT => '18:00',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => '18:00',
                                    SI_NAME => '18:00 ~',
                                ],
                                [
                                    SI_KEY => '18:30',
                                    SI_NAME => '18:30 ~',
                                ],
                                [
                                    SI_KEY => '19:00',
                                    SI_NAME => '19:00 ~',
                                ],
                                [
                                    SI_KEY => '19:30',
                                    SI_NAME => '19:30 ~',
                                ],
                                [
                                    SI_KEY => '20:00',
                                    SI_NAME => '20:00 ~',
                                ],
                                [
                                    SI_KEY => '20:30',
                                    SI_NAME => '20:30 ~',
                                ],
                                [
                                    SI_KEY => '21:00',
                                    SI_NAME => '21:00 ~',
                                ],
                                [
                                    SI_KEY => '21:30',
                                    SI_NAME => '21:30 ~',
                                ],
                                [
                                    SI_KEY => '22:00',
                                    SI_NAME => '22:00 ~',
                                ],
                                [
                                    SI_KEY => '22:30',
                                    SI_NAME => '22:30 ~',
                                ],
                                [
                                    SI_KEY => '23:00',
                                    SI_NAME => '23:00 ~',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                        [
                            SI_KEY => 'number_of_people',
                            SI_NAME => 'ご予約人数',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_NUMBER,
                            SI_DEFAULT => 5,
                            SI_ELEM_ATTRS => [ 'min' => 1, 'max' => 9, 'step' => 1 ],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                    ]
                ],
                [
                    SI_KEY => 'other',
                    SI_NAME => 'その他',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'message',
                            SI_NAME => 'メッセージ',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
                            SI_DEFAULT => "その他ご予約に際しまして、ご要望・ご質問などがございましたらご入力下さい。",
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ]
                    ]
                ],
            ]
        ];
    }

    static function test_spread()
    {
        return [
            SI_KEY => 'test_spread',
            SI_NAME => 'テスト',
            SI_FORM_ACTION => SI_FORM_ACTION_SAVE_SPREAD_SHEET,
            SI_CUSTOM_FIELDS => [
                [
                    SI_KEY => 'customer',
                    SI_NAME => 'お客様情報',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'kana',
                            SI_NAME => '予約者氏名（カナ）',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => null,
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                        [
                            SI_KEY => 'contact_method',
                            SI_NAME => 'ご連絡方法',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_RADIO,
                            SI_DEFAULT => 'tel',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => ['contact_method'],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => 'tel',
                                    SI_NAME => '電話',
                                ],
                                [
                                    SI_KEY => 'mail',
                                    SI_NAME => 'メール',
                                ],
                                [
                                    SI_KEY => 'line',
                                    SI_NAME => 'LINE',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                        [
                            SI_KEY => 'contact',
                            SI_NAME => 'ご連絡先',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
                            SI_DEFAULT => null,
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                    ]
                ],
                [
                    SI_KEY => 'reserve_info',
                    SI_NAME => 'ご予約内容',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'date',
                            SI_NAME => 'ご予約日付',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_DATE,
                            SI_DEFAULT => [
                                SI_DATE_EXTRA_TODAY_AFTER => 1,
                                SI_DATE_EXTRA_SET_TIME => '17:00:00',
                            ],
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [
                                SI_DATE_EXTRA_MIN_DATE_SETTING => [
                                    SI_DATE_EXTRA_TODAY_AFTER => 1,
                                    SI_DATE_EXTRA_SET_TIME => '17:00',
                                ],
                                SI_DATE_EXTRA_MAX_DATE_SETTING => [
                                    SI_DATE_EXTRA_TODAY_AFTER => 30,
                                    SI_DATE_EXTRA_SET_TIME => '05:00',
                                ],
                            ],
                        ],
                        [
                            SI_KEY => 'select',
                            SI_NAME => 'ご来店時間',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_SELECT,
                            SI_DEFAULT => '18:00',
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [
                                [
                                    SI_KEY => '18:00',
                                    SI_NAME => '18:00 ~',
                                ],
                                [
                                    SI_KEY => '18:30',
                                    SI_NAME => '18:30 ~',
                                ],
                                [
                                    SI_KEY => '19:00',
                                    SI_NAME => '19:00 ~',
                                ],
                                [
                                    SI_KEY => '19:30',
                                    SI_NAME => '19:30 ~',
                                ],
                                [
                                    SI_KEY => '20:00',
                                    SI_NAME => '20:00 ~',
                                ],
                                [
                                    SI_KEY => '20:30',
                                    SI_NAME => '20:30 ~',
                                ],
                                [
                                    SI_KEY => '21:00',
                                    SI_NAME => '21:00 ~',
                                ],
                                [
                                    SI_KEY => '21:30',
                                    SI_NAME => '21:30 ~',
                                ],
                                [
                                    SI_KEY => '22:00',
                                    SI_NAME => '22:00 ~',
                                ],
                                [
                                    SI_KEY => '22:30',
                                    SI_NAME => '22:30 ~',
                                ],
                                [
                                    SI_KEY => '23:00',
                                    SI_NAME => '23:00 ~',
                                ],
                            ],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                        [
                            SI_KEY => 'number_of_people',
                            SI_NAME => 'ご予約人数',
                            SI_FIELD_IS_REQUIRE => true,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_NUMBER,
                            SI_DEFAULT => 5,
                            SI_ELEM_ATTRS => [ 'min' => 1, 'max' => 9, 'step' => 1 ],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ],
                    ]
                ],
                [
                    SI_KEY => 'other',
                    SI_NAME => 'その他',
                    SI_IS_MULTIPLE => false,
                    SI_FIELDS => [
                        [
                            SI_KEY => 'message',
                            SI_NAME => 'メッセージ',
                            SI_FIELD_IS_REQUIRE => false,
                            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
                            SI_DEFAULT => "その他ご予約に際しまして、ご要望・ご質問などがございましたらご入力下さい。",
                            SI_ELEM_ATTRS => [],
                            SI_ELEM_CLASSES => [],
                            SI_FIELD_CHOICE_VALUES => [],
                            SI_FIELD_OPTION_AUTOLOAD => false,
                            SI_EXTRA => [],
                        ]
                    ]
                ],
            ]
        ];
    }
}