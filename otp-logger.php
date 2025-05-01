<?php
/**
 * Plugin Name: OTP Logger
 * Plugin URI: https://tarahgraph.ir/
 * Description: OTP submission logs
 * Version: 0.1
 * Author: Ali Hosseini
 * Author URI:  https://tarahgraph.ir/
 * Text Domain: otp-logger-text
 * Domain Path: /languages
 */
// Exit if accessed directly.
if (!defined( 'ABSPATH' )){exit;} 

//include files
include_once(plugin_dir_path(__FILE__) . 'assets/php/includes.php');


//load lang
function otp_logger_load_textdomain() {
    load_plugin_textdomain('otp-logger-text', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'otp_logger_load_textdomain');