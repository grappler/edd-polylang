<?php 
/*
Plugin Name: Easy Digital Downloads multilingual
Plugin URI: http://www.wpml.org/documentation/related-projects/easy-digital-downloads-multilingual
Description: A plugin to enable seemless integration between Easy-Digital-Downloads and WPML
Version: 1.1.1
Author: ICanLocalize
Author URI: http://wpml.org
License: GPL2
*/

if(defined('EDD_MULTILINGUAL_VERSION')) return;

define('EDD_MULTILINGUAL_VERSION', '1.1.1');
define('EDD_MULTILINGUAL_PATH', dirname(__FILE__));

require EDD_MULTILINGUAL_PATH . '/EDD_multilingual.class.php';

$edd_multilingual = new EDD_multilingual();
