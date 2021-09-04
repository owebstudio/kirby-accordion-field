<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Blueprint;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;

class AccordionField extends FieldClass
{
    protected $fields = [];
    protected $fieldsets;
    protected $pretty;
    protected $preview;
    protected $toggle;
    protected $value;

    public function __construct(array $params = [])
    {
        $this->setModel($params['model'] ?? site());
        $this->setFieldsets($params['fieldsets'] ?? []);
        $this->setPretty($params['pretty'] ?? false);
        $this->setPreview($params['preview'] ?? null);
        $this->setToggle($params['toggle'] ?? true);

        parent::__construct($params);
    }

    public function fields(string $fieldset = null): array
    {
        if (empty($fieldset) === false) {
            return $this->fieldsets[$fieldset]['fields'] ?? [];
        }

        return $this->fields;
    }

    public function fieldsets(): array
    {
        return $this->fieldsets;
    }

    public function form(array $fields = [], array $input = []): Form
    {
        return new Form([
            'fields' => $fields,
            'model' => $this->model(),
            'strict' => true,
            'values' => $input
        ]);
    }

    public function fill($value = null)
    {
        try {
            $fields = $this->fields();

            // set default values when initialization form
            if ($value === null) {
                $defaults = $this->form($fields, $this->default())->data(true);
                $this->value = $this->form($fields, $defaults)->values();
            } else {
                $this->value = $this->form($fields, $this->valueFromJson($value))->values();
            }
        } catch (Exception $e) {
            $this->value = [];
        }
    }

    public function pretty(): bool
    {
        return $this->pretty;
    }

    public function preview(): ?string
    {
        return $this->preview;
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
            'preview' => $this->preview(),
            'toggle' => $this->toggle(),
        ], parent::props());
    }

    protected function setFieldsets(array $fieldsets): void
    {
        foreach ($fieldsets as $key => &$fieldset) {
            $fieldset = Blueprint::extend($fieldset);

            // extends fieldsets and fields
            $fields = Blueprint::fieldsProps($fieldset['fields'] ?? []);
            $this->fields = array_replace_recursive($this->fields, $fields);
            $fields = $this->form($fields, $this->value ?? [])->fields();

            $fieldset['name'] = $key;
            $fieldset['label'] = I18n::translate($fieldset['label'], $fieldset['label']);
            $fieldset['fields'] = $fields->toArray();
            $fieldset['headline'] = $fieldset['headline'] ?? null;
            $fieldset['open'] = $fieldset['open'] ?? false;
        }

        $this->fieldsets = $fieldsets;
    }

    protected function setPretty(bool $pretty = false): void
    {
        $this->pretty = $pretty;
    }

    protected function setPreview(string $preview = null): void
    {
        $this->preview = $preview;
    }

    protected function setToggle(bool $toggle = true): void
    {
        $this->toggle = $toggle;
    }

    public function store($value)
    {
        $value = $this->valueToJson($value, $this->pretty());
        return empty($value) == false ? $value : '';
    }

    public function validations(): array
    {
        return [
            'accordion' => function ($value) {
                $errors = [];
                $fields = $this->form($this->fields(), $value)->fields();

                foreach ($fields as $field) {
                    $fieldErrors = $field->errors();

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
