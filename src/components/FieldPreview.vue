<script>
export default {
  extends: "k-text-field-preview",
  class: "k-accordion-field-preview",
  computed: {
    text() {
      if (this.field?.preview) {
        return this.$helper.string.template(this.field.preview, this.value);
      }

      return "…";
    }
  }
};
</script>
