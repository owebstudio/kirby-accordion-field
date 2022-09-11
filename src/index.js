import Field from "./components/Field.vue";
import FieldPreview from "./components/FieldPreview.vue";

panel.plugin("kirbynext/kirby-accordion-field", {
  components: {
    'k-accordion-field-preview': FieldPreview
  },
  fields: {
    accordion: Field
  },
});
