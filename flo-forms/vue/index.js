import Vue from 'vue';
import FormFields from '../admin/vue-components/FormFields.vue';
//import Hello from '../admin/vue-components/Hello.vue';

if (document.querySelector('.flo-forms-app') !== null) {

  // define the settings data in the prototype to make it available to each component
  //Vue.prototype.$formSettings = flo_form_settings;


  new Vue({
    el: '.flo-forms-app',
    
    beforeCreate: function() {
      //console.log(this.$formSettings)
    },


    data: {
      //vue_form_settings: flo_form_settings
    },

    components: {
      //'hello': Hello,
      'flo-form-fields' : FormFields
    },

    methods: {
      addFormField: function (key, event ) {
        console.log(event, key);
      }
      
    }


  });
    
}




