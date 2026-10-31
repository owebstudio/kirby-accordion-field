<template>
  <k-field
      v-bind="$props"
      :input="id"
      class="k-accordion-field"
  >
    <template v-if="resetable" #options>
      <k-button-group layout="collapsed">
        <k-button
            icon="undo"
            text="Reset"
            size="xs"
            variant="filled"
            @click="resetForm"
        />
      </k-button-group>
    </template>
    <details
        v-for="fieldset in items"
        :key="fieldset.name"
        :open="isOpen(fieldset.name)"
        class="k-accordion-details"
        @toggle="onToggle(fieldset.name, $event)"
    >
      <summary
          class="k-accordion-summary"
          @click.prevent="toggleFieldset(fieldset.name)"
      >
        <span class="k-accordion-label">{{ fieldset.label }}</span>
        <span v-if="fieldset.headline" class="k-accordion-headline">{{ headlineFieldset(fieldset) }}</span>
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
