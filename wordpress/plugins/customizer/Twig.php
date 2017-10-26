<?php

class CustomizerTwig
{
    /**
     * Twigインスタンスの生成
     * @return Twig_Environment
     */
    static function createEngine()
    {
        SiUtils::createDir(SI_TWIG_TEMPLATE_DIR);
        SiUtils::createDir(SI_TWIG_CACHE_DIR);

        $loader = new \Twig_Loader_Filesystem(SI_TWIG_TEMPLATE_DIR);
        $twig = new \Twig_Environment($loader, array(
            'debug' => SI_TWIG_DEBUG,
            'auto_reload' => SI_TWIG_DEBUG,
            'strict_variables' => SI_TWIG_DEBUG,
            'cache' => SI_TWIG_CACHE_DIR
        ));

        // 拡張機能の適用
        if (SI_TWIG_DEBUG) {
            $twig->addExtension(new \Twig_Extension_Debug());
        }
        $twig->addExtension(new CustomizerTwigExtension());
        return $twig;
    }

    /**
     * 今表示すべきページ情報を取得
     * 
     * Routingのロジックと
     * 画面に渡す変数取得ロジックは上書き可能
     * @param null $routing_logic
     * @param null $get_arguments_logic
     * @return array
     */
    static function currentPage($routing_logic = null, $get_arguments_logic = null)
    {
        return array(
            is_callable($routing_logic) ? $routing_logic() : self::defaultRoutingLogic(),
            is_callable($get_arguments_logic) ? $get_arguments_logic() : self::defaultGetArgumentsLogic(),
        );
    }

    /* *******************************
     *          Routing
     * *******************************/
    /**
     * デフォルトではWordpressと似た動作
     * @return string
     */
    static function defaultRoutingLogic()
    {
        return self::buildFileName(
            SiUtils::getPageType(), get_post_type()
        );
    }

    /**
     * 下の例のようにテンプレートの存在チェックを行いながら
     * 読み込むファイル名を返す
     * 
     * 
     * 引数が下記の例の場合の動作例 
     * buildFileName('page', 'access', 'default');
     * 
     * page-access.twig がなければ 
     * page.twig を返す。
     * page.twig がなければ
     * default.twig を返す。
     * 
     * @param $base
     * @param string $type
     * @param string $default
     * @return string
     */
    static function buildFileName($base, $type = '', $default = 'default')
    {
        /**
         * @var $si_twig \Twig_Environment
         */
        global $si_twig;
        $EXTENSION = '.twig';

        $base_file = $base . $EXTENSION;
        $prefixed_file = $base . SI_HYPHEN . $type . $EXTENSION;
        $file = $default . $EXTENSION;

        if ($si_twig->getLoader()->exists($prefixed_file)) {
            $file = $prefixed_file;
        } else if ($si_twig->getLoader()->exists($base_file)) {
            $file = $base_file;
        }
        
        return $file;
    }

    /* *******************************
     *          Arguments
     * *******************************/
    
    static function defaultGetArgumentsLogic()
    {
        global $post, $si_customs;
        
        $args = array();
        switch (SiUtils::getPageType()) {
            case SI_PAGE_TYPE_HOME:
                break;
            case SI_PAGE_TYPE_ARCHIVE:
                break;
            case SI_PAGE_TYPE_SINGLE:
                setCustoms($post->ID);
                $args['post'] = $post;
                $args['customs'] = $si_customs;
                break;
            case SI_PAGE_TYPE_PAGE:
                break;
            case SI_PAGE_TYPE_SEARCH:
                break;
            default:
                // 404

                break;
        }
        
        return $args;
    }

    /* *******************************
     *          Utils
     * *******************************/
    
}

class CustomizerTwigExtension extends Twig_Extension
{
    
}