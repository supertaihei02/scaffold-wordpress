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
define('SI_PLUGIN_PATH', __DIR__ . '/customizer');

// Modules
if (!is_file(SI_BASE_PATH . '/vendor/autoload.php')) {
    die('Wordpressインストールディレクトリで次のコマンドを実行してください `composer install` ');
}
require SI_BASE_PATH . '/vendor/autoload.php';
require SI_BASE_PATH . '/ClassLoader.php';

// DBの定義
CustomizerInstall::updateDb();

// Globals
$option_with_sequence_db_version = '1.0';
$si_logger = new Logger();
$si_customs = [];
$si_terms = [];
$si_twig = CustomizerTwig::createEngine();

// User定義情報
$seo_meta = [];
$conditions = [];
$forms = [];
$post_types = [];
$taxonomies = [];

// Plugin Initialize
CustomizerInstall::initialize();
