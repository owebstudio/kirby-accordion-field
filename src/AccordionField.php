<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Blueprint;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\FieldClass;
use Kirby\Form\Fields;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;

class AccordionField extends FieldClass
{
    protected $fields;
    protected $fieldsets;
    protected $pretty;
    protected $toggle;
    protected $value = [];

    public function __construct(array $params = [])
    {
        parent::__construct($params);

        $this->setPretty($params['pretty'] ?? false);
        $this->setToggle($params['toggle'] ?? true);
        $this->setFieldsets($params['fieldsets'] ?? []);
    }

    public function fields(string $fieldset = null): Fields
    {
        if (empty($fieldset) === false) {
            return $this->fieldsets[$fieldset]['fields'] ?? new Fields();
        }

        return $this->fields ?? new Fields();
    }

    public function fieldsets(): array
    {
        return $this->fieldsets;
    }

    public function form(array $fields = [], array $input = []): Form
    {
        return new Form([
            'fields' => $fields,
            'model' => $this->model,
            'strict' => true,
            'values' => $input
        ]);
    }

    public function fill($value = null)
    {
        return $this->value = $this->valueFromJson($value);
    }

    public function pretty(): bool
    {
        return $this->pretty;
    }

    public function toggle(): bool
    {
        return $this->toggle;
    }

    public function props(): array
    {
        return array_merge([
            'fieldsets' => $this->fieldsets(),
            'pretty' => $this->pretty(),
            'toggle' => $this->toggle(),
        ], parent::props());
    }

    protected function setFieldsets(array $fieldsets = []): self
    {
        $this->fields = new Fields();

        // normalize fieldsets
        foreach ($fieldsets as $key => &$fieldset) {
            $fieldset = Blueprint::extend($fieldset);

            $fields = $this->form($fieldset['fields'] ?? [], $this->value)->fields();

            $fieldset['name'] = $key;
            $fieldset['label'] = I18n::translate($fieldset['label'], $fieldset['label']);
            $fieldset['fields'] = $fields->toArray();
            $fieldset['open'] = $fieldset['open'] ?? false;

            $this->fields->append($fields);
        }

        $this->fieldsets = $fieldsets;

        return $this;
    }

    protected function setPretty(bool $pretty = false)
    {
        $this->pretty = $pretty;
    }

    protected function setToggle(bool $toggle = true)
    {
        $this->toggle = $toggle;
    }

    public function store($value)
    {
        return $this->valueToJson($value, $this->pretty());
    }

    public function validations(): array
    {
        return [
            'accordion' => function ($value) {
                $errors = [];
                $fields = $this->fields()->toArray();

                foreach ($this->form($fields, $value)->fields() as $field) {
                    $fieldErrors = $field->errors();

                    // rough first validation
                    if (empty($fieldErrors) === false) {
                        $errors = array_merge($errors, $fieldErrors);
                    }
                }

                if (empty($errors) === false) {
                    throw new InvalidArgumentException(implode(", ", array_values($errors)));
                }

                return true;
            }
        ];
    }
}
