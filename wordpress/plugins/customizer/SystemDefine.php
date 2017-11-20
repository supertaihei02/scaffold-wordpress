<?php
/* *******************************
 *     システム(ここは触らない)
 * *******************************/
// Role - 権限セットの種類
define('ROLE_SUPER_ADMIN',        'super_admin');    // [素のWordpressを触れる人]
define('ROLE_ADMIN',              'admin');          // [運用上最上位権限の人]
define('ROLE_OPERATOR',           'operator');       // [権限が指定される人]

define('DEFAULT_SUPER_USER',        'superuser');

define('NONCE_NAME',  'n_nonce');

define('SI_CRON_START',  'start');
define('SI_CRON_TYPE',  'type');

define('SI_BOND',  '_');
define('SI_HYPHEN',  '-');

define('SI_SYSTEM_DATE_FORMAT', 'Y-m-d');
define('SI_SYSTEM_TIME_FORMAT', ' H:i:s');
define('SI_SYSTEM_ZERO_TIME', ' 00:00:00');

define('SI_KEY',  'key');
define('SI_NAME',  'name');
define('SI_SELECTED',  'selected');
define('SI_DEFAULT',  'default');
define('SI_EXTRA',  'extra');
define('SI_POST_TYPE',  'post_type');
define('SI_POST_TYPES',  'post_types');
define('SI_BEFORE_FIELD_GROUP',  'before_field_group');
define('SI_TAXONOMIES',  'taxonomies');
define('SI_MENU_POSITION',  'menu_position');
define('SI_RELATED_COUNT',  'related_count');
define('SI_UNIQUE_SETTINGS',  'unique_settings');
define('SI_ALLOW_ROLES',  'allow_roles');
define('SI_IS_FIRST',  'is_first');
define('SI_IS_LAST',  'is_last');
define('SI_ARRAY_INDEX',  'array_index');
define('SI_VALUE_INDEX',  'value_index');
define('SI_GROUP_INFO',  'group_info');
define('SI_TAGS',  'tags');
define('SI_SLUG',  'slug');
define('SI_TERMS',  'terms');
define('SI_TERM_ID',  'term_id');
define('SI_IS_PLANE',  'plane');
define('SI_CUR_CLASS',  'current_class_name');
define('SI_HAS_ARCHIVE',  'has_archive');
define('SI_ARCHIVE_PREVIEW',  'archive_preview');
define('SI_INDEX',  'index');

define('SI_TAX_SHOW_UI',  'show_ui');
define('SI_TAX_HIERARCHICAL',  'hierarchical');
define('SI_TAX_USE_HIERARCHICAL_PARENT',  'use_hierarchical_parent');


define('SI_CUSTOM_FIELDS',  'custom_fields');
define('SI_IS_MULTIPLE',  'is_multiple');
define('SI_FIELDS',  'fields');
define('SI_FIELD_IS_REQUIRE',  'is_require');

define('SI_FIELD_TYPE',  'field_type');
define('SI_FIELD_TYPE_TEXT',  'text');
define('SI_FIELD_TYPE_TEXTAREA',  'textarea');
define('SI_FIELD_TYPE_CHECKBOX',  'checkbox');
define('SI_FIELD_TYPE_HIDDEN',  'hidden');
define('SI_FIELD_TYPE_FILE',  'file');
define('SI_FIELD_TYPE_RADIO',  'radio');
define('SI_FIELD_TYPE_SELECT',  'select');
define('SI_FIELD_TYPE_NUMBER',  'number');
define('SI_FIELD_TYPE_DATE',  'date');
define('SI_FIELD_TYPE_TIME',  'time');
define('SI_FIELD_TYPE_BUTTON',  'button');
define('SI_FIELD_TYPE_LINK_BUTTON',  'link_button');

// --- EXTRA keys ---
define('SI_EXTRA_SET_ATTR_NAME',  'set_attr_name');
define('SI_EXTRA_NOTICE',  'notice');

// --- Spread Sheet EXTRA keys ---
define('SI_SPREAD_SHEET_TARGET_SHEET_NAME',  'spread_sheet_target_sheet_name');


