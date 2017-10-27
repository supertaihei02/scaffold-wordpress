<?php

class CustomizerTwigExtension extends Twig_Extension
{
    function getFunctions()
    {
        return [
            new Twig_Function('getTerms', [$this, 'getTerms']),
            new Twig_Function('getPosts', [$this, 'getPosts']),
        ];
    }

    /**
     * 指定した条件のタグ一覧等の表示に利用
     * @param $condition_path
     * @return array
     */
    function getTerms($condition_path)
    {
        return getTerms(SiUtils::getCondition($condition_path));
    }

    /**
     * 指定した条件の投稿一覧の表示に利用
     * @param $condition_path
     * @return array
     */
    function getPosts($condition_path)
    {
        return getPostsForTemplate(SiUtils::getCondition($condition_path));
    }
}