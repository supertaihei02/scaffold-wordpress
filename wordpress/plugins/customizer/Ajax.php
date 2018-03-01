<?php

class CustomizerAjax
{
    static function requireParam($params, $key)
    {
        try {
            $param = CustomizerUtils::getRequire($params, $key);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => "[ {$key} ] is required."
            ]);
            die();
        }
        
        return $param;
    }

    static function dieAjax($error)
    {
        echo json_encode([
            'success' => false,
            'error' => $error
        ]);
        die();
    }
}

/**
 * API実行用URL等を
 * Javascriptから読めるように出力
 */
function addAjaxInfo() 
{
    wp_enqueue_script('customizer-fields', 
        plugin_dir_url( __FILE__ ) . 'js/customFields.js');

    $data = array(
//        'upload_url' => admin_url('async-upload.php'),
        'ajax_url'   => admin_url('admin-ajax.php'),
//        'nonce'      => wp_create_nonce('media-form'),
        'is_admin'   => is_admin()
    );

    wp_localize_script(
        'customizer-fields',
        'customizer',
        $data
    );
}
add_action('wp_enqueue_scripts', 'addAjaxInfo', 1);
add_action('admin_enqueue_scripts', 'addAjaxInfo', 1);

/**
 * GET系のテンプレ
 * @param $template
 * @param $condition
 * @return array
 */
function getApiTemplate($template, $condition)
{
    global $post, $si_twig;

    if (isset($_GET[SI_GET_P_OFFSET]) && intval($_GET[SI_GET_P_OFFSET]) !== -1) {
        $condition[SI_GET_P_OFFSET] = intval($_GET[SI_GET_P_OFFSET]) +
            (($condition[SI_GET_P_PAGE] - 1) * $condition[SI_GET_P_LIMIT]);
    }

    $condition = argsInitialize($condition);
    $render_obj = getPostsForRender($condition);
    $template_args = [];
    foreach ($render_obj->posts as $post) {
        $unique_values['link'] = $post->link;
        $unique_values['index'] = $post->index;
        $formatted_arg = formatForTemplate($post, true);
        foreach ($unique_values as $key => $unique_value) {
            $formatted_arg[$key] = $unique_value;
        }
        $template_args[] = $formatted_arg;
    }
    $html = $si_twig->render($template.SI_TEMPLATE_EXTENSION, [
        'posts' => $template_args
    ]);
    $count_all = $render_obj->found_posts;

    // paging
    $posts_per_page = CustomizerUtils::get($condition, SI_GET_P_LIMIT, 4);
    $next_page = CustomizerUtils::get($condition, SI_GET_P_PAGE, 1) + 1;
    $page_total = ceil($count_all / $posts_per_page);
    $next = $next_page > $page_total ? -1 : $next_page;
    return array(
        'html' => $html,
        'count' => intval($count_all),
        'display_count' => intval($render_obj->display_count),
        'max_page' => intval($page_total),
        'current_page' => intval(CustomizerUtils::get($condition, SI_GET_P_PAGE, 1)),
        'next' => intval($next),
    );
}
/* *******************************
 *   記事取得API
 * 
 * 　条件に合致した記事情報を
 * 　template-partsでHTMLにビルドした結果を返します。
 * 　
 * 　
 * 　【GETリクエスト例】
 *   http://localhost/wp-admin/admin-ajax.php?action=get_posts&conditions=_archive-news,api&template=news-archive
 *   
 * 　【リクエスト例の説明】
 * 　Wordpress特有のAPI実装方法です。
 * 　上記のリクエストによって、getPostsApiファンクションを呼び出します。
 * 
 *   【リクエストURL(http://localhost/wp-admin/admin-ajax.php)について】
 * 　HTMLのヘッドタグに Javascriptの変数として「ajaxurl」が定義されているのでそれを利用
 * 
 * 　【パラメータ action について】
 * 　これは「get_posts」のまま変える必要なし。
 * 　呼び出す関数名を指定している。
 * 
 * 　【パラメータ conditions について】
 * 　functions.php上部に定義されている $conditions のkey配列を指定する。
 * 　配列はカンマ区切りとか、JSONとかで指定可能。
 * 
 * 　【パラメータ template について】
 * 　template-parts 内のファイル名を指定する。
 * 　"news-archive" を指定すると "template-parts/news-archive.twig"が読み込まれる。
 * 　
 * *******************************/
