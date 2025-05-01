<?php
//DB
class OTP_Logger_DB {
    private $wpdb;
    private $table_name;

    public function __construct($wpdb) {
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'otp_log';
    }

    public function create_log_database() {
        $charset_collate = $this->wpdb->get_charset_collate();
        if ($this->wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") != $this->table_name) {
            $sql = "CREATE TABLE $this->table_name (
                id INT(11) NOT NULL AUTO_INCREMENT,
                phone_number VARCHAR(20) NOT NULL,
                request_date DATETIME NOT NULL,
                otp_code VARCHAR(10) NOT NULL,
                response_content TEXT NOT NULL,
                request_method VARCHAR(50) NOT NULL,
                request_from VARCHAR(100) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public function insert_log($phone_number, $otp_code, $response_content) {
        $this->wpdb->insert($this->table_name, [
            'phone_number'     => $phone_number,
            'request_date'     => current_time('mysql'),
            'otp_code'         => $otp_code,
            'response_content' => $response_content,
            'request_method'   => 'ajax',
            'request_from'     => 'admin_page'
        ]);
    }

    public function get_logs($limit = 20) {
        return $this->wpdb->get_results("SELECT * FROM $this->table_name ORDER BY request_date DESC LIMIT $limit");
    }
}

class OTP_Logger_Request {
    private $api_url;

    public function __construct() {
        $this->api_url = get_option('otp_api_url');
    }

    public function send_otp_request($phone_number) {
        if (empty($this->api_url)) {
            return new WP_Error('api_url_not_set', __('API URL is not set in settings', 'otp-logger-text'));
        }

        $response = wp_remote_post($this->api_url, [
            'body' => json_encode(['mobile' => $phone_number]),
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('request_failed', __('Request failed', 'otp-logger-text'));
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code != 200) {
            return new WP_Error('failed_to_send_otp', __('Failed to send OTP, status code: ', 'otp-logger-text') . $status_code);
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}


add_action('wp_ajax_send_otp_request', 'handle_otp_request'); 
add_action('wp_ajax_nopriv_send_otp_request', 'handle_otp_request'); 

function handle_otp_request() {
    check_ajax_referer('otp_logger_nonce', 'nonce');
    $phone_number = sanitize_text_field($_POST['phone_number']);
    $otp_request = new OTP_Logger_Request(); 
    $response = $otp_request->send_otp_request($phone_number); 
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
        return;
    }
    $otp_logger_db = new OTP_Logger_DB($GLOBALS['wpdb']);
    $otp_logger_db->insert_log($phone_number, $response['otp'], $response['message']);
    wp_send_json_success(['message' => __('OTP sent and logged', 'otp-logger-text')]);
}

