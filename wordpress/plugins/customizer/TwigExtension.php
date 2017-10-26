<?php

class CustomizerTwigExtension extends Twig_Extension
{
    function getFunctions()
    {
        return [
            new Twig_Function('getTerms', [$this, 'getTerms']),
            new Twig_Function('getTerms', [$this, 'getTerms']),
        ];
    }

    /**
     * 指定した条件のタグ一覧等の表示に利用
     * @param $condition_path
     * @return array
     */
    function getTerms($condition_path)
    {
        $args = SiUtils::getCondition($condition_path);
        // --- 取得 ---
        // taxonomyの指定
        $taxonomies = SiUtils::getRequire($args, SI_GET_T_TAXONOMIES);
        unset($args[SI_GET_T_TAXONOMIES]);

        // --- 独自パラメータを取得しておく
        // 指定のTermに印をつける
        $current_terms = SiUtils::get($args, SI_GET_T_TAGS, -1);
        $current_class = SiUtils::get($args, SI_GET_T_CUR_CLASS, 'cur');
        unset($args[SI_GET_T_TAGS]);
        unset($args[SI_GET_T_CUR_CLASS]);

        // DBから取得
        $terms = get_terms(SiUtils::asArray($taxonomies), $args);

        if (empty($terms)) {
            return [];
        }

        // Termsをカスタマイズ
        $custom_terms = [];
        $plane = true;

        if ($current_terms === -1) {
            $current_terms = [];
        } else {
            $current_terms = SiUtils::asArray($current_terms);
        }

        foreach ($terms as &$term) {
            // タームのメタ情報を付与
            $term->meta = getFormattedTermMeta($term);

            // 指定中のtermには印をつけておく
            if (in_array($term->slug, $current_terms)) {
                $term->is_current = true;
                $term->current_class = $current_class;
                $plane = false;
                $custom_terms[SI_CUR_CLASS] = $current_class;
            } else {
                $term->is_current = false;
            }
        }
        $custom_terms[SI_TERMS] = $terms;
        $custom_terms[SI_IS_PLANE] = $plane;

        return $custom_terms;
    }


}