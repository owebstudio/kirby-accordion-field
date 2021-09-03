<template>
  <div class="k-accordion-field-preview">
    <p class="k-structure-table-text">
      {{ preview }}
    </p>
  </div>
</template>

<script>
export default {
  props: {
    field: Object,
    value: Object
  },
  computed: {
    preview() {
      if (this.field.preview) {
        return this.$helper.string.template(this.field.preview, this.value);
      }

      return "…";
    }
  }
};
</script>
