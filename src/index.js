import Field from "./components/Field.vue";
import FieldPreview from "./components/FieldPreview.vue";

panel.plugin("clicktonext/kirby-accordion-field", {
  components: {
    'k-accordion-field-preview': FieldPreview
  },
  fields: {
    accordion: Field
  },
});
