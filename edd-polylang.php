<?php 
/*
Plugin Name: Easy Digital Downloads Polylang
Plugin URI: 
Description: A plugin to enable seemless integration between Easy-Digital-Downloads and Polylang
Version: 0.0.1
Author: Ulrich Pogson
Author URI: http://ulrich.pogson.ch
License: GPL2
*/

if ( defined( 'EDD_MULTILINGUAL_VERSION' ) ){
	return;
}

define( 'EDD_MULTILINGUAL_VERSION', '1.2.1' );
define( 'EDD_MULTILINGUAL_PATH', dirname( __FILE__ ) );

require EDD_MULTILINGUAL_PATH . '/class-edd-polylang.php';

$edd_polylang = new EDD_Polylang();
