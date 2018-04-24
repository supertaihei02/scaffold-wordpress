<?php

class CustomizerInstall
{
    static function updateDb()
    {
        global $option_with_sequence_db_version;
        $installed_version = get_site_option('option_with_sequence_db_version');

        // DBが更新されたら
        if ($installed_version !== $option_with_sequence_db_version) {
            self::install();
        }
        
        // Optionをメモリ上にLOAD
        CustomizerDatabase::loadAllOptions();
    }

    static function install()
    {
        CustomizerDatabase::createTable();
        CustomizerDatabase::insertInitialData();
    }

    static function uninstall()
    {
        global $wpdb, $option_with_sequence_db_version;
        $table_name = $wpdb->prefix . 'option_with_sequence';

        delete_option('option_with_sequence_db_version');
    }

    static function initialize()
    {
        // DBの定義
        register_uninstall_hook(__FILE__, 'CustomizerInstall::uninstall');
        
        // WP-Cronが効かない場合の回避策
        if (!(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) && CUSTOMIZER_CRON_MAIN_POWER) {
            if (!defined('ALTERNATE_WP_CRON')) {
                define('ALTERNATE_WP_CRON', true);
            }
            new CustomizerCron();
        }
        
        // タイトルタグを自動生成する機能を削除
        remove_action('wp_head', '_wp_render_title_tag', 1);
        
        // カスタムフィールド
        add_action('init', 'CustomPostTypes::createPostTypes');
        CustomizerFields::setEvents();
        
        // Ajax

        // スプレッドシート
        $setting = CustomizerFormSettings::get('backbone');
        $setting = CustomizerConfig::getFieldSetting($setting, 'enable_services');
        $use_spread_sheet = CustomizerConfig::getInputSetting($setting, 'google_spread_sheet');
        $use_spread_sheet = CustomizerDatabase::getOption('backbone_enable_services_google_spread_sheet', $use_spread_sheet[SI_DEFAULT], true);
        $use_spread_sheet = $use_spread_sheet === 'on' ? true : false;
        if ($use_spread_sheet) {
            // 以下AccessTokenの発行のためのAPI
            add_action( 'wp_ajax_auth_google_api', 'CustomizerSpreadSheet::getAuthUrl');
            add_action( 'wp_ajax_nopriv_auth_google_api', 'CustomizerSpreadSheet::getAuthUrl');
            add_action( 'wp_ajax_set_google_access_token', 'CustomizerSpreadSheet::createAccessToken');
            add_action( 'wp_ajax_nopriv_set_google_access_token', 'CustomizerSpreadSheet::createAccessToken');
            // Sheet作成API
            add_action( 'wp_ajax_create_google_spread_sheet', 'CustomizerSpreadSheet::createSpreadSheet');
            add_action( 'wp_ajax_nopriv_create_google_spread_sheet', 'CustomizerSpreadSheet::createSpreadSheet');
        }
    }
}

