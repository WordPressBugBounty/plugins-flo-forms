<template>
  <div class="component-container dropdown-component-container"
       @click="showOptions = !showOptions"
       v-closable="{
          exclude: ['button'],
          handler: 'onClose'
        }"
  >
    <div class="component__select">
      <span class="component__select--name">{{(value && value != 'initial') ? value : 'Select Font'}}</span>
      <span class="component__select_open-close dashicons-arrow-down-alt2" :class="showOptions ? 'oppened':'' "></span>
    </div>
    <ul class="component__select-options" v-if="showOptions" >

      <li class="select--option" v-for="option in options" @click="selectOption(option.name, option_name)">
        <label :style="'font-family:'+option.name ">
          <input type="radio" :value="option.name"  :checked="option.name == value"/>

          {{option.name}}
        </label>
      </li>
    </ul>
    <!--we will store the selected value in a hidden input. this solves the problem with the radio button above not -->
    <!--transmitting the $_POST data upon Saving-->
    <input type="hidden" :name="'flo_form_settings[styling]['+option_name+'][font]'" :value="value" ref="selected_font">
  </div>
</template>

<script>

  // This variable will hold the reference to
  // document's click handler
  let handleOutsideClick

  export default {

    methods: {
      selectOption(option, model) {
        this.$emit('input', option, model) //

        this.$refs.selected_font.value = option // updates the hidden input value

        setTimeout(() => {
          this.showOptions = false
        }, 10);
      },
      onClose () {
        this.showOptions = false
      }
    },

    directives: {

      // directive that detects when we click outside and element
      // in this case it is used to detect when clicking outside the '.component-container'
      // and we close the custom dropdown
      closable: {
        bind (el, binding, vnode) {
          // Here's the click/touchstart handler
          // (it is registered below)
          handleOutsideClick = (e) => {
            e.stopPropagation()
            // Get the handler method name and the exclude array
            // from the object used in v-closable
            const { handler, exclude } = binding.value
            // This variable indicates if the clicked element is excluded

            // We check to see if the clicked element is not
            // the dialog element and not excluded
            if (!el.contains(e.target) ) {
              // If the clicked element is outside the dialog
              // and not the button, then call the outside-click handler
              // from the same component this directive is used in
              vnode.context[handler]()
            }
          }
          // Register click/touchstart event listeners on the whole page
          document.addEventListener('click', handleOutsideClick)
          document.addEventListener('touchstart', handleOutsideClick)
        },
        unbind () {
          // If the element that has v-closable is removed, then
          // unbind click/touchstart listeners from the whole page
          document.removeEventListener('click', handleOutsideClick)
          document.removeEventListener('touchstart', handleOutsideClick)
        }
      }

    },

    data: () => ({
      showOptions: false
    }),
    props: ['options', 'value','option_name']
  }
</script>

<style scoped>
  .component__select {
    height: 38px;
    background-color: #F5F7FA;
    border: 1px solid #dddddd;
    line-height: 38px;
    display: grid;
    max-width: 500px;
    grid-template-columns: 10fr 1fr;
  }

  .component__select--name {
    font-size: 0.8rem;
    padding: 0 0 0 25px;
    cursor: pointer;
    height: 40px;
    overflow-y: hidden;
  }

  .component__select-options {
    max-height: 180px;
    border: 1px solid #dddddd;
    border-top: none;
    overflow: auto;
    position: absolute;
    z-index: 1500;
    max-width: 500px;
    width: 500px;
    margin: 0;
    padding: 0;
  }


  .component__select_open-close {
    font-family: dashicons;
    margin-right: 9px;
    cursor: pointer;
  }

  .component__select_open-close.oppened {
    transform: rotate(180deg);
    margin-right: 9px;
    margin-bottom: 3px;
  }

  .select--option {
    height: 35px;
    display: grid;
    align-content: center;
    padding: 0 0 0 25px;
    background-color: #f5f5fa;
    border-bottom: 1px solid #dddddd;
  }

  .select--option:last-child {
    border-bottom: none;
  }

  .select--option:nth-child(2n) {
    background-color: #ffffff;
  }

  .select--option input{
    display: none;
  }

  .single-option {
    height: 55px;
    background-color: #2595ec;
    font-size: 0.8rem;
    border: 1px solid red;
  }

  .cust-sel {
    width: 200px;
    height: 38px;
    background-color: #f5f5fa;
    border: 1px solid #dddddd;
  }

  .cust-sel:focus {
    outline-width: 0;
  }
</style>