// --- Input "button" EXTRA keys ---
define('SI_LINK_BUTTON_EXTRA_LINK_ELEMENT_VALUE',  'button_link_element_value');
define('SI_LINK_BUTTON_EXTRA_LINK_OPTION',  'button_link_option');
define('SI_LINK_BUTTON_EXTRA_LINK_OPTION_BY_OTHER_ELEMENT',  'button_link_option_by_other_element');

// --- Input "date" EXTRA keys ---
define('SI_DATE_EXTRA_MIN_DATE_SETTING',  'min_date');
define('SI_DATE_EXTRA_MAX_DATE_SETTING',  'max_date');
// --- Input "date" EXTRA values (Default値でも利用可能) ---
define('SI_DATE_EXTRA_NOW',  'now');
define('SI_DATE_EXTRA_TODAY',  'today');
define('SI_DATE_EXTRA_TODAY_BEFORE',  'today_before');
define('SI_DATE_EXTRA_TODAY_AFTER',  'today_after');
define('SI_DATE_EXTRA_SET_TIME',  'set_time');

define('SI_FIELD_CHOICE_VALUES',  'choice_values');
define('SI_FIELD_CHOICE_TYPE_USERS',  'users');
define('SI_FIELD_CHOICE_TYPE_POST_TYPES',  'post_types');

define('SI_FIELD_OPTION_AUTOLOAD',  'autoload');

define('SI_USE_ORIGINAL_ORDER',  'use_original_order');

define('SI_USE_RICH_EDITOR',  'use_rich_editor');
define('SI_RICH_EDITOR_NOT_USE',  -1);
define('SI_RICH_EDITOR_ONLY_ADMIN',  0);
define('SI_RICH_EDITOR_USE',  1);

// --- 以下 renderPosts系の変数名
define('SI_GET_P_POST_TYPE',  'post_type');
define('SI_GET_P_POST_NOT_IN',  'post__not_in');
define('SI_GET_P_POST_IN',  'post__in');
define('SI_GET_P_POST_ID',  'post_id');
define('SI_GET_P_PID',  'p');
define('SI_GET_P_ORDER_BY',  'orderby');
define('SI_GET_P_ORDER',  'order');
define('SI_GET_P_CATEGORY',  'category');
define('SI_GET_P_IS_PREVIEW',  'preview');
define('SI_GET_P_SEARCH_KEYWORDS',  's');

// TAGSによってtax_queryが自動的に決定される
define('SI_GET_P_TAGS',  'tags');
define('SI_GET_P_TAX_QUERY',  'tax_query');
define('SI_GET_P_TAX_QUERY_TX',  'taxonomy');
define('SI_GET_P_TAX_QUERY_FIELD',  'field');
define('SI_GET_P_TAX_QUERY_TERMS',  'terms');
define('SI_GET_P_TAX_QUERY_RELATION',  'relation');

// PAGEによってLIMITとOFFSETが自動的に決定される
define('SI_GET_P_PAGE',  'paged');
define('SI_GET_P_LIMIT',  'posts_per_page');
define('SI_GET_P_SIMPLE_OFFSET',  'simple_offset');
define('SI_GET_P_OFFSET',  'offset');
define('SI_GET_P_NO_PAGING',  'nopaging');
define('SI_GET_P_YEAR',  'y');
define('SI_GET_P_MONTH',  'm');
define('SI_GET_P_DAY',  'd');
define('SI_GET_ALL',  'all');

// POST STATUS
define('SI_GET_P_STATUS',  'post_status');
define('SI_GET_P_STATUS_PUBLISH',  'publish'); // 公開
define('SI_GET_P_STATUS_DRAFT',  'draft');     // 下書き
define('SI_GET_P_STATUS_FUTURE',  'future');     // 予約投稿

// FOR PREVIEW
define('SI_GET_P_POST_PARENT',  'post_parent');

// --- 以下 renderTerms系の変数名
define('SI_GET_T_TAXONOMIES',  'taxonomies');
define('SI_GET_T_SLUG',  'slug');
define('SI_GET_T_HIDE_EMPTY',  'hide_empty');
define('SI_GET_T_TAGS',  'tags');
define('SI_GET_T_CUR_CLASS',  'current_class_name');

