<?php

class CustomPostTypes
{
    static function createPostTypes() {
        $all_post_types = CustomizerPostTypeSettings::getAll();
        foreach ($all_post_types as $post_type_info) {
            $post_key = $post_type_info[SI_KEY];

            // --- POST TYPEの追加 ---
            // supports のパラメータを設定する配列（初期値だと title と editor のみ投稿画面で使える）
            $supports = ['title'];
            switch ($post_type_info[SI_USE_RICH_EDITOR]) {
                case SI_RICH_EDITOR_ONLY_ADMIN:
                    // 管理者権限の場合のみ、リッチエディタを利用できる
                    $user = wp_get_current_user();
                    if (!empty($user->data->user_login) &&
                        siCanIDo($user->data->user_login, [ROLE_ADMIN, ROLE_SUPER_ADMIN])) {
                        $supports[] = 'editor';
                    }
                    break;
                case SI_RICH_EDITOR_USE:
                    $supports[] = 'editor';
                    break;
                default:
                    break;
            }
            $has_archive = false;
            if ($post_type_info[SI_HAS_ARCHIVE] === true) {
                $has_archive = true;
                if ($post_type_info[SI_USE_ORIGINAL_ORDER] === true) {
                    $supports[] = 'page-attributes';
                }
            }
            register_post_type($post_key,                               // カスタム投稿ID
                array(
                    'show_in_rest' => true,                             // REST APIで取得できるようにする
                    'rest_base' => $post_key,                           // REST APIのURLベース => /wp-json/wp/v2/{$rest_base}
                    'label' => $post_type_info['name'],                 // 管理画面の左メニューに表示されるテキスト
                    'public' => true,                                   // 投稿タイプをパブリックにするか否か
                    'has_archive' => $has_archive,                      // アーカイブを有効にするか否か
                    'menu_position' => $post_type_info['menu_position'],// 管理画面上でどこに配置するか
                    'supports' => $supports                             // 投稿画面でどのmoduleを使うか的な設定
//                    ,
//                    'rewrite' => array(
//                        'with_front' => false
//                    ),
//                    'query_var' => true
                )
            );

            // --- TAXONOMY の追加 ---
            foreach (CustomizerTaxonomiesSettings::get($post_key, []) as $taxonomy) {
                $taxonomy_key = $post_key .SI_BOND. $taxonomy[SI_KEY];
                $taxonomy_args = array(
                    'show_in_rest' => true,
                    'label' => $taxonomy[SI_NAME],                     // 管理画面上に表示される名前（投稿で言うカテゴリー）
                    'labels' => array(
                        'all_items'    => $taxonomy[SI_NAME].' - 一覧', // 投稿画面の右カラムに表示されるテキスト（投稿で言うカテゴリー一覧）
                        'add_new_item' => $taxonomy[SI_NAME].' - 作成'  // 投稿画面の右カラムに表示されるカテゴリ追加リンク
                    ),
                    'hierarchical' => $taxonomy[SI_TAX_HIERARCHICAL],   // タクソノミーを階層化するか否か（子カテゴリを作れるか否か）
                    'meta_box_cb' => 'post_categories_meta_box',        // タクソノミーの設定形式は常にチェックボックス(post_tags_meta_box なら形式が変わる)

                    'show_ui' => true,                                  // タグの編集が管理画面上からできるようにするか否か
                    'show_in_menu' => $taxonomy[SI_TAX_SHOW_UI],        // MENUに編集画面が表示されるかどうか
                    'show_in_nav_menus' => true,                        // 
                    'show_tagcloud' => true,                            // 
                    'show_in_quick_edit' => true,                       // 
                );

                register_taxonomy(
                    $taxonomy_key,                                         // 追加するタクソノミー名（英小文字とアンダースコアのみ）
                    $post_key,                                             // 上で設定したカスタム投稿ID
                    $taxonomy_args
                );

                // default の termを追加
                $default_terms = isset($taxonomy[SI_DEFAULT]) ? $taxonomy[SI_DEFAULT] : [];
                foreach ($default_terms as $term) {
                    wp_insert_term(
                        $term[SI_NAME],
                        $taxonomy_key,
                        ['slug' => $term[SI_KEY]]
                    );
                }
            }
        }
    }
}
