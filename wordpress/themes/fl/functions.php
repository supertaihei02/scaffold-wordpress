<?php
add_action('after_setup_theme', 'blankslate_setup');

/*
 * global setup
 */
function blankslate_setup()
{
    load_theme_textdomain('blankslate', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
    add_theme_support('post-thumbnails');
    global $content_width;
    if (!isset($content_width)) {
        $content_width = 640;
    }
    register_nav_menus(
        array('main-menu' => __('Main Menu', 'blankslate'))
   );
}

/*
 * load JavaScript files
 */
add_action('wp_enqueue_scripts', 'blankslate_load_scripts');

function blankslate_load_scripts()
{
    wp_enqueue_script('vendor.bundle', get_settings('site_url') . '/wp-content/themes/fl/js/vendor.bundle.js');
    wp_enqueue_script('app', get_settings('site_url') . '/wp-content/themes/fl/js/index.js');
}

/*
 * set favicon
 */
add_action('wp_head', 'blog_favicon');

function blog_favicon()
{
    echo '<link rel="shortcut icon" type="image/x-icon" href="' . get_bloginfo('template_url').'/favicon.ico" />' . "\n";
}

/*
 * enable comment reply
 */
add_action('comment_form_before', 'blankslate_enqueue_comment_reply_script');

function blankslate_enqueue_comment_reply_script()
{
    if (get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

/*
 * set title
 */
add_filter('the_title', 'blankslate_title');

function blankslate_title($title)
{
    return $title === '' ? '&rarr;' : $title;
}

/*
 * set filtered title
 */
add_filter('wp_title', 'blankslate_filter_wp_title');

function blankslate_filter_wp_title($title)
{
    return $title . esc_attr(get_bloginfo('name'));
}

/*
 * set widgets
 */
add_action('widgets_init', 'blankslate_widgets_init');

function blankslate_widgets_init()
{
    register_sidebar(array(
        'name' => __('Sidebar Widget Area', 'blankslate'),
        'id' => 'primary-widget-area',
        'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
        'after_widget' => "</li>",
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
   ));
}

/*
 * set custom pings (not use it)
 */
function blankslate_custom_pings($comment)
{
    $GLOBALS['comment'] = $comment;
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>"><?php echo comment_author_link(); ?></li>
    <?php
}

/*
 * get comment number
 */
add_filter('get_comments_number', 'blankslate_comments_number');

function blankslate_comments_number($count)
{
    if (!is_admin()) {
        global $id;
        $comments_by_type = &separate_comments(get_comments('status=approve&post_id=' . $id));
        return count($comments_by_type['comment']);
    } else {
        return $count;
    }
}
