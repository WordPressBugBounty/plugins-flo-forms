import Vue from "vue";
import FloForm from "../public/vue-components/FloForm.vue";

// will store here the list of innitialized Flo Forms IDs.
window.initializedFloForms = [];

function initFloForms() {
	if (jQuery(".app-flo-forms").length) {
		jQuery(".app-flo-forms")
			.not(".flo-initialized-form")
			.each(function (index) {
				// make sure to not reinit the same forms if it was already initialized.
				// this will prevent the issue when the same form is inserted on the same page several times
				// this class will be used to prevent multiple initialization of the same form which causes issues
				jQuery(this).addClass("flo-initialized-form");

				new Vue({
					//el: '.app-flo-forms',
					el: this,
					beforeCreate: function () {
						//console.log(this.$formSettings)
					},
					data: {
						//vue_form_settings: flo_form_settings
					},
					props: ["form_id"],
					components: {
						//'hello': Hello,
						"flo-form": FloForm,
					},
					methods: {
						addFormField: function (key, event) {
							console.log(event, key);
						},
					},
				});
			});
	}
}
initFloForms();
jQuery("body").on("flo-forms-reinit", initFloForms);
