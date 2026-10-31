<?php

namespace Oweb\AccordionField;

use Kirby\Blueprint\Blueprint;
use Kirby\Data\Json;
use Kirby\Form\Field\InputField;
use Kirby\Form\Form;
use Kirby\Form\Interface\ProvidesNestedForm;
use Kirby\Form\Mixin;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

class AccordionField extends InputField implements ProvidesNestedForm
{
    use Mixin\Pretty;

    #[Prop]
    protected array $fieldsets = [];

    #[Prop]
    protected string|null $preview = null;

    #[Prop(getter: 'getReset')]
    protected bool $reset = false;

    #[Prop]
    protected bool $toggle = true;

    protected array $value = [];
    protected Form|null $form = null;

    public function default(): mixed
    {
        $default = array_filter(
            $this->form()->defaults(),
            fn ($value) => parent::isEmptyValue($value) === false
        );

        // extend default prop
        if (is_string($this->default) === false) {
            $defaultProp = $this->default;
        } else {
            if ($extend = Blueprint::extend($this->default)) {
                $defaultProp = $extend;
            } else {
                $defaultProp = $this->stringTemplate($this->default);
            }
        }

        if (empty($defaultProp) === true || is_array($defaultProp) === false) {
            $defaultProp = [];
        }

        // merge fields own defaults with accordion default
        $default = array_replace($default, $defaultProp);

        return array_change_key_case($default, CASE_LOWER);
    }

    public function fields(): array
    {
        $fields = [];

        foreach ($this->fieldsets as $fieldset) {
            $fieldset = Blueprint::extend($fieldset);
            $fields   = array_replace_recursive($fields, Blueprint::fieldsProps($fieldset['fields'] ?? []));
        }

        return $fields;
    }

    public function fieldsets(): array
    {
        $fieldsets = [];
        $props     = $this->form()->fields()->toProps(defaults: true);

        foreach ($this->fieldsets as $name => $fieldset) {
            $fieldset = Blueprint::extend($fieldset);
            $fields   = [];

            foreach (Blueprint::fieldsProps($fieldset['fields'] ?? []) as $key => $field) {
                $key = strtolower($key);

                if (isset($props[$key]) === true) {
                    $fields[$key] = $props[$key];
                }
            }

            $label = $fieldset['label'] ?? Str::label($name);

            $fieldsets[$name] = [
                ...$fieldset,
                'name'     => $name,
                'label'    => I18n::translate($label, $label),
                'fields'   => $fields,
                'headline' => $fieldset['headline'] ?? null,
                'icon'     => $fieldset['icon'] ?? null,
                'open'     => $fieldset['open'] ?? false,
            ];
        }

        return $fieldsets;
    }

    public function form(): Form
    {
        $this->form ??= new Form(
            fields: $this->fields(),
            model: $this->model(),
            language: 'current'
        );

        return $this->form->reset();
    }

    public function fill(mixed $value): static
    {
        try {
            $value = Json::decode($value);
        } catch (Throwable) {
            $value = [];
        }

        return parent::fill($value);
    }

    public function getReset(): bool
    {
        return $this->reset;
    }

    public function isEmpty(): bool
    {
        return $this->isEmptyValue($this->value);
    }

    public function isEmptyValue(mixed $value = null): bool
    {
        if (is_array($value) === true) {
            $value = array_filter(
                $value,
                fn ($item) => parent::isEmptyValue($item) === false
            );
        }

        return parent::isEmptyValue($value);
    }

    public function preview(): string|null
    {
        return $this->preview;
    }

    public function props(): array
    {
        return [
            ...parent::props(),
            'default' => $this->default(),
        ];
    }

    public function toggle(): bool
    {
        return $this->toggle;
    }

    public function toFormValue(): array
    {
        return $this
            ->form()
            ->fill(input: $this->value)
            ->toFormValues();
    }

    public function toStoredValue(): string
    {
        if ($this->isEmpty() === true) {
            return '';
        }

        $value = $this
            ->form()
            ->submit(input: $this->value)
            ->toStoredValues();

        // store booleans like the content storage does
        array_walk_recursive($value, function (&$item) {
            if (is_bool($item) === true) {
                $item = $item === true ? 'true' : 'false';
            }
        });

        return Json::encode($value, pretty: $this->pretty());
    }

    public function errors(): array
    {
        $errors    = parent::errors();
        $value     = $this->toFormValue();
        $fieldsets = $this->fieldsets();
        $form      = $this->form()->fill(input: $value);

        // one error per subfield and rule, so the Panel lists each of them
        foreach ($fieldsets as $fieldset) {
            foreach ($fieldset['fields'] as $name => $field) {
                foreach ($form->field($name)->errors() as $key => $error) {
                    $errors[$name . '.' . $key] = $error . ' (' . $fieldset['label'] . ' > ' . ($field['label'] ?? $name) . ')';
                }
            }
        }

        return $errors;
    }
}
