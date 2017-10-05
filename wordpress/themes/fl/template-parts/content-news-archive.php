<?php
/* *******************************
 *      1件分のHTMLを出力します    
 * *******************************/
/**
 * @var $post : Wordpressデフォルトの記事情報
 * @var $si_customs : 独自プラグインによって定義されたMeta情報
 */
global $post, $si_customs;
$news_img_dir = get_stylesheet_directory_uri() . '/images';

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
// --- news ---
$news = $custom['archive'];
// thumb image (見つからないときはデフォルト画像 boss.jpg)
$img = empty($news['archive-img']) ? $news_img_dir . '/boss.jpg' : $news['archive-img'];
// 記事タイトル
$title = get_the_title();
// 見出し
$topic = $news['archive-topic'];
// リンク
$link = get_the_permalink();
// 日付
$date = get_post_time('F d, Y');
?>
<dl>
    <dt>タイトル</dt>
    <dd><?php echo $title; ?></dd>
    
    <dt>見出し</dt>
    <dd><?php echo $topic; ?></dd>
    
    <dt>サムネイル画像</dt>
    <dd><?php echo $img; ?></dd>
    
    <dt>詳細ページリンク</dt>
    <dd><?php echo $link; ?></dd>

    <dt>記事公開日付</dt>
    <dd><?php echo $date; ?></dd>

    <dt>タグ</dt>
    <dd><?php echo $tag_html; ?></dd>
</dl>
