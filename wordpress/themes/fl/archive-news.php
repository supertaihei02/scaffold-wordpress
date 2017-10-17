<?php
global $title, $description, $keywords, $ogp_image, $conditions;
$title = 'NEWS';
$description = 'NEWSページのディスクリプション';
$keywords = 'キーワード1,キーワード2';
get_header();
?>

<h1>NEWS</h1>
<h2>タグによる絞り込み実装の場合に利用</h2>
<?php
/**
 * [ タグ一覧の出力 ]
 * テンプレートファイルはこちら
 * @see template-parts/content-news-terms.php
 */
renderTerms(
    'news-terms',
    $conditions['news-archive']['terms']
);
?>

<h2>記事一覧</h2>

<?php if (isExist($conditions['news-archive']['news'])): ?>
    
    <p>記事合計数: <?php echo countPosts($conditions['news-archive']['news']); ?></p>
    
    <?php
    /**
     * [ NEWS一覧の出力 ]
     * テンプレートファイルはこちら
     * @see template-parts/content-news-archive.php
     */
    renderPosts(
        'news-archive',
        $conditions['news-archive']['news']
    );
    ?>
    
<?php endif; ?>

<?php
get_footer();
