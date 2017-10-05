<?php
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

$conditions = [];
if (isActiveCustomizer()) {
    $conditions = [
        // Top Page
        'top' => [
            /*
             * News記事を 8件取得
             */
            'news' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_POST_TYPE => POST_NEWS,
                SI_GET_P_LIMIT => 8,
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
            ]
        ],
        // News Page 
        'news-archive' => [
            'terms' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_T_TAXONOMIES => POST_NEWS.'_categories',
                SI_GET_T_HIDE_EMPTY => false,
                SI_GET_T_TAGS => SiUtils::get($_GET, SI_GET_T_TAGS, -1),
            ],
            'news' => [
                SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
                SI_GET_P_POST_TYPE => POST_NEWS,
                SI_GET_P_LIMIT => SiUtils::get($_GET, SI_GET_P_LIMIT, 4),
                SI_GET_P_ORDER => 'DESC',
                SI_GET_P_ORDER_BY => 'date',
                SI_GET_P_PAGE => SiUtils::get($_GET, SI_GET_P_PAGE, 1),
                SI_COUNT_TYPE => SI_LIST_COUNT,
                SI_GET_P_TAGS => SiUtils::get($_GET, SI_GET_P_TAGS, -1),
                SI_GET_P_YEAR => SiUtils::get($_GET, SI_GET_P_YEAR, ''),
            ]
        ],
    ];



    /**
     * API実行用URLをJavascriptから読めるように出力
     */
    add_action('wp_enqueue_scripts', 'add_my_ajaxurl', 1);
    function add_my_ajaxurl() {
        ?>
        <script>
          var ajaxurl = '<?php echo admin_url( 'admin-ajax.php'); ?>';
        </script>
        <?php
    }

    /**
     * GET系のテンプレ
     * @param $template
     * @param $condition
     * @return array
     */
    function getApiTemplate($template, $condition)
    {
        header('content-type: application/json; charset=utf-8');
        if (isset($_GET[SI_GET_P_OFFSET]) && intval($_GET[SI_GET_P_OFFSET]) !== -1) {
            $condition[SI_GET_P_OFFSET] = intval($_GET[SI_GET_P_OFFSET]) +
                (($condition[SI_GET_P_PAGE] - 1) * $condition[SI_GET_P_LIMIT]);
        }
        ob_clean();
        ob_start();
        $count_all = renderPosts($template, $condition);
        $html = ob_get_contents();
        ob_end_clean();

        // paging
        $posts_per_page = SiUtils::get($condition, SI_GET_P_LIMIT, 4);
        $next_page = SiUtils::get($condition, SI_GET_P_PAGE, 1) + 1;
        $page_total = ceil($count_all / $posts_per_page);
        $next = $next_page > $page_total ? -1 : $next_page;
        return array(
            'html' => $html,
            'count' => $count_all,
            'next' => $next
        );
    }
    /* *******************************
     *   NEWS の取得API
     * *******************************/
    add_action( 'wp_ajax_get_news_archive', 'getNewsArchive');
    add_action( 'wp_ajax_nopriv_get_news_archive', 'getNewsArchive');
    function getNewsArchive()
    {
        global $conditions;
        $cnd = $conditions['news-archive']['news'];
        echo json_encode(getApiTemplate('news-archive', $cnd));
        die();
    }

    /* *******************************
     * タイトルタグを自動生成する機能を削除
     * *******************************/
    remove_action('wp_head', '_wp_render_title_tag', 1);
}
