<?php 
/*
Plugin Name: Easy Digital Downloads Polylang
Plugin URI: https://github.com/grappler/edd-polylang
Description: A plugin to enable seemless integration between Easy-Digital-Downloads and Polylang
Version: 0.0.1
Author: Ulrich Pogson
Author URI: http://ulrich.pogson.ch
License: GPL2
*/

require dirname( __FILE__ ) . '/class-edd-polylang.php';
$edd_polylang = new EDD_Polylang();
