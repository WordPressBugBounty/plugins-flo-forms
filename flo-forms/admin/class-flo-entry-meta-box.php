<?php

class Flo_Entry_Meta_Box {

	public function __construct() {


		if ( is_admin() ) {

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
			'flo_entry_settings', // meta box id 
			__( 'Entry content', 'flo-forms' ), // Title of the meta box
			array( $this, 'render_metabox' ),  // call back funtion
			'flo_form_entry', // post type
			'normal', // The context within the screen where the boxes should display.
			'high' // priority
		);

	}

	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'flo_entry_nonce_action', 'flo_form_nonce' );


		// Retrieve an existing value from the database.
		$flo_entry_settings = get_post_meta( $post->ID, 'message_table', true );

		$flo_entry_settings = apply_filters('flo_form_entry_dashboard_content', $flo_entry_settings, $post->ID);
//deb_e($flo_form_settings); die();

		echo $flo_entry_settings;

		$form_attachments = get_post_meta( $post->ID, 'flo_forms_attachments', true );

		if( is_array($form_attachments) && sizeof($form_attachments)) {
			echo sprintf(__('%sAttached files%s','flo-forms'),'<h3>','</h3>');
			echo '<ul>';
			foreach ($form_attachments as $file) {
				echo '<li>';
				echo '<a href="'.$file['attachment_url'].'" target="_blank">'.$file['name'].'</a>';
				echo '</li>';
			}
			echo '</ul>';
		}
	}

	public function save_metabox(){
		// we do nothing for this one for now.
	}

}

new Flo_Entry_Meta_Box;

?>
