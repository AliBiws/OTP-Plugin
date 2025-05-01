<?php
class OTP_Logger_Admin {
	private $ex;
	private $cv;
	private $fname;
	private $headertxt;
	
    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'settings_init']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
		$this->ex = get_option('otp_export_excel');
		$this->cv = get_option('otp_export_csv');
		$this->fname = get_option('otp_export_name');
		$this->headertxt = get_option('otp_export_header');
    }

    public function register_menu() {
        add_menu_page(
            __('OTP Logger', 'otp-logger-text'),
            __('OTP Logger', 'otp-logger-text'),
            'manage_options',
            'otp-logger',
            [$this, 'otp_logger_page'],
            'dashicons-admin-generic',
            80
        );

        add_submenu_page(
            'otp-logger',
            __('OTP Logger Settings', 'otp-logger-text'),
            __('Settings', 'otp-logger-text'),
            'manage_options',
            'otp-settings',
            [$this, 'otp_settings_page']
        );
    }

    public function otp_logger_page() {
        $db = new OTP_Logger_DB($GLOBALS['wpdb']);
        $logs = $db->get_logs();

        ?>
        <div class="wrap ol">
			<h1 class="wp-heading-inline"><?php _e('OTP Logs', 'otp-logger-text'); ?> </h1> 
 			<a href="javascript:void(0)" class="addnewbtn btn"><?php _e('Add New', 'otp-logger-text'); echo Svg_Icons::get('add'); ?></a>
			<a href="javascript:void(0)" class="closeform btn" style="display:none;"><?php _e('Close Form', 'otp-logger-text');echo Svg_Icons::get('close'); ?></a>
			<hr class="wp-header-end">
			<form id="otp-form" style="display:none;">
				<div class="inlineform">
					<input type="tel" id="phone_number" name="phone_number" placeholder="<?php _e('Enter phone number', 'otp-logger-text');?>" required />
					<button type="button" class="submition"><?php _e('Submit', 'otp-logger-text');echo Svg_Icons::get('send'); ?></button>
				</div>
			</form>
			<?php if (!empty($logs)) : ?>
            <h3 style="margin-top: 40px;"><?php _e('Latest Logs', 'otp-logger-text'); ?></h3>
            <table id="otp-logs-table" class="display">
                <thead>
                    <tr>
                        <th><?php _e('Phone Number', 'otp-logger-text'); ?></th>
                        <th><?php _e('OTP Code', 'otp-logger-text'); ?></th>
                        <th><?php _e('Message', 'otp-logger-text'); ?></th>
                        <th><?php _e('Date', 'otp-logger-text'); ?></th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($logs as $log) : 
							$request_date = $log->request_date;
							$dateofrq=date('Y/m/d', strtotime($request_date));
							if (is_plugin_active('wp-parsidate/wp-parsidate.php')) {
								$persian_date = parsidate('d/m/Y',$datetime=$request_date,$lang='per');;
								$dateofrq=$persian_date;
							} 
							?>
                            <tr>
                                <td><?php echo esc_html($log->phone_number); ?></td>
                                <td><?php echo esc_html($log->otp_code); ?></td>
                                <td><?php echo esc_html(__($log->response_content,'otp-logger-text')); ?></td>
                                <td><?php echo esc_html($dateofrq); ?></td>
                            </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
			 <?php else : ?>
               <div class="notfound">
				   <?php echo Svg_Icons::get('notfound'); ?>
				   <h4>
				   <?php _e('No logs found.', 'otp-logger-text'); ?>
				   </h4>
				   <button type="button" class="addnewbtn"><?php _e('Add New', 'otp-logger-text');echo Svg_Icons::get('add'); ?></button>
			   </div>
             <?php endif; ?>
        </div>
        <?php
    }

    public function otp_settings_page() {
        ?>
        <div class="wrap ols">
            <h1><?php _e('Settings', 'otp-logger-text'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('otp_settings_group');
                do_settings_sections('otp-settings');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('API URL', 'otp-logger-text'); ?> *</th>
                        <td><input type="text" name="otp_api_url" value="<?php echo esc_attr(get_option('otp_api_url')); ?>" required/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Export to Excel', 'otp-logger-text'); ?></th>
                        <td>
							<label class="switch">
								<input type="checkbox" name="otp_export_excel" value="1" <?php checked(1, get_option('otp_export_excel'), true); ?> />
								<span class="slider"></span>
							</label>
						</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Export to CSV', 'otp-logger-text'); ?></th>
                        <td>
							<label class="switch">
								<input type="checkbox" name="otp_export_csv" value="1" <?php checked(1, get_option('otp_export_csv'), true); ?> />
								<span class="slider"></span>
							</label>
						</td>
                    </tr>
					<tr valign="top" id="extra-field">
                        <th scope="row"><?php _e('Export File Name', 'otp-logger-text'); ?></th>
                        <td><input type="text" name="otp_export_name" value="<?php echo esc_attr(get_option('otp_export_name')); ?>" /></td>
                    </tr>
					<tr valign="top" id="excel-field">
                        <th scope="row"><?php _e('Export Header Text', 'otp-logger-text'); ?></th>
                        <td><input type="text" name="otp_export_header" value="<?php echo esc_attr(get_option('otp_export_header')); ?>" /></td>
                    </tr>
                </table>
                <button type="submit">
					<?php _e('Save Settings', 'otp-logger-text'); ?>
					<?php echo Svg_Icons::get('save'); ?>
				</button>
            </form>
        </div>
        <?php
    }

    public function settings_init() {
        register_setting('otp_settings_group', 'otp_api_url');
        register_setting('otp_settings_group', 'otp_export_excel');
        register_setting('otp_settings_group', 'otp_export_csv');
		register_setting('otp_settings_group', 'otp_export_name');
		register_setting('otp_settings_group', 'otp_export_header');
    }

	public function enqueue_scripts() {
		$excel = $this->ex;
		$csv = $this->cv;
		$filename=$this->fname;
		$headertxt =$this->headertxt;
		if (empty($headertxt)) {
			$headertxt = __('OTP Log', 'otp-logger-text');
		}
		if (!isset($_GET['page'])) return;
		$page = $_GET['page'];
		if ($page === 'otp-logger' || $page === 'otp-settings') {
			wp_enqueue_style('dataTables-css', plugin_dir_url(dirname(__DIR__)) . 'assets/css/dataTables.min.css');
			wp_enqueue_style('swalert', plugin_dir_url(dirname(__DIR__)) . 'assets/css/sweetalert2.min.css');
			wp_enqueue_style('otp-css', plugin_dir_url(dirname(__DIR__)) . 'assets/css/otp.css');
			
			if (!empty($excel) || !empty($csv) || $excel == 1 || $csv == 1) {
				wp_enqueue_script('dataTables', plugin_dir_url(dirname(__DIR__)) . 'assets/js/dataTables.min.js', array(), null, true);
				wp_enqueue_script('dataTables-buttons', plugin_dir_url(dirname(__DIR__)) . 'assets/js/dataTables.buttons.js', array('dataTables'), null, true);
				wp_enqueue_script('jszip', plugin_dir_url(dirname(__DIR__)) . 'assets/js/jszip.min.js', array(), null, true);
				wp_enqueue_script('buttons-html5', plugin_dir_url(dirname(__DIR__)) . 'assets/js/buttons.html5.min.js', array('dataTables-buttons', 'jszip'), null, true);
				wp_enqueue_script('buttons-dataTables', plugin_dir_url(dirname(__DIR__)) . 'assets/js/buttons.dataTables.js', array('dataTables-buttons'), null, true);
				$deps = ['jquery', 'dataTables', 'dataTables-buttons', 'jszip', 'buttons-html5', 'buttons-dataTables'];
			} else {
				$deps = ['jquery', 'dataTables','salert'];
			}
			
			wp_enqueue_script('dataTables', plugin_dir_url(dirname(__DIR__)) . 'assets/js/dataTables.min.js', array(), null, true);
			wp_enqueue_script('salert', plugin_dir_url(dirname(__DIR__)) . 'assets/js/sweetalert2.all.min.js', array(), null, true);
			wp_enqueue_script('otp', plugin_dir_url(dirname(__DIR__)) . 'assets/js/otp.js', $deps, null, true);
			wp_localize_script('otp', 'otp_obj', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('otp_logger_nonce'),
				'excel'    =>$excel,
				'excel_icon' => Svg_Icons::get('excel'),
				'csv'	   =>$csv,
				'csv_icon' => Svg_Icons::get('csv'),
				'filename' =>$filename,
				'btntxt'   => __('Export to', 'otp-logger-text'),
				'search'   => __('Search Phone Number :', 'otp-logger-text'),
    			'entries'  => __('entries per page', 'otp-logger-text'),
				'header'   => $headertxt,
				'alerts'   => [
					'empty_title'      => __('Empty Field', 'otp-logger-text'),
					'empty_text'       => __('Please enter a phone number before submitting.', 'otp-logger-text'),
					'invalid_title'    => __('Invalid Input', 'otp-logger-text'),
					'invalid_text'     => __('Phone number must contain digits only.', 'otp-logger-text'),
					'success_title'    => __('Success!', 'otp-logger-text'),
					'success_text'     => __('Phone number %s was added successfully.', 'otp-logger-text'),
					'error_title'      => __('Error', 'otp-logger-text'),
					'network_title'    => __('Network Error', 'otp-logger-text'),
					'network_text'     => __('An error occurred, please try again later.', 'otp-logger-text'),
					'ok'               => __('OK', 'otp-logger-text'),
				]
			]);
		}
	}
}

