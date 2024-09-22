<template>
  <div
    class="panel-body"
    v-bind:class="getPluginVersionClass()"
    :style="
      '--data-label-font: ' +
        getFontStyles(formSettings, (style_element = 'label')) +
        '; --data-input-font: ' +
        getFontStyles(formSettings, (style_element = 'input')) +
        '; --data-button-font: ' +
        getFontStyles(formSettings, (style_element = 'button')) +
        '; --data-hint-font: ' +
        getFontStyles(formSettings, (style_element = 'hint')) +
        renderStylingData(formSettings)
    "
  >
    <div
      id="form-tabs"
      class="form-tabs ui-tabs ui-widget ui-widget-content ui-corner-all"
    >
      <div
        class="new-form-options"
        v-if="
          schema.groups.length == 2 &&
            !schema.groups[0].fields.length &&
            newFormState &&
            isProVersion == 1
        "
      >
        <div class="new-form-options--scratch option" @click="show_tabs('new')">
          <div class="new-form-options--title-block">
            Create a New Form <br />
            from Scratch
            <span class="icon-flo-ctrl"></span>
          </div>
          <img v-bind:src="pluginDirUrlAdmin + 'images/create-new.png'" />
        </div>

        <div
          class="new-form-options--predefined option"
          @click="show_tabs('predefined')"
        >
          <div class="new-form-options--title-block">
            Start with a <br />
            Predefined Template
            <span class="icon-flo-ctrl"></span>
          </div>
          <img
            v-bind:src="pluginDirUrlAdmin + 'images/predefined-template.png'"
          />
        </div>
      </div>
      <div
        v-bind:class="{
          hidden:
            schema.groups.length == 2 &&
            !schema.groups[0].fields.length &&
            newFormState &&
            isProVersion == 1,
        }"
      >
        <ul class="form-tabs--wrapper">
          <li class="add-field">
            <a href="#add-field">
              <span class="dashicons dashicons-plus"></span>
              Add Field
            </a>
          </li>
          <li class="fields-settings">
            <a href="#fields-settings">
              <span class="flo-forms-icon icon-Filed_Settings_Inactive"></span>
              <span class="flo-forms-icon icon-Filed_Settings_Active">
                <span class="path1"></span><span class="path2"></span
                ><span class="path3"></span>
              </span>
              <div class="fields-settings--tab-label">Field settings</div>
            </a>
          </li>

          <li class="form-styling">
            <a href="#form-styling" v-if="isProVersion == 1">
              <span class="icon-Form_Styling_Active">
                <span class="icon-1"></span>
                <span></span>
              </span>
              Styling
            </a>

            <a onclick="ffOpenPrfb()" v-if="isProVersion != 1">
              <span class="forms-pro-label">Pro</span>
              <span class="icon-Form_Styling_Active">
                <span class="icon-1"></span>
                <span></span>
              </span>
              Styling
            </a>
          </li>
          <li class="form-settings">
            <a href="#form-settings">
              <span class="icon-Form_Settings_Inactive"> </span>
              Form settings
            </a>
          </li>
        </ul>
        <div id="add-field" class="add-field">
          <!-- <div class="tab-title">Click on the field type button you need to add it to the Form</div> -->
          <div class="add-field--inner-wrapper">
            <button
              v-for="(value, field_type) in inputTypes"
              @click.prevent="add_field(field_type)"
              class="add-field--btn "
            >
              <div class="add-field--flex">
                <span
                  v-bind:class="value.iconclass"
                  class="flo-forms-icon add-field--icon-class"
                ></span>
                {{ value.title }}
              </div>
            </button>
          </div>
        </div>
        <div id="fields-settings">
          <ul
            class="fields-controls"
            v-for="(group, group_id) in schema.groups"
          >
            <!--
            TO DO:
            make sure to replace the field_id with a computed value or something returned by a method
            otherwise there will be issues when there will be more that one groups (when columns will be available)
            -->
            <li
              v-for="(value, field_id) in schema.groups[group_id].fields"
              :data-field_id="'fid_' + value.field_id"
              class="field-settings"
            >
              <div
                class="field-settings-label field-settings-label--label-description"
                v-if="
                  value.type != 'submit' &&
                    value.type != 'checkbox' &&
                    !value.styleClasses.includes('flo-section-break')
                "
              >
                <div class="">
                  <label class="settings-label" v-if="value.type == 'label'">
                    Message value:
                  </label>
                  <label class="settings-label" v-else> Field Label: </label>
                  <FloEditor
                    v-if="value.type == 'label'"
                    :value="value.help"
                    v-model="value.help"
                  ></FloEditor>
                  <input
                    v-else
                    type="text"
                    class="width_100"
                    v-model="value.label"
                  />
                </div>

                <!-- used 1==2 to artificially hide the old hint.
                we still leave it here for a while in case we decide to come back to simple textarea in the future -->
                <span class="hint" v-if="value.type == 'label'">
                  NOTE! If you need to use '<' or '>' characters, we recommend
                  using the related
                  <a
                    href="https://www.w3schools.com/html/html_entities.asp"
                    target="_blank"
                    >HTML Character Entities</a
                  >
                  instead. Because these characters may break the form
                  sometimes.
                </span>
              </div>

              <div
                class="field-settings-label field-settings-label--ckbx-description"
                v-if="value.type == 'checkbox'"
              >
                <div class="">
                  <label class="settings-label width_100">
                    Checkbox description:
                  </label>
                  <FloEditor
                    :value="value.help"
                    v-model="value.help"
                  ></FloEditor>
                </div>
                <span class="hint">
                  NOTE! If you need to use '<' or '>' characters, we recommend
                  using the related
                  <a
                    href="https://www.w3schools.com/html/html_entities.asp"
                    target="_blank"
                    >HTML Character Entities</a
                  >
                  instead. Because these characters may break the form
                  sometimes.
                </span>
              </div>

              <div class="field-settings-label" v-if="value.type == 'select'">
                <div class="">
                  <label class="settings-label width_100">
                    None selected text:
                  </label>
                  <input
                    type="text"
                    class="width_100"
                    v-model="value.selectOptions.noneSelectedText"
                  />
                </div>
              </div>

              <div class="field-settings-label" v-if="value.type == 'submit'">
                <div class="">
                  <label class="settings-label width_100"> Button text: </label>
                  <input
                    type="text"
                    class="width_100"
                    v-model="value.buttonText"
                  />
                </div>
              </div>

              <div class="field-settings-label" v-if="value.type == 'submit'">
                <div class="">
                  <label class="settings-label width_100">
                    Button position:
                  </label>
                  <select
                    class="width_100"
                    v-model="value.attributes.input.button_position"
                    @change="change_btn_position"
                  >
                    <option
                      v-for="(position_label, position) in btnPositions"
                      :selected="position == btnPosition ? 'selected' : ''"
                      :value="position"
                    >
                      {{ position_label }}
                    </option>
                  </select>
                </div>
              </div>

              <div
                v-if="value.type == 'submit' && isProVersion != 1"
                class="input-group input-group--submit-colors field-settings-label"
                ref="text_colorpicker"
                :style="'--data-preview-color: ' + btnTextColorValue"
              >
                <div class="flex jc-space-between">
                  Button text color: <br />

                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-text-color--code"
                      v-model="btnTextColorValue"
                      @focus="showPicker('text')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-text"
                        @click="togglePicker('text')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <template>
                  <vue-color
                    v-if="value.type == 'submit' && displayPicker.text"
                    v-model="btnTextColorValue"
                    @input="updateElemColorPicker"
                    ref="flo_vue_color"
                    data-color_val="btnTextColorValue"
                    data-color_obj="submit_text_color"
                    :value="submit_text_color"
                  />
                </template>
              </div>

              <div
                v-if="value.type == 'submit' && isProVersion != 1"
                class="input-group input-group--submit-colors field-settings-label"
                ref="bg_colorpicker"
                :style="'--data-preview-color: ' + btnBgColorValue"
              >
                <div class="flex jc-space-between">
                  Button background color: <br />

                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-bg-color--code"
                      v-model="btnBgColorValue"
                      @focus="showPicker('bg')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-bg"
                        @click="togglePicker('bg')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <template>
                  <vue-color
                    v-if="value.type == 'submit' && displayPicker.bg"
                    v-model="btnBgColorValue"
                    @input="updateElemColorPicker"
                    ref="flo_vue_color"
                    data-color_val="btnBgColorValue"
                    data-color_obj="submit_bg_color"
                    :value="submit_bg_color"
                  />
                </template>
              </div>

              <div
                class="field-settings-label"
                v-if="
                  !fieldsW_O_placeholder.includes(value.type) &&
                    !value.styleClasses.includes('flo-section-break')
                "
              >
                <div class="">
                  <label class="settings-label width_100">
                    Field Placeholder:
                  </label>
                  <input
                    type="text"
                    class="width_100"
                    v-model="value.placeholder"
                  />
                </div>
              </div>

              <div
                class="field-settings-label"
                v-if="!fieldsW_O_defaultValue.includes(value.type)"
              >
                <div class="">
                  <label class="settings-label width_100">
                    Default Value:
                  </label>
                  <textarea
                    class="width_100"
                    v-if="value.type == 'label'"
                    v-model="model['field_' + value.field_id]"
                  ></textarea>
                  <input
                    class="width_100"
                    v-else
                    type="text"
                    v-model="model['field_' + value.field_id]"
                  />
                </div>
              </div>

              <div
                class="field-settings-label"
                v-if="
                  !fieldsW_O_hint.includes(value.type) &&
                    !value.styleClasses.includes('flo-section-break') &&
                    !value.styleClasses.includes('hidden')
                "
              >
                <div class="">
                  <label class="settings-label width_100"> Hint: </label>
                  <input type="text" class="width_100" v-model="value.hint" />
                </div>
              </div>

              <div
                class="field-settings-label"
                v-if="
                  typeof value.type != 'undefined' &&
                    value.type == 'pikaday' &&
                    typeof value.pikadayOptions != 'undefined'
                "
              >
                <div class="">
                  <label class="settings-label width_100"> Date format: </label>
                  <select
                    class="width_100"
                    v-model="value.pikadayOptions.format"
                    @change="change_date_format($event, field_id, group_id)"
                  >
                    <option
                      v-for="(formatLabel, formatValue) in dateFormatOptions"
                      :selected="
                        formatValue == value.pikadayOptions.format
                          ? 'selected'
                          : ''
                      "
                      :value="formatValue"
                    >
                      {{ formatLabel }}
                    </option>
                  </select>
                  <div class="hint">
                    If you need a custom date format, you can set or edit it
                    from
                    <a :href="settingsPageUrl + '&tab=settings'" target="_blank"
                      >general settings page</a
                    >.
                  </div>
                </div>
              </div>

              <div
                class="field-settings-label"
                v-if="
                  typeof value.type != 'undefined' &&
                    value.type == 'pikaday' &&
                    typeof value.pikadayOptions != 'undefined'
                "
              >
                <div class="">
                  <label class="settings-label width_100">
                    First Day of the Week:
                  </label>
                  <select
                    class="width_100"
                    v-model="value.pikadayOptions.firstDay"
                    @change="change_first_week_day($event, field_id, group_id)"
                  >
                    <option
                      v-for="(dayLabel, dayValue) in firstWeekDayOptions"
                      :selected="
                        dayValue == value.pikadayOptions.firstDay
                          ? 'selected'
                          : ''
                      "
                      :value="dayValue"
                    >
                      {{ dayLabel }}
                    </option>
                  </select>
                </div>
              </div>

              <div
                class="field-settings-label"
                v-if="!fieldsW_O_width.includes(value.type)"
              >
                <div class="">
                  <label class="settings-label width_100"> Field width: </label>
                  <select
                    class="width_100"
                    v-model="value.attributes.input.width"
                    @change="change_width_class(field_id, group_id)"
                  >
                    <option
                      v-for="(f_width, f_width_key) in fieldsWidth"
                      :selected="
                        f_width_key == currentFieldWidth(field_id, group_id)
                          ? 'selected'
                          : ''
                      "
                      :value="f_width_key"
                    >
                      {{ f_width_key }}
                    </option>
                  </select>
                </div>
              </div>
              <div
                class="field-settings-label flex jc-space-between"
                v-if="value.styleClasses.includes('flo-section-break')"
              >
                <div class="">
                  <label class="settings-label width_100">
                    Margin Top (px):
                  </label>
                  <input
                    type="number"
                    class="width_100"
                    name=""
                    v-model="value.margin_top"
                    @change="set_section_break_margin(field_id, group_id)"
                  />
                </div>
                <div class="">
                  <label class="settings-label width_100">
                    Margin Bottom (px):
                  </label>
                  <input
                    type="number"
                    class="width_100"
                    name=""
                    v-model="value.margin_bottom"
                    @change="set_section_break_margin(field_id, group_id)"
                  />
                </div>
              </div>

              <div class="field-settings-label" v-if="value.type != 'submit'">
                <div class="">
                  <label class="settings-label width_100">
                    Field CSS Class:
                  </label>
                  <input
                    type="text"
                    class="width_100"
                    name=""
                    v-model="value.styleClasses"
                  />
                </div>
              </div>

              <div
                class="field-settings-label flo-fancy-checkbox"
                v-if="
                  value.type != 'submit' &&
                    !value.styleClasses.includes('flo-section-break')
                "
              >
                <!-- we need to only simulate the required field in the backend because we don't want to be prevented to save the form
                without filling the required fields. Therefore we bind this checkbox to the input attribute, and then
                use the 'changeCurrentFieldRequiredClass' method to manipulate the required class
                In the front end we will check each filed and if this attribute is true, we will set the real 'required' property-->
                <label class="flex jc-space-between">
                  <div class="settings-label">Required</div>
                  <div class="switch">
                    <input
                      type="checkbox"
                      v-model="value.attributes.input.required"
                      v-on:change="
                        changeCurrentFieldRequiredClass(
                          field_id,
                          group_id,
                          $event
                        )
                      "
                    />
                    <span class="checkbox-slider round"></span>
                  </div>
                </label>
              </div>
              <div
                class="field-settings-label"
                v-if="fieldsWithChoices.includes(value.type)"
              >
                <div class="">
                  <label class="settings-label width_100">Choices</label>
                  <div class="field-settings--choices-wrapper">
                    <div
                      class="field-settings--choices"
                      v-for="(f_choice, choice_index) in value.values"
                    >
                      <input
                        class="width_100"
                        type="text"
                        v-model="value.values[choice_index]"
                      />
                      <span
                        class="add-new-choice dashicons dashicons-plus-alt"
                        title="Add choice"
                        v-on:click="value.values.push('Value 4')"
                      ></span>
                      <span
                        class="remove-choice dashicons dashicons-trash"
                        title="Remove choice"
                        v-if="choice_index > 0"
                        v-on:click="value.values.splice(choice_index, 1)"
                      >
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="conditional-logic flo-fancy-checkbox"
                v-if="value.type != 'submit'"
              >
                <label class="flex conditional-logic--label">
                  <span class="icon-Link">
                    <span class="path1"></span><span class="path2"></span>
                  </span>
                  <div class="">Enable conditional logic ?</div>
                  <div class="switch">
                    <input
                      type="checkbox"
                      v-model="value.attributes.input.enable_conditional_logic"
                    />
                    <span class="checkbox-slider round"></span>
                  </div>
                </label>

                <div
                  class="conditional-logic--rules"
                  v-if="value.attributes.input.enable_conditional_logic"
                >
                  <div
                    v-for="(cl_group, group_rule_id) in value.attributes.input
                      .conditional_logic_groups"
                    class="cl-rules--group-container"
                  >
                    <fieldset>
                      <legend align="center" v-if="group_rule_id == 0">
                        Show This field if:
                      </legend>

                      <div
                        v-for="(rule_data, rule_id) in cl_group.rules"
                        class="cl-rules--rule-container"
                      >
                        <select
                          class="cl-rules--rule-operator"
                          v-model="rule_data.rule_select"
                        >
                          <option
                            v-for="current_field in allFields"
                            :selected="
                              parseInt(current_field.field_id) ==
                              parseInt(rule_data.rule_select)
                                ? 'selected'
                                : ''
                            "
                            :value="current_field.field_id"
                          >
                            <span v-if="current_field.label != ''">
                              {{ current_field.label }}
                            </span>
                            <span
                              v-else-if="
                                typeof current_field.placeholder !==
                                  'undefined' && current_field.placeholder != ''
                              "
                            >
                              {{ current_field.placeholder }}
                            </span>
                            <span
                              v-else-if="
                                typeof current_field.obj.help !== 'undefined' &&
                                  current_field.obj.help != ''
                              "
                            >
                              {{ current_field.obj.help }}
                            </span>
                            <span
                              v-else-if="
                                typeof current_field.obj !== 'undefined' &&
                                  typeof current_field.obj.selectOptions !==
                                    'undefined' &&
                                  typeof current_field.obj.selectOptions
                                    .noneSelectedText !== 'undefined'
                              "
                            >
                              {{
                                current_field.obj.selectOptions.noneSelectedText
                              }}
                            </span>
                            <span v-else></span>
                          </option>
                        </select>

                        <select
                          class="cl-rules--rule-property"
                          v-model="rule_data.rule_property"
                        >
                          <option
                            v-for="(property,
                            property_index) in conditional_rule_properties"
                            :selected="
                              property_index == rule_data.rule_property
                                ? 'selected'
                                : ''
                            "
                            :value="property_index"
                          >
                            {{ property }}
                          </option>
                        </select>

                        <div
                          class="cl-rules--rule-property"
                          v-if="
                            typeof rule_data.rule_select == 'number' &&
                              typeof allFields[rule_data.rule_select] !==
                                'undefined' &&
                              typeof allFields[rule_data.rule_select].type !==
                                'undefined' &&
                              fieldsWithChoices.includes(
                                allFields[rule_data.rule_select].type
                              )
                          "
                        >
                          <select v-model="rule_data.rule_input">
                            <option
                              v-for="(v, ind) in allFields[
                                rule_data.rule_select
                              ].obj.values"
                              :selected="
                                v == rule_data.rule_input ? 'selected' : ''
                              "
                              :value="v"
                            >
                              {{ v }}
                            </option>
                          </select>
                        </div>
                        <div
                          class="cl-rules--rule-property"
                          v-else-if="
                            typeof allFields[rule_data.rule_select] !=
                              'undefined' &&
                              typeof allFields[rule_data.rule_select].type !=
                                'undefined' &&
                              allFields[rule_data.rule_select].type ==
                                'checkbox'
                          "
                        >
                          <select v-model="rule_data.rule_input">
                            <option
                              :selected="
                                true == rule_data.rule_input ? 'selected' : ''
                              "
                              :value="true"
                            >
                              Checked
                            </option>
                            <option
                              :selected="
                                false == rule_data.rule_input ? 'selected' : ''
                              "
                              :value="false"
                            >
                              Unchecked
                            </option>
                          </select>
                        </div>
                        <div
                          class="cl-rules--rule-property"
                          v-else="
                            typeof allFields[rule_data.rule_select] !=
                              'undefined' &&
                              typeof allFields[rule_data.rule_select].type !=
                                'undefined' &&
                              allFields[rule_data.rule_select].type == 'input'
                          "
                        >
                          <input
                            type="text"
                            class="cl-rules--rule-input"
                            v-model="rule_data.rule_input"
                            placeholder="Enter a value"
                          />
                        </div>

                        <select
                          class="cl-rules--inner-group-conditions-operator"
                          v-model="rule_data.next_rule_operator"
                        >
                          <option
                            value="and"
                            :selected="
                              rule_data.next_rule_operator == '&&' ||
                              rule_data.next_rule_operator == 'and'
                                ? 'selected'
                                : ''
                            "
                          >
                            And
                          </option>
                          <option
                            value="||"
                            :selected="
                              rule_data.next_rule_operator == '||'
                                ? 'selected'
                                : ''
                            "
                          >
                            Or
                          </option>
                        </select>

                        <div class="cl-rules--add-remove-rule-container">
                          <span
                            class="remove-rule dashicons dashicons-trash"
                            title="Remove this rule"
                            v-if="
                              cl_group.rules.length > 1 || group_rule_id > 0
                            "
                            v-on:click="
                              removeCondLogicRule(value, group_rule_id, rule_id)
                            "
                          >
                          </span>
                        </div>
                      </div>

                      <!-- cl_group.rules.push( Object.assign({}, cl_group.rules[0]) )   =>    clone the first rule object -->
                      <div
                        class="add-new-rule--wrapper"
                        v-on:click="
                          cl_group.rules.push(
                            Object.assign({}, cl_group.rules[0])
                          )
                        "
                      >
                        <span
                          class="add-new-rule dashicons dashicons-plus"
                          title="Add new rule"
                        >
                        </span>
                      </div>
                    </fieldset>
                    <select
                      class="cl-rules--group-to-group-conditions-operator"
                      v-model="cl_group.next_group_operator"
                    >
                      <option
                        value="and"
                        :selected="
                          cl_group.next_group_operator == '&&' ||
                          cl_group.next_group_operator == 'and'
                            ? 'selected'
                            : ''
                        "
                      >
                        And
                      </option>
                      <option
                        value="||"
                        :selected="
                          cl_group.next_group_operator == '||' ? 'selected' : ''
                        "
                      >
                        Or
                      </option>
                    </select>
                  </div>

                  <div
                    class="conditional-logic--add-rule-group"
                    v-on:click.prevent="
                      addRuleGroup(
                        value.attributes.input.conditional_logic_groups
                      )
                    "
                  >
                    <span class="dashicons dashicons-plus"></span>
                    Add New <br />
                    Rule Group
                    <span class="empty-placeholder"></span>
                  </div>
                </div>
              </div>
              <div class="field-settings-label" v-if="value.type != 'submit'">
                <div class="flex jc-space-between">
                  <label class="field-id--label settings-label">
                    Field ID:
                  </label>
                  <span class="field-id--value"> {{ value.field_id }}</span>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div id="form-styling" class="form-styling" v-if="isProVersion == 1">
          <div class="settings-styling-tabs">
            <div class="field-settings-label--wrapper flex">
              <label
                class="pro-styling pro-styling--font"
                :class="{ selected: formStylingActiveTab == 'fonts' }"
              >
                <input
                  type="radio"
                  class="confirmation_opt"
                  value="fonts"
                  v-model="formStylingActiveTab"
                />
                <div class="text-wrapper">Fonts</div>
              </label>

              <label
                class="pro-styling pro-styling--padding"
                :class="{ selected: formStylingActiveTab == 'padding' }"
              >
                <input
                  type="radio"
                  class="confirmation_opt"
                  value="padding"
                  v-model="formStylingActiveTab"
                />
                <div class="text-wrapper">Padding</div>
              </label>

              <label
                class="pro-styling pro-styling--colors"
                v-if="isProVersion == '1'"
                :class="{ selected: formStylingActiveTab == 'colors' }"
              >
                <input
                  type="radio"
                  class="confirmation_opt"
                  value="colors"
                  v-model="formStylingActiveTab"
                />
                <div class="text-wrapper">Colors</div>
              </label>

              <label
                class="pro-styling pro-styling--button"
                :class="{ selected: formStylingActiveTab == 'button' }"
              >
                <input
                  type="radio"
                  class="confirmation_opt"
                  value="button"
                  v-model="formStylingActiveTab"
                />
                <div class="text-wrapper">Button</div>
              </label>
            </div>
          </div>

          <div
            class="form-settings--styling"
            v-show="formStylingActiveTab == 'padding'"
          >
            <div class="form-settings--styling-padding">
              <label class="settings-label settings-label--big"
                >Desktop Form Padding:</label
              >
              <div class="form-settings--styling-padding_options">
                <div class="left padding-option">
                  <div>Left</div>
                  <input
                    type="number"
                    name="flo_form_settings[padding][left]"
                    v-model="formSettings.padding.left"
                  />px
                </div>
                <div class="right padding-option">
                  <div>Right</div>
                  <input
                    type="number"
                    name="flo_form_settings[padding][right]"
                    v-model="formSettings.padding.right"
                  />px
                </div>
                <div class="top padding-option">
                  <div>Top</div>
                  <input
                    type="number"
                    name="flo_form_settings[padding][top]"
                    v-model="formSettings.padding.top"
                  />px
                </div>
                <div class="bottom padding-option">
                  <div>Bottom</div>
                  <input
                    type="number"
                    name="flo_form_settings[padding][bottom]"
                    v-model="formSettings.padding.bottom"
                  />px
                </div>
              </div>
            </div>
          </div>

          <div
            class="form-settings--styling"
            v-show="formStylingActiveTab == 'padding'"
          >
            <div class="form-settings--styling-padding">
              <label class="settings-label settings-label--big"
                >Mobile Form Padding:</label
              >
              <div class="form-settings--styling-padding_options">
                <div class="left padding-option">
                  <div>Left</div>
                  <input
                    type="number"
                    name="flo_form_settings[mobile_padding][left]"
                    v-model="formSettings.mobile_padding.left"
                  />px
                </div>
                <div class="right padding-option">
                  <div>Right</div>
                  <input
                    type="number"
                    name="flo_form_settings[mobile_padding][right]"
                    v-model="formSettings.mobile_padding.right"
                  />px
                </div>
                <div class="top padding-option">
                  <div>Top</div>
                  <input
                    type="number"
                    name="flo_form_settings[mobile_padding][top]"
                    v-model="formSettings.mobile_padding.top"
                  />px
                </div>
                <div class="bottom padding-option">
                  <div>Bottom</div>
                  <input
                    type="number"
                    name="flo_form_settings[mobile_padding][bottom]"
                    v-model="formSettings.mobile_padding.bottom"
                  />px
                </div>
              </div>
            </div>
          </div>

          <div
            class="form-settings--styling"
            v-show="formStylingActiveTab == 'button'"
          >
            <!--add here the button settings-->
            <div
              class="input-group input-group--submit-colors field-settings-label"
              ref="text_colorpicker"
              :style="'--data-preview-color: ' + btnTextColorValue"
            >
              <div class="flex jc-space-between">
                Button text color: <br />
                <div>
                  <input
                    type="text"
                    class="submit-color-input submit-text-color--code"
                    v-model="btnTextColorValue"
                    @focus="showPicker('text')"
                  />
                  <div class="submit-color--preview-wrapper">
                    <span
                      class="submit-color--preview submit-color--preview-text"
                      @click="togglePicker('text')"
                    ></span>
                  </div>
                  <span class="icon-Color_Drop"></span>
                </div>
              </div>
              <template>
                <vue-color
                  v-if="displayPicker.text"
                  v-model="btnTextColorValue"
                  @input="updateElemColorPicker"
                  ref="flo_vue_color"
                  data-color_val="btnTextColorValue"
                  data-color_obj="submit_text_color"
                  :value="submit_text_color"
                />
              </template>
            </div>

            <div
              class="input-group input-group--submit-colors field-settings-label"
              ref="text_colorpicker_hover"
              :style="'--data-preview-color: ' + btnTextColorValueHover"
            >
              <div class="flex jc-space-between">
                Button hover text color: <br />
                <div>
                  <input
                    type="text"
                    class="submit-color-input submit-text-color--code"
                    v-model="btnTextColorValueHover"
                    @focus="showPicker('text_hover')"
                  />
                  <div class="submit-color--preview-wrapper">
                    <span
                      class="submit-color--preview submit-color--preview-text"
                      @click="togglePicker('text_hover')"
                    ></span>
                  </div>
                  <span class="icon-Color_Drop"></span>
                </div>
              </div>
              <template>
                <vue-color
                  v-if="displayPicker.text_hover"
                  v-model="btnTextColorValueHover"
                  @input="updateElemColorPicker"
                  ref="flo_vue_color"
                  data-color_val="btnTextColorValueHover"
                  data-color_obj="submit_text_color_hover"
                  :value="submit_text_color_hover"
                />
              </template>
            </div>

            <div
              class="input-group input-group--submit-colors field-settings-label"
              ref="bg_colorpicker"
              :style="'--data-preview-color: ' + btnBgColorValue"
            >
              <div class="flex jc-space-between">
                Button background color: <br />
                <div>
                  <input
                    type="text"
                    class="submit-color-input submit-bg-color--code"
                    v-model="btnBgColorValue"
                    @focus="showPicker('bg')"
                  />
                  <div class="submit-color--preview-wrapper">
                    <span
                      class="submit-color--preview submit-color--preview-bg"
                      @click="togglePicker('bg')"
                    ></span>
                  </div>
                  <span class="icon-Color_Drop"></span>
                </div>
              </div>
              <template>
                <vue-color
                  v-if="displayPicker.bg"
                  v-model="btnBgColorValue"
                  @input="updateElemColorPicker"
                  ref="flo_vue_color"
                  data-color_val="btnBgColorValue"
                  data-color_obj="submit_bg_color"
                  :value="submit_bg_color"
                />
              </template>
            </div>

            <div
              class="input-group input-group--submit-colors field-settings-label"
              ref="bg_colorpicker_hover"
              :style="'--data-preview-color: ' + btnBgColorValueHover"
            >
              <div class="flex jc-space-between">
                Button hover background color: <br />
                <div>
                  <input
                    type="text"
                    class="submit-color-input submit-bg-color--code"
                    v-model="btnBgColorValueHover"
                    @focus="showPicker('bg_hover')"
                  />
                  <div class="submit-color--preview-wrapper">
                    <span
                      class="submit-color--preview submit-color--preview-bg"
                      @click="togglePicker('bg_hover')"
                    ></span>
                  </div>
                  <span class="icon-Color_Drop"></span>
                </div>
              </div>
              <template>
                <vue-color
                  v-if="displayPicker.bg_hover"
                  v-model="btnBgColorValueHover"
                  @input="updateElemColorPicker"
                  ref="flo_vue_color"
                  data-color_val="btnBgColorValueHover"
                  data-color_obj="submit_bg_color_hover"
                  :value="submit_bg_color_hover"
                />
              </template>
            </div>

            <div
              class="input-group input-group--submit-colors field-settings-label"
              ref="btn_border_colorpicker"
              :style="'--data-preview-color: ' + btnBorderColorValue"
            >
              <div class="flex jc-space-between">
                Button border color: <br />
                <div>
                  <input
                    type="text"
                    class="submit-color-input submit-btn_border_color-color--code"
                    v-model="btnBorderColorValue"
                    @focus="showPicker('btn_border_color')"
                  />
                  <div class="submit-color--preview-wrapper">
                    <span
                      class="submit-color--preview submit-color--preview-btn_border_color"
                      @click="togglePicker('btn_border_color')"
                    ></span>
                  </div>
                  <span class="icon-Color_Drop"></span>
                </div>
              </div>
              <template>
                <vue-color
                  v-if="displayPicker.btn_border_color"
                  @input="updateElemColorPicker"
                  v-model="btnBorderColorValue"
                  ref="flo_vue_color"
                  data-color_val="btnBorderColorValue"
                  data-color_obj="btn_border_color"
                  :value="btn_border_color"
                />
              </template>
            </div>

            <div
              class="input-group input-group--submit-colors field-settings-label"
              ref="btn_border_colorpicker_hover"
              :style="'--data-preview-color: ' + btnBorderColorValueHover"
            >
              <div class="flex jc-space-between">
                Button hover border color: <br />
                <div>
                  <input
                    type="text"
                    class="submit-color-input submit-btn_border_color-color--code"
                    v-model="btnBorderColorValueHover"
                    @focus="showPicker('btn_border_color_hover')"
                  />
                  <div class="submit-color--preview-wrapper">
                    <span
                      class="submit-color--preview submit-color--preview-btn_border_color"
                      @click="togglePicker('btn_border_color_hover')"
                    ></span>
                  </div>
                  <span class="icon-Color_Drop"></span>
                </div>
              </div>
              <template>
                <vue-color
                  v-if="displayPicker.btn_border_color_hover"
                  v-model="btnBorderColorValueHover"
                  @input="updateElemColorPicker"
                  ref="flo_vue_color"
                  data-color_val="btnBorderColorValueHover"
                  data-color_obj="btn_border_color_hover"
                  :value="btn_border_color"
                />
              </template>
            </div>

            <div
              class="flex jc-space-between button-styles-digit ff_margin_bottom_25"
            >
              <div class="buttonp">
                <div class="label">Button width(px)</div>
                <input
                  type="number"
                  v-model="formSettings.styling.button.button_width"
                  :name="'flo_form_settings[styling][button][button_width]'"
                />
              </div>
            </div>
            <div
              class="flex jc-space-between button-styles-digit ff_margin_bottom_25"
            >
              <!--border_radius-->
              <div class="buttonp">
                <div class="label">Top/Bottom Padding(px)</div>
                <input
                  type="number"
                  v-model="formSettings.styling.button.padding_y"
                  :name="'flo_form_settings[styling][button][padding_y]'"
                />
              </div>
              <div class="buttonp">
                <div class="label">Left/Right Padding(px)</div>
                <input
                  type="number"
                  v-model="formSettings.styling.button.padding_x"
                  :name="'flo_form_settings[styling][button][padding_x]'"
                />
              </div>
            </div>

            <div
              class="flex jc-space-between button-styles-digit ff_margin_bottom_25"
            >
              <div class="buttonp">
                <div class="label">Border Radius(px)</div>
                <input
                  type="number"
                  v-model="formSettings.styling.button.border_radius"
                  :name="'flo_form_settings[styling][button][border_radius]'"
                />
              </div>
              <div class="buttonp">
                <div class="label">Border Width(px)</div>
                <input
                  type="number"
                  v-model="formSettings.styling.button.border_width"
                  :name="'flo_form_settings[styling][button][border_width]'"
                />
              </div>
            </div>
          </div>
          <div
            class="form-settings--styling"
            v-show="formStylingActiveTab == 'colors'"
          >
            <div class="flo-fancy-checkbox">
              <label class="flex jc-space-between">
                <div class="settings-label settings-label--big">
                  Apply Custom Colors
                </div>
                <div class="switch">
                  <input
                    type="checkbox"
                    name="flo_form_settings[styling][use_custom_colors]"
                    value="1"
                    v-model="formSettings.styling.use_custom_colors"
                  />
                  <span class="checkbox-slider round"></span>
                </div>
              </label>
            </div>

            <!--The following variables are necessary to properly initiate the color picker:-->
            <!--'rel' attribute from the container, in this example it is ref="label_colorpicker" -> make sure it is also used in the documentClick() method-->
            <!--v-model value-->
            <!--showPicker() function parameter-->
            <!--togglePicker() function parameter-->
            <!--:value for the <vue-color></vue-color>-->
            <div class="form-settings--styling-colors">
              <div
                v-show="formSettings.styling.use_custom_colors == 1"
                class="input-group form-settings--custom-colors"
                ref="label_colorpicker"
                :style="'--data-preview-color: ' + labelColorValue"
              >
                <!--the :style has the wrong value: btnBgColorValue. TO DO: replace it -->

                <div class="flex jc-space-between">
                  Labels Color: <br />
                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-text-color--code"
                      name="flo_form_settings[styling][label_color]"
                      v-model="labelColorValue"
                      @focus="showPicker('label_color')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-text"
                        @click="togglePicker('label_color')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <div class="form-settings--custom-colors_colorpicker">
                  <template>
                    <vue-color
                      v-if="displayPicker.label_color"
                      v-model="labelColorValue"
                      @input="updateElemColorPicker"
                      ref="flo_vue_color"
                      data-color_val="labelColorValue"
                      data-color_obj="label_color"
                      :value="label_color"
                    />
                  </template>
                </div>
              </div>

              <div
                v-show="formSettings.styling.use_custom_colors == 1"
                class="input-group form-settings--custom-colors"
                ref="input_colorpicker"
                :style="'--data-preview-color: ' + inputColorValue"
              >
                <!--the :style has the wrong value: btnBgColorValue. TO DO: replace it -->

                <div class="flex jc-space-between">
                  Inputs Text Color: <br />
                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-text-color--code"
                      name="flo_form_settings[styling][input_color]"
                      v-model="inputColorValue"
                      @focus="showPicker('input_color')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-text"
                        @click="togglePicker('input_color')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <div class="form-settings--custom-colors_colorpicker">
                  <template>
                    <vue-color
                      v-if="displayPicker.input_color"
                      @input="updateElemColorPicker"
                      v-model="inputColorValue"
                      ref="flo_vue_color"
                      data-color_val="inputColorValue"
                      data-color_obj="input_color"
                      :value="input_color"
                    />
                  </template>
                </div>
              </div>

              <div
                v-show="formSettings.styling.use_custom_colors == 1"
                class="input-group form-settings--custom-colors"
                ref="placeholder_colorpicker"
                :style="'--data-preview-color: ' + placeholderColorValue"
              >
                <!--the :style has the wrong value: btnBgColorValue. TO DO: replace it -->

                <div class="flex jc-space-between">
                  Placeholder Color: <br />
                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-text-color--code"
                      name="flo_form_settings[styling][placeholder_color]"
                      v-model="placeholderColorValue"
                      @focus="showPicker('placeholder_color')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-text"
                        @click="togglePicker('placeholder_color')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <div class="form-settings--custom-colors_colorpicker">
                  <template>
                    <vue-color
                      v-if="displayPicker.placeholder_color"
                      @input="updateElemColorPicker"
                      v-model="placeholderColorValue"
                      ref="flo_vue_color"
                      data-color_val="placeholderColorValue"
                      data-color_obj="placeholder_color"
                      :value="placeholder_color"
                    />
                  </template>
                </div>
              </div>

              <div
                v-show="formSettings.styling.use_custom_colors == 1"
                class="input-group form-settings--custom-colors"
                ref="border_colorpicker"
                :style="'--data-preview-color: ' + borderColorValue"
              >
                <!--the :style has the wrong value: btnBgColorValue. TO DO: replace it -->

                <div class="flex jc-space-between">
                  Inputs Border Color: <br />
                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-text-color--code"
                      name="flo_form_settings[styling][border_color]"
                      v-model="borderColorValue"
                      @focus="showPicker('border_color')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-text"
                        @click="togglePicker('border_color')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <div class="form-settings--custom-colors_colorpicker">
                  <template>
                    <vue-color
                      v-if="displayPicker.border_color"
                      @input="updateElemColorPicker"
                      v-model="borderColorValue"
                      ref="flo_vue_color"
                      data-color_val="borderColorValue"
                      data-color_obj="border_color"
                      :value="border_color"
                    />
                  </template>
                </div>
              </div>

              <div
                v-show="formSettings.styling.use_custom_colors == 1"
                class="input-group form-settings--custom-colors"
                ref="inputbg_colorpicker"
                :style="'--data-preview-color: ' + inputbgColorValue"
              >
                <!--the :style has the wrong value: btnBgColorValue. TO DO: replace it -->

                <div class="flex jc-space-between">
                  Inputs Background Color: <br />
                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-text-color--code"
                      name="flo_form_settings[styling][inputbg_color]"
                      v-model="inputbgColorValue"
                      @focus="showPicker('inputbg_color')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-text"
                        @click="togglePicker('inputbg_color')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <div class="form-settings--custom-colors_colorpicker">
                  <template>
                    <vue-color
                      v-if="displayPicker.inputbg_color"
                      @input="updateElemColorPicker"
                      v-model="inputbgColorValue"
                      ref="flo_vue_color"
                      data-color_val="inputbgColorValue"
                      data-color_obj="inputbg_color"
                      :value="inputbg_color"
                    />
                  </template>
                </div>
              </div>

              <div
                v-show="formSettings.styling.use_custom_colors == 1"
                class="input-group form-settings--custom-colors"
                ref="hint_colorpicker"
                :style="'--data-preview-color: ' + hintColorValue"
              >
                <div class="flex jc-space-between">
                  Hint Color: <br />
                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-text-color--code"
                      name="flo_form_settings[styling][hint_color]"
                      v-model="hintColorValue"
                      @focus="showPicker('hint_color')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-text"
                        @click="togglePicker('hint_color')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <div class="form-settings--custom-colors_colorpicker">
                  <template>
                    <vue-color
                      v-if="displayPicker.hint_color"
                      @input="updateElemColorPicker"
                      v-model="hintColorValue"
                      ref="flo_vue_color"
                      data-color_val="hintColorValue"
                      data-color_obj="hint_color"
                      :value="hint_color"
                    />
                  </template>
                </div>
              </div>

              <div
                v-show="formSettings.styling.use_custom_colors == 1"
                class="input-group form-settings--custom-colors"
                ref="formbg_colorpicker"
                :style="'--data-preview-color: ' + formbgColorValue"
              >
                <!--the :style has the wrong value: btnBgColorValue. TO DO: replace it -->

                <div class="flex jc-space-between">
                  Form Background Color: <br />
                  <div>
                    <input
                      type="text"
                      class="submit-color-input submit-text-color--code"
                      name="flo_form_settings[styling][formbg_color]"
                      v-model="formbgColorValue"
                      @focus="showPicker('formbg_color')"
                    />
                    <div class="submit-color--preview-wrapper">
                      <span
                        class="submit-color--preview submit-color--preview-text"
                        @click="togglePicker('formbg_color')"
                      ></span>
                    </div>
                    <span class="icon-Color_Drop"></span>
                  </div>
                </div>
                <div class="form-settings--custom-colors_colorpicker">
                  <template>
                    <vue-color
                      v-if="displayPicker.formbg_color"
                      @input="updateElemColorPicker"
                      v-model="formbgColorValue"
                      ref="flo_vue_color"
                      data-color_val="formbgColorValue"
                      data-color_obj="formbg_color"
                      :value="formbg_color"
                    />
                  </template>
                </div>
              </div>
            </div>
          </div>
          <div
            class="form-settings--styling"
            v-show="formStylingActiveTab == 'fonts'"
          >
            <div class="form-settings--styling-typography ">
              <div class="flo-fancy-checkbox">
                <label class="flex jc-space-between">
                  <div class="settings-label settings-label--big">
                    Apply Custom Fonts
                  </div>
                  <div class="switch">
                    <input
                      type="checkbox"
                      name="flo_form_settings[styling][use_custom_fonts]"
                      value="1"
                      v-model="formSettings.styling.use_custom_fonts"
                    />
                    <span class="checkbox-slider round"></span>
                  </div>
                </label>
              </div>

              <div v-show="showCustomFontsOptions()" class="">
                <div class="hint">
                  <b><u>Note!</u></b> The font sizes set here are responsive,
                  meaning they will adjust depending on the screen size. Also
                  the theme styles may alter the size on the front end and make
                  it look different(smaller or larger) - In this case increase
                  or decrease the font size according to your needs.
                </div>
                <br /><br />

                <!--loop through Font Options-->
                <div
                  v-for="fo in fonOptionsData"
                  class="font-option--outer-container"
                >
                  <div
                    v-if="
                      typeof formSettings.styling[fo.option_name] !==
                        'undefined'
                    "
                    :class="'font-option font-option_' + fo.option_index"
                  >
                    <div class="settings-label">{{ fo.font_label }}</div>

                    <div class="font-option--wrapper-border">
                      <div @click="openCloseFontOption(fo.option_index)">
                        <div class="label-font-preview">
                          {{
                            typeof formSettings.styling[fo.option_name].font !=
                            "undefined"
                              ? formSettings.styling[fo.option_name].font
                              : "initial"
                          }}
                        </div>

                        <!--pass the index ID of the wrapper-->
                        <div
                          class="font-option--open-close dashicons-arrow-down-alt2"
                        ></div>
                      </div>
                      <div class="font-option--wrapper">
                        <div class="font-option--font-family">
                          <div class="settings-label">Font Family</div>

                          <AdvancedSelect
                            :option_name="fo.option_name"
                            :options="getFontOptions()"
                            :value="formSettings.styling[fo.option_name].font"
                            v-model="formSettings.styling[fo.option_name].font"
                          ></AdvancedSelect>
                        </div>

                        <div
                          class="font-option--property font-option--font-size"
                        >
                          <label class="settings-label">
                            Font Size
                          </label>
                          <div class="vue-slider-wrapper">
                            <vue-slider
                              :ref="'font_size_slider--' + fo.option_name"
                              v-model="
                                formSettings.styling[fo.option_name].font_size
                              "
                            ></vue-slider>
                            <span class="unit">px</span>
                          </div>
                          <input
                            type="hidden"
                            :name="
                              'flo_form_settings[styling][' +
                                fo.option_name +
                                '][font_size]'
                            "
                            v-model="
                              formSettings.styling[fo.option_name].font_size
                            "
                          />
                        </div>

                        <div
                          class="font-option--property font-option--line-height"
                        >
                          <label class="settings-label">
                            Line Height
                          </label>
                          <div class="vue-slider-wrapper">
                            <vue-slider
                              :ref="'line_height_slider--' + fo.option_name"
                              v-model="
                                formSettings.styling[fo.option_name].line_height
                              "
                              v-bind:min="1"
                              v-bind:max="3"
                              v-bind:interval="0.1"
                            ></vue-slider>
                            <span class="unit">em</span>
                          </div>
                          <input
                            type="hidden"
                            :name="
                              'flo_form_settings[styling][' +
                                fo.option_name +
                                '][line_height]'
                            "
                            v-model="
                              formSettings.styling[fo.option_name].line_height
                            "
                          />
                        </div>

                        <div
                          class="font-option--property font-option--letter-spacing"
                        >
                          <label class="settings-label">
                            Letter Spacing
                          </label>
                          <div class="vue-slider-wrapper">
                            <vue-slider
                              :ref="'letter_spacing_slider--' + fo.option_name"
                              v-model="
                                formSettings.styling[fo.option_name]
                                  .letter_spacing
                              "
                              v-bind:min="0"
                              v-bind:max="10"
                            ></vue-slider>
                            <span class="unit">px</span>
                          </div>
                          <input
                            type="hidden"
                            :name="
                              'flo_form_settings[styling][' +
                                fo.option_name +
                                '][letter_spacing]'
                            "
                            v-model="
                              formSettings.styling[fo.option_name]
                                .letter_spacing
                            "
                          />
                        </div>

                        <div
                          class="font-option--property font-option--font-style"
                        >
                          <label class="settings-label">
                            Font Style
                          </label>
                          <div class="font-style-wrapper">
                            <label
                              :class="{
                                selected:
                                  formSettings.styling[fo.option_name].italic ==
                                  1,
                              }"
                            >
                              <input
                                type="checkbox"
                                :name="
                                  'flo_form_settings[styling][' +
                                    fo.option_name +
                                    '][italic]'
                                "
                                value="1"
                                v-model="
                                  formSettings.styling[fo.option_name].italic
                                "
                              />
                              <span
                                class="style-icon dashicons dashicons-editor-italic"
                              ></span>
                            </label>
                            <label
                              :class="{
                                selected:
                                  formSettings.styling[fo.option_name].bold ==
                                  1,
                              }"
                            >
                              <input
                                type="checkbox"
                                :name="
                                  'flo_form_settings[styling][' +
                                    fo.option_name +
                                    '][bold]'
                                "
                                value="1"
                                v-model="
                                  formSettings.styling[fo.option_name].bold
                                "
                              />
                              <span
                                class="style-icon dashicons dashicons-editor-bold"
                              ></span>
                            </label>
                            <label
                              :class="{
                                selected:
                                  formSettings.styling[fo.option_name]
                                    .underline == 1,
                              }"
                            >
                              <input
                                type="checkbox"
                                :name="
                                  'flo_form_settings[styling][' +
                                    fo.option_name +
                                    '][underline]'
                                "
                                value="1"
                                v-model="
                                  formSettings.styling[fo.option_name].underline
                                "
                              />
                              <span
                                class="style-icon dashicons dashicons-editor-underline"
                              ></span>
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="custom-fonts-hint">
                  <!--<div class="icon-flo-ctrl"></div>-->
                  <a
                    :href="settingsPageUrl + '&tab=custom_fonts'"
                    class="icon-flo-ctrl"
                    target="_blank"
                  ></a>
                  <div class="content">
                    <a
                      :href="settingsPageUrl + '&tab=custom_fonts'"
                      target="_blank"
                    >
                      Additional Custom Fonts <br />
                      can be added from the <br />
                      <u>general settings</u>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="form-settings" class="form-settings">
          <!-- <ul class="styling-tabs" v-if="isProVersion == '1'">
            <li>Settings</li>
            <li>Styling</li>
          </ul> -->

          <div class="settings-styling-tabs">
            <div class="field-settings-label--wrapper flex">
              <label
                class="pro-styling pro-styling--settings"
                :class="{ selected: formSettingsActiveTab == 'settings' }"
              >
                <input
                  type="radio"
                  class="confirmation_opt"
                  value="settings"
                  v-model="formSettingsActiveTab"
                />
                <div class="text-wrapper">Settings</div>
              </label>

              <label
                class="pro-styling pro-styling--integrations"
                :class="{ selected: formSettingsActiveTab == 'integrations' }"
              >
                <input
                  type="radio"
                  class="confirmation_opt"
                  value="integrations"
                  v-model="formSettingsActiveTab"
                />
                <div class="text-wrapper">Integrations</div>
              </label>
            </div>
          </div>

          <div
            class="form-settings--settings"
            v-show="formSettingsActiveTab == 'settings'"
          >
            <div class="field-settings-label border-bottom" for="send-to-email">
              <div class="flex">
                <label class="settings-label_140"> Recipient email: </label>

                <input
                  type="text"
                  name="flo_form_settings[send-to-email]"
                  id="send-to-email"
                  v-bind:value="formSettings['send-to-email']"
                />
              </div>
              <span class="tab-title hint"
                >If you need to send the messages to several recipients, add the
                email addresses separated by comma. Example: "info@test.com,
                support@test.com"</span
              >
            </div>

            <div class="field-settings-label border-bottom" for="email-subject">
              <div class="flex">
                <label class="settings-label_140"> Email subject: </label>
                <input
                  type="text"
                  name="flo_form_settings[email-subject]"
                  id="email-subject"
                  v-bind:value="formSettings['email-subject']"
                />
              </div>
              <span class="tab-title hint">
                You can use any of the existing fields value by using
                %field_id%. For example: %fid_95908% - where fid_95908 is the ID
                of one of the form’s input fields. To find out the ID of a
                field, just click on it in the preview on the right and it will
                show on the left side at the very bottom before the Save button.
              </span>
            </div>

            <div class="field-settings-label border-bottom">
              <div class="flex">
                <label class="settings-label_140"> Form columns: </label>
                <select @change="changeFormColumns">
                  <option
                    value="1"
                    :selected="schema.groups.length == 2 ? 'selected' : ''"
                  >
                    One Column
                  </option>
                  <option
                    value="2"
                    :selected="schema.groups.length == 3 ? 'selected' : ''"
                  >
                    Two Columns
                  </option>
                </select>
              </div>
            </div>

            <div
              class="field-settings-label field-settings-label--confirmation-options border-bottom"
            >
              Confirmation options: <br />
              <br />

              <div class="field-settings-label--wrapper flex">
                <label
                  class="confirmation-label confirmation-label--text"
                  :class="{
                    selected:
                      formSettings.confirmation_opt == 'text_confirmation',
                  }"
                >
                  <input
                    type="radio"
                    class="confirmation_opt"
                    name="flo_form_settings[confirmation_opt]"
                    value="text_confirmation"
                    v-model="formSettings.confirmation_opt"
                  />
                  <div class="text-wrapper">Show a text message</div>
                </label>

                <label
                  class="confirmation-label confirmation-label--page"
                  :class="{
                    selected:
                      formSettings.confirmation_opt == 'page_confirmation',
                  }"
                >
                  <input
                    type="radio"
                    class="confirmation_opt"
                    name="flo_form_settings[confirmation_opt]"
                    value="page_confirmation"
                    v-model="formSettings.confirmation_opt"
                  />
                  <div class="text-wrapper">Redirect to a page</div>
                </label>
              </div>

              <div
                class="text-confirmation-container"
                v-show="formSettings.confirmation_opt == 'text_confirmation'"
              >
                <textarea
                  name="flo_form_settings[text_confirmation_value]"
                  class="text-confirmation-value "
                  style="display: inline-block;"
                  v-model="formSettings.text_confirmation_value"
                >
