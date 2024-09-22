// <script>


import Vue from 'vue';
window.Vue = Vue;

//import VueFormGenerator from "vue-form-generator";
import VueFormGenerator from '../vue-components/vue-form-generator';
import VueColor from "vue-color";
import vueSlider from 'vue-slider-component';
import AdvancedSelect from '../vue-components/FloDropdown.vue';
import FloEditor from '../vue-components/FloEditor.vue';

import _ from "lodash";

import {flo} from '../js/flo-forms-admin.js'; // import the jQuery functions to reuse when necessary


Vue.use(VueFormGenerator);



export default {
  components: {
      "vue-form-generator": VueFormGenerator.component,
      "vue-color": VueColor.Chrome,
      "vue-slider": vueSlider,
      "AdvancedSelect" : AdvancedSelect,
      "FloEditor" : FloEditor,
      //"trumbowyg":Trumbowyg
    },

  computed: {
    // a computed getter
    allFields: function () {
      let all_fields = {}


      this.schema.groups.forEach((group,group_index) => {

        group.fields.forEach((current_field ) => { //console.log(current_field);
          let field_id = current_field.field_id,
            field_label = current_field.label != '' ? current_field.label : current_field.placeholder


            if(current_field.type != 'submit' && current_field.type != 'label' ) {
              // all_fields.push({
              //   'field_id':field_id,
              //   'label': field_label,
              //   'type': current_field.type
              // })

              all_fields[field_id] = {
                'field_id':field_id,
                'label': field_label,
                'type': current_field.type,
                'obj': current_field
              }
            }

        })
      })

      return all_fields
    },

    firstField:{
      // getter
      get: function () {
        if(this.schema.groups[0].fields.length && typeof this.schema.groups[0].fields[0].field_id != undefined) {
          return this.schema.groups[0].fields[0].field_id
        } else {
          return ''
        }
      },
      // setter:
      set: function (newValue) {
        return newValue
      }
    },

    groupedCustomFonts: function() {

      let grouped_custom_fonts = _.groupBy(this.formCustomFonts, function( fontEl ){ return fontEl.name })

      //we need to inject the font family
      Object.keys(grouped_custom_fonts).forEach((c_font,font_name) => {

        let customFonFaceStyle = this.floGetCustomFontface(c_font, grouped_custom_fonts[c_font])

        // append the customFonFaceStyle in the dom
        jQuery('.flo-forms-app').after(customFonFaceStyle)

      })

      return grouped_custom_fonts
    },

  },

  mounted() {
    this.setColor(this.color || '#000000');


    let groups_size = this.schema.groups.length
    // initialize the Submit button text color and bgcolor
    this.btnTextColorValue = this.schema.groups[groups_size-1].fields[0].attributes.input.text_color
    this.btnTextColorValueHover = this.schema.groups[groups_size-1].fields[0].attributes.input.text_color_hover
    this.btnBgColorValue = this.schema.groups[groups_size-1].fields[0].attributes.input.bg_color
    this.btnBgColorValueHover = this.schema.groups[groups_size-1].fields[0].attributes.input.bg_color_hover
    this.btnBorderColorValue = this.schema.groups[groups_size-1].fields[0].attributes.input.border_color
    this.btnBorderColorValueHover = this.schema.groups[groups_size-1].fields[0].attributes.input.border_color_hover
    this.btnPosition = this.schema.groups[groups_size-1].fields[0].attributes.input.button_position

  },

  methods: {
      log: function (val) {
        console.log(val)
      },

      // show the Pro version Preview tabs
      show_tabs: function(tab) {
        this.newFormState = false
        if(tab == 'predefined') {
          setTimeout(function(){
            jQuery('li.pick-template a').click();
          },50)

        }
      },

      /*===========================================
      =          BOF  Submit button color            =
      ===========================================*/


      updateElemColorPicker: function(color) {

        let colorObj = (typeof this.$refs.flo_vue_color[0] !== 'undefined') ? this.$refs.flo_vue_color[0].$attrs['data-color_obj'] : this.$refs.flo_vue_color.$attrs['data-color_obj'],
         // elemColorValue = this.$refs.flo_vue_color.$attrs['data-color_val']
          elemColorValue = (typeof this.$refs.flo_vue_color[0] !== 'undefined') ?  this.$refs.flo_vue_color[0].$attrs['data-color_val'] : this.$refs.flo_vue_color.$attrs['data-color_val']

        this[colorObj] = color;
        if(color.rgba.a == 1) {
          this[elemColorValue] = color.hex;
        } else {
          this[elemColorValue] = 'rgba(' + color.rgba.r + ', ' + color.rgba.g + ', ' + color.rgba.b + ', ' + color.rgba.a + ')';
        }

        // special cases for the submit button colors
        if('btnBgColorValue' == elemColorValue) {
          this.updateBtnAttributes('bg_color', this.btnBgColorValue)
        }

        if('btnBgColorValueHover' == elemColorValue) {
          this.updateBtnAttributes('bg_color_hover', this.btnBgColorValueHover)
        }

        if('btnTextColorValue' == elemColorValue) {
          this.updateBtnAttributes('text_color', this.btnTextColorValue)
        }

        if('btnTextColorValueHover' == elemColorValue) {
          this.updateBtnAttributes('text_color_hover', this.btnTextColorValueHover)
        }

        if('btnBorderColorValue' == elemColorValue) {
          this.updateBtnAttributes('border_color', this.btnBorderColorValue)
        }
        if('btnBorderColorValueHover' == elemColorValue) {
          this.updateBtnAttributes('border_color_hover', this.btnBorderColorValueHover)
        }



      },


      setColor(color) {
        this.updateColors(color);
        this.colorValue = color;
      },

      updateColors(color) {
        if(color.slice(0, 1) == '#') {
          this.colors = {
            hex: color
          };
        }
        else if(color.slice(0, 4) == 'rgba') {
          var rgba = color.replace(/^rgba?\(|\s+|\)$/g,'').split(','),
            hex = '#' + ((1 << 24) + (parseInt(rgba[0]) << 16) + (parseInt(rgba[1]) << 8) + parseInt(rgba[2])).toString(16).slice(1);
          this.colors = {
            hex: hex,
            a: rgba[3],
          }
        }
      },
      showPicker(color_type) {
        document.addEventListener('click', this.documentClick);
        this.displayPicker[color_type] = true;
      },
      hidePicker(color_type) {
        //document.removeEventListener('click', this.documentClick);
        this.displayPicker[color_type] = false;
      },
      togglePicker(color_type) {
        this.displayPicker[color_type] ? this.hidePicker(color_type) : this.showPicker(color_type);
      },
      documentClick(e) {
        var target = e.target;


        // add here the new Color Pickers Values
        var c_pickers = {'text_colorpicker': 'text','text_colorpicker_hover': 'text_hover','bg_colorpicker':'bg','bg_colorpicker_hover':'bg_hover','btn_border_colorpicker': 'btn_border_color','btn_border_colorpicker_hover': 'btn_border_color_hover',
          'label_colorpicker':'label_color','input_colorpicker':'input_color', 'border_colorpicker':'border_color', 'inputbg_colorpicker':'inputbg_color', 'placeholder_colorpicker':'placeholder_color',
          'hint_colorpicker':'hint_color','formbg_colorpicker':'formbg_color' }


        var this_refs = this.$refs;

        // hide the color picker when clicking outside its parent container
        for (const pick_key in c_pickers) {
          let picker_value = c_pickers[pick_key];
          console.log(pick_key, picker_value);

          var ref_el = this_refs[pick_key]

          if(typeof ref_el !== 'undefined') {
            if( (Array.isArray(ref_el) && ref_el[0] !== target && !ref_el[0].contains(target))
              || (!Array.isArray(ref_el) && !ref_el.contains(target) ) ) {
              this.hidePicker(picker_value)
            }
          }

        }

      },
      updateBtnAttributes: function(attr_name, attr_val) {
        let groups_size = this.schema.groups.length
        this.schema.groups[groups_size-1].fields[0].attributes.input[attr_name] = attr_val
      },

      /*=====  End of Submit button color  ======*/

      addRuleGroup: function(cond_logigic_groups) {
        let rules_group = _.cloneDeep(cond_logigic_groups[0]) // clone the first group
        rules_group.rules.splice( 1, rules_group.rules.length-1 ) // from the first group remove all the rules but the 1st one
        cond_logigic_groups.push(rules_group) // append the new group
      },

      removeCondLogicRule: function(value, group_rule_id, rule_id) {
        let all_cl_groups = value.attributes.input.conditional_logic_groups
        let current_cl_group = all_cl_groups[group_rule_id]
        let current_cl_rule = current_cl_group.rules[rule_id]

        current_cl_group.rules.splice(rule_id,1) // remove the specified rule
        if(current_cl_group.rules.length == 0) {
          all_cl_groups.splice(group_rule_id,1) // If all the rules from a group were removed, we delete the grpoup itself
        }
      },

      add_field: function (field_type) {
        let attributes = {
            input: {
              class: "form-control",
              width: '100%',
              enable_conditional_logic: false,
              required: false,
              conditional_logic_groups:
              [
                { 'rules' :

                    [
                      {
                        rule_select: this.firstField, // dropdown with all the available fields // NOTE! when the first field is added, there are no
                        // existing fields, therefore for it there will be no option preselected
                        rule_property: 'is', // is, is not, greater than, less than, contains, starts with, ends with,
                        rule_input: '', // input for the user to enter the value
                        next_rule_operator: 'and' // the default condition for the next rule within this group
                      }
                    ]
                  ,
                  'next_group_operator' : 'and'

                }
              ]


            }
          }
        let field_id = Math.floor(Math.random() * Math.floor(9999999));
        let that = this;

        this.firstField = field_id

        let field_buttons = [
            {
              classes: "remove-field dashicons dashicons-trash",
              label: "",
              title: "Remove this field",
              onclick: function(model, field) {
                event.preventDefault();
                that.removeField(model, field)
              }
            },
            {
              classes: "icon-Move-Field sort-handle",
              label: "",
              title: "Drag to reorder",
              onclick: function(model, field) {
                event.preventDefault();
              }
            }
          ]

          // there are properties that are the same for all input types
        let repeating_properties = {
          field_id: field_id,
          model: 'field_'+field_id,
          placeholder: "",
          styleClasses: "width-100 fid_"+field_id, // this adds the necessary classes to the field wrapper
          required: false,
          buttons: field_buttons,
          attributes: attributes,
          hint: '',
        }


        switch(field_type) {
            case 'email':
                var field_specific_properties = {
                  type: "input",
                  inputType: "email",
                  label: "E-mail",
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)
              break;
            case 'number':
                var field_specific_properties = {
                  type: "input",
                  inputType: "number",
                  label: "Number",
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)
              break;
            case 'text':
                var field_specific_properties = {
                  type: "input",
                  inputType: "text",
                  label: "Single line text",
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)
              break;
            case 'textArea':
                var field_specific_properties = {
                  type: "textArea",
                  label: "Text Area",
                  rows: 6,
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)
              break
            case 'checkbox':
                var field_specific_properties = {
                  type: "checkbox",
                  label: "",
                  help: "Checkbox description goes here",
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)

                // setTimeout(function() {
                //   $(function() {

                //     $('.field-settings--checkbox-description').froalaEditor({
                //       charCounterCount: false,
                //       editorClass: 'froala-text-confirmation-value',
                //       toolbarButtons: ['bold', 'italic', 'underline', 'color','insertLink']
                //     });
                //   });
                // }, 10);

              break;
            case 'switch':
                var field_specific_properties = {
                  type: "switch",
                  label: "Enable",
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)
              break;
            case 'checklist':
                var field_specific_properties = {
                  type: "checklist",
                  label: "Checklist",
                  listBox: true,
                  values: [
                    "Value 1",
                    "Value 2",
                    "Value 3",
                  ]
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)

              break;
            case 'radios':
                var field_specific_properties = {
                  type: "radios",
                  label: "Radio Button",
                  values: [
                    "Value 1",
                    "Value 2",
                    "Value 3",
                  ]
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)
              break;
            case 'select':
                var field_specific_properties = {
                  type: "select",
                  label: "Dropdown",
                  selectOptions: {
                    noneSelectedText: "Select an option",
                    //hideNoneSelectedText: true,
                  },

                  values: [
                    "Value 1",
                    "Value 2",
                    "Value 3",
                  ]
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)
              break;

            case 'hidden':
                  var field_specific_properties = {
                    type: "input",
                    inputType: "text",
                    label: "Hidden",
                  }

                  repeating_properties.styleClasses += ' hidden ' // append hidden class

                  var new_filed = Object.assign(repeating_properties, field_specific_properties)
                  this.schema.groups[0].fields.push(new_filed)
                break;
          case 'upload':
              var field_specific_properties = {
                type: "upload",
                //inputType: "text",
                label: "Upload",
                inputName: 'upload_'+field_id

              }

              var new_filed = Object.assign(repeating_properties, field_specific_properties)
              this.schema.groups[0].fields.push(new_filed)
              break;
            case 'message':
                  var field_specific_properties = {
                    type: "label",
                    label: '',
                    help: "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                    rows: 6,
                  }
                  var new_filed = Object.assign(repeating_properties, field_specific_properties)
                  this.schema.groups[0].fields.push(new_filed)
                break
            case 'section_break':
                  var margin_properties = { // this will store the top and bottom margin properties
                    margin_top: 8,
                    margin_bottom: 20,
                  }

                  var field_specific_properties = {
                    type: "label",
                    label: "",
                    help: "<hr class='flo-section-break' style='--data-margin-top: "+margin_properties.margin_top+"px; --data-margin-bottom: "+margin_properties.margin_bottom+"px '>",
                  }

                  repeating_properties.styleClasses += ' flo-section-break ' // append hidden class

                  var new_filed = Object.assign(repeating_properties, field_specific_properties, margin_properties)
                  this.schema.groups[0].fields.push(new_filed)
                break

            case 'pikaday':
                var field_specific_properties = {
                  type: "pikaday",
                  label: "Date",
                  validator: VueFormGenerator.validators.date,
                  pikadayOptions: {
                    position: "top left",
                    format: "DD-MMM-YYYY",
                    i18n : vue_app_data.flo_date_i18n,
                    firstDay: 0
                  },
                  format: "DD-MMM-YYYY",
                  firstDay: 0
                }
                var new_filed = Object.assign(repeating_properties, field_specific_properties)
                this.schema.groups[0].fields.push(new_filed)
              break;

            // default:
            //     code block


        }

        // make sure the sortable is initiated for the new forms
        setTimeout(function() { // init sortable for the new column
          initSortable()
        }, 100);

      },

      removeField: function(model, field, group_index = 0) {

        const field_index = this.schema.groups[group_index].fields.indexOf(field);

        this.schema.groups[group_index].fields.splice(field_index, 1) // remove the field from the schema

        // in case the element we are deleting is available in the mode, remove it from the model as well
        let model_key_to_remove = 'field_'+field.field_id
        delete this.model[model_key_to_remove]

        // if the last field is removed and the 'Field Settings' tab is empty, we switch to the first tab.
        setTimeout(function() {
          if( !jQuery('.fields-controls .field-settings.visible').length) {
            jQuery('.add-field a').click()
            console.log('0000');
          }
        }, 100);


      },

      currentFieldWidth: function(field_id,group_id) {
        //styleClasses
        return this.schema.groups[group_id].fields[field_id].attributes.input.width;
      },

      changeCurrentFieldRequiredClass: function(field_id,group_id, event) {
        let current_class = this.schema.groups[group_id].fields[field_id].styleClasses // store the current class in a var
        let new_class

        if(event.target.checked) {
          new_class = current_class + ' required';
        }else{
          new_class = current_class.replace('required','');
        }

        this.schema.groups[group_id].fields[field_id].styleClasses = new_class // update the schema
      },

      /**
       *
       * when the Field width dropdown is changed, we read the current field wrappers classes
       * and replace the width class with the selected value
       */
      change_width_class: function (field_id,group_id) {
        // store the current class in a var
        let current_class = this.schema.groups[group_id].fields[field_id].styleClasses;

        let regex = /(width-)\d+/gi; // reg ex to match all the classes starting with 'width-'

        // replace the matched class with the selected value
        let selected_width = this.schema.groups[group_id].fields[field_id].attributes.input.width
          selected_width = selected_width.replace('%','')
        let new_class = current_class.replace(regex, "width-" + selected_width);

        this.schema.groups[group_id].fields[field_id].styleClasses = new_class // update the schema
      },

      // update the visuals when the section brake margin is changed
      set_section_break_margin: function (field_id,group_id) {
        let margin_top = this.schema.groups[group_id].fields[field_id].margin_top,
            margin_bottom = this.schema.groups[group_id].fields[field_id].margin_bottom;

        this.schema.groups[group_id].fields[field_id].help = "<hr class='flo-section-break' style='--data-margin-top: "+margin_top+"px; --data-margin-bottom: "+margin_bottom+"px; '>"
      },

      /**
       *
       * when the Date Field date format is changed, we update the format property
       */
      change_date_format: function (event, field_id,group_id ) {
        this.schema.groups[group_id].fields[field_id].format = event.target.value
      },

      /**
       *
       * when the Date Field First week day is changed, we update the related property
       */
      change_first_week_day: function (event, field_id,group_id ) {
        this.schema.groups[group_id].fields[field_id].pikadayOptions.firstDay = parseInt(event.target.value)
      },

      change_btn_position: function(event) {
        this.btnPosition = event.target.value
      },

      changeFormColumns: function(event) {
        let selected_nr_columns = event.target.value;

        if(selected_nr_columns == 2 && this.schema.groups.length == 2) {
          //this.schema.groups.push({"styleClasses": "form-group-two","fields": []} )
          this.schema.groups.splice(1, 0, {"styleClasses": "form-group-section form-group-two","fields": []}); // insert as the 2nd array item

          jQuery('.form-preview').addClass('two-columns')

          setTimeout(function() { // init sortable for the new column
            initSortable()
          }, 10);

        }

        if(selected_nr_columns == 1 && this.schema.groups.length == 3) {
          let tempSchemaFields = [...this.schema.groups[0].fields, ...this.schema.groups[1].fields]

          this.schema.groups[0].fields = tempSchemaFields

          this.schema.groups.splice(1, 1); // remove item with index 1 from the groups array
        }

      },

      maybeSortSchema: function({ type, target }) {

        let sortedFieldKeys = target.value.split(','), // transform the Input value into an array
            tempSchema = [[],[]];

        let current_fields; // store here all the fields together, no mater which column they belong

        if(this.schema.groups.length == 3) { // if we have 3 field groups (2 columns)
          current_fields = [...this.schema.groups[0].fields, ...this.schema.groups[1].fields]
        }else{
          // in case we have 1 column, we work with the fields from the first columns only
          current_fields = this.schema.groups[0].fields;
        }

        //window.flo_form_fields is set from jQuery when the items are sorted using Sortable JS
        // sf_val -> schema field value
        if(typeof window.flo_form_fields != 'undefined') {
          window.flo_form_fields.forEach((sf_val,sf_index) => {

            sf_val.forEach((column_field_id,column_field_index) => {
              // iterate through schema fields
              current_fields.forEach(function(schema_field_val,schema_field_index) {

                  if(column_field_id == schema_field_val.field_id) {
                    tempSchema[sf_index].push(schema_field_val);

                    //var current_classes_str = $(this).attr("class");

                  }
                }
              )

            })

          })

          // Try to delay Updating the schema to avoid data messing up
          setTimeout(() => {
            //console.log('tempSchema[0]: ', tempSchema[0]);
            this.schema.groups[0].fields = tempSchema[0]
          }, 50);

          if(this.schema.groups.length == 3) { // when we have 3 columns
            setTimeout(() => {
              //console.log('tempSchema[1]: ', tempSchema[1]);
              this.schema.groups[1].fields = tempSchema[1]

              console.log('groups: ')
              console.log(this.schema.groups)

            }, 60);
          }

        }

      },

      flo_apply_template: function(template_groups, show_active_tab = false, confirm_alert = false) {

        if(!confirm_alert) {
          this.schema.groups = template_groups
        }else{
          let conf = confirm("Applying a Template will Replace all the progress you have made so far. Are You Sure you want to apply the selected template?")

          if(conf) {
            this.schema.groups = template_groups

            if(show_active_tab) {
              jQuery('li.form-design a').click()
              console.log('form-design clicked')
            }

            setTimeout(function() { // init sortable for the new column
              initSortable()
            }, 10);

          }
        }



      },

      getPluginVersionClass: function() {
        if(this.isProVersion == 1)
            return 'ff-pro'
          else
              return 'ff-free'
      },

      getStylingClass: function(formSettings) {
        let custom_classes = ''

        if(formSettings.styling.use_custom_colors == 1) {
          custom_classes += ' flo-form--custom-colors '
        }

        if(formSettings.styling.use_custom_fonts == 1) {
          custom_classes += ' flo-form--custom-fonts '
        }

        return custom_classes
      },

      getFontStyles: function(formSettings, style_element) {

        if(formSettings.styling[style_element] !== undefined && typeof formSettings.styling[style_element].font != 'undefined'){
          return formSettings.styling[style_element].font
        }else{
          return  'initial'
        }
      },

      floIsValidJSONString: function(str) {
        try {
          JSON.parse(str);
        } catch (e) {
          return false;
        }
        return true;
      },

      floGetCustomFontface: function (name, fonts ){
        //console.log(name, fonts, ' hopa');
        var fontface = '';

        fontface += '@font-face {';
        fontface += 'font-family: "'+ name + '"; ';

        var lines = [];

        var newFont = _.map(fonts, function(font) {

          var line = '';

          switch(font.format) {
            case 'otf':
              line = "url('" + font.url + "') format('opentype')";
              break;
            case 'svg':
              line = "url('" + font.url + "') format('svg')";
              break;
            case 'ttf':
              line = "url('" + font.url + "') format('truetype')";
              break;
            case 'woff':
              line = "url('" + font.url + "') format('woff')";
              break;
            case 'woff2':
              line = "url('" + font.url + "') format('woff2')";
              break;
            default:
            //
          }

          lines.push(line);

          return font;

        });

        // Note! eot fonts are problematic. Review this or not use at all
        var eot = _.find(fonts, function(o) { return o.format == 'eot'; });

        if ( typeof eot != 'undefined' ) {
          fontface += "src: url('" + eot.url + "');";
        }

        fontface += 'src:';

        fontface +=lines.join();
        fontface +=';';

        fontface += '}';

        return '<style>'+fontface+'</style>';

      },

      // maybe show Custom Fonts Options
      showCustomFontsOptions: function () {
        // show if the 'use_custom_fonts' checkbox is enabled
        if(this.formSettings.styling.use_custom_fonts == 1 || this.formSettings.styling.use_custom_fonts == true){

          return true
        }else{
          return false
        }
      },

      openCloseColapsableOption: function(wrapper_id) {

        let wrapper_class = 'colapsable-option_'+wrapper_id;
        jQuery('.'+wrapper_class).toggleClass('opened')

      },

      openCloseFontOption: function(wrapper_id) {
        let wrapper_class = 'font-option_'+wrapper_id;

        jQuery('.'+wrapper_class).toggleClass('opened')

        //because the vue-slider is located in a container that is initially hidden,
        // we need to refresh it when the container shows up

        this.fonOptionsData.forEach((fontOption,fontOption_index) => {
          console.log(fontOption);
          this.$nextTick(() => this.$refs['font_size_slider--'+fontOption.option_name][0].refresh());
          this.$nextTick(() => this.$refs['line_height_slider--'+fontOption.option_name][0].refresh());
          this.$nextTick(() => this.$refs['letter_spacing_slider--'+fontOption.option_name][0].refresh());
        })
      },

      renderStylingData: function(formSettings) {

        let data_string = ''

        // font-size, letter-spacing and line-height
        this.fonOptionsData.forEach((fontOption,fontOption_index) => {
          if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling[fontOption.option_name] !== 'undefined' && typeof formSettings.styling[fontOption.option_name].font_size !== 'undefined') {
            data_string += ' ; --data-'+fontOption.option_name+'-font-size: ' + formSettings.styling[fontOption.option_name].font_size/16 + 'rem ';
          }
          if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling[fontOption.option_name] !== 'undefined' && typeof formSettings.styling[fontOption.option_name].letter_spacing !== 'undefined') {
            data_string += ' ; --data-'+fontOption.option_name+'-letter-spacing: ' + formSettings.styling[fontOption.option_name].letter_spacing + 'px ';
          }
          if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling[fontOption.option_name] !== 'undefined' && typeof formSettings.styling[fontOption.option_name].line_height !== 'undefined') {
            data_string += ' ; --data-'+fontOption.option_name+'-line-height: ' + formSettings.styling[fontOption.option_name].line_height + 'em ';
          }

          if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling[fontOption.option_name] !== 'undefined' && typeof formSettings.styling[fontOption.option_name].italic !== 'undefined' &&  formSettings.styling[fontOption.option_name].italic == 1) {
            data_string += ' ; --data-'+fontOption.option_name+'-italic: italic ';
          }
          if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling[fontOption.option_name] !== 'undefined' && typeof formSettings.styling[fontOption.option_name].bold !== 'undefined' &&  formSettings.styling[fontOption.option_name].bold == 1) {
            data_string += ' ; --data-'+fontOption.option_name+'-bold: bold ';
          }
          if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling[fontOption.option_name] !== 'undefined' && typeof formSettings.styling[fontOption.option_name].underline !== 'undefined' &&  formSettings.styling[fontOption.option_name].underline == 1) {
            data_string += ' ; --data-'+fontOption.option_name+'-underline: underline ';
          }

        })


        if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling.button !== 'undefined' && typeof formSettings.styling.button.padding_y !== 'undefined') {
          data_string += ' ; --data-button-padding-y: ' + formSettings.styling.button.padding_y + 'px ';
        }

        if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling.button !== 'undefined' && typeof formSettings.styling.button.padding_x !== 'undefined') {
          data_string += ' ; --data-button-padding-x: ' + formSettings.styling.button.padding_x + 'px ';
        }

        if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling.button !== 'undefined' && typeof formSettings.styling.button.button_width!== 'undefined') {
          data_string += ' ; --data-button-width: ' + formSettings.styling.button.button_width + 'px ';
        }else{
          data_string += ' ; --data-button-width: 150px ';
        }

        if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling.button !== 'undefined' && typeof formSettings.styling.button.border_radius !== 'undefined') {
          data_string += ' ; --data-button-border-radius: ' + formSettings.styling.button.border_radius + 'px ';
        }

        if(typeof formSettings.styling !== 'undefined' && typeof formSettings.styling.button !== 'undefined' && typeof formSettings.styling.button.border_width !== 'undefined') {
          data_string += ' ; --data-button-border-width: ' + formSettings.styling.button.border_width + 'px ';
        }

        if( typeof formSettings.padding !== 'undefined' && typeof formSettings.padding.top !== 'undefined' ) {
          data_string += ' ; --data-form-padding-top: ' + formSettings.padding.top + 'px ';
        }
        if( typeof formSettings.padding !== 'undefined' && typeof formSettings.padding.right !== 'undefined' ) {
          data_string += ' ; --data-form-padding-right: ' + formSettings.padding.right + 'px ';
        }
        if( typeof formSettings.padding !== 'undefined' && typeof formSettings.padding.bottom !== 'undefined' ) {
          data_string += ' ; --data-form-padding-bottom: ' + formSettings.padding.bottom + 'px ';
        }
        if( typeof formSettings.padding !== 'undefined' && typeof formSettings.padding.left !== 'undefined' ) {
          data_string += ' ; --data-form-padding-left: ' + formSettings.padding.left + 'px ';
        }

        return data_string
      },

      removeUserSavedTemplate: function(template_index) {


        if(typeof this.userSavedTemplates == 'object') {

          // there are some issues with the reactivity of this object, and in order
          // to reflect the changes in the dom we need to delete an object property the following way:
          this.$delete(this.userSavedTemplates, template_index) //

        }

        if(typeof this.userSavedTemplates == 'array') {
          this.userSavedTemplates.splice(template_index,1)
        }


        jQuery.ajax({
          url: ajaxurl,
          data: '&action=flo_form_delete_template&template_index='+template_index,
          type: 'POST',
          dataType: "json",
          cache: false,
          success: function (json) {
            console.log(json);
          }

        });
      },

      getFontOptions: function() {
        let f_options = []

        f_options.push({ name: 'initial'})
        this.formSystemFonts.forEach((font, font_index) => {
          //console.log( 'aici: ', font);
          f_options.push({id:font_index, name: font})
        })

        if(this.formGoogleFonts.length && typeof this.formGoogleFonts == 'object') {
          this.formGoogleFonts.forEach((font) => {
            f_options.push({id:font.id, name: font.activeFont})
          })
        }

        if(this.formCustomFonts.length && typeof this.formCustomFonts == 'object') {

          for (var prop in this.groupedCustomFonts) {

            f_options.push({ name: prop})
          }

        }

        return f_options; // font options
      }


  },

  data: function() {

    let field_id = Math.floor(Math.random() * Math.floor(9999999));

    let floFormSchema,
        floFormModel,
        submit_btn = {
                      "styleClasses": "form-group-submit",
                      "fields": [
                        {
                          "field_id": field_id,
                          "model": "field_"+field_id,
                          "placeholder": "",
                          "styleClasses": "width-100 fid_"+field_id+" submit-btn",
                          "required": false,
                          "attributes": {
                            "input": {
                              "class": "form-control",
                              "width": "100%",
                              "text_color": "#ffffff",
                              "text_color_hover": "#ffffff",
                              "bg_color": "#000000",
                              "bg_color_hover": "#000000",
                              "border_color": "#000000",
                              "border_color_hover": "#000000",
                              "button_position": "auto",
                            }
                          },
                          "type": "submit",
                          "buttonText": "Send message",
                          "validateBeforeSubmit": true,
                        }
                      ]
                    }

    //flo_form_settings

    //flo_form_schema is localized from PHP
    if(typeof flo_form_schema === 'undefined') {
      floFormSchema = {"groups":[{"styleClasses": "form-group-section form-group-one","fields":[]},submit_btn]}

    }else{

      if(this.floIsValidJSONString(flo_form_schema)) {
        floFormSchema = JSON.parse(flo_form_schema)
        console.log('Valid schema')
        console.log(flo_form_schema)
      }else{
        floFormSchema = {"groups":[{"styleClasses": "form-group-section form-group-one","fields":[]},submit_btn]}
        console.log('Invalid schema')
        console.log(flo_form_schema)
      }


      // there are situations when the data is not saved properly and the submit button disapears
      // therefore we need to check if the last group contains the submit button
      // if it doesn't, then we add it
      if(typeof floFormSchema.groups[floFormSchema.groups.length - 1] !== 'undefined') {

        let lastGroupFields = floFormSchema.groups[floFormSchema.groups.length - 1]

        if(typeof lastGroupFields.fields[lastGroupFields.fields.length -1] !== 'undefined') {
          // the last field should always be the submit button
          let last_field = lastGroupFields.fields[lastGroupFields.fields.length -1]

          if(last_field.type != 'submit') {
            floFormSchema.groups.push(submit_btn);
          }else {
            // check if the submit btn has all the new attributes
            // the missing attributes for the submit button will be taken frm the default 'submit_btn'

            //Object.assign(last_field.attributes.input, submit_btn.fields[0].attributes.input);
            Object.assign(submit_btn.fields[0].attributes.input, last_field.attributes.input);
          }
        }
      }


      // Check if the 'styleClasses' are present
      // there was a situation when the styleClasses disapeared and we need to add it back if it happens
      floFormSchema.groups.forEach(function (group, group_index) {

        if(group_index != (floFormSchema.groups.length - 1) ) {  // we do not look at the last group which should be the submit button

          if(typeof group.styleClasses == 'undefined') {
            group.styleClasses = "form-group-section form-group-one";
          }
        }

      })

    }


    if(typeof flo_form_model === 'undefined') {
      floFormModel = {}
    }else{
      if(this.floIsValidJSONString(flo_form_model)) {
        floFormModel = JSON.parse(flo_form_model)
      }else{
        floFormModel = {}
        console.log('Invalid model: ')
        console.log(flo_form_model)
      }


    }

    if(typeof flo_form_id == 'undefined') {
      flo_form_id = 0
    }

    let userSavedTemplates
    if(typeof user_saved_templates === 'undefined') {
      userSavedTemplates = {}
    }else{
      userSavedTemplates = user_saved_templates
    }

    let input_types = {
      'text' : {'title':'Single Line Text', 'iconclass' : 'icon-Single_Line_Text-2' },
      'textArea': {'title':'Textarea','iconclass' : 'icon-Paragraph_Text' },
      'email' : {'title':'Email', 'iconclass' : 'icon-Email' },
      'number' : {'title':'Number', 'iconclass' : 'icon-Number' },
      'checkbox' : {'title':'Single Checkbox', 'iconclass' : 'icon-Checkbox' },
      'checklist' : {'title':'Checkbox List', 'iconclass' : 'icon-Checklist' },
      'radios' : {'title':'Radio buttons', 'iconclass' : 'icon-Radio-Button' },
      'select' : {'title':'Dropdown', 'iconclass' : 'icon-Dropdown' },
      //'switch' : {'title':'Switch', 'iconclass' : '' },
      'message' : {'title':'Paragraph', 'iconclass' : 'icon-Single_Line_Text' }, // implement this as a textarea and then on the front end just make some manipulations (set textarea value as label, and then empty the textarea) and hide the textarea
      'section_break' : {'title':'Section Break', 'iconclass' : 'icon-Separator' },
      'pikaday': {'title':'Date', 'iconclass' : 'icon-flo-calendar' },  // TO DO, it requires another library
      'hidden' : {'title':'Hidden', 'iconclass' : 'icon-Hidden' },
    }

    if(is_pro_version == '1') {
      // ADD THE PRO only field types
      input_types.upload = {'title':'Upload', 'iconclass' : 'dashicons dashicons-upload' }
    }

    return {
        model: floFormModel,
        schema: floFormSchema,
        formSettings: flo_form_settings,
        formSettingsActiveTab: 'settings',
        formStylingActiveTab: 'fonts',
        formGoogleFonts: flo_forms_google_fonts,
        formCustomFonts: flo_forms_custom_fonts,
        formSystemFonts: ['Georgia, serif', 'Palatino Linotype, Book Antiqua, Palatino, serif', 'Times New Roman, Times, serif',
        'Arial, Helvetica, sans-serif', 'Trebuchet MS, Helvetica, sans-serif', 'Verdana, Geneva, sans-serif', 'Courier New, Courier, monospace'],

        newFormState: true,
        floFormId: flo_form_id,
        isProVersion: is_pro_version,
        formIntegrations: vue_app_data.form_integrations,
        pluginDirUrlAdmin: plugin_dir_url_admin,
        settingsPageUrl: settings_page_url,
        predefinedTemplates : predefined_templates,
        userSavedTemplates : userSavedTemplates,

        formOptions: {
          validateAfterLoad: true,
          validateAfterChanged: true
        },
        inputTypes: input_types,
        fieldsWidth: {
          '100%':'100',
          '50%':'50',
          '33%':'33',
          '25%':'25'
        },
        btnPositions: {
          'auto':'Auto',
          'left':'Left',
          'center':'Center',
          'right':'Right'
        },
        dateFormatOptions: {
          'DD-MMM-YYYY':'10-Dec-2018',
          'MMM-DD-YYYY':'Dec-10-2018',
          'MM-DD-YYYY':'12-20-2018',
          'DD-MM-YYYY':'20-12-2018',
          'DD.MM.YYYY':'20.12.2018',
          'MMMM DD, YYYY':'December 19, 2018',
          'YYYY-MM-DD':'2018-12-19'
        },
        firstWeekDayOptions: {
          0: 'Sunday',
          1: 'Monday'
        },
        fieldsW_O_defaultValue: ['checklist','radios','select','checkbox','submit','label','pikaday','upload'], // fields that do not need the default value
        fieldsW_O_width: ['submit'], // fields that do not need the default value
        fieldsW_O_placeholder: ['checkbox', 'checklist','radios','select','submit','section_break','label','upload'], // fields that do not need placeholder
        fieldsW_O_hint : ['section_break','message', 'hidden','submit','checkbox','label'],
        fieldsWithChoices: ['checklist','radios','select'], // fields that need Choices
        confirmationOpt: flo_form_settings.confirmation_opt,  // TO DO: change this hardcoded option
        conditional_rule_properties : {'is' : 'is', 'is_not' : 'is not', 'greater_than' : 'greater than', 'less_than' : 'less than', 'contains' : 'contains', 'starts_with' : 'starts with', 'ends_with' : 'ends with'},

        btnTextColorValue: '#ffffff',
        btnTextColorValueHover: '#ffffff',
        btnBgColorValue: '#000000',
        btnBgColorValueHover: '#000000',
        btnBorderColorValue: '#000000',
        btnBorderColorValueHover: '#000000',
        colorSettingsElements : {'label':'Labels Color','input':'inputs Color',/*'border':'Border Color',*/'hint':'Hint Color','placeholder':'Placeholder Color','formbg':'Form Background Color'},
        labelColorValue: (typeof flo_form_settings.styling.label_color != 'undefined') ? flo_form_settings.styling.label_color :  "#444444",
        inputColorValue: (typeof flo_form_settings.styling.input_color != 'undefined') ? flo_form_settings.styling.input_color :  "#444444",
        hintColorValue: (typeof flo_form_settings.styling.hint_color != 'undefined') ? flo_form_settings.styling.hint_color :  "#444444",
        borderColorValue: (typeof flo_form_settings.styling.border_color != 'undefined') ? flo_form_settings.styling.border_color :  "#cccccc",
        inputbgColorValue: (typeof flo_form_settings.styling.inputbg_color != 'undefined') ? flo_form_settings.styling.inputbg_color :  "#ffffff",
        placeholderColorValue: (typeof flo_form_settings.styling.placeholder_color != 'undefined') ? flo_form_settings.styling.placeholder_color : "#32373c",
        formbgColorValue: (typeof flo_form_settings.styling.formbg_color != 'undefined') ? flo_form_settings.styling.formbg_color : "",
        displayPicker: {
          // use a foreach loop on colorSettingsElements to set the values below instead of hardcoding
          text: false,
          text_hover: false,
          bg: false,
          bg_hover: false,
          btn_border_color: false,
          btn_border_color_hover: false,
          label_color: false,
          input_color: false,
          inputbg_color: false,
          hint_color: false,
          border_color: false,
          placeholder_color: false,
          formbg_color: false,
        },
        submit_text_color: {
          hex: "#ffffff",
        },
        submit_text_color_hover: {
          hex: "#ffffff",
        },
        submit_bg_color: {
          hex: "#000000",
        },
        submit_bg_color_hover: {
          hex: "#000000",
        },
        btn_border_color: {
          hex: "#000000",
        },
        btn_border_color_hover: {
          hex: "#000000",
        },
        label_color: {
          hex: "#444444",
        },
        input_color: {
          hex: "#444444",
        },
        inputbg_color: {
          hex: "#ffffff",
        },
        hint_color: {
          hex: "#444444",
        },
        border_color: {
          hex: "#cccccc",
        },
        placeholder_color: {
          hex: "#32373c",
        },
        formbg_color: {
          hex: "",
        },
        btnPosition: 'auto',
        fonOptionsData: [
          {
            option_index: 1,
            font_label: 'Labels Font',
            option_name: 'label',
          },
          {
            option_index: 2,
            font_label: 'Inputs & Placeholders Font',
            option_name: 'input',
          },
          {
            option_index: 3,
            font_label: 'Hint Font',
            option_name: 'hint',
          },
          {
            option_index: 4,
            font_label: 'Button Font',
            option_name: 'button',
          },
        ]

    }

  },
  created: function () {

    // Add the custom date format if available
    if(typeof flo_forms_custom_date_format !== 'undefined'){
        this.dateFormatOptions[flo_forms_custom_date_format] = 'Custom (defined in general settings)'
    }

    /*===================================================
    =            fix the Remove Field Button            =
    = for some reason the 'onclick' event for the btn   =
    = is not showing in the schema and thus not saved   =
    = in the DB. Therefore when the component is loaded =
    = we iterate through the fields and add the proper  =
    = Remove Button =
    ===================================================*/

    let that = this;

    if(typeof this.schema.groups != 'undefined') {
      this.schema.groups.forEach(function (group, group_index) {

        if(typeof group.fields  != 'undefined') {
          group.fields.forEach(function (field) {
            let field_buttons = [
              {
                classes: "remove-field dashicons dashicons-trash",
                label: "",
                title: "Remove this field",
                onclick: function(model, field, $event) {
                  $event.preventDefault()
                  $event.stopPropagation()
                  that.removeField(model, field, group_index)
                }
              },
              {
                classes: "flo-forms-icon icon-Move-Field sort-handle",
                label: "",
                title: "Drag to reorder",
                onclick: function(model, field, $event) {
                  $event.preventDefault();
                }
              }
            ]

            // inject the hint property if necessary
            if(!that.fieldsW_O_placeholder.includes(field.type) && typeof field.hint == 'undefined') {
              field.hint = '';
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

              // safely transfer the content from the 'label' to 'help' property and empty the 'label' property
              if(field.label && field.label != ''){
                field.help = field.label
                field.label = ''
              }
            }

            if(field.type != 'submit') {
              field.buttons = field_buttons
            }

          });
        }

      });

    }
    /*=====  End of fix the Remove Field Button  ======*/

    /*=====  BOF Fix the missing formSettings fonts  ======*/
    let font_options = ['button', 'label', 'input', 'hint']

    font_options.forEach((font_option,font_index) => {

      if(typeof this.formSettings.styling[font_option].font == 'undefined') {
        this.formSettings.styling[font_option].font = 'inherit'
      }

    })
    /*=====  EOF Fix the missing formSettings fonts  ======*/
  }
}
