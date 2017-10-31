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

    // jsの読み込み
    static function loadScript($hook)
    {
        $targets = [
            'post.php', 'post-new.php',
            'edit-tags.php', 'term.php'
        ];
        if (in_array($hook, $targets)) {
            wp_enqueue_media();
            wp_enqueue_script('customFields', plugins_url('js/customFields.js', __FILE__));
        }
    }

}

