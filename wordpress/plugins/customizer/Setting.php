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
        add_options_page(
            'TEST', 'TEST', 'manage_options',
            'test', 'CustomizerSetting::test'
        );
        
        // スプレッドシートモードがONの場合はメニュー表示
    }
    
    static function backbone()
    {
        CustomizerTwigExtension::displayFormAdmin(SI_SETTING_BACKBONE);
    }

    static function seo()
    {
        CustomizerTwigExtension::displayFormAdmin(SI_SETTING_SEO);
    }

    static function test()
    {
        CustomizerTwigExtension::displayFormAdmin('test');
    }
}
