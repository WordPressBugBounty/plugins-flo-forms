<template>

	<div class="panel-body">

		<div class="form-preview topLabel " v-bind:class="{ 'two-columns': schema.groups.length == 3 }">
			<vue-form-generator :schema="schema" :model="model" tag="section" :options="formOptions"
													:style="'--data-btn-color: '+btnTextColorValue+'; --data-btn-bgcolor: '+ btnBgColorValue
													+ '; --data-btn-border-color: '+ btnBorderColorValue
													+ '; --data-btn-color-hover: '+btnTextColorValueHover+'; --data-btn-bgcolor-hover: '+ btnBgColorValueHover
													+ '; --data-btn-border-color-hover: '+ btnBorderColorValueHover + '; --data-inputbg-color: '+ formStyling.inputbg_color
													+ '; --data-label-color: ' + formStyling.label_color + '; --data-input-color: '+ formStyling.input_color
													+ '; --data-border-color: '+ formStyling.border_color + '; --data-placeholder-color: '+ formStyling.placeholder_color
													+ '; --data-hint-color: '+ formStyling.hint_color + renderStylingData(formStyling) "

													:class="'btn-position--'+btnPosition + getStylingClass(formStyling)" @validated="onValidated"></vue-form-generator>

			<textarea class="flo-form-data" v-if="model" v-html="model" name="flo-form-model"></textarea>
		  <textarea class="flo-form-data" v-if="schema.groups[0].fields.length" v-html="schema" name="flo-form-schema"></textarea>
		</div>

	</div>

</template>

<script>

import Vue from 'vue';
window.Vue = Vue;


//import CircularJSON from "circular-json";
//import VueFormGenerator from "vue-form-generator";
import VueFormGenerator from '../../admin/vue-components/vue-form-generator';

Vue.use(VueFormGenerator);

//console.log(JSON.parse(flo_form_schema));

/**
 *
 * custom field for flo captcha - flocaptcha
 *
 */
if(typeof forms_options !== 'undefined' &&  forms_options['enable-captcha'] && forms_options['enable-captcha'] != '0') {
	Vue.component("field-flocaptcha", {
	    template: '<div class="flo-g-recaptcha" style="width: 100%; text-align: center;"> <div> <div style="display: inline-block" class="g-recaptcha" data-sitekey="'+forms_options.g_site_key+'"></div> </div> </div>',
	    mixins: [ VueFormGenerator.abstractField ]
	});
}

