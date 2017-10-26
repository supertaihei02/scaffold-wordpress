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

define('SI_BASE_PATH', __DIR__);
// jsの読み込み
function loadScript($hook)
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
add_action( 'admin_enqueue_scripts', 'loadScript' );

// vendorを読み込む
if (!is_file(ABSPATH . '/vendor/autoload.php')) {
    die('Wordpressインストールディレクトリで次のコマンドを実行してください `composer install` ');
}
require_once ABSPATH . '/vendor/autoload.php';

// ベースファイルを読み込む
require_once SI_BASE_PATH . '/SystemDefine.php';
require_once SI_BASE_PATH . '/Utils.php';
require_once SI_BASE_PATH . '/Config.php';
require_once SI_BASE_PATH . '/Logger.php';
require_once SI_BASE_PATH . '/Twig.php';

// このプラグインで利用するグローバル変数
$si_logger = new Logger();
$si_posts = [];   // WP_Postクラスのリストが入る。使用後は空にする。
$si_customs = []; // post_idをkeyにしたカスタムフィールドの値が入る。使用後は空にする。
$si_terms = [];   // get_termsの結果が入る。使用後は空にする。
$si_twig = CustomizerTwig::createEngine();  // テンプレートエンジン Twig

// phpの読み込み
require_once SI_BASE_PATH . '/CustomPostTypes.php';
require_once SI_BASE_PATH . '/CustomFields.php';
require_once SI_BASE_PATH . '/ConsoleManager.php';
require_once SI_BASE_PATH . '/Template.php';
require_once SI_BASE_PATH . '/Ajax.php';

// WP-Cronが効かない場合の回避策
if (!(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) && CUSTOMIZER_CRON_MAIN_POWER) {
    if (!defined('ALTERNATE_WP_CRON')) {
        define('ALTERNATE_WP_CRON', true);
    }
    require_once SI_BASE_PATH . '/Cron.php';
    new CustomizerCron();
}