import Vue from "vue";
import FontSettings from "../../admin/vue-components/FontSettings.vue";

if (jQuery(".app-font-settings").length) {
	new Vue({
		el: ".app-font-settings",

		beforeCreate: function () {
			//console.log(this.$formSettings)
		},

		data: {
			//vue_form_settings: flo_form_settings
		},

		components: {
			//'flo-form-fonts' : FontSettings
			"flo-font-settings": FontSettings,
		},
	});
}