// --- 以下 Routing系定数
define('SI_TEMPLATE_EXTENSION',  '.twig');
define('SI_PAGE_TYPE_404',  '_404');
define('SI_PAGE_TYPE_HOME',  '_home');
define('SI_PAGE_TYPE_ARCHIVE',  '_archive');
define('SI_PAGE_TYPE_SINGLE',  '_single');
define('SI_PAGE_TYPE_PAGE',  '_page');
define('SI_PAGE_TYPE_SEARCH',  '_search');

// --- META KEY
define('SI_TITLE',  'title');
define('SI_DESCRIPTION',  'description');
define('SI_KEYWORDS',  'keywords');
define('SI_OGP_IMAGE',  'ogp_image');
define('SI_OGP_URL',  'ogp_url');
define('SI_OGP_SITE_NAME',  'ogp_site_name');

// --- Form項目系
define('SI_ELEM_TAG',  'tag');
define('SI_ELEM_ID',  'id');
define('SI_ELEM_NAME',  'name');
define('SI_ELEM_VALUE',  'value');
define('SI_ELEM_CLASSES',  'classes');
define('SI_ELEM_CLASS',  'class');
define('SI_ELEM_ATTRS',  'attrs');
define('SI_ELEM_ATTR',  'attr');
define('SI_ELEM_CHILDREN',  'children');

// --- Form Action
define('SI_FORM_ACTION',  'actions');
define('SI_FORM_ACTION_ENCRYPT_KEY',  'flFl43e89');
define('SI_FORM_ACTION_SAVE_ADD',  'add' . SI_FORM_ACTION_ENCRYPT_KEY);
define('SI_FORM_ACTION_SAVE_UPDATE',  'update' . SI_FORM_ACTION_ENCRYPT_KEY);
define('SI_FORM_ACTION_SAVE_WP_POST',  'wp_post' . SI_FORM_ACTION_ENCRYPT_KEY);
define('SI_FORM_ACTION_SAVE_WP_TERM',  'wp_term' . SI_FORM_ACTION_ENCRYPT_KEY);
define('SI_FORM_ACTION_SAVE_SPREAD_SHEET',  'spread_sheet' . SI_FORM_ACTION_ENCRYPT_KEY);
define('SI_FORM_ACTION_SEND_MAIL',  'send_mail' . SI_FORM_ACTION_ENCRYPT_KEY);

// --- Resource Type
define('SI_RESOURCE_TYPE',  'resource_type');
define('SI_RESOURCE_TYPE_OPTION_WITH_SEQUENCES',  'option_with_sequences');
define('SI_RESOURCE_TYPE_POST_META',  'post_meta');
define('SI_RESOURCE_TYPE_TERM_META',  'term_meta');
define('SI_RESOURCE_TYPE_SPREAD_SHEET',  'spread_sheet');
define('SI_RESOURCE_TYPE_DO_NOT_GET',  'none');


// --- 設定画面
define('SI_SETTING_BACKBONE',  'backbone');
define('SI_SETTING_SEO',  'seo');
define('SI_SETTING_GOOGLE_SPREAD_SHEET',  'google_spread_sheet');

