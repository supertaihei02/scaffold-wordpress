<?php
/*
 * タグ一覧要素
 */
global $si_terms;
foreach ($si_terms[SI_TERMS] as $term) : 
    // 件数が0のものはグレーアウトするために unableクラスをつける
    $query = new WP_Query([
        SI_GET_P_STATUS => SI_GET_P_STATUS_PUBLISH,
        'tax_query' => array(
            array(
                'taxonomy' => POST_NEWS.'_categories',
                'field'    => 'slug',
                'terms'    => $term->slug,
            ),
        ),
        SI_TAGS => $term->slug,
    ]);
    $unable = $query->found_posts > 0 ? 'enable' : 'unable';
    ?>
    <div class="<?php draw($unable); ?>" id="<?php draw($term->slug); ?>">
        #<?php draw($term->name); ?>
    </div>
<?php
endforeach;