<?php

class CustomizerTwig
{
    /**
     * Twigインスタンスの生成
     * @return Twig_Environment
     */
    static function createEngine()
    {
        $setting = CustomizerFormSettings::get('backbone');
        $setting = CustomizerConfig::getFieldSetting($setting, 'template');
        $debug_mode = CustomizerConfig::getInputSetting($setting, 'debug_mode');
        
        $is_debug = CustomizerDatabase::getOption('backbone_template_debug_mode', $debug_mode[SI_DEFAULT], true);
        $template_dir = get_template_directory();
        $template_cache_dir = $template_dir . '/twig_cache';
        $plugin_template_dir = plugin_dir_path(__FILE__) . '/templates';
        
        CustomizerUtils::createDir($template_cache_dir);

        $loader = new \Twig_Loader_Filesystem([
            // Theme用
            $template_dir,
            $template_dir . '/template-parts',
            // Plugin用
            $plugin_template_dir ,
            $plugin_template_dir . '/admin/normal',
            $plugin_template_dir . '/admin/post',
            $plugin_template_dir . '/admin/term',
            $plugin_template_dir . '/front/normal',
        ]);
        $is_debug = $is_debug === 'on' ? true : false;
        $twig = new \Twig_Environment($loader, [
            'debug' => $is_debug,
            'auto_reload' => $is_debug,
            'strict_variables' => $is_debug,
            'cache' => $template_cache_dir
        ]);

        // 拡張機能の適用
        if ($is_debug) {
            $twig->addExtension(new \Twig_Extension_Debug());
        }
        $twig->addExtension(new CustomizerTwigExtension());
        return $twig;
    }

    static function prepare()
    {
        global $si_twig;
        $key = CustomizerTwig::getTemplateKey(
            CustomizerUtils::getPageType(), get_post_type()
        );
        $si_twig->addGlobal('seo', getSeoMeta($key));
        $si_twig->addGlobal('basic', getBasicInfo());
        return $si_twig;
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
        $arguments = is_callable($get_arguments_logic) ? $get_arguments_logic() : self::defaultGetArgumentsLogic();
        
        /*
         * 常に渡す変数を定義
         */
        $arguments['theme_uri'] = get_template_directory_uri();
        
        return [
            is_callable($routing_logic) ? $routing_logic() : self::defaultRoutingLogic(),
            $arguments,
        ];
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
        $page_type = CustomizerUtils::getPageType();
        switch ($page_type) {
            case SI_PAGE_TYPE_PAGE:
                $name = get_post()->post_name;
                break;
            default:
                $name = get_post_type();
                break;
        }
        return self::buildFileName(
            $page_type, $name
        );
    }

    /**
     * 下の例のようにテンプレートの存在チェックを行いながら
     * 読み込むファイル名を返す
     * 
     * 
     * 引数が下記の例の場合の動作例 
     * buildFileName('page', 'access', '.twig', 'default');
     * 
     * page-access.twig がなければ 
     * page.twig を返す。
     * page.twig がなければ
     * default.twig を返す。
     * 
     * @param $base
     * @param string $type
     * @param string $extension
     * @param string $default
     * @return string
     */
    static function buildFileName($base, $type = '', $extension = SI_TEMPLATE_EXTENSION, $default = 'default')
    {
        /**
         * @var $si_twig \Twig_Environment
         */
        global $si_twig;

        $base_file = $base . $extension;
        $prefixed_file = $base . SI_HYPHEN . $type . $extension;
        $file = $default . $extension;

        if ($si_twig->getLoader()->exists($prefixed_file)) {
            $file = $prefixed_file;
        } else if ($si_twig->getLoader()->exists($base_file)) {
            $file = $base_file;
        }
        
        return $file;
    }

    static function getTemplateKey($base, $type = '', $extension = SI_TEMPLATE_EXTENSION)
    {
        return str_replace(
            $extension, '', 
            self::buildFileName($base, $type, $extension)
        );
    }

    /* *******************************
     *          Arguments
     * *******************************/
    
    static function defaultGetArgumentsLogic()
    {
        global $post, $conditions;
        
        $args = [];
        $search_args = null;

        $page_type = CustomizerUtils::getPageType();
        $key = self::getTemplateKey(
            $page_type, get_post_type()
        );
        
        // 各ページの onLoad 条件があれば記事取得する
        if (isset($conditions[$key]) && isset($conditions[$key]['onLoad']) && !empty($conditions[$key]['onLoad'])) {
            // 検索画面以外は投稿を取得
            if ($page_type !== SI_PAGE_TYPE_SEARCH) {
                $args['posts'] = getPostsForTemplate($conditions[$key]['onLoad']);
            } 
            // 検索画面では投稿取得ロジックが異なるので下で実行
            else {
                $search_args = $conditions[$key]['onLoad'];
            }
        }
        
        // 検索画面ではサイト内検索結果を取得する
        if (!is_null($search_args)) {
            $args['posts'] = getSearchForTemplate($search_args);
        }
        // 詳細画面ではその記事情報を取得する
        else if ($page_type === SI_PAGE_TYPE_SINGLE) {
            $args['post'] = getPostForTemplate($post->ID);
        }
        
        return $args;
    }

    
}
