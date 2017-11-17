<?php

class CustomizerSetting
{
    static function initialize()
    {
        global $si_spread_sheet;
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
        
        $setting = CustomizerFormSettings::get('backbone');
        $setting = CustomizerConfig::getFieldSetting($setting, 'enable_services');
        
        // スプレッドシートモードがONの場合はメニュー表示
        $use_spread_sheet = CustomizerConfig::getInputSetting($setting, 'google_spread_sheet');
        $use_spread_sheet = CustomizerDatabase::getOption('backbone_enable_services_google_spread_sheet', $use_spread_sheet[SI_DEFAULT], true);
        $use_spread_sheet = $use_spread_sheet === 'on' ? true : false;
        if ($use_spread_sheet) {
            // Spread Sheet用 Javascript 読み込み
            wp_enqueue_script('spread-sheet', plugins_url('js/googleSpreadSheet.js', SI_PLUGIN_PATH));
            add_options_page(
                'SPREAD SHEET', 'SPREAD SHEET', 'manage_options',
                'google_spread_sheet', 'CustomizerSetting::googleSpreadSheet'
            );
        }
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

    static function googleSpreadSheet()
    {
        CustomizerTwigExtension::displayFormAdmin(SI_SETTING_GOOGLE_SPREAD_SHEET);
    }
}
