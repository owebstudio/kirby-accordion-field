<template>
  <k-field
      :input="_uid"
      class="k-accordion-field"
      v-bind="$props"
  >
    <details
        :id="_uid"
        class="k-accordion-details"
        v-for="(fieldset, key) in fieldsets"
        :key="'accordion_' + key"
        :open="fieldset.open"
    >
      <summary
          class="k-accordion-summary"
          v-html="fieldset.label"
          @click="toggleFieldset(fieldset)"
      />
      <slot>
        <div class="k-accordion-form">
          <k-form
              :ref="fieldset.name"
              :autofocus="true"
              :fields="fieldset.fields"
              :novalidate="false"
              :validate="true"
              v-model="model"
              v-on="$listeners"
              @input="onInput"
          />
        </div>
      </slot>
    </details>
  </k-field>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    fieldsets: Object,
    label: String,
    toggle: {
      type: Boolean,
      default: true
    },
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  watch: {
    value(value) {
      if (value !== this.model) {
        this.model = value;
      }
    }
  },
  methods: {
    onInput() {
      this.$emit("input", this.model);
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
  },
  data() {
    return {
      model: this.value
    };
  }
}
</script>

<style lang="scss">
.k-accordion-field details {
  position: relative;
  border-bottom: 1px dashed rgba(#000, .1);
}

.k-accordion-field details .k-accordion-summary {
  --size: var(--text-base);
  font-size: var(--size);
  font-weight: var(--font-bold);
  line-height: 1.5em;
}

.k-accordion-field details .k-accordion-form {
  padding: 1.5rem 1.5rem 2rem;
  border-left: 1px dashed rgba(#000, .1);
  border-right: 1px dashed rgba(#000, .1);
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

.k-accordion-field summary:hover {
  cursor: pointer;
}
</style>