export default {
  	components: {
      "vue-form-generator": VueFormGenerator.component
    },

    data: function() {

	    //flo_form_schema, and flo_form_model and forms_options are localized from PHP

			// honeypot input to fight spam
			let flo_honeypot = {
					"field_id": 'flohp',
					"model": "field_flohp", // flohp = flo honeypot
					"styleClasses": "flohp", // the class used to hide the field
					"placeholder": "",
					"required": false,
					"type": "input",
					"inputType": 'text',
					"label": 'Flo contact phone',
					"attributes": {
	            input: {
              class: "form-control",
              width: '100%',
              enable_conditional_logic: false,
              required: false,
						}
					}
				}

			let the_schema = 	JSON.parse(eval( 'flo_form_schema_' + this.$attrs.form_id) )
			let firstGroupFields = the_schema.groups[0]

			firstGroupFields.fields.push(flo_honeypot);


	    return {
	        //model: {},
	        model: JSON.parse(eval('flo_form_model_' +  this.$attrs.form_id) ),
	        schema: the_schema,
					isProVersion: formData.is_pro_version,
	        formOptions: {
	          validateAfterLoad: false,
						validateAfterChanged: true,
						validateDebounceTime: 1200
	        },

	        fieldsWidth: {
	          '100%':'100',
	          '50%':'50',
	          '33%':'33',
	          '25%':'25'
	        },
					formStyling: eval('form_styling_' + this.$attrs.form_id),
				  formSettings: eval('flo_form_settings_' + this.$attrs.form_id),
	        btnPosition: "auto",
	        btnTextColorValue: "#ffffff",
					btnBorderColorValue: "#000000",
	        btnBgColorValue: "#000000",
					btnTextColorValueHover: "#ffffff",
					btnBorderColorValueHover: "#000000",
					inputbgColorValue: "#ffffff",
					btnBgColorValueHover: "#000000",
	        isValid: false,
	        validationErrors: [],
	        formsOptions: forms_options
	    }

	  },

	  mounted() {

	    let groups_size = this.schema.groups.length
	    // initialize the Submit button text color and bgcolor

	    this.btnTextColorValue = this.schema.groups[groups_size-1].fields[0].attributes.input.text_color
			this.btnBorderColorValue = this.schema.groups[groups_size-1].fields[0].attributes.input.border_color
	    this.btnBgColorValue = this.schema.groups[groups_size-1].fields[0].attributes.input.bg_color
			this.btnTextColorValueHover = this.schema.groups[groups_size-1].fields[0].attributes.input.text_color_hover
			this.btnBorderColorValueHover = this.schema.groups[groups_size-1].fields[0].attributes.input.border_color_hover
			this.btnBgColorValueHover = this.schema.groups[groups_size-1].fields[0].attributes.input.bg_color_hover
	    this.btnPosition = this.schema.groups[groups_size-1].fields[0].attributes.input.button_position

	    // insert captcha before the submit button
	    let flo_captcha = {
												    type: "flocaptcha",
												    label: "",
												    model: "flocaptcha"
												}

			if(forms_options['enable-captcha'] && forms_options['enable-captcha'] != '0') {
				this.schema.groups[groups_size-1].fields.splice(0,0, flo_captcha)
			}

			/*utm_campaign colection experiment*/
	    // console.log(this.schema.groups);
			// console.log(this.model);
			// var urlParams = new URLSearchParams(window.location.search);
			// console.log('urlParams',urlParams);
			// if(typeof this.schema.groups != 'undefined') {
			// 	let that = this;
			//
			// 	this.schema.groups.forEach(function (group, group_index) {
			//
			// 		if(typeof group.fields  != 'undefined') {
			// 			group.fields.forEach(function (field) {
			// 				if(field.label == 'utm_campaign'){
			// 					//field.label
			// 					that.model[field.model] = "test_utm_campaign"
			// 				}
			// 			})
			// 		}
			// 	})
			// }
	  },

	  /**
	   * Check the conditonal logic settings for the passed field and add
	   * or remove the class that hides a given field
	   */
	  methods: {

			onValidated(isValid, errors) {
	  		//console.log("Validation result: ", isValid, ", Errors:", errors);

	  		if(!isValid) { // for any field which is invalid we will check its classes

	  			let field_ids_to_remove = []
	  			errors.forEach((error, error_index ) => {
	  				let field_classes = error.field.styleClasses;

	  				if(field_classes.search('hidden-field') != -1) {
		  				// if the field has the class 'hidden-field', we ignore any validation errors

		  				field_ids_to_remove.push(error.field.field_id)
		  			}
	  			})


	  			field_ids_to_remove.forEach((err_field_id, err_field_id_index ) => {

	  				// use reducer to remove
	  				errors.reduce(function(list, current_error, index) {
						    if (current_error.field.field_id == err_field_id) list.push(index);
						    return list;
						}, []).reverse().forEach(function(index) {
						    errors.splice(index,1); // remove the error found by the reducer that has the field_id equal to the current iteration err_field_id
						});


	  			})

	  			if(!errors.length){ // if there are no errors, set isValid to true
  	 				this.isValid = true
  	 			}else{
  	 				this.isValid = isValid
  	 			}

	  			this.validationErrors = errors

	  		}else{
	  			this.isValid = isValid
  	 			this.validationErrors = errors
	  		}
		  },

	  	condLogicClass: function(field) {

	  		let that = this
	  		let field_logic_obj = [] // store here the truth evaluation from each conditonal logic group

	  		if( typeof field.attributes !== 'undefined' && field.attributes.input.enable_conditional_logic) { // cond logic enabled

	  			// conditional logic groups
	  			let cl_groups = field.attributes.input.conditional_logic_groups


	  			cl_groups.forEach( function(cl_group, cl_group_index){
	  				let logic_obj = []

	  				cl_group.rules.forEach(function(rule, rule_index){
	  					let rule_evaluation = that.checkCondLogicPropertyToValue(rule)

	  					logic_obj.push({'truth':rule_evaluation, 'next_operator' : rule.next_rule_operator })


	  				})
						// console.log(logic_obj);
						// console.log(that.evaluateTruth(logic_obj))

	  				if(cl_group.next_group_operator == 'and' || cl_group.next_group_operator == '&amp;&amp;') {
							cl_group.next_group_operator = ' && '
						}

						field_logic_obj.push({
							'truth':that.evaluateTruth(logic_obj),
							'next_operator' : cl_group.next_group_operator
						})
	  			})

	  			//console.log('Overall: ',that.evaluateTruth(field_logic_obj) )

	  			let current_field_class = field.styleClasses,
	  					updated_field_class = ''

	  			if(that.evaluateTruth(field_logic_obj)) {
	  				// remove hidden class
	  				updated_field_class = current_field_class.replace("hidden-field", "")
	  			}else{
	  				// add hidden class
	  				if(!current_field_class.includes('hidden-field')){
	  					updated_field_class = current_field_class + ' hidden-field';
	  				}else{
	  					updated_field_class = current_field_class
	  				}
	  			}

	  			field.styleClasses = updated_field_class

	  		}
	  	},

	  	checkConditionalLogicClass: function() {
	  		let that = this

	  		if(typeof this.schema.groups != 'undefined') {

		      this.schema.groups.forEach(function (group, group_index) {

		        if(typeof group.fields  != 'undefined') {
		          group.fields.forEach(function (field) {
		          	that.condLogicClass(field)
		          })
		        }
		      })
		    }

	  	},

	  	evaluateTruth: function(logic_obj) {
	  		let truth_str = ''
	  		logic_obj.forEach( function (logic_operator, index) {
	  			truth_str += logic_operator.truth;
	  			if(index != logic_obj.length - 1){

						if(logic_operator.next_operator == '&amp;&amp;' || logic_operator.next_operator == 'and') {
							logic_operator.next_operator = '&& '
						}

	  				truth_str += " " +logic_operator.next_operator
	  			}
	  		})

	  		return eval(truth_str);
	  	},

	  	/**
	  	 *
	  	 * Having a conditional logic rule, we take it's value and the rule property, and check against the model value
	  	 *
	  	 */

	  	checkCondLogicPropertyToValue: function(rule) {
	  		let model = this.model // model - where the for field's values are stored
	  		let field_model_val = '' // default init value
	  		let properties_maping = {'is' : '===', 'is_not' : '!==', 'greater_than' : '>', 'less_than' : '<', 'contains' : 'contains', 'starts_with' : 'starts with', 'ends_with' : 'ends with'}

	  		if(typeof this.model['field_'+rule.rule_select] != 'undefined') {
	  			field_model_val = this.model['field_'+rule.rule_select]
	  		}

	  		switch(rule.rule_property) {
			    case 'contains':
			    	  if( (typeof field_model_val != 'undefined') && field_model_val != '' && field_model_val != null && field_model_val.includes(rule.rule_input) ){
			        	return true;
			        }
			      break;
			    case 'starts_with':
			    		if(field_model_val.toString().startsWith(rule.rule_input)){
			        	return true;
			        }
			      break;
			    case 'ends_with':
			        if(field_model_val.toString().endsWith(rule.rule_input)){
			        	return true;
			        }
			      break;
			    default:
			    	let rule_property = properties_maping[rule.rule_property]

			    	// we have 'NaN' when a 'number' field is used and something is entered, then the field value is removed
			    	if( field_model_val == null || field_model_val.toString() == 'NaN'){
			    		return false
			    	}

			    	let result

			    	// for the checklist elements we have a special case because all the selected values are in an array
						if(Array.isArray(field_model_val) && field_model_val.length > 1) { // we have a checkbox list

							result = false
							field_model_val.forEach((list_elem_val) => {

								let list_elem_comparision = eval( '"'+(list_elem_val.toString().toLowerCase()+'"'+' '+ rule_property +' "'+rule.rule_input.toString().toLowerCase() + '"') )

								if(list_elem_comparision) { // if at least one of the checklist values coresponds to the selected criteria, we consider that we have matched the condition
									result = true
								}
							})

						}else{
							result = eval( '"'+(field_model_val.toString().toLowerCase()+'"'+' '+ rule_property +' "'+rule.rule_input.toString().toLowerCase() + '"') );
						}

			      return result

				}

				return false //return 'false' if we did not return anything until this moment

	  	},


			getStylingClass: function(formStyling) {
				let custom_classes = ''

				if(this.isProVersion == 1) {
					custom_classes += ' ff-pro '

					if(formStyling.use_custom_colors == 1) {
						custom_classes += ' flo-form--custom-colors '
					}

					if(formStyling.use_custom_fonts == 1) {
						custom_classes += ' flo-form--custom-fonts '
					}
				}


				return custom_classes
			},

			renderStylingData: function(formStyling) {

				if( this.isProVersion !== '1') {
					return '';
				}

				let data_string = ''

				// add in the array below the new elements which have font options
				let fonOptionsData = ['label','input','hint','button']

				fonOptionsData.forEach((fontOption,fontOption_index) => {
					if( typeof formStyling[fontOption] !== 'undefined' && typeof formStyling[fontOption].font !== 'undefined') {
						data_string += ' ; --data-'+fontOption+'-font: ' + formStyling[fontOption].font;
					}

					if(typeof formStyling[fontOption] !== 'undefined' && typeof formStyling[fontOption].font_size !== 'undefined') {
						data_string += ' ; --data-'+fontOption+'-font-size: ' + formStyling[fontOption].font_size/16 + 'rem ';
					}

					if( typeof formStyling[fontOption] !== 'undefined' && typeof formStyling[fontOption].letter_spacing !== 'undefined') {
						data_string += ' ; --data-'+fontOption+'-letter-spacing: ' + formStyling[fontOption].letter_spacing + 'px ';
					}

					if( typeof formStyling[fontOption] !== 'undefined' && typeof formStyling[fontOption].line_height !== 'undefined') {
						data_string += ' ; --data-'+fontOption+'-line-height: ' + formStyling[fontOption].line_height + 'em ';
					}

					if( typeof formStyling[fontOption] !== 'undefined' && typeof formStyling[fontOption].italic !== 'undefined' && formStyling[fontOption].italic == 1) {
						data_string += ' ; --data-'+fontOption+'-italic: italic';
					}
					if( typeof formStyling[fontOption] !== 'undefined' && typeof formStyling[fontOption].bold !== 'undefined' && formStyling[fontOption].bold == 1) {
						data_string += ' ; --data-'+fontOption+'-bold: bold';
					}
					if( typeof formStyling[fontOption] !== 'undefined' && typeof formStyling[fontOption].italic !== 'undefined' && formStyling[fontOption].underline == 1) {
						data_string += ' ; --data-'+fontOption+'-underline: underline';
					}



				})



				if(typeof formStyling.button !== 'undefined' && typeof formStyling.button.padding_y !== 'undefined') {
					data_string += ' ; --data-button-padding-y: ' + formStyling.button.padding_y + 'px ';
				}

				if(typeof formStyling.button !== 'undefined' && typeof formStyling.button.button_width !== 'undefined') {
					data_string += ' ; --data-button-width: ' + formStyling.button.button_width + 'px ';
				}

				if(typeof formStyling.button !== 'undefined' && typeof formStyling.button.border_radius !== 'undefined') {
					data_string += ' ; --data-button-border-radius: ' + formStyling.button.border_radius + 'px ';
				}

				if(typeof formStyling.button !== 'undefined' && typeof formStyling.button.border_width !== 'undefined') {
					data_string += ' ; --data-button-border-width: ' + formStyling.button.border_width + 'px ';
				}

				if(typeof formStyling.button !== 'undefined' && typeof formStyling.button.border_width!== 'undefined') {
					data_string += ' ; --data-button-border-width: ' + formStyling.button.border_width + 'px ';
				}

				if( typeof formStyling.button !== 'undefined' && typeof formStyling.button.line_height !== 'undefined') {
					data_string += ' ; --data-button-line-height: ' + formStyling.button.line_height + 'em ';
				}

				if( typeof this.formSettings.padding !== 'undefined' && typeof this.formSettings.padding.top!== 'undefined') {
					data_string += ' ; --data-form-padding-top: ' + this.formSettings.padding.top+ 'px; ';
				}
				if( typeof this.formSettings.padding !== 'undefined' && typeof this.formSettings.padding.right!== 'undefined') {
					data_string += ' --data-form-padding-right: ' + this.formSettings.padding.right+ 'px; ';
				}
				if( typeof this.formSettings.padding !== 'undefined' && typeof this.formSettings.padding.bottom!== 'undefined') {
					data_string += ' --data-form-padding-bottom: ' + this.formSettings.padding.bottom+ 'px; ';
				}
				if( typeof this.formSettings.padding !== 'undefined' && typeof this.formSettings.padding.left!== 'undefined') {
					data_string += ' --data-form-padding-left: ' + this.formSettings.padding.left+ 'px; ';
				}

				if( typeof this.formSettings.mobile_padding !== 'undefined' && typeof this.formSettings.mobile_padding.top!== 'undefined') {
					data_string += ' ; --data-form-mobile_padding-top: ' + this.formSettings.mobile_padding.top+ 'px; ';
				}
				if( typeof this.formSettings.mobile_padding  !== 'undefined' && typeof this.formSettings.mobile_padding.right!== 'undefined') {
					data_string += ' --data-form-mobile_padding-right: ' + this.formSettings.mobile_padding.right+ 'px; ';
				}
				if( typeof this.formSettings.mobile_padding !== 'undefined' && typeof this.formSettings.mobile_padding.bottom!== 'undefined') {
					data_string += ' --data-form-mobile_padding-bottom: ' + this.formSettings.mobile_padding.bottom+ 'px; ';
				}
				if( typeof this.formSettings.mobile_padding !== 'undefined' && typeof this.formSettings.mobile_padding.left!== 'undefined') {
					data_string += ' --data-form-mobile_padding-left: ' + this.formSettings.mobile_padding.left+ 'px; ';
				}

				if( typeof this.formSettings.styling !== 'undefined' && typeof this.formSettings.styling.formbg_color !== 'undefined') {
					data_string += ' --data-form-bgcolor: ' + this.formSettings.styling.formbg_color + '; ';
				}

				return data_string
			},

			get_browser_meta: function(){
					var unknown = '-';

					// screen
					var screenSize = '';
					if (screen.width) {
						var width = (screen.width) ? screen.width : '';
						var height = (screen.height) ? screen.height : '';
						screenSize += '' + width + " x " + height;
					}

					// browser
					var nVer = navigator.appVersion;
					var nAgt = navigator.userAgent;
					var browser = navigator.appName;
					var version = '' + parseFloat(navigator.appVersion);
					var majorVersion = parseInt(navigator.appVersion, 10);
					var nameOffset, verOffset, ix;

					// Opera
					if ((verOffset = nAgt.indexOf('Opera')) != -1) {
						browser = 'Opera';
						version = nAgt.substring(verOffset + 6);
						if ((verOffset = nAgt.indexOf('Version')) != -1) {
							version = nAgt.substring(verOffset + 8);
						}
					}
					// Opera Next
					if ((verOffset = nAgt.indexOf('OPR')) != -1) {
						browser = 'Opera';
						version = nAgt.substring(verOffset + 4);
					}
					// Edge
					else if ((verOffset = nAgt.indexOf('Edge')) != -1) {
						browser = 'Microsoft Edge';
						version = nAgt.substring(verOffset + 5);
					}
					// MSIE
					else if ((verOffset = nAgt.indexOf('MSIE')) != -1) {
						browser = 'Microsoft Internet Explorer';
						version = nAgt.substring(verOffset + 5);
					}
					// Chrome
					else if ((verOffset = nAgt.indexOf('Chrome')) != -1) {
						browser = 'Chrome';
						version = nAgt.substring(verOffset + 7);
					}
					// Safari
					else if ((verOffset = nAgt.indexOf('Safari')) != -1) {
						browser = 'Safari';
						version = nAgt.substring(verOffset + 7);
						if ((verOffset = nAgt.indexOf('Version')) != -1) {
							version = nAgt.substring(verOffset + 8);
						}
					}
					// Firefox
					else if ((verOffset = nAgt.indexOf('Firefox')) != -1) {
						browser = 'Firefox';
						version = nAgt.substring(verOffset + 8);
					}
					// MSIE 11+
					else if (nAgt.indexOf('Trident/') != -1) {
						browser = 'Microsoft Internet Explorer';
						version = nAgt.substring(nAgt.indexOf('rv:') + 3);
					}
					// Other browsers
					else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) < (verOffset = nAgt.lastIndexOf('/'))) {
						browser = nAgt.substring(nameOffset, verOffset);
						version = nAgt.substring(verOffset + 1);
						if (browser.toLowerCase() == browser.toUpperCase()) {
							browser = navigator.appName;
						}
					}
					// trim the version string
					if ((ix = version.indexOf(';')) != -1) version = version.substring(0, ix);
					if ((ix = version.indexOf(' ')) != -1) version = version.substring(0, ix);
					if ((ix = version.indexOf(')')) != -1) version = version.substring(0, ix);

					majorVersion = parseInt('' + version, 10);
					if (isNaN(majorVersion)) {
						version = '' + parseFloat(navigator.appVersion);
						majorVersion = parseInt(navigator.appVersion, 10);
					}

					// mobile version
					var mobile = /Mobile|mini|Fennec|Android|iP(ad|od|hone)/.test(nVer);

					// cookie
					var cookieEnabled = (navigator.cookieEnabled) ? true : false;

					if (typeof navigator.cookieEnabled == 'undefined' && !cookieEnabled) {
						document.cookie = 'testcookie';
						cookieEnabled = (document.cookie.indexOf('testcookie') != -1) ? true : false;
					}

					// system
					var os = unknown;
					var clientStrings = [
						{s:'Windows 10', r:/(Windows 10.0|Windows NT 10.0)/},
						{s:'Windows 8.1', r:/(Windows 8.1|Windows NT 6.3)/},
						{s:'Windows 8', r:/(Windows 8|Windows NT 6.2)/},
						{s:'Windows 7', r:/(Windows 7|Windows NT 6.1)/},
						{s:'Windows Vista', r:/Windows NT 6.0/},
						{s:'Windows Server 2003', r:/Windows NT 5.2/},
						{s:'Windows XP', r:/(Windows NT 5.1|Windows XP)/},
						{s:'Windows 2000', r:/(Windows NT 5.0|Windows 2000)/},
						{s:'Windows ME', r:/(Win 9x 4.90|Windows ME)/},
						{s:'Windows 98', r:/(Windows 98|Win98)/},
						{s:'Windows 95', r:/(Windows 95|Win95|Windows_95)/},
						{s:'Windows NT 4.0', r:/(Windows NT 4.0|WinNT4.0|WinNT|Windows NT)/},
						{s:'Windows CE', r:/Windows CE/},
						{s:'Windows 3.11', r:/Win16/},
						{s:'Android', r:/Android/},
						{s:'Open BSD', r:/OpenBSD/},
						{s:'Sun OS', r:/SunOS/},
						{s:'Linux', r:/(Linux|X11)/},
						{s:'iOS', r:/(iPhone|iPad|iPod)/},
						{s:'Mac OS X', r:/Mac OS X/},
						{s:'Mac OS', r:/(MacPPC|MacIntel|Mac_PowerPC|Macintosh)/},
						{s:'QNX', r:/QNX/},
						{s:'UNIX', r:/UNIX/},
						{s:'BeOS', r:/BeOS/},
						{s:'OS/2', r:/OS\/2/},
						{s:'Search Bot', r:/(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/}
					];
					for (var id in clientStrings) {
						var cs = clientStrings[id];
						if (cs.r.test(nAgt)) {
							os = cs.s;
							break;
						}
					}

					var osVersion = unknown;

					if (/Windows/.test(os)) {
						osVersion = /Windows (.*)/.exec(os)[1];
						os = 'Windows';
					}

					switch (os) {
						case 'Mac OS X':
							osVersion = /Mac OS X (10[\.\_\d]+)/.exec(nAgt)[1];
							break;

						case 'Android':
							osVersion = /Android ([\.\_\d]+)/.exec(nAgt)[1];
							break;

						case 'iOS':
							osVersion = /OS (\d+)_(\d+)_?(\d+)?/.exec(nVer);
							osVersion = osVersion[1] + '.' + osVersion[2] + '.' + (osVersion[3] | 0);
							break;
					}

					// flash (you'll need to include swfobject)
					/* script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js" */
					var flashVersion = 'no check';
					if (typeof swfobject != 'undefined') {
						var fv = swfobject.getFlashPlayerVersion();
						if (fv.major > 0) {
							flashVersion = fv.major + '.' + fv.minor + ' r' + fv.release;
						}
						else  {
							flashVersion = unknown;
						}
					}


					var jscd = {
						screen: screenSize,
						browser: browser,
						browserVersion: version,
						browserMajorVersion: majorVersion,
						mobile: mobile,
						os: os,
						osVersion: osVersion,
						cookies: cookieEnabled,
						flashVersion: flashVersion
					};

	  		return jscd;
			},

	  },

	  created: function () {
	    /*===================================================
	    =         Delete the Remove Field Button            =
	    = 							from the front End  							  =
	    = Remove Button =
	    ===================================================*/
	    let that = this;

	    if(typeof this.schema.groups != 'undefined') {
	      this.schema.groups.forEach(function (group, group_index) {

	        if(typeof group.fields  != 'undefined') {
						let elements_50 = 0, elements_33 = 0, elements_25 = 0;

	          group.fields.forEach(function (field) {

	          	// Add helper classes for the 50%, 33% and 25% width elements
							// these classes will be used to set the paddings properly
							if(that.isProVersion == 1 && that.formSettings.padding.left != 0 && that.formSettings.padding.right != 0) {
								// should work only for PRO and when the left and right padding is NOT 0(zero)
								if(field.styleClasses.includes('width-50')) {
									elements_50++;

									if(1 == (elements_50 % 2) ) {
										field.styleClasses = field.styleClasses + ' width-50-1 ';
									}else if(0 == (elements_50 % 2) ) {
										field.styleClasses = field.styleClasses + ' width-50-2 ';
									}
								}
								if(field.styleClasses.includes('width-33')) {
									elements_33++;
									if(1 == (elements_33 % 3) ) {
										field.styleClasses = field.styleClasses + ' width-33-1 ';
									}else if(2 == (elements_33 % 3) ) {
										field.styleClasses = field.styleClasses + ' width-33-2 ';
									}else if(0 == (elements_33 % 3) ) {
										field.styleClasses = field.styleClasses + ' width-33-3 ';
									}
								}
								if(field.styleClasses.includes('width-25')) {
									elements_25++;
									if(1 == (elements_25 % 4) ) {
										field.styleClasses = field.styleClasses + ' width-25-1 ';
									}else if(2 == (elements_25 % 4) ) {
										field.styleClasses = field.styleClasses + ' width-25-2 ';
									}else if(3 == (elements_25 % 4) ) {
										field.styleClasses = field.styleClasses + ' width-25-3 ';
									}else if(0 == (elements_25 % 4) ) {
										field.styleClasses = field.styleClasses + ' width-25-4 ';
									}
								}
							}

							// for the section break add the data attributes for the bottom and top margin
							if(field.type == 'label' && field.styleClasses.includes('flo-section-break')) {
								let margin_top, margin_bottom
								if(typeof field.margin_top != 'undefined') {
									margin_top = field.margin_top
								}
								if(typeof field.margin_bottom != 'undefined') {
									margin_bottom = field.margin_bottom
								}
								if(typeof field.help != 'undefined') {
									field.help = "<hr class='flo-section-break' style='--data-margin-top: "+margin_top+"px; --data-margin-bottom: "+margin_bottom+"px; '>"
								}
							}

							// for the Message Field:
	            // initially we were storing the value for this field in the 'label' property
	            // but that does not allow to use HTML markup.
	            // therefore we decided to switch to 'help' property
							if(field.type == 'label' && !field.styleClasses.includes('flo-section-break')) {
								if(field.label && field.label != ''){
									// safely transfer the content from the 'label' to 'help' property and empty the 'label' property
									field.help = field.label
									field.label = ''
								}

							}

	            if(field.type != 'submit') {

	            	field.buttons = ''; // set the buttons to empty string - this way we remove it

	            	// conditipnal login init
	            	that.condLogicClass(field);

								if(field.type == 'upload')  {


									var this_field = field;

									field.onChanged = function(model, newVal, oldVal, field) {
										that.checkConditionalLogicClass()

										var fi = document.getElementById( 'upload-'+newVal.field_id); // GET THE FILE INPUT.

										// VALIDATE OR CHECK IF ANY FILE IS SELECTED.
										if (fi.files.length > 0 ) {


											// RUN A LOOP TO CHECK EACH SELECTED FILE.
											for (var i = 0; i <= fi.files.length - 1; i++) {

												var fsize = fi.files.item(i).size;      // THE SIZE OF THE FILE.

													this_field.validator = function(value) {

														if(fsize > formData.max_upload_size) {
															return ["Uploaded file has exceeded the allowed max upload size."];
														}else{
															return [];
														}
													}

											}
										}else{

											if(this_field.required) {
												this_field.validator = 'required';
											}else{
												this_field.validator = function(value) {
													return [];
												}
											}

										}

									}
								}else{
									field.onChanged = function(model, newVal, oldVal, field) {
										that.checkConditionalLogicClass()
									}
								}


						    // check the required input attribute
						    let field_attr = field.attributes.input


						    if( typeof field_attr.required != 'undefined') {
						    	if(field_attr.required){ // the the required  attribute => set the 'required' property for the current field
						    		field.required = field_attr.required

						    		if(typeof field.validator != 'undefined') {
						    			if( !field.validator.includes('required') ) {
							    			field.validator.push("required")
							    		}
						    		}else{
						    			field.validator = ["required"]
						    		}


										// if the label is empty we delete it
										// this is done especially to avoid shoing the '*' for required fields without the labels
										// exceptions are fields with field.help property -> Single checkboxes
										if(typeof field.label !== 'undefined' && field.label == '' &&  typeof field.help == 'undefined') {
											// console.log('field.label: ', field.label)
											// console.log('field.help: ', field.help)
											delete field.label
										}

						    		if(field.type == 'input' && field.inputType == 'email') {

						    			// for some reason the required message shows up twice after the 'email' validator is pushed.
						    			// therefore we have to remove it
						    			var required_validator_index = field.validator.indexOf('required'); // find the index
						    			if (required_validator_index > -1) {
    										field.validator.splice(required_validator_index, 1); // remove the required_validator
    									}

						    			field.validator.push('email');
						    		}


						    		// for the checkbox we need a special case - to verify the value as well
						    		if(field.type == 'checkbox') {

						    			field.validator = function(value) {
						    				//console.log('value: ', value);

												if (value !== true){
													return ["This field is required!"];
													//return [ wp.i18n.__( "This field is required!", 'flo-forms' ) ];
													//return [this.__("List Items", "flo-forms")];
												} else {
													return []
												}

											}
						    		}

										if(field.type == 'checklist') {

											field.validator = function(value) {

												if ( typeof value == 'undefined' || !value.length ){
													return ["This field is required!"];
												} else {
													return []
												}

											}

										}
						    	}
						    }

						    if(field.inputType == 'email') {
						    	if(typeof field.validator == 'undefined') {
						    		field.validator = ["email"]
						    	}
						    }

	            }else{

	            	field.onSubmit = function() {

						  		if( that.isValid ) {
     								var $this =  jQuery(that.$el).parents('.flo-form');

						     		$this.find('.flo-response').html('');
						     		$this.find('.flo-response-error').html('');
						     		jQuery('.g-recaptcha').removeClass('invalid');
										$this.find('.flo.overlay-loader').show();
										jQuery('.app-flo-forms .flo-response').html('');

										var browser_meta = '';
										// temporarily disable this
										// browser_meta = JSON.stringify(that.get_browser_meta());
										// browser_meta = '&browser_meta='+browser_meta;

										jQuery(".field-submit input").prop('disabled', true);

										// prepare ajax data:
										var data = new FormData( $this[0] );


										data.append("action", "flo_form_submit");

										jQuery.ajax({
											url: formData.ajaxurl,
											//data: '&action=flo_form_submit&formNonce='+formData.formNonce+'&'+data+browser_meta,
											data: data,
											type: 'POST',
											dataType: "json",
											//////////////////////
											processData: false, // Don't process the files
											contentType: false, // Set content type to false as jQuery will tell the server its a query string request
											//////////////////////
											cache: false,
											success: function (json) {
												$this.find('.flo.overlay-loader').hide();
												//console.log(json);

												if(json.error){
													//$this.find('.flo-response').html(json.error);
													$this.find('.flo-response-error').html(json.error);

													if( json.error.includes('Please complete captcha verification') ) {
														jQuery('.g-recaptcha').addClass('invalid');
													}


												}else if(json.confirmation_opt && json.confirmation_opt == 'text_confirmation'){

													if(json.warning_msg) {
														$this.find('.flo-response-error').html( json.warning_msg );
													}

													$this.find('.flo-response').html(json.success_msg);
													that.model = {} // reset the form

													// reset the file fileds
													$this.find('.field-upload input[type=file]').val('');

													if(jQuery('.g-recaptcha').length){
														grecaptcha.reset() // reset captcha
													}

													$this.find('.flo-response').show();
													jQuery('.app-flo-forms .panel-body').addClass('response-mode');
													// jQuery('.app-flo-forms .flo-response').show();

													document.querySelector('.app-flo-forms_'+that.$attrs.form_id+' .flo-response').scrollIntoView({
													  behavior: 'smooth'
													});

													// hide the message in 5 sec
													setTimeout(function() {
														jQuery('.app-flo-forms .panel-body').removeClass('response-mode');
														jQuery('.app-flo-forms .flo-response').hide();

													}, 7000);

												}else if(json.confirmation_opt && json.confirmation_opt == 'page_confirmation'){
													window.location = json.success_page;
												}

												jQuery(".field-submit input").prop('disabled', false);
											}

										});

     							}else{
     								//console.log(that.validationErrors)

     								// scroll to the element that did not pass the validation

     								if(that.validationErrors.length) {
     									document.querySelector('.fid_'+that.validationErrors[0].field.field_id).scrollIntoView({
											  behavior: 'smooth'
											});
     								}

     							}

					      }
	            }

	          });
	        }
	      });

	    }
	    /*=====  End of fix the Remove Field Button  ======*/
	  }

}
</script>
