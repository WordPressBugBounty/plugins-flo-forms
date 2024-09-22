<?php

class Flo_Form_Meta_Box {

	public static $available_fields;

	public function __construct() {


		if ( is_admin() ) {

            $forms_options = get_option('flo_forms_options');

            if(!$forms_options){
                $current_user = wp_get_current_user(); // get the current user info

                // if the options are not save yet, we define the defaults
                $forms_options = array(
                    'enable_email_reminder' => 1,
                    'reply_to_header' => 1,
                    //how many days old should entries be in order to triger the reminder email
                    'entries_days_old_reminder' => 1,
                    'send_to_email' => $current_user->user_email,
										'custom_date_format' => '',
										'mail_from_name' => '',
                    'text_email' => 0,
                    'enable-captcha' => 0,
                    'g_site_key' => '',
                    'g_secret_key' => '',
										'use-smtp' => 1
                );
            }


			self::$available_fields = array(
				'text' => array(
						'class' => 'text dashicons dashicons-editor-textcolor',
						'label' => __('Single line text','flo-forms'),
						'has_placeholder' => true,
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => true
					),
				'textarea' => array(
						'class' => 'textarea dashicons dashicons-editor-paragraph',
						'label' => __('Paragraph text','flo-forms'),
						'has_placeholder' => true,
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => true
					),
				'email' => array(
						'class' => 'email dashicons dashicons-email-alt',
						'label' => __('Email','flo-forms'),
						'has_placeholder' => true,
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => true
					),
				'date' => array(
						'class' => 'date dashicons dashicons-calendar-alt',
						'label' => __('Date','flo-forms'),
						'has_placeholder' => true,
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => true
					),
				'checkbox' => array(
						'class' => 'checkbox dashicons dashicons-yes',
						'label' => __('Checkbox','flo-forms'),
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => true
					),
				'radio_button' => array(
						'class' => 'radio_button dashicons dashicons-marker',
						'label' => __('Radio button','flo-forms'),
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => true
					),
				'dropdown' => array(
						'class' => 'dropdown dashicons dashicons-arrow-down',
						'label' => __('Dropdown','flo-forms'),
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => true
					),
				'section_break' => array(
						'class' => 'section_break dashicons',
						'label' => __('Section break','flo-forms'),
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => true
					),
				'hidden' => array(
						'class' => ' dashicons-hidden',
						'label' => __('Hidden','flo-forms'),
                        'has_width' => false,
                        'has_css_class' => false,
                        'can_be_required' => false
					),
				'message' => array(
						'class' => ' message dashicons dashicons-media-text',
						'label' => __('Message','flo-forms'),
                        'has_width' => true,
                        'has_css_class' => true,
                        'can_be_required' => false
					),

			);

            if(isset($forms_options['enable-captcha']) && $forms_options['enable-captcha']){
                self::$available_fields['captcha'] = array(
                    'class' => 'dashicons dashicons-captcha',
                    'label' => __('Captcha','flo-forms'),
                    'has_width' => true,
                    'has_css_class' => true,
                    'can_be_required' => false
                );
            }

			// also add the conditons that it is flo_forms post type
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

	}

	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'add_metabox'  ),9        );
		add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );

	}

	public function add_metabox() {

		add_meta_box(
			'flo_form_settings', // meta box id
			__( 'FloForms Builder', 'flo-forms' ), // Title of the meta box
			array( $this, 'render_metabox' ),  // call back funtion
			'flo_forms', // post type
			'side', // The context within the screen where the boxes should display.
			'high' // priority
		);

	}

  /**
   * @param $post
   */
  public function render_metabox($post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'flo_form_nonce_action', 'flo_form_nonce' );



		$flo_form_settings = get_post_meta( $post->ID, 'flo_form_settings', true );

		$flo_form_schema = get_post_meta( $post->ID, 'flo_form_schema', true);

    $flo_form_schema = stripslashes($flo_form_schema);

		// the form fields values
		$flo_form_model = get_post_meta( $post->ID, 'flo_form_model', true);


		// make sure we set the defaults if some data is not available
		$flo_form_settings = self::set_default_settings($flo_form_settings);

		//prepare the data for the outpout
		$flo_form_settings = self::prepare_data($flo_form_settings);


		// if the confirmation option is a redirect to a page
		if( 'page_confirmation' == $flo_form_settings['confirmation_opt']){
			$text_conf_claass = ' hidden ';
			$page_conf_class = '';
		}else{
			$text_conf_claass = '';
			$page_conf_class = ' hidden ';
		}

		$args = array(
				'selected'              => $flo_form_settings['confimation_page'],
    			'echo'                  => 0,
    			'name'                  => 'flo_form_settings[confimation_page]',
    			'show_option_none'      => __('Select a page','flo-forms'), // string
    			'class'					=> 'page-confirmation-value '.$page_conf_class
			);
		$confimation_page = wp_dropdown_pages( $args );

		//include the form builder markup
		include_once('partials/flo-forms-admin-display.php');

		wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'flo_form_settings', $flo_form_settings);

		if($flo_form_schema && trim($flo_form_schema) != '') {
			wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'flo_form_schema', [$flo_form_schema]);
		}

		if($flo_form_model && trim($flo_form_model) != '') {
			wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'flo_form_model', [$flo_form_model]);
		}

		if(isset($_GET['post'])){
			wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'flo_form_id', [$_GET['post']]);
		}else{
			wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'flo_form_id', ['0']);
		}

		wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'plugin_dir_url_admin', [plugin_dir_url( __FILE__ )]);
    wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'settings_page_url', [menu_page_url( $menu_slug = 'flo_forms_settings', $echo = false)]);



		$plugin_path = dirname(__FILE__) . '/../flo-forms.php';

		$plugin_data = get_plugin_data($plugin_path);


		// will use $is_pro_version as a flag for the Pro or Free version
		if($plugin_data['Name'] == 'Flo Forms Pro') {
			$is_pro_version = '1';
			include_once dirname(__FILE__).'/../pro/flo-forms-pro.php';
		}else{
			$is_pro_version = '0';
      wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'predefined_templates', ['{}']); // default val
      wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'user_saved_templates', ['{}']); // default val
		}

		wp_localize_script( $plugin_name = 'flo_vue_bundle_js', 'is_pro_version', [$is_pro_version]);
	}

	public function save_metabox( $post_id, $post ) {

		// Add nonce for security and authentication.
		if(isset($_POST['flo_form_nonce'])){
			$nonce_name   = $_POST['flo_form_nonce'];
			$nonce_action = 'flo_form_nonce_action';
		}


		// Check if a nonce is set.
		if ( ! isset( $nonce_name ) )
			return;

		// Check if a nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) )
			return;

		// Check if the user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		// Check if it's not an autosave.
		if ( wp_is_post_autosave( $post_id ) )
			return;

		// Check if it's not a revision.
		if ( wp_is_post_revision( $post_id ) )
			return;


		// Sanitize user input.
		$flo_form_settings = isset( $_POST[ 'flo_form_settings' ] ) ? self::sanitize( $_POST[ 'flo_form_settings' ])  : '';
		// Update the meta field in the database.

    //var_dump($flo_form_settings); die;
		update_post_meta( $post_id, 'flo_form_settings', $flo_form_settings );


		// Vue settings

		if(isset( $_POST[ 'flo-form-schema' ] )) {
			// for sanitization of the user input.
			$allowed_html = array(
					'a' => array(
							'href' => array(),
							'title' => array(),
							'target' => array()
					),
					'br' => array(),
					'em' => array(),
					'strong' => array(),
					'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
					'p' => array(),
					'b' => array(),
					'i' => array()
			);


			// there may be fields attributes that allow html tags, and in case those tags contain double quotes -> '"' ,
			// we need to replace them with the single quotes.
			// That usually happens with <a> tags
			$schema = preg_replace_callback(
				'|<\s*a[^>]*>(.*?)<\s*/\s*a>|', // match the <a> tags and its content
				function ($matches) {
					return str_replace( '"',"'", $matches[0]); // replace the " with ' for each matched
				},
				stripslashes($_POST[ 'flo-form-schema' ]) // work with the schema without slashes
			);

			// replace the double quotes if any, and the '><' which breaks the form
      $schema = str_replace('><','> <',  str_replace('\"',"\'",$schema));

			// replace \n with <br>
			$schema = str_replace('\n','<br>',$schema);

			$schema = self::fix_non_matching_angle_brackets( $schema );

			// strip the slashes from the result one more time, just in case
			// sanitize the result using wp_kses_post
			// add the slashes back using wp_slash -> for the default schema double quotes

			$schema	= wp_slash(wp_kses_post(  stripslashes($schema), $allowed_html)); // sanitize


		}

		$flo_form_schema = isset( $_POST[ 'flo-form-schema' ] ) ? ( $schema ) : '{"groups":[{"fields":[]}]}';


		// Update the meta field in the database.
    update_post_meta( $post_id, 'flo_form_schema', $flo_form_schema ) ; // schema represents the Form fields structure

		$flo_form_model = isset( $_POST[ 'flo-form-model' ] ) ? ( sanitize_textarea_field($_POST[ 'flo-form-model' ]) ) : '{}';
		// Update the meta field in the database.
		update_post_meta( $post_id, 'flo_form_model', $flo_form_model ); // model represents the fields default value

	}

	/**
	 * Check if a giving string has non matching angle brackets and if the unmatched opening bracket (<) is found
	 * it is replaced with ('&lt;')
	 * Is used to clear the schema to it can safely be saved in the DB
	 *
	 * NOTE! this fix will fail if the given string contains 2 or more '<' without a match
	 *
	 * @param    string    $string        The given string
	 * @return   STRING    $string  the modiffie
	 */

	public static function fix_non_matching_angle_brackets( $string ) {

		$counter = 0; // will count the difference between oppening and closing brackets
		$length = strlen( $string );
		$nonMatchedIndex = array(); // store the index of the '<' character when necessary
		$open_indexes = array(); // store the index of the '<' character when encountered


		$open_threshhold = 1;
		for( $i = 0; $i < $length; $i ++ ) {
			$char = $string[ $i ];
			if( $char == '<' ) {
				$counter ++;
				$open_indexes[] = $i;
			} elseif( $char == '>' ) {
				$counter --;
			}

			if($counter > $open_threshhold) { // that means we are using the  '<' character two times in a row without closing it
				$open_threshhold ++;
				$nonMatchedIndex[] = $i;
			}

		}


		if(sizeof($nonMatchedIndex)) { // we end up here when

			for ( $i = sizeof($nonMatchedIndex) - 1; $i >= 0; $i-- ) {

					$ind = $nonMatchedIndex[$i];

					if($string[$ind] == '<') {
					//	var_dump('$string[$ind]PP: ', $string[$ind]);

						$string =   substr_replace($string, '&lt;', $ind, 1);
					}
				}
		}else if($counter > 0) {
			$string =   substr_replace($string, '&lt;', $open_indexes[sizeof($open_indexes) - 1], 1);
		}

		return $string;
	}

	/**
	 * A custom sanitization function that will take the incoming input, and sanitize
	 * the input before handing it back to WordPress to save to the database.
	 *
	 * @since    1.0.0
	 *
	 * @param    array    $input        The address input.
	 * @return   array    $new_input    The sanitized input.
	 */
	static public function sanitize( $input ) {

		// Initialize the new array that will hold the sanitize values
		$new_input = array();

		// Loop through the input and sanitize each of the values
		foreach ( $input as $key => $val ) {

			if(is_array($input[ $key ])){
			  $new_val = array();

			  foreach ($val as $val_key => $val_val) {
					if(!is_array($val_val)){
						$new_val[$val_key] = wp_kses_post($val_val);
					}else{
						$new_val[$val_key] = $val_val;
					}

        }

        $new_input[ $key ] = $new_val;
			}else{
				$new_input[ $key ] = ( isset( $input[ $key ] ) ) ?
				wp_kses_post($val) :
				'';
			}


		}

		return $new_input;

	}

	/**
	 *
	 * Prepare the saved setting for displaying on the screen
	 *
	 */
	static public function prepare_data($data_array){

		$new_data = array();

		foreach ($data_array as $key => $value) {
			//if( empty( $value ) ){
      if( !isset( $value ) ){
				$clean_value = ''; // set the default value
			}else{
				if(!is_array($value)){
					$clean_value = esc_attr($value); // escape the saved value
				}else{
					$clean_value = self::prepare_data($value); // if the value is an array, we recursively call this method again
				}

			}
			$new_data[$key] = $clean_value;
		}
		return $new_data;
	}


	static public function set_default_settings($data_array){

		$current_user = wp_get_current_user(); // get the current user info

		// when new options will be added, add the defaults below:
		$default_form_settings = array(
			'send-to-email' => $current_user->user_email,
			'email-subject' => sprintf(__('A new message from %s','flo-forms'),get_bloginfo('name')),
			'label-placement' => 'topLabel',
			'confirmation_opt' => 'text_confirmation',
			'text_confirmation_value' => __('Thank you!','flo-forms'),
			'nr_of_columns' => 1,
			'confimation_page' => 0,
			'confimation_page_title' => '',
      'enable_email_confirmation' => '',
      'email_confirmation_value' => '%all_fields%',
      'email_confirmation_to'=> '%field_email%',
      'email_confirmation_subject'=> __('Submission Confirmation', 'flo-forms'),
      'styling' => array(
        'use_custom_colors' => '',
        'label_color' => '',
        'input_color' => '',
				'inputbg_color' => '',
				'hint_color' => '',
        'border_color' => '',
        'placeholder_color' => '',
        'formbg_color' => '', // form background

        'use_custom_fonts' => '',
        'label' => array(
          'font' => 'initial',
          'font_size' => 12,
          'letter_spacing' => 0,
          'line_height' => 1.2,
					'italic' => '',
					'bold' => '',
					'underline' => ''
        ),
        'input' => array(
          'font' => 'initial',
          'font_size' => 12,
          'letter_spacing' => 0,
          'line_height' => 1.2,
					'italic' => '',
					'bold' => '',
					'underline' => ''
        ),
				'hint' => array(
					'font' => 'initial',
					'font_size' => 10,
					'letter_spacing' => 0,
					'line_height' => 1.2,
					'italic' => '',
					'bold' => '',
					'underline' => ''
				),
				'button' => array(
					'font' => 'initial',
					'font_size' => 12,
					'letter_spacing' => 0,
					'line_height' => 1.2,
					'border_radius' => 0,
					'border_width' => 0,
					'button_width' => 200,
					'padding_y' => 10,
					'padding_x' => 10,
					'italic' => '',
					'bold' => '',
					'underline' => ''
				),
      ),
      'padding' => array(
        'top' => '0',
        'right' => 0,
        'bottom' => 0,
        'left' => 0,
      ),
			'mobile_padding' => array(
				'top' => '0',
				'right' => 0,
				'bottom' => 0,
				'left' => 0,
			),
		);

    $default_form_settings = apply_filters('flo_form_default_form_settings', $default_form_settings);

		if(!is_array($data_array)){
			$data_array = $default_form_settings;
		}else{

			foreach ($default_form_settings as $key => $value) {

			  /*
			   * for the array values (i.e. styling), iterate through each key to make sure it exists
          if not, we set the default
			  */
        if(is_array($value)) {
          foreach ($value as $val_array_key => $vv) {

          	// for typography settings
          	if(is_array($vv)) {
          		foreach ($vv as $vv_key => $third_level_v) {

          			if(!isset($data_array[$key][$val_array_key][$vv_key])){
									$data_array[$key][$val_array_key][$vv_key] = $third_level_v;

								}
							}
						}

            if(!isset($data_array[$key][$val_array_key])){
              $data_array[$key][$val_array_key] = $vv;
            }
          }
        }

				// if for some reason one of the options does not exist, then use the default value
				if(!isset($data_array[$key])){
					$data_array[$key] = $value;
				}
			}
		}
	//var_dump($data_array);
		return $data_array;
	}


}

new Flo_Form_Meta_Box;
?>