// デフォルトSEOフィールド
define('SI_DEFAULT_SEO_FIELDS', [
    // グループID
    SI_KEY  => 'seo',
    // 項目のラベル
    SI_NAME => 'SEO',
    // 動的に増やせる項目なのかどうか
    SI_IS_MULTIPLE => false,
    // 入力項目リスト
    SI_FIELDS => [
        // 入力項目
        [
            // 項目ID(一意)
            SI_KEY  => 'title',
            // 項目のラベル
            SI_NAME => 'タイトル[未入力時は記事タイトル]',
            // 入力必須かどうか(SI_IS_MULTIPLE=true の場合は無効)
            SI_FIELD_IS_REQUIRE => false,
            /*
             * 項目の Input Type
             * - text:         SI_FIELD_TYPE_TEXT
             * - textarea:     SI_FIELD_TYPE_TEXTAREA
             * - hidden:       SI_FIELD_TYPE_HIDDEN
             * - file:         SI_FIELD_TYPE_FILE
             */
            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
            SI_DEFAULT => null,
            SI_ELEM_ATTRS => [],
            SI_ELEM_CLASSES => [],
            SI_FIELD_CHOICE_VALUES => [],
            SI_FIELD_OPTION_AUTOLOAD => false,
        ],
        [
            SI_KEY  => 'description',
            SI_NAME => 'ディスクリプション',
            SI_FIELD_IS_REQUIRE => false,
            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA,
            SI_DEFAULT => null,
            SI_ELEM_ATTRS => [],
            SI_ELEM_CLASSES => [],
            SI_FIELD_CHOICE_VALUES => [],
            SI_FIELD_OPTION_AUTOLOAD => false,
        ],
        [
            SI_KEY  => 'keywords',
            SI_NAME => 'キーワード[カンマ区切りで入力]',
            SI_FIELD_IS_REQUIRE => false,
            SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT,
            SI_DEFAULT => null,
            SI_ELEM_ATTRS => [],
            SI_ELEM_CLASSES => [],
            SI_FIELD_CHOICE_VALUES => [],
            SI_FIELD_OPTION_AUTOLOAD => false,
        ],
        [
            SI_KEY  => 'img',
            SI_NAME => 'OGPタグのIMAGE',
            SI_FIELD_IS_REQUIRE => false,
            SI_FIELD_TYPE => SI_FIELD_TYPE_FILE,
            SI_DEFAULT => null,
            SI_ELEM_ATTRS => [],
            SI_ELEM_CLASSES => [],
            SI_FIELD_CHOICE_VALUES => [],
            SI_FIELD_OPTION_AUTOLOAD => false,
        ],
    ]
]);

class CustomizerDefine
{
    static $IMAGE_EXTENSIONS = [
        'png', 'jpg', 'jpeg', 'gif', 'ico'
    ];
    
    static $DEFAULT_SEO_FIELDS = [
        // グループID
        SI_KEY  => 'seo',
        // 項目のラベル
        SI_NAME => 'SEO',
        // 動的に増やせる項目なのかどうか
        SI_IS_MULTIPLE => false,
        // 入力項目リスト
        SI_FIELDS => [
            // 入力項目
            [
                // 項目ID(一意)
                SI_KEY  => 'title',
                // 項目のラベル
                SI_NAME => 'タイトル[未入力時は記事タイトル]',
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
                SI_KEY  => 'description',
                SI_NAME => 'ディスクリプション',
                SI_FIELD_IS_REQUIRE => false,
                SI_FIELD_TYPE => SI_FIELD_TYPE_TEXTAREA
            ],
            [
                SI_KEY  => 'keywords',
                SI_NAME => 'キーワード[カンマ区切りで入力]',
                SI_FIELD_IS_REQUIRE => false,
                SI_FIELD_TYPE => SI_FIELD_TYPE_TEXT
            ],
            [
                SI_KEY  => 'img',
                SI_NAME => 'OGPタグのIMAGE',
                SI_FIELD_IS_REQUIRE => false,
                SI_FIELD_TYPE => SI_FIELD_TYPE_FILE
            ],
        ]
    ];
    
    static $ADMIN_PAGE_TYPES = [
        'index.php' => 'ダッシュボード', 
        'edit-comments.php' => 'コメント', 
        'plugins.php' => 'プラグイン', 
        'users.php' => 'ユーザー', 
        'themes.php' => '外観', 
        'options-general.php' => '設定', 
        'tools.php' => 'ツール', 
        'upload.php' => 'メディア', 
        'edit-tags.php' => '分類', 
    ];

    static $DEFAULT_ADMIN_ENABLE_PAGES = [
        'index.php',                // ダッシュボード
        'edit-comments.php',        // コメント
        'users.php',                // ユーザー
        'themes.php',               // 外観
        'options-general.php',      // 設定
        'tools.php',                // ツール
        'upload.php',               // メディア
        'edit-tags.php',            // 分類
    ];
    
    static $DEFAULT_WORKER_ENABLE_PAGES = [
        'upload.php',               // メディア
        'edit-tags.php',            // 分類
    ];

