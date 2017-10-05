<?php
/* *******************************
 *      1件分のHTMLを出力します    
 * *******************************/
/**
 * @var $post : Wordpressデフォルトの記事情報
 * @var $si_customs : 独自プラグインによって定義されたMeta情報
 */
global $post, $si_customs;
$news_img_dir = get_stylesheet_directory_uri().'/images';

/* *******************************
 *  NEWSに付いているTAGのHTMLを作成
 * *******************************/
$tag = empty($si_customs[$post->ID][SI_TAGS]) ? [] : $si_customs[$post->ID][SI_TAGS];
$tag_html = '';
foreach ($si_customs[$post->ID][SI_TAGS] as $term) {
    $tag_html .= "<p>NAME {$term->name} ID {$term->slug}</p>";
};

/* *******************************
 *         NEWSの表示項目
 * *******************************/
$custom = $si_customs[$post->ID];
// タイトル
$title = get_the_title();
// 本文
$content = get_the_content();
// リンク
$link = get_the_permalink();
// 日付
$date = get_post_time('F d, Y');

// --- news_basic ---
$news_basic = $custom['single-basic'];
// main image
$main_img = empty($news['single-basic-img']) ? $news_img_dir.'/boss.jpg' : $news['single-basic-img'];

// --- news_option ---
$news_options = $custom['single-options'];
// 配列項目のHTML作成
$options_html = '';
foreach ($news_options as $news_option) {
    $option_img = SiUtils::get($news_option, 'single-options-img', $news_img_dir.'/boss.jpg');
    $option_text = SiUtils::get($news_option, 'single-options-text', '');
    $options_html .= "<dt>画像</dt><dd>{$option_img}</dd><dt>テキスト</dt><dd>{$option_text}</dd>";
}
?>
<dl>
    <dt>タイトル</dt>
    <dd><?php echo $title; ?></dd>

    <dt>本文</dt>
    <dd><?php echo $content; ?></dd>

    <dt>メイン画像</dt>
    <dd><?php echo $main_img; ?></dd>

    <dt>このページリンク</dt>
    <dd><?php echo $link; ?></dd>

    <dt>記事公開日付</dt>
    <dd><?php echo $date; ?></dd>

    <dt>タグ</dt>
    <dd><?php echo $tag_html; ?></dd>
    
    <?php echo $options_html; ?>
</dl>