Thank you!</textarea
                >
              </div>

              <div
                class=""
                v-show="formSettings.confirmation_opt == 'page_confirmation'"
              >
                <input
                  type="text"
                  class="flo-suggest-page width_100"
                  placeholder="Start typing the page title"
                  name="flo_form_settings[confimation_page_title]"
                  v-model="formSettings.confimation_page_title"
                />

                <div class="tab-title hint">
                  Or use an URL if it is an external link.
                </div>
                <input
                  type="hidden"
                  class="confimation-page-id"
                  name="flo_form_settings[confimation_page]"
                  v-model="formSettings.confimation_page"
                />
              </div>
            </div>

            <div
              class="field-settings-label field-settings-label--email-confirmation-options flo-fancy-checkbox"
            >
              <label class="flex jc-space-between">
                <div class="settings-label">
                  Send Confirmation Email
                </div>

                <div class="switch">
                  <input
                    type="checkbox"
                    name="flo_form_settings[enable_email_confirmation]"
                    value="1"
                    v-model="formSettings.enable_email_confirmation"
                  />
                  <span class="checkbox-slider round"></span>
                </div>
              </label>

              <div
                class="email-confirmation-container"
                v-show="formSettings.enable_email_confirmation == '1'"
              >
                <div class="flex">
                  <label class="settings-label_140">
                    Email To
                  </label>
                  <input
                    type="text"
                    name="flo_form_settings[email_confirmation_to]"
                    v-model="formSettings.email_confirmation_to"
                  />
                </div>
                <span class="tab-title hint">
                  If %field_email% is used, then the confirmation email will be
                  sent to the email entered by the user in the ‘email field’, if
                  such field is available in the form.
                </span>
                <div class="flex ff_margin_bottom_20">
                  <label class="settings-label_140">
                    Email Subject
                  </label>
                  <input
                    type="text"
                    name="flo_form_settings[email_confirmation_subject]"
                    v-model="formSettings.email_confirmation_subject"
                  />
                </div>

                <div class="ff_margin_bottom_10">
                  Email message
                </div>
                <textarea
                  name="flo_form_settings[email_confirmation_value]"
                  id="email-confirmation-value"
                  class="text-confirmation-value email-confirmation-value"
                  style="display: inline-block;"
                  v-model="formSettings.email_confirmation_value"
                >