    static $POST_TYPES = [
        5  => '(デフォルトの)投稿',
        20 => '固定ページ'
    ];
}

/* *******************************
 *          共通関数
 * *******************************/
// --- 権限系 ---
if (!function_exists('siGetMyRole')) {
    /**
     * @param string $user_id
     * @return array
     */
    function siGetMyRole($user_id)
    {
        $roles = REL_USER_ROLE;
        $user_roles = [];
        foreach ($roles as $role => $user_ids) {
            if (in_array($user_id, $user_ids)) {
                $user_roles[] = $role;
            }
        }
        
        return $user_roles;
    }

    /**
     * @param string $user_id
     * @param array $authorized_roles 
     * @return bool
     */
    function siCanIDo($user_id, $authorized_roles)
    {
        $result = false;
        foreach (siGetMyRole($user_id) as $user_role) {
            if (in_array($user_role, $authorized_roles)) {
                $result = true;
                break;
            }
        }
        
        return $result;
    }

    /**
     * @param string $user_id
     * @return array
     */
    function siGetForbiddenPages($user_id)
    {
        $pages = array();
        $roles = siGetMyRole($user_id);
        if (empty($roles)) {
            $pages = BASIC_FORBIDDEN_PAGES;
        } else {
            $checked = false;
            $forbidden_pages = USER_FORBIDDEN_PAGES;
            foreach ($roles as $role) {
                if (!isset($forbidden_pages[$role])) {
                    break;
                }
                $checked = true;
                $pages = array_merge($pages, $forbidden_pages[$role]);
            }

            // Roleは持っているけど、それが USER_FORBIDDEN_PAGESに登録されていなければ表示させない
            if (!$checked) {
                $pages = BASIC_FORBIDDEN_PAGES;
            }
        }
        
        return $pages;
    }

    function siGetFieldGroupConfig($arg_post_type, $arg_group_key, $throw = true)
    {
        $conf = false;
        foreach (CustomizerPostTypeSettings::get($arg_post_type)[SI_CUSTOM_FIELDS] as $group) {
            if ($arg_group_key === $group[SI_KEY]) {
                $conf = $group;
                break;
            }
        }

        if ($conf === false && $throw) {
            throw new Exception("[ $arg_post_type => $arg_group_key ] is not exist.");
        }

        return $conf;
    }

    function siGetTaxonomyConfig($arg_post_type, $taxonomy_key, $throw = true)
    {
        $conf = false;
        foreach (CustomizerTaxonomiesSettings::get($arg_post_type) as $taxonomy) {
            if ($taxonomy_key === $taxonomy[SI_KEY]) {
                $conf = $taxonomy;
                break;
            }
        }

        if ($conf === false && $throw) {
            throw new Exception("[ $arg_post_type => $taxonomy_key ] is not Taxonomy.");
        }

        return $conf;
    }

    /**
     * $taxonomy_keyからコンフィグを取得する
     * @param $taxonomy_key
     * @return array | bool
     */
    function siSearchTaxonomyConfig($taxonomy_key)
    {
        $config = false;
        foreach (CustomizerTaxonomiesSettings::getAll() as $post_type => $taxonomies) {
            if (strpos($taxonomy_key, $post_type) === false) {
                continue;
            }
            $current_tax_key = str_replace($post_type . SI_BOND, '', $taxonomy_key);
            foreach ($taxonomies as $taxonomy) {
                if ($taxonomy[SI_KEY] !== $current_tax_key) {
                    continue;
                }
                $config = $taxonomy;
                $config[SI_POST_TYPE] = $post_type;
                break;
            }

            if ($config !== false) {
                break;
            }
        }

        return $config;
    }

    function siGetTaxonomyFieldGroupConfig($taxonomy_key, $arg_group_key, $throw = true)
    {
        $conf = false;
        foreach (siSearchTaxonomyConfig($taxonomy_key)[SI_CUSTOM_FIELDS] as $group) {
            if ($arg_group_key === $group[SI_KEY]) {
                $conf = $group;
                break;
            }
        }

        if ($conf === false && $throw) {
            throw new Exception("[ $taxonomy_key => $arg_group_key ] is not exist.");
        }

        return $conf;
    }
}
