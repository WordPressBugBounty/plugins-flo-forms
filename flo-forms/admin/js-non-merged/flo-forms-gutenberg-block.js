const { registerBlockType } = wp.blocks; //Blocks API
const { createElement } = wp.element; //React.createElement
const { __ } = wp.i18n; //translation functions
const { InspectorControls } = wp.blockEditor; //Block inspector wrapper
const { TextControl, SelectControl } = wp.components; //WordPress form inputs and server-side renderer
const ServerSideRender = wp.serverSideRender;

registerBlockType("flo-forms/form", {
	title: __("Flo Forms"), // Block title.
	category: __("common"), //category
	icon: "feedback",
	keywords: [__("Flo Forms"), __("Contact Form"), __("Form")],
	attributes: {
		id: {
			//default: '',
			default: ff_posts[0].value,
		},
	},

	//display the shortcode
	edit(props) {
		// the shortcode uses localized js variables that contain the necessary data to render the form.
		// because we can not localize the variables the standard way using wp_localize_script,
		// we need a workaround. Therefore we will inject the variables in the dom, and when the form block
		// is ready, we will inject the vue app script as well
		// The variables we need to inject:
		var schema_name = "flo_form_schema_" + props.attributes.id,
			model_name = "flo_form_model_" + props.attributes.id,
			form_styling_name = "form_styling_" + props.attributes.id,
			flo_form_settings_name = "flo_form_settings_" + props.attributes.id;

		// check if the schema variable for the current form is defined
		// If it is not defined, we will make an Ajax request to retrieve the necessary data.
		if (typeof window[schema_name] === "undefined") {
			jQuery.ajax({
				url: ajaxurl,
				data: "&action=get_schema_and_model&post_id=" + props.attributes.id,
				type: "POST",
				dataType: "json",
				cache: false,
				success: function (json) {
					if (json.flo_form_schema && json.flo_form_model) {
						const var_script = document.createElement("script", {
							key: Math.random(),
						});
						var_script.text =
							"var " +
							schema_name +
							"=" +
							JSON.stringify(json.flo_form_schema) +
							";";
						var_script.text +=
							"var " +
							model_name +
							"=" +
							JSON.stringify(json.flo_form_model) +
							";";
						var_script.text +=
							"var forms_options =" + JSON.stringify(json.forms_options) + ";";
						var_script.text += 'var formData = {"is_pro_version":false};';
						var_script.text +=
							"var " +
							form_styling_name +
							"={}; var " +
							flo_form_settings_name +
							"={};";
						document.body.appendChild(var_script);
					}
				},
			});
		}

		// Load the VUE app script when the form is available in the DOM
		var blockLoaded = false;
		var blockLoadedInterval = setInterval(function () {
			if (jQuery(".app-flo-forms_" + props.attributes.id).length) {
				const script = document.createElement("script", { key: Math.random() });
				script.src = flo_forms_dir_url + "../dist/js/app.min.js";

				document.body.appendChild(script);
				blockLoaded = true;
			}
			if (blockLoaded) {
				clearInterval(blockLoadedInterval);
			}
		}, 500);

		const attributes = props.attributes;
		const setAttributes = props.setAttributes;

		//Function to update id attribute
		function changeId(id) {
			setAttributes({ id }); // set the selected ID
		}

		//Display block preview and UI
		return createElement("div", { key: Math.random() }, [
			//Preview a block with a PHP render callback
			createElement(ServerSideRender, {
				key: Math.random(),
				block: "flo-forms/form",
				attributes: attributes,
			}),

			//Block inspector
			createElement(InspectorControls, { key: Math.random() }, [
				createElement(SelectControl, {
					key: Math.random(),
					value: attributes.id,
					label: __("Select a Flo Form"),
					onChange: changeId,
					options: ff_posts,
				}),
			]),
		]);
	},
	save() {
		return null; //save has to exist. This all we need
	},
});
