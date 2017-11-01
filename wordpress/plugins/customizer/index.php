<?php
/*
Plugin Name: Customizer
Plugin URI: 
Description: 
Version: 1.0
Author: FRAMELUNCH Inc.
Author URI: http://framelunch.jp/
License: GPL2
*/
/*  Copyright 2017 FRAMELUNCH Inc. (email : nakanishi@framelunch.jp)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Timezone
date_default_timezone_set(get_option('timezone_string'));
define('SI_BASE_PATH', __DIR__);

// Modules
if (!is_file(ABSPATH . '/vendor/autoload.php')) {
    die('Wordpressインストールディレクトリで次のコマンドを実行してください `composer install` ');
}
require ABSPATH . '/vendor/autoload.php';
require SI_BASE_PATH . '/ClassLoader.php';

// Install
add_action('plugins_loaded', 'CustomizerInstall::updateDb');
register_uninstall_hook(__FILE__, 'CustomizerInstall::uninstall');

// Globals
$si_logger = new Logger();
$si_customs = []; // post_idをkeyにしたカスタムフィールドの値が入る。使用後は空にする。
$si_terms = [];   // get_termsの結果が入る。使用後は空にする。
$si_twig = CustomizerTwig::createEngine();  // テンプレートエンジン Twig

// WP-Cronが効かない場合の回避策
if (!(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) && CUSTOMIZER_CRON_MAIN_POWER) {
    if (!defined('ALTERNATE_WP_CRON')) {
        define('ALTERNATE_WP_CRON', true);
    }
    new CustomizerCron();
}

// タイトルタグを自動生成する機能を削除 (きっとあとで移動する)
remove_action('wp_head', '_wp_render_title_tag', 1);