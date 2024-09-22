<?php



/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://flothemes.com
 * @since      1.0.0
 *
 * @package    Flo_Forms
 * @subpackage Flo_Forms/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Flo_Forms
 * @subpackage Flo_Forms/includes
 * @author     Alex G. <alexg@flothemes.com>
 */
if(!class_exists('Flo_Forms')){
	class Flo_Forms {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Flo_Forms_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

      if( !function_exists('get_plugin_data') ){
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
      }

      $plugin_data = get_plugin_data( plugin_dir_path( __FILE__ ) . '../flo-forms.php' );

      if(isset($plugin_data['Version'])) {
        $plugin_version = $plugin_data['Version'];
      }else{
        $plugin_version = '1.0.0';
      }

			$this->plugin_name = 'flo-forms';
			$this->version = $plugin_version;

			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
			$this->flo_register_custom_post_type();

		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Flo_Forms_Loader. Orchestrates the hooks of the plugin.
		 * - Flo_Forms_i18n. Defines internationalization functionality.
		 * - Flo_Forms_Admin. Defines all hooks for the admin area.
		 * - Flo_Forms_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-flo-forms-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-flo-forms-i18n.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-flo-forms-admin.php';

			/**
			 * The class responsible for creating the forms settings
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-flo-form-meta-box.php';

			/**
			 * The class responsible for creating the Entries Meta boxes
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-flo-entry-meta-box.php';

			/**
			 * The class responsible for creating MCE shortcode generator buttons
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-flo-forms-mce.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-flo-forms-public.php';

			/**
			 * The class responsible for registering the custom post types
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-flo-forms-custom-posts.php';

			$this->loader = new Flo_Forms_Loader();

		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Flo_Forms_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {

			$plugin_i18n = new Flo_Forms_i18n();

			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

		}

		/**
		 * Init registration of the custom posts type
		 *
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		public function flo_register_custom_post_type(){
			$flo_init_c_p_type = new Flo_Forms_custom_posts();
			$this->loader->add_action( 'init', $flo_init_c_p_type, 'flo_reg_custom_post_type' );
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {

			$plugin_admin = new Flo_Forms_Admin( $this->get_plugin_name(), $this->get_version() );

			//call register settings function
			$this->loader->add_action( 'admin_init', $plugin_admin, 'register_plugin_settings',99 );
			
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			$this->loader->add_action( 'wp_ajax_search_page', $plugin_admin, 'search_page' );
			$this->loader->add_action( 'wp_ajax_entry_read_unread', $plugin_admin, 'read_unread_entry' );
      $this->loader->add_action( 'wp_ajax_save_form_template', $plugin_admin, 'save_form_template' );
      $this->loader->add_action( 'wp_ajax_flo_form_delete_template', $plugin_admin, 'delete_form_template' );


			$this->loader->add_action( 'manage_flo_forms_posts_columns', $plugin_admin, 'set_custom_columns' ); // set the custom columns
			$this->loader->add_action( 'manage_flo_forms_posts_custom_column', $plugin_admin, 'custom_columns', 10, 2 ); // add custom columns to the forms browse page


			$this->loader->add_action( 'manage_flo_form_entry_posts_columns', $plugin_admin, 'set_entry_custom_columns' ); // set the custom columns
			$this->loader->add_action( 'manage_flo_form_entry_posts_custom_column', $plugin_admin, 'entry_custom_columns', 10, 2 ); // add custom columns to the entries browse page


			// we want to add Form taxonomy dropdown to the entries browse page to filter easily
			$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'restrict_entries_by_form' );

			// run an action on post edit page to update the read Entry meta.
			//post.php?post=1077&action=edit
			$this->loader->add_action( 'load-post.php', $plugin_admin, 'set_entry_read' );

			// add a note in the dashboard menu showing the number of unread Entries
			$this->loader->add_action( 'admin_menu', $plugin_admin, 'unread_entries_note', 999 );

			$this->loader->add_action( 'admin_menu', $plugin_admin, 'flo_add_forms_options' );

			$this->loader->add_action( 'admin_head', $plugin_admin, 'flo_dismiss_unread_messages_notice' );

      $this->loader->add_action( 'admin_init', $plugin_admin, 'flo_check_for_mixed_content' );

      $this->loader->add_action( 'wp_ajax_flo_export_forms_options', $plugin_admin, 'flo_export_forms_options' );

      $this->loader->add_action( 'wp_ajax_flo_import_forms_options', $plugin_admin, 'flo_import_forms_options' );

      $this->loader->add_action( 'wp_ajax_flo_send_test_email', $plugin_admin, 'flo_send_test_email' );

			$this->loader->add_action( 'wp_ajax_get_schema_and_model', $plugin_admin, 'get_schema_and_model' );

      $this->loader->add_action( 'admin_notices', $plugin_admin, 'ff_email_issues_notice' );

      $this->loader->add_action( 'admin_init', $plugin_admin, 'ff_dismiss_email_issues_notice' );


			//for the entries that are read we add a specific class
			$this->loader->add_filter( 'post_class', $plugin_admin, 'read_unread_entries', 10, 3 );

			// to the Entries browse list add a button that allows to mark each entry as read or unread
			$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'read_unread_entries_button', 10, 2 );


      $this->loader->add_filter( 'in_plugin_update_message-flo-forms/flo-forms.php', $plugin_admin, 'flo_forms_plugin_update_message', 10, 2 );

      $this->loader->add_filter( 'upload_mimes', $plugin_admin, 'flo_forms_additional_mime_types', 10);

      //$this->loader->add_filter( 'flo_form_entry_dashboard_content', $plugin_admin, 'flo_append_browser_meta', 10, 2 );

			$plugin_dirname = dirname(__FILE__);
			if(strpos($plugin_dirname, 'flo-forms-pro') === false ) {
				// run the free version updater
				//$this->loader->add_action( 'admin_init', $plugin_admin, 'flo_plugin_update' );
			}

			// For Gutenberg block:
			$plugin_public = new Flo_Forms_Public( $this->get_plugin_name(), $this->get_version() );
			add_action('init', array( $plugin_admin, 'register_flo_forms_gutenberg_block' ), 9);
			add_action( 'enqueue_block_editor_assets', array( $plugin_public, 'enqueue_scripts' ), 999);
			add_action( 'enqueue_block_editor_assets', array( $plugin_public, 'enqueue_styles' ), 998);

		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {

			$plugin_public = new Flo_Forms_Public( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

			// action that marks a certain Entry as read
			$this->loader->add_action( 'init', $plugin_public, 'read_entry' );

			// action for the daily Unread entries reminder
			$this->loader->add_action( 'wp_loaded', $plugin_public, 'unread_entries_reminder', 999 );

      $this->loader->add_action( 'wp_mail_failed', $plugin_public, 'log_mail_errors', 999 );

      // ajax actions
			$this->loader->add_action( 'wp_ajax_flo_form_submit', $plugin_public, 'flo_submit_form' );
			$this->loader->add_action( 'wp_ajax_nopriv_flo_form_submit', $plugin_public, 'flo_submit_form' );

			// registoer  the form  plugin
			add_shortcode( 'floform', array($plugin_public,'flo_forms_shortcode') ); // register the forms shortcode

      //$this->loader->add_action( 'flo_forms_before_send_mail', $plugin_public, 'maybe_store_user_meta' );

			// FIX for SG Optimizer plugin CSS cobine option conflict:
			$this->loader->add_filter( 'sgo_css_combine_exclude', $plugin_public, 'siteground_css_combine_exclude' );

		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Flo_Forms_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

		public function flo_form_post_init(){

		}


    /**
     * Send email via MailGun if applicable or via default wp_mail
     *
     * @return    bool
     */
    public static function wp_mail($tomail, $subject, $message, $headers, $from = '', $attachments = array())
    {

      global $flo_forms_activated; // make sure the plugin is activated

      $forms_options = get_option('flo_forms_options');

      // make sure we are dealing with an array
      if(!is_array($forms_options)){
      	$forms_options = array();
      }


      if(!isset($forms_options['use-smtp'])){
        $forms_options['use-smtp'] = 1;
      }

			if($from == ''){
				// get the admin email
				$from = get_option('admin_email');
			}

			// PHP closures are available only since version 5.3
			if(version_compare(phpversion(), '5.3', '>=')) {
				if($from != '') {

					// if using the 'mail_from' parameter then we can get "Unauthenticated email from yahoo.com is not accepted due to domain's 5.7.1 DMARC policy."
					// error message when sending to Gmail
					/*
					if(strpos($from, 'yahoo') === false ){ // only if $from is not an Yahoo email.
						add_filter( 'wp_mail_from', function() use ( $from ) {
							return $from;
						});
					}
					*/

				}

				// Use the as 'From name ' the site title. You may change this by adding an option
				if(isset($forms_options['mail_from_name']) && strlen($forms_options['mail_from_name']) ){
					$from_name = $forms_options['mail_from_name'];
				}else{
					$from_name = get_bloginfo('name');
				}

				add_filter( 'wp_mail_from_name', function() use ( $from_name ) {
					return html_entity_decode($from_name);
				});
			} // EOF if(version_compare(phpversion(), '5.3', '>=')) {

			// read internals flo for details about that.
			if(function_exists('get_mgkey')){
					$k = get_mgkey();
			}else{
					$k = false;
			}

			// if is Pro, activated and smtp enabled
      if( $flo_forms_activated && $forms_options['use-smtp'] == 1 && IS_FLO_FORMS_PRO && $k ) {

        // if(function_exists('configure_smtp')) {
        //   add_action( 'phpmailer_init', 'configure_smtp' );
        // }

				$params = array(
					'to' => $tomail,
					'subject' => $subject,
					//'text' => $message,
					//'html' => '<html>'.$message.'</html>',
					'headers' => $headers,
					'attachments' => $attachments,
				);

				if( (isset($form_option['text_email']) && !$form_option['text_email']) || !isset($form_option['text_email']) ){
          $params['html'] = '<html>'.$message.'</html>';
        }else{
					$params['text'] = $message;
				}

				if(strpos($from, 'yahoo') === false ){ // only if $from is not an Yahoo email.
					$params['from'] = $from_name.' <'.$from.'>';
					$params['h:Reply-To'] = $from;
				}else{
					$server_name = ! empty( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : wp_parse_url( get_home_url( get_current_blog_id() ), PHP_URL_HOST );

					// Get the site domain and get rid of www.
					$sitename = strtolower( $server_name );
					if ( substr( $sitename, 0, 4 ) === 'www.' ) {
						$sitename = substr( $sitename, 4 );
					}

					$default_from = 'wordpress@' . $sitename;
					if(strpos($default_from, '.') === false){ // usually only on localhost
						$default_from .= '.com';
					}

					$params['from'] = $from_name.' <'.$default_from.'>';
					$params['h:Reply-To'] = $from;
				}

				$result = flo_wp_mail($params);

      }else{
				// use default WP wp_mail function
	      $result = wp_mail( $tomail, $subject, $message, $headers, $attachments);
			}

      return $result;

    }

    /*
    * sanitize a given aray of data
    * @param array
    * @return array
    */
    public static function flo_sanitize($data) {
    	if(is_array($data) && sizeof($data)) {
    		$new_data = [];
    		foreach ($data as $key => $data_value) {
    			$new_data[$key] = esc_attr(wp_kses($data_value,['strip']));					
    		}

    		return $new_data;
    	}

    	return $data;
    }

	}
}

if(!function_exists('deb_e')){
	function deb_e( $data ){
		print '<pre style="margin:10px; border:1px dashed #999999; padding:10px; color:#333; background:#ffffff;">';
        $bt = debug_backtrace();
        $caller = array_shift($bt);
        //print "[ File : " . self::short( $caller['file'] ) . " ][ Line : " . $caller['line'] . " ]\n";
        print "--------------------------------------------------------------\n";
		print "</pre>";
	}
}
