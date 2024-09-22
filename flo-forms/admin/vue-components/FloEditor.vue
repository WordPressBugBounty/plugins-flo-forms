<template>
  <div class="editor">
    <editor-menu-bubble class="menububble" :editor="editor" @hide="hideLinkMenu" v-slot="{ commands, isActive, getMarkAttrs, menu }">
      <div
        class="menububble"
        :class="{ 'is-active': menu.isActive }"
        :style="`left: ${menu.left}px; bottom: ${menu.bottom}px;`"
      >

        <form class="menububble__form" v-if="linkMenuIsActive" @submit.prevent="setLinkUrl(commands.link, linkUrl)">
          <input class="menububble__input" type="text" v-model="linkUrl" placeholder="https://" ref="linkInput" @keydown.esc="hideLinkMenu"/>
          <button class="menububble__button" @click="setLinkUrl(commands.link, null)" type="button">
            <span class="dashicons dashicons-no-alt"></span>
          </button>
        </form>

        <template v-else>
          <button
            class="menububble__button"
            @click="showLinkMenu(getMarkAttrs('link'))"
            :class="{ 'is-active': isActive.link() }"
          >
            <span>{{ isActive.link() ? 'Update Link' : 'Add Link'}}</span>
            &nbsp;<span class="dashicons dashicons-admin-links"></span>
          </button>
        </template>

      </div>
    </editor-menu-bubble>

    <editor-content class="editor__content" :editor="editor" />
  </div>
</template>

<script>

import { Link } from 'tiptap-extensions'
// extend the Link element and make a custom one that has target="_blank" by default
export  class FloCustomLink extends Link {
  get schema() {
    return {
      attrs: {
        href: {
            default: null,
        },
        target: {
            default: null,
        }
      },
      inclusive: false,
      parseDOM: [
        {
          tag: 'a[href]',
          getAttrs: dom => ({
            href: dom.getAttribute('href'),
            target: dom.getAttribute('target')
          }),
        },
      ],
      toDOM: node => ['a', {
        ...node.attrs,
        target: '__blank',
        rel: 'noopener noreferrer nofollow',
      }, 0],
    }
  }

}


import { Editor, EditorContent, EditorMenuBubble } from 'tiptap'
import {
  Bold,
  Italic,
  //Link,
  History
} from 'tiptap-extensions'


export default {
  components: {
    EditorContent,
    EditorMenuBubble,
  },
  data() {

    return {
      editor: null,
      editorChange: false,
      linkUrl: null,
      linkMenuIsActive: false,
    };
  },
  methods: {
    showLinkMenu(attrs) {
      this.linkUrl = attrs.href
      this.linkMenuIsActive = true
      this.$nextTick(() => {
        this.$refs.linkInput.focus()
      })
    },
    hideLinkMenu() {
      this.linkUrl = null
      this.linkMenuIsActive = false
    },
    setLinkUrl(command, url) {
      command({ href: url })
      
      this.hideLinkMenu()
    },
    getCleanText(html) {
      // we want to get rid off the <p> because they tend to inherit styling from the themes
      // therefore we replace all paragraphs with DIVs

      html = html.replace(/<p>/g,"<div>");
      html = html.replace(/<\/p>/g,"</div>");

      return html;
    }
  },

  mounted() {
    this.editor = new Editor({
      onUpdate: ({ state, getHTML, getJSON }) => {
        this.editorChange = true;
        this.$emit("input", this.getCleanText(getHTML()));
      },
      content: this.value,
      emptyDocument: {
        type: 'doc',
        content: [{
          type: 'Text',
        }],
      },
      extensions: [
        new FloCustomLink(),
        //new Link(),
        new History(),
      ]
    });
  },

  props: ['value'],

  watch: {
    value(val) {
      if (this.editor && !this.editorChange) {
        this.editor.setContent(val, true);
      }
      this.editorChange = false;
    }
  }
}
</script>