function getPostsApi()
{
    if (!isset($_GET['conditions'])) {
        die('Parameter [ conditions ] are required.');
    }
    if (!isset($_GET['template'])) {
        die('Parameter [ template ] is required.');
    }

    $condition = CustomizerUtils::getCondition($_GET['conditions']);
    
    header('content-type: application/json; charset=utf-8');
    echo json_encode(getApiTemplate($_GET['template'], $condition));
    die();
}
add_action( 'wp_ajax_get_posts', 'getPostsApi');
add_action( 'wp_ajax_nopriv_get_posts', 'getPostsApi');

/**
 * サイト内検索ページング用API
 */
function getSearchResults()
{
    $args = CustomizerUtils::getCondition($_GET['conditions']);
    $query = getSearchQuery($args);

    $posts = array();
    while ($query->have_posts()) {
        $query->the_post();
        $data = array();
        $data['post_id'] = get_the_ID();
        $data['title'] = strip_tags(get_the_title());
        $data['link'] = get_the_permalink();
        $posts[] = $data;
    }
    
    // paging
    $next_page = CustomizerUtils::get($args, SI_GET_P_PAGE, 1) + 1;
    $next = $next_page > $query->max_num_pages ? -1 : $next_page;
    $results = array(
        'posts' => $posts,
        'search_word' => $args[SI_GET_P_SEARCH_KEYWORDS],
        'count' => intval($query->found_posts),
        'display_count' => intval($query->post_count),
        'max_page' => intval($query->max_num_pages),
        'current_page' => intval($args[SI_GET_P_PAGE]),
        'next' => intval($next),
    );

    header('content-type: application/json; charset=utf-8');
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    die();
}
add_action('wp_ajax_get_search_result', 'getSearchResults');
add_action('wp_ajax_nopriv_get_search_result', 'getSearchResults');

/**
 * マルチ要素のHTMLを追加する際に利用するAPI
 */
function getFormGroupHtml()
{
    global $si_twig;
    $result = [ 'success' => false ];
    
    $template = CustomizerUtils::getRequire($_GET, 'template');
    $target_path = CustomizerUtils::asArray(str_replace("\\", '', CustomizerUtils::getRequire($_GET, 'path')));
    $group_id = CustomizerUtils::getRequire($_GET, 'group_id');
    $group_key = CustomizerUtils::getRequire($_GET, 'group_key');
    $current_max_sequence = CustomizerUtils::getRequire($_GET, 'sequence');
    $next_sequence = $current_max_sequence + 1;
    
    if (!$si_twig->getLoader()->exists($template)) {
        $result['error'] = "{$template} is not exist.";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        die();
    }

    try {
        $config = CustomizerTwigExtension::getConfig($target_path);
        $elements = CustomizerForm::configToElements($config, $target_path);
        $elements = CustomizerForm::changeSequenceInfo($next_sequence, $elements);
        $new_block = new CustomizerElement($group_id, null, [], $target_path);
        $new_block = CustomizerForm::changeSequence($new_block, $next_sequence);
        $new_block->multiple = true;
        $new_block->multiple_last_block = true;
        $new_block->multiple_common_id = $group_key;
        $new_block->before_block_id = $group_id;
        $new_block->layer_name = null;
        $new_block->addChildren($elements);
        
        $result['html'] = $si_twig->render($template, [
            'element' => $new_block,
            'currentMaxSequence' => $next_sequence,
        ]);
        $result['success'] = true;
    } catch (Exception $e) {
        $result['error'] = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
    }

    header('content-type: application/json; charset=utf-8');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    die();
}
add_action( 'wp_ajax_get_form_group_html', 'getFormGroupHtml');
add_action( 'wp_ajax_nopriv_get_form_group_html', 'getFormGroupHtml');
