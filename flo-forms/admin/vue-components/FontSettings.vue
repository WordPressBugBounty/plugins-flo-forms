<script>
import Vue from "vue";
window.Vue = Vue;

import FontPicker from "font-picker-vue";

import _ from "lodash";
// this prevents the conflict between underscore js and lodash
// the global _ will now be lodash
window.lodash = _.noConflict();

export default {
  components: {
    "font-picker": FontPicker
  },

  computed: {
    groupedCustomFonts: function() {
      // need to group the fonts with the same name but different extensions(file type)
      let grouped_custom_fonts = _.groupBy(this.customFonts, function(fontEl) {
        return fontEl.name;
      });

      // we need to inject the font face styles
      Object.keys(grouped_custom_fonts).forEach((c_font, font_name) => {
        let customFonFaceStyle = this.floGetCustomFontface(
          c_font,
          grouped_custom_fonts[c_font]
        );

        // append the customFonFaceStyle in the dom
        jQuery(".app-font-settings").after(customFonFaceStyle);
      });

      return grouped_custom_fonts;
    }
  },

  methods: {
    /*when a Google font is selected from the font-picker dropdown
      the active font is updated and the related font style file is loaded*/
    onGoogleFontsChange: function(selectedFont, gf_index) {
      let ff_name = selectedFont.family.replace(" ", "+"), // replace the space with the '+'
        font_style_url =
          "https://fonts.googleapis.com/css?family=" + ff_name + ":regular";

      this.gFonts[gf_index].activeFont = selectedFont.family;
      this.gFonts[gf_index].font_styles_url = font_style_url;
    },

    addGoogleFont: function() {
      //console.log('this.gFonts: ',this.gFonts)

      let g_font_id = Math.floor(Math.random() * 9999); // random id for the component
      this.gFonts.push({
        name: "gf" + g_font_id,
        activeFont: "Open Sans",
        font_styles_url:
          "https://fonts.googleapis.com/css?family=Open+Sans:regular",
        id: g_font_id
      });
    },

    removeGoogleFont: function(font_id) {
      this.gFonts.splice(font_id, 1);
    },

    /*delete  a custom font*/
    floDeleteFontFormat: function(format) {
      let indexOfRemoved = _.findIndex(this.customFonts, format);
      // remove the element at the given index
      this.customFonts.splice(indexOfRemoved, 1);
    },

    floFormsUploadFont: function() {
      var typesPermitted = [
          "ttf",
          "woff",
          "woff2",
          "svg",
          "eot",
          "otf"
        ] /*allowed file types*/,
        multiple_file_frame;

      let that = this;

      multiple_file_frame = wp.media({
        frame: "select",
        multiple: true
      });

      multiple_file_frame.on("select", function() {
        var selection = multiple_file_frame
          .state()
          .get("selection")
          .toJSON();

        selection.forEach(function(el, index, array) {
          var name = el.filename;

          var f = name.split(".");

          var format = _.last(f);
          format = format.toLowerCase();

          if (_.indexOf(typesPermitted, format) == -1) {
            return true;
          }

          var name = _.last(el.url.split("/")).split(".")[0];

          var font = {
            name: name,
            format: format,
            url: el.url
          };

          // add the new font
          that.customFonts.push(font);
        });

        that.customFonts = _.uniq(that.customFonts, function(font) {
          return font.url;
        });
      });

      multiple_file_frame.open();
    },

    floGetCustomFontface: function(name, fonts) {
      //console.log(name, fonts, ' hopa');
      var fontface = "";

      fontface += "@font-face {";
      fontface += 'font-family: "' + name + '"; ';

      var lines = [];

      var newFont = _.map(fonts, function(font) {
        var line = "";

        switch (font.format) {
          case "otf":
            line = "url('" + font.url + "') format('opentype')";
            break;
          case "svg":
            line = "url('" + font.url + "') format('svg')";
            break;
          case "ttf":
            line = "url('" + font.url + "') format('truetype')";
            break;
          case "woff":
            line = "url('" + font.url + "') format('woff')";
            break;
          case "woff2":
            line = "url('" + font.url + "') format('woff2')";
            break;
          default:
          //
        }

        lines.push(line);

        return font;
      });

      // Note! eot fonts are problematic. Review this or not use at all
      var eot = _.find(fonts, function(o) {
        return o.format == "eot";
      });

      if (typeof eot != "undefined") {
        fontface += "src: url('" + eot.url + "');";
      }

      fontface += "src:";

      fontface += lines.join();
      fontface += ";";

      fontface += "}";

      return "<style>" + fontface + "</style>";
    }
  },

  data: function() {
    return {
      gFonts:
        typeof flo_forms_google_fonts !== "undefined"
          ? flo_forms_google_fonts
          : [],
      customFonts:
        typeof flo_forms_custom_fonts !== "undefined"
          ? flo_forms_custom_fonts
          : []
    };
  }
};
</script>

<template>
  <div class="form-fonts">
    <div
      class="form-fonts--google-font-picker"
      v-for="(g_font, font_id) in gFonts"
    >
      <span
        class="dashicons dashicons-trash form-fonts--delete-g-font"
        @click="removeGoogleFont(font_id)"
      ></span>

      <font-picker
        :api-key="'AIzaSyAIUDzM1DALL_senA3bOW3JKslISykPoyA'"
        :options="{ name: 'gf' + g_font.id, limit: 150 }"
        :active-font="g_font.activeFont"
        @change="onGoogleFontsChange($event, font_id)"
      ></font-picker>

      <div v-bind:class="'apply-font-gf' + g_font.id" class="apply-font">
        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
        veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea
        commodo.
      </div>
    </div>

    <div
      class="form-fonts--add-font"
      @click="addGoogleFont"
      v-if="!gFonts || gFonts.length < 6"
    >
      <!--allow max 6 google fonts-->
      <span class="form-fonts--add-font_circle">+</span>
      Add Google Font
    </div>

    <br />

    <h4>Custom fonts:</h4>
    <div class="">
      Use the same file name when you are adding multiple files of the same
      font. It is recommended to have at least a WOFF file and a TTF file for
      the same font
    </div>
    <br />

    <div class="form-fonts--custom-fonts-wrapper" v-if="customFonts.length">
      <div
        class="form-fonts--custom-font"
        v-for="(c_font, font_name) in groupedCustomFonts"
      >
        <div class="form-fonts--custom-entry-name">
          <span v-bind:style="'font-family:' + font_name + ';'">{{
            font_name
          }}</span>
        </div>
        <div class="form-fonts--custom-format-wrapper">
          <div class="form-fonts--custom-format" v-for="format in c_font">
            <span>
              {{ format.format }}
            </span>
            <div
              class="form-fonts--custom-delete-font dashicons dashicons-no-alt"
              @click="floDeleteFontFormat(format)"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-fonts--custom-add-font" @click="floFormsUploadFont">
      ADD CUSTOM FONT
    </div>

    <textarea
      class="flo-form-data"
      v-if="gFonts && gFonts.length"
      v-html="gFonts"
      name="flo_forms_options[google_fonts]"
    ></textarea>
    <textarea
      class="flo-form-data"
      v-if="customFonts && customFonts.length"
      v-html="customFonts"
      name="flo_forms_options[custom_fonts]"
    ></textarea>
  </div>
</template>
