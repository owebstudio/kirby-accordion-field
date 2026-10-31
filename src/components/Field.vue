<template>
  <k-field
      v-bind="$props"
      :input="id"
      class="k-accordion-field"
  >
    <template v-if="resetable || items.length > 1" #options>
      <k-button-group layout="collapsed">
        <k-button
            v-if="resetable"
            icon="undo"
            text="Reset"
            size="xs"
            variant="filled"
            @click="resetForm"
        />
        <k-button
            v-if="items.length > 1"
            :icon="isAllOpen ? 'collapse' : 'expand'"
            :title="$t(isAllOpen ? 'collapse.all' : 'expand.all')"
            size="xs"
            variant="filled"
            @click="toggleAll"
        />
      </k-button-group>
    </template>
    <details
        v-for="fieldset in items"
        :key="fieldset.name"
        :data-name="fieldset.name"
        :open="isOpen(fieldset.name)"
        class="k-accordion-details"
        @toggle="onToggle(fieldset.name, $event)"
    >
      <summary
          class="k-accordion-summary"
          @click.prevent="toggleFieldset(fieldset.name)"
      >
        <k-icon v-if="fieldset.icon" :type="fieldset.icon" class="k-accordion-icon"/>
        <span class="k-accordion-label">{{ fieldset.label }}</span>
        <span v-if="fieldset.headline" class="k-accordion-headline">{{ headlineFieldset(fieldset) }}</span>
        <k-icon type="alert" class="k-accordion-invalid"/>
      </summary>
      <k-fieldset
          :ref="fieldset.name"
          :disabled="disabled"
          :fields="fieldset.fields"
          :value="object"
          @input="onInput"
          @submit="onSubmit"
      />
    </details>
  </k-field>
</template>

<script>
import { useId } from "vue";

export default {
  inheritAttrs: false,
  props: {
    default: {
      type: [Object, Array],
      default() {
        return {};
      }
    },
    disabled: Boolean,
    endpoints: Object,
    fieldsets: {
      type: [Object, Array],
      default() {
        return {};
      }
    },
    hasDiff: Boolean,
    help: String,
    id: {
      type: [Number, String],
      default: () => useId()
    },
    label: String,
    name: String,
    preview: String,
    required: Boolean,
    reset: {
      type: Boolean,
      default: false
    },
    toggle: {
      type: Boolean,
      default: true
    },
    translate: Boolean,
    type: String,
    value: {
      type: [Object, Array],
      default() {
        return {};
      }
    }
  },
  emits: ["input", "submit"],
  data() {
    return {
      object: {},
      open: {}
    };
  },
  computed: {
    isAllOpen() {
      return this.items.every(fieldset => this.isOpen(fieldset.name));
    },
    items() {
      return Object.values(this.fieldsets).map(fieldset => ({
        ...fieldset,
        fields: this.subfields(fieldset.fields)
      }));
    },
    resetable() {
      return this.reset === true && this.disabled === false && this.$helper.object.length(this.default) > 0;
    }
  },
  watch: {
    value: {
      handler(value) {
        this.object = {...value ?? {}};
      },
      immediate: true
    },
    "$panel.dialog.component"(component) {
      // open fieldsets with errors when saving fails
      if (component === "k-validation-error-dialog") {
        this.openInvalid(this.$panel.dialog.props?.fields?.[this.name]);
      }
    }
  },
  methods: {
    onInput(value) {
      this.$emit("input", {...value});
    },
    onSubmit(event) {
      this.$emit("submit", event);
    },
    onToggle(name, event) {
      // keep the state in sync when the browser opens a fieldset,
      // e.g. for a match of the page search
      if (event.target.open !== this.isOpen(name)) {
        this.setOpen(name, event.target.open);
      }
    },
    headlineFieldset(fieldset) {
      const headline = this.$helper.string.template(fieldset.headline, this.object);

      if (headline === "…") {
        return "";
      }

      // values are inserted unescaped, so render them as plain text
      return this.$helper.string.unescapeHTML(this.$helper.string.stripHTML(headline));
    },
    isOpen(name) {
      return this.open[name] ?? this.fieldsets[name]?.open === true;
    },
    openInvalid(error) {
      const message = Object.values(error?.message ?? {}).join("\n");

      for (const fieldset of this.items) {
        const details = this.$el.querySelector(`:scope > .k-accordion-details[data-name="${CSS.escape(fieldset.name)}"]`);

        if (details?.matches(":has(:invalid)") || message.includes("(" + fieldset.label + " > ")) {
          this.setOpen(fieldset.name, true);
        }
      }
    },
    resetForm() {
      this.$emit("input", {...this.object, ...this.default});
    },
    setOpen(name, open) {
      this.open[name] = open;
    },
    subfields(fields) {
      const subfields = {};

      for (const name in fields) {
        subfields[name] = {...fields[name]};

        if (this.endpoints) {
          subfields[name].endpoints = {
            field: this.endpoints.field + "+" + name,
            model: this.endpoints.model
          };
        }
      }

      return subfields;
    },
    toggleAll() {
      const open = this.isAllOpen === false;

      for (const fieldset of this.items) {
        this.setOpen(fieldset.name, open);
      }
    },
    toggleFieldset(name) {
      const open = this.isOpen(name) === false;

      // close other fieldsets
      if (open === true && this.toggle === true) {
        for (const fieldset of this.items) {
          if (fieldset.name !== name && this.isOpen(fieldset.name)) {
            this.setOpen(fieldset.name, false);
          }
        }
      }

      this.setOpen(name, open);

      // focus first field
      if (open === true) {
        this.$nextTick(() => {
          const fieldset = this.$refs[name];
          (Array.isArray(fieldset) ? fieldset[0] : fieldset)?.focus();
        });
      }
    }
  }
}
</script>

<style>
.k-accordion-field details {
  position: relative;
  border-bottom: 1px dashed var(--item-color-back);
}

.k-accordion-field details summary {
  --size: var(--text-md);
  font-size: var(--size);
  font-weight: var(--font-bold);
  line-height: 1.5em;
  align-items: center;
  min-width: 0;
  padding-inline-end: .75rem;
}

.k-accordion-icon {
  display: inline-flex;
  vertical-align: middle;
  margin-inline-end: .5rem;
  color: var(--color-text-dimmed);
}

.k-accordion-label {
  margin-inline-end: .5rem;
}

.k-accordion-headline {
  color: var(--color-text-dimmed);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: var(--text-sm);
}

.k-accordion-invalid {
  display: none;
  float: inline-end;
  color: var(--color-red-600);
}

.k-accordion-details:has(:invalid) > summary .k-accordion-invalid {
  display: inline-flex;
}

.k-accordion-field details .k-fieldset {
  padding: 1.5rem 1.5rem 2rem;
  border-left: 1px dashed var(--item-color-back);
  border-right: 1px dashed var(--item-color-back);
}

@keyframes details-show {
  from {
    opacity: 0;
    transform: var(--details-translate, translateY(-0.5em));
  }
}

.k-accordion-field details[open] > *:not(summary) {
  animation: details-show 250ms ease-in-out;
}

.k-accordion-field summary {
  padding: .75rem;
  background: var(--item-color-back);
}

.k-accordion-field summary:hover {
  cursor: pointer;
}
</style>
