<?php
//include php files
include_once(plugin_dir_path(__FILE__) . 'db.php');
include_once(plugin_dir_path(__FILE__) . 'icons.php');
include_once(plugin_dir_path(__FILE__) . 'adminpages.php');

$otp_logger_db = new OTP_Logger_DB($GLOBALS['wpdb']);
$otp_logger_request = new OTP_Logger_Request();
$otp_logger_admin = new OTP_Logger_Admin();

// Create the database table
$otp_logger_db->create_log_database();

