<?php
global $post;
get_header();
?>

<h1>NEWS DETAIL</h1>
<?php
/**
 * [ NEWS一覧の出力 ]
 * テンプレートファイルはこちら
 * @see template-parts/content-news-archive.php
 */
renderPost(
    $post->ID, 
    'news-single'
);
?>

<?php
get_footer();
