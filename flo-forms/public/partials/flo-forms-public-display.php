<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://flothemes.com
 * @since      1.0.0
 *
 * @package    Flo_Forms
 * @subpackage Flo_Forms/public/partials
 */
 
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="app-flo-forms app-flo-forms_<?php echo $atts['id']; ?>" data-form_id="<?php echo $atts['id']; ?>">
  <form class="flo-form" enctype="multipart/form-data" >
    <flo-form v-bind:form_id="<?php echo $atts['id']; ?>"></flo-form>
    <input type="hidden" name="flo_fid" value="<?php echo $atts['id']; // add here the form ID ?>">
    <div class="flo overlay-loader">
      <div class="loader"><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
    </div>
    <div class="flo-response"></div>
    <div class="flo-response-error"></div>
  </form>
</div>