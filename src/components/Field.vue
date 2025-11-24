<template>
  <k-field
      :input="_uid"
      class="k-accordion-field"
      v-bind="$props"
  >
    <template #options>
      <k-button-group v-if="resetable" class="k-field-options">
        <k-button
            icon="undo"
            text="Reset"
            class="k-field-options-button"
            @click="resetForm"
        />
      </k-button-group>
    </template>
    <details
        class="k-accordion-details"
        v-for="(fieldset, key) in formFields"
        :key="'accordion_' + _uid"
        :open="fieldset.open"
    >
      <summary
          class="k-accordion-summary"
          @click="toggleFieldset(fieldset)"
      >
        <span v-html="fieldset.label" class="k-accordion-label"/>
        <span v-html="headlineFieldset(fieldset)" v-if="fieldset.headline" class="k-accordion-headline"/>
      </summary>
      <k-fieldset
          :ref="fieldset.name"
          :key="'fieldset_' + _uid"
          :fields="fieldset.fields"
          :novalidate="false"
          :validate="true"
          :value="value"
          v-on="$listeners"
          @input="onInput"
          @submit="onSubmit"
      />
    </details>
  </k-field>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    default: {
      type: Object,
      default() {
        return {};
      }
    },
    disabled: Boolean,
    endpoints: Object,
    fieldsets: {
      type: Object,
      default() {
        return {};
      }
    },
    name: String,
    label: String,
    preview: String,
    reset: {
      type: Boolean,
      default: false
    },
    toggle: {
      type: Boolean,
      default: true
    },
    value: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  computed: {
    formFields() {
      Object.keys(this.fieldsets).forEach(key => {
        let fieldset = this.fieldsets[key];

        Object.keys(fieldset.fields ?? []).forEach(name => {
          let field = fieldset.fields[name];

          field.section = this.name;
          field.endpoints = {
            field: this.endpoints.field + "+" + name,
            section: this.endpoints.section,
            model: this.endpoints.model
          };

          this.fieldsets[key].fields[name] = field;
        });
      });

      return this.fieldsets;
    },
    resetable() {
      return this.reset === true && this.default && Object.keys(this.default).length > 0;
    }
  },
  methods: {
    onInput() {
      this.$emit("input", this.value);
    },
    onSubmit() {
      this.$emit("submit", this.value);
    },
    headlineFieldset(fieldset) {
      const headline = this.$helper.string.template(fieldset.headline, this.value);
      return headline === "…" ? false : headline;
    },
    toggleFieldset(fieldset) {
      if (fieldset.open === false) {
        if (this.toggle === true) {
          const details = this.$el.querySelectorAll(".k-accordion-field details");

          details.forEach((target) => {
            target.addEventListener("toggle", () => {
              if (target.open === true) {
                details.forEach(detail => {
                  // close other fieldsets
                  if (target !== detail && detail.open) detail.open = false
                });
              }
            });
          });
        }
      }

      // focus first field
      if (this.$refs[fieldset.name]) {
        setTimeout(() => {
          this.$refs[fieldset.name][0].focus();
        }, 10);
      }
    },
    resetForm() {
      this.value = {...this.value ?? {}, ...this.default ?? {}};
      this.onInput();
    }
  }
}
</script>

<style lang="scss">
.k-accordion-field details {
  position: relative;
  border-bottom: 1px dashed var(--item-color-back);
}

.k-accordion-field details summary {
  --size: var(--text-base);
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
  color: var(--color-gray-600);
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
  background: var(--color-white);
}

[data-theme="dark"] .k-accordion-field summary {
  background: var(--item-color-back);
}

.k-accordion-field summary:hover {
  cursor: pointer;
}

/* fix multiple error lines for a field */
.k-error-dialog .k-error-details ul > li {
  white-space: pre-line;
}
</style>