%all_fields%</textarea
                >
                <span class="tab-title hint">
                  Use %all_fields% as a short code if you want to show all
                  submitted form fields. Also you can use any of the existing
                  fields value by using %field_id%. For example: %fid_95908% -
                  where fid_95908 is the ID of one of the form’s input fields.
                  To find out the ID of any field, just click on it in the
                  preview on the right and it will show on the left side at the
                  very bottom before the Save button. Additionally, any regular
                  text message can be used to replace or complement the
                  submitted fields.
                </span>
              </div>
            </div>
          </div>

          <div
            class="form-settings--integrations"
            v-show="formSettingsActiveTab == 'integrations'"
          >
            <!--as an example check the Slack integration-->
            <!--the 'flo_forms_integrations' filter is used to register a new integration-->

            <div
              v-if="Object.keys(formIntegrations).length == 0"
              class="form-settings--integrations-no_integrations"
            >
              There are no integrations available. Integrations with other
              services can be added via additional plugins.
            </div>
            <div
              v-else
              v-for="(integration, interg_index) in formIntegrations"
              :key="interg_index"
              :class="integration.wrapper_class"
              class="colapsable-option--outer-container"
            >
              <div
                class="colapsable-option "
                :class="'colapsable-option_' + interg_index"
              >
                <div
                  class="settings-label settings-label--big ff_margin_bottom_20"
                >
                  {{ integration.title }}
                </div>

                <div
                  class="font-option--open-close dashicons-arrow-down-alt2"
                  @click="openCloseColapsableOption(interg_index)"
                ></div>
                <div class="colapsable-option--wrapper">
                  <component
                    v-bind:is="integration.custom_tag"
                    v-bind:form-schema="JSON.stringify(schema)"
                  ></component>
                </div>
              </div>
            </div>

            <a
              class="form-settings--integrations-linkall"
              href="https://flothemes.com/floforms/"
              target="_blank"
            >
              View all available integrations
            </a>
          </div>
        </div>

        <div id="flo-publishing-action">
          <span class="spinner"></span>
          <input
            name="original_publish"
            type="hidden"
            id="flo_original_publish"
            value="Update"
          />

          <div
            class="flo-hotmail-warning"
            v-if="formSettings['send-to-email'].includes('@hotmail.com')"
          >
            You are using a <u>Hotmail</u> adress as the recipient email. There
            are known delivery issues to Hotmail. Use please another email
            address to avoid email delivery problems.
          </div>

          <input
            name="save"
            type="button"
            class="button button-primary button-long button-rounded"
            id="flo_publish"
            value="Save Form"
          />
        </div>
      </div>
    </div>

    <div
      class="row form-preview topLabel "
      v-bind:class="{ 'two-columns': schema.groups.length == 3 }"
    >
      <a
        v-if="isProVersion != 1"
        href="https://help.flothemes.com/category/427-floforms"
        target="_blank"
        class="forms-docs-link-icn forms-docs-link-icn--free dashicons dashicons-info"
        title="Documentation"
      >
      </a>

      <div
        class="no-fields-msg"
        v-if="
          schema.groups.length == 2 &&
            !schema.groups[0].fields.length &&
            newFormState
        "
      >
        <div
          class="no-fields-msg--start-designing"
          v-bind:class="getPluginVersionClass()"
          v-if="!newFormState || isProVersion != 1"
        >
          <h2 class="start-title">Start Designing</h2>
          <img v-bind:src="pluginDirUrlAdmin + 'images/start-example.png'" />
          <div class="start-form-note">
            Use the panel on the left to start creating your Form
          </div>
        </div>

        <!--the start tabs for the pro version-->
        <div
          v-bind:class="getPluginVersionClass()"
          class="start-how-to-tabs"
          v-if="this.isProVersion == 1"
        >
          <div id="how-it-works" class="start-how-to-tabs--content">
            <div class="title">
              A quick review of your FloForms plugin
            </div>
            <div class="text">
              Watch the video below to check out what options and settings are
              inside the FloForm plugin. This video goes over the main areas of
              the plugin including free and Pro features
            </div>
            <div class="video" style="text-align: center;">
              <iframe
                width="560"
                height="315"
                src="https://www.youtube.com/embed/8PULnBCdFzQ"
                frameborder="0"
                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
            </div>
          </div>
        </div>
      </div>
      <div>
        <div
          v-bind:class="{
            hidden:
              schema.groups.length == 2 &&
              !schema.groups[0].fields.length &&
              newFormState,
          }"
        >
          <!-- we need this wrapper div without any class here in order to have the tabs working-->
          <div class="preview-tabs">
            <ul v-if="isProVersion == 1" class="preview-tabs--wrapper">
              <li class="form-design">
                <a href="#form-design">
                  <span class="icon-2"></span>
                  Form Design
                </a>
                <a
                  href="https://help.flothemes.com/category/427-floforms"
                  target="_blank"
                  class="forms-docs-link-icn dashicons dashicons-info"
                  title="Documentation"
                >
                </a>
              </li>
              <li class="pick-template">
                <a href="#pick-template">
                  <span class="icon-Combined-Shape"></span>
                  Pick a Template
                </a>
              </li>
            </ul>

            <div id="form-design">
              <div
                class="no-fields-msg--start-designing"
                v-if="
                  schema.groups.length == 2 && !schema.groups[0].fields.length
                "
              >
                <h2 class="start-title">Start Designing</h2>
                <img
                  v-bind:src="pluginDirUrlAdmin + 'images/start-example.png'"
                />
                <div class="start-form-note">
                  Use the panel on the left to start creating your Form
                </div>
              </div>

              <vue-form-generator
                v-if="
                  !(
                    schema.groups.length == 2 && !schema.groups[0].fields.length
                  )
                "
                :schema="schema"
                :model="model"
                tag="section"
                :options="formOptions"
                :style="
                  '--data-btn-color: ' +
                    btnTextColorValue +
                    '; --data-btn-bgcolor: ' +
                    btnBgColorValue +
                    '; --data-btn-border-color: ' +
                    btnBorderColorValue +
                    '; --data-btn-color-hover: ' +
                    btnTextColorValueHover +
                    '; --data-btn-bgcolor-hover: ' +
                    btnBgColorValueHover +
                    '; --data-btn-border-color-hover: ' +
                    btnBorderColorValueHover +
                    '; --data-inputbg-color: ' +
                    inputbgColorValue +
                    '; --data-label-color: ' +
                    labelColorValue +
                    '; --data-border-color: ' +
                    borderColorValue +
                    '; --data-input-color: ' +
                    inputColorValue +
                    '; --data-placeholder-color: ' +
                    placeholderColorValue +
                    '; --data-hint-color: ' +
                    hintColorValue +
                    '; --data-formbg-color: ' +
                    formbgColorValue +
                    ';'
                "
                :class="
                  'btn-position--' +
                    btnPosition +
                    ' ' +
                    getStylingClass(formSettings)
                "
              ></vue-form-generator>

              <div
                class="form-bottom-actions"
                :class="{ 'two-options': isProVersion == 1 }"
                v-if="
                  !(
                    schema.groups.length == 2 &&
                    schema.groups[0].fields.length == 0
                  )
                "
              >
                <div
                  v-if="floFormId && floFormId != '0'"
                  class="form-shortcode-preview"
                >
                  <div class="form-bottom-actions--block-title">
                    Copy Form Shortcode
                  </div>
                  <div class="form-bottom-actions--block-text">
                    You can use the following shortcode in any page or post:
                  </div>

                  <div class="form-shortcode-preview--wrapper">
                    <input
                      type="text"
                      class="shortcode-input"
                      :value="'[floform id=' + floFormId + ']'"
                      readonly
                    />
                    <input
                      type="button"
                      class="shortcode-input--copy"
                      value="Copy"
                    />
                  </div>
                  <span class="shortcode-input--copy-msg"
                    >The shortcode was copied.</span
                  >
                </div>

                <div v-if="isProVersion == 1" class="flo-form-save-template">
                  <div
                    class=" save-template--open-close"
                    v-show="
                      schema.groups.length >= 2 &&
                        schema.groups[0].fields.length
                    "
                  >
                    <div class="form-bottom-actions--block-title">
                      Save To Templates
                    </div>
                    <div class="form-bottom-actions--block-text">
                      Want to save the current form as a template for future use
                      or backup ?
                    </div>
                  </div>
                  <div class="hide-if-no-js save-template-container">
                    <div class="template-info">
                      <div class="template-info--title">
                        <label class="template-info--wrapper">
                          <input
                            type="text"
                            placeholder="Type the Name First"
                            class="form-template-name"
                          />
                          <input
                            type="button"
                            class="form-template-save button-primary"
                            value="Save"
                          />
                        </label>
                      </div>

                      <span class="spinner save-template-spinner"></span>
                    </div>

                    <div class="form-template-messages-container"></div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="isProVersion == 1" id="pick-template">
              <div class="pick-template--group-title">
                Predefined templates <span class="icon-flo-ctrl"></span>
              </div>
              <div class="pick-template--templates-preview">
                <div
                  v-for="template in predefinedTemplates"
                  class="pick-template--templates-preview_element predefined-template"
                >
                  <div
                    class="pick-template--templates-preview_element-img"
                    :style="{
                      backgroundImage:
                        'url(' +
                        encodeURI(
                          pluginDirUrlAdmin +
                            '../pro/predefined-templates/img/' +
                            template.preview_url
                        ) +
                        ')',
                    }"
                  ></div>
                  <div
                    class="apply-template-btn"
                    v-on:click="flo_apply_template(template.groups, true, true)"
                  >
                    Apply Template
                  </div>
                </div>

                <!--<h3 v-if="Object.keys(userSavedTemplates).length > 0" class="saved-etmplates-title">Saved Form Templates:</h3>-->
                <div
                  v-if="Object.keys(userSavedTemplates).length > 0"
                  class="pick-template--group-title saved-etmplates-title"
                >
                  Your Templates<span class="icon-flo-ctrl"></span>
                </div>
                <div
                  v-for="(template, template_index) in userSavedTemplates"
                  class="pick-template--templates-preview_element saved-template"
                >
                  <div
                    class="pick-template--templates-preview_element-img"
                    :style="{
                      backgroundImage:
                        'url(' + encodeURI(template.img_src) + ')',
                    }"
                  ></div>
                  <div class="template-title">
                    {{ template.template_title }}
                  </div>

                  <div
                    class="apply-template-btn"
                    v-on:click="
                      flo_apply_template(template.schema.groups, true, true)
                    "
                  >
                    Apply Template
                  </div>

                  <div
                    class="remove-saved-templace dashicons dashicons-trash"
                    @click="removeUserSavedTemplate(template_index)"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <textarea
        class="flo-form-data"
        v-if="model"
        v-html="model"
        name="flo-form-model"
      ></textarea>
      <textarea
        class="flo-form-data flo-form-schema"
        v-if="schema.groups[0].fields.length"
        v-html="schema"
        name="flo-form-schema"
      >
      </textarea>
      <!-- the value of this field is changed programatically when the fields are reordered -->
      <!-- the value it contains doesn not matter because the window.flo_form_fields is really used -->
      <input
        type="text"
        name=""
        class="schema-fields-ids flo-form-data"
        data-group_id="0"
        value=""
        @input="maybeSortSchema"
      />
    </div>
  </div>
</template>

<script src="./FieldsJs.vue"></script>
