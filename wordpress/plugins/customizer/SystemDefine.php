<?php
/* *******************************
 *     システム(ここは触らない)
 * *******************************/
// Role - 権限セットの種類
define('ROLE_SUPER_ADMIN',        'super_admin');    // [素のWordpressを触れる人]
define('ROLE_ADMIN',              'admin');          // [運用上最上位権限の人]
define('ROLE_OPERATOR',           'operator');       // [権限が指定される人]

define('NONCE_NAME',  'n_nonce');

define('SI_KEY',  'key');
define('SI_NAME',  'name');
define('SI_DEFAULT',  'default');
define('SI_POST_TYPE',  'post_type');
define('SI_POST_TYPES',  'post_types');
define('SI_BEFORE_FIELD_GROUP',  'before_field_group');
define('SI_TAXONOMIES',  'taxonomies');
define('SI_MENU_POSITION',  'menu_position');
define('SI_COUNT_TYPE',  'count_type');
define('SI_LIST_COUNT',  'list_count');
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

define('SI_USE_RICH_EDITOR',  'use_rich_editor');
define('SI_RICH_EDITOR_NOT_USE',  -1);
define('SI_RICH_EDITOR_ONLY_ADMIN',  0);
define('SI_RICH_EDITOR_USE',  1);

// --- 以下 renderPosts系の変数名
define('SI_GET_P_POST_TYPE',  'post_type');
define('SI_GET_P_POST_ID',  'post_id');
define('SI_GET_P_ORDER_BY',  'orderby');
define('SI_GET_P_ORDER',  'order');
define('SI_GET_P_CATEGORY',  'category');

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
define('SI_GET_P_OFFSET',  'offset');

// POST STATUS
define('SI_GET_P_STATUS',  'post_status');
define('SI_GET_P_STATUS_PUBLISH',  'publish'); // 公開
define('SI_GET_P_OFFSET_DRAFT',  'draft');     // 下書き

// --- 以下 renderTerms系の変数名
define('SI_GET_T_TAXONOMIES',  'taxonomies');
define('SI_GET_T_SLUG',  'slug');
define('SI_GET_T_HIDE_EMPTY',  'hide_empty');
define('SI_GET_T_TAGS',  'tags');
define('SI_GET_T_CUR_CLASS',  'current_class_name');



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
}

function siLog($obj, $json = false, $path = null)
{
    if (!is_null($path)) {
        ini_set('error_log', $path);
    } else {
        ini_set('error_log', __DIR__.'/si.log');
    }
        
    error_log('sssssssssssssssssssssssssss');
    if ($json) {
        error_log(json_encode($obj, JSON_UNESCAPED_UNICODE));
    } else {
        error_log(print_r($obj, true));
    }
    error_log('eeeeeeeeeeeeeeeeeeeeeeeeeee');
}