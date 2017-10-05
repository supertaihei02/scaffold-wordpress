<?php
global $post, $si_customs;
/*
 * 各テンプレートで、get_header()を呼ぶ前に
 * global変数[$title, $description, $keywords, $ogp_image]をセットしてください
 */ 
global $title, $description, $keywords, $ogp_image;
$blog_name = get_bloginfo('name');
$ogp_url = site_url();
$ga_tag = '';
// Single記事の場合は、SEO系のMETAタグをセットします
if (isActiveCustomizer()) {
    
    // DefaultのOGP画像
    $ogp_image = $ogp_url . SI_DEFAULT_OGP_IMAGE;
    if (!empty($post) && SiUtils::isCustomizeSingle($post->post_type)) {
        // Custom Fieldsの値を取得
        setCustoms($post->ID);
        $custom = $si_customs[$post->ID];
        
        // タイトルの作成
        if (!empty($custom['seo']['seo-title'])) {
            $title = $custom['seo']['seo-title'] . SI_TITLE_SEPARATOR . $blog_name;
        } else {
            $title = get_the_title();
        }
        
        // ディスクリプションの作成
        if (!empty($custom['seo']['seo-description'])) {
            $description = $custom['seo']['seo-description'];
        } else {
            $description = SI_DEFAULT_DESCRIPTION . ' ' . $blog_name;
        }

        // キーワードの作成
        if (!empty($custom['seo']['seo-keywords'])) {
            $keywords = $custom['seo']['seo-keywords'];
        } else {
            $keywords = SI_DEFAULT_KEYWORDS . ',' . $blog_name;
        }

        // OGPタグのIMAGE作成
        if (!empty($custom['seo']['seo-img'])) {
            $ogp_image = $custom['seo']['seo-img'];
        }

        // OGPタグのURL作成
        $ogp_url = get_the_permalink();
        
        // Google Analytics Tag
        $ga_id = SI_GOOGLE_ANALYTICS_ID;
        $ga_tag = "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','//www.google-analytics.com/analytics.js','ga'); ga('create', '{$ga_id}', 'auto'); ga('send', 'pageview');</script>";
    }

    // タイトルにブログ名を追加
    $title = SiUtils::title($title);
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">

    <title><?php echo $title; ?></title>
    <meta name="description" content="<?php echo $description; ?>">
    <meta name="keywords" content="<?php echo $keywords; ?>">
    <!-- ogp -->
    <meta property="og:title" content="<?php echo $title; ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="<?php echo $ogp_url; ?>" />
    <meta property="og:image" content="<?php echo $ogp_image; ?>" />
    <meta property="og:site_name" content="<?php echo $blog_name; ?>" />
    <meta property="og:description" content="<?php echo $description; ?>" />
    
    <?php // 404ならリダイレクト
    if(is_404()): ?>
        <meta http-equiv="refresh" content="3; URL=/">
    <?php endif; ?>
    
    <link rel="shortcut icon" href="/wp-content/themes/fl/favicon.ico">
    <?php wp_head(); ?>

    <!-- GoogleAnalytics -->
    <?php echo $ga_tag; ?>
</head>
<body <?php body_class(); ?> >
