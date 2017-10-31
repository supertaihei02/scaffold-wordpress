<?php

class CustomizerSetting
{
    static function initialize()
    {
        add_options_page(
            '基幹設定', '基幹設定', 'manage_options',
            SI_SETTING_BACKBONE, 'CustomizerSetting::backbone'
        );
        add_options_page(
            'SEO設定', 'SEO設定', 'manage_options',
            SI_SETTING_SEO, 'CustomizerSetting::seo'
        );
    }
    
    static function backbone()
    {
        CustomizerTwigExtension::renderFormAdmin(SI_SETTING_BACKBONE);
    }

    static function seo()
    {
        CustomizerTwigExtension::renderFormAdmin(SI_SETTING_SEO);
    }
}

//function register_setting_keys() {
//    register_setting( SI_SETTING_BACKBONE, 'twig_debug_mode' );
//}

