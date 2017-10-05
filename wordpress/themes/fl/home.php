<?php
global $title, $description, $keywords, $ogp_image;
$title = 'トップページ';
$description = 'ディスクリプション';
$keywords = 'キーワード1,キーワード2';
get_header();
?>
<section id="content" role="main">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/boss.jpg">
</section>
<?php get_footer();
