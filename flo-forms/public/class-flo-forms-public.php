<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://flothemes.com
 * @since      1.0.0
 *
 * @package    Flo_Forms
 * @subpackage Flo_Forms/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Flo_Forms
 * @subpackage Flo_Forms/public
 * @author     Alex G. <alexg@flothemes.com>
 */
if(!class_exists('Flo_Forms_Public')){
	class Flo_Forms_Public {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string    $plugin_name       The name of the plugin.
		 * @param      string    $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version = $version;

		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {

			global $post, $running_flo_shortcode;

			// $running_flo_shortcode is defined in Flothemes blocks

			//if(has_shortcode( $post->post_content, 'floform' )  || $running_flo_shortcode === true  ) {


				wp_register_style( 'flo-forms-pikaday', plugin_dir_url( __FILE__ ) . 'css/pikaday.min.css', array(), $this->version, 'all' );
				wp_register_style( 'flo-forms-public', plugin_dir_url( __FILE__ ) . 'css/flo-forms-public.min.css?v2', array(), $this->version, 'all' );
        //wp_enqueue_style( 'dashicons' );

				// use this action if for some reason the scripts are not enqueued
				// see Monte theme as an example
				do_action('flo_forms_after_styles_register');
			//}
		}

		/**
		 * Register the JavaScript for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {

			global $post, $running_flo_shortcode;
			// $running_flo_shortcode is defined in Flothemes blocks


      // fn flo_get_options_field_objects()
			//if(has_shortcode( $post->post_content, 'floform' ) || $running_flo_shortcode === true ) {

				$forms_options = get_option('flo_forms_options');

				wp_register_script( 'flo-form-moment', plugin_dir_url( __FILE__ ) . 'vendor/moment.min.js', array(), $this->version, $in_footer = true );

				wp_register_script( 'flo-form-pikaday', plugin_dir_url( __FILE__ ) . 'vendor/pikaday.min.js', array(), $this->version, $in_footer = true );

				if( isset($forms_options['enable-captcha']) && $forms_options['enable-captcha']) {
					wp_register_script( 'flo_recaptcha', 'https://www.google.com/recaptcha/api.js', array(), $this->version, $in_footer = true );
				}

				wp_register_script( 'flo_vue_app_js', plugin_dir_url(__FILE__).'../dist/js/app.min.js', array('jquery','wp-i18n'), $this->version, $in_footer = true );

			//}

			// use this action if for some reason the scripts are not enqueued
			// see Monte theme as an example
			do_action('flo_forms_after_scripts_register');

		}

		public static function date_format_php_to_js( $sFormat ) {
		    switch( $sFormat ) {
		        //Predefined WP date formats
		        case 'F j, Y':
		            return( 'MM dd, yy' );
		            break;
		        case 'Y/m/d':
		            return( 'yy/mm/dd' );
		            break;
		        case 'm/d/Y':
		            return( 'mm/dd/yy' );
		            break;
		        case 'd/m/Y':
		            return( 'dd/mm/yy' );
		            break;
		        case 'Y-m-d':
		        	return('yy/mm/dd');
		        	break;
		        default:
		        	return( 'MM dd, yy' );
		            break;
		    }

		}


		/**
		 *
		 * Render the form shortcode
		 *
		 */
		static public function flo_forms_shortcode($atts){

			if(isset($atts['id']) && is_numeric($atts['id'])){

				$forms_options = get_option('flo_forms_options','{}');
				if($forms_options == ''){
					$forms_options = '{}';
				}
				// we don't want to expose to the public sensitive info

        if(isset($forms_options['g_secret_key'])){
          unset($forms_options['g_secret_key']);
        }
        if(isset($forms_options['send_to_email'])){
          unset($forms_options['send_to_email']);
        }


        // get the Form's styling settings
        $flo_form_settings = get_post_meta( $atts['id'], 'flo_form_settings', true ); // get the form settings
        $flo_form_settings = Flo_Form_Meta_Box::set_default_settings($flo_form_settings);

        $flo_form_settings = self::removeSensitiveData($flo_form_settings);


        if(isset($flo_form_settings['styling'])){
          $form_styling = $flo_form_settings['styling'];
        }else{
          $form_styling = '{}';
        }


				$flo_form_schema = get_post_meta( $atts['id'], 'flo_form_schema', true);
				$flo_form_model = get_post_meta( $atts['id'], 'flo_form_model', true);

				// enqueue the scripts only when the shortcode is used on a page

				if( !function_exists('get_plugin_data') ){
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				$plugin_data = get_plugin_data(plugin_dir_path( __FILE__ ).'../flo-forms.php');

				$plugin_version  = $plugin_data['Version'];

				$siteurl = admin_url( 'admin-ajax.php' );

        if($plugin_data['Name'] == 'Flo Forms Pro') {
          $is_pro_version = '1';
        }else{
          $is_pro_version = '0';
        }

				// we should have the options in the following format: http://pastebin.com/FGiNW6jr

				$formData = array(

				    // generate a nonce with a unique ID "header_1_menu_spacing_nonce_val"
				    // so that you can check it later when an AJAX request is sent
				    //'formNonce' => wp_create_nonce( 'formNonceVal' ),
				    'ajaxurl' => $siteurl,
				    'date_format' => Flo_Forms_Public::date_format_php_to_js( get_option( 'date_format' )),
            'is_pro_version' => $is_pro_version,
            'max_upload_size' => wp_max_upload_size()
				);


        if($is_pro_version) {
          self::maybe_custom_fonts_styles($atts['id'], $forms_options);
        }


				if( isset($forms_options['enable-captcha']) && $forms_options['enable-captcha']) {
					wp_enqueue_script( 'flo_recaptcha');
				}
				if( strpos($flo_form_schema, 'pikaday') !== false ) {
					wp_enqueue_script( 'flo-form-moment');
					wp_enqueue_script( 'flo-form-pikaday');
					wp_enqueue_style('flo-forms-pikaday');
				}
				wp_enqueue_style('flo-forms-public');
				if(IS_FLO_FORMS_PRO) {
					wp_enqueue_style( 'dashicons' ); // used for the checkboxes
				}
				wp_enqueue_script('flo_vue_app_js');

				// the action below is necessary only for the Flo themes based on Enzo framework
				add_action('flo_forms_after_scripts_register',function () use ( $flo_form_schema, $flo_form_model, $plugin_version, $atts, $formData, $forms_options, $form_styling, $flo_form_settings )  {
					if( isset($forms_options['enable-captcha']) && $forms_options['enable-captcha']) {
						wp_enqueue_script( 'flo_recaptcha', 'https://www.google.com/recaptcha/api.js', array(), $plugin_version, $in_footer = true );
					}


					if( strpos($flo_form_schema, 'pikaday') !== false ) {
						// enque the necessary scripts for the date field only if there is such a field in use
						wp_enqueue_script( 'moment', plugin_dir_url( __FILE__ ) . 'vendor/moment.min.js', array(), $plugin_version, $in_footer = true );
						wp_enqueue_script( 'pikaday', plugin_dir_url( __FILE__ ) . 'vendor/pikaday.min.js', array(), $plugin_version, $in_footer = true );
					}

					wp_enqueue_script( 'flo_vue_app_js', plugin_dir_url(__FILE__).'../dist/js/app.min.js', array(), $plugin_version, $in_footer = true );

					wp_enqueue_script( 'flo_vue_app_js' );


					// localize the necessary data

					wp_localize_script( 'flo_vue_app_js', 'formData', $formData );

					if(strlen($flo_form_schema) ) {
						wp_localize_script( 'flo_vue_app_js', 'forms_options', $forms_options );
            wp_localize_script( 'flo_vue_app_js', 'flo_form_settings_'.$atts['id'], $flo_form_settings );
            wp_localize_script( 'flo_vue_app_js', 'form_styling_'.$atts['id'], $form_styling );
						wp_localize_script( 'flo_vue_app_js', 'flo_form_schema_'.$atts['id'], [$flo_form_schema] );
						wp_localize_script( 'flo_vue_app_js', 'flo_form_model_'.$atts['id'], [$flo_form_model] );

					}
				}, 10, 3);

				/*=========================================================================================
				=            This code is repeating from the action above. But it is necessary            =
				=========================================================================================*/

					// localize the necessary data
					wp_localize_script( 'flo_vue_app_js', 'formData', $formData );

					if(strlen($flo_form_schema) ) {
						wp_localize_script( 'flo_vue_app_js', 'forms_options', $forms_options );
            wp_localize_script( 'flo_vue_app_js', 'flo_form_settings_'.$atts['id'], $flo_form_settings );
            wp_localize_script( 'flo_vue_app_js', 'form_styling_'.$atts['id'], $form_styling );
						wp_localize_script( 'flo_vue_app_js', 'flo_form_schema_'.$atts['id'], [$flo_form_schema] );
						wp_localize_script( 'flo_vue_app_js', 'flo_form_model_'.$atts['id'], [$flo_form_model] );
					}

				/*=====  End of This code is repeating from the action above. But it is necessary  ======*/


				$email_is_valid_string = isset($form_options['send-to-email']) && is_email($form_options['send-to-email'] );
				$email_is_array_with_valide_strings = true;

				if(isset($form_options['send-to-email']) && is_array($form_options['send-to-email'])){
	                if ($email_is_array_with_valide_strings){
	                        foreach ($form_options['send-to-email'] as $email){
	                            if(!is_email($email)){
	                                $email_is_array_with_valide_strings = false;
	                            }
	                        }
	                    }
	            }

				if( !($email_is_valid_string || $email_is_array_with_valide_strings) ){

					return __('Please add the Recipient email for this form. Make sure there is a valid email','flo-forms');
				}

				if( !strlen($flo_form_schema) ) {
					return __('The Form Schema is empty. Most probably the Form ID is not valid.','flo-forms');
				}

				$flo_form_schema_arr = json_decode( $flo_form_schema );

				// make sure we have fields in the firls field group
				if( isset($flo_form_schema_arr->groups[0]->fields) && is_array($flo_form_schema_arr->groups[0]->fields) && sizeof($flo_form_schema_arr->groups[0]->fields) ){

					ob_start();
					ob_clean();
					include('partials/flo-forms-public-display.php');
					$the_form = ob_get_clean();
					return $the_form;
				}else{
					return __('The form has no fields','flo-forms');
				}


			}else{
				return __('The passed Form ID is not valid','flo-forms');
			}

		}


    /**
     * clean the form settings from sensitive data that should not show up in the front end
     * @param array $flo_form_settings
     * @return array
     */
    static public function removeSensitiveData($flo_form_settings) {
		  $keys_to_remove = array('send-to-email', 'email-subject', 'confirmation_opt', 'text_confirmation_value', 'confimation_page_title', 'confimation_page' );

      foreach ($keys_to_remove as $k) {
        if(isset($flo_form_settings[$k])){
          unset($flo_form_settings[$k]);
        }
      }

      return $flo_form_settings;
    }

    /**
     * renders the custom font face styles OR includes the google font styles
     *
     * @param int $form_post_id
     * @param array $forms_options
     */
    static public function maybe_custom_fonts_styles($form_post_id, $forms_options) {
      $flo_form_settings = get_post_meta( $form_post_id, 'flo_form_settings', true );

      $typography_elements = array('label', 'input', 'button'); // if there will be fonts options for new elements, add them here

      $custom_fonts = array();
      $google_fonts = array();

      if(isset($forms_options['custom_fonts'])) {
        $custom_fonts = json_decode($forms_options['custom_fonts']);
        //var_dump($custom_fonts);
      }

      if(isset($forms_options['google_fonts'])) {
        $google_fonts = json_decode($forms_options['google_fonts']);
        //var_dump($google_fonts);
      }

      $custom_fonts_data = array();

      foreach ($typography_elements as $key => $elem) {
        $custom_font_detected = false;
        if(is_array($custom_fonts) && sizeof($custom_fonts)) {
          foreach ($custom_fonts as $c_font) {
            if( isset($flo_form_settings['styling']) && isset($flo_form_settings['styling'][$elem]) &&
              isset($flo_form_settings['styling'][$elem]['font']) && $flo_form_settings['styling'][$elem]['font'] == $c_font->name) {
              $custom_font_detected = true;
              //$custom_fonts_data[] = $c_font;
              $custom_fonts_data[] = array(
                'name' => $c_font->name,
                'format' => $c_font->format,
                'url' => $c_font->url,
              );
            }
          }
        }

        if(!$custom_font_detected) { // if the current font is not a custom font, then we check if it is a Google font
          if(is_array($google_fonts) && sizeof($google_fonts)) {
            foreach ($google_fonts as $g_font) {
              if( isset($flo_form_settings['styling']) && isset($flo_form_settings['styling'][$elem]) && $flo_form_settings['styling'][$elem]['font'] == $g_font->activeFont) {
                // enqueue the current google font style
                wp_enqueue_style( 'flo_'.$g_font->name, $g_font->font_styles_url, array(), false);
              }
            }
          }
        }
      }


      foreach($custom_fonts_data as $key => $value){ //var_dump($value);
        $grouped_used_custom_fonts[$value['name']][$key] = $value;
      }

      //var_dump($grouped_used_custom_fonts);
      if(isset($grouped_used_custom_fonts)) {
        self::render_custom_font_face($grouped_used_custom_fonts);
      }

    }


    /**
     * Render the font face styles for the given custom fonts
     * NOTE: the given fonts should be grouped by the font name if several formats are available for the same font family
     *
     * @param array $grouped_used_custom_fonts
     */
    static function render_custom_font_face($grouped_used_custom_fonts) {

		  //var_dump($grouped_used_custom_fonts);

      $fontface = '';

      foreach ($grouped_used_custom_fonts as $font_key => $font_group) {

        $eot = '';

        $lines = array();
        $fontface .= '@font-face {';
        $fontface .= 'font-family: "'.$font_key.'"; ';

        foreach ($font_group as $font_format) {
          $line = '';

          switch ($font_format['format']) {

            case 'woff2':
              $line = "url('" . $font_format['url'] . "') format('woff2')";
              break;
            case 'woff':
              $line = "url('" . $font_format['url'] . "') format('woff')";
              break;
            case 'otf':
              $line = "url('" . $font_format['url'] . "') format('opentype')";
              break;
            case 'svg':
              $line = "url('" . $font_format['url'] . "') format('svg')";
              break;
            case 'ttf':
              $line = "url('" . $font_format['url'] . "') format('truetype')";
              break;
            case 'eot':
              $line = "url('" . $font_format['url'] . "?#iefix') format('embedded-opentype')";
              $eot = "url('" . $font_format['url'] . "');"; /* IE9 Compat Modes */
              break;
          }
          $lines[] = $line;

        }

        if(strlen($eot)){
          $fontface .= "src: ".$eot; // the eot font should be first
        }

        if(sizeof($lines)){
          $fontface .= "src: ".implode(",",$lines).";";
        }

        $fontface .= "}";
		  }



      add_action( 'wp_footer', function() use ($fontface) {
        echo '<style>'.$fontface.'</style>';
      } );
    }

		/**
		 *
		 * check if the given string contains valid emails
		 *
		 */
		static public function is_valid_emails($str){

			$emails = explode(',',$str); // in case we have several emails separated by comma

			if(is_array($emails) && sizeof($emails) ){
				foreach ($emails as $email) {
					if(!is_email($email)){
						return false;
					}
				}
			}

			return true;
		}

    /**
     *
     */
    static public function flo_submit_form(){

	        $forms_options = get_option('flo_forms_options');

					$response = array();

					$form_id = $_POST['flo_fid']; // get the form id
		      $form_model = json_decode(stripslashes($_POST['flo-form-model']));
		      $form_schema = json_decode( stripslashes($_POST['flo-form-schema']));

					if( isset($form_model->field_flohp) && strlen($form_model->field_flohp) ){
						$response['error'] = sprintf(__('%s Busted %s','flo-forms'), '<div class="error">','</div>');
						echo( json_encode($response) );

						exit();
					}

	        if(!$forms_options){
	            $current_user = wp_get_current_user();

	            $forms_options = array(
	                'enable_email_reminder' => 1,
	                //how many days old should entries be in order to triger the reminder email
	                'entries_days_old_reminder' => 1,
	                'send_to_email' => $current_user->user_email,
	                'text_email' => 0,
	                'enable-captcha' => 0,
	                'g_site_key' => '',
	                'g_secret_key' => ''
	            );
	        }

	        if(isset($forms_options['enable-captcha']) && $forms_options['enable-captcha']){
	            if(!($_POST['g-recaptcha-response'] && $_POST['g-recaptcha-response'])){
	                $response['error'] = sprintf(__('%s Please complete captcha verification %s','flo-forms'), '<div class="error">','</div>');
	                echo( json_encode($response) );

	                exit();
	            }
	            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/grecaptcha/autoload.php';

							//$recaptcha = new \ReCaptcha\ReCaptcha($forms_options['g_secret_key'], new \ReCaptcha\RequestMethod\SocketPost());
							$recaptcha = new \ReCaptcha\ReCaptcha($forms_options['g_secret_key']);


	            $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

	            if(!$resp->isSuccess()){
	                $response['error'] = sprintf(__('%s Captcha not valid, please try again %s','flo-forms'), '<div class="error">','</div>');
	                echo( json_encode($response) );

	                exit();
	            }

	        }



      $form_options = get_post_meta( $form_id, 'flo_form_settings', true ); // get the form settings
      $submited_info = self::create_message_from_form($form_model,$form_schema);

      //$response['msg'] = $submited_info['table'];

	    // now we will create the Entry in the DB
			$entry_id = self::create_message_entry($submited_info, $form_id);

			// add form options to the $_POST data to have more info available
			$post_data = array_merge($_POST, array('form_options' => $form_options, 'entry_id' => $entry_id));

			//self::maybe_store_user_meta($post_data, $entry_id);


			if(isset($_FILES) && is_array($_FILES) && sizeof($_FILES) ) {

        $attachments = self::upload_attachments($_FILES);

        if(isset($attachments['error'])) {
          $response['error'] = $attachments['error'];

          $attachments = array(); // if there was an error

          echo( json_encode($response) );
          exit();
        }

        //var_dump($attachments );
      }else{
        $attachments = array();
      }

			if(sizeof($attachments)) {
        update_post_meta($entry_id, 'flo_forms_attachments', $attachments);
      }

      do_action( 'flo_forms_before_send_mail', $post_data );

			// $attachmets[] = array(
      //   'name' => $file['name'],
      //   'system_path' => $fullsize_path,
      //   'attachment_url' => wp_get_attachment_url($attachment_id)
      // );
			$attachmet_paths = $attachments;

			// send the email
			$maybe_send_email = self::send_message($form_options,$submited_info['table'],$submited_info['user_email'],$entry_id,$_POST, $attachmet_paths);

      $maybe_send_confirmation_email = self::send_confirmation_email($form_options,$submited_info['table'],$submited_info['user_email'], $_POST);


			if(true !== $maybe_send_email){ // if for somereasont he email was not delivered
			  if(!isset($form_options['send-to-email']) ||
          isset($form_options['send-to-email']) && !is_email($form_options['send-to-email'])  ) {

          $response['error'] = sprintf(__('%s Invalid recipient email. Contact the administrator to check the form settings. %s','flo-forms'), '<div class="error">','</div>');
        }else {
          $response['error'] = sprintf(__('%s The email could not be sent %s','flo-forms'), '<div class="error">','</div>');
        }

			}else{
				$response['confirmation_opt'] = $form_options['confirmation_opt'];

				if(isset($form_options['text_confirmation_value'])) {
				    // filter to overwrite the confirmation text if needed for developers
            $form_options['text_confirmation_value'] = apply_filters('flo_forms_text_confirmation_value', $form_options['text_confirmation_value'], $_POST, $form_options);

            // usage example:
            // https://pastebin.com/tHScuCwa
        }

				if('text_confirmation' === $form_options['confirmation_opt']){
					$response['success_msg'] = '<div class="success">'.nl2br($form_options['text_confirmation_value']).'</div>';
				}else{
          // if user entered a URL instead of a page title, the URL will have priority.
          if(isset($form_options['confimation_page_title']) && filter_var($form_options['confimation_page_title'], FILTER_VALIDATE_URL) ) {
            $response['success_page'] = $form_options['confimation_page_title'];
          }else if(is_numeric($form_options['confimation_page']) && 0 != $form_options['confimation_page']){

					  $response['success_page'] = get_permalink($form_options['confimation_page']);



						// added filter for the Redirect Page
            $response['success_page'] = apply_filters('flo_forms_success_page', $response['success_page'], $_POST, $form_options);

					}else{
						$response['confirmation_opt'] = 'text_confirmation';
						$response['success_msg'] = '<div class="success">'.$form_options['text_confirmation_value'].'</div>';
					}

				}
			}


			// we also have   $submited_info['submission_info'] // that contains the submited info in a array format
			// use it to attach to the submission custom post type

	        echo( json_encode($response) );
			exit();
		}


		/*
		 * Upload the attached files to the media library
		 * @param - array $files - the attached files if any
		 * @return - array - of attached files system path // OR an error message if something went wrong
		 * */
		public static function upload_attachments($files) {

      $max_upload_size = wp_max_upload_size();

      $attachmets = array();

      foreach ($files as $ind => $file) {

        if($file['size'] > $max_upload_size) {
          $attachmets['response'] = "ERROR";
          $attachmets['error'] =  sprintf(__('Uploaded file(%s) size has exceeded the allowed limit of %sMB.','flo-forms'), $file['name'], round($max_upload_size/1048576, 2)  );
          return $attachmets;

        }

        // make sure we have files
        if(isset($file['size']) && $file['size'] > 0 ) {
          $attachment_id = media_handle_upload( $ind, 0 );
          //var_dump($attachment_id);


          if ( is_wp_error( $attachment_id ) ) {
            //var_dump($attachment_id->get_error_message());
            $attachmets['response'] = "ERROR";
            $attachmets['error'] =  __('Attached file error. ','flo-forms'). $attachment_id->get_error_message();
          } else {
            $fullsize_path = get_attached_file($attachment_id);
            $pathinfo = pathinfo($fullsize_path);
            //$attachmets[$file['name']] = $fullsize_path;

            $attachmets[] = array(
              'name' => $file['name'],
              'system_path' => $fullsize_path,
              'attachment_url' => wp_get_attachment_url($attachment_id)
            );
          }
        }

      }

      return $attachmets;
    }

		/**
		 *
		 * the date is passed in the unix timestamp format, and we need to store it in a human readable way
		 *
		 */
		public static function maybe_corect_date($form_model, $schema){

			// we don't need any more to modify the date, therefore we just return the same passed model
			// Of course we could just delete this function.
			// But we keep it because it is called from the Tave add on.
			return $form_model;

			// $simplified_schema = Flo_Forms_Admin::get_fields_from_schema($schema);

			// $new_form_model = $form_model;

			// foreach ($form_model as $field_id => $field_value) {
			// 	if($simplified_schema[$field_id]->type == 'pikaday') {

			// 		// for some reason the date field gives us a weird timestamp
			// 		// therefore we need to remove three zeros from the end by deviding to 1000
			// 		// and then add 3hours in secconds - 10800
			// 		$date_value = $field_value/1000 + 10800;

			// 		$new_form_model->$field_id = date(get_option('date_format'), $date_value);
			// 	}
			// }

			// return $new_form_model;
		}
		/**
		 *
		 * The users can use field ID in the subject and we need to replace them with the field value
		 * @params:
		 * @subject - string : the email subject that may contain the field IDs
		 * @post_data - array containing the form POST data
		 *
		 * @return - string - the email subject with the field ID replaces by field Value
		 */
		static function maybe_replace_subject($subject,$post_data){

			$form_model =  json_decode(stripslashes( $post_data['flo-form-model']) );
			$patern = '%(fid_)?\d+%';
			preg_match_all($patern,$subject,$out,PREG_PATTERN_ORDER);

			if(isset($out[0]) && is_array($out[0]) && sizeof($out[0])){

				foreach ($out[0] as $key => $field_id) {
					$model_field_id = str_replace('fid_', '', $field_id);
					$model_field_key = 'field_'.$model_field_id;

					if(isset($form_model->$model_field_key)){
						$subject = str_replace('%'.$field_id.'%', $form_model->$model_field_key, $subject);
					}
				}


			}

			return $subject;
		}


    /**
     *
     * Send the confirmation email to the selected address  containing the contact form message
     * params:
     * @form_options - array containing the form settings
     * @submited_info_html - string, a html table that contains the info the user has submitted
     * @user_email - string, the email/emails fields the user completed in the form
     * @post_data - array the POST data send via ajax
     *
     * return - bool, true if the message was sent, and false otherwise
     */
		static function send_confirmation_email($form_options, $submited_info_html, $user_email, $post_data) {
      //var_dump('$form_options:', $form_options);
//      var_dump('$user_email: ', $user_email);


      // if email confirmation is enabled
      if(isset($form_options['enable_email_confirmation']) && $form_options['enable_email_confirmation'] == 1 ) {

        $tomail = '';

        if(isset($form_options['email_confirmation_to']) ) {
          if( trim($form_options['email_confirmation_to']) == '%field_email%'){
            // if the 'email_confirmation_to' is set to '%field_email%', then we will use the value from the email field - which in this case is passed
            // by $user_email
            $tomail = $user_email;
          }else{
            // otherwise if any other value is entered, we will use it
            $tomail = $form_options['email_confirmation_to'];
          }

        }else{
          $tomail = $user_email;
        }

        if(strpos($tomail, ',')){
          $tomail = str_replace(' ', '', $tomail);
          $tomail = explode(',',$tomail);
        }

        $subject = '';
        if(isset($form_options['email_confirmation_subject'])) {
          $subject = $form_options['email_confirmation_subject'];
        }

        $headers = array();
        if( (isset($form_option['text_email']) && !$form_option['text_email']) || !isset($form_option['text_email']) ){
          $headers[] = 'Content-Type: text/html; charset=UTF-8';// send html email
        }

        $message = '';
        if(isset($form_options['email_confirmation_value'])) {
          $message = str_replace('%all_fields%', $submited_info_html, $form_options['email_confirmation_value']);
          $message = self::maybe_replace_subject($message,$post_data); // replace any %field_id%
        }

        //$maybe_send_email = wp_mail( $tomail, $subject, $message, $headers);

        $from = trim($form_options['send-to-email']);

				// check if there are more emails
				//* Fix - Confirmation email issue when using more than one email addresses in in the recipient field.
				if(strpos($from,',') !== false){
					$from_emails = explode(',',$from);

					//If there are more emails, we use the first one.
					if(is_array($from_emails) && isset($from_emails[0])){
						$from = $from_emails[0];
					}
				}


				$forms_options = get_option('flo_forms_options');
				if(isset($forms_options['reply_to_header']) && 0 == $forms_options['reply_to_header']){
					$reply_to_header = false;
				}else{
					$reply_to_header = true;
				}

				if(strlen($from) && $reply_to_header ){
					$headers[] = 'Reply-To: ' . trim($from);
				}

        $maybe_send_email = Flo_Forms::wp_mail( $tomail, $subject, $message, $headers, $from);

        return $maybe_send_email;

      }

      return false;
    }

		/**
		 *
		 * Send the email containing the contact form message
		 * params:
		 * @form_options - array containing the form settings
		 * @submited_info_html - string, a html table that contains the info the user has submitted
		 * @user_email - string, the email/emails fields the user completed in the form
		 * @post_data - array the POST data send via ajax
		 *
		 * return - bool, true if the message was sent, and false otherwise
		 */
		static function send_message($form_options,$submited_info_html,$user_email,$entry_id, $post_data, $attachments){



			$tomail = $form_options['send-to-email'];

	        if(strpos($tomail, ',')){
	            $tomail = str_replace(' ', '', $tomail);
	            $tomail = explode(',',$tomail);
	        }

			$subject = self::maybe_replace_subject($form_options['email-subject'],$post_data);

			$forms_options = get_option('flo_forms_options');

			// generate a hidden image to be attached to the email
			// so that when the email is openned, the entry is marked as read
			if(isset($entry_id) && is_numeric($entry_id) && (!isset($plugin_options['text_email']) || !$forms_options['text_email']) ){
				//$hidden_image_url = plugin_dir_url( __FILE__ ).'read-entry.png?flo_read_msg='.$entry_id;
				$hidden_image_url = home_url().'?flo_read_msg='.$entry_id;
				$hidden_image = '<div style="display: none"><img style="visibility: hidden" src="'.$hidden_image_url.'" /></div>';
			}else{
				$hidden_image = '';
			}

			if(isset($forms_options['reply_to_header']) && 0 == $forms_options['reply_to_header']){
				$reply_to_header = false;
			}else{
				$reply_to_header = true;
			}

			$message = $submited_info_html.$hidden_image;
			$headers = array();
			if(!isset($plugin_options['text_email']) || !$forms_options['text_email']){
				$headers[] = 'Content-Type: text/html; charset=UTF-8';// send html email
			}else{
				$headers[] = "Content-Type: text/plain; charset=\"utf-8\"\r\n";
			}

			if(strlen($user_email) && $reply_to_header ){
				$headers[] = 'Reply-To: ' . trim($user_email);
			}

			//$maybe_send_email = wp_mail( $tomail, $subject, $message, $headers);
      $from = trim($user_email);
      $maybe_send_email = Flo_Forms::wp_mail( $tomail, $subject, $message, $headers, $from, $attachments);

			return $maybe_send_email;
		}


		/**
		 *
		 * Creates a message from the fields form
		 * that will be sent via email and used as content in the DB Entries
		 * params
		 * @form_model - array, the fields submitted by the visitor
		 * @form_schema - array, the form settings saved in the backend
		 *
		 * return array - an array that contains the submitted info in form of an html table, and the info in form of an array
		 *
		 */
		static function create_message_from_form($form_model,$form_schema) {
				$submission_info = array();


				$label_row_style = 'font-weight: bold; background-color: #fafafa; padding: 8px 35px';
				$value_row_style = 'padding: 5px 30px 5px 60px; background-color: #fff; border-bottom: 1px solid #DFDFDF;';

				$plugin_options = get_option('flo_forms_options');

        $plugin_options = apply_filters('flo_forms_message_plugin_options', $plugin_options);

				$table_rows = '';
				$email_fields = '';


				// the $form_model may have a random order depaeding how the user fills in the info
				// therefore we need to have the model data, in the same order as the schema fields
				$reordered_model = [];
				foreach ($form_schema->groups as $key => $field_group) {
					foreach ($field_group->fields as $schema_field_key => $schema_field) {

						foreach ($form_model as $model_field_key => $model_field_value) {
							if($schema_field->model == $model_field_key) {
								//echo $schema_field->label .' : '.$schema_field->model . '<br>';
								if(!is_array($model_field_value)){
									$reordered_model[$model_field_key] = wp_kses_post($model_field_value);
								}else{
									$reordered_model[$model_field_key] = $model_field_value;
								}

							}
						}
					}

				}
//var_dump($reordered_model);
				foreach ($reordered_model as $model_field_key => $model_field_value) {
					foreach ($form_schema->groups as $key => $field_group) {
						foreach ($field_group->fields as $schema_field_key => $schema_field) {
							if($schema_field->model == $model_field_key) {

								// TO DO ignore hidden, section and captcha fields


								if(isset($schema_field->type) && $schema_field->type == 'input' && isset($schema_field->inputType) && $schema_field->inputType == 'email' && is_email($model_field_value) ) {

									$email_fields .= $model_field_value.',';

								}

								if(isset($schema_field->label) && strlen($schema_field->label) ) {
									$label = $schema_field->label;
								}else if(isset($schema_field->placeholder) && strlen($schema_field->placeholder) ) {
									$label = $schema_field->placeholder;
								}else {

									if($schema_field->type == 'checkbox'){
										$label = $schema_field->help;

										if($model_field_value == ''){
                      $model_field_value = __('Unchecked', 'flo-forms');
                    }else{
                      $model_field_value = __('Checked', 'flo-forms');
                    }
									}else if($schema_field->type == 'select' && isset($schema_field->selectOptions->noneSelectedText)){
                    $label = $schema_field->selectOptions->noneSelectedText;
                  }else{
										$label = '';
									}

								}

								//output the feld value
								$message_field_value = '';
								if(is_array($model_field_value) ){
									// for the felds like cheboxes the value is an array that may contain several values
									foreach ($model_field_value as $val_key => $form_field_value) {
										$message_field_value .= $form_field_value . PHP_EOL;
									}
								} else {
									// for siple inputs

									// for date fields created using one of the earlier plugin versions we need to
									// transform the Unix timestamp into human readable date
									if(isset($schema_field->type) && $schema_field->type == 'pikaday' && is_numeric($model_field_value) ) {
										//var_dump( $schema_field->type, $model_field_value );
										$message_field_value = date(get_option('date_format'), $model_field_value/1000 + 10800 ) ;
									}else{
										$message_field_value = $model_field_value;
									}


								}

							}

							//break;
						}
					} // EOF 2nd foreach

					ob_start();
					ob_clean();
					if(!isset($plugin_options['text_email']) || !$plugin_options['text_email']){

				?>
					<tr style="<?php //echo $label_row_style; ?>">
						<td style="<?php echo $label_row_style; ?>"><?php echo $label; ?></td>
					</tr>
					<tr style="<?php //echo $value_row_style; ?>">
						<td style="<?php echo $value_row_style; ?>"><?php  echo  nl2br(sanitize_textarea_field($message_field_value)); ?></td>
					</tr>
				<?php
					}else{

					echo $label."\n";
					echo "\t\t".strip_tags(sanitize_text_field($message_field_value) )."\n\n";

					}
					$table_rows .= ob_get_clean();
					$submission_info[$label] = $message_field_value;

				} // EOF first Foreach

				if(!isset($plugin_options['text_email']) || !$plugin_options['text_email']){
					$the_table = '<table style="width: 100%; border: 1px solid #DFDFDF; border-bottom:0px; border-spacing: 0px;">';
					$the_table .= $table_rows;
					$the_table .= '</table>';
				}else{
					$the_table = $table_rows;
				}

			// $email_fields is built in form of user@something.com, some_other_email@smth.com,
			// therefore we need to remove the last comma
			$email_fields = rtrim($email_fields, ','); //remove the last comma from the email
			return array('table' => $the_table, 'submission_info' => $submission_info, 'user_email' => $email_fields);
		}

		/**
		 *
		 * Create the submission Entry post
		 * parems:
		 * @$submited_info array containing the info submitted by the user in html table format and array format
		 * @form_id - int, the ID of the contact form
		 *
		 * return - $entry_id if the Entry with the necessary meta data was created succesfully, or false otherwise
		 *
		 */
		static function create_message_entry($submited_info, $form_id){
			$form = get_post($form_id);
			$form_title = $form->post_title;

			$entry_info = array(
			  'post_title'    => $form_title,
			  'post_content'  => '',
			  'post_status'   => 'publish',
			  'post_type' => 'flo_form_entry'
			  //'post_author'   => 1,

			);

			// Insert the post into the database
			$entry_id = wp_insert_post( $entry_info );

			if(is_numeric($entry_id) && $entry_id > 0){ // if the post was created succesfully
				// create a taxonomy - 'entry_form' for the newlly created entry. The taxonomy title is the same as the form title
				wp_set_object_terms( $entry_id, $form_title, 'entry_form' );

				// now update the entry title
				$updated_entry = array(
				      'ID'           => $entry_id,
				      'post_title'   => $form_title. ' : '.__('Entry #','flo-forms').' '.$entry_id
				);

				// Update the post into the database
				wp_update_post( $updated_entry );

				// set the necessary meta data:
				$message_table = $submited_info['table'];
				$message_array = $submited_info['submission_info'];

				update_post_meta($entry_id, 'message_table', $message_table);
				update_post_meta($entry_id, 'message_array', $message_array);
				update_post_meta($entry_id, 'user_email', $submited_info['user_email']);


				// everything went goot, return the Entry ID
				return $entry_id;
			}else{
				return false;
			}
		}

		/**
		 *
		 * mark a Entry as read if http request has flo_read_msg
		 * Usually this method  is called from a hidden 1x1px image which is sent in the user email
		 * When user opens the email, the image which has the user http://site_url?flo_read_msg=entry_id
		 * triggers this function and we mark the entry as read
		 *
		 */
		public function read_entry(){
			if(isset($_GET['flo_read_msg']) && is_numeric($_GET['flo_read_msg']) ){
				header('Content-Type: image/jpeg');
				$hidden_image_url = plugin_dir_url( __FILE__ ).'read-entry.png';
				$img = @imageCreateFromPng($hidden_image_url);

				if($img){
					imagepng($img);
					imagedestroy($img);
				}


				//$_GET['flo_read_msg'] -> is the ID of the Entry post

				update_post_meta($_GET['flo_read_msg'],'entry_read','read'); // Mark  the entry as read

				die();
			}
		}


		public function unread_entries_reminder(){


			$forms_options = get_option('flo_forms_options');


			if(!$forms_options){
				$current_user = wp_get_current_user();

				$forms_options = array(
					'enable_email_reminder' => 1,
					//how many days old should entries be in order to triger the reminder email
					'entries_days_old_reminder' => 1,
					'send_to_email' => $current_user->user_email,
					'text_email' => 0,
	                'enable-captcha' => 0,
	                'g_site_key' => '',
	                'g_secret_key' => ''
				);
			}

			if(isset($forms_options['enable_email_reminder']) && $forms_options['enable_email_reminder'] == 1 && isset($forms_options['send_to_email']) && is_email($forms_options['send_to_email'])){

				$transient_name = 'flo_forms_reminder_interval';

				if ( FALSE == $request = get_transient( $transient_name ) ) {

		            $days_settings = $forms_options['entries_days_old_reminder'];
					$hours_old = 24*$days_settings;

					//subtract from the current time the number of hours passed as argument
					$old_entries_time = date("Y-m-d H:i:s", time()-3600*$hours_old );

					$args = array(
						'post_type' => 'flo_form_entry',
						'posts_per_page' => -1,
						'meta_query' => array(
						    array(
						     'key' => 'entry_read',
						     'compare' => 'NOT EXISTS' // this should work...
						    ),
						),
						'date_query' => array(
							array(
								'before'    => $old_entries_time,
								'inclusive' => true,
							),
						),

					);
					$query1 = new WP_Query( $args );

					if($query1->post_count){

						$tomail = $forms_options['send_to_email'];
						$subject = __('You have unread form entries on your site','flo-forms');
						$message = sprintf(__('This is a reminder that there are unread Entries on your website. You can check them %s here %s','flo-forms'),'<a  href="'.get_admin_url().'edit.php?post_type=flo_form_entry" target="_blank">','</a>');
						//get_admin_url().'edit.php?post_type=flo_form_entry'
						$headers = array();
						$headers[] = 'Content-Type: text/html; charset=UTF-8'; // send html email

						//$maybe_send_email = wp_mail( $tomail, $subject, $message, $headers);
            $maybe_send_email = Flo_Forms::wp_mail( $tomail, $subject, $message, $headers);

					}

					$transient_life = 24*60 * 60; // 24 hours transient
					//$transient_life = 1 * 60; // 1 min transient for test
		            set_transient( $transient_name, 'reminder run OK', $transient_life );
		        }
			}


		}

		public function maybe_store_user_meta($post_data, $entry_id) {

		  if(IS_FLO_FORMS_PRO) {
        $browser_meta = (array)json_decode( stripslashes( $post_data['browser_meta']) );

        /*
         * $browser_meta should be something like:
         * object(stdClass)#2141 (9) {
            ["screen"]=> string(10) "1440 x 900"
            ["browser"]=> string(6) "Chrome"
            ["browserVersion"]=> string(13) "73.0.3683.103"
            ["browserMajorVersion"]=>int(73)
            ["mobile"]=>bool(false)
            ["os"]=>string(8) "Mac OS X"
            ["osVersion"]=>string(7) "10_14_4"
            ["cookies"]=>bool(true)
            ["flashVersion"]=>string(8) "no check"
          }
         * */

        $users_ip = $_SERVER['REMOTE_ADDR'];

        $users_ip = '217.26.165.226'; // hardcoded office IP for testing

        $browser_meta['ip'] = $users_ip;

        $ip_api = "http://ip-api.com/json/".$users_ip."?fields=status,message,regionName,city,query";

        $args = array('sslverify'  => false );
        $response = wp_remote_get($url = $ip_api, $args);

        if(!is_wp_error( $response )) {

          $ip_response = json_decode( wp_remote_retrieve_body( $response ));

          if( isset($ip_response->status) && $ip_response->status == 'success' ) {
            if(isset($ip_response->regionName)  ) {
              $browser_meta['regionName'] = $ip_response->regionName;
            }

            if(isset($ip_response->city)  ) {
              $browser_meta['city'] = $ip_response->city;
            }
          }
        }

        update_post_meta($entry_id, 'browser_meta', $browser_meta);

      }

    }

    // log the errors occurring during the email sending
    public static function log_mail_errors($wp_error) {

      // log to the error file
      error_log('================ BOF FloForms email error [1]: (Read the details below) ================');
      error_log( print_r($wp_error, true) );
      error_log('================ EOF FloForms email error [1] ================');

      // log to an option as well in order to have a quiker access. Available by get parameter &flo_email_error=1 when on the settings page
      $error_log_length = 4096; // this is the max error length we want to keep in the options

      $messages = get_option( 'floforms_wperror_mail', '' );
      if (strlen($messages) > $error_log_length){
        $messages = ''; //reset the error log when it is too long
      }

      $messages .= '<h4 class="floforms-wperror-title">' . date('Y-m-d H:i:s') . '</h4><div>' . print_r($wp_error, true) . '</div>';
      update_option( 'floforms_wperror_mail', $messages );


    }

		/**
		 * triggered by sgo_css_combine_exclude.
		 * is meant to fix the css files combination issue caused by SG optimizer plugin
		 * It excludes the front end css files from combination
		 */
		public function siteground_css_combine_exclude( $exclude_list ) {
		    // Add the style handle to exclude list.
		    $exclude_list[] = 'flo-forms-public';
		    //$exclude_list[] = 'flo-forms-pikaday';

		    return $exclude_list;
		}

		/**
		 * Will return an array with links to style and app Js files
		 * used by FLEX Block plugin
		 */
		public static function getFiles(){
			$v = FLO_FORMS_VERSION || 123;
			return array(
				'style' => plugin_dir_url( __FILE__ ) . 'css/flo-forms-public.min.css?v=' . $v,
				'script' => plugin_dir_url(__FILE__).'../dist/js/app.min.js?v=' . $v
			);
		}
	}
}
