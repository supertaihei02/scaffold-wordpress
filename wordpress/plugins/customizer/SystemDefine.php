<?php
/* *******************************
 *     システム(ここは触らない)
 * *******************************/
// Role - 権限セットの種類
define('ROLE_SUPER_ADMIN',        'super_admin');    // [素のWordpressを触れる人]
define('ROLE_ADMIN',              'admin');          // [運用上最上位権限の人]
define('ROLE_OPERATOR',           'operator');       // [権限が指定される人]

define('NONCE_NAME',  'n_nonce');

define('SI_CRON_START',  'start');
define('SI_CRON_TYPE',  'type');

define('SI_BOND',  '_');
define('SI_HYPHEN',  '-');

define('SI_KEY',  'key');
define('SI_NAME',  'name');
define('SI_DEFAULT',  'default');
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

define('SI_USE_ORIGINAL_ORDER',  'use_original_order');

define('SI_USE_RICH_EDITOR',  'use_rich_editor');
define('SI_RICH_EDITOR_NOT_USE',  -1);
define('SI_RICH_EDITOR_ONLY_ADMIN',  0);
define('SI_RICH_EDITOR_USE',  1);

// --- 以下 renderPosts系の変数名
define('SI_GET_P_POST_TYPE',  'post_type');
define('SI_GET_P_POST_NOT_IN',  'post__not_in');
define('SI_GET_P_POST_ID',  'post_id');
define('SI_GET_P_PID',  'p');
define('SI_GET_P_ORDER_BY',  'orderby');
define('SI_GET_P_ORDER',  'order');
define('SI_GET_P_CATEGORY',  'category');
define('SI_GET_P_IS_PREVIEW',  'preview');

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
define('SI_GET_P_YEAR',  'year');

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
]);

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

    /**
     * $taxonomy_keyからコンフィグを取得する
     * @param $taxonomy_key
     * @return array | bool
     */
    function siSearchTaxonomyConfig($taxonomy_key)
    {
        $config = false;
        foreach (SI_CUSTOM_POST_TYPES[SI_TAXONOMIES] as $post_type => $taxonomies) {
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

    function siGetTaxonomyFieldGroupConfig($taxonomy_key, $arg_group_key)
    {
        $conf = false;
        foreach (siSearchTaxonomyConfig($taxonomy_key)[SI_CUSTOM_FIELDS] as $group) {
            if ($arg_group_key === $group[SI_KEY]) {
                $conf = $group;
                break;
            }
        }

        if ($conf === false) {
            throw new Exception("[ $taxonomy_key => $arg_group_key ] is not exist.");
        }

        return $conf;
    }
}
