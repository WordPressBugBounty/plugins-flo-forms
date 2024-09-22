<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the form builder
 *
 * @link       http://flothemes.com
 * @since      1.0.0
 *
 * @package    Flo_Forms
 * @subpackage Flo_Forms/admin/partials
 */


  wp_enqueue_media();
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="flo-forms-app">
	<div class="form-settings-outer-wrapper">

    <?php include('premium-features.html'); ?>
		<flo-form-fields></flo-form-fields>
	</div> <!-- EOF form-settings-outer-wrapper -->
</div>
